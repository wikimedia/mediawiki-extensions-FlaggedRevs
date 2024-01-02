<?php

use MediaWiki\Title\Title;
use Wikimedia\TestingAccessWrapper;

/**
 * @group Database
 * @covers \FRInclusionManager
 */
class FRInclusionManagerTest extends MediaWikiIntegrationTestCase {

	/* starting input */
	private const INPUT_TEMPLATES = [
		10 	=> [ 'XX' => '1242', 'YY' => '0', 'KK' => false ],
		4 	=> [ 'Cite' => '30', 'Moo' => 0 ],
		0 	=> [ 'ZZ' => 464, '0' => 13 ]
	];
	/* output to test against (<test,NS,dbkey,expected rev ID>) */
	private const REVIEWED_OUTPUT_TEMPLATES = [
		[ "Output template version when given '1224'", 10, 'XX', 1242 ],
		[ "Output template version when given '0'", 10, 'YY', 0 ],
		[ "Output template version when given false", 10, 'KK', 0 ],
		[ "Output template version when given '30'", 4, 'Cite', 30 ],
		[ "Output template version when given 0", 4, 'Moo', 0 ],
		[ "Output template version when given 464", 0, 'ZZ', 464 ],
		[ "Output template version when given 13", 0, '0', 13 ],
		[ "Output template version when not given", 0, 'Notexists', null ],
	];
	private const STABLE_OUTPUT_TEMPLATES = [
		[ "Output template version when given '1224'", 10, 'XX', 1242 ],
		[ "Output template version when given '0'", 10, 'YY', 0 ],
		[ "Output template version when given false", 10, 'KK', 0 ],
		[ "Output template version when given '30'", 4, 'Cite', 30 ],
		[ "Output template version when given 0", 4, 'Moo', 0 ],
		[ "Output template version when given 464", 0, 'ZZ', 464 ],
		[ "Output template version when given 13", 0, '0', 13 ],
		[ "Output template version when not given", 0, 'NotexistsPage1111', 0 ],
	];

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown(): void {
		parent::tearDown();
		FRInclusionManager::singleton()->clear();
	}

	public function testManagerInitial() {
		$im = FRInclusionManager::singleton();
		$this->assertFalse( $im->parserOutputIsStabilized(), "Starts off empty" );
	}

	public function testManagerClear() {
		/** @var FRInclusionManager $im */
		$im = TestingAccessWrapper::newFromObject( FRInclusionManager::singleton() );
		$im->setReviewedVersions( self::INPUT_TEMPLATES );
		$im->clear();
		$this->assertFalse( $im->parserOutputIsStabilized(), "Empty on clear()" );
	}

	public function testReviewedTemplateVersions() {
		/** @var FRInclusionManager $im */
		$im = TestingAccessWrapper::newFromObject( FRInclusionManager::singleton() );
		$im->setReviewedVersions( self::INPUT_TEMPLATES );
		foreach ( self::REVIEWED_OUTPUT_TEMPLATES as $triple ) {
			[ $test, $ns, $dbKey, $expId ] = $triple;
			$title = Title::makeTitleSafe( $ns, $dbKey );
			$actual = $im->getReviewedTemplateVersion( $title );
			$this->assertEquals( $expId, $actual, "Rev ID test: $test" );
		}
	}

	public function testStableTemplateVersions() {
		/** @var FRInclusionManager $im */
		$im = TestingAccessWrapper::newFromObject( FRInclusionManager::singleton() );
		$im->setReviewedVersions( [] );
		$im->setStableVersionCache( self::INPUT_TEMPLATES );
		foreach ( self::STABLE_OUTPUT_TEMPLATES as $triple ) {
			[ $test, $ns, $dbKey, $expId ] = $triple;
			$title = Title::makeTitleSafe( $ns, $dbKey );
			$actual = $im->getStableTemplateVersion( $title );
			$this->assertEquals( $expId, $actual, "Rev ID test: $test" );
		}
	}

}
