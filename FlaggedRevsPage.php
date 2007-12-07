<?php
#(c) Aaron Schulz, Joerg Baach, 2007 GPL

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

class Revisionreview extends SpecialPage
{

    function __construct() {
        SpecialPage::SpecialPage( 'Revisionreview', 'review' );
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgOut;

		$confirm = $wgRequest->wasPosted() && $wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );

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
		// Basic patrolling
		$this->patrolonly = $wgRequest->getBool( 'patrolonly' );
		$this->rcid = $wgRequest->getIntOrNull( 'rcid' );
		if( $this->patrolonly && $this->rcid ) {
			$this->markPatrolled();
			return;
		}
		
		global $wgFlaggedRevTags, $wgFlaggedRevValues;
		// Our target page
		$this->target = $wgRequest->getText( 'target' );
		$this->page = Title::newFromUrl( $this->target );
		// Revision ID
		$this->oldid = $wgRequest->getIntOrNull( 'oldid' );
		if( !$this->target || !$this->oldid || !FlaggedRevs::isPageReviewable( $this->page ) ) {
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
		$this->notes = (FlaggedRevs::allowComments() && $wgUser->isAllowed('validate')) ? 
			$wgRequest->getText('wpNotes') : '';
		// Get the revision's current flags, if any
		$this->oflags = FlaggedRevs::getRevisionTags( $this->oldid );
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
			// Users cannot review to beyond their rights level
			} else if( !$this->userCan( $tag, $this->dims[$tag] ) ) {
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
	
	function markPatrolled() {
		global $wgOut;
	
		RecentChange::markPatrolled( $this->rcid );
		PatrolLog::record( $this->rcid );
		# Inform the user
		$wgOut->setPageTitle( wfMsg( 'markedaspatrolled' ) );
		$wgOut->addWikiText( wfMsgNoTrans( 'markedaspatrolledtext' ) );
		$wgOut->returnToMain( false, SpecialPage::getTitleFor( 'Recentchanges' ) );
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
			Xml::hidden( 'wpEditToken', $wgUser->editToken() ),
			Xml::hidden( 'target', $this->page->getPrefixedText() ),
			Xml::hidden( 'oldid', $this->oldid ) );	
		
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
			"<textarea tabindex='1' name='wpNotes' id='wpNotes' rows='3' cols='80' style='width:100%'>" .
			htmlspecialchars( $this->notes ) .
			"</textarea>" .	
			"</fieldset>";
		}
       	// Not much to say unless you are a validator
		if( $wgUser->isAllowed( 'validate' ) )
			$form .= '<p>'.Xml::inputLabel( wfMsg( 'revreview-log' ), 'wpReason', 'wpReason', 60 ).'</p>';
		
