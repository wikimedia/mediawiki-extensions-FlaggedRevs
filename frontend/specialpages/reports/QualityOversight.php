<?php

class QualityOversight extends SpecialPage {
	/** @var int */
	private $namespace;

	/** @var int|null */
	private $level;

	/** @var int|null */
	private $status;

	/** @var string|null */
	private $user;

	public function __construct() {
		parent::__construct( 'QualityOversight' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		$out = $this->getOutput();
		$request = $this->getRequest();

		$this->setHeaders();

		$this->namespace = $request->getInt( 'namespace' );
		$this->level = $request->getIntOrNull( 'level' );
		$this->status = $request->getIntOrNull( 'status' );
		$this->user = $request->getVal( 'user' );
		# Check if the user exists
		$usertitle = Title::makeTitleSafe( NS_USER, $this->user );
		$u = $usertitle ? User::idFromName( $this->user ) : false;

		# Are the dropdown params given even valid?
		$actions = $this->getActions();
		if ( !$actions ) {
			$out->addWikiMsg( 'qualityoversight-list', 0 );
			$this->showForm();
			$out->addWikiMsg( 'logempty' );
			return;
		}

		# Get extra query conds
		$conds = [ 'log_namespace' => $this->namespace, 'log_action' => $actions ];
		# Get cutoff time (mainly for performance)
		if ( !$u ) {
			$dbr = wfGetDB( DB_REPLICA );
			$cutoff_unixtime = time() - 2592000;
			$cutoff = $dbr->addQuotes( $dbr->timestamp( $cutoff_unixtime ) );
			$conds[] = "log_timestamp >= $cutoff";
		}

		# Create a LogPager item to get the results and a LogEventsList item to format them...
		$loglist = new LogEventsList( $this->getContext()->getSkin(), $this->getLinkRenderer(), 0 );
		$pager = new LogPager( $loglist, 'review', $this->user, '', '', $conds );

		# Explanatory text
		$out->addWikiMsg( 'qualityoversight-list',
			$this->getLanguage()->formatNum( $pager->getNumRows() ) );
		# Show form options
		$this->showForm();

		# Insert list
		$logBody = $pager->getBody();
		if ( $logBody ) {
			$out->addModuleStyles( 'mediawiki.interface.helpers.styles' );
			$out->addHTML(
				$pager->getNavigationBar() .
				$loglist->beginLogEventsList() .
				$logBody .
				$loglist->endLogEventsList() .
				$pager->getNavigationBar()
			);
		} else {
			$out->addWikiMsg( 'logempty' );
		}
	}

	private function showForm() {
		$this->getOutput()->addHTML(
			Xml::openElement( 'form', [
				'name' => 'qualityoversight',
				'action' => $this->getConfig()->get( 'Script' ),
				'method' => 'get',
			] ) .
			'<fieldset><legend>' . $this->msg( 'qualityoversight-legend' )->escaped() . '</legend><p>' .
			Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBkey() ) .
			FlaggedRevsXML::getNamespaceMenu( $this->namespace ) . '&#160;' .
			Xml::inputLabel( $this->msg( 'specialloguserlabel' )->text(), 'user', 'user', 20,
				$this->user ) .
				'<br />' .
			FlaggedRevsXML::getStatusFilterMenu( $this->status ) . '&#160;' .
			Xml::submitButton( $this->msg( 'go' )->text() ) .
			'</p></fieldset>' . Xml::closeElement( 'form' )
		);
	}

	/**
	 * Get actions for IN clause
	 * @return string[]
	 */
	private function getActions() {
		$actions = [
			'approve' => 1, 'approve2' => 1, 'approve-a' => 1, 'approve-i' => 1,
			'approve-ia' => 1, 'approve2-i' => 1, 'unapprove' => 1, 'unapprove2' => 1
		];
		if ( $this->level === 0 ) { // checked revisions
			$actions['approve2'] = 0;
			$actions['approve2-i'] = 0;
			$actions['unapprove2'] = 0;
		} elseif ( $this->level === 1 ) { // quality revisions
			$actions['approve'] = 0;
			$actions['approve-i'] = 0;
			$actions['unapprove'] = 0;
		}
		if ( $this->status === 1 ) { // approved first time
			$actions['approve'] = 0;
			$actions['approve2'] = 0;
			$actions['unapprove'] = 0;
			$actions['unapprove2'] = 0;
		} elseif ( $this->status === 2 ) { // re-approved
			$actions['approve-i'] = 0;
			$actions['approve2-i'] = 0;
			$actions['unapprove'] = 0;
			$actions['unapprove2'] = 0;
		} elseif ( $this->status === 3 ) { // deprecated
			$actions['approve'] = 0;
			$actions['approve-i'] = 0;
			$actions['approve2'] = 0;
			$actions['approve2-i'] = 0;
		}
		$showActions = [];
		foreach ( $actions as $action => $show ) {
			if ( $show ) {
				$showActions[] = $action;
			}
		}
		return $showActions;
	}

	/**
	 * @return string
	 */
	protected function getGroupName() {
		return 'quality';
	}
}
