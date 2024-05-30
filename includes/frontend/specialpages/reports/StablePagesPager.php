<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Pager\AlphabeticPager;

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

	/** @var bool */
	private $indef;

	/** @var string|null */
	private $autoreview;

	/**
	 * @param StablePages $form
	 * @param array $conds
	 * @param int|null $namespace (null for "all")
	 * @param string $autoreview ('' for "all", 'none' for no restriction)
	 * @param bool $indef
	 */
	public function __construct( $form, $conds, $namespace, $autoreview, $indef ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->indef = $indef;
		// Must be content pages...
		if ( !is_int( $namespace ) || !FlaggedRevs::isReviewNamespace( $namespace ) ) {
			// Fallback to "all"
			$namespace = FlaggedRevs::getReviewNamespaces();
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
		$conds['fpc_override'] = 1;
		if ( $this->autoreview !== null ) {
			$conds['fpc_level'] = $this->autoreview;
		}
		$conds['page_namespace'] = $this->namespace;
		// Be sure not to include expired items
		if ( $this->indef ) {
			$conds['fpc_expiry'] = $this->mDb->getInfinity();
		} else {
			$conds[] = $this->mDb->expr( 'fpc_expiry', '>', $this->mDb->timestamp() );
		}
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
