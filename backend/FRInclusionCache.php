<?php

use MediaWiki\MediaWikiServices;

/**
 * Class containing draft template/file version usage for
 * Parser based on the source text of a revision ID & title.
 */
class FRInclusionCache {
	/**
	 * Get template and image versions from parsing a revision
	 * @param WikiPage $wikiPage
	 * @param Revision $rev
	 * @param User $user
	 * @param string $regen use 'regen' to force regeneration
	 * @return array [ templateIds, fileSHA1Keys ]
	 * templateIds like ParserOutput->mTemplateIds
	 * fileSHA1Keys like ParserOutput->mImageTimeKeys
	 */
	public static function getRevIncludes(
		WikiPage $wikiPage, Revision $rev, User $user, $regen = ''
	) {
		global $wgParserCacheExpireTime;

		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$key = self::getCacheKey( $cache, $wikiPage->getTitle(), $rev->getId() );

		$callback = function () use ( $wikiPage, $rev, $user ) {
			$pOut = false;
			if ( $rev->isCurrent() ) {
				$parserCache = MediaWikiServices::getInstance()->getParserCache();
				# Try current version parser cache for this user...
				$pOut = $parserCache->get( $wikiPage, $wikiPage->makeParserOptions( $user ) );
				if ( $pOut == false ) {
					# Try current version parser cache for the revision author...
					$optsUser = $rev->getUser()
						? User::newFromId( $rev->getUser() )
						: 'canonical';
					$pOut = $parserCache->get( $wikiPage, $wikiPage->makeParserOptions( $optsUser ) );
				}
			}

			if ( $pOut == false ) {
				$content = $rev->getContent( Revision::RAW );
				if ( !$content ) {
					// Just for extra sanity
					$pOut = new ParserOutput();
				} else {
					$pOut = $content->getParserOutput(
						$wikiPage->getTitle(),
						$rev->getId(),
						ParserOptions::newFromUser( $user )
					);
				}
			}

			# Get the template/file versions used...
			return [ $pOut->getTemplateIds(), $pOut->getFileSearchOptions() ];
		};

		if ( $regen === 'regen' ) {
			$versions = $callback(); // skip cache
		} else {
			if ( $rev->isCurrent() ) {
				// Check cache entry against page_touched
				$touchedCallback = function () use ( $wikiPage ) {
					return wfTimestampOrNull( TS_UNIX, $wikiPage->getTouched() );
				};
			} else {
				// Old revs won't always be invalidated with template/file changes.
				// Also, we don't care if page_touched changed due to a direct edit.
				$touchedCallback = function ( $oldValue ) {
					// Sanity check that the cache is reasonably up to date
					list( $templates, $files ) = $oldValue;
					if ( self::templatesStale( $templates ) || self::filesStale( $files ) ) {
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

	protected static function templatesStale( array $tVersions ) {
		# Do a link batch query for page_latest...
		$lb = new LinkBatch();
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
	 * @param array[] $fVersions
	 * @return bool
	 */
	protected static function filesStale( array $fVersions ) {
		$repoGroup = MediaWikiServices::getInstance()->getRepoGroup();
		# Check if any of these files have a newer version
		foreach ( $fVersions as $name => $timeAndSHA1 ) {
			$file = $repoGroup->findFile( $name );
			if ( $file ) {
				if ( $file->getTimestamp() != $timeAndSHA1['time'] ) {
					return true;
				}
			} else {
				if ( $timeAndSHA1['time'] ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Set the cache of template and image versions from parsing a revision
	 * @param Title $title
	 * @param int $revId
	 * @param ParserOutput $pOut
	 */
	public static function setRevIncludes( Title $title, $revId, ParserOutput $pOut ) {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$key = self::getCacheKey( $cache, $title, $revId );
		# Get the template/file versions used...
		$versions = [ $pOut->getTemplateIds(), $pOut->getFileSearchOptions() ];
		# Save to cache (check cache expiry for dynamic elements)...
		$cache->set( $key, $versions, $pOut->getCacheExpiry() );
	}

	/**
	 * @param WANObjectCache $cache
	 * @param Title $title
	 * @param int $revId
	 * @return string
	 */
	protected static function getCacheKey( WANObjectCache $cache, Title $title, $revId ) {
		$hash = md5( $title->getPrefixedDBkey() );

		return $cache->makeKey( 'flaggedrevs-inclusions', $revId, $hash );
	}
}
