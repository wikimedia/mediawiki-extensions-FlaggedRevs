<?php

/**
 * Query to list out stable versions for a page
 */
class StablePagesPager extends AlphabeticPager {
	/** @var StablePages */
	private $mForm;

	/** @var array */
	private $mConds;

	/** @var int|int[] */
	private $namespace;

	/** @var string */
	private $indef;

	/** @var string|null */
	private $autoreview;

	/**
	 * @param StablePages $form
	 * @param array $conds
	 * @param int|null $namespace (null for "all")
	 * @param string $autoreview ('' for "all", 'none' for no restriction)
	 * @param string $indef
	 */
	public function __construct( $form, $conds, $namespace, $autoreview, $indef ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->indef = $indef;
		// Must be content pages...
		$validNS = FlaggedRevs::getReviewNamespaces();
		if ( is_int( $namespace ) ) {
			if ( !in_array( $namespace, $validNS ) ) {
				$namespace = $validNS; // fallback to "all"
			}
		} else {
			$namespace = $validNS; // "all"
		}
		$this->namespace = $namespace;
		if ( $autoreview === 'none' ) {
			$autoreview = ''; // 'none' => ''
		} elseif ( $autoreview === '' ) {
			$autoreview = null; // '' => null
		}
		$this->autoreview = $autoreview;
		parent::__construct();
	}

	public function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	public function getQueryInfo() {
		$conds = $this->mConds;
		$conds[] = 'page_id = fpc_page_id';
		$conds['fpc_override'] = 1;
		if ( $this->autoreview !== null ) {
			$conds['fpc_level'] = $this->autoreview;
		}
		$conds['page_namespace'] = $this->namespace;
		// Be sure not to include expired items
		if ( $this->indef ) {
			$conds['fpc_expiry'] = $this->mDb->getInfinity();
		} else {
			$encCutoff = $this->mDb->addQuotes( $this->mDb->timestamp() );
			$conds[] = "fpc_expiry > {$encCutoff}";
		}
		return [
			'tables' => [ 'flaggedpage_config', 'page' ],
			'fields' => [ 'page_namespace', 'page_title', 'fpc_override',
				'fpc_expiry', 'fpc_page_id', 'fpc_level' ],
			'conds'  => $conds,
			'options' => []
		];
	}

	public function getIndexField() {
		return 'fpc_page_id';
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
