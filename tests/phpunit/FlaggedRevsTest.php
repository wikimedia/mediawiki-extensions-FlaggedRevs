<?php

use MediaWiki\Page\PageReference;

/**
 * @covers \FlaggedRevs
 */
class FlaggedRevsTest extends MediaWikiIntegrationTestCase {

	public function testReviewNamespaces() {
		$this->setMwGlobals( 'wgFlaggedRevsNamespaces', [ NS_FILE ] );

		$article = $this->createMock( PageReference::class );
		$file = $this->createMock( PageReference::class );
		$file->method( 'getNamespace' )->willReturn( NS_FILE );

		$this->assertSame( [ NS_FILE ], FlaggedRevs::getReviewNamespaces() );
		$this->assertFalse( FlaggedRevs::isReviewNamespace( NS_MAIN ) );
		$this->assertTrue( FlaggedRevs::isReviewNamespace( NS_FILE ) );
		$this->assertTrue( FlaggedRevs::isReviewNamespace( NS_MEDIA ) );
		$this->assertFalse( FlaggedRevs::inReviewNamespace( $article ) );
		$this->assertTrue( FlaggedRevs::inReviewNamespace( $file ) );
	}

	public function provideConfiguration() {
		return [
			'everything disabled' => [
				'config' => [],
				'expected' => []
			],
			'two levels (binary)' => [
				'config' => [
					'wgFlaggedRevsTags' => [ 'default' => [ 'levels' => 1 ] ],
				],
				'expected' => [
					'binaryFlagging' => true,
					'getLevels' => [ 'default-0', 'default-1' ],
					'getMaxLevel' => 1,
				]
			],
			'more than two levels (non-binary)' => [
				'config' => [
					'wgFlaggedRevsTags' => [ 'default' => [ 'levels' => 2 ] ],
				],
				'expected' => [
					'binaryFlagging' => false,
					'getLevels' => [ 'default-0', 'default-1', 'default-2' ],
					'getMaxLevel' => 2,
				]
			],

			'autoreview changes' => [
				'config' => [
					'wgFlaggedRevsAutoReview' => FR_AUTOREVIEW_CHANGES,
				],
				'expected' => [
					'autoReviewEdits' => true,
					'autoReviewEnabled' => true,
				]
			],
			'autoreview creation' => [
				'config' => [
					'wgFlaggedRevsAutoReview' => FR_AUTOREVIEW_CREATION,
				],
				'expected' => [
					'autoReviewEnabled' => true,
					'autoReviewNewPages' => true,
				]
			],
			'autoreview creation and changes' => [
				'config' => [
					'wgFlaggedRevsAutoReview' => FR_AUTOREVIEW_CREATION_AND_CHANGES,
				],
				'expected' => [
					'autoReviewEdits' => true,
					'autoReviewEnabled' => true,
					'autoReviewNewPages' => true,
				]
			],

			'stable revision shown instead of latest revision' => [
				'config' => [
					'wgFlaggedRevsOverride' => true,
				],
				'expected' => [
					'isStableShownByDefault' => true,
				]
			],
			'template stabilization mode' => [
				'config' => [
					'wgFlaggedRevsHandleIncludes' => FR_INCLUDES_STABLE,
				],
				'expected' => [
					'inclusionSetting' => FR_INCLUDES_STABLE,
				]
			],
			'trusted user groups' => [
				'config' => [
					'wgFlaggedRevsRestrictionLevels' => [ 'user-with-power' ],
				],
				'expected' => [
					'getRestrictionLevels' => [ 'user-with-power' ],
				]
			],

			'only protection flag with minimum configuration' => [
				'config' => [ 'wgFlaggedRevsProtection' => true ],
				'expected' => [
					'binaryFlagging' => true,
					'quickTag' => null,
					'quickTags' => [],
					'useOnlyIfProtected' => true,
				]
			],
			'only protection flag with trusted user groups configured' => [
				'config' => [
					'wgFlaggedRevsProtection' => true,
					'wgFlaggedRevsRestrictionLevels' => [ 'user-with-power' ],
				],
				'expected' => [
					'getRestrictionLevels' => [ 'user-with-power' ],
					'quickTag' => null,
					'quickTags' => [],
					'useOnlyIfProtected' => true,
					'useProtectionLevels' => true,
				]
			],
			'only protection flag disables page stabilization' => [
				'config' => [
					'wgFlaggedRevsOverride' => true,
					'wgFlaggedRevsProtection' => true,
				],
				'expected' => [
					'isStableShownByDefault' => false,
					'quickTag' => null,
					'quickTags' => [],
					'useOnlyIfProtected' => true,
				]
			],
			'only protection flag (mostly) disables non-binary flagging' => [
				'config' => [
					'wgFlaggedRevsProtection' => true,
					'wgFlaggedRevsTags' => [ 'default' => [ 'levels' => 2 ] ]
				],
				'expected' => [
					'binaryFlagging' => true,
					'getLevels' => [ 'default-0', 'default-1', 'default-2' ],
					'getMaxLevel' => 2,
					'quickTag' => null,
					'quickTags' => [],
					'useOnlyIfProtected' => true,
				]
			],
		];
	}

	/**
	 * @dataProvider provideConfiguration
	 */
	public function testBasicConfiguration( array $config, array $expected ) {
		$this->setMwGlobals( $config + [
			// Most minimal default configuration
			'wgFlaggedRevsAutoReview' => FR_AUTOREVIEW_NONE,
			'wgFlaggedRevsHandleIncludes' => FR_INCLUDES_CURRENT,
			'wgFlaggedRevsNamespaces' => [],
			'wgFlaggedRevsOverride' => false,
			'wgFlaggedRevsProtection' => false,
			'wgFlaggedRevsRestrictionLevels' => [],
			'wgFlaggedRevsTags' => [ 'default' => [ 'levels' => 0 ] ],
		] );

		// Methods to test with the most trivial return value that's true for most test cases
		$methodsToTest = [
			'autoReviewEdits' => false,
			'autoReviewEnabled' => false,
			'autoReviewNewPages' => false,
			'binaryFlagging' => true,
			'getLevels' => [ 'default-0' ],
			'getMaxLevel' => 0,
			'getRestrictionLevels' => [],
			'inclusionSetting' => FR_INCLUDES_CURRENT,
			'isStableShownByDefault' => false,
			'quickTag' => 1,
			'quickTags' => [ 'default' => 1 ],
			'useOnlyIfProtected' => false,
			'useProtectionLevels' => false,
		];
		foreach ( $methodsToTest as $method => $expectedValue ) {
			// To keep the data provider minimal it contains only exceptional expected values
			if ( array_key_exists( $method, $expected ) ) {
				$expectedValue = $expected[$method];
			}
			$this->assertSame( $expectedValue, [ FlaggedRevs::class, $method ](), $method );
		}

		// Some more that are currently identical for all test cases
		$this->assertSame( 'default', FlaggedRevs::getTagName() );
		$this->assertTrue( FlaggedRevs::tagIsValid( 0 ) );
	}

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
