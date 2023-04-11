<?php

use MediaWiki\Cache\CacheKeyHelper;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\PreparedUpdate;
use Wikimedia\Assert\PreconditionException;
use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\IDatabase;

/**
 * Class representing a MediaWiki article and history
 *
 * FlaggableWikiPage::getTitleInstance() is preferred over constructor calls
 */
class FlaggableWikiPage extends WikiPage {
	/** @var int */
	private $stable = 0;
	/** @var FlaggedRevision|false|null */
	private $stableRev = null;
	/** @var bool|null */
	private $revsArePending = null;
	/** @var int|null */
	private $pendingRevCount = null;
	/** @var array|null */
	private $pageConfig = null;
	/** @var bool|null */
	private $syncedInTracking = null;
	/** @var PreparedUpdate|null */
	private $preparedUpdate = null;
	/** @var MapCacheLRU|null */
	private static $instances = null;

	/**
	 * @return MapCacheLRU
	 */
	private static function getInstanceCache(): MapCacheLRU {
		if ( !self::$instances ) {
			self::$instances = new MapCacheLRU( 10 );
		}
		return self::$instances;
	}

	/**
	 * Get a FlaggableWikiPage for a given title
	 *
	 * @param Title|PageIdentity $title
	 * @return self
	 */
	public static function getTitleInstance( $title ) {
		$cache = self::getInstanceCache();
		$key = CacheKeyHelper::getKeyForPage( $title );
		$fwp = $cache->get( $key );
		if ( !$fwp ) {
			$fwp = self::newInstance( $title );
			$cache->set( $key, $fwp );
		}
		return $fwp;
	}

	/**
	 * @param PageIdentity $page
	 * @return self
	 */
	public static function newInstance( PageIdentity $page ) {
		return $page instanceof self ? $page : new self( $page );
	}

	/**
	 * @deprecated Please use {@see newInstance} instead
	 * @param PageIdentity $pageIdentity
	 */
	public function __construct( PageIdentity $pageIdentity ) {
		parent::__construct( $pageIdentity );
	}

	/**
	 * Transfer the prepared edit cache from a WikiPage object.
	 * Also make available the current prepared update to later
	 * calls to getCurrentUpdate().
	 *
	 * @note This will throw unless called during an ongoing edit!
	 *
	 * @param WikiPage $page
	 * @return void
	 */
	public function preloadPreparedEdit( WikiPage $page ) {
		$this->mPreparedEdit = $page->mPreparedEdit;

		try {
			$this->preparedUpdate = $page->getCurrentUpdate();
		} catch ( PreconditionException | LogicException $ex ) {
			// Ignore. getCurrentUpdate() will throw.
		}
	}

	/**
	 * @inheritDoc
	 * @return PreparedUpdate
	 */
	public function getCurrentUpdate(): PreparedUpdate {
		if ( $this->preparedUpdate ) {
			return $this->preparedUpdate;
		}
		return parent::getCurrentUpdate();
	}

	/**
	 * Clear object process cache values
	 * @return void
	 */
	public function clear() {
		$this->stable = 0;
		$this->stableRev = null;
		$this->revsArePending = null;
		$this->pendingRevCount = null;
		$this->pageConfig = null;
		$this->syncedInTracking = null;
		parent::clear(); // call super!
	}

	/**
	 * Is the stable version shown by default for this page?
	 * @return bool
	 */
	public function isStableShownByDefault() {
		if ( !$this->isReviewable() ) {
			return false; // no stable versions can exist
		}
		$config = $this->getStabilitySettings(); // page configuration
		return (bool)$config['override'];
	}

	/**
	 * Do edits have to be reviewed before being shown by default (going live)?
	 * @return bool
	 */
	public function editsRequireReview() {
		return (
			$this->isReviewable() && // reviewable page
			$this->isStableShownByDefault() && // and stable versions override
			$this->getStableRev() // and there is a stable version
		);
	}

	/**
	 * Has data for this page been loaded?
	 * @return bool
	 */
	public function isDataLoaded() {
		return $this->mDataLoaded;
	}

