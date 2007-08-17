<?php

#(c) Joerg Baach, Aaron Schulz, 2007 GPL

global $IP;
require_once( "$IP/includes/LogPage.php" );
require_once( "$IP/includes/SpecialLog.php" );

class Revisionreview extends SpecialPage
{

    function Revisionreview() {
        SpecialPage::SpecialPage('Revisionreview', 'review');
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgOut, $wgFlaggedRevs,
			$wgFlaggedRevTags, $wgFlaggedRevValues;

		$confirm = $wgRequest->wasPosted() &&
			$wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );

		if( $wgUser->isAllowed( 'review' ) ) {
			if( $wgUser->isBlocked( !$confirm ) ) {
				$wgOut->blockedPage();
				return;
			}
		} else {
			$wgOut->permissionRequired( 'review' );
			return;
		}
		if( wfReadOnly() ) {
			$wgOut->readOnlyPage();
			return;
		}
		
		$this->setHeaders();
		// Our target page
		$this->target = $wgRequest->getText( 'target' );
		// Revision ID
		$this->oldid = $wgRequest->getIntOrNull( 'oldid' );
		// Must be a valid content page
		$this->page = Title::newFromUrl( $this->target );
		if( !$this->target || !$this->oldid || !$this->page->isContentPage() ) {
			$wgOut->addHTML( wfMsgExt('revreview-main',array('parse')) );
			return;
		}
		if( is_null($this->page) || is_null($this->oldid) ) {
			$wgOut->showErrorPage('notargettitle', 'notargettext' );
			return;
		}
		// Check if page is protected
		if( !$this->page->quickUserCan( 'edit' ) ) {
			$wgOut->permissionRequired( 'badaccess-group0' );
			return;
		}
		// Special parameter mapping
		$this->templateParams = $wgRequest->getVal( 'templateParams' );
		$this->imageParams = $wgRequest->getVal( 'imageParams' );
		// Log comment
		$this->comment = $wgUser->isAllowed('validate') ? 
			$wgRequest->getText( 'wpReason' ) : '';
		// Additional notes (displayed at bottom of page)
		$this->notes = ($wgFlaggedRevs->allowComments() && $wgUser->isAllowed('validate')) ? 
			$wgRequest->getText('wpNotes') : '';
		// Get the revision's current flags, if any
		$this->oflags = $wgFlaggedRevs->getFlagsForRevision( $this->oldid );
		// Get our accuracy/quality dimensions
		$this->dims = array();
		$this->upprovedTags = 0;
		foreach( $wgFlaggedRevTags as $tag => $minQL ) {
			$this->dims[$tag] = $wgRequest->getIntOrNull( "wp$tag" );
			// Must be greater than zero
			if( $this->dims[$tag] < 0 || $this->dims[$tag] > $wgFlaggedRevValues ) {
				$wgOut->showErrorPage('notargettitle', 'notargettext' );
				return;
			}
			if( $this->dims[$tag]==0 )
				$this->upprovedTags++;
			// Check permissions
			if( !$this->userCan( $tag, $this->oflags[$tag] ) ) {
				# Users can't take away a status they can't set
				$wgOut->permissionRequired( 'badaccess-group0' );
				return;
			} else if( !$this->userCan( $tag, $this->dims[$tag] ) ) {
			// Users cannot review to beyond their rights level
				$wgOut->permissionRequired( 'badaccess-group0' );
				return;
			}
		}
		// We must at least rate each category as 1, the minimum
		// Exception: we can rate ALL as unapproved to depreciate a revision
		$valid = true;
		if( $this->upprovedTags && ($this->upprovedTags < count($wgFlaggedRevTags) || !$this->oflags) )
			$valid = false;
		if( !$wgUser->matchEditToken( $wgRequest->getVal('wpEditToken') ) )
			$valid = false;
		
