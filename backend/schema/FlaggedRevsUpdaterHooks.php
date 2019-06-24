<?php
/**
 * Class containing updater functions for a FlaggedRevs environment
 */
class FlaggedRevsUpdaterHooks {
	public static function addSchemaUpdates( DatabaseUpdater $du ) {
		global $wgDBtype;
		if ( $wgDBtype == 'mysql' ) {
			$base = __DIR__ . '/mysql';
			// Initial install tables (current schema)
			$du->addExtensionTable( 'flaggedrevs', "$base/FlaggedRevs.sql" );

			// Updates (in order)...
			$du->addExtensionField( 'flaggedpage_config', 'fpc_expiry', "$base/patch-fpc_expiry.sql" );
			$du->addExtensionIndex( 'flaggedpage_config', 'fpc_expiry', "$base/patch-expiry-index.sql" );
			$du->addExtensionTable( 'flaggedrevs_promote', "$base/patch-flaggedrevs_promote.sql" );
			$du->addExtensionTable( 'flaggedpages', "$base/patch-flaggedpages.sql" );
			$du->addExtensionField( 'flaggedrevs', 'fr_img_name', "$base/patch-fr_img_name.sql" );
			$du->addExtensionTable( 'flaggedrevs_tracking', "$base/patch-flaggedrevs_tracking.sql" );
			$du->addExtensionField( 'flaggedpages', 'fp_pending_since', "$base/patch-fp_pending_since.sql" );
			$du->addExtensionField( 'flaggedpage_config', 'fpc_level', "$base/patch-fpc_level.sql" );
			$du->addExtensionTable( 'flaggedpage_pending', "$base/patch-flaggedpage_pending.sql" );
			$du->addExtensionUpdate( [ 'FlaggedRevsUpdaterHooks::doFlaggedImagesTimestampNULL',
				"$base/patch-fi_img_timestamp.sql" ] );
			$du->addExtensionUpdate( [ 'FlaggedRevsUpdaterHooks::doFlaggedRevsRevTimestamp',
				"$base/patch-fr_page_rev-index.sql" ] );
			$du->addExtensionTable( 'flaggedrevs_statistics', "$base/patch-flaggedrevs_statistics.sql" );
			$du->addExtensionIndex( 'flaggedrevs', 'fr_user', "$base/patch-fr_user-index.sql" );

		} elseif ( $wgDBtype == 'postgres' ) {
			$base = __DIR__ . '/postgres';
			// Initial install tables (current schema)
			$du->addExtensionTable( 'flaggedrevs', "$base/FlaggedRevs.pg.sql" );

			// Updates (in order)...
			$du->addExtensionField( 'flaggedpage_config', 'fpc_expiry', "TIMESTAMPTZ NULL" );
			$du->addExtensionIndex( 'flaggedpage_config', 'fpc_expiry', "$base/patch-expiry-index.sql" );
			$du->addExtensionTable( 'flaggedrevs_promote', "$base/patch-flaggedrevs_promote.sql" );
			$du->addExtensionTable( 'flaggedpages', "$base/patch-flaggedpages.sql" );
			$du->addExtensionIndex( 'flaggedrevs', 'fr_img_sha1', "$base/patch-fr_img_name.sql" );
			$du->addExtensionTable( 'flaggedrevs_tracking', "$base/patch-flaggedrevs_tracking.sql" );
			$du->addExtensionIndex( 'flaggedpages', 'fp_pending_since', "$base/patch-fp_pending_since.sql" );
			$du->addExtensionField( 'flaggedpage_config', 'fpc_level', "TEXT NULL" );
			$du->addExtensionTable( 'flaggedpage_pending', "$base/patch-flaggedpage_pending.sql" );
			$du->addExtensionUpdate( [ 'FlaggedRevsUpdaterHooks::doFlaggedImagesTimestampNULL',
				"$base/patch-fi_img_timestamp.sql" ] );
			$du->addExtensionUpdate( [ 'FlaggedRevsUpdaterHooks::doFlaggedRevsRevTimestamp',
				"$base/patch-fr_page_rev-index.sql" ] );
			$du->addExtensionTable( 'flaggedrevs_statistics', "$base/patch-flaggedrevs_statistics.sql" );
			$du->addExtensionIndex( 'flaggedrevs', 'fr_user', "$base/patch-fr_user-index.sql" );

		} elseif ( $wgDBtype == 'sqlite' ) {
			$base = __DIR__ . '/mysql';
			$du->addExtensionTable( 'flaggedrevs', "$base/FlaggedRevs.sql" );
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

	public static function doFlaggedRevsRevTimestamp( $du, $patch ) {
		$exists = $du->getDB()->fieldInfo( 'flaggedrevs', 'fr_rev_timestamp' );
		if ( $exists ) {
			$du->output( "...fr_rev_timestamp already exists.\n" );
			return;
		}
		$scriptDir = __DIR__ . "/../../maintenance/populateRevTimestamp.php";
		if ( !file_exists( $scriptDir ) ) {
			$du->output( "...populateRevTimestamp.php missing! Aborting fr_rev_timestamp update.\n" );
			return; // sanity; all or nothing
		}
		$du->output( "Adding fr_rev_timestamp and redoing flaggedrevs table indexes... " );
		// Change the schema
		$du->getDB()->sourceFile( $patch );
		// Populate columns
		$task = $du->maintenance->runChild( 'PopulateFRRevTimestamp', $scriptDir );
		$task->execute();
		$du->output( "done.\n" );
	}
}