		$form .= '<p>'.Xml::submitButton( wfMsg( 'revreview-submit' ) ).'</p>';
		
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
	 * @return string
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
		global $wgOut, $wgUser, $wgFlaggedRevTags;
		# If all values are set to zero, this has been unapproved
		$approved = empty($wgFlaggedRevTags);
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
			$frev = FlaggedRevs::getFlaggedRev( $this->page, $this->oldid );
			// If we can't find this flagged rev, return to page???
			if( is_null($frev) ) {
				$wgOut->redirect( $this->page->getFullUrl() );
				return;
			}
		}
		
		$success = $approved ? 
			$this->approveRevision( $rev, $this->notes ) : $this->unapproveRevision( $frev );
		// Return to our page			
		if( $success ) {
			if( $request->getCheck( 'wpWatchthis' ) ) {
				$wgUser->addWatch( $this->page );
			} else {
				$wgUser->removeWatch( $this->page );
			}
        	$wgOut->redirect( $this->page->getFullUrl() );
		} else {
			$wgOut->showErrorPage( 'internalerror', 'revreview-changed' );
		}
	}

	/**
	 * Adds or updates the flagged revision table for this page/id set
	 * @param Revision $rev
	 * @param string $notes
	 */
	function approveRevision( $rev, $notes='' ) {
		global $wgUser, $wgFlaggedRevsWatch, $wgParser;
		// Get the page this corresponds to
		$title = $rev->getTitle();
		
		$quality = 0;
		if( FlaggedRevs::isQuality($this->dims) ) {
			$quality = FlaggedRevs::isPristine($this->dims) ? 2 : 1;
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
		// Our template version pointers
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
		// Our image version pointers
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
				'fi_img_sha1' => $key
			);
		}
		
		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin();
		// Clear out any previous garbage.
		// We want to be able to use this for tracking...
		$dbw->delete( 'flaggedtemplates', 
			array('ft_rev_id' => $rev->getId() ),
			__METHOD__ );
		$dbw->delete( 'flaggedimages', 
			array('fi_rev_id' => $rev->getId() ),
			__METHOD__ );
		// Update our versioning params
		if( !empty( $tmpset ) ) {
			$dbw->insert( 'flaggedtemplates', $tmpset, __METHOD__ );
		}
		if( !empty( $imgset ) ) {
			$dbw->insert( 'flaggedimages', $imgset, __METHOD__ );
		}
        // Get the page text and resolve all templates
        list($fulltext,$complete) = FlaggedRevs::expandText( $rev->getText(), $rev->getTitle(), $rev->getId() );
        if( !$complete ) {
        	$dbw->rollback(); // All versions must be specified, 0 for none
        	return false;
        }
        # Compress $fulltext, passed by reference
        $textFlags = FlaggedRevs::compressText( $fulltext );
		// Our review entry
 		$revset = array(
 			'fr_rev_id'    => $rev->getId(),
 			'fr_page_id'   => $title->getArticleID(),
			'fr_user'      => $wgUser->getId(),
			'fr_timestamp' => wfTimestampNow(),
			'fr_comment'   => $notes,
			'fr_quality'   => $quality,
			'fr_text'      => $fulltext, // Store expanded text for speed
			'fr_flags'     => $textFlags
		);
		// Update flagged revisions table
		$dbw->replace( 'flaggedrevs', array( array('fr_page_id','fr_rev_id') ), $revset, __METHOD__ );
		// Set all of our flags
		$dbw->replace( 'flaggedrevtags', array( array('frt_rev_id','frt_dimension') ), $flagset, __METHOD__ );
		// Mark as patrolled
		$dbw->update( 'recentchanges',
			array( 'rc_patrolled' => 1 ),
			array( 'rc_this_oldid' => $rev->getId(),
				'rc_timestamp' => $dbw->timestamp( $rev->getTimestamp() ) ),
			__METHOD__ 
		);
		$dbw->commit();
		
		// Update the article review log
		$this->updateLog( $this->page, $this->dims, $this->comment, $this->oldid, true );
		
		$article = new Article( $this->page );
		// Update the links tables as the stable version may now be the default page...
		$parserCache = ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if( $poutput==false ) {
			$text = $article->getContent();
			$options = ParserOptions::newFromUser($wgUser);
			$options->setTidy(true);
			$poutput = $wgParser->parse( $text, $article->mTitle, $options );
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
		// Delete from flaggedrevs table
		$dbw->delete( 'flaggedrevs', 
			array( 'fr_page_id' => $row->fr_page_id, 
				'fr_rev_id' => $row->fr_rev_id ) );
		// Wipe versioning params
		$dbw->delete( 'flaggedtemplates', array( 'ft_rev_id' => $row->fr_rev_id ) );
		$dbw->delete( 'flaggedimages', array( 'fi_rev_id' => $row->fr_rev_id ) );
		// And the flags...
		$dbw->delete( 'flaggedrevtags', array( 'frt_rev_id' => $row->fr_rev_id ) );
		
		// Update the article review log
		$this->updateLog( $this->page, $this->dims, $this->comment, $this->oldid, false );
		
		$article = new Article( $this->page );
		// Update the links tables as a new stable version
		// may now be the default page.
		$parserCache = ParserCache::singleton();
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
	 * @param bool $RC, add to recentchanges (kind of spammy)
	 */	
	public static function updateLog( $title, $dimensions, $comment, $oldid, $approve, $RC=false ) {
		$log = new LogPage( 'review', $RC );
		// ID, accuracy, depth, style
		$ratings = array();
		foreach( $dimensions as $quality => $level ) {
			$ratings[] = wfMsg( "revreview-$quality" ) . ": " . wfMsg("revreview-$quality-$level");
		}
		$rating = ($approve && !empty($ratings) ) ? ' [' . implode(', ',$ratings). ']' : '';
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

    function __construct() {
        SpecialPage::SpecialPage('Stableversions');
    }

    function execute( $par ) {
        global $wgRequest, $wgUser;

		$this->setHeaders();
		$this->skin = $wgUser->getSkin();
		# Our target page
		$this->target = $wgRequest->getText( 'page' );
		$this->page = Title::newFromUrl( $this->target );
		# Revision ID
		$this->oldid = $wgRequest->getIntOrNull( 'oldid' );
		# We need a page...
		if( is_null($this->page) ) {
			$this->showForm( $wgRequest );
			return;
		}
		
		if( $this->oldid ) {
			$this->showStableRevision( $wgRequest );
		} else {
			$this->showForm( $wgRequest );
			$this->showStableList( $wgRequest );
		}
	}
	
	function showForm( $wgRequest ) {
		global $wgOut, $wgTitle, $wgScript;
	
		$pageName = str_replace( '_', ' ', $this->target );
		
		$form = Xml::openElement( 'form',
			array( 'name' => 'stableversions', 'action' => $wgScript, 'method' => 'get' ) );
		$form .= "<fieldset><legend>".wfMsg('stableversions-leg1')."</legend>";
		$form .= "<table><tr>";
		$form .= "<td>".Xml::hidden( 'title', $wgTitle->getPrefixedText() )."</td>";
		$form .= "<td>".wfMsgHtml("stableversions-page")."</td>";
		$form .= "<td>".Xml::input('page', 40, $pageName, array( 'id' => 'page' ) )."</td>";
		$form .= "<td>".Xml::submitButton( wfMsg( 'go' ) )."</td>";
		$form .= "</tr></table>";
		$form .= "</fieldset></form>\n";
		
		$wgOut->addHTML( $form );
	}
	
	function showStableList() {
		global $wgOut, $wgUser, $wgLang;
		// Must be a content page
		if( !FlaggedRevs::isPageReviewable( $this->page ) ) {
			$wgOut->addHTML( wfMsgExt('stableversions-none', array('parse'), 
				$this->page->getPrefixedText() ) );
			return;
		}
		$pager = new StableRevisionsPager( $this, array(), $this->page->getNamespace(), $this->page );	
		if( $pager->getNumRows() ) {
			$wgOut->addHTML( wfMsgExt('stableversions-list', array('parse'), 
				$this->page->getPrefixedText() ) );
			$wgOut->addHTML( $pager->getNavigationBar() );
			$wgOut->addHTML( "<ul>" . $pager->getBody() . "</ul>" );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHTML( wfMsgExt('stableversions-none', array('parse'), 
				$this->page->getPrefixedText() ) );
		}
	}
	
	function showStableRevision( $frev ) {
		global $wgParser, $wgLang, $wgUser, $wgOut, $wgFlaggedArticle;
		// Get the revision
		$frev = FlaggedRevs::getFlaggedRev( $this->page, $this->oldid, true );
		// Revision must exists
		if( is_null($frev) ) {
			$wgOut->showErrorPage( 'notargettitle', 'revnotfoundtext' );
			return;
		}
		// Get flags and date
		$flags = $wgFlaggedArticle->getFlagsForRevision( $frev->fr_rev_id );
		$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $frev->fr_timestamp), true );
       	// We will be looking at the reviewed revision...
       	$tag = wfMsgExt( 'revreview-static', array('parseinline'), 
		   urlencode($this->page->getPrefixedText()), $time, $this->page->getPrefixedText() ) .
			' <a id="mwrevisiontoggle" style="display:none;" href="javascript:toggleRevRatings()">' .
			wfMsg('revreview-toggle') . '</a>' .
			'<span id="mwrevisionratings" style="display:block;">' .
			wfMsg('revreview-oldrating') . $wgFlaggedArticle->addTagRatings( $flags ) .
			'</span>';
		// Parse the text...
		$article = new Article( $this->page );
		
		$text = FlaggedRevs::uncompressText( $frev->fr_text, $frev->fr_flags );
		
       	$parserOutput = FlaggedRevs::parseStableText( $article, $text, $this->oldid );
		
		wfRunHooks( 'OutputPageParserOutput', array( &$wgOut, $parserOutput ) );
		// Set the new body HTML, place a tag on top
		$wgOut->addHTML( '<div id="mwrevisiontag" class="flaggedrevs_notice plainlinks">' .
			$tag . '</div>' . $parserOutput->getText() . $wgFlaggedArticle->ReviewNotes( $frev ) );
       	// Show stable categories and interwiki links only
       	$wgOut->mCategoryLinks = array();
       	$wgOut->addCategoryLinks( $parserOutput->getCategories() );
       	$wgOut->mLanguageLinks = array();
       	$wgOut->addLanguageLinks( $parserOutput->getLanguageLinks() );
	}
	
	function formatRow( $row ) {
		global $wgLang, $wgUser;
	
		$SV = SpecialPage::getTitleFor( 'Stableversions' );
		
		$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $row->rev_timestamp), true );
		$ftime = $wgLang->timeanddate( wfTimestamp(TS_MW, $row->fr_timestamp), true );
		$review = wfMsg( 'stableversions-review', $ftime, 
			$this->skin->userLink( $row->fr_user, $row->user_name ) .
			' ' . $this->skin->userToolLinks( $row->fr_user, $row->user_name ) );
		
		$lev = ( $row->fr_quality >=1 ) ? wfMsg('hist-quality') : wfMsg('hist-stable');
		$link = $this->skin->makeKnownLinkObj( $SV, $time, 
			'page='.urlencode( $this->page->getPrefixedText() ) . '&oldid='.$row->fr_rev_id );
		
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
		$this->pageID = $title->getArticleID();
		parent::__construct();
	}
	
	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		global $wgFlaggedRevsNamespaces;
	
		$conds = $this->mConds;
		// Must be in a reviewable namespace
		if( !in_array($this->namespace, $wgFlaggedRevsNamespaces) ) {
			$conds[] = "1 = 0";
		}
		$conds["fr_page_id"] = $this->pageID;
		$conds[] = "fr_rev_id = rev_id";
		$conds[] = "fr_user = user_id";
		$conds[] = 'rev_deleted & '.Revision::DELETED_TEXT.' = 0';
		return array(
			'tables' => array('flaggedrevs','revision','user'),
			'fields' => 'fr_rev_id,fr_timestamp,rev_timestamp,fr_quality,
				fr_user,user_name',
			'conds' => $conds,
			'options' => array('USE INDEX' => 'PRIMARY')
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

    function __construct() {
        SpecialPage::SpecialPage('Unreviewedpages','unreviewedpages');
    }

    function execute( $par ) {
        global $wgRequest;

		$this->setHeaders();
		
		$this->showList( $wgRequest );
	}
	
	function showList( $wgRequest ) {
		global $wgOut, $wgUser, $wgScript, $wgTitle;
		
		$namespace = $wgRequest->getIntOrNull( 'namespace' );
		$showoutdated = $wgRequest->getVal( 'showoutdated' );
		$category = $wgRequest->getVal( 'category' );
		
		$action = htmlspecialchars( $wgScript );
		$wgOut->addHTML( "<form action=\"$action\" method=\"get\">\n" .
			'<fieldset><legend>' . wfMsg('viewunreviewed') . '</legend>' .
			Xml::hidden( 'title', $wgTitle->getPrefixedText() ) .
			'<p>' . Xml::label( wfMsg("namespace"), 'namespace' ) . ' ' .
			FlaggedRevs::getNamespaceMenu( $namespace ) .
			'&nbsp;' . Xml::label( wfMsg("unreviewed-category"), 'category' ) . 
			' ' . Xml::input( 'category', 30, $category, array('id' => 'category') ) . '</p>' .
			'<p>' . Xml::check( 'showoutdated', $showoutdated, array('id' => 'showoutdated') ) . 
			' ' . Xml::label( wfMsg("unreviewed-outdated"), 'showoutdated' ) . "</p>\n" .
			Xml::submitButton( wfMsg( 'allpagessubmit' ) ) . "\n" .
			"</fieldset></form>");
		
		list( $limit, $offset ) = wfCheckLimits();
		
		$sdr = new UnreviewedPagesPage( $namespace, $showoutdated, $category );
		$sdr->doQuery( $offset, $limit );
	}
}

