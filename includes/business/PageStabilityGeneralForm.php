<?php

// Assumes $wgFlaggedRevsProtection is off
use MediaWiki\MediaWikiServices;

class PageStabilityGeneralForm extends PageStabilityForm {

	/**
	 * @return bool|null
	 */
	public function getReviewThis() {
		return $this->reviewThis;
	}

	/**
	 * @param bool $value
	 */
	public function setReviewThis( $value ) {
		$this->trySet( $this->reviewThis, $value );
	}

	/**
	 * @return int
	 */
	public function getOverride() {
		return $this->override;
	}

	/**
	 * @param int $value
	 */
	public function setOverride( $value ) {
		$this->trySet( $this->override, $value );
	}

	protected function reallyDoPreloadParameters() {
		$oldConfig = $this->getOldConfig();
		$this->override = $oldConfig['override'];
		$this->autoreview = $oldConfig['autoreview'];
		$this->watchThis = MediaWikiServices::getInstance()->getWatchlistManager()
			->isWatched( $this->getUser(), $this->title );
	}

	protected function reallyDoCheckParameters() {
		$this->override = $this->override ? 1 : 0; // default version settings is 0 or 1
		// Check autoreview restriction setting
		if ( $this->autoreview != '' // restriction other than 'none'
			&& !in_array( $this->autoreview, FlaggedRevs::getRestrictionLevels() )
		) {
			return 'stabilize_invalid_autoreview'; // invalid value
		}
		if ( !FlaggedRevs::userCanSetAutoreviewLevel( $this->user, $this->autoreview ) ) {
			return 'stabilize_denied'; // invalid value
		}
		return true;
	}
}
