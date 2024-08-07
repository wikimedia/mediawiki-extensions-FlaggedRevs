<?php

use MediaWiki\Cache\CacheKeyHelper;
use MediaWiki\Context\ContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\EditPage\EditPage;
use MediaWiki\HookContainer\HookRunner;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Request\WebRequest;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

/**
 * Class representing a web view of a MediaWiki page
 */
class FlaggablePageView extends ContextSource {
	private static ?MapCacheLRU $instances = null;

	private OutputPage $out;
	private FlaggableWikiPage $article;
	/** @var RevisionRecord[]|null Array of `old` and `new` RevisionsRecords for diffs */
	private ?array $diffRevRecords = null;
	private bool $isReviewableDiff = false;
	private bool $isDiffFromStable = false;
	private bool $isMultiPageDiff = false;
	private string $reviewNotice = '';
	private string $diffNoticeBox = '';
	private string $diffIncChangeBox = '';
	private ?RevisionRecord $reviewFormRevRecord = null;

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
	 * @return self
	 */
	public static function newFromTitle( PageIdentity $title ): FlaggablePageView {
		$cache = self::getInstanceCache();
		$key = CacheKeyHelper::getKeyForPage( $title );
		$view = $cache->get( $key );
		if ( !$view ) {
			$view = new self( $title );
			$cache->set( $key, $view );
		}
		return $view;
	}

	/**
	 * Get the FlaggablePageView for this request
	 *
	 * @deprecated Use ::newFromTitle() instead
	 * @return self
	 */
	public static function singleton(): FlaggablePageView {
		return self::newFromTitle( RequestContext::getMain()->getTitle() );
	}

	/**
	 * @param Title|PageIdentity $title
	 */
	private function __construct( PageIdentity $title ) {
		// Title is injected (a step up from everything being global), but
		// the rest is still implicitly uses RequestContext::getMain()
		// via parent class ContextSource::getContext().
		// TODO: Inject $context and call setContext() here.

		if ( !$title->canExist() ) {
			throw new InvalidArgumentException( 'FlaggablePageView needs a proper page' );
		}
		$this->article = FlaggableWikiPage::getTitleInstance( $title );
		$this->out = $this->getOutput(); // convenience
	}

	private function __clone() {
	}

	/**
	 * Clear the FlaggablePageView for this request.
	 * Only needed when page redirection changes the environment.
	 */
	public function clear(): void {
		self::$instances = null;
	}

	/**
	 * Get the FlaggableWikiPage instance associated with the current page title,
	 * or null if there isn't such a title
	 *
	 * @deprecated Use FlaggableWikiPage::getTitleInstance() instead
	 */
	public static function globalArticleInstance(): ?FlaggableWikiPage {
		$title = RequestContext::getMain()->getTitle();
		if ( $title && $title->canExist() ) {
			return FlaggableWikiPage::getTitleInstance( $title );
		}
		return null;
	}

	/**
	 * Check if the old and new diff revs are set for this page view
	 */
	public function diffRevRecordsAreSet(): bool {
		return (bool)$this->diffRevRecords;
	}

	/**
	 * Assuming that the current request is a page view (see isPageView()),
	 * check if a stable version exists and should be displayed.
	 */
	public function showingStable(): bool {
		$request = $this->getRequest();

		$canShowStable = (
			// Page is reviewable and has a stable version
			$this->article->getStableRev() &&
			// No parameters requesting a different version of the page
			!$request->getCheck( 'oldid' ) && !$request->getCheck( 'stableid' )
		);
		if ( !$canShowStable ) {
			return false;
		}

		// Check if a stable or unstable version is explicitly requested (?stable=1 or ?stable=0).
		$stableQuery = $request->getIntOrNull( 'stable' );
		if ( $stableQuery !== null ) {
			return $stableQuery === 1;
		}

		// Otherwise follow site/page config and user preferences.
		$reqUser = $this->getUser();
		$defaultForUser = $this->getPageViewStabilityModeForUser( $reqUser );
		$stableDefault = (
			// User is not configured to prefer current versions
			$defaultForUser !== FR_SHOW_STABLE_NEVER &&
			// User explicitly prefers stable versions of pages
			(
				$defaultForUser === FR_SHOW_STABLE_ALWAYS ||
				// Check if the stable version overrides the draft
				$this->article->getStabilitySettings()['override']
			)
		);
		return $stableDefault;
	}

	/**
	 * Should this be using a simple icon-based UI?
	 * Check the user's preferences first, using the site settings as the default.
	 */
	private function useSimpleUI(): bool {
		$default = (int)$this->getConfig()->get( 'SimpleFlaggedRevsUI' );
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		return (bool)$userOptionsLookup->getOption(
			$this->getUser(),
			'flaggedrevssimpleui',
			$default
		);
	}

	/**
	 * What version of pages should this user see by default?
	 * @return int One of the FR_SHOW_STABLE_* constants
	 */
	private function getPageViewStabilityModeForUser( User $user ): int {
		# Check user preferences (e.g. "show stable version by default?")
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		$preference = (int)$userOptionsLookup->getOption( $user, 'flaggedrevsstable' );
		if ( $preference === FR_SHOW_STABLE_ALWAYS || $preference === FR_SHOW_STABLE_NEVER ) {
			return $preference;
		}
		return $user->isRegistered() ? FR_SHOW_STABLE_NEVER : FR_SHOW_STABLE_DEFAULT;
	}

	/**
	 * Is this a view page action (including diffs)?
	 */
	private function isPageViewOrDiff(): bool {
		$action = $this->getActionName();
		return $action === 'view' || $action === 'render';
	}

	/**
	 * Is this a view page action (not including diffs)?
	 */
	private function isPageView(): bool {
		$request = $this->getRequest();
		return $this->isPageViewOrDiff()
			&& $request->getVal( 'diff' ) === null;
	}

	/**
	 * Output review notice
	 */
	public function displayTag(): void {
		// Sanity check that this is a reviewable page
		if ( $this->article->isReviewable() && $this->reviewNotice ) {
			$this->out->addSubtitle( $this->reviewNotice );
		}
	}

	/**
	 * Add a stable link when viewing old versions of an article that
	 * have been reviewed. (e.g. for &oldid=x urls)
	 */
	public function addStableLink(): void {
		$request = $this->getRequest();
		if ( !$this->article->isReviewable() ||
			!$request->getVal( 'oldid' ) ||
			$this->out->isPrintable()
		) {
			return;
		}

		# We may have nav links like "direction=prev&oldid=x"
		$revID = $this->getOldIDFromRequest();
		$frev = FlaggedRevision::newFromTitle( $this->article->getTitle(), $revID );
		if ( !$frev ) {
			return;
		}

		# Give a notice if this rev ID corresponds to a reviewed version...
		$time = $this->getLanguage()->date( $frev->getTimestamp(), true );
		$this->out->addHTML(
			Html::rawElement( 'div', [
				'id' => 'mw-fr-revisiontag-old',
				'class' => 'cdx-message cdx-message--block cdx-message--notice flaggedrevs_notice plainlinks noprint',
				'aria-live' => 'polite'
			],
				Html::element( 'span', [ 'class' => 'cdx-message__icon' ] ) .
				Html::rawElement( 'div', [ 'class' => 'cdx-message__content' ],
					$this->msg( 'revreview-basic-source', $frev->getRevId(), $time )->parse()
				)
			)
		);
	}