/**
 * Query to list out unreviewed pages
 */
class UnreviewedPagesPage extends PageQueryPage {
	
	function __construct( $namespace, $showOutdated=false, $category=NULL ) {
		$this->namespace = $namespace;
		$this->category = $category;
		$this->showOutdated = $showOutdated;
	}
	
	function getName() {
		return 'UnreviewedPages';
	}
	# Note: updateSpecialPages doesn't support extensions, but this is fast anyway
	function isExpensive( ) { return false; }
	function isSyndicated() { return false; }

	function getPageHeader( ) {
		return '<p>'.wfMsg("unreviewed-list")."</p>\n";
	}

	function getSQLText( &$dbr, $namespace, $showOutdated, $category ) {
		global $wgFlaggedRevsNamespaces;
		
		list($page,$flaggedrevs,$categorylinks) = $dbr->tableNamesN('page','flaggedrevs','categorylinks');
		# Must be a content page...
		if( !is_null($namespace) )
			$namespace = intval($namespace);
		
		if( is_null($namespace) || !in_array($namespace,$wgFlaggedRevsNamespaces) ) {
			$namespace = empty($wgFlaggedRevsNamespaces) ? -1 : $wgFlaggedRevsNamespaces[0];
		}
		# No redirects
		$where = "page_namespace={$namespace} AND page_is_redirect=0 ";
		# We don't like filesorts, so the query methods here will be very different
		if( !$showOutdated ) {
			$where .= "AND page_ext_reviewed IS NULL";
		} else {
			$where .= "AND page_ext_reviewed = 0";
		}
		# Filter by category
		if( $category ) {
			$category = str_replace( ' ', '_', $dbr->strencode($category) );
			$sql = "SELECT page_namespace AS ns,page_title AS title,page_len,page_ext_stable 
			FROM $page FORCE INDEX(ext_namespace_reviewed) 
			RIGHT JOIN $categorylinks ON(cl_from = page_id AND cl_to = '{$category}')";
		} else {
			$sql = "SELECT page_namespace AS ns,page_title AS title,page_len,page_ext_stable 
			FROM $page FORCE INDEX(ext_namespace_reviewed)";
		}
		$sql .= " WHERE ($where) ";
		
		return $sql;
	}
	
