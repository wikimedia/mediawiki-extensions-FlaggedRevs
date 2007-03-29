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
		// Log comment
		$this->comment = $wgRequest->getText( 'wpReason' );
		// Additional notes
		$this->notes = ($wgFlaggedRevComments) ? $wgRequest->getText('wpNotes') : '';
		// Get our accuracy/quality array
		$this->dimensions = array();
		foreach ( $wgFlaggedRevTags as $tag ) {
			$this->dimensions[$tag] = $wgRequest->getIntOrNull( "wp$tag" );
		}
		// Must be a valid page
		$this->page = Title::newFromUrl( $this->target );
		if( is_null($this->page) || is_null($this->oldid) || !$this->page->isContentPage() ) {
			$wgOut->showErrorPage( $this->page, 'notargettitle', 'notargettext' );
			return;
		}
		if( $wgRequest->wasPosted() ) {
			$this->submit( $wgRequest );
		} else {
			$this->showRevision( $wgRequest );
		}
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
		if( !isset( $rev ) ) {
			$wgOut->showErrorPage( 'internalerror', 'notargettitle', 'notargettext' );
			return;
		}
		// Do not mess with deleted revisions
		if ( $rev->mDeleted ) {
			$wgOut->showErrorPage( 'internalerror', 'badarticleerror' ); 
			return;
		}	
		$wgOut->addHtml( "<ul>" );
		$wgOut->addHtml( $this->historyLine( $rev ) );
		$wgOut->addHtml( "</ul>" );
		
		$wgOut->addWikiText( wfMsgHtml( 'revreview-text' ) );
		$formradios = array();
		// Dynamically contruct our radio options
		foreach ( $wgFlaggedRevTags as $tag ) {
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
		foreach ( $wgFlaggedRevTags as $tag ) {
			$form .= '<td><strong>' . wfMsgHtml( "revreview-$tag" ) . '</strong></td><td width=\'20\'></td>';
		}
		$form .= '</tr><tr>';
		foreach ( $formradios as $setname => $ratioset ) {
			$form .= '<td>';
			foreach( $ratioset as $item ) {
				list( $message, $name, $field ) = $item;
				$form .= "<div>".
					Xml::radio( $name, $field, ($field==$this->dimensions[$setname]) ) . ' ' . wfMsgHtml($message) .
					"</div>\n";
			}
			$form .= '</td><td width=\'20\'></td>';
		}
		$form .= '</tr></table></fieldset>';
		// List all images about to be copied over
		list($images,$thumbs) = FlaggedRevs::findLocalImages( FlaggedRevs::expandText( $rev->getText() ) );
		if ( $images ) {
			$form .= wfMsg('revreview-images') . "\n";
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

		$rev = Revision::newFromTitle( $this->page, $this->oldid );
		// Do not mess with deleted revisions
		if ( is_null($rev) || $rev->mDeleted ) {
			$wgOut->showErrorPage( 'internalerror', 'badarticleerror' ); 
			return;
		}
		$approved = false;
		# If all values are set to zero, this has been unnapproved
		foreach( $this->dimensions as $quality => $value ) {
			if( $value ) $approved = true;
		}
		$success = ( $approved ) ? $this->approveRevision( $rev ) : $this->unapproveRevision( $rev );
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
	function approveRevision( $rev=NULL ) {
		global $wgUser;
		if( is_null($rev) ) return false;

		wfProfileIn( __METHOD__ );
	
        $dbw = wfGetDB( DB_MASTER );
        $revid = $rev->getId();
        $user = $wgUser->getId();
        $timestamp = wfTimestampNow();
        
        $cache_text = FlaggedRevs::expandText( $rev->getText() );
 		$revset = array(
 			'fr_page_id' => $rev->getPage(),
			'fr_rev_id' => $revid,
			'fr_user' => $user,
			'fr_timestamp' => $timestamp,
			'fr_comment'=> $this->notes
		);
		$textset = array('ft_rev_id' => $revid, 'ft_text' => $cache_text);
		$flagset = array();
		foreach ( $this->dimensions as $tag => $value ) {
			$flagset[] = array('frt_dimension' => $tag, 'frt_rev_id' => $revid, 'frt_value' => $value);
		}
		// Update flagrevisions table
		$dbw->replace( 'flaggedrevs', array( array('fr_page_id','fr_rev_id') ), $revset, __METHOD__ );
		// Store/update the text
		$dbw->replace( 'flaggedtext', array('ft_rev_id'), $textset, __METHOD__ );
		// Set all of our flags
		$dbw->replace( 'flaggedrevtags', array( array('frt_rev_id','frt_dimension') ), $flagset, __METHOD__ );
		
		// Update the article review log
		$this->updateLog( $this->page, $this->dimensions, $this->comment, $this->oldid, true );
		
		// Clone images to stable dir
		list($images,$thumbs) = FlaggedRevs::findLocalImages( $cache_text );
		$copies = FlaggedRevs::makeStableImages( $images );
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
		$cache_text = FlaggedRevs::getFlaggedRevText( $rev->getId() );
		if( is_null($cache_text) ) {
		// Quietly ignore this...
			return true;
		}
		// Delete from table
		$db->delete( 'flaggedrevs', array( 'fr_rev_id' => $rev->getId ) );
		// And the text...
		$db->delete( 'flaggedtext', array( 'ft_rev_id' => $rev->getId ) );
		// And the flags...
		$db->delete( 'flaggedrevtags', array( 'frt_rev_id' => $rev->getId ) );
		
		// Update the article review log
		$this->updateLog( $this->page, $this->dimensions, $this->comment, $this->oldid, false );
		
		// Delete stable images if needed
		list($images,$thumbs) = FlaggedRevs::findLocalImages( $cache_text );
		$copies = FlaggedRevs::deleteStableImages( $images );
		// Stable versions must remake this thumbnail
		FlaggedRevs::purgeStableThumbnails( $thumbs );
		// Update stable image table
		FlaggedRevs::removeStableImages( $rev->getId(), $copies );
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
?>
