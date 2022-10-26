<?php

use MediaWiki\Feed\FeedItem;
use MediaWiki\MediaWikiServices;

class PendingChanges extends SpecialPage {
	/** @var PendingChangesPager|null */
	private $pager = null;

	/** @var int */
	private $currentUnixTS;

	/** @var int|null */
	private $namespace;

	/** @var string */
	private $category;

	/** @var int|null */
	private $size;

	/** @var bool */
	private $watched;

	/** @var bool */
	private $stable;

	public function __construct() {
		parent::__construct( 'PendingChanges' );
		$this->mIncludable = true;
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		$request = $this->getRequest();

		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:FlaggedRevs' );
		$this->currentUnixTS = (int)wfTimestamp();

		$this->namespace = $request->getIntOrNull( 'namespace' );
		$level = $request->getInt( 'level', -1 );
		$category = trim( $request->getVal( 'category', '' ) );
		$catTitle = Title::makeTitleSafe( NS_CATEGORY, $category );
		$this->category = $catTitle === null ? '' : $catTitle->getText();
		$this->size = $request->getIntOrNull( 'size' );
		$this->watched = $request->getCheck( 'watched' );
		$this->stable = $request->getCheck( 'stable' );
		$feedType = $request->getVal( 'feed' );

		$incLimit = 0;
		if ( $this->including() ) {
			$incLimit = $this->parseParams( $par ); // apply non-URL params
		}

		$this->pager = new PendingChangesPager( $this, $this->namespace,
			$level, $this->category, $this->size, $this->watched, $this->stable );

		# Output appropriate format...
		if ( $feedType != null ) {
			$this->feed( $feedType );
		} else {
			if ( $this->including() ) {
				if ( $incLimit ) { // limit provided
					$this->pager->setLimit( $incLimit ); // apply non-URL limit
				}
			} else {
				$this->setSyndicated();
				$this->showForm();
			}
			$this->showPageList();
		}
	}

	private function setSyndicated() {
		$request = $this->getRequest();
		$queryParams = [
			'namespace' => $request->getIntOrNull( 'namespace' ),
			'level'     => $request->getIntOrNull( 'level' ),
			'category'  => $request->getVal( 'category' ),
		];
		$this->getOutput()->setSyndicated();
		$this->getOutput()->setFeedAppendQuery( wfArrayToCgi( $queryParams ) );
	}

	private function showForm() {
		# Explanatory text
		$this->getOutput()->addWikiMsg( 'pendingchanges-list',
			$this->getLanguage()->formatNum( $this->pager->getNumRows() ) );

		$form = Html::openElement( 'form', [
			'name' => 'pendingchanges',
			'action' => $this->getConfig()->get( 'Script' ),
			'method' => 'get',
		] ) . "\n";
		$form .= "<fieldset><legend>" . $this->msg( 'pendingchanges-legend' )->escaped() . "</legend>\n";
		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBkey() ) . "\n";

		$items = [];
		if ( count( FlaggedRevs::getReviewNamespaces() ) > 1 ) {
			$items[] = "<span style='white-space: nowrap;'>" .
				FlaggedRevsXML::getNamespaceMenu( $this->namespace, '' ) . '</span>';
		}
		if ( !FlaggedRevs::isStableShownByDefault() && !FlaggedRevs::useOnlyIfProtected() ) {
			$items[] = "<span style='white-space: nowrap;'>" .
				Xml::check( 'stable', $this->stable, [ 'id' => 'wpStable' ] ) .
				Xml::label( $this->msg( 'pendingchanges-stable' )->text(), 'wpStable' ) . '</span>';
		}
		if ( $items ) {
			$form .= implode( ' ', $items ) . '<br />';
		}

