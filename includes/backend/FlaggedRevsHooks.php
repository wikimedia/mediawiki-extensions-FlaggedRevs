<?php
// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
// phpcs:disable MediaWiki.Commenting.FunctionComment.MissingDocumentationPublic

use MediaWiki\Config\Config;
use MediaWiki\Deferred\DeferredUpdates;
use MediaWiki\Extension\GoogleNewsSitemap\Specials\GoogleNewsSitemap;
use MediaWiki\Extension\Notifications\Model\Event;
use MediaWiki\Hook\ArticleMergeCompleteHook;
use MediaWiki\Hook\ArticleRevisionVisibilitySetHook;
use MediaWiki\Hook\MagicWordwgVariableIDsHook;
use MediaWiki\Hook\PageMoveCompleteHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\ParserGetVariableValueSwitchHook;
use MediaWiki\Hook\RecentChange_saveHook;
use MediaWiki\Hook\WikiExporter__dumpStableQueryHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\Hook\ArticleDeleteCompleteHook;
use MediaWiki\Page\Hook\ArticleUndeleteHook;
use MediaWiki\Page\Hook\RevisionFromEditCompleteHook;
use MediaWiki\Page\Hook\RevisionUndeletedHook;
use MediaWiki\Parser\Parser;
use MediaWiki\Permissions\Hook\GetUserPermissionsErrorsHook;
use MediaWiki\Permissions\Hook\UserGetRightsHook;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\EditResult;
use MediaWiki\Storage\Hook\BeforeRevertedTagUpdateHook;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use MediaWiki\Storage\Hook\RevisionDataUpdatesHook;
use MediaWiki\Title\Title;
use MediaWiki\User\ActorMigration;
use MediaWiki\User\Hook\AutopromoteConditionHook;
use MediaWiki\User\Hook\UserLoadAfterLoadFromSessionHook;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserIdentityUtils;
use MediaWiki\User\UserNameUtils;
use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\IDBAccessObject;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Rdbms\RawSQLValue;
use Wikimedia\Rdbms\SelectQueryBuilder;

/**
 * Class containing hooked functions for a FlaggedRevs environment
 */
