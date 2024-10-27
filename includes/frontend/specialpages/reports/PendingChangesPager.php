<?php

use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Pager\TablePager;
use Wikimedia\Rdbms\RawSQLExpression;

/**
 * Query to list out outdated reviewed pages
 */
class PendingChangesPager extends TablePager {

	private PendingChanges $mForm;
	private ?string $category;
	/** @var int|int[] */
	private $namespace;
	private ?string $size;
	private bool $watched;
	private bool $stable;
	private ?string $tagFilter;
	// Don't get too expensive
	private const PAGE_LIMIT = 100;

	/**
	 * The unique sort fields for the sort options for unique paginate
	 */
	private const INDEX_FIELDS = [
		'fp_pending_since' => [ 'fp_pending_since' ],
	];

	/**
	 * @param PendingChanges $form
	 * @param int|null $namespace
	 * @param string $category
	 * @param int|null $size
	 * @param bool $watched
	 * @param bool $stable
	 * @param ?string $tagFilter
	 */
	public function __construct( $form, $namespace, string $category = '',
		?int $size = null, bool $watched = false, bool $stable = false, ?string $tagFilter = ''
	) {
		$this->mForm = $form;
		# Must be a content page...
		if ( $namespace !== null ) {
			$namespace = (int)$namespace;
		}
		# Sanity check
		if ( $namespace === null || !FlaggedRevs::isReviewNamespace( $namespace ) ) {
			$namespace = FlaggedRevs::getReviewNamespaces();
		}
		$this->namespace = $namespace;
		$this->category = $category ? str_replace( ' ', '_', $category ) : null;
		$this->tagFilter = $tagFilter ? str_replace( ' ', '_', $tagFilter ) : null;
		$this->size = $size;
		$this->watched = $watched;
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
	public function formatRow( $row ): string {
		return $this->mForm->formatRow( $row );
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultQuery(): array {
		$query = parent::getDefaultQuery();
		$query['category'] = $this->category;
		$query['tagFilter'] = $this->tagFilter;
		return $query;
	}

	/**
	 * @inheritDoc
	 */
	public function getQueryInfo(): array {
		$tables = [ 'page', 'revision', 'flaggedpages' ];
		$fields = [
			'page_namespace',
			'page_title',
			'page_len',
			'rev_len',
			'page_latest',
			'stable' => 'fp_stable',
			'quality' => 'fp_quality',
			'pending_since' => 'fp_pending_since'
		];
		$conds = [
			'page_id = fp_page_id',
			'rev_id = fp_stable',
			$this->mDb->expr( 'fp_pending_since', '!=', null )
		];

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
		# Index field for sorting
		$this->mIndexField = 'fp_pending_since';
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
			$conds[] = new RawSQLExpression(
				"(GREATEST(page_len, rev_len) - LEAST(page_len, rev_len)) <= " . intval( $this->size )
			);
		}
		# Filter by tag
		if ( $this->tagFilter !== null && $this->tagFilter !== '' ) {
			$tables[] = 'change_tag';
			$tables[] = 'change_tag_def';
			$conds[] = 'ct_tag_id = ctd_id';
			$conds[] = 'ct_rev_id = rev_id';
			$conds['ctd_name'] = $this->tagFilter;
		}
		# Don't display pages with expired protection (T350527)
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			$tables[] = 'flaggedpage_config';
			$conds[] = 'fpc_page_id = fp_page_id';
			$conds[] = new RawSQLExpression( $this->mDb->buildComparison( '>',
					[ 'fpc_expiry' => $this->mDb->timestamp() ] ) . ' OR fpc_expiry = "infinity"'
			);
		}
		# Set sorting options
		$sortField = $this->getRequest()->getVal( 'sort', 'fp_pending_since' );
		$sortOrder = $this->getRequest()->getVal( 'asc' ) ? 'ASC' : 'DESC';
		$options = [ 'ORDER BY' => "$sortField $sortOrder" ];
		# Return query information
		return [
			'tables' => $tables,
			'fields' => $fields,
			'conds' => $conds,
			'options' => $options,
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
		$this->mResult->seek( 0 );
		$lb = MediaWikiServices::getInstance()->getLinkBatchFactory();
		$batch = $lb->newLinkBatch();
		foreach ( $this->mResult as $row ) {
			$batch->add( $row->page_namespace, $row->page_title );
		}
		$batch->execute();
	}

	/**
	 * @inheritDoc
	 * @since 1.43
	 */
	public function getDefaultSort(): string {
		return 'fp_pending_since';
	}

	/**
	 * @inheritDoc
	 * @since 1.43
	 */
	protected function getFieldNames(): array {
		$fields = [
			'page_title' => 'pendingchanges-table-page',
			'review' => 'pendingchanges-table-review',
			'rev_len' => 'pendingchanges-table-size',
			'fp_pending_since' => 'pendingchanges-table-pending-since',
		];

		if ( $this->getAuthority()->isAllowed( 'unwatchedpages' ) ) {
			$fields['watching'] = 'pendingchanges-table-watching';
		}

		return $fields;
	}

	/**
	 * @inheritDoc
	 * @since 1.43
	 */
	public function formatValue( $name, $value ): ?string {
		return htmlspecialchars( $value );
	}

	/**
	 * @inheritDoc
	 * @since 1.43
	 */
	protected function isFieldSortable( $field ): bool {
		return isset( self::INDEX_FIELDS[$field] );
	}

	/**
	 * Builds and returns the start body HTML for the table.
	 *
	 * @return string HTML
	 * @since 1.43
	 */
	protected function getStartBody(): string {
		return Html::openElement( 'div', [ 'class' => 'cdx-table mw-fr-pending-changes-table' ] ) .
			$this->buildTableHeader() .
			Html::openElement( 'div', [ 'class' => 'cdx-table__table-wrapper' ] ) .
			$this->buildTableElement();
	}

	/**
	 * Builds and returns the table header HTML.
	 *
	 * @return string HTML
	 */
	private function buildTableHeader(): string {
		$headerCaption = $this->buildHeaderCaption();
		$headerContent = $this->buildTableCaption( 'cdx-table__header__header-content' );

		return Html::rawElement(
			'div',
			[ 'class' => 'cdx-table__header' ],
			$headerCaption . $headerContent
		);
	}

	/**
	 * Builds and returns the header caption HTML.
	 *
	 * @return string HTML
	 */
	private function buildHeaderCaption(): string {
		return Html::rawElement(
			'div',
			[ 'class' => 'cdx-table__header__caption', 'aria-hidden' => 'true' ],
			$this->msg( 'pendingchanges-table-caption' )->text()
		);
	}

	/**
	 * Retrieves the count of pending pages.
	 *
	 * @return int The count of pending pages.
	 */
	private function getPendingCount(): int {
		return $this->mDb->selectRowCount(
			'flaggedpages', '*', [ $this->mDb->expr( 'fp_pending_since', '!=', null ) ], __METHOD__
		);
	}

	/**
	 * Builds and returns the table element HTML.
	 *
	 * @return string HTML
	 */
	private function buildTableElement(): string {
		$caption = Html::element( 'caption', [], $this->msg( 'pendingchanges-table-caption' )->text() );
		$thead = $this->buildTableHeaderCells();

		return Html::openElement( 'table', [ 'class' => 'cdx-table__table cdx-table__table--borders-vertical' ] ) .
			$caption .
			$thead .
			Html::openElement( 'tbody' );
	}

	/**
	 * Builds and returns the table header cells HTML.
	 *
	 * @return string HTML
	 */
	private function buildTableHeaderCells(): string {
		$fields = $this->getFieldNames();
		$headerCells = '';

		foreach ( $fields as $field => $labelKey ) {
			$class = ( $field === 'review' || $field === 'history' ) ? 'cdx-table__table__cell--align-center' : '';

			if ( $field === 'review' ) {
				$headerCells .= Html::rawElement(
					'th',
					[ 'scope' => 'col', 'class' => $class ],
					Html::rawElement(
						'span',
						[ 'class' => 'fr-cdx-icon-eye', 'aria-hidden' => 'true' ]
					)
				);
			} elseif ( $field === 'history' ) {
				$headerCells .= Html::rawElement(
					'th',
					[ 'scope' => 'col', 'class' => $class ],
					Html::rawElement(
						'span',
						[ 'class' => 'fr-cdx-icon-clock', 'aria-hidden' => 'true' ]
					)
				);
			} elseif ( $this->isFieldSortable( $field ) ) {
				$isCurrentSortField = ( $this->mSort === $field );
				$currentAsc = $this->getRequest()->getVal( 'asc', '1' );

				$newSortAsc = $isCurrentSortField && $currentAsc === '1' ? '' : '1';
				$newSortDesc = $isCurrentSortField && $currentAsc === '1' ? '1' : '';

				$ariaSort = 'none';
				if ( $isCurrentSortField ) {
					$ariaSort = $currentAsc === '1' ? 'ascending' : 'descending';
				}

				$iconClass = 'fr-cdx-icon-sort-vertical';
				if ( $isCurrentSortField ) {
					$iconClass = $currentAsc === '1' ? 'fr-icon-asc' : 'fr-icon-desc';
				}

				$currentParams = $this->getRequest()->getValues();
				unset( $currentParams['title'], $currentParams['sort'], $currentParams['asc'], $currentParams['desc'] );
				$currentParams['sort'] = $field;
				$currentParams['asc'] = $newSortAsc;
				$currentParams['desc'] = $newSortDesc;

				$href = $this->getTitle()->getLocalURL( $currentParams );

				$headerCells .= Html::rawElement(
					'th',
					[
						'scope' => 'col',
						'class' => 'cdx-table__table__cell--has-sort ' . $class,
						'aria-sort' => $ariaSort,
					],
					Html::rawElement(
						'a',
						[ 'href' => $href ],
						Html::rawElement(
							'button',
							[
								'class' => 'cdx-table__table__sort-button',
								'aria-selected' => $isCurrentSortField ? 'true' : 'false'
							],
							$this->msg( $labelKey )->text() . ' ' .
							Html::rawElement(
								'span',
								[ 'class' => 'cdx-icon cdx-icon--small cdx-table__table__sort-icon ' .
									$iconClass, 'aria-hidden' => 'true' ]
							)
						)
					)
				);
			} else {
				$headerCells .= Html::rawElement(
					'th',
					[ 'scope' => 'col', 'class' => $class ],
					Html::rawElement(
						'span',
						[ 'class' => 'cdx-table__th-content' ],
						$this->msg( $labelKey )->text()
					)
				);
			}
		}

		return Html::rawElement(
			'thead',
			[],
			Html::rawElement(
				'tr',
				[],
				$headerCells
			)
		);
	}

	/**
	 * Builds and returns the end body HTML for the table.
	 *
	 * @return string HTML
	 * @since 1.43
	 */
	protected function getEndBody(): string {
		return Html::closeElement( 'tbody' ) .
			Html::closeElement( 'table' ) .
			Html::closeElement( 'div' ) .
			$this->buildTableCaption( 'cdx-table__footer' ) .
			Html::closeElement( 'div' );
	}

	/**
	 * Builds and returns the table caption, currently used both in
	 * the header and the footer.
	 *
	 * @param string $class The class to use for the returning element
	 * @return string HTML
	 */
	private function buildTableCaption( string $class ): string {
		$pendingCount = $this->getPendingCount();
		$formattedCount = $this->getLanguage()->formatNum( $pendingCount );
		$chip = Html::element( 'strong', [ 'class' => 'cdx-info-chip' ], $formattedCount );
		$message = $this->msg( 'pendingchanges-table-footer', $chip )
			->numParams( $pendingCount )->text();

		return Html::rawElement(
			'div',
			[ 'class' => $class ],
			Html::rawElement( 'span', [], $message )
		);
	}
}
