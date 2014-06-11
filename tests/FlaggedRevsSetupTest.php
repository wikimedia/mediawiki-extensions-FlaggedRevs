<?php

class FlaggedRevsSetupTest extends PHPUnit_Framework_TestCase {
	/**
	 * Constructs the test case.
	 */
	public function __construct() {}

	public function testDefineSourcePaths() {
		$autoloadClasses = $messagesFiles = $aliasesFiles = $messagesDirs = array();
		FlaggedRevsSetup::defineSourcePaths( $autoloadClasses, $messagesFiles, $messagesDirs );
		$fileLists = array(
			'$autoloadClasses' => $autoloadClasses,
			'$messageFiles'    => $messagesFiles,
			'$messagesDirs'    => $messagesDirs,
		);
		foreach ( $fileLists as $listName => $list ) {
			$this->assertNotEmpty( $list, "$listName variable is not empty" );
			foreach ( $list as $name => $file ) {
				$this->assertEquals( true, file_exists( $file ), "$file exists" );
			}
		}
	}
}
