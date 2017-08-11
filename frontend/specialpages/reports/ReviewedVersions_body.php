<?php

class ReviewedVersions extends UnlistedSpecialPage {
	public function __construct() {
		parent::__construct( 'ReviewedVersions' );
	}

	public function execute( $par ) {
		$request = $this->getRequest();

		$this->setHeaders();
		# Our target page
		$this->target = $request->getText( 'page' );
		$this->page = Title::newFromText( $this->target );
		# Revision ID
		$this->oldid = $request->getVal( 'oldid' );
		$this->oldid = ( $this->oldid == 'best' ) ? 'best' : intval( $this->oldid );
		# We need a page...
		if ( is_null( $this->page ) ) {
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
		$review = $this->msg( 'reviewedversions-review' )->rawParams(
			$fdatim,
			Linker::userLink( $row->fr_user, $row->user_name ) .
			' ' . Linker::userToolLinks( $row->fr_user, $row->user_name ),
			$fdate, $ftime, $row->user_name
		)->text();
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

/**
 * Query to list out stable versions for a page
 */
class ReviewedVersionsPager extends ReverseChronologicalPager {
	public $mForm, $mConds;

	function __construct( $form, $conds = [], $title ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->pageID = $title->getArticleID();

		parent::__construct();
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$db = $this->getDatabase();
		$conds = $this->mConds;
		$conds['fr_page_id'] = $this->pageID;
		$conds[] = 'fr_rev_id = rev_id';
		$conds[] = $db->bitAnd( 'rev_deleted', Revision::DELETED_TEXT ) . ' = 0';
		$conds[] = 'fr_user = user_id';
		return [
			'tables'  => [ 'flaggedrevs', 'revision', 'user' ],
			'fields'  => 'fr_rev_id,fr_timestamp,rev_timestamp,fr_quality,fr_user,user_name',
			'conds'   => $conds
		];
	}

	function getIndexField() {
		return 'fr_rev_id';
	}
}
