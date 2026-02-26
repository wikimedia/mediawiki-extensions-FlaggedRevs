<?php

use MediaWiki\Extension\FlaggedRevs\Backend\FlaggedRevsParserCacheFactory;
use MediaWiki\MediaWikiServices;

/** @phpcs-require-sorted-array */
return [
	'FlaggedRevsParserCache' => static function ( MediaWikiServices $services ): FlaggedRevsParserCache {
		return new FlaggedRevsParserCache(
			$services
				->getParserCacheFactory()
				->getParserCache( FlaggedRevsParserCacheFactory::STABLE_PARSER_CACHE_NAME )
		);
	},

	FlaggedRevsParserCacheFactory::SERVICE_NAME =>
		static function ( MediaWikiServices $services ): FlaggedRevsParserCacheFactory {
			return new FlaggedRevsParserCacheFactory(
				$services->getParserCacheFactory(),
			);
		},

	'FlaggedRevsParsoidParserCache' => static function ( MediaWikiServices $services ): FlaggedRevsParserCache {
		return new FlaggedRevsParserCache(
			$services
				->getParserCacheFactory()
				->getParserCache( FlaggedRevsParserCacheFactory::STABLE_PARSOID_PARSER_CACHE_NAME ),
		);
	},
];
