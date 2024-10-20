<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\IDBAccessObject;

/**
 * Page stability configuration functions
 */
class FRPageConfig {
	/**
	 * Get visibility settings/restrictions for a page
	 * @param Title $title page title
	 * @param int $flags One of the IDBAccessObject::READ_… constants
	 * @return array [ 'override' => int, 'autoreview' => string, 'expiry' => string ]
	 */
	public static function getStabilitySettings( Title $title, $flags = 0 ) {
		if ( $flags & IDBAccessObject::READ_LATEST ) {
			$db = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();
		} else {
			$db = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
		}
		$row = $db->newSelectQueryBuilder()
			->select( [ 'fpc_override', 'fpc_level', 'fpc_expiry' ] )
			->from( 'flaggedpage_config' )
			->where( [ 'fpc_page_id' => $title->getArticleID() ] )
			->caller( __METHOD__ )
			->fetchRow();
		return self::getVisibilitySettingsFromRow( $row );
	}

	/**
	 * Get page configuration settings from a DB row
	 * @param stdClass|false $row
	 * @return array [ 'override' => int, 'autoreview' => string, 'expiry' => string ]
	 */
	public static function getVisibilitySettingsFromRow( $row ) {
		$expiry = false;
		if ( $row ) {
			$expiry = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase()
				->decodeExpiry( $row->fpc_expiry );
			# Only apply the settings if they haven't expired
			if ( !$expiry || $expiry < wfTimestampNow() ) {
				$row = null; // expired
			}
		}
		// Is there a non-expired row?
		if ( !$row ) {
			# Return the default config if this page doesn't have its own
			return self::getDefaultVisibilitySettings();
		}

		$level = self::isValidRestriction( $row->fpc_level ) ? $row->fpc_level : '';
		$config = [
			'override' => $row->fpc_override ? 1 : 0,
			'autoreview' => $level,
			'expiry' => $expiry // TS_MW
		];
		# If there are protection levels defined check if this is valid...
		if ( FlaggedRevs::useProtectionLevels() ) {
			$level = self::getProtectionLevel( $config );
			if ( $level == 'invalid' || $level == 'none' ) {
				// If 'none', make sure expiry is 'infinity'
				return self::getDefaultVisibilitySettings(); // revert to default (none)
			}
		}
		return $config;
	}

	/**
	 * Get default stability configuration settings
	 * @return array
	 */
	public static function getDefaultVisibilitySettings() {
		return [
			# Keep this consistent: 1 => override, 0 => don't
			'override'   => FlaggedRevs::isStableShownByDefault() ? 1 : 0,
			'autoreview' => '',
			'expiry'     => 'infinity'
		];
	}

	/**
	 * Set the stability configuration settings for a page
	 * @param Title $title
	 * @param array $config
	 * @return bool Row changed
	 */
	public static function setStabilitySettings( Title $title, array $config ) {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		# Purge expired entries on one in every 10 queries
		if ( !mt_rand( 0, 10 ) ) {
			self::purgeExpiredConfigurations();
		}
		# If setting to site default values and there is a row then erase it
		if ( self::configIsReset( $config ) ) {
			$dbw->newDeleteQueryBuilder()
				->deleteFrom( 'flaggedpage_config' )
				->where( [ 'fpc_page_id' => $title->getArticleID() ] )
				->caller( __METHOD__ )
				->execute();
			$changed = ( $dbw->affectedRows() > 0 ); // did this do anything?
		# Otherwise, add/replace row if we are not just setting it to the site default
		} else {
			$dbExpiry = $dbw->encodeExpiry( $config['expiry'] );
			# Get current config...
			$oldRow = $dbw->newSelectQueryBuilder()
				->select( [ 'fpc_override', 'fpc_level', 'fpc_expiry' ] )
				->from( 'flaggedpage_config' )
				->where( [ 'fpc_page_id' => $title->getArticleID() ] )
				->forUpdate() // lock
				->caller( __METHOD__ )
				->fetchRow();
			# Check if this is not the same config as the existing (if any) row
			$changed = ( !$oldRow // no previous config
				|| $oldRow->fpc_override != $config['override'] // ...override changed, or...
				|| $oldRow->fpc_level != $config['autoreview'] // ...autoreview level changed, or...
				|| $oldRow->fpc_expiry != $dbExpiry // ...expiry changed
			);
			# If the new config is different, replace the old row...
			if ( $changed ) {
				$dbw->newReplaceQueryBuilder()
					->replaceInto( 'flaggedpage_config' )
					->uniqueIndexFields( 'fpc_page_id' )
					->row( [
						'fpc_page_id'  => $title->getArticleID(),
						'fpc_override' => (int)$config['override'],
						'fpc_level'    => $config['autoreview'],
						'fpc_expiry'   => $dbExpiry
					] )
					->caller( __METHOD__ )
					->execute();
			}
		}
		return $changed;
	}