	/**
	 * Are edits to this page currently pending?
	 * @return bool
	 */
	public function revsArePending() {
		if ( !$this->mDataLoaded ) {
			$this->loadPageData();
		}
		return $this->revsArePending;
	}

	/**
	 * Get number of revs since the stable revision
	 * Note: slower than revsArePending()
	 * @param int $flags FR_MASTER (be sure to use loadFromDB( FR_MASTER ) if set)
	 * @return int
	 */
	public function getPendingRevCount( $flags = 0 ) {
		global $wgParserCacheExpireTime;

		if ( !$this->mDataLoaded ) {
			$this->loadPageData();
		}
		# Pending count deferred even after page data load
		if ( $this->pendingRevCount !== null ) {
			return $this->pendingRevCount; // use process cache
		}
		$srev = $this->getStableRev();
		if ( !$srev ) {
			return 0; // none
		}
		$sRevId = $srev->getRevId();

		$fname = __METHOD__;
		$callback = function (
			$oldValue = null, &$ttl = null, array &$setOpts = []
		) use ( $flags, $srev, $fname ) {
			$db = wfGetDB( ( $flags & FR_MASTER ) ? DB_PRIMARY : DB_REPLICA );
			$setOpts += Database::getCacheSetOptions( $db );

			return (int)$db->selectField(
				'revision',
				'COUNT(*)',
				[
					'rev_page' => $this->getId(),
					// T17515
					'rev_timestamp > ' .
						$db->addQuotes( $db->timestamp( $srev->getRevTimestamp() ) )
				],
				$fname
			);
		};

		if ( $flags & FR_MASTER ) {
			$this->pendingRevCount = $callback();
		} else {
			$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
			$this->pendingRevCount = (int)$cache->getWithSetCallback(
				# Confirm that cache value was made against the same stable rev Id.
				# This avoids lengthy cache pollution if $sRevId is outdated.
				$cache->makeKey( 'flaggedrevs-countPending', $this->getId(), $sRevId ),
				$wgParserCacheExpireTime,
				$callback,
				[
					'touchedCallback' => function () {
						return wfTimestampOrNull( TS_UNIX, $this->getTouched() );
					}
				]
			);
		}

		return $this->pendingRevCount;
	}

	/**
	 * Checks if the stable version is synced with the current revision
	 * Note: slower than getPendingRevCount()
	 * @return bool
	 */
	public function stableVersionIsSynced() {
		global $wgParserCacheExpireTime;

		$srev = $this->getStableRev();
		if ( !$srev ) {
			return true;
		}
		# Stable text revision must be the same as the current
		if ( $this->revsArePending() ) {
			return false;
		}
		# If using the current version of includes, there is nothing else to check.
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_CURRENT ) {
			return true; // short-circuit
		}

		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();

