<?php

require_once 'PHPUnit\Framework\TestSuite.php';

class FlaggedRevsTestSuite extends PHPUnit_Framework_TestSuite {
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName( 'FlaggedRevs' );
		$this->addTestSuite( 'FRInclusionManagerTest' );
	}

	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self();
	}
}
