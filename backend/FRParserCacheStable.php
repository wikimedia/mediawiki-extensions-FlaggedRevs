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
	 * @param Article $article
	 * @param string $hash
	 * @return mixed|string
	 */
	protected function getParserOutputKey( $article, $hash ) {
		$key = parent::getParserOutputKey( $article, $hash ); // call super!
		return str_replace( ':pcache:', ':stable-pcache:', $key );
	}

	/**
	 * Like ParserCache::getOptionsKey() with stable-pcache instead of pcache
	 * @param Article $article
	 * @return mixed|string
	 */
	protected function getOptionsKey( $article ) {
		$key = parent::getOptionsKey( $article ); // call super!
		return str_replace( ':pcache:', ':stable-pcache:', $key );
	}
}
