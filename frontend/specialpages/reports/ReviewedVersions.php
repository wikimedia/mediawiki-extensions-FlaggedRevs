<?php

class ReviewedVersions extends UnlistedSpecialPage {
	/** @var string|null */
	protected $target;

	/** @var Title|null */
	protected $page;

	/** @var int|null */
	protected $oldid;

	public function __construct() {
		parent::__construct( 'ReviewedVersions' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		$request = $this->getRequest();

		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:FlaggedRevs' );
		# Our target page
		$this->target = $request->getText( 'page' );
		$this->page = Title::newFromText( $this->target );
		# Revision ID
		$this->oldid = $request->getVal( 'oldid' );
		$this->oldid = ( $this->oldid == 'best' ) ? 'best' : intval( $this->oldid );
		# We need a page...
		if ( $this->page === null ) {
			$this->getOutput()->showErrorPage( 'notargettitle', 'notargettext' );
			return;
		}

		$this->showStableList();
	}

	protected function showStableList() {
		$out = $this->getOutput();
		# Must be a content page
		if ( !FlaggedRevs::inReviewNamespace( $this->page ) ) {
			$out->addWikiMsg( 'reviewedversions-none', $this->page->getPrefixedText() );
			return;
		}
		$pager = new ReviewedVersionsPager( $this, [], $this->page );
		$num = $pager->getNumRows();
		if ( $num ) {
			$out->addWikiMsg( 'reviewedversions-list',
				$this->page->getPrefixedText(), $this->getLanguage()->formatNum( $num ) );
			$out->addHTML( $pager->getNavigationBar() );
			$out->addHTML( "<ul>" . $pager->getBody() . "</ul>" );
			$out->addHTML( $pager->getNavigationBar() );
		} else {
			$out->addHTML( $this->msg( 'reviewedversions-none',	$this->page->getPrefixedText() )
					->parseAsBlock() );
		}
	}

	public function formatRow( $row ) {
		$rdatim = $this->getLanguage()->timeanddate( wfTimestamp( TS_MW, $row->rev_timestamp ),
			true );
		$fdatim = $this->getLanguage()->timeanddate( wfTimestamp( TS_MW, $row->fr_timestamp ), true );
		$fdate = $this->getLanguage()->date( wfTimestamp( TS_MW, $row->fr_timestamp ), true );
		$ftime = $this->getLanguage()->time( wfTimestamp( TS_MW, $row->fr_timestamp ), true );
		$review = $this->msg( 'reviewedversions-review' )
			->params( $fdatim )
			->rawParams( Linker::userLink( $row->fr_user, $row->user_name ) .
				' ' . Linker::userToolLinks( $row->fr_user, $row->user_name ) )
			->params( $fdate, $ftime, $row->user_name )
			->escaped();
		$lev = ( $row->fr_quality >= 1 )
			? $this->msg( 'revreview-hist-quality' )->escaped()
			: $this->msg( 'revreview-hist-basic' )->escaped();
		$link = $this->getLinkRenderer()->makeLink(
			$this->page,
			$rdatim,
			[],
			[ 'stableid' => $row->fr_rev_id ]
		);
		return '<li>' . $link . ' (' . $review . ') <strong>[' . $lev . ']</strong></li>';
	}
}
