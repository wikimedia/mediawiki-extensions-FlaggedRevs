<?php

use MediaWiki\Html\Html;
use MediaWiki\MainConfigNames;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;

// Assumes $wgFlaggedRevsProtection is on
class StablePages extends SpecialPage {
	/** @var StablePagesPager */
	private $pager = null;

	/** @var int|null */
	private $namespace;

	/** @var string|null */
	private $autoreview;

	/** @var bool */
	private $indef;

	public function __construct() {
		parent::__construct( 'StablePages' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		$request = $this->getRequest();

		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:FlaggedRevs' );

		$this->namespace = $request->getIntOrNull( 'namespace' );
		$this->autoreview = $request->getVal( 'restriction', '' );
		$this->indef = $request->getBool( 'indef' );

		$this->pager = new StablePagesPager( $this, [],
			$this->namespace, $this->autoreview, $this->indef );

		$this->showForm();
		$this->showPageList();
	}

	private function showForm() {
		$this->getOutput()->addWikiMsg( 'stablepages-list',
			$this->getLanguage()->formatNum( $this->pager->getNumRows() ) );

		$fields = [];
		// Namespace selector
		if ( count( FlaggedRevs::getReviewNamespaces() ) > 1 ) {
			$fields[] = FlaggedRevsHTML::getNamespaceMenu( $this->namespace, '' );
		}
		// Restriction level selector
		if ( FlaggedRevs::getRestrictionLevels() ) {
			$fields[] = FlaggedRevsHTML::getRestrictionFilterMenu( $this->autoreview );
		}
		$fields[] = Html::element( 'input', [
			'type' => 'checkbox', 'name' => 'indef', 'value' => '1',
			'checked' => $this->indef,
			'id' => 'stablepages-indef',
		] )
			. '&nbsp;'
			. Html::label( $this->msg( 'stablepages-indef' )->text(), 'stablepages-indef' );

		$form = Html::openElement( 'form', [
			'name' => 'stablepages',
			'action' => $this->getConfig()->get( MainConfigNames::Script ),
			'method' => 'get',
		] );
		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBkey() );
		$form .= "<fieldset><legend>" . $this->msg( 'stablepages' )->escaped() . "</legend>\n";
		$form .= implode( '&#160;', $fields ) . '&nbsp';
		$form .= ' ' . Html::submitButton( $this->msg( 'go' )->text() );
		$form .= "</fieldset>\n";
		$form .= Html::closeElement( 'form' ) . "\n";

		$this->getOutput()->addHTML( $form );
	}

	private function showPageList() {
		$out = $this->getOutput();
		if ( $this->pager->getNumRows() ) {
			$out->addHTML( $this->pager->getNavigationBar() );
			$out->addHTML( $this->pager->getBody() );
			$out->addHTML( $this->pager->getNavigationBar() );
		} else {
			$out->addWikiMsg( 'stablepages-none' );
		}
	}

	/**
	 * @param stdClass $row
	 * @return string HTML
	 */
	public function formatRow( $row ) {
		$title = Title::makeTitle( $row->page_namespace, $row->page_title );
		$linkRenderer = $this->getLinkRenderer();
		// Link to page
		$link = $linkRenderer->makeLink( $title );
		// Helpful utility links
		$utilLinks = [];
		$utilLinks[] = $linkRenderer->makeKnownLink(
			$title,
			$this->msg( 'stablepages-config' )->text(),
			[], [ 'action' => 'protect' ] );
		$utilLinks[] = $linkRenderer->makeKnownLink(
			$title,
			$this->msg( 'history' )->text(),
			[], [ 'action' => 'history' ] );
		$utilLinks[] = $linkRenderer->makeKnownLink(
			SpecialPage::getTitleFor( 'Log', 'stable' ),
			$this->msg( 'stable-logpage' )->text(),
			[], [ 'page' => $title->getPrefixedText() ] );
		// Autoreview/review restriction level
		$restr = '';
		if ( $row->fpc_level != '' ) {
			$restr = 'autoreview=' . htmlspecialchars( $row->fpc_level );
			$restr = "[$restr]";
		}
		// When these configuration settings expire
		if ( $row->fpc_expiry != 'infinity' && strlen( $row->fpc_expiry ) ) {
			$expiry_description = " (" . $this->msg(
				'protect-expiring',
				$this->getLanguage()->timeanddate( $row->fpc_expiry ),
				$this->getLanguage()->date( $row->fpc_expiry ),
				$this->getLanguage()->time( $row->fpc_expiry )
			)->inContentLanguage()->text() . ")";
		} else {
			$expiry_description = "";
		}
		$utilLinks = $this->getLanguage()->pipeList( $utilLinks );
		return "<li>{$link} ({$utilLinks}) {$restr}<i>{$expiry_description}</i></li>";
	}

	/**
	 * @return string
	 */
	protected function getGroupName() {
		return 'quality';
	}
}