		if( $valid && $wgRequest->wasPosted() ) {
			$this->submit( $wgRequest );
		} else {
			$this->showRevision();
		}
	}
	
	/**
	 * @param string $tag
	 * @param int $val
	 * Returns true if a user can do something
	 */	
	public static function userCan( $tag, $value ) {
		global $wgFlagRestrictions, $wgUser;
		
		if( !isset($wgFlagRestrictions[$tag]) )
			return true;
		// Validators always have full access
		if( $wgUser->isAllowed('validate') )
			return true;
		// Check if this user has any right that lets him/her set
		// up to this particular value
		foreach( $wgFlagRestrictions[$tag] as $right => $level ) {
			if( $value <= $level && $wgUser->isAllowed($right) ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Show revision review form
	 */
	function showRevision() {
		global $wgOut, $wgUser, $wgTitle, $wgFlaggedRevComments, $wgFlaggedRevsOverride,
			$wgFlaggedRevTags, $wgFlaggedRevValues;
		
		if( $this->upprovedTags )
			$wgOut->addWikiText( '<strong>' . wfMsg( 'revreview-toolow' ) . '</strong>' );
		
		$wgOut->addWikiText( wfMsg( 'revreview-selected', $this->page->getPrefixedText() ) );
		
		$this->skin = $wgUser->getSkin();
		$rev = Revision::newFromTitle( $this->page, $this->oldid );
		// Check if rev exists
		// Do not mess with deleted revisions
		if( !isset( $rev ) || $rev->mDeleted ) {
			$wgOut->showErrorPage( 'internalerror', 'notargettitle', 'notargettext' );
			return;
		}
		
		$wgOut->addHtml( "<ul>" );
		$wgOut->addHtml( $this->historyLine( $rev ) );
		$wgOut->addHtml( "</ul>" );
		
		if( $wgFlaggedRevsOverride )
			$wgOut->addWikiText( wfMsg('revreview-text') );
		
		$formradios = array();
		// Dynamically contruct our radio options
		foreach( $wgFlaggedRevTags as $tag => $minQL ) {
			$formradios[$tag] = array();
			for ($i=0; $i <= $wgFlaggedRevValues; $i++) {
				$formradios[$tag][] = array( "revreview-$tag-$i", "wp$tag", $i );
			}
		}
		$hidden = array(
			wfHidden( 'wpEditToken', $wgUser->editToken() ),
			wfHidden( 'target', $this->page->getPrefixedText() ),
			wfHidden( 'oldid', $this->oldid ) );	
		
		$action = $wgTitle->escapeLocalUrl( 'action=submit' );
		$form = "<form name='revisionreview' action='$action' method='post'>";
		$form .= '<fieldset><legend>' . wfMsgHtml( 'revreview-legend' ) . '</legend><table><tr>';
		// Dynamically contruct our review types
		foreach( $wgFlaggedRevTags as $tag => $minQL ) {
			$form .= '<td><strong>' . wfMsgHtml( "revreview-$tag" ) . '</strong></td><td width=\'20\'></td>';
		}
		$form .= '</tr><tr>';
		foreach( $formradios as $set => $ratioset ) {
			$form .= '<td>';
			foreach( $ratioset as $item ) {
				list( $message, $name, $field ) = $item;
				// Don't give options the user can't set unless its the status quo
				$attribs = array('id' => $name.$field);
				if( !$this->userCan($set,$field) )
					$attribs['disabled'] = 'true';
				$form .= "<div>";
				$form .= Xml::radio( $name, $field, ($field==$this->dims[$set]), $attribs );
				$form .= Xml::label( wfMsg($message), $name.$field );
				$form .= "</div>\n";
			}
			$form .= '</td><td width=\'20\'></td>';
		}
		$form .= '</tr></table></fieldset>';
		// Add box to add live notes to a flagged revision
		if( $wgFlaggedRevComments && $wgUser->isAllowed( 'validate' ) ) {
			$form .= "<fieldset><legend>" . wfMsgHtml( 'revreview-notes' ) . "</legend>" .
			"<textarea tabindex='1' name='wpNotes' id='wpNotes' rows='3' cols='80' style='width:100%'>$this->notes</textarea>" .	
			"</fieldset>";
		}
       	// Not much to say unless you are a validator
		if( $wgUser->isAllowed( 'validate' ) )
			$form .= '<p>'.wfInputLabel( wfMsgHtml( 'revreview-log' ), 'wpReason', 'wpReason', 60 ).'</p>';
		
		$form .= '<p>'.wfSubmitButton( wfMsgHtml( 'revreview-submit' ) ).'</p>';
		
		foreach( $hidden as $item ) {
			$form .= $item;
		}
		// Hack, versioning params
		$form .= Xml::hidden( 'templateParams', $this->templateParams );
		$form .= Xml::hidden( 'imageParams', $this->imageParams );
		
		$form .= '</form>';
		$wgOut->addHtml( $form );
	}
	
	/**
	 * @param Revision $rev
	 * @returns string
	 */
	function historyLine( $rev ) {
		global $wgContLang;
		$date = $wgContLang->timeanddate( $rev->getTimestamp() );
		
		$difflink = '(' . $this->skin->makeKnownLinkObj( $this->page, wfMsgHtml('diff'), 
		'&diff=' . $rev->getId() . '&oldid=prev' ) . ')';
		
		$revlink = $this->skin->makeLinkObj( $this->page, $date, 'oldid=' . $rev->getId() );
		
		return
			"<li> $difflink $revlink " . $this->skin->revUserLink( $rev ) . " " . $this->skin->revComment( $rev ) . "</li>";
	}
	
	function submit( $request ) {
		global $wgOut, $wgUser;
		
		$approved = false;
		# If all values are set to zero, this has been unapproved
		foreach( $this->dims as $quality => $value ) {
			if( $value ) {
				$approved = true;
				break;
			}
		}
		// We can only approve actual revisions...
		if( $approved ) {
			$rev = Revision::newFromTitle( $this->page, $this->oldid );
			// Do not mess with archived/deleted revisions
			if( is_null($rev) || $rev->mDeleted ) {
				$wgOut->showErrorPage( 'internalerror', 'revnotfoundtext' );
				return;
			}
		} else {
			$frev = FlaggedRevs::getFlaggedRev( $this->oldid );
			// If we can't find this flagged rev, return to page???
			if( is_null($frev) ) {
				$wgOut->redirect( $this->page->escapeLocalUrl() );
				return;
			}
		}
		
		$success = $approved ? $this->approveRevision( $rev, $this->notes ) : $this->unapproveRevision( $frev );
		// Return to our page			
		if( $success ) {
			if( $request->getCheck( 'wpWatchthis' ) ) {
				$wgUser->addWatch( $this->page );
			} else {
				$wgUser->removeWatch( $this->page );
			}
        	$wgOut->redirect( $this->page->escapeLocalUrl() );
		} else {
			$wgOut->showErrorPage( 'internalerror', 'revreview-changed' );
		}
	}

	/**
	 * @param Revision $rev
	 * Adds or updates the flagged revision table for this page/id set
	 */
	function approveRevision( $rev=NULL, $notes='' ) {
		global $wgUser, $wgFlaggedRevsWatch, $wgParser;
		
		if( is_null($rev) ) 
			return false;
		// Get the page this corresponds to
		$title = $rev->getTitle();
		
		$quality = 0;
		if( FlaggedRevs::isQuality($this->dims) ) {
			$quality = FlaggedRevs::getLCQuality($this->dims);
			$quality = ($quality > 1) ? $quality : 1;
		}
		// Our flags
		$flagset = array();
		foreach( $this->dims as $tag => $value ) {
			$flagset[] = array(
				'frt_rev_id' => $rev->getId(),
				'frt_dimension' => $tag,
				'frt_value' => $value 
			);
		}
		
		// Hack, our template version pointers
		$tmpset = $templates = array();
		$templateMap = explode('#',trim($this->templateParams) );
		foreach( $templateMap as $template ) {
			if( !$template ) continue;
			
			$m = explode('|',$template,2);
			if( !isset($m[0]) || !isset($m[1]) || !$m[0] ) continue;
			
			list($prefixed_text,$rev_id) = $m;
			
			if( in_array($prefixed_text,$templates) ) continue; // No dups!
			$templates[] = $prefixed_text;
			
			$tmp_title = Title::newFromText( $prefixed_text ); // Normalize this to be sure...
			if( is_null($title) ) continue; // Page must exist!
			
			$tmpset[] = array(
				'ft_rev_id' => $rev->getId(),
				'ft_namespace' => $tmp_title->getNamespace(),
				'ft_title' => $tmp_title->getDBKey(),
				'ft_tmp_rev_id' => $rev_id
			);
		}
		// Hack, our image version pointers
		$imgset = $images = array();
		$imageMap = explode('#',trim($this->imageParams) );
		foreach( $imageMap as $image ) {
			if( !$image ) continue;
			$m = explode('|',$image,3);
			# Expand our parameters ... <name>#<timestamp>#<key>
			if( !isset($m[0]) || !isset($m[1]) || !isset($m[2]) || !$m[0] )
				continue;
			
			list($dbkey,$timestamp,$key) = $m;
			
			if( in_array($dbkey,$images) ) continue; // No dups!
			
			$images[] = $dbkey;
			
			$img_title = Title::makeTitle( NS_IMAGE, $dbkey ); // Normalize
			if( is_null($img_title) ) continue; // Page must exist!
			
			$imgset[] = array( 
				'fi_rev_id' => $rev->getId(),
				'fi_name' => $img_title->getDBKey(),
				'fi_img_timestamp' => $timestamp,
				'fr_img_sha1' => $key
			);
		}
		
		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin();
		// Update our versioning pointers
		if( !empty( $tmpset ) ) {
			$dbw->replace( 'flaggedtemplates', array( array('ft_rev_id','ft_namespace','ft_title') ), $tmpset,
				__METHOD__ );
		}
		if( !empty( $imgset ) ) {
			$dbw->replace( 'flaggedimages', array( array('fi_rev_id','fi_name') ), $imgset, 
				__METHOD__ );
		}
        // Get the page text and resolve all templates
        list($fulltext,$complete) = FlaggedRevs::expandText( $rev->getText(), $rev->getTitle(), $rev->getId() );
        if( !$complete ) {
        	$dbw->rollback(); // All versions must be specified, 0 for none
        	return false;
        }
		// Our review entry
 		$revset = array(
 			'fr_rev_id'    => $rev->getId(),
 			'fr_namespace' => $title->getNamespace(),
 			'fr_title'     => $title->getDBkey(),
			'fr_user'      => $wgUser->getId(),
			'fr_timestamp' => wfTimestampNow(),
			'fr_comment'   => $notes,
			'fr_text'      => $fulltext, // Store expanded text for speed
			'fr_quality'   => $quality
		);
		// Update flagged revisions table
		$dbw->replace( 'flaggedrevs', array( array('fr_rev_id','fr_namespace','fr_title') ), $revset, __METHOD__ );
		// Set all of our flags
		$dbw->replace( 'flaggedrevtags', array( array('frt_rev_id','frt_dimension') ), $flagset, __METHOD__ );
		// Mark as patrolled
		$dbw->update( 'recentchanges', 
			array( 'rc_patrolled' => 1 ), 
			array( 'rc_this_oldid' => $rev->getId() ), 
			__METHOD__ 
		);
		$dbw->commit();
		
		// Update the article review log
		$this->updateLog( $this->page, $this->dims, $this->comment, $this->oldid, true );
		
		$article = new Article( $this->page );
		// Update the links tables as the stable version may now be the default page...
		$parserCache =& ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if( $poutput==false ) {
			$text = $article->getContent();
			$poutput = $wgParser->parse($text, $article->mTitle, ParserOptions::newFromUser($wgUser));
		}
		$u = new LinksUpdate( $this->page, $poutput );
		$u->doUpdate(); // Will trigger our hook to add stable links too...
		
		# Clear the cache...
		$this->page->invalidateCache();
		# Might as well save the cache
		$parserCache->save( $poutput, $article, $wgUser );
		# Purge squid for this page only
		$this->page->purgeSquid();
		
        return true;
    }

	/**
	 * @param Revision $rev
	 * Removes flagged revision data for this page/id set
	 */  
	function unapproveRevision( $row=NULL ) {
		global $wgUser, $wgFlaggedRevsWatch;
	
		if( is_null($row) ) return false;
		
		$user = $wgUser->getId();
		
		wfProfileIn( __METHOD__ );
        $dbw = wfGetDB( DB_MASTER );
		// Delete from table
		$dbw->delete( 'flaggedrevs', array( 'fr_rev_id' => $row->fr_rev_id ) );
		// Wipe versioning pointers
		$dbw->delete( 'flaggedtemplates', array( 'ft_rev_id' => $row->fr_rev_id ) );
		$dbw->delete( 'flaggedimages', array( 'fi_rev_id' => $row->fr_rev_id ) );
		// And the flags...
		$dbw->delete( 'flaggedrevtags', array( 'frt_rev_id' => $row->fr_rev_id ) );
		
		// Update the article review log
		$this->updateLog( $this->page, $this->dims, $this->comment, $this->oldid, false );
		
		$article = new Article( $this->page );
		// Update the links tables as a new stable version
		// may now be the default page.
		$parserCache =& ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if( $poutput==false ) {
			$text = $article->getContent();
			$poutput = $wgParser->parse($text, $article->mTitle, ParserOptions::newFromUser($wgUser));
		}
		$u = new LinksUpdate( $this->page, $poutput );
		$u->doUpdate();
		
		# Clear the cache...
		$this->page->invalidateCache();
		# Might as well save the cache
		$parserCache->save( $poutput, $article, $wgUser );
		# Purge squid for this page only
		$this->page->purgeSquid();
		
		wfProfileOut( __METHOD__ );
		
        return true;
    }

	/**
	 * Record a log entry on the action
	 * @param Title $title
	 * @param array $dimensions
	 * @param string $comment
	 * @param int $revid
	 * @param bool $approve
	 * @param bool $RC, add to recentchanges
	 */	
	public static function updateLog( $title, $dimensions, $comment, $oldid, $approve, $RC=true ) {
		// Lets NOT spam RC, set $RC to false
		$log = new LogPage( 'review', $RC );
		// ID, accuracy, depth, style
		$ratings = array();
		foreach( $dimensions as $quality => $level ) {
			$ratings[] = wfMsg( "revreview-$quality" ) . ": " . wfMsg("revreview-$quality-$level");
		}
		$rating = ($approve) ? ' [' . implode(', ',$ratings). ']' : '';
		// Append comment with action
		// FIXME: do this better
		$action = wfMsgExt('review-logaction', array('parsemag'), $oldid );
		if( $approve )
			$comment = ($comment) ? "$action: $comment$rating" : "$action $rating";
		else
			$comment = ($comment) ? "$action: $comment" : "$action";
			
		if( $approve ) {
			$log->addEntry( 'approve', $title, $comment );
		} else {
			$log->addEntry( 'unapprove', $title, $comment );
		}
	}
}

class Stableversions extends SpecialPage
{

    function Stableversions() {
        SpecialPage::SpecialPage('Stableversions');
    }

    function execute( $par ) {
        global $wgRequest, $wgUser;

		$this->setHeaders();
		// Our target page
		$this->page = $wgRequest->getText( 'page' );
		// Revision ID
		$this->oldid = $wgRequest->getIntOrNull( 'oldid' );
		
		$this->skin = $wgUser->getSkin();
		
		if( $this->oldid ) {
			$this->showStableRevision( $wgRequest );
		} else if( $this->page ) {
			$this->showStableList( $wgRequest );
		} else {
			$this->showForm( $wgRequest );
		}
	}
	
	function showForm( $wgRequest ) {
		global $wgOut, $wgTitle, $wgScript;
	
		$encPage = $this->page;
		$encId = $this->oldid;
		
		$form = "<form name='stableversions' action='$wgScript' method='get'>";
		$form .= "<fieldset><legend>".wfMsg('stableversions-leg1')."</legend>";
		$form .= "<table><tr>";
		$form .= "<td>".Xml::hidden( 'title', $wgTitle->getPrefixedText() )."</td>";
		$form .= "<td>".wfMsgHtml("stableversions-page").":</td>";
		$form .= "<td>".Xml::input('page', 50, $encPage, array( 'id' => 'page' ) )."</td>";
		$form .= "<td>".wfSubmitButton( wfMsgHtml( 'go' ) )."</td>";
		$form .= "</tr></table>";
		$form .= "</fieldset></form>\n";
		
		$form .= "<form name='stableversion' action='$wgScript' method='get'>";
		$form .= "<fieldset><legend>".wfMsg('stableversions-leg2')."</legend>";
		$form .= "<table><tr>";
		$form .= "<td>".Xml::hidden( 'title', $wgTitle->getPrefixedDBkey() )."</td>";
		$form .= "<td>".wfMsgHtml("stableversions-rev").":</td>";
		$form .= "<td>".Xml::input('oldid', 15, $encId, array( 'id' => 'oldid' ) )."</td>";
		$form .= "<td>".wfSubmitButton( wfMsgHtml( 'go' ) )."</td>";
		$form .= "</tr></table>";
		$form .= "</fieldset></form>";
		
		$wgOut->addHTML( $form );
	}
	
	function showStableRevision( $frev ) {
		global $wgParser, $wgLang, $wgUser, $wgOut, $wgTitle, $wgFlaggedRevs;
			
		// Get the revision
		$frev = FlaggedRevs::getFlaggedRev( $this->oldid );
		// Revision must exists
		if( is_null($frev) ) {
			$wgOut->showErrorPage( 'notargettitle', 'revnotfoundtext' );
			return;
		}
		$page = Title::makeTitle( $frev->fr_namespace, $frev->fr_title );
		
		$wgOut->setPagetitle( $page->getPrefixedText() );
		// Get flags and date
		$flags = $wgFlaggedRevs->getFlagsForRevision( $frev->fr_rev_id );
		$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $frev->fr_timestamp), true );
       	// We will be looking at the reviewed revision...
       	$tag = wfMsgExt('revreview-static', array('parseinline'), urlencode($page->getPrefixedText()), $time, $page->getPrefixedText());
		$tag .= ' <a id="mwrevisiontoggle" style="display:none;" href="javascript:toggleRevRatings()">' . wfMsg('revreview-toggle') . '</a>';
			$tag .= '<span id="mwrevisionratings" style="display:block;">' . 
				wfMsg('revreview-oldrating') . $wgFlaggedRevs->addTagRatings( $flags ) . 
				'</span>';
		// Parse the text...
		$text = $wgFlaggedRevs->getFlaggedRevText( $this->oldid );
		$options = ParserOptions::newFromUser($wgUser);
       	$parserOutput = $wgFlaggedRevs->parseStableText( $page, $text, $this->oldid, $options );
		$notes = $wgFlaggedRevs->ReviewNotes( $frev );
		// Set the new body HTML, place a tag on top
		$wgOut->addHTML('<div id="mwrevisiontag" class="flaggedrevs_notice plainlinks">'.$tag.'</div>' . $parserOutput->getText() . $notes);
       	// Show stable categories and interwiki links only
       	$wgOut->mCategoryLinks = array();
       	$wgOut->addCategoryLinks( $parserOutput->getCategories() );
       	$wgOut->mLanguageLinks = array();
       	$wgOut->addLanguageLinks( $parserOutput->getLanguageLinks() );
	}
	
	function showStableList() {
		global $wgOut, $wgUser, $wgLang;
		
		// Must be a valid page/Id
		$page = Title::newFromUrl( $this->page );
		if( is_null($page) || !$page->isContentPage() ) {
			$wgOut->showErrorPage('notargettitle', 'allpagesbadtitle' );
			return;
		}
		$article = new Article( $page );
		if( !$article ) {
			$wgOut->showErrorPage('notargettitle', 'allpagesbadtitle' );
			return;
		}
		$pager = new StableRevisionsPager( $this, array(), $page->getNamespace(), $page->getDBkey() );	
		if( $pager->getNumRows() ) {
			$wgOut->addHTML( wfMsgExt('stableversions-list', array('parse'), $page->getPrefixedText() ) );
			$wgOut->addHTML( $pager->getNavigationBar() );
			$wgOut->addHTML( "<ul>" . $pager->getBody() . "</ul>" );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHTML( wfMsgExt('stableversions-none', array('parse'), $page->getPrefixedText() ) );
		}
	}
	
	function formatRow( $row ) {
		global $wgLang, $wgUser;
	
		$SV = SpecialPage::getTitleFor( 'Stableversions' );
		$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $row->rev_timestamp), true );
		$ftime = $wgLang->timeanddate( wfTimestamp(TS_MW, $row->fr_timestamp), true );
		$review = wfMsg( 'stableversions-review', $ftime );
		
		$lev = ( $row->fr_quality >=1 ) ? wfMsg('hist-quality') : wfMsg('hist-stable');
		$link = $this->skin->makeKnownLinkObj( $SV, $time, 'oldid='.$row->fr_rev_id );
		
		return '<li>'.$link.' ('.$review.') <strong>'.$lev.'</strong></li>';	
	}
}

