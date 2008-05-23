<?php

class FlaggedRevs {
	public static $dimensions = array();
	public static $articleLoaded = false;
	protected static $loaded = false;
	protected static $qualityVersions = false;
	protected static $pristineVersions = false;
	protected static $extStorage = false;
	protected static $allowComments = false;

	public static function load() {
		global $wgFlaggedRevTags, $wgFlaggedRevValues, $wgFlaggedRevsComments;
		if( self::$loaded ) {
			return true;
		}
		# Assume true, then set to false if needed
		if( !empty($wgFlaggedRevTags) ) {
			self::$qualityVersions = true;
		}
		foreach( $wgFlaggedRevTags as $tag => $minQL ) {
			$safeTag = htmlspecialchars($tag);
			if( strpos($tag,':') || strpos($tag,'\n') || $safeTag !== $tag ) {
				throw new MWException( 'FlaggedRevs given invalid tag name!' );
			} else if( intval($minQL) != $minQL ) {
				throw new MWException( 'FlaggedRevs given invalid tag value!' );
			}
			self::$dimensions[$tag] = array();
			for( $i=0; $i <= $wgFlaggedRevValues; $i++ ) {
				self::$dimensions[$tag][$i] = "{$tag}-{$i}";
			}
			if( $minQL > $wgFlaggedRevValues ) {
				self::$qualityVersions = false;
			}
		}
		global $wgFlaggedRevPristine;
		if( $wgFlaggedRevValues >= $wgFlaggedRevPristine ) {
			self::$pristineVersions = true;
		}
		global $wgFlaggedRevsExternalStore, $wgDefaultExternalStore;
		self::$extStorage = $wgFlaggedRevsExternalStore ? 
			$wgFlaggedRevsExternalStore : $wgDefaultExternalStore;

		self::$allowComments = (bool)$wgFlaggedRevsComments;

		self::$loaded = true;
	}
	
	################# Basic accessors #################
	
	/**
	 * Are quality versions enabled?
	 */
	public static function qualityVersions() {
		self::load();
		return self::$qualityVersions;
	}
	
	/**
	 * Are pristine versions enabled?
	 */
	public static function pristineVersions() {
		self::load();
		return self::$pristineVersions;
	}
	
	/**
	 * Get external storage array. Default to main storage.
	 */
	public static function getExternalStorage() {
		self::load();
		return self::$extStorage;
	}

	/**
	 * Should this be using a simple icon-based UI?
	 * Check the user's preferences first, using the site settings as the default.
	 */
	public static function useSimpleUI() {
		global $wgUser, $wgSimpleFlaggedRevsUI;

		return $wgUser->getOption( 'flaggedrevssimpleui', intval($wgSimpleFlaggedRevsUI) );
	}

	/**
	 * Should comments be allowed on pages and forms?
	 */
	public static function allowComments() {
		self::load();
		return self::$allowComments;
	}
	
	################# Parsing functions #################

	/**
	 * @param string $text
	 * @param Title $title
	 * @param integer $id, revision id
	 * @return array( string, array, array, bool, int )
	 * All included pages/arguments are expanded out
	 */
	public static function expandText( $text='', $title, $id ) {
		global $wgParser;
		# Make our hooks to trigger
		$wgParser->fr_isStable = true;
		$wgParser->fr_includesMatched = true;
		# Parse with default options
		$options = new ParserOptions();
		$options->setRemoveComments( true ); // Save some bandwidth ;)
		$outputText = $wgParser->preprocess( $text, $title, $options, $id );
		$expandedText = array( $outputText, $wgParser->mOutput->mTemplates, $wgParser->mOutput->mTemplateIds, 
			$wgParser->fr_includesMatched, $wgParser->mOutput->fr_newestTemplateID );
		# Done with parser!
		$wgParser->fr_isStable = false;
		$wgParser->fr_includesMatched = false;
		# Return data array
		return $expandedText;
	}

	/**
	 * Get the HTML output of a revision based on $text.
	 * If the text is being reparsed from fr_text (expanded text), 
	 * it should be specified...In such cases, the parser will not have 
	 * template ID data. We need to know this so we can just get the data from the DB.
	 * @param Article $article
	 * @param string $text
	 * @param int $id
	 * @param bool $reparsed (is this being reparsed from fr_text?)
	 * @return ParserOutput
	 */
	public static function parseStableText( $article, $text='', $id, $reparsed = true ) {
		global $wgParser, $wgUseStableTemplates;
		$title = $article->getTitle(); // avoid pass-by-reference error
		# Make our hooks to trigger
		$wgParser->fr_isStable = true;
		$wgParser->fr_includesMatched = true;
		# Don't show section-edit links, they can be old and misleading
		$options = self::makeParserOptions();
		#$options->setEditSection( $id == $title->getLatestRevID(GAID_FOR_UPDATE) );
		# Parse the new body, wikitext -> html
	   	$parserOut = $wgParser->parse( $text, $title, $options, true, true, $id );
		$parserOut->fr_includesMatched = $wgParser->fr_includesMatched;
	   	# Done with parser!
	   	$wgParser->fr_isStable = false;
		$wgParser->fr_includesMatched = false;
		# Do we need to set the template uses via DB?
		if( $reparsed && !$wgUseStableTemplates ) {
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select( array('flaggedtemplates','revision'), 
				array( 'ft_namespace', 'ft_title', 'ft_tmp_rev_id AS rev_id', 'rev_page AS page_id' ),
				array( 'ft_rev_id' => $id, 'rev_id = ft_rev_id' ),
				__METHOD__ );
			# Add template metadata to output
			$maxTempID = 0;
			while( $row = $res->fetchObject() ) {
				if( !isset($parserOut->mTemplates[$row->ft_namespace]) ) {
					$parserOut->mTemplates[$row->ft_namespace] = array();
				}
				$parserOut->mTemplates[$row->ft_namespace][$row->ft_title] = $row->page_id;

				if( !isset($parserOut->mTemplateIds[$row->ft_namespace]) ) {
					$parserOut->mTemplateIds[$row->ft_namespace] = array();
				}
				$parserOut->mTemplateIds[$row->ft_namespace][$row->ft_title] = $row->rev_id;
				if( $row->rev_id > $maxTempID ) {
					$maxTempID = $row->rev_id;
				}
			}
			$parserOut->fr_newestTemplateID = $maxTempID;
		}
	   	return $parserOut;
	}
	
	/**
	* Get standard parser options
	*/
	public static function makeParserOptions( $user = NULL ) {
		$options = $user ? ParserOptions::newFromUser( $user ) : new ParserOptions();
		# Show inclusion/loop reports
		$options->enableLimitReport();
		# Fix bad HTML
		$options->setTidy( true );
		return $options;
	}
	
	/**
	* @param Article $article
	* @return ParserOutput
	* Get the page cache for the top stable revision of an article
	*/
	public static function getPageCache( $article ) {
		global $wgUser, $parserMemc, $wgCacheEpoch;

		wfProfileIn( __METHOD__ );
		# Make sure it is valid
		if( !$article->getId() )
			return null;

		$parserCache = ParserCache::singleton();
		$key = self::getCacheKey( $parserCache, $article, $wgUser );
		# Get the cached HTML
		wfDebug( "Trying parser cache $key\n" );
		$value = $parserMemc->get( $key );
		if( is_object( $value ) ) {
			wfDebug( "Found.\n" );
			# Delete if article has changed since the cache was made
			$canCache = $article->checkTouched();
			$cacheTime = $value->getCacheTime();
			$touched = $article->mTouched;
			if( !$canCache || $value->expired( $touched ) ) {
				if( !$canCache ) {
					wfIncrStats( "pcache_miss_invalid" );
					wfDebug( "Invalid cached redirect, touched $touched, epoch $wgCacheEpoch, cached $cacheTime\n" );
				} else {
					wfIncrStats( "pcache_miss_expired" );
					wfDebug( "Key expired, touched $touched, epoch $wgCacheEpoch, cached $cacheTime\n" );
				}
				$parserMemc->delete( $key );
				$value = false;
			} else {
				if( isset( $value->mTimestamp ) ) {
					$article->mTimestamp = $value->mTimestamp;
				}
				wfIncrStats( "pcache_hit" );
			}
		} else {
			wfDebug( "Parser cache miss.\n" );
			wfIncrStats( "pcache_miss_absent" );
			$value = false;
		}

		wfProfileOut( __METHOD__ );

		return $value;
	}

	/**
	 * Like ParserCache::getKey() with stable-pcache instead of pcache
	 */
	public static function getCacheKey( $parserCache, $article, &$user ) {
		$key = $parserCache->getKey( $article, $user );
		$key = str_replace( ':pcache:', ':stable-pcache:', $key );
		return $key;
	}

	/**
	* @param Article $article
	* @param parerOutput $parserOut
	* Updates the stable cache of a page with the given $parserOut
	*/
	public static function updatePageCache( $article, $parserOut=null ) {
		global $wgUser, $parserMemc, $wgParserCacheExpireTime;
		# Make sure it is valid
		if( is_null($parserOut) )
			return false;

		$parserCache = ParserCache::singleton();
		$key = self::getCacheKey( $parserCache, $article, $wgUser );
		# Add cache mark to HTML
		$now = wfTimestampNow();
		$parserOut->setCacheTime( $now );
		# Save the timestamp so that we don't have to load the revision row on view
		$parserOut->mTimestamp = $article->getTimestamp();
		$parserOut->mText .= "\n<!-- Saved in stable version parser cache with key $key and timestamp $now -->";
		# Set expire time
		if( $parserOut->containsOldMagic() ){
			$expire = 3600; // 1 hour
		} else {
			$expire = $wgParserCacheExpireTime;
		}
		# Save to objectcache
		$parserMemc->set( $key, $parserOut, $expire );

		return true;
	}
	
