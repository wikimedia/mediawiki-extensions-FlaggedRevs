<?php

/**
 * @covers \FlaggedRevs
 */
class FlaggedRevsTest extends MediaWikiIntegrationTestCase {

	public static function provideGetLevels() {
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
	public function testGetLevels( array $revsTags, array $expected ) {
		$this->setMwGlobals( [
			'wgFlaggedRevsTags' => $revsTags,
		] );

		$this->assertSame( $expected, FlaggedRevs::getLevels() );
		$this->assertSame( count( $expected ) - 1, FlaggedRevs::getMaxLevel() );
	}

}
