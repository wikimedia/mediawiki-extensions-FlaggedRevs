<?php

require_once 'PHPUnit\Framework\TestCase.php';

define( 'MEDIAWIKI', 1 ); // hack
if ( getenv( 'MW_INSTALL_PATH' ) ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = dirname(__FILE__).'/../../../..';
}
require_once "$IP/includes/defines.php"; // constants (NS_FILE ect...)
require_once dirname(__FILE__).'/../../FRInclusionManager.php';

##### Fake classes for dependencies ####
class Title {
	protected $ns;
	protected $dbKey;
	public static function makeTitleSafe( $ns, $dbKey ) {
		$t = new self();
		$t->ns = (int)$ns;
		$t->dbKey = (string)$dbKey;
		return $t;
	}
	public function getNamespace() {
		return $this->ns;
	}
	public function getDBKey() {
		return $this->dbKey;
	}
}
class FlaggedRevision {
	public static function newFromStable() {
		return null; // not in DB
	}
}
class MWException extends Exception { } // just in case
##### End fake classes #####

class FRInclusionManagerTest extends PHPUnit_Framework_TestCase {
	/* starting input */
	protected static $inputTemplates = array(
		10 	=> array('XX' => '1242', 'YY' => '0'),
		4 	=> array('cite' => '30', 'moo' => 0),
		0 	=> array('ZZ' => 464, '0' => 13)
	);
	protected static $inputFiles = array(
		'fXX' => array('ts' => '20100405192110', 'sha1' => 'abc1'),
		'fYY' => array('ts' => '20000403101300', 'sha1' => 'ffc2'),
		'fZZ' => array('ts' => '0', 'sha1' => ''),
		'fle' => array('ts' => 0, 'sha1' => ''),
		'0'   => array('ts' => '20000203101350', 'sha1' => 'ae33'),
	);
	/* output to test against (test# => <NS,dbkey,expected rev ID>) */
	protected static $reviewedOutputTemplates = array(
		0 => array( 10, 'XX', 1242 ),
		1 => array( 10, 'YY', 0 ),
		2 => array( 4, 'cite', 30 ),
		3 => array( 4, 'moo', 0 ),
		4 => array( 0, 'ZZ', 464 ),
		5 => array( 0, 'notexists', null ),
		6 => array( 0, '0', 13 ),
	);
	protected static $stableOutputTemplates = array(
		0 => array( 10, 'XX', 1242 ),
		1 => array( 10, 'YY', 0 ),
		2 => array( 4, 'cite', 30 ),
		3 => array( 4, 'moo', 0 ),
		4 => array( 0, 'ZZ', 464 ),
		5 => array( 0, 'notexists', 0 ),
		6 => array( 0, '0', 13 ),
	);
	/* output to test against (test# => <dbkey,expected TS,expected sha1>) */
	protected static $reviewedOutputFiles = array(
		0 => array( 'fXX', '20100405192110', 'abc1'),
		1 => array( 'fYY', '20000403101300', 'ffc2'),
		2 => array( 'fZZ', '0', ''),
		3 => array( 'fle', '0', ''),
		4 => array( 'notgiven', null, null),
	);
	protected static $stableOutputFiles = array(
		0 => array( 'fXX', '20100405192110', 'abc1'),
		1 => array( 'fYY', '20000403101300', 'ffc2'),
		2 => array( 'fZZ', '0', ''),
		3 => array( 'fle', '0', ''),
		4 => array( 'notgiven', '0', ''),
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
