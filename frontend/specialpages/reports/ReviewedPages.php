<?php

class ReviewedPages extends SpecialPage {
	/** @var ReviewedPagesPager */
	private $pager = null;

	/** @var int */
	private $namespace;

	/** @var int */
	private $type;

	/** @var bool */
	private $hideRedirs;

	public function __construct() {
		parent::__construct( 'ReviewedPages' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		$request = $this->getRequest();

		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:FlaggedRevs' );

		# Check if there is a featured level
		$maxType = FlaggedRevs::pristineVersions() ? 2 : 1;

		$this->namespace = $request->getInt( 'namespace' );
		$this->type = $request->getInt( 'level', -1 );
		$this->type = min( $this->type, $maxType );
		$this->hideRedirs = $request->getBool( 'hideredirs', true );

		$this->pager = new ReviewedPagesPager(
			$this, [], $this->type, $this->namespace, $this->hideRedirs );

		$this->showForm();
		$this->showPageList();
	}

	public function showForm() {
		// Text to explain level select (if there are several levels)
		if ( FlaggedRevs::qualityVersions() ) {
			$this->getOutput()->addWikiMsg( 'reviewedpages-list',
				$this->getLanguage()->formatNum( $this->pager->getNumRows() ) );
		}
		$form = Html::openElement( 'form', [
			'name' => 'reviewedpages',
			'action' => $this->getConfig()->get( 'Script' ),
			'method' => 'get',
		] );
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
		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBkey() ) . "\n";
		$form .= "</fieldset>";
		$form .= Html::closeElement( 'form ' ) . "\n";

		$this->getOutput()->addHTML( $form );
	}

	private function showPageList() {
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

	/**
	 * @param stdClass $row
	 * @return string HTML
	 */
	public function formatRow( $row ) {
		$title = Title::newFromRow( $row );
		$linkRenderer = $this->getLinkRenderer();
		$link = $linkRenderer->makeLink( $title ); # Link to page
		$dirmark = $this->getLanguage()->getDirMark(); # Direction mark
		$stxt = ''; # Size (bytes)
		$size = $row->page_len;
		if ( $size !== null ) {
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
			[ 'page' => $title->getPrefixedDBkey() ]
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

	/**
	 * @return string
	 */
	protected function getGroupName() {
		return 'quality';
	}
}