/**
 * Query to list out stable versions for a page
 */
class StableRevisionsPager extends ReverseChronologicalPager {
	public $mForm, $mConds;

	function __construct( $form, $conds = array(), $namespace, $title ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->namespace = $namespace;
		$this->title = $title;
		parent::__construct();
	}
	
	function formatRow( $row ) {
		$block = new Block;
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$conds = $this->mConds;
		$conds["fr_namespace"] = $this->namespace;
		$conds["fr_title"] = $this->title;
		$conds[] = "fr_rev_id = rev_id";
		$conds["rev_deleted"] = 0;
		return array(
			'tables' => array('flaggedrevs','revision'),
			'fields' => 'fr_rev_id,fr_timestamp,rev_timestamp,fr_quality',
			'conds' => $conds
		);
	}

	function getIndexField() {
		return 'fr_rev_id';
	}
}

/**
 * Special page to list unreviewed pages
 */
class Unreviewedpages extends SpecialPage
{

    function Unreviewedpages() {
        SpecialPage::SpecialPage('Unreviewedpages');
    }

    function execute( $par ) {
        global $wgRequest;

		$this->setHeaders();
		
		$this->showList( $wgRequest );
	}
	
	function showList( $wgRequest ) {
		global $wgOut, $wgUser, $wgScript, $wgTitle;
		
		$namespace = $wgRequest->getIntOrNull( 'namespace' );
		$nonquality = $wgRequest->getVal( 'includenonquality' );
		
		$action = htmlspecialchars( $wgScript );
		$wgOut->addHTML( "<form action=\"$action\" method=\"get\">\n" .
			'<fieldset><legend>' . wfMsg('viewunreviewed') . '</legend>' .
			Xml::label( wfMsgHtml("namespace"), 'namespace' ) . ' ' .
			$this->getNamespaceMenu( $namespace ) . "\n" .
			Xml::submitButton( wfMsg( 'allpagessubmit' ) ) . "\n" .
			'<p>' . Xml::check( 'includenonquality', $nonquality, array('id' => 'includenonquality') ) . 
			' ' . Xml::label( wfMsgHtml("included-nonquality"), 'includenonquality' ) . "</p>\n" .
			Xml::hidden( 'title', $wgTitle->getPrefixedText() ) .
			"</fieldset></form>");
		
		list( $limit, $offset ) = wfCheckLimits();
		
		$sdr = new UnreviewedPagesPage( $namespace, $nonquality );
		$sdr->doQuery( $offset, $limit );
	}
	
