<?php
namespace MediaWiki\Extension\FlaggedRevs\Tests\Integration;

use Closure;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Request\FauxRequest;
use MediaWiki\Tests\Api\ApiTestCase;
use MediaWiki\User\User;
use RevisionReviewForm;
use Wikimedia\Timestamp\ConvertibleTimestamp;
use WikiPage;

/**
 * @covers ApiQueryOldreviewedpages
 * @group Database
 */
class ApiQueryOldreviewedpagesTest extends ApiTestCase {
	private static User $user;
	private static User $tempUser;

	private static PageIdentity $watchedPage;
	private static PageIdentity $unwatchedPage;

	protected function setUp(): void {
		parent::setUp();

		// Avoid holding onto stale service references
		self::$user->clearInstanceCache();
		self::$tempUser->clearInstanceCache();
	}

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

		$req = new FauxRequest();

		self::$user = $this->getTestUser()->getUser();
		self::$tempUser = $this->getServiceContainer()
			->getTempUserCreator()
			->create( null, $req )
			->getUser();

		$this->getServiceContainer()
			->getWatchedItemStore()
			->addWatch( self::$user, $watchedPage );

		$this->getServiceContainer()
			->getWatchedItemStore()
			->addWatch( self::$tempUser, $watchedPage );

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

	/**
	 * @dataProvider provideUsersWithoutWatchlistAccess
	 */
	public function testShouldRejectUserWhenRequestingWatchedPages(
		Closure $userProvider,
		string $expectedCode
	): void {
		$this->expectApiErrorCode( $expectedCode );
		$performer = $userProvider->call( $this );

		$this->doApiRequest( [
			'action' => 'query',
			'list' => 'oldreviewedpages',
			'orfilterwatched' => 'watched'
		], null, false, $performer );
	}

	public static function provideUsersWithoutWatchlistAccess(): iterable {
		// phpcs:disable Squiz.Scope.StaticThisUsage.Found
		yield 'IP user' => [
			fn () => $this->getServiceContainer()->getUserFactory()->newAnonymous( '127.0.0.1' ),
			'notloggedin'
		];
		yield 'temporary account without "viewmywatchlist"' => [
			function (): User {
				$this->setGroupPermissions( [
					'*' => [ 'viewmywatchlist' => false ],
					'user' => [ 'viewmywatchlist' => true ]
				] );

				return self::$tempUser;
			},
			'notloggedin'
		];
		yield 'named user without "viewmywatchlist"' => [
			function (): User {
				$this->setGroupPermissions( [
					'*' => [ 'viewmywatchlist' => false ],
					'user' => [ 'viewmywatchlist' => false ]
				] );

				return self::$user;
			},
			'permissiondenied'
		];
		// phpcs:enable
	}

	/**
	 * @dataProvider provideUsersWithWatchlistAccess
	 */
	public function testShouldReturnWatchedPagesForRegisteredUserWhenRequestingWatchedPages(
		Closure $userProvider
	): void {
		$this->setGroupPermissions( [
			'*' => [ 'viewmywatchlist' => true ],
			'user' => [ 'viewmywatchlist' => true ]
		] );

		[ $res ] = $this->doApiRequest( [
			'action' => 'query',
			'list' => 'oldreviewedpages',
			'orfilterwatched' => 'watched'
		], null, false, $userProvider->call( $this ) );

		$pageIds = array_map( fn ( array $page ) => $page['pageid'], $res['query']['oldreviewedpages'] );
		sort( $pageIds );

		$this->assertSame(
			[ self::$watchedPage->getId() ],
			$pageIds
		);
	}

	public static function provideUsersWithWatchlistAccess(): iterable {
		// phpcs:disable Squiz.Scope.StaticThisUsage.Found
		yield 'named user' => [
			fn () => self::$user,
		];

		yield 'temporary account' => [
			fn () => self::$tempUser,
		];
		// phpcs:enable
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
		yield 'named user' => [
			fn () => self::$user,
		];

		yield 'IP user' => [
			fn () => $this->getServiceContainer()->getUserFactory()->newAnonymous( '127.0.0.1' )
		];

		yield 'temporary account' => [
			fn () => self::$tempUser,
		];
		// phpcs:enable
	}
}
