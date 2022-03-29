<?php

/**
 * @covers \FlaggedRevs
 */
class FlaggedRevsTest extends MediaWikiIntegrationTestCase {

	public function testGetLevels() {
		$this->setMwGlobals( [
			'wgExtensionFunctions' => [],
			'wgFlaggedRevsProtection' => false,
			'wgFlaggedRevsTags' => [ 'accuracy' => [ 'levels' => 3, 'quality' => 2 ] ],
		] );

		$this->assertSame( [
			'accuracy-0',
			'accuracy-1',
			'accuracy-2',
			'accuracy-3'
		], FlaggedRevs::getLevels() );
	}

}
