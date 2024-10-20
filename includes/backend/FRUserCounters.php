<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use Wikimedia\Rdbms\IDBAccessObject;

/**
 * Class containing utility functions for per-user stats
 */
class FRUserCounters {
	/**
	 * Get params for a user ID
	 * @param int $userId
	 * @param int $flags One of the IDBAccessObject::READ_… constants
	 * @return array
	 */
	public static function getUserParams( $userId, $flags = 0 ) {
		$p = [];
		$row = self::fetchParamsRow( $userId, $flags );
		if ( $row ) {
			$p = self::expandParams( $row->frp_user_params );
		}
		self::setUnitializedFields( $p );
		return $p;
	}

	/**
	 * Get params for a user
	 * @param UserIdentity $user
	 * @return array|null
	 * @suppress PhanUndeclaredProperty
	 */
	public static function getParams( UserIdentity $user ) {
		if ( !$user->isRegistered() ) {
			return null;
		}

		if ( !isset( $user->fr_user_params ) ) { // process cache...
			$user->fr_user_params = self::getUserParams( $user->getId() );
		}
		return $user->fr_user_params;
	}

	/**
	 * Initializes unset param fields to their starting values
	 * @param array &$p
	 */
	private static function setUnitializedFields( array &$p ) {
		if ( !isset( $p['uniqueContentPages'] ) ) {
			$p['uniqueContentPages'] = [];
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
	}

	/**
	 * Get the params row for a user
	 * @param int $userId
	 * @param int $flags One of the IDBAccessObject::READ_… constants
	 * @return stdClass|false
	 */
	private static function fetchParamsRow( $userId, $flags = 0 ) {
		if ( $flags & IDBAccessObject::READ_LATEST ) {
			$db = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();
		} else {
			$db = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
		}
		return $db->newSelectQueryBuilder()
			->select( 'frp_user_params' )
			->from( 'flaggedrevs_promote' )
			->where( [ 'frp_user_id' => $userId ] )
			->recency( $flags )
			->caller( __METHOD__ )
			->fetchRow();
	}

	/**
	 * Save params for a user
	 * @param int $userId
	 * @param array $params
	 */
	public static function saveUserParams( $userId, array $params ) {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		$dbw->newReplaceQueryBuilder()
			->replaceInto( 'flaggedrevs_promote' )
			->uniqueIndexFields( 'frp_user_id' )
			->row( [
				'frp_user_id' => $userId,
				'frp_user_params' => self::flattenParams( $params )
			] )
			->caller( __METHOD__ )
			->execute();
	}

	/**
	 * @param UserIdentity $user
	 */
	public static function deleteUserParams( UserIdentity $user ) {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'flaggedrevs_promote' )
			->where( [ 'frp_user_id' => $user->getId() ] )
			->caller( __METHOD__ )
			->execute();
	}

	/**
	 * @param UserIdentity $oldUser
	 * @param UserIdentity $newUser
	 */
	public static function mergeUserParams( UserIdentity $oldUser, UserIdentity $newUser ) {
		$oldParams = self::getUserParams( $oldUser->getId(), IDBAccessObject::READ_LATEST );
		$newParams = self::getUserParams( $newUser->getId(), IDBAccessObject::READ_LATEST );
		$newParams['uniqueContentPages'] = array_unique( array_merge(
			$newParams['uniqueContentPages'],
			$oldParams['uniqueContentPages']
		) );
		sort( $newParams['uniqueContentPages'] );
		$newParams['totalContentEdits'] += $oldParams['totalContentEdits'];
		$newParams['editComments'] += $oldParams['editComments'];
		$newParams['revertedEdits'] += $oldParams['revertedEdits'];

		self::saveUserParams( $newUser->getId(), $newParams );
	}

	/**
	 * Increments a count for a user
	 * @param int $userId
	 * @param string $param Count name
	 */
	public static function incCount( $userId, $param ) {
		$p = self::getUserParams( $userId, IDBAccessObject::READ_EXCLUSIVE );
		if ( !isset( $p[$param] ) ) {
			$p[$param] = 0;
		}
		$p[$param]++;
		self::saveUserParams( $userId, $p );
	}

	/**
	 * Flatten params for a user for DB storage
	 * Note: param values must be integers
	 * @param array $params
	 * @return string
	 */
	private static function flattenParams( array $params ) {
		$flatRows = [];
		foreach ( $params as $key => $value ) {
			if ( str_contains( $key, '=' ) || str_contains( $key, "\n" ) ) {
				throw new InvalidArgumentException( "flattenParams() - key cannot use '=' or newline" );
			}
			if ( $key === 'uniqueContentPages' ) { // list
				$value = implode( ',', array_map( 'intval', $value ) );
			} else {
				$value = intval( $value );
			}
			$flatRows[] = trim( $key ) . '=' . $value;
		}
		return implode( "\n", $flatRows );
	}

	/**
	 * Expand params for a user from DB storage
	 * @param string $flatPars
	 * @return array
	 */
	private static function expandParams( $flatPars ) {
		$p = []; // init
		$flatPars = explode( "\n", trim( $flatPars ) );
		foreach ( $flatPars as $pair ) {
			$m = explode( '=', trim( $pair ), 2 );
			$key = $m[0];
			$value = $m[1] ?? '';
			if ( $key === 'uniqueContentPages' ) { // list
				$value = ( $value === '' )
					? [] // explode() would make [ 0 => '' ]
					: array_map( 'intval', explode( ',', $value ) );
			} else {
				$value = intval( $value );
			}
			$p[$key] = $value;
		}
		return $p;
	}

	/**
	 * Update users params array for a user on edit
	 * @param array &$p user params
	 * @param Title $title the article just edited
	 * @param string $summary edit summary
	 * @return bool anything changed
	 */
	public static function updateUserParams( array &$p, Title $title, $summary ) {
		global $wgFlaggedRevsAutoconfirm, $wgFlaggedRevsAutopromote;
		# Update any special counters for non-null revisions
		$changed = false;
		if ( $title->isContentPage() ) {
			$pages = $p['uniqueContentPages']; // page IDs
			# Don't let this get bloated for no reason
			$maxUniquePages = 50; // some flexibility
			if ( is_array( $wgFlaggedRevsAutoconfirm ) &&
				$wgFlaggedRevsAutoconfirm['uniqueContentPages'] > $maxUniquePages
			) {
				$maxUniquePages = $wgFlaggedRevsAutoconfirm['uniqueContentPages'];
			}
			if ( is_array( $wgFlaggedRevsAutopromote ) &&
				$wgFlaggedRevsAutopromote['uniqueContentPages'] > $maxUniquePages
			) {
				$maxUniquePages = $wgFlaggedRevsAutopromote['uniqueContentPages'];
			}
			if ( count( $pages ) < $maxUniquePages // limit the size of this
				&& $title->getId()
				&& !in_array( $title->getId(), $pages )
			) {
				$pages[] = $title->getId();
				$p['uniqueContentPages'] = $pages;
			}
			$p['totalContentEdits'] += 1;
			$changed = true;
		}
		// Record non-automatic summary tally
		if ( !preg_match( '/^\/\*.*\*\/$/', $summary ) ) {
			$p['editComments'] += 1;
			$changed = true;
		}
		return $changed;
	}
}