	function getSQL() {
		$dbr = wfGetDB( DB_SLAVE );
		return $this->getSQLText( $dbr, $this->namespace, $this->showOutdated, $this->category );
	}

	function getOrder() {
		return 'ORDER BY page_id DESC';
	}
	
	function linkParameters() {
		return array( 'category' => $this->category, 'showoutdated' => $this->showOutdated );
	}

	function formatResult( $skin, $result ) {
		global $wgLang;
		
		$title = Title::makeTitle( $result->ns, $result->title );
		$link = $skin->makeKnownLinkObj( $title );
		$stxt = $review = '';
		if(!is_null($size = $result->page_len)) {
			if($size == 0)
				$stxt = ' <small>' . wfMsgHtml('historyempty') . '</small>';
			else
				$stxt = ' <small>' . wfMsgHtml('historysize', $wgLang->formatNum( $size ) ) . '</small>';
		}
		if( $result->page_ext_stable )
			$review = ' ('.$skin->makeKnownLinkObj( $title, wfMsg('unreviewed-diff'), 
				"diff=cur&oldid={$result->page_ext_stable}&editreview=1" ).')';

		return( "{$link} {$stxt} {$review}" );
	}
}

class Reviewedpages extends SpecialPage
{

    function __construct() {
        SpecialPage::SpecialPage('Reviewedpages');
    }

