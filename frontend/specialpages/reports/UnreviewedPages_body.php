<?php

class UnreviewedPages extends SpecialPage {
	protected $pager = null;

	public function __construct() {
		parent::__construct( 'UnreviewedPages', 'unreviewedpages' );
	}

	public function execute( $par ) {
		$request = $this->getRequest();

		$this->setHeaders();
		if ( !$this->getUser()->isAllowed( 'unreviewedpages' ) ) {
			throw new PermissionsError( 'unreviewedpages' );
		}

		$this->currentUnixTS = wfTimestamp( TS_UNIX ); // now

		# Get default namespace
		$namespaces = FlaggedRevs::getReviewNamespaces();
		$defaultNS = !$namespaces ? NS_MAIN : $namespaces[0];

		$this->namespace = $request->getIntOrNull( 'namespace', $defaultNS );
		$category = trim( $request->getVal( 'category' ) );
		$catTitle = Title::makeTitleSafe( NS_CATEGORY, $category );
		$this->category = is_null( $catTitle ) ? '' : $catTitle->getText();
		$this->level = $request->getInt( 'level' );
		$this->hideRedirs = $request->getBool( 'hideredirs', true );
		$this->live = self::generalQueryOK();

		$this->pager = new UnreviewedPagesPager( $this, $this->live,
			$this->namespace, !$this->hideRedirs, $this->category, $this->level );

		$this->showForm();
		$this->showPageList();
	}

	protected function showForm() {
		global $wgScript;

		# Add explanatory text
		$this->getOutput()->addWikiMsg( 'unreviewedpages-list',
			$this->getLanguage()->formatNum( $this->pager->getNumRows() ) );

		# show/hide links
		$showhide = [ $this->msg( 'show' )->text(), $this->msg( 'hide' )->text() ];
		$onoff = 1 - $this->hideRedirs;
		$link = $this->getLinkRenderer()->makeLink( $this->getPageTitle(), $showhide[$onoff], [],
			[ 'hideredirs' => $onoff, 'category' => $this->category,
				'namespace' => $this->namespace ]
		);
		$showhideredirs = $this->msg( 'whatlinkshere-hideredirs' )->rawParams( $link )->escaped();

		# Add form...
		$form = Html::openElement( 'form', [ 'name' => 'unreviewedpages',
			'action' => $wgScript, 'method' => 'get' ] ) . "\n";
		$form .= "<fieldset><legend>" . $this->msg( 'unreviewedpages-legend' )->escaped() . "</legend>\n";
		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBKey() ) . "\n";
		# Add dropdowns as needed
		if ( count( FlaggedRevs::getReviewNamespaces() ) > 1 ) {
			$form .= FlaggedRevsXML::getNamespaceMenu( $this->namespace ) . '&#160;';
		}
		if ( FlaggedRevs::qualityVersions() ) {
			$form .= FlaggedRevsXML::getLevelMenu( $this->level, false, 1 ) . '&#160;';
		}
		$form .=
			"<span style='white-space: nowrap;'>" .
			Xml::label( $this->msg( 'unreviewedpages-category' )->text(), 'category' ) . '&#160;' .
			Xml::input( 'category', 30, $this->category, [ 'id' => 'category' ] ) .
			'</span><br />';
		$form .= $showhideredirs . '&#160;&#160;';
		$form .= Xml::submitButton( $this->msg( 'allpagessubmit' )->text() );
		$form .= '</fieldset>';
		$form .= Html::closeElement( 'form' ) . "\n";

		# Query may get too slow to be live...
		if ( !$this->live ) {
			$dbr = wfGetDB( DB_REPLICA );
			$ts = $dbr->selectField( 'querycache_info', 'qci_timestamp',
				[ 'qci_type' => 'fr_unreviewedpages' ], __METHOD__ );
			if ( $ts ) {
				$ts = wfTimestamp( TS_MW, $ts );
				$td = $this->getLanguage()->timeanddate( $ts );
				$d = $this->getLanguage()->date( $ts );
				$t = $this->getLanguage()->time( $ts );
				$form .= $this->msg( 'perfcachedts', $td, $d, $t )->parseAsBlock();
			} else {
				$form .= $this->msg( 'perfcached' )->parseAsBlock();
			}
		}

