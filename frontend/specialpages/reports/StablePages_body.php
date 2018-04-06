<?php

// Assumes $wgFlaggedRevsProtection is on
class StablePages extends SpecialPage {
	protected $pager = null;

	public function __construct() {
		parent::__construct( 'StablePages' );
	}

	public function execute( $par ) {
		$request = $this->getRequest();

		$this->setHeaders();

		$this->namespace = $request->getIntOrNull( 'namespace' );
		$this->autoreview = $request->getVal( 'restriction', '' );
		$this->indef = $request->getBool( 'indef', false );

		$this->pager = new StablePagesPager( $this, [],
			$this->namespace, $this->autoreview, $this->indef );

		$this->showForm();
		$this->showPageList();
	}

	protected function showForm() {
		global $wgScript;

		$this->getOutput()->addWikiMsg( 'stablepages-list',
			$this->getLanguage()->formatNum( $this->pager->getNumRows() ) );

		$fields = [];
		// Namespace selector
		if ( count( FlaggedRevs::getReviewNamespaces() ) > 1 ) {
			$fields[] = FlaggedRevsXML::getNamespaceMenu( $this->namespace, '' );
		}
		// Restriction level selector
		if ( FlaggedRevs::getRestrictionLevels() ) {
			$fields[] = FlaggedRevsXML::getRestrictionFilterMenu( $this->autoreview );
		}
		$fields[] = Xml::checkLabel( $this->msg( 'stablepages-indef' )->text(), 'indef',
			'stablepages-indef', $this->indef );

		$form = Html::openElement( 'form',
			[ 'name' => 'stablepages', 'action' => $wgScript, 'method' => 'get' ] );
		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBKey() );
		$form .= "<fieldset><legend>" . $this->msg( 'stablepages' )->text() . "</legend>\n";
		$form .= implode( '&#160;', $fields ) . '&nbsp';
		$form .= " " . Xml::submitButton( $this->msg( 'go' )->text() );
		$form .= "</fieldset>\n";
		$form .= Html::closeElement( 'form' ) . "\n";

		$this->getOutput()->addHTML( $form );
	}

	protected function showPageList() {
		$out = $this->getOutput();
		if ( $this->pager->getNumRows() ) {
			$out->addHTML( $this->pager->getNavigationBar() );
			$out->addHTML( $this->pager->getBody() );
			$out->addHTML( $this->pager->getNavigationBar() );
		} else {
			$out->addWikiMsg( 'stablepages-none' );
		}
	}

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

	protected function getGroupName() {
		return 'quality';
	}
}

/**
 * Query to list out stable versions for a page
 */
class StablePagesPager extends AlphabeticPager {
	public $mForm, $mConds, $namespace, $override;

	/**
	 * @param StablePages $form
	 * @param array $conds
	 * @param int $namespace (null for "all")
	 * @param string $autoreview ('' for "all", 'none' for no restriction)
	 * @param string $indef
	 */
	function __construct( $form, $conds = [], $namespace, $autoreview, $indef ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->indef = $indef;
		// Must be content pages...
		$validNS = FlaggedRevs::getReviewNamespaces();
		if ( is_int( $namespace ) ) {
			if ( !in_array( $namespace, $validNS ) ) {
				$namespace = $validNS; // fallback to "all"
			}
		} else {
			$namespace = $validNS; // "all"
		}
		$this->namespace = $namespace;
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
		$conds['fpc_override'] = 1;
		if ( $this->autoreview !== null ) {
			$conds['fpc_level'] = $this->autoreview;
		}
		$conds['page_namespace'] = $this->namespace;
		// Be sure not to include expired items
		if ( $this->indef ) {
			$conds['fpc_expiry'] = $this->mDb->getInfinity();
		} else {
			$encCutoff = $this->mDb->addQuotes( $this->mDb->timestamp() );
			$conds[] = "fpc_expiry > {$encCutoff}";
		}
		return [
			'tables' => [ 'flaggedpage_config', 'page' ],
			'fields' => [ 'page_namespace', 'page_title', 'fpc_override',
				'fpc_expiry', 'fpc_page_id', 'fpc_level' ],
			'conds'  => $conds,
			'options' => []
		];
	}

	function getIndexField() {
		return 'fpc_page_id';
	}

	function doBatchLookups() {
		$lb = new LinkBatch();
		foreach ( $this->mResult as $row ) {
			$lb->add( $row->page_namespace, $row->page_title );
		}
		$lb->execute();
	}

	function getStartBody() {
		return '<ul>';
	}

	function getEndBody() {
		return '</ul>';
	}
}
