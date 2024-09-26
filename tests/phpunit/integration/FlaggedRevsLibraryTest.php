<?php

use MediaWiki\Extension\Scribunto\Tests\Engines\LuaCommon\LuaEngineTestBase;
use MediaWiki\Title\Title;

if ( !class_exists( LuaEngineTestBase::class ) ) {
	class_alias( MediaWikiIntegrationTestCase::class, LuaEngineTestBase::class );
}

/**
 * @covers \FlaggedRevsScribuntoLuaLibrary
 */
class FlaggedRevsLibraryTest extends LuaEngineTestBase {

	/** @inheritDoc */
	protected static $moduleName = 'FlaggedRevsLibraryTest';

	protected function setUp(): void {
		parent::setUp();

		$this->markTestSkippedIfExtensionNotLoaded( 'Scribunto' );

		$class = new ReflectionClass( FlaggableWikiPage::class );
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

	/** @inheritDoc */
	public function getTestModules() {
		 return parent::getTestModules() + [
			 'FlaggedRevsLibraryTest' => __DIR__ . '/FlaggedRevsLibraryTests.lua'
		 ];
	}
}
