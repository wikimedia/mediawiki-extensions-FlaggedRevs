<?php

wfLoadExtensionMessages( 'QualityOversight' );
class QualityOversight extends SpecialPage
{
    function __construct() {
        SpecialPage::SpecialPage( 'QualityOversight' );
    }

    function execute( $par ) {
		global $wgOut, $wgUser, $wgFlaggedRevsOversightAge;
		$this->setHeaders();
		$wgOut->addHTML( wfMsgExt('qualityoversight-list', array('parse') ) );
		# Create a LogPager item to get the results and a LogEventsList item to format them...
		$dbr = wfGetDB( DB_SLAVE );
		$cutoff = $dbr->addQuotes( $dbr->timestamp(time() - $wgFlaggedRevsOversightAge) );
		$loglist = new LogEventsList( $wgUser->getSkin(), $wgOut, 0 );
		$pager = new LogPager( $loglist, 'review', '', '', '', 
			array('log_action' => array('approve2','unapprove2'), "log_timestamp > $cutoff" ) );
		# Insert list
		$logBody = $pager->getBody();
		if( $logBody ) {
			$wgOut->addHTML(
				$pager->getNavigationBar() .
				$loglist->beginLogEventsList() .
				$logBody .
				$loglist->endLogEventsList() .
				$pager->getNavigationBar()
			);
		} else {
			$wgOut->addWikiMsg( 'logempty' );
		}
	}
}
