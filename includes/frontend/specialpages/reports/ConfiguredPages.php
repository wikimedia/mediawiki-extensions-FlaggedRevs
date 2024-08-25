<?php

use MediaWiki\Html\Html;
use MediaWiki\MainConfigNames;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;

// Assumes $wgFlaggedRevsProtection is off
class ConfiguredPages extends SpecialPage {
	/** @var ConfiguredPagesPager|null */
	private $pager = null;

	/** @var int|null */
	private $namespace;

	/** @var int|null */
	private $override;

	/** @var string|null */
	private $autoreview;

	public function __construct() {
		parent::__construct( 'ConfiguredPages' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		$request = $this->getRequest();

		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:FlaggedRevs' );

		$this->namespace = $request->getIntOrNull( 'namespace' );
		$this->override = $request->getIntOrNull( 'stable' );
		$this->autoreview = $request->getVal( 'restriction', '' );

		$this->pager = new ConfiguredPagesPager(
			$this, [], $this->namespace, $this->override, $this->autoreview );

		$this->showForm();
		$this->showPageList();
	}

	private function showForm() {
		# Explanatory text
		$this->getOutput()->addWikiMsg( 'configuredpages-list',
			$this->getLanguage()->formatNum( $this->pager->getNumRows() ) );

		$form = Html::openElement( 'form', [
				'name' => 'configuredpages',
				'action' => $this->getConfig()->get( MainConfigNames::Script ),
				'method' => 'get',
				'class' => 'mw-fr-form-container'
			] ) . "\n";

		$form .= Html::openElement( 'fieldset', [ 'class' => 'cdx-field' ] ) . "\n";

		$form .= Html::openElement( 'legend', [ 'class' => 'cdx-label' ] ) . "\n";
		$form .= Html::rawElement( 'span', [ 'class' => 'cdx-label__label' ],
			Html::element( 'span', [ 'class' => 'cdx-label__label__text' ],
				$this->msg( 'configuredpages' )->text() )
		);
		$form .= Html::closeElement( 'legend' ) . "\n";

		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBkey() ) . "\n";

		$form .= Html::openElement( 'div', [ 'class' => 'cdx-field__control' ] ) . "\n";

		# Namespace selector
		if ( count( FlaggedRevs::getReviewNamespaces() ) > 1 ) {
			$form .= Html::rawElement(
				'div',
				[ 'class' => 'cdx-field__item' ],
				FlaggedRevsHTML::getNamespaceMenu( $this->namespace, '' )
			);
		}

		# Default version selector
		$form .= Html::rawElement(
			'div',
			[ 'class' => 'cdx-field__item' ],
			FlaggedRevsHTML::getDefaultFilterMenu( $this->override )
		);

		# Restriction level selector
		if ( FlaggedRevs::getRestrictionLevels() ) {
			$form .= Html::rawElement(
				'div',
				[ 'class' => 'cdx-field__item' ],
				FlaggedRevsHTML::getRestrictionFilterMenu( $this->autoreview )
			);
		}

		$form .= Html::closeElement( 'div' ) . "\n";

		$form .= Html::rawElement(
				'div',
				[ 'class' => 'cdx-field__control' ],
				Html::submitButton( $this->msg( 'go' )->text(), [
					'class' => 'cdx-button cdx-button--action-progressive'
				] )
			) . "\n";

		$form .= Html::closeElement( 'fieldset' ) . "\n";
		$form .= Html::closeElement( 'form' ) . "\n";

		$this->getOutput()->addHTML( $form );
	}

	private function showPageList() {
		if ( $this->pager->getNumRows() ) {
			$this->getOutput()->addHTML( $this->pager->getNavigationBar() );
			$this->getOutput()->addHTML( $this->pager->getBody() );
			$this->getOutput()->addHTML( $this->pager->getNavigationBar() );
		} else {
			$this->getOutput()->addWikiMsg( 'configuredpages-none' );
		}
	}

	/**
	 * @param stdClass $row
	 * @return string HTML
	 */
	public function formatRow( $row ) {
		$title = Title::newFromRow( $row );
		# Link to page
		$linkRenderer = $this->getLinkRenderer();
		$link = $linkRenderer->makeLink( $title );
		# Link to page configuration
		$config = $linkRenderer->makeKnownLink(
			SpecialPage::getTitleFor( 'Stabilization' ),
			$this->msg( 'configuredpages-config' )->text(),
			[],
			[ 'page' => $title->getPrefixedDBkey() ]
		);
		# Show which version is the default (stable or draft)
		$msg = $row->fpc_override ? 'configuredpages-def-stable' : 'configuredpages-def-draft';
		$default = $this->msg( $msg )->escaped();
		# Autoreview/review restriction level
		$restr = '';
		if ( $row->fpc_level != '' ) {
			$restr = '[autoreview=' . htmlspecialchars( $row->fpc_level ) . ']';
		}
		# When these configuration settings expire
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
		return "<li>{$link} ({$config}) <b>[$default]</b> " .
			"{$restr}<i>{$expiry_description}</i></li>";
	}

	/**
	 * @return string
	 */
	protected function getGroupName() {
		return 'quality';
	}
}
