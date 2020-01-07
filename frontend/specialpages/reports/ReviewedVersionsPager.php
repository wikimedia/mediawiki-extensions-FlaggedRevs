<?php

/**
 * Query to list out stable versions for a page
 */
class ReviewedVersionsPager extends ReverseChronologicalPager {
	/** @var ReviewedVersions */
	public $mForm;

	/** @var array */
	public $mConds;

	/** @var int */
	protected $pageID;

	/**
	 * @param ReviewedVersions $form
	 * @param array $conds
	 * @param Title $title
	 */
	public function __construct( $form, $conds, $title ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->pageID = $title->getArticleID();

		parent::__construct();
	}

	public function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	public function getQueryInfo() {
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

	public function getIndexField() {
		return 'fr_rev_id';
	}
}
