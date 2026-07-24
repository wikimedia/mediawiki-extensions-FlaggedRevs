<?php

namespace MediaWiki\Extension\FlaggedRevs\Backend;

use MediaWiki\Extension\Notifications\AttributeManager;
use MediaWiki\Extension\Notifications\Hooks\BeforeCreateEchoEventHook;
use MediaWiki\Extension\Notifications\UserLocator;

class EchoHooks implements
	BeforeCreateEchoEventHook
{
	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforeCreateEchoEvent
	 *
	 * This should go once we can remove all Echo-specific code for reverts,
	 * see: T153570
	 *
	 * @inheritDoc
	 */
	public function onBeforeCreateEchoEvent(
		array &$notifications,
		array &$notificationCategories,
		array &$notificationIcons
	) {
		// Override default handlers
		// FlaggedRevs uses a different 'extra' property to pass multiple reverted users
		$notifications['reverted'][AttributeManager::ATTR_LOCATORS][] = [
			[ UserLocator::class, 'locateFromEventExtra' ],
			[ 'reverted-users-ids' ]
		];
	}
}
