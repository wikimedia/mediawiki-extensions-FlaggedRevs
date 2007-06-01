<?
#(c) Joerg Baach, Aaron Schulz 2007 GPL

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

if( !defined( 'FLAGGED_CSS' ) ) define('FLAGGED_CSS', $wgScriptPath.'/extensions/FlaggedRevs/flaggedrevs.css' );
if( !defined( 'FLAGGED_JS' ) ) define('FLAGGED_JS', $wgScriptPath.'/extensions/FlaggedRevs/flaggedrevs.js' );

if( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}

$wgExtensionCredits['parserhook'][] = array(
	'author' => 'Aaron Schulz, Joerg Baach',
	'name' => 'Flagged Revisions',
	'url' => 'http://www.mediawiki.org/wiki/Extension:FlaggedRevs',
	'description' => 'Allows for revision of pages to be made static regardless of templates/images'
);

$wgExtensionCredits['specialpage'][] = array(
	'author' => 'Aaron Schulz, Joerg Baach',
	'name' => 'Review revisions',
	'url' => 'http://www.mediawiki.org/wiki/Extension:FlaggedRevs',
	'description' => 'Gives editors/reviewers the ability to validate revisions and stabilize pages'
);

$wgExtensionFunctions[] = 'efLoadReviewMessages';

# Load promotion UI
include_once('SpecialMakevalidate.php');
# Load review UI
extAddSpecialPage( dirname(__FILE__) . '/FlaggedRevsPage.body.php', 'Revisionreview', 'Revisionreview' );
# Load stableversions UI
extAddSpecialPage( dirname(__FILE__) . '/FlaggedRevsPage.body.php', 'Stableversions', 'Stableversions' );
# Load unreviewed pages list
extAddSpecialPage( dirname(__FILE__) . '/FlaggedRevsPage.body.php', 'Unreviewedpages', 'UnreviewedPages' );

function efLoadReviewMessages() {
	global $wgMessageCache, $RevisionreviewMessages, $wgOut, $wgJsMimeType;
	# Internationalization
	require( dirname( __FILE__ ) . '/FlaggedRevsPage.i18n.php' );
	foreach ( $RevisionreviewMessages as $lang => $langMessages ) {
		$wgMessageCache->addMessages( $langMessages, $lang );
	}
	# UI CSS
	$wgOut->addLink( array(
		'rel'	=> 'stylesheet',
		'type'	=> 'text/css',
		'media'	=> 'screen,projection',
		'href'	=> FLAGGED_CSS,
	) );
	# UI JS
	$wgOut->addScript( "<script type=\"{$wgJsMimeType}\" src=\"" . FLAGGED_JS . "\"></script>\n" );
}

#########
# IMPORTANT:
# When configuring globals, add them to localsettings.php and edit them THERE

# Revision tagging can slow development...
# For example, the main user base may become complacent,
# perhaps treat flagged pages as "done",
# or just be too damn lazy to always click "current".
# We may just want non-user visitors to see reviewed pages by default.
$wgFlaggedRevsAnonOnly = true;
# Can users make comments that will show up below flagged revisions?
$wgFlaggedRevComments = true;
# How long to cache stable versions? (seconds)
$wgFlaggedRevsExpire = 7 * 24 * 3600;

# When setting up new dimensions or levels, you will need to add some 
# MediaWiki messages for the UI to show properly; any sysop can do this.
# Define the tags we can use to rate an article, 
# and set the minimum level to have it become a "quality" version.
# "quality" revisions take precidence over other reviewed revisions
$wgFlaggedRevTags = array( 'accuracy'=>2, 'depth'=>2, 'style'=>1 );
# How high can we rate these revisions?
$wgFlaggedRevValues = 4;
# Who can set what flags to what level? (use -1 for not at all)
# Users cannot lower tags from a level they can't set
# Users with 'validate' can do anything regardless
# This is mainly for custom, less experienced, groups
$wgFlagRestrictions = array(
	'accuracy' => array('review' => 1),
	'depth'    => array('review' => 2),
	'style'    => array('review' => 3),
);

# Lets some users access the review UI and set some flags
$wgAvailableRights[] = 'review';
# Let some users set higher settings
$wgAvailableRights[] = 'validate';

# Define our basic reviewer class
$wgGroupPermissions['editor']['review']        = true;
$wgGroupPermissions['editor']['autopatrol']    = true;
$wgGroupPermissions['editor']['patrol']        = true;
$wgGroupPermissions['editor']['unwatchedpages'] = true;
$wgGroupPermissions['editor']['autoconfirmed'] = true;

# Defines extra rights for advanced reviewer class
$wgGroupPermissions['reviewer']['validate']  = true;
# Let this stand alone just in case...
$wgGroupPermissions['reviewer']['review']    = true;

# Define when users get automatically promoted to editors
$wgFlaggedRevsAutopromote = array('days' => 60, 'edits' => 500, 'email' => true);

# What icons to display

# Settings below this point should probably not be modified
############

# Add review log
$wgLogTypes[] = 'review';
$wgLogNames['review'] = 'review-logpage';
$wgLogHeaders['review'] = 'review-logpagetext';
$wgLogActions['review/approve']  = 'review-logentrygrant';
$wgLogActions['review/unapprove'] = 'review-logentryrevoke';

class FlaggedRevs {

	function __construct() {
		global $wgFlaggedRevTags, $wgFlaggedRevValues;
		
		$this->dimensions = array();
		foreach ( array_keys($wgFlaggedRevTags) as $tag ) {
			$this->dimensions[$tag] = array();
			for ($i=0; $i <= $wgFlaggedRevValues; $i++) {
				$this->dimensions[$tag][$i] = "$tag-$i";
			}
		}
	}
    
    /**
     * @param string $text
     * @returns string
     * All included pages/arguments are expanded out
     */
    public static function expandText( $text, $title, $id=null ) {
    	global $wgParser;
    	
        $text = $text ? $text : '';
    	$wgParser->isStable = true; // Causes our hooks to trigger
        
        $options = new ParserOptions;
        $options->setRemoveComments( true ); // Less banwidth?
        $outputText = $wgParser->preprocess( $text, $title, $options, $id );
        
        $wgParser->isStable = false; // Done!
        
        return $outputText;
    }
    
