<?php
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
	'author' => 'Aaron Schulz',
	'name' => 'Flagged Revisions',
	'url' => 'http://www.mediawiki.org/wiki/Extension:FlaggedRevs',
	'description' => 'Allows for revisions of pages to be made static regardless of internal templates and images'
);

$wgExtensionCredits['specialpage'][] = array(
	'author' => 'Aaron Schulz, Joerg Baach',
	'name' => 'Review revisions',
	'url' => 'http://www.mediawiki.org/wiki/Extension:FlaggedRevs',
	'description' => 'Gives editors/reviewers the ability to validate revisions and stabilize pages'
);

$wgExtensionFunctions[] = 'efLoadReviewMessages';

# Load promotion UI
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
# Do flagged revs override the default view?
$wgFlaggedRevsOverride = true;
# Can users make comments that will show up below flagged revisions?
$wgFlaggedRevComments = false;
# Make user's watch pages when reviewed if they watch pages that they edit
$wgFlaggedRevsWatch = true;
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


# Allow sysops to grant and revoke 'editor' status.
$wgGroupPermissions['sysop']['userrights'] = true;

if (isset($wgAddGroups['sysop']))
	array_push( $wgAddGroups['sysop'], 'editor' );
else
	$wgAddGroups['sysop'] = array( 'editor' );

if (isset($wgRemoveGroups['sysop']))
	array_push( $wgRemoveGroups['sysop'], 'editor' );
else
	$wgRemoveGroups['sysop'] = array( 'editor' );

# Use RC Patrolling to check for vandalism
# When revisions are flagged, they count as patrolled
$wgUseRCPatrol = true;

