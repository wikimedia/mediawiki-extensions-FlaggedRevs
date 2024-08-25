<?php

use MediaWiki\Html\Html;
use MediaWiki\MainConfigNames;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;

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

	/** How many entries are at most stored in the cache */
	private const CACHE_SIZE = 5000;

	public function __construct() {
		parent::__construct( 'UnreviewedPages', 'unreviewedpages' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		$request = $this->getRequest();

		$this->isMiser = $this->getConfig()->get( MainConfigNames::MiserMode );

		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:FlaggedRevs' );
		if ( !MediaWikiServices::getInstance()->getPermissionManager()
			->userHasRight( $this->getUser(), 'unreviewedpages' )
		) {
			throw new PermissionsError( 'unreviewedpages' );
		}

		$this->currentUnixTS = (int)wfTimestamp();

		# Get default namespace
		$this->namespace = $request->getInt( 'namespace', FlaggedRevs::getFirstReviewNamespace() );
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
			[ 'class' => 'cdx-docs-link' ],
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
				'action' => $this->getConfig()->get( MainConfigNames::Script ),
				'method' => 'get',
				'class' => 'cdx-form mw-fr-form-container'
			] ) . "\n";

		$form .= Html::openElement( 'fieldset', [ 'class' => 'cdx-field' ] ) . "\n";

		$form .= Html::openElement( 'legend', [ 'class' => 'cdx-label' ] ) . "\n";
		$form .= Html::rawElement( 'span', [ 'class' => 'cdx-label__label' ],
			Html::element( 'span', [ 'class' => 'cdx-label__label__text' ],
				$this->msg( 'unreviewedpages-legend' )->text() )
		);
		$form .= Html::closeElement( 'legend' ) . "\n";

		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBkey() ) . "\n";

		$form .= Html::openElement( 'div', [ 'class' => 'cdx-field__control cdx-align-right' ] ) . "\n";

		# Namespace selector
		if ( count( FlaggedRevs::getReviewNamespaces() ) > 1 ) {
			$form .= Html::rawElement(
				'div',
				[ 'class' => 'cdx-field__item' ],
				FlaggedRevsHTML::getNamespaceMenu( $this->namespace )
			);
		}

		$form .= Html::rawElement(
				'div',
				[ 'class' => 'cdx-field__item' ],
				Html::label( $this->msg( 'unreviewedpages-category' )->text(), 'category',
					[ 'class' => 'cdx-label__label' ] ) .
				Html::input( 'category', $this->category, 'text', [
					'id' => 'category',
					'class' => 'cdx-text-input__input',
					'size' => 30
				] )
			) . "\n";

		$form .= Html::rawElement(
				'div',
				[ 'class' => 'cdx-field__item' ],
				$showhideredirs
			) . "\n";

		$form .= Html::closeElement( 'div' ) . "\n";

		$form .= Html::rawElement(
				'div',
				[ 'class' => 'cdx-field__control' ],
				Html::submitButton( $this->msg( 'allpagessubmit' )->text(), [
					'class' => 'cdx-button cdx-button--action-progressive'
				] )
			) . "\n";

		$form .= Html::closeElement( 'fieldset' ) . "\n";
		$form .= Html::closeElement( 'form' ) . "\n";

		# Query may get too slow to be live...
		if ( $this->isMiser ) {
			$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

			$ts = $dbr->newSelectQueryBuilder()
				->select( 'qci_timestamp' )
				->from( 'querycache_info' )
				->where( [ 'qci_type' => 'fr_unreviewedpages' ] )
				->caller( __METHOD__ )
				->fetchField();
			if ( $ts ) {
				$ts = wfTimestamp( TS_MW, $ts );
				$td = $this->getLanguage()->timeanddate( $ts );
				$d = $this->getLanguage()->date( $ts );
				$t = $this->getLanguage()->time( $ts );
				$form .= $this->msg( 'perfcachedts', $td, $d, $t, self::CACHE_SIZE )->parseAsBlock();
			} else {
				$form .= $this->msg( 'perfcached', self::CACHE_SIZE )->parseAsBlock();
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
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$insertRows = [];
		// Find pages that were never reviewed at all...
		$res = $dbr->newSelectQueryBuilder()
			->select( [ 'page_namespace', 'page_title', 'page_id' ] )
			->from( 'page' )
			->leftJoin( 'flaggedpages', null, 'fp_page_id = page_id' )
			->where( [
				'page_namespace' => $rNamespaces,
				'page_is_redirect' => 0, // no redirects
				'fp_page_id' => null,
			] )
			->limit( self::CACHE_SIZE )
			->caller( __METHOD__ )
			->fetchResultSet();
		foreach ( $res as $row ) {
			$insertRows[] = [
				'qc_type'       => 'fr_unreviewedpages',
				'qc_namespace'  => $row->page_namespace,
				'qc_title'      => $row->page_title,
				'qc_value'      => $row->page_id
			];
		}

		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		$dbw->startAtomic( __METHOD__ );
		# Clear out any old cached data
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'querycache' )
			->where( [ 'qc_type' => 'fr_unreviewedpages' ] )
			->caller( __METHOD__ )
			->execute();
		# Insert new data...
		if ( $insertRows ) {
			$dbw->newInsertQueryBuilder()
				->insertInto( 'querycache' )
				->rows( $insertRows )
				->caller( __METHOD__ )
				->execute();
		}
		# Update the querycache_info record for the page
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'querycache_info' )
			->where( [ 'qci_type' => 'fr_unreviewedpages' ] )
			->caller( __METHOD__ )
			->execute();
		$dbw->newInsertQueryBuilder()
			->insertInto( 'querycache_info' )
			->row( [ 'qci_type' => 'fr_unreviewedpages', 'qci_timestamp' => $dbw->timestamp() ] )
			->caller( __METHOD__ )
			->execute();
		$dbw->endAtomic( __METHOD__ );
		$lbFactory->commitPrimaryChanges( __METHOD__ );

		$insertRows = [];
		// Find pages that were never marked as "quality"...
		$res = $dbr->newSelectQueryBuilder()
			->select( [ 'page_namespace', 'page_title', 'page_id' ] )
			->from( 'page' )
			->leftJoin( 'flaggedpages', null, 'fp_page_id = page_id' )
			->where( [
				'page_namespace' => $rNamespaces,
				'page_is_redirect' => 0, // no redirects
				$dbr->expr( 'fp_page_id', '=', null )->or( 'fp_quality', '=', 0 ),
			] )
			->limit( self::CACHE_SIZE )
			->caller( __METHOD__ )
			->fetchResultSet();
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
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'querycache' )
			->where( [ 'qc_type' => 'fr_unreviewedpages_q' ] )
			->caller( __METHOD__ )
			->execute();
		# Insert new data...
		if ( $insertRows ) {
			$dbw->newInsertQueryBuilder()
				->insertInto( 'querycache' )
				->rows( $insertRows )
				->caller( __METHOD__ )
				->execute();
		}
		# Update the querycache_info record for the page
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'querycache_info' )
			->where( [ 'qci_type' => 'fr_unreviewedpages_q' ] )
			->caller( __METHOD__ )
			->execute();
		$dbw->newInsertQueryBuilder()
			->insertInto( 'querycache_info' )
			->row( [ 'qci_type' => 'fr_unreviewedpages_q', 'qci_timestamp' => $dbw->timestamp() ] )
			->caller( __METHOD__ )
			->execute();
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
