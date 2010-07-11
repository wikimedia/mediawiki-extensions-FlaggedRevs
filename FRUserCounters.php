<?php
/**
 * Class containing utility functions for per-user stats
 */
class FRUserCounters {
   	/**
	* Get params for a user
	* @param int $uid
	* @param string $DBName, optional wiki name
	* @returns array $params
	*/
	public static function getUserParams( $uid, $DBName = false ) {
		$dbw = wfGetDB( DB_MASTER, array(), $DBName );
		$row = $dbw->selectRow( 'flaggedrevs_promote',
			'frp_user_params',
			array( 'frp_user_id' => $uid ),
			__METHOD__
			// 'FOR UPDATE'
		);
		# Parse params
		$p = array(); // init
		if ( $row ) {
			$flatPars = explode( "\n", trim( $row->frp_user_params ) );
			foreach ( $flatPars as $pair ) {
				$m = explode( '=', trim( $pair ), 2 );
				$key = $m[0];
				$value = isset( $m[1] ) ? $m[1] : null;
				$p[$key] = $value;
			}
		}
		# Initialize fields as needed...
		if ( !isset( $p['uniqueContentPages'] ) ) {
			$p['uniqueContentPages'] = '';
		}
		if ( !isset( $p['totalContentEdits'] ) ) {
			$p['totalContentEdits'] = 0;
		}
		if ( !isset( $p['editComments'] ) ) {
			$p['editComments'] = 0;
		}
		if ( !isset( $p['revertedEdits'] ) ) {
			$p['revertedEdits'] = 0;
		}
		return $p;
	}

   	/**
	* Save params for a user
	* @param int $uid
	* @param array $params
	* @param string $DBName, optional wiki name
	* @returns bool success
	*/
	public static function saveUserParams( $uid, array $params, $DBName = false ) {
		$flatParams = '';
		foreach ( $params as $key => $value ) {
			$flatParams .= "{$key}={$value}\n";
		}
		$dbw = wfGetDB( DB_MASTER, array(), $DBName );
		$row = $dbw->replace( 'flaggedrevs_promote',
			array( 'frp_user_id' ),
			array( 'frp_user_id' => $uid, 'frp_user_params' => trim( $flatParams ) ),
			__METHOD__
		);
		return ( $dbw->affectedRows() > 0 );
	}

   	/**
	* Update users params array for a user on edit
	* @param &array $params
	* @param Article $article the article just edited
	* @param string $summary edit summary
	* @returns bool anything changed
	*/
	public static function updateUserParams( array &$p, Article $article, $summary ) {
		global $wgFlaggedRevsAutoconfirm, $wgFlaggedRevsAutopromote;
		# Update any special counters for non-null revisions
		$changed = false;
		if ( $article->getTitle()->isContentPage() ) {
			$pages = explode( ',', trim( $p['uniqueContentPages'] ) ); // page IDs
			# Don't let this get bloated for no reason
			$maxUniquePages = 50; // some flexibility
			if ( is_array( $wgFlaggedRevsAutoconfirm ) &&
				$wgFlaggedRevsAutoconfirm['uniqueContentPages'] > $maxUniquePages )
			{
				$maxUniquePages = $wgFlaggedRevsAutoconfirm['uniqueContentPages'];
			}
			if ( is_array( $wgFlaggedRevsAutopromote ) &&
				$wgFlaggedRevsAutopromote['uniqueContentPages'] > $maxUniquePages )
			{
				$maxUniquePages = $wgFlaggedRevsAutopromote['uniqueContentPages'];
			}
			if ( count( $pages ) < $maxUniquePages // limit the size of this
				&& !in_array( $article->getId(), $pages ) )
			{
				$pages[] = $article->getId();
				// Clear out any formatting garbage
				$p['uniqueContentPages'] = preg_replace( '/^,/', '', implode( ',', $pages ) );
			}
			$p['totalContentEdits'] += 1;
			$changed = true;
		}
		if ( $summary != '' ) {
			$p['editComments'] += 1;
			$changed = true;
		}
		return $changed;
	}
}