# This will only distinguish "sigted", "quality", and unreviewed
# A small icon will show in the upper right hand corner
$wgSimpleFlaggedRevsUI = false;

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
# Set to false to disable this
$wgFlaggedRevsAutopromote = array('days' => 60, 'edits' => 500, 'email' => true);

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
		foreach( array_keys($wgFlaggedRevTags) as $tag ) {
			$this->dimensions[$tag] = array();
			for($i=0; $i <= $wgFlaggedRevValues; $i++) {
				$this->dimensions[$tag][$i] = "$tag-$i";
			}
		}
	}
    
    /**
     * @param string $text
     * @returns array( string, bool )
     * All included pages/arguments are expanded out
     */
    public static function expandText( $text='', $title, $id=null ) {
    	global $wgParser;
    	# Causes our hooks to trigger
    	$wgParser->isStable = true;
    	$wgParser->includesMatched = true;
        # Parse with default options
        $options = new ParserOptions;
        $options->setRemoveComments( true ); // Save some bandwidth ;)
        $outputText = $wgParser->preprocess( $text, $title, $options, $id );
        $expandedText = array( $outputText, $wgParser->includesMatched );
        # Done!
        $wgParser->isStable = false;
        $wgParser->includesMatched = false;
        
        return $expandedText;
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
       	# Reset $wgParser
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
		wfProfileOut( __METHOD__ );
		
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
		wfProfileOut( __METHOD__ );
		
		return NULL;
	}

	/**
	 * @param int $page_id
	 * Get rev ids of reviewed revs for a page
	 * Will include deleted revs here
	 */
    public static function getReviewedRevs( $page ) {
    	$rows = array();
    
		wfProfileIn( __METHOD__ );  
		$db = wfGetDB( DB_SLAVE );
		
		$result = $db->select('flaggedrevs',
			array('fr_rev_id','fr_quality'),
			array('fr_namespace' => $page->getNamespace(), 'fr_title' => $page->getDBkey() ),
			__METHOD__ ,
			array('ORDER BY' => 'fr_rev_id DESC') );
		while( $row = $db->fetchObject($result) ) {
        	$rows[$row->fr_rev_id] = $row->fr_quality;
		}
		wfProfileOut( __METHOD__ );
		
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
		wfProfileOut( __METHOD__ );
		
		return $count;
    }
	
	/**
	* static counterpart for getOverridingRev()
	*/
    public static function getOverridingPageRev( $title=NULL ) {
    	if( is_null($title) )
			return null;
    	
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
    	// Set all flags to zero
    	$flags = array();
    	foreach( array_keys($wgFlaggedRevTags) as $tag ) {
    		$flags[$tag] = 0;
    	}
    	
    	wfProfileIn( __METHOD__ );
		$db = wfGetDB( DB_SLAVE );
		// Grab all the tags for this revision
		$result = $db->select('flaggedrevtags',
			array('frt_dimension', 'frt_value'), 
			array('frt_rev_id' => $rev_id),
			__METHOD__ );
		// Iterate through each tag result
		while ( $row = $db->fetchObject($result) ) {
			$flags[$row->frt_dimension] = $row->frt_value;
		}
		wfProfileOut( __METHOD__ );
		
		return $flags;
	}
	
	public static function isMainPage( $title ) {
		$mp = Title::newMainPage();
		return ( $title->getNamespace()==$mp->getNamespace() && $title->getDBKey()==$mp->getDBKey() );
	}
    
    public function addTagRatings( $flags, $prettyBox = false, $css='' ) {
        global $wgFlaggedRevTags;
        
        $tag = '';
        if( $prettyBox )
        	$tag .= "<table align='center' class='$css' cellpading='0'>";
        
		foreach( $this->dimensions as $quality => $value ) {
			$valuetext = wfMsgHtml('revreview-' . $this->dimensions[$quality][$flags[$quality]]);
            $level = $flags[$quality];
            $minlevel = $wgFlaggedRevTags[$quality];
            if( $level >= $minlevel )
                $classmarker = 2;
            elseif( $level > 0 )
                $classmarker = 1;
            else
                $classmarker = 0;

            $levelmarker = $level * 20 + 20; //XXX do this better
            if( $prettyBox ) {
            	$tag .= "<tr><td><span class='fr-group'><span class='fr-text'>" . wfMsgHtml("revreview-$quality") . 
					"</span></tr><tr><td><span class='fr-marker fr_value$levelmarker'>$valuetext</span></span></td></tr>\n";
            } else {
				$tag .= "&nbsp;<span class='fr-marker-$levelmarker'><strong>" . 
					wfMsgHtml("revreview-$quality") . 
					"</strong>: <span class='fr-text-value'>$valuetext&nbsp;</span>&nbsp;" .
					"</span>\n";    
			}
		}
		if( $prettyBox )
			$tag .= '</table>';
		 
		return $tag;
    }
    
	function prettyRatingBox( $tfrev, $flags, $revs_since, $simpleTag=false ) {
		global $wgLang, $wgUser;
		
        $box = '';
		# Get quality level
		$quality = self::isQuality( $flags );
		$pristine = self::isPristine( $flags );
		$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $tfrev->fr_timestamp), true );
		
 		$skin = $wgUser->getSkin();
		// Some checks for which tag CSS to use
		if( $simpleTag )
			$tagClass = 'flaggedrevs_box0';
		else if( $pristine )
			$tagClass = 'flaggedrevs_box3';
		else if( $quality )
			$tagClass = 'flaggedrevs_box2';
		else
			$tagClass = 'flaggedrevs_box1';
        // Construct some tagging
        $msg = $quality ? 'revreview-quality' : 'revreview-basic';
		$box = self::addTagRatings( $flags, true, "{$tagClass}a" );
		$box .= '<p><a id="mwrevisiontoggle" style="display:none;" href="javascript:toggleRevRatings()">' . 
			wfMsg('revreview-toggle') . '</a></p>';
		$box .= '<span id="mwrevisionratings">' . 
			wfMsgExt($msg, array('parseinline'), $tfrev->fr_rev_id, $time, $revs_since) .
			'</span>';
        
        return $box;
	}
    
    public static function ReviewNotes( $row ) {
    	global $wgUser, $wgFlaggedRevComments;
    	
    	if( !$row || !$wgFlaggedRevComments ) 
			return '';
    	
    	if( $row->fr_comment ) {
    		$skin = $wgUser->getSkin();
    		$notes = '<p><div class="flaggedrevs_notes plainlinks">';
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
    	global $wgUser, $action;
    	// Only trigger on article view for content pages, not for protect/delete/hist
		if( !$article || !$article->exists() || !$article->mTitle->isContentPage() || $action !='view' ) 
			return true;
		
		// User must have review rightss
		if( !$wgUser->isAllowed( 'review' ) ) 
			return true;
		
		$parserCache =& ParserCache::singleton();
    	$parserOutput = $parserCache->get( $article, $wgUser );
		if( $parserOutput ) {
			// Clear older, incomplete, cached versions
			// We need the IDs of templates and timestamps of images used
			if( !isset($parserOutput->mTemplateIds) || !isset($parserOutput->mImageTimestamps) ) {
				$article->mTitle->invalidateCache();
			}
		}
		return true;
    }
    
    function updateFromMove( &$movePageForm , &$oldtitle , &$newtitle ) {
    	$dbw = wfGetDB( DB_MASTER );
        $dbw->update( 'flaggedrevs',
			array('fr_namespace' => $newtitle->getNamespace(), 'fr_title' => $newtitle->getDBkey() ),
			array('fr_namespace' => $oldtitle->getNamespace(), 'fr_title' => $oldtitle->getDBkey() ),
			__METHOD__ );
			
		return true;
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
		
		return true;
    }
    
    public static function extraLinksUpdate( &$title ) {
    	$fname = 'FlaggedRevs::extraLinksUpdate';
    	wfProfileIn( $fname );
		    	
    	if( !$title->isContentPage() ) 
			return true;
    	# Check if this page has a stable version
    	$sv = self::getOverridingPageRev( $title );
    	if( !$sv ) 
			return true;
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
		return true;
    }
    
	static function parserFetchStableTemplate( &$parser, &$title, &$skip, &$id ) {
    	// Trigger for stable version parsing only
    	if( !isset($parser->isStable) || !$parser->isStable )
    		return true;
    	// Only called to make fr_text, right after template/image specifiers 
    	// are added to the DB. It's unlikely for slaves to have it yet
		$dbw = wfGetDB( DB_MASTER );
		$id = $dbw->selectField('flaggedtemplates', 'ft_tmp_rev_id',
			array('ft_rev_id' => $parser->mRevisionId, 
				'ft_namespace' => $title->getNamespace(), 'ft_title' => $title->getDBkey() ),
			__METHOD__ );
				
		if( !$id ) {
			if( $id === false )
				$parser->includesMatched = false; // May want to give an error
			$id = 0; // Zero for not found
			$skip = true;
		}
		return true;
    }
    
	static function parserMakeStableImageLink( &$parser, &$nt, &$skip, &$time ) {
    	// Trigger for stable version parsing only
    	if( !isset($parser->isStable) || !$parser->isStable )
    		return true;
    	
    	// Only called to make fr_text, right after template/image specifiers 
    	// are added to the DB. It's unlikely for slaves to have it yet
    	$dbw = wfGetDB( DB_MASTER );
       	$time = $dbw->selectField('flaggedimages', 'fi_img_timestamp',
			array('fi_rev_id' => $parser->mRevisionId, 'fi_name' => $nt->getDBkey() ),
			__METHOD__ );
		
		if( !$time ) {
			if( $time === false ) 
				$parser->includesMatched = false; // May want to give an error
			$time = 0; // Zero for not found
			$skip = true;
		}
		return true;
    }
    
    static function galleryFindStableFileTime( &$ig, &$nt, &$time ) {
    	// Trigger for stable version parsing only
    	if( !isset($ig->isStable) || !$ig->isStable )
    		return true;
    	
    	$dbr = wfGetDB( DB_SLAVE );
        $time = $dbr->selectField('flaggedimages', 'fi_img_timestamp',
			array('fi_rev_id' => $ig->mRevisionId, 'fi_name' => $nt->getDBkey() ),
			__METHOD__ );
		$time = $time ? $time : -1; // hack, will never find this
		
		return true;
    }
    
    static function parserMakeGalleryStable( &$parser, &$ig ) {
    	// Trigger for stable version parsing only
    	if( !isset($parser->isStable) || !$parser->isStable )
    		return true;
    	
    	$ig->isStable = true;
    	
    	return true;
    }
    
    static function parserInjectImageTimestamps( &$parser, &$text ) {
		$parser->mOutput->mImageTimestamps = array();
		# Fetch the timestamps of the images
		if( !empty($parser->mOutput->mImages) ) {
			$dbr = wfGetDB( DB_SLAVE );
        	$res = $dbr->select('image', array('img_name','img_timestamp'),
				array('img_name IN(' . $dbr->makeList( array_keys($parser->mOutput->mImages) ) . ')'),
			__METHOD__ );
			
			while( $row = $dbr->fetchObject($res) ) {
				$parser->mOutput->mImageTimestamps[$row->img_name] = $row->img_timestamp;
			}
		}
		return true;
    }
    
    static function outputInjectImageTimestamps( &$out, &$parserOutput ) {
    	$out->mImageTimestamps = $parserOutput->mImageTimestamps;
    	
    	return true;
    }

	/**
	* Callback that autopromotes user according to the setting in 
    * $wgFlaggedRevsAutopromote
	*/
	public static function autoPromoteUser( &$article, &$user, &$text, &$summary, &$isminor, &$iswatch, &$section ) {
		global $wgUser, $wgFlaggedRevsAutopromote;
		
		if( !$wgFlaggedRevsAutopromote )
			return true;
		// Grab current groups
		$groups = $user->getGroups();
		$now = time();
		$usercreation = wfTimestamp(TS_UNIX,$user->mRegistration);
		$userage = floor(($now-$usercreation) / 86400);
		// Do not give this to bots
		if( in_array( 'bot', $groups ) )
			return true;
		// Check if we need to promote...
		$vars = $wgFlaggedRevsAutopromote;
		if( !in_array('editor',$groups) && $userage >= $vars['days'] && $user->getEditCount() >= $vars['edits']
			&& ( !$vars['email'] || $wgUser->isAllowed('emailconfirmed') ) ) {
    		$fname = 'FlaggedRevs::autoPromoteUser';

    		# Do not re-add status if it was previously removed...
			$dbw = wfGetDB( DB_MASTER );
			$dbr = $dbw->selectRow( 'logging', 'log_params', 
				array(
					'log_type'  => 'rights',
					'log_title' => $wgUser->getName(),
					"log_params LIKE '%editor%'" ) ); 
			
			if (empty($dbr)) {
				$newGroups = $groups ;
				array_push( $newGroups, 'editor');

				# Lets NOT spam RC, set $RC to false
				$log = new LogPage( 'rights', false );
				$log->addEntry('rights', $user->getUserPage(), wfMsgHtml('makevalidate-autosum'), 
						array( makeGroupNameList( $groups ), makeGroupNameList( $newGroups ) ) );

				$user->addGroup('editor');
			}
		}
		return true;
    }
}

