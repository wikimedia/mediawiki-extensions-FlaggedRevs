<?php

use MediaWiki\User\UserIdentity;

/**
 * Class containing utility functions for per-user stats
 */
class FRUserCounters {
	/**
	 * Get params for a user ID
	 * @param int $userId
	 * @param int $flags FR_MASTER, FR_FOR_UPDATE
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
	 * @param int $flags FR_MASTER, FR_FOR_UPDATE
	 * @return stdClass|false
	 */
	private static function fetchParamsRow( $userId, $flags = 0 ) {
		$options = [];
		if ( $flags & FR_MASTER || $flags & FR_FOR_UPDATE ) {
			$db = wfGetDB( DB_PRIMARY );
			if ( $flags & FR_FOR_UPDATE ) {
				$options[] = 'FOR UPDATE';
			}
		} else {
			$db = wfGetDB( DB_REPLICA );
		}
		return $db->selectRow( 'flaggedrevs_promote',
			'frp_user_params',
			[ 'frp_user_id' => $userId ],
			__METHOD__,
			$options
		);
	}

	/**
	 * Save params for a user
	 * @param int $userId
	 * @param array $params
	 */
	public static function saveUserParams( $userId, array $params ) {
		$dbw = wfGetDB( DB_PRIMARY );
		$dbw->replace(
			'flaggedrevs_promote',
			'frp_user_id',
			[
				'frp_user_id' => $userId,
				'frp_user_params' => self::flattenParams( $params )
			],
			__METHOD__
		);
	}

	/**
	 * @param UserIdentity $user
	 */
	public static function deleteUserParams( UserIdentity $user ) {
		$dbw = wfGetDB( DB_PRIMARY );
		$dbw->delete(
			'flaggedrevs_promote',
			[ 'frp_user_id' => $user->getId() ],
			__METHOD__
		);
	}

	/**
	 * @param UserIdentity $oldUser
	 * @param UserIdentity $newUser
	 */
	public static function mergeUserParams( UserIdentity $oldUser, UserIdentity $newUser ) {
		$oldParams = self::getUserParams( $oldUser->getId(), FR_MASTER );
		$newParams = self::getUserParams( $newUser->getId(), FR_MASTER );
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
		$p = self::getUserParams( $userId, FR_FOR_UPDATE );
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
			if ( strpos( $key, '=' ) !== false || strpos( $key, "\n" ) !== false ) {
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
	 * @param WikiPage $wikiPage the article just edited
	 * @param string $summary edit summary
	 * @return bool anything changed
	 */
	public static function updateUserParams( array &$p, WikiPage $wikiPage, $summary ) {
		global $wgFlaggedRevsAutoconfirm, $wgFlaggedRevsAutopromote;
		# Update any special counters for non-null revisions
		$changed = false;
		if ( $wikiPage->getTitle()->isContentPage() ) {
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
				&& $wikiPage->getId()
				&& !in_array( $wikiPage->getId(), $pages )
			) {
				$pages[] = $wikiPage->getId();
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
