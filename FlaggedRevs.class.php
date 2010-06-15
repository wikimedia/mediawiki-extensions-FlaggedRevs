<?php
/**
 * Class containing utility functions for a FlaggedRevs environment
 *
 * Class is lazily-initialized, calling load() as needed
 */
class FlaggedRevs {
	# Tag name/level config
	protected static $dimensions = array();
	protected static $minSL = array();
	protected static $minQL = array();
	protected static $minPL = array();
	protected static $qualityVersions = false;
	protected static $pristineVersions = false;
	protected static $binaryFlagging = true;
	# Namespace config
	protected static $reviewNamespaces = array();
	protected static $patrolNamespaces = array();
	# Restriction levels/config
	protected static $restrictionLevels = array();
	# Temporary process cache variable
	protected static $includeVersionCache = array();
	
	protected static $loaded = false;

	public static function load() {
		global $wgFlaggedRevTags;
		if ( self::$loaded ) {
			return true;
		}
		self::$loaded = true;
		# Assume true, then set to false if needed
		if ( !empty( $wgFlaggedRevTags ) ) {
			self::$qualityVersions = true;
			self::$pristineVersions = true;
			self::$binaryFlagging = ( count( $wgFlaggedRevTags ) <= 1 );
		}
		foreach ( $wgFlaggedRevTags as $tag => $levels ) {
			# Sanity checks
			$safeTag = htmlspecialchars( $tag );
			if ( !preg_match( '/^[a-zA-Z]{1,20}$/', $tag ) || $safeTag !== $tag ) {
				throw new MWException( 'FlaggedRevs given invalid tag name!' );
			}
			# Define "quality" and "pristine" reqs
			if ( is_array( $levels ) ) {
				$minQL = $levels['quality'];
				$minPL = $levels['pristine'];
				$ratingLevels = $levels['levels'];
			# B/C, $levels is just an integer (minQL)
			} else {
				global $wgFlaggedRevPristine, $wgFlaggedRevValues;
				$ratingLevels = isset( $wgFlaggedRevValues ) ?
					$wgFlaggedRevValues : 1;
				$minQL = $levels; // an integer
				$minPL = isset( $wgFlaggedRevPristine ) ?
					$wgFlaggedRevPristine : $ratingLevels + 1;
			}
			# Set FlaggedRevs tags
			self::$dimensions[$tag] = array();
			for ( $i = 0; $i <= $ratingLevels; $i++ ) {
				self::$dimensions[$tag][$i] = "{$tag}-{$i}";
			}
			if ( $ratingLevels > 1 ) {
				self::$binaryFlagging = false; // more than one level
			}
			# Sanity checks
			if ( !is_integer( $minQL ) || !is_integer( $minPL ) ) {
				throw new MWException( 'FlaggedRevs given invalid tag value!' );
			}
			if ( $minQL > $ratingLevels ) {
				self::$qualityVersions = false;
				self::$pristineVersions = false;
			}
			if ( $minPL > $ratingLevels ) {
				self::$pristineVersions = false;
			}
			self::$minQL[$tag] = max( $minQL, 1 );
			self::$minPL[$tag] = max( $minPL, 1 );
			self::$minSL[$tag] = 1;
		}
		global $wgFlaggedRevsRestrictionLevels;
		# Make sure that the levels are unique
		self::$restrictionLevels = array_unique( $wgFlaggedRevsRestrictionLevels );
		self::$restrictionLevels = array_filter( self::$restrictionLevels, 'strlen' );
		# Make sure no talk namespaces are in review namespace
		global $wgFlaggedRevsNamespaces, $wgFlaggedRevsPatrolNamespaces;
		foreach ( $wgFlaggedRevsNamespaces as $ns ) {
			if ( MWNamespace::isTalk( $ns ) ) {
				throw new MWException( 'FlaggedRevs given talk namespace in $wgFlaggedRevsNamespaces!' );
			} else if ( $ns == NS_MEDIAWIKI ) {
				throw new MWException( 'FlaggedRevs given NS_MEDIAWIKI in $wgFlaggedRevsNamespaces!' );
			}
		}
		self::$reviewNamespaces = $wgFlaggedRevsNamespaces;
		# Note: reviewable *pages* override patrollable ones
		self::$patrolNamespaces = $wgFlaggedRevsPatrolNamespaces;
	}
	
	# ################ Basic config accessors #################

	/**
	 * Is there only one tag and it has only one level?
	 * @returns bool
	 */
	public static function binaryFlagging() {
		self::load();
		return self::$binaryFlagging;
	}
	
	/**
	 * If there only one tag and it has only one level, return it
	 * @returns string
	 */
	public static function binaryTagName() {
		if ( !self::binaryFlagging() ) {
			return null;
		}
		$tags = array_keys( self::$dimensions );
		return empty( $tags ) ? null : $tags[0];
	}
	
	/**
	 * Are quality versions enabled?
	 * @returns bool
	 */
	public static function qualityVersions() {
		self::load();
		return self::$qualityVersions;
	}
	
	/**
	 * Are pristine versions enabled?
	 * @returns bool
	 */
	public static function pristineVersions() {
		self::load();
		return self::$pristineVersions;
	}

	/**
	 * Should this be using a simple icon-based UI?
	 * Check the user's preferences first, using the site settings as the default.
	 * @TODO: dependency inject the User?
	 * @returns bool
	 */
	public static function useSimpleUI() {
		global $wgUser, $wgSimpleFlaggedRevsUI;
		return $wgUser->getOption( 'flaggedrevssimpleui', intval( $wgSimpleFlaggedRevsUI ) );
	}

	/**
	 * Allow auto-review edits directly to the stable version by reviewers?
	 * (1 to allow auto-sighting; 2 for auto-quality; 3 for auto-pristine)
	 * @returns bool
	 */
	public static function autoReviewEdits() {
		global $wgFlaggedRevsAutoReview;
		return (bool)$wgFlaggedRevsAutoReview;
	}

	/**
	 * Get the maximum level that $tag can be autoreviewed to
	 * @param string $tag
	 * @returns int
	 */
	public static function maxAutoReviewLevel( $tag ) {
		global $wgFlaggedRevsTagsAuto;
		self::load();
		if ( !self::autoReviewEdits() ) {
			return 0; // no auto-review allowed at all
		}
		if ( isset( $wgFlaggedRevsTagsAuto[$tag] ) ) {
			return (int)$wgFlaggedRevsTagsAuto[$tag];
		} else {
			return 1; // B/C (before $wgFlaggedRevsTagsAuto)
		}
	}

	/**
	 * Auto-review new pages with the minimal level?
	 * @returns bool
	 */
	public static function autoReviewNewPages() {
		global $wgFlaggedRevsAutoReviewNew;
		return (bool)$wgFlaggedRevsAutoReviewNew;
	}
	
	/**
	 * Should this user see stable versions by default?
	 * @returns bool
	 */
	public static function isStableShownByDefault() {
		global $wgFlaggedRevsOverride;
		return (bool)$wgFlaggedRevsOverride;
	}

