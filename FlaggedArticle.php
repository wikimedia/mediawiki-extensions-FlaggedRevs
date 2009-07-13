<?php

class FlaggedArticle extends Article {
	public $isDiffFromStable = false;
	protected $stableRev = null;
	protected $pageConfig = null;
	protected $flags = null;
	protected $reviewNotice = '';
	protected $reviewNotes = '';
	protected $file = NULL;
	protected $parent;

	/**
	 * Get the FlaggedArticle instance associated with $wgArticle/$wgTitle,
	 * or false if there isn't such a title
	 */
	public static function getGlobalInstance() {
		global $wgArticle, $wgTitle;
		if( !empty( $wgArticle ) ) {
			return self::getInstance( $wgArticle );
		} elseif( !empty( $wgTitle ) ) {
			return self::getTitleInstance( $wgTitle );
		}
		return null;
	}

	/**
	 * Get a FlaggedArticle for a given title.
	 * getInstance() is preferred if you have an Article available.
	 */
	public static function getTitleInstance( $title ) {
		if( !isset( $title->flaggedRevsArticle ) ) {
			$article = MediaWiki::articleFromTitle( $title );
			$article->flaggedRevsArticle = new FlaggedArticle( $article );
			$title->flaggedRevsArticle =& $article->flaggedRevsArticle;
		}
		return $title->flaggedRevsArticle;
	}

	/**
	 * Get an instance of FlaggedArticle for a given Article or Title object
	 * @param Article $article
	 */
	public static function getInstance( $article ) {
		# If instance already cached, return it!
		if( isset($article->flaggedRevsArticle) ) {
			return $article->flaggedRevsArticle;
		}
		if( isset( $article->getTitle()->flaggedRevsArticle ) ) {
			// Already have a FlaggedArticle cached in the Title object
			$article->flaggedRevsArticle =& $article->getTitle()->flaggedRevsArticle;
			$article->flaggedRevsArticle->parent =& $article;
		} else {
			// Create new FlaggedArticle
			$article->flaggedRevsArticle = new FlaggedArticle( $article );
			$article->getTitle()->flaggedRevsArticle =& $article->flaggedRevsArticle;
		}
		return $article->flaggedRevsArticle;
	}

	/**
	 * Construct a new FlaggedArticle from its Article parent
	 * Should not be called directly, use FlaggedArticle::getInstance()
	 */
	function __construct( $parent ) {
		$this->parent =& $parent;
	}

	/**
	 * Does the config and current URL params allow
	 * for overriding by stable revisions?
	 */
	public function pageOverride() {
		global $wgUser, $wgRequest;
		# This only applies to viewing content pages
		$action = $wgRequest->getVal( 'action', 'view' );
		if( !self::isViewAction($action) || !$this->isReviewable() )
			return false;
		# Does not apply to diffs/old revision...
		if( $wgRequest->getVal('oldid') || $wgRequest->getVal('diff') )
			return false;
		# Explicit requests  for a certain stable version handled elsewhere...
		if( $wgRequest->getVal('stableid') )
			return false;
		# Check user preferences
		if( $wgUser->getOption('flaggedrevsstable') )
			return !( $wgRequest->getIntOrNull('stable') === 0 );
		# Get page configuration
		$config = $this->getVisibilitySettings();
		# Does the stable version override the current one?
		if( $config['override'] ) {
			if( FlaggedRevs::ignoreDefaultVersion() ) {
				return ( $wgRequest->getIntOrNull('stable') === 1 );
			}
			# Viewer sees stable by default
			return !( $wgRequest->getIntOrNull('stable') === 0 );
		# We are explicity requesting the stable version?
		} elseif( $wgRequest->getIntOrNull('stable') === 1 ) {
			return true;
		}
		return false;
	}
	
	 /**
	 * Is this a view page action?
	 * @param $action string
	 * @returns bool
	 */
	protected static function isViewAction( $action ) {
		return ( $action == 'view' || $action == 'purge' || $action == 'render' );
	}

	 /**
	 * Is the stable version shown by default for this page?
	 * @returns bool
	 */
	public function showStableByDefault() {
		# Get page configuration
		$config = $this->getVisibilitySettings();
		return (bool)$config['override'];
	}
	
	 /**
	 * Is this user shown the stable version by default for this page?
	 * @returns bool
	 */
	public function showStableByDefaultUser() {
		# Get page configuration
		$config = $this->getVisibilitySettings();
		return ( $config['override'] && !FlaggedRevs::ignoreDefaultVersion() );
	}
	
	 /**
	 * Is most of the UI on this page to be hidden?
	 * @returns bool
	 */
	public function limitedUI() {
		global $wgFlaggedRevsUIForDefault;
		return ( $wgFlaggedRevsUIForDefault && !$this->showStableByDefault() );
	}

	/**
	 * Is this page less open than the site defaults?
	 * @returns bool
	 */
	public function isPageLocked() {
		return ( !FlaggedRevs::showStableByDefault() && $this->showStableByDefault() );
	}

	/**
	 * Is this page more open than the site defaults?
	 * @returns bool
	 */
	public function isPageUnlocked() {
		return ( FlaggedRevs::showStableByDefault() && !$this->showStableByDefault() );
	}

	/**
	 * Should tags only be shown for unreviewed content for this user?
	 * @returns bool
	 */
	public function lowProfileUI() {
		return FlaggedRevs::lowProfileUI() &&
			FlaggedRevs::showStableByDefault() == $this->showStableByDefault();
	}

	 /**
	 * Is this article reviewable?
	 * @param bool $titleOnly, only check if title is in reviewable namespace
	 */
	public function isReviewable( $titleOnly = false ) {
		global $wgFlaggedRevsReviewForDefault;
		if( !FlaggedRevs::isPageReviewable( $this->parent->getTitle() ) ) {
			return false;
		} elseif( !$titleOnly && $wgFlaggedRevsReviewForDefault && !$this->showStableByDefault() ) {
			return false;
		}
		return true;
	}
	
	/**
	* Is this page in patrolable?
	* @param bool $titleOnly, only check if title is in reviewable namespace
	* @return bool
	*/
	public function isPatrollable( $titleOnly = false ) {
		global $wgFlaggedRevsReviewForDefault;
		if( FlaggedRevs::isPagePatrollable( $this->parent->getTitle() ) ) {
			return true;
		} elseif( !$titleOnly && $wgFlaggedRevsReviewForDefault && !$this->showStableByDefault() ) {
			return true;
		}
		return false;
	}

