<?php

class FlaggedArticle {
	public $isDiffFromStable = false;
	public $skipReviewDiff = false;
	public $skipAutoReview = false;
	
	public $stablerev = null;
	public $pageconfig = null;
	public $flags = null;
	
	protected $dbw = null;
	/**
	 * Does the config and current URL params allow 
	 * for overriding by stable revisions?
	 */		
    public function pageOverride() {
    	global $wgUser, $wgRequest;
    	# This only applies to viewing content pages
		$action = $wgRequest->getVal( 'action', 'view' );
    	if( ($action !='view' && $action !='purge') || !$this->isReviewable() )
			return false;
    	# Does not apply to diffs/old revisions
    	if( $wgRequest->getVal('oldid') || $wgRequest->getVal('diff') )
			return false;
		# Get page configuration
		$config = $this->getVisibilitySettings();
    	# Does the stable version override the current one?
    	if( $config['override'] ) {
    		global $wgFlaggedRevsExceptions;
    		# Viewer sees current by default (editors, insiders, ect...) ?
    		foreach( $wgFlaggedRevsExceptions as $group ) {
    			if( $group == 'user' ) {
    				if( !$wgUser->isAnon() )
    					return ( $wgRequest->getIntOrNull('stable') === 1 );
    			} else if( in_array( $group, $wgUser->getGroups() ) ) {
    				return ( $wgRequest->getIntOrNull('stable') === 1 );
    			}
    		}
			# Viewer sees stable by default
    		return !( $wgRequest->getIntOrNull('stable') === 0 );
    	# We are explicity requesting the stable version?
		} else if( $wgRequest->getIntOrNull('stable') === 1 ) {
			return true;
		}
		return false;
	}
	
	 /**
	 * Is this user shown the stable version by default for this page?
	 */	
	public function showStableByDefault() {
		global $wgFlaggedRevsOverride, $wgFlaggedRevsExceptions, $wgUser;
		# Get page configuration
		$config = $this->getVisibilitySettings();
		if( !$config['override'] )
			return false;
    	# Viewer sees current by default (editors, insiders, ect...) ?
    	foreach( $wgFlaggedRevsExceptions as $group ) {
    		if( $group == 'user' ) {
    			if( !$wgUser->isAnon() )
    				return false;
    		} else if( in_array( $group, $wgUser->getGroups() ) ) {
    			return false;
    		}
    	}
		return true;
	}
	
	 /**
	 * Is this article reviewable?
	 */	
	public function isReviewable() {
		global $wgTitle;
		
		return FlaggedRevs::isPageReviewable( $wgTitle );
	}

