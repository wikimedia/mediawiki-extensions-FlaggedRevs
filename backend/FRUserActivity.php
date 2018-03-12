<?php
/**
 * Class of utility functions for getting/tracking user activity
 */
class FRUserActivity {
	const PAGE_REVIEW_SEC = 1200; // 20*60
	const CHANGE_REVIEW_SEC = 360; // 6*60

	/**
	 * Get number of active users watching a page
	 * @param Title $title
	 * @return int
	 */
	public static function numUsersWatchingPage( Title $title ) {
		global $wgMemc, $wgActiveUserDays;

		# Check the cache...
		$key = wfMemcKey( 'flaggedrevs', 'users-watching', $title->getArticleID() );
		$val = $wgMemc->get( $key );
		if ( is_int( $val ) ) {
			return $val; // cache hit
		}

		# Get number of active editors watching this page...
		$dbr = wfGetDB( DB_REPLICA );
		$actorQuery = ActorMigration::newMigration()->getJoin( 'rc_user' );
		$count = (int)$dbr->selectField(
			[ 'watchlist', 'user' ],
			'COUNT(*)',
			[
				'wl_namespace' => $title->getNamespace(),
				'wl_title'     => $title->getDBkey(),
				'wl_user = user_id',
				'EXISTS(' . $dbr->selectSQLText(
					[ 'recentchanges' ] + $actorQuery['tables'],
					'1',
					[
						'user_name = ' . $actorQuery['fields']['rc_user_text'],
						'rc_timestamp > ' . $dbr->timestamp( time() - 86400 * $wgActiveUserDays )
					],
					__METHOD__,
					[],
					$actorQuery['joins']
				) . ')'
			],
			__METHOD__
		);

		# Save new value to cache (more aggresive for larger counts)
		$wgMemc->set( $key, $count, ( $count > 100 ) ? 30 * 60 : 5 * 60 );

		return $count;
	}

	/**
	 * Get who is currently reviewing a page
	 * @param int $pageId
	 * @return array (username or null, MW timestamp or null)
	 */
	public static function getUserReviewingPage( $pageId ) {
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingPage', $pageId );
		$val = ObjectCache::getMainStashInstance()->get( $key );

		return ( count( $val ) == 3 )
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
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingPage', $pageId );
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
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingPage', $pageId );
		return self::decUserReviewingItem( $key, $user, self::PAGE_REVIEW_SEC );
	}

	/**
	 * Totally clear the flag for who is reviewing a page.
	 * @param int $pageId
	 * @return void
	 */
	public static function clearAllReviewingPage( $pageId ) {
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingPage', $pageId );
		ObjectCache::getMainStashInstance()->delete( $key );
	}

	/**
	 * Get who is currently reviewing a diff
	 * @param int $oldId
	 * @param int $newId
	 * @return array (username or null, MW timestamp or null)
	 */
	public static function getUserReviewingDiff( $oldId, $newId ) {
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingDiff', $oldId, $newId );
		$val = ObjectCache::getMainStashInstance()->get( $key );

		return ( count( $val ) == 3 )
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
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingDiff', $oldId, $newId );

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
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingDiff', $oldId, $newId );

		return self::decUserReviewingItem( $key, $user, self::CHANGE_REVIEW_SEC );
	}

	/**
	 * Totally clear the flag for who is reviewing a diff.
	 * @param int $oldId
	 * @param int $newId
	 * @return void
	 */
	public static function clearAllReviewingDiff( $oldId, $newId ) {
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingDiff', $oldId, $newId );
		ObjectCache::getMainStashInstance()->delete( $key );
	}

	/**
	 * @param string $key
	 * @param User $user
	 * @param int $ttlSec
	 * @return bool
	 */
	protected static function incUserReviewingItem( $key, User $user, $ttlSec ) {
		$wasSet = false; // was changed?

		ObjectCache::getMainStashInstance()->merge(
			$key,
			function ( BagOStuff $stash, $key, $oldVal ) use ( $user, &$wasSet ) {
				if ( count( $oldVal ) == 3 ) { // flag set
					list( $u, $ts, $cnt ) = $oldVal;
					if ( $u === $user->getName() ) { // by this user
						$wasSet = true;
						return [ $u, $ts, $cnt + 1 ]; // inc counter
					}
				} else { // no flag set
					$wasSet = true;
					return [ $user->getName(), wfTimestampNow(), 1 ];
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
	protected static function decUserReviewingItem( $key, User $user, $ttlSec ) {
		$wasSet = false; // was changed?

		ObjectCache::getMainStashInstance()->merge(
			$key,
			function ( BagOStuff $stash, $key, $oldVal ) use ( $user, &$wasSet ) {
				if ( count( $oldVal ) != 3 ) {
					return false; // flag not set
				}

				list( $u, $ts, $cnt ) = $oldVal;
				if ( $u === $user->getName() ) {
					$wasSet = true;
					if ( $cnt <= 1 ) {
						$stash->delete( $key );
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
}
