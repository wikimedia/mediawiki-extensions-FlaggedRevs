<?php

use MediaWiki\Page\PageRecord;
use Psr\Log\LoggerInterface;

/**
 * Encapsulate FlaggedRevs's ParserCache
 *
 * TODO: Refactor to make testable
 */
class FlaggedRevsParserCache {

	/** @var LoggerInterface */
	private $logger;

	/** @var ParserCache */
	private $stableParserCache;

	/**
	 * @param ParserCache $stableParserCache
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		ParserCache $stableParserCache,
		LoggerInterface $logger
	) {
		$this->stableParserCache = $stableParserCache;
		$this->logger = $logger;
	}

	/**
	 * Fetch from the cache.
	 *
	 * @param PageRecord $page Identifies the page to fetch.
	 * @param ParserOptions $parserOptions Encodes the audience, language, etc.
	 * @return ParserOutput|false
	 */
	public function get( PageRecord $page, ParserOptions $parserOptions ) {
		$this->logger->debug( __METHOD__ );
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
		$this->logger->debug( __METHOD__ );
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
		$this->logger->debug( __METHOD__ );
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
