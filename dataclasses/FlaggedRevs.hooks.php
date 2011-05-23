<?php
/**
 * Class containing hooked functions for a FlaggedRevs environment
 */
class FlaggedRevsHooks {
	/**
	* Update flaggedrevs table on revision restore
	*/
	public static function onRevisionRestore( $title, Revision $revision, $oldPageID ) {
		$dbw = wfGetDB( DB_MASTER );
		# Some revisions may have had null rev_id values stored when deleted.
		# This hook is called after insertOn() however, in which case it is set
		# as a new one.
		$dbw->update( 'flaggedrevs',
			array( 'fr_page_id' => $revision->getPage() ),
			array( 'fr_page_id' => $oldPageID, 'fr_rev_id' => $revision->getID() ),
			__METHOD__
		);
		return true;
	}

	/**
	* Update flaggedrevs page/tracking tables (revision moving)
	*/
	public static function onArticleMergeComplete( Title $sourceTitle, Title $destTitle ) {
		$oldPageID = $sourceTitle->getArticleID();
		$newPageID = $destTitle->getArticleID();
		# Get flagged revisions from old page id that point to destination page
		$dbw = wfGetDB( DB_MASTER );
		$result = $dbw->select(
			array( 'flaggedrevs', 'revision' ),
			array( 'fr_rev_id' ),
			array( 'fr_page_id' => $oldPageID,
				'fr_rev_id = rev_id',
				'rev_page' => $newPageID ),
			__METHOD__
		);
		# Update these rows
		$revIDs = array();
		foreach( $result as $row ) {
			$revIDs[] = $row->fr_rev_id;
		}
		if ( !empty( $revIDs ) ) {
			$dbw->update( 'flaggedrevs',
				array( 'fr_page_id' => $newPageID ),
				array( 'fr_page_id' => $oldPageID, 'fr_rev_id' => $revIDs ),
				__METHOD__
			);
		}
		# Update pages...stable versions possibly lost to another page
		FlaggedRevs::stableVersionUpdates( $sourceTitle );
		FlaggedRevs::HTMLCacheUpdates( $sourceTitle );
		FlaggedRevs::stableVersionUpdates( $destTitle );
		FlaggedRevs::HTMLCacheUpdates( $destTitle );
		return true;
	}

	/**
	* (a) Update flaggedrevs page/tracking tables
	* (b) Autoreview pages moved into content NS
	*/
	public static function onTitleMoveComplete(
		Title $otitle, Title $ntitle, $user, $pageId
	) {
		$fa = FlaggedPage::getTitleInstance( $ntitle );
		$fa->loadFromDB( FR_MASTER );
		// Re-validate NS/config (new title may not be reviewable)
		if ( $fa->isReviewable() ) {
			// Moved from non-reviewable to reviewable NS?
			// Auto-review such edits like new pages...
			if ( !FlaggedRevs::inReviewNamespace( $otitle )
				&& FlaggedRevs::autoReviewNewPages()
				&& $ntitle->userCan( 'autoreview' ) )
			{
				$rev = Revision::newFromTitle( $ntitle );
				if ( $rev ) { // sanity
					FlaggedRevs::autoReviewEdit( $fa, $user, $rev );
				}
			}
		}
		# Update page and tracking tables and clear cache
		FlaggedRevs::stableVersionUpdates( $otitle );
		FlaggedRevs::HTMLCacheUpdates( $otitle );
		FlaggedRevs::stableVersionUpdates( $ntitle );
		FlaggedRevs::HTMLCacheUpdates( $ntitle );
		return true;
	}

	/**
	* (a) Update flaggedrevs page/tracking tables
	* (b) Pages with stable versions that use this page will be purged
	* Note: pages with current versions that use this page should already be purged
	*/
	public static function onArticleEditUpdates( Article $article ) {
		FlaggedRevs::stableVersionUpdates( $article->getTitle() );
		FlaggedRevs::extraHTMLCacheUpdate( $article->getTitle() );
		return true;
	}

	/**
	* (a) Update flaggedrevs page/tracking tables
	* (b) Pages with stable versions that use this page will be purged
	* Note: pages with current versions that use this page should already be purged
	*/
	public static function onArticleDelete( Article $article, $user, $reason, $id ) {
		FlaggedRevs::clearTrackingRows( $id );
		FlaggedRevs::extraHTMLCacheUpdate( $article->getTitle() );
		return true;
	}

	/**
	* (a) Update flaggedrevs page/tracking tables
	* (b) Pages with stable versions that use this page will be purged
	* Note: pages with current versions that use this page should already be purged
	*/
	public static function onArticleUndelete( Title $title ) {
		FlaggedRevs::stableVersionUpdates( $title );
		FlaggedRevs::HTMLCacheUpdates( $title );
		return true;
	}

	/**
	* (a) Update flaggedrevs page/tracking tables
	* (b) Pages with stable versions that use this page will be purged
	* Note: pages with current versions that use this page should already be purged
	*/
	public static function onFileUpload( File $file ) {
		FlaggedRevs::stableVersionUpdates( $file->getTitle() );
		FlaggedRevs::extraHTMLCacheUpdate( $file->getTitle() );
		return true;
	}

