<?php
/**
 * Class representing a web view of a MediaWiki page
 */
class FlaggedArticleView {
	protected $article = null;

	protected $isDiffFromStable = false;
	protected $isMultiPageDiff = false;
	protected $reviewNotice = '';
	protected $reviewNotes = '';
	protected $diffNoticeBox = '';
	protected $reviewFormRev = false;

	protected $loaded = false;

	protected static $instance = null;

	/*
	* Get the FlaggedArticleView for this request
	*/
	public static function singleton() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	protected function __construct() { }
	protected function __clone() { }

	/*
	* Load the global FlaggedArticle instance
	*/
	protected function load() {
		if ( !$this->loaded ) {
			$this->loaded = true;
			$this->article = self::globalArticleInstance();
			if ( $this->article == null ) {
				throw new MWException( 'FlaggedArticleView has no context article!' );
			}
		}
	}

	/**
	 * Get the FlaggedArticle instance associated with $wgArticle/$wgTitle,
	 * or false if there isn't such a title
	 */
	public static function globalArticleInstance() {
		global $wgTitle;
		if ( !empty( $wgTitle ) ) {
			return FlaggedArticle::getTitleInstance( $wgTitle );
		}
		return null;
	}

	/**
	 * Do the config and current URL params allow
	 * for content overriding by the stable version?
	 * @returns bool
	 */
	public function pageOverride() {
		global $wgUser, $wgRequest;
		$this->load();
		# This only applies to viewing content pages
		$action = $wgRequest->getVal( 'action', 'view' );
		if ( !self::isViewAction( $action ) || !$this->article->isReviewable() ) {
			return false;
		}
		# Does not apply to diffs/old revision...
		if ( $wgRequest->getVal( 'oldid' ) || $wgRequest->getVal( 'diff' ) ) {
			return false;
		}
		# Explicit requests  for a certain stable version handled elsewhere...
		if ( $wgRequest->getVal( 'stableid' ) ) {
			return false;
		}
		# Check user preferences
		if ( $wgUser->getOption( 'flaggedrevsstable' ) ) {
			return !( $wgRequest->getIntOrNull( 'stable' ) === 0 );
		}
		# Get page configuration
		$config = $this->article->getVisibilitySettings();
		# Does the stable version override the current one?
		if ( $config['override'] ) {
			if ( FlaggedRevs::ignoreDefaultVersion() ) {
				return ( $wgRequest->getIntOrNull( 'stable' ) === 1 );
			}
			# Viewer sees stable by default
			return !( $wgRequest->getIntOrNull( 'stable' ) === 0 );
		# We are explicity requesting the stable version?
		} elseif ( $wgRequest->getIntOrNull( 'stable' ) === 1 ) {
			return true;
		}
		return false;
	}

	 /**
	 * Is this user shown the stable version by default for this page?
	 * @returns bool
	 */
	public function isStableShownByDefaultUser() {
		$this->load();
		if ( $this->article->isReviewable() ) {
			$config = $this->article->getVisibilitySettings(); // page configuration
			return ( $config['override'] && !FlaggedRevs::ignoreDefaultVersion() );
		}
		return false; // no stable
	}
	
	 /**
	 * Is this user shown the diff-to-stable on edit for this page?
	 * @returns bool
	 */
	public function isDiffShownOnEdit() {
		global $wgUser;
		$this->load();
		return ( $wgUser->isAllowed( 'review' ) || $this->isStableShownByDefaultUser() );
	}

	 /**
	 * Is this a view page action?
	 * @param $action string
	 * @returns bool
	 */
	protected static function isViewAction( $action ) {
		return ( $action == 'view' || $action == 'purge' || $action == 'render'
			|| $action == 'historysubmit' );
	}

	 /**
	 * Output review notice
	 */
	public function displayTag() {
		global $wgOut;
		$this->load();
		// Sanity check that this is a reviewable page
		if ( $this->article->isReviewable() ) {
			$wgOut->appendSubtitle( $this->reviewNotice );
		}
		return true;
	}

	 /**
	 * Add a stable link when viewing old versions of an article that
	 * have been reviewed. (e.g. for &oldid=x urls)
	 */
	public function addStableLink() {
		global $wgRequest, $wgOut, $wgLang;
		$this->load();
		if ( !$this->article->isReviewable() || !$wgRequest->getVal( 'oldid' ) ) {
			return true;
		}
		# We may have nav links like "direction=prev&oldid=x"
		$revID = $this->article->getOldIDFromRequest();
		$frev = FlaggedRevision::newFromTitle( $this->article->getTitle(), $revID );
		# Give a notice if this rev ID corresponds to a reviewed version...
		if ( $frev ) {
			$time = $wgLang->date( $frev->getTimestamp(), true );
			$flags = $frev->getTags();
			$quality = FlaggedRevs::isQuality( $flags );
			$msg = $quality ? 'revreview-quality-source' : 'revreview-basic-source';
			$tag = wfMsgExt( $msg, array( 'parseinline' ), $frev->getRevId(), $time );
			# Hide clutter
			if ( !FlaggedRevs::useSimpleUI() && !empty( $flags ) ) {
				$tag .= FlaggedRevsXML::ratingToggle() .
					"<div id='mw-fr-revisiondetails' style='display:block;'>" .
					wfMsgHtml( 'revreview-oldrating' ) .
					FlaggedRevsXML::addTagRatings( $flags ) . '</div>';
			}
			$css = 'flaggedrevs_notice plainlinks noprint';
			$tag = "<div id='mw-fr-revisiontag-old' class='$css'>$tag</div>";
			$wgOut->addHTML( $tag );
		}
		return true;
	}
	
	/**
	* @returns mixed int/false/null
	*/
	protected function getRequestedStableId() {
		global $wgRequest;
		$reqId = $wgRequest->getVal( 'stableid' );
		if ( $reqId === "best" ) {
			$reqId = FlaggedRevs::getPrimeFlaggedRevId( $this->article );
		}
		return $reqId;
	}