	/**
	 * @param Title $title
	 * @param string $text
	 * @param int $id
	 * @param ParserOptions $options
	 * @param int $timeframe, when the revision was reviewed
	 * Get the HTML of a revision based on how it was during $timeframe
	 */
    public static function parseStableText( $title, $text, $id=NULL, $options ) {
    	global $wgParser;
    	
    	$wgParser->isStable = true; // Causes our hooks to trigger
		# Don't show section-edit links
		# They can be old and misleading
		$options->setEditSection(false);
		# Parse the new body, wikitext -> html
       	$parserOut = $wgParser->parse( $text, $title, $options, true, true, $id );
       	
       	$wgParser->isStable = false; // Done!
       	
       	return $parserOut;
    }
    
	/**
	 * @param int $rev_id
	 * Get the text of a stable version
	 */	
    public static function getFlaggedRevText( $rev_id ) {
    	wfProfileIn( __METHOD__ );
    	
 		$db = wfGetDB( DB_SLAVE );
 		// Get the text from the flagged revisions table
		$result = $db->select( 
			array('flaggedrevs','revision'),
			array('fr_text'),
			array('fr_rev_id' => $rev_id, 'fr_rev_id = rev_id', 'rev_deleted = 0'), 
			__METHOD__,
			array('LIMIT' => 1) );
		if( $row = $db->fetchObject($result) ) {
			return $row->fr_text;
		}
		return NULL;
    }
    
	/**
	 * @param int $rev_id
	 * Returns a revision row
	 * Will not return if deleted
	 */		
	public static function getFlaggedRev( $rev_id ) {
		wfProfileIn( __METHOD__ );
    	
		$db = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
		$result = $db->select(
			array('flaggedrevs','revision'),
			array('fr_namespace', 'fr_title', 'fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp'),
			array('fr_rev_id' => $rev_id, 'fr_rev_id = rev_id', 'rev_deleted = 0'),
			__METHOD__ );
		// Sorted from highest to lowest, so just take the first one if any
		if( $row = $db->fetchObject($result) ) {
			return $row;
		}
		return NULL;
	}

	/**
	 * @param int $page_id
	 * Get rev ids of reviewed revs for a page
	 * Will include deleted revs here
	 */
    public static function getReviewedRevs( $page ) {
		wfProfileIn( __METHOD__ );
		  
		$db = wfGetDB( DB_SLAVE );
		$rows = array();
		
		$result = $db->select(
			array('flaggedrevs'),
			array('fr_rev_id','fr_quality'),
			array('fr_namespace' => $page->getNamespace(), 'fr_title' => $page->getDBkey() ),
			__METHOD__ ,
			array('ORDER BY' => 'fr_rev_id DESC') );
		while ( $row = $db->fetchObject($result) ) {
        	$rows[$row->fr_rev_id] = $row->fr_quality;
		}
		return $rows;
    }

	/**
	 * @param int $page_id
	 * @param int $from_rev
	 * Get number of revs since a certain revision
	 */
    public static function getRevCountSince( $page_id, $from_rev ) {   
		wfProfileIn( __METHOD__ );
		  
		$db = wfGetDB( DB_SLAVE );
		$count = $db->selectField('revision', 'COUNT(*)',
			array('rev_page' => $page_id, "rev_id > $from_rev"),
			__METHOD__ );
		
		return $count;
    }
	
