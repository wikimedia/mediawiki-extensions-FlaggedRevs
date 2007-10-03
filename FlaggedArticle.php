<?php

class FlaggedArticle extends FlaggedRevs {
	/**
	 * Does the config and current URL params allow 
	 * for overriding by stable revisions?
	 */		
    function pageOverride() {
    	global $wgFlaggedRevsAnonOnly, $wgFlaggedRevs,
			$wgTitle, $wgUser, $wgRequest, $action;
    	# This only applies to viewing content pages
    	if( $action !='view' || !$this->isReviewable( $wgTitle ) ) 
			return false;
    	# Does not apply to diffs/old revisions
    	if( $wgRequest->getVal('oldid') || $wgRequest->getVal('diff') ) 
			return false;
		# Get page config
		$config = $wgFlaggedRevs->getVisibilitySettings( $wgTitle );
    	# Does the stable version override the current one?
    	if( $config['override'] ) {
    		# If $wgFlaggedRevsAnonOnly is set to false, stable version are only requested explicitly
    		if( $wgFlaggedRevsAnonOnly && $wgUser->isAnon() ) {
    			return !( $wgRequest->getIntOrNull('stable') === 0 );
    		} else {
    			return ( $wgRequest->getIntOrNull('stable') === 1 );
    		}
    	# We are explicity requesting the stable version?
		} else if( $wgRequest->getIntOrNull('stable') === 1 ) {
			return true;
		}
		return false;
	}
	
	 /**
	 * Is this user shown the stable version by default for this page?
	 */	
	function showStableByDefault() {
		global $wgFlaggedRevsOverride, $wgFlaggedRevsAnonOnly, $wgUser;
		
		$config = $this->getVisibilitySettings();
		
		return ( $config['override'] && !($wgFlaggedRevsAnonOnly && !$wgUser->isAnon()) );
	}