	 /**
	 * Replaces a page with the last stable version if possible
	 * Adds stable version status/info tags and notes
	 * Adds a quick review form on the bottom if needed
	 */
	function setPageContent( $article, &$outputDone, &$pcache ) {
		global $wgRequest, $wgTitle, $wgOut, $wgUser;
		
		$skin = $wgUser->getSkin();
		$action = $wgRequest->getVal( 'action', 'view' );
		# For unreviewable pages, allow for basic patrolling
		if( !FlaggedRevs::isPageReviewable( $article->getTitle() ) ) {
			# If we have been passed an &rcid= parameter, we want to give the user a
			# chance to mark this new article as patrolled.
			$rcid = $wgRequest->getIntOrNull( 'rcid' );
			if( !is_null( $rcid ) && $rcid != 0 && $wgUser->isAllowed( 'patrolother' ) ) {
				$reviewtitle = SpecialPage::getTitleFor( 'Revisionreview' );
				$wgOut->addHTML( "<div class='patrollink'>" .
					wfMsgHtml( 'markaspatrolledlink',
					$skin->makeKnownLinkObj( $reviewtitle, wfMsgHtml('markaspatrolledtext'),
						"patrolonly=1&rcid=$rcid" )
			 		) .
					'</div>'
			 	);
			}
			return true;
		}
		# Only trigger on article view for content pages, not for protect/delete/hist
		if( ($action !='view' && $action !='purge') || !$article || !$article->exists() ) 
			return true;
		# Grab page and rev ids
		$pageid = $article->getId();
		$revid = $article->mRevision ? $article->mRevision->mId : $article->getLatest();
		if( !$revid ) 
			return true;
		
		$vis_id = $revid;
		$tag = $notes = '';
		# Check the newest stable version...
		$tfrev = $this->getStableRev( true );
		$simpleTag = false;
		if( $wgRequest->getVal('diff') || $wgRequest->getVal('oldid') ) {
    		// Do not clutter up diffs any further...
		} else if( !is_null($tfrev) ) {
			global $wgLang;
			# Get flags and date
			$flags = FlaggedRevs::expandRevisionTags( $tfrev->fr_tags );
			# Get quality level
			$quality = FlaggedRevs::isQuality( $flags );
			$pristine =  FlaggedRevs::isPristine( $flags );
			$time = $wgLang->date( wfTimestamp(TS_MW, $tfrev->fr_timestamp), true );
			# Looking at some specific old rev or if flagged revs override only for anons
			if( !$this->pageOverride() ) {
				$revs_since = FlaggedRevs::getRevCountSince( $pageid, $tfrev->fr_rev_id );
				$simpleTag = true;
				# Construct some tagging
				if( !$wgOut->isPrintable() ) {
					if( FlaggedRevs::useSimpleUI() ) {
						$msg = $quality ? 'revreview-quick-see-quality' : 'revreview-quick-see-basic';
						$tag .= "<span class='fr_tab_current plainlinks'></span>" . 
								wfMsgExt($msg,array('parseinline'), $tfrev->fr_rev_id, $revs_since);
						$tag .= $this->prettyRatingBox( $tfrev, $flags, $revs_since, false );							
					} else {
						$msg = $quality ? 'revreview-newest-quality' : 'revreview-newest-basic';
						$tag .= wfMsgExt($msg, array('parseinline'), $tfrev->fr_rev_id, $time, $revs_since);
						# Hide clutter
						if( !empty($flags) ) {
							$tag .= ' <span id="mw-revisiontoggle" class="flaggedrevs_toggle" style="display:none; cursor:pointer;"' . 
								' onclick="javascript:toggleRevRatings()">'.wfMsg('revreview-toggle').'</span>';
							$tag .= '<span id="mw-revisionratings" style="display:block;">' . 
								wfMsg('revreview-oldrating') . $this->addTagRatings( $flags ) . '</span>';
						}
					}
				}
			// Viewing the page normally: override the page
			} else {
       			# We will be looking at the reviewed revision...
       			$vis_id = $tfrev->fr_rev_id;
       			$revs_since = FlaggedRevs::getRevCountSince( $pageid, $vis_id );
				# Construct some tagging
				if( !$wgOut->isPrintable() ) {
					if( FlaggedRevs::useSimpleUI() ) {
						$msg = $quality ? 'revreview-quick-quality' : 'revreview-quick-basic';
						$css = $quality ? 'fr_tab_quality' : 'fr_tab_stable';
						$tag .= "<span class='$css plainlinks'></span>" . 
							wfMsgExt($msg,array('parseinline'),$tfrev->fr_rev_id,$revs_since);
					 	$tag .= $this->prettyRatingBox( $tfrev, $flags, $revs_since );
					} else {
						$msg = $quality ? 'revreview-quality' : 'revreview-basic';
						$tag = wfMsgExt($msg, array('parseinline'), $vis_id, $time, $revs_since);
						if( !empty($flags) ) {
							$tag .= ' <span id="mw-revisiontoggle" class="flaggedrevs_toggle" style="display:none; cursor:pointer;"' .
								' onclick="javascript:toggleRevRatings()">'.wfMsg('revreview-toggle').'</span>';
							$tag .= '<span id="mw-revisionratings" style="display:block;">' . 
								$this->addTagRatings( $flags ) . '</span>';
						}
					}
				}
				# Try the stable page cache
				$parserOut = FlaggedRevs::getPageCache( $article );
				# If no cache is available, get the text and parse it
				if( $parserOut==false ) {
					global $wgUseStableTemplates;
					if( $wgUseStableTemplates ) {
						$rev = Revision::newFromId( $tfrev->fr_rev_id );
						$text = $rev->getText();
					} else {
						$text = FlaggedRevs::uncompressText( $tfrev->fr_text, $tfrev->fr_flags );
					}
       				$parserOut = FlaggedRevs::parseStableText( $article, $text, $vis_id );
       				# Update the general cache
       				FlaggedRevs::updatePageCache( $article, $parserOut );
       			}
				# Output HTML
       			$wgOut->addParserOutput( $parserOut );
				$notes = $this->ReviewNotes( $tfrev );
				# Tell MW that parser output is done
				$outputDone = true;
				$pcache = false;
			}
			# Some checks for which tag CSS to use
			if( FlaggedRevs::useSimpleUI() )
				$tagClass = 'flaggedrevs_short';
			else if( $simpleTag )
				$tagClass = 'flaggedrevs_notice';
			else if( $pristine )
				$tagClass = 'flaggedrevs_tag3';
			else if( $quality )
				$tagClass = 'flaggedrevs_tag2';
			else
				$tagClass = 'flaggedrevs_tag1';
			# Wrap tag contents in a div
			if( $tag !='' )
				$tag = '<div id="mw-revisiontag" class="'.$tagClass.' plainlinks">'.$tag.'</div>';
			# Set the new body HTML, place a tag on top
			$wgOut->mBodytext = $tag . $wgOut->mBodytext . $notes;
		// Add "no reviewed version" tag, but not for main page
		} else if( !$wgOut->isPrintable() && !FlaggedRevs::isMainPage( $article->getTitle() ) ) {
			if( FlaggedRevs::useSimpleUI() ) {
				$tag .= "<span class='fr_tab_current plainlinks'></span>" . 
					wfMsgExt('revreview-quick-none',array('parseinline'));
				$tag = '<div id="mw-revisiontag" class="flaggedrevs_short plainlinks">'.$tag.'</div>';
			} else {
				$tag = '<div id="mw-revisiontag" class="flaggedrevs_notice plainlinks">' .
					wfMsgExt('revreview-noflagged', array('parseinline')) . '</div>';
			}
			$wgOut->addHTML( $tag );
		}
		
		return true;
    }
    
