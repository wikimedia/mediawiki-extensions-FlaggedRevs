<?php

use MediaWiki\MediaWikiServices;

class UnreviewedPages extends SpecialPage {
	/** @var UnreviewedPagesPager */
	private $pager = null;

	/** @var int */
	private $currentUnixTS;

	/** @var int */
	private $namespace;

	/** @var string */
	private $category;

	/** @var bool */
	private $hideRedirs;

	/** @var bool */
	private $isMiser;

	public function __construct() {
		parent::__construct( 'UnreviewedPages', 'unreviewedpages' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		$request = $this->getRequest();

		$this->isMiser = $this->getConfig()->get( 'MiserMode' );

		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:FlaggedRevs' );
		if ( !MediaWikiServices::getInstance()->getPermissionManager()
			->userHasRight( $this->getUser(), 'unreviewedpages' )
		) {
			throw new PermissionsError( 'unreviewedpages' );
		}

		$this->currentUnixTS = (int)wfTimestamp();

		# Get default namespace
		$namespaces = FlaggedRevs::getReviewNamespaces();
		$this->namespace = $request->getInt( 'namespace', $namespaces[0] ?? NS_MAIN );
		$category = trim( $request->getVal( 'category', '' ) );
		$catTitle = Title::makeTitleSafe( NS_CATEGORY, $category );
		$this->category = $catTitle === null ? '' : $catTitle->getText();
		$level = $request->getInt( 'level' );
		$this->hideRedirs = $request->getBool( 'hideredirs', true );

		$this->pager = new UnreviewedPagesPager( $this, !$this->isMiser,
			$this->namespace, !$this->hideRedirs, $this->category, $level );

		$this->showForm();
		$this->showPageList();
	}

	private function showForm() {
		# Add explanatory text
		$this->getOutput()->addWikiMsg( 'unreviewedpages-list',
			$this->getLanguage()->formatNum( $this->pager->getNumRows() ) );

		# show/hide links
		$link = $this->getLinkRenderer()->makeLink(
			$this->getPageTitle(),
			$this->msg( $this->hideRedirs ? 'show' : 'hide' )->text(),
			[],
			[
				'hideredirs' => $this->hideRedirs ? '0' : '1',
				'category' => $this->category,
				'namespace' => $this->namespace,
			]
		);
		$showhideredirs = $this->msg( 'unreviewedpages-showhide-redirect' )->rawParams( $link )->escaped();

		# Add form...
		$form = Html::openElement( 'form', [
			'name' => 'unreviewedpages',
			'action' => $this->getConfig()->get( 'Script' ),
			'method' => 'get',
		] ) . "\n";
		$form .= "<fieldset><legend>" . $this->msg( 'unreviewedpages-legend' )->escaped() . "</legend>\n";
		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBkey() ) . "\n";
		# Add dropdowns as needed
		if ( count( FlaggedRevs::getReviewNamespaces() ) > 1 ) {
			$form .= FlaggedRevsXML::getNamespaceMenu( $this->namespace ) . '&#160;';
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
		if ( $this->isMiser ) {
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

	private function showPageList() {
		$out = $this->getOutput();
		if ( $this->pager->getNumRows() ) {
			$out->addHTML( $this->pager->getNavigationBar() );
			$out->addHTML( $this->pager->getBody() );
			$out->addHTML( $this->pager->getNavigationBar() );
		} else {
			$out->addWikiMsg( 'unreviewedpages-none' );
		}
	}

	/**
	 * @param stdClass $row
	 * @return string HTML
	 */
	public function formatRow( $row ) {
		$title = Title::newFromRow( $row );

		$stxt = '';
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
		if ( $size !== null ) {
			$stxt = ( $size == 0 )
				? $this->msg( 'historyempty' )->escaped()
				: $this->msg( 'historysize' )->numParams( $size )->escaped();
			$stxt = " <small>$stxt</small>";
		}
		# Get how long the first unreviewed edit has been waiting...
		$firstPendingTime = (int)wfTimestamp( TS_UNIX, $row->creation );
		$hours = ( $this->currentUnixTS - $firstPendingTime ) / 3600;
		$days = round( $hours / 24 );
		if ( $days >= 3 ) {
			$age = ' ' . $this->msg( 'unreviewedpages-days' )->numParams( $days )->escaped();
		} elseif ( $hours >= 1 ) {
			$age = ' ' . $this->msg( 'unreviewedpages-hours' )->numParams( round( $hours ) )->escaped();
		} else {
			$age = ' ' . $this->msg( 'unreviewedpages-recent' )->escaped(); // hot off the press :)
		}
		if ( MediaWikiServices::getInstance()->getPermissionManager()
			->userHasRight( $this->getUser(), 'unwatchedpages' )
		) {
			$uw = FRUserActivity::numUsersWatchingPage( $title );
			$watching = ' ';
			$watching .= $uw
				? $this->msg( 'unreviewedpages-watched' )->numParams( $uw )->escaped()
				: $this->msg( 'unreviewedpages-unwatched' )->escaped();
		} else {
			$uw = -1;
			$watching = '';
		}
		$css = $this->getLineClass( $hours, $uw );
		$css = $css ? " class='$css'" : "";

		return ( "<li{$css}>{$link} $dirmark {$stxt} ({$hist})" .
			"{$age}{$watching}</li>" );
	}

	/**
	 * @param float $hours
	 * @param int $numUsersWatching Number of users or -1 when not allowed to see the number
	 * @return string
	 */
	private function getLineClass( $hours, $numUsersWatching ) {
		$days = $hours / 24;
		if ( $numUsersWatching == 0 ) {
			return 'fr-unreviewed-unwatched';
		} elseif ( $days > 20 ) {
			return 'fr-pending-long2';
		} elseif ( $days > 7 ) {
			return 'fr-pending-long';
		} else {
			return '';
		}
	}

	/**
	 * Run an update to the cached query rows
	 * @return void
	 */
	public static function updateQueryCache() {
		$rNamespaces = FlaggedRevs::getReviewNamespaces();
		if ( !$rNamespaces ) {
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

		$dbw = wfGetDB( DB_PRIMARY );
		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
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
		$lbFactory->commitPrimaryChanges( __METHOD__ );

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
		$lbFactory->commitPrimaryChanges( __METHOD__ );
	}

	/**
	 * @return string
	 */
	protected function getGroupName() {
		return 'quality';
	}
}
