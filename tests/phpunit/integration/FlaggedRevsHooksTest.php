<?php

namespace MediaWiki\Extension\FlaggedRevs\Test;

use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;

/**
 * Tests the Hooks class.
 *
 * @covers FlaggedRevsHooks
 *
 * @group FlaggedRevs
 * @group extensions
 * @group medium
 * @group Database
 */
class FlaggedRevsHooksTest extends MediaWikiIntegrationTestCase {

	/**
	 * @covers FlaggedRevsHooks::onPageMoveComplete()
	 */
	public function testAutoReviewedPageStaysReviewedAfterMove() {
		// configure
		global $wgFlaggedRevsOverride, $wgFlaggedRevsProtection;
		$this->overrideConfigValues( [
			'FlaggedRevsOverride' => true,
			'FlaggedRevsProtection' => false,
		] );
		$this->assertTrue( $wgFlaggedRevsOverride, 'Setting should be configured' );
		$this->assertFalse( $wgFlaggedRevsProtection, 'Setting should be configured' );

		// admin creates page, should be auto reviewed
		$admin = $this->getTestSysop()->getUser();
		$array = $this->insertPage( 'FlaggedRevs', 'Loren ipsum', 0, $admin );
		$pageId = $array[ 'id' ];
		$this->assertTrue( $pageId > 0, 'Page should be created' );

		// assert that page is reviewed
		$row = $this->newSelectQueryBuilder()
			->select( 'fp_reviewed' )
			->from( 'flaggedpages' )
			->where( [ 'fp_page_id' => $pageId ] )
			->caller( __METHOD__ )->fetchRow();
		$this->assertTrue( $row->fp_reviewed === "1", 'Page should be reviewed' );

		// admin moves it without leaving a redirect
		$oldTitle = Title::makeTitle( 0, 'FlaggedRevs' );
		$newTitle = Title::makeTitle( 0, 'FlaggedRevs2' );
		$movePage = $this->getServiceContainer()->getMovePageFactory()->newMovePage( $oldTitle, $newTitle );
		$movePage->move( $admin, 'Edit summary', true );
		$currentPageTitle = Title::newFromID( $pageId )->getText();
		$this->assertTrue( $currentPageTitle === 'FlaggedRevs2', 'Page should have moved to new title' );

		// assert that page is reviewed
		$row = $this->newSelectQueryBuilder()
			->select( 'fp_reviewed' )
			->from( 'flaggedpages' )
			->where( [ 'fp_page_id' => $pageId ] )
			->caller( __METHOD__ )->fetchRow();
		$this->assertTrue( $row->fp_reviewed === "1", 'Page should be reviewed' );
	}
}
