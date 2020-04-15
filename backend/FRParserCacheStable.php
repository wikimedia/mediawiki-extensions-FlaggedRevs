<?php
/**
 * Cache for stable version outputs of the PHP parser
 */
use MediaWiki\MediaWikiServices;

class FRParserCacheStable extends ParserCache {
	/**
	 * Get an instance of this object
	 * @return self
	 */
	public static function singleton() {
		static $instance;
		if ( !isset( $instance ) ) {
			global $wgCacheEpoch;
			$instance = new self(
				MediaWikiServices::getInstance()->getParserCache()->getCacheStorage(),
				$wgCacheEpoch
			);
		}
		return $instance;
	}

	/**
	 * Like ParserCache::getParserOutputKey() with stable-pcache instead of pcache
	 * @param WikiPage $wikiPage
	 * @param string $hash
	 * @return mixed|string
	 */
	protected function getParserOutputKey( WikiPage $wikiPage, $hash ) {
		$key = parent::getParserOutputKey( $wikiPage, $hash ); // call super!
		return str_replace( ':pcache:', ':stable-pcache:', $key );
	}

	/**
	 * Like ParserCache::getOptionsKey() with stable-pcache instead of pcache
	 * @param WikiPage $wikiPage
	 * @return mixed|string
	 */
	protected function getOptionsKey( WikiPage $wikiPage ) {
		$key = parent::getOptionsKey( $wikiPage ); // call super!
		return str_replace( ':pcache:', ':stable-pcache:', $key );
	}
}
