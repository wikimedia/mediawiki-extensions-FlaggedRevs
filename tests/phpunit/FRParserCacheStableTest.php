<?php

use Wikimedia\TestingAccessWrapper;

/**
 * @covers \FRParserCacheStable
 */
class FRParserCacheStableTest extends PHPUnit\Framework\TestCase {

	/**
	 * @var FRParserCacheStable|TestingAccessWrapper
	 */
	private $cache;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() : void {
		parent::setUp();
		$this->cache = TestingAccessWrapper::newFromObject( FRParserCacheStable::singleton() );
	}

	// Tests for ParserCache changes - make sure stable keys are different
	public function testGetParserOutputKey() {
		$wikiPage = new WikiPage( Title::newMainPage() );
		$key = $this->cache->getParserOutputKey( $wikiPage, '' );
		$this->assertRegExp( '/:stable-pcache:/', $key, 'Stable/latest cache has separation' );
	}

	// Tests for ParserCache changes - make sure stable keys are different
	public function testGetOptionsKey() {
		$wikiPage = new WikiPage( Title::newMainPage() );
		$key = $this->cache->getOptionsKey( $wikiPage );
		$this->assertRegExp( '/:stable-pcache:/', $key, 'Stable/latest cache has separation' );
	}
}