	################# Synchronization and link update functions #################
	
	/**
	* @param FlaggedRevision $frev
	* @param Article $article
	* @param ParserOutput $stableOutput, will fetch if not given
	* @param ParserOutput $currentOutput, will fetch if not given
	* @return bool
	* See if a flagged revision is synced with the current
	*/	
	public static function flaggedRevIsSynced( $frev, $article, $stableOutput=null, $currentOutput=null ) {
		# Must be the same revision
		if( $frev->getRevId() != $article->getTitle()->getLatestRevID(GAID_FOR_UPDATE) ) {
			return false;
		}
		# Must have same file
		if( $article instanceof ImagePage && $article->getFile() ) {
			if( $frev->getFileTimestamp() != $article->getFile()->getTimestamp() ) {
				return false;
			}
		}
		global $wgMemc;
		# Try the cache. Uses format <page ID>-<UNIX timestamp>.
		$key = wfMemcKey( 'flaggedrevs', 'syncStatus', $article->getId(), $article->getTouched() );
		$syncvalue = $wgMemc->get($key);
		# Convert string value to boolean and return it
		if( $syncvalue ) {
			if( $syncvalue == "true" ) {
				return true;
			} else if( $syncvalue == "false" ) {
				return false;
			}
		}
		# If parseroutputs not given, fetch them...
		if( is_null($stableOutput) || !isset($stableOutput->fr_newestTemplateID) ) {
			# Get parsed stable version
			$stableOutput = self::getPageCache( $article );
			if( $stableOutput==false ) {
				$text = $frev->getTextForParse();
	   			$stableOutput = self::parseStableText( $article, $text, $frev->getRevId() );
	   			# Update the stable version cache
	   			self::updatePageCache( $article, $stableOutput );
	   		}
		}
		if( is_null($currentOutput) || !isset($currentOutput->fr_newestTemplateID) ) {
			global $wgUser, $wgParser;
			# Get parsed current version
			$parserCache = ParserCache::singleton();
			$currentOutput = $parserCache->get( $article, $wgUser );
			if( $currentOutput==false ) {
				$text = $article->getContent();
				$title = $article->getTitle();
				$options = self::makeParserOptions( $wgUser );
				$currentOutput = $wgParser->parse( $text, $title, $options );
				# Might as well save the cache while we're at it
				global $wgEnableParserCache;
				if( $wgEnableParserCache )
					$parserCache->save( $currentOutput, $article, $wgUser );
			}
		}
		# Only current of revisions of inclusions can be reviewed. Since the stable and current revisions
		# have the same text, the only thing that can make them different is updating a template or image.
		# If this is the case, the current revision will have a newer template or image version used somewhere. 
		if( $currentOutput->fr_newestImageTime > $stableOutput->fr_newestImageTime ) {
			$synced = false;
		} else if( $currentOutput->fr_newestTemplateID > $stableOutput->fr_newestTemplateID ) {
			$synced = false;
		} else {
			$synced = true;
		}
		# Save to cache. This will be updated whenever the page is re-parsed as well. This means
		# that MW can check a light-weight key first. Uses format <page ID>-<UNIX timestamp>.
		global $wgParserCacheExpireTime;
		$syncData = $synced ? "true" : "false";
		$wgMemc->set( $key, $syncData, $wgParserCacheExpireTime );

		return $synced;
	}
	
	/**
	 * @param Article $article
	 * @param int $from_rev
	 * @return int
	 * Get number of revs since a certain revision
	 */
	public static function getRevCountSince( $article, $from_rev ) {
		# Check if the count is zero by using $article->getLatest().
		# I don't trust using memcache and PHP for values like '0'
		# as it may confuse "expired" with "0". -aaron
		if( $article->getTitle()->getLatestRevID(GAID_FOR_UPDATE) == $from_rev ) {
			return 0;
		}
		global $wgMemc;
		# Try the cache
		$key = wfMemcKey( 'flaggedrevs', 'unreviewedrevs', $article->getId() );
		if( !$count = intval($wgMemc->get($key)) ) {
			$dbr = wfGetDB( DB_SLAVE );
			$count = $dbr->selectField( 'revision', 'COUNT(*)',
				array('rev_page' => $article->getId(), "rev_id > " . intval($from_rev) ),
				__METHOD__ );
			# Save to cache
			$wgMemc->set( $key, $count, 3600*24*7 );
		}
		return $count;
	}
	
 	/**
	* @param Article $article
	* @param Integer $rev_id, the stable version rev_id
	* @param mixed $latest, the latest rev ID (optional)
	* Updates the fp_stable and fp_reviewed fields
	*/
	public static function updateArticleOn( $article, $rev_id, $latest=NULL ) {
		global $wgMemc;
		wfProfileIn( __METHOD__ );

		$lastID = $latest ? $latest : $article->getTitle()->getLatestRevID(GAID_FOR_UPDATE);

		$dbw = wfGetDB( DB_MASTER );
		# Get the highest quality revision (not necessarily this one).
		$maxQuality = $dbw->selectField( array('flaggedrevs','revision'),
			'fr_quality',
			array( 'fr_page_id' => $article->getTitle()->getArticleID(),
				'rev_id = fr_rev_id',
				'rev_page = fr_page_id',
				'rev_deleted & '.Revision::DELETED_TEXT => 0 ),
			__METHOD__,
			array( 'ORDER BY' => 'fr_quality DESC', 'LIMIT' => 1 ) );
		$maxQuality = $maxQuality===false ? null : $maxQuality;
		# Alter table metadata
		$dbw->replace( 'flaggedpages',
			array( 'fp_page_id' ),
			array( 'fp_stable' => $rev_id,
				'fp_reviewed' => ($lastID == $rev_id) ? 1 : 0,
				'fp_quality' => $maxQuality,
				'fp_page_id' => $article->getId() ),
			__METHOD__ );
		# Update the cache
		$key = wfMemcKey( 'flaggedrevs', 'unreviewedrevs', $article->getId() );

		$count = $dbw->selectField( 'revision', 'COUNT(*)',
			array('rev_page' => $article->getId(), "rev_id > " . intval($rev_id) ),
			__METHOD__ );

		$wgMemc->set( $key, $count, 3600*24*7 );
		
		wfProfileOut( __METHOD__ );
		return true;
	}
	
	/**
	* Clears cache for a page when merges are done.
	* We may have lost the stable revision to another page.
	*/
	public static function articleLinksUpdate( $article ) {
		global $wgUser, $wgParser;
		# Update the links tables as the stable version may now be the default page...
		$parserCache = ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if( $poutput==false ) {
			$text = $article->getContent();
			$options = self::makeParserOptions( $wgUser );
			$poutput = $wgParser->parse($text, $article->getTitle(), $options);
			# Might as well save the cache while we're at it
			global $wgEnableParserCache;
			if( $wgEnableParserCache )
				$parserCache->save( $poutput, $article, $wgUser );
		}
		$u = new LinksUpdate( $article->getTitle(), $poutput );
		$u->doUpdate(); // this will trigger our hook to add stable links too...

		return true;
	}

	/**
	* Clears cache for a page when revisiondelete/undelete is used
	*/
	public static function titleLinksUpdate( $title ) {
		return self::articleLinksUpdate( new Article($title) );
	}
	
	################# Revision functions #################

	/**
	 * @param Title $title
	 * @param int $rev_id
	 * @param bool $getText, fetch fr_text and fr_flags too?
	 * @param bool $forUpdate, use master?
	 * @param int $page_id, optional page ID to use, will defer to $title if not given
	 * @returns mixed FlaggedRevision (null on failure)
	 * Will not return a revision if deleted
	 */
	public static function getFlaggedRev( $title, $rev_id, $getText=false, $forUpdate=false, $page_id=false ) {
		$columns = FlaggedRevision::selectFields();
		if( $getText ) {
			$columns[] = 'fr_text';
			$columns[] = 'fr_flags';
		}
		$db = $forUpdate ? wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		$flags = $forUpdate ? GAID_FOR_UPDATE : 0;
		$page_id = $page_id ? $page_id : $title->getArticleID( $flags );
		# Skip deleted revisions
		$row = $db->selectRow( array('flaggedrevs','revision'),
			$columns,
			array( 'fr_page_id' => $page_id,
				'fr_rev_id' => $rev_id,
				'rev_id = fr_rev_id',
				'rev_page = fr_page_id',
				'rev_deleted & '.Revision::DELETED_TEXT => 0 ),
			__METHOD__ );
		# Sorted from highest to lowest, so just take the first one if any
		if( $row ) {
			return new FlaggedRevision( $title, $row );
		}
		return null;
	}
	