		$this->getOutput()->addHTML( $form );
	}

	protected function showPageList() {
		$out = $this->getOutput();
		if ( $this->pager->getNumRows() ) {
			$out->addHTML( $this->pager->getNavigationBar() );
			$out->addHTML( $this->pager->getBody() );
			$out->addHTML( $this->pager->getNavigationBar() );
		} else {
			$out->addWikiMsg( 'unreviewedpages-none' );
		}
	}

	public function formatRow( $row ) {
		$title = Title::newFromRow( $row );

		$stxt = $underReview = $watching = '';
		$linkRenderer = $this->getLinkRenderer();
		$link = $linkRenderer->makeLink( $title, null, [], [ 'redirect' => 'no' ] );
		$dirmark = $this->getLanguage()->getDirMark();
		$hist = $linkRenderer->makeKnownLink(
			$title,
			$this->msg( 'hist' )->text(),
			[],
			[ 'action' => 'history' ]
		);
		$size = $row->page_len;
		if ( !is_null( $size ) ) {
			$stxt = ( $size == 0 )
				? $this->msg( 'historyempty' )->escaped()
				: $this->msg( 'historysize' )->numParams( $size )->escaped();
			$stxt = " <small>$stxt</small>";
		}
		# Get how long the first unreviewed edit has been waiting...
		$firstPendingTime = wfTimestamp( TS_UNIX, $row->creation );
		$hours = ( $this->currentUnixTS - $firstPendingTime ) / 3600;
		// After three days, just use days
		if ( $hours > ( 3 * 24 ) ) {
			$days = round( $hours / 24, 0 );
			$age = ' ' . $this->msg( 'unreviewedpages-days' )->numParams( $days )->escaped();
		// If one or more hours, use hours
		} elseif ( $hours >= 1 ) {
			$hours = round( $hours, 0 );
			$age = ' ' . $this->msg( 'unreviewedpages-hours' )->numParams( $hours )->escaped();
		} else {
			$age = ' ' . $this->msg( 'unreviewedpages-recent' )->escaped(); // hot off the press :)
		}
		if ( $this->getUser()->isAllowed( 'unwatchedpages' ) ) {
			$uw = FRUserActivity::numUsersWatchingPage( $title );
			$watching = $uw
				? $this->msg( 'unreviewedpages-watched' )->numParams( $uw )->escaped()
				: $this->msg( 'unreviewedpages-unwatched' )->escaped();
			$watching = " $watching"; // Oh-noes!
		} else {
			$uw = - 1;
		}
		$css = self::getLineClass( $hours, $uw );
		$css = $css ? " class='$css'" : "";

		# Show if a user is looking at this page
		list( $u, $ts ) = FRUserActivity::getUserReviewingPage( $row->page_id );
		if ( $u !== null ) {
			$underReview = " <span class='fr-under-review'>" .
				$this->msg( 'unreviewedpages-viewing' )->escaped() . '</span>';
		}

		return ( "<li{$css}>{$link} $dirmark {$stxt} ({$hist})" .
			"{$age}{$watching}{$underReview}</li>" );
	}

	protected static function getLineClass( $hours, $uw ) {
		if ( $uw == 0 ) {
			return 'fr-unreviewed-unwatched';
		} elseif ( $hours > 20 * 24 ) {
			return 'fr-pending-long2';
		} elseif ( $hours > 7 * 24 ) {
			return 'fr-pending-long';
		} else {
			return "";
		}
	}

	/**
	 * There may be many pages, most of which are reviewed
	 * @return bool
	 */
	public static function generalQueryOK() {
		$namespaces = FlaggedRevs::getReviewNamespaces();
		if ( !$namespaces || !wfQueriesMustScale() ) {
			return true;
		}
		# Get est. of fraction of pages that are reviewed
		$dbr = wfGetDB( DB_REPLICA );
		$reviewedpages = $dbr->estimateRowCount( 'flaggedpages', '*', [], __METHOD__ );
		$pages = $dbr->estimateRowCount( 'page', '*',
			[ 'page_namespace' => $namespaces ],
			__METHOD__
		);
		$ratio = $pages / ( $pages - $reviewedpages );
		# If dist. is equal, # of rows scanned = $ratio * LIMIT (or until list runs out)
		return ( $ratio <= 400 );
	}

	/**
	 * Run an update to the cached query rows
	 * @return void
	 */
	public static function updateQueryCache() {
		$rNamespaces = FlaggedRevs::getReviewNamespaces();
		if ( empty( $rNamespaces ) ) {
			return;
		}
		$dbr = wfGetDB( DB_REPLICA );

		$insertRows = [];
		// Find pages that were never reviewed at all...
		$res = $dbr->select(
			[ 'page', 'flaggedpages' ],
			[ 'page_namespace', 'page_title', 'page_id' ],
			[ 'page_namespace' => $rNamespaces,
				'page_is_redirect' => 0, // no redirects
				'fp_page_id IS NULL' ],
			__METHOD__,
			[ 'LIMIT' => 5000 ],
			[ 'flaggedpages' => [ 'LEFT JOIN', 'fp_page_id = page_id' ] ]
		);
		foreach ( $res as $row ) {
			$insertRows[] = [
				'qc_type'       => 'fr_unreviewedpages',
				'qc_namespace'  => $row->page_namespace,
				'qc_title'      => $row->page_title,
				'qc_value'      => $row->page_id
			];
		}
		$dbr->freeResult( $res );

		$dbw = wfGetDB( DB_MASTER );
		$lbFactory = \MediaWiki\MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		$dbw->startAtomic( __METHOD__ );
		# Clear out any old cached data
		$dbw->delete( 'querycache', [ 'qc_type' => 'fr_unreviewedpages' ], __METHOD__ );
		# Insert new data...
		if ( $insertRows ) {
			$dbw->insert( 'querycache', $insertRows, __METHOD__ );
		}
		# Update the querycache_info record for the page
		$dbw->delete( 'querycache_info',
			[ 'qci_type' => 'fr_unreviewedpages' ], __METHOD__ );
		$dbw->insert( 'querycache_info',
			[ 'qci_type' => 'fr_unreviewedpages', 'qci_timestamp' => $dbw->timestamp() ],
			__METHOD__
		);
		$dbw->endAtomic( __METHOD__ );
		$lbFactory->commitMasterChanges( __METHOD__ );

		$insertRows = [];
		// Find pages that were never marked as "quality"...
		$res = $dbr->select(
			[ 'page', 'flaggedpages' ],
			[ 'page_namespace', 'page_title', 'page_id' ],
			[ 'page_namespace' => $rNamespaces,
				'page_is_redirect' => 0, // no redirects
				'fp_page_id IS NULL OR fp_quality = 0' ],
			__METHOD__,
			[ 'LIMIT' => 5000 ],
			[ 'flaggedpages' => [ 'LEFT JOIN','fp_page_id = page_id' ] ]
		);
		foreach ( $res as $row ) {
			$insertRows[] = [
				'qc_type'       => 'fr_unreviewedpages_q',
				'qc_namespace'  => $row->page_namespace,
				'qc_title'      => $row->page_title,
				'qc_value'      => $row->page_id
			];
		}
		$dbr->freeResult( $res );

		$dbw->startAtomic( __METHOD__ );
		# Clear out any old cached data
		$dbw->delete( 'querycache', [ 'qc_type' => 'fr_unreviewedpages_q' ], __METHOD__ );
		# Insert new data...
		if ( $insertRows ) {
			$dbw->insert( 'querycache', $insertRows, __METHOD__ );
		}
		# Update the querycache_info record for the page
		$dbw->delete( 'querycache_info',
			[ 'qci_type' => 'fr_unreviewedpages_q' ], __METHOD__ );
		$dbw->insert( 'querycache_info',
			[ 'qci_type' => 'fr_unreviewedpages_q', 'qci_timestamp' => $dbw->timestamp() ],
			__METHOD__ );
		$dbw->endAtomic( __METHOD__ );
		$lbFactory->commitMasterChanges( __METHOD__ );
	}

	protected function getGroupName() {
		return 'quality';
	}
}

