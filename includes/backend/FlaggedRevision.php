<?php

use MediaWiki\Content\TextContent;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionAccessException;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use Wikimedia\Rdbms\IDBAccessObject;
use Wikimedia\Rdbms\SelectQueryBuilder;

/**
 * Class representing a stable version of a MediaWiki revision
 *
 * This contains a page revision
 */
class FlaggedRevision {

	/** @var RevisionRecord base revision */
	private $mRevRecord;

	/* Flagging metadata */
	/** @var mixed review timestamp */
	private $mTimestamp;
	/** @var array<string,int> Review tags */
	private array $mTags;
	/** @var string[] flags (for auto-review ect...) */
	private $mFlags;
	/** @var int reviewing user */
	private $mUser;

	/* Redundant fields for lazy-loading */
	/** @var Title|null */
	private $mTitle;
	/** @var array|null stable versions of template version used */
	private $mStableTemplates;

	/**
	 * @param stdClass $row DB row
	 * @param Title $title
	 * @param int $flags One of the IDBAccessObject::READ_… constants
	 * @return self
	 */
	private static function newFromRow( stdClass $row, Title $title, $flags ) {
		# Base Revision object
		$revFactory = MediaWikiServices::getInstance()->getRevisionFactory();
		$revRecord = $revFactory->newRevisionFromRow( $row, $flags, $title );
		$frev = new self( [
			'timestamp' => $row->fr_timestamp,
			'tags' => $row->fr_tags,
			'flags' => $row->fr_flags,
			'user_id' => $row->fr_user,
			'revrecord' => $revRecord,
		] );
		$frev->mTitle = $title;
		return $frev;
	}

	/**
	 * @param array $row
	 */
	public function __construct( array $row ) {
		if ( !is_array( $row['tags'] ) ) {
			$row['tags'] = self::expandRevisionTags( $row['tags'] );
		}

		$this->mTimestamp = $row['timestamp'];
		$this->mTags = array_merge( self::getDefaultTags(), $row['tags'] );
		$this->mFlags = explode( ',', $row['flags'] );
		$this->mUser = intval( $row['user_id'] );
		# Base Revision object
		$this->mRevRecord = $row['revrecord'];
		if ( !( $this->mRevRecord instanceof RevisionRecord ) ) {
			throw new InvalidArgumentException(
				'FlaggedRevision constructor passed invalid RevisionRecord object.'
			);
		}
	}

	/**
	 * Get a FlaggedRevision for a title and rev ID.
	 * Note: will return NULL if the revision is deleted.
	 * @param Title $title
	 * @param int $revId
	 * @param int $flags One of the IDBAccessObject::READ_… constants
	 * @return self|null (null on failure)
	 */
	public static function newFromTitle( Title $title, $revId, $flags = 0 ) {
		if ( !$revId || !FlaggedRevs::inReviewNamespace( $title ) ) {
			return null; // short-circuit
		}
		# User primary/replica as appropriate...
		$pageId = $title->getArticleID( $flags );
		if ( !$pageId ) {
			return null; // short-circuit query
		}
		if ( $flags & IDBAccessObject::READ_LATEST ) {
			$db = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();
		} else {
			$db = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
		}
		# Skip deleted revisions
		$frQuery = self::getQueryInfo();
		$row = $db->newSelectQueryBuilder()
			->tables( $frQuery['tables'] )
			->fields( $frQuery['fields'] )
			->where( [
				'fr_page_id' => $pageId,
				'fr_rev_id'  => $revId,
				$db->bitAnd( 'rev_deleted', RevisionRecord::DELETED_TEXT ) . ' = 0'
			] )
			->joinConds( $frQuery['joins'] )
			->caller( __METHOD__ )
			->fetchRow();
		# Sorted from highest to lowest, so just take the first one if any
		return $row ? self::newFromRow( $row, $title, $flags ) : null;
	}

