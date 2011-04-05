<?php
/**
 * Class containing updater functions for a FlaggedRevs environment
 */
class FlaggedRevsUpdaterHooks {
	public static function addSchemaUpdates( DatabaseUpdater $du ) {
		global $wgDBtype;
		$base = dirname( __FILE__ );
		if ( $wgDBtype == 'mysql' ) {
			// Initial install tables (current schema)
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs', "$base/FlaggedRevs.sql", true ) );
			// Updates (in order)...
			$du->addExtensionUpdate( array( 'addField',
				'flaggedpage_config', 'fpc_expiry', "$base/mysql/patch-fpc_expiry.sql", true ) );
			$du->addExtensionUpdate( array( 'addIndex',
				'flaggedpage_config', 'fpc_expiry', "$base/mysql/patch-expiry-index.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs_promote', "$base/mysql/patch-flaggedrevs_promote.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedpages', "$base/mysql/patch-flaggedpages.sql", true ) );
			$du->addExtensionUpdate( array( 'addField',
				'flaggedrevs', 'fr_img_name', "$base/mysql/patch-fr_img_name.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs_tracking', "$base/mysql/patch-flaggedrevs_tracking.sql", true ) );
			$du->addExtensionUpdate( array( 'addField',
				'flaggedpages', 'fp_pending_since', "$base/mysql/patch-fp_pending_since.sql", true ) );
			$du->addExtensionUpdate( array( 'addField',
				'flaggedpage_config', 'fpc_level', "$base/mysql/patch-fpc_level.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedpage_pending', "$base/mysql/patch-flaggedpage_pending.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs_stats', "$base/mysql/patch-flaggedrevs_stats.sql", true ) );
			$du->addExtensionUpdate( array( 'FlaggedRevsUpdaterHooks::doFlaggedImagesTimestampNULL',
				"$base/mysql/patch-fi_img_timestamp.sql" ) );
		} elseif ( $wgDBtype == 'postgres' ) {
			// Initial install tables (current schema)
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs', "$base/FlaggedRevs.pg.sql", true ) );
			// Updates (in order)...
			$du->addExtensionUpdate( array( 'addField',
				'flaggedpage_config', 'fpc_expiry', "TIMESTAMPTZ NULL" ) );
			$du->addExtensionUpdate( array( 'addIndex',
				'flaggedpage_config', 'fpc_expiry', "$base/postgres/patch-expiry-index.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs_promote', "$base/postgres/patch-flaggedrevs_promote.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedpages', "$base/postgres/patch-flaggedpages.sql", true ) );
			$du->addExtensionUpdate( array( 'addIndex',
				'flaggedrevs', 'fr_img_sha1', "$base/postgres/patch-fr_img_name.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs_tracking', "$base/postgres/patch-flaggedrevs_tracking.sql", true ) );
			$du->addExtensionUpdate( array( 'addIndex',
				'flaggedpages', 'fp_pending_since', "$base/postgres/patch-fp_pending_since.sql", true ) );
			$du->addExtensionUpdate( array( 'addField',
				'flaggedpage_config', 'fpc_level', "TEXT NULL" ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedpage_pending', "$base/postgres/patch-flaggedpage_pending.sql", true ) );
			// @TODO: PG stats table???
			$du->addExtensionUpdate( array( 'FlaggedRevsUpdaterHooks::doFlaggedImagesTimestampNULL',
				"$base/postgres/patch-fi_img_timestamp.sql" ) );
		} elseif ( $wgDBtype == 'sqlite' ) {
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs', "$base/FlaggedRevs.sql", true ) );
		}
		return true;
	}

	public static function doFlaggedImagesTimestampNULL( $du, $patch ) {
		$info = $du->getDB()->fieldInfo( 'flaggedimages', 'fi_img_timestamp' );
		if ( $info->isNullable() ) {
			$du->output( "...fi_img_timestamp is already nullable.\n" );
			return;
		}
		$du->output( "Making fi_img_timestamp nullable... " );
		$du->getDB()->sourceFile( $patch );
		$du->output( "done.\n" );
	}
}