	/**
	 * Replaces a page with the last stable version if possible
	 * Adds stable version status/info tags and notes
	 * Adds a quick review form on the bottom if needed
	 * @param bool|ParserOutput|null &$outputDone
	 * @param bool &$useParserCache
	 */
	public function setPageContent( &$outputDone, &$useParserCache ): void {
		$request = $this->getRequest();
		# Only trigger on page views with no oldid=x param
		if ( !$this->isPageView() || $request->getVal( 'oldid' ) ) {
			return;
		# Only trigger for reviewable pages that exist
		} elseif ( !$this->article->exists() || !$this->article->isReviewable() ) {
			return;
		}
		$tag = ''; // review tag box/bar message
		$old = false;
		$stable = false;
		// Initialize $tagTypeClass
		$tagTypeClass = '';
		// Check the newest stable version.
		$srev = $this->article->getStableRev();
		$stableId = $srev ? $srev->getRevId() : 0;
		$frev = $srev; // $frev is the revision we are looking at
		# Check for any explicitly requested reviewed version (stableid=X)...
		$reqId = $this->getRequest()->getVal( 'stableid' );
		if ( $reqId === "best" ) {
			$reqId = $this->article->getBestFlaggedRevId();
		}
		if ( $reqId ) {
			if ( !$stableId ) {
				$reqId = false; // must be invalid
			# Treat requesting the stable version by ID as &stable=1
			} elseif ( $stableId == $reqId ) {
				$stable = true; // stable version requested by ID
			} else {
				$old = true; // old reviewed version requested by ID
				$frev = FlaggedRevision::newFromTitle( $this->article->getTitle(), $reqId );
				if ( !$frev ) {
					$reqId = false; // invalid ID given
				}
			}
		}
		// $reqId is null if nothing requested, false if invalid
		if ( $reqId === false ) {
			$this->out->addWikiMsg( 'revreview-invalid' );
			$this->out->returnToMain( false, $this->article->getTitle() );
			# Tell MW that parser output is done
			$outputDone = true;
			$useParserCache = false;
			return;
		}
		// Is the page config altered?
		$this->enableIcons();
		$prot = FlaggedRevsXML::lockStatusIcon( $this->article );

		if ( $frev ) {
			// has stable version?
			// Looking at some specific old stable revision ("&stableid=x")
			// set to override given the relevant conditions. If the user is
			// requesting the stable revision ("&stableid=x"), defer to override
			// behavior below, since it is the same as ("&stable=1").
			if ( $old ) {
				# Tell MW that parser output is done by setting $outputDone
				$outputDone = $this->showOldReviewedVersion( $frev, $tag, $prot );
				$useParserCache = false;
				$tagTypeClass = 'flaggedrevs_oldstable';
			} elseif ( $stable || ( $this->isPageView() && $this->showingStable() ) ) {
				// Check if $srev is not null before calling showStableVersion
				if ( $srev !== null ) {
					# Tell MW that parser output is done by setting $outputDone
					$outputDone = $this->showStableVersion( $srev, $tag, $prot );
					$useParserCache = false;
					$tagTypeClass = ( $this->article->stableVersionIsSynced() ) ?
						'flaggedrevs_stable_synced' : 'flaggedrevs_stable_notsynced';
				}
			} elseif ( $srev !== null ) {
				// Check if $srev is not null before calling showDraftVersion
				$this->showDraftVersion( $srev, $tag, $prot );
				$tagTypeClass = ( $this->article->stableVersionIsSynced() ) ?
					'flaggedrevs_draft_synced' : 'flaggedrevs_draft_notsynced';
			}
		} else {
			// Looking at a page with no stable version; add "no reviewed version" tag.
			if ( !$this->out->isPrintable() ) {
				$this->enableIcons();
				$msg = $this->useSimpleUI() ? 'revreview-quick-none' : 'revreview-noflagged';
				$tag .= $prot . ( $this->useSimpleUI() ? FlaggedRevsXML::draftStatusIcon() : '' ) .
					$this->msg( $msg )->parse();
			}
			$tagTypeClass = 'flaggedrevs_unreviewed';
		}

		if ( $tag != '' ) {
			$notice = Html::openElement( 'div', [ 'id' => 'mw-fr-revisiontag' ] );
			if ( $this->useSimpleUI() ) {
				$tagClass = 'flaggedrevs_short';
				$cssClasses = "{$tagClass} {$tagTypeClass} plainlinks noprint cdx-info-chip";
				$notice .= Html::rawElement( 'div', [ 'class' => $cssClasses ], $tag );
			} else {
				$tagClass = 'flaggedrevs_basic';
				$cssClasses = "{$tagClass} {$tagTypeClass} plainlinks noprint";
				$notice .= Html::openElement( 'div', [
					'class' => 'cdx-message cdx-message--block cdx-message--notice ' . $cssClasses,
					'aria-live' => 'polite'
				] );
				$notice .= Html::element( 'span', [ 'class' => 'cdx-message__icon' ] );
				$notice .= Html::rawElement( 'div', [ 'class' => 'cdx-message__content' ], $tag );
				$notice .= Html::closeElement( 'div' );
			}
			$notice .= Html::closeElement( 'div' );
			$this->reviewNotice .= $notice;
		}
	}

	/**
	 * If the page has a stable version and it shows by default,
	 * tell search crawlers to index only that version of the page.
	 * Also index the draft as well if they are synced (bug 27173).
	 * However, any URL with ?stableid=x should not be indexed (as with ?oldid=x).
	 */
	public function setRobotPolicy(): void {
		$request = $this->getRequest();
		if ( $this->article->getStableRev() && $this->article->isStableShownByDefault() ) {
			if ( $this->isPageView() && $this->showingStable() ) {
				return; // stable version - index this
			} elseif ( !$request->getVal( 'stableid' )
				&& $this->out->getRevisionId() == $this->article->getStable()
				&& $this->article->stableVersionIsSynced()
			) {
				return; // draft that is synced with the stable version - index this
			}
			$this->out->setRobotPolicy( 'noindex,nofollow' ); // don't index this version
		}
	}

