<?php

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\Database;

/**
 * Class of utility functions for getting/tracking user activity
 */
class FRUserActivity {
	// 20*60
	private const PAGE_REVIEW_SEC = 1200;
	// 6*60
	private const CHANGE_REVIEW_SEC = 360;

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
			function ( $oldValue, &$ttl, array &$setOpts ) use ( $cache, $title, $fname ) {
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

	/**
	 * Get who is currently reviewing a page
	 * @param int $pageId
	 * @return array (username or null, MW timestamp or null)
	 */
	public static function getUserReviewingPage( $pageId ) {
		$cache = self::getActivityStore();
		$key = $cache->makeKey( 'flaggedrevs', 'userReviewingPage', $pageId );
		$val = $cache->get( $key );

		return is_array( $val ) && count( $val ) == 3
			? [ $val[0], $val[1] ]
			: [ null, null ];
	}

	/**
	 * Check if someone is currently reviewing a page
	 * @param int $pageId
	 * @return bool
	 */
	public static function pageIsUnderReview( $pageId ) {
		$m = self::getUserReviewingPage( $pageId );

		return ( $m[0] !== null );
	}

	/**
	 * Set the flag for who is reviewing a page if not already set by someone.
	 * If already set, then increment the instance counter (multiple windows)
	 * and add on time to the expiry.
	 *
	 * @param User $user
	 * @param int $pageId
	 * @return bool flag set
	 */
	public static function setUserReviewingPage( User $user, $pageId ) {
		$cache = self::getActivityStore();
		$key = $cache->makeKey( 'flaggedrevs', 'userReviewingPage', $pageId );

		return self::incUserReviewingItem( $key, $user, self::PAGE_REVIEW_SEC );
	}

	/**
	 * Clear an instance of a user reviewing a page by decrementing the counter.
	 * If it reaches 0 instances, then clear the flag for who is reviewing the page.
	 * @param User $user
	 * @param int $pageId
	 * @return bool flag unset
	 */
	public static function clearUserReviewingPage( User $user, $pageId ) {
		$cache = self::getActivityStore();
		$key = $cache->makeKey( 'flaggedrevs', 'userReviewingPage', $pageId );

		return self::decUserReviewingItem( $key, $user, self::PAGE_REVIEW_SEC );
	}

	/**
	 * Get who is currently reviewing a diff
	 * @param int $oldId
	 * @param int $newId
	 * @return array (username or null, MW timestamp or null)
	 */
	public static function getUserReviewingDiff( $oldId, $newId ) {
		$cache = self::getActivityStore();
		$key = $cache->makeKey( 'flaggedrevs', 'userReviewingDiff', $oldId, $newId );
		$val = $cache->get( $key );

		return is_array( $val ) && count( $val ) == 3
			? [ $val[0], $val[1] ]
			: [ null, null ];
	}

	/**
	 * Check if someone is currently reviewing a diff
	 * @param int $oldId
	 * @param int $newId
	 * @return bool
	 */
	public static function diffIsUnderReview( $oldId, $newId ) {
		$m = self::getUserReviewingDiff( $oldId, $newId );

		return ( $m[0] !== null );
	}

	/**
	 * Set the flag for who is reviewing a diff if not already set by someone.
	 * If already set, then increment the instance counter (multiple windows)
	 * and add on time to the expiry.
	 * @param User $user
	 * @param int $oldId
	 * @param int $newId
	 * @return bool flag set
	 */
	public static function setUserReviewingDiff( User $user, $oldId, $newId ) {
		$cache = self::getActivityStore();
		$key = $cache->makeKey( 'flaggedrevs', 'userReviewingDiff', $oldId, $newId );

		return self::incUserReviewingItem( $key, $user, self::CHANGE_REVIEW_SEC );
	}

	/**
	 * Clear an instance of a user reviewing a diff by decrementing the counter.
	 * If it reaches 0 instances, then clear the flag for who is reviewing the diff.
	 * @param User $user
	 * @param int $oldId
	 * @param int $newId
	 * @return bool flag unset
	 */
	public static function clearUserReviewingDiff( User $user, $oldId, $newId ) {
		$cache = self::getActivityStore();
		$key = $cache->makeKey( 'flaggedrevs', 'userReviewingDiff', $oldId, $newId );

		return self::decUserReviewingItem( $key, $user, self::CHANGE_REVIEW_SEC );
	}

	/**
	 * @param string $key
	 * @param User $user
	 * @param int $ttlSec
	 * @return bool
	 */
	private static function incUserReviewingItem( $key, User $user, $ttlSec ) {
		$wasSet = false; // was changed?

		$now = wfTimestampNow();
		self::getActivityStore()->merge(
			$key,
			function ( BagOStuff $store, $key, $oldVal ) use ( $user, &$wasSet, $now ) {
				if ( is_array( $oldVal ) && count( $oldVal ) == 3 ) { // flag set
					list( $u, $ts, $cnt ) = $oldVal;
					if ( $u === $user->getName() ) { // by this user
						$wasSet = true;
						return [ $u, $ts, $cnt + 1 ]; // inc counter
					}
				} else { // no flag set
					$wasSet = true;
					return [ $user->getName(), $now, 1 ];
				}

				return false; // do nothing
			},
			$ttlSec
		);

		return $wasSet;
	}

	/**
	 * @param string $key
	 * @param User $user
	 * @param int $ttlSec
	 * @return bool
	 */
	private static function decUserReviewingItem( $key, User $user, $ttlSec ) {
		$wasSet = false; // was changed?

		self::getActivityStore()->merge(
			$key,
			function ( BagOStuff $store, $key, $oldVal ) use ( $user, &$wasSet ) {
				if ( is_array( $oldVal ) && count( $oldVal ) != 3 ) {
					return false; // flag not set
				}

				list( $u, $ts, $cnt ) = $oldVal;
				if ( $u === $user->getName() ) {
					$wasSet = true;
					if ( $cnt <= 1 ) {
						$store->delete( $key );
					} else {
						return [ $u, $ts, $cnt - 1 ]; // dec counter
					}
				}

				return false; // do nothing
			},
			$ttlSec
		);

		return $wasSet;
	}

	/**
	 * @return BagOStuff
	 */
	private static function getActivityStore() {
		return ObjectCache::getInstance( 'db-replicated' );
	}
}