	/**
	 * Get latest quality rev, if not, the latest reviewed one.
	 * @param Title $title, page title
	 * @param bool $getText, fetch fr_text and fr_flags too?
	 * @param bool $forUpdate, use master DB and avoid using fp_stable?
	 * @returns mixed FlaggedRevision (null on failure)
	 */
	public static function getStablePageRev( $title, $getText=false, $forUpdate=false ) {
		$columns = FlaggedRevision::selectFields();
		if( $getText ) {
			$columns[] = 'fr_text';
			$columns[] = 'fr_flags';
		}
		$row = null;
		# If we want the text, then get the text flags too
		if( !$forUpdate ) {
			$dbr = wfGetDB( DB_SLAVE );
			$row = $dbr->selectRow( array('flaggedpages','flaggedrevs'),
				$columns,
				array( 'fp_page_id' => $title->getArticleId(),
					'fr_page_id' => $title->getArticleId(),
					'fp_stable = fr_rev_id' ),
				__METHOD__  );
			if( !$row )
				return null;
		} else {
			# Get visiblity settings...
			$config = self::getPageVisibilitySettings( $title, $forUpdate );
			$dbw = wfGetDB( DB_MASTER );
			# Look for the latest pristine revision...
			if( self::pristineVersions() && $config['select'] != FLAGGED_VIS_LATEST ) {
				$prow = $dbw->selectRow( array('flaggedrevs','revision'),
					$columns,
					array( 'fr_page_id' => $title->getArticleID(),
						'fr_quality = 2',
						'rev_id = fr_rev_id',
						'rev_page = fr_page_id',
						'rev_deleted & '.Revision::DELETED_TEXT => 0),
					__METHOD__,
					array( 'ORDER BY' => 'fr_rev_id DESC') );
				# Looks like a plausible revision
				$row = $prow ? $prow : null;
			}
			# Look for the latest quality revision...
			if( self::qualityVersions() && $config['select'] != FLAGGED_VIS_LATEST ) {
				// If we found a pristine rev above, this one must be newer, unless
				// we specifically want pristine revs to have precedence...
				$newerClause = ($row && $config['select'] != FLAGGED_VIS_PRISTINE) ?
					"fr_rev_id > {$row->fr_rev_id}" : "1 = 1";
				$qrow = $dbw->selectRow( array('flaggedrevs','revision'),
					$columns,
					array( 'fr_page_id' => $title->getArticleID(),
						'fr_quality = 1',
						$newerClause,
						'rev_id = fr_rev_id',
						'rev_page = fr_page_id',
						'rev_deleted & '.Revision::DELETED_TEXT => 0),
					__METHOD__,
					array( 'ORDER BY' => 'fr_rev_id DESC') );
				$row = $qrow ? $qrow : $row;
			}
			# Do we have one? If not, try the latest reviewed revision...
			if( !$row ) {
				$row = $dbw->selectRow( array('flaggedrevs','revision'),
					$columns,
					array( 'fr_page_id' => $title->getArticleID(),
						'rev_id = fr_rev_id',
						'rev_page = fr_page_id',
						'rev_deleted & '.Revision::DELETED_TEXT => 0),
					__METHOD__,
					array( 'ORDER BY' => 'fr_rev_id DESC' ) );
				if( !$row )
					return null;
			}
		}
		return new FlaggedRevision( $title, $row );
	}
	
	/**
	 * Get flags for a revision
	 * @param Title $title
	 * @param int $rev_id
	 * @return Array
	*/
	public static function getRevisionTags( $title, $rev_id ) {
		$dbr = wfGetDB( DB_SLAVE );
		$tags = $dbr->selectField( 'flaggedrevs', 'fr_tags',
			array( 'fr_rev_id' => $rev_id,
				'fr_page_id' => $title->getArticleId() ),
			__METHOD__ );
		if( !$tags )
			return false;

		return FlaggedRevision::expandRevisionTags( strval($tags) );
	}
	
	/**
	 * @param Title $title
	 * @param int $rev_id
	 * @param $flags, GAID_FOR_UPDATE
	 * @returns mixed (int or false)
	 * Get quality of a revision
	 */
	public static function getRevQuality( $title, $rev_id, $flags=0 ) {
		$db = ($flags & GAID_FOR_UPDATE) ? wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		$quality = $db->selectField( 'flaggedrevs', 
			'fr_quality',
			array( 'fr_page_id' => $title->getArticleID( $flags ),
				'fr_rev_id' => $rev_id ),
			__METHOD__,
			array( 'FORCE INDEX' => 'PRIMARY' )
		);
		return $quality;
	}
	
	/**
	 * @param Title $title
	 * @param int $rev_id
	 * @param $flags, GAID_FOR_UPDATE
	 * @returns bool
	 * Useful for quickly pinging to see if a revision is flagged
	 */
	public static function revIsFlagged( $title, $rev_id, $flags=0 ) {
		$quality = self::getRevQuality( $title, $rev_id, $flags );
		return ($quality !== false);
	}
	
	/**
	 * Get the "prime" flagged revision of a page
	 * @param Article $article
	 * @returns mixed (integer/false)
	 * Will not return a revision if deleted
	 */
	public static function getPrimeFlaggedRevId( $article ) {
		$dbr = wfGetDB( DB_SLAVE );
		# Get the highest quality revision (not necessarily this one).
		$oldid = $dbr->selectField( array('flaggedrevs','revision'),
			'fr_rev_id',
			array( 'fr_page_id' => $article->getId(),
				'rev_page = fr_page_id',
				'rev_id = fr_rev_id'),
			__METHOD__,
			array( 'ORDER BY' => 'fr_quality DESC, fr_rev_id DESC',
				'USE INDEX' => array('flaggedrevs' => 'page_qal_rev','revision' => 'PRIMARY') )
		);
		return $oldid;
	}
	
	################# Page configuration functions #################

	/**
	 * Get visiblity restrictions on page
	 * @param Title $title, page title
	 * @param bool $forUpdate, use master DB?
	 * @returns Array (select,override)
	*/
	public static function getPageVisibilitySettings( $title, $forUpdate=false ) {
		$db = $forUpdate ? wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		$row = $db->selectRow( 'flaggedpage_config',
			array( 'fpc_select', 'fpc_override', 'fpc_expiry' ),
			array( 'fpc_page_id' => $title->getArticleID() ),
			__METHOD__ );

		if( $row ) {
			$now = wfTimestampNow();
			# This code should be refactored, now that it's being used more generally.
			$expiry = Block::decodeExpiry( $row->fpc_expiry );
			# Only apply the settigns if they haven't expired
			if( !$expiry || $expiry < $now ) {
				$row = null;
				self::purgeExpiredConfigurations();
			}
		}

		if( !$row ) {
			global $wgFlaggedRevsOverride, $wgFlaggedRevsPrecedence;
			# Keep this consistent across settings. 1 -> override, 0 -> don't
			$override = $wgFlaggedRevsOverride ? 1 : 0;
			# Keep this consistent across settings. 0 -> precedence, 0 -> none
			$select = $wgFlaggedRevsPrecedence ? FLAGGED_VIS_NORMAL : FLAGGED_VIS_LATEST;
			return array('select' => $select, 'override' => $override, 'expiry' => 'infinity');
		}

		return array('select' => $row->fpc_select, 'override' => $row->fpc_override, 'expiry' => $row->fpc_expiry);
	}
	
