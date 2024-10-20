<?php

use MediaWiki\Page\PageRecord;
use MediaWiki\Parser\ParserCache;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\ParserOutput;

/**
 * Encapsulate FlaggedRevs's ParserCache
 */
class FlaggedRevsParserCache {

	/** @var ParserCache */
	private $stableParserCache;

	/**
	 * Instantiate this class using one of:
	 * * $services->getService( 'FlaggedRevsParserCache' )
	 * * $services->getService( 'FlaggedRevsParsoidParserCache' )
	 *
	 * @param ParserCache $stableParserCache
	 */
	public function __construct( ParserCache $stableParserCache ) {
		$this->stableParserCache = $stableParserCache;
	}

	/**
	 * Fetch from the cache.
	 *
	 * @param PageRecord $page Identifies the page to fetch.
	 * @param ParserOptions $parserOptions Encodes the audience, language, etc.
	 * @return ParserOutput|false
	 */
	public function get( PageRecord $page, ParserOptions $parserOptions ) {
		return $this->stableParserCache->get( $page, $parserOptions );
	}

	/**
	 * Fetch from the cache, allowing stale content.
	 *
	 * @param PageRecord $page Identifies the page to fetch.
	 * @param ParserOptions $parserOptions Encodes the audience, language, etc.
	 * @return ParserOutput|false
	 */
	public function getDirty( PageRecord $page, ParserOptions $parserOptions ) {
		return $this->stableParserCache->getDirty( $page, $parserOptions );
	}

	/**
	 * Store to the cache.
	 *
	 * @param ParserOutput $output Parsed content to store.
	 * @param PageRecord $page Identifies the page that was parsed.
	 * @param ParserOptions $parserOptions Encodes the audience, language, etc.
	 */
	public function save( ParserOutput $output, PageRecord $page,
		ParserOptions $parserOptions
	): void {
		$this->stableParserCache->save( $output, $page, $parserOptions );
	}

	/**
	 * Return a key that will be unique to this page and the requested parser options.
	 *
	 * @param PageRecord $page Identifies the page that was parsed.
	 * @param ParserOptions $parserOptions Encodes the audience, language, etc.
	 * @return string
	 */
	public function makeKey( PageRecord $page, ParserOptions $parserOptions ): string {
		$parserCacheMetadata = $this->stableParserCache->getMetadata( $page );
		return $this->stableParserCache->makeParserOutputKey(
			$page,
			$parserOptions,
			$parserCacheMetadata ? $parserCacheMetadata->getUsedOptions() : null
		);
	}
}