class FlaggedArticle extends FlaggedRevs {
	/**
	 * Does the config and current URL params allow 
	 * for overriding by stable revisions?
	 */		
    static function pageOverride() {
    	global $wgTitle, $wgFlaggedRevsAnonOnly, $wgFlaggedRevsOverride, $wgUser, $wgRequest, $action;
    	# This only applies to viewing content pages
    	if( $action !='view' || !$wgTitle->isContentPage() ) return;
    	# Does not apply to diffs/old revisions
    	if( $wgRequest->getVal('oldid') || $wgRequest->getVal('diff') ) return;
    	# Does the stable version override the current one?
    	if( $wgFlaggedRevsOverride ) {
    		# If $wgFlaggedRevsAnonOnly is set to false, stable version are only requested explicitly
    		if( $wgFlaggedRevsAnonOnly && $wgUser->isAnon() ) {
    			return !( $wgRequest->getIntOrNull('stable')===0 );
    		} else {
    			return ( $wgRequest->getIntOrNull('stable')===1 );
    		}
		} else {
    		return !( $wgRequest->getIntOrNull('stable') !==1 );
		}
	}
	/**
	 * Should this be using a simple icon-based UI?
	 */	
	static function useSimpleUI() {
		global $wgSimpleFlaggedRevsUI;
		
		return $wgSimpleFlaggedRevsUI;
	}