    function execute( $par ) {
        global $wgRequest, $wgUser;

		$this->setHeaders();
		$this->skin = $wgUser->getSkin();
		# Our target page
		$this->type = $wgRequest->getInt( 'level' );
		$this->namespace = $wgRequest->getInt( 'namespace' );
		
		$this->showForm();
		$this->showPageList();
	}
	
	function showForm() {
		global $wgOut, $wgTitle, $wgScript;
		
		$form = Xml::openElement( 'form',
			array( 'name' => 'reviewedpages', 'action' => $wgScript, 'method' => 'get' ) );
		$form .= "<fieldset><legend>".wfMsg('reviewedpages-leg')."</legend>\n";
		
		$form .= Xml::label( wfMsg("namespace"), 'namespace' ) . ' ' .
			FlaggedRevs::getNamespaceMenu( $this->namespace ) . ' ';
		
		$form .= Xml::openElement( 'select', array('name' => 'level') );
		$form .= Xml::option( wfMsg( "reviewedpages-lev-0" ), 0, $this->type==0 );
		$form .= Xml::option( wfMsg( "reviewedpages-lev-1" ), 1, $this->type==1 );
		$form .= Xml::option( wfMsg( "reviewedpages-lev-2" ), 2, $this->type==2 );
		$form .= Xml::closeElement('select')."\n";

		$form .= " ".Xml::submitButton( wfMsg( 'go' ) );
		$form .= Xml::hidden( 'title', $wgTitle->getPrefixedText() );
		$form .= "</fieldset></form>\n";
		
		$wgOut->addHTML( $form );
	}
	
