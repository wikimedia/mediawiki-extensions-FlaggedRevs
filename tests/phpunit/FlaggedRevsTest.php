<?php

use Wikimedia\TestingAccessWrapper;

/**
 * @covers \FlaggedRevs
 */
class FlaggedRevsTest extends MediaWikiIntegrationTestCase {

	public function provideGetLevels() {
		return [
			'three levels' => [
				'revsTags' => [ 'accuracy' => [ 'levels' => 3 ] ],
				'expected' => [ 'accuracy-0', 'accuracy-1', 'accuracy-2', 'accuracy-3' ],
			],
			'two named levels' => [
				'revsTags' => [ 'accuracy' => [ 'levels' => 2 ] ],
				'expected' => [ 'accuracy-0', 'accuracy-1', 'accuracy-2' ],
			],
		];
	}

	/**
	 * @dataProvider provideGetLevels
	 */
	public function testGetLevels( $revsTags, $expected ) {
		$this->setMwGlobals( [
			'wgExtensionFunctions' => [],
			'wgFlaggedRevsProtection' => false,
			'wgFlaggedRevsTags' => $revsTags,
		] );

		/** @var FlaggedRevs $staticAccess */
		$staticAccess = TestingAccessWrapper::newFromClass( FlaggedRevs::class );
		$staticAccess->dimensions = null;

		$this->assertSame( $expected, FlaggedRevs::getLevels() );
	}

}
