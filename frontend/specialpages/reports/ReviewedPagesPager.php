<?php

/**
 * Query to list out reviewed pages
 */
class ReviewedPagesPager extends AlphabeticPager {
	/** @var ReviewedPages */
	public $mForm;

	/** @var array */
	public $mConds;

	/** @var int */
	public $namespace;

	/** @var int */
	public $type;

	/** @var bool */
	public $hideRedirs;

	public function __construct( $form, $conds = [], $type = 0, $namespace = 0, $hideRedirs = 1 ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->type = $type;
		# Must be a content page...
		if ( $namespace !== null ) {
			$namespace = intval( $namespace );
		}
		$vnamespaces = FlaggedRevs::getReviewNamespaces();
		if ( $namespace === null || !in_array( $namespace, $vnamespaces ) ) {
			$namespace = !$vnamespaces ? - 1 : $vnamespaces[0];
		}
		$this->namespace = $namespace;
		$this->hideRedirs = $hideRedirs;

		parent::__construct();
	}

	public function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	public function getQueryInfo() {
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

	public function getIndexField() {
		return 'fp_page_id';
	}

	public function doBatchLookups() {
		$lb = new LinkBatch();
		foreach ( $this->mResult as $row ) {
			$lb->add( $row->page_namespace, $row->page_title );
		}
		$lb->execute();
	}

	public function getStartBody() {
		return '<ul>';
	}

	public function getEndBody() {
		return '</ul>';
	}
}
