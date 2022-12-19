<?php

use MediaWiki\MediaWikiServices;

/**
 * Query to list out outdated reviewed pages
 */
class PendingChangesPager extends AlphabeticPager {
	/** @var PendingChanges */
	private $mForm;

	/** @var string|null */
	private $category;

	/** @var int|int[] */
	private $namespace;

	/** @var int */
	private $level;

	/** @var int|null */
	private $size;

	/** @var bool */
	private $watched;

	/** @var bool */
	private $stable;

	// Don't get too expensive
	private const PAGE_LIMIT = 100;

	/**
	 * @param PendingChanges $form
	 * @param int|null $namespace
	 * @param int $level
	 * @param string $category
	 * @param int|null $size
	 * @param bool $watched
	 * @param bool $stable
	 */
	public function __construct( $form, $namespace, $level = -1, $category = '',
		$size = null, $watched = false, $stable = false
	) {
		$this->mForm = $form;
		# Must be a content page...
		$vnamespaces = FlaggedRevs::getReviewNamespaces();
		$namespace = $namespace === null ? $vnamespaces : (int)$namespace;
		# Sanity check
		if ( !in_array( $namespace, $vnamespaces ) ) {
			$namespace = $vnamespaces;
		}
		$this->namespace = $namespace;
		# Sanity check level: 0 = checked; 1 = quality; 2 = pristine
		$this->level = ( $level >= 0 && $level <= 2 ) ? $level : -1;
		$this->category = $category ? str_replace( ' ', '_', $category ) : null;
		$this->size = ( $size !== null ) ? intval( $size ) : null;
		$this->watched = (bool)$watched;
		$this->stable = $stable && !FlaggedRevs::isStableShownByDefault()
			&& !FlaggedRevs::useOnlyIfProtected();

		parent::__construct();
		# Don't get too expensive
		$this->mLimitsShown = [ 20, 50, 100 ];
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
	public function getDefaultQuery() {
		$query = parent::getDefaultQuery();
		$query['category'] = $this->category;
		return $query;
	}

	/**
	 * @inheritDoc
	 */
	public function getQueryInfo() {
		$tables = [ 'page', 'revision' ];
		$fields = [ 'page_namespace', 'page_title', 'page_len', 'rev_len', 'page_latest' ];
		$conds = [];
		# Show outdated "stable" versions
		if ( $this->level < 0 ) {
			$tables[] = 'flaggedpages';
			$fields[] = 'fp_stable AS stable';
			$fields[] = 'fp_quality AS quality';
			$fields[] = 'fp_pending_since AS pending_since';
			$conds[] = 'page_id = fp_page_id';
			$conds[] = 'rev_id = fp_stable'; // PK
			$conds[] = 'fp_pending_since IS NOT NULL';
			# Filter by pages configured to be stable
			if ( $this->stable ) {
				$tables[] = 'flaggedpage_config';
				$conds[] = 'fp_page_id = fpc_page_id';
				$conds['fpc_override'] = 1;
			}
			# Filter by category
			if ( $this->category != '' ) {
				$tables[] = 'categorylinks';
				$conds[] = 'cl_from = fp_page_id';
				$conds['cl_to'] = $this->category;
			}
			$this->mIndexField = 'fp_pending_since';
		# Show outdated version for a specific review level
		} else {
			$tables[] = 'flaggedpage_pending';
			$fields[] = 'fpp_rev_id AS stable';
			$fields[] = 'fpp_quality AS quality';
			$fields[] = 'fpp_pending_since AS pending_since';
			$conds[] = 'page_id = fpp_page_id';
			$conds[] = 'rev_id = fpp_rev_id'; // PK
			$conds[] = 'fpp_pending_since IS NOT NULL';
			# Filter by review level
			$conds['fpp_quality'] = $this->level;
			# Filter by pages configured to be stable
			if ( $this->stable ) {
				$tables[] = 'flaggedpage_config';
				$conds[] = 'fpp_page_id = fpc_page_id';
				$conds['fpc_override'] = 1;
			}
			# Filter by category
			if ( $this->category != '' ) {
				$tables[] = 'categorylinks';
				$conds[] = 'cl_from = fpp_page_id';
				$conds['cl_to'] = $this->category;
			}
			$this->mIndexField = 'fpp_pending_since';
		}
		$fields[] = $this->mIndexField; // Pager needs this
		# Filter namespace
		if ( $this->namespace !== null ) {
			$conds['page_namespace'] = $this->namespace;
		}
		# Filter by watchlist
		if ( $this->watched ) {
			$uid = $this->getUser()->getId();
			if ( $uid ) {
				$tables[] = 'watchlist';
				$conds['wl_user'] = $uid;
				$conds[] = 'page_namespace = wl_namespace';
				$conds[] = 'page_title = wl_title';
			}
		}
		# Filter by bytes changed
		if ( $this->size !== null && $this->size >= 0 ) {
			# Note: ABS(x-y) is broken due to mysql unsigned int design.
			$conds[] = 'GREATEST(page_len,rev_len)-LEAST(page_len,rev_len) <= ' .
				intval( $this->size );
		}
		return [
			'tables'  => $tables,
			'fields'  => $fields,
			'conds'   => $conds
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
