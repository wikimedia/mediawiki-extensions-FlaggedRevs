<?php
namespace MediaWiki\Extension\FlaggedRevs\Tests\Integration;

use Closure;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Tests\Api\ApiTestCase;
use MediaWiki\Tests\User\TempUser\TempUserTestTrait;
use RevisionReviewForm;
use Wikimedia\Timestamp\ConvertibleTimestamp;
use WikiPage;

/**
 * @covers ApiQueryOldreviewedpages
 * @group Database
 */
class ApiQueryOldreviewedpagesTest extends ApiTestCase {
	use TempUserTestTrait;

	private static Authority $user;

	private static PageIdentity $watchedPage;
	private static PageIdentity $unwatchedPage;

	public function addDBDataOnce() {
		$this->overrideConfigValues( [
			'FlaggedRevsProtection' => false,
			'FlaggedRevsTags' => [ 'accuracy' => [ 'levels' => 3 ] ]
		] );

		$referenceTime = wfTimestamp() - 86400;
		ConvertibleTimestamp::setFakeTime( $referenceTime );

		$watchedPage = $this->getExistingTestPage();
		$unwatchedPage = $this->getExistingTestPage();

		$this->editPage( $watchedPage, __METHOD__ );
		$this->editPage( $unwatchedPage, __METHOD__ );

		$this->approveRevision( $watchedPage );
		$this->approveRevision( $unwatchedPage );

		ConvertibleTimestamp::setFakeTime( $referenceTime + 1800 );

		$this->editPage( $watchedPage, __METHOD__ . '-unreviewed' );
		$this->editPage( $unwatchedPage, __METHOD__ . '-unreviewed' );

		self::$user = $this->getTestUser()->getUser();

		$this->getServiceContainer()
			->getWatchedItemStore()
			->addWatch( self::$user, $watchedPage );

		self::$watchedPage = $watchedPage;
		self::$unwatchedPage = $unwatchedPage;

		ConvertibleTimestamp::setFakeTime( false );
	}

	/**
	 * Convenience function to approve the latest revision of the given page.
	 * @param WikiPage $wikiPage
	 * @return void
	 */
	private function approveRevision( WikiPage $wikiPage ): void {
		$form = new RevisionReviewForm( $this->getTestSysop()->getUser() );
		$form->setTitle( $wikiPage->getTitle() );
		$form->setOldId( $wikiPage->getLatest() );
		$form->setAction( RevisionReviewForm::ACTION_APPROVE );
		$form->setTag( 1 );
		$form->bypassValidationKey();
		$form->ready();
		$this->assertTrue( $form->submit() );
	}

	public function testShouldRejectIPUserWhenRequestingWatchedPages(): void {
		$this->expectApiErrorCode( 'notloggedin' );

		$performer = $this->getServiceContainer()->getUserFactory()->newAnonymous( '127.0.0.1' );
		$this->doApiRequest( [
			'action' => 'query',
			'list' => 'oldreviewedpages',
			'orfilterwatched' => 'watched'
		], null, false, $performer );
	}

	public function testShouldReturnWatchedPagesForRegisteredUserWhenRequestingWatchedPages(): void {
		[ $res ] = $this->doApiRequest( [
			'action' => 'query',
			'list' => 'oldreviewedpages',
			'orfilterwatched' => 'watched'
		], null, false, self::$user );

		$pageIds = array_map( fn ( array $page ) => $page['pageid'], $res['query']['oldreviewedpages'] );

		$this->assertSame(
			[ self::$watchedPage->getId() ],
			$pageIds
		);
	}

	/**
	 * @dataProvider provideUsers
	 */
	public function testShouldReturnAllPages( Closure $userProvider ): void {
		[ $res ] = $this->doApiRequest( [
			'action' => 'query',
			'list' => 'oldreviewedpages',
		], null, false, $userProvider->call( $this ) );

		$pageIds = array_map( fn ( array $page ) => $page['pageid'], $res['query']['oldreviewedpages'] );
		sort( $pageIds );

		$this->assertSame(
			[ self::$watchedPage->getId(), self::$unwatchedPage->getId() ],
			$pageIds
		);
	}

	public static function provideUsers(): iterable {
		// phpcs:disable Squiz.Scope.StaticThisUsage.Found
		yield 'registered user' => [
			fn () => self::$user,
		];

		yield 'IP user' => [
			fn () => $this->getServiceContainer()->getUserFactory()->newAnonymous( '127.0.0.1' )
		];
		// phpcs:enable
	}
}