		return (bool)$cache->getWithSetCallback(
			$cache->makeKey( 'flaggedrevs-includes-synced', $this->getId() ),
			$wgParserCacheExpireTime,
			static function () use ( $srev ) {
				# Since the stable and current revisions have the same text and only outputs, the
				# only other things to check for are template differences in the output.
				# (a) Check if the current output has a newer template used
				# (b) Check if the stable version has a template that was deleted
				return ( !$srev->findPendingTemplateChanges() ) ? 1 : 0;
			},
			[
				'touchedCallback' => function () {
					return $this->getTouched();
				}
			]
		);
	}

	/**
	 * Are template changes and ONLY template changes pending?
	 * @return bool
	 */
	public function onlyTemplatesPending() {
		return ( !$this->revsArePending() && !$this->stableVersionIsSynced() );
	}

	/**
	 * Is this page less open than the site defaults?
	 * @return bool
	 */
	public function isPageLocked() {
		return ( !FlaggedRevs::isStableShownByDefault() && $this->isStableShownByDefault() );
	}

	/**
	 * Is this page more open than the site defaults?
	 * @return bool
	 */
	public function isPageUnlocked() {
		return ( FlaggedRevs::isStableShownByDefault() && !$this->isStableShownByDefault() );
	}

	/**
	 * Tags are only shown for unreviewed content and this page is not locked/unlocked?
	 * @return bool
	 */
	public function lowProfileUI() {
		global $wgFlaggedRevsLowProfile;
		return $wgFlaggedRevsLowProfile &&
			FlaggedRevs::isStableShownByDefault() == $this->isStableShownByDefault();
	}

	/**
	 * Is this article reviewable?
	 * @return bool
	 */
	public function isReviewable() {
		if ( !FlaggedRevs::inReviewNamespace( $this->mTitle ) ) {
			return false;
		}
		# Check if flagging is disabled for this page via config
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			$config = $this->getStabilitySettings(); // page configuration
			return (bool)$config['override']; // stable is default or flagging disabled
		}
		return true;
	}

	/**
	 * Get the stable revision ID
	 * @return int
	 */
	public function getStable() {
		if ( !FlaggedRevs::inReviewNamespace( $this->mTitle ) ) {
			return 0; // short-circuit
		}
		if ( !$this->mDataLoaded ) {
			$this->loadPageData();
		}
		return (int)$this->stable;
	}

	/**
	 * Get the stable revision
	 * @return FlaggedRevision|null
	 */
	public function getStableRev() {
		if ( !FlaggedRevs::inReviewNamespace( $this->mTitle ) ) {
			return null; // short-circuit
		}
		if ( !$this->mDataLoaded ) {
			$this->loadPageData();
		}
		# Stable rev deferred even after page data load
		if ( $this->stableRev === null ) {
			$srev = FlaggedRevision::newFromTitle( $this->mTitle, $this->stable );
			$this->stableRev = $srev ?: false; // cache negative hits too
		}
		return $this->stableRev ?: null; // false => null
	}

	/**
	 * Get visibility restrictions on page
	 * @return array [ 'override' => int, 'autoreview' => string, 'expiry' => string ]
	 */
	public function getStabilitySettings() {
		if ( !$this->mDataLoaded ) {
			$this->loadPageData();
		}
		return $this->pageConfig;
	}

	/**
	 * Get the fp_reviewed value for this page
	 * @return bool
	 */
	public function syncedInTracking() {
		if ( !$this->mDataLoaded ) {
			$this->loadPageData();
		}
		return $this->syncedInTracking;
	}

	/**
	 * Get the newest of the highest rated flagged revisions of this page
	 * Note: will not return deleted revisions
	 * @return int
	 */
	public function getBestFlaggedRevId() {
		$dbr = wfGetDB( DB_REPLICA );
		# Get the highest quality revision (not necessarily this one).
		$oldid = $dbr->selectField( [ 'flaggedrevs', 'revision' ],
			'fr_rev_id',
			[
				'fr_page_id' => $this->getId(),
				'rev_page = fr_page_id', // sanity
				'rev_id = fr_rev_id',
				$dbr->bitAnd( 'rev_deleted', RevisionRecord::DELETED_TEXT ) . ' = 0'
			],
			__METHOD__,
			[
				'ORDER BY' 	=> 'fr_rev_timestamp DESC'
			]
		);
		return (int)$oldid;
	}

	/**
	 * Updates the fp_reviewed field for this article
	 */
	public function lazyUpdateSyncStatus() {
		$services = MediaWikiServices::getInstance();
		if ( $services->getReadOnlyMode()->isReadOnly() ) {
			return;
		}

		$services->getJobQueueGroup()->push(
			new FRExtraCacheUpdateJob(
				$this->getTitle(),
				[ 'type' => 'updatesyncstate' ]
			)
		);
	}

	/**
	 * Fetch a page record with the given conditions
	 * @param IDatabase $dbr Database object
	 * @param array $conditions
	 * @param array $options
	 * @return stdClass|false
	 */
	protected function pageData( $dbr, $conditions, $options = [] ) {
		$fname = __METHOD__;
		$selectCallback = static function () use ( $dbr, $conditions, $options, $fname ) {
			$pageQuery = WikiPage::getQueryInfo();

			return $dbr->selectRow(
				array_merge( $pageQuery['tables'], [ 'flaggedpages', 'flaggedpage_config' ] ),
				array_merge(
					$pageQuery['fields'],
					[ 'fpc_override', 'fpc_level', 'fpc_expiry' ],
					[ 'fp_pending_since', 'fp_stable', 'fp_reviewed' ]
				),
				$conditions,
				$fname,
				$options,
				$pageQuery['joins'] + [
					'flaggedpages' => [ 'LEFT JOIN', 'fp_page_id = page_id' ],
					'flaggedpage_config' => [ 'LEFT JOIN', 'fpc_page_id = page_id' ],
				]
			);
		};

		if ( !$dbr->isReadOnly() ) {
			// load data directly without cache
			return $selectCallback();
		} else {
			$cache = MediaWikiServices::getInstance()->getLocalServerObjectCache();

			return $cache->getWithSetCallback(
				$cache->makeKey( 'flaggedrevs-pageData', $this->getNamespace(), $this->getDBkey() ),
				$cache::TTL_MINUTE,
				$selectCallback
			);
		}
	}

	/**
	 * Set the page field data loaded from some source
	 * @param stdClass|string|int $data Database row object or "fromdb" or "fromdbmaster"
	 * @return void
	 */
	public function loadPageData( $data = self::READ_NORMAL ) {
		$this->mDataLoaded = true; // sanity

		// Initialize defaults before trying to access the database
		$this->stable = 0; // 0 => "found nothing"
		$this->stableRev = null; // defer this one...
		$this->revsArePending = false; // false => "found nothing" or "none pending"
		$this->pendingRevCount = null; // defer this one...
		$this->pageConfig = FRPageConfig::getDefaultVisibilitySettings(); // default
		$this->syncedInTracking = true; // false => "unreviewed" or "synced"

		# Fetch data from DB as needed...
		$from = WikiPage::convertSelectType( $data );
		if ( $from === self::READ_NORMAL || $from === self::READ_LATEST ) {
			$db = wfGetDB( $from === self::READ_LATEST ? DB_PRIMARY : DB_REPLICA );
			$data = $this->pageDataFromTitle( $db, $this->mTitle );
		}
		# Load in primary page data...
		parent::loadPageData( $data /* Row obj */ );
		# Load in flaggedrevs Row data if the page exists...(sanity check NS)
		if ( $data && FlaggedRevs::inReviewNamespace( $this->mTitle ) ) {
			if ( $data->fpc_override !== null ) { // page config row found
				$this->pageConfig = FRPageConfig::getVisibilitySettingsFromRow( $data );
			}
			if ( $data->fp_stable !== null ) { // stable rev found
				$this->stable = (int)$data->fp_stable;
				$this->revsArePending = ( $data->fp_pending_since !== null ); // revs await review
				$this->syncedInTracking = (bool)$data->fp_reviewed;
			}
		}
	}

	/**
	 * Updates the flagging tracking tables for this page
	 * @param FlaggedRevision $srev The new stable version
	 * @param int|null $latest The latest rev ID (optional)
	 */
	public function updateStableVersion( FlaggedRevision $srev, $latest = null ) {
		if ( !$this->exists() ) {
			// No bogus entries
			return;
		}

		$revRecord = $srev->getRevisionRecord();
		if ( !$revRecord ) {
			// No bogus entries
			return;
		}

		# Get the latest revision ID if not set
		if ( !$latest ) {
			$latest = $this->mTitle->getLatestRevID( Title::GAID_FOR_UPDATE );
		}
		$dbw = wfGetDB( DB_PRIMARY );
		# Get the timestamp of the first edit after the stable version (if any)...
		$nextTimestamp = null;
		if ( $revRecord->getId() != $latest ) {
			$timestamp = $dbw->timestamp( $revRecord->getTimestamp() );
			$nextEditTS = $dbw->selectField( 'revision',
				'rev_timestamp',
				[
					'rev_page' => $this->getId(),
					"rev_timestamp > " . $dbw->addQuotes( $timestamp ) ],
				__METHOD__,
				[ 'ORDER BY' => 'rev_timestamp ASC', 'LIMIT' => 1 ]
			);
			if ( $nextEditTS ) { // sanity check
				$nextTimestamp = $nextEditTS;
			}
		}
		# Get the new page sync status...
		$synced = !(
			$nextTimestamp !== null || // edits pending
			$srev->findPendingTemplateChanges() // template changes pending
		);
		# Alter table metadata
		$dbw->replace(
			'flaggedpages',
			'fp_page_id',
			[
				'fp_page_id'       => $revRecord->getPageId(), // Don't use $this->getId(), T246720
				'fp_stable'        => $revRecord->getId(),
				'fp_reviewed'      => $synced ? 1 : 0,
				'fp_quality'       => FR_CHECKED,
				'fp_pending_since' => $dbw->timestampOrNull( $nextTimestamp )
			],
			__METHOD__
		);
		# Update pending edit tracking table
		$this->updatePendingList( $this->getId(), $latest );
	}

	/**
	 * Updates the flagging tracking tables for this page
	 */
	public function clearStableVersion() {
		if ( !$this->exists() ) {
			return; // nothing to do
		}
		$dbw = wfGetDB( DB_PRIMARY );
		$dbw->delete( 'flaggedpages', [ 'fp_page_id' => $this->getId() ], __METHOD__ );
		$dbw->delete( 'flaggedpage_pending', [ 'fpp_page_id' => $this->getId() ], __METHOD__ );
	}

	/**
	 * Updates the flaggedpage_pending table
	 * @param int $pageId
	 * @param int $latest Latest revision
	 */
	private function updatePendingList( $pageId, $latest ) {
		$data = [];

		$dbw = wfGetDB( DB_PRIMARY );

		# Get the latest revision of FR_CHECKED
		$row = $dbw->selectRow(
			[ 'flaggedrevs', 'revision' ],
			[ 'fr_rev_id', 'rev_timestamp' ],
			[
				'fr_page_id' => $pageId,
				'rev_id = fr_rev_id', // rev exists
				'rev_page = fr_page_id', // sanity
				$dbw->bitAnd( 'rev_deleted', RevisionRecord::DELETED_TEXT ) . ' = 0'
			],
			__METHOD__,
			[ 'ORDER BY' => 'fr_rev_timestamp DESC', 'LIMIT' => 1 ]
		);
		# If there is a revision of this level, track it...
		# Revisions accepted to one tier count as accepted
		# at the lower tiers (i.e. quality -> checked).
		if ( $row ) {
			$id = (int)$row->fr_rev_id;
			$ts = $row->rev_timestamp;
		} else { // use previous rev of higher tier (if any)
			$id = 0;
			$ts = '';
		}
		# Get edits that actually are pending...
		if ( $id && $latest > $id ) {
			# Get the timestamp of the edit after this version (if any)
			$nextTimestamp = $dbw->selectField( 'revision',
				'rev_timestamp',
				[
					'rev_page' => $pageId,
					$dbw->makeList( [
						'rev_timestamp > ' . $dbw->addQuotes( $ts ),
						'rev_timestamp = ' . $dbw->addQuotes( $ts ) . ' AND rev_id > ' . $id,
					], IDatabase::LIST_OR ),
				],
				__METHOD__,
				[ 'ORDER BY' => [ 'rev_timestamp ASC', 'rev_id ASC' ], 'LIMIT' => 1 ]
			);
			// No newer revision found
			if ( $nextTimestamp !== false ) {
				$data[] = [
					'fpp_page_id'       => $pageId,
					'fpp_quality'       => FR_CHECKED,
					'fpp_rev_id'        => $id,
					'fpp_pending_since' => $nextTimestamp
				];
			}
		}
		# Clear any old junk, and insert new rows
		$dbw->delete( 'flaggedpage_pending', [ 'fpp_page_id' => $pageId ], __METHOD__ );
		if ( $data !== [] ) {
			$dbw->insert( 'flaggedpage_pending', $data, __METHOD__ );
		}
	}
}
