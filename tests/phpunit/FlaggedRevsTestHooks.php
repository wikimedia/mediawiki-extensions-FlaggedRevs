<?php

/**
 * Class containing test related event-handlers for FlaggedRevs
 */
class FlaggedRevsTestHooks {
	public static function onParserTestTables( array &$tables ) {
		$tables[] = 'flaggedpages';
		$tables[] = 'flaggedrevs';
		$tables[] = 'flaggedpage_pending';
		$tables[] = 'flaggedpage_config';
		$tables[] = 'flaggedtemplates';
		$tables[] = 'flaggedimages';
		$tables[] = 'flaggedrevs_promote';
		$tables[] = 'flaggedrevs_tracking';
		return true;
	}
}
