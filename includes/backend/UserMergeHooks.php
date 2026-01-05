<?php

namespace MediaWiki\Extension\FlaggedRevs\Backend;

use FRUserCounters;
use MediaWiki\Extension\UserMerge\Hooks\AccountFieldsHook;
use MediaWiki\Extension\UserMerge\Hooks\DeleteAccountHook;
use MediaWiki\Extension\UserMerge\Hooks\MergeAccountFromToHook;
use MediaWiki\User\User;

class UserMergeHooks implements
	AccountFieldsHook,
	DeleteAccountHook,
	MergeAccountFromToHook
{
	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/DeleteAccount
	 */
	public function onDeleteAccount( User &$oldUser ) {
		FRUserCounters::deleteUserParams( $oldUser );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/MergeAccountFromTo
	 */
	public function onMergeAccountFromTo( User &$oldUser, User &$newUser ) {
		if ( $newUser->isRegistered() ) {
			FRUserCounters::mergeUserParams( $oldUser, $newUser );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/UserMergeAccountFields
	 */
	public function onUserMergeAccountFields( array &$updateFields ) {
		$updateFields[] = [ 'flaggedrevs', 'fr_user' ];
	}
}
