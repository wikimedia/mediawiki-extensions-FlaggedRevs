<?php

use MediaWiki\MediaWikiServices;

/**
 * Query to list out stable versions for a page
 */
class ConfiguredPagesPager extends AlphabeticPager {
	/** @var ConfiguredPages */
	private $mForm;

	/** @var array */
	private $mConds;

	/** @var int|int[] */
	private $namespace;

	/** @var int|null */
	private $override;

	/** @var string|null */
	private $autoreview;

	/**
	 * @param ConfiguredPages $form
	 * @param array $conds
	 * @param int|null $namespace (null for "all")
	 * @param int|null $override (null for "either")
	 * @param string $autoreview ('' for "all", 'none' for no restriction)
	 */
	public function __construct( $form, $conds, $namespace, $override, $autoreview ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		# Must be content pages...
		$validNS = FlaggedRevs::getReviewNamespaces();
		if ( !is_int( $namespace ) || !in_array( $namespace, $validNS ) ) {
			// Fallback to "all"
			$namespace = $validNS;
		}
		$this->namespace = $namespace;
		if ( !is_int( $override ) ) {
			$override = null; // "all"
		}
		$this->override = $override;
		if ( $autoreview === 'none' ) {
			$autoreview = ''; // 'none' => ''
		} elseif ( $autoreview === '' ) {
			$autoreview = null; // '' => null
		}
		$this->autoreview = $autoreview;
		parent::__construct();
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
		$conds = $this->mConds;
		$conds[] = 'page_id = fpc_page_id';
		if ( $this->override !== null ) {
			$conds['fpc_override'] = $this->override;
		}
		if ( $this->autoreview !== null ) {
			$conds['fpc_level'] = $this->autoreview;
		}
		$conds['page_namespace'] = $this->namespace;
		# Be sure not to include expired items
		$conds[] = 'fpc_expiry > ' . $this->mDb->addQuotes( $this->mDb->timestamp() );
		return [
			'tables' => [ 'flaggedpage_config', 'page' ],
			'fields' => [ 'page_namespace', 'page_title', 'fpc_override',
				'fpc_expiry', 'fpc_page_id', 'fpc_level' ],
			'conds'  => $conds,
			'options' => []
		];
	}

	/**
	 * @return string
	 */
	public function getIndexField() {
		return 'fpc_page_id';
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