	/**
	* Update flaggedrevs page/tracking tables
	*/
	public static function onRevisionDelete( Title $title ) {
		$changed = FlaggedRevs::stableVersionUpdates( $title );
		if ( $changed ) {
			FlaggedRevs::HTMLCacheUpdates( $title );
		}
		return true;
	}

	/**
	* Select the desired templates based on the selected stable revision IDs
	* Note: $parser can be false
	*/
	public static function parserFetchStableTemplate( $parser, Title $title, &$skip, &$id ) {
		if ( !( $parser instanceof Parser ) || $title->getNamespace() < 0 ) {
			return true; // nothing to do
		}
		$incManager = FRInclusionManager::singleton();
		if ( !$incManager->parserOutputIsStabilized() ) {
			return true; // trigger for stable version parsing only
		}
		$id = false; // current version
		# Check for the version of this template used when reviewed...
		$maybeId = $incManager->getReviewedTemplateVersion( $title );
		if ( $maybeId !== null ) {
			$id = (int)$maybeId; // use if specified (even 0)
		}
		# Check for stable version of template if this feature is enabled...
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
			$maybeId = $incManager->getStableTemplateVersion( $title );
			# Take the newest of these two...
			if ( $maybeId && $maybeId > $id ) {
				$id = (int)$maybeId;
			}
		}
		# If $id is zero, don't bother loading it (use blue/red link)
		if ( $id === 0 ) {
			$skip = true;
		}
		return true;
	}

	/**
	* Select the desired images based on the selected stable version time/SHA-1
	*/
	public static function parserFetchStableFile( $parser, Title $title, &$time, &$sha1, &$query ) {
		if ( !( $parser instanceof Parser ) ) {
			return true; // nothing to do
		}
		$incManager = FRInclusionManager::singleton();
		if ( !$incManager->parserOutputIsStabilized() ) {
			return true; // trigger for stable version parsing only
		}
		# Normalize NS_MEDIA to NS_FILE
		if ( $title->getNamespace() == NS_MEDIA ) {
			$title = Title::makeTitle( NS_FILE, $title->getDBkey() );
			$title->resetArticleId( $title->getArticleId() ); // avoid extra queries
		} else {
			$title =& $title;
		}
		$time = $sha1 = false; // current version
		# Check for the version of this file used when reviewed...
		list( $maybeTS, $maybeSha1 ) = $incManager->getReviewedFileVersion( $title );
		if ( $maybeTS !== null ) {
			$time = $maybeTS; // use if specified (even '0')
			$sha1 = $maybeSha1;
		}
		# Check for stable version of file if this feature is enabled...
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
			list( $maybeTS, $maybeSha1 ) = $incManager->getStableFileVersion( $title );
			# Take the newest of these two...
			if ( $maybeTS && $maybeTS > $time ) {
				$time = $maybeTS;
				$sha1 = $maybeSha1;
			}
		}
		# Stabilize the file link
		if ( $time ) {
			if ( $query != '' ) $query .= '&';
			$query = "filetimestamp=" . urlencode( wfTimestamp( TS_MW, $time ) );
		}
		return true;
	}

	public static function onParserFirstCallInit( &$parser ) {
		$parser->setFunctionHook( 'pagesusingpendingchanges',
			'FlaggedRevsHooks::parserPagesUsingPendingChanges' );
		return true;
	}

	public static function onLanguageGetMagic( &$magicWords, $langCode ) {
		$magicWords['pagesusingpendingchanges'] = array( 0, 'pagesusingpendingchanges' );
		$magicWords['pendingchangelevel'] = array( 0, 'pendingchangelevel' );
		return true;
	}

	public static function onParserGetVariableValueSwitch( &$parser, &$cache, &$word, &$ret ) {
		if ( $word == 'pendingchangelevel' ) {
			$title = $parser->getTitle();
			if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
				$ret = '';
			} else {
				$config = FlaggedPageConfig::getStabilitySettings( $title );
				$ret = $config['autoreview'];
			}
		}
		return true;
	}

	public static function onMagicWordwgVariableIDs( &$words ) {
		$words[] = 'pendingchangelevel';
		return true;
	}

	public static function parserPagesUsingPendingChanges( &$parser, $ns = '' ) {
		$nsList = FlaggedRevs::getReviewNamespaces();
		if ( !$nsList ) {
			return 0;
		}

		if ( $ns !== '' ) {
			$ns = intval( $ns );
			if ( !in_array( $ns, $nsList ) ) {
				return 0;
			}
		}

		static $pcCounts = null;
		if ( !$pcCounts ) {
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select( 'flaggedrevs_stats', '*', array(), __METHOD__ );
			$totalCount = 0;
			foreach( $res as $row ) {
				$nsList[ "ns-{$row->namespace}" ] = $row->reviewed;
				$totalCount += $row->reviewed;
			}
			$nsList[ 'all' ] = $totalCount;
		}

		if ( $ns === '' ) {
			return $nsList['all'];
		} else {
			return $nsList[ "ns-$ns" ];
		}
	}

	/**
	* Check page move and patrol permissions for FlaggedRevs
	*/
	public static function onUserCan( Title $title, $user, $action, &$result ) {
		if ( $result === false ) {
			return true; // nothing to do
		}
		# Don't let users vandalize pages by moving them...
		if ( $action === 'move' ) {
			if ( !FlaggedRevs::inReviewNamespace( $title ) || !$title->exists() ) {
				return true; // extra short-circuit
			}
			$flaggedArticle = FlaggedPage::getTitleInstance( $title );
			# If the draft shows by default anyway, nothing to do...
			if ( !$flaggedArticle->isStableShownByDefault() ) {
				return true;
			}
			$frev = $flaggedArticle->getStableRev();
			if ( $frev && !$user->isAllowed( 'review' ) && !$user->isAllowed( 'movestable' ) ) {
				# Allow for only editors/reviewers to move this page
				$result = false;
				return false;
			}
		# Don't let users patrol pages not in $wgFlaggedRevsPatrolNamespaces
		} else if ( $action === 'patrol' || $action === 'autopatrol' ) {
			$flaggedArticle = FlaggedPage::getTitleInstance( $title );
			# For a page to be patrollable it must not be reviewable.
			# Note: normally, edits to non-reviewable, non-patrollable, pages are
			# silently marked patrolled automatically. With $wgUseNPPatrol on, the
			# first edit to those pages is left as being unpatrolled.
			if ( $flaggedArticle->isReviewable() ) {
				$result = false;
				return false;
			}
		# Enforce autoreview/review restrictions
		} else if ( $action === 'autoreview' || $action === 'review' ) {
			# Get autoreview restriction settings...
			$fa = FlaggedPage::getTitleInstance( $title );
			$config = $fa->getStabilitySettings();
			# Convert Sysop -> protect
			$right = ( $config['autoreview'] === 'sysop' ) ?
				'protect' : $config['autoreview'];
			# Check if the user has the required right, if any
			if ( $right != '' && !$user->isAllowed( $right ) ) {
				$result = false;
				return false;
			}
		}
		return true;
	}

	/**
	* When an edit is made by a user, review it if either:
	* (a) The user can 'autoreview' and the edit's base revision is a checked
	* (b) The edit is a self-revert to the stable version (by anyone)
	* (c) The user can 'autoreview' new pages and this edit is a new page
	* (d) The user can 'review' and the "review pending edits" checkbox was checked
	*
	* Note: RC items not inserted yet, RecentChange_save hook does rc_patrolled bit...
	* Note: $article one of Article, ImagePage, Category page as appropriate.
	*/
	public static function maybeMakeEditReviewed(
		Article $article, $rev, $baseRevId = false, $user = null
	) {
		global $wgRequest;
		# Edit must be non-null, to a reviewable page, with $user set
		$fa = FlaggedPage::getArticleInstance( $article );
		$fa->loadFromDB( FR_MASTER );
		if ( !$rev || !$user || !$fa->isReviewable() ) {
			return true;
		}
		$title = $article->getTitle(); // convenience
		$title->resetArticleID( $rev->getPage() ); // Avoid extra DB hit and lag issues
		# Get what was just the current revision ID
		$prevRevId = $rev->getParentId();
		# Get edit timestamp. Existance already validated by EditPage.php.
		$editTimestamp = $wgRequest->getVal( 'wpEdittime' );
		# Is the page manually checked off to be reviewed?
		if ( $editTimestamp
			&& $wgRequest->getCheck( 'wpReviewEdit' )
			&& $title->getUserPermissionsErrors( 'review', $user ) === array() )
		{
			if ( self::editCheckReview( $article, $rev, $user, $editTimestamp ) ) {
				return true; // reviewed...done!
			}
		}
		# All cases below require auto-review of edits to be enabled
		if ( !FlaggedRevs::autoReviewEnabled() ) {
			return true; // short-circuit
		}
		# If a $baseRevId is passed in, the edit is using an old revision's text
		$isOldRevCopy = (bool)$baseRevId; // null edit or rollback
		# Get the revision ID the incoming one was based off...
		if ( !$baseRevId && $prevRevId ) {
			$prevTimestamp = Revision::getTimestampFromId( $title, $prevRevId );
			# The user just made an edit. The one before that should have
			# been the current version. If not reflected in wpEdittime, an
			# edit may have been auto-merged in between, in that case, discard
			# the baseRevId given from the client.
			if ( !$editTimestamp || $prevTimestamp == $editTimestamp ) {
				$baseRevId = intval( trim( $wgRequest->getVal( 'baseRevId' ) ) );
			}
			# If baseRevId not given, assume the previous revision ID (for bots).
			# For auto-merges, this also occurs since the given ID is ignored.
			if ( !$baseRevId ) {
				$baseRevId = $prevRevId;
			}
		}
		$frev = null; // flagged rev this edit was based on
		$flags = null; // review flags (null => default flags)
		# Case A: this user can auto-review edits. Check if either:
		# (a) this new revision creates a new page and new page autoreview is enabled
		# (b) this new revision is based on an old, reviewed, revision
		if ( $title->getUserPermissionsErrors( 'autoreview', $user ) === array() ) {
			// New pages
			if ( !$prevRevId ) {
				$reviewableNewPage = FlaggedRevs::autoReviewNewPages();
				$reviewableChange = false;
			// Edits to existing pages
			} elseif ( $baseRevId ) {
				$reviewableNewPage = false; // had previous rev
				# Check if the base revision was reviewed...
				if ( FlaggedRevs::autoReviewEdits() ) {
					$frev = FlaggedRevision::newFromTitle( $title, $baseRevId, FR_MASTER );
				}
				$reviewableChange = (bool)$frev;
			}
			// Is this an edit directly to a reviewed version or a new page?
			if ( $reviewableNewPage || $reviewableChange ) {
				if ( $isOldRevCopy && $frev ) {
					$flags = $frev->getTags(); // null edits & rollbacks keep previous tags
				}
				# Review this revision of the page...
				FlaggedRevs::autoReviewEdit( $article, $user, $rev, $flags );
			}
		# Case B: the user cannot autoreview edits. Check if either:
		# (a) this is a rollback to the stable version
		# (b) this is a self-reversion to the stable version
		# These are subcases of making a new revision based on an old, reviewed, revision.
		} elseif ( FlaggedRevs::autoReviewEdits() && $fa->getStableRev() ) {
			$srev = $fa->getStableRev();
			# Check for rollbacks...
			$reviewableChange = (
				$isOldRevCopy && // rollback or null edit
				$baseRevId != $prevRevId && // not a null edit
				$baseRevId == $srev->getRevId() && // restored stable rev
				$title->getUserPermissionsErrors( 'autoreviewrestore', $user ) === array()
			);
			# Check for self-reversions...
			if ( !$reviewableChange ) {
				$reviewableChange = self::isSelfRevertToStable( $rev, $srev, $baseRevId, $user );
			}
			// Is this a rollback or self-reversion to the stable rev?
			if ( $reviewableChange ) {
				$flags = $srev->getTags(); // use old tags
				# Review this revision of the page...
				FlaggedRevs::autoReviewEdit( $article, $user, $rev, $flags );
			}
		}
		return true;
	}

	// Review $rev if $editTimestamp matches the previous revision's timestamp.
	// Otherwise, review the revision that has $editTimestamp as its timestamp value.
	protected static function editCheckReview(
		Article $article, $rev, $user, $editTimestamp
	) {
		$prevTimestamp = $flags = null;
		$prevRevId = $rev->getParentId(); // revision before $rev
		$title = $article->getTitle(); // convenience
		# Check wpEdittime against the former current rev for verification
		if ( $prevRevId ) {
			$prevTimestamp = Revision::getTimestampFromId( $title, $prevRevId );
		}
		# Was $rev is an edit to an existing page?
		if ( $prevTimestamp ) {
			# Check wpEdittime against the former current revision's time.
			# If an edit was auto-merged in between, then the new revision
			# has content different than what the user expected. However, if
			# the auto-merged edit was reviewed, then assume that it's OK.
			if ( $editTimestamp != $prevTimestamp
				&& !FlaggedRevision::revIsFlagged( $title, $prevRevId, FR_MASTER )
			) {
				return false; // not flagged?
			}
		}
		# Review this revision of the page...
		return FlaggedRevs::autoReviewEdit(
			$article, $user, $rev, $flags, false  /* manual */ );
	}

	/**
	* Check if a user reverted himself to the stable version
	*/
	protected static function isSelfRevertToStable(
		Revision $rev, $srev, $baseRevId, $user
	) {
		if ( !$srev || $baseRevId != $srev->getRevId() ) {
			return false; // user reports they are not the same
		}
		$dbw = wfGetDB( DB_MASTER );
		# Such a revert requires 1+ revs between it and the stable
		$revertedRevs = $dbw->selectField( 'revision', '1',
			array(
				'rev_page' => $rev->getPage(),
				'rev_id > ' . intval( $baseRevId ), // stable rev
				'rev_id < ' . intval( $rev->getId() ), // this rev
				'rev_user_text' => $user->getName()
			), __METHOD__
		);
		if ( !$revertedRevs ) {
			return false; // can't be a revert
		}
		# Check that this user is ONLY reverting his/herself.
		$otherUsers = $dbw->selectField( 'revision', '1',
			array(
				'rev_page' => $rev->getPage(),
				'rev_id > ' . intval( $baseRevId ),
				'rev_user_text != ' . $dbw->addQuotes( $user->getName() )
			), __METHOD__
		);
		if ( $otherUsers ) {
			return false; // only looking for self-reverts
		}
		# Confirm the text because we can't trust this user.
		return ( $rev->getText() == $srev->getRevText() );
	}

	/**
	* When an user makes a null-edit we sometimes want to review it...
	* (a) Null undo or rollback
	* (b) Null edit with review box checked
	* Note: called after edit ops are finished
	*/
	public static function maybeNullEditReview(
		Article $article, $user, $text, $s, $m, $a, $b, $flags, $rev, &$status, $baseId
	) {
		global $wgRequest;
		# Revision must *be* null (null edit). We also need the user who made the edit.
		if ( !$user || $rev !== null ) {
			return true;
		}
		$fa = FlaggedPage::getArticleInstance( $article );
		$fa->loadFromDB( FR_MASTER );
		if ( !$fa->isReviewable() ) {
			return true; // page is not reviewable
		}
		$title = $article->getTitle(); // convenience
		# Get the current revision ID
		$rev = Revision::newFromTitle( $title );
		if ( !$rev ) {
			return true; // wtf?
		}
		$flags = null;
		# Is this a rollback/undo that didn't change anything?
		if ( $baseId > 0 ) {
			$frev = FlaggedRevision::newFromTitle( $title, $baseId );
			# Was the edit that we tried to revert to reviewed?
			if ( $frev ) {
				# Review this revision of the page...
				$ok = FlaggedRevs::autoReviewEdit( $article, $user, $rev, $flags );
				if ( $ok ) {
					FlaggedRevs::markRevisionPatrolled( $rev ); // reviewed -> patrolled
					FlaggedRevs::extraHTMLCacheUpdate( $title );
					return true;
				}
			}
		}
		# Get edit timestamp, it must exist.
		$editTimestamp = $wgRequest->getVal( 'wpEdittime' );
		# Is the page checked off to be reviewed?
		if ( $editTimestamp
			&& $wgRequest->getCheck( 'wpReviewEdit' )
			&& $title->userCan( 'review' ) )
		{
			# Check wpEdittime against current revision's time.
			# If an edit was auto-merged in between, review only up to what
			# was the current rev when this user started editing the page.
			if ( $rev->getTimestamp() != $editTimestamp ) {
				$dbw = wfGetDB( DB_MASTER );
				$rev = Revision::loadFromTimestamp( $dbw, $title, $editTimestamp );
				if ( !$rev ) {
					return true; // deleted?
				}
			}
			# Review this revision of the page...
			$ok = FlaggedRevs::autoReviewEdit( $article, $user, $rev, $flags, false );
			if ( $ok ) {
				FlaggedRevs::markRevisionPatrolled( $rev ); // reviewed -> patrolled
				FlaggedRevs::extraHTMLCacheUpdate( $title );
			}
		}
		return true;
	}

	/**
	* When an edit is made to a page:
	* (a) If the page is reviewable, silently mark the edit patrolled if it was auto-reviewed
	* (b) If the page can be patrolled, auto-patrol the edit patrolled as normal
	* (c) If the page is new and $wgUseNPPatrol is on, auto-patrol the edit patrolled as normal
	* (d) If the edit is neither reviewable nor patrolleable, silently mark it patrolled
	*/
	public static function autoMarkPatrolled( RecentChange &$rc ) {
		global $wgUser;
		if ( empty( $rc->mAttribs['rc_this_oldid'] ) ) {
			return true;
		}
		$fa = FlaggedPage::getTitleInstance( $rc->getTitle() );
		$fa->loadFromDB( FR_MASTER );
		// Is the page reviewable?
		if ( $fa->isReviewable() ) {
			$revId = $rc->mAttribs['rc_this_oldid'];
			$quality = FlaggedRevision::getRevQuality(
				$rc->mAttribs['rc_cur_id'], $revId, FR_MASTER );
			// Reviewed => patrolled
			if ( $quality !== false && $quality >= FR_CHECKED ) {
				RevisionReviewForm::updateRecentChanges( $rc->getTitle(), $revId );
				$rc->mAttribs['rc_patrolled'] = 1; // make sure irc/email notifs know status
			}
			return true;
		}
		global $wgFlaggedRevsRCCrap;
		if ( $wgFlaggedRevsRCCrap ) {
			// Is this page in patrollable namespace?
			if ( FlaggedRevs::inPatrolNamespace( $rc->getTitle() ) ) {
				# Bots and users with 'autopatrol' have edits to patrollable
				# pages marked automatically on edit.
				$patrol = $wgUser->isAllowed( 'autopatrol' ) || $wgUser->isAllowed( 'bot' );
				$record = true; // record if patrolled
			} else {
				global $wgUseNPPatrol;
				// Is this is a new page edit and $wgUseNPPatrol is enabled?
				if ( $wgUseNPPatrol && !empty( $rc->mAttribs['rc_new'] ) ) {
					# Automatically mark it patrolled if the user can do so
					$patrol = $wgUser->isAllowed( 'autopatrol' );
					$record = true;
				// Otherwise, this edit is not patrollable
				} else {
					# Silently mark it "patrolled" so that it doesn't show up as being unpatrolled
					$patrol = true;
					$record = false;
				}
			}
			// Set rc_patrolled flag and add log entry as needed
			if ( $patrol ) {
				$rc->reallyMarkPatrolled();
				$rc->mAttribs['rc_patrolled'] = 1; // make sure irc/email notifs now status
				if ( $record ) {
					PatrolLog::record( $rc->mAttribs['rc_id'], true );
				}
			}
		}
		return true;
	}

	public static function incrementRollbacks(
		Article $article, $user, $goodRev, Revision $badRev
	) {
		# Mark when a user reverts another user, but not self-reverts
		$badUserId = $badRev->getRawUser();
		if ( $badUserId && $user->getId() != $badUserId ) {
			$p = FRUserCounters::getUserParams( $badUserId, FR_FOR_UPDATE );
			if ( !isset( $p['revertedEdits'] ) ) {
				$p['revertedEdits'] = 0;
			}
			$p['revertedEdits']++;
			FRUserCounters::saveUserParams( $badUserId, $p );
		}
		return true;
	}

	public static function incrementReverts(
		Article $article, $rev, $baseRevId = false, $user = null
	) {
		global $wgRequest;
		# Was this an edit by an auto-sighter that undid another edit?
		$undid = $wgRequest->getInt( 'undidRev' );
		if ( $rev && $undid && $user->isAllowed( 'autoreview' ) ) {
			// Note: $rev->getTitle() might be undefined (no rev id?)
			$badRev = Revision::newFromTitle( $article->getTitle(), $undid );
			if ( $badRev && $badRev->getRawUser() // by logged-in user
				&& $badRev->getRawUser() != $rev->getRawUser() ) // no self-reverts
			{
				FRUserCounters::incCount( $badRev->getRawUser(), 'revertedEdits' );
			}
		}
		return true;
	}

	/*
	 * Check if a user meets the edit spacing requirements.
	 * If the user does not, return a *lower bound* number of seconds
	 * that must elapse for it to be possible for the user to meet them.
	 * @param int $spacingReq days apart (of edit points)
	 * @param int $pointsReq number of edit points
	 * @param User $user
	 * @return mixed (true if passed, int seconds on failure)
	 */
	protected static function editSpacingCheck( $spacingReq, $pointsReq, $user ) {
		$benchmarks = 0; // actual edit points
		# Convert days to seconds...
		$spacingReq = $spacingReq * 24 * 3600;
		# Check the oldest edit
		$dbr = wfGetDB( DB_SLAVE );
		$lower = $dbr->selectField( 'revision', 'rev_timestamp',
			array( 'rev_user' => $user->getId() ),
			__METHOD__,
			array( 'ORDER BY' => 'rev_timestamp ASC', 'USE INDEX' => 'user_timestamp' )
		);
		# Recursively check for an edit $spacingReq seconds later, until we are done.
		if ( $lower ) {
			$benchmarks++; // the first edit above counts
			while ( $lower && $benchmarks < $pointsReq ) {
				$next = wfTimestamp( TS_UNIX, $lower ) + $spacingReq;
				$lower = $dbr->selectField( 'revision', 'rev_timestamp',
					array( 'rev_user' => $user->getId(),
						'rev_timestamp > ' . $dbr->addQuotes( $dbr->timestamp( $next ) ) ),
						__METHOD__,
					array( 'ORDER BY' => 'rev_timestamp ASC', 'USE INDEX' => 'user_timestamp' )
				);
				if ( $lower !== false ) $benchmarks++;
			}
		}
		if ( $benchmarks >= $pointsReq ) {
			return true;
		} else {
			// Does not add time for the last required edit point; it could be a
			// fraction of $spacingReq depending on the last actual edit point time.
			return ( $spacingReq * ($pointsReq - $benchmarks - 1) );
		}
	}

	/**
	* Check if a user has enough implicitly reviewed edits (before stable version)
	* @param $user User
	* @param $editsReq int
	* @param $cutoff_unixtime int exclude edits after this timestamp
	* @return bool
	*/
	protected static function reviewedEditsCheck( $user, $editsReq, $cutoff_unixtime = 0 ) {
		$dbr = wfGetDB( DB_SLAVE );
		$encCutoff = $dbr->addQuotes( $dbr->timestamp( $cutoff_unixtime ) );
		$res = $dbr->select( array( 'revision', 'flaggedpages' ), '1',
			array( 'rev_user' => $user->getId(),
				"rev_timestamp < $encCutoff",
				'fp_page_id = rev_page',
				'fp_pending_since IS NULL OR fp_pending_since > rev_timestamp' // bug 15515
			),
			__METHOD__,
			array( 'USE INDEX' => array( 'revision' => 'user_timestamp' ), 'LIMIT' => $editsReq )
		);
		return ( $dbr->numRows( $res ) >= $editsReq );
	}

	/**
	* Checks if $user was previously blocked
	*/
	public static function wasPreviouslyBlocked( $user ) {
		$dbr = wfGetDB( DB_SLAVE );
		return (bool)$dbr->selectField( 'logging', '1',
			array(
				'log_namespace' => NS_USER,
				'log_title'     => $user->getUserPage()->getDBkey(),
				'log_type'      => 'block',
				'log_action'    => 'block' ),
			__METHOD__,
			array( 'USE INDEX' => 'page_time' )
		);
	}

	/**
	* Grant 'autoreview' rights to users with the 'bot' right
	*/
	public static function onUserGetRights( $user, array &$rights ) {
		# Make sure bots always have the 'autoreview' right
		if ( in_array( 'bot', $rights ) && !in_array( 'autoreview', $rights ) ) {
			$rights[] = 'autoreview';
		}
		return true;
	}

	/**
	* Callback that autopromotes user according to the setting in
	* $wgFlaggedRevsAutopromote. This also handles user stats tallies.
	*/
	public static function onArticleSaveComplete(
		Article $article, $user, $text, $summary, $m, $a, $b, &$f, $rev
	) {
		global $wgFlaggedRevsAutopromote, $wgFlaggedRevsAutoconfirm;
		# Ignore NULL edits or edits by anon users
		if ( !$rev || !$user->getId() ) {
			return true;
		# No sense in running counters if nothing uses them
		} elseif ( !$wgFlaggedRevsAutopromote && !$wgFlaggedRevsAutoconfirm ) {
			return true;
		}
		$p = FRUserCounters::getUserParams( $user->getId(), FR_FOR_UPDATE );
		$changed = FRUserCounters::updateUserParams( $p, $article, $summary );
		if ( $changed ) {
			FRUserCounters::saveUserParams( $user->getId(), $p ); // save any updates
		}
		if ( is_array( $wgFlaggedRevsAutopromote ) ) {
			self::maybeMakeEditor( $user, $p, $wgFlaggedRevsAutopromote );
		}
		return true;
	}

	/**
	* Check an autopromote condition that is defined by FlaggedRevs
	*
	* Note: some unobtrusive caching is used to avoid DB hits.
	*/
	public static function checkAutoPromoteCond( $cond, array $params, User $user, &$result ) {
		global $wgMemc;
		switch( $cond ) {
			case APCOND_FR_EDITSUMMARYCOUNT:
				$p = FRUserCounters::getParams( $user );
				$result = ( is_array( $p ) && $p['editComments'] >= $params[0] );
				break;
			case APCOND_FR_NEVERBOCKED:
				$key = wfMemcKey( 'flaggedrevs', 'autopromote-blocked-ok', $user->getId() );
				$val = $wgMemc->get( $key );
				if ( $val === 'true' ) {
					$result = true; // passed
				} elseif ( $val === 'false' ) {
					$result = false; // failed
				} else {
					# Hit the DB only if the result is not cached...
					$result = !self::wasPreviouslyBlocked( $user );
					$wgMemc->set( $key, $result ? 'true' : 'false', 3600 * 24 * 7 ); // cache results
				}
				break;
			case APCOND_FR_UNIQUEPAGECOUNT:
				$p = FRUserCounters::getParams( $user );
				$result = ( is_array( $p ) && $p['uniqueContentPages'] >= $params[0] );
				break;
			case APCOND_FR_EDITSPACING:
				$key = wfMemcKey( 'flaggedrevs', 'autopromote-spacing-ok', $user->getId() );
				$val = $wgMemc->get( $key );
				if ( $val === 'true' ) {
					$result = true; // passed
				} elseif ( $val === 'false' ) {
					$result = false; // failed
				} else {
					# Hit the DB only if the result is not cached...
					$pass = self::editSpacingCheck( $params[0], $params[1], $user );
					# Make a key to store the results
					if ( $pass === true ) {
						$wgMemc->set( $key, 'true', 14 * 24 * 3600 );
					} else {
						$wgMemc->set( $key, 'false', $pass /* wait time */ );
					}
				}
				break;
			case APCOND_FR_CONTENTEDITCOUNT:
				$p = FRUserCounters::getParams( $user );
				$result = ( is_array( $p ) && $p['totalContentEdits'] >= $params[0] );
				break;
			case APCOND_FR_CHECKEDEDITCOUNT:
				$result = self::reviewedEditsCheck( $user, $params[0] );
				break;
		}
		return true;
	}

	/**
	* Autopromotes user according to the setting in $wgFlaggedRevsAutopromote.
	* @param $user User
	* @param $p array user tallies
	* @param $conds array $wgFlaggedRevsAutopromote
	*/
	protected static function maybeMakeEditor( User $user, array $p, array $conds ) {
		global $wgMemc, $wgContentNamespaces;
		$groups = $user->getGroups(); // current groups
		$regTime = wfTimestampOrNull( TS_UNIX, $user->getRegistration() );
		if (
			!$user->getId() ||
			# Do not give this to current holders
			in_array( 'editor', $groups ) ||
			# Do not give this right to bots
			$user->isAllowed( 'bot' ) ||
			# Do not re-add status if it was previously removed!
			( isset( $p['demoted'] ) && $p['demoted'] ) ||
			# Check if user edited enough unique pages
			$conds['uniqueContentPages'] > count( $p['uniqueContentPages'] ) ||
			# Check edit summary usage
			$conds['editComments'] > $p['editComments'] ||
			# Check reverted edits
			$conds['maxRevertedEditRatio']*$user->getEditCount() < $p['revertedEdits'] ||
			# Check user edit count
			$conds['edits'] > $user->getEditCount() ||
			# Check account age
			( $regTime && $conds['days'] > ( ( time() - $regTime ) / 86400 ) ) ||
			# See if the page actually has sufficient content...
			$conds['userpageBytes'] > $user->getUserPage()->getLength() ||
			# Don't grant to currently blocked users...
			$user->isBlocked()
		) {
			return true; // not ready
		}
		# User needs to meet 'totalContentEdits' OR 'totalCheckedEdits'
		$failedContentEdits = ( $conds['totalContentEdits'] > $p['totalContentEdits'] );

		# More expensive checks below...
		# Check if results are cached to avoid DB queries
		$APSkipKey = wfMemcKey( 'flaggedrevs', 'autopromote-skip', $user->getId() );
		if ( $wgMemc->get( $APSkipKey ) === 'true' ) {
			return true;
		}
		# Check if user was ever blocked before
		if ( $conds['neverBlocked'] && self::wasPreviouslyBlocked( $user ) ) {
			$wgMemc->set( $APSkipKey, 'true', 3600 * 24 * 7 ); // cache results
			return true;
		}
		$dbr = wfGetDB( DB_SLAVE );
		$cutoff_ts = 0;
		# Check to see if the user has enough non-"last minute" edits.
		if ( $conds['excludeLastDays'] > 0 ) {
			$minDiffAll = $user->getEditCount() - $conds['edits'] + 1;
			# Get cutoff timestamp
			$cutoff_ts = time() - 86400*$conds['excludeLastDays'];
			$encCutoff = $dbr->addQuotes( $dbr->timestamp( $cutoff_ts ) );
			# Check all recent edits...
			$res = $dbr->select( 'revision', '1',
				array( 'rev_user' => $user->getId(), "rev_timestamp > $encCutoff" ),
				__METHOD__,
				array( 'LIMIT' => $minDiffAll )
			);
			if ( $dbr->numRows( $res ) >= $minDiffAll ) {
				return true; // delay promotion
			}
			# Check recent content edits...
			if ( !$failedContentEdits && $wgContentNamespaces ) {
				$minDiffContent = $p['totalContentEdits'] - $conds['totalContentEdits'] + 1;
				$res = $dbr->select( array( 'revision', 'page' ), '1',
					array( 'rev_user' => $user->getId(),
						"rev_timestamp > $encCutoff",
						'rev_page = page_id',
						'page_namespace' => $wgContentNamespaces ),
					__METHOD__,
					array( 'USE INDEX' => array( 'revision' => 'user_timestamp' ),
						'LIMIT' => $minDiffContent )
				);
				if ( $dbr->numRows( $res ) >= $minDiffContent ) {
					$failedContentEdits = true; // totalCheckedEdits needed
				}
			}
		}
		# Check for edit spacing. This lets us know that the account has
		# been used over N different days, rather than all in one lump.
		if ( $conds['spacing'] > 0 && $conds['benchmarks'] > 1 ) {
			$pass = self::editSpacingCheck( $conds['spacing'], $conds['benchmarks'], $user );
			if ( $pass !== true ) {
				$wgMemc->set( $APSkipKey, 'true', $pass /* wait time */ ); // cache results
				return true;
			}
		}
		# Check if there are enough implicitly reviewed edits
		if ( $failedContentEdits && $conds['totalCheckedEdits'] > 0 ) {
			if ( !self::reviewedEditsCheck( $user, $conds['totalCheckedEdits'], $cutoff_ts ) ) {
				return true;
			}
		}

		# Add editor rights...
		$newGroups = $groups;
		array_push( $newGroups, 'editor' );
		$log = new LogPage( 'rights', false /* $rc */ );
		$log->addEntry( 'rights',
			$user->getUserPage(),
			wfMsgForContent( 'rights-editor-autosum' ),
			array( implode( ', ', $groups ), implode( ', ', $newGroups ) )
		);
		$user->addGroup( 'editor' );

		return true;
	}

	/**
	* Record demotion so that auto-promote will be disabled
	*/
	public static function recordDemote( $user, array $addgroup, array $removegroup ) {
		if ( $removegroup && in_array( 'editor', $removegroup ) ) {
			$dbName = false; // this wiki
			// Cross-wiki rights changes...
			if ( $user instanceof UserRightsProxy ) {
				$dbName = $user->getDBName(); // use foreign DB of the user
			}
			$p = FRUserCounters::getUserParams( $user->getId(), FR_FOR_UPDATE, $dbName );
			$p['demoted'] = 1;
			FRUserCounters::saveUserParams( $user->getId(), $p, $dbName );
		}
		return true;
	}

	public static function stableDumpQuery( array &$tables, array &$opts, array &$join ) {
		$namespaces = FlaggedRevs::getReviewNamespaces();
		if ( $namespaces ) {
			$tables[] = 'flaggedpages';
			$opts['ORDER BY'] = 'fp_page_id ASC';
			$opts['USE INDEX'] = array( 'flaggedpages' => 'PRIMARY' );
			$join['page'] = array( 'INNER JOIN',
				array( 'page_id = fp_page_id', 'page_namespace' => $namespaces )
			);
			$join['revision'] = array( 'INNER JOIN',
				'rev_page = fp_page_id AND rev_id = fp_stable' );
		}
		return false; // final
	}

	public static function gnsmQueryModifier( array $params, array &$joins, array &$conditions, array &$tables ) {
		$filterSet = array( GoogleNewsSitemap::OPT_ONLY => true,
				GoogleNewsSitemap::OPT_EXCLUDE => true
		);
		# Either involves the same JOIN here...
		if ( isset( $filterSet[ $params['stable'] ] ) || isset( $filterSet[ $params['quality'] ] ) ) {
			$tables[] = 'flaggedpages';
			$joins['flaggedpages'] = array( 'LEFT JOIN', 'page_id = fp_page_id' );
		}

		switch( $params['stable'] ) {
			case GoogleNewsSitemap::OPT_ONLY:
				$conditions[] = 'fp_stable IS NOT NULL ';
				break;
			case GoogleNewsSitemap::OPT_EXCLUDE:
				$conditions['fp_stable'] = null;
				break;
		}

		switch( $params['quality'] ) {
			case GoogleNewsSitemap::OPT_ONLY:
				$conditions[] = 'fp_quality >= 1';
				break;
			case GoogleNewsSitemap::OPT_EXCLUDE:
				$conditions[] = 'fp_quality = 0 OR fp_quality IS NULL';
				break;
		}

		return true;
	}
}
