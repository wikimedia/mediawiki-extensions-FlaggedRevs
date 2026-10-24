<?php

use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

/**
 * Class containing updater functions for a FlaggedRevs environment
 */
class FlaggedRevsUpdaterHooks implements
	LoadExtensionSchemaUpdatesHook
{

	/**
	 * @inheritDoc
	 */
	public function onLoadExtensionSchemaUpdates( $du ) {
		$dbType = $du->getDB()->getType();

		// Initial install tables (current schema)
		$du->addExtensionTable( 'flaggedrevs', __DIR__ . "/$dbType/tables-generated.sql" );

		// 1.42
		$du->dropExtensionTable( 'flaggedtemplates' );
		$du->dropExtensionTable( 'flaggedpage_pending' );

		// 1.45
		$du->dropExtensionTable( 'flaggedrevs_tracking' );
	}
}
