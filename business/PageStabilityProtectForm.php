<?php

use MediaWiki\MediaWikiServices;

// Assumes $wgFlaggedRevsProtection is on
class PageStabilityProtectForm extends PageStabilityForm {
	protected function reallyDoPreloadParameters() {
		$oldConfig = $this->getOldConfig();
		$this->autoreview = $oldConfig['autoreview']; // protect level
		$this->watchThis = MediaWikiServices::getInstance()->getWatchlistManager()
			->isWatched( $this->getUser(), $this->title );
	}

	protected function reallyDoCheckParameters() {
		$oldConfig = $this->getOldConfig();
		# Autoreview only when protecting currently unprotected pages
		$this->reviewThis = ( FRPageConfig::getProtectionLevel( $oldConfig ) == 'none' );
		# Autoreview restriction => use stable
		# No autoreview restriction => site default
		$this->override = ( $this->autoreview != '' )
			? 1 // edits require review before being published
			: (int)FlaggedRevs::isStableShownByDefault(); // site default
		# Check that settings are a valid protection level...
		$newConfig = [
			'override'   => $this->override,
			'autoreview' => $this->autoreview
		];
		if ( FRPageConfig::getProtectionLevel( $newConfig ) == 'invalid' ) {
			return 'stabilize_invalid_level'; // double-check configuration
		}
		# Check autoreview restriction setting
		if ( !FlaggedRevs::userCanSetAutoreviewLevel( $this->user, $this->autoreview ) ) {
			return 'stabilize_denied'; // invalid value
		}
		return true;
	}
}
