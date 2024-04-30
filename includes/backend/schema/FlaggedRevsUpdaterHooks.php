<?php
// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
// phpcs:disable MediaWiki.Commenting.FunctionComment.MissingDocumentationPublic

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
		$du->dropExtensionTable( 'flaggedimages' );

		// Initial install tables (current schema)
		$du->addExtensionTable( 'flaggedrevs', __DIR__ . "/$dbType/tables-generated.sql" );

		if ( $dbType == 'mysql' ) {
			$base = __DIR__ . '/mysql';

			// 1.38
			$du->dropExtensionField(
				'flaggedrevs',
				'fr_img_name',
				"$base/patch-drop-fr_img.sql"
			);

			// 1.39
			$du->dropExtensionField(
				'flaggedpage_config',
				'fpc_select',
				"$base/patch-drop-fpc_select.sql"
			);
			$du->modifyExtensionField(
				'flaggedpages',
				'fp_pending_since',
				"$base/patch-flaggedpages-timestamp.sql"
			);
			$du->modifyExtensionField(
				'flaggedrevs',
				'fr_timestamp',
				"$base/patch-flaggedrevs-timestamps.sql"
			);
			$du->modifyExtensionField(
				'flaggedrevs_statistics',
				'frs_timestamp',
				"$base/patch-flaggedrevs_statistics-timestamp.sql"
			);
			$du->modifyExtensionField(
				'flaggedpage_config',
				'fpc_expiry',
				"$base/patch-flaggedpage_config-timestamp.sql"
			);
		} elseif ( $dbType == 'postgres' ) {
			$base = __DIR__ . '/postgres';

			// 1.38
			$du->addExtensionUpdate(
				[ 'dropPgIndex', 'flaggedrevs', 'fr_img_sha1' ]
			);
			$du->addExtensionUpdate(
				[ 'dropPgField', 'flaggedrevs', 'fr_img_name' ]
			);
			$du->addExtensionUpdate(
				[ 'dropPgField', 'flaggedrevs', 'fr_img_timestamp' ]
			);
			$du->addExtensionUpdate(
				[ 'dropPgField', 'flaggedrevs', 'fr_img_sha1' ]
			);

			// 1.39
			$du->addExtensionUpdate(
				[ 'dropPgField', 'flaggedpage_config', 'fpc_select' ]
			);
			$du->addExtensionUpdate( [
				'dropDefault', 'flaggedpages', 'fp_page_id'
			] );
			$du->addExtensionUpdate( [
				'dropDefault', 'flaggedpages', 'fp_stable'
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedpages', 'fp_page_id', 'INT', ''
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedpages', 'fp_reviewed', 'SMALLINT', 'fp_reviewed::SMALLINT DEFAULT 0'
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedpages', 'fp_stable', 'INT', ''
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedpages', 'fp_quality', 'SMALLINT', ''
			] );
			$du->addExtensionUpdate( [
				'dropDefault', 'flaggedrevs', 'fr_rev_id'
			] );
			$du->addExtensionUpdate( [
				'dropDefault', 'flaggedrevs', 'fr_page_id'
			] );
			$du->addExtensionUpdate( [
				'dropDefault', 'flaggedrevs', 'fr_tags'
			] );
			$du->addExtensionUpdate( [
				'changeNullableField', 'flaggedrevs', 'fr_user', 'NOT NULL', true
			] );
			$du->addExtensionUpdate( [
				'changeNullableField', 'flaggedrevs', 'fr_timestamp', 'NOT NULL', true
			] );
			$du->addExtensionUpdate( [
				'renameIndex', 'flaggedrevs', 'page_rev', 'fr_page_rev'
			] );
			$du->addExtensionUpdate( [
				'renameIndex', 'flaggedrevs', 'page_time', 'fr_page_time'
			] );
			$du->addExtensionUpdate( [
				'renameIndex', 'flaggedrevs', 'page_qal_rev', 'fr_page_qal_rev'
			] );
			$du->addExtensionUpdate( [
				'renameIndex', 'flaggedrevs', 'page_qal_time', 'fr_page_qal_time'
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedrevs', 'fr_rev_id', 'INT', ''
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedrevs', 'fr_page_id', 'INT', ''
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedrevs', 'fr_user', 'INT', ''
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedrevs', 'fr_quality', 'SMALLINT', 'fr_quality::SMALLINT DEFAULT 0'
			] );
			$du->addExtensionUpdate( [
				'dropDefault', 'flaggedpage_config', 'fpc_page_id'
			] );
			$du->addExtensionUpdate( [
				'changeNullableField', 'flaggedpage_config', 'fpc_expiry', 'NOT NULL', true
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedpage_config', 'fpc_page_id', 'INT', ''
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedpage_config', 'fpc_override', 'SMALLINT', ''
			] );
			$du->addExtensionUpdate( [
				'renameIndex', 'flaggedrevs_tracking', 'namespace_title_from', 'frt_namespace_title_from'
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedrevs_tracking', 'ftr_from', 'INT', 'ftr_from::INT DEFAULT 0'
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedrevs_tracking', 'ftr_namespace', 'INT', 'ftr_namespace::INT DEFAULT 0'
			] );
			$du->dropExtensionIndex(
				'flaggedrevs_tracking', 'flaggedrevs_tracking_pkey', "$base/patch-flaggedrevs_tracking-drop-pk.sql"
			);
			$du->addExtensionUpdate( [
				'dropDefault', 'flaggedrevs_promote', 'frp_user_id'
			] );
			$du->addExtensionUpdate( [
				'dropDefault', 'flaggedrevs_promote', 'frp_user_params'
			] );
			$du->addExtensionUpdate( [
				'changeField', 'flaggedrevs_promote', 'frp_user_id', 'INT', ''
			] );
		} elseif ( $dbType == 'sqlite' ) {
			$base = __DIR__ . '/sqlite';

			// 1.38
			$du->dropExtensionField(
				'flaggedrevs',
				'fr_img_name',
				"$base/patch-drop-fr_img.sql"
			);

			// 1.39
			$du->dropExtensionField(
				'flaggedpage_config',
				'fpc_select',
				"$base/patch-drop-fpc_select.sql"
			);
		}

		// 1.40
		$du->dropExtensionIndex(
			'flaggedrevs_tracking',
			'frt_from_namespace_title',
			__DIR__ . "/$dbType/patch-flaggedrevs_tracking-unique-to-pk.sql"
		);
		// 1.42
		$du->dropExtensionTable( 'flaggedtemplates' );
		$du->dropExtensionTable( 'flaggedpage_pending' );
	}
}
