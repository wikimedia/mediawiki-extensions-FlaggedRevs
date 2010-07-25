<?php

require_once 'PHPUnit\Framework\TestCase.php';

class FRInclusionManagerTest extends PHPUnit_Framework_TestCase {
	/* starting input */
	protected static $inputTemplates = array(
		10 	=> array('XX' => '1242', 'YY' => '0'),
		4 	=> array('Cite' => '30', 'Moo' => 0),
		0 	=> array('ZZ' => 464, '0' => 13)
	);
	protected static $inputFiles = array(
		'FileXX' => array('ts' => '20100405192110', 'sha1' => 'abc1'),
		'FileYY' => array('ts' => '20000403101300', 'sha1' => 'ffc2'),
		'FileZZ' => array('ts' => '0', 'sha1' => ''),
		'Filele' => array('ts' => 0, 'sha1' => ''),
		'0'   	 => array('ts' => '20000203101350', 'sha1' => 'ae33'),
	);
	/* output to test against (test# => <NS,dbkey,expected rev ID>) */
	protected static $reviewedOutputTemplates = array(
		0 => array( 10, 'XX', 1242 ),
		1 => array( 10, 'YY', 0 ),
		2 => array( 4, 'Cite', 30 ),
		3 => array( 4, 'Moo', 0 ),
		4 => array( 0, 'ZZ', 464 ),
		5 => array( 0, 'Notexists', null ),
		6 => array( 0, '0', 13 ),
	);
	protected static $stableOutputTemplates = array(
		0 => array( 10, 'XX', 1242 ),
		1 => array( 10, 'YY', 0 ),
		2 => array( 4, 'Cite', 30 ),
		3 => array( 4, 'Moo', 0 ),
		4 => array( 0, 'ZZ', 464 ),
		5 => array( 0, 'NotexistsPage1111', 0 ),
		6 => array( 0, '0', 13 ),
	);
	/* output to test against (test# => <dbkey,expected TS,expected sha1>) */
	protected static $reviewedOutputFiles = array(
		0 => array( 'FileXX', '20100405192110', 'abc1'),
		1 => array( 'FileYY', '20000403101300', 'ffc2'),
		2 => array( 'FileZZ', '0', ''),
		3 => array( 'Filele', '0', ''),
		4 => array( 'Notgiven', null, null),
	);
	protected static $stableOutputFiles = array(
		0 => array( 'FileXX', '20100405192110', 'abc1'),
		1 => array( 'FileYY', '20000403101300', 'ffc2'),
		2 => array( 'FileZZ', '0', ''),
		3 => array( 'Filele', '0', ''),
		4 => array( 'NotexistsPage1111', '0', ''),
	);

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		parent::tearDown();
		FRInclusionManager::singleton()->clear();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct() {}

	public function testManagerInitial() {
		$im = FRInclusionManager::singleton();
		$this->assertEquals( false, $im->parserOutputIsStabilized(), "Starts off empty" );
	}

	public function testManagerClear() {
		$im = FRInclusionManager::singleton();
		$im->setReviewedVersions( self::$inputTemplates, self::$inputFiles );
		$im->clear();
		$this->assertEquals( false, $im->parserOutputIsStabilized(), "Empty on clear()" );
	}

	public function testReviewedTemplateVersions() {
		$im = FRInclusionManager::singleton();
		$im->setReviewedVersions( self::$inputTemplates, self::$inputFiles );
		foreach ( self::$reviewedOutputTemplates as $x => $triple ) {
			list($ns,$dbKey,$expId) = $triple;
			$title = Title::makeTitleSafe( $ns, $dbKey );
			$actual = $im->getReviewedTemplateVersion( $title );
			$this->assertEquals( $expId, $actual, "Rev ID test case $x" );
		}
	}

	public function testReviewedFileVersions() {
		$im = FRInclusionManager::singleton();
		$im->setReviewedVersions( self::$inputTemplates, self::$inputFiles );
		foreach ( self::$reviewedOutputFiles as $x => $triple ) {
			list($dbKey,$expTS,$expSha1) = $triple;
			$title = Title::makeTitleSafe( NS_FILE, $dbKey );
			list($actualTS,$actualSha1) = $im->getReviewedFileVersion( $title );
			$this->assertEquals( $expTS, $actualTS, "Timestamp test case $x" );
			$this->assertEquals( $expSha1, $actualSha1, "Sha1 test case $x" );
		}
	}

	public function testStableTemplateVersions() {
		$im = FRInclusionManager::singleton();
		$im->setReviewedVersions( array(), array() );
		$im->setStableVersionCache( self::$inputTemplates, self::$inputFiles );
		foreach ( self::$stableOutputTemplates as $x => $triple ) {
			list($ns,$dbKey,$expId) = $triple;
			$title = Title::makeTitleSafe( $ns, $dbKey );
			$actual = $im->getStableTemplateVersion( $title );
			$this->assertEquals( $expId, $actual, "Rev ID test case $x" );
		}
	}

	public function testStableFileVersions() {
		$im = FRInclusionManager::singleton();
		$im->setReviewedVersions( array(), array() );
		$im->setStableVersionCache( self::$inputTemplates, self::$inputFiles );
		foreach ( self::$stableOutputFiles as $x => $triple ) {
			list($dbKey,$expTS,$expSha1) = $triple;
			$title = Title::makeTitleSafe( NS_FILE, $dbKey );
			list($actualTS,$actualSha1) = $im->getStableFileVersion( $title );
			$this->assertEquals( $expTS, $actualTS, "Timestamp test case $x" );
			$this->assertEquals( $expSha1, $actualSha1, "Sha1 test case $x" );
		}
	}
}
