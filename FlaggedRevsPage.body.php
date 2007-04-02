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
        global $wgRequest, $wgUser, $wgOut, $wgFlaggedRevComments, $wgFlaggedRevTags;
        
		if( !$wgUser->isAllowed( 'review' ) ) {
			$wgOut->permissionRequired( 'review' );
			return;
		}

		$this->setHeaders();
		// Our target page
		$this->target = $wgRequest->getText( 'target' );
		// Revision ID
		$this->oldid = $wgRequest->getIntOrNull( 'oldid' );
		
		// Must be a valid page/Id
		$this->page = Title::newFromUrl( $this->target );
		if( is_null($this->page) || is_null($this->oldid) || !$this->page->isContentPage() ) {
			$wgOut->showErrorPage('notargettitle', 'notargettext' );
			return;
		}
		
		// Log comment
		$this->comment = $wgRequest->getText( 'wpReason' );
		// Additional notes
		$this->notes = ($wgFlaggedRevComments) ? $wgRequest->getText('wpNotes') : '';
		// Get the revision's current flags, if any
		$this->oflags = FlaggedRevs::getFlagsForRevision( $this->oldid );
		// Get our accuracy/quality dimensions
		$this->dims = array();
		foreach ( array_keys($wgFlaggedRevTags) as $tag ) {
			$this->dims[$tag] = $wgRequest->getIntOrNull( "wp$tag" );
			// Must be greater than zero
			if ( $this->dims[$tag] < 0 ) {
				$wgOut->showErrorPage('notargettitle', 'notargettext' );
				return;
			}
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
		if( $wgRequest->wasPosted() ) {
			$this->submit( $wgRequest );
		} else {
			$this->showRevision( $wgRequest );
		}
	}
	
	/**
	 * @param string $tag
	 * @param int $val
	 * Returns true if a user can do something
	 */	
	function userCan( $tag, $value ) {
		global $wgFlagRestrictions, $wgUser;
		
		if ( !isset($wgFlagRestrictions[$tag]) )
			return true;
		// Validators always have full access
		if ( $wgUser->isAllowed('validate') )
			return true;
		// Check if this user has any right that lets him/her set
		// up to this particular value
		foreach ( $wgFlagRestrictions[$tag] as $right => $level ) {
			if ( $value <= $level && $wgUser->isAllowed($right) ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @param webrequest $request
	 */
	function showRevision( $request ) {
		global $wgOut, $wgUser, $wgTitle, $wgFlaggedRevComments, $wgFlaggedRevTags, $wgFlaggedRevValues;
		
		$wgOut->addWikiText( wfMsgExt( 'revreview-selected', array('parsemag'), $this->page->getPrefixedText() ) );
		
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
		
		$wgOut->addWikiText( wfMsg('revreview-text') );
		$formradios = array();
		// Dynamically contruct our radio options
		foreach ( array_keys($wgFlaggedRevTags) as $tag ) {
			$formradios[$tag] = array();
			for ($i=0; $i <= $wgFlaggedRevValues; $i++) {
				$formradios[$tag][] = array( "revreview-$tag-$i", "wp$tag", $i );
			}
		}
		$items = array(
			wfInputLabel( wfMsgHtml( 'revreview-log' ), 'wpReason', 'wpReason', 60 ),
			wfSubmitButton( wfMsgHtml( 'revreview-submit' ) ) );
		$hidden = array(
			wfHidden( 'wpEditToken', $wgUser->editToken() ),
			wfHidden( 'target', $this->page->getPrefixedText() ),
			wfHidden( 'oldid', $this->oldid ) );	
		
		$action = $wgTitle->escapeLocalUrl( 'action=submit' );		
		$form = "<form name='revisionreview' action='$action' method='post'>";
		$form .= '<fieldset><legend>' . wfMsgHtml( 'revreview-legend' ) . '</legend><table><tr>';
		// Dynamically contruct our review types
		foreach ( array_keys($wgFlaggedRevTags) as $tag ) {
			$form .= '<td><strong>' . wfMsgHtml( "revreview-$tag" ) . '</strong></td><td width=\'20\'></td>';
		}
		$form .= '</tr><tr>';
		foreach ( $formradios as $set => $ratioset ) {
			$form .= '<td>';
			foreach( $ratioset as $item ) {
				list( $message, $name, $field ) = $item;
				// Don't give options the user can't set unless its the status quo
				$disabled = ( !$this->userCan($set,$field) ) ? array('disabled' => 'true') : array();
				$form .= "<div>";
				$form .= Xml::radio( $name, $field, ($field==$this->dims[$set]), $disabled ) . ' ' . wfMsg($message);
				$form .= "</div>\n";
			}
			$form .= '</td><td width=\'20\'></td>';
		}
		$form .= '</tr></table></fieldset>';
		// List all images about to be copied over
		list($images,$thumbs) = FlaggedRevs::findLocalImages( FlaggedRevs::expandText( $rev->getText() ) );
		if ( $images ) {
			$form .= wfMsgExt('revreview-images', array('parse')) . "\n";
			$form .= "<ul>";
			$imglist = '';
			foreach ( $images as $image ) {
				$imglist .= "<li>" . $this->skin->makeKnownLink( $image ) . "</li>\n";
			}
			$form .= $imglist;
			$form .= "</ul>\n";
		}
		// Add box to add live notes to a flagged revision
		if ( $wgFlaggedRevComments ) {
			$form .= "<fieldset><legend>" . wfMsgHtml( 'revreview-notes' ) . "</legend>" .
			"<textarea tabindex='1' name='wpNotes' id='wpNotes' rows='3' cols='80' style='width:100%'></textarea>" .	
			"</fieldset>";
		}
		
		foreach( $items as $item ) {
			$form .= '<p>' . $item . '</p>';
		}	
		foreach( $hidden as $item ) {
			$form .= $item;
		}
		
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
		global $wgOut;
		
		$approved = false;
		# If all values are set to zero, this has been unnapproved
		foreach( $this->dims as $quality => $value ) {
			if( $value ) $approved = true;
		}
		// We can only approve actually revs
		if ( $approved ) {
			$rev = Revision::newFromTitle( $this->page, $this->oldid );
			// Do not mess with archived/deleted revisions
			if ( is_null($rev) || $rev->mDeleted ) {
				$wgOut->showErrorPage( 'internalerror', 'badarticleerror' ); 
				return;
			}
		} else {
			$frev = FlaggedRevs::getFlaggedRev( $this->oldid );
			if ( is_null($frev) ) {
				$wgOut->showErrorPage( 'internalerror', 'badarticleerror' ); 
				return;
			}
		}
		
		$success = ( $approved ) ? 
			$this->approveRevision( $rev, $this->notes ) : $this->unapproveRevision( $frev );
		// Return to our page			
		if ( $success ) {
        	$wgOut->redirect( $this->page->escapeLocalUrl() );
		} else {
			$wgOut->showErrorPage( 'internalerror', 'badarticleerror' ); 
		}
	}

	/**
	 * @param Revision $rev
	 * Adds or updates the flagged revision table for this page/id set
	 */
	function approveRevision( $rev=NULL, $notes='' ) {
		global $wgUser;
		if( is_null($rev) ) return false;

		wfProfileIn( __METHOD__ );
	
        $dbw = wfGetDB( DB_MASTER );
        $revid = $rev->getId();
        $user = $wgUser->getId();
        $timestamp = wfTimestampNow();
        
        $cache_text = FlaggedRevs::expandText( $rev->getText() );
 		$revset = array(
 			'fr_page_id'   => $rev->getPage(),
			'fr_rev_id'    => $revid,
			'fr_user'      => $user,
			'fr_timestamp' => $timestamp,
			'fr_comment'   => $notes,
			'fr_text'      => $cache_text
		);
		$flagset = array();
		foreach ( $this->dims as $tag => $value ) {
			$flagset[] = array(
				'frt_dimension' => $tag, 
				'frt_page_id' => $rev->getPage(), 
				'frt_rev_id' => $revid, 
				'frt_value' => $value 
			);
		}
		// Update flagrevisions table
		$dbw->replace( 'flaggedrevs', array( array('fr_page_id','fr_rev_id') ), $revset, __METHOD__ );
		// Set all of our flags
		$dbw->replace( 'flaggedrevtags', array( array('frt_rev_id','frt_dimension') ), $flagset, __METHOD__ );
		
		// Update the article review log
		$this->updateLog( $this->page, $this->dims, $this->comment, $this->oldid, true );
		
		// Clone images to stable dir
		$updateImgs = $wgUser->isAllowed('validate');
		list($images,$thumbs) = FlaggedRevs::findLocalImages( $cache_text );
		$copies = FlaggedRevs::makeStableImages( $images, $updateImgs );
		if ( $updateImgs )
			FlaggedRevs::purgeStableThumbnails( $thumbs );
		// Update stable image table
		FlaggedRevs::insertStableImages( $revid, $copies );
		// Clear cache...
		$this->page->invalidateCache();
        return true;
    }

	/**
	 * @param Revision $rev
	 * Removes flagged revision data for this page/id set
	 */  
	function unapproveRevision( $rev=NULL ) {
		global $wgUser;
	
		if( is_null($rev) ) return false;
        $db = wfGetDB( DB_MASTER );
        $user = $wgUser->getId();
        $timestamp = wfTimestampNow();
		// get the flagged revision to access its cache text
		$cache_text = FlaggedRevs::getFlaggedRevText( $rev->fr_rev_id );
		if( is_null($cache_text) ) {
		// Quietly ignore this...
			return true;
		}
		// Delete from table
		$db->delete( 'flaggedrevs', array( 'fr_rev_id' => $rev->fr_rev_id ) );
		// And the flags...
		$db->delete( 'flaggedrevtags', array( 'frt_rev_id' => $rev->fr_rev_id ) );
		
		// Update the article review log
		$this->updateLog( $this->page, $this->dims, $this->comment, $this->oldid, false );
		
		// Delete stable images if needed
		list($images,$thumbs) = FlaggedRevs::findLocalImages( $cache_text );
		$copies = FlaggedRevs::deleteStableImages( $images );
		// Stable versions must remake this thumbnail
		FlaggedRevs::purgeStableThumbnails( $thumbs );
		// Update stable image table
		FlaggedRevs::removeStableImages( $rev->fr_rev_id, $copies );
		// Clear cache...
		$this->page->invalidateCache();
        return true;
    }

	/**
	 * Record a log entry on the action
	 * @param Title $title
	 * @param array $dimensions
	 * @param string $comment
	 * @param int $revid
	 * @param bool $approve
	 */	
	function updateLog( $title, $dimensions, $comment, $oldid, $approve ) {
		$log = new LogPage( 'review' );
		// ID, accuracy, depth, style
		$ratings = array();
		foreach( $dimensions as $quality => $level ) {
			$ratings[] = wfMsg( "revreview-$quality" ) . ": " . wfMsg("revreview-$quality-$level");
		}
		$rating = ($approve) ? ' [' . implode(', ',$ratings). ']' : '';
		// Append comment with action
		$action = wfMsgExt('review-logaction', array('parsemag'), $oldid );
		$comment = ($comment) ? "$action: $comment$rating" : "$action $rating"; 
			
		if ( $approve ) {
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
        global $wgRequest;

		$this->setHeaders();
		// Our target page
		$this->page = $wgRequest->getText( 'page' );
		// Revision ID
		$this->oldid = $wgRequest->getIntOrNull( 'oldid' );
		
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
		global $wgParser, $wgLang, $wgUser, $wgOut, $wgTitle;
			
		// Get the revision
		$frev = FlaggedRevs::getFlaggedRev( $this->oldid );
		// Revision must exists
		if( is_null($frev) ) {
			$wgOut->showErrorPage('badarticleerror', 'notargettext' );
			return;
		}
		// Must be a valid page/Id
		$page = Title::newFromID( $frev->fr_page_id );
		if( is_null($page) || !$page->isContentPage() ) {
			$wgOut->showErrorPage('notargettitle', 'notargettext' );
			return;
		}
		// Must be a content page
		$article = new Article( $page );
		if( is_null($article) ) {
			$wgOut->showErrorPage('badarticleerror', 'notargettext' );
			return;
		}
		$wgOut->setPagetitle( $page->getPrefixedText() );
		// Modifier instance
		$RevFlagging = new FlaggedRevs();
		// Get flags and date
		$flags = $RevFlagging->getFlagsForRevision( $frev->fr_rev_id );
		$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $frev->fr_timestamp), true );
       	// We will be looking at the reviewed revision...
       	$flaghtml = wfMsgExt('revreview-stable', array('parse'), urlencode($page->getPrefixedText()), 
		   $time, $page->getPrefixedText());
		// Parse the text
		$text = $RevFlagging->getFlaggedRevText( $this->oldid );
		$options = ParserOptions::newFromUser($wgUser);
		// Parsing this text is kind of funky...
       	$newbody = $RevFlagging->parseStableText( $page, $text, $this->oldid, $options );
		$notes = $RevFlagging->ReviewNotes( $frev );
		// Construct some tagging
		$flaghtml .= $RevFlagging->addTagRatings( $flags );
		// Set the new body HTML, place a tag on top
		$wgOut->addHTML('<div class="mw-warning plainlinks"><small>' . $flaghtml . '</small></div>' . $newbody . $notes);
	}
	
	function showStableList() {
		global $wgOut, $wgUser, $wgLang;
	
		$skin = $wgUser->getSkin();
		// Must be a valid page/Id
		$page = Title::newFromUrl( $this->page );
		if( is_null($page) || !$page->isContentPage() ) {
			$wgOut->showErrorPage('notargettitle', 'notargettext' );
			return;
		}
		$article = new Article( $page );
		$page_id = $article->getID();
		if( !$page_id ) {
			$wgOut->showErrorPage('notargettitle', 'notargettext' );
			return;
		}
		
		$pager = new StableRevisionsPager( $this, array(), $page_id );		
		if ( $pager->getNumRows() ) {
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
		
		static $skin=null;

		if( is_null( $skin ) )
			$skin = $wgUser->getSkin();
	
		$special = SpecialPage::getTitleFor( 'Stableversions' );
		$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $row->rev_timestamp), true );
		$ftime = $wgLang->timeanddate( wfTimestamp(TS_MW, $row->fr_timestamp), true );
		$review = wfMsg( 'stableversions-review', $ftime );
		return '<li>'.$skin->makeKnownLinkObj( $special, $time, 'oldid='.$row->fr_rev_id ).' ('.$review.')'.'</li>';	
	}
}

/**
 *
 *
 */
class StableRevisionsPager extends ReverseChronologicalPager {
	public $mForm, $mConds;

	function __construct( $form, $conds = array(), $page_id ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->page_id = $page_id;
		parent::__construct();
	}
	
	function formatRow( $row ) {
		$block = new Block;
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$conds = $this->mConds;
		$conds[] = "fr_page_id = $this->page_id";
		$conds[] = 'fr_rev_id = rev_id';
		$conds[] = 'rev_deleted = 0';
		return array(
			'tables' => array('flaggedrevs','revision'),
			'fields' => 'fr_rev_id,fr_timestamp,rev_timestamp',
			'conds' => $conds
		);
	}

	function getIndexField() {
		return 'fr_rev_id';
	}
}
?>