	/**
	* static counterpart for getOverridingRev()
	*/
    public static function getOverridingPageRev( $title=NULL ) {
    	if( !$title ) return null;
    	
		$dbr = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
        $result = $dbr->select(
			array('flaggedrevs', 'revision'),
			array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp'),
			array('fr_namespace' => $title->getNamespace(), 'fr_title' => $title->getDBkey(), 'fr_quality >= 1',
			'fr_rev_id = rev_id', 'rev_page' => $title->getArticleID(), 'rev_deleted = 0'),
			__METHOD__,
			array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 ) );
		// Do we have one?
        if( !$row = $dbr->fetchObject($result) ) {
        	$result = $dbr->select(
				array('flaggedrevs', 'revision'),
				array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp'),
				array('fr_namespace' => $title->getNamespace(), 'fr_title' => $title->getDBkey(), 'fr_quality >= 1',
				'fr_rev_id = rev_id', 'rev_page' => $title->getArticleID(), 'rev_deleted = 0'),
				__METHOD__,
				array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 ) );
			if( !$row = $dbr->fetchObject($result) )
				return null;
		}
		return $row;
    }
    
	/**
	* static counterpart for getFlagsForRevision()
	*/
    public static function getFlagsForPageRev( $rev_id ) {
    	global $wgFlaggedRevTags;
    	
    	wfProfileIn( __METHOD__ );
    	// Set all flags to zero
    	$flags = array();
    	foreach( array_keys($wgFlaggedRevTags) as $tag ) {
    		$flags[$tag] = 0;
    	}
		$db = wfGetDB( DB_SLAVE );
		// Grab all the tags for this revision
		$result = $db->select(
			array('flaggedrevtags'),
			array('frt_dimension', 'frt_value'), 
			array('frt_rev_id' => $rev_id),
			__METHOD__ );
		// Iterate through each tag result
		while ( $row = $db->fetchObject($result) ) {
			$flags[$row->frt_dimension] = $row->frt_value;
		}
		
		return $flags;
	}
    
    public function addTagRatings( $flags ) {
        global $wgFlaggedRevTags;
    	$tag = "<p>";
		foreach ( $this->dimensions as $quality => $value ) {
			$valuetext = wfMsgHtml('revreview-' . $this->dimensions[$quality][$flags[$quality]]);
            $level = $flags[$quality];
            $minlevel = $wgFlaggedRevTags[$quality];
            if($level >= $minlevel)
                $classmarker = 2;
            elseif($level > 0)
                $classmarker = 1;
            else
                $classmarker = 0;
            $levelmarker = $level * 20 + 20; //XXX do this better
			$tag .= "&nbsp;<span class='fr-marker-$levelmarker'><strong>" . wfMsgHtml("revreview-$quality") . "</strong>: <span class='fr-text-value'>$valuetext&nbsp;</span>&nbsp;</span>\n";    
		}
		$tag .= '</p>';
		return $tag;
    }
    
    public static function ReviewNotes( $row ) {
    	global $wgUser, $wgFlaggedRevComments;
    	$notes = '';
    	if( !$row || !$wgFlaggedRevComments) return $notes;
    	
    	$skin = $wgUser->getSkin();
    	
    	if( $row->fr_comment ) {
    		$notes .= '<p><div class="flaggedrevs_notes plainlinks">';
    		$notes .= wfMsgExt('revreview-note', array('parse'), User::whoIs( $row->fr_user ) );
    		$notes .= '<i>' . $skin->formatComment( $row->fr_comment ) . '</i></div></p><br/>';
    	}
    	return $notes;
    }
    
	/**
	* @param Array $flags
	* @output bool, is this revision at quality condition?
	*/
    public static function isQuality( $flags ) {
    	global $wgFlaggedRevTags;
    	
    	foreach ( $wgFlaggedRevTags as $f => $v ) {
    		if( !isset($flags[$f]) || $v > $flags[$f] ) return false;
    	}
    	return true;
    }
    
	/**
	* @param Array $flags
	* @output bool, is this revision at optimal condition?
	*/
    public static function isPristine( $flags ) {
    	global $wgFlaggedRevValues;
    	
    	foreach ( $flags as $f => $v ) {
    		if( $v < $wgFlaggedRevValues ) return false;
    	}
    	return true;
    }
    
	/**
	* @param Array $flags
	* @output integer, lowest rating level
	*/
    public static function getLCQuality( $flags ) {
    	global $wgFlaggedRevValues;
    	
    	$min = false;
    	foreach ( $flags as $f => $v ) {
    		if( $min==false || $v < $min ) $min = $v;
    	}
    	return $min;
    }
    
	/**
	* @param Article $article
	* Get the page cache for the top stable revision of an article
	*/   
    public static function getPageCache( $article ) {
    	global $wgUser, $parserMemc, $wgCacheEpoch, $wgFlaggedRevsExpire;
    	
		$fname = 'FlaggedRevs::getPageCache';
		wfProfileIn( $fname );
		
    	// Make sure it is valid
    	if( !$article || !$article->getId() ) return NULL;
    	
    	$parserCache =& ParserCache::singleton();
    	$key = 'sv-' . $parserCache->getKey( $article, $wgUser );
		// Get the cached HTML
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
		
		wfProfileOut( $fname );
		
		return $value;		
    }
    
	/**
	* @param Article $article
	* @param parerOutput $parserOutput
	* Updates the stable cache of a page with the given $parserOutput
	*/
    public static function updatePageCache( $article, $parserOutput=NULL ) {
    	global $wgUser, $parserMemc, $wgFlaggedRevsExpire;
    	// Make sure it is valid
    	if( is_null($parserOutput) || !$article ) return false;
    	
		// Update the cache...
		$article->mTitle->invalidateCache();
		
		$parserCache =& ParserCache::singleton();
    	$key = 'sv-' . $parserCache->getKey( $article, $wgUser );
    	// Add cache mark to HTML
		$now = wfTimestampNow();
		$parserOutput->setCacheTime( $now );

		// Save the timestamp so that we don't have to load the revision row on view
		$parserOutput->mTimestamp = $article->getTimestamp();
    	
    	$parserOutput->mText .= "\n<!-- Saved in stable version parser cache with key $key and timestamp $now -->";
		// Set expire time
		if( $parserOutput->containsOldMagic() ){
			$expire = 3600; # 1 hour
		} else {
			$expire = $wgFlaggedRevsExpire;
		}
		// Save to objectcache
		$parserMemc->set( $key, $parserOutput, $expire );
		// Purge squid for this page only
		$article->mTitle->purgeSquid();
		
		return true;
    }
    
    function maybeUpdateMainCache( &$article, &$outputDone, &$pcache ) {
    	global $wgUser;
    	// Only trigger on article view for content pages, not for protect/delete/hist
		if( !$article || !$article->exists() || !$article->mTitle->isContentPage() || $action !='view' ) 
			return;
		
		// User must have review rights
		if( !$wgUser->isAllowed( 'review' ) ) return;
		
    	$parserOutput = $parserCache->get( $article, $wgUser );
		if( $parserOutput ) {
			// Clear older, incomplete, cached versions
			// We need the IDs of templates and timestamps of images used
			if( !isset($parserOutput->mTemplateIds) || !isset($parserOutput->mImageTimestamps) ) {
				$article->mTitle->invalidateCache();
			}
		}
    }
    
    function updateFromMove( &$movePageForm , &$oldtitle , &$newtitle ) {
    	$dbw = wfGetDB( DB_MASTER );
        $dbw->update( 'flaggedrevs',
			array('fr_namespace' => $newtitle->getNamespace(), 'fr_title' => $newtitle->getDBkey() ),
			array('fr_namespace' => $oldtitle->getNamespace(), 'fr_title' => $oldtitle->getDBkey() ),
			__METHOD__ );
    }
    
    public static function articleLinksUpdate( &$title ) {
    	global $wgUser, $wgParser;
    
    	$article = new Article( $title );
		// Update the links tables as the stable version may now be the default page...
		$parserCache =& ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if( $poutput==false ) {
			$text = $article->getContent();
			$poutput = $wgParser->parse($text, $article->mTitle, ParserOptions::newFromUser($wgUser));
			# Might as well save the cache while we're at it
			$parserCache->save( $poutput, $article, $wgUser );
		}
		$u = new LinksUpdate( $title, $poutput );
		$u->doUpdate(); // this will trigger our hook to add stable links too...
    }
    
    public static function extraLinksUpdate( &$title ) {
    	$fname = 'FlaggedRevs::extraLinksUpdate';
    	wfProfileIn( $fname );
		    	
    	if( !$title->isContentPage() ) return;
    	# Check if this page has a stable version
    	$sv = self::getOverridingPageRev( $title );
    	if( !$sv ) return;
    	# Retrieve the text
    	$text = self::getFlaggedRevText( $sv->fr_rev_id );
    	# Parse the revision
    	$options = new ParserOptions;
    	$poutput = self::parseStableText( $title, $text, $sv->fr_rev_id, $options );

    	# Might as well update the cache while we're at it
    	$article = new Article( $title );
    	FlaggedRevs::updatePageCache( $article, $poutput );

    	# Update the links tables to include these
    	# We want the UNION of links between the current
		# and stable version. Therefore, we only care about
		# links that are in the stable version and not the regular one.
		$u = new LinksUpdate( $article->mTitle, $poutput );

		# Page links
		$existing = $u->getExistingLinks();
		$u->incrTableUpdate( 'pagelinks', 'pl', array(),
			$u->getLinkInsertions( $existing ) );

		# Image links
		$existing = $u->getExistingImages();
		$u->incrTableUpdate( 'imagelinks', 'il', array(),
			$u->getImageInsertions( $existing ) );

		# Invalidate all image description pages which had links added
		$imageUpdates = array_diff_key( $u->mImages, $existing );
		$u->invalidateImageDescriptions( $imageUpdates );

		# External links
		$existing = $u->getExistingExternals();
		$u->incrTableUpdate( 'externallinks', 'el', array(),
	        $u->getExternalInsertions( $existing ) );

		# Language links
		$existing = $u->getExistingInterlangs();
		$u->incrTableUpdate( 'langlinks', 'll', array(),
			$u->getInterlangInsertions( $existing ) );

		# Template links
		$existing = $u->getExistingTemplates();
		$u->incrTableUpdate( 'templatelinks', 'tl', array(),
			$u->getTemplateInsertions( $existing ) );

		# Category links
		$existing = $u->getExistingCategories();
		$u->incrTableUpdate( 'categorylinks', 'cl', array(),
			$u->getCategoryInsertions( $existing ) );

		# Invalidate all categories which were added, deleted or changed (set symmetric difference)
		$categoryUpdates = array_diff_assoc( $u->mCategories, $existing );
		$u->invalidateCategories( $categoryUpdates );

		# Refresh links of all pages including this page
		# This will be in a separate transaction
		if( $u->mRecursive ) {
			$u->queueRecursiveJobs();
		}

		wfProfileOut( $fname );
    }
    
	static function parserFetchStableTemplate( &$parser, &$title, &$skip, &$id ) {
    	// Trigger for stable version parsing only
    	if( !isset($parser->isStable) || !$parser->isStable ) return;
    	
    	$dbr = wfGetDB( DB_SLAVE );
        $id = $dbr->selectField('flaggedtemplates', 'ft_tmp_rev_id',
			array('ft_rev_id' => $parser->mRevisionId,
				'ft_namespace' => $title->getNamespace(), 'ft_title' => $title->getDBkey() ),
			__METHOD__ );
		// Slave lag maybe? try master...
		if( $id===false ) {
			$dbw = wfGetDB( DB_MASTER );
			$id = $dbw->selectField('flaggedtemplates', 'ft_tmp_rev_id',
				array('ft_rev_id' => $parser->mRevisionId, 
					'ft_namespace' => $title->getNamespace(), 'ft_title' => $title->getDBkey() ),
				__METHOD__ );
		}
		if( !$id ) {
			$id = 0; // Zero for not found
			$skip = true;
		}
    }
    
	static function parserMakeStableImageLink( &$parser, &$nt, &$skip, &$time ) {
    	// Trigger for stable version parsing only
    	if( !isset($parser->isStable) || !$parser->isStable ) return;
    	
    	$dbr = wfGetDB( DB_SLAVE );
        $time = $dbr->selectField('flaggedimages', 'fi_img_timestamp',
			array('fi_rev_id' => $parser->mRevisionId, 'fi_name' => $nt->getDBkey() ),
			__METHOD__ );
		// Slave lag maybe? try master...
		if( $time===false ) {
    		$dbw = wfGetDB( DB_MASTER );
        	$time = $dbw->selectField('flaggedimages', 'fi_img_timestamp',
				array('fi_rev_id' => $parser->mRevisionId, 'fi_name' => $nt->getDBkey() ),
				__METHOD__ );
		}
		if( !$time ) {
			$time = 0; // Zero for not found
			$skip = true;
		}
    }
    
    static function galleryFindStableFileTime( &$ig, &$nt, &$time ) {
    	// Trigger for stable version parsing only
    	if( !isset($ig->isStable) || !$ig->isStable ) return;
    	
    	$dbr = wfGetDB( DB_SLAVE );
        $time = $dbr->selectField('flaggedimages', 'fi_img_timestamp',
			array('fi_rev_id' => $ig->mRevisionId, 'fi_name' => $nt->getDBkey() ),
			__METHOD__ );
		$time = $time ? $time : -1; // hack, will never find this
    }
    
    static function parserMakeGalleryStable( &$parser, &$ig ) {
    	// Trigger for stable version parsing only
    	if( !isset($parser->isStable) || !$parser->isStable ) return;
    	
    	$ig->isStable = true;
    }

	/**
	* Callback that autopromotes user according to the setting in 
    * $wgFlaggedRevsAutopromote
	*/
	private static function autoPromoteUser( &$article, &$user, &$text, &$summary, &$isminor, &$iswatch, &$section ) {
		global $wgUser, $wgFlaggedRevsAutopromote;
		
		$groups = $user->getGroups();
		$now = time();
		$usercreation = wfTimestamp(TS_UNIX,$user->mRegistration);
		$userage = floor(($now-$usercreation) / 86400);
		// Do not give this to bots
		if( in_array( 'bot', $groups ) ) return;
		// Check if we need to promote...
		$vars = $wgFlaggedRevsAutopromote;
		if( !in_array('editor',$groups) && $userage >= $vars['days'] && $user->getEditCount() >= $vars['edits']
			&& ( !$vars['email'] || $wgUser->isAllowed('emailconfirmed') ) ) {
    		# Do not re-add status if it was previously removed...
    		$fname = 'FlaggedRevs::autoPromoteUser';
			$db = wfGetDB( DB_SLAVE );
    		$result = $db->select('logging',
				array('log_user'),
				array("log_type='validate'", "log_action='revoke1'", 'log_namespace' => NS_USER, 'log_title' => $user->getName() ),
				$fname,
				array('LIMIT' => 1) );
			# Add rights if they were never removed
			if( !$db->numRows($result) ) {
				$user->addGroup('editor');
				# Lets NOT spam RC, set $RC to false
				$log = new LogPage( 'validate', false );
				$log->addEntry('grant1', $user->getUserPage(), wfMsgHtml('makevalidate-autosum') );
			}
		}
    }
}