	/**
	 * Tag output function must be called by caller
	 * Parser cache control deferred to caller
	 * @param FlaggedRevision $srev stable version
	 * @param string &$tag review box/bar info
	 * @param string $prot protection notice icon
	 */
	private function showDraftVersion( FlaggedRevision $srev, string &$tag, string $prot ): void {
		$request = $this->getRequest();
		$reqUser = $this->getUser();
		if ( $this->out->isPrintable() ) {
			return; // all this function does is add notices; don't show them
		}
		$time = $this->getLanguage()->date( $srev->getTimestamp(), true );
		# Get stable version sync status
		$synced = $this->article->stableVersionIsSynced();
		if ( $synced ) { // draft == stable
			$diffToggle = ''; // no diff to show
		} else { // draft != stable
			# The user may want the diff (via prefs)
			$diffToggle = $this->getTopDiffToggle( $srev );
			if ( $diffToggle != '' ) {
				$diffToggle = " $diffToggle";
			}
			# Make sure there is always a notice bar when viewing the draft.
			if ( $this->useSimpleUI() ) { // we already one for detailed UI
				$this->setPendingNotice( $srev, $diffToggle );
			}
		}
		# Give a "your edit is pending" notice to newer users if
		# an unreviewed edit was completed...
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		if ( $request->getVal( 'shownotice' )
			&& $this->article->getUserText( RevisionRecord::RAW ) == $reqUser->getName()
			&& $this->article->revsArePending()
			&& !$pm->userHasRight( $reqUser, 'review' )
		) {
			$revsSince = $this->article->getPendingRevCount();
			$pending = $prot;
			$pending .= $this->msg( 'revreview-edited', $srev->getRevId() )
				->numParams( $revsSince )->parse();
			$anchor = $request->getVal( 'fromsection' );
			if ( $anchor != null ) {
				// Hack: reverse some of the Sanitizer::escapeId() encoding
				$section = urldecode( str_replace( // bug 35661
					[ ':', '.' ], [ '%3A', '%' ], $anchor
				) );
				$section = str_replace( '_', ' ', $section ); // prettify
				$pending .= $this->msg( 'revreview-edited-section', $anchor, $section )
					->parseAsBlock();
			}
		# Notice should always use subtitle
			$this->reviewNotice = Html::openElement( 'div', [
					'id' => 'mw-fr-reviewnotice',
					'class' => 'cdx-message cdx-message--block cdx-message--notice
					flaggedrevs_preview plainlinks noprint',
					'aria-live' => 'polite'
				] )
				. Html::element( 'span', [ 'class' => 'cdx-message__icon' ] )
				. Html::rawElement( 'div', [ 'class' => 'cdx-message__content' ], $pending )
				. Html::closeElement( 'div' );
		# Otherwise, construct some tagging info for non-printable outputs.
		# Also, if low profile UI is enabled and the page is synced, skip the tag.
		# Note: the "your edit is pending" notice has all this info, so we never add both.
		} elseif ( !( $this->article->lowProfileUI() && $synced ) ) {
			$revsSince = $this->article->getPendingRevCount();
			// Simple icon-based UI
			if ( $this->useSimpleUI() ) {
				if ( !$reqUser->isRegistered() ) {
					$msgHTML = ''; // Anons just see simple icons
				} else {
					$msg = $synced ? 'revreview-quick-basic-same' : 'revreview-quick-see-basic';
					$msgHTML = $this->msg( $msg, $srev->getRevId() )
						->numParams( $revsSince )->parse();
				}
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$this->enableIcons();
					$icon = $synced ?
						FlaggedRevsXML::stableStatusIcon() :
						FlaggedRevsXML::draftStatusIcon();
				}
				$msgHTML = $prot . $icon . $msgHTML;
				$tag .= FlaggedRevsXML::prettyRatingBox( $srev, $msgHTML,
					$revsSince, 'draft', $synced );
			// Standard UI
			} else {
				if ( $synced ) {
					$msg = 'revreview-basic-same';
				} else {
					$msg = !$revsSince ? 'revreview-newest-basic-i' : 'revreview-newest-basic';
				}
				$msgHTML = $this->msg( $msg, $srev->getRevId(), $time )
					->numParams( $revsSince )->parse();
				$this->enableIcons();
				$tag .= $prot . $msgHTML . $diffToggle;
			}
		}
	}

	/**
	 * @param User $reqUser
	 * @return ParserOptions
	 */
	private function makeParserOptions( User $reqUser ): ParserOptions {
		$parserOptions = $this->article->makeParserOptions( $reqUser );
		# T349037: The ArticleParserOptions hook should be broadened to take
		# a WikiPage (aka $this->article) instead of an Article.  But for now
		# fake the Article.
		$article = Article::newFromWikiPage( $this->article, RequestContext::getMain() );
		# Allow extensions to vary parser options used for article rendering,
		# in the same way Article does
		( new HookRunner( MediaWikiServices::getInstance()->getHookContainer() ) )
			->onArticleParserOptions( $article, $parserOptions );

		return $parserOptions;
	}

	/**
	 * Tag output function must be called by caller
	 * Parser cache control deferred to caller
	 * @param FlaggedRevision $frev selected flagged revision
	 * @param string &$tag review box/bar info
	 * @param string $prot protection notice icon
	 */
	private function showOldReviewedVersion( FlaggedRevision $frev, string &$tag, string $prot ): ?ParserOutput {
		$reqUser = $this->getUser();
		$time = $this->getLanguage()->date( $frev->getTimestamp(), true );
		# Set display revision ID
		$this->out->setRevisionId( $frev->getRevId() );

		# Construct some tagging for non-printable outputs. Note that the pending
		# notice has all this info already, so don't do this if we added that already.
		if ( !$this->out->isPrintable() ) {
			$this->enableIcons();
			// Simple icon-based UI
			if ( $this->useSimpleUI() ) {
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$icon = FlaggedRevsXML::stableStatusIcon();
				}
				$revsSince = $this->article->getPendingRevCount();
				if ( !$reqUser->isRegistered() ) {
					$msgHTML = ''; // Anons just see simple icons
				} else {
					$msg = 'revreview-quick-basic-old';
					$msgHTML = $this->msg( $msg, $frev->getRevId() )
						->numParams( $revsSince )->parse();
				}
				$msgHTML = $prot . $icon . $msgHTML;
				$tag = FlaggedRevsXML::prettyRatingBox( $frev, $msgHTML, $revsSince );
				// Standard UI
			} else {
				$icon = FlaggedRevsXML::stableStatusIcon();
				$msg = 'revreview-basic-old';
				$tag = $prot . $icon;
				$tag .= $this->msg( $msg, $frev->getRevId(), $time )->parse();
			}
		}

		# Generate the uncached parser output for this old reviewed version
		$parserOptions = $this->makeParserOptions( $reqUser );
		$parserOut = FlaggedRevs::parseStableRevision( $frev, $parserOptions );
		if ( !$parserOut ) {
			return null;
		}

		# Add the parser output to the page view
		$this->out->addParserOutput(
			$parserOut,
			[ 'enableSectionEditLinks' => false, ]
		);

		return $parserOut;
	}

	/**
	 * Tag output function must be called by caller
	 * Parser cache control deferred to caller
	 * @param FlaggedRevision $srev stable version
	 * @param string &$tag review box/bar info
	 * @param string $prot protection notice
	 */
	private function showStableVersion( FlaggedRevision $srev, string &$tag, string $prot ): ?ParserOutput {
		$reqUser = $this->getUser();
		$time = $this->getLanguage()->date( $srev->getTimestamp(), true );
		# Set display revision ID
		$this->out->setRevisionId( $srev->getRevId() );
		$synced = $this->article->stableVersionIsSynced();
		# Construct some tagging
		if (
			!$this->out->isPrintable() &&
			!( $this->article->lowProfileUI() && $synced )
		) {
			$revsSince = $this->article->getPendingRevCount();
			// Simple icon-based UI
			if ( $this->useSimpleUI() ) {
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$icon = FlaggedRevsXML::stableStatusIcon();
					$this->enableIcons();
				}
				if ( !$reqUser->isRegistered() ) {
					$msgHTML = ''; // Anons just see simple icons
				} else {
					$msg = $synced ? 'revreview-quick-basic-same' : 'revreview-quick-basic';
					$msgHTML = $this->msg( $msg, $srev->getRevId() )
						->numParams( $revsSince )->parse();
				}
				$msgHTML = $prot . $icon . $msgHTML;
				$tag = FlaggedRevsXML::prettyRatingBox( $srev, $msgHTML,
					$revsSince, 'stable', $synced );
			// Standard UI
			} else {
				$this->enableIcons();
				if ( $synced ) {
					$msg = 'revreview-basic-same';
				} else {
					$msg = !$revsSince ? 'revreview-basic-i' : 'revreview-basic';
				}
				$tag = $prot;
				$tag .= $this->msg( $msg, $srev->getRevId(), $time )
					->numParams( $revsSince )->parse();
			}
		}

		// TODO: Rewrite to use ParserOutputAccess
		$parserOptions = $this->makeParserOptions( $reqUser );
		$stableParserCache = FlaggedRevs::getParserCacheInstance( $parserOptions );
		// Check the stable version cache for the parser output
		$parserOut = $stableParserCache->get( $this->article, $parserOptions );

		if ( !$parserOut ) {
			if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_CURRENT && $synced ) {
				# Stable and draft version are identical; check the draft version cache
				$draftParserCache = MediaWikiServices::getInstance()->getParserCache();
				$parserOut = $draftParserCache->get( $this->article, $parserOptions );
			}

			if ( !$parserOut ) {
				# Regenerate the parser output, debouncing parse requests via PoolCounter
				$status = FlaggedRevs::parseStableRevisionPooled( $srev, $parserOptions );
				if ( !$status->isGood() ) {
					$this->out->disableClientCache();
					$this->out->setRobotPolicy( 'noindex,nofollow' );

					$errortext = $status->getWikiText( false, 'view-pool-error' );
					$this->out->addHTML(
						Html::errorBox( $this->out->parseAsContent( $errortext ) )
					);
					return null;
				}
				$parserOut = $status->getValue();
			}

			if ( $parserOut instanceof ParserOutput ) {
				# Update the stable version cache
				$stableParserCache->save( $parserOut, $this->article, $parserOptions );

				# Enqueue a job to update the "stable version only" dependencies
				if ( !MediaWikiServices::getInstance()->getReadOnlyMode()->isReadOnly() ) {
					$update = new FRDependencyUpdate( $this->article->getTitle(), $parserOut );
					$update->doUpdate( FRDependencyUpdate::DEFERRED );
				}
			}
		}

		if ( !$parserOut ) {
			$this->out->disableClientCache();
			$this->out->setRobotPolicy( 'noindex,nofollow' );

			$this->out->addWikiMsg(
				'missing-article',
				$this->article->getTitle()->getPrefixedText(),
				$this->msg( 'missingarticle-rev', $srev->getRevId() )->plain()
			);
			return null;
		}

		# Add the parser output to the page view
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		$poOptions = [];
		if ( $this->out->isPrintable() ||
			!$pm->quickUserCan( 'edit', $reqUser, $this->article->getTitle() )
		) {
			$poOptions['enableSectionEditLinks'] = false;
		}
		$this->out->addParserOutput( $parserOut, $poOptions );

		# Update page sync status for tracking purposes.
		# NOTE: avoids primary hits and doesn't have to be perfect for what it does
		if ( $this->article->syncedInTracking() != $synced ) {
			$this->article->lazyUpdateSyncStatus();
		}

		return $parserOut;
	}

	private function enableIcons(): void {
		$this->out->addModuleStyles( 'ext.flaggedRevs.icons' );
	}

	/**
	 * Show icons for draft/stable/old reviewed versions
	 */
	private function showRatingIcon(): bool {
		// If there is only one quality level and we have tabs to know which version we are looking
		// at, then just use the lock icon...
		return !FlaggedRevs::useOnlyIfProtected();
	}

	/**
	 * Get a toggle for a collapsible diff-to-stable to add to the review notice as needed
	 * @param FlaggedRevision $srev stable version
	 * @return string|false the html line (either "" or "<diff toggle>")
	 */
	private function getTopDiffToggle( FlaggedRevision $srev ) {
		$reqUser = $this->getUser();
		if ( !MediaWikiServices::getInstance()->getUserOptionsLookup()
			->getBoolOption( $reqUser, 'flaggedrevsviewdiffs' )
		) {
			return false; // nothing to do here
		}
		# Diff should only show for the draft
		$oldid = $this->getOldIDFromRequest();
		$latest = $this->article->getLatest();
		if ( $oldid && $oldid != $latest ) {
			return false; // not viewing the draft
		}
		$revsSince = $this->article->getPendingRevCount();
		if ( !$revsSince ) {
			return false; // no pending changes
		}

		$title = $this->article->getTitle(); // convenience
		if ( $srev->getRevId() !== $latest ) {
			$nEdits = $revsSince - 1; // full diff-to-stable, no need for query
			if ( $nEdits ) {
				$limit = 100;
				try {
					$latestRevObj = MediaWikiServices::getInstance()
						->getRevisionLookup()
						->getRevisionById( $latest );
					$users = MediaWikiServices::getInstance()
						->getRevisionStore()
						->getAuthorsBetween(
							$title->getArticleID(),
							$srev->getRevisionRecord(),
							$latestRevObj,
							null,
							$limit
						);
					$nUsers = count( $users );
				} catch ( InvalidArgumentException $e ) {
					$nUsers = 0;
				}
				$multiNotice = DifferenceEngine::intermediateEditsMsg( $nEdits, $nUsers, $limit );
			} else {
				$multiNotice = '';
			}
			$this->isDiffFromStable = true; // alter default review form tags
			// @phan-suppress-next-line SecurityCheck-DoubleEscaped multiNotice is used in a-tag
			return FlaggedRevsXML::diffToggle( $title, $srev->getRevId(), $latest, $multiNotice );
		}

		return '';
	}

	/**
	 * Adds stable version tags to page when viewing history
	 */
	public function addToHistView(): void {
		# Add a notice if there are pending edits...
		$srev = $this->article->getStableRev();
		if ( $srev && $this->article->revsArePending() ) {
			$revsSince = $this->article->getPendingRevCount();
			$this->enableIcons();
			$tag = "<div id='mw-fr-revisiontag-edit' class='flaggedrevs_notice plainlinks'>" .
				FlaggedRevsXML::lockStatusIcon( $this->article ) . # flag protection icon as needed
				FlaggedRevsXML::pendingEditNotice( $srev, $revsSince ) . "</div>";
			$this->out->addHTML( $tag );
		}
	}

	/**
	 * @param Title $title
	 * @param int $oldid
	 * @param string[] &$notices
	 */
	public function getEditNotices( Title $title, int $oldid, array &$notices ): void {
		if ( !$this->article->isReviewable() ) {
			return;
		}
		// HACK fake EditPage
		$editPage = new EditPage( new Article( $title, $oldid ) );
		$editPage->oldid = $oldid;
		$reqUser = $this->getUser();

		$lines = [];

		$log = $this->stabilityLogNotice();
		if ( $log ) {
			$lines[] = $log;
		} elseif ( $this->editWillRequireReview( $editPage ) ) {
			$lines[] = $this->msg( 'revreview-editnotice' )->parseAsBlock();
		}
		$frev = $this->article->getStableRev();
		if ( $frev && $this->article->revsArePending() ) {
			$revsSince = $this->article->getPendingRevCount();
			$pendingMsg = FlaggedRevsXML::pendingEditNoticeMessage(
				$frev, $revsSince
			);
			$lines[] = $pendingMsg->setContext( $this->getContext() )->parseAsBlock();
		}
		$latestId = $this->article->getLatest();
		$revId  = $oldid ?: $latestId;
		if ( $frev && $frev->getRevId() < $latestId // changes were made
			&& MediaWikiServices::getInstance()->getUserOptionsLookup()
				->getBoolOption( $reqUser, 'flaggedrevseditdiffs' ) // not disabled via prefs
			&& $revId === $latestId // only for current rev
		) {
			$lines[] = '<p>' . $this->msg( 'review-edit-diff' )->parse() . ' ' .
				FlaggedRevsXML::diffToggle( $this->article->getTitle(), $frev->getRevId(), $revId ) . '</p>';
		}

		$srev = $this->article->getStableRev();
		$revsSince = $this->article->getPendingRevCount();

		if ( $frev && $this->article->onlyTemplatesPending() && $revsSince === 0 && $srev ) {
			$time = $this->getLanguage()->userTimeAndDate( $srev->getTimestamp(), $this->getUser() );
			$lines[] = '<p>' . $this->msg( 'revreview-newest-basic-i',
					$srev->getRevId(), $time )->numParams( $revsSince )->parse() . '</p>';
		}

		if ( $lines ) {
			$lineMessages = '';
			foreach ( $lines as $line ) {
				$lineMessages .= Html::rawElement( 'div', [
					'class' => 'cdx-message cdx-message--inline cdx-message--notice',
					'aria-live' => 'polite'
				],
					Html::element( 'span', [ 'class' => 'cdx-message__icon' ] ) .
					Html::rawElement( 'div', [ 'class' => 'cdx-message__content' ], $line )
				);
			}

			$notices['flaggedrevs_editnotice'] = Html::rawElement( 'div', [
				'class' => 'mw-fr-edit-messages',
			], $lineMessages );
		}
	}

	private function stabilityLogNotice(): string {
		if ( $this->article->isPageLocked() ) {
			$msg = 'revreview-locked';
		} elseif ( $this->article->isPageUnlocked() ) {
			$msg = 'revreview-unlocked';
		} else {
			return '';
		}
		$s = $this->msg( $msg )->parseAsBlock();
		return $s . FlaggedRevsXML::stabilityLogExcerpt( $this->article->getTitle() );
	}

	public function addToNoSuchSection( string &$s ): void {
		$srev = $this->article->getStableRev();
		# Add notice for users that may have clicked "edit" for a
		# section in the stable version that isn't in the draft.
		if ( $srev && $this->article->revsArePending() ) {
			$revsSince = $this->article->getPendingRevCount();
			if ( $revsSince ) {
				$s .= "<div class='flaggedrevs_editnotice plainlinks'>" .
					$this->msg( 'revreview-pending-nosection',
						$srev->getRevId() )->numParams( $revsSince )->parse() . "</div>";
			}
		}
	}

	/**
	 * Add unreviewed pages links
	 */
	public function addToCategoryView(): void {
		$reqUser = $this->getUser();
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		if ( !$pm->userHasRight( $reqUser, 'review' ) ) {
			return;
		}

		if ( !FlaggedRevs::useOnlyIfProtected() ) {
			# Add links to lists of unreviewed pages and pending changes in this category
			$category = $this->article->getTitle()->getText();
			$this->out->addSubtitle(
				Html::rawElement(
					'span',
					[ 'class' => 'plainlinks', 'id' => 'mw-fr-category-oldreviewed' ],
					$this->msg( 'flaggedrevs-categoryview', urlencode( $category ) )->parse()
				)
			);
		}
	}

	/**
	 * Add review form to pages when necessary on a regular page view (action=view).
	 * If $output is an OutputPage then this prepends the form onto it.
	 * If $output is a string then this appends the review form to it.
	 * @param string|OutputPage &$output
	 */
	public function addReviewForm( &$output ): void {
		if ( $this->out->isPrintable() ) {
			// Must be on non-printable output
			return;
		}

		# User must have review rights
		$reqUser = $this->getUser();
		if ( !MediaWikiServices::getInstance()->getPermissionManager()
			->userHasRight( $reqUser, 'review' )
		) {
			return;
		}
		# Page must exist and be reviewable
		if ( !$this->article->exists() || !$this->article->isReviewable() ) {
			return;
		}
		# Must be a page view action...
		if ( !$this->isPageViewOrDiff() ) {
			return;
		}
		# Get the revision being displayed
		$revRecord = false;
		if ( $this->reviewFormRevRecord ) { // diff
			$revRecord = $this->reviewFormRevRecord; // $newRev for diffs stored here
		} elseif ( $this->out->getRevisionId() ) { // page view
			$revRecord = MediaWikiServices::getInstance()
				->getRevisionLookup()
				->getRevisionById( $this->out->getRevisionId() );
		}
		# Build the review form as needed
		if ( $revRecord && ( !$this->diffRevRecords || $this->isReviewableDiff ) ) {
			$form = new RevisionReviewFormUI(
				$this->getContext(),
				$this->article,
				$revRecord
			);
			# Default tags and existence of "reject" button depend on context
			if ( $this->diffRevRecords ) {
				$oldRevRecord = $this->diffRevRecords['old'];
				$form->setDiffPriorRevRecord( $oldRevRecord );
			}
			# Review notice box goes in top of form
			$form->setTopNotice( $this->diffNoticeBox );
			$form->setBottomNotice( $this->diffIncChangeBox );

			[ $html, ] = $form->getHtml();
			# Diff action: place the form at the top of the page
			if ( $output instanceof OutputPage ) {
				$output->prependHTML( $html );
			# View action: place the form at the bottom of the page
			} else {
				$output .= $html;
			}
		}
	}

	/**
	 * Add link to stable version setting to protection form
	 */
	public function addStabilizationLink(): void {
		$request = $this->getRequest();
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			// Simple custom levels set for action=protect
			return;
		}
		# Check only if the title is reviewable
		if ( !FlaggedRevs::inReviewNamespace( $this->article ) ) {
			return;
		}
		$action = $request->getVal( 'action', 'view' );
		if ( $action == 'protect' || $action == 'unprotect' ) {
			$title = SpecialPage::getTitleFor( 'Stabilization' );
			# Give a link to the page to configure the stable version
			$frev = $this->article->getStableRev();
			if ( !$frev ) {
				$msg = 'revreview-visibility-nostable';
			} elseif ( $frev->getRevId() == $this->article->getLatest() ) {
				$msg = 'revreview-visibility-synced';
			} else {
				$msg = 'revreview-visibility-outdated';
			}
			$this->out->prependHTML( "<span class='revreview-visibility $msg plainlinks'>" .
				$this->msg( $msg, $title->getPrefixedText() )->parse() . '</span>' );
		}
	}

	/**
	 * Modify an array of action links, as used by SkinTemplateNavigation and
	 * SkinTemplateTabs, to inlude flagged revs UI elements
	 */
	public function setActionTabs( array &$actions ): void {
		$reqUser = $this->getUser();

		if ( FlaggedRevs::useOnlyIfProtected() ) {
			return; // simple custom levels set for action=protect
		}

		$title = $this->article->getTitle()->getSubjectPage();
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
			return; // Only reviewable pages need these tabs
		}

		// Check if we should show a stabilization tab
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		if (
			!$this->article->getTitle()->isTalkPage() &&
			!isset( $actions['protect'] ) &&
			!isset( $actions['unprotect'] ) &&
			$pm->userHasRight( $reqUser, 'stablesettings' ) &&
			$title->exists()
		) {
			$stableTitle = SpecialPage::getTitleFor( 'Stabilization' );
			// Add the tab
			$actions['default'] = [
				'class' => false,
				'text' => $this->msg( 'stabilization-tab' )->text(),
				'href' => $stableTitle->getLocalURL( 'page=' . $title->getPrefixedURL() )
			];
		}
	}

	/**
	 * Modify an array of tab links to include flagged revs UI elements
	 * @param Skin $skin
	 * @param array[] &$views
	 */
	public function setViewTabs( Skin $skin, array &$views ): void {
		if ( !FlaggedRevs::inReviewNamespace( $this->article ) ) {
			// Short-circuit for non-reviewable pages
			return;
		}
		# Hack for bug 16734 (some actions update and view all at once)
		if ( $this->pageWriteOpRequested() &&
			MediaWikiServices::getInstance()->getDBLoadBalancer()->hasOrMadeRecentPrimaryChanges()
		) {
			# Tabs need to reflect the new stable version so users actually
			# see the results of their action (i.e. "delete"/"rollback")
			$this->article->loadPageData( IDBAccessObject::READ_LATEST );
		}
		$srev = $this->article->getStableRev();
		if ( !$srev ) {
			// No stable revision exists
			return;
		}
		$synced = $this->article->stableVersionIsSynced();
		$pendingEdits = !$synced && $this->article->isStableShownByDefault();
		// Set the edit tab names as needed...
		if ( $pendingEdits && $this->isPageView() && $this->showingStable() ) {
			// bug 31489; direct user to current
			if ( isset( $views['edit'] ) ) {
				$views['edit']['href'] = $skin->getTitle()->getFullURL( 'action=edit' );
			}
			if ( isset( $views['viewsource'] ) ) {
				$views['viewsource']['href'] = $skin->getTitle()->getFullURL( 'action=edit' );
			}
			// Instruct alternative editors like VisualEditor to load the latest ("current")
			// revision for editing, rather than the one from 'wgRevisionId'
			$skin->getOutput()->addJsConfigVars( 'wgEditLatestRevision', true );
		}
		# Add "pending changes" tab if the page is not synced
		if ( !$synced ) {
			$this->addDraftTab( $views, $srev );
		}
	}

	/**
	 * Add "pending changes" tab and set tab selection CSS
	 * @param array[] &$views
	 * @param FlaggedRevision $srev
	 */
	private function addDraftTab( array &$views, FlaggedRevision $srev ): void {
		$request = $this->getRequest();
		$title = $this->article->getTitle(); // convenience
		$tabs = [
			'read' => [ // view stable
				'text'  => '', // unused
				'href'  => $title->getLocalURL( 'stable=1' ),
				'class' => ''
			],
			'draft' => [ // view draft
				'text'  => $this->msg( 'revreview-current' )->text(),
				'href'  => $title->getLocalURL( 'stable=0&redirect=no' ),
				'class' => 'collapsible'
			],
		];
		// Set tab selection CSS
		if ( ( $this->isPageView() && $this->showingStable() ) || $request->getVal( 'stableid' ) ) {
			// We are looking a the stable version or an old reviewed one
			$tabs['read']['class'] = 'selected';
		} elseif ( $this->isPageViewOrDiff() ) {
			$ts = null;
			if ( $this->out->getRevisionId() ) { // @TODO: avoid same query in Skin.php
				if ( $this->out->getRevisionId() == $this->article->getLatest() ) {
					$ts = $this->article->getTimestamp(); // skip query
				} else {
					$ts = MediaWikiServices::getInstance()
						->getRevisionLookup()
						->getTimestampFromId( $this->out->getRevisionId() );
				}
			}
			// Are we looking at a pending revision?
			if ( $ts > $srev->getRevTimestamp() ) { // bug 15515
				$tabs['draft']['class'] .= ' selected';
			// Are there *just* pending template changes.
			} elseif ( $this->article->onlyTemplatesPending()
				&& $this->out->getRevisionId() == $this->article->getStable()
			) {
				$tabs['draft']['class'] .= ' selected';
			// Otherwise, fallback to regular tab behavior
			} else {
				$tabs['read']['class'] = 'selected';
			}
		}
		$newViews = [];
		// Rebuild tabs array
		$previousTab = null;
		foreach ( $views as $tabAction => $data ) {
			// The 'view' tab. Make it go to the stable version...
			if ( $tabAction == 'view' ) {
				// 'view' for content page; make it go to the stable version
				$newViews[$tabAction]['text'] = $data['text']; // keep tab name
				$newViews[$tabAction]['href'] = $tabs['read']['href'];
				$newViews[$tabAction]['class'] = $tabs['read']['class'];
			// All other tabs...
			} else {
				if ( $previousTab == 'view' ) {
					$newViews['current'] = $tabs['draft'];
				}
				$newViews[$tabAction] = $data;
			}
			$previousTab = $tabAction;
		}
		// Replaces old tabs with new tabs
		$views = $newViews;
	}

	/**
	 * Check if a flaggedrevs relevant write op was done this page view
	 */
	private function pageWriteOpRequested(): bool {
		$request = $this->getRequest();
		# Hack for bug 16734 (some actions update and view all at once)
		$action = $request->getVal( 'action' );
		return $action === 'rollback' ||
			( $action === 'delete' && $request->wasPosted() );
	}

	private function getOldIDFromRequest(): int {
		$article = Article::newFromWikiPage( $this->article, RequestContext::getMain() );
		return $article->getOldIDFromRequest();
	}

	/**
	 * Adds a notice saying that this revision is pending review
	 *
	 * @param FlaggedRevision $srev The stable version
	 * @param string $diffToggle either "" or " <diff toggle><diff div>"
	 */
	private function setPendingNotice( FlaggedRevision $srev, $diffToggle = '' ): void {
		$time = $this->getLanguage()->date( $srev->getTimestamp(), true );
		$revsSince = $this->article->getPendingRevCount();
		$msg = !$revsSince ? 'revreview-newest-basic-i' : 'revreview-newest-basic';
		# Add bar msg to the top of the page...
		$msgHTML = $this->msg( $msg, $srev->getRevId(), $time )->numParams( $revsSince )->parse();

		if ( !$this->useSimpleUI() ) {
			$htmlParts = [];
			$htmlParts[] = Html::openElement( 'div', [
				'class' => 'cdx-message cdx-message--block cdx-message--notice',
				'aria-live' => 'polite'
			] );
			$htmlParts[] = Html::element( 'span', [ 'class' => 'cdx-message__icon' ] );
			$htmlParts[] = Html::openElement( 'div', [ 'class' => 'cdx-message__content' ] );
			$htmlParts[] = $msgHTML . $diffToggle;
			$htmlParts[] = Html::closeElement( 'div' ) . Html::closeElement( 'div' );

			$this->reviewNotice .= implode( '', $htmlParts );
		}
	}

	/**
	 * When viewing a diff:
	 * (a) Add the review form to the top of the page
	 * (b) Mark off which versions are checked or not
	 * (c) When comparing the stable revision to the current:
	 *   (i)  Show a tag with some explanation for the diff
	 */
	public function addToDiffView( ?RevisionRecord $oldRevRecord, ?RevisionRecord $newRevRecord ): void {
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		$request = $this->getRequest();
		$reqUser = $this->getUser();
		# Exempt printer-friendly output
		if ( $this->out->isPrintable() ) {
			return;
		# Multi-page diffs are useless and misbehave (bug 19327). Sanity check $newRevRecord.
		} elseif ( $this->isMultiPageDiff || !$newRevRecord ) {
			return;
		# Page must be reviewable.
		} elseif ( !$this->article->isReviewable() ) {
			return;
		}
		$srev = $this->article->getStableRev();
		if ( $srev && $this->isReviewableDiff ) {
			$this->reviewFormRevRecord = $newRevRecord;
		}
		# Check if this is a diff-to-stable. If so:
		# (a) prompt reviewers to review the changes
		if ( $srev
			&& $this->isDiffFromStable
			&& !$this->article->stableVersionIsSynced() // pending changes
		) {
			# If there are pending revs, notify the user...
			if ( $this->article->revsArePending() ) {
				# If the user can review then prompt them to review them...
				if ( $pm->userHasRight( $reqUser, 'review' ) ) {
					// Reviewer just edited...
					if ( $request->getInt( 'shownotice' )
						&& $newRevRecord->isCurrent()
						&& $newRevRecord->getUser( RevisionRecord::RAW )
							->equals( $reqUser )
					) {
						$title = $this->article->getTitle(); // convenience
						// @TODO: make diff class cache this
						$n = MediaWikiServices::getInstance()
							->getRevisionStore()
							->countRevisionsBetween(
								$title->getArticleID(),
								$oldRevRecord,
								$newRevRecord
							);
						if ( $n ) {
							$msg = 'revreview-update-edited-prev'; // previous pending edits
						} else {
							$msg = 'revreview-update-edited'; // just couldn't autoreview
						}
					// All other cases...
					} else {
						$msg = 'revreview-update'; // generic "please review" notice...
					}
					// add as part of form
					$this->diffNoticeBox = $this->msg( $msg )->parseAsBlock();
				}
			}
		}
		# Add a link to diff from stable to current as needed.
		# Show review status of the diff revision(s). Uses a <table>.
		$this->out->addHTML(
			'<div id="mw-fr-diff-headeritems">' .
			self::diffLinkAndMarkers(
				$this->article,
				$oldRevRecord,
				$newRevRecord
			) .
			'</div>'
		);
	}

	/**
	 * get new diff header items for in-place page review
	 */
	public static function buildDiffHeaderItems(): string {
		$args = func_get_args(); // <oldid, newid>
		if ( count( $args ) >= 2 ) {
			$oldid = (int)$args[0];
			$newid = (int)$args[1];
			$revLookup = MediaWikiServices::getInstance()->getRevisionLookup();
			$newRevRecord = $revLookup->getRevisionById( $newid );
			if ( $newRevRecord && $newRevRecord->getPageAsLinkTarget() ) {
				$oldRevRecord = $revLookup->getRevisionById( $oldid );
				$fa = FlaggableWikiPage::getTitleInstance(
					Title::newFromLinkTarget( $newRevRecord->getPageAsLinkTarget() )
				);
				return self::diffLinkAndMarkers( $fa, $oldRevRecord, $newRevRecord );
			}
		}
		return '';
	}

	/**
	 * (a) Add a link to diff from stable to current as needed
	 * (b) Show review status of the diff revision(s). Uses a <table>.
	 * Note: used by ajax function to rebuild diff page
	 */
	private static function diffLinkAndMarkers(
		FlaggableWikiPage $article,
		?RevisionRecord $oldRevRecord,
		?RevisionRecord $newRevRecord
	): string {
		$s = '<form id="mw-fr-diff-dataform">';
		$s .= Html::hidden( 'oldid', $oldRevRecord ? $oldRevRecord->getId() : 0 );
		$s .= Html::hidden( 'newid', $newRevRecord ? $newRevRecord->getId() : 0 );
		$s .= "</form>\n";
		if ( $newRevRecord && $oldRevRecord ) { // sanity check
			$s .= self::diffToStableLink( $article, $oldRevRecord, $newRevRecord );
			$s .= self::diffReviewMarkers( $article, $oldRevRecord, $newRevRecord );
		}
		return $s;
	}

	/**
	 * Add a link to diff-to-stable for reviewable pages
	 */
	private static function diffToStableLink(
		FlaggableWikiPage $article,
		RevisionRecord $oldRevRecord,
		RevisionRecord $newRevRecord
	): string {
		$srev = $article->getStableRev();
		if ( !$srev ) {
			return ''; // nothing to do
		}
		$review = '';
		# Is this already the full diff-to-stable?
		$fullStableDiff = $newRevRecord->isCurrent()
			&& self::isDiffToStable(
				$srev,
				$oldRevRecord,
				$newRevRecord
			);
		# Make a link to the full diff-to-stable if:
		# (a) Actual revs are pending and (b) We are not viewing the full diff-to-stable
		if ( $article->revsArePending() && !$fullStableDiff ) {
			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
			$reviewLink = $linkRenderer->makeKnownLink(
				$article->getTitle(),
				wfMessage( 'review-diff2stable' )->text(),
				[],
				[ 'oldid' => $srev->getRevId(), 'diff' => 'cur' ]
			);
			$reviewWrapped = wfMessage( 'parentheses' )->rawParams( $reviewLink )->escaped();
			$review = "<div class='fr-diff-to-stable' style='text-align: center;'>$reviewWrapped</div>";
		}
		return $review;
	}

	/**
	 * Add [checked version] and such to left and right side of diff
	 */
	private static function diffReviewMarkers(
		FlaggableWikiPage $article,
		?RevisionRecord $oldRevRecord,
		?RevisionRecord $newRevRecord
	): string {
		$table = '';
		$srev = $article->getStableRev();
		# Diff between two revisions
		if ( $oldRevRecord && $newRevRecord ) {
			[ $msg, $class ] = self::getDiffRevMsgAndClass( $oldRevRecord, $srev );
			$table .= "<table class='fr-diff-ratings'><tr>";
			$table .= "<td style='text-align: center; width: 50%;'>";
			// @todo i18n FIXME: Hard coded brackets
			$table .= "<span class='$class'>[" .
				wfMessage( $msg )->escaped() . "]</span>";

			[ $msg, $class ] = self::getDiffRevMsgAndClass( $newRevRecord, $srev );
			$table .= "</td><td style='text-align: center; width: 50%;'>";
			// @todo i18n FIXME: Hard coded brackets
			$table .= "<span class='$class'>[" .
				wfMessage( $msg )->escaped() . "]</span>";

			$table .= "</td></tr></table>\n";
		# New page "diffs" - just one rev
		} elseif ( $newRevRecord ) {
			[ $msg, $class ] = self::getDiffRevMsgAndClass( $newRevRecord, $srev );
			$table .= "<table class='fr-diff-ratings'>";
			$table .= "<tr><td style='text-align: center;'><span class='$class'>";
			// @todo i18n FIXME: Hard coded brackets
			$table .= '[' . wfMessage( $msg )->escaped() . ']';
			$table .= "</span></td></tr></table>\n";
		}
		return $table;
	}

	/**
	 * @return string[]
	 */
	private static function getDiffRevMsgAndClass(
		RevisionRecord $revRecord, ?FlaggedRevision $srev
	): array {
		$checked = FlaggedRevision::revIsFlagged( $revRecord->getId() );
		if ( $checked ) {
			$msg = 'revreview-hist-basic';
		} else {
			$msg = ( $srev && $revRecord->getTimestamp() > $srev->getRevTimestamp() ) ? // bug 15515
				'revreview-hist-pending' :
				'revreview-hist-draft';
		}
		return [ $msg, $checked ? 'flaggedrevs-color-1' : 'flaggedrevs-color-0' ];
	}

	/**
	 * Set $this->isDiffFromStable and $this->isMultiPageDiff fields
	 */
	public function setViewFlags(
		DifferenceEngine $diff,
		?RevisionRecord $oldRevRecord,
		?RevisionRecord $newRevRecord
	): void {
		// We only want valid diffs that actually make sense...
		if ( !( $newRevRecord
			&& $oldRevRecord
			&& $newRevRecord->getTimestamp() >= $oldRevRecord->getTimestamp() )
		) {
			return;
		}

		// Is this a diff between two pages?
		if ( $newRevRecord->getPageId() != $oldRevRecord->getPageId() ) {
			$this->isMultiPageDiff = true;
		// Is there a stable version?
		} elseif ( $this->article->isReviewable() ) {
			$srev = $this->article->getStableRev();
			// Is this a diff of a draft rev against the stable rev?
			if ( self::isDiffToStable(
				$srev,
				$oldRevRecord,
				$newRevRecord
			) ) {
				$this->isDiffFromStable = true;
				$this->isReviewableDiff = true;
			// Is this a diff of a draft rev against a reviewed rev?
			} elseif (
				FlaggedRevision::newFromTitle(
					$diff->getTitle(),
					$oldRevRecord->getId()
				) ||
				FlaggedRevision::newFromTitle(
					$diff->getTitle(),
					$newRevRecord->getId()
				)
			) {
				$this->isReviewableDiff = true;
			}
		}

		$this->diffRevRecords = [
			'old' => $oldRevRecord,
			'new' => $newRevRecord
		];
	}

	/**
	 * Is a diff from $oldRev to $newRev a diff-to-stable?
	 */
	private static function isDiffToStable(
		?FlaggedRevision $srev,
		?RevisionRecord $oldRevRecord,
		?RevisionRecord $newRevRecord
	): bool {
		return ( $srev
			&& $oldRevRecord
			&& $newRevRecord
			&& $oldRevRecord->getPageId() === $newRevRecord->getPageId() // no multipage diffs
			&& $oldRevRecord->getId() == $srev->getRevId()
			&& $newRevRecord->getTimestamp() >= $oldRevRecord->getTimestamp() // no backwards diffs
		);
	}

	/**
	 * Redirect users out to review the changes to the stable version.
	 * Only for people who can review and for pages that have a stable version.
	 */
	public function injectPostEditURLParams( string &$sectionAnchor, string &$extraQuery ): void {
		$reqUser = $this->getUser();
		$this->article->loadPageData( IDBAccessObject::READ_LATEST );
		# Get the stable version from the primary DB
		$frev = $this->article->getStableRev();
		if ( !$frev ) {
			// Only for pages with stable versions
			return;
		}

		$params = [];
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		// If the edit was not autoreviewed, and the user can actually make a
		// new stable version, then go to the diff...
		if ( $this->article->revsArePending() && $frev->userCanSetTag( $reqUser ) ) {
			$params += [ 'oldid' => $frev->getRevId(), 'diff' => 'cur', 'shownotice' => 1 ];
		// ...otherwise, go to the draft revision after completing an edit.
		// This allows for users to immediately see their changes. Even if the stable
		// and draft page match, we can avoid a parse due to FR_INCLUDES_STABLE.
		} else {
			$params += [ 'stable' => 0 ];
			// Show a notice at the top of the page for non-reviewers...
			if ( $this->article->revsArePending()
				&& $this->article->isStableShownByDefault()
				&& !$pm->userHasRight( $reqUser, 'review' )
			) {
				$params += [ 'shownotice' => 1 ];
				if ( $sectionAnchor ) {
					// Pass a section parameter in the URL as needed to add a link to
					// the "your changes are pending" box on the top of the page...
					$params += [ 'fromsection' => substr( $sectionAnchor, 1 ) ]; // strip #
					$sectionAnchor = ''; // go to the top of the page to see notice
				}
			}
		}
		if ( $extraQuery !== '' ) {
			$extraQuery .= '&';
		}
		$extraQuery .= wfArrayToCgi( $params ); // note: EditPage will add initial "&"
	}

	/**
	 * If submitting the edit will leave it pending, then change the button text
	 * Note: interacts with 'review pending changes' checkbox
	 * @param EditPage $editPage
	 * @param \OOUI\ButtonInputWidget[] $buttons
	 */
	public function changeSaveButton( EditPage $editPage, array $buttons ): void {
		if ( !$this->editWillRequireReview( $editPage ) ) {
			// Edit will go live or be reviewed on save
			return;
		}
		if ( isset( $buttons['save'] ) ) {
			$buttonLabel = $this->msg( 'revreview-submitedit' )->text();
			$buttons['save']->setLabel( $buttonLabel );
			$buttonTitle = $this->msg( 'revreview-submitedit-title' )->text();
			$buttons['save']->setTitle( $buttonTitle );
		}
	}

	/**
	 * If this edit will not go live on submit (accounting for wpReviewEdit)
	 */
	private function editWillRequireReview( EditPage $editPage ): bool {
		$request = $this->getRequest(); // convenience
		$title = $this->article->getTitle(); // convenience
		if ( !$this->article->editsRequireReview() || $this->editWillBeAutoreviewed( $editPage ) ) {
			return false; // edit will go live immediately
		} elseif ( $request->getCheck( 'wpReviewEdit' ) &&
			MediaWikiServices::getInstance()->getPermissionManager()
				->userCan( 'review', $this->getUser(), $title )
		) {
			return false; // edit checked off to be reviewed on save
		}
		return true; // edit needs review
	}

	/**
	 * If this edit will be auto-reviewed on submit
	 * Note: checking wpReviewEdit does not count as auto-reviewed
	 */
	private function editWillBeAutoreviewed( EditPage $editPage ): bool {
		$title = $this->article->getTitle(); // convenience
		if ( !$this->article->isReviewable() ) {
			return false;
		}
		if ( MediaWikiServices::getInstance()->getPermissionManager()
			->quickUserCan( 'autoreview', $this->getUser(), $title )
		) {
			if ( FlaggedRevs::autoReviewNewPages() && !$this->article->exists() ) {
				return true; // edit will be autoreviewed
			}

			$baseRevId = self::getBaseRevId( $editPage, $this->getRequest() );
			$baseRevId2 = self::getAltBaseRevId( $editPage, $this->getRequest() );
			$baseFRev = FlaggedRevision::newFromTitle( $title, $baseRevId );
			if ( !$baseFRev && $baseRevId2 ) {
				$baseFRev = FlaggedRevision::newFromTitle( $title, $baseRevId2 );
			}

			if ( $baseFRev ) {
				return true; // edit will be autoreviewed
			}
		}
		return false; // edit won't be autoreviewed
	}

	/**
	 * Add a "review pending changes" checkbox to the edit form iff:
	 * (a) there are currently any revisions pending (bug 16713)
	 * (b) this is an unreviewed page (bug 23970)
	 */
	public function addReviewCheck( EditPage $editPage, array &$checkboxes ): void {
		$request = $this->getRequest();
		$title = $this->article->getTitle(); // convenience
		if ( !$this->article->isReviewable() ||
			!MediaWikiServices::getInstance()->getPermissionManager()
				->userCan( 'review', $this->getUser(), $title )
		) {
			// Not needed
			return;
		} elseif ( $this->editWillBeAutoreviewed( $editPage ) ) {
			// Edit will be auto-reviewed
			return;
		}
		if ( self::getBaseRevId( $editPage, $request ) == $this->article->getLatest() ) {
			# For pages with either no stable version, or an outdated one, let
			# the user decide if he/she wants it reviewed on the spot. One might
			# do this if he/she just saw the diff-to-stable and *then* decided to edit.
			# Note: check not shown when editing old revisions, which is confusing.
			$name = 'wpReviewEdit';
			$options = [
				'label-message' => null,
				'id' => $name,
				'default' => $request->getCheck( $name ),
				'title-message' => null,
				'legacy-name' => 'reviewed',
			];
			// For reviewed pages...
			if ( $this->article->getStable() ) {
				// For pending changes...
				if ( $this->article->revsArePending() ) {
					$n = $this->article->getPendingRevCount();
					$options['title-message'] = 'revreview-check-flag-p-title';
					$options['label-message'] = $this->msg( 'revreview-check-flag-p' )
						->numParams( $n );
				// For just the user's changes...
				} else {
					$options['title-message'] = 'revreview-check-flag-y-title';
					$options['label-message'] = 'revreview-check-flag-y';
				}
			// For unreviewed pages...
			} else {
				$options['title-message'] = 'revreview-check-flag-u-title';
				$options['label-message'] = 'revreview-check-flag-u';
			}
			$checkboxes[$name] = $options;
		}
	}

	/**
	 * Guess the rev ID the text of this form is based off
	 */
	private static function getBaseRevId( EditPage $editPage, WebRequest $request ): int {
		if ( $editPage->isConflict ) {
			return 0; // throw away these values (bug 33481)
		}

		$article = $editPage->getArticle(); // convenience
		$latestId = $article->getPage()->getLatest(); // current rev
		# Undoing edits...
		if ( $request->getIntOrNull( 'wpUndidRevision' ) ?? $request->getIntOrNull( 'undo' ) ) {
			$revId = $latestId; // current rev is the base rev
		# Other edits...
		} else {
			# If we are editing via oldid=X, then use that rev ID.
			$revId = $article->getOldID();
		}
		# Zero oldid => draft revision
		$baseRevId = $revId ?: $latestId;

		return $baseRevId;
	}

	/**
	 * Guess the alternative rev ID the text of this form is based off.
	 * When undoing the top X edits, the base can be though of as either
	 * the current or the edit X edits prior to the latest.
	 */
	private static function getAltBaseRevId( EditPage $editPage, WebRequest $request ): int {
		if ( $editPage->isConflict ) {
			return 0; // throw away these values (bug 33481)
		}

		$article = $editPage->getArticle(); // convenience
		$latestId = $article->getPage()->getLatest(); // current rev
		$undo = $request->getIntOrNull( 'wpUndidRevision' ) ?? $request->getIntOrNull( 'undo' );
		# Undoing consecutive top edits...
		if ( $undo && $undo === $latestId ) {
			# Treat this like a revert to a base revision.
			# We are undoing all edits *after* some rev ID (undoafter).
			# If undoafter is not given, then it is the previous rev ID.
			$revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
			$revision = $revisionLookup->getRevisionById( $latestId );
			$previousRevision = $revision ? $revisionLookup->getPreviousRevision( $revision ) : null;
			$altBaseRevId = $request->getInt( 'wpUndoAfter', $request->getInt( 'undoafter',
				$previousRevision ? $previousRevision->getId() : null
			) );
		} else {
			$altBaseRevId = 0;
		}

		return $altBaseRevId;
	}
}