	 /**
	 * Output review notice
	 */
	private function displayTag() {
		global $wgOut, $wgRequest;
		// UI may be limited to unobtrusive patrolling system
		if( $wgRequest->getVal('stableid') || !$this->limitedUI() ) {
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
		# Only for viewing old versions. UI may be limited to unobtrusive patrolling system.
		if( !$wgRequest->getVal('oldid') || $this->limitedUI() )
			return true;
		# We may have nav links like "direction=prev&oldid=x"
		$revID = $this->parent->getOldIDFromRequest();
		$frev = FlaggedRevision::newFromTitle( $this->parent->getTitle(), $revID );
		# Give a notice if this rev ID corresponds to a reviewed version...
		if( !is_null($frev) ) {
			wfLoadExtensionMessages( 'FlaggedRevs' );
			$time = $wgLang->date( $frev->getTimestamp(), true );
			$flags = $frev->getTags();
			$quality = FlaggedRevs::isQuality( $flags );
			$msg = $quality ? 'revreview-quality-source' : 'revreview-basic-source';
			$tag = wfMsgExt( $msg, array('parseinline'), $frev->getRevId(), $time );
			# Hide clutter
			if( !FlaggedRevs::useSimpleUI() && !empty($flags) ) {
				$tag .= " " . FlaggedRevsXML::ratingToggle() . 
					"<span id='mw-revisionratings' style='display:block;'><br/>" .
					wfMsgHtml('revreview-oldrating') .
					FlaggedRevsXML::addTagRatings( $flags ) . '</span>';
			}
			$tag = "<div id='mw-revisiontag-old' class='flaggedrevs_notice plainlinks noprint'>$tag</div>";
			$wgOut->addHTML( $tag );
		}
		return true;
	}

	 /**
	 * Replaces a page with the last stable version if possible
	 * Adds stable version status/info tags and notes
	 * Adds a quick review form on the bottom if needed
	 */
	public function setPageContent( &$outputDone, &$pcache ) {
		global $wgRequest, $wgOut, $wgUser, $wgLang;
		# Only trigger on article view for content pages, not for protect/delete/hist...
		$action = $wgRequest->getVal( 'action', 'view' );
		if( !self::isViewAction($action) || !$this->parent->exists() )
			return true;
		# Do not clutter up diffs any further and leave archived versions alone...
		if( $wgRequest->getVal('diff') || $wgRequest->getVal('oldid') ) {
			return true;
		}
		# Only trigger for reviewable pages
		if( !$this->isReviewable() ) {
			return true;
		}
		# Load required messages
		wfLoadExtensionMessages( 'FlaggedRevs' );
		$simpleTag = $old = $stable = false;
		$tag = $prot = '';
		# Check the newest stable version.
		$frev = $srev = $this->getStableRev();
		$stableId = $frev ? $frev->getRevId() : 0;
		# Also, check for any explicitly requested old stable version...
		$reqId = $wgRequest->getVal('stableid');
		if( $reqId === "best" ) {
			$reqId = FlaggedRevs::getPrimeFlaggedRevId( $this->parent );
		}
		if( $stableId && $reqId ) {
			if( $reqId != $stableId ) {
				$frev = FlaggedRevision::newFromTitle( $this->parent->getTitle(), $reqId, FR_TEXT );
				$old = true; // old reviewed version requested by ID
				if( !$frev ) {
					$wgOut->addWikiText( wfMsg('revreview-invalid') );
					$wgOut->returnToMain( false, $this->parent->getTitle() );
					# Tell MW that parser output is done
					$outputDone = true;
					$pcache = false;
					return true;
				}
			} else {
				$stable = true; // stable version requested by ID
			}
		}
		// Is the page config altered?
		if( $this->isPageLocked() ) {
			$prot = "<span class='fr-icon-locked' title=\"".
				wfMsgHtml('revreview-locked-title')."\"></span>";
		} elseif( $this->isPageUnlocked() ) {
			$prot = "<span class='fr-icon-unlocked' title=\"".
				wfMsgHtml('revreview-unlocked-title')."\"></span>";
		}
		// Is there no stable version?
		if( is_null($frev) ) {
			// Add "no reviewed version" tag, but not for printable output.
			if( !$wgOut->isPrintable() ) {
				// Simple icon-based UI
				if( FlaggedRevs::useSimpleUI() ) {
					$msg = $old ? 'revreview-quick-invalid' : 'revreview-quick-none';
					$tag .= "{$prot}<span class='fr-icon-current plainlinks'></span>" .
						wfMsgExt($msg,array('parseinline'));
					$tag = "<div id='mw-revisiontag' class='flaggedrevs_short plainlinks noprint'>$tag</div>";
					$this->reviewNotice .= $tag;
				// Standard UI
				} else {
					$msg = $old ? 'revreview-invalid' : 'revreview-noflagged';
					$tag = "<div id='mw-revisiontag' class='flaggedrevs_notice plainlinks noprint'>" .
						"{$prot}<span class='fr-icon-current plainlinks'></span>" .
						wfMsgExt($msg, array('parseinline')) . "</div>";
					$this->reviewNotice .= $tag;
				}
			}
			# Show notice bar/icon
			$this->displayTag();
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
		if( $old ) {
			$this->showOldReviewedVersion( $srev, $frev, $tag, $prot );
			$outputDone = true; # Tell MW that parser output is done
			$pcache = false;
		// Looking at some specific old revision (&oldid=x) or if FlaggedRevs is not
		// set to override given the relevant conditions (like &action=protect).
		} elseif( !$stable && !$this->pageOverride() ) {
			$this->showRegularVersion( $srev, $frev, $tag, $prot );
		// The relevant conditions are met to override the page with the stable version.
		} else {
	   		$this->showStableVersion( $srev, $frev, $tag, $prot );
			$outputDone = true; # Tell MW that parser output is done
			$pcache = false;
		}
		# Some checks for which tag CSS to use
		if( FlaggedRevs::useSimpleUI() ) $tagClass = 'flaggedrevs_short';
		elseif( $simpleTag ) $tagClass = 'flaggedrevs_notice';
		elseif( $pristine ) $tagClass = 'flaggedrevs_pristine';
		elseif( $quality ) $tagClass = 'flaggedrevs_quality';
		else $tagClass = 'flaggedrevs_basic';
		# Wrap tag contents in a div
		if( $tag !='' ) {
			$tag = "<div id='mw-revisiontag' class='$tagClass plainlinks noprint'>$tag</div>";
			$this->reviewNotice .= $tag;
		}
		# Show notice bar/icon
		$this->displayTag();

		return true;
	}
	
	/**
	* @param $srev stable version
	* @param $frev selected flagged revision
	* @param $tag review box/bar info
	* @param $prot protection notice
	* Tag output function must be called by caller
	* Parser cache control deferred to caller
	*/
	protected function showRegularVersion( $srev, $frev, &$tag, $prot ) {
		global $wgUser, $wgOut, $wgLang, $wgRequest;
		$flags = $frev->getTags();
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		$revsSince = FlaggedRevs::getRevCountSince( $this->parent, $srev->getRevId() );
		$synced = false;
		# We only care about syncing if not viewing an old stable version
		if( $srev->getRevId() == $frev->getRevId() ) {
			$synced = FlaggedRevs::stableVersionIsSynced( $frev, $this->parent );
			if( $synced ) $this->getReviewNotes( $frev ); // Still the same
		}
		$pending = '';
		# Give notice to newer users if an unreviewed edit was completed...
		if( !$synced && $wgRequest->getVal('shownotice') && !$wgUser->isAllowed('review') ) {
			$tooltip = wfMsgHtml('revreview-draft-title');
			$pending = "{$prot}<span class='fr-icon-current' title=\"{$tooltip}\"></span>" .
				wfMsgExt('revreview-edited',array('parseinline'),$frev->getRevId(),$revsSince);
			$pending = "<div id='mw-reviewnotice' class='flaggedrevs_preview plainlinks'>$pending</div>";
			# Notice should always use subtitle
			$this->reviewNotice = $pending;
		}
		# If they are synced, do special styling
		$simpleTag = !$synced;
		# Construct some tagging for non-printable outputs. Note that the pending
		# notice has all this info already, so don't do this if we added that already.
		if( !$wgOut->isPrintable() && !$pending && !($this->lowProfileUI() && $synced) ) {
			$class = 'fr-icon-current'; // default
			$tooltip = 'revreview-draft-title';
			// Simple icon-based UI
			if( FlaggedRevs::useSimpleUI() ) {
				if( $synced ) {
					$msg = $quality ? 'revreview-quick-quality-same' : 'revreview-quick-basic-same';
					$class = $quality ? 'fr-icon-quality' : 'fr-icon-stable';
					$tooltip = $quality ? 'revreview-quality-title' : 'revreview-stable-title';
					$msgHTML = wfMsgExt( $msg, array('parseinline'), $frev->getRevId(), $revsSince );
				} else {
					$msg = $quality ? 'revreview-quick-see-quality' : 'revreview-quick-see-basic';
					$msgHTML = wfMsgExt( $msg, array('parseinline'), $frev->getRevId(), $revsSince );
				}
				$tooltip = wfMsgHtml($tooltip);
				$msgHTML = "{$prot}<span class='{$class}' title=\"{$tooltip}\"></span>$msgHTML";
				$tag .= FlaggedRevsXML::prettyRatingBox( $frev, $msgHTML, $revsSince,
							$synced, $synced, false );
			// Standard UI
			} else {
				if( $synced ) {
					$msg = $quality ? 'revreview-quality-same' : 'revreview-basic-same';
					$class = $quality ? 'fr-icon-quality' : 'fr-icon-stable';
					$tooltip = $quality ? 'revreview-quality-title' : 'revreview-stable-title';
					$msgHTML = wfMsgExt( $msg, array('parseinline'), $frev->getRevId(),
									$time, $revsSince );
				} else {
					$msg = $quality ? 'revreview-newest-quality' : 'revreview-newest-basic';
					$msg .= ($revsSince == 0) ? '-i' : '';
					$msgHTML = wfMsgExt( $msg, array('parseinline'), $frev->getRevId(),
									$time, $revsSince );
				}
				$tooltip = wfMsgHtml($tooltip);
				$tag .= "{$prot}<span class='{$class}' title=\"{$tooltip}\"></span>" . $msgHTML;
				# Hide clutter
				if( !empty($flags) ) {
					$tag .= " " . FlaggedRevsXML::ratingToggle();
					$tag .= "<span id='mw-revisionratings' style='display:block;'><br/>" .
						wfMsgHtml('revreview-oldrating') . FlaggedRevsXML::addTagRatings( $flags ) . '</span>';
				}
			}
		}
		# Index the stable version only if it is the default
		if( $this->showStableByDefault() ) {
			$wgOut->setRobotPolicy( 'noindex,nofollow' );
		}
	}
	
	/**
	* @param $srev stable version
	* @param $frev selected flagged revision
	* @param $tag review box/bar info
	* @param $prot protection notice
	* Tag output function must be called by caller
	* Parser cache control deferred to caller
	*/
	protected function showOldReviewedVersion( $srev, $frev, &$tag, $prot ) {
		global $wgOut, $wgLang;
		$flags = $frev->getTags();
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		$revsSince = FlaggedRevs::getRevCountSince( $this->parent, $srev->getRevId() );
		$text = $frev->getRevText();
	   	$parserOut = FlaggedRevs::parseStableText( $this->parent, $text, $frev->getRevId() );
		# Construct some tagging for non-printable outputs. Note that the pending
		# notice has all this info already, so don't do this if we added that already.
		if( !$wgOut->isPrintable() ) {
			$class = $quality ? 'fr-icon-quality' : 'fr-icon-stable';
			$tooltip = $quality ? 'revreview-quality-title' : 'revreview-stable-title';
			$tooltip = wfMsgHtml($tooltip);
			// Simple icon-based UI
			if( FlaggedRevs::useSimpleUI() ) {
				$msg = $quality ? 'revreview-quick-quality-old' : 'revreview-quick-basic-old';
				$html = "{$prot}<span class='{$class}' title=\"{$tooltip}\"></span>" .
					wfMsgExt( $msg, array('parseinline'), $frev->getRevId(), $time );
				$tag = FlaggedRevsXML::prettyRatingBox( $frev, $html, $revsSince,
							true, false, true );
			// Standard UI
			} else {
				$msg = $quality ? 'revreview-quality-old' : 'revreview-basic-old';
				$tag = "{$prot}<span class='{$class}' title=\"{$tooltip}\"></span>" .
					wfMsgExt( $msg, array('parseinline'), $frev->getRevId(), $time );
				# Hide clutter
				if( !empty($flags) ) {
					$tag .= " " . FlaggedRevsXML::ratingToggle();
					$tag .= "<span id='mw-revisionratings' style='display:block;'><br/>" .
						wfMsgHtml('revreview-oldrating') .
						FlaggedRevsXML::addTagRatings( $flags ) . '</span>';
				}
			}
		}
		# Output HTML
		$this->getReviewNotes( $frev );
	   	$wgOut->addParserOutput( $parserOut );
		$wgOut->setRevisionId( $frev->getRevId() );
		# Index the stable version only
		$wgOut->setRobotPolicy( 'noindex,nofollow' );
	}

	/**
	* @param $srev stable version
	* @param $frev selected flagged revision
	* @param $tag review box/bar info
	* @param $prot protection notice
	* Tag output function must be called by caller
	* Parser cache control deferred to caller
	*/
	protected function showStableVersion( $srev, $frev, &$tag, $prot ) {
		global $wgOut, $wgLang;
		$flags = $frev->getTags();
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		# We will be looking at the reviewed revision...
	   	$revsSince = FlaggedRevs::getRevCountSince( $this->parent, $frev->getRevId() );
		# Get parsed stable version
		$parserOut = FlaggedRevs::getPageCache( $this->parent );
		if( $parserOut == false ) {
			$text = $frev->getRevText();
	   		$parserOut = FlaggedRevs::parseStableText( $this->parent, $text, $frev->getRevId() );
	   		# Update the stable version cache
			FlaggedRevs::updatePageCache( $this->parent, $parserOut );
	   	}
		$synced = FlaggedRevs::stableVersionIsSynced( $frev, $this->parent, $parserOut, null );
		# Construct some tagging
		if( !$wgOut->isPrintable() && !($this->lowProfileUI() && $synced) ) {
			$class = $quality ? 'fr-icon-quality' : 'fr-icon-stable';
			$tooltip = $quality ? 'revreview-quality-title' : 'revreview-stable-title';
			$tooltip = wfMsgHtml($tooltip);
			// Simple icon-based UI
			if( FlaggedRevs::useSimpleUI() ) {
				$msg = $quality ? 'revreview-quick-quality' : 'revreview-quick-basic';
				# uses messages 'revreview-quick-quality-same', 'revreview-quick-basic-same'
				$msg = $synced ? "{$msg}-same" : $msg;
				$html = "{$prot}<span class='{$class}' title=\"{$tooltip}\"></span>" .
					wfMsgExt( $msg, array('parseinline'), $frev->getRevId(), $revsSince );
				$tag = FlaggedRevsXML::prettyRatingBox( $frev, $html, $revsSince, true, $synced );
			// Standard UI
			} else {
				$msg = $quality ? 'revreview-quality' : 'revreview-basic';
				if( $synced ) {
					# uses messages 'revreview-quality-same', 'revreview-basic-same'
					$msg .= '-same';
				} elseif( $revsSince == 0 ) {
					# uses messages 'revreview-quality-i', 'revreview-basic-i'
					$msg .= '-i';
				}
				$tag = "{$prot}<span class='{$class} plainlinks' title=\"{$tooltip}\"></span>" .
					wfMsgExt( $msg, array('parseinline'), $frev->getRevId(), $time, $revsSince );
				if( !empty($flags) ) {
					$tag .= " " . FlaggedRevsXML::ratingToggle();
					$tag .= "<span id='mw-revisionratings' style='display:block;'><br/>" .
						FlaggedRevsXML::addTagRatings( $flags ) . '</span>';
				}
			}
		}
		# Output HTML
		$this->getReviewNotes( $frev );
	   	$wgOut->addParserOutput( $parserOut );
		$wgOut->setRevisionId( $frev->getRevId() );
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
		global $wgRequest;
		# Determine timestamp. A reviewed version may have explicitly been requested...
		$frev = null;
		$time = false;
		if( $reqId = $wgRequest->getVal('stableid') ) {
			$frev = FlaggedRevision::newFromTitle( $this->parent->getTitle(), $reqId );
		} elseif( $this->pageOverride() ) {
			$frev = $this->getStableRev();
		}
		if( !is_null($frev) ) {
			$time = $frev->getFileTimestamp();
			// B/C, may be stored in associated image version metadata table
			if( !$time ) {
				$dbr = wfGetDB( DB_SLAVE );
				$time = $dbr->selectField( 'flaggedimages',
					'fi_img_timestamp',
					array( 'fi_rev_id' => $frev->getRevId(),
						'fi_name' => $this->parent->getTitle()->getDBkey() ),
					__METHOD__ );
			}
			# NOTE: if not found, this will use the current
			$this->parent = new ImagePage( $this->parent->getTitle(), $time );
		}
		if( !$time ) {
			# Try request parameter
			$time = $wgRequest->getVal( 'filetimestamp', false );
		}

		if( !$time ) {
			return; // Use the default behaviour
		}

		$title = $this->parent->getTitle();
		$displayFile = wfFindFile( $title, $time );
		# If none found, try current
		if( !$displayFile ) {
			wfDebug( __METHOD__.": {$title->getPrefixedDBkey()}: $time not found, using current\n" );
			$displayFile = wfFindFile( $title );
			# If none found, use a valid local placeholder
			if( !$displayFile ) {
				$displayFile = wfLocalFile( $title ); // fallback to current
			}
			$normalFile = $displayFile;
		# If found, set $normalFile
		} else {
			wfDebug( __METHOD__.": {$title->getPrefixedDBkey()}: using timestamp $time\n" );
			$normalFile = wfFindFile( $title );
		}
	}

	/**
	 * Adds latest stable version tag to page when editing
	 */
	public function addToEditView( $editPage ) {
		global $wgRequest, $wgOut;
		# Must be reviewable. UI may be limited to unobtrusive patrolling system.
		if( !$this->isReviewable() || $this->limitedUI() )
			return true;
		# Show stabilization log
		$this->showStabilityLog();
		# Set new body html text as that of now
		$tag = $warning = $prot = '';
		# Check the newest stable version
		$quality = 0;
		if( $frev = $this->getStableRev() ) {
			global $wgLang, $wgUser, $wgFlaggedRevsAutoReview;
			# Find out revision id
			$revId = $editPage->oldid ? $editPage->oldid : $this->parent->getLatest();
			# If this will be autoreviewed, notify the user...
			if( !FlaggedRevs::lowProfileUI() && $wgFlaggedRevsAutoReview && $wgUser->isAllowed('review') ) {
				# If we are editing some reviewed revision, any changes this user
				# makes will be autoreviewed...
				$ofrev = FlaggedRevision::newFromTitle( $this->parent->getTitle(), $revId );
				if( !is_null($ofrev) ) {
					wfLoadExtensionMessages( 'FlaggedRevs' );
					$msg = ( $revId==$frev->getRevId() ) ? 'revreview-auto-w' : 'revreview-auto-w-old';
					$warning = "<div id='mw-autoreviewtag' class='flaggedrevs_warning plainlinks'>" .
						wfMsgExt($msg,array('parseinline')) . "</div>";
				}
			# Let new users know about review procedure a tag
			} elseif( !$wgUser->getId() && $this->showStableByDefault() ) {
				wfLoadExtensionMessages( 'FlaggedRevs' );
				$warning = "<div id='mw-editwarningtag' class='flaggedrevs_editnotice plainlinks'>" .
						wfMsgExt('revreview-editnotice',array('parseinline')) . "</div>";
			}
			if( $frev->getRevId() != $revId ) {
				wfLoadExtensionMessages( 'FlaggedRevs' );
				$time = $wgLang->date( $frev->getTimestamp(), true );
				$flags = $frev->getTags();
				if( FlaggedRevs::isQuality($flags) ) {
					$quality = FlaggedRevs::isPristine($flags) ? 2 : 1;
				}
				$revsSince = FlaggedRevs::getRevCountSince( $this->parent, $frev->getRevId() );
				// Is the page config altered?
				if( $this->isPageLocked() ) {
					$prot = "<span class='fr-icon-locked' title=\"".
						wfMsgHtml('revreview-locked-title')."\"></span>";
				} elseif( $this->isPageUnlocked() ) {
					$prot = "<span class='fr-icon-unlocked' title=\"".
						wfMsgHtml('revreview-unlocked-title')."\"></span>";
				}
				# Streamlined UI
				if( FlaggedRevs::useSimpleUI() ) {
					$msg = $quality ? 'revreview-newest-quality' : 'revreview-newest-basic';
					$msg .= ($revsSince == 0) ? '-i' : '';
					$tag = "{$prot}<span class='fr-checkbox'></span>" .
						wfMsgExt( $msg, array('parseinline'), $frev->getRevId(), $time, $revsSince );
					$tag = "<div id='mw-revisiontag-edit' class='flaggedrevs_editnotice plainlinks'>$tag</div>";
				# Standard UI
				} else {
					$msg = $quality ? 'revreview-newest-quality' : 'revreview-newest-basic';
					$msg .= ($revsSince == 0) ? '-i' : '';
					$tag = "{$prot}<span class='fr-checkbox'></span>" .
						wfMsgExt( $msg, array('parseinline'), $frev->getRevId(), $time, $revsSince );
					# Hide clutter
					if( !empty($flags) ) {
						$tag .= " " . FlaggedRevsXML::ratingToggle();
						$tag .= '<span id="mw-revisionratings" style="display:block;"><br/>' .
							wfMsg('revreview-oldrating') . FlaggedRevsXML::addTagRatings( $flags ) . '</span>';
					}
					$tag = "<div id='mw-revisiontag-edit' class='flaggedrevs_editnotice plainlinks'>$tag</div>";
				}
			}
			# Output notice and warning for editors
			if( $tag || $warning ) {
				$wgOut->addHTML( $tag . $warning );
			}

			if( $frev->getRevId() == $revId )
				return true; // nothing to show here
			# Show diff to stable, to make things less confusing.
			if( !$wgUser->isAllowed('review') && !$this->showStableByDefaultUser() ) {
				return true;
			}
			# Don't show for old revisions, diff, preview, or undo.
			if( $editPage->oldid || $editPage->section === "new"
				|| in_array($editPage->formtype,array('diff','preview')) )
			{
				return true; // nothing to show here
			}
			
			# Conditions are met to show diff...
			wfLoadExtensionMessages( 'FlaggedRevs' ); // load required messages
			$leftNote = $quality ? 'revreview-quality-title' : 'revreview-stable-title';
			$rClass = FlaggedRevsXML::getQualityColor( false );
			$lClass = FlaggedRevsXML::getQualityColor( (int)$quality );
			$rightNote = "<span class='$rClass'>[".wfMsgHtml('revreview-draft-title')."]</span>";
			$leftNote = "<span class='$lClass'>[".wfMsgHtml($leftNote)."]</span>";
			$text = $frev->getRevText();
			# Are we editing a section?
			$section = ($editPage->section == "") ? false : intval($editPage->section);
			if( $section !== false ) {
				$text = $this->parent->getSection( $text, $section );
			}
			if( $text !== false && strcmp($text,$editPage->textbox1) !== 0 ) {
				$diffEngine = new DifferenceEngine();
				$diffEngine->showDiffStyle();
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
					$diffEngine->generateDiffBody( $text, $editPage->textbox1 ) .
					"</table>" .
					"</div>\n" );
			}
		}
		return true;
	}
	
	protected function showStabilityLog() {
		global $wgOut;
		# Only for pages manually made to be stable...
		if( $this->isPageLocked() ) {
			wfLoadExtensionMessages( 'FlaggedRevs' );
			$wgOut->addHTML( "<div class='mw-warning-with-logexcerpt'>" );
			$wgOut->addWikiMsg( 'revreview-locked' );
			LogEventsList::showLogExtract( $wgOut, 'stable',
				$this->parent->getTitle()->getPrefixedText(), '', 1 );
			$wgOut->addHTML( "</div>" );
		# ...or unstable
		} elseif( $this->isPageUnlocked() ) {
			wfLoadExtensionMessages( 'FlaggedRevs' );
			$wgOut->addHTML( "<div class='mw-warning-with-logexcerpt'>" );
			$wgOut->addWikiMsg( 'revreview-unlocked' );
			LogEventsList::showLogExtract( $wgOut, 'stable',
				$this->parent->getTitle()->getPrefixedText(), '', 1 );
			$wgOut->addHTML( "</div>" );
		}
		return true;
	}

	/**
	 * Add unreviewed pages links
	 */
	public function addToCategoryView() {
		global $wgOut, $wgUser;
		if( !$wgUser->isAllowed( 'review' ) ) {
			return true;
		}
		wfLoadExtensionMessages( 'FlaggedRevs' );
		# Load special page names
		wfLoadExtensionMessages( 'OldReviewedPages' );
		wfLoadExtensionMessages( 'UnreviewedPages' );

		$category = $this->parent->getTitle()->getText();

		$unreviewed = SpecialPage::getTitleFor( 'UnreviewedPages' );
		$unreviewedLink = $wgUser->getSkin()->makeKnownLinkObj( $unreviewed,
			wfMsgHtml('unreviewedpages'), 'category=' . urlencode($category) );

		$oldreviewed = SpecialPage::getTitleFor( 'OldReviewedPages' );
		$oldreviewedLink = $wgUser->getSkin()->makeKnownLinkObj( $oldreviewed,
			wfMsgHtml('oldreviewedpages'), 'category=' . urlencode($category) );

		$wgOut->appendSubtitle("<span id='mw-category-oldreviewed'>$unreviewedLink / $oldreviewedLink</span>");

		return true;
	}

	 /**
	 * Add review form to pages when necessary
	 */
	public function addReviewForm( &$data ) {
		global $wgRequest, $wgUser, $wgOut;
		# User must have review rights and page must be reviewable
		if( !$wgUser->isAllowed('review')  || !$this->parent->exists() || !$this->isReviewable() ) {
			return true;
		}
		# Unobtrusive patrolling UI only shows forms if requested
		if( !$wgRequest->getInt('reviewform') && $this->limitedUI() ) {
			return true;
		}
		# Check action and if page is protected
		$action = $wgRequest->getVal( 'action', 'view' );
		# Must be view/diff action...and title must not be ambiguous
		if( !self::isViewAction($action) || !$wgRequest->getVal('title') ) {
			return true;
		}
		$this->addQuickReview( $data, $wgRequest->getVal('diff'), false );
		return true;
	}

	 /**
	 * Add link to stable version setting to protection form
	 */
	public function addVisibilityLink( &$data ) {
		global $wgUser, $wgRequest, $wgOut;
		# Check only if the title is reviewable
		if( !$this->isReviewable(true) ) {
			return true;
		}
		$action = $wgRequest->getVal( 'action', 'view' );
		if( $action == 'protect' || $action == 'unprotect' ) {
			wfLoadExtensionMessages( 'FlaggedRevs' );
			wfLoadExtensionMessages( 'Stabilization' ); // Load special page name
			$title = SpecialPage::getTitleFor( 'Stabilization' );
			# Give a link to the page to configure the stable version
			$frev = $this->getStableRev();
			if( $frev && $frev->getRevId() == $this->parent->getLatest() ) {
				$wgOut->prependHTML( "<span class='plainlinks'>" .
					wfMsgExt( 'revreview-visibility',array('parseinline'),
						$title->getPrefixedText() ) . "</span>" );
			} elseif( $frev ) {
				$wgOut->prependHTML( "<span class='plainlinks'>" .
					wfMsgExt( 'revreview-visibility2',array('parseinline'),
						$title->getPrefixedText() ) . "</span>" );
			} else {
				$wgOut->prependHTML( "<span class='plainlinks'>" .
					wfMsgExt( 'revreview-visibility3',array('parseinline'),
						$title->getPrefixedText() ) . "</span>" );
			}
		}
		return true;
	}

	/**
	 * Modify an array of action links, as used by SkinTemplateNavigation and
	 * SkinTemplateTabs, to inlude flagged revs UI elements
	 */
	public function setActionTabs( $skin, &$actions ) {
		global $wgRequest, $wgUser, $wgFlaggedRevTabs;
	
		$title = $this->parent->getTitle()->getSubjectPage();
		if ( !FlaggedRevs::isPageReviewable( $title ) || !$title->exists() ) {
			// Exit, since only reviewable pages need these tabs
			return true;
		}
		// Check if we should show a stabilization tab
		if (
			!$skin->mTitle->isTalkPage() &&
			$wgUser->isAllowed( 'stablesettings' ) &&
			is_array( $actions ) &&
			!isset( $actions['protect'] ) &&
			!isset( $actions['unprotect'] )
		) {
			wfLoadExtensionMessages( 'Stabilization' );
			$stableTitle = SpecialPage::getTitleFor( 'Stabilization' );
			// Add a tab
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
	public function setViewTabs( $skin, &$views ) {
		global $wgRequest, $wgUser, $wgFlaggedRevTabs;
		
		$title = $this->parent->getTitle()->getSubjectPage();
		$action = $wgRequest->getVal( 'action', 'view' );
		$fa = FlaggedArticle::getTitleInstance( $title );
		if ( !$fa->isReviewable() || $this->limitedUI() ) {
			// Exit, since this isn't a reviewable page or the UI is hidden
			return true;
		}
	   	$srev = $this->getStableRev( $action == 'rollback' ? FR_MASTER : 0 );
	   	if( is_null( $srev ) ) {
			// Exit, since no stable revision exists
			return true;
		}
		wfLoadExtensionMessages( 'FlaggedRevs' );
		$article = new Article( $title );
		$synced = FlaggedRevs::stableVersionIsSynced( $srev, $article );
	   	if ( !$skin->mTitle->isTalkPage() && !$synced ) {
	   		if ( isset( $views['edit'] ) ) {
				if ( $this->showStableByDefault() ) {
					$views['edit']['text'] = wfMsg('revreview-edit');
				}
				if ( $this->pageOverride() ) {
					$views['edit']['href'] = $title->getLocalUrl( 'action=edit' );
				}
	   		}
	   		if ( isset( $views['viewsource'] ) ) {
				if ( $this->showStableByDefault() ) {
					$views['viewsource']['text'] = wfMsg('revreview-source');
				}
				if ( $this->pageOverride() ) {
					$views['viewsource']['href'] = $title->getLocalUrl( 'action=edit' );
				}
			}
	   	}
	 	if ( !$wgFlaggedRevTabs || $synced ) {
	 		// Exit, since either the flagged revisions tabs should not be shown
	 		// or the page is already the most current revision
	   		return true;
	 	}
	 	$tabs = array(
	 		'stable' => array(
				'text' => wfMsg( 'revreview-stable' ),
				'href' => $title->getLocalUrl( 'stable=1' ),
	 			'class' => ''
	 		),
	 		'current' => array(
				'text' => wfMsg( 'revreview-current' ),
				'href' => $title->getLocalUrl( 'stable=0&redirect=no' ),
	 			'class' => ''
	 		),
	 	);
		if ( $this->pageOverride() || $wgRequest->getVal( 'stableid' ) ) {
			// We are looking a the stable version
			$tabs['stable']['class'] = 'selected';
		}
		elseif (
			( self::isViewAction( $action ) || $action == 'edit' ) &&
			!$skin->mTitle->isTalkPage()
		) {
			// We are looking at the current revision or in edit mode
			$tabs['current']['class'] = 'selected';
		}
		$first = true;
		$newViews = array();
		foreach ( $views as $tabAction => $data ) {
			if ( $first ) {
				if( $synced ) {
					// Use existing first tabs when synced
					$newViews[$tabAction] = $data;
				} else {
					// Use split current and stable tabs when not synced
					$newViews['stable'] = $tabs['stable'];
					$newViews['current'] = $tabs['current'];
				}
				$first = false;
			} else {
				$newViews[$tabAction] = $data;
			}
	   	}
	   	// Replaces old tabs with new tabs
	   	$views = $newViews;
		return true;
	}
	
	/**
	 * @param FlaggedRevision $frev
	 * @return string, revision review notes
	 */
	public function getReviewNotes( $frev ) {
		global $wgUser;
		if( !FlaggedRevs::allowComments() || !$frev || !$frev->getComment() ) {
			return '';
		}
		wfLoadExtensionMessages( 'FlaggedRevs' );
   		$notes = "<br/><div class='flaggedrevs_notes plainlinks'>";
   		$notes .= wfMsgExt('revreview-note', array('parseinline'), User::whoIs( $frev->getUser() ) );
   		$notes .= '<br/><i>' . $wgUser->getSkin()->formatComment( $frev->getComment() ) . '</i></div>';
		$this->reviewNotes = $notes;
	}

	/**
	* When comparing the stable revision to the current after editing a page, show
	* a tag with some explaination for the diff.
	*/
	public function addDiffNoticeAndIncludes( $diff, $oldRev, $newRev ) {
		global $wgRequest, $wgUser, $wgOut, $wgMemc;
		# Page must be reviewable. Exempt printer-friendly output.
		# UI may be limited to unobtrusive patrolling system
		if( $wgOut->isPrintable() || !$this->isReviewable() || $this->limitedUI() )
			return true;
		# Load required messages
		wfLoadExtensionMessages( 'FlaggedRevs' );
		# Check if this might be the diff to stable. If so, enhance it.
		if( $newRev->isCurrent() && $oldRev ) {
			$article = new Article( $newRev->getTitle() );
			# Try the sync value cache...
			$key = wfMemcKey( 'flaggedrevs', 'includesSynced', $article->getId() );
			$value = FlaggedRevs::getMemcValue( $wgMemc->get($key), $article );
			$synced = ($value === "true") ? true : false; // default as false to trigger query
			$frev = $this->getStableRev();
			if( $frev && $frev->getRevId() == $oldRev->getID() ) {
				global $wgParserCacheExpireTime;

				$changeList = array();
				$skin = $wgUser->getSkin();

				# Try the cache. Uses format <page ID>-<UNIX timestamp>.
				$key = wfMemcKey( 'stableDiffs', 'templates', $article->getId() );
				$tmpChanges = FlaggedRevs::getMemcValue( $wgMemc->get($key), $article );
				if( empty($tmpChanges) && !$synced ) {
					$tmpChanges = false; // don't use cache
				}

				# Make a list of each changed template...
				if( $tmpChanges === false ) {
					$dbr = wfGetDB( DB_SLAVE );
					// Get templates where the current and stable are not the same revision
					$ret = $dbr->select( array('flaggedtemplates','page','flaggedpages'),
						array( 'ft_namespace', 'ft_title', 'fp_stable','ft_tmp_rev_id', 'page_latest' ),
						array( 'ft_rev_id' => $frev->getRevId(),
							'page_namespace = ft_namespace',
							'page_title = ft_title' ),
						__METHOD__,
						array(), /* OPTIONS */
						array( 'flaggedpages' => array('LEFT JOIN','fp_page_id = page_id') )
					);
					$tmpChanges = array();
					while( $row = $dbr->fetchObject( $ret ) ) {
						$title = Title::makeTitleSafe( $row->ft_namespace, $row->ft_title );
						$revIdDraft = $row->page_latest;
						// stable time -> time when reviewed (unless the other is newer)
						$revIdStable = isset($row->fp_stable) && $row->fp_stable >= $row->ft_tmp_rev_id ?
							$row->fp_stable : $row->ft_tmp_rev_id;
						// compare to current
						if( $revIdDraft > $revIdStable ) {
							$tmpChanges[] = $skin->makeKnownLinkObj( $title, $title->getPrefixedText(),
								"diff=cur&oldid={$revIdStable}" );
						}
					}
					$wgMemc->set( $key, FlaggedRevs::makeMemcObj($tmpChanges), $wgParserCacheExpireTime );
				}
				# Add set to list
				if( $tmpChanges )
					$changeList += $tmpChanges;

				# Try the cache. Uses format <page ID>-<UNIX timestamp>.
				$key = wfMemcKey( 'stableDiffs', 'images', $article->getId() );
				$imgChanges = FlaggedRevs::getMemcValue( $wgMemc->get($key), $article );
				if( empty($imgChanges) && !$synced ) {
					$imgChanges = false; // don't use cache
				}

				// Get list of each changed image...
				if( $imgChanges === false ) {
					$dbr = wfGetDB( DB_SLAVE );
					// Get images where the current and stable are not the same revision
					$ret = $dbr->select( array('flaggedimages','page','image','flaggedpages','flaggedrevs'),
						array( 'fi_name', 'fi_img_timestamp', 'fr_img_timestamp' ),
						array( 'fi_rev_id' => $frev->getRevId() ),
						__METHOD__,
						array(), /* OPTIONS */
						array( 'page' => array('LEFT JOIN','page_namespace = '. NS_FILE .' AND page_title = fi_name'),
							'image' => array('LEFT JOIN','img_name = fi_name'),
							'flaggedpages' => array('LEFT JOIN','fp_page_id = page_id'),
							'flaggedrevs' => array('LEFT JOIN','fr_page_id = fp_page_id AND fr_rev_id = fp_stable') )
					);
					$imgChanges = array();
					while( $row = $dbr->fetchObject( $ret ) ) {
						$title = Title::makeTitleSafe( NS_FILE, $row->fi_name );
						// stable time -> time when reviewed (unless the other is newer)
						$timestamp = isset($row->fr_img_timestamp) && $row->fr_img_timestamp >= $row->fi_img_timestamp ?
							$row->fr_img_timestamp : $row->fi_img_timestamp;
						// compare to current
						$file = wfFindFile( $title );
						if( $file && $file->getTimestamp() > $timestamp )
							$imgChanges[] = $skin->makeKnownLinkObj( $title, $title->getPrefixedText() );
					}
					$wgMemc->set( $key, FlaggedRevs::makeMemcObj($imgChanges), $wgParserCacheExpireTime );
				}
				if( $imgChanges )
					$changeList += $imgChanges;

				# Some important information...
				$notice = '';
				if( count($changeList) > 0 ) {
					$notice = '<br/>' . wfMsgExt('revreview-update-use', array('parseinline'));
				} elseif( !$synced ) {
					$diff->mTitle->invalidateCache(); // bad cache, said they were not synced
				}

				# If the user is allowed to review, prompt them!
				if( empty($changeList) && $wgUser->isAllowed('review') ) {
					$wgOut->addHTML( "<div id='mw-difftostable' class='flaggedrevs_diffnotice plainlinks'>" .
						wfMsgExt('revreview-update-none', array('parseinline')).$notice.'</div>' );
				} elseif( !empty($changeList) && $wgUser->isAllowed('review') ) {
					$changeList = implode(', ',$changeList);
					$wgOut->addHTML( "<div id='mw-difftostable' class='flaggedrevs_diffnotice plainlinks'>" .
						wfMsgExt('revreview-update', array('parseinline')).'&nbsp;'.
							$changeList.$notice.'</div>' );
				} elseif( !empty($changeList) ) {
					$changeList = implode(', ',$changeList);
					$wgOut->addHTML( "<div id='mw-difftostable' class='flaggedrevs_diffnotice plainlinks'>" .
						wfMsgExt('revreview-update-includes', array('parseinline')).'&nbsp;'.
							$changeList.$notice.'</div>' );
				}
				# Set flag for review form to tell it to autoselect tag settings from the
				# old revision unless the current one is tagged to.
				if( !FlaggedRevision::newFromTitle( $diff->mTitle, $newRev->getID() ) ) {
					$this->isDiffFromStable = true;
				}

				# Set a key to note that someone is viewing this
				if( $wgUser->isAllowed('review') ) {
					$key = wfMemcKey( 'stableDiffs', 'underReview', $oldRev->getID(), $newRev->getID() );
					$wgMemc->set( $key, '1', 10*60 ); // 10 min
				}
			}
		}
		$newRevQ = FlaggedRevs::getRevQuality( $newRev->getPage(), $newRev->getId() );
		$oldRevQ = $oldRev ? FlaggedRevs::getRevQuality( $newRev->getPage(), $oldRev->getId() ) : false;
		# Diff between two revisions
		if( $oldRev ) {
			$wgOut->addHTML( "<table class='fr-diff-ratings' width='100%'><tr>" );

			$class = FlaggedRevsXML::getQualityColor( $oldRevQ );
			if( $oldRevQ !== false ) {
				$msg = $oldRevQ ? 'hist-quality' : 'hist-stable';
			} else {
				$msg = 'hist-draft';
			}
			$wgOut->addHTML( "<td width='50%' align='center'>" );
			$wgOut->addHTML( "<span class='$class'><b>[" . wfMsgHtml( $msg ) . "]</b></span>" );

			$class = FlaggedRevsXML::getQualityColor( $newRevQ );
			if( $newRevQ !== false ) {
				$msg = $newRevQ ? 'hist-quality' : 'hist-stable';
			} else {
				$msg = 'hist-draft';
			}
			$wgOut->addHTML( "</td><td width='50%' align='center'>" );
			$wgOut->addHTML( "<span class='$class'><b>[" . wfMsgHtml( $msg ) . "]</b></span>" );

			$wgOut->addHTML( '</td></tr></table>' );
		# New page "diffs" - just one rev
		} else {
			if( $newRevQ !== false ) {
				$msg = $newRevQ ? 'hist-quality' : 'hist-stable';
			} else {
				$msg = 'hist-draft';
			}
			$wgOut->addHTML( "<table class='fr-diff-ratings' width='100%'><tr><td class='fr-$msg' align='center'>" );
			$wgOut->addHTML( "<b>[" . wfMsgHtml( $msg ) . "]</b>" );
			$wgOut->addHTML( '</td></tr></table>' );
		}
		return true;
	}

	/**
	* Add a link to patrol non-reviewable pages.
	* Also add a diff to stable for other pages if possible.
	*/
	public function addDiffLink( $diff, $oldRev, $newRev ) {
		global $wgUser, $wgOut;
		// Is there a stable version?
		if( $oldRev && $this->isReviewable() ) {
			$frev = $this->getStableRev();
			if( $frev && $frev->getRevId() == $oldRev->getID() && $newRev->isCurrent() ) {
				$this->isDiffFromStable = true;
			}
			# Give a link to the diff-to-stable if needed
			if( $frev && !$this->isDiffFromStable ) {
				$article = new Article( $newRev->getTitle() );
				# Is the stable revision using the same revision as the current?
				if( $article->getLatest() != $frev->getRevId() ) {
					wfLoadExtensionMessages( 'FlaggedRevs' );
					$patrol = '(' . $wgUser->getSkin()->makeKnownLinkObj( $newRev->getTitle(),
						wfMsgHtml( 'review-diff2stable' ), "oldid={$frev->getRevId()}&diff=cur&diffonly=0" ) . ')';
					$wgOut->addHTML( "<div class='fr-diff-to-stable' align='center'>$patrol</div>" );
				}
			}
		}
		return true;
	}

	/**
	* Redirect users out to review the changes to the stable version.
	* Only for people who can review and for pages that have a stable version.
	*/
	public function injectReviewDiffURLParams( &$sectionAnchor, &$extraQuery ) {
		global $wgUser, $wgReviewChangesAfterEdit;
		# Don't show this for pages that are not reviewable
		if( !$this->isReviewable() || $this->parent->getTitle()->isTalkPage() )
			return true;
		# We may want to skip some UI elements
		if( $this->limitedUI() ) return true;
		# Get the stable version, from master
		$frev = $this->getStableRev( FR_MASTER );
		if( !$frev )
			return true;
		$latest = $this->parent->getTitle()->getLatestRevID(GAID_FOR_UPDATE);
		// If we are supposed to review after edit, and it was not autoreviewed,
		// and the user can actually make new stable version, take us to the diff...
		if( $wgReviewChangesAfterEdit && $frev && $latest > $frev->getRevId() && $frev->userCanSetFlags() ) {
			$extraQuery .= $extraQuery ? '&' : '';
			$extraQuery .= "oldid={$frev->getRevId()}&diff=cur&diffonly=0"; // override diff-only
		// ...otherwise, go to the current revision after completing an edit.
		} else {
			if( $frev && $latest != $frev->getRevId() ) {
				$extraQuery .= "stable=0";
				if( !$wgUser->isAllowed('review') && $this->showStableByDefault() ) {
					$extraQuery .= "&shownotice=1";
				}
			}
		}
		return true;
	}

	/**
	* Add a hidden revision ID field to edit form.
	* Needed for autoreview so it can select the flags from said revision.
	*/
	public function addRevisionIDField( $editPage, $out ) {
		global $wgRequest;
		# Find out revision id
		if( $this->parent->mRevision ) {
	   		$revId = $this->parent->mRevision->mId;
		} else {
			$latest = $this->parent->getTitle()->getLatestRevID(GAID_FOR_UPDATE);
	   		$revId = $latest;
			wfDebug( 'FlaggedArticle::addRevisionIDField - ID not specified, assumed current' );
	   	}
		# If undoing a few consecutive top edits, we know the base ID
		if( $undo = $wgRequest->getIntOrNull('undo') ) {
			$undoAfter = $wgRequest->getIntOrNull('undoafter');
			$latest = isset($latest) ? $latest : $this->parent->getTitle()->getLatestRevID(GAID_FOR_UPDATE);
			if( $undoAfter && $undo == $this->parent->getLatest() ) {
				$revId = $undoAfter;
			}
		}
		$out->addHTML( "\n" . Xml::hidden( 'baseRevId', $revId ) );
		$out->addHTML( "\n" . Xml::hidden( 'undidRev', 
			empty($editPage->undidRev) ? 0 : $editPage->undidRev ) );
		return true;
	}

	/**
	 * Get latest quality rev, if not, the latest reviewed one
	 * @param int $flags
	 * @return Row
	 */
	public function getStableRev( $flags=0 ) {
		if( $this->stableRev === false ) {
			return null; // We already looked and found nothing...
		}
		# Cached results available?
		if( !is_null($this->stableRev) ) {
			return $this->stableRev;
		}
		# Get the content page, skip talk
		$title = $this->parent->getTitle()->getSubjectPage();
		# Do we have one?
		$srev = FlaggedRevision::newFromStable( $title, $flags );
		if( $srev ) {
			$this->stableRev = $srev;
			$this->flags[$srev->getRevId()] = $srev->getTags();
			return $srev;
		} else {
			$this->stableRev = false;
			return null;
		}
	}

	/**
	 * Get visiblity restrictions on page
	 * @param Bool $forUpdate, use DB master?
	 * @returns Array (select,override)
	*/
	public function getVisibilitySettings( $forUpdate = false ) {
		# Cached results available?
		if( !is_null($this->pageConfig) ) {
			return $this->pageConfig;
		}
		# Get the content page, skip talk
		$title = $this->parent->getTitle()->getSubjectPage();
		$config = FlaggedRevs::getPageVisibilitySettings( $title, $forUpdate );
		$this->pageConfig = $config;
		return $config;
	}

	/**
	 * @param int $revId
	 * @eturns Array, output of the flags for a given revision
	 */
	public function getFlagsForRevision( $revId ) {
		# Cached results?
		if( isset($this->flags[$revId]) ) {
			return $this->flags[$revId];
		}
		# Get the flags
		$flags = FlaggedRevs::getRevisionTags( $this->parent->getTitle(), $revId );
		# Try to cache results
		$this->flags[$revId] = $flags;

		return $flags;
	}

	 /**
	 * Adds brief review notes to a page.
	 * @param OutputPage $out
	 */
	public function addReviewNotes( &$data ) {
		if( $this->reviewNotes ) {
			$data .= $this->reviewNotes;
		}
		return true;
	}

	 /**
	 * Adds a brief review form to a page.
	 * @param string $data
	 * @param bool $top
	 * @param bool hide
	 * @param bool $top, should this form always go on top?
	 */
	public function addQuickReview( &$data, $top = false, $hide = false ) {
		global $wgOut, $wgUser, $wgRequest;
		# Revision being displayed
		$id = $wgOut->getRevisionId();
		if( !$id ) {
			if( !$this->isDiffFromStable ) {
				return false; // only safe to assume current if diff-to-stable
			}
			$rev = Revision::newFromTitle( $this->parent->getTitle() );
			$id = $rev->getId();
		} else {
			$rev = Revision::newFromTitle( $this->parent->getTitle(), $id );
		}
		
		# Load required messages
		wfLoadExtensionMessages( 'FlaggedRevs' );
		# Must be a valid non-printable output and revision must be public
		if( $wgOut->isPrintable() || !$rev || $rev->isDeleted(Revision::DELETED_TEXT) ) {
			return false;
		}
		$useCurrent = false;
		if( !isset($wgOut->mTemplateIds) || !isset($wgOut->fr_ImageSHA1Keys) ) {
			$useCurrent = true;
		}
		$skin = $wgUser->getSkin();

		$config = $this->getVisibilitySettings();
		# Variable for sites with no flags, otherwise discarded
		$approve = $wgRequest->getBool('wpApprove');
		# See if the version being displayed is flagged...
		$oldFlags = $this->getFlagsForRevision( $id );
		# If we are reviewing updates to a page, start off with the stable revision's
		# flags. Otherwise, we just fill them in with the selected revision's flags.
		if( $this->isDiffFromStable ) {
			$srev = $this->getStableRev();
			$flags = $srev->getTags();
			# Check if user is allowed to renew the stable version.
			# If not, then get the flags for the new revision itself.
			if( !RevisionReview::userCanSetFlags( $oldFlags ) ) {
				$flags = $oldFlags;
			}
		} else {
			$flags = $this->getFlagsForRevision( $id );
		}

		$reviewTitle = SpecialPage::getTitleFor( 'RevisionReview' );
		$action = $reviewTitle->getLocalUrl( 'action=submit' );
		$params = array( 'method' => 'post', 'action' => $action, 'id' => 'mw-reviewform' );
		if( $hide ) {
			$params['class'] = 'fr-hiddenform';
		}
		$form = Xml::openElement( 'form', $params );
		$form .= Xml::openElement( 'fieldset', array('class' => 'flaggedrevs_reviewform noprint') );
		$form .= "<legend><strong>" . wfMsgHtml( 'revreview-flag', $id ) . "</strong></legend>\n";

		# Show explanatory text
		if( !FlaggedRevs::lowProfileUI() ) {
			$msg = FlaggedRevs::showStableByDefault() ? 'revreview-text' : 'revreview-text2';
			$form .= wfMsgExt( $msg, array('parse') );
		}

		# Current user has too few rights to change at least one flag, thus entire form disabled
		$uneditable = !$this->parent->getTitle()->quickUserCan('edit');
		$disabled = !RevisionReview::userCanSetFlags( $flags ) || $uneditable;
		if( $disabled ) {
			$form .= Xml::openElement( 'div', array('class' => 'fr-rating-controls-disabled',
				'id' => 'fr-rating-controls-disabled') );
			$toggle = array( 'disabled' => "disabled" );
		} else {
			$form .= Xml::openElement( 'div', array('class' => 'fr-rating-controls', 
				'id' => 'fr-rating-controls') );
			$toggle = array();
		}
		$size = count(FlaggedRevs::getDimensions(),1) - count(FlaggedRevs::getDimensions());

		$form .= Xml::openElement( 'span', array('id' => 'mw-ratingselects') );
		# Loop through all different flag types
		foreach( FlaggedRevs::getDimensions() as $quality => $levels ) {
			$label = array();
			$selected = ( isset($flags[$quality]) && $flags[$quality] > 0 ) ?
				$flags[$quality] : 1;
			# Disabled form? Set the selected item label
			if( $disabled ) {
				$label[$selected] = $levels[$selected];
			# Collect all quality levels of a flag current user can set
			} else {
				foreach( $levels as $i => $name ) {
					if( !RevisionReview::userCan($quality,$i,$config) ) {
						if( $selected == $i ) $selected++; // bump default
						continue; // skip this level
					}
					$label[$i] = $name;
				}
			}
			$quantity = count( $label );
			$form .= Xml::openElement( 'span', array('class' => 'fr-rating-options') ) . "\n";
			$form .= "<b>" . Xml::tags( 'label', array( 'for' => "wp$quality" ), FlaggedRevs::getTagMsg( $quality ) ) . ":</b>\n";
			# If the sum of qualities of all flags is above 6, use drop down boxes
			# 6 is an arbitrary value choosen according to screen space and usability
			if( $size > 6 ) {
				$attribs = array( 'name' => "wp$quality", 'id' => "wp$quality", 'onchange' => "updateRatingForm()" ) + $toggle;
				$form .= Xml::openElement( 'select', $attribs );
				foreach( $label as $i => $name ) {
					$optionClass = array( 'class' => "fr-rating-option-$i" );
					$form .= Xml::option( FlaggedRevs::getTagMsg($name), $i, ($i == $selected), $optionClass )."\n";
				}
				$form .= Xml::closeElement('select')."\n";
			# If there are more than two qualities (none, 1 and more) current user gets radio buttons
			} elseif( $quantity > 2 ) {
				foreach( $label as $i => $name ) {
					$attribs = array( 'class' => "fr-rating-option-$i", 'onchange' => "updateRatingForm()" );
					$form .= Xml::radioLabel( FlaggedRevs::getTagMsg($name), "wp$quality", $i, "wp$quality".$i,
						($i == $selected), $attribs ) . "\n";
				}
			# Otherwise make checkboxes (two qualities available for current user
			# and disabled fields in case we are below the magic 6)
			} else {
				$i = $disabled ? $selected : 1;
				$attribs = array( 'class' => "fr-rating-option-$i", 'onchange' => "updateRatingForm()" ) + $toggle;
				$form .= Xml::checkLabel( wfMsg( "revreview-$label[$i]" ), "wp$quality", "wp$quality",
					($selected == $i), $attribs ) . "\n";
			}
			$form .= Xml::closeElement( 'span' );
		}
		# If there were none, make one checkbox to approve/unapprove
		if( FlaggedRevs::dimensionsEmpty() ) {
			$form .= Xml::openElement( 'span', array('class' => 'fr-rating-options') ) . "\n";
			$form .= Xml::checkLabel( wfMsg( "revreview-approved" ), "wpApprove", "wpApprove", 1 ) . "\n";
			$form .= Xml::closeElement( 'span' );
		}
		$form .= Xml::closeElement( 'span' );

		if( FlaggedRevs::allowComments() && $wgUser->isAllowed( 'validate' ) ) {
			$form .= "<div id='mw-notebox'>\n";
			$form .= "<p>".wfMsgHtml( 'revreview-notes' ) . "</p>\n";
			$form .= Xml::openElement( 'textarea', array('name' => 'wpNotes', 'id' => 'wpNotes',
				'class' => 'fr-notes-box', 'rows' => '2', 'cols' => '80') ) . Xml::closeElement('textarea') . "\n";
			$form .= "</div>\n";
		}

		$imageParams = $templateParams = $fileVersion = '';
		if( $useCurrent ) {
			global $wgUser, $wgParser, $wgEnableParserCache;
			# Get parsed current version
			$parserCache = ParserCache::singleton();
			$article = $this->parent;
			$currentOutput = $parserCache->get( $article, $wgUser );
			if( $currentOutput==false ) {
				$text = $article->getContent();
				$title = $article->getTitle();
				$options = FlaggedRevs::makeParserOptions();
				$currentOutput = $wgParser->parse( $text, $title, $options );
				# Might as well save the cache while we're at it
				if( $wgEnableParserCache )
					$parserCache->save( $currentOutput, $article, $wgUser );
			}
			$templateIDs = $currentOutput->mTemplateIds;
			$imageSHA1Keys = $currentOutput->fr_ImageSHA1Keys;
		} else {
			$templateIDs = $wgOut->mTemplateIds;
			$imageSHA1Keys = $wgOut->fr_ImageSHA1Keys;
		}
		list($templateParams,$imageParams,$fileVersion) = 
			FlaggedRevs::getIncludeParams( $this->parent, $templateIDs, $imageSHA1Keys );

		$form .= Xml::openElement( 'span', array('style' => 'white-space: nowrap;') );
		# Hide comment if needed
		if( !$disabled ) {
			$form .= "<span id='mw-commentbox' style='clear:both'>" . 
				Xml::inputLabel( wfMsg('revreview-log'), 'wpReason', 'wpReason', 40, '', 
					array('class' => 'fr-comment-box') ) . "&nbsp;&nbsp;&nbsp;</span>";
		}
		$form .= Xml::submitButton( wfMsg('revreview-submit'), array('id' => 'submitreview',
			'accesskey' => wfMsg('revreview-ak-review'), 
			'title' => wfMsg('revreview-tt-review').' ['.wfMsg('revreview-ak-review').']') + $toggle
		);
		$form .= Xml::closeElement( 'span' );

		$form .= Xml::closeElement( 'div' ) . "\n";
		
		# Show stability log if there is anything interesting...
		if( $this->isPageLocked() ) {
			$loglist = new LogEventsList( $wgUser->getSkin(), $wgOut, LogEventsList::NO_ACTION_LINK );
			$pager = new LogPager( $loglist, 'stable', '', $this->parent->getTitle()->getPrefixedDBKey() );
			$pager->mLimit = 1; // top item
			if( ($logBody = $pager->getBody()) ) {
				$form .= "<div><ul style='list-style:none; margin: 0;'>$logBody</ul></div>";
			}
		}

		# Hidden params
		$form .= Xml::hidden( 'title', $reviewTitle->getPrefixedText() ) . "\n";
		$form .= Xml::hidden( 'target', $this->parent->getTitle()->getPrefixedDBKey() ) . "\n";
		$form .= Xml::hidden( 'oldid', $id ) . "\n";
		$form .= Xml::hidden( 'action', 'submit') . "\n";
		$form .= Xml::hidden( 'wpEditToken', $wgUser->editToken() ) . "\n";
		# Add review parameters
		$form .= Xml::hidden( 'templateParams', $templateParams ) . "\n";
		$form .= Xml::hidden( 'imageParams', $imageParams ) . "\n";
		$form .= Xml::hidden( 'fileVersion', $fileVersion ) . "\n";
		# Pass this in if given; useful for new page patrol
		$form .= Xml::hidden( 'rcid', $wgRequest->getVal('rcid') ) . "\n";
		# Special token to discourage fiddling...
		$checkCode = RevisionReview::validationKey( $templateParams, $imageParams, $fileVersion, $id );
		$form .= Xml::hidden( 'validatedParams', $checkCode ) . "\n";

		$form .= Xml::closeElement( 'fieldset' );
		$form .= Xml::closeElement( 'form' );

		if( $top ) {
			$wgOut->prependHTML( $form );
		} else {
			$data .= $form;
		}
		return true;
	}

	/**
	* Updates parser cache output to included needed versioning params.
	*/
	public function maybeUpdateMainCache( &$outputDone, &$pcache ) {
		global $wgUser, $wgRequest;

		$action = $wgRequest->getVal( 'action', 'view' );
		if( $action == 'purge' ) return true; // already purging!
		# Only trigger on article view for content pages, not for protect/delete/hist
		if( !self::isViewAction($action) || !$wgUser->isAllowed( 'review' ) )
			return true;
		if( !$this->parent->exists() || !$this->isReviewable() )
			return true;

		$parserCache = ParserCache::singleton();
		$parserOut = $parserCache->get( $this->parent, $wgUser );
		if( $parserOut ) {
			# Clear older, incomplete, cached versions
			# We need the IDs of templates and timestamps of images used
			if( !isset($parserOut->fr_newestTemplateID) || !isset($parserOut->fr_newestImageTime) )
				$this->parent->getTitle()->invalidateCache();
		}
		return true;
	}
}