	 /**
	 * Replaces a page with the last stable version if possible
	 * Adds stable version status/info tags and notes
	 * Adds a quick review form on the bottom if needed
	 */
	function setPageContent( &$article, &$outputDone, &$pcache ) {
		global $wgRequest, $wgTitle, $wgOut, $action, $wgUser;
		// Only trigger on article view for content pages, not for protect/delete/hist
		if( !$article || !$article->exists() || !$article->mTitle->isContentPage() || $action !='view' ) 
			return true;
		// Grab page and rev ids
		$pageid = $article->getId();
		$revid = $article->mRevision ? $article->mRevision->mId : $article->getLatest();
		if( !$revid ) 
			return true;
			
		$skin = $wgUser->getSkin();
		
		$vis_id = $revid;
		$tag = ''; $notes = '';
		// Check the newest stable version...
		$tfrev = $this->getOverridingRev();
		$simpleTag = false;
		if( $wgRequest->getVal('diff') || $wgRequest->getVal('oldid') ) {
    		// Do not clutter up diffs any further...
		} else if( !is_null($tfrev) ) {
			global $wgLang;
			# Get flags and date
			$flags = $this->getFlagsForRevision( $tfrev->fr_rev_id );
			# Get quality level
			$quality = parent::isQuality( $flags );
			$pristine =  parent::isPristine( $flags );
			$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $tfrev->fr_timestamp), true );
			# Looking at some specific old rev or if flagged revs override only for anons
			if( !$this->pageOverride() ) {
				$revs_since = parent::getRevCountSince( $pageid, $tfrev->fr_rev_id );
				$simpleTag = true;
				# Construct some tagging
				if( !$wgOut->isPrintable() ) {
					if( $this->useSimpleUI() ) {
						$msg = $quality ? 'revreview-quick-see-quality' : 'revreview-quick-see-basic';
						$tag .= "<span class='fr_tab_current plainlinks'></span>" . wfMsgExt($msg,array('parseinline'));
					} else {
						$msg = $quality ? 'revreview-newest-quality' : 'revreview-newest-basic';
						$tag .= wfMsgExt($msg, array('parseinline'), $tfrev->fr_rev_id, $time, $revs_since);
						# Hide clutter
						$tag .= ' <a id="mwrevisiontoggle" style="display:none;" href="javascript:toggleRevRatings()">' . 
							wfMsg('revreview-toggle') . '</a>';
						$tag .= '<span id="mwrevisionratings" style="display:block;">' . 
							wfMsg('revreview-oldrating') . parent::addTagRatings( $flags ) . '</span>';
					}
				}
			# Viewing the page normally: override the page
			} else {
       			# We will be looking at the reviewed revision...
       			$vis_id = $tfrev->fr_rev_id;
       			$revs_since = parent::getRevCountSince( $pageid, $vis_id );
				// Construct some tagging
				if( !$wgOut->isPrintable() ) {
					if( $this->useSimpleUI() ) {
						$msg = $quality ? 'revreview-quick-quality' : 'revreview-quick-basic';
						$css = $quality ? 'fr_tab_quality' : 'fr_tab_stable';
						$tag .= "<span class='$css plainlinks'></span>" . 
							wfMsgExt($msg,array('parseinline'),$tfrev->fr_rev_id,$revs_since);
					} else {
						$msg = $quality ? 'revreview-quality' : 'revreview-basic';
						$tag = wfMsgExt($msg, array('parseinline'), $vis_id, $time, $revs_since);
						$tag .= ' <a id="mwrevisiontoggle" style="display:none;" href="javascript:toggleRevRatings()">' . 
							wfMsg('revreview-toggle') . '</a>';
						$tag .= '<span id="mwrevisionratings" style="display:block;">' . 
							parent::addTagRatings( $flags ) . '</span>';
					}
				}
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
				# Tell MW that parser output is done
				$outputDone = true;
				$pcache = false;
			}
			// Some checks for which tag CSS to use
			if( $this->useSimpleUI() )
				$tagClass = 'flaggedrevs_short';
			else if( $simpleTag )
				$tagClass = 'flaggedrevs_notice';
			else if( $pristine )
				$tagClass = 'flaggedrevs_tag3';
			else if( $quality )
				$tagClass = 'flaggedrevs_tag2';
			else
				$tagClass = 'flaggedrevs_tag1';
			// Wrap tag contents in a div
			if( $tag !='' )
				$tag = '<div id="mwrevisiontag" class="' . $tagClass . ' plainlinks">'.$tag.'</div>';
			// Set the new body HTML, place a tag on top
			$wgOut->mBodytext = $tag . $wgOut->mBodytext . $notes;
		// Add "no reviewed version" tag, but not for main page
		} else if( !$wgOut->isPrintable() && !parent::isMainPage( $article->mTitle ) ) {
			if( $this->useSimpleUI() ) {
				$tag .= "<span class='fr_tab_current plainlinks'></span>" . 
					wfMsgExt('revreview-quick-none',array('parseinline'));
				$tag = '<div id="mwrevisiontag" class="flaggedrevs_short plainlinks">'.$tag.'</div>';
			} else {
				$tag = '<div id="mwrevisiontag" class="mw-warning plainlinks">' .
					wfMsgExt('revreview-noflagged', array('parseinline')) . '</div>';
			}
			$wgOut->addHTML( $tag );
		}
		return true;
    }
    
    function addToEditView( &$editform ) {
		global $wgRequest, $wgTitle, $wgOut;
		// Talk pages cannot be validated
		if( !$editform->mArticle || !$wgTitle->isContentPage() )
			return false;
		// Find out revision id
		if( $editform->mArticle->mRevision ) {
       		$revid = $editform->mArticle->mRevision->mId;
		} else {
       		$revid = $editform->mArticle->getLatest();
       	}
		// Grab the ratings for this revision if any
		if( !$revid ) 
			return true;
		// Set new body html text as that of now
		$tag = '';
		// Check the newest stable version
		$tfrev = $this->getOverridingRev();
		if( is_object($tfrev) ) {
			global $wgLang;		
			$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $tfrev->fr_timestamp), true );
			$flags = $this->getFlagsForRevision( $tfrev->fr_rev_id );
			$revs_since = parent::getRevCountSince( $editform->mArticle->getID(), $tfrev->fr_rev_id );
			# Construct some tagging
			$msg = parent::isQuality( $flags ) ? 'revreview-newest-quality' : 'revreview-newest-basic';
			$tag = wfMsgExt($msg, array('parseinline'), $tfrev->fr_rev_id, $time, $revs_since );
			# Hide clutter
			$tag .= ' <a id="mwrevisiontoggle" style="display:none;" href="javascript:toggleRevRatings()">' . wfMsg('revreview-toggle') . '</a>';
			$tag .= '<span id="mwrevisionratings" style="display:block;">' . 
				wfMsg('revreview-oldrating') . parent::addTagRatings( $flags ) . 
				'</span>';
			$wgOut->addHTML( '<div id="mwrevisiontag" class="flaggedrevs_notice plainlinks">' . $tag . '</div><br/>' );
		}
		return true;
    }
	
    function addReviewForm( &$out ) {
    	global $wgArticle, $action;

		if( !$wgArticle || !$wgArticle->exists() || !$wgArticle->mTitle->isContentPage() || $action !='view' ) 
			return true;
		// Check if page is protected
		if( !$wgArticle->mTitle->quickUserCan( 'edit' ) ) {
			return true;
		}
		// Get revision ID
		$revId = ( $wgArticle->mRevision ) ? $wgArticle->mRevision->mId : $wgArticle->getLatest();
		// We cannot review deleted revisions
		if( is_object($wgArticle->mRevision) && $wgArticle->mRevision->mDeleted ) 
			return true;
    	// Add quick review links IF we did not override, otherwise, they might
		// review a revision that parses out newer templates/images than what they saw.
		// Revisions are always reviewed based on current templates/images.
		if( $this->pageOverride() ) {
			$tfrev = $this->getOverridingRev();
			if( $tfrev ) return true;
		}
		$this->addQuickReview( $revId, $out, false );
		
		return true;
    }
    
    function setPermaLink( &$sktmp, &$nav_urls, &$revid, &$revid ) {
		// Non-content pages cannot be validated
		if( !$this->pageOverride() ) return true;
		// Check for an overridabe revision
		$tfrev = $this->getOverridingRev();
		if( !$tfrev ) 
			return true;
		// Replace "permalink" with an actual permanent link
		$nav_urls['permalink'] = array(
			'text' => wfMsg( 'permalink' ),
			'href' => $sktmp->makeSpecialUrl( 'Stableversions', "oldid={$tfrev->fr_rev_id}" )
		);
		
		global $wgHooks;
		// Are we using the popular cite extension?
		if( in_array('wfSpecialCiteNav',$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink']) ) {
			if( $sktmp->mTitle->isContentPage() && $revid !== 0 ) {
				$nav_urls['cite'] = array(
					'text' => wfMsg( 'cite_article_link' ),
					'href' => $sktmp->makeSpecialUrl( 'Cite', "page=" . wfUrlencode( "{$sktmp->thispage}" ) . "&id={$tfrev->fr_rev_id}" )
				);
			}
		}
		return true;
    }
    
    function setCurrentTab( &$sktmp, &$content_actions ) {
    	global $wgRequest, $wgFlaggedRevsAnonOnly, $wgFlaggedRevsOverride, $wgUser, $action;
		// Get the subject page, not all skins have it :(
		if( !isset($sktmp->mTitle) )
			return true;
		$title = $sktmp->mTitle->getSubjectPage();
		// Non-content pages cannot be validated
		if( !$title->isContentPage() || !$title->exists() ) 
			return true;
		$article = new Article( $title );
		// If we are viewing a page normally, and it was overridden,
		// change the edit tab to a "current revision" tab
       	$tfrev = $this->getOverridingRev();
       	// No quality revs? Find the last reviewed one
       	if( !is_object($tfrev) ) 
			return true;
       	// Note that revisions may not be set to override for users
       	if( $this->pageOverride() ) {
       		# Remove edit option altogether
       		unset( $content_actions['edit']);
       		unset( $content_actions['viewsource']);
			$new_actions = array(); $counter = 0;
			# Straighten out order
			foreach( $content_actions as $tab_action => $data ) {
				if( $counter==1 ) {
					# Set the tab AFTER the main tab is set
					if( $wgFlaggedRevsOverride && !($wgFlaggedRevsAnonOnly && !$wgUser->isAnon()) ) {
						$new_actions['current'] = array(
							'class' => '',
							'text' => wfMsg('revreview-current'),
							'href' => $title->getLocalUrl( 'stable=0' )
						);
					} else {
					# Add 'stable' tab if either $wgFlaggedRevsOverride is off, 
					# or this is a user viewing the page with $wgFlaggedRevsAnonOnly on
						$new_actions['stable'] = array(
							'class' => 'selected',
							'text' => wfMsg('revreview-stable'),
							'href' => $title->getLocalUrl( 'stable=1' )
						);
					}
				}
       			$new_actions[$tab_action] = $data;
       			$counter++;
       		}
       		# Reset static array
       		$content_actions = $new_actions;
    	} else if( $action !='view' || $wgRequest->getVal('oldid') || $sktmp->mTitle->isTalkPage() ) {
    	// We are looking at the talk page or diffs/hist/oldids
			$new_actions = array(); $counter = 0;
			# Straighten out order
			foreach( $content_actions as $tab_action => $data ) {
				if( $counter==1 ) {
					# Set the tab AFTER the main tab is set
					if( $wgFlaggedRevsOverride && !($wgFlaggedRevsAnonOnly && !$wgUser->isAnon()) ) {
						$new_actions['current'] = array(
							'class' => '',
							'text' => wfMsg('revreview-current'),
							'href' => $title->getLocalUrl( 'stable=0' )
						);
					} else {
					# Add 'stable' tab if either $wgFlaggedRevsOverride is off, 
					# or this is a user viewing the page with $wgFlaggedRevsAnonOnly on
						$new_actions['stable'] = array(
							'class' => 'selected',
							'text' => wfMsg('revreview-stable'),
							'href' => $title->getLocalUrl( 'stable=1' )
						);
					}
				}
       			$new_actions[$tab_action] = $data;
       			$counter++;
       		}
       		# Reset static array
       		$content_actions = $new_actions;
    	} else {
		// We are looking at the current revision
			$new_actions = array(); $counter = 0;
			# Straighten out order
			foreach( $content_actions as $tab_action => $data ) {
				if( $counter==1 ) {
       				# Set the tab AFTER the main tab is set
					if( $wgFlaggedRevsOverride && !($wgFlaggedRevsAnonOnly && !$wgUser->isAnon()) ) {
						$new_actions['current'] = array(
							'class' => 'selected',
							'text' => wfMsg('revreview-current'),
							'href' => $title->getLocalUrl( 'stable=0' )
						);
					} else {
					# Add 'stable' tab if either $wgFlaggedRevsOverride is off, 
					# or this is a user viewing the page with $wgFlaggedRevsAnonOnly on
						$new_actions['stable'] = array(
							'class' => '',
							'text' => wfMsg('revreview-stable'),
							'href' => $title->getLocalUrl( 'stable=1' )
						);
				 	}
				}
       			$new_actions[$tab_action] = $data;
       			$counter++;
       		}
       		# Reset static array
       		$content_actions = $new_actions;
    	}
    	return true;
    }
    
    function addToPageHist( &$article ) {
    	global $wgUser;
    
    	$this->pageFlaggedRevs = array();
    	$rows = $this->getReviewedRevs( $article->getTitle() );
    	if( !$rows ) 
			return true;
    	// Try to keep the skin readily accesible,
    	// addToHistLine() will use it
    	$this->skin = $wgUser->getSkin();
    	
    	foreach( $rows as $rev => $quality )
    		$this->pageFlaggedRevs[$rev] = $quality;

    	return true;
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
		
		return true;
    }
    
    function addQuickReview( $id=NULL, $out ) {
		global $wgOut, $wgTitle, $wgUser, $wgFlaggedRevsOverride, $wgFlaggedRevComments, $wgFlaggedRevsWatch;
		// User must have review rights
		if( !$wgUser->isAllowed( 'review' ) ) return;
		// Looks ugly when printed
		if( $out->isPrintable() ) return;
		
		$skin = $wgUser->getSkin();
		// Already flagged?
		$flags = $this->getFlagsForRevision( $id );
       
		$reviewtitle = SpecialPage::getTitleFor( 'Revisionreview' );
		$action = $reviewtitle->escapeLocalUrl( 'action=submit' );
		$form = Xml::openElement( 'form', array( 'method' => 'post', 'action' => $action ) );
		$form .= "<fieldset><legend>" . wfMsgHtml( 'revreview-flag', $id ) . "</legend>\n";
		
		if( $wgFlaggedRevsOverride )
			$form .= '<p>'.wfMsgExt( 'revreview-text', array('parseinline') ).'</p>';
		
		$form .= wfHidden( 'title', $reviewtitle->getPrefixedText() );
		$form .= wfHidden( 'target', $wgTitle->getPrefixedText() );
		$form .= wfHidden( 'oldid', $id );
		$form .= wfHidden( 'action', 'submit');
        $form .= wfHidden( 'wpEditToken', $wgUser->editToken() );
        
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
        
        $watchLabel = wfMsgExt('watchthis', array('parseinline'));
        $watchAttribs = array('accesskey' => wfMsg( 'accesskey-watch' ), 'id' => 'wpWatchthis');
        $watchChecked = ( $wgFlaggedRevsWatch && $wgUser->getOption( 'watchdefault' ) || $wgTitle->userIsWatching() );
		$form .= "<p>&nbsp;&nbsp;&nbsp;".Xml::check( 'wpWatchthis', $watchChecked, $watchAttribs );
		$form .= "&nbsp;<label for='wpWatchthis'".$skin->tooltipAndAccesskey('watch').">{$watchLabel}</label></p>";
        
        $form .= "<p>".wfInputLabel( wfMsgHtml( 'revreview-log' ), 'wpReason', 'wpReason', 60 )."\n";
        
		$form .= Xml::submitButton( wfMsgHtml( 'revreview-submit' ) ) . "</p></fieldset>";
		$form .= Xml::closeElement( 'form' );
		
		$wgOut->addHTML( '<hr style="clear:both"></hr>' . $form );
    }

	/**
	 * Get latest quality rev, if not, the latest reviewed one
	 */
	function getOverridingRev() {
		if( !$row = $this->getLatestQualityRev() ) {
			if( !$row = $this->getLatestStableRev() ) {
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
	 * @output array ( rev, flags )
	 */
	function getLatestQualityRev() {
		global $wgTitle;
        // Cached results available?
		if( isset($this->stablefound) ) {
			return ( $this->stablefound ) ? $this->stablerev : null;
		}
		
		wfProfileIn( __METHOD__ );
		$dbr = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
        $result = $dbr->select(
			array('flaggedrevs', 'revision'),
			array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp'),
			array('fr_namespace' => $wgTitle->getNamespace(), 'fr_title' => $wgTitle->getDBkey(), 'fr_quality >= 1',
			'fr_rev_id = rev_id', 'rev_page' => $wgTitle->getArticleID(), 'rev_deleted = 0'),
			__METHOD__,
			array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 ) );
		wfProfileOut( __METHOD__ );
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
	 * The cache here doesn't make sense for arbitrary articles
	 * @output array ( rev, flags )
	 */
	function getLatestStableRev() {
		global $wgTitle;
		
        // Cached results available?
		if( isset($this->latestfound) ) {
			return ( $this->latestfound ) ? $this->latestrev : NULL;
		}
		wfProfileIn( __METHOD__ );
		$dbr = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
        $result = $dbr->select( 
			array('flaggedrevs', 'revision'),
			array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp'),
			array('fr_namespace' => $wgTitle->getNamespace(), 'fr_title' => $wgTitle->getDBkey(),
			'fr_rev_id = rev_id', 'rev_page' => $wgTitle->getArticleID(), 'rev_deleted = 0'),
			__METHOD__,
			array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 ) );
		wfProfileOut( __METHOD__ );
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
    	
    	// Cached results?
    	if( isset($this->flags[$rev_id]) && $this->flags[$rev_id] )
    		return $this->revflags[$rev_id];
    	// Set all flags to zero
    	$flags = array();
    	foreach( array_keys($wgFlaggedRevTags) as $tag ) {
    		$flags[$tag] = 0;
    	}
    	
    	wfProfileIn( __METHOD__ );
		$db = wfGetDB( DB_SLAVE );
		// Grab all the tags for this revision
		$result = $db->select('flaggedrevtags',
			array('frt_dimension', 'frt_value'), 
			array('frt_rev_id' => $rev_id),
			__METHOD__ );
		wfProfileOut( __METHOD__ );
		
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

function makeGroupNameList( $ids ) {
	return implode( ', ', $ids );
}

// Our class instances
$flaggedRevsModifier = new FlaggedArticle();
// Main hooks, overrides pages content, adds tags, sets tabs and permalink
$wgHooks['SkinTemplateTabs'][] = array($flaggedRevsModifier, 'setCurrentTab');
// Update older, incomplete, page caches (ones that lack template Ids/image timestamps)
$wgHooks['ArticleViewHeader'][] = array($flaggedRevsModifier, 'maybeUpdateMainCache');
$wgHooks['ArticleViewHeader'][] = array($flaggedRevsModifier, 'setPageContent');
$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink'][] = array($flaggedRevsModifier, 'setPermaLink');
// Add tags do edit view
$wgHooks['EditPage::showEditForm:initial'][] = array($flaggedRevsModifier, 'addToEditView');
// Add review form
$wgHooks['BeforePageDisplay'][] = array($flaggedRevsModifier, 'addReviewForm');
// Mark of items in page history
$wgHooks['PageHistoryBeforeList'][] = array($flaggedRevsModifier, 'addToPageHist');
$wgHooks['PageHistoryLineEnding'][] = array($flaggedRevsModifier, 'addToHistLine');
// Autopromote Editors
$wgHooks['ArticleSaveComplete'][] = array($flaggedRevsModifier, 'autoPromoteUser');
// Adds table link references to include ones from the stable version
$wgHooks['TitleLinkUpdatesAfterCompletion'][] = array($flaggedRevsModifier, 'extraLinksUpdate');
// If a stable version is hidden, move to the next one if possible, and update things
$wgHooks['ArticleRevisionVisiblityUpdates'][] = array($flaggedRevsModifier, 'articleLinksUpdate');
// Update our table NS/Titles when things are moved
$wgHooks['SpecialMovepageAfterMove'][] = array($flaggedRevsModifier, 'updateFromMove');
// Parser hooks, selects the desired images/templates
$wgHooks['BeforeParserrenderImageGallery'][] = array( $flaggedRevsModifier, 'parserMakeGalleryStable');
$wgHooks['BeforeGalleryFindFile'][] = array( $flaggedRevsModifier, 'galleryFindStableFileTime');
$wgHooks['BeforeParserFetchTemplateAndtitle'][] = array( $flaggedRevsModifier, 'parserFetchStableTemplate');
$wgHooks['BeforeParserMakeImageLinkObj'][] = array( $flaggedRevsModifier, 'parserMakeStableImageLink');
// Additional parser versioning
$wgHooks['ParserAfterTidy'][] = array( $flaggedRevsModifier, 'parserInjectImageTimestamps');
$wgHooks['OutputPageParserOutput'][] = array( $flaggedRevsModifier, 'outputInjectImageTimestamps');
