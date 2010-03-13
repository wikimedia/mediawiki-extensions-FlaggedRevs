<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

class StablePages extends SpecialPage
{
	public function __construct() {
        parent::__construct( 'StablePages' );
    }

	public function execute( $par ) {
        global $wgRequest, $wgUser;

		$this->setHeaders();
		$this->skin = $wgUser->getSkin();

		$this->namespace = $wgRequest->getIntOrNull( 'namespace' );
		$this->precedence = $wgRequest->getIntOrNull( 'precedence' );

		$this->showForm();
		$this->showPageList();
	}

	protected function showForm() {
		global $wgOut, $wgScript;
		$wgOut->addHTML( wfMsgExt( 'stablepages-text', array( 'parseinline' ) ) );
		$fields = array();
		$namespaces = FlaggedRevs::getReviewNamespaces();
		if ( count( $namespaces ) > 1 ) {
			$fields[] = FlaggedRevsXML::getNamespaceMenu( $this->namespace, '' );
		}
		if ( FlaggedRevs::qualityVersions() ) {
			$fields[] = Xml::label( wfMsg( 'stablepages-precedence' ), 'wpPrecedence' ) .
				'&nbsp;' . FlaggedRevsXML::getPrecedenceMenu( $this->precedence );
		}
		if ( count( $fields ) ) {
			$form = Xml::openElement( 'form',
				array( 'name' => 'stablepages', 'action' => $wgScript, 'method' => 'get' ) );
			$form .= "<fieldset><legend>" . wfMsg( 'stablepages' ) . "</legend>\n";
			$form .= implode( '&nbsp;', $fields ) . '&nbsp';
			$form .= " " . Xml::submitButton( wfMsg( 'go' ) );
			$form .= Xml::hidden( 'title', $this->getTitle()->getPrefixedDBKey() );
			$form .= "</fieldset>\n";
			$form .= Xml::closeElement( 'form' );
			$wgOut->addHTML( $form );
		}
	}

	protected function showPageList() {
		global $wgOut;
		# Take this opportunity to purge out expired configurations
		FlaggedRevs::purgeExpiredConfigurations();
		$pager = new StablePagesPager( $this, array(), $this->namespace, $this->precedence );
		if ( $pager->getNumRows() ) {
			$wgOut->addHTML( $pager->getNavigationBar() );
			$wgOut->addHTML( $pager->getBody() );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHTML( wfMsgExt( 'stablepages-none', array( 'parse' ) ) );
		}
	}

	public function formatRow( $row ) {
		global $wgLang;

		$title = Title::makeTitle( $row->page_namespace, $row->page_title );
		$link = $this->skin->makeKnownLinkObj( $title, $title->getPrefixedText() );

		$stitle = SpecialPage::getTitleFor( 'Stabilization' );
		if ( count( FlaggedRevs::getProtectionLevels() ) ) {
			$config = $this->skin->makeKnownLinkObj( $title, wfMsgHtml( 'stablepages-config' ),
				'action=protect' );
		} else {
			$config = $this->skin->makeKnownLinkObj( $stitle, wfMsgHtml( 'stablepages-config' ),
				'page=' . $title->getPrefixedUrl() );
		}

		$type = '';
		// Show precedence if there are several possible levels
		if ( FlaggedRevs::qualityVersions() ) {
			if ( intval( $row->fpc_select ) === FLAGGED_VIS_PRISTINE ) {
				$type = wfMsgHtml( 'stablepages-prec-pristine' );
			} elseif ( intval( $row->fpc_select ) === FLAGGED_VIS_QUALITY ) {
				$type = wfMsgHtml( 'stablepages-prec-quality' );
			} else {
				$type = wfMsgHtml( 'stablepages-prec-none' );
			}
			$type = "(<b>{$type}</b>)";
		}

		$restr = '';
		if( $row->fpc_level != '' ) {
			$restr = 'autoreview='.htmlspecialchars($row->fpc_level);
			$restr = "[$restr]";
		}

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

		return "<li>{$link} ({$config}) {$type} {$restr}<i>{$expiry_description}</i></li>";
	}
}

/**
 * Query to list out stable versions for a page
 */
class StablePagesPager extends AlphabeticPager {
	public $mForm, $mConds, $namespace;

	// @param int $namespace (null for "all")
	// @param int $precedence (null for "all")
	function __construct( $form, $conds = array(), $namespace = null, $precedence = null ) {
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
		if ( !is_integer( $precedence ) ) {
			$precedence = null; // "all"
		}
		$this->precedence = $precedence;
		parent::__construct();
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$conds = $this->mConds;
		$conds[] = 'page_id = fpc_page_id';
		$conds['fpc_override'] = 1;
		if ( $this->precedence !== null ) {
			$conds['fpc_select'] = $this->precedence;
		}
		$conds['page_namespace'] = $this->namespace;
		return array(
			'tables' => array( 'flaggedpage_config', 'page' ),
			'fields' => 'page_namespace,page_title,fpc_expiry,fpc_page_id,fpc_select,fpc_level',
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
		while ( $row = $this->mResult->fetchObject() ) {
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