	/**
	 * Does FlaggedRevs only show for pages were the stable version is the default?
	 * @returns bool
	 */
	public static function forDefaultVersionOnly() {
		global $wgFlaggedRevsReviewForDefault;
		return (bool)$wgFlaggedRevsReviewForDefault;
	}

	/**
	 * Does FlaggedRevs only show for pages that have been set to do so?
	 * @returns bool
	 */
	public static function stableOnlyIfConfigured() {
		return self::forDefaultVersionOnly() && !self::isStableShownByDefault();
	}
	
	/**
	 * Should this user ignore the site and page default version settings?
	 * @TODO: dependency inject the User?
	 * @returns bool
	 */
	public static function ignoreDefaultVersion() {
		global $wgFlaggedRevsExceptions, $wgUser;
		# Viewer sees current by default (editors, insiders, ect...) ?
		foreach ( $wgFlaggedRevsExceptions as $group ) {
			if ( $group == 'user' ) {
				return ( !$wgUser->isAnon() );
			} elseif ( in_array( $group, $wgUser->getGroups() ) ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Return the include handling configuration
	 * @returns int
	 */
	public static function inclusionSetting() {
		global $wgFlaggedRevsHandleIncludes;
		return $wgFlaggedRevsHandleIncludes;
	}
	
	/**
	 * Should tags only be shown for unreviewed content for this user?
	 * @returns bool
	 */
	public static function lowProfileUI() {
		global $wgFlaggedRevsLowProfile;
		return $wgFlaggedRevsLowProfile;
	}

	/**
	 * Are there site defined protection levels for review
	 * @returns bool
	 */
	public static function useProtectionLevels() {
		global $wgFlaggedRevsProtection;
		return $wgFlaggedRevsProtection && self::getRestrictionLevels();
	}

	/**
	 * Get the appropriate PageStabilityForm depending on whether protection
	 * levels are being used
	 * @return PageStabilityForm
	 */
	public static function getPageStabilityForm() {
		return FlaggedRevs::useProtectionLevels() ?
			new PageStabilityProtectForm() :
			new PageStabilityGeneralForm();
	}

	/**
	 * Get the autoreview restriction levels available
	 * @returns array
	 */
	public static function getRestrictionLevels() {
		self::load();
		return self::$restrictionLevels;
	}

	/**
	 * Should comments be allowed on pages and forms?
	 * @returns bool
	 */
	public static function allowComments() {
		global $wgFlaggedRevsComments;
		return $wgFlaggedRevsComments;
	}
	
	/**
	 * Get the array of tag dimensions and level messages
	 * @returns array
	 */
	public static function getDimensions() {
		self::load();
		return self::$dimensions;
	}
	
	/**
	 * Get min level this tag needs to be for a rev to be "quality"
	 * @returns int
	 */
	public static function getMinQL( $tag ) {
		self::load();
		return self::$minQL[$tag];
	}
	
	/**
	 * Get min level this tag needs to be for a rev to be "pristine"
	 * @returns int
	 */
	public static function getMinPL( $tag ) {
		self::load();
		return self::$minPL[$tag];
	}
	
	/**
	 * Get the associative array of tag dimensions
	 * (tags => array(levels => msgkey))
	 * @returns array
	 */
	public static function getTags() {
		self::load();
		return array_keys( self::$dimensions );
	}

	/**
	 * Get the associative array of tag restrictions
	 * (tags => array(rights => levels))
	 * @returns array
	 */
	public static function getTagRestrictions() {
		global $wgFlagRestrictions;
		return $wgFlagRestrictions;
	}
	
	/**
	 * Get the UI name for a tag
	 * @param string $tag
	 * @returns string
	 */
	public static function getTagMsg( $tag ) {
		return wfMsgExt( "revreview-$tag", array( 'escapenoentities' ) );
	}
	
	/**
	 * Get the levels for a tag. Gives map of level to message name.
	 * @param string $tag
	 * @returns associative array (integer -> string)
	 */
	public static function getTagLevels( $tag ) {
		self::load();
		return isset( self::$dimensions[$tag] ) ?
			self::$dimensions[$tag] : array();
	}
	
	/**
	 * Get the the UI name for a value of a tag
	 * @param string $tag
	 * @param int $value
	 * @returns string
	 */
	public static function getTagValueMsg( $tag, $value ) {
		self::load();
		if ( !isset( self::$dimensions[$tag] ) )
			return '';
		if ( !isset( self::$dimensions[$tag][$value] ) )
			return '';
		# Return empty string if not there
		return wfMsgExt( 'revreview-' . self::$dimensions[$tag][$value],
			array( 'escapenoentities' ) );
	}
	
	/**
	 * Are there no actual dimensions?
	 * @returns bool
	 */
	public static function dimensionsEmpty() {
		self::load();
		return empty( self::$dimensions );
	}

	/**
	 * Get corresponding text for the api output of flagging levels
	 *
	 * @param int $level
	 * @return string
	 */
	public static function getQualityLevelText( $level ) {
		static $levelText = array(
			0 => 'stable',
			1 => 'quality',
			2 => 'pristine'
		);
		if ( isset( $levelText[$level] ) ) {
			return $levelText[$level];
		} else {
			return '';
		}
	}
	
	/**
	 * Get the URL path to /client
	 * @return string
	 */
	public static function styleUrlPath() {
		global $wgFlaggedRevsStylePath, $wgScriptPath;
		return str_replace( '$wgScriptPath', $wgScriptPath, $wgFlaggedRevsStylePath );
	}

	# ################ Permission functions #################	
	
	/**
	 * Returns true if a user can set $tag to $value.
	 * @param string $tag
	 * @param int $value
	 * @returns bool
	 * @TODO: dependency inject the User?
	 */
	public static function userCanSetTag( $tag, $value ) {
		global $wgUser;
		# Sanity check tag and value
		$levels = self::getTagLevels( $tag );
		$highest = count( $levels ) - 1;
		if( !$levels || $value < 0 || $value > $highest ) {
			return false; // flag range is invalid
		}
		$restrictions = self::getTagRestrictions();
		# No restrictions -> full access
		if ( !isset( $restrictions[$tag] ) ) {
			return true;
		}
		# Validators always have full access
		if ( $wgUser->isAllowed( 'validate' ) ) {
			return true;
		}
		# Check if this user has any right that lets him/her set
		# up to this particular value
		foreach ( $restrictions[$tag] as $right => $level ) {
			if ( $value <= $level && $level > 0 && $wgUser->isAllowed( $right ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns true if a user can set $flags.
	 * This checks if the user has the right to review
	 * to the given levels for each tag.
	 * @param array $flags, suggested flags
	 * @param array $oldflags, pre-existing flags
	 * @returns bool
	 * @TODO: dependency inject the User?
	 */
	public static function userCanSetFlags( $flags, $oldflags = array() ) {
		global $wgUser;
		if ( !$wgUser->isAllowed( 'review' ) ) {
			return false; // User is not able to review pages
		}
		# Check if all of the required site flags have a valid value
		# that the user is allowed to set.
		foreach ( self::getDimensions() as $qal => $levels ) {
			$level = isset( $flags[$qal] ) ? $flags[$qal] : 0;
			$highest = count( $levels ) - 1; // highest valid level
			if ( !self::userCanSetTag( $qal, $level ) ) {
				return false; // user cannot set proposed flag
			} elseif ( isset( $oldflags[$qal] )
				&& !self::userCanSetTag( $qal, $oldflags[$qal] ) )
			{
				return false; // user cannot change old flag
			}
		}
		return true;
	}

	/**
	* Check if a user can set the autoreview restiction level to $right
	* @param string $right the level
	* @returns bool
	* @TODO: dependency inject the User?
	*/
	public static function userCanSetAutoreviewLevel( $right ) {
		global $wgUser;
		if ( $right == '' ) {
			return true; // no restrictions (none)
		}
		if ( !in_array( $right, FlaggedRevs::getRestrictionLevels() ) ) {
			return false; // invalid restriction level
		}
		# Don't let them choose levels above their own rights
		if ( $right == 'sysop' ) {
			// special case, rewrite sysop to protect and editprotected
			if ( !$wgUser->isAllowed( 'protect' ) && !$wgUser->isAllowed( 'editprotected' ) ) {
				return false;
			}
		} else if ( !$wgUser->isAllowed( $right ) ) {
			return false;
		}
		return true;
	}

	# ################ Parsing functions #################

	/**
	 * @param string $text
	 * @param Title $title
	 * @param integer $id, revision id
	 * @return array( string, array, array, array, int )
	 * All included pages/arguments are expanded out
	 */
	public static function expandText( $text = '', Title $title, $id ) {
		global $wgParser;
		# Make our hooks trigger (force unstub so setting doesn't get lost)
		$wgParser->firstCallInit();
		$wgParser->fr_isStable = ( self::inclusionSetting() != FR_INCLUDES_CURRENT );
		# Parse with default options
		$options = self::makeParserOptions();
		$outputText = $wgParser->preprocess( $text, $title, $options, $id );
		$out =& $wgParser->mOutput;
		$data = array( $outputText, $out->mTemplates, $out->mTemplateIds,
			$out->fr_includeErrors, $out->fr_newestTemplateID );
		# Done!
		$wgParser->fr_isStable = false;
		# Return data array
		return $data;
	}

	/**
	 * Get the HTML output of a revision based on $text.
	 * @param Article $article
	 * @param string $text
	 * @param int $id
	 * @return ParserOutput
	 */
	public static function parseStableText( Article $article, $text, $id ) {
		global $wgParser;
		$title = $article->getTitle(); // avoid pass-by-reference error
		# Make our hooks trigger (force unstub so setting doesn't get lost)
		$wgParser->firstCallInit();
		$wgParser->fr_isStable = ( self::inclusionSetting() != FR_INCLUDES_CURRENT );
		# Don't show section-edit links, they can be old and misleading
		$options = self::makeParserOptions();
		# Parse the new body, wikitext -> html
	   	$parserOut = $wgParser->parse( $text, $title, $options, true, true, $id );
	   	# Done with parser!
	   	$wgParser->fr_isStable = false;
	   	return $parserOut;
	}
	
	/**
	* Get standard parser options
	* @param User $user (optional)
	* @returns ParserOptions
	*/
	public static function makeParserOptions( $user = null ) {
		global $wgUser;
		$user = $user ? $user : $wgUser; // assume current
		$options = ParserOptions::newFromUser( $user );
		# Show inclusion/loop reports
		$options->enableLimitReport();
		# Fix bad HTML
		$options->setTidy( true );
		return $options;
	}
	
	/**
	* @param Article $article
	* @param User $user
	* @return ParserOutput
	* Get the page cache for the top stable revision of an article
	*/
	public static function getPageCache( Article $article, $user ) {
		global $parserMemc, $wgCacheEpoch;
		wfProfileIn( __METHOD__ );
		# Make sure it is valid
		if ( !$article->getId() ) {
			wfProfileOut( __METHOD__ );
			return null;
		}
		$parserCache = ParserCache::singleton();
		$key = self::getCacheKey( $parserCache, $article, $user );
		# Get the cached HTML
		wfDebug( "Trying parser cache $key\n" );
		$value = $parserMemc->get( $key );
		if ( is_object( $value ) ) {
			wfDebug( "Found.\n" );
			# Delete if article has changed since the cache was made
			$canCache = $article->checkTouched();
			$cacheTime = $value->getCacheTime();
			$touched = $article->mTouched;
			if ( !$canCache || $value->expired( $touched ) ) {
				if ( !$canCache ) {
					wfIncrStats( "pcache_miss_invalid" );
					wfDebug( "Invalid cached redirect, touched $touched, epoch $wgCacheEpoch, cached $cacheTime\n" );
				} else {
					wfIncrStats( "pcache_miss_expired" );
					wfDebug( "Key expired, touched $touched, epoch $wgCacheEpoch, cached $cacheTime\n" );
				}
				$parserMemc->delete( $key );
				$value = false;
			} else {
				if ( isset( $value->mTimestamp ) ) {
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
	public static function getCacheKey( $parserCache, Article $article, $user ) {
		$key = $parserCache->getKey( $article, $user );
		$key = str_replace( ':pcache:', ':stable-pcache:', $key );
		return $key;
	}

	/**
	* @param Article $article
	* @param User $user
	* @param parserOutput $parserOut
	* Updates the stable cache of a page with the given $parserOut
	*/
	public static function updatePageCache(
		Article $article, $user, ParserOutput $parserOut = null
	) {
		global $parserMemc, $wgParserCacheExpireTime, $wgEnableParserCache;
		# Make sure it is valid and $wgEnableParserCache is enabled
		if ( !$wgEnableParserCache || is_null( $parserOut ) )
			return false;

		$parserCache = ParserCache::singleton();
		$key = self::getCacheKey( $parserCache, $article, $user );
		# Add cache mark to HTML
		$now = wfTimestampNow();
		$parserOut->setCacheTime( $now );
		# Save the timestamp so that we don't have to load the revision row on view
		$parserOut->mTimestamp = $article->getTimestamp();
		$parserOut->mText .= "\n<!-- Saved in stable version parser cache with key $key and timestamp $now -->";
		# Set expire time
		if ( $parserOut->containsOldMagic() ) {
			$expire = 3600; // 1 hour
		} else {
			$expire = $wgParserCacheExpireTime;
		}
		# Save to objectcache
		$parserMemc->set( $key, $parserOut, $expire );

		return true;
	}
	
	/**
	 * @param int $revId
	 * @param array $tmpParams (like ParserOutput template IDs)
	 * @param array $imgParams (like ParserOutput image time->sha1 pairs)
	 * Set the template/image versioning cache for parser
	 */
	public static function setIncludeVersionCache( $revId, array $tmpParams, array $imgParams ) {
		self::load();
		self::$includeVersionCache[$revId] = array();
		self::$includeVersionCache[$revId]['templates'] = $tmpParams;
		self::$includeVersionCache[$revId]['files'] = $imgParams;
	}
	
	/**
	 * Destroy the template/image versioning cache instance for parser
	 */
	public static function clearIncludeVersionCache( $revId ) {
		self::load();
		if ( isset( self::$includeVersionCache[$revId] ) ) {
			unset( self::$includeVersionCache[$revId] );
		}
	}

	/**
	 * Should the params be in the process cache?
	 */
	public static function useProcessCache( $revId ) {
		self::load();
		if ( isset( self::$includeVersionCache[$revId] ) ) {
			return true;
		}
		return false;
	}
	
	/**
	 * Get template versioning cache for parser
	 * @param int $revID
	 * @param int $namespace
	 * @param string $dbKey
	 * @returns mixed (integer/null)
	 */
	public static function getTemplateIdFromCache( $revId, $namespace, $dbKey ) {
		self::load();
		if ( isset( self::$includeVersionCache[$revId] ) ) {
			if ( isset( self::$includeVersionCache[$revId]['templates'][$namespace] ) ) {
				if ( isset( self::$includeVersionCache[$revId]['templates'][$namespace][$dbKey] ) ) {
					return self::$includeVersionCache[$revId]['templates'][$namespace][$dbKey];
				}
			}
			return false; // missing?
		}
		return null; // cache not found
	}
	
	/**
	 * Get image versioning cache for parser
	 * @param int $revID
	 * @param string $dbKey
	 * @returns mixed (array/null)
	 */
	public static function getFileVersionFromCache( $revId, $dbKey ) {
		self::load();
		if ( isset( self::$includeVersionCache[$revId] ) ) {
			# All NS_FILE, no need to check namespace
			if ( isset( self::$includeVersionCache[$revId]['files'][$dbKey] ) ) {
				$time_SHA1 = self::$includeVersionCache[$revId]['files'][$dbKey];
				foreach ( $time_SHA1 as $time => $sha1 ) {
					// Should only be one, but this is an easy check
					return array( $time, $sha1 );
				}
				return array( false, "" ); // missing?
			}
		}
		return null; // cache not found
	}
	
	# ################ Synchronization and link update functions #################

	/**
	 * Check if all includes in $stableOutput are the same as those in $currentOutput
	 * @param ParserOutput $stableOutput
	 * @param ParserOutput $currentOutput
	 * @return bool
	 */
	public static function includesAreSynced(
		ParserOutput $stableOutput, ParserOutput $currentOutput
	) {
		$sTmpls = $stableOutput->mTemplateIds;
		$cTmpls = $currentOutput->mTemplateIds;
		foreach ( $sTmpls as $name => $revId ) {
			if ( isset( $cTmpls[$name] ) && $cTmpls[$name] != $revId ) {
				return false; // updated/created
			}
		}
		$sFiles = $stableOutput->fr_ImageSHA1Keys;
		$cFiles = $currentOutput->fr_ImageSHA1Keys;
		foreach ( $sFiles as $name => $timeKey ) {
			foreach ( $timeKey as $sTs => $sSha1 ) {
				if ( isset( $cFiles[$name] ) && !isset( $cFiles[$name][$sTs] ) ) {
					return false; // updated/created
				}
			}
		}
		return true;
	}

	/**
	 * @param string $val
	 * @return Object (val,time) tuple
	 * Get a memcache storage object
	 */
	public static function makeMemcObj( $val ) {
		$data = (object) array();
		$data->value = $val;
		$data->time = wfTimestampNow();
		return $data;
	}
	
	/**
	* @param mixed $data makeMemcObj() tuple (false/Object)
	* @param Article $article
	* @return mixed
	* Return memc value if not expired
	*/
	public static function getMemcValue( $data, Article $article ) {
		if ( is_object( $data ) && $data->time >= $article->getTouched() ) {
			return $data->value;
		}
		return false;
	}

 	/**
	* @param Article $article
	* @param Revision $rev, the new stable version
	* @param mixed $latest, the latest rev ID (optional)
	* Updates the tracking tables and pending edit count cache. Called on edit.
	*/
	public static function updateStableVersion( Article $article, Revision $rev, $latest = null ) {
		if ( !$article->getId() ) {
			return true; // no bogus entries
		}
		# Get the latest revision ID if not set
		if ( !$latest ) {
			$latest = $article->getTitle()->getLatestRevID( GAID_FOR_UPDATE );
		}
		# Get the highest quality revision (not necessarily this one)
		$dbw = wfGetDB( DB_MASTER );
		$maxQuality = $dbw->selectField( array( 'flaggedrevs', 'revision' ),
			'fr_quality',
			array( 'fr_page_id' => $article->getId(),
				'rev_id = fr_rev_id',
				'rev_page = fr_page_id',
				'rev_deleted & ' . Revision::DELETED_TEXT => 0
			),
			__METHOD__,
			array( 'ORDER BY' => 'fr_quality DESC', 'LIMIT' => 1 )
		);
		# Get the timestamp of the edit after the stable version (if any)
		$revId = $rev->getId();
		if ( $latest != $revId ) {
			# Get the latest revision ID
			$timestamp = $rev->getTimestamp();
			$nextTimestamp = $dbw->selectField( 'revision',
				'rev_timestamp',
				array( 'rev_page' => $article->getId(),
					"rev_timestamp > " . $dbw->addQuotes( $dbw->timestamp( $timestamp ) ) ),
				__METHOD__,
				array( 'ORDER BY' => 'rev_timestamp ASC', 'LIMIT' => 1 )
			);
		} else {
			$nextTimestamp = null;
		}
		# Alter table metadata
		$dbw->replace( 'flaggedpages',
			array( 'fp_page_id' ),
			array(
				'fp_stable'        => $revId,
				'fp_reviewed'      => ( $latest == $revId ) ? 1 : 0,
				'fp_quality'       => ( $maxQuality === false ) ? null : $maxQuality,
				'fp_page_id'       => $article->getId(),
				'fp_pending_since' => $nextTimestamp ? $dbw->timestamp( $nextTimestamp ) : null
			),
			__METHOD__
		);
		# Alter pending edit tracking table
		self::updatePendingList( $article, $latest );
		return true;
	}
	
 	/**
	* @param Article $article
	* @param mixed $latest, the latest rev ID (optional)
	* Updates the flaggedpage_pending table
	*/
	public static function updatePendingList( Article $article, $latest = null ) {
		$data = array();
		$level = self::pristineVersions() ? FR_PRISTINE : FR_QUALITY;
		if ( !self::qualityVersions() )
			$level = FR_SIGHTED;
		# Get the latest revision ID if not set
		if ( !$latest ) {
			$latest = $article->getTitle()->getLatestRevID( GAID_FOR_UPDATE );
		}
		$pageId = $article->getId();
		# Update pending times for each level, going from highest to lowest
		$dbw = wfGetDB( DB_MASTER );
		$higherLevelId = 0;
		$higherLevelTS = '';
		while ( $level >= 0 ) {
			# Get the latest revision of this level...
			$row = $dbw->selectRow( array( 'flaggedrevs', 'revision' ),
				array( 'fr_rev_id', 'rev_timestamp' ),
				array( 'fr_page_id' => $pageId,
					'fr_quality' => $level,
					'rev_id = fr_rev_id',
					'rev_page = fr_page_id',
					'rev_deleted & ' . Revision::DELETED_TEXT => 0,
					'rev_id > ' . intval( $higherLevelId )
				),
				__METHOD__,
				array( 'ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 )
			);
			# If there is a revision of this level, track it...
			# Revisions reviewed to one level  count as reviewed
			# at the lower levels (i.e. quality -> sighted).
			if ( $row ) {
				$id = $row->fr_rev_id;
				$ts = $row->rev_timestamp;
			} else {
				$id = $higherLevelId; // use previous (quality -> sighted)
				$ts = $higherLevelTS; // use previous (quality -> sighted)
			}
			# Get edits that actually are pending...
			if ( $id && $latest > $id ) {
				# Get the timestamp of the edit after this version (if any)
				$nextTimestamp = $dbw->selectField( 'revision',
					'rev_timestamp',
					array( 'rev_page' => $pageId, "rev_timestamp > " . $dbw->addQuotes( $ts ) ),
					__METHOD__,
					array( 'ORDER BY' => 'rev_timestamp ASC', 'LIMIT' => 1 )
				);
				$data[] = array(
					'fpp_page_id'       => $pageId,
					'fpp_quality'       => $level,
					'fpp_rev_id'        => $id,
					'fpp_pending_since' => $nextTimestamp
				);
				$higherLevelId = $id;
				$higherLevelTS = $ts;
			}
			$level--;
		}
		# Clear any old junk, and insert new rows
		$dbw->delete( 'flaggedpage_pending', array( 'fpp_page_id' => $pageId ), __METHOD__ );
		$dbw->insert( 'flaggedpage_pending', $data, __METHOD__ );
		return true;
	}
	
	/**
	* Resets links for a page when changed (other than edits)
	*/
	public static function articleLinksUpdate( Article $article ) {
		global $wgUser, $wgParser;
		# Update the links tables as the stable version may now be the default page...
		$parserCache = ParserCache::singleton();
		$anon = new User(); // anon cache most likely to exist
		$poutput = $parserCache->get( $article, $anon );
		if ( $poutput == false && $wgUser->getId() )
			$poutput = $parserCache->get( $article, $wgUser );
		if ( $poutput == false ) {
			$text = $article->getContent();
			$options = self::makeParserOptions();
			$poutput = $wgParser->parse( $text, $article->getTitle(), $options );
		}
		$u = new LinksUpdate( $article->getTitle(), $poutput );
		$u->doUpdate(); // this will trigger our hook to add stable links too...
		return true;
	}

	/**
	* Resets links for a page when changed (other than edits)
	*/
	public static function titleLinksUpdate( Title $title ) {
		return self::articleLinksUpdate( new Article( $title ) );
	}
	
	# ################ Revision functions #################

	/**
	 * Get flags for a revision
	 * @param Title $title
	 * @param int $rev_id
	 * @return Array
	*/
	public static function getRevisionTags( Title $title, $rev_id ) {
		$dbr = wfGetDB( DB_SLAVE );
		$tags = $dbr->selectField( 'flaggedrevs', 'fr_tags',
			array( 'fr_rev_id' => $rev_id,
				'fr_page_id' => $title->getArticleId() ),
			__METHOD__ );
		$tags = $tags ? $tags : "";
		return FlaggedRevision::expandRevisionTags( strval( $tags ) );
	}
	
	/**
	 * @param int $page_id
	 * @param int $rev_id
	 * @param $flags, FR_MASTER
	 * @returns mixed (int or false)
	 * Get quality of a revision
	 */
	public static function getRevQuality( $page_id, $rev_id, $flags = 0 ) {
		$db = ( $flags & FR_MASTER ) ?
			wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		return $db->selectField( 'flaggedrevs',
			'fr_quality',
			array( 'fr_page_id' => $page_id, 'fr_rev_id' => $rev_id ),
			__METHOD__,
			array( 'USE INDEX' => 'PRIMARY' )
		);
	}
	
	/**
	 * @param Title $title
	 * @param int $rev_id
	 * @param $flags, FR_MASTER
	 * @returns bool
	 * Useful for quickly pinging to see if a revision is flagged
	 */
	public static function revIsFlagged( Title $title, $rev_id, $flags = 0 ) {
		$quality = self::getRevQuality( $title->getArticleId(), $rev_id, $flags );
		return ( $quality !== false );
	}
	
	/**
	 * Get the "prime" flagged revision of a page
	 * @param Article $article
	 * @returns mixed (integer/false)
	 * Will not return a revision if deleted
	 */
	public static function getPrimeFlaggedRevId( Article $article ) {
		$dbr = wfGetDB( DB_SLAVE );
		# Get the highest quality revision (not necessarily this one).
		$oldid = $dbr->selectField( array( 'flaggedrevs', 'revision' ),
			'fr_rev_id',
			array(
				'fr_page_id' => $article->getId(),
				'rev_page = fr_page_id',
				'rev_id = fr_rev_id'
			),
			__METHOD__,
			array(
				'ORDER BY' => 'fr_quality DESC, fr_rev_id DESC',
				'USE INDEX' => array( 'flaggedrevs' => 'page_qal_rev', 'revision' => 'PRIMARY' )
			)
		);
		return $oldid;
	}
	
	/**
	 * Mark a revision as patrolled if needed
	 * @param Revision $rev
	 * @returns bool DB write query used
	 */
	public static function markRevisionPatrolled( Revision $rev ) {
		$rcid = $rev->isUnpatrolled();
		# Make sure it is now marked patrolled...
		if ( $rcid ) {
			$dbw = wfGetDB( DB_MASTER );
			$dbw->update( 'recentchanges',
				array( 'rc_patrolled' => 1 ),
				array( 'rc_id' => $rcid ),
				__METHOD__
			);
			return true;
		}
		return false;
	}
	
	# ################ Page configuration functions #################

	/**
	 * Get visibility settings/restrictions for a page
	 * @param Title $title, page title
	 * @param int $flags, FR_MASTER
	 * @returns array (associative) (select,override,autoreview,expiry)
	 */
	public static function getPageVisibilitySettings( Title $title, $flags = 0 ) {
		$db = ( $flags & FR_MASTER ) ?
			wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		$row = $db->selectRow( 'flaggedpage_config',
			array( 'fpc_select', 'fpc_override', 'fpc_level', 'fpc_expiry' ),
			array( 'fpc_page_id' => $title->getArticleID() ),
			__METHOD__
		);
		if ( $row ) {
			# This code should be refactored, now that it's being used more generally.
			$expiry = Block::decodeExpiry( $row->fpc_expiry );
			# Only apply the settings if they haven't expired
			if ( !$expiry || $expiry < wfTimestampNow() ) {
				$row = null; // expired
				self::purgeExpiredConfigurations();
				self::titleLinksUpdate( $title ); // re-find stable version
				$title->invalidateCache(); // purge squid/memcached
			}
		}
		// Is there a non-expired row?
		if ( $row ) {
			$precedence = intval( $row->fpc_select );
			if ( self::useProtectionLevels() || !self::isValidPrecedence( $precedence ) ) {
				$precedence = self::getPrecedence(); // site default; ignore fpc_select
			}
			$level = $row->fpc_level;
			if ( !self::isValidRestriction( $row->fpc_level ) ) {
				$level = ''; // site default; ignore fpc_level
			}
			$config = array(
				'select' 	 => $precedence,
				'override'   => $row->fpc_override ? 1 : 0,
				'autoreview' => $level,
				'expiry'	 => Block::decodeExpiry( $row->fpc_expiry ) // TS_MW
			);
			# If there are protection levels defined check if this is valid...
			if ( self::useProtectionLevels() ) {
				$level = self::getProtectionLevel( $config );
				if ( $level == 'invalid' || $level == 'none' ) {
					// If 'none', make sure expiry is 'infinity'
					$config = self::getDefaultVisibilitySettings(); // revert to default (none)
				}
			}
		} else {
			# Return the default config if this page doesn't have its own
			$config = self::getDefaultVisibilitySettings();
		}
		return $config;
	}

	/**
	 * Get default page configuration settings
	 */
	public static function getDefaultVisibilitySettings() {
		return array(
			# Keep this consistent across settings: 
			# # 2 = pristine -> quality -> stable; 
			# # 1 = quality -> stable
			# # 0 = none
			'select'     => self::getPrecedence(),
			# Keep this consistent across settings:
			# # 1 -> override, 0 -> don't
			'override'   => self::isStableShownByDefault() ? 1 : 0,
			'autoreview' => '',
			'expiry'     => 'infinity'
		);
	}

	
	/**
	 * Find what protection level a config is in
	 * @param array $config
	 * @returns string
	 */
	public static function getProtectionLevel( array $config ) {
		if ( !self::useProtectionLevels() ) {
			throw new MWException( 'getProtectionLevel() called with $wgFlaggedRevsProtection off' );
		}
		$defaultConfig = self::getDefaultVisibilitySettings();
		# Check if the page is not protected at all...
		if ( $config['override'] == $defaultConfig['override']
			&& $config['autoreview'] == '' )
		{
			return "none"; // not protected
		}
		# All protection levels have 'override' on
		if ( $config['override'] ) {
			# The levels are defined by the 'autoreview' settings
			if ( in_array( $config['autoreview'], self::getRestrictionLevels() ) ) {
				return $config['autoreview'];
			}
		}
		return "invalid";
	}

	/**
	 * Check if an fpc_select value is valid
	 * @param int $select
	 */
	public static function isValidPrecedence( $select ) {
		$allowed = array( FLAGGED_VIS_QUALITY, FLAGGED_VIS_LATEST, FLAGGED_VIS_PRISTINE );
		return in_array( $select, $allowed, true );
	}

	/**
	 * Check if an fpc_level value is valid
	 * @param string $right
	 */
	public static function isValidRestriction( $right ) {
		if ( $right == '' ) {
			return true; // no restrictions (none)
		}
		return in_array( $right, self::getRestrictionLevels(), true );
	}

	/**
	 * Purge expired restrictions from the flaggedpage_config table.
	 * The stable version of pages may change and invalidation may be required.
	 */
	public static function purgeExpiredConfigurations() {
		$dbw = wfGetDB( DB_MASTER );
		$pageIds = array();
		$pagesClearTracking = $pagesRetrack = array();
		$config = self::getDefaultVisibilitySettings(); // config is to be reset
		$encCutoff = $dbw->addQuotes( $dbw->timestamp() );
		$ret = $dbw->select( 'flaggedpage_config',
			array( 'fpc_page_id', 'fpc_select' ),
			array( 'fpc_expiry < ' . $encCutoff ),
			__METHOD__
			// array( 'LOCK IN SHARE MODE' )
		);
		while ( $row = $dbw->fetchObject( $ret ) ) {
			// If FlaggedRevs got "turned off" for this page (due to not
			// having the stable version as the default), then clear it
			// from the tracking tables...
			if ( !$config['override'] && self::forDefaultVersionOnly() ) {
				$pagesClearTracking[] = $row->fpc_page_id; // no stable version
			// Check if the new (default) config has a different way
			// of selecting the stable version of this page...
			} else if ( $config['select'] !== intval( $row->fpc_select ) ) {
				$pagesRetrack[] = $row->fpc_page_id; // new stable version
			}
			$pageIds[] = $row->fpc_page_id; // page with expired config
		}
		// Clear the expired config for these pages
		if ( count( $pageIds ) ) {
			$dbw->delete( 'flaggedpage_config',
				array( 'fpc_page_id' => $pageIds, 'fpc_expiry < ' . $encCutoff ),
				__METHOD__ );
		}
		// Clear the tracking rows where needed
		if ( count( $pagesClearTracking ) ) {
			self::clearTrackingRows( $pagesClearTracking );
		}
		// Find and track the new stable version where needed
		foreach ( $pagesRetrack as $pageId ) {
			$title = Title::newFromId( $pageId, GAID_FOR_UPDATE );
			// Determine the new stable version and update the tracking tables...
			$srev = FlaggedRevision::newFromStable( $title, FR_MASTER, $config );
			if ( $srev ) {
				$article = new Article( $title );
				self::updateStableVersion( $article, $srev->getRevision(), $title->getArticleID() );
			} else {
				self::clearTrackingRows( $pageId ); // no stable version
			}
		}
	}
	
	# ################ Other utility functions #################

	/**
	* @param array $flags
	* @return bool, is this revision at basic review condition?
	*/
	public static function isSighted( array $flags ) {
		return self::tagsAtLevel( $flags, self::$minSL );
	}
	
	/**
	* @param array $flags
	* @return bool, is this revision at quality review condition?
	*/
	public static function isQuality( array $flags ) {
		return self::tagsAtLevel( $flags, self::$minQL );
	}

	/**
	* @param array $flags
	* @return bool, is this revision at pristine review condition?
	*/
	public static function isPristine( array $flags ) {
		return self::tagsAtLevel( $flags, self::$minPL );
	}
	
	// Checks if $flags meets $reqFlagLevels
	protected static function tagsAtLevel( array $flags, $reqFlagLevels ) {
		if ( empty( $flags ) ) {
			return false;
		}
		foreach ( self::$dimensions as $f => $x ) {
			if ( !isset( $flags[$f] ) || $reqFlagLevels[$f] > $flags[$f] ) {
				return false;
			}
		}
		return true;
	}
	
	/**
	* Get the quality tier of review flags
	* @param array $flags
	* @return int, flagging tier (-1 for non-sighted)
	*/
	public static function getLevelTier( array $flags ) {
		if ( self::isPristine( $flags ) )
			return FR_PRISTINE; // 2
		elseif ( self::isQuality( $flags ) )
			return FR_QUALITY; // 1
		elseif ( self::isSighted( $flags ) )
			return FR_SIGHTED; // 0
		else
			return -1;
	}

	/**
	 * Get global revision status precedence setting
	 * or a specific one if given a tag tier (e.g. FR_QUALITY).
	 * Returns one of FLAGGED_VIS_PRISTINE, FLAGGED_VIS_QUALITY, FLAGGED_VIS_LATEST.
	 *
	 * @param int config tier, optional (FR_PRISTINE,FR_QUALITY,FR_SIGHTED)
	 * @return int
	 */
	public static function getPrecedence( $configTier = null ) {
		global $wgFlaggedRevsPrecedence;
		if ( is_null( $configTier ) ) {
			$configTier = (int)$wgFlaggedRevsPrecedence;
		}
		switch( $configTier )
		{
			case FR_PRISTINE:
				$select = FLAGGED_VIS_PRISTINE;
				break;
			case FR_QUALITY:
				$select = FLAGGED_VIS_QUALITY;
				break;
			default:
				$select = FLAGGED_VIS_LATEST;
				break;
		}
		return $select;
	}
	
	/**
	 * Get minimum level tags for a tier
	 * @return array
	 */
	public static function quickTags( $tier ) {
		self::load();
		switch( $tier ) // select reference levels
		{
			case FR_PRISTINE:
				$minLevels = self::$minPL;
			case FR_QUALITY:
				$minLevels = self::$minQL;
			default:
				$minLevels = self::$minSL;
		}
		$flags = array();
		foreach ( self::getDimensions() as $tag => $x ) {
			$flags[$tag] = $minLevels[$tag];
		}
		return $flags;
	}

	/**
	 * Get minimum tags that are closest to $oldFlags
	 * given the site, page, and user rights limitations.
	 * @param array $oldFlags previous stable rev flags
	 * @TODO: dependency inject the User?
	 * @return mixed array or null
	 */
	public static function getAutoReviewTags( array $oldFlags ) {
		if ( !self::autoReviewEdits() ) {
			return null; // shouldn't happen
		}
		$flags = array();
		foreach ( self::getDimensions() as $tag => $levels ) {
			# Try to keep this tag val the same as the stable rev's
			$val = isset( $oldFlags[$tag] ) ? $oldFlags[$tag] : 1;
			$val = min( $val, self::maxAutoReviewLevel( $tag ) );
			# Dial down the level to one the user has permission to set
			while ( !self::userCanSetTag( $tag, $val ) ) {
				$val--;
				if ( $val <= 0 ) {
					return null; // all tags vals must be > 0
				}
			}
			$flags[$tag] = $val;
		}
		return $flags;
	}	

	/**
	* Get the list of reviewable namespaces
	* @return array
	*/
	public static function getReviewNamespaces() {
		self::load(); // validates namespaces
		return self::$reviewNamespaces;
	}
	
	/**
	* Get the list of patrollable namespaces
	* @return array
	*/
	public static function getPatrolNamespaces() {
		self::load(); // validates namespaces
		return self::$patrolNamespaces;
	}
	
	
	/**
	* Is this page in reviewable namespace?
	* Note: this checks $wgFlaggedRevsWhitelist
	* @param Title, $title
	* @return bool
	*/
	public static function inReviewNamespace( Title $title ) {
		global $wgFlaggedRevsWhitelist;
		$namespaces = self::getReviewNamespaces();
		$ns = ( $title->getNamespace() == NS_MEDIA ) ?
			NS_FILE : $title->getNamespace(); // Treat NS_MEDIA as NS_FILE
		# Check for MW: pages and whitelist for exempt pages
		if ( in_array( $title->getPrefixedDBKey(), $wgFlaggedRevsWhitelist ) ) {
			return false;
		}
		return ( in_array( $ns, $namespaces ) );
	}
	
	/**
	* Is this page in patrollable namespace?
	* @param Title, $title
	* @return bool
	*/
	public static function inPatrolNamespace( Title $title ) {
		$namespaces = self::getPatrolNamespaces();
		$ns = ( $title->getNamespace() == NS_MEDIA ) ?
			NS_FILE : $title->getNamespace(); // Treat NS_MEDIA as NS_FILE
		return ( in_array( $ns, $namespaces ) );
	}
	
   	/**
	* Clear FlaggedRevs tracking tables for this page
	* @param mixed $pageId (int or array)
	*/
	public static function clearTrackingRows( $pageId ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'flaggedpages', array( 'fp_page_id' => $pageId ), __METHOD__ );
		$dbw->delete( 'flaggedrevs_tracking', array( 'ftr_from' => $pageId ), __METHOD__ );
		$dbw->delete( 'flaggedpage_pending', array( 'fpp_page_id' => $pageId ), __METHOD__ );
	}

   	/**
	* Get params for a user
	* @param int $uid
	* @param string $DBName, optional wiki name
	* @returns array $params
	*/
	public static function getUserParams( $uid, $DBName = false ) {
		$dbw = wfGetDB( DB_MASTER, array(), $DBName );
		$row = $dbw->selectRow( 'flaggedrevs_promote',
			'frp_user_params',
			array( 'frp_user_id' => $uid ),
			__METHOD__
		);
		# Parse params
		$p = array(); // init
		if ( $row ) {
			$flatPars = explode( "\n", trim( $row->frp_user_params ) );
			foreach ( $flatPars as $pair ) {
				$m = explode( '=', trim( $pair ), 2 );
				$key = $m[0];
				$value = isset( $m[1] ) ? $m[1] : null;
				$p[$key] = $value;
			}
		}
		# Initialize fields as needed...
		if ( !isset( $p['uniqueContentPages'] ) )
			$p['uniqueContentPages'] = '';
		if ( !isset( $p['totalContentEdits'] ) )
			$p['totalContentEdits'] = 0;
		if ( !isset( $p['editComments'] ) )
			$p['editComments'] = 0;
		if ( !isset( $p['revertedEdits'] ) )
			$p['revertedEdits'] = 0;

		return $p;
	}
	
   	/**
	* Save params for a user
	* @param int $uid
	* @param array $params
	* @param string $DBName, optional wiki name
	* @returns bool success
	*/
	public static function saveUserParams( $uid, array $params, $DBName = false ) {
		$flatParams = '';
		foreach ( $params as $key => $value ) {
			$flatParams .= "{$key}={$value}\n";
		}
		$dbw = wfGetDB( DB_MASTER, array(), $DBName );
		$row = $dbw->replace( 'flaggedrevs_promote',
			array( 'frp_user_id' ),
			array( 'frp_user_id' => $uid, 'frp_user_params' => trim( $flatParams ) ),
			__METHOD__
		);
		return ( $dbw->affectedRows() > 0 );
	}

	# ################ Auto-review function #################

	/**
	* Automatically review an revision and add a log entry in the review log.
	*
	* This is called during edit operations after the new revision is added
	* and the page tables updated, but before LinksUpdate is called.
	*
	* $auto is here for revisions checked off to be reviewed. Auto-review
	* triggers on edit, but we don't want it to count as just automatic.
	* This also makes it so the user's name shows up in the page history.
	*
	* If $flags is given, then they will be the review tags. If not, the one
	* from the stable version will be used or minimal tags if that's not possible.
	* If no appropriate tags can be found, then the review will abort.
	*/
	public static function autoReviewEdit(
		Article $article, $user, $text, Revision $rev, array $flags = null, $auto = true
	) {
		wfProfileIn( __METHOD__ );
		$title = $article->getTitle();
		# Get current stable version ID (for logging)
		$oldSv = FlaggedRevision::newFromStable( $title );
		$oldSvId = $oldSv ? $oldSv->getRevId() : 0;
		# Set the auto-review tags from the prior stable version.
		# Normally, this should already be done and given here...
		if ( !is_array( $flags ) ) {
			if ( $oldSv ) {
				# Use the last stable version if $flags not given
				if ( $user->isAllowed( 'bot' ) ) {
					$flags = $oldSv->getTags(); // no change for bot edits
				} else {
					$flags = self::getAutoReviewTags( $oldSv->getTags() ); // account for perms
				}
			} else { // new page?
				$flags = self::quickTags( FR_SIGHTED ); // use minimal level
			}
			if ( !is_array( $flags ) ) {
				wfProfileOut( __METHOD__ );
				return false; // can't auto-review this revision
			}
		}
		$quality = 0;
		if ( self::isQuality( $flags ) ) {
			$quality = self::isPristine( $flags ) ? 2 : 1;
		}

		$tmpset = $imgset = array();
		$poutput = false;

		# Rev ID is not put into parser on edit, so do the same here.
		# Also, a second parse would be triggered otherwise.
		$editInfo = $article->prepareTextForEdit( $text ); // Parse the revision HTML output
		$poutput = $editInfo->output;

		$dbw = wfGetDB( DB_MASTER );		

		# NS:title -> rev ID mapping
		foreach ( $poutput->mTemplateIds as $namespace => $titleAndID ) {
			foreach ( $titleAndID as $dbkey => $id ) {
				$tmpset[] = array(
					'ft_rev_id' 	=> $rev->getId(),
					'ft_namespace'  => $namespace,
					'ft_title' 		=> $dbkey,
					'ft_tmp_rev_id' => $id
				);
			}
		}
		# Image -> timestamp mapping
		foreach ( $poutput->fr_ImageSHA1Keys as $dbkey => $timeAndSHA1 ) {
			foreach ( $timeAndSHA1 as $time => $sha1 ) {
				$fileIncludeData = array(
					'fi_rev_id' 		=> $rev->getId(),
					'fi_name' 			=> $dbkey,
					'fi_img_sha1' 		=> $sha1,
					// b/c: NULL becomes '' for old fi_img_timestamp def (non-strict)
					'fi_img_timestamp'  => $time ? $dbw->timestamp( $time ) : null
				);
				$imgset[] = $fileIncludeData;
			}
		}

		# If this is an image page, store corresponding file info
		$fileData = array();
		if ( $title->getNamespace() == NS_FILE ) {
			$file = $article instanceof ImagePage ?
				$article->getFile() : wfFindFile( $title );
			if ( is_object( $file ) && $file->exists() ) {
				$fileData['name'] = $title->getDBkey();
				$fileData['timestamp'] = $file->getTimestamp();
				$fileData['sha1'] = $file->getSha1();
			}
		}

		# Our review entry
		$flaggedRevision = new FlaggedRevision( array(
			'fr_page_id'       => $rev->getPage(),
			'fr_rev_id'	       => $rev->getId(),
			'fr_user'	       => $user->getId(),
			'fr_timestamp'     => $rev->getTimestamp(),
			'fr_comment'       => "",
			'fr_quality'       => $quality,
			'fr_tags'	       => FlaggedRevision::flattenRevisionTags( $flags ),
			'fr_img_name'      => $fileData ? $fileData['name'] : null,
			'fr_img_timestamp' => $fileData ? $fileData['timestamp'] : null,
			'fr_img_sha1'      => $fileData ? $fileData['sha1'] : null
		) );
		$flaggedRevision->insertOn( $tmpset, $imgset, $auto );
		# Update the article review log
		FlaggedRevsLogs::updateLog( $title, $flags, array(), '', $rev->getId(),
			$oldSvId, true, $auto );

		# If we know that this is now the new stable version 
		# (which it probably is), save it to the cache...
		$sv = FlaggedRevision::newFromStable( $article->getTitle(), FR_MASTER/*consistent*/ );
		if ( $sv && $sv->getRevId() == $rev->getId() ) {
			global $wgMemc;
			# Update stable page cache. Don't cache redirects;
			# it would go unused and complicate things.
			if ( !Title::newFromRedirect( $text ) ) {
				self::updatePageCache( $article, $user, $poutput  );
			}
			# Update page tracking fields
			self::updateStableVersion( $article, $rev, $rev->getId() );
			# We can set the sync cache key already.
			global $wgParserCacheExpireTime;
			$key = wfMemcKey( 'flaggedrevs', 'includesSynced', $article->getId() );
			$data = self::makeMemcObj( "true" );
			$wgMemc->set( $key, $data, $wgParserCacheExpireTime );
		} else if ( $sv ) {
			# Update tracking table
			self::updatePendingList( $article, $rev->getId() );
		} else {
			# Weird case: autoreview when flaggedrevs is deactivated for page
			self::clearTrackingRows( $article->getId() );
		}
		wfProfileOut( __METHOD__ );
		return true;
	}
	
	/**
	 * Get JS script params
	 */
	public static function getJSTagParams() {
		self::load();
		# Param to pass to JS function to know if tags are at quality level
		$tagsJS = array();
		foreach ( self::$dimensions as $tag => $x ) {
			$tagsJS[$tag] = array();
			$tagsJS[$tag]['levels'] = count( $x ) - 1;
			$tagsJS[$tag]['quality'] = self::$minQL[$tag];
			$tagsJS[$tag]['pristine'] = self::$minPL[$tag];
		}
		$params = array( 'tags' => (object)$tagsJS );
		return (object)$params;
	}
	
	/**
	 * Get template and image parameters from parser output
	 * @param FlaggedArticle $article
	 * @param array $templateIDs (from ParserOutput/OutputPage->mTemplateIds)
	 * @param array $imageSHA1Keys (from ParserOutput/OutputPage->fr_ImageSHA1Keys)
	 * @returns array( templateParams, imageParams, fileVersion )
	 */
	public static function getIncludeParams(
		FlaggedArticle $article, array $templateIDs, array $imageSHA1Keys
	) {
		$templateParams = $imageParams = $fileVersion = '';
		# NS -> title -> rev ID mapping
		foreach ( $templateIDs as $namespace => $t ) {
			foreach ( $t as $dbKey => $revId ) {
				$temptitle = Title::makeTitle( $namespace, $dbKey );
				$templateParams .= $temptitle->getPrefixedDBKey() . "|" . $revId . "#";
			}
		}
		# Image -> timestamp -> sha1 mapping
		foreach ( $imageSHA1Keys as $dbKey => $timeAndSHA1 ) {
			foreach ( $timeAndSHA1 as $time => $sha1 ) {
				$imageParams .= $dbKey . "|" . $time . "|" . $sha1 . "#";
			}
		}
		# For image pages, note the displayed image version
		if ( $article->getTitle()->getNamespace() == NS_FILE ) {
			$file = $article->getDisplayedFile(); // File obj
			if ( $file ) {
				$fileVersion = $file->getTimestamp() . "#" . $file->getSha1();
			}
		}
		return array( $templateParams, $imageParams, $fileVersion );
	}
}