		$items = [];
		$items[] =
			Xml::label( $this->msg( "pendingchanges-category" )->text(), 'wpCategory' ) . '&#160;' .
			Xml::input( 'category', 30, $this->category, [ 'id' => 'wpCategory' ] );
		if ( $this->getUser()->getId() ) {
			$items[] = Xml::check( 'watched', $this->watched, [ 'id' => 'wpWatched' ] ) .
				Xml::label( $this->msg( 'pendingchanges-onwatchlist' )->text(), 'wpWatched' );
		}
		$form .= implode( ' ', $items ) . '<br />';
		$form .=
			Xml::label( $this->msg( 'pendingchanges-size' )->text(), 'wpSize' ) .
			Xml::input( 'size', 4, (string)$this->size, [ 'id' => 'wpSize' ] ) . ' ' .
			Xml::submitButton( $this->msg( 'allpagessubmit' )->text() ) . "\n";
		$form .= "</fieldset>";
		$form .= Html::closeElement( 'form' ) . "\n";

		$this->getOutput()->addHTML( $form );
	}

	private function showPageList() {
		$out = $this->getOutput();

		if ( !$this->pager->getNumRows() ) {
			$out->addWikiMsg( 'pendingchanges-none' );
			return;
		}

		// To style output of ChangesList::showCharacterDifference
		$out->addModuleStyles( 'mediawiki.special.changeslist' );
		$out->addModuleStyles( 'mediawiki.interface.helpers.styles' );

		if ( $this->including() ) {
			// If this list is transcluded...
			$out->addHTML( $this->pager->getBody() );
		} else {
			// Viewing the list normally...
			$navigationBar = $this->pager->getNavigationBar();
			$out->addHTML( $navigationBar );
			$out->addHTML( $this->pager->getBody() );
			$out->addHTML( $navigationBar );
		}
	}

	/**
	 * Set pager parameters from $par, return pager limit
	 * @param string $par
	 * @return bool|int
	 */
	private function parseParams( $par ) {
		$bits = preg_split( '/\s*,\s*/', trim( $par ) );
		$limit = false;
		foreach ( $bits as $bit ) {
			if ( is_numeric( $bit ) ) {
				$limit = intval( $bit );
			}
			$m = [];
			if ( preg_match( '/^limit=(\d+)$/', $bit, $m ) ) {
				$limit = intval( $m[1] );
			}
			if ( preg_match( '/^namespace=(.*)$/', $bit, $m ) ) {
				$ns = $this->getLanguage()->getNsIndex( $m[1] );
				if ( $ns !== false ) {
					$this->namespace = $ns;
				}
			}
			if ( preg_match( '/^category=(.+)$/', $bit, $m ) ) {
				$this->category = $m[1];
			}
		}
		return $limit;
	}

	/**
	 * Output a subscription feed listing recent edits to this page.
	 * @param string $type
	 */
	private function feed( $type ) {
		if ( !$this->getConfig()->get( 'Feed' ) ) {
			$this->getOutput()->addWikiMsg( 'feed-unavailable' );
			return;
		}

		$feedClasses = $this->getConfig()->get( 'FeedClasses' );
		if ( !isset( $feedClasses[$type] ) ) {
			$this->getOutput()->addWikiMsg( 'feed-invalid' );
			return;
		}

		$feed = new $feedClasses[$type](
			$this->feedTitle(),
			$this->msg( 'tagline' )->text(),
			$this->getPageTitle()->getFullURL()
		);
		$this->pager->mLimit = min( $this->getConfig()->get( 'FeedLimit' ), $this->pager->mLimit );

		$feed->outHeader();
		if ( $this->pager->getNumRows() > 0 ) {
			foreach ( $this->pager->mResult as $row ) {
				$feed->outItem( $this->feedItem( $row ) );
			}
		}
		$feed->outFooter();
	}

	private function feedTitle() {
		$languageCode = $this->getConfig()->get( 'LanguageCode' );
		$sitename = $this->getConfig()->get( 'Sitename' );

		$page = MediaWikiServices::getInstance()->getSpecialPageFactory()
			->getPage( 'PendingChanges' );
		$desc = $page->getDescription();
		return "$sitename - $desc [$languageCode]";
	}

	/**
	 * @param stdClass $row
	 * @return FeedItem|null
	 */
	private function feedItem( $row ) {
		$title = Title::makeTitle( $row->page_namespace, $row->page_title );
		if ( !$title ) {
			return null;
		}

		$date = $row->pending_since;
		$comments = $title->getTalkPage()->getFullURL();
		$curRevRecord = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionByTitle( $title );
		$currentComment = $curRevRecord->getComment() ?
			$curRevRecord->getComment()->text :
			'';
		$currentUserText = $curRevRecord->getUser() ?
			$curRevRecord->getUser()->getName() :
			'';
		return new FeedItem(
			$title->getPrefixedText(),
			FeedUtils::formatDiffRow(
				$title,
				$row->stable,
				$curRevRecord->getId(),
				$row->pending_since,
				$currentComment
			),
			$title->getFullURL(),
			$date,
			$currentUserText,
			$comments
		);
	}

	/**
	 * @param stdClass $row
	 * @return string HTML
	 */
	public function formatRow( $row ) {
		$css = '';
		$quality = '';
		$title = Title::newFromRow( $row );
		$stxt = ChangesList::showCharacterDifference( $row->rev_len, $row->page_len );
		# Page links...
		$linkRenderer = $this->getLinkRenderer();
		$link = $linkRenderer->makeLink( $title );
		$hist = $linkRenderer->makeKnownLink(
			$title,
			$this->msg( 'hist' )->text(),
			[],
			[ 'action' => 'history' ]
		);
		$review = $linkRenderer->makeKnownLink(
			$title,
			$this->msg( 'pendingchanges-diff' )->text(),
			[],
			[ 'diff' => 'cur', 'oldid' => $row->stable ] + FlaggedRevs::diffOnlyCGI()
		);
		# Is anybody watching?
		// Only show information to users with the `unwatchedpages` who could find this
		// information elsewhere anyway, T281065
		if ( !$this->including() && MediaWikiServices::getInstance()->getPermissionManager()
				->userHasRight( $this->getUser(), 'unwatchedpages' )
		) {
			$uw = FRUserActivity::numUsersWatchingPage( $title );
			$watching = ' ';
			$watching .= $uw
				? $this->msg( 'pendingchanges-watched' )->numParams( $uw )->escaped()
				: $this->msg( 'pendingchanges-unwatched' )->escaped();
		} else {
			$uw = -1;
			$watching = '';
		}
		# Get how long the first unreviewed edit has been waiting...
		if ( $row->pending_since ) {
			$firstPendingTime = (int)wfTimestamp( TS_UNIX, $row->pending_since );
			$hours = ( $this->currentUnixTS - $firstPendingTime ) / 3600;
			$days = round( $hours / 24 );
			if ( $days >= 3 ) {
				$age = $this->msg( 'pendingchanges-days' )->numParams( $days )->escaped();
			} elseif ( $hours >= 1 ) {
				$age = $this->msg( 'pendingchanges-hours' )->numParams( round( $hours ) )->escaped();
			} else {
				$age = $this->msg( 'pendingchanges-recent' )->escaped(); // hot off the press :)
			}
			// Oh-noes!
			$class = $this->getLineClass( $uw );
			$css = $class ? " class='$class'" : "";
		} else {
			$age = ""; // wtf?
		}

		return ( "<li{$css}>{$link} ({$hist}) {$stxt} ({$review}) <i>{$age}</i>" .
			"{$quality}{$watching}</li>" );
	}

	/**
	 * @param int $numUsersWatching Number of users or -1 when not allowed to see the number
	 * @return string
	 */
	private function getLineClass( $numUsersWatching ) {
		return $numUsersWatching == 0 ? 'fr-unreviewed-unwatched' : '';
	}

	/**
	 * @return string
	 */
	protected function getGroupName() {
		return 'quality';
	}
}
