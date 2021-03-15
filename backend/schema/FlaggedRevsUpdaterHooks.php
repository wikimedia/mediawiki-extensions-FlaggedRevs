<?php

/**
 * Class containing updater functions for a FlaggedRevs environment
 */
class FlaggedRevsUpdaterHooks {
	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/LoadExtensionSchemaUpdates
	 *
	 * @param DatabaseUpdater $du
	 */
	public static function addSchemaUpdates( DatabaseUpdater $du ) {
		global $wgDBtype;
		if ( $wgDBtype == 'mysql' ) {
			$base = __DIR__ . '/mysql';
			// Initial install tables (current schema)
			$du->addExtensionTable( 'flaggedrevs', "$base/FlaggedRevs.sql" );
			$du->addExtensionIndex( 'flaggedrevs', 'fr_user', "$base/patch-fr_user-index.sql" );

		} elseif ( $wgDBtype == 'postgres' ) {
			$base = __DIR__ . '/postgres';
			// Initial install tables (current schema)
			$du->addExtensionTable( 'flaggedrevs', "$base/FlaggedRevs.pg.sql" );
			$du->addExtensionIndex( 'flaggedrevs', 'fr_user', "$base/patch-fr_user-index.sql" );

		} elseif ( $wgDBtype == 'sqlite' ) {
			$base = __DIR__ . '/mysql';
			$du->addExtensionTable( 'flaggedrevs', "$base/FlaggedRevs.sql" );
		}
	}
}
