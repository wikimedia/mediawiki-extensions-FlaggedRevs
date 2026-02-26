<?php

declare( strict_types=1 );
namespace MediaWiki\Extension\FlaggedRevs\Backend;

use MediaWiki\Parser\ParserCache;
use MediaWiki\Parser\ParserCacheFactory;
use MediaWiki\Parser\ParserOptions;

class FlaggedRevsParserCacheFactory {
	/**
	 * @internal Only for use by ServiceWiring.php or when locating the service, in which case its value
	 * is stable to use.
	 */
	public const SERVICE_NAME = 'FlaggedRevsParserCacheFactory';
	/**
	 * The name of the ParserCache to use for stable revisions caching.
	 *
	 * @note This name is used as a part of the ParserCache key, so
	 * changing it will invalidate the parser cache for stable revisions.
	 * @internal
	 */
	public const STABLE_PARSER_CACHE_NAME = 'stable-pcache';

	/** @internal */
	public const STABLE_PARSOID_PARSER_CACHE_NAME = 'stable-parsoid-pcache';

	public function __construct(
		private readonly ParserCacheFactory $parserCacheFactory,
	) {
	}

	public function getParserCache( ParserOptions $pOpts ): ParserCache {
		$cacheName = $pOpts->getUseParsoid() ? self::STABLE_PARSOID_PARSER_CACHE_NAME : self::STABLE_PARSER_CACHE_NAME;
		return $this->parserCacheFactory->getParserCache( $cacheName );
	}
}
