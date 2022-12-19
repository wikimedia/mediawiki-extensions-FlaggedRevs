<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;

/**
 * Class containing draft template version usage for
 * Parser based on the source text of a revision ID & title.
 */
class FRInclusionCache {
	/**
	 * Get template versions from parsing a revision
	 * @param WikiPage $wikiPage
	 * @param RevisionRecord $revRecord
	 * @param User $user
	 * @param string $regen use 'regen' to force regeneration
	 * @return array[] [ templateIds ], where
	 *  - templateIds is an int[][] array, {@see ParserOutput::$mTemplateIds} or
	 *    {@see OutputPage::$mTemplateIds}
	 */
	public static function getRevIncludes(
		WikiPage $wikiPage,
		RevisionRecord $revRecord,
		User $user,
		$regen = ''
	) {
		global $wgParserCacheExpireTime;
		$services = MediaWikiServices::getInstance();
		$cache = $services->getMainWANObjectCache();
		$key = self::getCacheKey( $cache, $wikiPage->getTitle(), $revRecord->getId() );

		$callback = static function () use ( $wikiPage, $revRecord, $user, $services ) {
			$pOut = false;
			if ( $revRecord->isCurrent() ) {
				$parserCache = $services->getParserCache();
				# Try current version parser cache for this user...
				$pOut = $parserCache->get( $wikiPage, $wikiPage->makeParserOptions( $user ) );
				if ( $pOut == false ) {
					# Try current version parser cache for the revision author...
					$optsUser = $revRecord->getUser()
						? User::newFromIdentity( $revRecord->getUser() )
						: 'canonical';
					$pOut = $parserCache->get( $wikiPage, $wikiPage->makeParserOptions( $optsUser ) );
				}
			}

			if ( $pOut == false ) {
				$content = $revRecord->getContent( SlotRecord::MAIN, RevisionRecord::RAW );
				if ( !$content ) {
					// Just for extra sanity
					$pOut = new ParserOutput();
				} else {
					$contentRenderer = $services->getContentRenderer();
					$pOut = $contentRenderer->getParserOutput(
						$content,
						$wikiPage->getTitle(),
						$revRecord->getId(),
						ParserOptions::newFromUser( $user )
					);
				}
			}

			# Get the template versions used...
			return [ $pOut->getTemplateIds() ];
		};

		if ( $regen === 'regen' ) {
			$versions = $callback(); // skip cache
		} else {
			if ( $revRecord->isCurrent() ) {
				// Check cache entry against page_touched
				$touchedCallback = static function () use ( $wikiPage ) {
					return wfTimestampOrNull( TS_UNIX, $wikiPage->getTouched() );
				};
			} else {
				// Old revs won't always be invalidated with template/file changes.
				// Also, we don't care if page_touched changed due to a direct edit.
				$touchedCallback = function ( $oldValue ) {
					// Sanity check that the cache is reasonably up to date
					$templates = $oldValue[0];
					if ( self::templatesStale( $templates ) ) {
						// Treat value as if it just expired
						return time();
					}

					return null;
				};
			}
			$versions = $cache->getWithSetCallback(
				$key,
				$wgParserCacheExpireTime,
				$callback,
				[ 'touchedCallback' => $touchedCallback ]
			);
		}

		return $versions;
	}

	/**
	 * @param int[][] $tVersions
	 * @return bool
	 */
	private static function templatesStale( array $tVersions ) {
		# Do a link batch query for page_latest...
		$lb = MediaWikiServices::getInstance()->getLinkBatchFactory()->newLinkBatch();
		foreach ( $tVersions as $ns => $tmps ) {
			foreach ( $tmps as $dbKey => $revIdDraft ) {
				$lb->add( $ns, $dbKey );
			}
		}
		$lb->execute();
		# Check if any of these templates have a newer version
		foreach ( $tVersions as $ns => $tmps ) {
			foreach ( $tmps as $dbKey => $revIdDraft ) {
				$title = Title::makeTitle( $ns, $dbKey );
				if ( $revIdDraft != $title->getLatestRevID() ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Set the cache of template versions from parsing a revision
	 * @param Title $title
	 * @param int $revId
	 * @param ParserOutput $pOut
	 */
	public static function setRevIncludes( Title $title, $revId, ParserOutput $pOut ) {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$key = self::getCacheKey( $cache, $title, $revId );
		# Get the template versions used...
		$versions = [ $pOut->getTemplateIds() ];
		# Save to cache (check cache expiry for dynamic elements)...
		$cache->set( $key, $versions, $pOut->getCacheExpiry() );
	}

	/**
	 * @param WANObjectCache $cache
	 * @param Title $title
	 * @param int $revId
	 * @return string
	 */
	private static function getCacheKey( WANObjectCache $cache, Title $title, $revId ) {
		$hash = md5( $title->getPrefixedDBkey() );

		return $cache->makeKey( 'flaggedrevs-inclusions', $revId, $hash );
	}
}
