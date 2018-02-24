<?php
/**
 * Class containing test related event-handlers for FlaggedRevs
 */
class FlaggedRevsTestHooks {
	public static function getUnitTests( &$files ) {
		$files[] = __DIR__ . '/FRInclusionManagerTest.php';
		$files[] = __DIR__ . '/FRUserCountersTest.php';
		$files[] = __DIR__ . '/FRUserActivityTest.php';
		$files[] = __DIR__ . '/FRParserCacheStableTest.php';
		$files[] = __DIR__ . '/FlaggablePageTest.php';
		$files[] = __DIR__ . '/FlaggedRevsSetupTest.php';
		if ( ExtensionRegistry::getInstance()->isLoaded( 'Scribunto' ) ) {
			$files[] = __DIR__ . '/FlaggedRevsLibraryTest.php';
		}
		return true;
	}

	public static function onParserTestTables( array &$tables ) {
		$tables[] = 'flaggedpages';
		$tables[] = 'flaggedrevs';
		$tables[] = 'flaggedpage_pending';
		$tables[] = 'flaggedpage_config';
		$tables[] = 'flaggedtemplates';
		$tables[] = 'flaggedimages';
		$tables[] = 'flaggedrevs_promote';
		$tables[] = 'flaggedrevs_tracking';
		$tables[] = 'valid_tag'; // we need this core table
		return true;
	}
}
