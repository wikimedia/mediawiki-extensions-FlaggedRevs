<?php
/**
 * Class containing hooked functions for a FlaggedRevs environment
 */
class FlaggedRevsHooks {
	/**
	 * Update flaggedrevs table on revision restore
	 * @param Title $title
	 * @param Revision $revision
	 * @param int $oldPageID
	 * @return true
	 */
	public static function onRevisionRestore( $title, Revision $revision, $oldPageID ) {
		$dbw = wfGetDB( DB_MASTER );
		# Some revisions may have had null rev_id values stored when deleted.
		# This hook is called after insertOn() however, in which case it is set
		# as a new one.
		$dbw->update( 'flaggedrevs',
			[ 'fr_page_id' => $revision->getPage() ],
			[ 'fr_page_id' => $oldPageID, 'fr_rev_id' => $revision->getID() ],
			__METHOD__
		);
		return true;
	}

	/**
	 * Update flaggedrevs page/tracking tables (revision moving)
	 * @param Title $sourceTitle
	 * @param Title $destTitle
	 * @return true
	 */
	public static function onArticleMergeComplete( Title $sourceTitle, Title $destTitle ) {
		$oldPageID = $sourceTitle->getArticleID();
		$newPageID = $destTitle->getArticleID();
		# Get flagged revisions from old page id that point to destination page
		$dbw = wfGetDB( DB_MASTER );
		$result = $dbw->select(
			[ 'flaggedrevs', 'revision' ],
			[ 'fr_rev_id' ],
			[ 'fr_page_id' => $oldPageID,
				'fr_rev_id = rev_id',
				'rev_page' => $newPageID ],
			__METHOD__
		);
		# Update these rows
		$revIDs = [];
		foreach ( $result as $row ) {
			$revIDs[] = $row->fr_rev_id;
		}
		if ( !empty( $revIDs ) ) {
			$dbw->update( 'flaggedrevs',
				[ 'fr_page_id' => $newPageID ],
				[ 'fr_page_id' => $oldPageID, 'fr_rev_id' => $revIDs ],
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
	 * (b) Autoreview pages moved into reviewable namespaces (bug 19379)
	 * @param Title $otitle
	 * @param Title $ntitle
	 * @param User $user
	 * @param int $pageId
	 * @param int $redirid
	 * @param string $reason
	 * @return true
	 */
	public static function onTitleMoveComplete(
		Title $otitle, Title $ntitle, $user, $pageId, $redirid, $reason
	) {
		if ( FlaggedRevs::inReviewNamespace( $ntitle ) ) {
			if ( !FlaggedRevs::inReviewNamespace( $otitle ) ) {
				if ( FlaggedRevs::autoReviewNewPages() ) {
					$fa = FlaggableWikiPage::getTitleInstance( $ntitle );
					$fa->loadPageData( 'fromdbmaster' );
					// Re-validate NS/config (new title may not be reviewable)
					if ( $fa->isReviewable() && $ntitle->userCan( 'autoreview' ) ) {
						// Auto-review such edits like new pages...
						$rev = Revision::newFromTitle( $ntitle, false, Revision::READ_LATEST );
						if ( $rev ) { // sanity
							FlaggedRevs::autoReviewEdit( $fa, $user, $rev );
						}
					}
				}
			} else {
				$fa = FlaggableWikiPage::getTitleInstance( $ntitle );
				$fa->loadPageData( 'fromdbmaster' );
				$config = $fa->getStabilitySettings();
				// Insert a stable log entry if page doesn't have default wiki settings
				if ( !FRPageConfig::configIsReset( $config ) ) {
					FlaggedRevsLog::updateStabilityLogOnMove( $ntitle, $otitle, $reason, $user );
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
	 *
	 * @param WikiPage $wikiPage
	 * @param array $editInfo
	 *
	 * @return true
	 */
	public static function onArticleEditUpdates( WikiPage $wikiPage, $editInfo ) {
		FlaggedRevs::stableVersionUpdates( $wikiPage->getTitle(), null, null, $editInfo );
		FlaggedRevs::extraHTMLCacheUpdate( $wikiPage->getTitle() );
		return true;
	}

	/**
	 * (a) Update flaggedrevs page/tracking tables
	 * (b) Pages with stable versions that use this page will be purged
	 * Note: pages with current versions that use this page should already be purged
	 * @param Page $article
	 * @param User $user
	 * @param string $reason
	 * @param int $id
	 * @return true
	 */
	public static function onArticleDelete( Page $article, $user, $reason, $id ) {
		FlaggedRevs::clearTrackingRows( $id );
		FlaggedRevs::extraHTMLCacheUpdate( $article->getTitle() );
		return true;
	}

	/**
	 * (a) Update flaggedrevs page/tracking tables
	 * (b) Pages with stable versions that use this page will be purged
	 * Note: pages with current versions that use this page should already be purged
	 * @param Title $title
	 * @return true
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
	 * @param File $file
	 * @return true
	 */
	public static function onFileUpload( File $file ) {
		FlaggedRevs::stableVersionUpdates( $file->getTitle() );
		FlaggedRevs::extraHTMLCacheUpdate( $file->getTitle() );
		return true;
	}

	/**
	 * Update flaggedrevs page/tracking tables
	 * @param Title $title
	 * @return true
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
	 * @param Parser $parser
	 * @param Title $title
	 * @param bool &$skip
	 * @param int &$id
	 * @return true
	 */
	public static function parserFetchStableTemplate( $parser, Title $title, &$skip, &$id ) {
		if ( !( $parser instanceof Parser ) ) {
			return true; // nothing to do
		}
		if ( $title->getNamespace() < 0 || $title->getNamespace() == NS_MEDIAWIKI ) {
			return true; // nothing to do (bug 29579 for NS_MEDIAWIKI)
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
	 * @param Parser $parser
	 * @param Title $title
	 * @param array &$options
	 * @param string &$query
	 * @return true
	 */
	public static function parserFetchStableFile( $parser, Title $title, &$options, &$query ) {
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
			$title->resetArticleId( $title->getArticleID() ); // avoid extra queries
		}
		# Check if this file is only on a foreign repo
		$file = wfFindFile( $title );
		if ( $file && !$file->isLocal() ) {
			return true; // just use the current version (bug 41832)
		}
		$time = $sha1 = false; // unspecified (defaults to current version)
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
		# Tell Parser what file version to use
		if ( $time === '0' ) {
			$options['broken'] = true;
		} elseif ( $time !== false ) {
			$options['time'] = $time;
			$options['sha1'] = $sha1;
			# Stabilize the file link
			if ( $query != '' ) {
				$query .= '&';
			}
			$query .= "filetimestamp=" . urlencode( wfTimestamp( TS_MW, $time ) );
		}

		return true;
	}

	public static function onParserFirstCallInit( &$parser ) {
		$parser->setFunctionHook( 'pagesusingpendingchanges',
			'FlaggedRevsHooks::parserPagesUsingPendingChanges' );
		$parser->setFunctionHook( 'pendingchangelevel',
			'FlaggedRevsHooks::parserPendingChangeLevel', Parser::SFH_NO_HASH );
		return true;
	}

	public static function onParserGetVariableValueSwitch( &$parser, &$cache, &$word, &$ret ) {
		if ( $word == 'pendingchangelevel' ) {
			$ret = self::parserPendingChangeLevel( $parser );
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
			$stats = FlaggedRevsStats::getStats();
			$reviewedPerNS = $stats['reviewedPages-NS'];
			$totalCount = 0;
			foreach ( $reviewedPerNS as $ns => $reviewed ) {
				$nsList[ "ns-{$ns}" ] = $reviewed;
				$totalCount += $reviewed;
			}
			$nsList[ 'all' ] = $totalCount;
		}

		if ( $ns === '' ) {
			return $nsList['all'];
		} else {
			return $nsList[ "ns-$ns" ];
		}
	}

	public static function parserPendingChangeLevel( &$parser, $page = '' ) {
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
	 * Check page move and patrol permissions for FlaggedRevs
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param bool &$result
	 * @return true
	 */
	public static function onGetUserPermissionsErrors( Title $title, $user, $action, &$result ) {
		if ( $result === false ) {
			return true; // nothing to do
		}
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
			if ( $frev && !$user->isAllowed( 'review' ) && !$user->isAllowed( 'movestable' ) ) {
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
			if ( $right != '' && !$user->isAllowed( $right ) ) {
				$result = false;
				return false;
			}
			# Respect page protection to handle cases of "review wars".
			# If a page is restricted from editing such that a user cannot
			# edit it, then said user should not be able to review it.
			foreach ( $title->getRestrictions( 'edit' ) as $right ) {
				if ( $right === 'sysop' ) {
					// Backwards compatibility, rewrite sysop -> editprotected
					$right = 'editprotected';
				}
				if ( $right === 'autoconfirmed' ) {
					// Backwards compatibility, rewrite autoconfirmed -> editsemiprotected
					$right = 'editsemiprotected';
				}
				if ( $right != '' && !$user->isAllowed( $right ) ) {
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
	 * Note: RC items not inserted yet, RecentChange_save hook does rc_patrolled bit...
	 * Note: $article one of Article, ImagePage, Category page as appropriate.
	 * @param Page $article
	 * @param Revision $rev
	 * @param int|false $baseRevId
	 * @param User|null $user
	 * @return true
	 */
	public static function maybeMakeEditReviewed(
		Page $article, $rev, $baseRevId = false, $user = null
	) {
		global $wgRequest;

		$title = $article->getTitle(); // convenience
		# Edit must be non-null, to a reviewable page, with $user set
		$fa = FlaggableWikiPage::getTitleInstance( $title );
		$fa->loadPageData( 'fromdbmaster' );
		if ( !$rev || !$user || !$fa->isReviewable() ) {
			return true;
		}
		$fa->preloadPreparedEdit( $article ); // avoid double parse
		$title->resetArticleID( $rev->getPage() ); // Avoid extra DB hit and lag issues
		# Get what was just the current revision ID
		$prevRevId = $rev->getParentId();
		# Get edit timestamp. Existance already validated by EditPage.php.
		$editTimestamp = $wgRequest->getVal( 'wpEdittime' );
		# Is the page manually checked off to be reviewed?
		if ( $editTimestamp
			&& $wgRequest->getCheck( 'wpReviewEdit' )
			&& $title->getUserPermissionsErrors( 'review', $user ) === []
		) {
			if ( self::editCheckReview( $fa, $rev, $user, $editTimestamp ) ) {
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
			$prevTimestamp = Revision::getTimestampFromId(
				$title, $prevRevId, Revision::READ_LATEST );
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
		if ( $title->getUserPermissionsErrors( 'autoreview', $user ) === [] ) {
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
				$prevRevTimestamp = Revision::getTimestampFromId(
					$title, $prevRevId, Revision::READ_LATEST );
				if ( $editTimestamp && $editTimestamp !== $prevRevTimestamp ) {
					$baseRevId = $prevRevId;
					$altBaseRevId = 0;
				}
				# Check if the base revision was reviewed...
				if ( FlaggedRevs::autoReviewEdits() ) {
					$frev = FlaggedRevision::newFromTitle( $title, $baseRevId, FR_MASTER );
					if ( !$frev && $altBaseRevId ) {
						$frev = FlaggedRevision::newFromTitle( $title, $altBaseRevId, FR_MASTER );
					}
				}
				$reviewableChange = $frev ||
					# Bug 57073: If a user with autoreview returns the page to its last stable
					# version, it should be marked stable, regardless of the method used to do so.
					( $srev && $rev->getSha1() === $srev->getRevision()->getSha1() );
			}
			# Is this an edit directly to a reviewed version or a new page?
			if ( $reviewableNewPage || $reviewableChange ) {
				if ( $isOldRevCopy && $frev ) {
					$flags = $frev->getTags(); // null edits & rollbacks keep previous tags
				}
				# Review this revision of the page...
				FlaggedRevs::autoReviewEdit( $fa, $user, $rev, $flags );
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
				$title->getUserPermissionsErrors( 'autoreviewrestore', $user ) === []
			);
			# Check for self-reversions (checks text hashes)...
			if ( !$reviewableChange ) {
				$reviewableChange = self::isSelfRevertToStable( $rev, $srev, $baseRevId, $user );
			}
			# Is this a rollback or self-reversion to the stable rev?
			if ( $reviewableChange ) {
				$flags = $srev->getTags(); // use old tags
				# Review this revision of the page...
				FlaggedRevs::autoReviewEdit( $fa, $user, $rev, $flags );
			}
		}
		return true;
	}

	/**
	 * Review $rev if $editTimestamp matches the previous revision's timestamp.
	 * Otherwise, review the revision that has $editTimestamp as its timestamp value.
	 * @param Page $article
	 * @param Revision $rev
	 * @param User $user
	 * @param string $editTimestamp
	 * @return bool
	 */
	protected static function editCheckReview(
		Page $article, $rev, $user, $editTimestamp
	) {
		$prevTimestamp = null;
		$prevRevId = $rev->getParentId(); // revision before $rev
		$title = $article->getTitle(); // convenience
		# Check wpEdittime against the former current rev for verification
		if ( $prevRevId ) {
			$prevTimestamp = Revision::getTimestampFromId(
				$title, $prevRevId, Revision::READ_LATEST );
		}
		# Was $rev is an edit to an existing page?
		if ( $prevTimestamp ) {
			# Check wpEdittime against the former current revision's time.
			# If an edit was auto-merged in between, then the new revision
			# has content different than what the user expected. However, if
			# the auto-merged edit was reviewed, then assume that it's OK.
			if ( $editTimestamp != $prevTimestamp
				&& !FlaggedRevision::revIsFlagged( $prevRevId, FR_MASTER )
			) {
				return false; // not flagged?
			}
		}
		$flags = null;
		# Review this revision of the page...
		return FlaggedRevs::autoReviewEdit( $article, $user, $rev, $flags, false /* manual */ );
	}

	/**
	 * Check if a user reverted himself to the stable version
	 * @param Revision $rev
	 * @param FlaggedRevision $srev
	 * @param int $baseRevId
	 * @param User $user
	 * @return bool
	 */
	protected static function isSelfRevertToStable(
		Revision $rev, $srev, $baseRevId, $user
	) {
		if ( !$srev || $baseRevId != $srev->getRevId() ) {
			return false; // user reports they are not the same
		}
		$dbw = wfGetDB( DB_MASTER );
		# Such a revert requires 1+ revs between it and the stable
		$revWhere = ActorMigration::newMigration()->getWhere( $dbw, 'rev_user', $user );
		$revertedRevs = $dbw->selectField(
			[ 'revision' ] + $revWhere['tables'],
			'1',
			[
				'rev_page' => $rev->getPage(),
				'rev_id > ' . intval( $baseRevId ), // stable rev
				'rev_id < ' . intval( $rev->getId() ), // this rev
				$revWhere['conds']
			],
			__METHOD__,
			[],
			$revWhere['joins']
		);
		if ( !$revertedRevs ) {
			return false; // can't be a revert
		}
		# Check that this user is ONLY reverting his/herself.
		$otherUsers = $dbw->selectField(
			[ 'revision' ] + $revWhere['tables'],
			'1',
			[
				'rev_page' => $rev->getPage(),
				'rev_id > ' . intval( $baseRevId ),
				'NOT( ' . $revWhere['conds'] . ' )'
			],
			__METHOD__,
			[],
			$revWhere['joins']
		);
		if ( $otherUsers ) {
			return false; // only looking for self-reverts
		}
		# Confirm the text because we can't trust this user.
		return ( $rev->getSha1() === $srev->getRevision()->getSha1() );
	}

	/**
	 * When an user makes a null-edit we sometimes want to review it...
	 * (a) Null undo or rollback
	 * (b) Null edit with review box checked
	 * Note: called after edit ops are finished
	 *
	 * @param WikiPage $wikiPage
	 * @param User $user
	 * @param Content $content
	 * @param string $s
	 * @param bool $m
	 * @param string $a
	 * @param bool $b
	 * @param int $flags
	 * @param Revision $rev
	 * @param bool &$status
	 * @param int $baseId
	 *
	 * @return true
	 */
	public static function maybeNullEditReview(
		WikiPage $wikiPage, $user, $content, $s, $m, $a, $b, $flags, $rev, &$status, $baseId
	) {
		global $wgRequest;
		# Revision must *be* null (null edit). We also need the user who made the edit.
		if ( !$user || $rev !== null ) {
			return true;
		}
		# Rollback/undo or box checked
		$reviewEdit = $wgRequest->getCheck( 'wpReviewEdit' );
		if ( !$baseId && !$reviewEdit ) {
			return true; // short-circuit
		}
		$fa = FlaggableWikiPage::getTitleInstance( $wikiPage->getTitle() );
		$fa->loadPageData( 'fromdbmaster' );
		if ( !$fa->isReviewable() ) {
			return true; // page is not reviewable
		}
		$title = $wikiPage->getTitle(); // convenience
		# Get the current revision ID
		$rev = Revision::newFromTitle( $title, false, Revision::READ_LATEST );
		if ( !$rev ) {
			return true; // wtf?
		}
		$flags = null;
		# Is this a rollback/undo that didn't change anything?
		if ( $baseId > 0 ) {
			$frev = FlaggedRevision::newFromTitle( $title, $baseId ); // base rev of null edit
			$pRev = Revision::newFromId( $rev->getParentId() ); // current rev parent
			$revIsNull = ( $pRev && $pRev->getTextId() == $rev->getTextId() );
			# Was the edit that we tried to revert to reviewed?
			# We avoid auto-reviewing null edits to avoid confusion (bug 28476).
			if ( $frev && !$revIsNull ) {
				# Review this revision of the page...
				$ok = FlaggedRevs::autoReviewEdit( $wikiPage, $user, $rev, $flags );
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
		if ( $editTimestamp && $reviewEdit && $title->userCan( 'review' ) ) {
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
			$ok = FlaggedRevs::autoReviewEdit( $wikiPage, $user, $rev, $flags, false /* manual */ );
			if ( $ok ) {
				FlaggedRevs::markRevisionPatrolled( $rev ); // reviewed -> patrolled
				FlaggedRevs::extraHTMLCacheUpdate( $title );
			}
		}
		return true;
	}

	/**
	 * Mark auto-reviewed edits as patrolled
	 * @param RecentChange &$rc
	 * @return true
	 */
	public static function autoMarkPatrolled( RecentChange &$rc ) {
		if ( empty( $rc->mAttribs['rc_this_oldid'] ) ) {
			return true;
		}
		// don't autopatrol autoreviewed edits when using pending changes,
		// otherwise edits by autoreviewed users on pending changes protected pages would be
		// autopatrolled and could not be checked through RC patrol as on regular pages
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			return true;
		}
		$fa = FlaggableWikiPage::getTitleInstance( $rc->getTitle() );
		$fa->loadPageData( 'fromdbmaster' );
		// Is the page reviewable?
		if ( $fa->isReviewable() ) {
			$revId = $rc->mAttribs['rc_this_oldid'];
			// If the edit we just made was reviewed, then it's the stable rev
			$frev = FlaggedRevision::newFromTitle( $rc->getTitle(), $revId, FR_MASTER );
			// Reviewed => patrolled
			if ( $frev ) {
				DeferredUpdates::addCallableUpdate( function () use ( $rc, $frev ) {
					RevisionReviewForm::updateRecentChanges( $rc, 'patrol', $frev );
				} );
				$rc->mAttribs['rc_patrolled'] = 1; // make sure irc/email notifs know status
			}
			return true;
		}
		return true;
	}

	public static function incrementRollbacks(
		WikiPage $article, User $user, $goodRev, Revision $badRev
	) {
		# Mark when a user reverts another user, but not self-reverts
		$badUserId = $badRev->getUser( Revision::RAW );
		if ( $badUserId && $user->getId() != $badUserId ) {
			DeferredUpdates::addCallableUpdate( function () use ( $badUserId ) {
				$p = FRUserCounters::getUserParams( $badUserId, FR_FOR_UPDATE );
				if ( !isset( $p['revertedEdits'] ) ) {
					$p['revertedEdits'] = 0;
				}
				$p['revertedEdits']++;
				FRUserCounters::saveUserParams( $badUserId, $p );
			} );
		}

		return true;
	}

	/**
	 * @param Page $article
	 * @param Revision $rev
	 * @param bool $baseRevId
	 * @param null $user
	 * @return bool
	 */
	public static function incrementReverts(
		Page $article, $rev, $baseRevId = false, $user = null
	) {
		global $wgRequest;
		# Was this an edit by an auto-sighter that undid another edit?
		$undid = $wgRequest->getInt( 'undidRev' );
		if ( $rev && $undid && $user->isAllowed( 'autoreview' ) ) {
			// Note: $rev->getTitle() might be undefined (no rev id?)
			$badRev = Revision::newFromTitle( $article->getTitle(), $undid );
			if ( $badRev && $badRev->getUser( Revision::RAW ) // by logged-in user
				&& $badRev->getUser( Revision::RAW ) != $rev->getUser( Revision::RAW ) // no self-reverts
			) {
				FRUserCounters::incCount( $badRev->getUser( Revision::RAW ), 'revertedEdits' );
			}
		}
		return true;
	}

	/**
	 * Get query data for making efficient queries based on rev_user and
	 * rev_timestamp in an actor table world.
	 * @param IDatabase $dbr
	 * @param User $user
	 * @return array
	 */
	private static function getQueryData( $dbr, $user ) {
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
			} elseif ( $key === 'userid' ) {
				$data = [
					'tables' => [ 'revision' ],
					'tsField' => 'rev_timestamp',
					'cond' => $cond,
					'joins' => [],
					'useIndex' => [ 'revision' => 'user_timestamp' ],
				];
			} else { // future migration from revision_actor_temp to rev_actor
				$data = [
					'tables' => [ 'revision' ],
					'tsField' => 'rev_timestamp',
					'cond' => $cond,
					'joins' => [],
					'useIndex' => [ 'revision' => 'actor_timestamp' ],
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
	 * @return mixed (true if passed, int seconds on failure)
	 */
	protected static function editSpacingCheck( User $user, $spacingReq, $pointsReq ) {
		$dbr = wfGetDB( DB_REPLICA );
		$queryData = self::getQueryData( $dbr, $user );

		$benchmarks = 0; // actual edit points
		# Convert days to seconds...
		$spacingReq = $spacingReq * 24 * 3600;
		# Check the oldest edit
		$lower = false;
		foreach ( $queryData as $data ) {
			$ts = $dbr->selectField(
				$data['tables'],
				$data['tsField'],
				$data['cond'],
				__METHOD__,
				[ 'ORDER BY' => $data['tsField'] . ' ASC', 'USE INDEX' => $data['useIndex'] ],
				$data['joins']
			);
			$lower = $lower && $ts ? min( $lower, $ts ) : ( $lower ?: $ts );
		}
		# Recursively check for an edit $spacingReq seconds later, until we are done.
		if ( $lower ) {
			$benchmarks++; // the first edit above counts
			while ( $lower && $benchmarks < $pointsReq ) {
				$next = wfTimestamp( TS_UNIX, $lower ) + $spacingReq;
				$lower = false;
				foreach ( $queryData as $data ) {
					$ts = $dbr->selectField(
						$data['tables'],
						$data['tsField'],
						[ $data['cond'], $data['tsField'] . ' > ' . $dbr->addQuotes( $dbr->timestamp( $next ) ) ],
						__METHOD__,
						[ 'ORDER BY' => $data['tsField'] . ' ASC', 'USE INDEX' => $data['useIndex'] ],
						$data['joins']
					);
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
	protected static function reviewedEditsCheck( User $user, $editsReq, $seconds = 0 ) {
		$dbr = wfGetDB( DB_REPLICA );
		$queryData = self::getQueryData( $dbr, $user );
		// Get cutoff timestamp (excludes edits that are too recent)
		foreach ( $queryData as $k => $data ) {
			$queryData[$k]['conds'] = [
				$data['cond'],
				$data['tsField'] . ' < ' . $dbr->addQuotes( $dbr->timestamp( time() - $seconds ) )
			];
		}
		// Get the lower cutoff to avoid scanning over many rows.
		// Users with many revisions will only have the last 10k inspected.
		$lowCutoff = false;
		if ( $user->getEditCount() > 10000 ) {
			$lowCutoff = false;
			foreach ( $queryData as $data ) {
				$lowCutoff = max( $lowCutoff, $dbr->selectField(
					$data['tables'],
					$data['tsField'],
					$data['conds'],
					__METHOD__,
					[ 'ORDER BY' => $data['tsField'] . ' DESC', 'OFFSET' => 9999, 'LIMIT' => 1 ],
					$data['joins']
				) );
			}
		}
		$lowCutoff = $lowCutoff ?: 1; // default to UNIX 1970
		// Get revs from pages that have a reviewed rev of equal or higher timestamp
		$ct = 0;
		foreach ( $queryData as $data ) {
			if ( $ct >= $editsReq ) {
				break;
			}
			$res = $dbr->select(
				array_merge( $data['tables'], [ 'flaggedpages' ] ),
				'1',
				array_merge(
					$data['conds'],
					[
						// bug 15515
						'fp_pending_since IS NULL OR fp_pending_since > ' . $data['tsField'],
						// Avoid too much scanning
						$data['tsField'] . ' > ' . $dbr->addQuotes( $dbr->timestamp( $lowCutoff ) )
					]
				),
				__METHOD__,
				[ 'LIMIT' => $editsReq - $ct ],
				[ 'flaggedpages' => [ 'JOIN', 'fp_page_id = rev_page' ] ] + $data['joins']
			);
			$ct += $dbr->numRows( $res );
		}
		return ( $ct >= $editsReq );
	}

	/**
	 * Checks if $user was previously blocked since $cutoff_unixtime
	 * @param User $user
	 * @param int $cutoff_unixtime = 0
	 * @return bool
	 */
	protected static function wasPreviouslyBlocked( User $user, $cutoff_unixtime = 0 ) {
		$dbr = wfGetDB( DB_REPLICA );
		$conds = [
			'log_namespace' => NS_USER,
			'log_title'     => $user->getUserPage()->getDBkey(),
			'log_type'      => 'block',
			'log_action'    => 'block'
		];
		if ( $cutoff_unixtime > 0 ) {
			# Hint to improve NS,title,timestamp INDEX use
			$encCutoff = $dbr->addQuotes( $dbr->timestamp( $cutoff_unixtime ) );
			$conds[] = "log_timestamp >= $encCutoff";
		}
		return (bool)$dbr->selectField( 'logging', '1', $conds, __METHOD__ );
	}

	protected static function recentEditCount( $uid, $seconds, $limit ) {
		$dbr = wfGetDB( DB_REPLICA );
		$queryData = self::getQueryData( $dbr, User::newFromId( $uid ) );
		# Get cutoff timestamp (edits that are too recent)
		$encCutoff = $dbr->addQuotes( $dbr->timestamp( time() - $seconds ) );
		# Check all recent edits...
		$ct = 0;
		foreach ( $queryData as $data ) {
			if ( $ct > $limit ) {
				break;
			}
			$res = $dbr->select(
				$data['tables'],
				'1',
				[ $data['cond'], $data['tsField'] . ' > ' . $encCutoff ],
				__METHOD__,
				[ 'LIMIT' => $limit + 1 - $ct ],
				$data['joins']
			);
			$ct += $dbr->numRows( $res );
		}
		return $ct;
	}

	protected static function recentContentEditCount( $uid, $seconds, $limit ) {
		$dbr = wfGetDB( DB_REPLICA );
		$queryData = self::getQueryData( $dbr, User::newFromId( $uid ) );
		# Get cutoff timestamp (edits that are too recent)
		$encCutoff = $dbr->addQuotes( $dbr->timestamp( time() - $seconds ) );
		# Check all recent content edits...
		$ct = 0;
		foreach ( $queryData as $data ) {
			if ( $ct > $limit ) {
				break;
			}
			$res = $dbr->select(
				array_merge( $data['tables'], [ 'page' ] ),
				'1',
				[
					$data['cond'],
					"{$data['tsField']} > $encCutoff",
					'page_namespace' => MWNamespace::getContentNamespaces()
				],
				__METHOD__,
				[ 'LIMIT' => $limit + 1 - $ct, 'USE INDEX' => $data['useIndex'] ],
				[ 'page' => [ 'JOIN', 'rev_page = page_id' ] ] + $data['joins']
			);
			$ct += $dbr->numRows( $res );
		}
		return $ct;
	}

	/**
	 * Grant 'autoreview' rights to users with the 'bot' right
	 * @param User $user
	 * @param array &$rights
	 * @return true
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
	 *
	 * @param WikiPage $wikiPage
	 * @param User $user
	 * @param Content $content
	 * @param string $summary
	 * @param bool $m
	 * @param string $a
	 * @param bool $b
	 * @param int &$f
	 * @param Revision $rev
	 *
	 * @return true
	 */
	public static function onPageContentSaveComplete(
		WikiPage $wikiPage, User $user, $content, $summary, $m, $a, $b, &$f, $rev
	) {
		global $wgFlaggedRevsAutopromote, $wgFlaggedRevsAutoconfirm;
		# Ignore NULL edits, edits by anon users, and MW role account edits
		if ( !$rev || !$user->getId() || !User::isUsableName( $user->getName() ) ) {
			return true;
		# No sense in running counters if nothing uses them
		} elseif ( !$wgFlaggedRevsAutopromote && !$wgFlaggedRevsAutoconfirm ) {
			return true;
		}

		DeferredUpdates::addCallableUpdate( function () use ( $user, $wikiPage, $summary ) {
			$p = FRUserCounters::getUserParams( $user->getId(), FR_FOR_UPDATE );
			$changed = FRUserCounters::updateUserParams( $p, $wikiPage, $summary );
			if ( $changed ) {
				FRUserCounters::saveUserParams( $user->getId(), $p ); // save any updates
			}
		} );

		return true;
	}

	/**
	 * Check an autopromote condition that is defined by FlaggedRevs
	 *
	 * Note: some unobtrusive caching is used to avoid DB hits.
	 * @param int $cond
	 * @param array $params
	 * @param User $user
	 * @param bool &$result
	 * @return true
	 */
	public static function checkAutoPromoteCond( $cond, array $params, User $user, &$result ) {
		global $wgMemc;
		switch ( $cond ) {
			case APCOND_FR_EDITSUMMARYCOUNT:
				$p = FRUserCounters::getParams( $user );
				$result = ( $p && $p['editComments'] >= $params[0] );
				break;
			case APCOND_FR_NEVERBLOCKED:
				if ( $user->isBlocked() ) {
					$result = false; // failed
				} else {
					$key = wfMemcKey( 'flaggedrevs', 'autopromote-notblocked', $user->getId() );
					$val = $wgMemc->get( $key );
					if ( $val === 'false' ) {
						$result = false; // failed
					} else {
						# Hit the DB if the result is not cached or if we need
						# to check if the user was blocked since the last check...
						$now_unix = time();
						$last_checked = is_int( $val ) ? $val : 0; // TS_UNIX
						$result = !self::wasPreviouslyBlocked( $user, $last_checked );
						$wgMemc->set( $key, $result ? $now_unix : 'false', 7 * 86400 );
					}
				}
				break;
			case APCOND_FR_UNIQUEPAGECOUNT:
				$p = FRUserCounters::getParams( $user );
				$result = ( $p && count( $p['uniqueContentPages'] ) >= $params[0] );
				break;
			case APCOND_FR_EDITSPACING:
				$key = wfMemcKey( 'flaggedrevs', 'autopromote-editspacing',
					$user->getId(), $params[0], $params[1] );
				$val = $wgMemc->get( $key );
				if ( $val === 'true' ) {
					$result = true; // passed
				} elseif ( $val === 'false' ) {
					$result = false; // failed
				} else {
					# Hit the DB only if the result is not cached...
					$pass = self::editSpacingCheck( $user, $params[0], $params[1] );
					# Make a key to store the results
					if ( $pass === true ) {
						$wgMemc->set( $key, 'true', 14 * 86400 );
					} else {
						$wgMemc->set( $key, 'false', $pass /* wait time */ );
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
				$key = wfMemcKey( 'flaggedrevs', 'autopromote-reviewededits',
					$user->getId(), $params[0], $params[1] );
				$val = $wgMemc->get( $key );
				if ( $val === 'true' ) {
					$result = true; // passed
				} elseif ( $val === 'false' ) {
					$result = false; // failed
				} else {
					# Hit the DB only if the result is not cached...
					$result = self::reviewedEditsCheck( $user, $params[0], $params[1] );
					if ( $result ) {
						$wgMemc->set( $key, 'true', 7 * 86400 );
					} else {
						$wgMemc->set( $key, 'false', 3600 ); // briefly cache
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
				$result = ( $p && empty( $p['demoted'] ) );
				break;
		}
		return true;
	}

	public static function setSessionKey( User $user ) {
		global $wgRequest;
		if ( $user->isLoggedIn() && $user->isAllowed( 'review' ) ) {
			$key = $wgRequest->getSessionData( 'wsFlaggedRevsKey' );
			if ( $key === null ) { // should catch login
				$key = MWCryptRand::generateHex( 32 );
				// Temporary secret key attached to this session
				$wgRequest->setSessionData( 'wsFlaggedRevsKey', $key );
			}
		}
		return true;
	}

	public static function stableDumpQuery( array &$tables, array &$opts, array &$join ) {
		$namespaces = FlaggedRevs::getReviewNamespaces();
		if ( $namespaces ) {
			$tables[] = 'flaggedpages';
			$opts['ORDER BY'] = 'fp_page_id ASC';
			$opts['USE INDEX'] = [ 'flaggedpages' => 'PRIMARY' ];
			$join['page'] = [ 'INNER JOIN',
				[ 'page_id = fp_page_id', 'page_namespace' => $namespaces ]
			];
			$join['revision'] = [ 'INNER JOIN',
				'rev_page = fp_page_id AND rev_id = fp_stable' ];
		}
		return false; // final
	}

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

		switch ( $params['stable'] ) {
			case GoogleNewsSitemap::OPT_ONLY:
				$conditions[] = 'fp_stable IS NOT NULL ';
				break;
			case GoogleNewsSitemap::OPT_EXCLUDE:
				$conditions['fp_stable'] = null;
				break;
		}

		switch ( $params['quality'] ) {
			case GoogleNewsSitemap::OPT_ONLY:
				$conditions[] = 'fp_quality >= 1';
				break;
			case GoogleNewsSitemap::OPT_EXCLUDE:
				$conditions[] = 'fp_quality = 0 OR fp_quality IS NULL';
				break;
		}

		return true;
	}

	/**
	 * Handler for EchoGetDefaultNotifiedUsers hook.
	 * @param EchoEvent $event EchoEvent to get implicitly subscribed users for
	 * @param array &$users Array to append implicitly subscribed users to.
	 * @return bool true in all cases
	 */
	public static function onEchoGetDefaultNotifiedUsers( $event, &$users ) {
		$extra = $event->getExtra();
		if ( $event->getType() == 'reverted' && $extra['method'] == 'flaggedrevs-reject' ) {
			foreach ( $extra['reverted-users-ids'] as $userId ) {
				$users[$userId] = User::newFromId( intval( $userId ) );
			}
		}
		return true;
	}

	/**
	 * @param array &$updateFields
	 * @return bool
	 */
	public static function onUserMergeAccountFields( array &$updateFields ) {
		$updateFields[] = [ 'flaggedrevs', 'fr_user' ];

		return true;
	}

	public static function onMergeAccountFromTo( User &$oldUser, User &$newUser ) {
		// Don't merge into anonymous users...
		if ( $newUser->getId() !== 0 ) {
			FRUserCounters::mergeUserParams( $oldUser, $newUser );
		}

		return true;
	}

	public static function onDeleteAccount( User $oldUser ) {
		FRUserCounters::deleteUserParams( $oldUser );

		return true;
	}

	public static function onScribuntoExternalLibraries( $engine, array &$extraLibraries ) {
		if ( $engine == 'lua' ) {
			$extraLibraries['mw.ext.FlaggedRevs'] = 'Scribunto_LuaFlaggedRevsLibrary';
		}
		return true;
	}
}