/**
 * Query to list out unreviewed pages
 */
class UnreviewedPagesPager extends AlphabeticPager {
	public $mForm;
	protected $live, $namespace, $category, $showredirs;

	const PAGE_LIMIT = 50; // Don't get too expensive

	function __construct(
		$form, $live, $namespace, $redirs = false, $category = null, $level = 0
	) {
		$this->mForm = $form;
		$this->live = (bool)$live;
		# Must be a content page...
		if ( !is_null( $namespace ) ) {
			$namespace = (int)$namespace;
		}
		$vnamespaces = FlaggedRevs::getReviewNamespaces();
		# Must be a single NS for perfomance reasons
		if ( is_null( $namespace ) || !in_array( $namespace, $vnamespaces ) ) {
			$namespace = !$vnamespaces ? -1 : $vnamespaces[0];
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

	function setLimit( $limit ) {
		$this->mLimit = min( $limit, self::PAGE_LIMIT );
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		if ( !$this->live ) {
			return $this->getQueryCacheInfo();
		}
		$fields = [ 'page_namespace', 'page_title', 'page_len', 'page_id',
			'MIN(rev_timestamp) AS creation' ];
		# Filter by level
		if ( $this->level == 1 ) {
			$conds[] = "fp_page_id IS NULL OR fp_quality = 0";
		} else {
			$conds[] = 'fp_page_id IS NULL';
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
			$conds['cl_to'] = $this->category;
			$conds[] = 'cl_from = page_id';
			# Note: single NS always specified
			if ( $this->namespace == NS_FILE ) {
				$conds['cl_type'] = 'file';
			} elseif ( $this->namespace == NS_CATEGORY ) {
				$conds['cl_type'] = 'subcat';
			} else {
				$conds['cl_type'] = 'page';
			}
			$this->mIndexField = 'cl_sortkey';
			$useIndex = [ 'categorylinks' => 'cl_sortkey' ];
			$groupBy = 'cl_sortkey,cl_from';
		} else {
			$tables = [ 'page', 'flaggedpages', 'revision' ];
			$this->mIndexField = 'page_title';
			$useIndex = [ 'page' => 'name_title' ];
			$groupBy = 'page_title';
		}
		$useIndex['revision'] = 'page_timestamp'; // sigh...
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

	function getQueryCacheInfo() {
		$conds = $this->mConds;
		$fields = [ 'page_namespace', 'page_title', 'page_len', 'page_id',
			'qc_value', 'MIN(rev_timestamp) AS creation' ];
		# Re-join on flaggedpages to double-check since things
		# could have changed since the cache date. Also, use
		# the proper cache for this level.
		if ( $this->level == 1 ) {
			$conds['qc_type'] = 'fr_unreviewedpages_q';
			$conds[] = "fp_page_id IS NULL OR fp_quality < 1";
		} else {
			$conds['qc_type'] = 'fr_unreviewedpages';
			$conds[] = 'fp_page_id IS NULL';
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
			if ( $this->namespace == NS_FILE ) {
				$conds['cl_type'] = 'file';
			} elseif ( $this->namespace == NS_CATEGORY ) {
				$conds['cl_type'] = 'subcat';
			} else {
				$conds['cl_type'] = 'page';
			}
		} else {
			$tables = [ 'page', 'querycache', 'flaggedpages', 'revision' ];
		}
		$useIndex = [ 'querycache' => 'qc_type', 'page' => 'PRIMARY',
			'revision' => 'page_timestamp' ]; // sigh...
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

	function getIndexField() {
		return $this->mIndexField;
	}

	function doBatchLookups() {
		$lb = new LinkBatch();
		foreach ( $this->mResult as $row ) {
			$lb->add( $row->page_namespace, $row->page_title );
		}
		$lb->execute();
	}

	function getStartBody() {
		return '<ul>';
	}

	function getEndBody() {
		return '</ul>';
	}
}
