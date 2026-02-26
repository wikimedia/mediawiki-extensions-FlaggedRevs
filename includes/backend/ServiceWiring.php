<?php

use MediaWiki\Extension\FlaggedRevs\Backend\FlaggedRevsParserCacheFactory;
use MediaWiki\MediaWikiServices;

/** @phpcs-require-sorted-array */
return [
	FlaggedRevsParserCacheFactory::SERVICE_NAME =>
		static function ( MediaWikiServices $services ): FlaggedRevsParserCacheFactory {
			return new FlaggedRevsParserCacheFactory(
				$services->getParserCacheFactory(),
				$services->getMainConfig(),
			);
		},
];
