<?php
/**
 * Class containing hooked functions for a FlaggedRevs environment
 */
class FlaggedRevsUIHooks {
	/**
	 * Add FlaggedRevs css/js.
	 *
	 * @param OutputPage $out
	 * @return bool
	 */
	protected static function injectStyleAndJS( OutputPage $out ) {
		static $loadedModules = false;
		if ( $loadedModules ) {
			return true; // don't double-load
		}
		$loadedModules = true;
		$fa = FlaggablePageView::globalArticleInstance();
		# Try to only add to relevant pages
		if ( !$fa || !$fa->isReviewable() ) {
			return true;
		}
		# Add main CSS & JS files
		$out->addModuleStyles( 'ext.flaggedRevs.basic' );
		$out->addModules( 'ext.flaggedRevs.advanced' );
		# Add review form JS for reviewers
		if ( $out->getUser()->isAllowed( 'review' ) ) {
			$out->addModules( 'ext.flaggedRevs.review' );
			$out->addModuleStyles( 'ext.flaggedRevs.review.styles' );
		}
		return true;
	}

	/**
	 * Hook: MakeGlobalVariablesScript
	 *
	 * @param array &$globalVars
	 * @param OutputPage $out
	 * @return bool
	 */
	public static function injectGlobalJSVars( array &$globalVars, OutputPage $out ) {
		# Get the review tags on this wiki
		$rTags = FlaggedRevs::getJSTagParams();
		$globalVars['wgFlaggedRevsParams'] = $rTags;
		# Get page-specific meta-data
		$fa = FlaggableWikiPage::getTitleInstance( $out->getTitle() );
		# Try to only add to relevant pages
		if ( $fa && $fa->isReviewable() ) {
			$frev = $fa->getStableRev();
			$stableId = $frev ? $frev->getRevId() : 0;
		} else {
			$stableId = null;
		}
		$globalVars['wgStableRevisionId'] = $stableId;
		return true;
	}

	/**
	 * Add FlaggedRevs css for relevant special pages.
	 * @param OutputPage &$out
	 * @return bool
	 */
	protected static function injectStyleForSpecial( &$out ) {
		$title = $out->getTitle();
		$spPages = [ 'UnreviewedPages', 'PendingChanges', 'ProblemChanges',
			'Watchlist', 'Recentchanges', 'Contributions', 'Recentchangeslinked' ];
		foreach ( $spPages as $key ) {
			if ( $title->isSpecial( $key ) ) {
				$out->addModuleStyles( 'ext.flaggedRevs.basic' ); // CSS only
				break;
			}
		}
		return true;
	}

	/**
	 * Add tag notice, CSS/JS, protect form link, and set robots policy
	 * @param OutputPage &$out
	 * @param Skin &$skin
	 * @return true
	 */
	public static function onBeforePageDisplay( &$out, &$skin ) {
		if ( $out->getTitle()->getNamespace() != NS_SPECIAL ) {
			$view = FlaggablePageView::singleton();
			$view->addStabilizationLink(); // link on protect form
			$view->displayTag(); // show notice bar/icon in subtitle
			if ( $out->isArticleRelated() ) {
				// Only use this hook if we want to prepend the form.
				// We prepend the form for diffs, so only handle that case here.
				if ( $view->diffRevsAreSet() ) {
					$view->addReviewForm( $out ); // form to be prepended
				}
			}
			$view->setRobotPolicy(); // set indexing policy
			self::injectStyleAndJS( $out ); // full CSS/JS
		} else {
			self::maybeAddBacklogNotice( $out ); // RC/Watchlist notice
			self::injectStyleForSpecial( $out ); // try special page CSS
		}
		return true;
	}

	/**
	 * Add user preferences (uses prefs-flaggedrevs, prefs-flaggedrevs-ui msgs)
	 * @param User $user
	 * @param array &$preferences
	 * @return true
	 */
	public static function onGetPreferences( $user, array &$preferences ) {
		// Box or bar UI
		$preferences['flaggedrevssimpleui'] =
			[
				'type' => 'radio',
				'section' => 'rc/flaggedrevs-ui',
				'label-message' => 'flaggedrevs-pref-UI',
				'options' => [
					wfMessage( 'flaggedrevs-pref-UI-0' )->text() => 0,
					wfMessage( 'flaggedrevs-pref-UI-1' )->text() => 1,
				],
			];
		// Default versions...
		$preferences['flaggedrevsstable'] =
			[
				'type' => 'radio',
				'section' => 'rc/flaggedrevs-ui',
				'label-message' => 'flaggedrevs-prefs-stable',
				'options' => [
					wfMessage( 'flaggedrevs-pref-stable-0' )->text() => FR_SHOW_STABLE_DEFAULT,
					wfMessage( 'flaggedrevs-pref-stable-1' )->text() => FR_SHOW_STABLE_ALWAYS,
					wfMessage( 'flaggedrevs-pref-stable-2' )->text() => FR_SHOW_STABLE_NEVER,
				],
			];
		// Review-related rights...
		if ( $user->isAllowed( 'review' ) ) {
			// Watching reviewed pages
			$preferences['flaggedrevswatch'] =
				[
					'type' => 'toggle',
					'section' => 'watchlist/advancedwatchlist',
					'label-message' => 'flaggedrevs-prefs-watch',
				];
			// Diff-to-stable on edit
			$preferences['flaggedrevseditdiffs'] =
				[
					'type' => 'toggle',
					'section' => 'editing/advancedediting',
					'label-message' => 'flaggedrevs-prefs-editdiffs',
				];
			// Diff-to-stable on draft view
			$preferences['flaggedrevsviewdiffs'] =
				[
					'type' => 'toggle',
					'section' => 'rc/flaggedrevs-ui',
					'label-message' => 'flaggedrevs-prefs-viewdiffs',
				];
		}
		return true;
	}

