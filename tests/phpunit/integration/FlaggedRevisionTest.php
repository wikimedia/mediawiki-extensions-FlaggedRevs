<?php

use MediaWiki\Content\TextContent;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Title\TitleValue;

/**
 * @covers \FlaggedRevision
 */
class FlaggedRevisionTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		$this->overrideConfigValues( [
			'FlaggedRevsProtection' => false,
			'FlaggedRevsTags' => [ 'example' => [ 'levels' => 3 ] ],
		] );
		parent::setUp();
	}

	public function testConstructorAndTrivialGetters() {
		$revRecord = $this->createMock( RevisionRecord::class );
		$revRecord->method( 'getContent' )
			->willReturn( new TextContent( __FUNCTION__ ) );
		$revRecord->method( 'getPageAsLinkTarget' )
			->willReturn( new TitleValue( NS_MAIN, __FUNCTION__ ) );
		$revRecord->method( 'getTimestamp' )
			->willReturn( '19990101000000' );

		$frev = new FlaggedRevision( [
			'timestamp' => '20221231000000',
			'tags' => 'a:2',
			'flags' => '',
			'user_id' => 0,
			'revrecord' => $revRecord,
		] );

		$this->assertSame( null, $frev->getRevId() );
		$this->assertSame( __FUNCTION__, $frev->getTitle()->getPrefixedText() );
		$this->assertSame( '20221231000000', $frev->getTimestamp() );
		$this->assertSame( '19990101000000', $frev->getRevTimestamp() );
		$this->assertSame( $revRecord, $frev->getRevisionRecord() );
		$this->assertSame( __FUNCTION__, $frev->getRevText() );
		$this->assertSame( [ 'example' => 0, 'a' => 2 ], $frev->getTags() );
		$this->assertSame( 0, $frev->getTag() );
	}

	public static function provideNonDefaultTags() {
		return [
			[ '', [ 'example' => 0 ] ],
			[ [], [ 'example' => 0 ] ],
			[ [ 'a' => 2 ], [ 'example' => 0, 'a' => 2 ] ],
		];
	}

	/**
	 * @dataProvider provideNonDefaultTags
	 */
	public function testConstructorInitializesDefaultTags( $tags, array $expected ) {
		$frev = new FlaggedRevision( [
			'timestamp' => '20221231000000',
			'tags' => $tags,
			'flags' => '',
			'user_id' => 0,
			'revrecord' => $this->createMock( RevisionRecord::class ),
		] );
		$this->assertSame( $expected, $frev->getTags() );
	}

	public static function provideRevisionTagArrays() {
		return [
			[ [], '' ],
			[ [ 'a' => 2, 'b' => 3 ], "a:2\nb:3" ],
			[ [ 'a' => ' 2 ', 'b' => 3.9 ], "a:2\nb:3" ],
			[ [ 'a' => -8, 'b' => 9 ], "a:-8\nb:9" ],
		];
	}

	/**
	 * @covers \FlaggedRevision::flattenRevisionTags
	 * @dataProvider provideRevisionTagArrays
	 */
	public function testFlattenRevisionTags( array $tags, string $expected ) {
		$string = FlaggedRevision::flattenRevisionTags( $tags );
		$this->assertSame( $expected, $string );
	}

	public static function provideFlattenedRevisionTags() {
		return [
			[ '', [] ],
			[ "a\nb=3", [] ],
			[ 'a:2\nb:3', [ 'a' => 2, 'b' => 3 ] ],
			[ "example: 2 \nb:3.9\n", [ 'example' => 2, 'b' => 3 ] ],
			[ "example:-8\nb:9\n", [ 'example' => 0, 'b' => 3 ] ],
		];
	}

	/**
	 * @covers \FlaggedRevision::expandRevisionTags
	 * @dataProvider provideFlattenedRevisionTags
	 */
	public function testExpandRevisionTags( string $tags, array $expected ) {
		$array = FlaggedRevision::expandRevisionTags( $tags );
		$this->assertSame( $expected, $array );

		$this->assertSame( $expected, FlaggedRevision::expandRevisionTags(
			FlaggedRevision::flattenRevisionTags( $array )
		), 'full round-trip' );
	}

}
