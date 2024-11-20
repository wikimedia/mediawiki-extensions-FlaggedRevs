<?php

namespace MediaWiki\Extension\FlaggedRevs\Backend\Hook;

use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserIdentity;

interface FlaggedRevsStabilitySettingsChangedHook {

	/**
	 * Use this hook to run code when the stabilisation settings have changed for a page.
	 *
	 * This is intended to function similarly to the {@link ArticleProtectCompleteHook} from MediaWiki core.
	 *
	 * @since 1.44
	 *
	 * @param PageIdentity $title The page where the settings have changed
	 * @param array $newStabilitySettings The new stabilisation settings for the page, containing array keys
	 *   'override', 'autoreview', and 'expiry'.
	 * @param UserIdentity $userIdentity User who modified the stabilisation settings
	 * @param string $reason Reason given for the modification
	 */
	public function onFlaggedRevsStabilitySettingsChanged( $title, $newStabilitySettings, $userIdentity, $reason );
}
