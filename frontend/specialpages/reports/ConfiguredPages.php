<?php

// Assumes $wgFlaggedRevsProtection is off
class ConfiguredPages extends SpecialPage {
	/** @var ConfiguredPagesPager|null */
	protected $pager = null;

	/** @var int|null */
	protected $namespace;

	/** @var int|null */
	protected $override;

	/** @var string|null */
	protected $autoreview;

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

	protected function showForm() {
		global $wgScript;

		# Explanatory text
		$this->getOutput()->addWikiMsg( 'configuredpages-list',
			$this->getLanguage()->formatNum( $this->pager->getNumRows() ) );

		$fields = [];
		# Namespace selector
		if ( count( FlaggedRevs::getReviewNamespaces() ) > 1 ) {
			$fields[] = FlaggedRevsXML::getNamespaceMenu( $this->namespace, '' );
		}
		# Default version selector
		$fields[] = FlaggedRevsXML::getDefaultFilterMenu( $this->override );
		# Restriction level selector
		if ( FlaggedRevs::getRestrictionLevels() ) {
			$fields[] = FlaggedRevsXML::getRestrictionFilterMenu( $this->autoreview );
		}

		$form = Html::openElement( 'form',
			[ 'name' => 'configuredpages', 'action' => $wgScript, 'method' => 'get' ] );
		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBkey() );
		$form .= "<fieldset><legend>" . $this->msg( 'configuredpages' )->escaped() . "</legend>\n";
		$form .= implode( '&#160;', $fields ) . '<br/>';
		$form .= Xml::submitButton( $this->msg( 'go' )->text() );
		$form .= "</fieldset>\n";
		$form .= Html::closeElement( 'form' ) . "\n";

		$this->getOutput()->addHTML( $form );
	}

	protected function showPageList() {
		if ( $this->pager->getNumRows() ) {
			$this->getOutput()->addHTML( $this->pager->getNavigationBar() );
			$this->getOutput()->addHTML( $this->pager->getBody() );
			$this->getOutput()->addHTML( $this->pager->getNavigationBar() );
		} else {
			$this->getOutput()->addWikiMsg( 'configuredpages-none' );
		}
	}

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
			[ 'page' => $title->getPrefixedURL() ]
		);
		# Show which version is the default (stable or draft)
		if ( intval( $row->fpc_override ) ) {
			$default = $this->msg( 'configuredpages-def-stable' )->escaped();
		} else {
			$default = $this->msg( 'configuredpages-def-draft' )->escaped();
		}
		# Autoreview/review restriction level
		$restr = '';
		if ( $row->fpc_level != '' ) {
			$restr = 'autoreview=' . htmlspecialchars( $row->fpc_level );
			$restr = "[$restr]";
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

	protected function getGroupName() {
		return 'quality';
	}
}