	/**
	 * Does this config equal the default settings?
	 * @param array $config
	 * @return bool
	 */
	public static function configIsReset( array $config ) {
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			return ( $config['autoreview'] == '' );
		} else {
			return ( $config['override'] == FlaggedRevs::isStableShownByDefault()
				&& $config['autoreview'] == '' );
		}
	}

	/**
	 * Find what protection level a config is in
	 * @param array $config
	 * @return string
	 */
	public static function getProtectionLevel( array $config ) {
		if ( !FlaggedRevs::useProtectionLevels() ) {
			throw new LogicException( '$wgFlaggedRevsProtection is disabled' );
		}
		$defaultConfig = self::getDefaultVisibilitySettings();
		# Check if the page is not protected at all...
		if ( $config['override'] == $defaultConfig['override']
			&& $config['autoreview'] == ''
		) {
			return "none"; // not protected
		}
		# All protection levels have 'override' on
		if ( $config['override'] ) {
			# The levels are defined by the 'autoreview' settings
			if ( in_array( $config['autoreview'], FlaggedRevs::getRestrictionLevels() ) ) {
				return $config['autoreview'];
			}
		}
		return "invalid";
	}

	/**
	 * Check if an fpc_level value is valid
	 * @param string $right
	 * @return bool
	 */
	private static function isValidRestriction( $right ) {
		if ( $right == '' ) {
			return true; // no restrictions (none)
		}
		return in_array( $right, FlaggedRevs::getRestrictionLevels(), true );
	}

	/**
	 * Purge expired restrictions from the flaggedpage_config table.
	 * The stable version of pages may change and invalidation may be required.
	 */
	private static function purgeExpiredConfigurations() {
		if ( MediaWikiServices::getInstance()->getReadOnlyMode()->isReadOnly() ) {
			return;
		}
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		# Find pages with expired configs...
		$config = self::getDefaultVisibilitySettings(); // config is to be reset
		$cutoff = $dbw->timestamp();
		$ret = $dbw->newSelectQueryBuilder()
			->select( [ 'fpc_page_id', 'page_namespace', 'page_title' ] )
			->from( 'flaggedpage_config' )
			->join( 'page', null, 'page_id = fpc_page_id' )
			->where( $dbw->expr( 'fpc_expiry', '<', $cutoff ) )
			->caller( __METHOD__ )
			->fetchResultSet();
		# Figured out to do with each page...
		$pagesClearConfig = [];
		$pagesClearTracking = [];
		$titlesClearTracking = [];
		foreach ( $ret as $row ) {
			# If FlaggedRevs got "turned off" (in protection config)
			# for this page, then clear it from the tracking tables...
			if ( FlaggedRevs::useOnlyIfProtected() && !$config['override'] ) {
				$pagesClearTracking[] = $row->fpc_page_id; // no stable version
				$titlesClearTracking[] = Title::newFromRow( $row ); // no stable version
			}
			$pagesClearConfig[] = $row->fpc_page_id; // page with expired config
		}
		# Clear the expired config for these pages...
		if ( count( $pagesClearConfig ) ) {
			$dbw->newDeleteQueryBuilder()
				->deleteFrom( 'flaggedpage_config' )
				->where( [ 'fpc_page_id' => $pagesClearConfig, $dbw->expr( 'fpc_expiry', '<', $cutoff ) ] )
				->caller( __METHOD__ )
				->execute();
		}
		# Clear the tracking rows and update page_touched for the
		# pages in $pagesClearConfig that do now have a stable version...
		if ( count( $pagesClearTracking ) ) {
			FlaggedRevs::clearTrackingRows( $pagesClearTracking );
			$dbw->newUpdateQueryBuilder()
				->update( 'page' )
				->set( [ 'page_touched' => $dbw->timestamp() ] )
				->where( [ 'page_id' => $pagesClearTracking ] )
				->caller( __METHOD__ )
				->execute();
		}
		# Also, clear their squid caches and purge other pages that use this page.
		# NOTE: all of these updates are deferred via DeferredUpdates
		foreach ( $titlesClearTracking as $title ) {
			FlaggedRevs::purgeMediaWikiHtmlCdn( $title );
			if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
				FlaggedRevs::updateHtmlCaches( $title ); // purge pages that use this page
			}
		}
	}
}
