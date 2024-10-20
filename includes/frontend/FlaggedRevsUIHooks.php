<?php
// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
// phpcs:disable MediaWiki.Commenting.FunctionComment.MissingDocumentationPublic

use MediaWiki\Context\IContextSource;
use MediaWiki\Diff\Hook\DifferenceEngineViewHeaderHook;
use MediaWiki\Diff\Hook\NewDifferenceEngineHook;
use MediaWiki\Hook\ArticleUpdateBeforeRedirectHook;
use MediaWiki\Hook\ChangesListInsertArticleLinkHook;
use MediaWiki\Hook\ContribsPager__getQueryInfoHook;
use MediaWiki\Hook\ContributionsLineEndingHook;
use MediaWiki\Hook\EditPageBeforeEditButtonsHook;
use MediaWiki\Hook\EditPageGetCheckboxesDefinitionHook;
use MediaWiki\Hook\EditPageNoSuchSectionHook;
use MediaWiki\Hook\InfoActionHook;
use MediaWiki\Hook\InitializeArticleMaybeRedirectHook;
use MediaWiki\Hook\PageHistoryBeforeListHook;
use MediaWiki\Hook\PageHistoryLineEndingHook;
use MediaWiki\Hook\PageHistoryPager__getQueryInfoHook;
use MediaWiki\Hook\ProtectionForm__saveHook;
use MediaWiki\Hook\ProtectionForm__showLogExtractHook;
use MediaWiki\Hook\ProtectionFormAddFormFieldsHook;
use MediaWiki\Hook\SkinAfterContentHook;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Hook\SpecialNewpagesConditionsHook;
use MediaWiki\Hook\SpecialNewPagesFiltersHook;
use MediaWiki\Hook\TitleGetEditNoticesHook;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\Hook\MakeGlobalVariablesScriptHook;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\Hook\ArticleViewHeaderHook;
use MediaWiki\Page\Hook\CategoryPageViewHook;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use MediaWiki\ResourceLoader\ResourceLoader;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\SpecialPage\Hook\ChangesListSpecialPageQueryHook;
use MediaWiki\SpecialPage\Hook\ChangesListSpecialPageStructuredFiltersHook;
use MediaWiki\SpecialPage\Hook\SpecialPage_initListHook;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleValue;
use Wikimedia\Rdbms\IDBAccessObject;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Rdbms\RawSQLExpression;

/**
 * Class containing hooked functions for a FlaggedRevs environment
 */
