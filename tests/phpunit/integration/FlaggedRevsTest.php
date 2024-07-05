<?php

use MediaWiki\Page\PageReference;

/**
 * @covers \FlaggedRevs
 */
class FlaggedRevsTest extends MediaWikiIntegrationTestCase {

	public function testReviewNamespaces() {
		$this->overrideConfigValue( 'FlaggedRevsNamespaces', [ NS_FILE ] );

		$article = $this->createMock( PageReference::class );
		$media = $this->createMock( PageReference::class );
		$media->method( 'getNamespace' )->willReturn( NS_MEDIA );

		$this->assertSame( [ NS_FILE ], FlaggedRevs::getReviewNamespaces() );
		$this->assertSame( NS_FILE, FlaggedRevs::getFirstReviewNamespace() );
		$this->assertFalse( FlaggedRevs::isReviewNamespace( NS_MAIN ) );
		$this->assertTrue( FlaggedRevs::isReviewNamespace( NS_FILE ) );
		$this->assertFalse( FlaggedRevs::inReviewNamespace( $article ) );
		$this->assertTrue( FlaggedRevs::inReviewNamespace( $media ) );
	}

	public function provideConfiguration() {
		return [
			'everything disabled' => [
				'config' => [],
				'expected' => []
			],
			'two levels (binary)' => [
				'config' => [
					'FlaggedRevsTags' => [ 'default' => [ 'levels' => 1 ] ],
				],
				'expected' => [
					'binaryFlagging' => true,
					'getMaxLevel' => 1,
				]
			],
			'more than two levels (non-binary)' => [
				'config' => [
					'FlaggedRevsTags' => [ 'default' => [ 'levels' => 2 ] ],
				],
				'expected' => [
					'binaryFlagging' => false,
					'getMaxLevel' => 2,
				]
			],

			'autoreview changes' => [
				'config' => [
					'FlaggedRevsAutoReview' => FR_AUTOREVIEW_CHANGES,
				],
				'expected' => [
					'autoReviewEdits' => true,
					'autoReviewEnabled' => true,
				]
			],
			'autoreview creation' => [
				'config' => [
					'FlaggedRevsAutoReview' => FR_AUTOREVIEW_CREATION,
				],
				'expected' => [
					'autoReviewEnabled' => true,
					'autoReviewNewPages' => true,
				]
			],
			'autoreview creation and changes' => [
				'config' => [
					'FlaggedRevsAutoReview' => FR_AUTOREVIEW_CREATION_AND_CHANGES,
				],
				'expected' => [
					'autoReviewEdits' => true,
					'autoReviewEnabled' => true,
					'autoReviewNewPages' => true,
				]
			],

			'stable revision shown instead of latest revision' => [
				'config' => [
					'FlaggedRevsOverride' => true,
				],
				'expected' => [
					'isStableShownByDefault' => true,
				]
			],
			'template stabilization mode' => [
				'config' => [
					'FlaggedRevsHandleIncludes' => FR_INCLUDES_STABLE,
				],
				'expected' => [
					'inclusionSetting' => FR_INCLUDES_STABLE,
				]
			],
			'trusted user groups' => [
				'config' => [
					'FlaggedRevsRestrictionLevels' => [ 'user-with-power' ],
				],
				'expected' => [
					'getRestrictionLevels' => [ 'user-with-power' ],
				]
			],

			'only protection flag with minimum configuration' => [
				'config' => [ 'FlaggedRevsProtection' => true ],
				'expected' => [
					'binaryFlagging' => true,
					'quickTag' => null,
					'quickTags' => [],
					'useOnlyIfProtected' => true,
				]
			],
			'only protection flag with trusted user groups configured' => [
				'config' => [
					'FlaggedRevsProtection' => true,
					'FlaggedRevsRestrictionLevels' => [ 'user-with-power' ],
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
					'FlaggedRevsOverride' => true,
					'FlaggedRevsProtection' => true,
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
					'FlaggedRevsProtection' => true,
					'FlaggedRevsTags' => [ 'default' => [ 'levels' => 2 ] ]
				],
				'expected' => [
					'binaryFlagging' => true,
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
		$this->overrideConfigValues( $config + [
			// Most minimal default configuration
			'FlaggedRevsAutoReview' => FR_AUTOREVIEW_NONE,
			'FlaggedRevsHandleIncludes' => FR_INCLUDES_CURRENT,
			'FlaggedRevsNamespaces' => [],
			'FlaggedRevsOverride' => false,
			'FlaggedRevsProtection' => false,
			'FlaggedRevsRestrictionLevels' => [],
			'FlaggedRevsTags' => [ 'default' => [ 'levels' => 0 ] ],
		] );

		// Methods to test with the most trivial return value that's true for most test cases
		$methodsToTest = [
			[ [ FlaggedRevs::class, 'autoReviewEdits' ], false ],
			[ [ FlaggedRevs::class, 'autoReviewEnabled' ], false ],
			[ [ FlaggedRevs::class, 'autoReviewNewPages' ], false ],
			[ [ FlaggedRevs::class, 'binaryFlagging' ], true ],
			[ [ FlaggedRevs::class, 'getMaxLevel' ], 0 ],
			[ [ FlaggedRevs::class, 'getRestrictionLevels' ], [] ],
			[ [ FlaggedRevs::class, 'inclusionSetting' ], FR_INCLUDES_CURRENT ],
			[ [ FlaggedRevs::class, 'isStableShownByDefault' ], false ],
			[ [ FlaggedRevs::class, 'quickTag' ], 1 ],
			[ [ FlaggedRevs::class, 'quickTags' ], [ 'default' => 1 ] ],
			[ [ FlaggedRevs::class, 'useOnlyIfProtected' ], false ],
			[ [ FlaggedRevs::class, 'useProtectionLevels' ], false ],
		];
		foreach ( $methodsToTest as [ $callable, $expectedValue ] ) {
			$method = $callable[1];
			// To keep the data provider minimal it contains only exceptional expected values
			if ( array_key_exists( $method, $expected ) ) {
				$expectedValue = $expected[$method];
			}
			$this->assertSame( $expectedValue, $callable(), $method );
		}

		// Some more that are currently identical for all test cases
		$this->assertSame( NS_MAIN, FlaggedRevs::getFirstReviewNamespace() );
		$this->assertSame( [], FlaggedRevs::getReviewNamespaces() );
		$this->assertSame( 'default', FlaggedRevs::getTagName() );
		$this->assertTrue( FlaggedRevs::tagIsValid( 0 ) );
	}

}
