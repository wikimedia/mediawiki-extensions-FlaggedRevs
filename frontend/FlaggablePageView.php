<?php

use MediaWiki\Cache\CacheKeyHelper;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Revision\RevisionRecord;

/**
 * Class representing a web view of a MediaWiki page
 */
class FlaggablePageView extends ContextSource {
	/** @var MapCacheLRU|null */
	private static $instances = null;

	/** @var OutputPage */
	private $out;
	/** @var FlaggableWikiPage */
	private $article;
	/** @var array|null of old and new RevisionsRecords for diffs */
	private $diffRevRecords = null;
	/** @var array|null [ array of templates ] */
	private $oldRevIncludes = null;
	/** @var bool */
	private $isReviewableDiff = false;
	/** @var bool */
	private $isDiffFromStable = false;
	/** @var bool */
	private $isMultiPageDiff = false;
	/** @var string */
	private $reviewNotice = '';
	/** @var string */
	private $diffNoticeBox = '';
	/** @var string */
	private $diffIncChangeBox = '';
	/** @var RevisionRecord|false */
	private $reviewFormRevRecord = false;
	/** @var bool */
	private $noticesDone = false;

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
	public static function newFromTitle( PageIdentity $title ) {
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
	public static function singleton() {
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
	public function clear() {
		self::$instances = null;
	}

	/**
	 * Get the FlaggableWikiPage instance associated with the current page title,
	 * or false if there isn't such a title
	 *
	 * @deprecated Use FlaggableWikiPage::getTitleInstance() instead
	 * @return FlaggableWikiPage|null
	 */
	public static function globalArticleInstance() {
		$title = RequestContext::getMain()->getTitle();
		if ( $title && $title->canExist() ) {
			return FlaggableWikiPage::getTitleInstance( $title );
		}
		return null;
	}

	/**
	 * Check if the old and new diff revs are set for this page view
	 * @return bool
	 */
	public function diffRevRecordsAreSet() {
		return (bool)$this->diffRevRecords;
	}

	/**
	 * Whether this web response is for a request to view a page where both:
	 * (a) no explicit page version was requested via URL params (e.g. for the default version)
	 * (b) a stable version exists and is to be displayed per configuration (e.g. the default)
	 * This factors in site/page config, user preferences, and web request params.
	 * @return bool
	 */
	private function showingStableAsDefault() {
		$reqUser = $this->getUser();
		$defaultForUser = $this->getPageViewStabilityModeForUser( $reqUser );

		return (
			# Request is for the default page version
			$this->isDefaultPageView() &&
			# Page is reviewable and has a stable version
			$this->article->getStableRev() &&
			# User is not configured to prefer current versions
			$defaultForUser !== FR_SHOW_STABLE_NEVER &&
			# User explicitly prefers stable versions of pages
			(
				$defaultForUser === FR_SHOW_STABLE_ALWAYS ||
				# Check if the stable version overrides the draft
				$this->article->getStabilitySettings()['override']
			)
		);
	}

	/**
	 * Is this web response for a request to view a page where both:
	 * (a) the stable version of a page was requested (?stable=1)
	 * (b) the stable version exists and is to be displayed
	 * @return bool
	 */
	private function showingStableByRequest() {
		$request = $this->getRequest();
		# Are we explicity requesting the stable version?
		if ( $request->getIntOrNull( 'stable' ) === 1 ) {
			# This only applies to viewing a version of the page...
			if ( !$this->isPageView() ) {
				return false;
			# ...with no version parameters other than ?stable=1...
			} elseif ( $request->getVal( 'oldid' ) || $request->getVal( 'stableid' ) ) {
				return false; // over-determined
			# ...and the page must be reviewable and have a stable version
			} elseif ( !$this->article->getStableRev() ) {
				return false;
			}
			return true; // show stable version
		}
		return false;
	}

	/**
	 * Is this web response for a request to view a page
	 * where a stable version exists and is to be displayed
	 * @return bool
	 */
	public function showingStable() {
		return $this->showingStableByRequest() || $this->showingStableAsDefault();
	}

	/**
	 * Should this be using a simple icon-based UI?
	 * Check the user's preferences first, using the site settings as the default.
	 * @return bool
	 */
	private function useSimpleUI() {
		$default = (int)$this->getConfig()->get( 'SimpleFlaggedRevsUI' );
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		return $userOptionsLookup->getOption(
			$this->getUser(),
			'flaggedrevssimpleui',
			$default
		);
	}

	/**
	 * What version of pages should this user see by default?
	 * @param User $user
	 * @return int One of the FR_SHOW_STABLE_* constants
	 */
	private function getPageViewStabilityModeForUser( $user ) {
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
	 * @return bool
	 */
	private function isPageViewOrDiff() {
		$action = $this->getActionName();
		return $action === 'view' || $action === 'purge' || $action === 'render';
	}

	/**
	 * Is this a view page action (not including diffs)?
	 * @return bool
	 */
	private function isPageView() {
		$request = $this->getRequest();
		return $this->isPageViewOrDiff()
			&& $request->getVal( 'diff' ) === null;
	}

	/**
	 * Is this a web request to just *view* the *default* version of a page?
	 * @return bool
	 */
	private function isDefaultPageView() {
		$request = $this->getRequest();
		return $this->isPageView()
			&& $request->getVal( 'oldid' ) === null
			&& $request->getVal( 'stable' ) === null
			&& $request->getVal( 'stableid' ) === null;
	}

	/**
	 * Output review notice
	 */
	public function displayTag() {
		// Sanity check that this is a reviewable page
		if ( $this->article->isReviewable() && $this->reviewNotice ) {
			$this->out->addSubtitle( $this->reviewNotice );
		}
	}

	/**
	 * Add a stable link when viewing old versions of an article that
	 * have been reviewed. (e.g. for &oldid=x urls)
	 */
	public function addStableLink() {
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
		$this->out->addHTML( Html::rawElement(
			'div',
			[
				'id' => 'mw-fr-revisiontag-old',
				'class' => 'flaggedrevs_notice plainlinks noprint',
			],
			$this->msg( 'revreview-basic-source', $frev->getRevId(), $time )->parse()
		) );
	}

	/**
	 * @return string|int|null
	 */
	private function getRequestedStableId() {
		$reqId = $this->getRequest()->getVal( 'stableid' );
		if ( $reqId === "best" ) {
			return $this->article->getBestFlaggedRevId();
		}
		return $reqId;
	}

	/**
	 * Replaces a page with the last stable version if possible
	 * Adds stable version status/info tags and notes
	 * Adds a quick review form on the bottom if needed
	 * @param bool|ParserOutput|null &$outputDone
	 * @param bool &$useParserCache
	 */
	public function setPageContent( &$outputDone, &$useParserCache ) {
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
		# Check the newest stable version.
		$srev = $this->article->getStableRev();
		$stableId = $srev ? $srev->getRevId() : 0;
		$frev = $srev; // $frev is the revision we are looking at
		# Check for any explicitly requested reviewed version (stableid=X)...
		$reqId = $this->getRequestedStableId();
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
		$this->enableOOUI();
		if ( $this->isOnMobile() ) {
			// It's not going to get injected, don't try to make it
			$prot = '';
		} else {
			$prot = FlaggedRevsXML::lockStatusIcon( $this->article );
		}

		if ( $frev ) { // has stable version?
			// Looking at some specific old stable revision ("&stableid=x")
			// set to override given the relevant conditions. If the user is
			// requesting the stable revision ("&stableid=x"), defer to override
			// behavior below, since it is the same as ("&stable=1").
			if ( $old ) {
				# Tell MW that parser output is done by setting $outputDone
				$outputDone = $this->showOldReviewedVersion( $frev, $tag, $prot );
				$useParserCache = false;
				$tagTypeClass = 'flaggedrevs_oldstable';
			// Stable version requested by ID or relevant conditions met to
			// to override page view with the stable version.
			} elseif ( $stable || $this->showingStable() ) {
				# Tell MW that parser output is done by setting $outputDone
				// @phan-suppress-next-line PhanTypeMismatchArgumentNullable FIXME, this should be unreachable with null
				$outputDone = $this->showStableVersion( $srev, $tag, $prot );
				$useParserCache = false;
				$tagTypeClass = ( $this->article->stableVersionIsSynced() ) ?
					'flaggedrevs_stable_synced' : 'flaggedrevs_stable_notsynced';
			// Looking at some specific old revision (&oldid=x) or if FlaggedRevs is not
			// set to override given the relevant conditions (like &stable=0).
			} else {
				// @phan-suppress-next-line PhanTypeMismatchArgumentNullable FIXME, this should be unreachable with null
				$this->showDraftVersion( $srev, $tag, $prot );
				$tagTypeClass = ( $this->article->stableVersionIsSynced() ) ?
					'flaggedrevs_draft_synced' : 'flaggedrevs_draft_notsynced';
			}
		} else {
			// Looking at a page with no stable version; add "no reviewed version" tag.
			$this->showUnreviewedPage( $tag, $prot );
			$tagTypeClass = 'flaggedrevs_unreviewed';
		}
		# Some checks for which tag CSS to use
		$inject = true;
		if ( $this->useSimpleUI() ) {
			$tagClass = 'flaggedrevs_short';
			$inject = !$this->isOnMobile();
		} else {
			// As it is the only message for non-simple UI, it must be displayed
			$tagClass = $frev ? 'flaggedrevs_basic' : 'flaggedrevs_notice';
		}
		# Wrap tag contents in a div, with class indicating sync status and
		# whether stable version is shown (for customization of the notice)
		if ( $tag != '' && $inject ) {
			$css = "{$tagClass} {$tagTypeClass} plainlinks noprint";
			$notice = "<div id=\"mw-fr-revisiontag\" class=\"{$css}\">{$tag}</div>\n";
			$this->reviewNotice .= $notice;
		}
	}

	/**
	 * @return bool
	 */
	private function isOnMobile() {
		return ExtensionRegistry::getInstance()->isLoaded( 'MobileFrontend' ) &&
			MobileContext::singleton()->shouldDisplayMobileView();
	}

	/**
	 * If the page has a stable version and it shows by default,
	 * tell search crawlers to index only that version of the page.
	 * Also index the draft as well if they are synced (bug 27173).
	 * However, any URL with ?stableid=x should not be indexed (as with ?oldid=x).
	 */
	public function setRobotPolicy() {
		$request = $this->getRequest();
		if ( $this->article->getStableRev() && $this->article->isStableShownByDefault() ) {
			if ( $this->showingStable() ) {
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
	 * @param string &$tag review box/bar info
	 * @param string $prot protection notice
	 * Tag output function must be called by caller
	 */
	private function showUnreviewedPage( &$tag, $prot ) {
		if ( $this->out->isPrintable() || $this->isOnMobile() ) {
			return; // all this function does is add notices; don't show them
		}
		$this->enableOOUI();
		$msg = $this->useSimpleUI() ? 'revreview-quick-none' : 'revreview-noflagged';
		$tag .= $prot . FlaggedRevsXML::draftStatusIcon() . $this->msg( $msg )->parse();
	}

	/**
	 * Tag output function must be called by caller
	 * Parser cache control deferred to caller
	 * @param FlaggedRevision $srev stable version
	 * @param string &$tag review box/bar info
	 * @param string $prot protection notice icon
	 * @return void
	 */
	private function showDraftVersion( FlaggedRevision $srev, &$tag, $prot ) {
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
			if ( $this->showRatingIcon() && !$this->isOnMobile() ) {
				$this->enableOOUI();
				$pending .= FlaggedRevsXML::draftStatusIcon();
			}
			$pending .= $this->msg( 'revreview-edited', $srev->getRevId() )
				->numParams( $revsSince )->parse();
			$anchor = $request->getVal( 'fromsection' );
			if ( $anchor != null ) {
				// Hack: reverse some of the Sanitizer::escapeId() encoding
				$section = urldecode( str_replace( // bug 35661
					[ ':' , '.' ], [ '%3A', '%' ], $anchor
				) );
				$section = str_replace( '_', ' ', $section ); // prettify
				$pending .= $this->msg( 'revreview-edited-section', $anchor, $section )
					->parseAsBlock();
			}
			# Notice should always use subtitle
			$this->reviewNotice = "<div id='mw-fr-reviewnotice' " .
				"class='flaggedrevs_preview plainlinks noprint'>$pending</div>";
		# Otherwise, construct some tagging info for non-printable outputs.
		# Also, if low profile UI is enabled and the page is synced, skip the tag.
		# Note: the "your edit is pending" notice has all this info, so we never add both.
		} elseif ( !( $this->article->lowProfileUI() && $synced ) && !$this->isOnMobile() ) {
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
				if ( $this->showRatingIcon() && !$this->isOnMobile() ) {
					$this->enableOOUI();
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
				$this->enableOOUI();
				$icon = $synced ?
					FlaggedRevsXML::stableStatusIcon() :
					FlaggedRevsXML::draftStatusIcon();
				$tag .= $prot . $icon . $msgHTML . $diffToggle;
			}
		}
	}

	/**
	 * Tag output function must be called by caller
	 * Parser cache control deferred to caller
	 * @param FlaggedRevision $frev selected flagged revision
	 * @param string &$tag review box/bar info
	 * @param string $prot protection notice icon
	 * @return ParserOutput|null
	 */
	private function showOldReviewedVersion( FlaggedRevision $frev, &$tag, $prot ) {
		$reqUser = $this->getUser();
		$time = $this->getLanguage()->date( $frev->getTimestamp(), true );
		# Set display revision ID
		$this->out->setRevisionId( $frev->getRevId() );

		# Construct some tagging for non-printable outputs. Note that the pending
		# notice has all this info already, so don't do this if we added that already.
		if ( !$this->out->isPrintable() && !$this->isOnMobile() ) {
			$this->enableOOUI();
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
		$parserOptions = $this->article->makeParserOptions( $reqUser );
		$parserOut = FlaggedRevs::parseStableRevision( $frev, $parserOptions );
		if ( !$parserOut ) {
			return null;
		}

		# Add the parser output to the page view
		$this->out->setSections( $parserOut->getSections() );
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
	 * @return ParserOutput|null
	 */
	private function showStableVersion( FlaggedRevision $srev, &$tag, $prot ) {
		$reqUser = $this->getUser();
		$time = $this->getLanguage()->date( $srev->getTimestamp(), true );
		# Set display revision ID
		$this->out->setRevisionId( $srev->getRevId() );
		$synced = $this->article->stableVersionIsSynced();
		# Construct some tagging
		if (
			!$this->out->isPrintable() &&
			!( $this->article->lowProfileUI() && $synced ) &&
			!$this->isOnMobile()
		) {
			$revsSince = $this->article->getPendingRevCount();
			// Simple icon-based UI
			if ( $this->useSimpleUI() ) {
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$icon = FlaggedRevsXML::stableStatusIcon();
					$this->enableOOUI();
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
				$icon = FlaggedRevsXML::stableStatusIcon();
				$this->enableOOUI();
				if ( $synced ) {
					$msg = 'revreview-basic-same';
				} else {
					$msg = !$revsSince ? 'revreview-basic-i' : 'revreview-basic';
				}
				$tag = $prot . $icon;
				$tag .= $this->msg( $msg, $srev->getRevId(), $time )
					->numParams( $revsSince )->parse();
			}
		}

		// TODO: Rewrite to use ParserOutputAccess
		# Check the stable version cache for the parser output
		/** @var FlaggedRevsParserCache $stableParserCache */
		$stableParserCache = MediaWikiServices::getInstance()->getService( 'FlaggedRevsParserCache' );
		$parserOptions = $this->article->makeParserOptions( $reqUser );
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
					$this->showPoolError( $status );
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
			$this->showMissingRevError( $srev->getRevId() );
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
		$this->out->setSections( $parserOut->getSections() );
		$this->out->addParserOutput( $parserOut, $poOptions );

		# Update page sync status for tracking purposes.
		# NOTE: avoids primary hits and doesn't have to be perfect for what it does
		if ( $this->article->syncedInTracking() != $synced ) {
			$this->article->lazyUpdateSyncStatus();
		}

		return $parserOut;
	}

	private function enableOOUI() {
		// Loading icons is pretty expensive, see T181108
		if ( $this->isOnMobile() ) {
			return;
		}

		$this->out->addModuleStyles( 'ext.flaggedRevs.icons' );
		$this->out->enableOOUI();
	}

	/**
	 * @param Status $status
	 */
	private function showPoolError( Status $status ) {
		$this->out->disableClientCache();
		$this->out->setRobotPolicy( 'noindex,nofollow' );

		$errortext = $status->getWikiText( false, 'view-pool-error' );
		$this->out->addHTML(
			Html::errorBox( $this->out->parseAsContent( $errortext ) )
		);
	}

	/**
	 * @param int $revId
	 */
	private function showMissingRevError( $revId ) {
		$this->out->disableClientCache();
		$this->out->setRobotPolicy( 'noindex,nofollow' );

		$this->out->addWikiMsg( 'missing-article',
			$this->article->getTitle()->getPrefixedText(),
			$this->msg( 'missingarticle-rev', $revId )->plain()
		);
	}

	/**
	 * Show icons for draft/stable/old reviewed versions
	 * @return bool
	 */
	private function showRatingIcon() {
		// If there is only one quality level and we have tabs to know which version we are looking
		// at, then just use the lock icon...
		return !FlaggedRevs::useOnlyIfProtected();
	}

	/**
	 * Get collapsible diff-to-stable html to add to the review notice as needed
	 * @param FlaggedRevision $srev stable version
	 * @return string|false the html line (either "" or "<diff toggle><diff div>")
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
		# Review status of left diff revision...
		$leftNote = 'revreview-hist-basic';
		$lClass = FlaggedRevsXML::getQualityColor( FR_CHECKED );
		// @todo FIXME: i18n Hard coded brackets.
		$leftNote = "<span class='$lClass'>[" . $this->msg( $leftNote )->escaped() . "]</span>";
		# Review status of right diff revision...
		$rClass = FlaggedRevsXML::getQualityColor( false );
		// @todo FIXME: i18n Hard coded brackets.
		$rightNote = "<span class='$rClass'>[" .
			$this->msg( 'revreview-hist-pending' )->escaped() . "]</span>";
		# Get the actual body of the diff...
		$diffEngine = new DifferenceEngine( $this, $srev->getRevId(), $latest );
		$diffBody = $diffEngine->getDiffBody();
		if ( strlen( $diffBody ) > 0 ) {
			$nEdits = $revsSince - 1; // full diff-to-stable, no need for query
			if ( $nEdits ) {
				$limit = 100;
				try {
					$latest = MediaWikiServices::getInstance()
						->getRevisionLookup()
						->getRevisionById( $latest );
					$users = MediaWikiServices::getInstance()
						->getRevisionStore()
						->getAuthorsBetween(
							$title->getArticleID(),
							$srev->getRevisionRecord(),
							$latest,
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
			$diffEngine->showDiffStyle(); // add CSS
			$this->isDiffFromStable = true; // alter default review form tags
			return FlaggedRevsXML::diffToggle() .
				"<div id='mw-fr-stablediff'>\n" .
				$diffEngine->addHeader( $diffBody, "<b>$leftNote</b>", "<b>$rightNote</b>", $multiNotice ) .
				"</div>\n";
		}

		return '';
	}

	/**
	 * Adds stable version tags to page when viewing history
	 */
	public function addToHistView() {
		# Add a notice if there are pending edits...
		$srev = $this->article->getStableRev();
		if ( $srev && $this->article->revsArePending() ) {
			$revsSince = $this->article->getPendingRevCount();
			$this->enableOOUI();
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
	public function getEditNotices( Title $title, $oldid, array &$notices ) {
		// HACK: EditPage invokes addToEditView() before this function, so $this->noticesDone
		// will only be true if we're being called by EditPage, in which case we need to do nothing
		// to avoid duplicating the notices.
		if ( $this->noticesDone || !$this->article->isReviewable() ) {
			return;
		}
		// HACK fake EditPage
		$editPage = new EditPage( new Article( $title, $oldid ) );
		$editPage->oldid = $oldid;
		$reqUser = $this->getUser();

		// HACK this duplicates logic from addToEditView()
		$log = $this->stabilityLogNotice( false );
		if ( $log ) {
			$notices[$this->article->isPageLocked()
				? 'revreview-locked'
				: 'revreview-unlocked'] = $log;
		} elseif ( $this->editWillRequireReview( $editPage ) ) {
			$notices['revreview-editnotice'] = $this->msg( 'revreview-editnotice' )->parseAsBlock();
		}
		$frev = $this->article->getStableRev();
		if ( $frev && $this->article->revsArePending() ) {
			$revsSince = $this->article->getPendingRevCount();
			$pendingMsg = FlaggedRevsXML::pendingEditNoticeMessage(
				$frev, $revsSince
			);
			$notices[$pendingMsg->getKey()] = '<div class="plainlinks">'
				. $pendingMsg->parseAsBlock() . '</div>';
		}
		$latestId = $this->article->getLatest();
		$revId  = $oldid ?: $latestId;
		if ( $frev && $frev->getRevId() < $latestId // changes were made
			&& MediaWikiServices::getInstance()->getUserOptionsLookup()
				->getBoolOption( $reqUser, 'flaggedrevseditdiffs' ) // not disabled via prefs
			&& $revId === $latestId // only for current rev
		) {
			// Construct a link to the diff
			$diffUrl = $this->article->getTitle()->getFullURL( [
				'diff' => $revId, 'oldid' => $frev->getRevId() ]
			);
			$notices['review-edit-diff'] = $this->msg( 'review-edit-diff' )->parse() . ' ' .
				FlaggedRevsXML::diffToggle( $diffUrl );
		}

		if ( $frev && $this->article->onlyTemplatesPending() &&
			$this->article->getPendingRevCount() == 0
		) {
			$this->setPendingNotice( $frev, '', false );
			$notices['review-transclusions'] = $this->reviewNotice;
		}
	}

	/**
	 * Adds stable version tags to page when editing
	 * @param EditPage $editPage
	 */
	public function addToEditView( EditPage $editPage ) {
		$reqUser = $this->getUser();

		# Must be reviewable. UI may be limited to unobtrusive patrolling system.
		if ( !$this->article->isReviewable() ) {
			return;
		}
		$items = [];
		# Show stabilization log
		$log = $this->stabilityLogNotice();
		if ( $log ) {
			$items[] = $log;
		}
		# Check the newest stable version
		$frev = $this->article->getStableRev();
		if ( $frev ) {
			# Find out revision id of base version
			$latestId = $this->article->getLatest();
			$revId = $editPage->oldid ?: $latestId;
			# Let users know if their edit will have to be reviewed.
			# Note: if the log excerpt was shown then this is redundant.
			if ( !$log && $this->editWillRequireReview( $editPage ) ) {
				$items[] = $this->msg( 'revreview-editnotice' )->parse();
			}
			# Add a notice if there are pending edits...
			if ( $this->article->revsArePending() ) {
				$revsSince = $this->article->getPendingRevCount();
				$items[] = FlaggedRevsXML::pendingEditNotice( $frev, $revsSince );
			}
			# Show diff to stable, to make things less confusing.
			# This can be disabled via user preferences and other conditions...
			if ( $frev->getRevId() < $latestId // changes were made
				&& MediaWikiServices::getInstance()->getUserOptionsLookup()
					->getBoolOption( $reqUser, 'flaggedrevseditdiffs' ) // not disable via prefs
				&& $revId == $latestId // only for current rev
				&& $editPage->section != 'new' // not for new sections
				&& $editPage->formtype != 'diff' // not "show changes"
			) {
				# Left diff side...
				$leftNote = 'revreview-hist-basic';
				$lClass = FlaggedRevsXML::getQualityColor( FR_CHECKED );
				// @todo i18n FIXME: Hard coded brackets
				$leftNote = "<span class='$lClass'>[" .
					$this->msg( $leftNote )->escaped() . "]</span>";
				# Right diff side...
				$rClass = FlaggedRevsXML::getQualityColor( false );
				// @todo i18n FIXME: Hard coded brackets
				$rightNote = "<span class='$rClass'>[" .
					$this->msg( 'revreview-hist-pending' )->escaped() . "]</span>";
				# Get the stable version source
				$text = $frev->getRevText();
				# Are we editing a section?
				$section = ( $editPage->section == "" ) ?
					false : intval( $editPage->section );
				if ( $section !== false ) {
					$text = MediaWikiServices::getInstance()->getParser()->getSection( $text, $section );
				}
				if ( $text !== null && strcmp( $text, $editPage->textbox1 ) !== 0 ) {
					$diffEngine = new DifferenceEngine( $this );
					$diffBody = $diffEngine->generateTextDiffBody( $text, $editPage->textbox1 );
					$diffHtml =
						$this->msg( 'review-edit-diff' )->parse() . ' ' .
						FlaggedRevsXML::diffToggle() .
						"<div id='mw-fr-stablediff'>" .
						$diffEngine->addHeader( $diffBody, "<b>$leftNote</b>", "<b>$rightNote</b>" ) .
						"</div>\n";
					$items[] = $diffHtml;
					$diffEngine->showDiffStyle(); // add CSS
				}
			}
			# Output items
			if ( count( $items ) ) {
				$html = "<table class='flaggedrevs_editnotice plainlinks'>";
				foreach ( $items as $item ) {
					$html .= '<tr><td>' . $item . '</td></tr>';
				}
				$html .= '</table>';
				// @phan-suppress-next-line SecurityCheck-XSS Possible DiffEngine::generateTextDiffBody
				$this->out->addHTML( $html );
			}
		}
		$this->noticesDone = true;
	}

	/**
	 * @param bool $showToggle
	 * @return string
	 */
	private function stabilityLogNotice( $showToggle = true ) {
		$s = '';
		# Only for pages manually made to be stable...
		if ( $this->article->isPageLocked() ) {
			$s = $this->msg( 'revreview-locked' )->parse();
			if ( $showToggle ) {
				$s .= ' ' . FlaggedRevsXML::logDetailsToggle();
			}
			$s .= FlaggedRevsXML::stabilityLogExcerpt( $this->article );
		# ...or unstable
		} elseif ( $this->article->isPageUnlocked() ) {
			$s = $this->msg( 'revreview-unlocked' )->parse();
			if ( $showToggle ) {
				$s .= ' ' . FlaggedRevsXML::logDetailsToggle();
			}
			$s .= FlaggedRevsXML::stabilityLogExcerpt( $this->article );
		}
		return $s;
	}

	/**
	 * @param string &$s
	 */
	public function addToNoSuchSection( &$s ) {
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
	public function addToCategoryView() {
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
	public function addReviewForm( &$output ) {
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

			# $wgOut might not have the inclusion IDs, such as for diffs with diffonly=1.
			# If they're lacking, then we use getRevIncludes() to get the draft inclusion versions.
			# Note: showStableVersion() already makes sure that $wgOut
			# has the stable inclusion versions.
			if ( FlaggedRevs::inclusionSetting() === FR_INCLUDES_CURRENT ) {
				$tmpVers = []; // unused
			} elseif ( $this->out->getRevisionId() == $revRecord->getId() ) {
				$tmpVers = $this->out->getTemplateIds();
			} elseif ( $this->oldRevIncludes ) { // e.g. diffonly=1, stable diff
				# We may have already fetched the inclusion IDs to get the template changes.
				$tmpVers = $this->oldRevIncludes[0]; // reuse
			} else { // e.g. diffonly=1, other diffs
				# $wgOut may not already have the inclusion IDs, such as for diffonly=1.
				# RevisionReviewForm will fetch them as needed however.
				$tmpVers = FRInclusionCache::getRevIncludes(
					$this->article,
					$revRecord,
					$reqUser
				)[0];
			}
			$form->setIncludeVersions( $tmpVers );

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
	public function addStabilizationLink() {
		$request = $this->getRequest();
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			// Simple custom levels set for action=protect
			return;
		}
		# Check only if the title is reviewable
		if ( !FlaggedRevs::inReviewNamespace( $this->article->getTitle() ) ) {
			return;
		}
		$action = $request->getVal( 'action', 'view' );
		if ( $action == 'protect' || $action == 'unprotect' ) {
			$title = SpecialPage::getTitleFor( 'Stabilization' );
			# Give a link to the page to configure the stable version
			$frev = $this->article->getStableRev();
			if ( $frev && $frev->getRevId() == $this->article->getLatest() ) {
				$this->out->prependHTML(
					"<span class='revreview-visibility revreview-visibility-synced plainlinks'>" .
					$this->msg( 'revreview-visibility-synced',
						$title->getPrefixedText() )->parse() . "</span>" );
			} elseif ( $frev ) {
				$this->out->prependHTML(
					"<span class='revreview-visibility revreview-visibility-outdated plainlinks'>" .
					$this->msg( 'revreview-visibility-outdated',
						$title->getPrefixedText() )->parse() . "</span>" );
			} else {
				$this->out->prependHTML(
					"<span class='revreview-visibility revreview-visibility-nostable plainlinks'>" .
					$this->msg( 'revreview-visibility-nostable',
						$title->getPrefixedText() )->parse() . "</span>" );
			}
		}
	}

	/**
	 * Modify an array of action links, as used by SkinTemplateNavigation and
	 * SkinTemplateTabs, to inlude flagged revs UI elements
	 * @param array &$actions
	 */
	public function setActionTabs( array &$actions ) {
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
	public function setViewTabs( Skin $skin, array &$views ) {
		if ( !FlaggedRevs::inReviewNamespace( $this->article->getTitle() ) ) {
			// Short-circuit for non-reviewable pages
			return;
		}
		# Hack for bug 16734 (some actions update and view all at once)
		if ( $this->pageWriteOpRequested() &&
			MediaWikiServices::getInstance()->getDBLoadBalancer()->hasOrMadeRecentPrimaryChanges()
		) {
			# Tabs need to reflect the new stable version so users actually
			# see the results of their action (i.e. "delete"/"rollback")
			$this->article->loadPageData( FlaggableWikiPage::READ_LATEST );
		}
		$srev = $this->article->getStableRev();
		if ( !$srev ) {
			// No stable revision exists
			return;
		}
		$synced = $this->article->stableVersionIsSynced();
		$pendingEdits = !$synced && $this->article->isStableShownByDefault();
		// Set the edit tab names as needed...
		if ( $pendingEdits && $this->showingStable() ) {
			// bug 31489; direct user to current
			if ( isset( $views['edit'] ) ) {
				$views['edit']['href'] = $skin->getTitle()->getFullURL( 'action=edit' );
			}
			if ( isset( $views['viewsource'] ) ) {
				$views['viewsource']['href'] = $skin->getTitle()->getFullURL( 'action=edit' );
			}
			// Instruct alternative editors like VisualEditor to load the latest ("current")
			// revision for editing, rather than the one from 'wgRevisionId'
			$skin->getOutput()->addJsConfigVars( 'wgFlaggedRevsEditLatestRevision', true );
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
	private function addDraftTab( array &$views, FlaggedRevision $srev ) {
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
		if ( $this->showingStable() || $request->getVal( 'stableid' ) ) {
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
	 * @return bool
	 */
	private function pageWriteOpRequested() {
		$request = $this->getRequest();
		# Hack for bug 16734 (some actions update and view all at once)
		$action = $request->getVal( 'action' );
		return $action === 'rollback' ||
			( $action === 'delete' && $request->wasPosted() );
	}

	/**
	 * @return int
	 */
	private function getOldIDFromRequest() {
		$article = Article::newFromWikiPage( $this->article, RequestContext::getMain() );
		return $article->getOldIDFromRequest();
	}

	/**
	 * Adds a notice saying that this revision is pending review
	 * @param FlaggedRevision $srev The stable version
	 * @param string $diffToggle either "" or " <diff toggle><diff div>"
	 * @param bool $background Whether to add the 'flaggedrevs_preview' CSS class (the blue background)
	 *   (the blue background)
	 * @return void
	 */
	private function setPendingNotice(
		FlaggedRevision $srev, $diffToggle = '', $background = true
	) {
		$time = $this->getLanguage()->date( $srev->getTimestamp(), true );
		$revsSince = $this->article->getPendingRevCount();
		$msg = !$revsSince ? 'revreview-newest-basic-i' : 'revreview-newest-basic';
		# Add bar msg to the top of the page...
		$css = 'plainlinks';
		if ( $background ) {
			$css .= ' flaggedrevs_preview';
		}
		$msgHTML = $this->msg( $msg, $srev->getRevId(), $time )->numParams( $revsSince )->parse();
		$this->reviewNotice .= "<div id='mw-fr-reviewnotice' class='$css'>" .
			"$msgHTML$diffToggle</div>";
	}

	/**
	 * When viewing a diff:
	 * (a) Add the review form to the top of the page
	 * (b) Mark off which versions are checked or not
	 * (c) When comparing the stable revision to the current:
	 *   (i)  Show a tag with some explanation for the diff
	 *   (ii) List any template changes pending review
	 *
	 * @param RevisionRecord|null $oldRevRecord
	 * @param RevisionRecord|null $newRevRecord
	 */
	public function addToDiffView( $oldRevRecord, $newRevRecord ) {
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
		# (b) list template changes if only includes are pending
		if ( $srev
			&& $this->isDiffFromStable
			&& !$this->article->stableVersionIsSynced() // pending changes
		) {
			$changeText = '';
			# Page not synced only due to includes?
			if ( !$this->article->revsArePending() ) {
				# Add a list of links to each changed template...
				$changeList = self::fetchTemplateChanges( $srev );
				# Correct bad cache which said they were not synced...
				if ( !count( $changeList ) ) {
					$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
					$cache->set(
						$cache->makeKey( 'flaggedrevs-includes-synced', $this->article->getId() ),
						1,
						$this->getConfig()->get( 'ParserCacheExpireTime' )
					);
				}
			# Otherwise, check for includes pending on top of edits pending...
			} elseif ( FlaggedRevs::inclusionSetting() !== FR_INCLUDES_CURRENT ) {
				$incs = FRInclusionCache::getRevIncludes(
					$this->article,
					$newRevRecord,
					$reqUser
				);
				$this->oldRevIncludes = $incs; // process cache
				# Add a list of links to each changed template...
				$changeList = self::fetchTemplateChanges( $srev, $incs[0] );
			} else {
				$changeList = []; // unused
			}
			# If there are pending revs or templates changes, notify the user...
			if ( $this->article->revsArePending() || count( $changeList ) ) {
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
				# Add include change list...
				if ( count( $changeList ) ) { // just inclusion changes
					$changeText .= "<p>" .
						$this->msg( 'revreview-update-includes' )->parse() .
						'&#160;' . implode( ', ', $changeList ) . "</p>\n";
				}
			}
			# template change list
			if ( $changeText != '' ) {
				if ( $pm->userHasRight( $reqUser, 'review' ) ) {
					$this->diffIncChangeBox = "<p>$changeText</p>";
				} else {
					$css = 'flaggedrevs_diffnotice plainlinks';
					$this->out->addHTML(
						"<div id='mw-fr-difftostable' class='$css'>$changeText</div>\n"
					);
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
	 * @return string
	 */
	public static function buildDiffHeaderItems() {
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
	 * @param FlaggableWikiPage $article
	 * @param RevisionRecord|null $oldRevRecord
	 * @param RevisionRecord|null $newRevRecord
	 * @return string
	 */
	private static function diffLinkAndMarkers(
		FlaggableWikiPage $article,
		$oldRevRecord,
		$newRevRecord
	) {
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
	 * @param FlaggableWikiPage $article
	 * @param RevisionRecord $oldRevRecord
	 * @param RevisionRecord $newRevRecord
	 * @return string
	 */
	private static function diffToStableLink(
		FlaggableWikiPage $article,
		RevisionRecord $oldRevRecord,
		RevisionRecord $newRevRecord
	) {
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
				[ 'oldid' => $srev->getRevId(), 'diff' => 'cur' ] + FlaggedRevs::diffOnlyCGI()
			);
			$reviewWrapped = wfMessage( 'parentheses' )->rawParams( $reviewLink )->escaped();
			$review = "<div class='fr-diff-to-stable' style='text-align: center;'>$reviewWrapped</div>";
		}
		return $review;
	}

	/**
	 * Add [checked version] and such to left and right side of diff
	 * @param FlaggableWikiPage $article
	 * @param RevisionRecord|null $oldRevRecord
	 * @param RevisionRecord|null $newRevRecord
	 * @return string
	 */
	private static function diffReviewMarkers(
		FlaggableWikiPage $article,
		$oldRevRecord,
		$newRevRecord
	) {
		$table = '';
		$srev = $article->getStableRev();
		# Diff between two revisions
		if ( $oldRevRecord && $newRevRecord ) {
			list( $msg, $class ) = self::getDiffRevMsgAndClass( $oldRevRecord, $srev );
			$table .= "<table class='fr-diff-ratings'><tr>";
			$table .= "<td style='text-align: center; width: 50%;'>";
			// @todo i18n FIXME: Hard coded brackets
			$table .= "<span class='$class'>[" .
				wfMessage( $msg )->escaped() . "]</span>";

			list( $msg, $class ) = self::getDiffRevMsgAndClass( $newRevRecord, $srev );
			$table .= "</td><td style='text-align: center; width: 50%;'>";
			// @todo i18n FIXME: Hard coded brackets
			$table .= "<span class='$class'>[" .
				wfMessage( $msg )->escaped() . "]</span>";

			$table .= "</td></tr></table>\n";
		# New page "diffs" - just one rev
		} elseif ( $newRevRecord ) {
			list( $msg, $class ) = self::getDiffRevMsgAndClass( $newRevRecord, $srev );
			$table .= "<table class='fr-diff-ratings'>";
			$table .= "<tr><td style='text-align: center;'><span class='$class'>";
			// @todo i18n FIXME: Hard coded brackets
			$table .= '[' . wfMessage( $msg )->escaped() . ']';
			$table .= "</span></td></tr></table>\n";
		}
		return $table;
	}

	/**
	 * @param RevisionRecord $revRecord
	 * @param FlaggedRevision|null $srev
	 *
	 * @return string[]
	 */
	private static function getDiffRevMsgAndClass(
		RevisionRecord $revRecord, FlaggedRevision $srev = null
	) {
		$tier = FlaggedRevision::getRevQuality( $revRecord->getId() );
		if ( $tier === FR_CHECKED ) {
			$msg = 'revreview-hist-basic';
		} else {
			$msg = ( $srev && $revRecord->getTimestamp() > $srev->getRevTimestamp() ) ? // bug 15515
				'revreview-hist-pending' :
				'revreview-hist-draft';
		}
		$css = FlaggedRevsXML::getQualityColor( $tier );
		return [ $msg, $css ];
	}

	/**
	 * Fetch template changes for a reviewed revision since review
	 * @param FlaggedRevision $frev
	 * @param int[][]|null $newTemplates
	 * @return string[]
	 */
	private static function fetchTemplateChanges( FlaggedRevision $frev, $newTemplates = null ) {
		$diffLinks = [];
		if ( $newTemplates === null ) {
			$changes = $frev->findPendingTemplateChanges();
		} else {
			$changes = $frev->findTemplateChanges( $newTemplates );
		}
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		foreach ( $changes as $tuple ) {
			list( $title, $revIdStable, $hasStable ) = $tuple;
			$link = $linkRenderer->makeKnownLink(
				$title,
				$title->getPrefixedText(),
				[],
				[ 'diff' => 'cur', 'oldid' => $revIdStable ] );
			if ( !$hasStable ) {
				$link = "<strong>$link</strong>";
			}
			$diffLinks[] = $link;
		}
		return $diffLinks;
	}

	/**
	 * Set $this->isDiffFromStable and $this->isMultiPageDiff fields
	 * @param DifferenceEngine $diff
	 * @param RevisionRecord|null $oldRevRecord
	 * @param RevisionRecord|null $newRevRecord
	 */
	public function setViewFlags( $diff, $oldRevRecord, $newRevRecord ) {
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
	 * @param FlaggedRevision|false $srev
	 * @param RevisionRecord|false $oldRevRecord
	 * @param RevisionRecord|false $newRevRecord
	 * @return bool
	 */
	private static function isDiffToStable( $srev, $oldRevRecord, $newRevRecord ) {
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
	 * @param string &$sectionAnchor
	 * @param string &$extraQuery
	 */
	public function injectPostEditURLParams( &$sectionAnchor, &$extraQuery ) {
		$reqUser = $this->getUser();
		$this->article->loadPageData( FlaggableWikiPage::READ_LATEST );
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
		if ( $this->article->revsArePending() && $frev->userCanSetFlags( $reqUser ) ) {
			$params += [ 'oldid' => $frev->getRevId(), 'diff' => 'cur', 'shownotice' => 1 ];
			$params += FlaggedRevs::diffOnlyCGI();
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
	 * @todo would be nice if hook passed in button attribs, not XML
	 * @param EditPage $editPage
	 * @param \OOUI\ButtonInputWidget[] $buttons
	 */
	public function changeSaveButton( EditPage $editPage, array $buttons ) {
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
	 * @param EditPage $editPage
	 * @return bool
	 */
	private function editWillRequireReview( EditPage $editPage ) {
		$request = $this->getRequest(); // convenience
		$title = $this->article->getTitle(); // convenience
		if ( !$this->editRequiresReview( $editPage ) ) {
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
	 * If this edit will not go live on submit unless wpReviewEdit is checked
	 * @param EditPage $editPage
	 * @return bool
	 */
	private function editRequiresReview( EditPage $editPage ) {
		if ( !$this->article->editsRequireReview() ) {
			return false; // edits go live immediatly
		} elseif ( $this->editWillBeAutoreviewed( $editPage ) ) {
			return false; // edit will be autoreviewed anyway
		}
		return true; // edit needs review
	}

	/**
	 * If this edit will be auto-reviewed on submit
	 * Note: checking wpReviewEdit does not count as auto-reviewed
	 * @param EditPage $editPage
	 * @return bool
	 */
	private function editWillBeAutoreviewed( EditPage $editPage ) {
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
			if ( !isset( $editPage->fr_baseFRev ) ) {
				$baseRevId = self::getBaseRevId( $editPage, $this->getRequest() );
				$baseRevId2 = self::getAltBaseRevId( $editPage, $this->getRequest() );
				$editPage->fr_baseFRev = FlaggedRevision::newFromTitle( $title, $baseRevId );
				if ( !$editPage->fr_baseFRev && $baseRevId2 ) {
					$editPage->fr_baseFRev = FlaggedRevision::newFromTitle( $title, $baseRevId2 );
				}
			}
			if ( $editPage->fr_baseFRev ) {
				return true; // edit will be autoreviewed
			}
		}
		return false; // edit won't be autoreviewed
	}

	/**
	 * Add a "review pending changes" checkbox to the edit form iff:
	 * (a) there are currently any revisions pending (bug 16713)
	 * (b) this is an unreviewed page (bug 23970)
	 * @param EditPage $editPage
	 * @param array &$checkboxes
	 */
	public function addReviewCheck( EditPage $editPage, array &$checkboxes ) {
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
	 * (a) Add a hidden field that has the rev ID the text is based off.
	 * (b) If an edit was undone, add a hidden field that has the rev ID of that edit.
	 * Needed for autoreview and user stats (for autopromote).
	 * Note: baseRevId trusted for Reviewers - text checked for others.
	 * @param EditPage $editPage
	 * @param OutputPage $out
	 */
	public function addRevisionIDField( EditPage $editPage, OutputPage $out ) {
		$out->addHTML( "\n" . Html::hidden( 'baseRevId',
			self::getBaseRevId( $editPage, $this->getRequest() ) ) );
		$out->addHTML( "\n" . Html::hidden( 'altBaseRevId',
			self::getAltBaseRevId( $editPage, $this->getRequest() ) ) );
		$out->addHTML( "\n" . Html::hidden( 'undidRev', $editPage->undidRev ) );
	}

	/**
	 * Guess the rev ID the text of this form is based off
	 * Note: baseRevId trusted for Reviewers - check text for others.
	 * @param EditPage $editPage
	 * @param WebRequest $request
	 * @return int
	 */
	private static function getBaseRevId( EditPage $editPage, WebRequest $request ) {
		if ( $editPage->isConflict ) {
			return 0; // throw away these values (bug 33481)
		}
		if ( !isset( $editPage->fr_baseRevId ) ) {
			$article = $editPage->getArticle(); // convenience
			$latestId = $article->getPage()->getLatest(); // current rev
			# Undoing edits...
			if ( $request->getIntOrNull( 'undo' ) ) {
				$revId = $latestId; // current rev is the base rev
			# Other edits...
			} else {
				# If we are editing via oldid=X, then use that rev ID.
				# Otherwise, check if the client specified the ID (bug 23098).
				$revId = $article->getOldID() ?:
					$request->getInt( 'baseRevId' ); // e.g. "show changes"/"preview"
			}
			# Zero oldid => draft revision
			$editPage->fr_baseRevId = $revId ?: $latestId;
		}
		return $editPage->fr_baseRevId;
	}

	/**
	 * Guess the alternative rev ID the text of this form is based off.
	 * When undoing the top X edits, the base can be though of as either
	 * the current or the edit X edits prior to the latest.
	 * Note: baseRevId trusted for Reviewers - check text for others.
	 * @param EditPage $editPage
	 * @param WebRequest $request
	 * @return int
	 */
	private static function getAltBaseRevId( EditPage $editPage, WebRequest $request ) {
		if ( $editPage->isConflict ) {
			return 0; // throw away these values (bug 33481)
		}
		if ( !isset( $editPage->fr_altBaseRevId ) ) {
			$article = $editPage->getArticle(); // convenience
			$latestId = $article->getPage()->getLatest(); // current rev
			$undo = $request->getIntOrNull( 'undo' );
			# Undoing consecutive top edits...
			if ( $undo && $undo === $latestId ) {
				# Treat this like a revert to a base revision.
				# We are undoing all edits *after* some rev ID (undoafter).
				# If undoafter is not given, then it is the previous rev ID.
				$revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
				$revision = $revisionLookup->getRevisionById( $latestId );
				$previousRevision = $revision ? $revisionLookup->getPreviousRevision( $revision ) : null;
				$revId = $request->getInt( 'undoafter',
					$previousRevision ? $previousRevision->getId() : null
				);
			} else {
				$revId = $request->getInt( 'altBaseRevId' );
			}
			$editPage->fr_altBaseRevId = $revId;
		}
		return $editPage->fr_altBaseRevId;
	}
}
