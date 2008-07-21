<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

class ReaderFeedback extends UnlistedSpecialPage
{
    function __construct() {
        UnlistedSpecialPage::UnlistedSpecialPage( 'ReaderFeedback', 'feedback' );
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgOut;
		$confirm = $wgRequest->wasPosted() && $wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );
		if( $wgUser->isAllowed( 'feedback' ) ) {
			if( $wgUser->isBlocked( !$confirm ) ) {
				$wgOut->blockedPage();
				return;
			}
		} else {
			$wgOut->permissionRequired( 'feedback' );
			return;
		}
		if( wfReadOnly() ) {
			$wgOut->readOnlyPage();
			return;
		}
		$this->setHeaders();
		# Our target page
		$this->target = $wgRequest->getVal( 'target' );
		$this->page = Title::newFromUrl( $this->target );
		if( is_null($this->page) ) {
			$wgOut->showErrorPage('notargettitle', 'notargettext' );
			return;
		}
		# Revision ID
		$this->oldid = $wgRequest->getIntOrNull( 'oldid' );
		if( !$this->oldid || !FlaggedRevs::isPageReviewable( $this->page ) ) {
			$wgOut->addHTML( wfMsgExt('readerfeedback-main',array('parse')) );
			return;
		}
		# Get our rating dimensions
		$this->dims = array();
		foreach( FlaggedRevs::getFeedbackTags() as $tag => $weight ) {
			$this->dims[$tag] = $wgRequest->getInt( "wp$tag" );
		}
		# Submit valid requests
		if( $wgUser->matchEditToken( $wgRequest->getVal('wpEditToken') ) && $wgRequest->wasPosted() ) {
			$this->submit();
		}
		# Back to the page!
		$wgOut->redirect( $this->page->getFullUrl() );
	}

	private function submit() {
		global $wgUser;
		$dbw = wfGetDB( DB_MASTER );
		# Get date timestamp...
		$date = str_pad( substr( wfTimestampNow(), 0, 8 ), 14, '0' );
		# Make sure revision is valid!
		$rev = Revision::newFromId( $this->oldid );
		if( !$rev || !$rev->getTitle()->equals( $this->page ) ) {
			return false; // opps!
		}
		$article = new Article( $this->page );
		# Check if user already voted before...
		if( $wgUser->getId() ) {
			$userVoted = $dbw->selectField( 'reader_feedback', '1', 
				array( 'rfb_rev_id' => $this->oldid, 'rfb_user' => $wgUser->getId() ), 
				__METHOD__ );
			if( $userVoted ) {
				return false;
			}
		} else {
			$ipVoted = $dbw->selectField( 'reader_feedback', '1', 
				array( 'rfb_rev_id' => $this->oldid, 'rfb_user' => 0, 'rfb_ip' => wfGetIP() ), 
				__METHOD__ );
			if( $ipVoted ) {
				return false;
			}
		}
		$dbw->begin();
		# Update review records to limit double voting!
		$insertRow = array( 
			'rfb_rev_id' => $this->oldid, 
			'rfb_user'   => $wgUser->getId(), 
			'rfb_ip'     => wfGetIP() 
		);
		$dbw->insert( 'reader_feedback', $insertRow, __METHOD__, 'IGNORE' );
		# Make sure initial page data is there to begin with...
		$insertRows = array();
		foreach( $this->dims as $tag => $val ) {
			$insertRows[] = array(
				'rfh_page_id' => $rev->getPage(),
				'rfh_tag'     => $tag,
				'rfh_total'   => 0,
				'rfh_count'   => 0,
				'rfh_date'    => $date
			);
		}
		$dbw->insert( 'reader_feedback_history', $insertRows, __METHOD__, 'IGNORE' );
		# Update aggregate data for this page over time...
		$touched = $dbw->timestamp( wfTimestampNow() );
		$overall = 0;
		$insertRows = array();
		foreach( $this->dims as $tag => $val ) {
			$dbw->update( 'reader_feedback_history',
				array( 'rfh_total = rfh_total + '.intval($val), 
					'rfh_count = rfh_count + 1'),
				array( 'rfh_page_id' => $rev->getPage(), 
					'rfh_tag' => $tag,
					'rfh_date' => $date ),
				__METHOD__ );
			# Get effective tag values for this page..
			$aveVal = FlaggedRevs::getAverageRating( $article, $tag, true );
			$insertRows[] = array( 
				'rfp_page_id' => $rev->getPage(),
				'rfp_tag'     => $tag,
				'rfp_ave_val' => $aveVal,
				'rfp_touched' => $touched
			);
			$overall += FlaggedRevs::getFeedbackWeight( $tag ) * $aveVal;
		}
		# Get overall data for this page. Used to rank best/worst pages...
		$insertRows[] = array( 
			'rfp_page_id' => $rev->getPage(),
			'rfp_tag'     => 'overall',
			'rfp_ave_val' => ($overall / count($this->dims)),
			'rfp_touched' => $touched
		);
		$dbw->replace( 'reader_feedback_pages', array( 'PRIMARY' ), $insertRows, __METHOD__ );
		# Done!
		$dbw->commit();
		return true;
	}
}