	function getNamespaceMenu( $selected=NULL, $allnamespaces = null, $includehidden=false ) {
		global $wgContLang, $wgContentNamespaces;
		
		$selector = "<label for='namespace'>" . wfMsgHtml('namespace') . "</label>";
		if( $selected !== '' ) {
			if( is_null( $selected ) ) {
				// No namespace selected; let exact match work without hitting Main
				$selected = '';
			} else {
				// Let input be numeric strings without breaking the empty match.
				$selected = intval( $selected );
			}
		}
		$s = "\n<select id='namespace' name='namespace' class='namespaceselector'>\n";
		$arr = $wgContLang->getFormattedNamespaces();
		if( !is_null($allnamespaces) ) {
			$arr = array($allnamespaces => wfMsg('namespacesall')) + $arr;
		}

		$s .= "\t" . Xml::element("option", array("value" => "all"), "all") . "\n";
		
		foreach($arr as $index => $name) {
			# Content only
			if($index < NS_MAIN || !in_array($index, $wgContentNamespaces) ) 
				continue;

			$name = $index !== 0 ? $name : wfMsg('blanknamespace');

			if($index === $selected) {
				$s .= "\t" . Xml::element("option",
						array("value" => $index, "selected" => "selected"),
						$name) . "\n";
			} else {
				$s .= "\t" . Xml::element("option", array("value" => $index), $name) . "\n";
			}
		}
		$s .= "</select>\n";
		return $s;
	}
}