	/**
	 * Purge expired restrictions from the flaggedpage_config table
	 */
	public static function purgeExpiredConfigurations() {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'flaggedpage_config',
			array( 'fpc_expiry < ' . $dbw->addQuotes( $dbw->timestamp() ) ),
			__METHOD__ );
	}
	
	################# Other utility functions #################

	/**
	 * @param Title $title
	 * @return bool, is $title the main page?
	 */
	public static function isMainPage( $title ) {
		return $title->equals( Title::newMainPage() );
	}

	/**
	* @param Array $flags
	* @return bool, is this revision at quality condition?
	*/
	public static function isQuality( $flags ) {
		global $wgFlaggedRevTags;

		if( empty($flags) )
			return false;

		foreach( $wgFlaggedRevTags as $f => $v ) {
			if( !isset($flags[$f]) || $v > $flags[$f] )
				return false;
		}
		
		return true;
	}

	/**
	* @param Array $flags
	* @return bool, is this revision at optimal condition?
	*/
	public static function isPristine( $flags ) {
		global $wgFlaggedRevTags, $wgFlaggedRevPristine;

		if( empty($flags) )
			return false;

		foreach( $wgFlaggedRevTags as $f => $v ) {
			if( !isset($flags[$f]) || $flags[$f] < $wgFlaggedRevPristine )
				return false;
		}
		
		return true;
	}
	
	/**
	* Is this page in reviewable namespace?
	* @param Title, $title
	* @return bool
	*/
	public static function isPageReviewable( $title ) {
		global $wgFlaggedRevsNamespaces;
		# FIXME: Treat NS_MEDIA as NS_IMAGE
		$ns = ( $title->getNamespace() == NS_MEDIA ) ? NS_IMAGE : $title->getNamespace();
		return ( in_array($ns,$wgFlaggedRevsNamespaces) && !$title->isTalkPage() );
	}
	
	/**
	* Is this page in patrolable namespace?
	* @param Title, $title
	* @return bool
	*/
	public static function isPagePatrollable( $title ) {
		global $wgFlaggedRevsPatrolNamespaces;
		# No collisions!
		if( self::isPageReviewable($title) ) {
			return false;
		}
		# FIXME: Treat NS_MEDIA as NS_IMAGE
		$ns = ( $title->getNamespace() == NS_MEDIA ) ? NS_IMAGE : $title->getNamespace();
		return ( in_array($ns,$wgFlaggedRevsPatrolNamespaces) && !$title->isTalkPage() );
	}
	
	/**
	 * Make stable version link and return the css
	 * @param Title $title
	 * @param Row $row, from history page
	 * @returns array (string,string)
	 */
	public static function markHistoryRow( $title, $row, $skin ) {
		if( isset($row->fr_quality) ) {
			$css = FlaggedRevsXML::getQualityColor( $row->fr_quality );
			$user = User::whois( $row->fr_user );
			$msg = ($row->fr_quality >= 1) ? 'hist-quality-user' : 'hist-stable-user';
			$st = $title->getPrefixedDBkey();
			$link = "<span class='plainlinks'>".wfMsgExt($msg,array('parseinline'),$st,$row->rev_id,$user)."</span>";
		} else {
			return array("","");
		}
		return array($link,$css);
	}
	
	/**
	 * Get JS script params for onloading
	 */
	public static function getJSParams() {
		# Param to pass to JS function to know if tags are at quality level
		global $wgFlaggedRevTags;
		$params = array( 'tags' => (object)$wgFlaggedRevTags );
		return Xml::encodeJsVar( (object)$params );
	}
	
   	/**
	* Get params for a user
	* @param User $user
	*/
	public static function getUserParams( $user ) {
		$dbw = wfGetDB( DB_MASTER );
		$row = $dbw->selectRow( 'flaggedrevs_promote', 'frp_user_params',
			array( 'frp_user_id' => $user->getId() ),
			__METHOD__ );
		# Parse params
		$params = array();
		if( $row ) {
			$flatPars = explode( "\n", trim($row->frp_user_params) );
			foreach( $flatPars as $pair ) {
				$m = explode( '=', trim($pair), 2 );
				$key = $m[0];
				$value = isset($m[1]) ? $m[1] : null;
				$params[$key] = $value;
			}
		}
		return $params;
	}
	
   	/**
	* Save params for a user
	* @param User $user
	* @param Array $params
	*/
	public static function saveUserParams( $user, $params ) {
		$flatParams = '';
		foreach( $params as $key => $value ) {
			$flatParams .= "{$key}={$value}\n";
		}
		$dbw = wfGetDB( DB_MASTER );
		$row = $dbw->replace( 'flaggedrevs_promote', 
			array( 'frp_user_id' ),
			array( 'frp_user_id' => $user->getId(), 
				'frp_user_params' => trim($flatParams) ),
			__METHOD__ );

		return ( $dbw->affectedRows() > 0 );
	}
	
	################# Auto-review function #################

	/**
	* Automatically review an edit and add a log entry in the review log.
	* LinksUpdate was already called via edit operations, so the page
	* fields will be up to date. This updates the stable version.
	*/
	public static function autoReviewEdit( $article, $user, $text, $rev, $flags, $patrol = true ) {
		global $wgParser, $wgFlaggedRevsAutoReview;

		wfProfileIn( __METHOD__ );

		$quality = 0;
		if( self::isQuality($flags) ) {
			$quality = self::isPristine($flags) ? 2 : 1;
		}
		$tmpset = $imgset = array();
		$poutput = false;

		# Use master to avoid lag issues.
		$latestID = $article->getTitle()->getLatestRevID(GAID_FOR_UPDATE);
		$latestID = $latestID ? $latestID : $rev->getId(); // new pages, page row not added yet

		$title = $article->getTitle();
		# Rev ID is not put into parser on edit, so do the same here.
		# Also, a second parse would be triggered otherwise.
		$parseId = ($rev->getId() == $latestID) ? null : $rev->getId();
		# Parse the revision HTML output
		$editInfo = $article->prepareTextForEdit( $text, $parseId );
		$poutput = $editInfo->output;

		# NS:title -> rev ID mapping
		foreach( $poutput->mTemplateIds as $namespace => $titleAndID ) {
			foreach( $titleAndID as $dbkey => $id ) {
				$tmpset[] = array(
					'ft_rev_id' => $rev->getId(),
					'ft_namespace' => $namespace,
					'ft_title' => $dbkey,
					'ft_tmp_rev_id' => $id
				);
			}
		}
		# Image -> timestamp mapping
		foreach( $poutput->fr_ImageSHA1Keys as $dbkey => $timeAndSHA1 ) {
			foreach( $timeAndSHA1 as $time => $sha1 ) {
				$imgset[] = array(
					'fi_rev_id' => $rev->getId(),
					'fi_name' => $dbkey,
					'fi_img_timestamp' => $time,
					'fi_img_sha1' => $sha1
				);
			}
		}

		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin();
		# Update our versioning pointers
		if( !empty( $tmpset ) ) {
			$dbw->replace( 'flaggedtemplates',
				array( array('ft_rev_id','ft_namespace','ft_title') ), $tmpset,
				__METHOD__ );
		}
		if( !empty( $imgset ) ) {
			$dbw->replace( 'flaggedimages',
				array( array('fi_rev_id','fi_name') ), $imgset,
				__METHOD__ );
		}
		# Get the page text and resolve all templates
		list($fulltext,$templateIDs,$complete,$maxID) = self::expandText( $text, $article->getTitle(), $rev->getId() );

		# Compress $fulltext, passed by reference
		$textFlags = FlaggedRevision::compressText( $fulltext );

		# Write to external storage if required
		$storage = self::getExternalStorage();
		if( $storage ) {
			if( is_array($storage) ) {
				# Distribute storage across multiple clusters
				$store = $storage[mt_rand(0, count( $storage ) - 1)];
			} else {
				$store = $storage;
			}
			# Store and get the URL
			$fulltext = ExternalStore::insert( $store, $fulltext );
			if( !$fulltext ) {
				# This should only happen in the case of a configuration error, where the external store is not valid
				wfProfileOut( __METHOD__ );
				throw new MWException( "Unable to store text to external storage $store" );
			}
			if( $textFlags ) {
				$textFlags .= ',';
			}
			$textFlags .= 'external';
		}

		# If this is an image page, store corresponding file info
		$fileData = array();
		if( $title->getNamespace() == NS_IMAGE && $file = wfFindFile($title) ) {
			$fileData['name'] = $title->getDBkey();
			$fileData['timestamp'] = $file->getTimestamp();
			$fileData['sha1'] = $file->getSha1();
		}

		# Our review entry
		$revisionset = array(
			'fr_page_id'       => $rev->getPage(),
			'fr_rev_id'	       => $rev->getId(),
			'fr_user'	       => $user->getId(),
			'fr_timestamp'     => $dbw->timestamp( wfTimestampNow() ),
			'fr_comment'       => "",
			'fr_quality'       => $quality,
			'fr_tags'	       => FlaggedRevision::flattenRevisionTags( $flags ),
			'fr_text'	       => $fulltext, # Store expanded text for speed
			'fr_flags'	       => $textFlags,
			'fr_img_name'      => $fileData ? $fileData['name'] : null,
			'fr_img_timestamp' => $fileData ? $fileData['timestamp'] : null,
			'fr_img_sha1'      => $fileData ? $fileData['sha1'] : null
		);
		# Update flagged revisions table
		$dbw->replace( 'flaggedrevs',
			array( array('fr_page_id','fr_rev_id') ), $revisionset,
			__METHOD__ );
		# Mark as patrolled
		if( $patrol ) {
			$dbw->update( 'recentchanges',
				array( 'rc_patrolled' => 1 ),
				array( 'rc_this_oldid' => $rev->getId(),
					'rc_user_text' => $rev->getRawUserText(),
					'rc_timestamp' => $dbw->timestamp( $rev->getTimestamp() ) ),
				__METHOD__,
				array( 'LIMIT' => 1 ) );
		}
		# Done!
		$dbw->commit();

		# Update the article review log
		RevisionReview::updateLog( $title, $flags, array(), wfMsgForContent('revreview-auto'), $rev->getID(), true, true );

		# If we know that this is now the new stable version 
		# (which it probably is), save it to the cache...
		$sv = self::getStablePageRev( $article->getTitle(), false, true );
		if( $sv && $sv->getRevId() == $rev->getId() ) {
			# Update stable cache
			self::updatePageCache( $article, $poutput );
			# Update page fields
			self::updateArticleOn( $article, $rev->getId(), $rev->getId() );
			# Purge squid for this page only
			$article->getTitle()->purgeSquid();
		}
		
		wfProfileOut( __METHOD__ );

		return true;
	}
	
	################# Hooked functions #################
	
	/**
	* Remove 'patrol' and 'autopatrol' rights. Reviewing revisions will patrol them as well.
	*/
	public static function stripPatrolRights( $user, &$rights ) {
		# Use only our extension mechanisms
		foreach( $rights as $n => $right ) {
			if( $right == 'patrol' || $right == 'autopatrol' ) {
				unset($rights[$n]);
			}
		}
		return true;
	}

	/**
	* Add FlaggedRevs css/js.
	*/
	public static function injectStyleAndJS() {
		global $wgOut, $wgJsMimeType;
		# Don't double-load
		if ( $wgOut->hasHeadItem( 'FlaggedRevs' ) ) {
			return true;
		}
		if ( !$wgOut->isArticleRelated() ) {
			return true;
		}

		global $wgArticle, $wgScriptPath, $wgFlaggedRevStyleVersion, $wgJsMimeType, $wgFlaggedRevsStylePath;

		$flaggedArticle = FlaggedArticle::getInstance( $wgArticle );
		$stylePath = str_replace( '$wgScriptPath', $wgScriptPath, $wgFlaggedRevsStylePath );
		$JSparams = self::getJSParams();
		$frev = $flaggedArticle->getStableRev( true );
		$stableId = $frev ? $frev->getRevId() : 0;
		$encCssFile = htmlspecialchars( "$stylePath/flaggedrevs.css?$wgFlaggedRevStyleVersion" );
		$encJsFile = htmlspecialchars( "$stylePath/flaggedrevs.js?$wgFlaggedRevStyleVersion" );
		$head = <<<EOT
<link rel="stylesheet" type="text/css" media="screen, projection" href="$encCssFile"/>
<script type="$wgJsMimeType">
var wgFlaggedRevsParams = $JSparams;
var wgStableRevisionId = $stableId;
</script>
<script type="$wgJsMimeType" src="$encJsFile"></script>

EOT;
		$wgOut->addHeadItem( 'FlaggedRevs', $head );
		return true;
	}
	
	/**
	* Add FlaggedRevs css for relevant special pages.
	*/
	public static function InjectStyleForSpecial() {
		global $wgTitle, $wgOut;
		$spPages = array();
		$spPages[] = SpecialPage::getTitleFor( 'UnreviewedPages' );
		$spPages[] = SpecialPage::getTitleFor( 'OldReviewedPages' );
		foreach( $spPages as $n => $title ) {
			if( $wgTitle->equals( $title ) ) {
				# UI CSS
				$wgOut->addLink( array(
					'rel'	=> 'stylesheet',
					'type'	=> 'text/css',
					'media'	=> 'screen, projection',
					'href'	=> FLAGGED_CSS,
				) );
				break;
			}
		}
		return true;
	}

	/**
	* Update flaggedrevs table on revision restore
	*/
	public static function updateFromRestore( $title, $revision, $oldPageID ) {
		$dbw = wfGetDB( DB_MASTER );
		# Some revisions may have had null rev_id values stored when deleted.
		# This hook is called after insertOn() however, in which case it is set
		# as a new one.
		$dbw->update( 'flaggedrevs',
			array( 'fr_page_id' => $revision->getPage() ),
			array( 'fr_page_id' => $oldPageID,
				'fr_rev_id' => $revision->getID() ),
			__METHOD__ );

		return true;
	}

	/**
	* Update flaggedrevs table on article history merge
	*/
	public static function updateFromMerge( $sourceTitle, $destTitle ) {
		wfProfileIn( __METHOD__ );
	
		$oldPageID = $sourceTitle->getArticleID();
		$newPageID = $destTitle->getArticleID();
		# Get flagged revisions from old page id that point to destination page
		$dbw = wfGetDB( DB_MASTER );
		$result = $dbw->select( array('flaggedrevs','revision'),
			array( 'fr_rev_id' ),
			array( 'fr_page_id' => $oldPageID,
				'fr_rev_id = rev_id',
				'rev_page' => $newPageID ),
			__METHOD__ );
		# Update these rows
		$revIDs = array();
		while( $row = $dbw->fetchObject($result) ) {
			$revIDs[] = $row->fr_rev_id;
		}
		if( !empty($revIDs) ) {
			$dbw->update( 'flaggedrevs',
				array( 'fr_page_id' => $newPageID ),
				array( 'fr_page_id' => $oldPageID,
					'fr_rev_id' => $revIDs ),
				__METHOD__ );
		}
		# Update pages
		self::titleLinksUpdate( $sourceTitle );
		self::titleLinksUpdate( $destTitle );

		wfProfileOut( __METHOD__ );
		return true;
	}

	/**
	* Clears visiblity settings on page delete
	*/
	public static function deleteVisiblitySettings( $article, $user, $reason ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'flaggedpage_config',
			array( 'fpc_page_id' => $article->getID() ),
			__METHOD__ );

		return true;
	}

	/**
	* Inject stable links on LinksUpdate
	*/
	public static function extraLinksUpdate( $linksUpdate ) {
		wfProfileIn( __METHOD__ );
		if( !self::isPageReviewable( $linksUpdate->mTitle ) ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Check if this page has a stable version by fetching it. Do not
		# get the fr_text field if we are to use the latest stable template revisions.
		global $wgUseStableTemplates;
		$sv = self::getStablePageRev( $linksUpdate->mTitle, !$wgUseStableTemplates, true );
		if( !$sv ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Get the either the full flagged revision text or the revision text
		$article = new Article( $linksUpdate->mTitle );
		# Try stable version cache. This should be updated before this is called.
		$parserOut = self::getPageCache( $article );
		if( $parserOut==false ) {
			$text = $sv->getTextForParse();
			# Parse the text
			$parserOut = self::parseStableText( $article, $text, $sv->getRevId() );
		}
		# Update page fields
		self::updateArticleOn( $article, $sv->getRevId() );
		# Update the links tables to include these
		# We want the UNION of links between the current
		# and stable version. Therefore, we only care about
		# links that are in the stable version and not the regular one.
		foreach( $parserOut->getLinks() as $ns => $titles ) {
			foreach( $titles as $title => $id ) {
				if( !isset($linksUpdate->mLinks[$ns]) ) {
					$linksUpdate->mLinks[$ns] = array();
					$linksUpdate->mLinks[$ns][$title] = $id;
				} else if( !isset($linksUpdate->mLinks[$ns][$title]) ) {
					$linksUpdate->mLinks[$ns][$title] = $id;
				}
			}
		}
		foreach( $parserOut->getImages() as $image => $n ) {
			if( !isset($linksUpdate->mImages[$image]) )
				$linksUpdate->mImages[$image] = $n;
		}
		foreach( $parserOut->getTemplates() as $ns => $titles ) {
			foreach( $titles as $title => $id ) {
				if( !isset($linksUpdate->mTemplates[$ns]) ) {
					$linksUpdate->mTemplates[$ns] = array();
					$linksUpdate->mTemplates[$ns][$title] = $id;
				} else if( !isset($linksUpdate->mTemplates[$ns][$title]) ) {
					$linksUpdate->mTemplates[$ns][$title] = $id;
				}
			}
		}
		foreach( $parserOut->getExternalLinks() as $url => $n ) {
			if( !isset($linksUpdate->mExternals[$url]) )
				$linksUpdate->mExternals[$url] = $n;
		}
		foreach( $parserOut->getCategories() as $category => $sort ) {
			if( !isset($linksUpdate->mCategories[$category]) )
				$linksUpdate->mCategories[$category] = $sort;
		}
		foreach( $parserOut->getLanguageLinks() as $n => $link ) {
			list( $key, $title ) = explode( ':', $link, 2 );
			if( !isset($linksUpdate->mInterlangs[$key]) )
				$linksUpdate->mInterlangs[$key] = $title;
		}
		foreach( $parserOut->getProperties() as $prop => $val ) {
			if( !isset($linksUpdate->mProperties[$prop]) )
				$linksUpdate->mProperties[$prop] = $val;
		}
		wfProfileOut( __METHOD__ );
		return true;
	}
	
	/**
	* Add special fields to parser.
	*/
	public static function parserAddFields( $parser ) {
		$parser->mOutput->fr_ImageSHA1Keys = array();
		$parser->mOutput->fr_newestImageTime = "0";
		$parser->mOutput->fr_newestTemplateID = 0;
		return true;
	}

	/**
	* Select the desired templates based on the selected stable revision IDs
	* NOTE: $p comes in false from this hook ... weird
	*/
	public static function parserFetchStableTemplate( $p=false, $title, &$skip, &$id ) {
		global $wgParser;
		# Trigger for stable version parsing only
		$parser =& $wgParser;
		if( !isset($parser->fr_isStable) || !$parser->fr_isStable )
			return true;
		# Special namespace ... ?
		if( $title->getNamespace() < 0 )
			return true;
		# Only called to make fr_text, right after template/image specifiers
		# are added to the DB. Slaves may not have it yet...
		$dbw = wfGetDB( DB_MASTER );
		# Check for stable version of template if this feature is enabled.
		# Should be in reviewable namespace, this saves unneeded DB checks as
		# well as enforce site settings if they are later changed.
		global $wgUseStableTemplates;
		if( $wgUseStableTemplates && self::isPageReviewable( $title ) ) {
			$id = $dbw->selectField( 'flaggedpages', 'fp_stable',
				array( 'fp_page_id' => $title->getArticleId() ),
				__METHOD__ );
		}
		# If there is no stable version (or that feature is not enabled), use
		# the template revision during review time.
		if( !$id ) {
			$id = $dbw->selectField( 'flaggedtemplates', 'ft_tmp_rev_id',
				array( 'ft_rev_id' => $parser->mRevisionId,
					'ft_namespace' => $title->getNamespace(),
					'ft_title' => $title->getDBkey() ),
				__METHOD__ );
		}
		# If none specified, see if we are allowed to use the current revision
		if( !$id ) {
			global $wgUseCurrentTemplates;
			if( $id === false ) {
				$parser->fr_includesMatched = false; // May want to give an error
				if( !$wgUseCurrentTemplates ) {
					$skip = true;
				}
			} else {
				$skip = true;
			}
		}
		if( $id > $parser->mOutput->fr_newestTemplateID ) {
			$parser->mOutput->fr_newestTemplateID = $id;
		}
		
		return true;
	}

	/**
	* Select the desired images based on the selected stable revision times/SHA-1s
	*/
	public static function parserMakeStableImageLink( $parser, $nt, &$skip, &$time, &$query=false ) {
		# Trigger for stable version parsing only
		if( !isset($parser->fr_isStable) || !$parser->fr_isStable )
			return true;
		# Only called to make fr_text, right after template/image specifiers
		# are added to the DB. Slaves may not have it yet...
		$dbw = wfGetDB( DB_MASTER );
		# Check for stable version of image if this feature is enabled.
		# Should be in reviewable namespace, this saves unneeded DB checks as
		# well as enforce site settings if they are later changed.
		$sha1 = "";
		global $wgUseStableImages;
		if( $wgUseStableImages && self::isPageReviewable( $nt ) ) {
			$srev = self::getStablePageRev( $nt, false, true );
			if( $srev ) {
				$time = $srev->getFileTimestamp();
				$sha1 = $srev->getFileSha1();
				// B/C, may be stored in associated image version metadata table
				if( !$time || !$sha1 ) {
					$row = $dbw->selectRow( 'flaggedimages',
						array( 'fi_img_timestamp', 'fi_img_sha1' ),
						array( 'fi_rev_id' => $srev->getRevId(),
							'fi_name' => $nt->getDBkey() ),
						__METHOD__ );
					$time = $row ? $row->fi_img_timestamp : $time;
					$sha1 = $row ? $row->fi_img_sha1 : $sha1;
				}
			}
		}
		# If there is no stable version (or that feature is not enabled), use
		# the image revision during review time.
		if( !$time ) {
			$row = $dbw->selectRow( 'flaggedimages', 
				array( 'fi_img_timestamp', 'fi_img_sha1' ),
				array( 'fi_rev_id' => $parser->mRevisionId,
					'fi_name' => $nt->getDBkey() ),
				__METHOD__ );
			$time = $row ? $row->fi_img_timestamp : $time;
			$sha1 = $row ? $row->fi_img_sha1 : $sha1;
			$query = $row ? "filetimestamp=" . urlencode( wfTimestamp(TS_MW,$row->fi_img_timestamp) ) : "";
		}
		# If none specified, see if we are allowed to use the current revision
		if( !$time ) {
			global $wgUseCurrentImages;
			# If the DB found nothing...
			if( $time === false ) {
				$parser->fr_includesMatched = false; // May want to give an error
				if( !$wgUseCurrentImages ) {
					$time = "0";
				} else {
					$file = wfFindFile( $nt );
					$time = $file ? $file->getTimestamp() : "0"; // Use current
				}
			} else {
				$time = "0";
			}
		}
		# Add image metadata to parser output
		$parser->mOutput->fr_ImageSHA1Keys[$nt->getDBkey()] = array();
		$parser->mOutput->fr_ImageSHA1Keys[$nt->getDBkey()][$time] = $sha1;
		
		if( $time > $parser->mOutput->fr_newestImageTime ) {
			$parser->mOutput->fr_newestImageTime = $time;
		}
		
		return true;
	}

	/**
	* Select the desired images based on the selected stable revision times/SHA-1s
	*/
	public static function galleryFindStableFileTime( $ig, $nt, &$time, &$query=false ) {
		# Trigger for stable version parsing only
		if( !isset($ig->fr_isStable) || !$ig->fr_isStable )
			return true;
		# Slaves may not have it yet...
		$dbw = wfGetDB( DB_MASTER );
		# Check for stable version of image if this feature is enabled.
		# Should be in reviewable namespace, this saves unneeded DB checks as
		# well as enforce site settings if they are later changed.
		$sha1 = "";
		global $wgUseStableImages;
		if( $wgUseStableImages && self::isPageReviewable( $nt ) ) {
			$srev = self::getStablePageRev( $nt, false, true );
			if( $srev ) {
				$time = $srev->getFileTimestamp();
				$sha1 = $srev->getFileSha1();
				// B/C, may be stored in associated image version metadata table
				if( !$time || !$sha1 ) {
					$row = $dbw->selectRow( 'flaggedimages',
						array( 'fi_img_timestamp', 'fi_img_sha1' ),
						array( 'fi_rev_id' => $srev->getRevId(),
							'fi_name' => $nt->getDBkey() ),
						__METHOD__ );
					$time = $row ? $row->fi_img_timestamp : $time;
					$sha1 = $row ? $row->fi_img_sha1 : $sha1;
				}
			}
		}
		# If there is no stable version (or that feature is not enabled), use
		# the image revision during review time.
		if( !$time ) {
			$row = $dbw->selectRow( 'flaggedimages', 
				array( 'fi_img_timestamp', 'fi_img_sha1' ),
				array('fi_rev_id' => $ig->mRevisionId,
					'fi_name' => $nt->getDBkey() ),
				__METHOD__ );
			$time = $row ? $row->fi_img_timestamp : $time;
			$sha1 = $row ? $row->fi_img_sha1 : $sha1;
			$query = $row ? "filetimestamp=" . urlencode( wfTimestamp(TS_MW,$row->fi_img_timestamp) ) : "";
		}
		# If none specified, see if we are allowed to use the current revision
		if( !$time ) {
			global $wgUseCurrentImages;
			# If the DB found nothing...
			if( $time === false ) {
				$ig->fr_parentParser->fr_includesMatched = false; // May want to give an error
				if( !$wgUseCurrentImages ) {
					$time = "0";
				} else {
					$file = wfFindFile( $nt );
					$time = $file ? $file->getTimestamp() : "0";
				}
			} else {
				$time = "0";
			}
		}
		# Add image metadata to parser output
		$ig->fr_parentParser->mOutput->fr_ImageSHA1Keys[$nt->getDBkey()] = array();
		$ig->fr_parentParser->mOutput->fr_ImageSHA1Keys[$nt->getDBkey()][$time] = $sha1;
		
		if( $time > $ig->fr_parentParser->mOutput->fr_newestImageTime ) {
			$ig->fr_parentParser->mOutput->fr_newestImageTime = $time;
		}

		return true;
	}

	/**
	* Flag of an image galley as stable
	*/
	public static function parserMakeGalleryStable( $parser, $ig ) {
		# Trigger for stable version parsing only
		if( !isset($parser->fr_isStable) || !$parser->fr_isStable )
			return true;

		$ig->fr_isStable = true;
		$ig->fr_parentParser =& $parser; // hack

		return true;
	}

	/**
	* Insert image timestamps/SHA-1 keys into parser output
	*/
	public static function parserInjectTimestamps( $parser, &$text ) {
		# Don't trigger this for stable version parsing...it will do it separately.
		if( isset($parser->fr_isStable) && $parser->fr_isStable )
			return true;

		wfProfileIn( __METHOD__ );

		$maxRevision = 0;
		# Record the max template revision ID
		if( !empty($parser->mOutput->mTemplateIds) ) {
			foreach( $parser->mOutput->mTemplateIds as $namespace => $DBkey_rev ) {
				foreach( $DBkey_rev as $DBkey => $revID ) {
					if( $revID > $maxRevision ) {
						$maxRevision = $revID;
					} 
				}
			}
		}
		$parser->mOutput->fr_newestTemplateID = $maxRevision;
			
		$maxTimestamp = "0";
		# Fetch the current timestamps of the images.
		if( !empty($parser->mOutput->mImages) ) {
			$filenames = array_keys($parser->mOutput->mImages);
			foreach( $filenames as $filename ) {
				$file = wfFindFile( Title::makeTitle( NS_IMAGE, $filename ) );
				$parser->mOutput->fr_ImageSHA1Keys[$filename] = array();
				if( $file ) {
					if( $file->getTimestamp() > $maxTimestamp ) {
						$maxTimestamp = $file->getTimestamp();
					}
					$parser->mOutput->fr_ImageSHA1Keys[$filename][$file->getTimestamp()] = $file->getSha1();
				} else {
					$parser->mOutput->fr_ImageSHA1Keys[$filename]["0"] = '';
				}
			}
		}
		# Record the max timestamp
		$parser->mOutput->fr_newestImageTime = $maxTimestamp;

		wfProfileOut( __METHOD__ );
		return true;
	}

	/**
	* Insert image timestamps/SHA-1s into page output
	*/
	public static function outputInjectTimestamps( $out, $parserOut ) {
		# Leave as defaults if missing. Relevant things will be updated only when needed.
		# We don't want to go around resetting caches all over the place if avoidable...
		$out->fr_ImageSHA1Keys = isset($parserOut->fr_ImageSHA1Keys) ? $parserOut->fr_ImageSHA1Keys : array();

		return true;
	}

	/**
	* Don't let users vandalize pages by moving them.
	*/
	public static function userCanMove( $title, $user, $action, $result ) {
		if( $action != 'move' || !self::isPageReviewable( $title ) )
			return true;

		$flaggedArticle = FlaggedArticle::getInstance( $title );
		$frev = $flaggedArticle->getStableRev();
		if( !$frev )
			return true;

		# Allow for only editors/reviewers to move this
		$right = $frev->getQuality() ? 'validate' : 'review';
		if( !$user->isAllowed($right) && !$user->isAllowed('movestable') ) {
			$result = false;
			return false;
		}
		return true;
	}
	
    /**
    * Allow users to view reviewed pages.
    */
    public static function userCanView( $title, $user, $action, $result ) {
        global $wgFlaggedRevsVisible, $wgFlaggedRevsTalkVisible, $wgTitle;
        # Assume $action may still not be set, in which case, treat it as 'view'...
        if( !$wgFlaggedRevsVisible || $action != 'read' )
            return true;
        # Admin may set this to false, rather than array()...
        $groups = $user->getGroups();
        $groups[] = '*';
        if( empty($wgFlaggedRevsVisible) || !array_intersect($groups,$wgFlaggedRevsVisible) )
            return true;
        # Is this a talk page?
        if( $wgFlaggedRevsTalkVisible && $title->isTalkPage() ) {
            $result = true;
            return true;
        }
        # See if there is a stable version. Also, see if, given the page 
        # config and URL params, the page can be overriden.
		$flaggedArticle = FlaggedArticle::getInstance( $title );
        if( $wgTitle && $wgTitle->equals( $title ) && $flaggedArticle->getStableRev( true ) ) {
            // Cache stable version while we are at it.
            if( $flaggedArticle->pageOverride() ) {
                $result = true;
            }
        } else {
            if( self::getStablePageRev( $title ) ) {
                $result = true;
            }
        }
        return true;
    }
	
	/**
	* When an edit is made by a reviewer, if the current revision is the stable
	* version, try to automatically review it.
	*/
	public static function maybeMakeEditReviewed( $article, $rev, $baseRevID = false ) {
		global $wgFlaggedRevsAutoReview, $wgRequest;
		# Get the user
		$user = User::newFromId( $rev->getUser() );
		if( !$wgFlaggedRevsAutoReview || !$user->isAllowed('autoreview') )
			return true;
		# Must be in reviewable namespace
		$title = $article->getTitle();
		if( !$title || !self::isPageReviewable( $title ) ) {
			return true;
		}
		$frev = null;
		$reviewableNewPage = false;
		# Get the revision ID the incoming one was based off
		if ( !$baseRevID ) {
			$baseRevID = $wgRequest->getIntOrNull('baseRevId');
		}
		# Get what was just the current revision ID
		$prevRevID = $title->getPreviousRevisionId( $rev->getId(), GAID_FOR_UPDATE );
		# If baseRevId not given, assume the previous revision ID
		if ( !$baseRevID ) {
			$baseRevID = $prevRevID;
		}
		if( $baseRevID ) {
			$frev = self::getFlaggedRev( $title, $baseRevID, false, true, $rev->getPage() );
			# If the base revision was not reviewed, check if the previous one was
			if ( !$frev ) {
				$frev = self::getFlaggedRev( $title, $prevRevID, false, true, $rev->getPage() );
			}
		} else {
			$prevRevID = $title->getPreviousRevisionId( $rev->getId(), GAID_FOR_UPDATE );
			$prevRev = $prevRevID ? Revision::newFromID( $prevRevID ) : null;
			# Check for null edits
			if( $prevRev && $prevRev->getTextId() == $rev->getTextId() ) {
				$frev = self::getFlaggedRev( $title, $prevRev->getId() );
			# Check for new pages
			} else if( !$prevRevID ) {
				global $wgFlaggedRevsAutoReviewNew;
				$reviewableNewPage = ($wgFlaggedRevsAutoReviewNew && $user->isAllowed('review'));
			}
		}
		# Is this an edit directly to the stable version?
		if( $reviewableNewPage || !is_null($frev) ) {
			# Assume basic flagging level
			$flags = array();
			foreach( self::$dimensions as $tag => $minQL ) {
				$flags[$tag] = 1;
			}
			# Review this revision of the page. Let articlesavecomplete hook do rc_patrolled bit...
			self::autoReviewEdit( $article, $user, $rev->getText(), $rev, $flags, false );
		}
		return true;
	}

	/**
	* When an edit is made to a page that can't be reviewed, autopatrol if allowed.
	* This is not loggged for perfomance reasons and no one cares if talk pages and such
	* are autopatrolled.
	*/
	public static function autoMarkPatrolled( $article, $user, $text, $c, $m, $a, $b, $flags, $rev ) {
		if( !$rev ) {
			return true; // NULL edit
		}
		$title = $article->getTitle();
		$patrol = false;
		// Is the page reviewable?
		if( self::isPageReviewable($title) ) {
			$patrol = self::revIsFlagged( $title, $rev->getId(), GAID_FOR_UPDATE );
		// Can this be patrolled?
		} else if( self::isPagePatrollable($title) ) {
			$patrol = $user->isAllowed('autopatrolother');
		} else {
			$patrol = true; // mark by default
		}
		if( $patrol ) {
			$dbw = wfGetDB( DB_MASTER );
			$dbw->update( 'recentchanges',
				array( 'rc_patrolled' => 1 ),
				array( 'rc_this_oldid' => $rev->getId(),
					'rc_user_text' => $rev->getRawUserText(),
					'rc_timestamp' => $dbw->timestamp( $rev->getTimestamp() ) ),
				__METHOD__,
				array( 'USE INDEX' => 'rc_user_text', 'LIMIT' => 1 ) );
		}
		return true;
	}

	/**
	* Callback that autopromotes user according to the setting in
	* $wgFlaggedRevsAutopromote. This is not as efficient as it should be
	*/
	public static function autoPromoteUser( $article, $user, &$text, &$summary, &$m, &$a, &$b, &$f, $rev ) {
		global $wgFlaggedRevsAutopromote, $wgMemc;

		if( empty($wgFlaggedRevsAutopromote) || !$rev )
			return true;

		wfProfileIn( __METHOD__ );
		# Grab current groups
		$groups = $user->getGroups();
		# Do not give this to current holders or bots
		if( in_array( 'bot', $groups ) || in_array( 'editor', $groups ) ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Do not re-add status if it was previously removed!
		$p = self::getUserParams( $user );
		if( isset($p['demoted']) && $p['demoted'] ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Update any special counters for non-null revisions
		$changed = false;
		$pages = array();
		$p['uniqueContentPages'] = isset($p['uniqueContentPages']) ? $p['uniqueContentPages'] : '';
		$p['totalContentEdits'] = isset($p['totalContentEdits']) ? $p['totalContentEdits'] : 0;
		$p['editComments'] = isset($p['editComments']) ? $p['editComments'] : 0;
		if( $article->getTitle()->isContentPage() ) {
			$pages = explode( ',', trim($p['uniqueContentPages']) ); // page IDs
			# Don't let this get bloated for no reason
			if( count($pages) < $wgFlaggedRevsAutopromote['uniqueContentPages'] && !in_array($article->getId(),$pages) ) {
				$pages[] = $article->getId();
				$p['uniqueContentPages'] = preg_replace('/^,/','',implode(',',$pages)); // clear any garbage
			}
			$p['totalContentEdits'] += 1;
			$changed = true;
		}
		if( $summary ) {
			$p['editComments'] += 1;
			$changed = true;
		}
		# Save any updates to user params
		if( $changed ) {
			self::saveUserParams( $user, $p );
		}
		# Check if user edited enough content pages
		if( $wgFlaggedRevsAutopromote['totalContentEdits'] > $p['totalContentEdits'] ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Check if user edited enough unique pages
		if( $wgFlaggedRevsAutopromote['uniqueContentPages'] > count($pages) ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Check edit comment use
		if( $wgFlaggedRevsAutopromote['editComments'] > $p['editComments'] ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Check if results are cached to avoid DB queries
		$key = wfMemcKey( 'flaggedrevs', 'autopromote-skip', $user->getID() );
		$value = $wgMemc->get( $key );
		if( $value == 'true' ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Check basic, already available, promotion heuristics first...
		$now = time();
		$usercreation = wfTimestamp( TS_UNIX, $user->getRegistration() );
		$userage = floor(($now - $usercreation) / 86400);
		if( $userage < $wgFlaggedRevsAutopromote['days'] ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		if( $user->getEditCount() < $wgFlaggedRevsAutopromote['edits'] ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		if( $wgFlaggedRevsAutopromote['email'] && !$user->isEmailConfirmed() ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Don't grant to currently blocked users...
		if( $user->isBlocked() ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Check if user was ever blocked before
		if( $wgFlaggedRevsAutopromote['neverBlocked'] ) {
			$dbr = wfGetDB( DB_SLAVE );
			$blocked = $dbr->selectField( 'logging', '1',
				array( 'log_namespace' => NS_USER, 
					'log_title' => $user->getUserPage()->getDBKey(),
					'log_type' => 'block',
					'log_action' => 'block' ),
				__METHOD__,
				array( 'USE INDEX' => 'user_time' ) );
			if( $blocked ) {
				# Make a key to store the results
				$wgMemc->set( $key, 'true', 3600*24*7 );
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# See if the page actually has sufficient content...
		if( $wgFlaggedRevsAutopromote['userpage'] ) {
			if( !$user->getUserPage()->exists() ) {
				wfProfileOut( __METHOD__ );
				return true;
			}
			$dbr = isset($dbr) ? $dbr : wfGetDB( DB_SLAVE );
			$size = $dbr->selectField( 'page', 'page_len',
				array( 'page_namespace' => $user->getUserPage()->getNamespace(),
					'page_title' => $user->getUserPage()->getDBKey() ),
				__METHOD__ );
			if( $size < $wgFlaggedRevsAutopromote['userpageBytes'] ) {
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Check for edit spacing. This lets us know that the account has
		# been used over N different days, rather than all in one lump.
		if( $wgFlaggedRevsAutopromote['spacing'] > 0 && $wgFlaggedRevsAutopromote['benchmarks'] > 1 ) {
			# Convert days to seconds...
			$spacing = $wgFlaggedRevsAutopromote['spacing'] * 24 * 3600;
			# Check the oldest edit
			$dbr = isset($dbr) ? $dbr : wfGetDB( DB_SLAVE );
			$lower = $dbr->selectField( 'revision', 'rev_timestamp',
				array( 'rev_user' => $user->getID() ),
				__METHOD__,
				array( 'ORDER BY' => 'rev_timestamp ASC',
					'USE INDEX' => 'user_timestamp' ) );
			# Recursively check for an edit $spacing seconds later, until we are done.
			# The first edit counts, so we have one less scans to do...
			$benchmarks = 0;
			$needed = $wgFlaggedRevsAutopromote['benchmarks'] - 1;
			while( $lower && $benchmarks < $needed ) {
				$next = wfTimestamp( TS_UNIX, $lower ) + $spacing;
				$lower = $dbr->selectField( 'revision', 'rev_timestamp',
					array( 'rev_user' => $user->getID(),
						'rev_timestamp > ' . $dbr->addQuotes( $dbr->timestamp($next) ) ),
					__METHOD__,
					array( 'ORDER BY' => 'rev_timestamp ASC',
						'USE INDEX' => 'user_timestamp' ) );
				if( $lower !== false )
					$benchmarks++;
			}
			if( $benchmarks < $needed ) {
				# Make a key to store the results
				$wgMemc->set( $key, 'true', 3600*24*$spacing*($benchmarks - $needed - 1) );
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Check if this user is sharing IPs with another users
		if( $wgFlaggedRevsAutopromote['uniqueIPAddress'] ) {
			$uid = $user->getId();

			$dbr = isset($dbr) ? $dbr : wfGetDB( DB_SLAVE );
			$shared = $dbr->selectField( 'recentchanges', '1',
				array( 'rc_ip' => wfGetIP(),
					"rc_user != '$uid'" ),
				__METHOD__,
				array( 'USE INDEX' => 'rc_ip' ) );
			if( $shared ) {
				# Make a key to store the results
				$wgMemc->set( $key, 'true', 3600*24*7 );
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Check for bot attacks/sleepers
		global $wgSorbsUrl, $wgProxyWhitelist;
		if( $wgSorbsUrl && $wgFlaggedRevsAutopromote['noSorbsMatches'] ) {
			$ip = wfGetIP();
			if( !in_array($ip,$wgProxyWhitelist) && $user->inDnsBlacklist( $ip, $wgSorbsUrl ) ) {
				# Make a key to store the results
				$wgMemc->set( $key, 'true', 3600*24*7 );
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Check if the user has any recent content edits
		if( $wgFlaggedRevsAutopromote['recentContentEdits'] > 0 ) {
			global $wgContentNamespaces;
		
			$dbr = isset($dbr) ? $dbr : wfGetDB( DB_SLAVE );
			$res = $dbr->select( 'recentchanges', '1', 
				array( 'rc_user_text' => $user->getName(),
					'rc_namespace' => $wgContentNamespaces ), 
				__METHOD__, 
				array( 'USE INDEX' => 'rc_ns_usertext',
					'LIMIT' => $wgFlaggedRevsAutopromote['recentContent'] ) );
			if( $dbr->numRows($res) < $wgFlaggedRevsAutopromote['recentContent'] ) {
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Check to see if the user has so many deleted edits that
		# they don't actually enough live edits. This is because
		# $user->getEditCount() is the count of edits made, not live.
		if( $wgFlaggedRevsAutopromote['excludeDeleted'] ) {
			$dbr = isset($dbr) ? $dbr : wfGetDB( DB_SLAVE );
			$minDiff = $user->getEditCount() - $wgFlaggedRevsAutopromote['days'] + 1;
			# Use an estimate if the number starts to get large
			if( $minDiff <= 100 ) {
				$res = $dbr->select( 'archive', '1', 
					array( 'ar_user_text' => $user->getName() ), 
					__METHOD__, 
					array( 'USE INDEX' => 'usertext_timestamp', 'LIMIT' => $minDiff ) );
				$deletedEdits = $dbr->numRows($res);
			} else {
				$deletedEdits = $dbr->estimateRowCount( 'archive', '1',
					array( 'ar_user_text' => $user->getName() ),
					__METHOD__,
					array( 'USE INDEX' => 'usertext_timestamp' ) );
			}
			if( $deletedEdits >= $minDiff ) {
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Add editor rights
		$newGroups = $groups ;
		array_push( $newGroups, 'editor' );
		# Lets NOT spam RC, set $RC to false
		$log = new LogPage( 'rights', false );
		$log->addEntry( 'rights', $user->getUserPage(), wfMsg('rights-editor-autosum'),
			array( implode(', ',$groups), implode(', ',$newGroups) ) );
		$user->addGroup('editor');

		wfProfileOut( __METHOD__ );
		return true;
	}
	
   	/**
	* Record demotion so that auto-promote will be disabled
	*/
	public static function recordDemote( $u, $addgroup, $removegroup ) {
		if( $removegroup && in_array('editor',$removegroup) ) {
			$params = self::getUserParams( $u );
			$params['demoted'] = 1;
			self::saveUserParams( $u, $params );
		}
		return true;
	}
	
	/**
	* Add user preference to form HTML
	*/
	public static function injectPreferences( $form, $out ) {
		$prefsHtml = FlaggedRevsXML::stabilityPreferences( $form );
		$out->addHTML( $prefsHtml );
		return true;
	}
	
	/**
	* Add user preference to form object based on submission
	*/
	public static function injectFormPreferences( $form, $request ) {
		global $wgUser;
		$form->mFlaggedRevsStable = $request->getInt( 'wpFlaggedRevsStable' );
		$form->mFlaggedRevsSUI = $request->getInt( 'wpFlaggedRevsSUI' );
		$form->mFlaggedRevsWatch = $wgUser->isAllowed( 'review' ) ? $request->getInt( 'wpFlaggedRevsWatch' ) : 0;
		return true;
	}
	
	/**
	* Set preferences on form based on user settings
	*/
	public static function resetPreferences( $form, $user ) {
		global $wgSimpleFlaggedRevsUI;
		$form->mFlaggedRevsStable = $user->getOption( 'flaggedrevsstable' );
		$form->mFlaggedRevsSUI = $user->getOption( 'flaggedrevssimpleui', intval($wgSimpleFlaggedRevsUI) );
		$form->mFlaggedRevsWatch = $user->getOption( 'flaggedrevswatch' );
		return true;
	}
	
	/**
	* Set user preferences into user object before it is applied to DB
	*/
	public static function savePreferences( $form, $user, &$msg ) {
		$user->setOption( 'flaggedrevsstable', $form->validateInt( $form->mFlaggedRevsStable, 0, 1 ) );
		$user->setOption( 'flaggedrevssimpleui', $form->validateInt( $form->mFlaggedRevsSUI, 0, 1 ) );
		$user->setOption( 'flaggedrevswatch', $form->validateInt( $form->mFlaggedRevsWatch, 0, 1 ) );
		return true;
	}
	
	/**
	* Create revision link for log line entry
	* @param string $type
	* @param string $action
	* @param object $title
	* @param array $paramArray
	* @param string $c
	* @param string $r user tool links
	* @param string $t timestamp of the log entry
	* @return bool true
	*/
	public static function reviewLogLine( $type = '', $action = '', $title = null, $paramArray = array(), &$c = '', &$r = '', $t = '' ) {
		# Show link to page with oldid=x
		if( $type == 'review' && in_array($action,array('approve','approve2','unapprove','unapprove2')) ) {
			global $wgUser;
			if( is_object($title) && isset($paramArray[0]) ) {
				$r = '(' . $wgUser->getSkin()->makeKnownLinkObj( $title, 
					wfMsgHtml('review-logentry-id',$paramArray[0]), "oldid={$paramArray[0]}") . ')';
			}
		}
		return true;
	}

	public static function imagePageFindFile( $imagePage, &$normalFile, &$displayFile ) {
		$flaggedArticle = FlaggedArticle::getInstance( $imagePage );
		$flaggedArticle->imagePageFindFile( $normalFile, $displayFile );
		return true;
	}

	static function setActionTabs( $skin, &$contentActions ) {
		global $wgArticle;
		if ( $wgArticle ) {
			FlaggedArticle::getInstance( $wgArticle )->setActionTabs( $skin, $contentActions );
		}
		return true;
	}

	static function setLastModified( $skin, &$tpl ) {
		global $wgArticle;
		if ( $wgArticle ) {
			FlaggedArticle::getInstance( $wgArticle )->setLastModified( $skin, $tpl );
		}
		return true;
	}

	static function onArticleViewHeader( $article, &$outputDone, &$pcache ) {
		$flaggedArticle = FlaggedArticle::getInstance( $article );
		$flaggedArticle->maybeUpdateMainCache( $outputDone, $pcache );
		$flaggedArticle->setPageContent( $outputDone, $pcache );
		$flaggedArticle->addPatrolLink( $outputDone, $pcache );
		return true;
	}
	
	static function setPermaLink( $skin, &$navUrls, &$revId, &$id ) {
		global $wgArticle;
		if ( $wgArticle ) {
			FlaggedArticle::getInstance( $wgArticle )->setPermaLink( $skin, $navUrls, $revId, $id );
		}
		return true;
	}
	
	static function addToEditView( $editPage ) {
		return FlaggedArticle::getInstance( $editPage->mArticle )->addToEditView( $editPage );
	}
	
	static function addReviewForm( $out ) {
		global $wgArticle;
		if ( $wgArticle && $out->isArticleRelated() ) {
			FlaggedArticle::getInstance( $wgArticle )->addReviewForm( $out );
		}
		return true;
	}
	
	static function addVisibilityLink( $out ) {
		global $wgArticle;
		if ( $wgArticle && $out->isArticleRelated() ) {
			FlaggedArticle::getInstance( $wgArticle )->addVisibilityLink( $out );
		}
		return true;
	}
	
	static function addToHistQuery( $pager, &$queryInfo ) {
		$flaggedArticle = FlaggedArticle::getInstance( $pager->mPageHistory->getTitle() );
		if( $flaggedArticle->isReviewable() ) {
			$queryInfo['tables'][] = 'flaggedrevs';
			$queryInfo['fields'][] = 'fr_quality';
			$queryInfo['fields'][] = 'fr_user';
			$queryInfo['join_conds']['flaggedrevs'] = array( 'LEFT JOIN', "fr_page_id = rev_page AND fr_rev_id = rev_id" );
		}
		return true;
	}
	
	static function addToHistLine( $history, $row, &$s ) {
		return FlaggedArticle::getInstance( $history->getArticle() )->addToHistLine( $history, $row, $s );
	}
	
	static function addToFileHistLine( $hist, $file, &$s, &$rowClass ) {
		return FlaggedArticle::getInstance( $hist->getImagePage() )->addToFileHistLine( $hist, $file, $s, $rowClass );
	}
	
	static function injectReviewDiffURLParams( $article, &$sectionAnchor, &$extraQuery ) {
		return FlaggedArticle::getInstance( $article )->injectReviewDiffURLParams( $sectionAnchor, $extraQuery );
	}

	static function onDiffViewHeader( $diff, $oldRev, $newRev ) {
		$flaggedArticle = FlaggedArticle::getInstance( $diff->getTitle() );
		$flaggedArticle->addPatrolAndDiffLink( $diff, $oldRev, $newRev );
		$flaggedArticle->addDiffNoticeAndIncludes( $diff, $oldRev, $newRev );
		return true;
	}

	static function addRevisionIDField( $editPage, $out ) {
		return FlaggedArticle::getInstance( $editPage->mArticle )->addRevisionIDField( $editPage, $out );
	}
}

