<?php

use Wikimedia\TestingAccessWrapper;

class FRParserCacheStableTest extends PHPUnit\Framework\TestCase {

	/**
	 * @var FRParserCacheStable|TestingAccessWrapper
	 */
	private $cache;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp();
		$this->cache = TestingAccessWrapper::newFromObject( FRParserCacheStable::singleton() );
	}

	// Tests for ParserCache changes - make sure stable keys are different
	public function testGetParserOutputKey() {
		$article = new Article( Title::newMainPage() );
		$key = $this->cache->getParserOutputKey( $article, '' );
		$this->assertRegExp( '/:stable-pcache:/', $key, 'Stable/latest cache has separation' );
	}

	// Tests for ParserCache changes - make sure stable keys are different
	public function testGetOptionsKey() {
		$article = new Article( Title::newMainPage() );
		$key = $this->cache->getOptionsKey( $article );
		$this->assertRegExp( '/:stable-pcache:/', $key, 'Stable/latest cache has separation' );
	}
}
