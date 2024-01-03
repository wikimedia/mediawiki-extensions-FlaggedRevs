<?php

use MediaWiki\Title\Title;

/**
 * @group Database
 * @covers \FRUserCounters
 */
class FRUserCountersTest extends MediaWikiIntegrationTestCase {

	public function testGetAndSaveUserParams() {
		$p = FRUserCounters::getUserParams( -1 );
		$expected = [
			'uniqueContentPages' 	=> [],
			'totalContentEdits'  	=> 0,
			'editComments' 			=> 0,
			'revertedEdits' 		=> 0
		];
		$this->assertEquals( $expected, $p, "Initial params" );

		$expected = [
			'uniqueContentPages'	=> [],
			'totalContentEdits' 	=> 666,
			'editComments' 			=> 666,
			'revertedEdits' 		=> 13
		];
		FRUserCounters::saveUserParams( 1, $expected );
		$ps = FRUserCounters::getUserParams( 1 );
		$this->assertEquals( $expected, $ps, "Param save and fetch from DB 1" );

		$expected = [
			'uniqueContentPages'	=> [ 23, 55 ],
			'totalContentEdits' 	=> 666,
			'editComments' 			=> 666,
			'revertedEdits' 		=> 13
		];
		FRUserCounters::saveUserParams( 1, $expected );
		$ps = FRUserCounters::getUserParams( 1 );
		$this->assertEquals( $expected, $ps, "Param save and fetch from DB 2" );
	}

	public function testUpdateUserParams() {
		$p = FRUserCounters::getUserParams( -1 );
		# Assumes (main) IN content namespace
		$title = Title::makeTitleSafe( 0, 'helloworld' );

		$copyP = $p;
		FRUserCounters::updateUserParams( $copyP, $title, "Manual edit comment" );
		$this->assertEquals( $p['editComments'] + 1, $copyP['editComments'], "Manual summary" );

		$copyP = $p;
		FRUserCounters::updateUserParams( $copyP, $title, "/* section */" );
		$this->assertEquals( $p['editComments'], $copyP['editComments'], "Auto summary" );

		$copyP = $p;
		FRUserCounters::updateUserParams( $copyP, $title, "edit summary" );
		$this->assertEquals( $p['totalContentEdits'] + 1, $copyP['totalContentEdits'],
			"Content edit count on content edit" );

		$expected = $p['uniqueContentPages'];
		$this->assertEquals( $expected, $copyP['uniqueContentPages'],
			"Unique content pages on content edit" );

		# Assumes (user) NOT IN content namespace
		$title = Title::makeTitleSafe( NS_USER, 'helloworld' );

		$copyP = $p;
		FRUserCounters::updateUserParams( $copyP, $title, "Manual edit comment" );
		$this->assertEquals( $p['editComments'] + 1, $copyP['editComments'], "Manual summary" );

		$copyP = $p;
		FRUserCounters::updateUserParams( $copyP, $title, "/* section */" );
		$this->assertEquals( $p['editComments'], $copyP['editComments'], "Auto summary" );

		$title = Title::makeTitleSafe( NS_USER, 'helloworld' );

		$copyP = $p;
		FRUserCounters::updateUserParams( $copyP, $title, "edit summary" );
		$this->assertEquals( $p['totalContentEdits'], $copyP['totalContentEdits'],
			"Content edit count on non-content edit" );
		$this->assertEquals( $p['uniqueContentPages'], $copyP['uniqueContentPages'],
			"Unique content pages on non-content edit" );
	}
}
