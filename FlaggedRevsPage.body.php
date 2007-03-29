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
        global $wgRequest, $wgUser, $wgOut, $wgFlaggedRevComments;
        
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
        $this->dimensions['acc']   =  $wgRequest->getIntOrNull('accuracy');
        $this->dimensions['depth'] = $wgRequest->getIntOrNull('depth');
        $this->dimensions['style'] = $wgRequest->getIntOrNull('style');
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
		global $wgOut, $wgUser, $wgTitle, $wgFlaggedRevComments;
		
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
		
		$this->accRadios = array(
			array( 'revreview-acc-0', 'wpAcc1', 0 ),
			array( 'revreview-acc-1', 'wpAcc2', 1 ),
			array( 'revreview-acc-2', 'wpAcc3', 2 ),
			array( 'revreview-acc-3', 'wpAcc4', 3 ) );
		$this->depthRadios = array(
			array( 'revreview-depth-0', 'wpDepth1', 0 ),
			array( 'revreview-depth-1', 'wpDepth2', 1 ),
			array( 'revreview-depth-2', 'wpDepth3', 2 ),
			array( 'revreview-depth-3', 'wpDepth4', 3 ) );
		$this->styleRadios = array(
			array( 'revreview-style-0', 'wpStyle1', 0 ),
			array( 'revreview-style-1', 'wpStyle2', 1 ),
			array( 'revreview-style-2', 'wpStyle3', 2 ),
			array( 'revreview-style-3', 'wpStyle4', 3 ) );
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
		$form .= '<td><strong>' . wfMsgHtml( 'revreview-acc' ) . '</strong></td>';
		$form .= '<td width=\'25\'></td><td><strong>' . wfMsgHtml( 'revreview-depth' ) . '</strong></td>';
		$form .= '<td width=\'25\'></td><td><strong>' . wfMsgHtml( 'revreview-style' ) . '</strong></td>';
		$form .= '</tr><tr><td>';
		foreach( $this->accRadios as $item ) {
			list( $message, $name, $field ) = $item;
			$form .= "<div>" .
				Xml::radio( 'accuracy', $field, ($field==$this->dimensions['acc']) ) . ' ' . wfMsgHtml($message) .
				"</div>\n";
		}
		$form .= '<td width=\'25\'></td></td><td>';
		foreach( $this->depthRadios as $item ) {
			list( $message, $name, $field ) = $item;
			$form .= "<div>" .
				Xml::radio( 'depth', $field, ($field==$this->dimensions['depth']) ) . ' ' . wfMsgHtml($message) .
				"</div>\n";
		}
		$form .= '<td width=\'25\'></td></td><td>';
		foreach( $this->styleRadios as $item ) {
			list( $message, $name, $field ) = $item;
			$form .= "<div>" .
				Xml::radio( 'style', $field, ($field==$this->dimensions['style']) ) . ' ' . wfMsgHtml($message) .
				"</div>\n";
		}
		$form .= '</td></tr></table></fieldset>';
		
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
	
        $db = wfGetDB( DB_MASTER );
        $user = $wgUser->getId();
        $timestamp = wfTimestampNow();
        
        $cache_text = FlaggedRevs::expandText( $rev->getText() );
		// Add or update entry for this revision
 		$set = array(
 			'fr_page_id' => $rev->getPage(),
			'fr_rev_id' => $rev->getId(),
			'fr_acc' => $this->dimensions['acc'],
			'fr_dep' => $this->dimensions['depth'],
			'fr_sty' => $this->dimensions['style'],
			'fr_user' => $user,
			'fr_timestamp' => $timestamp,
			'fr_comment'=> $this->notes
		);
		$set2 = array('ft_rev_id' => $rev->getId(), 'ft_text' => $cache_text);
		// Update flagrevisions table
		$db->replace( 'flaggedrevs', array( array('fr_page_id','fr_rev_id') ), $set, __METHOD__ );
		// Store/update the text
		$db->replace( 'flaggedtext', array('ft_rev_id'), $set2, __METHOD__ );
		// Update the article review log
		$this->updateLog( $this->page, $this->dimensions, $this->comment, $this->oldid, true );
		// Clone images to stable dir
		list($images,$thumbs) = FlaggedRevs::findLocalImages( $cache_text );
		$copies = FlaggedRevs::makeStableImages( $images );
		FlaggedRevs::purgeStableThumbnails( $thumbs );
		// Update stable image table
		FlaggedRevs::insertStableImages( $rev->getId(), $copies );
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
		$frev = FlaggedRevs::getFlaggedRevision( $rev->getId() );
		if( !$frev ) {
		// Quitly ignore this...
			return true;
		}
		$db->delete( 'flaggedrevs', array( 'fr_rev_id' => $rev->getId ) );
		// And the text...
		$db->delete( 'flaggedtext', array( 'ft_rev_id' => $rev->getId ) );
		// Update the article review log
		$this->updateLog( $this->page, $this->dimensions, $this->comment, $this->oldid, false );
		
		$cache_text = FlaggedRevs::getFlaggedRevText( $rev->getId ) ;
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