	 /**
	 * Replaces a page with the last stable version if possible
	 * Adds stable version status/info tags and notes
	 * Adds a quick review form on the bottom if needed
	 */
	public function setPageContent( &$outputDone, &$pcache ) {
		global $wgRequest, $wgOut, $wgLang, $wgContLang;
		$this->load();
		# Only trigger on article view for content pages, not for protect/delete/hist...
		$action = $wgRequest->getVal( 'action', 'view' );
		if ( !self::isViewAction( $action ) || !$this->article->exists() )
			return true;
		# Do not clutter up diffs any further and leave archived versions alone...
		if ( $wgRequest->getVal( 'diff' ) || $wgRequest->getVal( 'oldid' ) ) {
			return true;
		}
		# Only trigger for reviewable pages
		if ( !$this->article->isReviewable() ) {
			return true;
		}
		$simpleTag = $old = $stable = false;
		$tag = $prot = '';
		# Check the newest stable version.
		$srev = $this->article->getStableRev();
		$stableId = $srev ? $srev->getRevId() : 0;
		$frev = $srev; // $frev is the revision we are looking at
		# Check for any explicitly requested old stable version...
		$reqId = $this->getRequestedStableId();
		if ( $reqId ) {
			if ( !$stableId ) {
				$reqId = false; // must be invalid
			# Treat requesting the stable version by ID as &stable=1
			} else if ( $reqId != $stableId ) {
				$old = true; // old reviewed version requested by ID
				$frev = FlaggedRevision::newFromTitle( $this->article->getTitle(),
					$reqId, FR_TEXT );
				if ( !$frev ) {
					$reqId = false; // invalid ID given
				}
			} else {
				$stable = true; // stable version requested by ID
			}
		}
		// $reqId is null if nothing requested, false if invalid
		if ( $reqId === false ) {
			$wgOut->addWikiText( wfMsg( 'revreview-invalid' ) );
			$wgOut->returnToMain( false, $this->article->getTitle() );
			# Tell MW that parser output is done
			$outputDone = true;
			$pcache = false;
			return true;
		}
		// Is the page config altered?
		$prot = FlaggedRevsXML::lockStatusIcon( $this->article );
		// Is there no stable version?
		if ( !$frev ) {
			# Add "no reviewed version" tag, but not for printable output
			$this->showUnreviewedPage( $tag, $prot );
			return true;
		}
		# Get flags and date
		$time = $wgLang->date( $frev->getTimestamp(), true );
		$flags = $frev->getTags();
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		// Looking at some specific old stable revision ("&stableid=x")
		// set to override given the relevant conditions. If the user is
		// requesting the stable revision ("&stableid=x"), defer to override
		// behavior below, since it is the same as ("&stable=1").
		if ( $old ) {
			$this->showOldReviewedVersion( $srev, $frev, $tag, $prot );
			$outputDone = true; # Tell MW that parser output is done
			$pcache = false;
		// Stable version requested by ID or relevant conditions met to
		// to override page view.
		} else if ( $stable || $this->pageOverride() ) {
	   		$this->showStableVersion( $srev, $tag, $prot );
			$outputDone = true; # Tell MW that parser output is done
			$pcache = false;
		// Looking at some specific old revision (&oldid=x) or if FlaggedRevs is not
		// set to override given the relevant conditions (like &stable=0) or there
		// is no stable version.
		} else {
	   		$this->showDraftVersion( $srev, $tag, $prot );
		}
		$encJS = ''; // JS events to use
		# Some checks for which tag CSS to use
		if ( FlaggedRevs::useSimpleUI() ) {
			$tagClass = 'flaggedrevs_short';
			# Collapse the box details on mouseOut
			$encJS .= ' onMouseOut="FlaggedRevs.onBoxMouseOut(event)"';
		} elseif ( $simpleTag ) {
			$tagClass = 'flaggedrevs_notice';
		} elseif ( $pristine ) {
			$tagClass = 'flaggedrevs_pristine';
		} elseif ( $quality ) {
			$tagClass = 'flaggedrevs_quality';
		} else {
			$tagClass = 'flaggedrevs_basic';
		}
		# Wrap tag contents in a div
		if ( $tag != '' ) {
			$rtl = $wgContLang->isRTL() ? " rtl" : ""; // RTL langauges
			$css = "{$tagClass}{$rtl} plainlinks noprint";
			$notice = "<div id=\"mw-fr-revisiontag\" class=\"{$css}\"{$encJS}>{$tag}</div>\n";
			$this->reviewNotice .= $notice;
		}
		return true;
	}

	// For pages that have a stable version, index only that version
	public function setRobotPolicy() {
		global $wgOut;
		if ( !$this->article->isReviewable() || !$this->article->getStableRev() ) {
			return true; // page has no stable version
		}
		if ( !$this->pageOverride() && $this->article->isStableShownByDefault() ) {
			# Index the stable version only if it is the default
			$wgOut->setRobotPolicy( 'noindex,nofollow' );
		}
		return true;
	}

	/**
	* @param $tag review box/bar info
	* @param $prot protection notice
	* Tag output function must be called by caller
	*/
	protected function showUnreviewedPage( $tag, $prot ) {
		global $wgOut, $wgContLang;
		if ( $wgOut->isPrintable() ) {
			return;
		}
		$icon = FlaggedRevsXML::draftStatusIcon();
		// Simple icon-based UI
		if ( FlaggedRevs::useSimpleUI() ) {
			// RTL langauges
			$rtl = $wgContLang->isRTL() ? " rtl" : "";
			$tag .= $prot . $icon . wfMsgExt( 'revreview-quick-none', array( 'parseinline' ) );
			$css = "flaggedrevs_short{$rtl} plainlinks noprint";
			$this->reviewNotice .= "<div id='mw-fr-revisiontag' class='$css'>$tag</div>";
		// Standard UI
		} else {
			$css = 'flaggedrevs_notice plainlinks noprint';
			$tag = "<div id='mw-fr-revisiontag' class='$css'>" .
				$prot . $icon . wfMsgExt( 'revreview-noflagged', array( 'parseinline' ) ) .
				"</div>";
			$this->reviewNotice .= $tag;
		}
	}
	
