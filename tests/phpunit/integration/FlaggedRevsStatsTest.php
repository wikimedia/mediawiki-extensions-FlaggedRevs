<?php
namespace MediaWiki\Extension\FlaggedRevs\Tests\Integration;

use FlaggedRevsStats;
use MediaWiki\Request\FauxRequest;
use MediaWiki\Tests\User\TempUser\TempUserTestTrait;
use MediaWikiIntegrationTestCase;
use RevisionReviewForm;
use Wikimedia\Timestamp\ConvertibleTimestamp;
use WikiPage;

/**
 * @covers FlaggedRevsStats
 * @group Database
 */
class FlaggedRevsStatsTest extends MediaWikiIntegrationTestCase {
	use TempUserTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->overrideConfigValues( [
			'FlaggedRevsProtection' => false,
			'FlaggedRevsTags' => [ 'accuracy' => [ 'levels' => 3 ] ]
		] );
	}

	/**
	 * Setup test data for stats calculations.
	 *
	 * Note that for FlaggedRevsStats to not error, there must be reviewed revisions authored by IP users
	 * as well as registered users that started off as pending, i.e. were made to a page that already had a
	 * stable revision.
	 */
	public function addDBDataOnce() {
		$this->overrideConfigValues( [
			'FlaggedRevsProtection' => false,
			'FlaggedRevsTags' => [ 'accuracy' => [ 'levels' => 3 ] ]
		] );

		$referenceTime = wfTimestamp() - 86400;
		ConvertibleTimestamp::setFakeTime( $referenceTime );

		$ipUserPage = $this->getExistingTestPage();
		$namedUserPage = $this->getExistingTestPage();
		$tempUserPage = $this->getExistingTestPage();

		$this->enableAutoCreateTempUser();

		$req = new FauxRequest();

		$ipUser = $this->getServiceContainer()->getUserFactory()->newAnonymous( '127.0.0.1' );
		$namedUser = $this->getTestUser()->getUser();
		$tempUser = $this->getServiceContainer()
			->getTempUserCreator()
			->create( null, $req )
			->getUser();

		$this->disableAutoCreateTempUser();

		$this->editPage( $ipUserPage, __METHOD__ . '-stable', '', NS_MAIN, $ipUser );
		$this->approveRevision( $ipUserPage );

		$this->editPage( $namedUserPage, __METHOD__ . '-stable', '', NS_MAIN, $namedUser );
		$this->approveRevision( $namedUserPage );

		$this->editPage( $tempUserPage, __METHOD__ . '-stable', '', NS_MAIN, $tempUser );
		$this->approveRevision( $tempUserPage );

		// Make further edits at some point later in time after the existing revisions were marked stable.
		ConvertibleTimestamp::setFakeTime( $referenceTime + 900 );
		$this->editPage( $ipUserPage, __METHOD__, '', NS_MAIN, $ipUser );
		$this->editPage( $namedUserPage, __METHOD__, '', NS_MAIN, $namedUser );
		$this->editPage( $tempUserPage, __METHOD__, '', NS_MAIN, $tempUser );

		// Simulate varying delays in reviewing these new revisions.
		ConvertibleTimestamp::setFakeTime( $referenceTime + 1800 );
		$this->approveRevision( $ipUserPage );
		ConvertibleTimestamp::setFakeTime( $referenceTime + 2700 );
		$this->approveRevision( $namedUserPage );
		ConvertibleTimestamp::setFakeTime( $referenceTime + 3600 );
		$this->approveRevision( $tempUserPage );
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

	public function testShouldComputeStatisticsWhenTempUsersDisabled(): void {
		$this->disableAutoCreateTempUser();

		FlaggedRevsStats::updateCache();

		$stats = FlaggedRevsStats::getStats();

		$this->assertSame( 1, $stats['reviewLag-anon-sampleSize'] );
		$this->assertSame( 2, $stats['reviewLag-user-sampleSize'] );

		$this->assertSame( 900, $stats['reviewLag-anon-median'] );
		$this->assertSame( 2700, $stats['reviewLag-user-median'] );

		$this->assertSame( 900, $stats['reviewLag-anon-average'] );
		$this->assertSame( 2250, $stats['reviewLag-user-average'] );
	}

	public function testShouldComputeStatisticsWhenTempUsersEnabled(): void {
		$this->enableAutoCreateTempUser();

		FlaggedRevsStats::updateCache();

		$stats = FlaggedRevsStats::getStats();

		$this->assertSame( 2, $stats['reviewLag-anon-sampleSize'] );
		$this->assertSame( 1, $stats['reviewLag-user-sampleSize'] );

		$this->assertSame( 2700, $stats['reviewLag-anon-median'] );
		$this->assertSame( 1800, $stats['reviewLag-user-median'] );

		$this->assertSame( 1800, $stats['reviewLag-anon-average'] );
		$this->assertSame( 1800, $stats['reviewLag-user-average'] );
	}
}
