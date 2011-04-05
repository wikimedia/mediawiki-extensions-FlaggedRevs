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
	* @return Array (username or null, MW timestamp or null)
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
		$wasSet = false;

		$wgMemc->lock( $key, 4 ); // 4 sec timeout
		if ( !$wgMemc->get( $key ) ) { // no flag set
			$wgMemc->set( $key, $val, 20*60 ); // 20 min
			$wasSet = true;
		}
		$wgMemc->unlock( $key );

		return $wasSet;
	}

	/*
	* Clear the flag for who is reviewing a page
	* @param User $user
	* @param int $pageId
	*/
	public static function clearUserReviewingPage( $user, $pageId ) {
		global $wgMemc;
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingPage', $pageId );
		$wgMemc->lock( $key, 4 ); // 4 sec timeout
		$val = $wgMemc->get( $key );
		if ( is_array( $val ) && count( $val ) == 2 ) { // flag set
			list( $u, $ts ) = $val;
			if ( $u === $user->getName() ) {
				$wgMemc->delete( $key );
			}
		}
		$this->unlock();
	}

	/*
	* Get who is currently reviewing a diff
	* @param int $oldId
	* @param int $newId
	* @return Array (username or null, MW timestamp or null)
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
		$wasSet = false;

		$wgMemc->lock( $key, 4 ); // 4 sec timeout
		if ( !$wgMemc->get( $key ) ) { // no flag set
			$wgMemc->set( $key, $val, 6*20 ); // 6 min
			$wasSet = true;
		}
		$wgMemc->unlock( $key );

		return $wasSet;
	}

	/*
	* Clear the flag for who is reviewing a diff
	* @param User $user
	* @param int $oldId
	* @param int $newId
	*/
	public static function clearUserReviewingDiff( $user, $oldId, $newId ) {
		global $wgMemc;
		$key = wfMemcKey( 'flaggedrevs', 'userReviewingDiff', $oldId, $newId );
		$wgMemc->lock( $key, 4 ); // 4 sec timeout
		$val = $wgMemc->get( $key );
		if ( is_array( $val ) && count( $val ) == 2 ) { // flag set
			list( $u, $ts ) = $val;
			if ( $u === $user->getName() ) {
				$wgMemc->delete( $key );
			}
		}
		$this->unlock();
	}
}
