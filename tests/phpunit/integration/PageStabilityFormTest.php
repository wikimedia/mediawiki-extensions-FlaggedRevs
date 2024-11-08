<?php
namespace MediaWiki\Extension\FlaggedRevs\Tests\Integration;

use FRPageConfig;
use MediaWiki\MainConfigNames;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MediaWikiIntegrationTestCase;
use PageStabilityProtectForm;
use Wikimedia\Timestamp\ConvertibleTimestamp;

/**
 * @covers PageStabilityForm
 * @covers PageStabilityProtectForm
 * @group Database
 */
class PageStabilityFormTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();

		$this->overrideConfigValue( 'FlaggedRevsProtection', true );
		$this->overrideConfigValue( 'FlaggedRevsRestrictionLevels', [ 'autoconfirmed' ] );
	}

	public function testShouldNotAllowStabilitySettingsChangeIfTitleInvalid() {
		$page = $this->getExistingTestPage();
		$user = $this->getTestSysop()->getUser();
		$title = $page->getTitle();

		$status = $this->doChangeStabilitySettings( $user, $title, 'invalid', 'test', '1 year' );

		$this->assertSame( 'stabilize_invalid_level', $status );
	}

	public function testShouldNotAllowStabilitySettingsChangeIfUserLacksPermission() {
		$page = $this->getExistingTestPage();
		$user = $this->getTestUser()->getUser();
		$title = $page->getTitle();

		$status = $this->doChangeStabilitySettings(
			$user, $title, 'autoconfirmed', 'test', '1 year'
		);

		$this->assertSame( 'stabilize_denied', $status );
	}

	public function testShouldNotAllowStabilitySettingsChangeIfPageDoesNotExist() {
		$page = $this->getExistingTestPage();
		$user = $this->getTestSysop()->getUser();
		$title = $page->getTitle();

		$this->deletePage( $page );

		$status = $this->doChangeStabilitySettings( $user, $title, 'autoconfirmed', 'test', '1 year' );

		$this->assertSame( 'stabilize_page_notexists', $status );
	}

	public function testShouldNotAllowStabilitySettingsChangeIfPageNotInReviewNamespace() {
		$this->overrideConfigValue( 'FlaggedRevsNamespaces', [ 0 ] );

		$page = $this->getExistingTestPage( Title::newFromText( 'Test', NS_TALK ) );
		$user = $this->getTestSysop()->getUser();
		$title = $page->getTitle();

		$status = $this->doChangeStabilitySettings( $user, $title, 'autoconfirmed', 'test', '1 year' );

		$this->assertSame( 'stabilize_page_unreviewable', $status );
	}

	public function testShouldNotAllowStabilitySettingsChangeIfExpiryInvalid() {
		$page = $this->getExistingTestPage();
		$user = $this->getTestSysop()->getUser();
		$title = $page->getTitle();

		$status = $this->doChangeStabilitySettings( $user, $title, 'autoconfirmed', 'test', 'invalidexpiry' );

		$this->assertSame( 'stabilize_expiry_invalid', $status );
	}

	public function testShouldNotAllowStabilitySettingsChangeIfExpiryBeforeCurrentTime() {
		ConvertibleTimestamp::setFakeTime( '20230405060708' );
		$page = $this->getExistingTestPage();
		$user = $this->getTestSysop()->getUser();
		$title = $page->getTitle();

		$status = $this->doChangeStabilitySettings( $user, $title, 'autoconfirmed', 'test', '5 May 2022' );

		$this->assertSame( 'stabilize_expiry_old', $status );
	}

	public function testStabilitySettingsChange() {
		$this->overrideConfigValue( MainConfigNames::LanguageCode, 'qqx' );
		ConvertibleTimestamp::setFakeTime( '20230405060708' );
		$page = $this->getExistingTestPage();
		$user = $this->getTestSysop()->getUser();
		$title = $page->getTitle();

		$stabilitySettingsChangedHookCalled = false;
		$this->setTemporaryHook(
			'FlaggedRevsStabilitySettingsChanged',
			function ( Title $actualTitle, $newConfig, UserIdentity $userIdentity, $reason ) use (
				&$stabilitySettingsChangedHookCalled, $title, $user
			) {
				$this->assertTrue( $title->equals( $actualTitle ) );
				$this->assertTrue( $user->equals( $userIdentity ) );
				$this->assertSame( 'Setting stabilisation settings to be autoconfirmed', $reason );
				$this->assertArrayEquals(
					[ 'override' => 1, 'autoreview' => 'autoconfirmed', 'expiry' => '20240405060708' ],
					$newConfig,
					false,
					true
				);
				$stabilitySettingsChangedHookCalled = true;
			}
		);

		$status = $this->doChangeStabilitySettings(
			$user, $title, 'autoconfirmed', 'Setting stabilisation settings to be autoconfirmed', '1 year'
		);

		$this->assertTrue( $status );
		$this->assertTrue( $stabilitySettingsChangedHookCalled );

		// Check that a revision has been added to indicate the change in stability settings.
		$page->clear();
		$latestRevisionRecord = $page->getRevisionRecord();
		$this->assertTrue( $user->equals( $latestRevisionRecord->getUser() ) );
		$revisionCommentText = $latestRevisionRecord->getComment()->text;
		$this->assertStringContainsString( 'stable-logentry-config', $revisionCommentText );
		$this->assertStringContainsString( 'Setting stabilisation settings to be autoconfirmed', $revisionCommentText );

		// Check that the FlaggedRevs DB has the expected stability settings
		$this->assertArrayEquals(
			[ 'override' => 1, 'autoreview' => 'autoconfirmed', 'expiry' => '20240405060708' ],
			FRPageConfig::getStabilitySettings( $title ),
			false,
			true
		);
	}

	public function testStabilitySettingsChangeAndThenRevert() {
		$this->overrideConfigValue( MainConfigNames::LanguageCode, 'qqx' );
		ConvertibleTimestamp::setFakeTime( '20230405060708' );
		$page = $this->getExistingTestPage();
		$user = $this->getTestSysop()->getUser();
		$title = $page->getTitle();

		$firstStatus = $this->doChangeStabilitySettings(
			$user, $title, 'autoconfirmed', 'Setting stabilisation settings to be autoconfirmed', '1 year'
		);
		$this->assertTrue( $firstStatus );
		$secondStatus = $this->doChangeStabilitySettings(
			$user, $title, '', 'Undoing stabilisation settings change', 'infinity'
		);
		$this->assertTrue( $secondStatus );

		// Check that a revision has been added to indicate the change in stability settings.
		$page->clear();
		$latestRevisionRecord = $page->getRevisionRecord();
		$this->assertTrue( $user->equals( $latestRevisionRecord->getUser() ) );
		$revisionCommentText = $latestRevisionRecord->getComment()->text;
		$this->assertStringContainsString( 'stable-logentry-reset', $revisionCommentText );
		$this->assertStringContainsString( 'Undoing stabilisation settings change', $revisionCommentText );

		// Check that the FlaggedRevs DB has the expected stability settings
		$this->assertArrayEquals(
			[ 'override' => 0, 'autoreview' => '', 'expiry' => 'infinity' ],
			FRPageConfig::getStabilitySettings( $title ),
			false,
			true
		);
	}

	/**
	 * @param User $user
	 * @param Title $title
	 * @param string $permission
	 * @param string $reason
	 * @param string $expiry
	 * @return string|true
	 */
	private function doChangeStabilitySettings(
		User $user, Title $title, string $permission, string $reason, string $expiry
	) {
		$form = new PageStabilityProtectForm( $user );

		$form->setTitle( $title );
		$form->setAutoreview( $permission );
		$form->setReasonExtra( $reason );
		$form->setReasonSelection( 'other' );
		$form->setExpiryCustom( $expiry );

		$form->ready();

		return $form->submit();
	}
}
