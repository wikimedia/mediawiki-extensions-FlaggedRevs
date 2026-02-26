<?php

namespace MediaWiki\Extension\FlaggedRevs\Tests\Integration;

use FlaggablePageView;
use FlaggableWikiPage;
use FlaggedRevs;
use MediaWiki\Context\RequestContext;
use MediaWiki\Page\WikiPage;
use MediaWiki\Parser\ParserCache;
use MediaWiki\Parser\ParserCacheFactory;
use MediaWiki\Tests\Parser\ParserCacheTestBase;
use MediaWiki\Tests\Parser\TrackerWrapper;
use MediaWiki\Tests\Parser\TrackingParserCache;

/**
 * @group Database
 * @covers FlaggablePageView
 */
class FlaggedRevsCacheTest extends ParserCacheTestBase {

	private TrackerWrapper $trackerWrapper;
	private WikiPage $testPage;

	public function setUp(): void {
		parent::setUp();
		$parserCacheFactory = $this->createMock( ParserCacheFactory::class );
		$this->overrideMwServices( null, [ 'ParserCacheFactory' => static fn () => $parserCacheFactory ] );

		$this->setTemporaryHook(
			'ParserOptionsRegister',
			static function ( &$defaults, &$inCacheKey, &$lazyLoad ) {
				$defaults['useParsoid'] = true;
			}
		);
		$this->trackerWrapper = new TrackerWrapper();
		$caches = [];

		$parserCacheFactory->method( 'getParserCache' )->willReturnCallback(
			function ( $cacheName ) use ( &$caches ) {
				$caches[$cacheName] ??= $this->getParserCache( $cacheName );
				return $caches[$cacheName];
			}
		);

		$this->testPage = $this->getExistingTestPage();
		$this->editPage( $this->testPage, 'hello' );
		$stableRev = $this->testPage->getRevisionRecord();

		FlaggedRevs::autoReviewEdit(
			$this->testPage,
			$this->getTestSysop()->getUser(),
			$stableRev
		);
		// clear local cache to update stable version in there
		FlaggableWikiPage::getTitleInstance( $this->testPage->getTitle() )->clear();
	}

	protected function createParserCache( ...$args ): ParserCache {
		return new TrackingParserCache( $this->trackerWrapper, ...$args );
	}

	public function testCache() {
		$this->overrideConfigValue( 'UsePostprocCacheParsoid', true );
		$this->setTemporaryHook(
			'ParserOptionsRegister',
			static function ( &$defaults, &$inCacheKey, &$lazyLoad ) {
				$defaults['useParsoid'] = true;
			}
		);

		RequestContext::getMain()->setTitle( $this->testPage->getTitle() );
		$flaggablePageView = FlaggablePageView::newFromTitle( $this->testPage );
		$flaggablePageView->getRequest()->appendQueryValue( 'stable', 1 );

		// first call: nothing in cache
		$parserOutput = null;
		$useCache = true;
		$flaggablePageView->setPageContent( $parserOutput, $useCache );

		$this->assertArrayEquals( [
				[ 'stable-parsoid-pcache-postproc', false ],
				[ 'stable-parsoid-pcache', false ]
			],
			$this->trackerWrapper->calls );

		// second call: find it in the cache
		$this->trackerWrapper->calls = [];
		$parserOutput = null;
		$useCache = true;
		$flaggablePageView->setPageContent( $parserOutput, $useCache );
		$this->assertArrayEquals( [ [ 'stable-parsoid-pcache-postproc', true ] ], $this->trackerWrapper->calls );
	}

	public function testCacheDisabled() {
		$this->overrideConfigValue( 'UsePostprocCacheParsoid', false );

		RequestContext::getMain()->setTitle( $this->testPage->getTitle() );
		$flaggablePageView = FlaggablePageView::newFromTitle( $this->testPage );
		$flaggablePageView->getRequest()->appendQueryValue( 'stable', 1 );

		// first call: nothing in cache
		$parserOutput = null;
		$useCache = true;
		$flaggablePageView->setPageContent( $parserOutput, $useCache );
		$this->assertArrayEquals( [
			[ 'stable-parsoid-pcache', false ]
		],
			$this->trackerWrapper->calls );

		// second call: find it in the cache
		$this->trackerWrapper->calls = [];
		$parserOutput = null;
		$useCache = true;
		$flaggablePageView->setPageContent( $parserOutput, $useCache );
		$this->assertArrayEquals( [ [ 'stable-parsoid-pcache', true ] ], $this->trackerWrapper->calls );
	}
}
