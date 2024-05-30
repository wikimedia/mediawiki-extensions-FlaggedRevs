<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Pager\AlphabeticPager;

/**
 * Query to list out unreviewed pages
 */
class UnreviewedPagesPager extends AlphabeticPager {
	/** @var UnreviewedPages */
	private $mForm;

	/** @var bool */
	private $live;

	/** @var int */
	private $namespace;

	/** @var string|null */
	private $category;

	/** @var bool */
	private $showredirs;

	/** @var int */
	private $level;

	// Don't get too expensive
	private const PAGE_LIMIT = 50;

	/**
	 * @param UnreviewedPages $form
	 * @param bool $live
	 * @param int|null $namespace
	 * @param bool $redirs
	 * @param string|null $category
	 * @param int $level
	 */
	public function __construct(
		$form, $live, $namespace, $redirs = false, $category = null, $level = 0
	) {
		$this->mForm = $form;
		$this->live = (bool)$live;
		# Must be a content page...
		if ( $namespace !== null ) {
			$namespace = (int)$namespace;
		}
		# Must be a single NS for performance reasons
		if ( $namespace === null || !FlaggedRevs::isReviewNamespace( $namespace ) ) {
			$namespace = FlaggedRevs::getFirstReviewNamespace();
		}
		$this->namespace = $namespace;
		$this->category = $category ? str_replace( ' ', '_', $category ) : null;
		$this->level = intval( $level );
		$this->showredirs = (bool)$redirs;
		parent::__construct();
		// Don't get too expensive
		$this->mLimitsShown = [ 20, 50 ];
		$this->setLimit( $this->mLimit ); // apply max limit
	}

	/**
	 * @inheritDoc
	 */
	public function setLimit( $limit ) {
		$this->mLimit = min( $limit, self::PAGE_LIMIT );
	}

	/**
	 * @inheritDoc
	 */
	public function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	/**
	 * @inheritDoc
	 */
	public function getQueryInfo() {
		if ( !$this->live ) {
			return $this->getQueryCacheInfo();
		}
		$fields = [ 'page_namespace', 'page_title', 'page_len', 'page_id',
			'creation' => 'MIN(rev_timestamp)' ];
		$groupBy = [ 'page_namespace', 'page_title', 'page_len', 'page_id' ];
		# Filter by level
		$conds = [];
		if ( $this->level == 1 ) {
			$conds[] = $this->mDb->expr( 'fp_page_id', '=', null )->or( 'fp_quality', '=', 0 );
		} else {
			$conds['fp_page_id'] = null;
		}
		# Reviewable pages only
		$conds['page_namespace'] = $this->namespace;
		# No redirects
		if ( !$this->showredirs ) {
			$conds['page_is_redirect'] = 0;
		}
		# Filter by category
		if ( $this->category != '' ) {
			$tables = [ 'categorylinks', 'page', 'flaggedpages', 'revision' ];
			$fields[] = 'cl_sortkey';
			$groupBy[] = 'cl_sortkey';
			$conds['cl_to'] = $this->category;
			$conds[] = 'cl_from = page_id';
			# Note: single NS always specified
			if ( $this->namespace === NS_FILE ) {
				$conds['cl_type'] = 'file';
			} elseif ( $this->namespace === NS_CATEGORY ) {
				$conds['cl_type'] = 'subcat';
			} else {
				$conds['cl_type'] = 'page';
			}
			$this->mIndexField = 'cl_sortkey';
			$useIndex = [ 'categorylinks' => 'cl_sortkey' ];
		} else {
			$tables = [ 'page', 'flaggedpages', 'revision' ];
			$this->mIndexField = 'page_title';
			$useIndex = [ 'page' => 'page_name_title' ];
		}
		$useIndex['revision'] = 'rev_page_timestamp';
		return [
			'tables'  => $tables,
			'fields'  => $fields,
			'conds'   => $conds,
			'options' => [ 'USE INDEX' => $useIndex, 'GROUP BY' => $groupBy ],
			'join_conds' => [
				'revision'     => [ 'LEFT JOIN', 'rev_page=page_id' ], // Get creation date
				'flaggedpages' => [ 'LEFT JOIN', 'fp_page_id=page_id' ]
			]
		];
	}

	/**
	 * @return array
	 */
	private function getQueryCacheInfo() {
		$conds = [];
		$fields = [ 'page_namespace', 'page_title', 'page_len', 'page_id',
			'qc_value', 'creation' => 'MIN(rev_timestamp)' ];
		# Re-join on flaggedpages to double-check since things
		# could have changed since the cache date. Also, use
		# the proper cache for this level.
		if ( $this->level == 1 ) {
			$conds['qc_type'] = 'fr_unreviewedpages_q';
			$conds[] = $this->mDb->expr( 'fp_page_id', '=', null )->or( 'fp_quality', '<', 1 );
		} else {
			$conds['qc_type'] = 'fr_unreviewedpages';
			$conds['fp_page_id'] = null;
		}
		# Reviewable pages only
		$conds['qc_namespace'] = $this->namespace;
		# No redirects
		if ( !$this->showredirs ) {
			$conds['page_is_redirect'] = 0;
		}
		$this->mIndexField = 'qc_value'; // page_id
		# Filter by category
		if ( $this->category != '' ) {
			$tables = [ 'page', 'categorylinks', 'querycache', 'flaggedpages', 'revision' ];
			$conds['cl_to'] = $this->category;
			$conds[] = 'cl_from = qc_value'; // page_id
			# Note: single NS always specified
			if ( $this->namespace === NS_FILE ) {
				$conds['cl_type'] = 'file';
			} elseif ( $this->namespace === NS_CATEGORY ) {
				$conds['cl_type'] = 'subcat';
			} else {
				$conds['cl_type'] = 'page';
			}
		} else {
			$tables = [ 'page', 'querycache', 'flaggedpages', 'revision' ];
		}

		$useIndex = [ 'querycache' => 'qc_type', 'page' => 'PRIMARY', 'revision' => 'rev_page_timestamp' ];
		return [
			'tables'  => $tables,
			'fields'  => $fields,
			'conds'   => $conds,
			'options' => [ 'USE INDEX' => $useIndex, 'GROUP BY' => 'qc_value' ],
			'join_conds' => [
				'querycache'    => [ 'LEFT JOIN', 'qc_value=page_id' ],
				'revision'      => [ 'LEFT JOIN', 'rev_page=page_id' ], // Get creation date
				'flaggedpages'  => [ 'LEFT JOIN', 'fp_page_id=page_id' ],
				'categorylinks' => [ 'LEFT JOIN',
					[ 'cl_from=page_id', 'cl_to' => $this->category ] ]
			]
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getIndexField() {
		return $this->mIndexField;
	}

	/**
	 * @inheritDoc
	 */
	protected function doBatchLookups() {
		$lb = MediaWikiServices::getInstance()->getLinkBatchFactory()->newLinkBatch();
		foreach ( $this->mResult as $row ) {
			$lb->add( $row->page_namespace, $row->page_title );
		}
		$lb->execute();
	}

	/**
	 * @return string HTML
	 */
	protected function getStartBody() {
		return '<ul>';
	}

	/**
	 * @return string HTML
	 */
	protected function getEndBody() {
		return '</ul>';
	}
}
