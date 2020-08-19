<?php

use Wikimedia\TestingAccessWrapper;

/**
 * @covers \FRInclusionManager
 */
class FRInclusionManagerTest extends PHPUnit\Framework\TestCase {
	/* starting input */
	private static $inputTemplates = [
		10 	=> [ 'XX' => '1242', 'YY' => '0', 'KK' => false ],
		4 	=> [ 'Cite' => '30', 'Moo' => 0 ],
		0 	=> [ 'ZZ' => 464, '0' => 13 ]
	];
	private static $inputFiles = [
		'FileXX' => [ 'time' => '20100405192110', 'sha1' => 'abc1' ],
		'FileYY' => [ 'time' => '20000403101300', 'sha1' => 1134 ],
		'FileZZ' => [ 'time' => '0', 'sha1' => '' ],
		'Filele' => [ 'time' => 0, 'sha1' => '' ],
		'FileKK' => [ 'time' => false, 'sha1' => false ],
		'0'   	 => [ 'time' => '20000203101350', 'sha1' => 'ae33' ],
	];
	/* output to test against (<test,NS,dbkey,expected rev ID>) */
	private static $reviewedOutputTemplates = [
		[ "Output template version when given '1224'", 10, 'XX', 1242 ],
		[ "Output template version when given '0'", 10, 'YY', 0 ],
		[ "Output template version when given false", 10, 'KK', 0 ],
		[ "Output template version when given '30'", 4, 'Cite', 30 ],
		[ "Output template version when given 0", 4, 'Moo', 0 ],
		[ "Output template version when given 464", 0, 'ZZ', 464 ],
		[ "Output template version when given 13", 0, '0', 13 ],
		[ "Output template version when not given", 0, 'Notexists', null ],
	];
	private static $stableOutputTemplates = [
		[ "Output template version when given '1224'", 10, 'XX', 1242 ],
		[ "Output template version when given '0'", 10, 'YY', 0 ],
		[ "Output template version when given false", 10, 'KK', 0 ],
		[ "Output template version when given '30'", 4, 'Cite', 30 ],
		[ "Output template version when given 0", 4, 'Moo', 0 ],
		[ "Output template version when given 464", 0, 'ZZ', 464 ],
		[ "Output template version when given 13", 0, '0', 13 ],
		[ "Output template version when not given", 0, 'NotexistsPage1111', 0 ],
	];
	/* output to test against (<test,dbkey,expected TS,expected sha1>) */
	private static $reviewedOutputFiles = [
		[ "Output file version when given '20100405192110'/'abc1'",
			'FileXX', '20100405192110', 'abc1' ],
		[ "Output file version when given '20000403101300'/'ffc2'",
			'FileYY', '20000403101300', '1134' ],
		[ "Output file version when given '0'/''", 'FileZZ', '0', false ],
		[ "Output file version when given false/''", 'FileKK', '0', false ],
		[ "Output file version when given 0/''", 'Filele', '0', false ],
		[ "Output file version when not given", 'Notgiven', null, null ],
	];
	private static $stableOutputFiles = [
		[ "Output file version when given '20100405192110'/'abc1'",
			'FileXX', '20100405192110', 'abc1' ],
		[ "Output file version when given '20000403101300'/'ffc2'",
			'FileYY', '20000403101300', '1134' ],
		[ "Output file version when given '0'/''", 'FileZZ', '0', false ],
		[ "Output file version when given false/''", 'FileKK', '0', false ],
		[ "Output file version when given 0/''", 'Filele', '0', false ],
		[ "Output file version when not given", 'NotexistsPage1111', '0', false ],
	];

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() : void {
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
		$im->setReviewedVersions( self::$inputTemplates, self::$inputFiles );
		$im->clear();
		$this->assertFalse( $im->parserOutputIsStabilized(), "Empty on clear()" );
	}

	public function testReviewedTemplateVersions() {
		/** @var FRInclusionManager $im */
		$im = TestingAccessWrapper::newFromObject( FRInclusionManager::singleton() );
		$im->setReviewedVersions( self::$inputTemplates, self::$inputFiles );
		foreach ( self::$reviewedOutputTemplates as $triple ) {
			list( $test,$ns,$dbKey,$expId ) = $triple;
			$title = Title::makeTitleSafe( $ns, $dbKey );
			$actual = $im->getReviewedTemplateVersion( $title );
			$this->assertEquals( $expId, $actual, "Rev ID test: $test" );
		}
	}

	public function testReviewedFileVersions() {
		/** @var FRInclusionManager $im */
		$im = TestingAccessWrapper::newFromObject( FRInclusionManager::singleton() );
		$im->setReviewedVersions( self::$inputTemplates, self::$inputFiles );
		foreach ( self::$reviewedOutputFiles as $triple ) {
			list( $test,$dbKey,$expTS,$expSha1 ) = $triple;
			$title = Title::makeTitleSafe( NS_FILE, $dbKey );
			list( $actualTS,$actualSha1 ) = $im->getReviewedFileVersion( $title );
			$this->assertEquals( $expTS, $actualTS, "Timestamp test: $test" );
			$this->assertEquals( $expSha1, $actualSha1, "Sha1 test: $test" );
		}
	}

	public function testStableTemplateVersions() {
		/** @var FRInclusionManager $im */
		$im = TestingAccessWrapper::newFromObject( FRInclusionManager::singleton() );
		$im->setReviewedVersions( [], [] );
		$im->setStableVersionCache( self::$inputTemplates, self::$inputFiles );
		foreach ( self::$stableOutputTemplates as $triple ) {
			list( $test,$ns,$dbKey,$expId ) = $triple;
			$title = Title::makeTitleSafe( $ns, $dbKey );
			$actual = $im->getStableTemplateVersion( $title );
			$this->assertEquals( $expId, $actual, "Rev ID test: $test" );
		}
	}

	public function testStableFileVersions() {
		/** @var FRInclusionManager $im */
		$im = TestingAccessWrapper::newFromObject( FRInclusionManager::singleton() );
		$im->setReviewedVersions( [], [] );
		$im->setStableVersionCache( self::$inputTemplates, self::$inputFiles );
		foreach ( self::$stableOutputFiles as $triple ) {
			list( $test,$dbKey,$expTS,$expSha1 ) = $triple;
			$title = Title::makeTitleSafe( NS_FILE, $dbKey );
			list( $actualTS,$actualSha1 ) = $im->getStableFileVersion( $title );
			$this->assertEquals( $expTS, $actualTS, "Timestamp test: $test" );
			$this->assertEquals( $expSha1, $actualSha1, "Sha1 test: $test" );
		}
	}
}