    /**
	 * Adds latest stable version tag to page when editing
	 */
    function addToEditView( $editform ) {
		global $wgRequest, $wgTitle, $wgOut;
		# Talk pages cannot be validated
		if( !$editform->mArticle || !$this->isReviewable() )
			return false;
		# Find out revision id
		if( $editform->mArticle->mRevision ) {
       		$revid = $editform->mArticle->mRevision->mId;
		} else {
       		$revid = $editform->mArticle->getLatest();
       	}
		# Grab the ratings for this revision if any
		if( !$revid ) 
			return true;
		# Set new body html text as that of now
		$tag = '';
		# Check the newest stable version
		$tfrev = $this->getStableRev();
		if( is_object($tfrev) ) {
			global $wgLang, $wgUser, $wgFlaggedRevsAutoReview;
			
			$time = $wgLang->date( wfTimestamp(TS_MW, $tfrev->fr_timestamp), true );
			$flags = FlaggedRevs::expandRevisionTags( $tfrev->fr_tags );
			$revs_since = FlaggedRevs::getRevCountSince( $editform->mArticle->getID(), $tfrev->fr_rev_id );
			# Construct some tagging
			$msg = FlaggedRevs::isQuality( $flags ) ? 'revreview-newest-quality' : 'revreview-newest-basic';
			$tag = wfMsgExt($msg, array('parseinline'), $tfrev->fr_rev_id, $time, $revs_since );
			# Hide clutter
			if( !empty($flags) ) {
				$tag .= ' <span id="mw-revisiontoggle" class="flaggedrevs_toggle" style="display:none; cursor:pointer;"' .
					' onclick="javascript:toggleRevRatings()">'.wfMsg('revreview-toggle').'</span>';
				$tag .= '<span id="mw-revisionratings" style="display:block;">' . 
					wfMsg('revreview-oldrating') . $this->addTagRatings( $flags ) . 
					'</span>';
			}
			$wgOut->addHTML( '<div id="mw-revisiontag" class="flaggedrevs_notice plainlinks">' . $tag . '</div>' );
			# If this will be autoreviewed, notify the user...
			if( !$wgFlaggedRevsAutoReview )
				return true;
			if( $wgUser->isAllowed('review') && $tfrev->fr_rev_id==$editform->mArticle->getLatest() ) {
				# Check if user is allowed to renew the stable version.
				# If it has been reviewed too highly for this user, abort.
				foreach( $flags as $quality => $level ) {
					if( !Revisionreview::userCan($quality,$level) ) {
						return true;
					}
				}
				$msg = ($revid==$tfrev->fr_rev_id) ? 'revreview-auto-w' : 'revreview-auto-w-old';
				$wgOut->addHTML( '<div id="mw-autoreviewtag" class="flaggedrevs_warning plainlinks">' . 
					wfMsgExt($msg,array('parseinline')) . '</div>' );
			}
		}
		return true;
    }

	 /**
	 * Add review form to page when necessary
	 */	
    function addReviewForm( $out ) {
    	global $wgArticle, $wgRequest;

		if( !$wgArticle || !$wgArticle->exists() || !$this->isReviewable() ) 
			return true;
		# Check if page is protected
		$action = $wgRequest->getVal( 'action', 'view' );
		if( ($action !='view' && $action !='purge') || !$wgArticle->getTitle()->quickUserCan( 'edit' ) ) {
			return true;
		}
		# Get revision ID
		$revId = $out->mRevisionId ? $out->mRevisionId : $wgArticle->getLatest();
		# We cannot review deleted revisions
		if( is_object($wgArticle->mRevision) && $wgArticle->mRevision->mDeleted ) 
			return true;
    	# Add quick review links IF we did not override, otherwise, they might
		# review a revision that parses out newer templates/images than what they saw.
		# Revisions are always reviewed based on current templates/images.
		if( $this->pageOverride() ) {
			$tfrev = $this->getStableRev();
			if( $tfrev ) 
				return true;
		}
		$this->addQuickReview( $revId, $out, $wgRequest->getBool('editreview') );
		
		return true;
    }
  
	 /**
	 * Add link to stable version setting to protection form
	 */
    function addVisibilityLink( $out ) {
    	global $wgUser, $wgRequest, $wgTitle;
    	
    	if( !$this->isReviewable() )
    		return true;
		
    	$action = $wgRequest->getVal( 'action', 'view' );
    	if( $action=='protect' || $action=='unprotect' ) {
			# Check for an overridabe revision
			$tfrev = $this->getStableRev();
			if( !$tfrev )
				return true;
			$title = SpecialPage::getTitleFor( 'Stabilization' );
			# Give a link to the page to configure the stable version
			$out->mBodytext = '<span class="plainlinks">' .
				wfMsgExt( 'revreview-visibility',array('parseinline'), $title->getPrefixedText() ) . 
				'</span>' . $out->mBodytext;
		}
		return true;
    }

