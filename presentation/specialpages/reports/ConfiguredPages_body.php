<?php

// Assumes $wgFlaggedRevsProtection is off
class ConfiguredPages extends SpecialPage {
	protected $pager = null;

	public function __construct() {
		parent::__construct( 'ConfiguredPages' );
	}

	public function execute( $par ) {
		global $wgRequest, $wgUser;

		$this->setHeaders();
		$this->skin = $wgUser->getSkin();

		$this->namespace = $wgRequest->getIntOrNull( 'namespace' );
		$this->override = $wgRequest->getIntOrNull( 'stable' );
		$this->autoreview = $wgRequest->getVal( 'restriction', '' );

		$this->pager = new ConfiguredPagesPager(
			$this, array(), $this->namespace, $this->override, $this->autoreview );

		$this->showForm();
		$this->showPageList();
	}

	protected function showForm() {
		global $wgOut, $wgScript, $wgLang;
		# Explanatory text
		$wgOut->addWikiMsg( 'configuredpages-list',
			$wgLang->formatNum( $this->pager->getNumRows() ) );

		$fields = array();
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
			array( 'name' => 'configuredpages', 'action' => $wgScript, 'method' => 'get' ) );
		$form .= Html::hidden( 'title', $this->getTitle()->getPrefixedDBKey() );
		$form .= "<fieldset><legend>" . wfMsg( 'configuredpages' ) . "</legend>\n";
		$form .= implode( '&#160;', $fields ) . '<br/>';
		$form .= Xml::submitButton( wfMsg( 'go' ) );
		$form .= "</fieldset>\n";
		$form .= Html::closeElement( 'form' ) . "\n";

		$wgOut->addHTML( $form );
	}

	protected function showPageList() {
		global $wgOut;
		if ( $this->pager->getNumRows() ) {
			$wgOut->addHTML( $this->pager->getNavigationBar() );
			$wgOut->addHTML( $this->pager->getBody() );
			$wgOut->addHTML( $this->pager->getNavigationBar() );
		} else {
			$wgOut->addWikiMsg( 'configuredpages-none' );
		}
		# Purge expired entries on one in every 10 queries
		if ( !mt_rand( 0, 10 ) ) {
			FRPageConfig::purgeExpiredConfigurations();
		}
	}

	public function formatRow( $row ) {
		global $wgLang;
		$title = Title::newFromRow( $row );
		# Link to page
		$link = $this->skin->link( $title );
		# Link to page configuration
		$config = $this->skin->linkKnown(
			SpecialPage::getTitleFor( 'Stabilization' ),
			wfMsgHtml( 'configuredpages-config' ),
			array(),
			'page=' . $title->getPrefixedUrl()
		);
		# Show which version is the default (stable or draft)
		if ( intval( $row->fpc_override ) ) {
			$default = wfMsgHtml( 'configuredpages-def-stable' );
		} else {
			$default = wfMsgHtml( 'configuredpages-def-draft' );
		}
		# Autoreview/review restriction level
		$restr = '';
		if ( $row->fpc_level != '' ) {
			$restr = 'autoreview=' . htmlspecialchars( $row->fpc_level );
			$restr = "[$restr]";
		}
		# When these configuration settings expire
		if ( $row->fpc_expiry != 'infinity' && strlen( $row->fpc_expiry ) ) {
			$expiry_description = " (" . wfMsgForContent(
				'protect-expiring',
				$wgLang->timeanddate( $row->fpc_expiry ),
				$wgLang->date( $row->fpc_expiry ),
				$wgLang->time( $row->fpc_expiry )
			) . ")";
		} else {
			$expiry_description = "";
		}
		return "<li>{$link} ({$config}) <b>[$default]</b> " .
			"{$restr}<i>{$expiry_description}</i></li>";
	}
}

/**
 * Query to list out stable versions for a page
 */
class ConfiguredPagesPager extends AlphabeticPager {
	public $mForm, $mConds, $namespace, $override, $autoreview;

	/*
	* @param int $namespace (null for "all")
	* @param int $override (null for "either")
	* @param string $autoreview ('' for "all", 'none' for no restriction)
	*/
	function __construct( $form, $conds = array(), $namespace, $override, $autoreview ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		# Must be content pages...
		$validNS = FlaggedRevs::getReviewNamespaces();
		if ( is_integer( $namespace ) ) {
			if ( !in_array( $namespace, $validNS ) ) {
				$namespace = $validNS; // fallback to "all"
			}
		} else {
			$namespace = $validNS; // "all"
		}
		$this->namespace = $namespace;
		if ( !is_integer( $override ) ) {
			$override = null; // "all"
		}
		$this->override = $override;
		if ( $autoreview === 'none' ) {
			$autoreview = ''; // 'none' => ''
		} elseif ( $autoreview === '' ) {
			$autoreview = null; // '' => null
		}
		$this->autoreview = $autoreview;
		parent::__construct();
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$conds = $this->mConds;
		$conds[] = 'page_id = fpc_page_id';
		if ( $this->override !== null ) {
			$conds['fpc_override'] = $this->override;
		}
		if ( $this->autoreview !== null ) {
			$conds['fpc_level'] = $this->autoreview;
		}
		$conds['page_namespace'] = $this->namespace;
		# Be sure not to include expired items
		$encCutoff = $this->mDb->addQuotes( $this->mDb->timestamp() );
		$conds[] = "fpc_expiry > {$encCutoff}";
		return array(
			'tables' => array( 'flaggedpage_config', 'page' ),
			'fields' => array( 'page_namespace', 'page_title', 'fpc_override',
				'fpc_expiry', 'fpc_page_id', 'fpc_level' ),
			'conds'  => $conds,
			'options' => array()
		);
	}

	function getIndexField() {
		return 'fpc_page_id';
	}

	function getStartBody() {
		wfProfileIn( __METHOD__ );
		# Do a link batch query
		$lb = new LinkBatch();
		foreach ( $this->mResult as $row ) {
			$lb->add( $row->page_namespace, $row->page_title );
		}
		$lb->execute();
		wfProfileOut( __METHOD__ );
		return '<ul>';
	}
	
	function getEndBody() {
		return '</ul>';
	}
}
