<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\Request\FauxRequest;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Tests\User\TempUser\TempUserTestTrait;
use MediaWiki\User\User;

/**
 * @group Database
 * @covers FlaggablePageView
 */
class FlaggablePageViewTest extends MediaWikiIntegrationTestCase {
	use TempUserTestTrait;

	private static WikiPage $testPage;
	private static RevisionRecord $unstableRev;
	private static User $tempUser;

	public function addDBDataOnce() {
		$this->overrideConfigValue( 'FlaggedRevsProtection', false );
		$this->enableAutoCreateTempUser();

		self::$testPage = $this->getExistingTestPage();
		$stableRev = self::$testPage->getRevisionRecord();

		FlaggedRevs::autoReviewEdit(
			self::$testPage,
			$this->getTestSysop()->getUser(),
			$stableRev
		);

		$pageUpdateStatus = $this->editPage( self::$testPage, 'unreviewed revision' );
		self::$unstableRev = $pageUpdateStatus->getNewRevision();

		$this->enableAutoCreateTempUser();

		$req = new FauxRequest();
		self::$tempUser = $this->getServiceContainer()
			->getTempUserCreator()
			->create( null, $req )
			->getUser();
	}

	/**
	 * @dataProvider provideStableVersionOptions
	 * @param Closure $userProvider
	 * @param array $userOptions
	 * @param string[] $queryParams
	 * @return void
	 */
	public function testShouldShowStableVersion(
		Closure $userProvider,
		array $userOptions,
		array $queryParams
	): void {
		$flaggablePageView = $this->getFlaggablePageView(
			$userProvider->call( $this ),
			$userOptions,
			$queryParams
		);

		$this->assertTrue( $flaggablePageView->showingStable() );
	}

	public static function provideStableVersionOptions(): iterable {
		// phpcs:disable Squiz.Scope.StaticThisUsage.Found
		$registeredUserProvider = fn (): User => $this->getTestUser()->getUser();

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
				return self::$tempUser;
			},
			[],
			[]
		];

		// phpcs:enable

		yield 'registered user requesting stable version via preferences' => [
			$registeredUserProvider,
			[ 'flaggedrevsstable' => FR_SHOW_STABLE_ALWAYS ],
			[]
		];

		yield 'registered user requesting stable version via query param' => [
			$registeredUserProvider,
			[ 'flaggedrevsstable' => FR_SHOW_STABLE_NEVER ],
			[ 'stable' => '1' ]
		];
	}

	/**
	 * @dataProvider provideUnstableVersionOptions
	 * @param Closure $userProvider
	 * @param array $userOptions
	 * @param Closure $queryParamProvider
	 * @return void
	 */
	public function testShouldShowUnstableVersion(
		Closure $userProvider,
		array $userOptions,
		Closure $queryParamProvider
	): void {
		$flaggablePageView = $this->getFlaggablePageView(
			$userProvider->call( $this ),
			$userOptions,
			$queryParamProvider()
		);

		$this->assertFalse( $flaggablePageView->showingStable() );
	}

	/**
	 * Convenience function to initialize a FlaggablePageView for a test page.
	 *
	 * @param User $user The user viewing the page.
	 * @param string[] $userOptions Associative array of user options to set for the user.
	 * @param string[] $queryParams Associative array of request query parameters to set.
	 *
	 * @return FlaggablePageView
	 */
	private function getFlaggablePageView(
		User $user,
		array $userOptions,
		array $queryParams
	): FlaggablePageView {
		$request = new FauxRequest( $queryParams );

		$context = new RequestContext();
		$context->setUser( $user );
		$context->setRequest( $request );

		if ( $userOptions ) {
			$userOptionsManager = $this->getServiceContainer()->getUserOptionsManager();
			foreach ( $userOptions as $option => $value ) {
				$userOptionsManager->setOption( $user, $option, $value );
			}

			$user->saveSettings();
		}

		$flaggablePageView = FlaggablePageView::newFromTitle( self::$testPage );
		$flaggablePageView->setContext( $context );

		return $flaggablePageView;
	}

	public static function provideUnstableVersionOptions(): iterable {
		// phpcs:disable Squiz.Scope.StaticThisUsage.Found
		$registeredUserProvider = fn (): User => $this->getTestUser()->getUser();
		$anonUserProvider = function (): User {
			$this->disableAutoCreateTempUser();
			return $this->getServiceContainer()->getUserFactory()->newAnonymous( '127.0.0.1' );
		};

		$tempUserProvider = function (): User {
			$this->enableAutoCreateTempUser();
			return self::$tempUser;
		};
		// phpcs:enable

		yield 'registered user requesting unstable version via preferences' => [
			$registeredUserProvider,
			[ 'flaggedrevsstable' => FR_SHOW_STABLE_NEVER ],
			static fn () => []
		];

		yield 'registered user requesting unstable version via stable param' => [
			$registeredUserProvider,
			[ 'flaggedrevsstable' => FR_SHOW_STABLE_ALWAYS ],
			static fn () => [ 'stable' => '0' ]
		];

		yield 'registered user requesting unstable version via oldid param' => [
			$registeredUserProvider,
			[ 'flaggedrevsstable' => FR_SHOW_STABLE_ALWAYS ],
			static fn () => [ 'oldid' => self::$unstableRev->getId() ]
		];

		yield 'anonymous user requesting unstable version via stable param' => [
			$anonUserProvider,
			[ 'flaggedrevsstable' => FR_SHOW_STABLE_ALWAYS ],
			static fn () => [ 'stable' => '0' ]
		];

		yield 'anonymous user requesting unstable version via oldid param' => [
			$anonUserProvider,
			[ 'flaggedrevsstable' => FR_SHOW_STABLE_ALWAYS ],
			static fn () => [ 'oldid' => self::$unstableRev->getId() ]
		];

		yield 'temporary user requesting unstable version via stable param' => [
			$tempUserProvider,
			[ 'flaggedrevsstable' => FR_SHOW_STABLE_ALWAYS ],
			static fn () => [ 'stable' => '0' ]
		];

		yield 'temporary user requesting unstable version via oldid param' => [
			$tempUserProvider,
			[ 'flaggedrevsstable' => FR_SHOW_STABLE_ALWAYS ],
			static fn () => [ 'oldid' => self::$unstableRev->getId() ]
		];
	}
}