	public static function onImagePageFindFile( $imagePage, &$normalFile, &$displayFile ) {
		$view = FlaggablePageView::singleton();
		$view->imagePageFindFile( $normalFile, $displayFile );
		return true;
	}

	/**
	 * MonoBook et al: $contentActions is all the tabs
	 * Vector et al: $contentActions is all the action tabs...unused
	 * @param Skin $skin
	 * @param array &$contentActions
	 * @return true
	 */
	public static function onSkinTemplateTabs( Skin $skin, array &$contentActions ) {
		if ( $skin instanceof SkinVector ) {
			// *sigh*...skip, dealt with in setNavigation()
			return true;
		}
		if ( FlaggablePageView::globalArticleInstance() != null ) {
			$view = FlaggablePageView::singleton();
			$view->setActionTabs( $skin, $contentActions );
			$view->setViewTabs( $skin, $contentActions, 'flat' );
		}
		return true;
	}

	/**
	 * Vector et al: $links is all the tabs (2 levels)
	 * @param Skin $skin
	 * @param array &$links
	 * @return true
	 */
	public static function onSkinTemplateNavigation( Skin $skin, array &$links ) {
		if ( FlaggablePageView::globalArticleInstance() != null ) {
			$view = FlaggablePageView::singleton();
			$view->setActionTabs( $skin, $links['actions'] );
			$view->setViewTabs( $skin, $links['views'], 'nav' );
		}
		return true;
	}

	public static function onArticleViewHeader( &$article, &$outputDone, &$useParserCache ) {
		$view = FlaggablePageView::singleton();
		$view->addStableLink( $outputDone, $useParserCache );
		$view->setPageContent( $outputDone, $useParserCache );
		return true;
	}

	public static function overrideRedirect(
		Title $title, WebRequest $request, &$ignoreRedirect, &$target, Page &$article
	) {
		global $wgMemc, $wgParserCacheExpireTime;
		$fa = FlaggableWikiPage::getTitleInstance( $title );
		if ( !$fa->isReviewable() ) {
			return true; // nothing to do
		}
		# Viewing an old reviewed version...
		if ( $request->getVal( 'stableid' ) ) {
			$ignoreRedirect = true; // don't redirect (same as ?oldid=x)
			return true;
		}
		$srev = $fa->getStableRev();
		$view = FlaggablePageView::singleton();
		# Check if we are viewing an unsynced stable version...
		if ( $srev && $view->showingStable() && $srev->getRevId() != $article->getLatest() ) {
			# Check the stable redirect properties from the cache...
			$key = wfMemcKey( 'flaggedrevs', 'overrideRedirect', $article->getId() );
			$tuple = FlaggedRevs::getMemcValue( $wgMemc->get( $key ), $article );
			if ( is_array( $tuple ) ) { // cache hit
				list( $ignoreRedirect, $target ) = $tuple;
			} else { // cache miss; fetch the stable rev text...
				$content = $srev->getRevision()->getContent();
				$redirect = $fa->getRedirectURL( $content->getUltimateRedirectTarget() );
				if ( $redirect ) {
					$target = $redirect; // use stable redirect
				} else {
					$ignoreRedirect = true; // make MW skip redirection
				}
				$data = FlaggedRevs::makeMemcObj( [ $ignoreRedirect, $target ] );
				$wgMemc->set( $key, $data, $wgParserCacheExpireTime ); // cache results
			}
			$clearEnvironment = (bool)$target;
		# Check if the we are viewing a draft or synced stable version...
		} else {
			# In both cases, we can just let MW use followRedirect()
			# on the draft as normal, avoiding any page text hits.
			$clearEnvironment = $article->isRedirect();
		}
		# Environment will change in MediaWiki::initializeArticle
		if ( $clearEnvironment ) {
			$view->clear();
		}

		return true;
	}

	public static function addToEditView( &$editPage ) {
		$view = FlaggablePageView::singleton();
		$view->addToEditView( $editPage );
		return true;
	}

	public static function getEditNotices( $title, $oldid, &$notices ) {
		$view = FlaggablePageView::singleton();
		$view->getEditNotices( $title, $oldid, $notices );
		return true;
	}

	public static function onBeforeEditButtons( &$editPage, &$buttons ) {
		$view = FlaggablePageView::singleton();
		$view->changeSaveButton( $editPage, $buttons );
		return true;
	}

	public static function onNoSuchSection( &$editPage, &$s ) {
		$view = FlaggablePageView::singleton();
		$view->addToNoSuchSection( $editPage, $s );
		return true;
	}

	public static function addToHistView( &$article ) {
		$view = FlaggablePageView::singleton();
		$view->addToHistView();
		return true;
	}

	public static function onCategoryPageView( &$category ) {
		$view = FlaggablePageView::singleton();
		$view->addToCategoryView();
		return true;
	}

	public static function onSkinAfterContent( &$data ) {
		global $wgOut;
		if ( $wgOut->isArticleRelated()
			&& FlaggablePageView::globalArticleInstance() != null
		) {
			$view = FlaggablePageView::singleton();
			// Only use this hook if we want to append the form.
			// We *prepend* the form for diffs, so skip that case here.
			if ( !$view->diffRevsAreSet() ) {
				$view->addReviewForm( $data ); // form to be appended
			}
		}
		return true;
	}

