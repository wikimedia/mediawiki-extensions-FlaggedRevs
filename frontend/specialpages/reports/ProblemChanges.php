<?php

use MediaWiki\MediaWikiServices;

class ProblemChanges extends SpecialPage {
	/** @var ProblemChangesPager */
	protected $pager = null;

	/** @var string */
	protected $currentUnixTS;

	/** @var int */
	protected $level;

	/** @var string */
	protected $tag;

	/** @var string */
	protected $category;

	public function __construct() {
		parent::__construct( 'ProblemChanges' );
		$this->mIncludable = true;
	}

	public function execute( $par ) {
		$request = $this->getRequest();

		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:FlaggedRevs' );
		$this->currentUnixTS = wfTimestamp( TS_UNIX ); // now

		$this->level = $request->getInt( 'level', - 1 );
		$this->tag = trim( $request->getVal( 'tagfilter' ) );
		$category = trim( $request->getVal( 'category' ) );
		$catTitle = Title::newFromText( $category );
		$this->category = $catTitle === null ? '' : $catTitle->getText();
		$feedType = $request->getVal( 'feed' );

		$incLimit = 0;
		if ( $this->including() ) {
			$incLimit = $this->parseParams( $par ); // apply non-URL params
		}

		$this->pager = new ProblemChangesPager(
			$this, $this->level, $this->category, $this->tag );

		# Output appropriate format...
		if ( $feedType != null ) {
			$this->feed( $feedType );
		} else {
			if ( $this->including() ) {
				$this->pager->setLimit( $incLimit ); // apply non-URL limit
			} else {
				$this->setSyndicated();
				$this->showForm();
			}
			$this->showPageList();
		}
	}

	protected function setSyndicated() {
		$request = $this->getRequest();
		$queryParams = [
			'level'     => $request->getIntOrNull( 'level' ),
			'tag'       => $request->getVal( 'tag' ),
			'category'  => $request->getVal( 'category' ),
		];
		$this->getOutput()->setSyndicated( true );
		$this->getOutput()->setFeedAppendQuery( wfArrayToCgi( $queryParams ) );
	}

	public function showForm() {
		global $wgScript;

		// Add explanatory text
		$this->getOutput()->addWikiMsg( 'problemchanges-list',
			$this->getLanguage()->formatNum( $this->pager->getNumRows() ) );

		$form = Html::openElement( 'form', [ 'name' => 'problemchanges',
			'action' => $wgScript, 'method' => 'get' ] ) . "\n";
		$form .= "<fieldset><legend>" . $this->msg( 'problemchanges-legend' )->escaped() . "</legend>\n";
		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBKey() ) . "\n";
		$form .=
			( FlaggedRevs::qualityVersions()
				? "<span style='white-space: nowrap;'>" .
					FlaggedRevsXML::getLevelMenu( $this->level, 'revreview-filter-stable' ) .
					'</span> '
				: ""
			);
		$tagForm = ChangeTags::buildTagFilterSelector( $this->tag );
		if ( count( $tagForm ) ) {
			$form .= Xml::tags( 'td', [ 'class' => 'mw-label' ], $tagForm[0] );
			$form .= Xml::tags( 'td', [ 'class' => 'mw-input' ], $tagForm[1] );
		}
		$form .= '<br />' .
			Xml::label( $this->msg( "problemchanges-category" )->text(), 'wpCategory' ) . '&#160;' .
			Xml::input( 'category', 30, $this->category, [ 'id' => 'wpCategory' ] ) . ' ';
		$form .= Xml::submitButton( $this->msg( 'allpagessubmit' )->text() ) . "\n";
		$form .= '</fieldset>';
		$form .= Html::closeElement( 'form' ) . "\n";

