<?php

use MediaWiki\Feed\FeedItem;
use MediaWiki\Feed\FeedUtils;
use MediaWiki\Html\Html;
use MediaWiki\MainConfigNames;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;

class PendingChanges extends SpecialPage {

	private ?PendingChangesPager $pager = null;
	private int $currentUnixTS;
	private ?int $namespace;
	private string $category;
	private ?string $tagFilter;
	private ?int $size;
	private bool $watched;

	public function __construct() {
		parent::__construct( 'PendingChanges' );
		$this->mIncludable = true;
	}

	/**
	 * @inheritDoc
	 * @throws MWException
	 */
	public function execute( $subPage ) {
		$request = $this->getRequest();

		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:FlaggedRevs' );
		$this->currentUnixTS = (int)wfTimestamp();

		$this->namespace = $request->getIntOrNull( 'namespace' );
		$this->tagFilter = $request->getVal( 'tagFilter' );
		$category = trim( $request->getVal( 'category', '' ) );
		$catTitle = Title::makeTitleSafe( NS_CATEGORY, $category );
		$this->category = $catTitle === null ? '' : $catTitle->getText();
		$this->size = $request->getIntOrNull( 'size' );
		$this->watched = $request->getCheck( 'watched' );
		$stable = $request->getCheck( 'stable' );
		$feedType = $request->getVal( 'feed' );
		$limit = $request->getInt( 'limit', 50 );

		$incLimit = 0;
		if ( $this->including() && $subPage !== null ) {
			$incLimit = $this->parseParams( $subPage ); // apply non-URL params
		}

		$this->pager = new PendingChangesPager( $this, $this->namespace,
			$this->category, $this->size, $this->watched, $stable, $this->tagFilter );
		$this->pager->setLimit( $limit );

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
			'category'  => $request->getVal( 'category' ),
		];
		$this->getOutput()->setSyndicated();
		$this->getOutput()->setFeedAppendQuery( wfArrayToCgi( $queryParams ) );
	}

	private function showForm() {
		$form = Html::openElement( 'form', [
				'name' => 'pendingchanges',
				'action' => $this->getConfig()->get( MainConfigNames::Script ),
				'method' => 'get',
				'class' => 'mw-fr-form-container'
			] ) . "\n";

		$form .= Html::openElement( 'fieldset', [ 'class' => 'cdx-field' ] ) . "\n";

		$form .= Html::openElement( 'legend', [ 'class' => 'cdx-label' ] ) . "\n";
		$form .= Html::rawElement( 'span', [ 'class' => 'cdx-label__label' ],
			Html::element( 'span', [ 'class' => 'cdx-label__label__text' ],
				$this->msg( 'pendingchanges-legend' )->text() )
		);

		# Explanatory text
		$form .= Html::rawElement( 'span', [ 'class' => 'cdx-label__description' ],
			$this->msg( 'pendingchanges-list' )->params(
				$this->getLanguage()->formatNum( $this->pager->getNumRows() )
			)->parse()
		);

		$form .= Html::closeElement( 'legend' ) . "\n";

		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBkey() ) . "\n";

		$form .= Html::openElement( 'div', [ 'class' => 'cdx-field__control' ] ) . "\n";

		if ( count( FlaggedRevs::getReviewNamespaces() ) > 1 ) {
			$form .= Html::rawElement(
				'div',
				[ 'class' => 'cdx-field__item' ],
				FlaggedRevsHTML::getNamespaceMenu( $this->namespace, '' )
			);
		}

		$form .= Html::rawElement(
			'div',
			[ 'class' => 'cdx-field__item' ],
			FlaggedRevsHTML::getEditTagFilterMenu( $this->tagFilter )
		);

		$form .= Html::rawElement(
			'div',
			[ 'class' => 'cdx-field__item' ],
			$this->getLimitSelector( $this->pager->mLimit )
		);

		$form .= Html::rawElement(
				'div',
				[ 'class' => 'cdx-field__item' ],
				Html::label( $this->msg( 'pendingchanges-category' )->text(), 'wpCategory',
					[ 'class' => 'cdx-label__label' ] ) .
				Html::input( 'category', $this->category, 'text', [
					'id' => 'wpCategory',
					'class' => 'cdx-text-input__input'
				] )
			) . "\n";

		$form .= Html::rawElement(
				'div',
				[ 'class' => 'cdx-field__item' ],
				Html::label( $this->msg( 'pendingchanges-size' )->text(), 'wpSize',
					[ 'class' => 'cdx-label__label' ] ) .
				Html::input( 'size', (string)$this->size, 'number', [
					'id' => 'wpSize',
					'class' => 'cdx-text-input__input'
				] )
			) . "\n";

		$form .= Html::closeElement( 'div' ) . "\n";

		$form .= Html::rawElement(
				'div',
				[ 'class' => 'cdx-field__control' ],
				Html::rawElement( 'span', [ 'class' => 'cdx-checkbox cdx-checkbox--inline' ],
					Html::check( 'watched', $this->watched, [
						'id' => 'wpWatched',
						'class' => 'cdx-checkbox__input'
					] ) .
					Html::rawElement( 'span', [ 'class' => 'cdx-checkbox__icon' ] ) .
					Html::rawElement(
						'div',
						[ 'class' => 'cdx-checkbox__label cdx-label' ],
						Html::label( $this->msg( 'pendingchanges-onwatchlist' )->text(), 'wpWatched',
							[ 'class' => 'cdx-label__label' ] )
					)
				)
			) . "\n";

		$form .= Html::rawElement(
				'div',
				[ 'class' => 'cdx-field__control' ],
				Html::submitButton( $this->msg( 'pendingchanges-filter-submit-button-text' )->text(), [
					'class' => 'cdx-button cdx-button--action-progressive'
				] )
			) . "\n";

		$form .= Html::closeElement( 'fieldset' ) . "\n";
		$form .= Html::closeElement( 'form' ) . "\n";

		$this->getOutput()->addHTML( $form );
	}

	/**
	 * Get a selector for limit options
	 *
	 * @param int $selected The currently selected limit
	 */
	private function getLimitSelector( int $selected = 20 ): string {
		$s = Html::rawElement( 'div', [ 'class' => 'cdx-field__item' ],
			Html::rawElement( 'div', [ 'class' => 'cdx-label' ],
				Html::label(
					$this->msg( 'pendingchanges-limit' )->text(),
					'wpLimit',
					[ 'class' => 'cdx-label__label' ]
				)
			)
		);

		$options = [ 20, 50, 100 ];
		$selectOptions = '';
		foreach ( $options as $option ) {
			$selectOptions .= Html::element( 'option', [
				'value' => $option,
				'selected' => $selected == $option
			], $this->getLanguage()->formatNum( $option ) );
		}

		$s .= Html::rawElement( 'select', [
			'name' => 'limit',
			'id' => 'wpLimit',
			'class' => 'cdx-select'
		], $selectOptions );

		return $s;
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
	 * Set pager parameters from $subPage, return pager limit
	 * @param string $subPage
	 * @return bool|int
	 */
	private function parseParams( string $subPage ) {
		$bits = preg_split( '/\s*,\s*/', trim( $subPage ) );
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
	 * @throws MWException
	 */
	private function feed( string $type ) {
		if ( !$this->getConfig()->get( MainConfigNames::Feed ) ) {
			$this->getOutput()->addWikiMsg( 'feed-unavailable' );
			return;
		}

		$feedClasses = $this->getConfig()->get( MainConfigNames::FeedClasses );
		if ( !isset( $feedClasses[$type] ) ) {
			$this->getOutput()->addWikiMsg( 'feed-invalid' );
			return;
		}

		$feed = new $feedClasses[$type](
			$this->feedTitle(),
			$this->msg( 'tagline' )->text(),
			$this->getPageTitle()->getFullURL()
		);
		$this->pager->mLimit = min( $this->getConfig()->get( MainConfigNames::FeedLimit ), $this->pager->mLimit );

		$feed->outHeader();
		if ( $this->pager->getNumRows() > 0 ) {
			foreach ( $this->pager->mResult as $row ) {
				$feed->outItem( $this->feedItem( $row ) );
			}
		}
		$feed->outFooter();
	}

	private function feedTitle(): string {
		$languageCode = $this->getConfig()->get( MainConfigNames::LanguageCode );
		$sitename = $this->getConfig()->get( MainConfigNames::Sitename );

		$page = MediaWikiServices::getInstance()->getSpecialPageFactory()
			->getPage( 'PendingChanges' );
		$desc = $page->getDescription();
		return "$sitename - $desc [$languageCode]";
	}

	/**
	 * @param stdClass $row
	 * @return FeedItem|null
	 * @throws MWException
	 */
	private function feedItem( stdClass $row ): ?FeedItem {
		$title = Title::makeTitle( $row->page_namespace, $row->page_title );
		if ( !$title ) {
			return null;
		}

		$date = $row->pending_since;
		$services = MediaWikiServices::getInstance();
		$comments = $services->getNamespaceInfo()->getTalkPage( $title );
		$curRevRecord = $services->getRevisionLookup()->getRevisionByTitle( $title );
		$currentComment = $curRevRecord->getComment() ? $curRevRecord->getComment()->text : '';
		$currentUserText = $curRevRecord->getUser() ? $curRevRecord->getUser()->getName() : '';
		return new FeedItem(
			$title->getPrefixedText(),
			FeedUtils::formatDiffRow2(
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

	public function formatRow( stdClass $row ): string {
		$css = '';
		$title = Title::newFromRow( $row );
		$size = ChangesList::showCharacterDifference( $row->rev_len, $row->page_len );
		# Page links...
		$linkRenderer = $this->getLinkRenderer();

		$query = $title->isRedirect() ? [ 'redirect' => 'no' ] : [];

		$link = $linkRenderer->makeKnownLink(
			$title,
			null,
			[ 'class' => 'mw-fr-pending-changes-page-title' ],
			$query
		);
		$linkArr = [];
		$linkArr[] = $linkRenderer->makeKnownLink(
			$title,
			$this->msg( 'hist' )->text(),
			[ 'class' => 'mw-fr-pending-changes-page-history' ],
			[ 'action' => 'history' ]
		);
		if ( $this->getAuthority()->isAllowed( 'edit' ) ) {
			$linkArr[] = $linkRenderer->makeKnownLink(
				$title,
				$this->msg( 'editlink' )->text(),
				[ 'class' => 'mw-fr-pending-changes-page-edit' ],
				[ 'action' => 'edit' ]
			);
		}
		if ( $this->getAuthority()->isAllowed( 'delete' ) ) {
			$linkArr[] = $linkRenderer->makeKnownLink(
				$title,
				$this->msg( 'tags-delete' )->text(),
				[ 'class' => 'mw-fr-pending-changes-page-delete' ],
				[ 'action' => 'delete' ]
			);
		}
		$links = $this->msg( 'parentheses' )->rawParams( $this->getLanguage()
			->pipeList( $linkArr ) )->escaped();
		$review = Html::rawElement(
			'a',
			[
				'class' => 'cdx-docs-link',
				'href' => $title->getFullURL( [ 'diff' => 'cur', 'oldid' => $row->stable ] )
			],
			$this->msg( 'pendingchanges-diff' )->text()
		);
		# Is anybody watching?
		// Only show information to users with the `unwatchedpages` who could find this
		// information elsewhere anyway, T281065
		if ( !$this->including() && $this->getAuthority()->isAllowed( 'unwatchedpages' ) ) {
			$uw = FRUserActivity::numUsersWatchingPage( $title );
			$watching = ' ';
			$watching .= $uw
				? $this->getWatchingFormatted( $uw )
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
				$age = $this->msg( 'pendingchanges-days' )->numParams( $days )->text();
			} elseif ( $hours >= 1 ) {
				$age = $this->msg( 'pendingchanges-hours' )->numParams( round( $hours ) )->text();
			} else {
				$age = $this->msg( 'pendingchanges-recent' )->text(); // hot off the press :)
			}
			$age = Html::element( 'span', [], $age );
			// Oh-noes!
			$class = $this->getLineClass( $uw );
			$css = $class ? " $class" : "";
		} else {
			$age = "";
		}
		$watchingColumn = $watching ? "<td>$watching</td>" : '';
		return (
			"<tr class='$css'>
				<td>$link $links</td>
				<td class='cdx-table__table__cell--align-center'>$review</td>
				<td>$size</td>
				<td>$age</td>
				$watchingColumn
			</tr>"
		);
	}

	/**
	 * @param int $numUsersWatching Number of users or -1 when not allowed to see the number
	 * @return string
	 */
	private function getLineClass( int $numUsersWatching ): string {
		return $numUsersWatching == 0 ? 'fr-unreviewed-unwatched' : '';
	}

	/**
	 * @return string
	 */
	protected function getGroupName(): string {
		return 'quality';
	}

	/**
	 * Get formatted text for the watching value
	 *
	 * @param int $watching
	 * @return string
	 * @since 1.43
	 */
	public function getWatchingFormatted( int $watching ): string {
		return $watching > 0
			? Html::element( 'span', [], $this->getLanguage()->formatNum( $watching ) )
			: Html::rawElement(
				'div',
				[ 'class' => 'cdx-info-chip' ],
				Html::element( 'span', [ 'class' => 'cdx-info-chip--text' ],
					$this->msg( 'pendingchanges-unwatched' )->text() )
			);
	}
}