	/**
	 * Registers a filter on Special:NewPages to hide edits that have been reviewed
	 * through FlaggedRevs.
	 *
	 * @param SpecialPage $specialPage Special page
	 * @param array &$filters Array of filters
	 * @return true
	 */
	public static function addHideReviewedUnstructuredFilter( $specialPage, &$filters ) {
		if ( !FlaggedRevs::useSimpleConfig() ) {
			$filters['hideReviewed'] = [
				'msg' => 'flaggedrevs-hidereviewed', 'default' => false
			];
		}
		return true;
	}

	/**
	 * Registers a filter to hide edits that have been reviewed through
	 * FlaggedRevs.
	 *
	 * @param ChangesListSpecialPage $specialPage Special page, such as
	 *   Special:RecentChanges or Special:Watchlist
	 * @return true
	 */
	public static function addHideReviewedFilter( ChangesListSpecialPage $specialPage ) {
		if ( FlaggedRevs::useSimpleConfig() ) {
			return true;
		}

		// Old filter, replaced in structured UI
		$flaggedRevsUnstructuredGroup = new ChangesListBooleanFilterGroup(
			[
				'name' => 'flaggedRevsUnstructured',
				'priority' => -1,
				'filters' => [
					[
						'name' => 'hideReviewed',
						'showHide' => 'flaggedrevs-hidereviewed',
						'isReplacedInStructuredUi' => true,
						'default' => false,
						'queryCallable' => function ( $specialClassName, $ctx, $dbr, &$tables,
							&$fields, &$conds, &$query_options, &$join_conds
						) {
							FlaggedRevsUIHooks::hideReviewedChangesUnconditionally(
								$conds
							);
						},
					],
				],
			]
		);

		$specialPage->registerFilterGroup( $flaggedRevsUnstructuredGroup );

		$flaggedRevsGroup = new ChangesListStringOptionsFilterGroup(
			[
				'name' => 'flaggedrevs',
				'title' => 'flaggedrevs',
				'priority' => -9,
				'default' => ChangesListStringOptionsFilterGroup::NONE,
				'isFullCoverage' => true,
				'filters' => [
					[
						'name' => 'needreview',
						'label' => 'flaggedrevs-rcfilters-need-review-label',
						'description' => 'flaggedrevs-rcfilters-need-review-desc',
						'cssClassSuffix' => 'need-review',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							$namespaces = FlaggedRevs::getReviewNamespaces();
							return ( in_array( $rc->getAttribute( 'rc_namespace' ), $namespaces ) &&
								$rc->getAttribute( 'rc_type' ) !== RC_EXTERNAL ) &&
								(
									!$rc->getAttribute( 'fp_stable' ) ||
									(
										// The rc_timestamp >= fp_pending_since condition implies that fp_pending_since is
										// not null, because all comparisons with null values are false in MySQL. It doesn't
										// work that way in PHP, so we have to explicitly check that fp_pending_since is not null
										$rc->getAttribute( 'fp_pending_since' ) &&
										$rc->getAttribute( 'rc_timestamp' ) >= $rc->getAttribute( 'fp_pending_since' )
									)
								);
						}
					],
					[
						'name' => 'reviewed',
						'label' => 'flaggedrevs-rcfilters-reviewed-label',
						'description' => 'flaggedrevs-rcfilters-reviewed-desc',
						'cssClassSuffix' => 'reviewed',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							$namespaces = FlaggedRevs::getReviewNamespaces();
							return ( in_array( $rc->getAttribute( 'rc_namespace' ), $namespaces ) &&
								$rc->getAttribute( 'rc_type' ) !== RC_EXTERNAL ) &&
								$rc->getAttribute( 'fp_stable' ) &&
								(
									!$rc->getAttribute( 'fp_pending_since' ) ||
									$rc->getAttribute( 'rc_timestamp' ) < $rc->getAttribute( 'fp_pending_since' )
								);
						}
					],
					[
						'name' => 'notreviewable',
						'label' => 'flaggedrevs-rcfilters-not-reviewable-label',
						'description' => 'flaggedrevs-rcfilters-not-reviewable-desc',
						'cssClassSuffix' => 'not-reviewable',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							$namespaces = FlaggedRevs::getReviewNamespaces();
							return !in_array( $rc->getAttribute( 'rc_namespace' ), $namespaces );
						}
					],
				],
				'queryCallable' => function ( $specialClassName, $ctx, $dbr, &$tables,
					&$fields, &$conds, &$query_options, &$join_conds, $selectedValues
				) {
					$fields[] = 'fp_stable';
					$fields[] = 'fp_pending_since';
					$fields[] = 'rc_namespace';

					$namespaces = FlaggedRevs::getReviewNamespaces();
					$needReviewCond = 'rc_timestamp >= fp_pending_since OR fp_stable IS NULL';
					$reviewedCond = '(fp_pending_since IS NULL OR rc_timestamp < fp_pending_since) '.
						'AND fp_stable IS NOT NULL';
					$notReviewableCond = 'rc_namespace NOT IN (' . $dbr->makeList( $namespaces ) .
						') OR rc_type = ' . $dbr->addQuotes( RC_EXTERNAL );
					$reviewableCond = 'rc_namespace IN (' . $dbr->makeList( $namespaces ) .
						') AND rc_type != ' . $dbr->addQuotes( RC_EXTERNAL );

					if ( $selectedValues === [ 'needreview', 'notreviewable', 'reviewed' ] ) {
						// no filters
						return;
					}

					if ( $selectedValues === [ 'needreview', 'reviewed' ] ) {
						$conds[] = $reviewableCond;
						return;
					}

					if ( $selectedValues === [ 'needreview', 'notreviewable' ] ) {
						$conds[] = $dbr->makeList( [
							$notReviewableCond,
							$needReviewCond
						], LIST_OR );
						return;
					}

					if ( $selectedValues === [ 'notreviewable', 'reviewed' ] ) {
						$conds[] = $dbr->makeList( [
							$notReviewableCond,
							$reviewedCond
						], LIST_OR );
						return;
					}

					if ( $selectedValues === [ 'needreview' ] ) {
						$conds[] = $dbr->makeList( [
							$reviewableCond,
							$needReviewCond
						], LIST_AND );
						return;
					}

					if ( $selectedValues === [ 'notreviewable' ] ) {
						$conds[] = $notReviewableCond;
						return;
					}

					if ( $selectedValues === [ 'reviewed' ] ) {
						$conds[] = $dbr->makeList( [
							$reviewableCond,
							$reviewedCond
						], LIST_AND );
						return;
					}
				}
			]
		);

		$specialPage->registerFilterGroup( $flaggedRevsGroup );
		return true;
	}

	public static function addToHistQuery( HistoryPager $pager, array &$queryInfo ) {
		$flaggedArticle = FlaggableWikiPage::getTitleInstance( $pager->getTitle() );
		# Non-content pages cannot be validated. Stable version must exist.
		if ( $flaggedArticle->isReviewable() && $flaggedArticle->getStableRev() ) {
			# Highlight flaggedrevs
			$queryInfo['tables'][] = 'flaggedrevs';
			$queryInfo['fields'][] = 'fr_quality';
			$queryInfo['fields'][] = 'fr_user';
			$queryInfo['fields'][] = 'fr_flags';
			$queryInfo['join_conds']['flaggedrevs'] = [ 'LEFT JOIN', "fr_rev_id = rev_id" ];
			# Find reviewer name. Sanity check that no extensions added a `user` query.
			if ( !in_array( 'user', $queryInfo['tables'] ) ) {
				$queryInfo['tables'][] = 'user';
				$queryInfo['fields'][] = 'user_name AS reviewer';
				$queryInfo['join_conds']['user'] = [ 'LEFT JOIN', "user_id = fr_user" ];
			}
		}
		return true;
	}

	public static function addToFileHistQuery(
		File $file, array &$tables, array &$fields, &$conds, array &$opts, array &$join_conds
	) {
		if ( !$file->isLocal() ) {
			return true; // local files only
		}
		$flaggedArticle = FlaggableWikiPage::getTitleInstance( $file->getTitle() );
		# Non-content pages cannot be validated. Stable version must exist.
		if ( $flaggedArticle->isReviewable() && $flaggedArticle->getStableRev() ) {
			$tables[] = 'flaggedrevs';
			$fields[] = 'MAX(fr_quality) AS fr_quality';
			# Avoid duplicate rows due to multiple revs with the same sha-1 key

			# This is a stupid hack to get all the field names in our GROUP BY
			# clause. Postgres yells at you for not including all of the selected
			# columns, so grab the full list, unset the two we actually want to
			# order by, then append the rest of them to our two. It would be
			# REALLY nice if we handled this automagically in makeSelectOptions()
			# or something *sigh*
			$groupBy = OldLocalFile::getQueryInfo()['fields'];
			unset( $groupBy[ array_search( 'oi_name', $groupBy ) ] );
			unset( $groupBy[ array_search( 'oi_timestamp', $groupBy ) ] );
			$opts['GROUP BY'] = 'oi_name,oi_timestamp,' . implode( ',', $groupBy );

			$join_conds['flaggedrevs'] = [ 'LEFT JOIN',
				'oi_sha1 = fr_img_sha1 AND oi_timestamp = fr_img_timestamp' ];
		}
		return true;
	}

	public static function addToContribsQuery( $pager, array &$queryInfo ) {
		# Highlight flaggedrevs
		$queryInfo['tables'][] = 'flaggedrevs';
		$queryInfo['fields'][] = 'fr_quality';
		$queryInfo['join_conds']['flaggedrevs'] = [ 'LEFT JOIN', "fr_rev_id = rev_id" ];
		# Highlight unchecked content
		$queryInfo['tables'][] = 'flaggedpages';
		$queryInfo['fields'][] = 'fp_stable';
		$queryInfo['fields'][] = 'fp_pending_since';
		$queryInfo['join_conds']['flaggedpages'] = [ 'LEFT JOIN', "fp_page_id = rev_page" ];
		return true;
	}

	public static function modifyNewPagesQuery(
		$specialPage, $opts, &$conds, &$tables, &$fields, &$join_conds
	) {
		self::makeAllQueryChanges( $conds, $tables, $join_conds, $fields );

		return true;
	}

	public static function modifyChangesListSpecialPageQuery(
		$name, &$tables, &$fields, &$conds, &$query_options, &$join_conds, $opts
	) {
		self::addMetadataQueryJoins( $tables, $join_conds, $fields );
	}

	/**
	 * Make all query changes, both joining for FlaggedRevs metadata and conditionally
	 * hiding reviewed changes
	 *
	 * @param array &$conds Query conditions
	 * @param array &$tables Tables to query
	 * @param array &$join_conds Query join conditions
	 * @param array &$fields Fields to query
	 */
	public static function makeAllQueryChanges(
		array &$conds, array &$tables, array &$join_conds, array &$fields
	) {
		self::addMetadataQueryJoins( $tables, $join_conds, $fields );
		self::hideReviewedChangesIfNeeded( $conds );
	}

	/**
	 * Add FlaggedRevs metadata by adding fields and joins
	 *
	 * @param array &$tables Tables to query
	 * @param array &$join_conds Query join conditions
	 * @param array &$fields Fields to query
	 */
	public static function addMetadataQueryJoins(
		array &$tables, array &$join_conds, array &$fields
	) {
		$tables[] = 'flaggedpages';
		$fields[] = 'fp_stable';
		$fields[] = 'fp_pending_since';
		$join_conds['flaggedpages'] = [ 'LEFT JOIN', 'fp_page_id = rc_cur_id' ];
	}

	/**
	 * Checks the request variable and hides reviewed changes if requested
	 *
	 * Must already be joined into the FlaggedRevs tables.
	 *
	 * @param array &$conds Query conditions
	 */
	public static function hideReviewedChangesIfNeeded(
		array &$conds
	) {
		global $wgRequest;

		if ( $wgRequest->getBool( 'hideReviewed' ) && !FlaggedRevs::useSimpleConfig() ) {
			self::hideReviewedChangesUnconditionally( $conds );
		}
	}

	/**
	 * Hides reviewed changes unconditionally; assumes you have checked whether to do
	 * so already
	 *
	 * Must already be joined into the FlaggedRevs tables.
	 *
	 * @param array &$conds Query conditions
	 */
	public static function hideReviewedChangesUnconditionally(
		array &$conds
	) {
		// Don't filter external changes as FlaggedRevisions doesn't apply to those
		$conds[] = 'rc_timestamp >= fp_pending_since OR fp_stable IS NULL OR rc_type = ' . RC_EXTERNAL;
	}

	public static function addToHistLine( HistoryPager $history, $row, &$s, &$liClasses ) {
		$fa = FlaggableWikiPage::getTitleInstance( $history->getTitle() );
		if ( !$fa->isReviewable() ) {
			return true; // nothing to do here
		}
		# Fetch and process cache the stable revision
		if ( !isset( $history->fr_stableRevId ) ) {
			$srev = $fa->getStableRev();
			$history->fr_stableRevId = $srev ? $srev->getRevId() : null;
			$history->fr_stableRevUTS = $srev ? // bug 15515
				wfTimestamp( TS_UNIX, $srev->getRevTimestamp() ) : null;
			$history->fr_pendingRevs = false;
		}
		if ( !$history->fr_stableRevId ) {
			return true; // nothing to do here
		}
		$title = $history->getTitle();
		$revId = (int)$row->rev_id;
		// Pending revision: highlight and add diff link
		$link = $class = '';
		if ( wfTimestamp( TS_UNIX, $row->rev_timestamp ) > $history->fr_stableRevUTS ) {
			$class = 'flaggedrevs-pending';
			$link = $history->msg( 'revreview-hist-pending-difflink',
				$title->getPrefixedText(), $history->fr_stableRevId, $revId )->parse();
			$link = '<span class="plainlinks mw-fr-hist-difflink">' . $link . '</span>';
			$history->fr_pendingRevs = true; // pending rev shown above stable
		// Reviewed revision: highlight and add link
		} elseif ( isset( $row->fr_quality ) ) {
			if ( !( $row->rev_deleted & Revision::DELETED_TEXT ) ) {
				# Add link to stable version of *this* rev, if any
				list( $link, $class ) = self::markHistoryRow( $history, $title, $row );
				# Space out and demark the stable revision
				if ( $revId == $history->fr_stableRevId && $history->fr_pendingRevs ) {
					$liClasses[] = 'fr-hist-stable-margin';
				}
			}
		}
		# Style the row as needed
		if ( $class ) {
			$s = "<span class='$class'>$s</span>";
		}
		# Add stable old version link
		if ( $link ) {
			$s .= " $link";
		}
		return true;
	}

	/**
	 * Make stable version link and return the css
	 * @param IContextSource $ctx
	 * @param Title $title
	 * @param stdClass $row from history page
	 * @return array (string,string)
	 */
	protected static function markHistoryRow( IContextSource $ctx, Title $title, $row ) {
		if ( !isset( $row->fr_quality ) ) {
			return [ "", "" ]; // not reviewed
		}
		$liCss = FlaggedRevsXML::getQualityColor( $row->fr_quality );
		$flags = explode( ',', $row->fr_flags );
		if ( in_array( 'auto', $flags ) ) {
			$msg = ( $row->fr_quality >= 1 )
				? 'revreview-hist-quality-auto'
				: 'revreview-hist-basic-auto';
			$css = ( $row->fr_quality >= 1 )
				? 'fr-hist-quality-auto'
				: 'fr-hist-basic-auto';
		} else {
			$msg = ( $row->fr_quality >= 1 )
				? 'revreview-hist-quality-user'
				: 'revreview-hist-basic-user';
			$css = ( $row->fr_quality >= 1 )
				? 'fr-hist-quality-user'
				: 'fr-hist-basic-user';
		}
		$name = isset( $row->reviewer ) ?
			$row->reviewer : User::whoIs( $row->fr_user );
		$link = $ctx->msg( $msg, $title->getPrefixedDBkey(), $row->rev_id, $name )->parse();
		$link = "<span class='$css plainlinks'>[$link]</span>";
		return [ $link, $liCss ];
	}

	public static function addToFileHistLine( $hist, File $file, &$s, &$rowClass ) {
		if ( !$file->isVisible() ) {
			return true; // Don't bother showing notice for deleted revs
		}
		# Quality level for old versions selected all at once.
		# Commons queries cannot be done all at once...
		if ( !$file->isOld() || !$file->isLocal() ) {
			$dbr = wfGetDB( DB_REPLICA );
			$quality = $dbr->selectField( 'flaggedrevs', 'fr_quality',
				[ 'fr_img_sha1' => $file->getSha1(),
					'fr_img_timestamp' => $dbr->timestamp( $file->getTimestamp() ) ],
				__METHOD__
			);
		} else {
			$quality = is_null( $file->quality ) ? false : $file->quality;
		}
		# If reviewed, class the line
		if ( $quality !== false ) {
			$rowClass = FlaggedRevsXML::getQualityColor( $quality );
		}
		return true;
	}

	/**
	 * Intercept contribution entries and format them to FlaggedRevs standards
	 *
	 * @param SpecialPage $contribs SpecialPage object for contributions
	 * @param string &$ret the HTML line
	 * @param stdClass $row Row the DB row for this line
	 * @param array &$classes the classes to add to the surrounding <li>
	 * @return bool
	 */
	public static function addToContribsLine( $contribs, &$ret, $row, &$classes ) {
		// make sure that we're parsing revisions data
		if ( isset( $row->rev_id ) ) {
			$namespaces = FlaggedRevs::getReviewNamespaces();
			if ( !in_array( $row->page_namespace, $namespaces ) ) {
				// do nothing
			} elseif ( isset( $row->fr_quality ) ) {
				$classes[] = FlaggedRevsXML::getQualityColor( $row->fr_quality );
			} elseif ( isset( $row->fp_pending_since )
				&& $row->rev_timestamp >= $row->fp_pending_since // bug 15515
			) {
				$classes[] = 'flaggedrevs-pending';
			} elseif ( !isset( $row->fp_stable ) ) {
				$classes[] = 'flaggedrevs-unreviewed';
			}
		}
		return true;
	}

	public static function addToChangeListLine( &$list, &$articlelink, &$s, RecentChange &$rc ) {
		global $wgUser;
		$title = $rc->getTitle(); // convenience
		if ( !FlaggedRevs::inReviewNamespace( $title )
			|| empty( $rc->mAttribs['rc_this_oldid'] ) // rev, not log
			|| !array_key_exists( 'fp_stable', $rc->mAttribs )
		) {
			return true; // confirm that page is in reviewable namespace
		}
		$rlink = $css = '';
		// page is not reviewed
		if ( $rc->mAttribs['fp_stable'] == null ) {
			// Is this a config were pages start off reviewable?
			// Hide notice from non-reviewers due to vandalism concerns (bug 24002).
			if ( !FlaggedRevs::useSimpleConfig() && $wgUser->isAllowed( 'review' ) ) {
				$rlink = wfMessage( 'revreview-unreviewedpage' )->escaped();
				$css = 'flaggedrevs-unreviewed';
			}
		// page is reviewed and has pending edits (use timestamps; bug 15515)
		} elseif ( isset( $rc->mAttribs['fp_pending_since'] ) &&
			$rc->mAttribs['rc_timestamp'] >= $rc->mAttribs['fp_pending_since']
		) {
			$rlink = Linker::link(
				$title,
				wfMessage( 'revreview-reviewlink' )->escaped(),
				[ 'title' => wfMessage( 'revreview-reviewlink-title' )->text() ],
				[ 'oldid' => $rc->mAttribs['fp_stable'], 'diff' => 'cur' ] +
					FlaggedRevs::diffOnlyCGI()
			);
			$css = 'flaggedrevs-pending';
		}
		if ( $rlink != '' ) {
			$articlelink .= " <span class=\"mw-fr-reviewlink $css\">[$rlink]</span>";
		}
		return true;
	}

	public static function injectPostEditURLParams( $article, &$sectionAnchor, &$extraQuery ) {
		if ( FlaggablePageView::globalArticleInstance() != null ) {
			$view = FlaggablePageView::singleton();
			$view->injectPostEditURLParams( $sectionAnchor, $extraQuery );
		}
		return true;
	}

	/**
	 * diff=review param (bug 16923)
	 * @param Title $titleObj
	 * @param int &$mOldid
	 * @param int &$mNewid
	 * @param string $old
	 * @param string $new
	 * @return true
	 */
	public static function checkDiffUrl( $titleObj, &$mOldid, &$mNewid, $old, $new ) {
		if ( $new === 'review' && isset( $titleObj ) ) {
			$sRevId = FlaggedRevision::getStableRevId( $titleObj );
			if ( $sRevId ) {
				$mOldid = $sRevId; // stable
				$mNewid = 0; // cur
			}
		}
		return true;
	}

	/**
	 * Hook: DiffViewHeader
	 *
	 * @param DifferenceEngine $diff
	 * @param Revision|null $oldRev
	 * @param Revision $newRev
	 * @return bool
	 */
	public static function onDiffViewHeader( DifferenceEngine $diff, $oldRev, $newRev ) {
		self::injectStyleAndJS( $diff->getOutput() );
		$view = FlaggablePageView::singleton();
		$view->setViewFlags( $diff, $oldRev, $newRev );
		$view->addToDiffView( $diff, $oldRev, $newRev );
		return true;
	}

	public static function addRevisionIDField( $editPage, $out ) {
		$view = FlaggablePageView::singleton();
		$view->addRevisionIDField( $editPage, $out );
		return true;
	}

	public static function onEditPageBeforeEditChecks( $editPage, &$checks, &$tabindex ) {
		$view = FlaggablePageView::singleton();
		$view->addReviewCheck( $editPage, $checks, $tabindex );
		return true;
	}

	public static function onEditPageGetCheckboxesDefinition( $editPage, &$checkboxes ) {
		$view = FlaggablePageView::singleton();
		$view->addReviewCheck( $editPage, $checkboxes );
		return true;
	}

	protected static function maybeAddBacklogNotice( OutputPage &$out ) {
		global $wgUser;
		if ( !$wgUser->isAllowed( 'review' ) ) {
			return true; // not relevant to user
		}
		$namespaces = FlaggedRevs::getReviewNamespaces();
		$watchlist = SpecialPage::getTitleFor( 'Watchlist' );
		# Add notice to watchlist about pending changes...
		if ( $out->getTitle()->equals( $watchlist ) && $namespaces ) {
			$dbr = wfGetDB( DB_REPLICA, 'watchlist' ); // consistency with watchlist
			$watchedOutdated = (bool)$dbr->selectField(
				[ 'watchlist', 'page', 'flaggedpages' ],
				'1', // existence
				[ 'wl_user' => $wgUser->getId(), // this user
					'wl_namespace' => $namespaces, // reviewable
					'wl_namespace = page_namespace',
					'wl_title = page_title',
					'fp_page_id = page_id',
					'fp_pending_since IS NOT NULL', // edits pending
				], __METHOD__
			);
			# Give a notice if pages on the users's wachlist have pending edits
			if ( $watchedOutdated ) {
				$css = 'plainlinks fr-watchlist-pending-notice';
				$out->prependHTML( "<div id='mw-fr-watchlist-pending-notice' class='$css'>" .
					wfMessage( 'flaggedrevs-watched-pending' )->parse() . "</div>" );
			}
		}
		return true;
	}

	/**
	 * Add selector of review "protection" options
	 * Code stolen from Stabilization (which was stolen from ProtectionForm)
	 * @param Page $article
	 * @param string &$output
	 * @return true
	 */
	public static function onProtectionForm( Page $article, &$output ) {
		global $wgUser, $wgOut, $wgRequest, $wgLang;
		if ( !$article->exists() ) {
			return true; // nothing to do
		} elseif ( !FlaggedRevs::inReviewNamespace( $article->getTitle() ) ) {
			return true; // not a reviewable page
		}
		$form = new PageStabilityProtectForm( $wgUser );
		$form->setPage( $article->getTitle() );
		# Can the user actually do anything?
		$isAllowed = $form->isAllowed();
		$disabledAttrib = $isAllowed ?
			[] : [ 'disabled' => 'disabled' ];

		# Get the current config/expiry
		$mode = $wgRequest->wasPosted() ? FR_MASTER : 0;
		$config = FRPageConfig::getStabilitySettings( $article->getTitle(), $mode );
		$oldExpirySelect = ( $config['expiry'] == 'infinity' ) ? 'infinite' : 'existing';

		# Load requested restriction level, default to current level...
		$restriction = $wgRequest->getVal( 'mwStabilityLevel',
			FRPageConfig::getProtectionLevel( $config ) );
		# Load the requested expiry time (dropdown)
		$expirySelect = $wgRequest->getVal( 'mwStabilizeExpirySelection', $oldExpirySelect );
		# Load the requested expiry time (field)
		$expiryOther = $wgRequest->getVal( 'mwStabilizeExpiryOther', '' );
		if ( $expiryOther != '' ) {
			$expirySelect = 'othertime'; // mutual exclusion
		}

		# Add an extra row to the protection fieldset tables.
		# Includes restriction dropdown and expiry dropdown & field.
		$output .= "<tr><td>";
		$output .= Xml::openElement( 'fieldset' );
		$legendMsg = wfMessage( 'flaggedrevs-protect-legend' )->parse();
		$output .= "<legend>{$legendMsg}</legend>";
		# Add a "no restrictions" level
		$effectiveLevels = FlaggedRevs::getRestrictionLevels();
		array_unshift( $effectiveLevels, "none" );
		# Show all restriction levels in a <select>...
		$attribs = [
			'id'    => 'mwStabilityLevel',
			'name'  => 'mwStabilityLevel',
			'size'  => count( $effectiveLevels ),
		] + $disabledAttrib;
		$output .= Xml::openElement( 'select', $attribs );
		foreach ( $effectiveLevels as $limit ) {
			if ( $limit == 'none' ) {
				$label = wfMessage( 'flaggedrevs-protect-none' )->text();
			} else {
				$label = wfMessage( 'flaggedrevs-protect-' . $limit )->text();
			}
			// Default to the key itself if no UI message
			if ( wfMessage( 'flaggedrevs-protect-' . $limit )->isDisabled() ) {
				$label = 'flaggedrevs-protect-' . $limit;
			}
			$output .= Xml::option( $label, $limit, $limit == $restriction );
		}
		$output .= Xml::closeElement( 'select' );

		# Get expiry dropdown <select>...
		$scExpiryOptions = wfMessage( 'protect-expiry-options' )->inContentLanguage()->text();
		$showProtectOptions = ( $scExpiryOptions !== '-' && $isAllowed );
		# Add the current expiry as an option
		$expiryFormOptions = '';
		if ( $config['expiry'] != 'infinity' ) {
			$timestamp = $wgLang->timeanddate( $config['expiry'] );
			$d = $wgLang->date( $config['expiry'] );
			$t = $wgLang->time( $config['expiry'] );
			$expiryFormOptions .=
				Xml::option(
					wfMessage( 'protect-existing-expiry', $timestamp, $d, $t )->text(),
					'existing',
					$expirySelect == 'existing'
				) . "\n";
		}
		$expiryFormOptions .= Xml::option( wfMessage(
				'protect-othertime-op' )->text(),
				'othertime'
		) . "\n";
		# Add custom dropdown levels (from MediaWiki message)
		foreach ( explode( ',', $scExpiryOptions ) as $option ) {
			if ( strpos( $option, ":" ) === false ) {
				$show = $value = $option;
			} else {
				list( $show, $value ) = explode( ":", $option );
			}
			$show = htmlspecialchars( $show );
			$value = htmlspecialchars( $value );
			$expiryFormOptions .= Xml::option( $show, $value, $expirySelect == $value ) . "\n";
		}
		# Actually add expiry dropdown to form
		$output .= "<table>"; // expiry table start
		if ( $showProtectOptions && $isAllowed ) {
			$output .= "
				<tr>
					<td class='mw-label'>" .
						Xml::label( wfMessage( 'stabilization-expiry' )->text(),
							'mwStabilizeExpirySelection' ) .
					"</td>
					<td class='mw-input'>" .
						Xml::tags( 'select',
							[
								'id'        => 'mwStabilizeExpirySelection',
								'name'      => 'mwStabilizeExpirySelection',
								'onchange'  => 'onFRChangeExpiryDropdown()',
							] + $disabledAttrib,
							$expiryFormOptions ) .
					"</td>
				</tr>";
		}
		# Add custom expiry field to form
		$attribs = [ 'id' => 'mwStabilizeExpiryOther',
			'onkeyup' => 'onFRChangeExpiryField()' ] + $disabledAttrib;
		$output .= "
			<tr>
				<td class='mw-label'>" .
					Xml::label(
						wfMessage( 'stabilization-othertime' )->text(),
						'mwStabilizeExpiryOther'
					) .
				'</td>
				<td class="mw-input">' .
					Xml::input( 'mwStabilizeExpiryOther', 50, $expiryOther, $attribs ) .
				'</td>
			</tr>';
		$output .= "</table>"; // expiry table end
		# Close field set and table row
		$output .= Xml::closeElement( 'fieldset' );
		$output .= "</td></tr>";

		# Add some javascript for expiry dropdowns
		$wgOut->addScript(
			"<script type=\"text/javascript\">
				function onFRChangeExpiryDropdown() {
					document.getElementById('mwStabilizeExpiryOther').value = '';
				}
				function onFRChangeExpiryField() {
					document.getElementById('mwStabilizeExpirySelection').value = 'othertime';
				}
			</script>"
		);
		return true;
	}

	/**
	 * Add stability log extract to protection form
	 * @param Page $article
	 * @param OutputPage $out
	 * @return true
	 */
	public static function insertStabilityLog( Page $article, OutputPage $out ) {
		if ( !$article->exists() ) {
			return true; // nothing to do
		} elseif ( !FlaggedRevs::inReviewNamespace( $article->getTitle() ) ) {
			return true; // not a reviewable page
		}
		# Show relevant lines from the stability log:
		$logPage = new LogPage( 'stable' );
		$out->addHTML( Xml::element( 'h2', null, $logPage->getName() ) );
		LogEventsList::showLogExtract( $out, 'stable', $article->getTitle()->getPrefixedText() );
		return true;
	}

	/**
	 * Update stability config from request
	 * @param Page $article
	 * @param string &$errorMsg
	 * @return true
	 */
	public static function onProtectionSave( Page $article, &$errorMsg ) {
		global $wgUser, $wgRequest;
		if ( !$article->exists() ) {
			return true; // simple custom levels set for action=protect
		} elseif ( !FlaggedRevs::inReviewNamespace( $article->getTitle() ) ) {
			return true; // not a reviewable page
		} elseif ( wfReadOnly() || !$wgUser->isAllowed( 'stablesettings' ) ) {
			return true; // user cannot change anything
		}
		$form = new PageStabilityProtectForm( $wgUser );
		$form->setPage( $article->getTitle() ); // target page
		$permission = $wgRequest->getVal( 'mwStabilityLevel' );
		if ( $permission == "none" ) {
			$permission = ''; // 'none' => ''
		}
		$form->setAutoreview( $permission ); // protection level (autoreview restriction)
		$form->setWatchThis( null ); // protection form already has a watch check
		$form->setReasonExtra( $wgRequest->getText( 'mwProtect-reason' ) ); // manual
		$form->setReasonSelection( $wgRequest->getVal( 'wpProtectReasonSelection' ) ); // dropdown
		$form->setExpiryCustom( $wgRequest->getVal( 'mwStabilizeExpiryOther' ) ); // manual
		$form->setExpirySelection( $wgRequest->getVal( 'mwStabilizeExpirySelection' ) ); // dropdown
		$form->ready(); // params all set
		if ( $wgRequest->wasPosted() && $form->isAllowed() ) {
			$status = $form->submit();
			if ( $status !== true ) {
				$errorMsg = wfMessage( $status )->text(); // some error message
			}
		}
		return true;
	}

	public static function onSpecialPage_initList( array &$list ) {
		global $wgFlaggedRevsProtection, $wgFlaggedRevsNamespaces, $wgUseTagFilter;

		// Show special pages only if FlaggedRevs is enabled on some namespaces
		if ( count( $wgFlaggedRevsNamespaces ) ) {
			$list['RevisionReview'] = 'RevisionReview'; // unlisted
			$list['ReviewedVersions'] = 'ReviewedVersions'; // unlisted
			$list['PendingChanges'] = 'PendingChanges';
			// Show tag filtered pending edit page if there are tags
			if ( $wgUseTagFilter ) {
				$list['ProblemChanges'] = 'ProblemChanges';
			}
			if ( !$wgFlaggedRevsProtection ) {
				$list['ReviewedPages'] = 'ReviewedPages';
				$list['UnreviewedPages'] = 'UnreviewedPages';
			}
			$list['QualityOversight'] = 'QualityOversight';
			$list['ValidationStatistics'] = 'ValidationStatistics';
			// Protect levels define allowed stability settings
			if ( $wgFlaggedRevsProtection ) {
				$list['StablePages'] = 'StablePages';
			} else {
				$list['ConfiguredPages'] = 'ConfiguredPages';
				$list['Stabilization'] = 'Stabilization'; // unlisted
			}
		}
	}
}