/**
 * Query to list out unreviewed pages
 */
class UnreviewedPagesPage extends PageQueryPage {
	
	function __construct( $namespace=NULL, $nonquality=false ) {
		$this->namespace = $namespace;
		$this->nonquality = $nonquality;
	}
	
	function getName() {
		return 'UnreviewedPages';
	}

	function isExpensive( ) { return true; }
	function isSyndicated() { return false; }

	function getPageHeader( ) {
		return '<p>'.wfMsg("unreviewed-list")."</p>\n";
	}

	function getSQLText( &$dbr, $namespace, $includenonquality = false ) {
		global $wgContentNamespaces;
		
		list( $page, $flaggedrevs ) = $dbr->tableNamesN( 'page', 'flaggedrevs' );
		
		# Must be a content page...
		$contentNS = 'page_namespace IN(' . implode(',',$wgContentNamespaces) . ')';
		
		$ns = ($namespace !== null) ? "page_namespace=$namespace" : '1 = 1';
		
		$where = $includenonquality ? '1 = 1' : 'fr_rev_id IS NULL';
		$having = $includenonquality ? 'MAX(fr_quality) < 1' : '1 = 1';
		
		$sql = 
			"SELECT page_namespace,page_title,page_len AS size 
			FROM $page 
			LEFT JOIN $flaggedrevs ON (fr_namespace = page_namespace AND fr_title = page_title) 
			WHERE page_is_redirect=0 AND $ns AND $contentNS AND ($where) 
			GROUP BY page_id HAVING $having ";
		return $sql;
	}
	
	function getSQL() {
		$dbr = wfGetDB( DB_SLAVE );
		return $this->getSQLText( $dbr, $this->namespace, $this->nonquality );
	}

	function getOrder() {
		return 'ORDER BY page_id DESC';
	}

	function formatResult( $skin, $result ) {
		global $wgLang;
		
		$fname = 'UnreviewedPagesPage::formatResult';
		$title = Title::makeTitle( $result->page_namespace, $result->page_title );
		$link = $skin->makeKnownLinkObj( $title );
		$stxt = '';
		if(!is_null($size = $result->size)) {
			if($size == 0)
				$stxt = ' <small>' . wfMsgHtml('historyempty') . '</small>';
			else
				$stxt = ' <small>' . wfMsgHtml('historysize', $wgLang->formatNum( $size ) ) . '</small>';
		}

		return( "{$link} {$stxt}" );
	}
}
