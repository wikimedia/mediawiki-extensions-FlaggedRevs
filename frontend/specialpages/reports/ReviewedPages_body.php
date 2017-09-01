<?php

class ReviewedPages extends SpecialPage {
	protected $pager = null;

	public function __construct() {
		parent::__construct( 'ReviewedPages' );
	}

	public function execute( $par ) {
		$request = $this->getRequest();

		$this->setHeaders();

		# Check if there is a featured level
		$maxType = FlaggedRevs::pristineVersions() ? 2 : 1;

		$this->namespace = $request->getInt( 'namespace' );
		$this->type = $request->getInt( 'level', - 1 );
		$this->type = min( $this->type, $maxType );
		$this->hideRedirs = $request->getBool( 'hideredirs', true );

		$this->pager = new ReviewedPagesPager(
			$this, [], $this->type, $this->namespace, $this->hideRedirs );

		$this->showForm();
		$this->showPageList();
	}

	public function showForm() {
		global $wgScript;

		// Text to explain level select (if there are several levels)
		if ( FlaggedRevs::qualityVersions() ) {
			$this->getOutput()->addWikiMsg( 'reviewedpages-list',
				$this->getLanguage()->formatNum( $this->pager->getNumRows() ) );
		}
		$form = Html::openElement( 'form',
			[ 'name' => 'reviewedpages', 'action' => $wgScript, 'method' => 'get' ] );
		$form .= "<fieldset><legend>" . $this->msg( 'reviewedpages-leg' )->escaped() . "</legend>\n";

		// show/hide links
		$showhide = [ $this->msg( 'show' )->text(), $this->msg( 'hide' )->text() ];
		$onoff = 1 - $this->hideRedirs;
		$link = $this->getLinkRenderer()->makeLink( $this->getPageTitle(), $showhide[$onoff], [],
			 [ 'hideredirs' => $onoff, 'namespace' => $this->namespace ]
		);
		$showhideredirs = $this->msg( 'whatlinkshere-hideredirs' )->rawParams( $link )->escaped();

		$fields = [];
		$namespaces = FlaggedRevs::getReviewNamespaces();
		if ( count( $namespaces ) > 1 ) {
			$fields[] = FlaggedRevsXML::getNamespaceMenu( $this->namespace ) . ' ';
		}
		if ( FlaggedRevs::qualityVersions() ) {
			$fields[] = FlaggedRevsXML::getLevelMenu( $this->type ) . ' ';
		}
		$form .= implode( ' ', $fields ) . ' ';
		$form .= $showhideredirs;

		if ( count( $fields ) ) {
			$form .= " " . Xml::submitButton( $this->msg( 'go' )->text() );
		}
		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBKey() ) . "\n";
		$form .= "</fieldset>";
		$form .= Html::closeElement( 'form ' ) . "\n";

		$this->getOutput()->addHTML( $form );
	}

	protected function showPageList() {
		$out = $this->getOutput();
		$num = $this->pager->getNumRows();
		if ( $num ) {
			$out->addHTML( $this->pager->getNavigationBar() );
			$out->addHTML( $this->pager->getBody() );
			$out->addHTML( $this->pager->getNavigationBar() );
		} else {
			$out->addHTML( $this->msg( 'reviewedpages-none' )->parseAsBlock() );
		}
	}

	public function formatRow( $row ) {
		$title = Title::newFromRow( $row );
		$linkRenderer = $this->getLinkRenderer();
		$link = $linkRenderer->makeLink( $title ); # Link to page
		$dirmark = $this->getLanguage()->getDirMark(); # Direction mark
		$stxt = ''; # Size (bytes)
		$size = $row->page_len;
		if ( !is_null( $size ) ) {
			if ( $size == 0 ) {
				$stxt = ' <small>' . $this->msg( 'historyempty' )->escaped() . '</small>';
			} else {
				$stxt = ' <small>' .
					$this->msg( 'historysize' )->numParams( $size )->escaped() .
					'</small>';
			}
		}
		# Link to list of reviewed versions for page
		$list = $linkRenderer->makeKnownLink(
			SpecialPage::getTitleFor( 'ReviewedVersions' ),
			$this->msg( 'reviewedpages-all' )->text(),
			[],
			[ 'page' => $title->getPrefixedUrl() ]
		);
		# Link to highest tier rev
		$best = '';
		if ( FlaggedRevs::qualityVersions() ) {
			$best = $linkRenderer->makeKnownLink(
				$title,
				$this->msg( 'reviewedpages-best' )->text(),
				[],
				[ 'stableid' => 'best' ]
			);
			$best = " [$best]";
		}

		return "<li>$link $dirmark $stxt ($list)$best</li>";
	}

	protected function getGroupName() {
		return 'quality';
	}
}

/**
 * Query to list out reviewed pages
 */
class ReviewedPagesPager extends AlphabeticPager {
	public $mForm, $mConds, $namespace, $type;

	function __construct( $form, $conds = [], $type = 0, $namespace = 0, $hideRedirs = 1 ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->type = $type;
		# Must be a content page...
		if ( !is_null( $namespace ) ) {
			$namespace = intval( $namespace );
		}
		$vnamespaces = FlaggedRevs::getReviewNamespaces();
		if ( is_null( $namespace ) || !in_array( $namespace, $vnamespaces ) ) {
			$namespace = !$vnamespaces ? - 1 : $vnamespaces[0];
		}
		$this->namespace = $namespace;
		$this->hideRedirs = $hideRedirs;

		parent::__construct();
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$conds = $this->mConds;
		$conds[] = 'page_id = fp_page_id';
		if ( $this->type >= 0 ) {
			$conds['fp_quality'] = $this->type;
		}
		if ( $this->hideRedirs ) {
			$conds['page_is_redirect'] = 0;
		}
		$conds['page_namespace'] = $this->namespace; // Sanity check NS
		return [
			'tables' => [ 'flaggedpages', 'page' ],
			'fields' => 'page_namespace,page_title,page_len,fp_page_id',
			'conds'  => $conds,
		];
	}

	function getIndexField() {
		return 'fp_page_id';
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
