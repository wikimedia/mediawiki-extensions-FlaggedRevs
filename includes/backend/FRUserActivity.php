<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
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

				$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

				$setOpts += Database::getCacheSetOptions( $dbr );
				// Get number of active editors watching this page...
				$count = (int)$dbr->newSelectQueryBuilder()
					->select( 'COUNT(*)' )
					->from( 'watchlist' )
					->join( 'user', null, 'wl_user = user_id' )
					->where( [
						'wl_namespace' => $title->getNamespace(),
						'wl_title' => $title->getDBkey(),
						'EXISTS(' . $dbr->newSelectQueryBuilder()
							->select( '1' )
							->from( 'recentchanges' )
							->join( 'actor', null, 'actor_id=rc_actor' )
							->where( [
								'actor_name=user_name',
								$dbr->expr( 'rc_timestamp', '>',
									$dbr->timestamp( time() - 86400 * $wgActiveUserDays ) )
							] )
							->caller( $fname )
							->getSQL() .
						')'
					] )
					->caller( $fname )
					->fetchField();

				if ( $count > 100 ) {
					// More aggresive caching for larger counts
					$ttl = $cache::TTL_MINUTE * 30;
				}

				return $count;
			}
		);
	}

}