class FlaggedArticle extends FlaggedRevs {
	/**
	 * Do the current URL params allow for overriding by stable revisions?
	 */		
    function pageOverride() {
    	global $wgTitle, $wgFlaggedRevsAnonOnly, $wgUser, $wgRequest, $action;
    	return !( ($wgFlaggedRevsAnonOnly && !$wgUser->isAnon()) || $action !='view' || !$wgTitle->isContentPage() || 
			$wgRequest->getVal('oldid') || $wgRequest->getVal('diff') || $wgRequest->getIntOrNull('stable')===0 );
	}

	 /**
	 * Replaces a page with the last stable version if possible
	 * Adds stable version status/info tags and notes
	 * Adds a quick review form on the bottom if needed
	 */
	function setPageContent( &$article, &$outputDone, &$pcache ) {
		global $wgRequest, $wgTitle, $wgOut, $action;
		// Only trigger on article view for content pages, not for protect/delete/hist
		if( !$article || !$article->exists() || !$article->mTitle->isContentPage() || $action !='view' ) 
			return;
		// Grab page and rev ids
		$pageid = $article->getId();
		$revid = $article->mRevision ? $article->mRevision->mId : $article->getLatest();
		if( !$revid ) return;
		
		$vis_id = $revid;
		$tag = ''; $notes = '';
		// Check the newest stable version...
		$stable = $quality = $pristine = false;
		if( $this->pageOverride() ) {
			$tfrev = $this->getOverridingRev( $article );
		} else {
			$tfrev = $this->getLatestStableRev( $article );
		}
		if( $wgRequest->getVal('diff') ) {
    		// Do not clutter up diffs any further...
		} else if( !is_null($tfrev) ) {
			global $wgParser, $wgLang;
			// Get flags and date
			$flags = $this->getFlagsForRevision( $tfrev->fr_rev_id );
			$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $tfrev->fr_timestamp), true );
			// Looking at some specific old rev or if flagged revs override only for anons
			if( !$this->pageOverride() ) {
				$revs_since = parent::getRevCountSince( $pageid, $tfrev->fr_rev_id );
				$tag .= wfMsgExt('revreview-newest', array('parseinline'), $tfrev->fr_rev_id, $time, $revs_since);
				// Construct some tagging
				$tag .= parent::addTagRatings( $flags );
			# Viewing the page normally: override the page
			} else {
				global $wgUser;

				$skin = $wgUser->getSkin();
				# See if this page is featured
				$stable = true;
				$quality = $this->isQuality( $flags );
				$pristine = $this->isPristine( $flags );
       			# We will be looking at the reviewed revision...
       			$vis_id = $tfrev->fr_rev_id;
       			$revs_since = parent::getRevCountSince( $pageid, $vis_id );
       			if( $quality )
       				$tag = wfMsgExt('revreview-quality', array('parseinline'), $vis_id, $article->getLatest(), $revs_since, $time);
				else
					$tag = wfMsgExt('revreview-basic', array('parseinline'), $vis_id, $article->getLatest(), $revs_since, $time);
				// Construct some tagging
				$tag .= ' <a href="javascript:toggleRevRatings()">' . wfMsg('revreview-toggle') . '</a>';
				$tag .= '<span id="mwrevisionratings" style="display:none">' . parent::addTagRatings( $flags ) . '</span>';
				# Try the stable page cache
				$parserOutput = parent::getPageCache( $article );
				# If no cache is available, get the text and parse it
				if( $parserOutput==false ) {
					$text = parent::getFlaggedRevText( $vis_id );
					$options = ParserOptions::newFromUser($wgUser);
       				$parserOutput = parent::parseStableText( $wgTitle, $text, $vis_id, $options );
       				# Update the general cache
       				parent::updatePageCache( $article, $parserOutput );
       			}
       			$wgOut->mBodytext = $parserOutput->getText();
       			# Show stable categories and interwiki links only
       			$wgOut->mCategoryLinks = array();
       			$wgOut->addCategoryLinks( $parserOutput->getCategories() );
       			$wgOut->mLanguageLinks = array();
       			$wgOut->addLanguageLinks( $parserOutput->getLanguageLinks() );
				$notes = parent::ReviewNotes( $tfrev );
				// Tell MW that parser output is done
				$outputDone = true;
				$pcache = false;
			}
			// Some checks for which tag CSS to use
			if( $pristine )
				$tag = '<div id="mwrevisiontag" class="flaggedrevs_tag3 plainlinks">'.$tag.'</div>';
			else if( $quality )
				$tag = '<div id="mwrevisiontag" class="flaggedrevs_tag2 plainlinks">'.$tag.'</div>';
			else if( $stable )
				$tag = '<div id="mwrevisiontag" class="flaggedrevs_tag1 plainlinks">'.$tag.'</div>';
			else
				$tag = '<div id="mwrevisiontag" class="flaggedrevs_notice plainlinks">'.$tag.'</div>';
			// Set the new body HTML, place a tag on top
			$wgOut->mBodytext = $tag . $wgOut->mBodytext . $notes;
		} else {
			$tag = '<div id="mwrevisiontag" class="mw-warning plainlinks">'.wfMsgExt('revreview-noflagged', array('parseinline')).'</div>';
			$wgOut->addHTML( $tag );
		}
    }
    
    function addToEditView( &$editform ) {
		global $wgRequest, $wgTitle, $wgOut;
		// Talk pages cannot be validated
		if( !$editform->mArticle || !$wgTitle->isContentPage() )
           return;
		// Find out revision id
		if( $editform->mArticle->mRevision ) {
       		$revid = $editform->mArticle->mRevision->mId;
		} else {
       		$revid = $editform->mArticle->getLatest();
       	}
		// Grab the ratings for this revision if any
		if( !$revid ) return;
		// Set new body html text as that of now
		$tag = '';
		// Check the newest stable version
		$tfrev = $this->getLatestStableRev( $editform->mArticle );
		if( is_object($tfrev) ) {
			global $wgParser, $wgLang;		
			$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $tfrev->fr_timestamp), true );
			$flags = $this->getFlagsForRevision( $tfrev->fr_rev_id );
			$revs_since = parent::getRevCountSince( $editform->mArticle->getID(), $tfrev->fr_rev_id );
			// Construct some tagging
			$tag = wfMsgExt('revreview-newest', array('parseinline'), $tfrev->fr_rev_id, $time, $revs_since );
			$tag .= parent::addTagRatings( $flags );
			$wgOut->addHTML( '<div id="mwrevisiontag" class="flaggedrevs_notice plainlinks">' . $tag . '</div><br/>' );
       }
    }
	
    function addReviewForm( &$out ) {
    	global $wgArticle, $action;

		if( !$wgArticle || !$wgArticle->exists() || !$wgArticle->mTitle->isContentPage() || $action !='view' ) 
			return;
		// Check if page is protected
		if( !$wgArticle->mTitle->quickUserCan( 'edit' ) ) {
			return;
		}
		// Get revision ID
		$revId = ( $wgArticle->mRevision ) ? $wgArticle->mRevision->mId : $wgArticle->getLatest();
		// We cannot review deleted revisions
		if( is_object($wgArticle->mRevision) && $wgArticle->mRevision->mDeleted ) 
			return;
    	// Add quick review links IF we did not override, otherwise, they might
		// review a revision that parses out newer templates/images than what they saw.
		// Revisions are always reviewed based on current templates/images.
		if( $this->pageOverride() ) {
			$tfrev = $this->getOverridingRev();
			if( $tfrev ) return;
		}
		$this->addQuickReview( $revId, $out, false );
    }
    
    function setPermaLink( &$sktmp, &$nav_urls, &$oldid, &$revid ) {
    	global $wgTitle;
		// Non-content pages cannot be validated
		if( !$wgTitle->isContentPage() || !$this->pageOverride() ) return;
		// Check for an overridabe revision
		$tfrev = $this->getOverridingRev();
		if( !$tfrev ) return;
		// Replace "permalink" with an actual permanent link
		$nav_urls['permalink'] = array(
			'text' => wfMsg( 'permalink' ),
			'href' => $sktmp->makeSpecialUrl( 'Stableversions', "oldid={$tfrev->fr_rev_id}" )
		);
		// Are we using the popular cite extension?
		if( isset($nav_urls['cite']) ) {
			$nav_urls['cite'] = array(
				'text' => wfMsg( 'cite_article_link' ),
				'href' => $sktmp->makeSpecialUrl( 'Cite', "page=" . wfUrlencode( "{$sktmp->thispage}" ) . "&id={$tfrev->fr_rev_id}" )
			);
		}
    }
    
    function setCurrentTab( &$sktmp, &$content_actions ) {
    	global $wgRequest, $wgFlaggedRevsAnonOnly, $wgUser, $action;
		// Get the subject page
		$title = $sktmp->mTitle->getSubjectPage();
		// Non-content pages cannot be validated
		if( !$title->isContentPage() || !$title->exists() ) return;
		$article = new Article( $title );
		// If we are viewing a page normally, and it was overrode
		// change the edit tab to a "current revision" tab
		if( !( $wgFlaggedRevsAnonOnly && !$wgUser->isAnon() ) ) {
       		$tfrev = $this->getOverridingRev( $article );
       		// No quality revs? Find the last reviewed one
       		if( !is_object($tfrev) ) return;
       		// Note that revisions may not be set to override for users
       		if( $this->pageOverride() ) {
       			# Remove edit option altogether
       			unset( $content_actions['edit']);
       			unset( $content_actions['viewsource']);
				# Straighten out order
				$new_actions = array(); $counter = 0;
				foreach( $content_actions as $tab_action => $data ) {
					if( $counter==1 ) {
       					# Set current rev tab AFTER the main tab is set
						$new_actions['current'] = array(
							'class' => '',
							'text' => wfMsg('revreview-current'),
							'href' => $title->getLocalUrl( 'stable=0' )
						);
					}
       				$new_actions[$tab_action] = $data;
       				$counter++;
       			}
       			# Reset static array
       			$content_actions = $new_actions;
    		} else if( $action != 'view' || $wgRequest->getVal('oldid') || $sktmp->mTitle->isTalkPage() ) {
				# Straighten out order
				$new_actions = array(); $counter = 0;
				foreach( $content_actions as $tab_action => $data ) {
					if( $counter==1 ) {
       					# Set current rev tab AFTER the main tab is set
						$new_actions['current'] = array(
							'class' => '',
							'text' => wfMsg('revreview-current'),
							'href' => $title->getLocalUrl( 'stable=0' )
						);
					}
       				$new_actions[$tab_action] = $data;
       				$counter++;
       			}
       			# Reset static array
       			$content_actions = $new_actions;
    		} else {
				# Straighten out order
				$new_actions = array(); $counter = 0;
				foreach( $content_actions as $tab_action => $data ) {
					if( $counter==1 ) {
       					# Set current rev tab AFTER the main tab is set
						$new_actions['current'] = array(
							'class' => 'selected',
							'text' => wfMsg('revreview-current'),
							'href' => $title->getLocalUrl( 'stable=0' )
						);
					}
       				$new_actions[$tab_action] = $data;
       				$counter++;
       			}
       			# Reset static array
       			$content_actions = $new_actions;
    		}
    	}
    }
       
    function addQuickReview( $id=NULL, $out ) {
		global $wgOut, $wgTitle, $wgUser, $wgFlaggedRevComments, $wgArticle, $wgRequest;
		// Hack, we don't want two forms!
		if( !$id || isset($this->formCount) && $this->formCount > 0 ) return;
		$this->formCount = 1;
		// User must have review rights
		if( !$wgUser->isAllowed( 'review' ) ) return;
		// Already flagged?
		$flags = $this->getFlagsForRevision( $id );
       
		$reviewtitle = SpecialPage::getTitleFor( 'Revisionreview' );
		$action = $reviewtitle->escapeLocalUrl( 'action=submit' );
		$form = Xml::openElement( 'form', array( 'method' => 'post', 'action' => $action ) );
		$form .= "<fieldset><legend>" . wfMsgHtml( 'revreview-flag', $id ) . "</legend>\n";
		$form .= wfMsgExt( 'revreview-text', array('parse') );
		$form .= wfHidden( 'title', $reviewtitle->getPrefixedText() );
		$form .= wfHidden( 'target', $wgTitle->getPrefixedText() );
		$form .= wfHidden( 'oldid', $id );
		$form .= wfHidden( 'action', 'submit');
        $form .= wfHidden( 'wpEditToken', $wgUser->editToken() );
        // It takes time to review, make sure that we record what the reviewer had in mind
        $form .= wfHidden( 'wpTimestamp', wfTimestampNow() );
		foreach( $this->dimensions as $quality => $levels ) {
			$options = ''; $disabled = '';
			foreach( $levels as $idx => $label ) {
				$selected = ( $flags[$quality]==$idx || $flags[$quality]==0 && $idx==1 ) ? 'selected=\'selected\'' : '';
				// Do not show options user's can't set unless that is the status quo
				if( !Revisionreview::userCan($quality, $flags[$quality]) ) {
					$disabled = 'disabled = true';
					$options .= "<option value='$idx' $selected>" . wfMsgHtml("revreview-$label") . "</option>\n";
				} else if( Revisionreview::userCan($quality, $idx) ) {
					$options .= "<option value='$idx' $selected>" . wfMsgHtml("revreview-$label") . "</option>\n";
				}
			}
			$form .= "\n" . wfMsgHtml("revreview-$quality") . ": <select name='wp$quality' $disabled>\n";
			$form .= $options;
			$form .= "</select>\n";
		}
        if( $wgFlaggedRevComments ) {
			$form .= "<br/><p>" . wfMsgHtml( 'revreview-notes' ) . "</p>" .
			"<p><textarea tabindex='1' name='wpNotes' id='wpNotes' rows='2' cols='80' style='width:100%'></textarea>" .	
			"</p>\n";
		}
        $form .= "<p>".wfInputLabel( wfMsgHtml( 'revreview-log' ), 'wpReason', 'wpReason', 60 )."\n";
        $imageParams = $templateParams = '';
        if( !isset($out->mTemplateIds) || !isset($out->mImageTimestamps) ) {
        	return; // something went terribly wrong...
        }
        // XXX: dirty hack, add NS:title -> rev ID mapping
        foreach( $out->mTemplateIds as $namespace => $title ) {
        	foreach( $title as $dbkey => $id ) {
        		$title = Title::makeTitle( $namespace, $dbkey );
        		$templateParams .= $title->getPrefixedText() . "|" . $id . "#";
        	}
        }
        $form .= Xml::hidden( 'templateParams', $templateParams ) . "\n";
        // XXX: dirty hack, image -> timestamp mapping
        foreach( $out->mImageTimestamps as $dbkey => $timestamp ) {
        	$imageParams .= $dbkey . "|" . $timestamp . "#";
        }
        $form .= Xml::hidden( 'imageParams', $imageParams ) . "\n";
        
		$form .= Xml::submitButton( wfMsgHtml( 'revreview-submit' ) ) . "</p></fieldset>";
		$form .= Xml::closeElement( 'form' );
		
		$wgOut->addHTML( '<hr style="clear:both"></hr>' . $form );
    }
    
    function addToPageHist( &$article ) {
    	global $wgUser;
    
    	$this->pageFlaggedRevs = array();
    	$rows = $this->getReviewedRevs( $article->getTitle() );
    	
    	// Try to keep the skin readily accesible
    	$this->skin = $wgUser->getSkin();
    	
    	if( !$rows ) return;
    	
    	foreach( $rows as $rev => $quality ) {
    		$this->pageFlaggedRevs[$rev] = $quality;
    	}
    }
    
    function addToHistLine( &$row, &$s ) {
    	global $wgUser;
    
    	if( isset($this->pageFlaggedRevs) && array_key_exists($row->rev_id,$this->pageFlaggedRevs) ) {
    		$msg = ($this->pageFlaggedRevs[$row->rev_id] >= 1) ? 'hist-quality' : 'hist-stable';
    		$special = SpecialPage::getTitleFor( 'Stableversions' );
    		$s .= ' <tt><small><strong>' . 
				$this->skin->makeLinkObj( $special, wfMsgHtml($msg), 'oldid='.$row->rev_id ) . 
				'</strong></small></tt>';
		}
    }

	/**
	 * Get latest quality rev, if not, the latest reviewed one
	 */
	function getOverridingRev( $article=NULL ) {
		if( !$row = $this->getLatestQualityRev( $article ) ) {
			if( !$row = $this->getLatestStableRev( $article ) ) {
				return null;
			}
		}
		return $row;
	}
    
	/**
	 * Get latest flagged revision that meets requirments
	 * per the $wgFlaggedRevTags variable
	 * This passes rev_deleted revisions
	 * This is based on the current article and caches results
	 * Accepts an argument because of the fact that the article
	 * object for edit mode is part of the editform; insert only
	 * the article object for the current page!
	 * @output array ( rev, flags )
	 */
	function getLatestQualityRev( $article=NULL ) {
		global $wgArticle;
		
		wfProfileIn( __METHOD__ );
		
		$article = $article ? $article : $wgArticle;
		$title = $article->getTitle();
        // Cached results available?
		if( isset($this->stablefound) ) {
			return ( $this->stablefound ) ? $this->stablerev : null;
		}
		$dbr = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
        $result = $dbr->select(
			array('flaggedrevs', 'revision'),
			array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp'),
			array('fr_namespace' => $title->getNamespace(), 'fr_title' => $title->getDBkey(), 'fr_quality >= 1',
			'fr_rev_id = rev_id', 'rev_page' => $article->getId(), 'rev_deleted = 0'),
			__METHOD__,
			array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 ) );
		// Do we have one?
        if( $row = $dbr->fetchObject($result) ) {
        	$this->stablefound = true;
			$this->stablerev = $row;
			return $row;
	    } else {
            $this->stablefound = false;    
            return null;
        }
    }
    
	/**
	 * Get latest flagged revision
	 * This passes rev_deleted revisions
	 * This is based on the current article and caches results
	 * Accepts an argument because of the fact that the article
	 * object for edit mode is part of the editform; insert only
	 * the article object for the current page!
	 * The cache here doesn't make sense for arbitrary articles
	 * @output array ( rev, flags )
	 */
	function getLatestStableRev( $article=NULL ) {
		global $wgArticle;
		
		wfProfileIn( __METHOD__ );
		
		$article = $article ? $article : $wgArticle;
		$title = $article->getTitle();
        // Cached results available?
		if( isset($this->latestfound) ) {
			return ( $this->latestfound ) ? $this->latestrev : NULL;
		}
		$dbr = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
        $result = $dbr->select( 
			array('flaggedrevs', 'revision'),
			array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp'),
			array('fr_namespace' => $title->getNamespace(), 'fr_title' => $title->getDBkey(),
			'fr_rev_id = rev_id', 'rev_page' => $article->getId(), 'rev_deleted = 0'),
			__METHOD__,
			array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 ) );
		// Do we have one?
        if( $row = $dbr->fetchObject($result) ) {
        	$this->latestfound = true;
			$this->latestrev = $row;
			return $row;
	    } else {
            $this->latestfound = false;    
            return null;
        }
    }
    
	/**
	 * @param int $rev_id
	 * Return an array output of the flags for a given revision
	 */	
    public function getFlagsForRevision( $rev_id ) {
    	global $wgFlaggedRevTags;
    	
    	wfProfileIn( __METHOD__ );
    	// Cached results?
    	if( isset($this->flags[$rev_id]) && $this->flags[$rev_id] )
    		return $this->revflags[$rev_id];
    	// Set all flags to zero
    	$flags = array();
    	foreach( array_keys($wgFlaggedRevTags) as $tag ) {
    		$flags[$tag] = 0;
    	}
		$db = wfGetDB( DB_SLAVE );
		// Grab all the tags for this revision
		$result = $db->select(
			array('flaggedrevtags'),
			array('frt_dimension', 'frt_value'), 
			array('frt_rev_id' => $rev_id),
			__METHOD__ );
		// Iterate through each tag result
		while( $row = $db->fetchObject($result) ) {
			$flags[$row->frt_dimension] = $row->frt_value;
		}
		// Try to cache results
		$this->flags[$rev_id] = true;
		$this->revflags[$rev_id] = $flags;
		
		return $flags;
	}

}

