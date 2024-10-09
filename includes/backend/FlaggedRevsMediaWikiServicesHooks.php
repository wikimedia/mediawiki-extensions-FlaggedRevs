<?php
namespace MediaWiki\Extension\FlaggedRevs\Backend;

use FlaggedRevsSetup;
use MediaWiki\Hook\MediaWikiServicesHook;

/**
 * Initialize derived configuration variables on service container setup.
 */
class FlaggedRevsMediaWikiServicesHooks implements MediaWikiServicesHook {

	/**
	 * @inheritDoc
	 */
	public function onMediaWikiServices( $services ): void {
		( new FlaggedRevsSetup( $services->getMainConfig() ) )->doSetup();
	}
}