	 /**
	 * Replaces a page with the last stable version if possible
	 * Adds stable version status/info tags and notes
	 * Adds a quick review form on the bottom if needed
	 */
	function setPageContent( $article, &$outputDone, &$pcache ) {
		global $wgRequest, $wgTitle, $wgOut, $action, $wgUser;
		// Only trigger on article view for content pages, not for protect/delete/hist
		if( $action !='view' || !$article || !$article->exists() || !$this->isReviewable( $article->mTitle ) ) 
			return true;
		// Grab page and rev ids
		$pageid = $article->getId();
		$revid = $article->mRevision ? $article->mRevision->mId : $article->getLatest();
		if( !$revid ) 
			return true;
			
		$skin = $wgUser->getSkin();
		
		$vis_id = $revid;
		$tag = $notes = '';
		# Check the newest stable version...
		$tfrev = $this->getStableRev( null, true );
		$simpleTag = false;
		if( $wgRequest->getVal('diff') || $wgRequest->getVal('oldid') ) {
    		// Do not clutter up diffs any further...
		} else if( !is_null($tfrev) ) {
			global $wgLang;
			# Get flags and date
			$flags = $this->getFlagsForRevision( $tfrev->fr_rev_id );
			# Get quality level
			$quality = $this->isQuality( $flags );
			$pristine =  $this->isPristine( $flags );
			$time = $wgLang->date( wfTimestamp(TS_MW, $tfrev->fr_timestamp), true );
			# Looking at some specific old rev or if flagged revs override only for anons
			if( !$this->pageOverride() ) {
				$revs_since = $this->getRevCountSince( $pageid, $tfrev->fr_rev_id );
				$simpleTag = true;
				# Construct some tagging
				if( !$wgOut->isPrintable() ) {
					if( $this->useSimpleUI() ) {
						if($revs_since) {
							$msg = $quality ? 'revreview-quick-see-quality' : 'revreview-quick-see-basic';
							$tag .= "<span class='fr_tab_current plainlinks'></span>" . 
								wfMsgExt($msg,array('parseinline'), $tfrev->fr_rev_id, $revs_since);
						} else {
							$msg = $quality ? 'revreview-quick-seeandis-quality': 'revreview-quick-seeandis-basic';
							$css = $quality ? 'fr_tab_quality' : 'fr_tab_stable';
							$tag .= "<span class='$css'></span>" . 
								wfMsgExt($msg,array('parseinline'));
						}
						$tag .= $this->prettyRatingBox( $tfrev, $flags, $revs_since, false );							
					} else {
						$msg = $quality ? 'revreview-newest-quality' : 'revreview-newest-basic';
						$tag .= wfMsgExt($msg, array('parseinline'), $tfrev->fr_rev_id, $time, $revs_since);
						# Hide clutter
						if( !empty($flags) ) {
							$tag .= ' <a id="mw-revisiontoggle" style="display:none;" href="javascript:toggleRevRatings()">' . 
								wfMsg('revreview-toggle') . '</a>';
							$tag .= '<span id="mw-revisionratings" style="display:block;">' . 
								wfMsg('revreview-oldrating') . $this->addTagRatings( $flags ) . '</span>';
						}
					}
				}
			// Viewing the page normally: override the page
			} else {
       			# We will be looking at the reviewed revision...
       			$vis_id = $tfrev->fr_rev_id;
       			$revs_since = $this->getRevCountSince( $pageid, $vis_id );
				# Construct some tagging
				if( !$wgOut->isPrintable() ) {
					if( $this->useSimpleUI() ) {
						$msg = $quality ? 'revreview-quick-quality' : 'revreview-quick-basic';
						$css = $quality ? 'fr_tab_quality' : 'fr_tab_stable';
						$tag .= "<span class='$css plainlinks'></span>" . 
							wfMsgExt($msg,array('parseinline'),$tfrev->fr_rev_id,$revs_since);
					 	$tag .= $this->prettyRatingBox( $tfrev, $flags, $revs_since );
					} else {
						$msg = $quality ? 'revreview-quality' : 'revreview-basic';
						$tag = wfMsgExt($msg, array('parseinline'), $vis_id, $time, $revs_since);
						if( !empty($flags) ) {
							$tag .= ' <a id="mw-revisiontoggle" style="display:none;" href="javascript:toggleRevRatings()">' . 
								wfMsg('revreview-toggle') . '</a>';
							$tag .= '<span id="mw-revisionratings" style="display:block;">' . 
								$this->addTagRatings( $flags ) . '</span>';
						}
					}
				}
				# Try the stable page cache
				$parserOut = $this->getPageCache( $article );
				# If no cache is available, get the text and parse it
				if( $parserOut==false ) {
					$text = $this->uncompressText( $tfrev->fr_text, $tfrev->fr_flags );
       				$parserOut = $this->parseStableText( $article, $text, $vis_id );
       				# Update the general cache
       				$this->updatePageCache( $article, $parserOut );
       			}
       			$wgOut->mBodytext = $parserOut->getText();
       			# Show stable categories and interwiki links only
       			$wgOut->mCategoryLinks = array();
       			$wgOut->addCategoryLinks( $parserOut->getCategories() );
       			$wgOut->mLanguageLinks = array();
       			$wgOut->addLanguageLinks( $parserOut->getLanguageLinks() );
				$notes = $this->ReviewNotes( $tfrev );
				# Tell MW that parser output is done
				$outputDone = true;
				$pcache = false;
			}
			# Some checks for which tag CSS to use
			if( $this->useSimpleUI() )
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
				$tag = '<div id="mw-revisiontag" class="' . $tagClass . ' plainlinks">'.$tag.'</div>';
			# Set the new body HTML, place a tag on top
			$wgOut->mBodytext = $tag . $wgOut->mBodytext . $notes;
		// Add "no reviewed version" tag, but not for main page
		} else if( !$wgOut->isPrintable() && !$this->isMainPage( $article->mTitle ) ) {
			if( $this->useSimpleUI() ) {
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
    
    function addToEditView( $editform ) {
		global $wgRequest, $wgTitle, $wgOut;
		# Talk pages cannot be validated
		if( !$editform->mArticle || !$this->isReviewable( $wgTitle ) )
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
			$flags = $this->getFlagsForRevision( $tfrev->fr_rev_id );
			$revs_since = $this->getRevCountSince( $editform->mArticle->getID(), $tfrev->fr_rev_id );
			# Construct some tagging
			$msg = $this->isQuality( $flags ) ? 'revreview-newest-quality' : 'revreview-newest-basic';
			$tag = wfMsgExt($msg, array('parseinline'), $tfrev->fr_rev_id, $time, $revs_since );
			# Hide clutter
			if( !empty($flags) ) {
				$tag .= ' <a id="mw-revisiontoggle" style="display:none;" href="javascript:toggleRevRatings()">' . 
					wfMsg('revreview-toggle') . '</a>';
				$tag .= '<span id="mw-revisionratings" style="display:block;">' . 
					wfMsg('revreview-oldrating') . $this->addTagRatings( $flags ) . 
					'</span>';
			}
			$wgOut->addHTML( '<div id="mw-revisiontag" class="flaggedrevs_notice plainlinks">' . $tag . '</div>' );
			# If this will be autoreviewed, notify the user...
			if( !$wgFlaggedRevsAutoReview )
				return true;
			if( $wgUser->isAllowed('review') && $tfrev->fr_rev_id==$editform->mArticle->getLatest() ) {
				# Grab the flags for this revision
				$flags = $this->getFlagsForRevision( $tfrev->fr_rev_id );
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
	
    function addReviewForm( $out ) {
    	global $wgArticle, $wgRequest, $action;

		if( !$wgArticle || !$wgArticle->exists() || !$this->isReviewable( $wgArticle->mTitle ) ) 
			return true;
		# Check if page is protected
		if( $action !='view' || !$wgArticle->mTitle->quickUserCan( 'edit' ) ) {
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
    
    function addVisibilityLink( $out ) {
    	global $wgUser, $wgRequest, $wgTitle, $action;
    	
    	if( !$this->isReviewable( $wgTitle ) )
    		return true;
    	
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
			'href' => $sktmp->makeSpecialUrl( 'Stableversions', "oldid={$tfrev->fr_rev_id}" )
		);
		# Are we using the popular cite extension?
		global $wgHooks;
		if( in_array('wfSpecialCiteNav',$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink']) ) {
			if( $this->isReviewable( $sktmp->mTitle ) && $revid !== 0 ) {
				$nav_urls['cite'] = array(
					'text' => wfMsg( 'cite_article_link' ),
					'href' => $sktmp->makeSpecialUrl( 'Cite', "page=" . wfUrlencode( "{$sktmp->thispage}" ) . "&id={$tfrev->fr_rev_id}" )
				);
			}
		}
		return true;
    }
    
    function setActionTabs( $sktmp, &$content_actions ) {
    	global $wgRequest, $wgUser, $action, $wgFlaggedRevsOverride, $wgFlaggedRevTabs;
		# Get the subject page, not all skins have it :(
		if( !isset($sktmp->mTitle) )
			return true;
		$title = $sktmp->mTitle->getSubjectPage();
		# Non-content pages cannot be validated
		if( !$this->isReviewable( $title ) || !$title->exists() )
			return true;
		$article = new Article( $title );
		# If we are viewing a page normally, and it was overridden,
		# change the edit tab to a "current revision" tab
       	$tfrev = $this->getStableRev();
       	# No quality revs? Find the last reviewed one
       	if( !is_object($tfrev) ) {
			return true;
		}
       	/* 
		// If the stable version is the same is the current, move along...
    	if( $article->getLatest() == $tfrev->fr_rev_id ) {
       		return true;
       	}
       	*/
       	# Be clear about what is being edited...
       	if( !$sktmp->mTitle->isTalkPage() && $this->showStableByDefault() ) {
       		if( isset( $content_actions['edit'] ) )
       			$content_actions['edit']['text'] = wfMsg('revreview-edit');
       		if( isset( $content_actions['viewsource'] ) )
       			$content_actions['viewsource']['text'] = wfMsg('revreview-source');
       	}

	// If we're set up to only show stable versions on request, this can be overriden
	// on a per-page basis using Special:Stabilization, and the tab for accessing it
	// with the current page is inserted here.
	if(!$wgFlaggedRevsOverride) {
		$stabTitle = SpecialPage::getTitleFor( 'Stabilization' );
       		$content_actions['qa'] = array(
			'class' => false,
			'text' => wfmsg('stabilization-tab'),
			'href' => $stabTitle->getLocalUrl('page='.$title->getPrefixedUrl())
		);
	}

     	if( !$wgFlaggedRevTabs ) {
       		return true;
       	}
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
    	} else if( $action !='view' || $sktmp->mTitle->isTalkPage() ) {
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
    
    function addToHistLine( $row, &$s ) {
    	global $wgUser, $wgTitle;
		# Non-content pages cannot be validated
		if( !$this->isReviewable( $wgTitle ) ) 
			return true;
		
		if( !isset($this->dbw) ) {
    		$this->dbw = wfGetDB( DB_MASTER );
    	}
    	
    	$quality = $this->dbw->selectField( 'flaggedrevs', 'fr_quality',
    		array( 'fr_namespace' => $wgTitle->getNamespace(),
				'fr_title' => $wgTitle->getDBKey(),
				'fr_rev_id' => $row->rev_id ),
			__METHOD__,
			array( 'FORCE INDEX' => 'PRIMARY') );
    	
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
    
    function addQuickReview( $id=NULL, $out, $top=false ) {
		global $wgOut, $wgTitle, $wgUser, $wgRequest, $wgFlaggedRevComments, 
			$wgFlaggedRevsOverride, $wgFlaggedRevsAnonOnly, $wgFlaggedRevsWatch;
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
		$action = $reviewtitle->escapeLocalUrl( 'action=submit' );
		$form = Xml::openElement( 'form', array( 'method' => 'post', 'action' => $action ) );
		$form .= "<fieldset><legend>" . wfMsgHtml( 'revreview-flag', $id ) . "</legend>\n";
		
		if( $wgFlaggedRevsOverride && $wgFlaggedRevsAnonOnly )
			$form .= '<p>'.wfMsgExt( 'revreview-text', array('parseinline') ).'</p>';
		
		$form .= Xml::hidden( 'title', $reviewtitle->getPrefixedText() );
		$form .= Xml::hidden( 'target', $wgTitle->getPrefixedText() );
		$form .= Xml::hidden( 'oldid', $id );
		$form .= Xml::hidden( 'action', 'submit');
        $form .= Xml::hidden( 'wpEditToken', $wgUser->editToken() );
        
		foreach( $this->dimensions as $quality => $levels ) {
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
			if( $disabled ) $selectAttribs['disabled'] = 'disabled';
			$form .= Xml::openElement( 'select', $selectAttribs );
			$form .= implode( "\n", $options );
			$form .= "</select>\n";
		}
        if( $wgFlaggedRevComments && $wgUser->isAllowed( 'validate' ) ) {
			$form .= "<br/><p>" . wfMsgHtml( 'revreview-notes' ) . "</p>" .
			"<p><textarea tabindex='1' name='wpNotes' id='wpNotes' rows='2' cols='80' style='width:100%'></textarea>" .	
			"</p>\n";
		}
		
		$imageParams = $templateParams = '';
        if( !isset($out->mTemplateIds) || !isset($out->mImageSHA1Keys) ) {
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
        foreach( $out->mImageSHA1Keys as $dbkey => $timeAndSHA1 ) {
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
			$out->mBodytext =  $form . '<span style="clear:both"/>' . $out->mBodytext;
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
        
		foreach( $this->dimensions as $quality => $value ) {
			$encValueText = wfMsgHtml('revreview-' . $this->dimensions[$quality][$flags[$quality]]);
            $level = $flags[$quality];
            $minlevel = $wgFlaggedRevTags[$quality];
            if( $level >= $minlevel )
                $classmarker = 2;
            elseif( $level > 0 )
                $classmarker = 1;
            else
                $classmarker = 0;

            $levelmarker = $level * 20 + 20; //XXX do this better
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
	 * @param int $rev_since, revisions since review
	 * @param bool $stable, are we referring to the stable revision?
	 * @return string
	 * Generates a review box using a table using addTagRatings()
	 */	
	public function prettyRatingBox( $tfrev, $flags, $revs_since, $stable=true ) {
		global $wgLang;
		# Get quality level
		$quality = self::isQuality( $flags );
		$pristine = self::isPristine( $flags );
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
        
		$box = ' <a id="mw-revisiontoggle" style="display:none;" href="javascript:toggleRevRatings()">' . 
			wfMsg('revreview-toggle') . '</a>';
		$box .= '<span id="mw-revisionratings">' .
			wfMsgExt($msg, array('parseinline'), $tfrev->fr_rev_id, $time, $revs_since);
		if( !empty($flags) ) {
			$encRatingLabel = $stable ? '' : ' ' . wfMsgHtml('revreview-oldrating');
			$box .= $encRatingLabel . self::addTagRatings( $flags, true, "{$tagClass}a" );
		}
		$box .= '</span>';
        return $box;
	}
	
	/**
	 * @param Row $row
	 * @return string
	 * Generates revision review notes
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
	 * Get latest quality rev, if not, the latest reviewed one
	 * Same params for the sake of inheritance
	 * @return Row
	 */
	function getStableRev( $t=null, $getText=false, $forUpdate=false ) {
		global $wgTitle, $wgFlaggedRevs;
        # Cached results available?
        if( $getText ) {
  			if( isset($this->stablerev) && isset($this->stablerev->fr_text) ) {
				return $this->stablerev;
			}       
        } else {
 			if( isset($this->stablerev) ) {
				return $this->stablerev;
			}       
        }
		# Get the content page, skip talk
		$title = $wgTitle->getSubjectPage();
		# Do we have one?
		$row = $this->getStablePageRev( $title, $getText, $forUpdate );
        if( $row ) {
			$this->stablerev = $row;
			return $row;
	    } else {
            $this->stablerev = null;
            return null;
        }
	}
	
    /**
	 * Get visiblity restrictions on page
	 * Same params for the sake of inheritance
	 * @returns Array
	*/
    public function getVisibilitySettings( $t=null, $forUpdate=false ) {
    	global $wgTitle;
        # Cached results available?
		if( isset($this->pageconfig) ) {
			return $this->pageconfig;
		}
		# Get the content page, skip talk
		$title = $wgTitle->getSubjectPage();
		
		$config = $this->getPageVisibilitySettings( $title, $forUpdate );
		$this->pageconfig = $config;
		
		return $config;
	}
    
	/**
	 * @param int $rev_id
	 * Return an array output of the flags for a given revision
	 */	
    public function getFlagsForRevision( $rev_id ) {
    	global $wgFlaggedRevTags;
    	# Cached results?
    	if( isset($this->flags[$rev_id]) && $this->flags[$rev_id] )
    		return $this->revflags[$rev_id];
    	# Get the flags
    	$flags = $this->getRevisionTags( $rev_id );
		# Try to cache results
		$this->flags[$rev_id] = true;
		$this->revflags[$rev_id] = $flags;
		
		return $flags;
	}

}
