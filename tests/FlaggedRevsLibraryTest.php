<?php
class FlaggedRevsLibraryTest extends Scribunto_LuaEngineTestBase {
	protected static $moduleName = 'FlaggedRevsLibraryTest';

	function setUp() {
		parent::setUp();

		$class = new ReflectionClass( 'FlaggableWikiPage' );
		$pageConfig = $class->getProperty( 'pageConfig' );
		$pageConfig->setAccessible( true );
		$mDataLoaded = $class->getProperty( 'mDataLoaded' );
		$mDataLoaded->setAccessible( true );

		$title = Title::newFromText( 'Page without FR' );
		$article = FlaggableWikiPage::getTitleInstance( $title );
		$pageConfig->setValue( $article, [
			'override'   => 0,
			'autoreview' => '',
			'expiry'     => 'infinity'
		] );
		$mDataLoaded->setValue( $article, true );

		$title = Title::newFromText( 'Page with FR' );
		$article = FlaggableWikiPage::getTitleInstance( $title );
		$pageConfig->setValue( $article, [
			'override'   => 1,
			'autoreview' => 'autoconfirmed',
			'expiry'     => '20370101000000'
		] );
		$mDataLoaded->setValue( $article, true );
	}

	function getTestModules() {
		 return parent::getTestModules() + [
			 'FlaggedRevsLibraryTest' => __DIR__ . '/FlaggedRevsLibraryTests.lua'
		 ];
	}
}
