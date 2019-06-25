<?php
/**
 * Class containing UI setup functions for a FlaggedRevs environment.
 * This depends on config variables in LocalSettings.php.
 * Note: avoid FlaggedRevs class calls here for performance (like load.php).
 */
class FlaggedRevsUISetup {
	/**
	 * Register FlaggedRevs special page cache updates as needed.
	 * @param array &$updates $wgSpecialPageCacheUpdates (assoc array of special page updaters)
	 */
	public static function defineSpecialPageCacheUpdates( array &$updates ) {
		global $wgFlaggedRevsProtection, $wgFlaggedRevsNamespaces;

		// Show special pages only if FlaggedRevs is enabled on some namespaces
		if ( count( $wgFlaggedRevsNamespaces ) ) {
			if ( !$wgFlaggedRevsProtection ) {
				$updates['UnreviewedPages'] = 'UnreviewedPages::updateQueryCache';
			}
			$updates['ValidationStatistics'] = 'FlaggedRevsStats::updateCache';
		}
	}
}