	/**
	* @param $srev stable version
	* @param $tag review box/bar info
	* @param $prot protection notice icon
	* Tag output function must be called by caller
	* Parser cache control deferred to caller
	*/
	protected function showDraftVersion( FlaggedRevision $srev, &$tag, $prot ) {
		global $wgUser, $wgOut, $wgLang, $wgRequest;
		$this->load();
		$flags = $srev->getTags();
		$time = $wgLang->date( $srev->getTimestamp(), true );
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		# Get stable version sync status
		$synced = $this->article->stableVersionIsSynced();
		if ( $synced ) {
			$this->setReviewNotes( $srev ); // Still the same
		} else {
			$this->maybeShowTopDiff( $srev, $quality ); // user may want diff (via prefs)
		}
		# If they are synced, do special styling
		$simpleTag = !$synced;
		# Give notice to newer users if an unreviewed edit was completed...
		if ( $wgRequest->getVal( 'shownotice' )
			&& $this->article->getUserText() == $wgUser->getName() // FIXME: rawUserText?
			&& $this->article->revsArePending()
			&& !$wgUser->isAllowed( 'review' ) )
		{
			$revsSince = $this->article->getPendingRevCount();
			$tooltip = wfMsgHtml( 'revreview-draft-title' );
			$pending = $prot;
			if ( self::showRatingIcon() ) {
				$pending .= FlaggedRevsXML::draftStatusIcon();
			}
			$pending .= wfMsgExt( 'revreview-edited',
				array( 'parseinline' ), $srev->getRevId(), $revsSince );
			$anchor = $wgRequest->getVal( 'fromsection' );
			if ( $anchor != null ) {
				$section = str_replace( '_', ' ', $anchor ); // prettify
				$pending .= wfMsgExt( 'revreview-edited-section', 'parse', $anchor, $section );
			}
			# Notice should always use subtitle
			$this->reviewNotice = "<div id='mw-fr-reviewnotice' " .
				"class='flaggedrevs_preview plainlinks'>$pending</div>";
		# Construct some tagging for non-printable outputs. Note that the pending
		# notice has all this info already, so don't do this if we added that already.
		# Also, if low profile UI is enabled and the page is synced, skip the tag.
		} else if ( !$wgOut->isPrintable() && !( $this->article->lowProfileUI() && $synced ) ) {
			$revsSince = $this->article->getPendingRevCount();
			// Simple icon-based UI
			if ( FlaggedRevs::useSimpleUI() ) {
				if ( !$wgUser->getId() ) {
					$msgHTML = ''; // Anons just see simple icons
				} else if ( $synced ) {
					$msg = $quality
						? 'revreview-quick-quality-same'
						: 'revreview-quick-basic-same';
					$msgHTML = wfMsgExt( $msg, array( 'parseinline' ),
						$srev->getRevId(), $revsSince );
				} else {
					$msg = $quality
						? 'revreview-quick-see-quality'
						: 'revreview-quick-see-basic';
					$msgHTML = wfMsgExt( $msg, array( 'parseinline' ),
						$srev->getRevId(), $revsSince );
				}
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$icon = $synced
						? FlaggedRevsXML::stableStatusIcon( $quality )
						: FlaggedRevsXML::draftStatusIcon();
				}
				$msgHTML = $prot . $icon . $msgHTML;
				$tag .= FlaggedRevsXML::prettyRatingBox( $srev, $msgHTML,
					$revsSince, 'draft', $synced, false );
			// Standard UI
			} else {
				if ( $synced ) {
					if ( $quality ) {
						$msg = 'revreview-quality-same';
					} else {
						$msg = 'revreview-basic-same';
					}
					$msgHTML = wfMsgExt( $msg, array( 'parseinline' ),
						$srev->getRevId(), $time, $revsSince );
				} else {
					$msg = $quality
						? 'revreview-newest-quality'
						: 'revreview-newest-basic';
					$msg .= ( $revsSince == 0 ) ? '-i' : '';
					$msgHTML = wfMsgExt( $msg, array( 'parseinline' ),
						$srev->getRevId(), $time, $revsSince );
				}
				$icon = $synced
					? FlaggedRevsXML::stableStatusIcon( $quality )
					: FlaggedRevsXML::draftStatusIcon();
				$tag .= $prot . $icon . $msgHTML;
			}
		}
	}
	
	/**
	* @param $srev stable version
	* @param $frev selected flagged revision
	* @param $tag review box/bar info
	* @param $prot protection notice icon
	* Tag output function must be called by caller
	* Parser cache control deferred to caller
	*/
	protected function showOldReviewedVersion(
		FlaggedRevision $srev, FlaggedRevision $frev, &$tag, $prot
	) {
		global $wgUser, $wgOut, $wgLang;
		$this->load();
		$flags = $frev->getTags();
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Set display revision ID
		$wgOut->setRevisionId( $frev->getRevId() );
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		$text = $frev->getRevText();
		# Check if this is a redirect...
		$redirHtml = $this->getRedirectHtml( $text );
		if ( $redirHtml == '' ) {
			$parserOut = FlaggedRevs::parseStableText( $this->article, $text, $frev->getRevId() );
		}
		# Construct some tagging for non-printable outputs. Note that the pending
		# notice has all this info already, so don't do this if we added that already.
		if ( !$wgOut->isPrintable() ) {
			// Simple icon-based UI
			if ( FlaggedRevs::useSimpleUI() ) {
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$icon = FlaggedRevsXML::stableStatusIcon( $quality );
				}
				$revsSince = $this->article->getPendingRevCount();
				if ( !$wgUser->getId() ) {
					$msgHTML = ''; // Anons just see simple icons
				} else {
					$msg = $quality
						? 'revreview-quick-quality-old'
						: 'revreview-quick-basic-old';
					$msgHTML = wfMsgExt( $msg, array( 'parseinline' ), $frev->getRevId(), $revsSince );
				}
				$msgHTML = $prot . $icon . $msgHTML;
				$tag = FlaggedRevsXML::prettyRatingBox( $frev, $msgHTML,
					$revsSince, 'oldstable', false /*synced*/ );
			// Standard UI
			} else {
				$icon = FlaggedRevsXML::stableStatusIcon( $quality );
				$msg = $quality
					? 'revreview-quality-old'
					: 'revreview-basic-old';
				$tag = $prot . $icon;
				$tag .= wfMsgExt( $msg, 'parseinline', $frev->getRevId(), $time );
				# Hide clutter
				if ( !empty( $flags ) ) {
					$tag .= FlaggedRevsXML::ratingToggle();
					$tag .= "<div id='mw-fr-revisiondetails' style='display:block;'>" .
						wfMsgHtml( 'revreview-oldrating' ) .
						FlaggedRevsXML::addTagRatings( $flags ) . '</div>';
				}
			}
		}
		# Output HTML
		$this->setReviewNotes( $frev );
	   	if ( $redirHtml != '' ) {
			$wgOut->addHtml( $redirHtml );
		} else {
			$this->addParserOutput( $parserOut );
		}
	}

	/**
	* @param $srev stable version
	* @param $tag review box/bar info
	* @param $prot protection notice
	* Tag output function must be called by caller
	* Parser cache control deferred to caller
	*/
	protected function showStableVersion( FlaggedRevision $srev, &$tag, $prot ) {
		global $wgOut, $wgLang, $wgUser;
		$this->load();
		$flags = $srev->getTags();
		$time = $wgLang->date( $srev->getTimestamp(), true );
		# Set display revision ID
		$wgOut->setRevisionId( $srev->getRevId() );
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		# Get parsed stable version
		$redirHtml = '';
		$parserOut = FlaggedRevs::getPageCache( $this->article, $wgUser );
		if ( $parserOut == false ) {
			$text = $srev->getRevText();
			# Check if this is a redirect...
			$redirHtml = $this->getRedirectHtml( $text );
			if ( $redirHtml == '' ) {
				$parserOut = FlaggedRevs::parseStableText(
					$this->article, $text, $srev->getRevId() );
				# Update the stable version cache
				FlaggedRevs::updatePageCache( $this->article, $wgUser, $parserOut );
			} else {
				$parserOut = null;
			}
	   	}
		$synced = $this->article->stableVersionIsSynced();
		# Construct some tagging
		if ( !$wgOut->isPrintable() && !( $this->article->lowProfileUI() && $synced ) ) {
			$revsSince = $this->article->getPendingRevCount();
			// Simple icon-based UI
			if ( FlaggedRevs::useSimpleUI() ) {
				$icon = '';
				# For protection based configs, show lock only if it's not redundant.
				if ( $this->showRatingIcon() ) {
					$icon = FlaggedRevsXML::stableStatusIcon( $quality );
				}
				if ( !$wgUser->getId() ) {
					$msgHTML = ''; // Anons just see simple icons
				} else {
					$msg = $quality
						? 'revreview-quick-quality'
						: 'revreview-quick-basic';
					# Uses messages 'revreview-quick-quality-same', 'revreview-quick-basic-same'
					$msg = $synced ? "{$msg}-same" : $msg;
					$msgHTML = wfMsgExt( $msg, array( 'parseinline' ),
						$srev->getRevId(), $revsSince );
				}
				$msgHTML = $prot . $icon . $msgHTML;
				$tag = FlaggedRevsXML::prettyRatingBox( $srev, $msgHTML,
					$revsSince, 'stable', $synced );
			// Standard UI
			} else {
				$icon = FlaggedRevsXML::stableStatusIcon( $quality );
				$msg = $quality ? 'revreview-quality' : 'revreview-basic';
				if ( $synced ) {
					# uses messages 'revreview-quality-same', 'revreview-basic-same'
					$msg .= '-same';
				} elseif ( $revsSince == 0 ) {
					# uses messages 'revreview-quality-i', 'revreview-basic-i'
					$msg .= '-i';
				}
				$tag = $prot . $icon;
				$tag .= wfMsgExt( $msg, 'parseinline', $srev->getRevId(), $time, $revsSince );
				if ( !empty( $flags ) ) {
					$tag .= FlaggedRevsXML::ratingToggle();
					$tag .= "<div id='mw-fr-revisiondetails' style='display:block;'>" .
						FlaggedRevsXML::addTagRatings( $flags ) . '</div>';
				}
			}
		}
		# Output HTML
		$this->setReviewNotes( $srev );
		if ( $redirHtml != '' ) {
			$wgOut->addHtml( $redirHtml );
		} else {
			// $parserOut will not be null here
			$this->addParserOutput( $parserOut );
		}
	}

	// Add parser output and update title
	// @TODO: refactor MW core to move this back
	protected function addParserOutput( ParserOutput $parserOut ) {
		global $wgOut;
		$wgOut->addParserOutput( $parserOut );
		# Adjust the title if it was set by displaytitle, -{T|}- or language conversion
		$titleText = $parserOut->getTitleText();
		if ( strval( $titleText ) !== '' ) {
			$wgOut->setPageTitle( $titleText );
		}
	}

	// Get fancy redirect arrow and link HTML
	protected function getRedirectHtml( $text ) {
		$rTarget = $this->article->followRedirectText( $text );
		if ( $rTarget ) {
			return $this->article->viewRedirect( $rTarget );
		}
		return '';
	}
	
	// Show icons for draft/stable/old reviewed versions
	protected function showRatingIcon() {
		if ( FlaggedRevs::forDefaultVersionOnly() ) {
			// If there is only on quality level and we have tabs to know
			// which version we are looking at, then just use the lock icon...
			return FlaggedRevs::qualityVersions();
		}
		return true;
	}

	/**
	* Add diff-to-stable to top of page views as needed
	* @param FlaggedRevision $srev, stable version
	* @param bool $quality, revision is quality
	* @returns bool, diff added to output
	*/
	protected function maybeShowTopDiff( FlaggedRevision $srev, $quality ) {
		global $wgUser, $wgOut;
		$this->load();
		if ( !$wgUser->getBoolOption( 'flaggedrevsviewdiffs' ) ) {
			return false; // nothing to do here
		} elseif ( !$wgUser->isAllowed( 'review' ) ) {
			return false; // does not apply to this user
		}
		# Diff should only show for the draft
		$oldid = $this->article->getOldIDFromRequest();
		$latest = $this->article->getLatest();
		if ( $oldid && $oldid != $latest ) {
			return false; // not viewing the draft
		}
		# Conditions are met to show diff...
		$leftNote = $quality
			? 'revreview-hist-quality'
			: 'revreview-hist-basic';
		$rClass = FlaggedRevsXML::getQualityColor( false );
		$lClass = FlaggedRevsXML::getQualityColor( (int)$quality );
		$rightNote = "<span id='mw-fr-diff-rtier' class='$rClass'>[" .
			wfMsgHtml( 'revreview-hist-draft' ) . "]</span>";
		$leftNote = "<span id='mw-fr-diff-ltier' class='$lClass'>[" .
			wfMsgHtml( $leftNote ) . "]</span>";
		# Fetch the stable and draft revision text
		$oText = $srev->getRevText();
		if ( $oText === false ) {
			return false; // deleted revision or something?
		}
		$nText = $this->article->getContent();
		if ( $nText === false ) {
			return false; // deleted revision or something?
		}
		# Build diff at the top of the page
		if ( strcmp( $oText, $nText ) !== 0 ) {
			$diffEngine = new DifferenceEngine( $this->article->getTitle() );
			$diffEngine->showDiffStyle();
			$n = $this->article->getTitle()->countRevisionsBetween( $srev->getRevId(), $latest );
			if ( $n ) {
				$multiNotice = "<tr><td colspan='4' align='center' class='diff-multi'>" .
					wfMsgExt( 'diff-multi', array( 'parse' ), $n ) . "</td></tr>";
			} else {
				$multiNotice = '';
			}
			$wgOut->addHTML(
				"<div>" .
				"<table border='0' width='98%' cellpadding='0' cellspacing='4' class='diff'>" .
				"<col class='diff-marker' />" .
				"<col class='diff-content' />" .
				"<col class='diff-marker' />" .
				"<col class='diff-content' />" .
				"<tr>" .
					"<td colspan='2' width='50%' align='center' class='diff-otitle'><b>" .
						$leftNote . "</b></td>" .
					"<td colspan='2' width='50%' align='center' class='diff-ntitle'><b>" .
						$rightNote . "</b></td>" .
				"</tr>" .
				$multiNotice .
				$diffEngine->generateDiffBody( $oText, $nText ) .
				"</table>" .
				"</div>\n"
			);
			$this->isDiffFromStable = true;
			return true;
		}
		return false;
	}

	/**
	 * Get the normal and display files for the underlying ImagePage.
	 * If the a stable version needs to be displayed, this will set $normalFile
	 * to the current version, and $displayFile to the desired version.
	 *
	 * If no stable version is required, the reference parameters will not be set
	 *
	 * Depends on $wgRequest
	 */
	public function imagePageFindFile( &$normalFile, &$displayFile ) {
		global $wgRequest, $wgArticle;
		$this->load();
		# Determine timestamp. A reviewed version may have explicitly been requested...
		$frev = null;
		$time = false;
		if ( $reqId = $wgRequest->getVal( 'stableid' ) ) {
			$frev = FlaggedRevision::newFromTitle( $this->article->getTitle(), $reqId );
		} elseif ( $this->pageOverride() ) {
			$frev = $this->article->getStableRev();
		}
		if ( $frev ) {
			$time = $frev->getFileTimestamp();
			// B/C, may be stored in associated image version metadata table
			if ( !$time ) {
				$dbr = wfGetDB( DB_SLAVE );
				$time = $dbr->selectField( 'flaggedimages',
					'fi_img_timestamp',
					array( 'fi_rev_id' => $frev->getRevId(),
						'fi_name' => $this->article->getTitle()->getDBkey() ),
					__METHOD__
				);
			}
			# NOTE: if not found, this will use the current
			$wgArticle = new ImagePage( $this->article->getTitle(), $time );
		}
		if ( !$time ) {
			# Try request parameter
			$time = $wgRequest->getVal( 'filetimestamp', false );
		}

		if ( !$time ) {
			return; // Use the default behaviour
		}

		$title = $this->article->getTitle();
		$displayFile = wfFindFile( $title, array( 'time' => $time ) );
		# If none found, try current
		if ( !$displayFile ) {
			wfDebug( __METHOD__ . ": {$title->getPrefixedDBkey()}: $time not found, using current\n" );
			$displayFile = wfFindFile( $title );
			# If none found, use a valid local placeholder
			if ( !$displayFile ) {
				$displayFile = wfLocalFile( $title ); // fallback to current
			}
			$normalFile = $displayFile;
		# If found, set $normalFile
		} else {
			wfDebug( __METHOD__ . ": {$title->getPrefixedDBkey()}: using timestamp $time\n" );
			$normalFile = wfFindFile( $title );
		}
	}

	/**
	 * Adds stable version tags to page when viewing history
	 */
	public function addToHistView() {
		global $wgOut;
		$this->load();
		# Must be reviewable. UI may be limited to unobtrusive patrolling system.
		if ( !$this->article->isReviewable() ) {
			return true;
		}
		# Add a notice if there are pending edits...
		$srev = $this->article->getStableRev();
		if ( $srev && $this->article->revsArePending() ) {
			$revsSince = $this->article->getPendingRevCount();
			$tag = "<div id='mw-fr-revisiontag-edit' class='flaggedrevs_notice plainlinks'>" .
				FlaggedRevsXML::lockStatusIcon( $this->article ) . # flag protection icon as needed
				FlaggedRevsXML::pendingEditNotice( $this->article, $srev, $revsSince ) . "</div>";
			$wgOut->addHTML( $tag );
		}
		return true;
	}

	/**
	 * Adds stable version tags to page when editing
	 */
	public function addToEditView( EditPage $editPage ) {
		global $wgRequest, $wgOut, $wgLang, $wgUser;
		$this->load();
		# Must be reviewable. UI may be limited to unobtrusive patrolling system.
		if ( !$this->article->isReviewable() ) {
			return true;
		}
		$items = array();
		$tag = $warning = $prot = '';
		# Show stabilization log
		$log = $this->stabilityLogNotice();
		if ( $log ) $items[] = $log;
		# Check the newest stable version
		$quality = 0;
		$frev = $this->article->getStableRev();
		if ( $frev ) {
			$quality = $frev->getQuality();
			# Find out revision id of base version
			$latestId = $this->article->getLatest();
			$revId = $editPage->oldid ? $editPage->oldid : $latestId;
			# Let new users know about review procedure a tag.
			# If the log excerpt was shown this is redundant.
			if ( !$log && !$wgUser->getId() && $this->article->isStableShownByDefault() ) {
				$items[] = wfMsgExt( 'revreview-editnotice', array( 'parseinline' ) );
			}
			# Add a notice if there are pending edits...
			if ( $this->article->revsArePending() ) {
				$revsSince = $this->article->getPendingRevCount();
				$items[] = FlaggedRevsXML::pendingEditNotice( $this->article, $frev, $revsSince );
			}
			# Show diff to stable, to make things less confusing...
			# This can be disabled via user preferences
			if ( $frev->getRevId() < $latestId // changes were made
				&& $this->isDiffShownOnEdit() // stable default and user cannot review
				&& $wgUser->getBoolOption( 'flaggedrevseditdiffs' ) // not disable via prefs
				&& $revId == $latestId // only for current rev
				&& $editPage->section != "new" // not for new sections
				&& !in_array( $editPage->formtype, array( 'diff', 'preview' ) ) // not preview/"show changes"
			) {
				# Conditions are met to show diff...
				$leftNote = $quality
					? 'revreview-hist-quality'
					: 'revreview-hist-basic';
				$rClass = FlaggedRevsXML::getQualityColor( false );
				$lClass = FlaggedRevsXML::getQualityColor( (int)$quality );
				$rightNote = "<span id='mw-fr-diff-rtier' class='$rClass'>[" .
					wfMsgHtml( 'revreview-hist-draft' ) . "]</span>";
				$leftNote = "<span id='mw-fr-diff-ltier' class='$lClass'>[" .
					wfMsgHtml( $leftNote ) . "]</span>";
				$text = $frev->getRevText();
				# Are we editing a section?
				$section = ( $editPage->section == "" ) ?
					false : intval( $editPage->section );
				if ( $section !== false ) {
					$text = $this->article->getSection( $text, $section );
				}
				if ( $text !== false && strcmp( $text, $editPage->textbox1 ) !== 0 ) {
					$diffEngine = new DifferenceEngine( $this->article->getTitle() );
					$diffEngine->showDiffStyle();
					$diffHtml =
						wfMsgExt( 'review-edit-diff', 'parseinline' ) . ' ' .
						FlaggedRevsXML::diffToggle() .
						"<div id='mw-fr-stablediff'>" .
						"<table border='0' width='98%' cellpadding='0' cellspacing='4' class='diff'>" .
						"<col class='diff-marker' />" .
						"<col class='diff-content' />" .
						"<col class='diff-marker' />" .
						"<col class='diff-content' />" .
						"<tr>" .
							"<td colspan='2' width='50%' align='center' class='diff-otitle'><b>" .
								$leftNote . "</b></td>" .
							"<td colspan='2' width='50%' align='center' class='diff-ntitle'><b>" .
								$rightNote . "</b></td>" .
						"</tr>" .
						$diffEngine->generateDiffBody( $text, $editPage->textbox1 ) .
						"</table>" .
						"</div>\n";
					$items[] = $diffHtml;
				}
			}
			# Output items
			if ( count( $items ) ) {
				$html = "<table class='flaggedrevs_editnotice plainlinks'>";
				foreach ( $items as $item ) {
					$html .= '<tr><td>' . $item . '</td></tr>';
				}
				$html .= '</table>';
				$wgOut->addHTML( $html );
			}
		}
		return true;
	}
	
	protected function stabilityLogNotice() {
		$this->load();
		$s = '';
		# Only for pages manually made to be stable...
		if ( $this->article->isPageLocked() ) {
			$s = wfMsgExt( 'revreview-locked', 'parseinline' );
			$s .= ' ' . FlaggedRevsXML::logDetailsToggle();
			$s .= FlaggedRevsXML::stabilityLogExcerpt( $this->article );
		# ...or unstable
		} elseif ( $this->article->isPageUnlocked() ) {
			$s = wfMsgExt( 'revreview-unlocked', 'parseinline' );
			$s .= ' ' . FlaggedRevsXML::logDetailsToggle();
			$s .= FlaggedRevsXML::stabilityLogExcerpt( $this->article );
		}
		return $s;
	}
	
	public function addToNoSuchSection( EditPage $editPage, &$s ) {
		$this->load();
		if ( !$this->article->isReviewable() ) {
			return true; // nothing to do
		}
		$srev = $this->article->getStableRev();
		if ( $srev && $this->article->revsArePending() ) {
			$revsSince = $this->article->getPendingRevCount();
			if ( $revsSince ) {
				$s .= "<div class='flaggedrevs_editnotice plainlinks'>" .
					wfMsgExt( 'revreview-pending-nosection', array( 'parseinline' ),
						$srev->getRevId(), $revsSince ) . "</div>";
			}
		}
		return true;
	}

	/**
	 * Add unreviewed pages links
	 */
	public function addToCategoryView() {
		global $wgOut, $wgUser;
		$this->load();
		if ( !$wgUser->isAllowed( 'review' ) ) {
			return true;
		}
		if ( !FlaggedRevs::stableOnlyIfConfigured() ) {
			$links = array();
			$category = $this->article->getTitle()->getText();
			# Add link to list of unreviewed pages in this category
			$links[] = $wgUser->getSkin()->makeKnownLinkObj(
				SpecialPage::getTitleFor( 'UnreviewedPages' ),
				wfMsgHtml( 'unreviewedpages' ),
				'category=' . urlencode( $category )
			);
			# Add link to list of pages in this category with pending edits
			$links[] = $wgUser->getSkin()->makeKnownLinkObj(
				SpecialPage::getTitleFor( 'OldReviewedPages' ),
				wfMsgHtml( 'oldreviewedpages' ),
				'category=' . urlencode( $category )
			);
			$quickLinks = implode( ' / ', $links );
			$wgOut->appendSubtitle(
				"<span id='mw-fr-category-oldreviewed'>$quickLinks</span>"
			);
		}
		return true;
	}

	 /**
	 * Add review form to pages when necessary
	 * on a regular page view (action=view)
	 */
	public function addReviewForm( &$data ) {
		global $wgRequest, $wgUser, $wgOut;
		$this->load();
		if ( $wgOut->isPrintable() ) {
			return false; // Must be on non-printable output 
		}
		# User must have review rights
		if ( !$wgUser->isAllowed( 'review' ) ) {
			return true;
		}
		# Page must exist and be reviewable
		if ( !$this->article->exists() || !$this->article->isReviewable() ) {
			return true;
		}
		# Check action and if page is protected
		$action = $wgRequest->getVal( 'action', 'view' );
		# Must be view action...diffs handled elsewhere
		if ( !self::isViewAction( $action ) ) {
			return true;
		}
		# Get the revision being displayed
		$rev = false;
		if ( $this->reviewFormRev ) {
			$rev = $this->reviewFormRev; // $newRev for diffs stored here
		} elseif ( $wgOut->getRevisionId() ) {
			$rev = Revision::newFromId( $wgOut->getRevisionId() );
		}
		# Build the review form as needed
		if ( $rev ) {
			$templateIDs = $fileSHA1Keys = null;
			# $wgOut may not already have the inclusion IDs, such as for diffonly=1.
			if ( $wgOut->getRevisionId() == $rev->getId()
				&& isset( $wgOut->mTemplateIds )
				&& isset( $wgOut->fr_ImageSHA1Keys ) )
			{
				$templateIDs = $wgOut->mTemplateIds;
				$fileSHA1Keys = $wgOut->fr_ImageSHA1Keys;
			}
			$form = RevisionReviewForm::buildQuickReview( $this->article,
				$rev, $templateIDs, $fileSHA1Keys, $this->isDiffFromStable );
			# Diff action: place the form at the top of the page
			if ( $wgRequest->getVal( 'diff' ) ) {
				# Review notice box goes above form
				$wgOut->prependHTML( $this->diffNoticeBox . $form );
			# View action: place the form at the bottom of the page
			} else {
				$data .= $form;
			}
		}
		return true;
	}

	 /**
	 * Add link to stable version setting to protection form
	 */
	public function addVisibilityLink( &$data ) {
		global $wgUser, $wgRequest, $wgOut;
		$this->load();
		if ( FlaggedRevs::useProtectionLevels() ) {
			return true; // simple custom levels set for action=protect
		}
		# Check only if the title is reviewable
		if ( !FlaggedRevs::inReviewNamespace( $this->article->getTitle() ) ) {
			return true;
		}
		$action = $wgRequest->getVal( 'action', 'view' );
		if ( $action == 'protect' || $action == 'unprotect' ) {
			$title = SpecialPage::getTitleFor( 'Stabilization' );
			# Give a link to the page to configure the stable version
			$frev = $this->article->getStableRev();
			if ( $frev && $frev->getRevId() == $this->article->getLatest() ) {
				$wgOut->prependHTML( "<span class='plainlinks'>" .
					wfMsgExt( 'revreview-visibility', array( 'parseinline' ),
						$title->getPrefixedText() ) . "</span>" );
			} elseif ( $frev ) {
				$wgOut->prependHTML( "<span class='plainlinks'>" .
					wfMsgExt( 'revreview-visibility2', array( 'parseinline' ),
						$title->getPrefixedText() ) . "</span>" );
			} else {
				$wgOut->prependHTML( "<span class='plainlinks'>" .
					wfMsgExt( 'revreview-visibility3', array( 'parseinline' ),
						$title->getPrefixedText() ) . "</span>" );
			}
		}
		return true;
	}

	/**
	 * Modify an array of action links, as used by SkinTemplateNavigation and
	 * SkinTemplateTabs, to inlude flagged revs UI elements
	 */
	public function setActionTabs( $skin, array &$actions ) {
		global $wgUser;
		$this->load();
		if ( FlaggedRevs::useProtectionLevels() ) {
			return true; // simple custom levels set for action=protect
		}
		$title = $this->article->getTitle()->getSubjectPage();
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
			return true; // Only reviewable pages need these tabs
		}
		// Check if we should show a stabilization tab
		if (
			!$skin->mTitle->isTalkPage() &&
			is_array( $actions ) &&
			!isset( $actions['protect'] ) &&
			!isset( $actions['unprotect'] ) &&
			$wgUser->isAllowed( 'stablesettings' ) &&
			$title->exists() )
		{
			$stableTitle = SpecialPage::getTitleFor( 'Stabilization' );
			// Add the tab
			$actions['default'] = array(
				'class' => false,
				'text' => wfMsg( 'stabilization-tab' ),
				'href' => $stableTitle->getLocalUrl(
					'page=' . $title->getPrefixedUrl()
				)
			);
		}
		return true;
	}
	
	/**
	 * Modify an array of view links, as used by SkinTemplateNavigation and
	 * SkinTemplateTabs, to inlude flagged revs UI elements
	 */
	public function setViewTabs( $skin, array &$views ) {
		global $wgRequest;
		$this->load();
		if ( $skin->mTitle->isTalkPage() ) {
			return true; // leave talk pages alone
		}
		$fa = FlaggedArticle::getTitleInstance( $skin->mTitle );
		// Get the type of action requested
		$action = $wgRequest->getVal( 'action', 'view' );
		if ( !$fa->isReviewable() ) {
			return true; // Not a reviewable page or the UI is hidden
		}
		// XXX: shouldn't the session slave position check handle this?
		$flags = ( $action == 'rollback' ) ? FR_MASTER : 0;
		$srev = $fa->getStableRev( $flags );
	   	if ( !$srev ) {
			return true; // No stable revision exists
		}
		$synced = $this->article->stableVersionIsSynced();
		$pendingEdits = !$synced && $fa->isStableShownByDefault();
		// Set the edit tab names as needed...
	   	if ( $pendingEdits ) {
	   		if ( isset( $views['edit'] ) ) {
				$views['edit']['text'] = wfMsg( 'revreview-edit' );
	   		}
	   		if ( isset( $views['viewsource'] ) ) {
				$views['viewsource']['text'] = wfMsg( 'revreview-source' );
			}
	   	}
		# Add "pending changes" tab if the page is not synced
		if ( !$synced ) {
			$this->addDraftTab( $fa, $views, $srev, $action );
		}
		return true;
	}

	// Add "pending changes" tab and set tab selection CSS
	protected function addDraftTab(
		FlaggedArticle $fa, array &$views, FlaggedRevision $srev, $action
	) {
		global $wgRequest, $wgOut;
	 	$tabs = array(
	 		'read' => array( // view stable
				'text'  => '', // unused
				'href'  => $fa->getTitle()->getLocalUrl( 'stable=1' ),
	 			'class' => ''
	 		),
	 		'draft' => array( // view draft
				'text'  => wfMsg( 'revreview-current' ),
				'href'  => $fa->getTitle()->getLocalUrl( 'stable=0&redirect=no' ),
	 			'class' => ''
	 		),
	 	);
		// Set tab selection CSS
		if ( $this->pageOverride() || $wgRequest->getVal( 'stableid' ) ) {
			// We are looking a the stable version or an old reviewed one
			$tabs['read']['class'] = 'selected';
		} elseif ( self::isViewAction( $action ) ) {
			// Are we looking at a draft/current revision?
			// Note: there may *just* be template/file changes.
			if ( $wgOut->getRevisionId() >= $srev->getRevId() ) {
				$tabs['draft']['class'] = 'selected';
			// Otherwise, fallback to regular tab behavior
			} else {
				$tabs['read']['class'] = 'selected';
			}
		}
		$first = true;
		$newViews = array();
		// Rebuild tabs array. Deals with Monobook vs Vector differences.
		foreach ( $views as $tabAction => $data ) {
			// The first tab ('page' or 'view')...
			if ( $first ) {
				$first = false;
				// 'view' tab? In this case, the "page"/"discussion" tabs are not
				// part of $views. Also, both the page/talk page have a 'view' tab.
				if ( $tabAction == 'view' ) {
					// 'view' for content page; make it go to the stable version
					$newViews[$tabAction]['text'] = $data['text']; // keep tab name
					$newViews[$tabAction]['href'] = $tabs['read']['href'];
					$newViews[$tabAction]['class'] = $tabs['read']['class'];
				// 'page' tab? Make it go to the stable version...
				} else {
					$newViews[$tabAction]['text'] = $data['text']; // keep tab name
					$newViews[$tabAction]['href'] = $tabs['read']['href'];
					$newViews[$tabAction]['class'] = $data['class']; // keep tab class
				}
			// All other tabs...
			} else {
				// Add 'draft' tab to content page to the left of 'edit'...
				if ( $tabAction == 'edit' || $tabAction == 'viewsource' ) {
					$newViews['current'] = $tabs['draft'];
				}
				$newViews[$tabAction] = $data;
			}
	   	}
	   	// Replaces old tabs with new tabs
	   	$views = $newViews;
	}
	
	/**
	 * @param FlaggedRevision $frev
	 * @return string, revision review notes
	 */
	public function setReviewNotes( $frev ) {
		global $wgUser;
		$this->load();
		if ( $frev && FlaggedRevs::allowComments() && $frev->getComment() != '' ) {
			$this->reviewNotes = "<br /><div class='flaggedrevs_notes plainlinks'>";
			$this->reviewNotes .= wfMsgExt( 'revreview-note', array( 'parseinline' ),
				User::whoIs( $frev->getUser() ) );
			$this->reviewNotes .= '<br /><i>' .
				$wgUser->getSkin()->formatComment( $frev->getComment() ) . '</i></div>';
		}
	}

	/**
	* When viewing a diff:
	* (a) Add the review form to the top of the page
	* (b) Mark off which versions are checked or not
	* (c) When comparing the stable revision to the current after editing a page:
	* 	(i)  Show a tag with some explanation for the diff
	*	(ii) List any template/file changes pending review
	*/
	public function addToDiffView( $diff, $oldRev, $newRev ) {
		global $wgRequest, $wgUser, $wgOut, $wgMemc;
		$this->load();
		# Exempt printer-friendly output
		if ( $wgOut->isPrintable() ) {
			return true;
		}
		# Avoid multi-page diffs that are useless and misbehave (bug 19327).
		# Also sanity check $newRev just in case.
		if ( $this->isMultiPageDiff || !$newRev ) {
			return true;
		}
		# Page must be reviewable. Sanity check $oldRev.
		if ( !$this->article->isReviewable() ) {
			return true;
		}
		$form = '';
		$frev = $this->article->getStableRev();
		# Check if this might be a diff to stable (old rev is the stable rev).
		# For reviewers, add a notice and list inclusion changes for this case.
		if ( $this->isDiffFromStable && $wgUser->isAllowed( 'review' ) ) {
			$this->reviewFormRev = $newRev;
			# Check the page sync value cache...
			$key = wfMemcKey( 'flaggedrevs', 'includesSynced', $this->article->getId() );
			$value = FlaggedRevs::getMemcValue( $wgMemc->get( $key ), $this->article );

			$changeList = array();
			# Trigger queries if sync cache value is not 'true'
			if ( $value !== "true" ) {
				# Add a list of links to each changed template...
				$changeList = array_merge( $changeList, $this->fetchTemplateChanges( $frev ) );
				# Add a list of links to each changed file...
				$changeList = array_merge( $changeList, $this->fetchFileChanges( $frev ) );
			}

			# Some important information about include version selection...
			$notice = '';
			if ( count( $changeList ) ) {
				$notice = wfMsgExt( 'revreview-update-use', 'parse' );
			} elseif ( $value === "false" ) {
				global $wgParserCacheExpireTime;
				# Correct bad cache which said they were not synced
				$data = FlaggedRevs::makeMemcObj( "true" );
				$wgMemc->set( $key, $data, $wgParserCacheExpireTime );
			}
			# If there are pending revs or templates/files changes,
			# notify the user and prompt them to review them...
			if ( $this->article->revsArePending() || count( $changeList ) ) {
				// Reviewer just edited...
				if ( $wgRequest->getInt( 'shownotice' )
					&& $newRev->isCurrent()
					&& $newRev->getRawUserText() == $wgUser->getName() )
				{
					$title = $this->article->getTitle(); // convenience
					// @TODO: make diff class cache this
					$n = $title->countRevisionsBetween( $oldRev->getId(), $newRev->getId() );
					if ( $n ) {
						$msg = 'revreview-update-edited-prev'; // previous pending edits
					} else {
						$msg = 'revreview-update-edited'; // just couldn't autoreview
					}
				// All other cases...
				} else {
					$msg = 'revreview-update'; // generic "Please review" notice...
				}
				$changeDiv = wfMsgExt( $msg, 'parse' );
				if ( count( $changeList ) ) {
					# Add include change list...
					$changeDiv .= '<p>' .
						wfMsgExt( 'revreview-update-includes', 'parseinline' ) .
						'&#160;' . implode( ', ', $changeList ) . '</p>';
					# Add include usage notice...
					$changeDiv .= $notice;
				}
				$css = 'flaggedrevs_diffnotice plainlinks';
				$form .= "<div id='mw-fr-difftostable' class='$css'>$changeDiv</div>\n";
	
				# Set a key to note that someone is viewing this
				$this->markDiffUnderReview( $oldRev, $newRev );
			}
		}
		# Add a link to diff from stable to current as needed
		if ( $frev ) {
			$wgOut->addHTML( $this->diffToStableLink( $frev, $newRev ) );
		}
		# Show review status of the diff revision(s). Uses a <table>.
		$wgOut->addHTML( $this->diffReviewMarkers( $oldRev, $newRev ) );

		$this->diffNoticeBox = $form;
		return true;
	}

	/**
	* Add a link to diff-to-stable for reviewable pages
	*/
	protected function diffToStableLink( FlaggedRevision $frev, Revision $newRev ) {
		global $wgUser;
		$this->load();
		$review = '';
		# Make a link to the full diff-to-stable if:
		# (a) Actual revs are pending and (b) We are not viewing the stable diff
		if ( $this->article->revsArePending() &&
			!( $this->isDiffFromStable && $newRev->isCurrent() ) )
		{
			$review = $wgUser->getSkin()->makeKnownLinkObj(
				$this->article->getTitle(),
				wfMsgHtml( 'review-diff2stable' ),
				'oldid=' . $frev->getRevId() . '&diff=cur&diffonly=0'
			);
			$review = wfMsgHtml( 'parentheses', $review );
			$review = "<div class='fr-diff-to-stable' align='center'>$review</div>";
		}
		return $review;
	}

	/**
	* Add [checked version] and such to left and right side of diff
	*/
	protected function diffReviewMarkers( $oldRev, $newRev ) {
		$form = '';
		$oldRevQ = $newRevQ = false;
		if ( $oldRev ) {
			$oldRevQ = FlaggedRevs::getRevQuality( $oldRev->getPage(), $oldRev->getId() );
		}
		if ( $newRev ) {
			$newRevQ = FlaggedRevs::getRevQuality( $newRev->getPage(), $newRev->getId() );
		}
		# Diff between two revisions
		if ( $oldRev && $newRev ) {
			$form .= "<table class='fr-diff-ratings'><tr>";

			$class = FlaggedRevsXML::getQualityColor( $oldRevQ );
			if ( $oldRevQ !== false ) {
				$msg = $oldRevQ
					? 'revreview-hist-quality'
					: 'revreview-hist-basic';
			} else {
				$msg = 'revreview-hist-draft';
			}
			$form .= "<td width='50%' align='center'>";
			$form .= "<span id='mw-fr-diff-ltier' class='$class'>[" .
				wfMsgHtml( $msg ) . "]</span>";

			$class = FlaggedRevsXML::getQualityColor( $newRevQ );
			if ( $newRevQ !== false ) {
				$msg = $newRevQ
					? 'revreview-hist-quality'
					: 'revreview-hist-basic';
			} else {
				$msg = 'revreview-hist-draft';
			}
			$form .= "</td><td width='50%' align='center'>";
			$form .= "<span id='mw-fr-diff-rtier' class='$class'>[" .
				wfMsgHtml( $msg ) . "]</span>";

			$form .= '</td></tr></table>';
		# New page "diffs" - just one rev
		} elseif ( $newRev ) {
			if ( $newRevQ !== false ) {
				$msg = $newRevQ
					? 'revreview-hist-quality'
					: 'revreview-hist-basic';
			} else {
				$msg = 'revreview-hist-draft';
			}
			$class = FlaggedRevsXML::getQualityColor( $newRevQ );
			$form .=
				"<table class='fr-diff-ratings'>" .
				"<tr><td align='center'><span id='mw-fr-diff-rtier' class='$class'>" .
				'[' . wfMsgHtml( $msg ) . ']' .
				'</span></td></tr></table>';
		}
		return $form;
	}

	// Fetch template changes for a reviewed revision since review
	// @returns array
	protected function fetchTemplateChanges( FlaggedRevision $frev ) {
		global $wgUser;
		$skin = $wgUser->getSkin();
		$diffLinks = array();
		$changes = $frev->findPendingTemplateChanges();
		foreach ( $changes as $tuple ) {
			list( $title, $revIdStable ) = $tuple;
			$diffLinks[] = $skin->makeLinkObj( $title,
				$title->getPrefixedText(),
				'diff=cur&oldid=' . (int)$revIdStable );
		}
		return $diffLinks;
	}

	// Fetch file changes for a reviewed revision since review
	// @returns array
	protected function fetchFileChanges( FlaggedRevision $frev ) {
		global $wgUser;
		$skin = $wgUser->getSkin();
		$diffLinks = array();
		$changes = $frev->findPendingFileChanges();
		foreach ( $changes as $tuple ) {
			list( $title, $revIdStable ) = $tuple;
			// @TODO: change when MW has file diffs
			$diffLinks[] = $skin->makeLinkObj( $title, $title->getPrefixedText() );
		}
		return $diffLinks;
	}

	// Mark that someone is viewing a portion or all of the diff-to-stable
	protected function markDiffUnderReview( Revision $oldRev, Revision $newRev ) {
		global $wgMemc;
		$key = wfMemcKey( 'stableDiffs', 'underReview', $oldRev->getID(), $newRev->getID() );
		$wgMemc->set( $key, '1', 10 * 60 ); // 10 min
	}

	/**
	* Set $this->isDiffFromStable and $this->isMultiPageDiff fields
	* Note: $oldRev could be false
	*/
	public function setViewFlags( $diff, $oldRev, $newRev ) {
		$this->load();
		if ( $newRev && $oldRev ) {
			// Is this a diff between two pages?
			if ( $newRev->getPage() != $oldRev->getPage() ) {
				$this->isMultiPageDiff = true;
			// Is there a stable version?
			} elseif ( $this->article->isReviewable() ) {
				$srevId = $this->article->getStable();
				// Is this a diff of a draft rev against the stable rev?
				if ( $srevId
					&& $oldRev->getId() == $srevId
					&& $newRev->getTimestamp() >= $oldRev->getTimestamp() )
				{
					$this->isDiffFromStable = true;
				}
			}
		}
		return true;
	}

	/**
	* Redirect users out to review the changes to the stable version.
	* Only for people who can review and for pages that have a stable version.
	*/
	public function injectPostEditURLParams( &$sectionAnchor, &$extraQuery ) {
		global $wgUser;
		$this->load();
		# Don't show this for pages that are not reviewable
		if ( !$this->article->isReviewable() ) {
			return true;
		}
		# Get the stable version, from master
		$frev = $this->article->getStableRev( FR_MASTER );
		if ( !$frev ) {
			return true;
		}
		# Get latest revision Id (lag safe)
		$latest = $this->article->getTitle()->getLatestRevID( GAID_FOR_UPDATE );
		if ( $latest == $frev->getRevId() ) {
			return true; // only for pages with pending edits
		}
		// If the edit was not autoreviewed, and the user can actually make a
		// new stable version, then go to the diff...
		if ( $frev->userCanSetFlags() ) {
			$extraQuery .= $extraQuery ? '&' : '';
			// Override diffonly setting to make sure the content is shown
			$extraQuery .= 'oldid=' . $frev->getRevId() . '&diff=cur&diffonly=0&shownotice=1';
		// ...otherwise, go to the current revision after completing an edit.
		// This allows for users to immediately see their changes.
		} else {
			$extraQuery .= $extraQuery ? '&' : '';
			$extraQuery .= 'stable=0';
			// Show a notice at the top of the page for non-reviewers...
			if ( !$wgUser->isAllowed( 'review' ) && $this->article->isStableShownByDefault() ) {
				$extraQuery .= '&shownotice=1';
				if ( $sectionAnchor ) {
					// Pass a section parameter in the URL as needed to add a link to
					// the "your changes are pending" box on the top of the page...
					$section = str_replace(
						array( ':' , '.' ), array( '%3A', '%' ), // hack: reverse special encoding
						substr( $sectionAnchor, 1 ) // remove the '#'
					);
					$extraQuery .= '&fromsection=' . $section;
					$sectionAnchor = ''; // go to the top of the page to see notice
				}
			}
		}
		return true;
	}

	/**
	* If submitting the edit will leave it pending, then change the button text
	* Note: interacts with 'review pending changes' checkbox
	* @TODO: would be nice if hook passed in button attribs, not XML
	*/
	public function changeSaveButton( EditPage $editPage, array &$buttons ) {
		$title = $this->article->getTitle(); // convenience
		if ( !$this->article->editsRequireReview() ) {
			return true; // edit will go live immediatly
		} elseif ( $title->userCan( 'autoreview' ) ) {
			if ( FlaggedRevs::autoReviewNewPages() && !$this->article->exists() ) {
				return true; // edit will be autoreviewed anyway
			}
			$frev = FlaggedRevision::newFromTitle( $title, self::getBaseRevId( $editPage ) );
			if ( $frev ) {
				return true; // edit will be autoreviewed anyway
			}
		}
		if ( extension_loaded( 'domxml' ) ) {
			wfDebug( "Warning: you have the obsolete domxml extension for PHP. Please remove it!\n" );
			return true; # PECL extension conflicts with the core DOM extension (see bug 13770)
		} elseif ( isset( $buttons['save'] ) && extension_loaded( 'dom' ) ) {
			$dom = new DOMDocument();
			$result = $dom->loadXML( $buttons['save'] ); // load button XML from hook
			foreach ( $dom->getElementsByTagName( 'input' ) as $input ) { // one <input>
				$input->setAttribute( 'value', wfMsg( 'revreview-submitedit' ) );
				$input->setAttribute( 'title', // keep accesskey
					wfMsg( 'revreview-submitedit-title' ).' ['.wfMsg( 'accesskey-save' ).']' );
				# Change submit button text & title
				$buttons['save'] = $dom->saveXML( $dom->documentElement );
			}
		}
		return true;
	}

	/**
	* Add a "review pending changes" checkbox to the edit form if:
	* (a) there are currently any revisions pending (bug 16713)
	* (b) this is an unreviewed page (bug 23970)
	*/
	public function addReviewCheck( EditPage $editPage, array &$checkboxes, &$tabindex ) {
		global $wgUser, $wgRequest;
		if ( !$this->article->isReviewable()
			|| !$this->article->getTitle()->userCan( 'review' )
			|| ( $this->article->getStable() && !$this->article->revsArePending() )
		) {
			return true; // not needed
		}
		$oldid = $wgRequest->getInt( 'baseRevId', $this->article->getLatest() );
		if ( $oldid == $this->article->getLatest() ) {
			# For pages with either no stable version, or an outdated one, let
			# the user decide if he/she wants it reviewed on the spot. One might
			# do this if he/she just saw the diff-to-stable and *then* decided to edit.
			# Note: check not shown when editing old revisions, which is confusing.
			$checkbox = Xml::check(
				'wpReviewEdit',
				$wgRequest->getCheck( 'wpReviewEdit' ),
				array( 'tabindex' => ++$tabindex, 'id' => 'wpReviewEdit' )
			);
			$attribs = array( 'for' => 'wpReviewEdit' );
			// For pending changes...
			if ( $this->article->getStable() ) {
				$attribs['title'] = wfMsg( 'revreview-check-flag-p-title' );
				$labelMsg = wfMsgExt( 'revreview-check-flag-p', 'parseinline' );
			// For unreviewed pages...
			} else {
				$attribs['title'] = wfMsg( 'revreview-check-flag-u-title' );
				$labelMsg = wfMsgExt( 'revreview-check-flag-u', 'parseinline' );
			}
			$label = Xml::element( 'label', $attribs, $labelMsg );
			$checkboxes['reviewed'] = $checkbox . '&nbsp;' . $label;
		}
		return true;
	}
	
	/**
	* (a) Add a hidden field that has the rev ID the text is based off.
	* (b) If an edit was undone, add a hidden field that has the rev ID of that edit.
	* Needed for autoreview and user stats (for autopromote).
	* Note: baseRevId trusted for Reviewers - text checked for others.
	*/
	public function addRevisionIDField( EditPage $editPage, OutputPage $out ) {
		$this->load();
		$revId = self::getBaseRevId( $editPage );
		$out->addHTML( "\n" . Xml::hidden( 'baseRevId', $revId ) );
		$out->addHTML( "\n" . Xml::hidden( 'undidRev',
			empty( $editPage->undidRev ) ? 0 : $editPage->undidRev )
		);
		return true;
	}

	/**
	* Guess the rev ID the text of this form is based off
	* Note: baseRevId trusted for Reviewers - text checked for others.
	* @return int
	*/
	protected static function getBaseRevId( EditPage $editPage ) {
		global $wgRequest;
		if ( !isset( $editPage->fr_baseRevId ) ) {
			$article = $editPage->getArticle(); // convenience
			$latestId = $article->getLatest(); // current rev
			$undo = $wgRequest->getIntOrNull( 'undo' );
			# Undoing consecutive top edits...
			if ( $undo && $undo === $latestId ) {
				# Treat this like a revert to a base revision.
				# We are undoing all edits *after* some rev ID (undoafter).
				# If undoafter is not given, then it is the previous rev ID.
				$revId = $wgRequest->getInt( 'undoafter',
					$article->getTitle()->getPreviousRevisionID( $latestId, GAID_FOR_UPDATE ) );
			# Undoing other edits...
			} elseif ( $undo ) {
				$revId = $latestId; // current rev is the base rev
			# Other edits...
			} else {
				# If we are editing via oldid=X, then use that rev ID.
				# Otherwise, check if the client specified the ID (bug 23098).
				$revId = $article->getOldID()
					? $article->getOldID()
					: $wgRequest->getInt( 'baseRevId' ); // e.g. "show changes"/"preview"
			}
			# Zero oldid => current revision
			if ( !$revId ) {
				$revId = $latestId;
			}
			$editPage->fr_baseRevId = $revId;
		}
		return $editPage->fr_baseRevId;
	}

	 /**
	 * Adds brief review notes to a page.
	 * @param OutputPage $out
	 */
	public function addReviewNotes( &$data ) {
		$this->load();
		if ( $this->reviewNotes ) {
			$data .= $this->reviewNotes;
		}
		return true;
	}

	/**
	* Updates parser cache output to included needed versioning params.
	*/
	public function maybeUpdateMainCache( &$outputDone, &$pcache ) {
		global $wgUser, $wgRequest;
		$this->load();

		$action = $wgRequest->getVal( 'action', 'view' );
		if ( $action == 'purge' )
			return true; // already purging!
		# Only trigger on article view for content pages, not for protect/delete/hist
		if ( !self::isViewAction( $action ) || !$wgUser->isAllowed( 'review' ) )
			return true;
		if ( !$this->article->exists() || !$this->article->isReviewable() )
			return true;

		$parserCache = ParserCache::singleton();
		$parserOut = $parserCache->get( $this->article, $wgUser );
		if ( $parserOut ) {
			# Clear older, incomplete, cached versions
			# We need the IDs of templates and timestamps of images used
			if ( !isset( $parserOut->fr_newestTemplateID )
				|| !isset( $parserOut->fr_newestImageTime ) )
			{
				$this->article->getTitle()->invalidateCache();
			}
		}
		return true;
	}
}
