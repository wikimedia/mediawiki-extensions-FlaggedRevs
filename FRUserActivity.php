<?php
/*
* Class of utility functions for getting/tracking user activity
*/
class FRUserActivity {
	/**
	 * Get number of active users watching a page
	 * @param Title $title
	 * @return int
	 */
	public static function numUsersWatchingPage( Title $title ) {
		global $wgMemc, $wgCookieExpiration;
		# Check the cache...
		$key = wfMemcKey( 'flaggedrevs', 'usersWatching', $title->getArticleID() );
		$val = $wgMemc->get( $key );
		if ( is_int( $val ) ) {
			return $val; // cache hit
		}
		# Get number of active editors watching this page...
		$dbr = wfGetDB( DB_SLAVE );
		$cutoff = $dbr->timestamp( wfTimestamp( TS_UNIX ) - 2 * $wgCookieExpiration );
		$count = (int)$dbr->selectField(
			array( 'watchlist', 'user' ),
			'COUNT(*)',
			array(
				'wl_namespace'    => $title->getNamespace(),
				'wl_title'        => $title->getDBkey(),
				'wl_user = user_id',
				'user_touched > ' . $dbr->addQuotes( $cutoff ) // logged in or out
			),
			__METHOD__,
			array( 'USE INDEX' => array( 'watchlist' => 'namespace_title' ) )
		);
		if ( $count > 10 ) {
			# Save new value to cache (more aggresive for larger counts)
			$wgMemc->set( $key, $count, ( $count > 200 ) ? 30*60 : 5*60 );
		}

		return $count;
	}

	/*
	* Get who is currently reviewing a page
	* @param int $pageId
	* @return array (username or null, MW timestamp or null)
	*/
	public static function getUserReviewingPage( $pageId ) {
		global $wgMemc;
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingPage', $pageId );
		$val = $wgMemc->get( $key );
		if ( is_array( $val ) && count( $val ) == 2 ) {
			return $val;
		}
		return array( null, null );
	}

	/*
	* Set the flag for who is reviewing a page if not already set by someone
	* @param User $user
	* @param int $pageId
	* @return bool flag set
	*/
	public static function setUserReviewingPage( $user, $pageId ) {
		global $wgMemc;
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingPage', $pageId );
		$val = array( $user->getName(), wfTimestampNow() );
		if ( !$wgMemc->get( $key ) ) { // no flag set
			# Set the flag (use locks if available)
			$wgMemc->lock( $key, 4000 ); // 4 sec timeout
			$wgMemc->set( $key, $val, 20*60 ); // 20 min
			$wgMemc->unlock( $key );
			return true;
		}
		return false;
	}

	/*
	* Clear the flag for who is reviewing a page
	* @param int $pageId
	*/
	public static function clearUserReviewingPage( $pageId ) {
		global $wgMemc;
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingPage', $pageId );
		$wgMemc->delete( $key );
	}

	/*
	* Get who is currently reviewing a diff
	* @param int $oldId
	* @param int $newId
	* @return array (username or null, MW timestamp or null)
	*/
	public static function getUserReviewingDiff( $oldId, $newId ) {
		global $wgMemc;
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingDiff', $oldId, $newId );
		$val = $wgMemc->get( $key );
		if ( is_array( $val ) && count( $val ) == 2 ) {
			return $val;
		}
		return array( null, null );
	}

	/*
	* Set the flag for who is reviewing a diff if not already set by someone
	* @param User $user
	* @param int $pageId
	* @return bool flag set
	*/
	public static function setUserReviewingDiff( $user, $oldId, $newId ) {
		global $wgMemc;
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingDiff', $oldId, $newId );
		$val = array( $user->getName(), wfTimestampNow() );
		if ( !$wgMemc->get( $key ) ) { // no flag set
			# Set the flag (use locks if available)
			$wgMemc->lock( $key, 4000 ); // 4 sec timeout
			$wgMemc->set( $key, $val, 6*20 ); // 6 min
			$wgMemc->unlock( $key );
			return true;
		}
		return false;
	}

	/*
	* Clear the flag for who is reviewing a diff
	* @param int $pageId
	*/
	public static function clearUserReviewingDiff( $oldId, $newId ) {
		global $wgMemc;
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingDiff', $oldId, $newId );
		$wgMemc->delete( $key );
	}
}