class FlaggedRevsUIHooks implements
	ArticleUpdateBeforeRedirectHook,
	ArticleViewHeaderHook,
	BeforePageDisplayHook,
	CategoryPageViewHook,
	ChangesListInsertArticleLinkHook,
	ChangesListSpecialPageQueryHook,
	ChangesListSpecialPageStructuredFiltersHook,
	ContribsPager__getQueryInfoHook,
	ContributionsLineEndingHook,
	DifferenceEngineViewHeaderHook,
	EditPageBeforeEditButtonsHook,
	EditPageGetCheckboxesDefinitionHook,
	EditPageNoSuchSectionHook,
	GetPreferencesHook,
	InfoActionHook,
	InitializeArticleMaybeRedirectHook,
	MakeGlobalVariablesScriptHook,
	NewDifferenceEngineHook,
	PageHistoryBeforeListHook,
	PageHistoryLineEndingHook,
	PageHistoryPager__getQueryInfoHook,
	ProtectionFormAddFormFieldsHook,
	ProtectionForm__saveHook,
	ProtectionForm__showLogExtractHook,
	SkinAfterContentHook,
	SkinTemplateNavigation__UniversalHook,
	SpecialNewpagesConditionsHook,
	SpecialNewPagesFiltersHook,
	SpecialPage_initListHook,
	TitleGetEditNoticesHook
{
	/**
	 * Add FlaggedRevs css/js.
	 *
	 * @param OutputPage $out
	 */
	private static function injectStyleAndJS( OutputPage $out ) {
		if ( !$out->getTitle()->canExist() ) {
			return;
		}
		$fa = FlaggableWikiPage::getTitleInstance( $out->getTitle() );
		// Try to only add to relevant pages
		if ( !$fa || !$fa->isReviewable() ) {
			return;
		}
		// Add main CSS & JS files
		$out->addModuleStyles( 'ext.flaggedRevs.basic' );
		$out->addModules( 'ext.flaggedRevs.advanced' );
		// Add review form and edit page CSS and JS for reviewers
		if ( MediaWikiServices::getInstance()->getPermissionManager()
			->userHasRight( $out->getUser(), 'review' )
		) {
			$out->addModuleStyles( 'codex-styles' );
			$out->addModules( 'ext.flaggedRevs.review' );
		} else {
			$out->addModuleStyles( 'mediawiki.codex.messagebox.styles' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onMakeGlobalVariablesScript( &$vars, $out ): void {
		// Get the review tags on this wiki
		$levels = FlaggedRevs::getMaxLevel();
		if ( $levels > 0 ) {
			$vars['wgFlaggedRevsParams'] = [
				'tags' => [
					FlaggedRevs::getTagName() => [ 'levels' => $levels ]
				],
			];
		}

		// Get page-specific meta-data
		$title = $out->getTitle();
		$fa = $title->canExist() ? FlaggableWikiPage::getTitleInstance( $title ) : null;

		// Try to only add to relevant pages
		if ( $fa && $fa->isReviewable() ) {
			$frev = $fa->getStableRev();
			$vars['wgStableRevisionId'] = $frev ? $frev->getRevId() : 0;
		}
	}

	/**
	 * Add FlaggedRevs CSS for relevant special pages.
	 *
	 * @param OutputPage $out
	 */
	private static function injectStyleForSpecial( OutputPage $out ) {
		$title = $out->getTitle();
		$specialPagesWithAdvanced = [ 'PendingChanges', 'ConfiguredPages', 'UnreviewedPages' ];
		$specialPages = array_merge( $specialPagesWithAdvanced,
			[ 'Watchlist', 'Recentchanges', 'Contributions', 'Recentchangeslinked' ] );

		foreach ( $specialPages as $key ) {
			if ( $title->isSpecial( $key ) ) {
				$out->addModuleStyles( 'ext.flaggedRevs.basic' ); // CSS only
				$out->addModuleStyles( 'codex-styles' );

				if ( in_array( $key, $specialPagesWithAdvanced ) ) {
					$out->addModules( 'ext.flaggedRevs.advanced' );
				}
				break;
			}
		}
	}

	/**
	 * @inheritDoc
	 * Add tag notice, CSS/JS, protect form link, and set robots policy.
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( $out->getTitle()->getNamespace() === NS_SPECIAL ) {
			self::maybeAddBacklogNotice( $out ); // RC/Watchlist notice
			self::injectStyleForSpecial( $out ); // try special page CSS
		} elseif ( $out->getTitle()->canExist() ) {
			$view = FlaggablePageView::newFromTitle( $out->getTitle() );
			$view->addStabilizationLink(); // link on protect form
			$view->displayTag(); // show notice bar/icon in subtitle
			if ( $out->isArticleRelated() ) {
				// Only use this hook if we want to prepend the form.
				// We prepend the form for diffs, so only handle that case here.
				if ( $view->diffRevRecordsAreSet() ) {
					$view->addReviewForm( $out ); // form to be prepended
				}
			}
			$view->setRobotPolicy(); // set indexing policy
			self::injectStyleAndJS( $out ); // full CSS/JS
		}
	}

	/**
	 * @inheritDoc
	 * Add user preferences (uses prefs-flaggedrevs, prefs-flaggedrevs-ui msgs)
	 */
	public function onGetPreferences( $user, &$preferences ) {
		// Box or bar UI
		$preferences['flaggedrevssimpleui'] =
			[
				'type' => 'radio',
				'section' => 'rc/flaggedrevs-ui',
				'label-message' => 'flaggedrevs-pref-UI',
				'options-messages' => [
					'flaggedrevs-pref-UI-0' => 0,
					'flaggedrevs-pref-UI-1' => 1,
				],
			];
		// Default versions...
		$preferences['flaggedrevsstable'] =
			[
				'type' => 'radio',
				'section' => 'rc/flaggedrevs-ui',
				'label-message' => 'flaggedrevs-prefs-stable',
				'options-messages' => [
					'flaggedrevs-pref-stable-0' => FR_SHOW_STABLE_DEFAULT,
					'flaggedrevs-pref-stable-1' => FR_SHOW_STABLE_ALWAYS,
					'flaggedrevs-pref-stable-2' => FR_SHOW_STABLE_NEVER,
				],
			];
		// Review-related rights...
		if ( MediaWikiServices::getInstance()->getPermissionManager()
			->userHasRight( $user, 'review' )
		) {
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
	}

	/**
	 * @inheritDoc
	 * Vector et al: $links is all the tabs (2 levels)
	 */
	public function onSkinTemplateNavigation__Universal( $skin, &$links ): void {
		if ( $skin->getTitle()->canExist() ) {
			$view = FlaggablePageView::newFromTitle( $skin->getTitle() );
			$view->setActionTabs( $links['actions'] );
			$view->setViewTabs( $skin, $links['views'] );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onArticleViewHeader( $article, &$outputDone, &$useParserCache ) {
		if ( $article->getTitle()->canExist() ) {
			$view = FlaggablePageView::newFromTitle( $article->getTitle() );
			$view->addStableLink();
			$view->setPageContent( $outputDone, $useParserCache );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onInitializeArticleMaybeRedirect(
		$title,
		$request,
		&$ignoreRedirect,
		&$target,
		&$article
	) {
		global $wgParserCacheExpireTime;
		$wikiPage = $article->getPage();

		$fa = FlaggableWikiPage::getTitleInstance( $title );
		if ( !$fa->isReviewable() ) {
			return;
		}
		# Viewing an old reviewed version...
		if ( $request->getVal( 'stableid' ) ) {
			$ignoreRedirect = true; // don't redirect (same as ?oldid=x)
			return;
		}
		$srev = $fa->getStableRev();
		$view = FlaggablePageView::newFromTitle( $title );
		# Check if we are viewing an unsynced stable version...
		# (Make sure that nothing in this code calls WebRequest::getActionName(): T323254)
		if ( $srev && $view->showingStable() && $srev->getRevId() != $wikiPage->getLatest() ) {
			# Check the stable redirect properties from the cache...
			$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
			$stableRedirect = $cache->getWithSetCallback(
				$cache->makeKey( 'flaggedrevs-stable-redirect', $wikiPage->getId() ),
				$wgParserCacheExpireTime,
				static function () use ( $fa, $srev ) {
					$content = $srev->getRevisionRecord()
						->getContent( SlotRecord::MAIN );

					return $fa->getRedirectURL( $content->getRedirectTarget() ) ?: '';
				},
				[
					'touchedCallback' => static function () use ( $wikiPage ) {
						return wfTimestampOrNull( TS_UNIX, $wikiPage->getTouched() );
					}
				]
			);
			if ( $stableRedirect ) {
				$target = $stableRedirect; // use stable redirect
			} else {
				$ignoreRedirect = true; // make MW skip redirection
			}
			$clearEnvironment = (bool)$target;
		# Check if the we are viewing a draft or synced stable version...
		} else {
			# In both cases, we can just let MW use followRedirect()
			# on the draft as normal, avoiding any page text hits.
			$clearEnvironment = $wikiPage->isRedirect();
		}
		# Environment will change in MediaWiki::initializeArticle
		if ( $clearEnvironment ) {
			$view->clear();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onTitleGetEditNotices( $title, $oldid, &$notices ) {
		if ( $title->canExist() ) {
			$view = FlaggablePageView::newFromTitle( $title );
			$view->getEditNotices( $title, $oldid, $notices );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onEditPageBeforeEditButtons( $editPage, &$buttons, &$tabindex ) {
		if ( $editPage->getTitle()->canExist() ) {
			$view = FlaggablePageView::newFromTitle( $editPage->getTitle() );
			$view->changeSaveButton( $editPage, $buttons );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onEditPageNoSuchSection( $editPage, &$s ) {
		if ( $editPage->getTitle()->canExist() ) {
			$view = FlaggablePageView::newFromTitle( $editPage->getTitle() );
			$view->addToNoSuchSection( $s );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onPageHistoryBeforeList( $article, $context ) {
		if ( $article->getTitle()->canExist() ) {
			$view = FlaggablePageView::newFromTitle( $article->getTitle() );
			$view->addToHistView();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onCategoryPageView( $category ) {
		if ( $category->getTitle()->canExist() ) {
			$view = FlaggablePageView::newFromTitle( $category->getTitle() );
			$view->addToCategoryView();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onSkinAfterContent( &$data, $skin ) {
		if ( $skin->getOutput()->isArticleRelated()
			&& $skin->getTitle()->canExist()
		) {
			$view = FlaggablePageView::newFromTitle( $skin->getTitle() );
			// Only use this hook if we want to append the form.
			// We *prepend* the form for diffs, so skip that case here.
			if ( !$view->diffRevRecordsAreSet() ) {
				$view->addReviewForm( $data ); // form to be appended
			}
		}
	}

	/**
	 * @inheritDoc
	 * Registers a filter on Special:NewPages to hide edits that have been reviewed
	 * through FlaggedRevs.
	 */
	public function onSpecialNewPagesFilters( $specialPage, &$filters ) {
		if ( !FlaggedRevs::useOnlyIfProtected() ) {
			$filters['hideReviewed'] = [
				'msg' => 'flaggedrevs-hidereviewed', 'default' => false
			];
		}
	}

	/**
	 * @inheritDoc
	 * Registers a filter to hide edits that have been reviewed through
	 * FlaggedRevs.
	 */
	public function onChangesListSpecialPageStructuredFilters( $specialPage ) {
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			return;
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
							self::hideReviewedChangesUnconditionally(
								$conds, $dbr
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
						'isRowApplicableCallable' => static function ( $ctx, $rc ) {
							return ( FlaggedRevs::isReviewNamespace( $rc->getAttribute( 'rc_namespace' ) ) &&
									$rc->getAttribute( 'rc_type' ) !== RC_EXTERNAL ) &&
								(
									!$rc->getAttribute( 'fp_stable' ) ||
									(
										// The rc_timestamp >= fp_pending_since condition implies that
										// fp_pending_since is not null, because all comparisons with null
										// values are false in MySQL. It doesn't work that way in PHP,
										// so we have to explicitly check that fp_pending_since is not null
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
						'isRowApplicableCallable' => static function ( $ctx, $rc ) {
							return ( FlaggedRevs::isReviewNamespace( $rc->getAttribute( 'rc_namespace' ) ) &&
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
						'isRowApplicableCallable' => static function ( $ctx, $rc ) {
							return !FlaggedRevs::isReviewNamespace( $rc->getAttribute( 'rc_namespace' ) );
						}
					],
				],
				'queryCallable' => static function ( $specialClassName, $ctx, $dbr, &$tables,
					&$fields, &$conds, &$query_options, &$join_conds, $selectedValues
				) {
					if ( !$selectedValues || count( $selectedValues ) > 2 ) {
						// Nothing/everything was selected, no filter needed
						return;
					}

					$namespaces = FlaggedRevs::getReviewNamespaces();
					$needReviewCond = $dbr->expr( 'fp_stable', '=', null )
						->orExpr( new RawSQLExpression( 'rc_timestamp >= fp_pending_since' ) );
					$reviewedCond = $dbr->expr( 'fp_stable', '!=', null )
						->andExpr(
							$dbr->expr( 'fp_pending_since', '=', null )
								->orExpr( new RawSQLExpression( 'rc_timestamp < fp_pending_since' ) )
						);
					$notReviewableCond = $dbr->expr( 'rc_namespace', '!=', $namespaces )
						->or( 'rc_type', '=', RC_EXTERNAL );
					$reviewableCond = $dbr->expr( 'rc_namespace', '=', $namespaces )
						->and( 'rc_type', '!=', RC_EXTERNAL );

					$filters = [];
					if ( in_array( 'needreview', $selectedValues ) ) {
						$filters[] = $needReviewCond;
					}
					if ( in_array( 'reviewed', $selectedValues ) ) {
						$filters[] = $reviewedCond;
					}
					if ( count( $filters ) > 1 ) {
						// Both selected, no filter needed
						$filters = [];
					}

					if ( in_array( 'notreviewable', $selectedValues ) ) {
						$filters[] = $notReviewableCond;
						$conds[] = $dbr->orExpr( $filters );
					} else {
						$filters[] = $reviewableCond;
						$conds[] = $dbr->andExpr( $filters );
					}
				}
			]
		);

		$specialPage->registerFilterGroup( $flaggedRevsGroup );
	}

	/**
	 * @inheritDoc
	 */
	public function onPageHistoryPager__getQueryInfo( $pager, &$queryInfo ) {
		$flaggedArticle = FlaggableWikiPage::getTitleInstance( $pager->getTitle() );
		# Non-content pages cannot be validated. Stable version must exist.
		if ( $flaggedArticle->isReviewable() && $flaggedArticle->getStableRev() ) {
			# Highlight flaggedrevs
			$queryInfo['tables'][] = 'flaggedrevs';
			$queryInfo['fields'][] = 'fr_rev_id';
			$queryInfo['fields'][] = 'fr_user';
			$queryInfo['fields'][] = 'fr_flags';
			$queryInfo['join_conds']['flaggedrevs'] = [ 'LEFT JOIN', "fr_rev_id = rev_id" ];
			# Find reviewer name. Sanity check that no extensions added a `user` query.
			if ( !in_array( 'user', $queryInfo['tables'] ) ) {
				$queryInfo['tables'][] = 'user';
				$queryInfo['fields']['reviewer'] = 'user_name';
				$queryInfo['join_conds']['user'] = [ 'LEFT JOIN', "user_id = fr_user" ];
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onContribsPager__getQueryInfo( $pager, &$queryInfo ) {
		global $wgFlaggedRevsProtection;

		if ( $wgFlaggedRevsProtection ) {
			return;
		}

		# Highlight flaggedrevs
		$queryInfo['tables'][] = 'flaggedrevs';
		$queryInfo['fields'][] = 'fr_rev_id';
		$queryInfo['join_conds']['flaggedrevs'] = [ 'LEFT JOIN', "fr_rev_id = rev_id" ];
		# Highlight unchecked content
		$queryInfo['tables'][] = 'flaggedpages';
		$queryInfo['fields'][] = 'fp_stable';
		$queryInfo['fields'][] = 'fp_pending_since';
		$queryInfo['join_conds']['flaggedpages'] = [ 'LEFT JOIN', "fp_page_id = rev_page" ];
	}

	/**
	 * @inheritDoc
	 */
	public function onSpecialNewpagesConditions(
		$specialPage, $opts, &$conds, &$tables, &$fields, &$join_conds
	) {
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
		self::makeAllQueryChanges( $conds, $tables, $join_conds, $fields, $dbr );
	}

	/**
	 * @inheritDoc
	 */
	public function onChangesListSpecialPageQuery(
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
	 * @param string[] &$fields Fields to query
	 * @param IReadableDatabase $dbr
	 */
	private static function makeAllQueryChanges(
		array &$conds, array &$tables, array &$join_conds, array &$fields, IReadableDatabase $dbr
	) {
		self::addMetadataQueryJoins( $tables, $join_conds, $fields );
		self::hideReviewedChangesIfNeeded( $conds, $dbr );
	}

	/**
	 * Add FlaggedRevs metadata by adding fields and joins
	 *
	 * @param array &$tables Tables to query
	 * @param array &$join_conds Query join conditions
	 * @param string[] &$fields Fields to query
	 */
	private static function addMetadataQueryJoins(
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
	 * @param IReadableDatabase $dbr
	 */
	private static function hideReviewedChangesIfNeeded(
		array &$conds, IReadableDatabase $dbr
	) {
		global $wgRequest;

		if ( $wgRequest->getBool( 'hideReviewed' ) && !FlaggedRevs::useOnlyIfProtected() ) {
			self::hideReviewedChangesUnconditionally( $conds, $dbr );
		}
	}

	/**
	 * Hides reviewed changes unconditionally; assumes you have checked whether to do
	 * so already
	 *
	 * Must already be joined into the FlaggedRevs tables.
	 *
	 * @param array &$conds Query conditions
	 * @param IReadableDatabase $dbr
	 */
	private static function hideReviewedChangesUnconditionally(
		array &$conds, IReadableDatabase $dbr
	) {
		// Don't filter external changes as FlaggedRevisions doesn't apply to those
		$conds[] = $dbr->orExpr( [
			new RawSQLExpression( 'rc_timestamp >= fp_pending_since' ),
			'fp_stable' => null,
			'rc_type' => RC_EXTERNAL,
		] );
	}

	/**
	 * @inheritDoc
	 * @suppress PhanUndeclaredProperty For HistoryPager->fr_*
	 */
	public function onPageHistoryLineEnding( $history, &$row, &$s, &$liClasses, &$attribs ) {
		$fa = FlaggableWikiPage::getTitleInstance( $history->getTitle() );
		if ( !$fa->isReviewable() ) {
			return;
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
			return;
		}
		$title = $history->getTitle();
		$revId = (int)$row->rev_id;
		// Pending revision: highlight and add diff link
		$link = '';
		$class = '';
		if ( wfTimestamp( TS_UNIX, $row->rev_timestamp ) > $history->fr_stableRevUTS ) {
			$class = 'flaggedrevs-pending';
			$link = $history->msg( 'revreview-hist-pending-difflink',
				$title->getPrefixedText(), $history->fr_stableRevId, $revId )->parse();
			$link = '<span class="plainlinks mw-fr-hist-difflink">' . $link . '</span>';
			$history->fr_pendingRevs = true; // pending rev shown above stable
		// Reviewed revision: highlight and add link
		} elseif ( isset( $row->fr_rev_id ) ) {
			if (
				!( $row->rev_deleted & RevisionRecord::DELETED_TEXT )
				&& !( $row->rev_deleted & RevisionRecord::DELETED_USER )
			) {
				# Add link to stable version of *this* rev, if any
				[ $link, $class ] = self::markHistoryRow( $history, $title, $row );
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
	}

	/**
	 * Make stable version link and return the css
	 * @param IContextSource $ctx
	 * @param Title $title
	 * @param stdClass $row from history page
	 * @return string[]
	 */
	private static function markHistoryRow( IContextSource $ctx, Title $title, $row ) {
		if ( !isset( $row->fr_rev_id ) ) {
			return [ "", "" ]; // not reviewed
		}
		$flags = explode( ',', $row->fr_flags );
		if ( in_array( 'auto', $flags ) ) {
			$msg = 'revreview-hist-basic-auto';
			$css = 'fr-hist-basic-auto';
		} else {
			$msg = 'revreview-hist-basic-user';
			$css = 'fr-hist-basic-user';
		}
		if ( isset( $row->reviewer ) ) {
			$name = $row->reviewer;
		} else {
			$reviewer = MediaWikiServices::getInstance()
				->getActorStore()
				->getUserIdentityByUserId( $row->fr_user );
			$name = $reviewer ? $reviewer->getName() : false;
		}
		$link = $ctx->msg( $msg, $title->getPrefixedDBkey(), $row->rev_id, $name )->parse();
		$link = "<span class='$css plainlinks'>[$link]</span>";
		return [ $link, 'flaggedrevs-color-1' ];
	}

	/**
	 * @inheritDoc
	 * Intercept contribution entries and format them to FlaggedRevs standards
	 */
	public function onContributionsLineEnding( $contribs, &$ret, $row, &$classes, &$attribs ) {
		global $wgFlaggedRevsProtection;

		// make sure that we're parsing revisions data
		if ( !$wgFlaggedRevsProtection && isset( $row->rev_id ) ) {
			if ( !FlaggedRevs::isReviewNamespace( $row->page_namespace ) ) {
				// do nothing
			} elseif ( isset( $row->fr_rev_id ) ) {
				$classes[] = 'flaggedrevs-color-1';
			} elseif ( isset( $row->fp_pending_since )
				&& $row->rev_timestamp >= $row->fp_pending_since // bug 15515
			) {
				$classes[] = 'flaggedrevs-pending';
			} elseif ( !isset( $row->fp_stable ) ) {
				$classes[] = 'flaggedrevs-unreviewed';
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onChangesListInsertArticleLink(
		$list,
		&$articlelink,
		&$s,
		$rc,
		$unpatrolled,
		$watched
	) {
		$page = $rc->getPage();
		if ( !$page || !FlaggedRevs::inReviewNamespace( $page )
			|| !$rc->getAttribute( 'rc_this_oldid' ) // rev, not log
			|| !array_key_exists( 'fp_stable', $rc->getAttributes() )
		) {
			// Confirm that page is in reviewable namespace
			return;
		}
		$rlink = '';
		$css = '';
		// page is not reviewed
		if ( $rc->getAttribute( 'fp_stable' ) == null ) {
			// Is this a config were pages start off reviewable?
			// Hide notice from non-reviewers due to vandalism concerns (bug 24002).
			if ( !FlaggedRevs::useOnlyIfProtected() && MediaWikiServices::getInstance()
					->getPermissionManager()
					->userHasRight( $list->getUser(), 'review' )
			) {
				$rlink = $list->msg( 'revreview-unreviewedpage' )->escaped();
				$css = 'flaggedrevs-unreviewed';
			}
		// page is reviewed and has pending edits (use timestamps; bug 15515)
		} elseif ( $rc->getAttribute( 'fp_pending_since' ) !== null &&
			$rc->getAttribute( 'rc_timestamp' ) >= $rc->getAttribute( 'fp_pending_since' )
		) {
			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
			$rlink = $linkRenderer->makeLink(
				$page,
				$list->msg( 'revreview-reviewlink' )->text(),
				[ 'title' => $list->msg( 'revreview-reviewlink-title' )->text() ],
				[ 'oldid' => $rc->getAttribute( 'fp_stable' ), 'diff' => 'cur' ]
			);
			$css = 'flaggedrevs-pending';
		}
		if ( $rlink != '' ) {
			$articlelink .= " <span class=\"mw-fr-reviewlink $css\">[$rlink]</span>";
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onArticleUpdateBeforeRedirect( $article, &$sectionAnchor, &$extraQuery ) {
		if ( $article->getTitle()->canExist() ) {
			$view = FlaggablePageView::newFromTitle( $article->getTitle() );
			$view->injectPostEditURLParams( $sectionAnchor, $extraQuery );
		}
	}

	/**
	 * @inheritDoc
	 * diff=review param (bug 16923)
	 */
	public function onNewDifferenceEngine( $titleObj, &$mOldid, &$mNewid, $old, $new ) {
		if ( $new === 'review' && $titleObj ) {
			$sRevId = FlaggedRevision::getStableRevId( $titleObj );
			if ( $sRevId ) {
				$mOldid = $sRevId; // stable
				$mNewid = 0; // cur
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onDifferenceEngineViewHeader( $diff ) {
		self::injectStyleAndJS( $diff->getOutput() );

		if ( $diff->getTitle()->canExist() ) {
			$view = FlaggablePageView::newFromTitle( $diff->getTitle() );

			$oldRevRecord = $diff->getOldRevision();
			$newRevRecord = $diff->getNewRevision();
			$view->setViewFlags( $diff, $oldRevRecord, $newRevRecord );
			$view->addToDiffView( $oldRevRecord, $newRevRecord );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onEditPageGetCheckboxesDefinition( $editPage, &$checkboxes ) {
		if ( $editPage->getTitle()->canExist() ) {
			$view = FlaggablePageView::newFromTitle( $editPage->getTitle() );
			$view->addReviewCheck( $editPage, $checkboxes );
		}
	}

	/**
	 * @param OutputPage $out
	 */
	private static function maybeAddBacklogNotice( OutputPage $out ) {
		if ( !MediaWikiServices::getInstance()->getPermissionManager()
			->userHasRight( $out->getUser(), 'review' ) ) {
			// Not relevant to user
			return;
		}
		$namespaces = FlaggedRevs::getReviewNamespaces();
		$watchlist = SpecialPage::getTitleFor( 'Watchlist' );
		# Add notice to watchlist about pending changes...
		if ( $out->getTitle()->equals( $watchlist ) && $namespaces ) {
			$dbr = MediaWikiServices::getInstance()->getConnectionProvider()
				->getReplicaDatabase( false, 'watchlist' ); // consistency with watchlist
			$watchedOutdated = (bool)$dbr->newSelectQueryBuilder()
				->select( '1' ) // existence
				->from( 'watchlist' )
				->join( 'page', null, [
					'wl_namespace = page_namespace',
					'wl_title = page_title',
				] )
				->join( 'flaggedpages', null, 'fp_page_id = page_id' )
				->where( [
					'wl_user' => $out->getUser()->getId(), // this user
					'wl_namespace' => $namespaces, // reviewable
					$dbr->expr( 'fp_pending_since', '!=', null ), // edits pending
				] )
				->caller( __METHOD__ )
				->fetchField();
			# Give a notice if pages on the users's wachlist have pending edits
			if ( $watchedOutdated ) {
				$out->prependHTML(
					Html::warningBox( $out->msg( 'flaggedrevs-watched-pending' )->parse(),
						'mw-fr-watchlist-pending-notice' )
				);
			}
		}
	}

	/**
	 * @inheritDoc
	 * Add selector of review "protection" options
	 */
	public function onProtectionFormAddFormFields( $article, &$fields ) {
		global $wgFlaggedRevsProtection;

		$wikiPage = $article->getPage();
		$title = $wikiPage->getTitle();
		$context = $article->getContext();

		if (
			!$wgFlaggedRevsProtection
			|| !$wikiPage->exists()
			|| !FlaggedRevs::inReviewNamespace( $wikiPage )
		) {
			return;
		}

		$user = $context->getUser();
		$request = $context->getRequest();
		$mode = $request->wasPosted() ? IDBAccessObject::READ_LATEST : 0;
		$form = new PageStabilityProtectForm( $user );
		$form->setTitle( $title );

		$config = FRPageConfig::getStabilitySettings( $title, $mode );
		$expirySelect = $request->getVal(
			'mwStabilizeExpirySelection',
			$config['expiry'] == 'infinity' ? 'infinite' : 'existing'
		);
		$isAllowed = $form->isAllowed();

		$expiryOther = $request->getVal( 'mwStabilizeExpiryOther' );
		if ( $expiryOther ) {
			$expirySelect = 'othertime'; // mutual exclusion
		}

		# Get and add restriction levels to an array
		$effectiveLevels = [ 'none', ...FlaggedRevs::getRestrictionLevels() ];
		$options = [];
		foreach ( $effectiveLevels as $limit ) {
			$msg = $context->msg( 'flaggedrevs-protect-' . $limit );
			// Default to the key itself if no UI message
			$options[$msg->isDisabled() ? 'flaggedrevs-protect-' . $limit : $msg->text()] = $limit;
		}

		# Get and add expiry options to an array
		$scExpiryOptions = wfMessage( 'protect-expiry-options' )->inContentLanguage()->text();
		$expiryOptions = [];

		if ( $config['expiry'] != 'infinity' ) {
			$lang = $context->getLanguage();
			$timestamp = $lang->userTimeAndDate( $config['expiry'], $user );
			$date = $lang->userDate( $config['expiry'], $user );
			$time = $lang->userTime( $config['expiry'], $user );
			$existingExpiryMessage = $context->msg( 'protect-existing-expiry', $timestamp, $date, $time );
			$expiryOptions[$existingExpiryMessage->text()] = 'existing';
		}

		$expiryOptions[$context->msg( 'protect-othertime-op' )->text()] = 'othertime';

		foreach ( explode( ',', $scExpiryOptions ) as $option ) {
			$pair = explode( ':', $option, 2 );
			$expiryOptions[$pair[0]] = $pair[1] ?? $pair[0];
		}

		# Create restriction level select
		$fields['mwStabilityLevel'] = [
			'type' => 'select',
			'name' => 'mwStabilityLevel',
			'id' => 'mwStabilityLevel',
			'disabled' => !$isAllowed,
			'options' => $options,
			'default' => $request->getVal( 'mwStabilityLevel', FRPageConfig::getProtectionLevel( $config ) ),
			'section' => 'flaggedrevs-protect-legend',
		];

		# Create expiry options select
		if ( $scExpiryOptions !== '-' ) {
			$fields['mwStabilizeExpirySelection'] = [
				'type' => 'select',
				'name' => 'mwStabilizeExpirySelection',
				'id' => 'mwStabilizeExpirySelection',
				'disabled' => !$isAllowed,
				'label' => $context->msg( 'stabilization-expiry' )->text(),
				'options' => $expiryOptions,
				'default' => $expirySelect,
				'section' => 'flaggedrevs-protect-legend',
			];
		}

		# Create other expiry time input
		if ( $isAllowed ) {
			$fields['mwStabilizeExpiryOther'] = [
				'type' => 'text',
				'name' => 'mwStabilizeExpiryOther',
				'id' => 'mwStabilizeExpiryOther',
				'label' => $context->msg( 'stabilization-othertime' )->text(),
				'default' => $expiryOther,
				'section' => 'flaggedrevs-protect-legend'
			];
		}

		# Add some javascript for expiry dropdowns
		$context->getOutput()->addInlineScript( ResourceLoader::makeInlineCodeWithModule( 'oojs-ui-core', "
			var changeExpiryDropdown = OO.ui.infuse( $( '#mwStabilizeExpirySelection' ) ),
				changeExpiryInput = OO.ui.infuse( $( '#mwStabilizeExpiryOther' ) );

			changeExpiryDropdown.on( 'change', function ( val ) {
				if ( val !== 'othertime' ) {
					changeExpiryInput.setValue( '' );
				}
			} );

			changeExpiryInput.on( 'change', function ( val ) {
				if ( val ) {
					changeExpiryDropdown.setValue( 'othertime' );
				}
			} );
		" ) );
	}

	/**
	 * @inheritDoc
	 * Add stability log extract to protection form
	 */
	public function onProtectionForm__showLogExtract(
		$article,
		$out
	) {
		global $wgFlaggedRevsProtection;
		$wikiPage = $article->getPage();
		$title = $wikiPage->getTitle();

		if (
			!$wgFlaggedRevsProtection
			|| !$wikiPage->exists()
			|| !FlaggedRevs::inReviewNamespace( $wikiPage )
		) {
			return;
		}

		# Show relevant lines from the stability log:
		$logPage = new LogPage( 'stable' );
		$out->addHTML( Html::element( 'h2', [], $logPage->getName()->text() ) );
		LogEventsList::showLogExtract( $out, 'stable', $title->getPrefixedText() );
	}

	/**
	 * @inheritDoc
	 * Update stability config from request
	 */
	public function onProtectionForm__save( $article, &$errorMsg, $reasonstr ) {
		global $wgRequest, $wgFlaggedRevsProtection;
		$wikiPage = $article->getPage();
		$title = $wikiPage->getTitle();
		$user = $article->getContext()->getUser();

		if (
			!$wgFlaggedRevsProtection
			|| !$wikiPage->exists() // simple custom levels set for action=protect
			|| !FlaggedRevs::inReviewNamespace( $wikiPage )
		) {
			return;
		}

		$services = MediaWikiServices::getInstance();
		if ( $services->getReadOnlyMode()->isReadOnly() || !$services->getPermissionManager()
				->userHasRight( $user, 'stablesettings' )
		) {
			// User cannot change anything
			return;
		}
		$form = new PageStabilityProtectForm( $user );
		$form->setTitle( $title ); // target page
		$permission = (string)$wgRequest->getVal( 'mwStabilityLevel', '' );
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
	}

	/**
	 * @inheritDoc
	 */
	public function onSpecialPage_initList( &$list ) {
		global $wgFlaggedRevsProtection, $wgFlaggedRevsNamespaces;

		// Show special pages only if FlaggedRevs is enabled on some namespaces
		if ( $wgFlaggedRevsNamespaces ) {
			$list['RevisionReview'] = RevisionReview::class; // unlisted
			$list['PendingChanges'] = PendingChanges::class;
			$list['ValidationStatistics'] = ValidationStatistics::class;
			// Protect levels define allowed stability settings
			if ( $wgFlaggedRevsProtection ) {
				$list['StablePages'] = StablePages::class;
			} else {
				$list['ConfiguredPages'] = ConfiguredPages::class;
				$list['UnreviewedPages'] = UnreviewedPages::class;
				$list['Stabilization'] = Stabilization::class; // unlisted
			}
		}
	}

	/**
	 * Adds list of translcuded pages waiting for review to action=info
	 *
	 * @param IContextSource $context
	 * @param array[] &$pageInfo
	 */
	public function onInfoAction( $context, &$pageInfo ) {
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_CURRENT ) {
			return; // short-circuit
		}
		if ( !$context->getTitle()->exists() ) {
			return;
		}
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$linksMigration = MediaWikiServices::getInstance()->getLinksMigration();
		[ $nsField, $titleField ] = $linksMigration->getTitleFields( 'templatelinks' );
		$queryInfo = $linksMigration->getQueryInfo( 'templatelinks' );
		// Keep it in sync with FlaggedRevision::findPendingTemplateChanges()
		$ret = $dbr->newSelectQueryBuilder()
			->select( [ $nsField, $titleField ] )
			->tables( $queryInfo['tables'] )
			->leftJoin( 'page', null, [ "page_namespace = $nsField", "page_title = $titleField" ] )
			->join( 'flaggedpages', null, 'fp_page_id = page_id' )
			->where( [
				'tl_from' => $context->getTitle()->getArticleID(),
				$dbr->expr( 'fp_pending_since', '!=', null )->or( 'fp_stable', '=', null ),
			] )
			->joinConds( $queryInfo['joins'] )
			->caller( __METHOD__ )
			->fetchResultSet();
		$titles = [];
		foreach ( $ret as $row ) {
			$titleValue = new TitleValue( (int)$row->$nsField, $row->$titleField );
			$titles[] = MediaWikiServices::getInstance()->getLinkRenderer()->makeLink( $titleValue );
		}
		if ( $titles ) {
			$valueHTML = Html::openElement( 'ul' );
			foreach ( $titles as $title ) {
				$valueHTML .= Html::rawElement( 'li', [], $title );
			}
			$valueHTML .= Html::closeElement( 'ul' );
		} else {
			$valueHTML = $context->msg( 'flaggedrevs-action-info-pages-waiting-for-review-none' )->parse();
		}

		$pageInfo['header-properties'][] = [
			$context->msg( 'flaggedrevs-action-info-pages-waiting-for-review' ),
			$valueHTML
		];
	}
}
