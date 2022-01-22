<?php

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\Database;

/**
 * Class of utility functions for getting/tracking user activity
 */
class FRUserActivity {
	/**
	 * Get number of active users watching a page
	 * @param Title $title
	 * @return int
	 */
	public static function numUsersWatchingPage( Title $title ) {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$fname = __METHOD__;

		return $cache->getWithSetCallback(
			$cache->makeKey( 'flaggedrevs-users-watching', $title->getArticleID() ),
			$cache::TTL_MINUTE * 5,
			static function ( $oldValue, &$ttl, array &$setOpts ) use ( $cache, $title, $fname ) {
				global $wgActiveUserDays;

				$dbr = wfGetDB( DB_REPLICA );
				$setOpts += Database::getCacheSetOptions( $dbr );
				// Get number of active editors watching this page...
				$count = (int)$dbr->selectField(
					[ 'watchlist', 'user' ],
					'COUNT(*)',
					[
						'wl_namespace' => $title->getNamespace(),
						'wl_title' => $title->getDBkey(),
						'wl_user = user_id',
						'EXISTS(' . $dbr->selectSQLText(
							[ 'recentchanges', 'actor' ],
							'1',
							[
								'actor_name=user_name',
								'rc_timestamp > ' .
									$dbr->timestamp( time() - 86400 * $wgActiveUserDays )
							],
							$fname,
							[],
							[ 'actor' => [ 'JOIN', 'actor_id=rc_actor' ] ]
						) . ')'
					],
					$fname
				);

				if ( $count > 100 ) {
					// More aggresive caching for larger counts
					$ttl = $cache::TTL_MINUTE * 30;
				}

				return $count;
			}
		);
	}

}