	function showPageList() {
		global $wgOut, $wgUser, $wgLang;
		
		$pager = new ReviewedPagesPager( $this, array(), $this->type );	
		if( $pager->getNumRows() ) {
			$wgOut->addHTML( wfMsgExt('reviewedpages-list', array('parse') ) );
			$wgOut->addHTML( $pager->getNavigationBar() );
			$wgOut->addHTML( "<ul>" . $pager->getBody() . "</ul>" );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHTML( wfMsgExt('reviewedpages-none', array('parse') ) );
		}
	}
	
	function formatRow( $row ) {
		global $wgLang, $wgUser;
	
		$title = Title::makeTitle( $row->page_namespace, $row->page_title );
		$link = $this->skin->makeKnownLinkObj( $title, $title->getPrefixedText() );
		
		return '<li>'.$link.'</li>';	
	}
}

/**
 * Query to list out stable versions for a page
 */
class ReviewedPagesPager extends ReverseChronologicalPager {
	public $mForm, $mConds;

	function __construct( $form, $conds = array(), $type=0, $namespace=0 ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->type = $type;
		$this->namespace = $namespace;

		parent::__construct();
	}
	
	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		global $wgFlaggedRevsNamespaces;
	
		$conds = $this->mConds;
		// Must be in a reviewable namespace
		if( !in_array($this->namespace, $wgFlaggedRevsNamespaces) ) {
			$conds[] = "1 = 0";
		}
		$conds['page_namespace'] = $this->namespace;
		$conds['page_ext_quality'] = $this->type;
		return array(
			'tables' => array('page'),
			'fields' => 'page_namespace,page_title,page_id',
			'conds'  => $conds,
			'options' => array('USE INDEX' => 'ext_namespace_quality')
		);
	}

	function getIndexField() {
		return 'page_title';
	}
}

class Stabilization extends SpecialPage
{

    function __construct() {
        SpecialPage::SpecialPage('Stabilization','stablesettings');
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgOut;

		$confirm = $wgRequest->wasPosted() &&
			$wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );
		
		$this->isAllowed = $wgUser->isAllowed( 'stablesettings' );
		# Let anyone view, but not submit...
		if( $wgRequest->wasPosted() ) {
			if( $wgUser->isBlocked( !$confirm ) ) {
				$wgOut->blockedPage();
				return;
			} else if( !$this->isAllowed ) {
				$wgOut->permissionRequired( 'stablesettings' );
				return;
			} else if( wfReadOnly() ) {
				$wgOut->readOnlyPage();
				return;
			}
		}

		$this->setHeaders();
		$this->skin = $wgUser->getSkin();
		# Our target page
		$this->target = $wgRequest->getText( 'page' );
		$this->page = Title::newFromUrl( $this->target );
		# Params
		$this->select = $wgRequest->getInt( 'select' );
		$this->override = intval( $wgRequest->getBool( 'override' ) );
		$this->comment = $wgRequest->getVal( 'wpReason' );
		
