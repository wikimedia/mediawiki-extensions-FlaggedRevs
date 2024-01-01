<?php

use MediaWiki\MediaWikiServices;

return [

	'FlaggedRevsParserCache' => static function ( MediaWikiServices $services ) {
		return new FlaggedRevsParserCache(
			$services
				->getParserCacheFactory()
				->getParserCache( FlaggedRevs::PARSER_CACHE_NAME )
		);
	},
	'FlaggedRevsParsoidParserCache' => static function ( MediaWikiServices $services ) {
		return new FlaggedRevsParserCache(
			$services
				->getParserCacheFactory()
				->getParserCache( FlaggedRevs::PARSOID_PARSER_CACHE_NAME )
		);
	},

];