		$this->getOutput()->addHTML( $form );
	}

	public function showPageList() {
		$out = $this->getOutput();
		// Viewing the page normally...
		if ( !$this->including() ) {
			if ( $this->pager->getNumRows() ) {
				$out->addHTML( $this->pager->getNavigationBar() );
				$out->addHTML( $this->pager->getBody() );
				$out->addHTML( $this->pager->getNavigationBar() );
			} else {
				$out->addWikiMsg( 'problemchanges-none' );
			}
		// If this page is transcluded...
		} else {
			if ( $this->pager->getNumRows() ) {
				$out->addHTML( $this->pager->getBody() );
			} else {
				$out->addWikiMsg( 'problemchanges-none' );
			}
		}
	}

	/**
	 * Set pager parameters from $par, return pager limit
	 * @param string $par
	 * @return bool|int
	 */
	protected function parseParams( $par ) {
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
			if ( preg_match( '/^category=(.+)$/', $bit, $m ) ) {
				$this->category = $m[1];
			}
			if ( preg_match( '/^tagfilter=(.+)$/', $bit, $m ) ) {
				$this->tag = $m[1];
			}
		}
		return $limit;
	}

	/**
	 * Output a subscription feed listing recent edits to this page.
	 * @param string $type
	 */
	protected function feed( $type ) {
		global $wgFeed, $wgFeedClasses, $wgFeedLimit;

		if ( !$wgFeed ) {
			$this->getOutput()->addWikiMsg( 'feed-unavailable' );
			return;
		}
		if ( !isset( $wgFeedClasses[$type] ) ) {
			$this->getOutput()->addWikiMsg( 'feed-invalid' );
			return;
		}
		$feed = new $wgFeedClasses[$type](
			$this->feedTitle(),
			$this->msg( 'tagline' )->text(),
			$this->getPageTitle()->getFullUrl()
		);
		$this->pager->mLimit = min( $wgFeedLimit, $this->pager->mLimit );

		$feed->outHeader();
		if ( $this->pager->getNumRows() > 0 ) {
			foreach ( $this->pager->mResult as $row ) {
				$feed->outItem( $this->feedItem( $row ) );
			}
		}
		$feed->outFooter();
	}

	protected function feedTitle() {
		global $wgContLanguageCode, $wgSitename;

		$page = MediaWikiServices::getInstance()->getSpecialPageFactory()
			->getPage( 'ProblemChanges' );
		$desc = $page->getDescription();
		return "$wgSitename - $desc [$wgContLanguageCode]";
	}

	/**
	 * @param stdClass $row
	 * @return FeedItem|null
	 * @suppress SecurityCheck-DoubleEscaped false positive
	 */
	protected function feedItem( $row ) {
		$title = Title::MakeTitle( $row->page_namespace, $row->page_title );
		if ( $title ) {
			$date = $row->pending_since;
			$comments = $title->getTalkPage()->getFullURL();
			$curRev = Revision::newFromTitle( $title );
			return new FeedItem(
				$title->getPrefixedText(),
				FeedUtils::formatDiffRow( $title, $row->stable, $curRev->getId(),
					$row->pending_since, $curRev->getComment() ),
				$title->getFullURL(),
				$date,
				$curRev->getUserText(),
				$comments
			);
		} else {
			return null;
		}
	}

	public function formatRow( $row ) {
		$css = $quality = $tags = $underReview = '';

		$title = Title::newFromRow( $row );
		$linkRenderer = $this->getLinkRenderer();
		$link = $linkRenderer->makeLink( $title );
		$review = $linkRenderer->makeKnownLink(
			$title,
			$this->msg( 'pendingchanges-diff' )->text(),
			[],
			[ 'diff' => 'cur', 'oldid' => $row->stable ] + FlaggedRevs::diffOnlyCGI()
		);
		# Show quality level if there are several
		if ( FlaggedRevs::qualityVersions() ) {
			$quality = $row->quality
				? $this->msg( 'revreview-lev-quality' )->escaped()
				: $this->msg( 'revreview-lev-basic' )->escaped();
			$quality = " <b>[{$quality}]</b>";
		}
		# What are the tags?
		$dbTags = self::getChangeTags( $title->getArticleID(), $row->stable );
		if ( $dbTags ) {
			$tags = implode( ', ', $dbTags );
			$tags = ' <b>' . $this->msg( 'parentheses', $tags )->escaped() . '</b>';
		}
		# Is anybody watching?
		if ( !$this->including() && MediaWikiServices::getInstance()->getPermissionManager()
				->userHasRight( $this->getUser(), 'unreviewedpages' )
		) {
			$uw = FRUserActivity::numUsersWatchingPage( $title );
			$watching = $uw
				? $this->msg( 'pendingchanges-watched' )->numParams( $uw )->escaped()
				: $this->msg( 'pendingchanges-unwatched' )->escaped();
			$watching = " {$watching}";
		} else {
			$uw = - 1;
			$watching = ''; // leave out data
		}
		# Get how long the first unreviewed edit has been waiting...
		if ( $row->pending_since ) {
			$firstPendingTime = wfTimestamp( TS_UNIX, $row->pending_since );
			$hours = ( $this->currentUnixTS - $firstPendingTime ) / 3600;
			// After three days, just use days
			if ( $hours > ( 3 * 24 ) ) {
				$days = round( $hours / 24, 0 );
				$age = $this->msg( 'pendingchanges-days' )->numParams( $days )->escaped();
			// If one or more hours, use hours
			} elseif ( $hours >= 1 ) {
				$hours = round( $hours, 0 );
				$age = $this->msg( 'pendingchanges-hours' )->numParams( $hours )->escaped();
			} else {
				$age = $this->msg( 'pendingchanges-recent' )->escaped(); // hot off the press :)
			}
			// Oh-noes!
			$css = self::getLineClass( $hours, $uw );
			$css = $css ? " class='$css'" : "";
		} else {
			$age = ""; // wtf?
		}
		# Show if a user is looking at this page
		list( $u, $ts ) = FRUserActivity::getUserReviewingDiff( $row->stable, $row->page_latest );
		if ( $u !== null ) {
			$underReview = ' <span class="fr-under-review">' .
				$this->msg( 'pendingchanges-viewing' )->escaped() . '</span>';
		}

		return ( "<li{$css}>{$link} ({$review}) <i>{$age}</i>" .
			"{$quality}{$tags}{$watching}{$underReview}</li>" );
	}

	/**
	 * Get the tags of the revisions of a page after a certain rev
	 * @param int $pageId page ID
	 * @param int $revId rev ID
	 * @return array
	 */
	protected static function getChangeTags( $pageId, $revId ) {
		$tags = [];
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			[ 'revision', 'change_tag', 'change_tag_def' ],
			'DISTINCT(ctd_name)', // unique tags
			[ 'rev_page' => $pageId,
				'rev_id > ' . intval( $revId ),
				'rev_id = ct_rev_id',
				'ct_tag_id = ctd_id' ],
			__METHOD__
		);
		foreach ( $res as $row ) {
			$tags[] = $row->ctd_name;
		}
		return $tags;
	}

	protected static function getLineClass( $hours, $uw ) {
		if ( $uw == 0 ) {
			return 'fr-unreviewed-unwatched';
		} else {
			return "";
		}
	}

	protected function getGroupName() {
		return 'quality';
	}
}
