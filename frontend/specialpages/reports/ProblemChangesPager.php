<?php

/**
 * Query to list out outdated reviewed pages
 */
class ProblemChangesPager extends AlphabeticPager {
	/** @var ProblemChanges */
	private $mForm;

	/** @var string|null */
	private $category;

	/** @var int[] */
	private $namespace;

	/** @var string */
	private $tag;

	/** @var int */
	private $level;

	const PAGE_LIMIT = 100; // Don't get too expensive

	public function __construct( $form, $level = - 1, $category = '', $tag = '' ) {
		$this->mForm = $form;
		# Must be a content page...
		$this->namespace = FlaggedRevs::getReviewNamespaces();
		# Sanity check level: 0 = checked; 1 = quality; 2 = pristine
		$this->level = ( $level >= 0 && $level <= 2 ) ? $level : - 1;
		$this->tag = $tag;
		$this->category = $category ? str_replace( ' ', '_', $category ) : null;
		parent::__construct();
		// Don't get to expensive
		$this->mLimitsShown = [ 20, 50, 100 ];
		$this->setLimit( $this->mLimit ); // apply max limit
	}

	public function setLimit( $limit ) {
		$this->mLimit = min( $limit, self::PAGE_LIMIT );
	}

	public function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	public function getQueryInfo() {
		$tables = [ 'revision', 'change_tag', 'change_tag_def', 'page' ];
		$conds = [ 'ctd_id = ct_tag_id' ];

		$fields = [ 'page_namespace' , 'page_title', 'page_latest' ];
		# Show outdated "stable" pages
		if ( $this->level < 0 ) {
			$fields[] = 'fp_stable AS stable';
			$fields[] = 'fp_quality AS quality';
			$fields[] = 'fp_pending_since AS pending_since';
			# Find revisions that are tagged as such
			$conds[] = 'fp_pending_since IS NOT NULL';
			$conds[] = 'rev_page = fp_page_id';
			$conds[] = 'rev_id > fp_stable';
			$conds[] = 'ct_rev_id = rev_id';
			if ( $this->tag != '' ) {
				$conds['ctd_name'] = $this->tag;
			}
			$conds[] = 'page_id = fp_page_id';
			$useIndex = [
				'flaggedpages' => 'fp_pending_since',
				'change_tag' => 'change_tag_rev_tag_id'
			];

			# Filter by category
			if ( $this->category != '' ) {
				array_unshift( $tables, 'categorylinks' ); // order matters
				$conds[] = 'cl_from = fp_page_id';
				$conds['cl_to'] = $this->category;
				$useIndex['categorylinks'] = 'PRIMARY';
			}
			array_unshift( $tables, 'flaggedpages' ); // order matters
			$this->mIndexField = 'fp_pending_since';
			$this->mExtraSortFields = [ 'fp_page_id' ];
			$groupBy = 'fp_pending_since,fp_page_id';
		# Show outdated pages for a specific review level
		} else {
			$fields[] = 'fpp_rev_id AS stable';
			$fields[] = 'fpp_quality AS quality';
			$fields[] = 'fpp_pending_since AS pending_since';
			$conds[] = 'fpp_pending_since IS NOT NULL';
			$conds[] = 'page_id = fpp_page_id';
			# Find revisions that are tagged as such
			$conds[] = 'rev_page = page_id';
			$conds[] = 'rev_id > fpp_rev_id';
			$conds[] = 'rev_id = ct_rev_id';
			$conds['ctd_name'] = $this->tag;
			$useIndex = [
				'flaggedpage_pending' => 'fpp_quality_pending', 'change_tag' => 'change_tag_rev_tag_id' ];
			# Filter by review level
			$conds['fpp_quality'] = $this->level;
			# Filter by category
			if ( $this->category ) {
				array_unshift( $tables, 'categorylinks' ); // order matters
				$conds[] = 'cl_from = fpp_page_id';
				$conds['cl_to'] = $this->category;
				$useIndex['categorylinks'] = 'PRIMARY';
			}
			array_unshift( $tables, 'flaggedpage_pending' ); // order matters
			$this->mIndexField = 'fpp_pending_since';
			$this->mExtraSortFields = [ 'fpp_page_id' ];
			$groupBy = 'fpp_pending_since,fpp_page_id';
		}
		$fields[] = $this->mIndexField; // Pager needs this
		$conds['page_namespace'] = $this->namespace; // sanity check NS
		return [
			'tables'  => $tables,
			'fields'  => $fields,
			'conds'   => $conds,
			'options' => [ 'USE INDEX' => $useIndex,
				'GROUP BY' => $groupBy, 'STRAIGHT_JOIN' ]
		];
	}

	public function getIndexField() {
		return $this->mIndexField;
	}

	protected function doBatchLookups() {
		$lb = new LinkBatch();
		foreach ( $this->mResult as $row ) {
			$lb->add( $row->page_namespace, $row->page_title );
		}
		$lb->execute();
	}

	protected function getStartBody() {
		return '<ul>';
	}

	protected function getEndBody() {
		return '</ul>';
	}
}
