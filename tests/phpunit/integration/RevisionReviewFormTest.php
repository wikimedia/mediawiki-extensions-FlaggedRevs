<?php
namespace MediaWiki\Extension\FlaggedRevs\Tests\Integration;

use Closure;
use FRUserCounters;
use MediaWiki\Request\FauxRequest;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Tests\User\TempUser\TempUserTestTrait;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWikiIntegrationTestCase;
use RevisionReviewForm;

/**
 * @covers RevisionReviewForm
 * @group Database
 */
class RevisionReviewFormTest extends MediaWikiIntegrationTestCase {
	use TempUserTestTrait;

	protected function setUp(): void {
		parent::setUp();

		$this->overrideConfigValues( [
			'FlaggedRevsProtection' => false,
			'FlaggedRevsTags' => [ 'accuracy' => [ 'levels' => 3 ] ]
		] );
	}

	/**
	 * @dataProvider provideActions
	 */
	public function testShouldNotAllowActionsIfUserLacksPermission( string $action ): void {
		$page = $this->getExistingTestPage();

		$user = $this->getTestUser()->getUser();
		$title = $page->getTitle();
		$revisionId = $page->getLatest();

		$status = $this->doRevisionReview( $user, $title, $revisionId, $action );

		$this->assertSame( 'review_denied', $status );
	}

	/**
	 * @dataProvider provideActions
	 */
	public function testShouldNotAllowActionsIfPageDoesNotExist( string $action ): void {
		$page = $this->getExistingTestPage();

		$user = $this->getTestSysop()->getUser();
		$title = $page->getTitle();
		$revisionId = $page->getLatest();

		$this->deletePage( $page );

		$status = $this->doRevisionReview( $user, $title, $revisionId, $action );

		$this->assertSame( 'review_page_notexists', $status );
	}

	/**
	 * @dataProvider provideActions
	 */
	public function testShouldNotAllowActionsIfRevisionDoesNotExist( string $action ): void {
		$page = $this->getExistingTestPage();
		$otherPage = $this->getExistingTestPage();

		$user = $this->getTestSysop()->getUser();
		$revisionId = $page->getLatest();

		$this->deletePage( $page );

		$status = $this->doRevisionReview( $user, $otherPage->getTitle(), $revisionId, $action );
		$expected = $action === RevisionReviewForm::ACTION_UNAPPROVE ? 'review_not_flagged' : 'review_bad_oldid';

		$this->assertSame( $expected, $status );
	}

	/**
	 * @dataProvider provideActions
	 */
	public function testShouldNotAllowActionsIfRevisionDeleted( string $action ): void {
		$page = $this->getExistingTestPage();
		$this->editPage( $page, __METHOD__ );

		$user = $this->getTestSysop()->getUser();
		$title = $page->getTitle();
		$revisionId = $page->getRevisionRecord()->getParentId();

		$this->revisionDelete( $revisionId );

		$status = $this->doRevisionReview( $user, $title, $revisionId, $action );
		$expected = $action === RevisionReviewForm::ACTION_UNAPPROVE ? 'review_not_flagged' : 'review_bad_oldid';

		$this->assertSame( $expected, $status );
	}

	public static function provideActions(): iterable {
		foreach ( [ RevisionReviewForm::ACTION_APPROVE, RevisionReviewForm::ACTION_REJECT,
			RevisionReviewForm::ACTION_UNAPPROVE ] as $action ) {
			yield $action => [ $action ];
		}
	}

	/**
	 * @dataProvider provideUsers
	 */
	public function testShouldRejectRevision( Closure $authorProvider ): void {
		/** @var User $author */
		$author = $authorProvider->call( $this );

		$page = $this->getExistingTestPage();
		$origContent = __METHOD__ . '-original';

		$this->editPage( $page, $origContent, '', NS_MAIN, $author );
		$this->editPage( $page, __METHOD__, '', NS_MAIN, $author );

		$reviewer = $this->getTestSysop()->getUser();
		$title = $page->getTitle();
		$revRecord = $page->getRevisionRecord();

		$status = $this->doRevisionReview(
			$reviewer,
			$title,
			$revRecord->getId(),
			RevisionReviewForm::ACTION_REJECT,
			$revRecord->getParentId()
		);
		$params = FRUserCounters::getParams( $author );

		$curContent = $this->getServiceContainer()
			->getRevisionLookup()
			->getKnownCurrentRevision( $page )
			->getContent( SlotRecord::MAIN );

		$currentText = $this->getServiceContainer()
			->getContentHandlerFactory()
			->getContentHandler( CONTENT_MODEL_WIKITEXT )
			->serializeContent( $curContent );

		$this->assertSame( '1', $status );
		$this->assertSame( $origContent, $currentText );

		if ( !$author->isRegistered() ) {
			$this->assertNull( $params );
		} elseif ( $author->isTemp() ) {
			$this->assertSame( 0, $params['revertedEdits'] ?? null );
		} else {
			// NOTE: this double-counts (T377263)
			$this->assertSame( 2, $params['revertedEdits'] ?? null );
		}
	}

	public static function provideUsers(): iterable {
		// phpcs:disable Squiz.Scope.StaticThisUsage.Found
		yield 'anonymous user' => [
			function (): User {
				$this->disableAutoCreateTempUser();
				return $this->getServiceContainer()->getUserFactory()->newAnonymous( '127.0.0.1' );
			},
			[],
			[]
		];

		yield 'temporary user' => [
			function (): User {
				$this->enableAutoCreateTempUser();

				$req = new FauxRequest();
				return $this->getServiceContainer()
					->getTempUserCreator()
					->create( null, $req )
					->getUser();
			},
			[],
			[]
		];

		yield 'registered user' => [
			fn () => $this->getTestUser()->getUser(),
		];
		// phpcs:enable
	}

	/**
	 * Convenience function to perform a review action on a revision.
	 *
	 * @param User $user The user performing the review.
	 * @param Title $title The page being reviewed.
	 * @param int $revisionId The ID of the revision that is being reviewed.
	 * @param string $action The review action, one of the RevisionReviewForm::ACTION_* constants.
	 * @param int|null $refId Optional reference revision ID, used for unapproving revisions.
	 *
	 * @return string
	 */
	private function doRevisionReview(
		User $user,
		Title $title,
		int $revisionId,
		string $action,
		?int $refId = null
	): string {
		$form = new RevisionReviewForm( $user );

		$form->setTitle( $title );
		$form->setOldId( $revisionId );

		if ( $refId !== null ) {
			$form->setRefId( $refId );
		}

		$form->setAction( $action );
		$form->setTag( 1 );
		$form->bypassValidationKey();

		$form->ready();

		return $form->submit();
	}
}
