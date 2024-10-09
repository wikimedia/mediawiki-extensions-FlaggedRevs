<?php

namespace MediaWiki\Extension\FlaggedRevs\Test;

use Closure;
use FRUserCounters;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Request\FauxRequest;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Storage\EditResult;
use MediaWiki\Tests\User\TempUser\TempUserTestTrait;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
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
	use TempUserTestTrait;

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

	/**
	 * @covers FlaggedRevsHooks::onPageSaveComplete
	 * @covers FlaggedRevsHooks::maybeIncrementReverts
	 */
	public function testShouldNotUpdateCountersOnContentPageEditForNamedUserIfNoAutopromote(): void {
		$this->overrideConfigValue( 'FlaggedRevsAutoconfirm', false );

		$page = $this->getNonexistingTestPage();
		$editor = $this->getTestUser()->getAuthority();

		$this->editPage( $page, 'test', '', NS_MAIN, $editor );

		$params = FRUserCounters::getParams( $editor->getUser() );

		$this->assertSame(
			[
				'uniqueContentPages' => [],
				'totalContentEdits' => 0,
				'editComments' => 0,
				'revertedEdits' => 0,
			],
			$params
		);
	}

	/**
	 * @covers FlaggedRevsHooks::onPageSaveComplete
	 * @covers FlaggedRevsHooks::maybeIncrementReverts
	 */
	public function testShouldUpdateCountersOnContentPageEditForNamedUser(): void {
		$this->overrideConfigValue( 'FlaggedRevsAutoconfirm', true );

		$page = $this->getNonexistingTestPage();
		$editor = $this->getTestUser()->getAuthority();

		$this->editPage( $page, 'test', '', NS_MAIN, $editor );

		$params = FRUserCounters::getParams( $editor->getUser() );

		$this->assertSame(
			[
				'uniqueContentPages' => [ $page->getId() ],
				'totalContentEdits' => 1,
				'editComments' => 1,
				'revertedEdits' => 0,
			],
			$params
		);
	}

	/**
	 * @dataProvider provideAnonymousOrTemporaryUsers
	 * @covers FlaggedRevsHooks::onPageSaveComplete
	 * @covers FlaggedRevsHooks::maybeIncrementReverts
	 */
	public function testShouldNotUpdateCountersOnContentPageEditForAnonymousUser(
		Closure $userProvider
	): void {
		$this->overrideConfigValue( 'FlaggedRevsAutoconfirm', true );
		$this->disableAutoCreateTempUser();

		$page = $this->getNonexistingTestPage();
		$editor = $userProvider->call( $this );

		$this->editPage( $page, 'test', '', NS_MAIN, $editor );

		$params = FRUserCounters::getUserParams( $editor->getId() );

		$this->assertSame(
			[
				'uniqueContentPages' => [],
				'totalContentEdits' => 0,
				'editComments' => 0,
				'revertedEdits' => 0,
			],
			$params
		);
	}

	/**
	 * @covers FlaggedRevsHooks::onPageSaveComplete
	 * @covers FlaggedRevsHooks::maybeIncrementReverts
	 */
	public function testShouldNotUpdateCountersOnNonContentPageEdit(): void {
		$this->overrideConfigValue( 'FlaggedRevsAutoconfirm', true );

		$page = Title::makeTitle( NS_USER, 'Test' );
		$editor = $this->getTestUser()->getAuthority();

		$this->editPage( $page, 'test', '', NS_MAIN, $editor );

		$params = FRUserCounters::getParams( $editor->getUser() );

		$this->assertSame(
			[
				'uniqueContentPages' => [],
				'totalContentEdits' => 0,
				'editComments' => 1,
				'revertedEdits' => 0,
			],
			$params
		);
	}

	/**
	 * @covers FlaggedRevsHooks::onPageSaveComplete
	 * @covers FlaggedRevsHooks::maybeIncrementReverts
	 */
	public function testShouldUpdateRevertCountForNamedUserOnUndoIfNoAutopromote(): void {
		$this->overrideConfigValue( 'FlaggedRevsAutoconfirm', false );

		$page = $this->getExistingTestPage();
		$editor = $this->getTestUser()->getAuthority();
		$reverter = $this->getTestSysop()->getUserIdentity();

		$this->editPage( $page, 'test', '', NS_MAIN, $editor );
		$this->doRevertPage( $page, $page->getRevisionRecord(), $reverter );

		$params = FRUserCounters::getParams( $editor->getUser() );

		$this->assertSame(
			[
				'uniqueContentPages' => [],
				'totalContentEdits' => 0,
				'editComments' => 0,
				'revertedEdits' => 1,
			],
			$params
		);
	}

	/**
	 * @covers FlaggedRevsHooks::onPageSaveComplete
	 * @covers FlaggedRevsHooks::maybeIncrementReverts
	 */
	public function testShouldUpdateRevertCountForNamedUserOnUndo(): void {
		$this->overrideConfigValue( 'FlaggedRevsAutoconfirm', true );

		$page = $this->getExistingTestPage();
		$editor = $this->getTestUser()->getAuthority();
		$reverter = $this->getTestSysop()->getUserIdentity();

		$this->editPage( $page, 'test', '', NS_MAIN, $editor );
		$this->doRevertPage( $page, $page->getRevisionRecord(), $reverter );

		$params = FRUserCounters::getParams( $editor->getUser() );

		$this->assertSame(
			[
				'uniqueContentPages' => [ $page->getId() ],
				'totalContentEdits' => 1,
				'editComments' => 1,
				'revertedEdits' => 1,
			],
			$params
		);
	}

	/**
	 * @covers FlaggedRevsHooks::onPageSaveComplete
	 * @covers FlaggedRevsHooks::maybeIncrementReverts
	 */
	public function testShouldUpdateRevertCountForNamedUserOnRollbackIfNoAutopromote(): void {
		$this->overrideConfigValue( 'FlaggedRevsAutoconfirm', false );

		$page = $this->getExistingTestPage();
		$editor = $this->getTestUser()->getAuthority();
		$reverter = $this->getTestSysop()->getAuthority();

		$this->editPage( $page, 'test', '', NS_MAIN, $editor );

		$rollbackStatus = $this->getServiceContainer()
			->getRollbackPageFactory()
			->newRollbackPage( $page, $reverter, $editor->getUser() )
			->rollback();

		$params = FRUserCounters::getParams( $editor->getUser() );

		$this->assertStatusGood( $rollbackStatus );
		$this->assertSame(
			[
				'uniqueContentPages' => [],
				'totalContentEdits' => 0,
				'editComments' => 0,
				'revertedEdits' => 1,
			],
			$params
		);
	}

	/**
	 * @covers FlaggedRevsHooks::onPageSaveComplete
	 * @covers FlaggedRevsHooks::maybeIncrementReverts
	 */
	public function testShouldUpdateRevertCountForNamedUserOnRollback(): void {
		$this->overrideConfigValue( 'FlaggedRevsAutoconfirm', true );

		$page = $this->getExistingTestPage();
		$editor = $this->getTestUser()->getAuthority();
		$reverter = $this->getTestSysop()->getAuthority();

		$this->editPage( $page, 'test', '', NS_MAIN, $editor );

		$rollbackStatus = $this->getServiceContainer()
			->getRollbackPageFactory()
			->newRollbackPage( $page, $reverter, $editor->getUser() )
			->rollback();

		$params = FRUserCounters::getParams( $editor->getUser() );

		$this->assertStatusGood( $rollbackStatus );
		$this->assertSame(
			[
				'uniqueContentPages' => [ $page->getId() ],
				'totalContentEdits' => 1,
				'editComments' => 1,
				'revertedEdits' => 1,
			],
			$params
		);
	}

	/**
	 * @dataProvider provideAnonymousOrTemporaryUsers
	 * @covers FlaggedRevsHooks::onPageSaveComplete
	 * @covers FlaggedRevsHooks::maybeIncrementReverts
	 */
	public function testShouldNotUpdateRevertCountForAnonymousOrTemporaryUserOnUndo(
		Closure $userProvider
	): void {
		$this->overrideConfigValue( 'FlaggedRevsAutoconfirm', true );

		$page = $this->getExistingTestPage();
		$editor = $userProvider->call( $this );
		$reverter = $this->getTestSysop()->getUserIdentity();

		$this->editPage( $page, 'test', '', NS_MAIN, $editor );
		$this->doRevertPage( $page, $page->getRevisionRecord(), $reverter );

		$params = FRUserCounters::getUserParams( $editor->getId() );

		$this->assertSame(
			[
				'uniqueContentPages' => [],
				'totalContentEdits' => 0,
				'editComments' => 0,
				'revertedEdits' => 0,
			],
			$params
		);
	}

	/**
	 * @dataProvider provideAnonymousOrTemporaryUsers
	 * @covers FlaggedRevsHooks::onPageSaveComplete
	 * @covers FlaggedRevsHooks::maybeIncrementReverts
	 */
	public function testShouldNotUpdateRevertCountForAnonymousOrTemporaryUserOnRollback(
		Closure $userProvider
	): void {
		$this->overrideConfigValue( 'FlaggedRevsAutoconfirm', true );

		$page = $this->getExistingTestPage();
		$editor = $userProvider->call( $this );
		$reverter = $this->getTestSysop()->getAuthority();

		$this->editPage( $page, 'test', '', NS_MAIN, $editor );

		$rollbackStatus = $this->getServiceContainer()
			->getRollbackPageFactory()
			->newRollbackPage( $page, $reverter, $editor->getUser() )
			->rollback();

		$params = FRUserCounters::getUserParams( $editor->getId() );

		$this->assertStatusGood( $rollbackStatus );
		$this->assertSame(
			[
				'uniqueContentPages' => [],
				'totalContentEdits' => 0,
				'editComments' => 0,
				'revertedEdits' => 0,
			],
			$params
		);
	}

	public static function provideAnonymousOrTemporaryUsers(): iterable {
		// phpcs:disable Squiz.Scope.StaticThisUsage.Found
		yield 'anonymous user' => [
			function (): User {
				$this->disableAutoCreateTempUser();

				return $this->getServiceContainer()->getUserFactory()->newAnonymous( '127.0.0.1' );
			}
		];

		yield 'temporary user' => [
			function (): User {
				$this->enableAutoCreateTempUser();

				$req = new FauxRequest();

				return $this->getServiceContainer()
					->getTempUserCreator()
					->create( null, $req )
					->getUser();
			}
		];

		// phpcs:enable
	}

	/**
	 * Convenience function to revert a single edit.
	 *
	 * @param PageIdentity $page The page to perform the revert on.
	 * @param RevisionRecord $revertRev The revision to revert.
	 * @param UserIdentity $performer The user that will be performing the revert.
	 * @return void
	 */
	private function doRevertPage( PageIdentity $page, RevisionRecord $revertRev, UserIdentity $performer ): void {
		$prevRev = $this->getServiceContainer()
			->getRevisionLookup()
			->getPreviousRevision( $revertRev );

		$this->assertNotNull( $prevRev, 'Cannot revert if no previous revision exists.' );

		$pageUpdater = $this->getServiceContainer()
			->getPageUpdaterFactory()
			->newPageUpdater( $page, $performer )
			->setContent( SlotRecord::MAIN, $prevRev->getContent( SlotRecord::MAIN ) )
			->markAsRevert( EditResult::REVERT_UNDO, $revertRev->getId(), $prevRev->getId() );

		$pageUpdater->saveRevision( CommentStoreComment::newUnsavedComment( 'test' ) );

		$this->assertStatusGood( $pageUpdater->getStatus() );
	}
}