	/**
	 * Get a FlaggedRevision of the stable version of a title.
	 * Note: will return NULL if the revision is deleted, though this
	 * should never happen as fp_stable is updated as revs are deleted.
	 * @param Title $title page title
	 * @param int $flags One of the IDBAccessObject::READ_… constants
	 * @return self|null (null on failure)
	 */
	public static function newFromStable( Title $title, $flags = 0 ) {
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
			return null; // short-circuit
		}
		# User primary/replica as appropriate...
		$pageId = $title->getArticleID( $flags );
		if ( !$pageId ) {
			return null; // short-circuit query
		}
		if ( $flags & IDBAccessObject::READ_LATEST ) {
			$db = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();
		} else {
			$db = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
		}
		# Check tracking tables
		$frQuery = self::getQueryInfo();
		$row = $db->newSelectQueryBuilder()
			->tables( $frQuery['tables'] )
			->fields( $frQuery['fields'] )
			->select( [ 'fr_page_id' ] )
			->join( 'flaggedpages', null, 'fr_rev_id = fp_stable' )
			->where( [
				'fp_page_id' => $pageId,
				$db->bitAnd( 'rev_deleted', RevisionRecord::DELETED_TEXT ) . ' = 0', // sanity
			] )
			->joinConds( $frQuery['joins'] )
			->caller( __METHOD__ )
			->fetchRow();
		if ( $row ) {
			if ( (int)$row->rev_page !== $pageId || (int)$row->fr_page_id !== $pageId ) {
				// Warn about inconsistent flaggedpages rows, see T246720
				$logger = LoggerFactory::getInstance( 'FlaggedRevisions' );
				$logger->warning( 'Found revision with mismatching page ID! ', [
					'fp_page_id' => $pageId,
					'fr_page_id' => $row->fr_page_id,
					'rev_page' => $row->rev_page,
					'rev_id' => $row->rev_id,
					'trace' => wfBacktrace()
				] );

				// TODO: Can we make this self-healing somehow? We shouldn't return a FlaggedRevision
				//       here that belongs to a different page. Can we find the correct revision for
				//       the given page ID in flaggedrevs? Can we rely on fr_page_id, or is that
				//       going to be wrong as well?
				return null;
			}

			return self::newFromRow( $row, $title, $flags );
		}
		return null;
	}

	/**
	 * Get the ID of the stable version of a title.
	 * @param Title $title page title
	 * @return int (0 on failure)
	 */
	public static function getStableRevId( Title $title ) {
		$srev = self::newFromStable( $title );
		return $srev ? $srev->getRevId() : 0;
	}

	/**
	 * Get a FlaggedRevision of the stable version of a title.
	 * Skips tracking tables to figure out new stable version.
	 * @param Title $title page title
	 * @return self|null (null on failure)
	 */
	public static function determineStable( Title $title ) {
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
			return null; // short-circuit
		}

		$db = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();
		$pageId = $title->getArticleID( IDBAccessObject::READ_LATEST );
		if ( !$pageId ) {
			return null; // short-circuit query
		}
		# Get visibility settings to see if page is reviewable...
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			$config = FRPageConfig::getStabilitySettings( $title, IDBAccessObject::READ_LATEST );
			if ( !$config['override'] ) {
				return null; // page is not reviewable; no stable version
			}
		}
		$baseConds = [
			'fr_page_id' => $pageId,
			'rev_id = fr_rev_id',
			'rev_page = fr_page_id', // sanity
			$db->bitAnd( 'rev_deleted', RevisionRecord::DELETED_TEXT ) . ' = 0'
		];

		$frQuery = self::getQueryInfo();
		$row = $db->newSelectQueryBuilder()
			->tables( $frQuery['tables'] )
			->fields( $frQuery['fields'] )
			->where( $baseConds )
			->orderBy( [ 'fr_rev_timestamp', 'fr_rev_id' ], SelectQueryBuilder::SORT_DESC )
			->joinConds( $frQuery['joins'] )
			->caller( __METHOD__ )
			->fetchRow();
		return $row ? self::newFromRow( $row, $title, IDBAccessObject::READ_LATEST ) : null;
	}

	/**
	 * Insert a FlaggedRevision object into the database
	 *
	 * @return true|string true on success, error string on failure
	 */
	public function insert() {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		# Set any flagged revision flags
		$this->mFlags = array_merge( $this->mFlags, [ 'dynamic' ] ); // legacy
		# Sanity check for partial revisions
		if ( !$this->getPage() ) {
			return 'no page id';
		} elseif ( !$this->getRevId() ) {
			return 'no revision id';
		}
		# Our new review entry
		$revRow = [
			'fr_page_id'       => $this->getPage(),
			'fr_rev_id'        => $this->getRevId(),
			'fr_rev_timestamp' => $dbw->timestamp( $this->getRevTimestamp() ),
			'fr_user'          => $this->mUser,
			'fr_timestamp'     => $dbw->timestamp( $this->mTimestamp ),
			'fr_quality'       => FR_CHECKED,
			'fr_tags'          => self::flattenRevisionTags( $this->mTags ),
			'fr_flags'         => implode( ',', $this->mFlags ),
		];
		# Update the main flagged revisions table...
		$dbw->newInsertQueryBuilder()->insertInto( 'flaggedrevs' )
			->ignore()
			->row( $revRow )
			->caller( __METHOD__ )
			->execute();
		if ( !$dbw->affectedRows() ) {
			return 'duplicate review';
		}
		return true;
	}

	/**
	 * Remove a FlaggedRevision object from the database
	 */
	public function delete() {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		# Delete from flaggedrevs table
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'flaggedrevs' )
			->where( [ 'fr_rev_id' => $this->getRevId() ] )
			->caller( __METHOD__ )
			->execute();
	}

	/**
	 * Get query info for FlaggedRevision DB row (flaggedrevs/revision tables)
	 * @return array
	 */
	private static function getQueryInfo() {
		$revQuery = MediaWikiServices::getInstance()->getRevisionStore()->getQueryInfo();
		return [
			'tables' => array_merge( [ 'flaggedrevs' ], $revQuery['tables'] ),
			'fields' => array_merge( $revQuery['fields'], [
				'fr_rev_id', 'fr_page_id', 'fr_rev_timestamp',
				'fr_user', 'fr_timestamp', 'fr_tags', 'fr_flags'
			] ),
			'joins' => [
				'revision' => [ 'JOIN', [
					'rev_id = fr_rev_id',
					'rev_page = fr_page_id', // sanity
				] ],
			] + $revQuery['joins'],
		];
	}

	/**
	 * @return int revision record's ID
	 */
	public function getRevId() {
		return $this->mRevRecord->getId();
	}

	/**
	 * @return int page ID
	 */
	private function getPage() {
		return $this->mRevRecord->getPageId();
	}

	/**
	 * @return Title
	 */
	public function getTitle() {
		if ( $this->mTitle === null ) {
			$linkTarget = $this->mRevRecord->getPageAsLinkTarget();
			$this->mTitle = Title::newFromLinkTarget( $linkTarget );
		}
		return $this->mTitle;
	}

	/**
	 * Get timestamp of review
	 * @return string revision timestamp in MW format
	 */
	public function getTimestamp() {
		return wfTimestamp( TS_MW, $this->mTimestamp );
	}

	/**
	 * Get timestamp of the corresponding revision
	 * Note: here for convenience
	 * @return string revision timestamp in MW format
	 */
	public function getRevTimestamp() {
		return $this->mRevRecord->getTimestamp();
	}

	/**
	 * Get the corresponding revision record
	 * @return RevisionRecord
	 */
	public function getRevisionRecord() {
		return $this->mRevRecord;
	}

	/**
	 * Get text of the corresponding revision
	 * Note: here for convenience
	 * @return string|null Revision text, if available
	 */
	public function getRevText() {
		try {
			$content = $this->mRevRecord->getContent( SlotRecord::MAIN );
		} catch ( RevisionAccessException $e ) {
			return '';
		}
		return ( $content instanceof TextContent ) ? $content->getText() : null;
	}

	/**
	 * Get tags (levels) of all tiers this revision has.
	 * Use getTag() instead unless you really need other tiers set on
	 * historical revisions (these tiers are no longer supported, cannot
	 * be set by users anymore).
	 * @return array<string,int> tag metadata
	 */
	public function getTags(): array {
		return $this->mTags;
	}

	/**
	 * Get the tag (level) of the page in the default tier.
	 * This is always defined (possibly zero) unless in protection mode.
	 */
	public function getTag(): ?int {
		return $this->mTags[FlaggedRevs::getTagName()] ?? null;
	}

	/**
	 * Whether the given user can set the tag in the default tier.
	 * Always returns true in protection mode if the user has review right.
	 */
	public function userCanSetTag( UserIdentity $user ): bool {
		return FlaggedRevs::userCanSetTag( $user, $this->getTag() );
	}

	/**
	 * Get the current stable version of the templates
	 * @return int[][] template versions (ns -> dbKey -> rev Id)
	 * Note: 0 used for template rev Id if it doesn't exist
	 */
	public function getStableTemplateVersions() {
		if ( $this->mStableTemplates == null ) {
			$this->mStableTemplates = [];
			$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

			$linksMigration = MediaWikiServices::getInstance()->getLinksMigration();
			[ $nsField, $titleField ] = $linksMigration->getTitleFields( 'templatelinks' );
			$queryInfo = $linksMigration->getQueryInfo( 'templatelinks' );
			$res = $dbr->newSelectQueryBuilder()
				->select( [ 'page_namespace', 'page_title', 'fp_stable' ] )
				->tables( $queryInfo['tables'] )
				->leftJoin( 'page', null, [ "page_namespace = $nsField", "page_title = $titleField" ] )
				->leftJoin( 'flaggedpages', null, 'fp_page_id = page_id' )
				->where( [
					'tl_from' => $this->getPage(),
					# Only get templates with stable or "review time" versions.
					$dbr->expr( 'fp_stable', '!=', null ),
				] ) // current version templates
				->joinConds( $queryInfo['joins'] )
				->caller( __METHOD__ )
				->fetchResultSet();
			foreach ( $res as $row ) {
				$revId = (int)$row->fp_stable; // 0 => none
				$this->mStableTemplates[$row->page_namespace][$row->page_title] = $revId;
			}
		}
		return $this->mStableTemplates;
	}

	/**
	 * Fetch pending template changes for this reviewed page version.
	 * For each template, the "version used" (for stable parsing) is:
	 *    (a) (the latest rev) if FR_INCLUDES_CURRENT. Might be non-existing.
	 *    (b) newest( stable rev, rev at time of review ) if FR_INCLUDES_STABLE
	 *
	 * @return bool
	 */
	public function findPendingTemplateChanges() {
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_CURRENT ) {
			return false; // short-circuit
		}
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$linksMigration = MediaWikiServices::getInstance()->getLinksMigration();
		[ $nsField, $titleField ] = $linksMigration->getTitleFields( 'templatelinks' );
		$queryInfo = $linksMigration->getQueryInfo( 'templatelinks' );
		$ret = $dbr->newSelectQueryBuilder()
			->select( [ $nsField, $titleField ] )
			->tables( $queryInfo['tables'] )
			->leftJoin( 'page', null, [ "page_namespace = $nsField", "page_title = $titleField" ] )
			->join( 'flaggedpages', null, 'fp_page_id = page_id' )
			->where( [
				'tl_from' => $this->getPage(),
				# Only get templates with stable or "review time" versions.
				$dbr->expr( 'fp_pending_since', '!=', null )->or( 'fp_stable', '=', null ),
			] ) // current version templates
			->joinConds( $queryInfo['joins'] )
			->caller( __METHOD__ )
			->fetchResultSet();
		return (bool)$ret->count();
	}

	/**
	 * Notify the reverted tag subsystem that the edit was reviewed.
	 */
	public function approveRevertedTagUpdate() {
		$rtuManager = MediaWikiServices::getInstance()->getRevertedTagUpdateManager();
		$rtuManager->approveRevertedTagForRevision( $this->getRevId() );
	}

	/**
	 * @param int $rev_id
	 * @param int $flags One of the IDBAccessObject::READ_… constants
	 * @return bool
	 */
	public static function revIsFlagged( int $rev_id, int $flags = 0 ): bool {
		if ( $flags & IDBAccessObject::READ_LATEST ) {
			$db = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();
		} else {
			$db = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
		}
		return (bool)$db->newSelectQueryBuilder()
			->select( '1' )
			->from( 'flaggedrevs' )
			->where( [ 'fr_rev_id' => $rev_id ] )
			->caller( __METHOD__ )
			->fetchField();
	}

	/**
	 * @return array<string,int>
	 */
	public static function getDefaultTags(): array {
		return FlaggedRevs::useOnlyIfProtected() ? [] : [ FlaggedRevs::getTagName() => 0 ];
	}

	public static function getDefaultTag(): ?int {
		return FlaggedRevs::useOnlyIfProtected() ? null : 0;
	}

	/**
	 * @param string $tags
	 * @return array<string,int>
	 */
	public static function expandRevisionTags( string $tags ): array {
		$flags = [];
		$max = FlaggedRevs::getMaxLevel();
		$tags = str_replace( '\n', "\n", $tags ); // B/C, old broken rows
		// Tag string format is <tag:val\ntag:val\n...>
		$tags = explode( "\n", $tags );
		foreach ( $tags as $tuple ) {
			$set = explode( ':', $tuple, 2 );
			// Skip broken and old serializations that end with \n, which shows up as [ "" ] here
			if ( count( $set ) == 2 ) {
				[ $tag, $value ] = $set;
				$flags[$tag] = min( max( 0, (int)$value ), $max );
			}
		}
		return $flags;
	}

	/**
	 * @param array<string,int> $tags
	 * @return string
	 */
	public static function flattenRevisionTags( array $tags ): string {
		$flags = '';
		foreach ( $tags as $tag => $value ) {
			if ( $flags ) {
				$flags .= "\n";
			}
			$flags .= $tag . ':' . (int)$value;
		}
		return $flags;
	}
}