// Our class instances
$flaggedrevs = new FlaggedRevs();
$flaggedarticle = new FlaggedArticle();
// Main hooks, overrides pages content, adds tags, sets tabs and permalink
$wgHooks['SkinTemplateTabs'][] = array($flaggedarticle, 'setCurrentTab');
$wgHooks['ArticleViewHeader'][] = array($flaggedarticle, 'setPageContent');
$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink'][] = array($flaggedarticle, 'setPermaLink');
// Add tags do edit view
$wgHooks['EditPage::showEditForm:initial'][] = array($flaggedarticle, 'addToEditView');
// Add review form
$wgHooks['BeforePageDisplay'][] = array($flaggedarticle, 'addReviewForm');
// Mark of items in page history
$wgHooks['PageHistoryBeforeList'][] = array($flaggedarticle, 'addToPageHist');
$wgHooks['PageHistoryLineEnding'][] = array($flaggedarticle, 'addToHistLine');
// Autopromote Editors
$wgHooks['ArticleSaveComplete'][] = array($flaggedrevs, 'autoPromoteUser');
// Update older, incomplete, page caches (ones that lack template Ids/image timestamps)
$wgHooks['ArticleViewHeader'][] = array($flaggedrevs, 'maybeUpdateMainCache');
// Adds table link references to include ones from the stable version
$wgHooks['TitleLinkUpdatesAfterCompletion'][] = array($flaggedrevs, 'extraLinksUpdate');
// If a stable version is hidden, move to the next one if possible, and update things
$wgHooks['ArticleRevisionVisiblityUpdates'][] = array($flaggedrevs, 'articleLinksUpdate');
// Update our table NS/Titles when things are moved
$wgHooks['SpecialMovepageAfterMove'][] = array($flaggedrevs, 'updateFromMove');
// Parser hooks, selects the desired images/templates
$wgHooks['BeforeParserrenderImageGallery'][] = array( $flaggedrevs, 'parserMakeGalleryStable');
$wgHooks['BeforeGalleryFindFile'][] = array( $flaggedrevs, 'galleryFindStableFileTime');
$wgHooks['BeforeParserFetchTemplateAndtitle'][] = array( $flaggedrevs, 'parserFetchStableTemplate');
$wgHooks['BeforeParserMakeImageLinkObj'][] = array( $flaggedrevs, 'parserMakeStableImageLink');
?>