		$isValid = true;
		# Only 0 or 1
		if( $this->select && ($this->select !==0 && $this->select !==1) ) {
			$isValid = false;
		}
		
		# We need a page...
		if( is_null($this->page) ) {
			$isValid = false;
		} else if( !$this->page->exists() ) {
			$wgOut->addHTML( wfMsgExt( 'stabilization-notexists', array('parseinline'),
				$this->page->getPrefixedText() ) );
			$isValid = false;
		} else if( !FlaggedRevs::isPageReviewable( $this->page ) ) {
			$wgOut->addHTML( wfMsgExt( 'stabilization-notcontent', array('parseinline'),
				$this->page->getPrefixedText() ) );
			$isValid = false;
		}
		
		if( !$isValid ) {
			$this->showForm();
			return;
		}
		
		if( $confirm ) {
			$this->submit();
		} else {
			$this->showSettings();
		}
	}
	
	function showForm() {
		global $wgOut, $wgTitle, $wgScript;
	
		$pageName = str_replace( '_', ' ', $this->target );
		$form = Xml::openElement( 'form', array( 'name' => 'stablization', 'action' => $wgScript, 'method' => 'get' ) );
		$form .= "<fieldset><legend>".wfMsg('stabilization-leg')."</legend>";
		$form .= "<table><tr>";
		$form .= "<td>".Xml::hidden( 'title', $wgTitle->getPrefixedText() )."</td>";
		$form .= "<td>".wfMsgHtml("stabilization-page")."</td>";
		$form .= "<td>".Xml::input('page', 40, $pageName, array( 'id' => 'page' ) )."</td>";
		$form .= "<td>".Xml::submitButton( wfMsg( 'go' ) )."</td>";
		$form .= "</tr></table>";
		$form .= "</fieldset></form>\n";
		
		$wgOut->addHTML( $form );
	}
	
	function showSettings() {
		global $wgOut, $wgScript, $wgTitle, $wgUser;
		// Must be a content page
		if( !FlaggedRevs::isPageReviewable( $this->page ) ) {
			$wgOut->addHTML( wfMsgExt('stableversions-none', array('parse'), 
				$this->page->getPrefixedText() ) );
			return;
		}
		// Get visiblity settings...
		$config = FlaggedRevs::getPageVisibilitySettings( $this->page, true );
		$selectSetting = $this->select ? $this->select : $config['select'];
		$overrideSetting = $this->override ? $this->override : $config['override'];
		
		if( !$this->isAllowed ) {
			$form = '<p>'.wfMsgExt( 'stabilization-perm', array('parse'), $this->page->getPrefixedText() ).'</p>';
			$off = array('disabled' => 'true');
		} else {
			$form = wfMsgExt( 'stabilization-text', array('parse'), $this->page->getPrefixedText() );
			$off = array();
		}
		
		$form .= Xml::openElement( 'form',
			array( 'name' => 'stabilization', 'action' => $wgScript, 'method' => 'post' ) );
		
		$form .= "<fieldset><legend>".wfMsg('stabilization-def')."</legend>";
		$form .= "<table><tr>";
		$form .= "<td>".Xml::radio( 'override', 1, (1==$overrideSetting), array('id' => 'default1') + $off)."</td>";
		$form .= "<td>".Xml::label( wfMsg('stabilization-def1'), 'default1' )."</td>";
		$form .= "</tr><tr>";
		$form .= "<td>".Xml::radio( 'override', 0, (0==$overrideSetting), array('id' => 'default2') + $off)."</td>";
		$form .= "<td>".Xml::label( wfMsg('stabilization-def2'), 'default2' )."</td>";
		$form .= "</tr></table></fieldset>";
		
		$form .= "<fieldset><legend>".wfMsg('stabilization-select')."</legend>";
		$form .= "<table><tr>";
		$form .= "<td>".Xml::radio( 'select', 0, (0==$selectSetting), array('id' => 'select1') + $off )."</td>";
		$form .= "<td>".Xml::label( wfMsg('stabilization-select1'), 'select1' )."</td>";
		$form .= "</tr><tr>";
		$form .= "<td>".Xml::radio( 'select', 1, (1==$selectSetting), array('id' => 'select2') + $off )."</td>";
		$form .= "<td>".Xml::label( wfMsg('stabilization-select2'), 'select2' )."</td>";
		$form .= "</tr></table></fieldset>";
		
		if( $this->isAllowed ) {
			$form .= Xml::hidden('title', $wgTitle->getPrefixedText() );
			$form .= Xml::hidden('page', $this->page->getPrefixedText() );
			$form .= Xml::hidden( 'wpEditToken', $wgUser->editToken() );
		
			$form .= '<p>'.Xml::inputLabel( wfMsg( 'revreview-log' ), 'wpReason', 'wpReason', 60 ).'</p>';
			$form .= Xml::submitButton( wfMsg( 'stabilization-submit' ) );
		}
		$form .= '</form>';
		
		$wgOut->addHTML( $form );
		
		$wgOut->addHtml( Xml::element( 'h2', NULL, htmlspecialchars( LogPage::logName( 'stable' ) ) ) );
		$logViewer = new LogViewer( 
			new LogReader( new FauxRequest( 
				array( 'page' => $this->page->getPrefixedText(), 'type' => 'stable' ) ) ) );
		$logViewer->showList( $wgOut );
	}
	
	function submit() {
		global $wgOut, $wgUser, $wgParser, $wgFlaggedRevsOverride;
		
		$changed = $reset = false;
		
		$dbw = wfGetDB( DB_MASTER );
		# Get current config
		$row = $dbw->selectRow( 'flaggedpage_config', 
			array( 'fpc_select', 'fpc_override' ),
			array( 'fpc_page_id' => $this->page->getArticleID() ),
			__METHOD__ );
		# If setting to site default values, erase the row if there is one
		if( $row && $this->select==0 && $this->override==$wgFlaggedRevsOverride ) {
			$reset = true;
			$changed = true;
			$dbw->delete( 'flaggedpage_config',
				array( 'fpc_page_id' => $this->page->getArticleID() ),
				__METHOD__ );
		# Otherwise, add a row unless we are just setting it as the site default
		} else if( $this->select !=0 || $this->override !=$wgFlaggedRevsOverride ) {
			$changed = true;
			$dbw->replace( 'flaggedpage_config',
				array( 'fpc_page_id' ),
				array( 'fpc_page_id' => $this->page->getArticleID(),
					'fpc_select' => $this->select,
					'fpc_override' => $this->override ),
				__METHOD__ );
		}
		# Log if changed
		# @FIXME: do this better
		if( $changed ) {
			$log = new LogPage( 'stable' );
			// ID, accuracy, depth, style
			$set = array();
			$set[] = wfMsg( "stabilization-sel-short" ) . ": " . 
				wfMsg("stabilization-sel-short-{$this->select}");
			$set[] = wfMsg( "stabilization-def-short" ) . ": " . 
				wfMsg("stabilization-def-short-{$this->override}");
			
			$settings = ' [' . implode(', ',$set). ']';
			// Append comment with settings
			$comment = ($this->comment) ? "{$this->comment}$settings" : "$settings";
			
			if( $reset )
				$log->addEntry( 'reset', $this->page, $this->comment );
			else
				$log->addEntry( 'config', $this->page, $comment );
		}
		
    	$article = new Article( $this->page );
		# Update the links tables as the stable version may now be the default page...
		$parserCache = ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if( $poutput==false ) {
			$text = $article->getContent();
			$poutput = $wgParser->parse($text, $article->mTitle, ParserOptions::newFromUser($wgUser));
			# Might as well save the cache while we're at it
			$parserCache->save( $poutput, $article, $wgUser );
		}
		$u = new LinksUpdate( $article->mTitle, $poutput );
		$u->doUpdate(); // this will trigger our hook to add stable links too...
		
		# Success message
		$wgOut->addHTML( wfMsgExt('stabilization-success',array('parse'),
			$this->page->getPrefixedText() ) );
	}
}