class FlaggedRevsHooks implements
	ArticleDeleteCompleteHook,
	ArticleMergeCompleteHook,
	ArticleRevisionVisibilitySetHook,
	ArticleUndeleteHook,
	AutopromoteConditionHook,
	BeforeRevertedTagUpdateHook,
	getUserPermissionsErrorsHook,
	MagicWordwgVariableIDsHook,
	RevisionFromEditCompleteHook,
	PageSaveCompleteHook,
	PageMoveCompleteHook,
	ParserFirstCallInitHook,
	ParserGetVariableValueSwitchHook,
	RecentChange_saveHook,
	RevisionDataUpdatesHook,
	RevisionUndeletedHook,
	UserGetRightsHook,
	UserLoadAfterLoadFromSessionHook,
	WikiExporter__dumpStableQueryHook
{

	private Config $config;
	private PermissionManager $permissionManager;
	private RevisionLookup $revisionLookup;
	private UserNameUtils $userNameUtils;
	private UserIdentityUtils $userIdentityUtils;

	public function __construct(
		Config $config,
		PermissionManager $permissionManager,
		RevisionLookup $revisionLookup,
		UserNameUtils $userNameUtils,
		UserIdentityUtils $userIdentityUtils
	) {
		$this->config = $config;
		$this->permissionManager = $permissionManager;
		$this->revisionLookup = $revisionLookup;
		$this->userNameUtils = $userNameUtils;
		$this->userIdentityUtils = $userIdentityUtils;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Extension_registration#Customizing_registration
	 */
	public static function onRegistration() {
		# Review tier constants...
		define( 'FR_CHECKED', 0 ); // "basic"/"checked"

		# Inclusion (templates) settings
		define( 'FR_INCLUDES_CURRENT', 0 );
		define( 'FR_INCLUDES_STABLE', 2 );

		# Autoreview settings for priviledged users
		define( 'FR_AUTOREVIEW_NONE', 0 );
		define( 'FR_AUTOREVIEW_CHANGES', 1 );
		define( 'FR_AUTOREVIEW_CREATION', 2 );
		define( 'FR_AUTOREVIEW_CREATION_AND_CHANGES', FR_AUTOREVIEW_CHANGES | FR_AUTOREVIEW_CREATION );

		# User preference for when page views use stable or current page versions
		define( 'FR_SHOW_STABLE_DEFAULT', 0 ); // page config default; b/c with "false"
		define( 'FR_SHOW_STABLE_ALWAYS', 1 ); // stable version (current version if none)
		define( 'FR_SHOW_STABLE_NEVER', 2 ); // current version

		# Autopromote conds (F=70,R=82)
		# @TODO: move these 6 to core
		define( 'APCOND_FR_EDITSUMMARYCOUNT', 70821 );
		define( 'APCOND_FR_NEVERBLOCKED', 70822 );
		define( 'APCOND_FR_NEVERBOCKED', 70822 ); // b/c
		define( 'APCOND_FR_UNIQUEPAGECOUNT', 70823 );
		define( 'APCOND_FR_CONTENTEDITCOUNT', 70824 );
		define( 'APCOND_FR_USERPAGEBYTES', 70825 );
		define( 'APCOND_FR_EDITCOUNT', 70826 );

		define( 'APCOND_FR_EDITSPACING', 70827 );
		define( 'APCOND_FR_CHECKEDEDITCOUNT', 70828 );
		define( 'APCOND_FR_MAXREVERTEDEDITRATIO', 70829 );
		define( 'APCOND_FR_NEVERDEMOTED', 70830 );
	}

	/**
	 * @inheritDoc
	 *
	 * Update flaggedrevs table on revision restore
	 */
	public function onRevisionUndeleted( $revision, $oldPageID ) {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		# Some revisions may have had null rev_id values stored when deleted.
		# This hook is called after insertOn() however, in which case it is set
		# as a new one.
		$dbw->newUpdateQueryBuilder()
			->update( 'flaggedrevs' )
			->set( [ 'fr_page_id' => $revision->getPageId() ] )
			->where( [ 'fr_page_id' => $oldPageID, 'fr_rev_id' => $revision->getId() ] )
			->caller( __METHOD__ )
			->execute();
	}

	/**
	 * @inheritDoc
	 */
	public function onArticleMergeComplete( $sourceTitle, $destTitle ) {
		$oldPageID = $sourceTitle->getArticleID();
		$newPageID = $destTitle->getArticleID();
		# Get flagged revisions from old page id that point to destination page
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		$revIDs = $dbw->newSelectQueryBuilder()
			->select( 'fr_rev_id' )
			->from( 'flaggedrevs' )
			->join( 'revision', null, 'fr_rev_id = rev_id' )
			->where( [
				'fr_page_id' => $oldPageID,
				'rev_page' => $newPageID
			] )
			->caller( __METHOD__ )
			->fetchFieldValues();
		# Update these rows
		if ( $revIDs ) {
			$dbw->newUpdateQueryBuilder()
				->update( 'flaggedrevs' )
				->set( [ 'fr_page_id' => $newPageID ] )
				->where( [ 'fr_page_id' => $oldPageID, 'fr_rev_id' => $revIDs ] )
				->caller( __METHOD__ )
				->execute();
		}
		# Update pages...stable versions possibly lost to another page
		FlaggedRevs::stableVersionUpdates( $sourceTitle );
		FlaggedRevs::updateHtmlCaches( $sourceTitle );
		FlaggedRevs::stableVersionUpdates( $destTitle );
		FlaggedRevs::updateHtmlCaches( $destTitle );
	}

	/**
	 * @inheritDoc
	 * (a) Update flaggedrevs page/tracking tables
	 * (b) Autoreview pages moved into reviewable namespaces (bug 19379)
	 */
	public function onPageMoveComplete(
		$oLinkTarget,
		$nLinkTarget,
		$userIdentity,
		$pageId,
		$redirid,
		$reason,
		$revision
	) {
		$services = MediaWikiServices::getInstance();

		$ntitle = Title::newFromLinkTarget( $nLinkTarget );
		$otitle = Title::newFromLinkTarget( $oLinkTarget );
		if ( FlaggedRevs::inReviewNamespace( $ntitle ) ) {
			$user = User::newFromIdentity( $userIdentity );

			if ( FlaggedRevs::inReviewNamespace( $otitle ) ) {
				$fa = FlaggableWikiPage::getTitleInstance( $ntitle );
				$fa->loadPageData( IDBAccessObject::READ_LATEST );
				$config = $fa->getStabilitySettings();
				// Insert a stable log entry if page doesn't have default wiki settings
				if ( !FRPageConfig::configIsReset( $config ) ) {
					FlaggedRevsLog::updateStabilityLogOnMove( $ntitle, $otitle, $reason, $user );
				}
			} elseif ( FlaggedRevs::autoReviewNewPages() ) {
				$fa = FlaggableWikiPage::getTitleInstance( $ntitle );
				$fa->loadPageData( IDBAccessObject::READ_LATEST );
				// Re-validate NS/config (new title may not be reviewable)
				if ( $fa->isReviewable() &&
					$services->getPermissionManager()->userCan( 'autoreview', $user, $ntitle )
				) {
					// Auto-review such edits like new pages...
					$revRecord = $services->getRevisionLookup()
						->getRevisionByTitle( $ntitle, 0, IDBAccessObject::READ_LATEST );
					if ( $revRecord ) { // sanity
						FlaggedRevs::autoReviewEdit(
							$fa,
							$user,
							$revRecord,
							null,
							true,
							true // approve the reverted tag update
						);
					}
				}
			}
		}

		# Update page and tracking tables and clear cache
		FlaggedRevs::stableVersionUpdates( $otitle );
		FlaggedRevs::updateHtmlCaches( $otitle );
		FlaggedRevs::stableVersionUpdates( $ntitle );
		FlaggedRevs::updateHtmlCaches( $ntitle );
	}

	/**
	 * @inheritDoc
	 * (a) Update flaggedrevs page/tracking tables
	 * (b) Pages with stable versions that use this page will be purged
	 * Note: pages with current versions that use this page should already be purged
	 */
	public function onRevisionDataUpdates(
		$title, $renderedRevision, &$updates
	) {
		$updates[] = new FRStableVersionUpdate( $title, $renderedRevision );
		$updates[] = new FRExtraCacheUpdate( $title );
	}

	/**
	 * @inheritDoc
	 * (a) Update flaggedrevs page/tracking tables
	 * (b) Pages with stable versions that use this page will be purged
	 * Note: pages with current versions that use this page should already be purged
	 */
	public function onArticleDeleteComplete( $wikiPage, $user, $reason, $id, $content, $logEntry, $count ) {
		FlaggedRevs::clearTrackingRows( $id );
		FlaggedRevs::extraHTMLCacheUpdate( $wikiPage->getTitle() );
	}

	/**
	 * @inheritDoc
	 * (a) Update flaggedrevs page/tracking tables
	 * (b) Pages with stable versions that use this page will be purged
	 * Note: pages with current versions that use this page should already be purged
	 */
	public function onArticleUndelete( $title, $create, $comment, $oldPageId, $restoredPages ) {
		FlaggedRevs::stableVersionUpdates( $title );
		FlaggedRevs::updateHtmlCaches( $title );
	}

	/**
	 * @inheritDoc
	 * Update flaggedrevs page/tracking tables
	 */
	public function onArticleRevisionVisibilitySet( $title, $ids, $visibilityChangeMap ) {
		$changed = FlaggedRevs::stableVersionUpdates( $title );
		if ( $changed ) {
			FlaggedRevs::updateHtmlCaches( $title );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onParserFirstCallInit( $parser ) {
		global $wgFlaggedRevsProtection;

		if ( !$wgFlaggedRevsProtection ) {
			return;
		}

		$parser->setFunctionHook( 'pendingchangelevel',
			[ __CLASS__, 'parserPendingChangeLevel' ], Parser::SFH_NO_HASH );
	}

	/**
	 * @inheritDoc
	 */
	public function onParserGetVariableValueSwitch( $parser, &$cache, $word, &$ret, $frame ) {
		global $wgFlaggedRevsProtection;
		if ( $wgFlaggedRevsProtection && $word === 'pendingchangelevel' ) {
			$ret = self::parserPendingChangeLevel( $parser );
			$cache[$word] = $ret;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onMagicWordwgVariableIDs( &$words ) {
		global $wgFlaggedRevsProtection;

		if ( !$wgFlaggedRevsProtection ) {
			return;
		}

		$words[] = 'pendingchangelevel';
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Parser_functions#The_setFunctionHook_hook
	 *
	 * @param Parser $parser
	 * @param string $page
	 * @return string
	 */
	public static function parserPendingChangeLevel( Parser $parser, $page = '' ) {
		$title = Title::newFromText( $page );
		if ( !( $title instanceof Title ) ) {
			$title = $parser->getTitle();
		}
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
			return '';
		}
		$page = FlaggableWikiPage::getTitleInstance( $title );
		if ( !$page->isDataLoaded() && !$parser->incrementExpensiveFunctionCount() ) {
			return '';
		}
		$config = $page->getStabilitySettings();
		return $config['autoreview'];
	}

	/**
	 * @inheritDoc
	 * Check page move and patrol permissions for FlaggedRevs
	 */
	public function onGetUserPermissionsErrors( $title, $user, $action, &$result ) {
		if ( $result === false ) {
			return true; // nothing to do
		}
		$services = MediaWikiServices::getInstance();
		$pm = $services->getPermissionManager();
		# Don't let users vandalize pages by moving them...
		if ( $action === 'move' ) {
			if ( !FlaggedRevs::inReviewNamespace( $title ) || !$title->exists() ) {
				return true; // extra short-circuit
			}
			$flaggedArticle = FlaggableWikiPage::getTitleInstance( $title );
			# If the draft shows by default anyway, nothing to do...
			if ( !$flaggedArticle->isStableShownByDefault() ) {
				return true;
			}
			$frev = $flaggedArticle->getStableRev();
			if ( $frev && !$pm->userHasRight( $user, 'review' ) &&
				!$pm->userHasRight( $user, 'movestable' )
			) {
				# Allow for only editors/reviewers to move this page
				$result = false;
				return false;
			}
		# Enforce autoreview/review restrictions
		} elseif ( $action === 'autoreview' || $action === 'review' ) {
			# Get autoreview restriction settings...
			$fa = FlaggableWikiPage::getTitleInstance( $title );
			$config = $fa->getStabilitySettings();
			# Convert Sysop -> protect
			$right = $config['autoreview'];
			if ( $right === 'sysop' ) {
				// Backwards compatibility, rewrite sysop -> editprotected
				$right = 'editprotected';
			}
			if ( $right === 'autoconfirmed' ) {
				// Backwards compatibility, rewrite autoconfirmed -> editsemiprotected
				$right = 'editsemiprotected';
			}
			# Check if the user has the required right, if any
			if ( $right != '' && !$pm->userHasRight( $user, $right ) ) {
				$result = false;
				return false;
			}
			# Respect page protection to handle cases of "review wars".
			# If a page is restricted from editing such that a user cannot
			# edit it, then said user should not be able to review it.
			foreach ( $services->getRestrictionStore()->getRestrictions( $title, 'edit' ) as $right ) {
				if ( $right === 'sysop' ) {
					// Backwards compatibility, rewrite sysop -> editprotected
					$right = 'editprotected';
				}
				if ( $right === 'autoconfirmed' ) {
					// Backwards compatibility, rewrite autoconfirmed -> editsemiprotected
					$right = 'editsemiprotected';
				}
				if ( $right != '' && !$pm->userHasRight( $user, $right ) ) {
					$result = false;
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * When an edit is made by a user, review it if either:
	 * (a) The user can 'autoreview' and the edit's base revision was checked
	 * (b) The edit is a self-revert to the stable version (by anyone)
	 * (c) The user can 'autoreview' new pages and this edit is a new page
	 * (d) The user can 'review' and the "review pending edits" checkbox was checked
	 *
	 * Note: This hook handler is triggered in a variety of places, not just
	 *       during edits. Example include null revision creation, imports,
	 *       and page moves.
	 *
	 * Note: RC items not inserted yet, RecentChange_save hook does rc_patrolled bit...
	 * @param WikiPage $wikiPage
	 * @param RevisionRecord $revRecord
	 * @param int|false $baseRevId
	 * @param UserIdentity $user
	 */
	public static function maybeMakeEditReviewed(
		WikiPage $wikiPage, RevisionRecord $revRecord, $baseRevId, UserIdentity $user
	) {
		global $wgRequest;

		$title = $wikiPage->getTitle(); // convenience
		# Edit must be non-null, to a reviewable page, with $user set
		$fa = FlaggableWikiPage::getTitleInstance( $title );
		$fa->loadPageData( IDBAccessObject::READ_LATEST );
		if ( !$fa->isReviewable() ) {
			return;
		}

		$user = User::newFromIdentity( $user );

		$fa->preloadPreparedEdit( $wikiPage ); // avoid double parse
		$title->resetArticleID( $revRecord->getPageId() ); // Avoid extra DB hit and lag issues
		# Get what was just the current revision ID
		$prevRevId = $revRecord->getParentId();
		# Get edit timestamp. Existance already validated by \MediaWiki\EditPage\EditPage
		$editTimestamp = $wgRequest->getVal( 'wpEdittime' );
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		# Is the page manually checked off to be reviewed?
		if ( $editTimestamp
			&& $wgRequest->getCheck( 'wpReviewEdit' )
			&& $pm->userCan( 'review', $user, $title )
			&& self::editCheckReview( $fa, $revRecord, $user, $editTimestamp )
		) {
			// Reviewed... done!
			return;
		}
		# All cases below require auto-review of edits to be enabled
		if ( !FlaggedRevs::autoReviewEnabled() ) {
			// Short-circuit
			return;
		}
		# If a $baseRevId is passed in, the edit is using an old revision's text
		$isOldRevCopy = (bool)$baseRevId; // null edit or rollback
		# Get the revision ID the incoming one was based off...
		$revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
		if ( !$baseRevId && $prevRevId ) {
			$prevTimestamp = $revisionLookup->getTimestampFromId(
				$prevRevId,
				IDBAccessObject::READ_LATEST
			);
			# The user just made an edit. The one before that should have
			# been the current version. If not reflected in wpEdittime, an
			# edit may have been auto-merged in between, in that case, discard
			# the baseRevId given from the client.
			if ( $editTimestamp && $prevTimestamp === $editTimestamp ) {
				$baseRevId = $wgRequest->getInt( 'baseRevId' );
			}
			# If baseRevId not given, assume the previous revision ID (for bots).
			# For auto-merges, this also occurs since the given ID is ignored.
			if ( !$baseRevId ) {
				$baseRevId = $prevRevId;
			}
		}
		$frev = null; // flagged rev this edit was based on
		$flags = null; // review flags (null => default flags)
		$srev = $fa->getStableRev();
		# Case A: this user can auto-review edits. Check if either:
		# (a) this new revision creates a new page and new page autoreview is enabled
		# (b) this new revision is based on an old, reviewed, revision
		if ( $pm->userCan( 'autoreview', $user, $title ) ) {
			# For rollback/null edits, use the previous ID as the alternate base ID.
			# Otherwise, use the 'altBaseRevId' parameter passed in by the request.
			$altBaseRevId = $isOldRevCopy ? $prevRevId : $wgRequest->getInt( 'altBaseRevId' );
			if ( !$prevRevId ) { // New pages
				$reviewableNewPage = FlaggedRevs::autoReviewNewPages();
				$reviewableChange = false;
			} else { // Edits to existing pages
				$reviewableNewPage = false; // had previous rev
				# If a edit was automatically merged, do not trust 'baseRevId' (bug 33481).
				# Do this by verifying the user-provided edittime against the prior revision.
				$prevRevTimestamp = $revisionLookup->getTimestampFromId(
					$prevRevId,
					IDBAccessObject::READ_LATEST
				);
				if ( $editTimestamp && $editTimestamp !== $prevRevTimestamp ) {
					$baseRevId = $prevRevId;
					$altBaseRevId = 0;
				}
				# Check if the base revision was reviewed...
				if ( FlaggedRevs::autoReviewEdits() ) {
					$frev = FlaggedRevision::newFromTitle( $title, $baseRevId, IDBAccessObject::READ_LATEST );
					if ( !$frev && $altBaseRevId ) {
						$frev = FlaggedRevision::newFromTitle( $title, $altBaseRevId, IDBAccessObject::READ_LATEST );
					}
				}
				$reviewableChange = $frev ||
					# Bug 57073: If a user with autoreview returns the page to its last stable
					# version, it should be marked stable, regardless of the method used to do so.
					( $srev && $revRecord->getSha1() === $srev->getRevisionRecord()->getSha1() );
			}
			# Is this an edit directly to a reviewed version or a new page?
			if ( $reviewableNewPage || $reviewableChange ) {
				if ( $isOldRevCopy && $frev ) {
					$flags = $frev->getTags(); // null edits & rollbacks keep previous tags
				}
				# Review this revision of the page...
				FlaggedRevs::autoReviewEdit( $fa, $user, $revRecord, $flags );
			}
		# Case B: the user cannot autoreview edits. Check if either:
		# (a) this is a rollback to the stable version
		# (b) this is a self-reversion to the stable version
		# These are subcases of making a new revision based on an old, reviewed, revision.
		} elseif ( FlaggedRevs::autoReviewEdits() && $srev ) {
			# Check for rollbacks...
			$reviewableChange = (
				$isOldRevCopy && // rollback or null edit
				$baseRevId != $prevRevId && // not a null edit
				$baseRevId == $srev->getRevId() && // restored stable rev
				$pm->userCan( 'autoreviewrestore', $user, $title )
			);
			# Check for self-reversions (checks text hashes)...
			if ( !$reviewableChange ) {
				$reviewableChange = self::isSelfRevertToStable( $revRecord, $srev, $baseRevId, $user );
			}
			# Is this a rollback or self-reversion to the stable rev?
			if ( $reviewableChange ) {
				$flags = $srev->getTags(); // use old tags
				# Review this revision of the page...
				FlaggedRevs::autoReviewEdit( $fa, $user, $revRecord, $flags );
			}
		}
	}

	/**
	 * Review $rev if $editTimestamp matches the previous revision's timestamp.
	 * Otherwise, review the revision that has $editTimestamp as its timestamp value.
	 * @param WikiPage $wikiPage
	 * @param RevisionRecord $revRecord
	 * @param User $user
	 * @param string $editTimestamp
	 * @return bool
	 */
	private static function editCheckReview(
		WikiPage $wikiPage, $revRecord, $user, $editTimestamp
	) {
		$prevTimestamp = null;
		$prevRevId = $revRecord->getParentId(); // id for revision before $revRecord
		# Check wpEdittime against the former current rev for verification
		if ( $prevRevId ) {
			$prevTimestamp = MediaWikiServices::getInstance()
				->getRevisionLookup()
				->getTimestampFromId( $prevRevId, IDBAccessObject::READ_LATEST );
		}
		# Was $revRecord an edit to an existing page?
		if ( $prevTimestamp && $prevRevId ) {
			# Check wpEdittime against the former current revision's time.
			# If an edit was auto-merged in between, then the new revision
			# has content different than what the user expected. However, if
			# the auto-merged edit was reviewed, then assume that it's OK.
			if ( $editTimestamp != $prevTimestamp
				&& !FlaggedRevision::revIsFlagged( $prevRevId, IDBAccessObject::READ_LATEST )
			) {
				return false; // not flagged?
			}
		}
		$flags = null;
		# Review this revision of the page...
		return FlaggedRevs::autoReviewEdit( $wikiPage, $user, $revRecord, $flags, false /* manual */ );
	}

	/**
	 * Check if a user reverted himself to the stable version
	 * @param RevisionRecord $revRecord
	 * @param FlaggedRevision $srev
	 * @param int $baseRevId
	 * @param User $user
	 * @return bool
	 */
	private static function isSelfRevertToStable(
		RevisionRecord $revRecord,
		$srev,
		$baseRevId,
		$user
	) {
		if ( !$srev || $baseRevId != $srev->getRevId() ) {
			return false; // user reports they are not the same
		}
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		# Such a revert requires 1+ revs between it and the stable
		$revWhere = ActorMigration::newMigration()->getWhere( $dbw, 'rev_user', $user );
		$revertedRevs = (bool)$dbw->newSelectQueryBuilder()
			->select( '1' )
			->from( 'revision' )
			->tables( $revWhere['tables'] )
			->where( [
				'rev_page' => $revRecord->getPageId(),
				$dbw->expr( 'rev_id', '>', intval( $baseRevId ) ), // stable rev
				$dbw->expr( 'rev_id', '<', intval( $revRecord->getId() ) ), // this rev
				$revWhere['conds']
			] )
			->joinConds( $revWhere['joins'] )
			->caller( __METHOD__ )
			->fetchField();
		if ( !$revertedRevs ) {
			return false; // can't be a revert
		}
		# Check that this user is ONLY reverting his/herself.
		$otherUsers = (bool)$dbw->newSelectQueryBuilder()
			->select( '1' )
			->from( 'revision' )
			->tables( $revWhere['tables'] )
			->where( [
				'rev_page' => $revRecord->getPageId(),
				$dbw->expr( 'rev_id', '>', intval( $baseRevId ) ),
				'NOT( ' . $revWhere['conds'] . ' )'
			] )
			->joinConds( $revWhere['joins'] )
			->caller( __METHOD__ )
			->fetchField();
		if ( $otherUsers ) {
			return false; // only looking for self-reverts
		}
		# Confirm the text because we can't trust this user.
		return ( $revRecord->getSha1() === $srev->getRevisionRecord()->getSha1() );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageSaveComplete
	 *
	 * When an user makes a null-edit we sometimes want to review it...
	 * (a) Null undo or rollback
	 * (b) Null edit with review box checked
	 * Note: called after edit ops are finished
	 *
	 * @param WikiPage $wikiPage
	 * @param UserIdentity $userIdentity
	 * @param string $summary
	 * @param int $flags
	 * @param RevisionRecord $revisionRecord
	 * @param EditResult $editResult
	 */
	public static function maybeNullEditReview(
		WikiPage $wikiPage,
		UserIdentity $userIdentity,
		string $summary,
		int $flags,
		RevisionRecord $revisionRecord,
		EditResult $editResult
	) {
		global $wgRequest;
		if ( !$editResult->isNullEdit() ) {
			// Not a null edit
			return;
		}

		$baseId = $editResult->getOriginalRevisionId();

		# Rollback/undo or box checked
		$reviewEdit = $wgRequest->getCheck( 'wpReviewEdit' );
		if ( !$baseId && !$reviewEdit ) {
			// Short-circuit
			return;
		}

		$title = $wikiPage->getTitle(); // convenience
		$fa = FlaggableWikiPage::getTitleInstance( $title );
		$fa->loadPageData( IDBAccessObject::READ_LATEST );
		if ( !$fa->isReviewable() ) {
			// Page is not reviewable
			return;
		}
		# Get the current revision ID
		$revLookup = MediaWikiServices::getInstance()->getRevisionLookup();
		$revRecord = $revLookup->getRevisionByTitle( $title, 0, IDBAccessObject::READ_LATEST );
		if ( !$revRecord ) {
			return;
		}

		$flags = null;
		$user = User::newFromIdentity( $userIdentity );
		# Is this a rollback/undo that didn't change anything?
		if ( $baseId > 0 ) {
			$frev = FlaggedRevision::newFromTitle( $title, $baseId ); // base rev of null edit
			$pRevRecord = $revLookup->getRevisionById( $revRecord->getParentId() ); // current rev parent
			$revIsNull = ( $pRevRecord && $pRevRecord->hasSameContent( $revRecord ) );
			# Was the edit that we tried to revert to reviewed?
			# We avoid auto-reviewing null edits to avoid confusion (bug 28476).
			if ( $frev && !$revIsNull ) {
				# Review this revision of the page...
				$ok = FlaggedRevs::autoReviewEdit( $wikiPage, $user, $revRecord, $flags );
				if ( $ok ) {
					FlaggedRevs::markRevisionPatrolled( $revRecord ); // reviewed -> patrolled
					FlaggedRevs::extraHTMLCacheUpdate( $title );
					return;
				}
			}
		}
		# Get edit timestamp, it must exist.
		$editTimestamp = $wgRequest->getVal( 'wpEdittime' );
		# Is the page checked off to be reviewed?
		if ( $editTimestamp && $reviewEdit && MediaWikiServices::getInstance()
				->getPermissionManager()->userCan( 'review', $user, $title )
		) {
			# Check wpEdittime against current revision's time.
			# If an edit was auto-merged in between, review only up to what
			# was the current rev when this user started editing the page.
			if ( $revRecord->getTimestamp() != $editTimestamp ) {
				$revRecord = $revLookup->getRevisionByTimestamp(
					$title,
					$editTimestamp,
					IDBAccessObject::READ_LATEST
				);
				if ( !$revRecord ) {
					// Deleted?
					return;
				}
			}
			# Review this revision of the page...
			$ok = FlaggedRevs::autoReviewEdit( $wikiPage, $user, $revRecord, $flags, false /* manual */ );
			if ( $ok ) {
				FlaggedRevs::markRevisionPatrolled( $revRecord ); // reviewed -> patrolled
				FlaggedRevs::extraHTMLCacheUpdate( $title );
			}
		}
	}

	/**
	 * @inheritDoc
	 * Mark auto-reviewed edits as patrolled
	 */
	public function onRecentChange_save( $rc ) {
		if ( !$rc->getAttribute( 'rc_this_oldid' ) ) {
			return;
		}
		// don't autopatrol autoreviewed edits when using pending changes,
		// otherwise edits by autoreviewed users on pending changes protected pages would be
		// autopatrolled and could not be checked through RC patrol as on regular pages
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			return;
		}
		$fa = FlaggableWikiPage::getTitleInstance( $rc->getTitle() );
		$fa->loadPageData( IDBAccessObject::READ_LATEST );
		// Is the page reviewable?
		if ( $fa->isReviewable() ) {
			$revId = $rc->getAttribute( 'rc_this_oldid' );
			// If the edit we just made was reviewed, then it's the stable rev
			$frev = FlaggedRevision::newFromTitle( $rc->getTitle(), $revId, IDBAccessObject::READ_LATEST );
			// Reviewed => patrolled
			if ( $frev ) {
				DeferredUpdates::addCallableUpdate( static function () use ( $rc, $frev ) {
					RevisionReviewForm::updateRecentChanges( $rc, 'patrol', $frev );
				} );
				$rcAttribs = $rc->getAttributes();
				$rcAttribs['rc_patrolled'] = 1; // make sure irc/email notifs know status
				$rc->setAttribs( $rcAttribs );
			}
		}
	}

	private function maybeIncrementReverts(
		WikiPage $wikiPage, RevisionRecord $revRecord, EditResult $editResult, UserIdentity $user
	) {
		$undid = $editResult->getOldestRevertedRevisionId();

		# Was this an edit by an auto-sighter that undid another edit?
		if ( !( $undid && $this->permissionManager->userHasRight( $user, 'autoreview' ) ) ) {
			return;
		}

		// Note: $rev->getTitle() might be undefined (no rev id?)
		$badRevRecord = $this->revisionLookup->getRevisionByTitle( $wikiPage->getTitle(), $undid );
		if ( !( $badRevRecord && $badRevRecord->getUser( RevisionRecord::RAW ) ) ) {
			return;
		}

		$revRecordUser = $revRecord->getUser( RevisionRecord::RAW );
		$badRevRecordUser = $badRevRecord->getUser( RevisionRecord::RAW );
		if ( $this->userIdentityUtils->isNamed( $badRevRecordUser )
			&& !$badRevRecordUser->equals( $revRecordUser ) // no self-reverts
		) {
			FRUserCounters::incCount( $badRevRecordUser->getId(), 'revertedEdits' );
		}
	}

	/**
	 * Get query data for making efficient queries based on rev_user and
	 * rev_timestamp in an actor table world.
	 * @param IReadableDatabase $dbr
	 * @param User $user
	 * @return array[]
	 */
	private static function getQueryData( IReadableDatabase $dbr, $user ) {
		$revWhere = ActorMigration::newMigration()->getWhere( $dbr, 'rev_user', $user );
		$queryData = [];
		foreach ( $revWhere['orconds'] as $key => $cond ) {
			if ( $key === 'actor' ) {
				$data = [
					'tables' => [ 'revision' ] + $revWhere['tables'],
					'tsField' => 'revactor_timestamp',
					'cond' => $cond,
					'joins' => $revWhere['joins'],
					'useIndex' => [ 'temp_rev_user' => 'actor_timestamp' ],
				];
				$data['joins']['temp_rev_user'][0] = 'JOIN';
			} elseif ( $key === 'username' ) {
				// Ignore this, shouldn't happen
				continue;
			} else { // future migration from revision_actor_temp to rev_actor
				$data = [
					'tables' => [ 'revision' ],
					'tsField' => 'rev_timestamp',
					'cond' => $cond,
					'joins' => [],
					'useIndex' => [ 'revision' => 'rev_actor_timestamp' ],
				];
			}
			$queryData[] = $data;
		}
		return $queryData;
	}

	/**
	 * Check if a user meets the edit spacing requirements.
	 * If the user does not, return a *lower bound* number of seconds
	 * that must elapse for it to be possible for the user to meet them.
	 * @param User $user
	 * @param int $spacingReq days apart (of edit points)
	 * @param int $pointsReq number of edit points
	 * @return true|int True if passed, int seconds on failure
	 */
	private static function editSpacingCheck( User $user, $spacingReq, $pointsReq ) {
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$queryData = self::getQueryData( $dbr, $user );

		$benchmarks = 0; // actual edit points
		# Convert days to seconds...
		$spacingReq = $spacingReq * 24 * 3600;
		# Check the oldest edit
		$lower = false;
		foreach ( $queryData as $data ) {
			$ts = $dbr->newSelectQueryBuilder()
				->tables( $data['tables'] )
				->field( $data['tsField'] )
				->where( $data['cond'] )
				->orderby( $data['tsField'] )
				->useIndex( $data['useIndex'] )
				->joinConds( $data['joins'] )
				->caller( __METHOD__ )
				->fetchField();
			$lower = $lower && $ts ? min( $lower, $ts ) : ( $lower ?: $ts );
		}
		# Recursively check for an edit $spacingReq seconds later, until we are done.
		if ( $lower ) {
			$benchmarks++; // the first edit above counts
			while ( $lower && $benchmarks < $pointsReq ) {
				$next = (int)wfTimestamp( TS_UNIX, $lower ) + $spacingReq;
				$lower = false;
				foreach ( $queryData as $data ) {
					$ts = $dbr->newSelectQueryBuilder()
						->tables( $data['tables'] )
						->field( $data['tsField'] )
						->where( $data['cond'] )
						->andWhere( $dbr->expr( $data['tsField'], '>', $dbr->timestamp( $next ) ) )
						->orderBy( $data['tsField'] )
						->useIndex( $data['useIndex'] )
						->joinConds( $data['joins'] )
						->caller( __METHOD__ )
						->fetchField();
					$lower = $lower && $ts ? min( $lower, $ts ) : ( $lower ?: $ts );
				}
				if ( $lower !== false ) {
					$benchmarks++;
				}
			}
		}
		if ( $benchmarks >= $pointsReq ) {
			return true;
		} else {
			// Does not add time for the last required edit point; it could be a
			// fraction of $spacingReq depending on the last actual edit point time.
			return ( $spacingReq * ( $pointsReq - $benchmarks - 1 ) );
		}
	}

	/**
	 * Check if a user has enough implicitly reviewed edits (before stable version)
	 * @param User $user
	 * @param int $editsReq
	 * @param int $seconds
	 * @return bool
	 */
	private static function reviewedEditsCheck( User $user, $editsReq, $seconds = 0 ) {
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$queryData = self::getQueryData( $dbr, $user );
		// Get cutoff timestamp (excludes edits that are too recent)
		foreach ( $queryData as $k => $data ) {
			$queryData[$k]['conds'] = [
				$data['cond'],
				$dbr->expr( $data['tsField'], '<', $dbr->timestamp( time() - $seconds ) ),
			];
		}
		// Get the lower cutoff to avoid scanning over many rows.
		// Users with many revisions will only have the last 10k inspected.
		$lowCutoff = false;
		if ( $user->getEditCount() > 10000 ) {
			foreach ( $queryData as $data ) {
				$lowCutoff = max( $lowCutoff, $dbr->newSelectQueryBuilder()
					->tables( $data['tables'] )
					->field( $data['tsField'] )
					->where( $data['conds'] )
					->orderBy( $data['tsField'], SelectQueryBuilder::SORT_DESC )
					->offset( 9999 )
					->joinConds( $data['joins'] )
					->caller( __METHOD__ )
					->fetchField()
				);
			}
		}
		$lowCutoff = $lowCutoff ?: 1; // default to UNIX 1970
		// Get revs from pages that have a reviewed rev of equal or higher timestamp
		$ct = 0;
		foreach ( $queryData as $data ) {
			if ( $ct >= $editsReq ) {
				break;
			}
			$res = $dbr->newSelectQueryBuilder()
				->select( '1' )
				->tables( $data['tables'] )
				->join( 'flaggedpages', null, 'fp_page_id = rev_page' )
				->where( $data['conds'] )
				->andWhere( [
						// bug 15515
						$dbr->expr( 'fp_pending_since', '=', null )
							->or( 'fp_pending_since', '>', new RawSQLValue( $data['tsField'] ) ),
						// Avoid too much scanning
						$dbr->expr( $data['tsField'], '>', $dbr->timestamp( $lowCutoff ) )
				] )
				->limit( $editsReq - $ct )
				->joinConds( $data['joins'] )
				->caller( __METHOD__ )
				->fetchResultSet();
			$ct += $res->numRows();
		}
		return ( $ct >= $editsReq );
	}

	/**
	 * Checks if $user was previously blocked since $cutoff_unixtime
	 * @param User $user
	 * @param IReadableDatabase $db
	 * @param int $cutoff_unixtime = 0
	 * @return bool
	 */
	private static function wasPreviouslyBlocked(
		User $user,
		IReadableDatabase $db,
		$cutoff_unixtime = 0
	) {
		$conds = [
			'log_namespace' => NS_USER,
			'log_title'     => $user->getUserPage()->getDBkey(),
			'log_type'      => 'block',
			'log_action'    => 'block'
		];
		if ( $cutoff_unixtime > 0 ) {
			# Hint to improve NS,title,timestamp INDEX use
			$conds[] = $db->expr( 'log_timestamp', '>=', $db->timestamp( $cutoff_unixtime ) );
		}
		return (bool)$db->newSelectQueryBuilder()
			->select( '1' )
			->from( 'logging' )
			->where( $conds )
			->caller( __METHOD__ )
			->fetchField();
	}

	/**
	 * @param int $userId
	 * @param int $seconds
	 * @param int $limit
	 * @return int
	 */
	private static function recentEditCount( $userId, $seconds, $limit ) {
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$queryData = self::getQueryData( $dbr, User::newFromId( $userId ) );
		# Get cutoff timestamp (edits that are too recent)
		$cutoff = $dbr->timestamp( time() - $seconds );
		# Check all recent edits...
		$ct = 0;
		foreach ( $queryData as $data ) {
			if ( $ct > $limit ) {
				break;
			}
			$res = $dbr->newSelectQueryBuilder()
				->select( '1' )
				->tables( $data['tables'] )
				->where( $data['cond'] )
				->andWhere( $dbr->expr( $data['tsField'], '>', $cutoff ) )
				->limit( $limit + 1 - $ct )
				->joinConds( $data['joins'] )
				->caller( __METHOD__ )
				->fetchResultSet();
			$ct += $res->numRows();
		}
		return $ct;
	}

	/**
	 * @param int $userId
	 * @param int $seconds
	 * @param int $limit
	 * @return int
	 */
	private static function recentContentEditCount( $userId, $seconds, $limit ) {
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$queryData = self::getQueryData( $dbr, User::newFromId( $userId ) );
		# Get cutoff timestamp (edits that are too recent)
		$cutoff = $dbr->timestamp( time() - $seconds );
		# Check all recent content edits...
		$ct = 0;
		$contentNamespaces = MediaWikiServices::getInstance()
			->getNamespaceInfo()
			->getContentNamespaces();
		foreach ( $queryData as $data ) {
			if ( $ct > $limit ) {
				break;
			}
			$res = $dbr->newSelectQueryBuilder()
				->select( '1' )
				->tables( $data['tables'] )
				->join( 'page', null, 'rev_page = page_id' )
				->where( [
					$data['cond'],
					$dbr->expr( $data['tsField'], '>', $cutoff ),
					'page_namespace' => $contentNamespaces
				] )
				->limit( $limit + 1 - $ct )
				->useIndex( $data['useIndex'] )
				->joinConds( $data['joins'] )
				->caller( __METHOD__ )
				->fetchResultSet();
			$ct += $res->numRows();
		}
		return $ct;
	}

	/**
	 * @inheritDoc
	 * Grant 'autoreview' rights to users with the 'bot' right
	 */
	public function onUserGetRights( $user, &$rights ) {
		# Make sure bots always have the 'autoreview' right
		if ( in_array( 'bot', $rights ) && !in_array( 'autoreview', $rights ) ) {
			$rights[] = 'autoreview';
		}
	}

	/**
	 * @inheritDoc
	 * Mark the edit as autoreviewed if needed.
	 * This must happen in this hook, and not in onPageSaveComplete(), for two reasons:
	 * - onBeforeRevertedTagUpdate() implementation relies on it happening first (T361918)
	 * - It must also be done for null revisions created during some actions (T361940, T361960)
	 */
	public function onRevisionFromEditComplete(
		$wikiPage, $revRecord, $baseRevId, $user, &$tags
	) {
		self::maybeMakeEditReviewed( $wikiPage, $revRecord, $baseRevId, $user );
	}

	/**
	 * @inheritDoc
	 * Callback that autopromotes user according to the setting in
	 * $wgFlaggedRevsAutopromote. This also handles user stats tallies.
	 */
	public function onPageSaveComplete(
		$wikiPage,
		$userIdentity,
		$summary,
		$flags,
		$revisionRecord,
		$editResult
	) {
		self::maybeIncrementReverts( $wikiPage, $revisionRecord, $editResult, $userIdentity );

		self::maybeNullEditReview( $wikiPage, $userIdentity, $summary, $flags, $revisionRecord, $editResult );

		# Ignore null edits, edits by IP users or temporary users, and MW role account edits
		if ( $editResult->isNullEdit() ||
			!$this->userIdentityUtils->isNamed( $userIdentity ) ||
			!$this->userNameUtils->isUsable( $userIdentity->getName() )
		) {
			return;
		}

		# No sense in running counters if nothing uses them
		if ( !( $this->config->get( 'FlaggedRevsAutopromote' ) || $this->config->get( 'FlaggedRevsAutoconfirm' ) ) ) {
			return;
		}

		$userId = $userIdentity->getId();
		DeferredUpdates::addCallableUpdate( static function () use ( $userId, $wikiPage, $summary ) {
			$p = FRUserCounters::getUserParams( $userId, IDBAccessObject::READ_EXCLUSIVE );
			$changed = FRUserCounters::updateUserParams( $p, $wikiPage->getTitle(), $summary );
			if ( $changed ) {
				FRUserCounters::saveUserParams( $userId, $p ); // save any updates
			}
		} );
	}

	/**
	 * @inheritDoc
	 * Check an autopromote condition that is defined by FlaggedRevs
	 *
	 * Note: some unobtrusive caching is used to avoid DB hits.
	 */
	public function onAutopromoteCondition( $cond, $params, $user, &$result ) {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		switch ( $cond ) {
			case APCOND_FR_EDITSUMMARYCOUNT:
				$p = FRUserCounters::getParams( $user );
				$result = ( $p && $p['editComments'] >= $params[0] );
				break;
			case APCOND_FR_NEVERBLOCKED:
				if ( $user->getBlock() ) {
					$result = false; // failed
				} else {
					// See T262970 for an explanation of this
					$hasPriorBlock = $cache->getWithSetCallback(
						$cache->makeKey( 'flaggedrevs-autopromote-notblocked', $user->getId() ),
						$cache::TTL_SECOND,
						function ( $oldValue, &$ttl, array &$setOpts, $oldAsOf ) use ( $user ) {
							// Once the user is blocked once, this condition will always
							// fail. To avoid running queries again, if the old cached value
							// is `priorBlock`, just return that immediately.
							if ( $oldValue === 'priorBlock' ) {
								return 'priorBlock';
							}

							// Since the user had no block prior to the last time
							// the value was cached, we only need to check for
							// blocks since then. If there was no prior cached
							// value, check for all time (since time 0).
							// The time of the last check is itself the cached
							// value.
							$startingTimestamp = is_int( $oldValue ) ? $oldValue : 0;

							// If the user still hasn't been blocked, we will
							// update the cached value to be the current timestamp
							$newTimestamp = time();

							$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

							$setOpts += Database::getCacheSetOptions( $dbr );

							$hasPriorBlock = self::wasPreviouslyBlocked(
								$user,
								$dbr,
								$startingTimestamp
							);
							if ( $hasPriorBlock ) {
								// Store 'priorBlock' so that we can
								// skip everything in the future
								return 'priorBlock';
							}

							// Store the current time, so that future
							// checks don't query everything
							return $newTimestamp;
						},
						[ 'staleTTL' => $cache::TTL_WEEK ]
					);
					$result = ( $hasPriorBlock !== 'priorBlock' );
				}
				break;
			case APCOND_FR_UNIQUEPAGECOUNT:
				$p = FRUserCounters::getParams( $user );
				$result = ( $p && count( $p['uniqueContentPages'] ) >= $params[0] );
				break;
			case APCOND_FR_EDITSPACING:
				$key = $cache->makeKey(
					'flaggedrevs-autopromote-editspacing',
					$user->getId(),
					$params[0],
					$params[1]
				);
				$val = $cache->get( $key );
				if ( $val === 'true' ) {
					$result = true; // passed
				} elseif ( $val === 'false' ) {
					$result = false; // failed
				} else {
					# Hit the DB only if the result is not cached...
					$pass = self::editSpacingCheck( $user, $params[0], $params[1] );
					# Make a key to store the results
					if ( $pass === true ) {
						$cache->set( $key, 'true', 2 * $cache::TTL_WEEK );
					} else {
						$cache->set( $key, 'false', $pass /* wait time */ );
					}
					$result = ( $pass === true );
				}
				break;
			case APCOND_FR_EDITCOUNT:
				# $maxNew is the *most* edits that can be too recent
				$maxNew = $user->getEditCount() - $params[0];
				if ( $maxNew < 0 ) {
					$result = false; // doesn't meet count even *with* recent edits
				} elseif ( $params[1] <= 0 ) {
					$result = true; // passed; we aren't excluding any recent edits
				} else {
					# Check all recent edits...
					$n = self::recentEditCount( $user->getId(), $params[1], $maxNew );
					$result = ( $n <= $maxNew );
				}
				break;
			case APCOND_FR_CONTENTEDITCOUNT:
				$p = FRUserCounters::getParams( $user );
				if ( !$p ) {
					$result = false;
				} else {
					# $maxNew is the *most* edits that can be too recent
					$maxNew = $p['totalContentEdits'] - $params[0];
					if ( $maxNew < 0 ) {
						$result = false; // doesn't meet count even *with* recent edits
					} elseif ( $params[1] <= 0 ) {
						$result = true; // passed; we aren't excluding any recent edits
					} else {
						# Check all recent content edits...
						$n = self::recentContentEditCount( $user->getId(), $params[1], $maxNew );
						$result = ( $n <= $maxNew );
					}
				}
				break;
			case APCOND_FR_CHECKEDEDITCOUNT:
				$key = $cache->makeKey(
					'flaggedrevs-autopromote-reviewededits',
					$user->getId(),
					$params[0],
					$params[1]
				);
				$val = $cache->get( $key );
				if ( $val === 'true' ) {
					$result = true; // passed
				} elseif ( $val === 'false' ) {
					$result = false; // failed
				} else {
					# Hit the DB only if the result is not cached...
					$result = self::reviewedEditsCheck( $user, $params[0], $params[1] );
					if ( $result ) {
						$cache->set( $key, 'true', $cache::TTL_WEEK );
					} else {
						$cache->set( $key, 'false', $cache::TTL_HOUR ); // briefly cache
					}
				}
				break;
			case APCOND_FR_USERPAGEBYTES:
				$result = ( !$params[0] || $user->getUserPage()->getLength() >= $params[0] );
				break;
			case APCOND_FR_MAXREVERTEDEDITRATIO:
				$p = FRUserCounters::getParams( $user );
				$result = ( $p && $params[0] * $user->getEditCount() >= $p['revertedEdits'] );
				break;
			case APCOND_FR_NEVERDEMOTED: // b/c
				$p = FRUserCounters::getParams( $user );
				$result = $p !== null && !( $p['demoted'] ?? false );
				break;
		}
	}

	/**
	 * @inheritDoc
	 * Set session key.
	 */
	public function onUserLoadAfterLoadFromSession( $user ) {
		global $wgRequest;
		if ( $user->isRegistered() && MediaWikiServices::getInstance()->getPermissionManager()
				->userHasRight( $user, 'review' )
		) {
			$key = $wgRequest->getSessionData( 'wsFlaggedRevsKey' );
			if ( $key === null ) { // should catch login
				$key = MWCryptRand::generateHex( 32 );
				// Temporary secret key attached to this session
				$wgRequest->setSessionData( 'wsFlaggedRevsKey', $key );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onWikiExporter__dumpStableQuery( &$tables, &$opts, &$join ) {
		$namespaces = FlaggedRevs::getReviewNamespaces();
		if ( $namespaces ) {
			$tables[] = 'flaggedpages';
			$opts['ORDER BY'] = 'fp_page_id ASC';
			$opts['USE INDEX'] = [ 'flaggedpages' => 'PRIMARY' ];
			$join['page'] = [ 'INNER JOIN',
				[ 'page_id = fp_page_id', 'page_namespace' => $namespaces ]
			];
			$join['revision'] = [ 'INNER JOIN',
				[ 'rev_page = fp_page_id', 'rev_id = fp_stable' ] ];
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GoogleNewsSitemap::Query
	 *
	 * @param array $params
	 * @param array &$joins
	 * @param array &$conditions
	 * @param array &$tables
	 */
	public static function gnsmQueryModifier(
		array $params, array &$joins, array &$conditions, array &$tables
	) {
		$filterSet = [ GoogleNewsSitemap::OPT_ONLY => true,
			GoogleNewsSitemap::OPT_EXCLUDE => true
		];
		# Either involves the same JOIN here...
		if ( isset( $filterSet[ $params['stable'] ] ) || isset( $filterSet[ $params['quality'] ] ) ) {
			$tables[] = 'flaggedpages';
			$joins['flaggedpages'] = [ 'LEFT JOIN', 'page_id = fp_page_id' ];
		}

		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
		switch ( $params['stable'] ) {
			case GoogleNewsSitemap::OPT_ONLY:
				$conditions[] = $dbr->expr( 'fp_stable', '!=', null );
				break;
			case GoogleNewsSitemap::OPT_EXCLUDE:
				$conditions['fp_stable'] = null;
				break;
		}

		switch ( $params['quality'] ) {
			case GoogleNewsSitemap::OPT_ONLY:
				$conditions[] = $dbr->expr( 'fp_quality', '>=', 1 );
				break;
			case GoogleNewsSitemap::OPT_EXCLUDE:
				$conditions[] = $dbr->expr( 'fp_quality', '=', 0 )->or( 'fp_quality', '=', null );
				break;
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EchoGetDefaultNotifiedUsers
	 *
	 * This should go once we can remove all Echo-specific code for reverts,
	 * see: T153570
	 * @param Event $event Event to get implicitly subscribed users for
	 * @param User[] &$users Array to append implicitly subscribed users to.
	 */
	public static function onEchoGetDefaultNotifiedUsers( $event, &$users ) {
		$extra = $event->getExtra();
		if ( $event->getType() == 'reverted' && $extra['method'] == 'flaggedrevs-reject' ) {
			foreach ( $extra['reverted-users-ids'] as $userId ) {
				$users[$userId] = User::newFromId( intval( $userId ) );
			}
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/UserMergeAccountFields
	 *
	 * @param array &$updateFields
	 */
	public static function onUserMergeAccountFields( array &$updateFields ) {
		$updateFields[] = [ 'flaggedrevs', 'fr_user' ];
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/MergeAccountFromTo
	 *
	 * @param User $oldUser
	 * @param User $newUser
	 */
	public static function onMergeAccountFromTo( User $oldUser, User $newUser ) {
		if ( $newUser->isRegistered() ) {
			FRUserCounters::mergeUserParams( $oldUser, $newUser );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/DeleteAccount
	 *
	 * @param User $oldUser
	 */
	public static function onDeleteAccount( User $oldUser ) {
		FRUserCounters::deleteUserParams( $oldUser );
	}

	/**
	 * @inheritDoc
	 * As the hook is called after saving the edit (in a deferred update), we have already
	 * figured out whether the edit should be autoreviewed or not (see: maybeMakeEditReviewed
	 * method). This hook just checks whether the edit is marked as reviewed or not.
	 */
	public function onBeforeRevertedTagUpdate(
		$wikiPage,
		$user,
		$summary,
		$flags,
		$revisionRecord,
		$editResult,
		&$approved
	): void {
		$title = $wikiPage->getTitle();
		$fPage = FlaggableWikiPage::getTitleInstance( $title );
		$fPage->loadPageData( IDBAccessObject::READ_LATEST );
		if ( !$fPage->isReviewable() ) {
			// The page is not reviewable
			return;
		}

		// Check if the revision was approved
		$flaggedRev = FlaggedRevision::newFromTitle(
			$wikiPage->getTitle(),
			$revisionRecord->getId(),
			IDBAccessObject::READ_LATEST
		);
		// FlaggedRevision object exists if and only if for each of the defined review tags,
		// the edit has at least a "minimum" review level.
		$approved = $flaggedRev !== null;
	}
}