	 /**
	 * Set permalink to stable version if we are viewing a stable version.
	 * Also sets the citation link if that extension is on.
	 */  
    function setPermaLink( $sktmp, &$nav_urls, &$revid, &$revid ) {
		# Non-content pages cannot be validated
		if( !$this->pageOverride() ) 
			return true;
		# Check for an overridabe revision
		$tfrev = $this->getStableRev();
		if( !$tfrev ) 
			return true;
		# Replace "permalink" with an actual permanent link
		$nav_urls['permalink'] = array(
			'text' => wfMsg( 'permalink' ),
			'href' => $sktmp->makeSpecialUrl( 'Stableversions', "page=" . wfUrlencode( "{$sktmp->thispage}" ) . "&oldid={$tfrev->fr_rev_id}" )
		);
		# Are we using the popular cite extension?
		global $wgHooks;
		if( in_array('wfSpecialCiteNav',$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink']) ) {
			if( FlaggedRevs::isPageReviewable( $sktmp->mTitle ) && $revid !== 0 ) {
				$nav_urls['cite'] = array(
					'text' => wfMsg( 'cite_article_link' ),
					'href' => $sktmp->makeSpecialUrl( 'Cite', "page=" . wfUrlencode( "{$sktmp->thispage}" ) . "&id={$tfrev->fr_rev_id}" )
				);
			}
		}
		return true;
    }

	 /**
	 * Add stable version tabs. Rename some of the others if necessary.
	 */  
    function setActionTabs( $sktmp, &$content_actions ) {
    	global $wgRequest, $wgUser, $wgFlaggedRevsOverride, $wgFlaggedRevTabs;
		# Get the subject page, not all skins have it :(
		if( !isset($sktmp->mTitle) )
			return true;
		$title = $sktmp->mTitle->getSubjectPage();
		# Non-content pages cannot be validated
		if( !FlaggedRevs::isPageReviewable( $title ) || !$title->exists() )
			return true;
		$article = new Article( $title );
		$action = $wgRequest->getVal( 'action', 'view' );
		# If we are viewing a page normally, and it was overridden,
		# change the edit tab to a "current revision" tab
       	$tfrev = $this->getStableRev();
       	# No quality revs? Find the last reviewed one
       	if( !is_object($tfrev) ) {
			return true;
		}
       	// Be clear about what is being edited...
       	if( !$sktmp->mTitle->isTalkPage() && $this->showStableByDefault() ) {
       		if( isset( $content_actions['edit'] ) )
       			$content_actions['edit']['text'] = wfMsg('revreview-edit');
       		if( isset( $content_actions['viewsource'] ) )
       			$content_actions['viewsource']['text'] = wfMsg('revreview-source');
       	}
		// We can change the behavoir of stable version for this page to be different
		// than the site default.
		$stabTitle = SpecialPage::getTitleFor( 'Stabilization' );
       	$content_actions['default'] = array(
			'class' => false,
			'text' => wfmsg('stabilization-tab'),
			'href' => $stabTitle->getLocalUrl('page='.$title->getPrefixedUrl())
		);
		// Add auxillary tabs...
     	if( !$wgFlaggedRevTabs )
       		return true;
       	// We are looking at the stable version
       	if( $this->pageOverride() ) {
			$new_actions = array(); $counter = 0;
			# Straighten out order, set the tab AFTER the main tab is set
			foreach( $content_actions as $tab_action => $data ) {
				if( $counter==1 ) {
					if( $this->showStableByDefault() ) {
						$new_actions['current'] = array(
							'class' => '',
							'text' => wfMsg('revreview-current'),
							'href' => $title->getLocalUrl( 'stable=0' )
						);
					} else {
						$new_actions['stable'] = array(
							'class' => 'selected',
							'text' => wfMsg('revreview-stable'),
							'href' => $title->getLocalUrl( 'stable=1' )
						);
					}
				}
       			$new_actions[$tab_action] = $data;
       			$counter++;
       		}
       		# Reset static array
       		$content_actions = $new_actions;
    	} else if( ($action !='view' && $action !='purge') || $sktmp->mTitle->isTalkPage() ) {
    	// We are looking at the talk page or diffs/hist/oldids, or in edit mode
			$new_actions = array(); $counter = 0;
			# Straighten out order, set the tab AFTER the main tab is set
			foreach( $content_actions as $tab_action => $data ) {
				if( $counter==1 ) {
					if( $this->showStableByDefault() ) {
						$new_actions['current'] = array(
							'class' => '',
							'text' => wfMsg('revreview-current'),
							'href' => $title->getLocalUrl( 'stable=0' )
						);
					} else {
						$new_actions['stable'] = array(
							'class' => '',
							'text' => wfMsg('revreview-stable'),
							'href' => $title->getLocalUrl( 'stable=1' )
						);
					}
				}
       			$new_actions[$tab_action] = $data;
       			$counter++;
       		}
       		# Reset static array
       		$content_actions = $new_actions;
    	} else if( $wgFlaggedRevTabs ) {
		// We are looking at the current revision
			$new_actions = array(); $counter = 0;
			# Straighten out order, set the tab AFTER the main tab is set
			foreach( $content_actions as $tab_action => $data ) {
				if( $counter==1 ) {
					if( $this->showStableByDefault() ) {
						$new_actions['current'] = array(
							'class' => 'selected',
							'text' => wfMsg('revreview-current'),
							'href' => $title->getLocalUrl( 'stable=0' )
						);
					} else {
						$new_actions['stable'] = array(
							'class' => '',
							'text' => wfMsg('revreview-stable'),
							'href' => $title->getLocalUrl( 'stable=1' )
						);
				 	}
				}
       			$new_actions[$tab_action] = $data;
       			$counter++;
       		}
       		# Reset static array
       		$content_actions = $new_actions;
    	}
    	return true;
    }
    
	 /**
	 * Add link to stable version of reviewed revisions
	 */
    function addToHistLine( $row, &$s ) {
    	global $wgUser, $wgTitle;
		# Non-content pages cannot be validated
		if( !$this->isReviewable() ) 
			return true;
		
		if( !$this->dbw ) {
    		$this->dbw = wfGetDB( DB_MASTER );
    	}
    	
    	$quality = $this->dbw->selectField( 'flaggedrevs', 'fr_quality',
    		array( 'fr_page_id' => $wgTitle->getArticleID(),
				'fr_rev_id' => $row->rev_id ),
			__METHOD__,
			array( 'FORCE INDEX' => 'PRIMARY' ) );
    	
    	if( $quality !== false ) {
    		$skin = $wgUser->getSkin();
    		
    		$msg = ($quality >= 1) ? 'hist-quality' : 'hist-stable';
    		$special = SpecialPage::getTitleFor( 'Stableversions' );
    		$s .= ' <tt><small><strong>' . 
				$skin->makeLinkObj( $special, wfMsgHtml( $msg ),
					'page=' . urlencode( $wgTitle->getPrefixedText() ) .
					'&oldid=' . $row->rev_id ) . 
				'</strong></small></tt>';
		}
		
		return true;
    }

	 /**
	 * Adds a brief review form to a page.
	 * @param Integer $id, the revision ID
	 * @param OutputPage $out
	 * @param bool $top, should this form always go on top?
	 */
    function addQuickReview( $id, $out, $top=false ) {
		global $wgOut, $wgTitle, $wgUser, $wgRequest, $wgFlaggedRevComments, 
			$wgFlaggedRevsOverride, $wgFlaggedRevsWatch;
		# User must have review rights
		if( !$wgUser->isAllowed( 'review' ) ) 
			return;
		# Looks ugly when printed
		if( $out->isPrintable() ) 
			return;
		
		$skin = $wgUser->getSkin();
		# If we are reviewing updates to a page, start off with the stable revision's
		# flags. Otherwise, we just fill them in with the selected revision's flags.
		if( $this->isDiffFromStable ) {
			$flags = $this->getFlagsForRevision( $wgRequest->getVal('oldid') );
			# Check if user is allowed to renew the stable version.
			# It may perhaps have been reviewed too highly for this user, if so,
			# then get the flags for the new revision itself.
			foreach( $flags as $quality => $level ) {
				if( !Revisionreview::userCan($quality,$level) ) {
					$flags = $this->getFlagsForRevision( $id );
					break;
				}
			}
		} else {
			$flags = $this->getFlagsForRevision( $id );
		}
       
		$reviewtitle = SpecialPage::getTitleFor( 'Revisionreview' );
		$action = $reviewtitle->getLocalUrl( 'action=submit' );
		$form = Xml::openElement( 'form', array( 'method' => 'post', 'action' => $action ) );
		$form .= "<fieldset><legend>" . wfMsgHtml( 'revreview-flag', $id ) . "</legend>\n";
		
		if( $wgFlaggedRevsOverride )
			$form .= '<p>'.wfMsgExt( 'revreview-text', array('parseinline') ).'</p>';
		
		$form .= Xml::hidden( 'title', $reviewtitle->getPrefixedText() );
		$form .= Xml::hidden( 'target', $wgTitle->getPrefixedText() );
		$form .= Xml::hidden( 'oldid', $id );
		$form .= Xml::hidden( 'action', 'submit');
        $form .= Xml::hidden( 'wpEditToken', $wgUser->editToken() );
        
		foreach( FlaggedRevs::$dimensions as $quality => $levels ) {
			$options = array();
			$disabled = false;
			foreach( $levels as $idx => $label ) {
				$selected = ( $flags[$quality]==$idx || $flags[$quality]==0 && $idx==1 );
				# Do not show options user's can't set unless that is the status quo
				if( !Revisionreview::userCan($quality, $flags[$quality]) ) {
					$disabled = true;
					$options[] = Xml::option( wfMsg( "revreview-$label" ), $idx, $selected );
				} else if( Revisionreview::userCan($quality, $idx) ) {
					$options[] = Xml::option( wfMsg( "revreview-$label" ), $idx, $selected );
				}
			}
			$form .= "\n" . wfMsgHtml("revreview-$quality") . ": ";
			$selectAttribs = array( 'name' => "wp$quality" );
			if( $disabled ) 
				$selectAttribs['disabled'] = 'disabled';
			$form .= Xml::openElement( 'select', $selectAttribs );
			$form .= implode( "\n", $options );
			$form .= Xml::closeElement('select')."\n";
		}
        if( $wgFlaggedRevComments && $wgUser->isAllowed( 'validate' ) ) {
			$form .= "<br/><p>" . wfMsgHtml( 'revreview-notes' ) . "</p>" .
			"<p><textarea tabindex='1' name='wpNotes' id='wpNotes' rows='2' cols='80' style='width:100%'></textarea>" .	
			"</p>\n";
		}
		
		$imageParams = $templateParams = '';
        if( !isset($out->mTemplateIds) || !isset($out->fr_ImageSHA1Keys) ) {
        	return; // something went terribly wrong...
        }
        # Hack, add NS:title -> rev ID mapping
        foreach( $out->mTemplateIds as $namespace => $title ) {
        	foreach( $title as $dbkey => $id ) {
        		$title = Title::makeTitle( $namespace, $dbkey );
        		$templateParams .= $title->getPrefixedText() . "|" . $id . "#";
        	}
        }
        $form .= Xml::hidden( 'templateParams', $templateParams ) . "\n";
        # Hack, image -> timestamp mapping
        foreach( $out->fr_ImageSHA1Keys as $dbkey => $timeAndSHA1 ) {
        	foreach( $timeAndSHA1 as $time => $sha1 ) {
        		$imageParams .= $dbkey . "|" . $time . "|" . $sha1 . "#";
        	}
        }
		$form .= Xml::hidden( 'imageParams', $imageParams ) . "\n";
        
        $watchLabel = wfMsgExt('watchthis', array('parseinline'));
        $watchAttribs = array('accesskey' => wfMsg( 'accesskey-watch' ), 'id' => 'wpWatchthis');
        $watchChecked = ( $wgFlaggedRevsWatch && $wgUser->getOption( 'watchdefault' ) || $wgTitle->userIsWatching() );
       	# Not much to say unless you are a validator
		if( $wgUser->isAllowed( 'validate' ) )
        	$form .= "<p>".Xml::inputLabel( wfMsg( 'revreview-log' ), 'wpReason', 'wpReason', 60 )."</p>\n";
        
		$form .= "<p>&nbsp;&nbsp;&nbsp;".Xml::check( 'wpWatchthis', $watchChecked, $watchAttribs );
		$form .= "&nbsp;<label for='wpWatchthis'".$skin->tooltipAndAccesskey('watch').">{$watchLabel}</label>";
        
		$form .= '&nbsp;&nbsp;&nbsp;'.Xml::submitButton( wfMsg( 'revreview-submit' ) )."</p></fieldset>";
		$form .= Xml::closeElement( 'form' );
		
		if( $top )
			$out->mBodytext =  $form . $out->mBodytext;
		else
			$wgOut->addHTML( '<hr style="clear:both"/>' . $form );
    }
    
	/**
	 * @param array $flags
	 * @param bool $prettybox
	 * @param string $css, class to wrap box in
	 * @return string
	 * Generates a review box/tag
	 */	
    public function addTagRatings( $flags, $prettyBox = false, $css='' ) {
        global $wgFlaggedRevTags;
        
        $tag = '';
        if( $prettyBox )
        	$tag .= "<table align='center' class='$css' cellpading='0'>";
		foreach( FlaggedRevs::$dimensions as $quality => $value ) {
			$level = isset( $flags[$quality] ) ? $flags[$quality] : 0;
			$encValueText = wfMsgHtml('revreview-' . FlaggedRevs::$dimensions[$quality][$level]);
            $level = $flags[$quality];
            $minlevel = $wgFlaggedRevTags[$quality];
            if( $level >= $minlevel )
                $classmarker = 2;
            elseif( $level > 0 )
                $classmarker = 1;
            else
                $classmarker = 0;

            $levelmarker = $level * 20 + 20;
            if( $prettyBox ) {
            	$tag .= "<tr><td><span class='fr-group'><span class='fr-text'>" . 
					wfMsgHtml("revreview-$quality") . 
					"</span></td><td><span class='fr-marker fr_value$levelmarker'>" .
					$encValueText . "</span></span></td></tr>\n";
            } else {
				$tag .= "&nbsp;<span class='fr-marker-$levelmarker'><strong>" . 
					wfMsgHtml("revreview-$quality") . 
					"</strong>: <span class='fr-text-value'>$encValueText&nbsp;</span>&nbsp;" .
					"</span>\n";    
			}
		}
		if( $prettyBox )
			$tag .= '</table>';
		 
		return $tag;
    }
    
	/**
	 * @param Row $trev, flagged revision row
	 * @param array $flags
	 * @param int $revs_since, revisions since review
	 * @param bool $stable, are we referring to the stable revision?
	 * @return string
	 * Generates a review box using a table using addTagRatings()
	 */	
	public function prettyRatingBox( $tfrev, $flags, $revs_since, $stable=true ) {
		global $wgLang;
		# Get quality level
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		$time = $wgLang->date( wfTimestamp(TS_MW, $tfrev->fr_timestamp), true );
		# Some checks for which tag CSS to use
		if( $pristine )
			$tagClass = 'flaggedrevs_box3';
		else if( $quality )
			$tagClass = 'flaggedrevs_box2';
		else
			$tagClass = 'flaggedrevs_box1';
        # Construct some tagging
        $msg = $stable ? 'revreview-' : 'revreview-newest-';
        $msg .= $quality ? 'quality' : 'basic';
        
		$box = ' <span id="mw-revisiontoggle" class="flaggedrevs_toggle" style="display:none; cursor:pointer;" 
			onclick="javascript:toggleRevRatings()">'.wfMsg('revreview-toggle').'</span>';
		$box .= '<span id="mw-revisionratings">' .
			wfMsgExt($msg, array('parseinline'), $tfrev->fr_rev_id, $time, $revs_since);
		if( !empty($flags) ) {
			$encRatingLabel = $stable ? '' : ' ' . wfMsgHtml('revreview-oldrating');
			$box .= $encRatingLabel . $this->addTagRatings( $flags, true, "{$tagClass}a" );
		}
		$box .= '</span>';
        return $box;
	}
	
	/**
	 * @param Row $row
	 * @return string, revision review notes
	 */	    
    public function ReviewNotes( $row ) {
    	global $wgUser, $wgFlaggedRevComments;
    	
    	if( !$wgFlaggedRevComments || !$row || $row->fr_comment == '' ) 
			return '';
    	
   		$skin = $wgUser->getSkin();
   		$notes = '<p><div class="flaggedrevs_notes plainlinks">';
   		$notes .= wfMsgExt('revreview-note', array('parse'), User::whoIs( $row->fr_user ) );
   		$notes .= '<i>' . $skin->formatComment( $row->fr_comment ) . '</i></div></p><br/>';
    	return $notes;
    }
    
	/**
	* When comparing the stable revision to the current after editing a page, show
	* a tag with some explaination for the diff.
	*/ 
	public function addDiffNoticeAfterEdit( $diff, $OldRev, $NewRev ) {
		global $wgRequest, $wgUser, $wgOut;
		
		if( !$wgUser->isAllowed('review') || !$wgRequest->getBool('editreview') || !$NewRev->isCurrent() )
			return true;
		
		$frev = $this->getStableRev();
		if( !$frev || $frev->fr_rev_id != $OldRev->getID() )
			return true;
			
		$changeList = array();
		$skin = $wgUser->getSkin();
		# Make a list of each changed template...
		$dbr = wfGetDB( DB_SLAVE );
		$ret = $dbr->select( array('flaggedtemplates','page'),
			array( 'ft_namespace', 'ft_title', 'ft_tmp_rev_id' ),
			array( 'ft_rev_id' => $frev->fr_rev_id,
				'ft_namespace = page_namespace',
				'ft_title = page_title',
				'ft_tmp_rev_id != page_latest' ),
			__METHOD__ );
			
		while( $row = $dbr->fetchObject( $ret ) ) {
			$title = Title::makeTitle( $row->ft_namespace, $row->ft_title );
			$changeList[] = $skin->makeKnownLinkObj( $title, 
				$title->GetPrefixedText(),
				"diff=cur&oldid=" . $row->ft_tmp_rev_id );
		}
		# And images...
		$ret = $dbr->select( array('flaggedimages','image'),
			array( 'fi_name' ),
			array( 'fi_rev_id' => $frev->fr_rev_id,
				'fi_name = img_name',
				'fi_img_sha1 != img_sha1' ),
			__METHOD__ );
			
		while( $row = $dbr->fetchObject( $ret ) ) {
			$title = Title::makeTitle( NS_IMAGE, $row->fi_name );
			$changeList[] = $skin->makeKnownLinkObj( $title );
		}
		
		if( empty($changeList) ) {
			$wgOut->addHTML( '<div id="mw-difftostable" class="flaggedrevs_notice plainlinks">' .
				wfMsg('revreview-update-none').'</div>' );
		} else {
			$changeList = implode(', ',$changeList);
			$wgOut->addHTML( '<div id="mw-difftostable" class="flaggedrevs_notice plainlinks"><p>' .
				wfMsg('revreview-update').'</p>'.$changeList.'</div>' );
		}
		# Set flag for review form to tell it to autoselect tag settings from the
		# old revision unless the current one is tagged to.
		if( !FlaggedRevs::getFlaggedRev( $diff->mTitle, $NewRev->getID() ) ) {
			global $wgFlaggedArticle;
			$wgFlaggedArticle->isDiffFromStable = true;
		}
		
		return true;
	}
    
	/**
	* Redirect users out to review the changes to the stable version.
	* Only for people who can review and for pages that have a stable version.
	*/ 
    public function injectReviewDiffURLParams( $article, &$sectionanchor, &$extraq ) {
    	global $wgUser, $wgReviewChangesAfterEdit;
    	
    	$frev = $this->getStableRev();
		# Was this already autoreviewed, are we allowed?
		if( $this->skipReviewDiff || !$wgReviewChangesAfterEdit || !$wgUser->isAllowed('review') ) {
			if( $frev )	{
				$extraq .= "stable=0";
			}
    	} else if( $frev )	{
			$frev_id = $frev->fr_rev_id;
			$extraq .= "oldid={$frev_id}&diff=cur&editreview=1";
		}
		
		return true;
	}
	
	/**
	* When a new page is made by a reviwer, try to automatically review it.
	*/ 	
	public function maybeMakeNewPageReviewed( $article, $user, $text, $c, $flags, $a, $b, $flags, $rev ) {
		global $wgFlaggedRevsAutoReviewNew;
	
		if( $this->skipAutoReview || !$wgFlaggedRevsAutoReviewNew || !$user->isAllowed('review') )
			return true;
		# Revision will be null for null edits
		if( !$rev ) {
			$this->skipReviewDiff = true; // Don't jump to diff...
			return true;
		}
		# Assume basic flagging level
		$flags = array();
    	foreach( FlaggedRevs::$dimensions as $tag => $minQL ) {
    		$flags[$tag] = 1;
    	}
		FlaggedRevs::autoReviewEdit( $article, $user, $text, $rev, $flags );
		
		$this->skipReviewDiff = true; // Don't jump to diff...
		$this->skipAutoReview = true; // Be sure not to do stuff twice
		
		return true;
	}

	/**
	* When an edit is made by a reviwer, if the current revision is the stable
	* version, try to automatically review it.
	*/ 
	public function maybeMakeEditReviewed( $article, $user, $text, $c, $m, $a, $b, $flags, $rev ) {
		global $wgFlaggedRevsAutoReview;
		
		if( $this->skipAutoReview || !$wgFlaggedRevsAutoReview || !$user->isAllowed('review') )
			return true;
		# Revision will be null for null edits
		if( !$rev ) {
			$this->skipReviewDiff = true; // Don't jump to diff...
			return true;
		}
		# The previous revision was the current one.
		$prev_id = $article->getTitle()->getPreviousRevisionID( $rev->getID() );
		if( !$prev_id )
			return true;
		$frev = FlaggedRevs::getStablePageRev( $article->getTitle() );
		# Is this an edit directly to the stable version?
		if( is_null($frev) || $prev_id != $frev->fr_rev_id )
			return true;
		# Grab the flags for this revision
		$flags = FlaggedRevs::expandRevisionTags( $frev->fr_tags );
		# Check if user is allowed to renew the stable version.
		# If it has been reviewed too highly for this user, abort.
		foreach( $flags as $quality => $level ) {
			if( !Revisionreview::userCan($quality,$level) ) {
				return true;
			}
		}
		FlaggedRevs::autoReviewEdit( $article, $user, $text, $rev, $flags );
		
		$this->skipReviewDiff = true; // Don't jump to diff...
		$this->skipAutoReview = true; // Be sure not to do stuff twice
		
		return true;
	}
	
	/**
	* When a rollback is made by a reviwer, try to automatically review it.
	*/ 	
	public function maybeMakeRollbackReviewed( $article, $user, $rev ) {
		global $wgFlaggedRevsAutoReview;
		
		if( $this->skipAutoReview || !$wgFlaggedRevsAutoReview || !$user->isAllowed('review') )
			return true;
		# Was this revision flagged?
		$frev = FlaggedRevs::getFlaggedRev( $article->getTitle(), $rev->getID() );
		if( is_null($frev) )
			return true;
		# Grab the flags for this revision
		$flags = FlaggedRevs::getRevisionTags( $rev->getID() );
		# Check if user is allowed to renew the stable version.
		# If it has been reviewed too highly for this user, abort.
		foreach( $flags as $quality => $level ) {
			if( !Revisionreview::userCan($quality,$level) ) {
				return true;
			}
		}
		# Select the version that is now current. Create a new article object
		# to avoid using one with outdated field data.
		$article = new Article( $article->getTitle() );
		$newRev = Revision::newFromId( $article->getLatest() );
		FlaggedRevs::autoReviewEdit( $article, $user, $rev->getText(), $newRev, $flags );
		
		$this->skipReviewDiff = true; // Don't jump to diff...
		$this->skipAutoReview = true; // Be sure not to do stuff twice
		
		return true;
	}
	
	/**
	* Add a link to patrol non-reviewable pages.
	* Also add a diff to stable for other pages if possible.
	*/ 
	public function addPatrolLink( $diff, $OldRev, $NewRev ) {
		global $wgUser, $wgOut;
		// Is there a stable version?
		if( FlaggedRevs::isPageReviewable( $NewRev->getTitle() ) ) {
			global $wgFlaggedArticle;
			
			$frev = $this->getStableRev();
			if( $frev && $frev->fr_rev_id==$OldRev->getID() && $NewRev->isCurrent() ) {
				$wgFlaggedArticle->isDiffFromStable = true;
			}
			// Give a link to the diff-to-stable if needed
			if( $frev && !$wgFlaggedArticle->isDiffFromStable ) {
				$skin = $wgUser->getSkin();
			
				$patrol = '(' . $skin->makeKnownLinkObj( $NewRev->getTitle(),
					wfMsgHtml( 'review-diff2stable' ),
					"oldid={$frev->fr_rev_id}&diff=cur&editreview=1" ) . ')';
				$wgOut->addHTML( '<div align=center>' . $patrol . '</div>' );
			}
		// Prepare a change patrol link, if applicable
		} else if( $wgUser->isAllowed( 'patrolother' ) ) {
			// If we've been given an explicit change identifier, use it; saves time
			if( $diff->mRcidMarkPatrolled ) {
				$rcid = $diff->mRcidMarkPatrolled;
			} else {
				// Look for an unpatrolled change corresponding to this diff
				$dbr = wfGetDB( DB_SLAVE );
				$change = RecentChange::newFromConds(
					array(
						// Add redundant timestamp condition so we can use the existing index
						'rc_timestamp'  => $dbr->timestamp( $diff->mNewRev->getTimestamp() ),
						'rc_this_oldid' => $diff->mNewid,
						'rc_last_oldid' => $diff->mOldid,
						'rc_patrolled'  => 0,
					),
					__METHOD__
				);
				if( $change instanceof RecentChange ) {
					$rcid = $change->mAttribs['rc_id'];
				} else {
					// None found
					$rcid = 0;
				}
			}
			// Build the link
			if( $rcid ) {
				$skin = $wgUser->getSkin();
			
				$reviewtitle = SpecialPage::getTitleFor( 'Revisionreview' );
				$patrol = '[' . $skin->makeKnownLinkObj( $reviewtitle, wfMsgHtml( 'markaspatrolleddiff' ),
					"patrolonly=1&target=" . $NewRev->getTitle()->getPrefixedUrl() . "&rcid={$rcid}" ) . ']';
			} else {
				$patrol = '';
			}
			$wgOut->addHTML( '<div align=center>' . $patrol . '</div>' );
		}
		return true;
	}
    
	/**
	 * Get latest quality rev, if not, the latest reviewed one
	 * @param Bool $getText, get text and params columns?
	 * @param Bool $forUpdate, use DB master and avoid page table?
	 * @return Row
	 */
	public function getStableRev( $getText=false, $forUpdate=false ) {
		if( $this->stablerev === false ) {
			return null; // We already looked and found nothing...
		}
        # Cached results available?
        if( $getText ) {
  			if( !is_null($this->stablerev) && isset($this->stablerev->fr_text) ) {
				return $this->stablerev;
			}
        } else {
 			if( !is_null($this->stablerev) ) {
				return $this->stablerev;
			}       
        }
		# Get the content page, skip talk
		global $wgTitle;
		$title = $wgTitle->getSubjectPage();
		# Do we have one?
		$row = FlaggedRevs::getStablePageRev( $title, $getText, $forUpdate );
        if( $row ) {
			$this->stablerev = $row;
			return $row;
	    } else {
            $this->stablerev = false;
            return null;
        }
	}
	
    /**
	 * Get visiblity restrictions on page
	 * @param Bool $forUpdate, use DB master?
	 * @returns Array (select,override)
	*/
    public function getVisibilitySettings( $forUpdate=false ) {
    	global $wgTitle;
        # Cached results available?
		if( !is_null($this->pageconfig) ) {
			return $this->pageconfig;
		}
		# Get the content page, skip talk
		$title = $wgTitle->getSubjectPage();
		
		$config = FlaggedRevs::getPageVisibilitySettings( $title, $forUpdate );
		$this->pageconfig = $config;
		
		return $config;
	}
    
	/**
	 * @param int $rev_id
	 * @eturns Array, output of the flags for a given revision
	 */
    public function getFlagsForRevision( $rev_id ) {
    	global $wgFlaggedRevTags;
    	# Cached results?
    	if( isset($this->flags[$rev_id]) && $this->flags[$rev_id] )
    		return $this->flags[$rev_id];
    	# Get the flags
    	$flags = FlaggedRevs::getRevisionTags( $rev_id );
		# Try to cache results
		$this->flags[$rev_id] = $flags;
		
		return $flags;
	}
	
	/**
	* Updates parser cache output to included needed versioning params.
	*/
	function maybeUpdateMainCache( $article, &$outputDone, &$pcache ) {
		global $wgUser, $wgRequest;
		
		$action = $wgRequest->getVal( 'action', 'view' );
		# Only trigger on article view for content pages, not for protect/delete/hist
		if( ($action !='view' && $action !='purge') || !$wgUser->isAllowed( 'review' ) )
			return true;
		if( !$article || !$article->exists() || !FlaggedRevs::isPageReviewable( $article->getTitle() ) )
			return true;
		
		$parserCache = ParserCache::singleton();
		$parserOut = $parserCache->get( $article, $wgUser );
		if( $parserOut ) {
			# Clear older, incomplete, cached versions
			# We need the IDs of templates and timestamps of images used
			if( !isset($parserOut->mTemplateIds) || !isset($parserOut->fr_ImageSHA1Keys) )
				$article->getTitle()->invalidateCache();
		}
		return true;
	}

}
