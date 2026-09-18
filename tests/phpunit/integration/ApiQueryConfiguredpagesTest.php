<?php
namespace MediaWiki\Extension\FlaggedRevs\Tests\Integration;

use FRPageConfig;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Tests\Api\ApiTestCase;

/**
 * @covers \ApiQueryConfiguredpages
 * @group Database
 */
class ApiQueryConfiguredpagesTest extends ApiTestCase {

	/** Stable version is the default, and only sysops can autoreview. */
	private static PageIdentity $restrictedPage;

	/** Latest version is the default, and anybody can autoreview. */
	private static PageIdentity $draftPage;

	/** Same as self::$draftPage, but the configuration is no longer valid. */
	private static PageIdentity $expiredPage;

	public function addDBDataOnce() {
		$this->overrideConfigValues( [
			'FlaggedRevsProtection' => false,
			'FlaggedRevsRestrictionLevels' => [ 'sysop' ],
		] );

		self::$restrictedPage = $this->newConfiguredPage( [
			'override' => 1,
			'autoreview' => 'sysop',
			'expiry' => 'infinity',
		] );
		self::$draftPage = $this->newConfiguredPage( [
			'override' => 0,
			'autoreview' => '',
			'expiry' => 'infinity',
		] );

		// Write the expired row directly. FRPageConfig::setStabilitySettings()
		// purges expired rows at random, which makes the test unstable.
		self::$expiredPage = $this->getExistingTestPage();
		$dbw = $this->getDb();
		$dbw->newInsertQueryBuilder()
			->insertInto( 'flaggedpage_config' )
			->row( [
				'fpc_page_id' => self::$expiredPage->getId(),
				'fpc_override' => 0,
				'fpc_level' => '',
				'fpc_expiry' => $dbw->encodeExpiry( wfTimestamp( TS_MW, (int)wfTimestamp() - 86400 ) ),
			] )
			->caller( __METHOD__ )
			->execute();
	}

	/**
	 * Create a page and give it the supplied stability configuration.
	 * @param array $config
	 * @return PageIdentity
	 */
	private function newConfiguredPage( array $config ): PageIdentity {
		$page = $this->getExistingTestPage();
		$this->assertTrue( FRPageConfig::setStabilitySettings( $page->getTitle(), $config ) );
		return $page;
	}

	/**
	 * Run list=configuredpages and return the page IDs it reports, in order.
	 * @param array $params
	 * @return int[]
	 */
	private function getReportedPageIds( array $params = [] ): array {
		[ $res ] = $this->doApiRequest( $params + [
			'action' => 'query',
			'list' => 'configuredpages',
			'cplimit' => 'max',
		] );
		return array_map(
			static fn ( array $page ) => $page['pageid'],
			$res['query']['configuredpages']
		);
	}

	public function testShouldListEveryConfiguredPage(): void {
		$this->assertSame(
			[
				self::$restrictedPage->getId(),
				self::$draftPage->getId(),
				// T438559: This configuration has expired and should not be listed.
				self::$expiredPage->getId(),
			],
			$this->getReportedPageIds()
		);
	}

	public function testShouldReportTheConfigurationOfAPage(): void {
		[ $res ] = $this->doApiRequest( [
			'action' => 'query',
			'list' => 'configuredpages',
			'cplimit' => 'max',
		] );
		$pages = $res['query']['configuredpages'];

		$this->assertSame( self::$restrictedPage->getId(), $pages[0]['pageid'] );
		$this->assertSame( NS_MAIN, $pages[0]['ns'] );
		$this->assertSame( 1, $pages[0]['stable_is_default'] );
		$this->assertSame( 'sysop', $pages[0]['autoreview'] );
		$this->assertSame( 'infinity', $pages[0]['expiry'] );

		$this->assertSame( 0, $pages[1]['stable_is_default'] );
		$this->assertSame( '', $pages[1]['autoreview'] );
	}

	/**
	 * @dataProvider provideDefaultVersionFilters
	 */
	public function testShouldFilterByDefaultVersion( string $default, array $expectedKeys ): void {
		$this->assertSame(
			array_map( static fn ( string $key ) => self::$$key->getId(), $expectedKeys ),
			$this->getReportedPageIds( [ 'cpdefault' => $default ] )
		);
	}

	public static function provideDefaultVersionFilters(): iterable {
		yield 'stable version' => [ 'stable', [ 'restrictedPage' ] ];
		yield 'latest version' => [ 'latest', [ 'draftPage', 'expiredPage' ] ];
	}

	/**
	 * @dataProvider provideAutoreviewFilters
	 */
	public function testShouldFilterByAutoreviewLevel( string $level, array $expectedKeys ): void {
		$this->assertSame(
			array_map( static fn ( string $key ) => self::$$key->getId(), $expectedKeys ),
			$this->getReportedPageIds( [ 'cpautoreview' => $level ] )
		);
	}

	public static function provideAutoreviewFilters(): iterable {
		yield 'restricted to sysops' => [ 'sysop', [ 'restrictedPage' ] ];
		yield 'not restricted' => [ 'none', [ 'draftPage', 'expiredPage' ] ];
	}

	public function testShouldFilterByNamespace(): void {
		$this->assertSame( [], $this->getReportedPageIds( [ 'cpnamespace' => NS_FILE ] ) );
	}
}
