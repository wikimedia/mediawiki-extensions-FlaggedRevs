<?
#(c) Joerg Baach, Aaron Schulz 2007 GPL
/*
Possible Hooks
--------------

'BeforePageDisplay': Called just before outputting a page (all kinds of,
		     articles, special, history, preview, diff, edit, ...)
		     Can be used to set custom CSS/JS
$out: OutputPage object

'OutputPageBeforeHTML': a page has been processed by the parser and
the resulting HTML is about to be displayed.  
$parserOutput: the parserOutput (object) that corresponds to the page 
$text: the text that will be displayed, in HTML (string)

*/

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

if( !defined( 'FLAGGED_CSS' ) ) define('FLAGGED_CSS', $wgScriptPath.'/extensions/FlaggedRevs/flaggedrevs.css' );

if( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}

$wgExtensionCredits['other'][] = array(
	'author' => 'Aaron Schulz, Joerg Baach',
	'name' => 'Flagged Revisions',
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
	global $wgMessageCache, $RevisionreviewMessages, $wgOut;
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
# MW can try to dynamically parse text from a timeframe, however
# it does have limitations. Using expanded text cache will avoid 
# this issues with regard to transcluded page moves/deletes. However
# messages like {{CURRENTDATE}} will not remain dynamic.
$wgUseExpandedCache = false;

# When setting up new dimensions or levels, you will need to add some 
# MediaWiki messages for the UI to show properly; any sysop can do this.
# Define the tags we can use to rate an article, 
# and set the minimum level to have it become a "quality" version.
# "quality" revisions take precidence over other reviewed revisions
$wgFlaggedRevTags = array( 'accuracy'=>2, 'depth'=>1, 'style'=>1 );
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
$wgGroupPermissions['editor']['unwatched']     = true;
$wgGroupPermissions['editor']['autoconfirmed'] = true;

# Defines extra rights for advanced reviewer class
$wgGroupPermissions['reviewer']['validate']  = true;
# Let this stand alone just in case...
$wgGroupPermissions['reviewer']['review']    = true;

# Define when users get automatically promoted to editors
$wgFlaggedRevsAutopromote = array('days' => 60, 'edits' => 1000, 'email' => true);

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
	/* 50MB allows fixing those huge pages */
    const MAX_INCLUDE_SIZE = 50000000;

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
    public static function expandText( $text, $title ) {
        global $wgParser, $wgTitle;
        
        if ( $text==false ) return;
        
        $options = new ParserOptions;
        $options->setRemoveComments( true );
        $options->setMaxIncludeSize( self::MAX_INCLUDE_SIZE );
        $output = $wgParser->preprocess( $text, $title, $options );
        return $output;
    }
    
	/**
	 * @param Title $title
	 * @param string $text
	 * @param int $id
	 * @param ParserOptions $options
	 * @param int $timeframe, when the revision was reviewed
	 * Get the HTML of a revision based on how it was during $timeframe
	 */
    public static function parseStableText( $title, $text, $id=NULL, $options, $timeframe=NULL ) {
    	global $wgUser, $wgParser;
    	
		$options->setTidy(true);
		# Don't show section-edit links
		# They can be old and misleading
		$options->setEditSection(false);
		# Parse the new body, wikitext -> html
       	$parserOut = $wgParser->parse( $text, $title, $options, true, true, $id, $timeframe );
       	
       	return $parserOut;
    }
    
	/**
	 * @param int $rev_id
	 * Get the text of a stable version
	 */	
    public static function getFlaggedRevText( $rev_id ) {
    	global $wgUseExpandedCache;
    
    	wfProfileIn( __METHOD__ );
    	
 		$db = wfGetDB( DB_SLAVE );
 		if ( $wgUseExpandedCache ) {
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
		} else {
 			// Get text straight from text table
			$result = $db->select( 
				array('revision','text'),
				array('old_text'),
				array('rev_id' => $rev_id, 'rev_text_id = old_id', 'rev_deleted = 0'), 
				__METHOD__,
				array('LIMIT' => 1) );
			if( $row = $db->fetchObject($result) ) {
				return $row->old_text;
			}
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
		if ( $row = $db->fetchObject($result) ) {
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
		// Skip deleted revisions
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
		$result = $db->select(
			array('revision'),
			array('rev_id'),
			array('rev_page' => $page_id, "rev_id > $from_rev"),
			__METHOD__ );
		// Return count of revisions
		return $db->numRows($result);
    }

	/**
	 * @param int $rev_id
	 * Return an array output of the flags for a given revision
	 */	
    public function getFlagsForRevision( $rev_id ) {
    	global $wgFlaggedRevTags;
    	
    	wfProfileIn( __METHOD__ );
    	// Cached results?
    	if ( isset($this->flags[$rev_id]) && $this->flags[$rev_id] )
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
		while ( $row = $db->fetchObject($result) ) {
			$flags[$row->frt_dimension] = $row->frt_value;
		}
		// Try to cache results
		$this->flags[$rev_id] = true;
		$this->revflags[$rev_id] = $flags;
		
		return $flags;
	}
	
	/**
	* static counterpart for getOverridingRev()
	*/   
    public static function getOverridingPageRev( $article ) {
    	if ( !is_object($article) )
    		return null;
    	
    	$title = $article->getTitle();
    	
		$dbr = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
        $result = $dbr->select(
			array('flaggedrevs', 'revision'),
			array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp'),
			array('fr_namespace' => $title->getNamespace(), 'fr_title' => $title->getDBkey(), 'fr_quality >= 1',
			'fr_rev_id = rev_id', 'rev_page' => $article->getId(), 'rev_deleted=0'),
			__METHOD__,
			array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 ) );
		// Do we have one?
        if( !$row = $dbr->fetchObject($result) ) {
        	$result = $dbr->select(
				array('flaggedrevs', 'revision'),
				array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp'),
				array('fr_namespace' => $title->getNamespace(), 'fr_title' => $title->getDBkey(), 'fr_quality >= 1',
				'fr_rev_id = rev_id', 'rev_page' => $article->getId(), 'rev_deleted=0'),
				__METHOD__,
				array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 ) );
			if( !$row = $dbr->fetchObject($result) )
				return null;
		}
		return $row;
    }
    
    public function addTagRatings( $flags ) {
        global $wgFlaggedRevTags;
    	$tag = "<p>";
		foreach ( $this->dimensions as $quality => $value ) {
			$valuetext = wfMsgHtml('revreview-' . $this->dimensions[$quality][$flags[$quality]]);
            $level = $flags[$quality];
            $minlevel = $wgFlaggedRevTags[$quality];
            if ($level >= $minlevel)
                $classmarker = 2;
            elseif ($level > 0)
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
    		if ( !isset($flags[$f]) || $v > $flags[$f] ) return false;
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
    		if ( $v < $wgFlaggedRevValues ) return false;
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
    		if ( $min==false || $v < $min ) $min = $v;
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
    	if ( !$article || !$article->getId() ) return NULL;
    	$key = 'sv-' . ParserCache::getKey( $article, $wgUser );
		// Get the cached HTML
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
		// Update the cache...
		$article->mTitle->invalidateCache();
    	// Make sure it is valid
    	if ( is_null($parserOutput) || !$article )
			return false;
    	$key = 'sv-' . ParserCache::getKey( $article, $wgUser );
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
    
    function updateFromMove( &$movePageForm , &$oldtitle , &$newtitle ) {
    	$dbw = wfGetDB( DB_MASTER );
        $dbw->update( 'flaggedrevs',
			array('fr_namespace' => $newtitle->getNamespace(), 'fr_title' => $newtitle->getDBkey() ),
			array('fr_namespace' => $oldtitle->getNamespace(), 'fr_title' => $oldtitle->getDBkey() ),
			__METHOD__ );
    }
    
    function extraLinksUpdate( &$article ) {
    	$fname = 'FlaggedRevs::doIncrementalUpdate';
    	wfProfileIn( $fname );
    	# Check if this page has a stable version
    	$sv = $this->getOverridingPageRev( $article );
    	if ( !$sv ) return;
    	# Retrieve the text
    	$text = $this->getFlaggedRevText( $sv->fr_rev_id );
    	# Parse the revision
    	$options = ParserOptions::newFromUser($wgUser);
    	$poutput = $this->parseStableText( $article->mTitle, $text, $sv->fr_rev_id, $options, $sv->fr_timestamp );
    	
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
		if ( $u->mRecursive ) {
			$u->queueRecursiveJobs();
		}
		
		wfProfileOut( $fname );
    	
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
		if ( in_array( 'bot', $groups ) ) return;
		// Check if we need to promote...
		$vars = $wgFlaggedRevsAutopromote;
		if ( !in_array('editor',$groups) && $userage >= $vars['days'] && $user->getEditCount() >= $vars['edits']
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
			if ( !$db->numRows($result) ) {
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
    	global $wgFlaggedRevsAnonOnly, $wgUser, $wgRequest;
    	return !( ( $wgFlaggedRevsAnonOnly && !$wgUser->isAnon() ) || 
			$wgRequest->getVal('oldid') || $wgRequest->getVal('diff') || $wgRequest->getText('stable')=='false' );
	}

	 /**
	 * Replaces a page with the last stable version if possible
	 * Adds stable version status/info tags and notes
	 * Adds a quick review form on the bottom if needed
	 */
	function setPageContent( &$article, &$outputDone, &$pcache ) {
		global $wgRequest, $wgTitle, $wgOut, $action;
		// Only trigger on article view, not for protect/delete/hist
		// Talk pages cannot be validated
		if( !$article || !$article->mTitle->isContentPage() || $action !='view' ) 
			return;
		// Grab page and rev ids
		$pageid = $article->getId();
		$revid = ( $article->mRevision ) ? $article->mRevision->mId : $article->getLatest();
		// There must be a valid rev ID
		if( !$revid ) return;
		
		$vis_id = $revid;
		$tag = ''; $notes = '';
		// Check the newest stable version...
		$quality = false; $featured = false;
		if ( $this->pageOverride() ) {
			// getLatestQualityRev() is slower, don't use it if we won't need to
			$tfrev = $this->getLatestQualityRev( $article );
			if ( is_null($tfrev) ) {
				$tfrev = $this->getLatestStableRev( $article );
			} else {
				$quality = true;
			}
		} else {
			$tfrev = $this->getLatestStableRev( $article );
		}
		if( $wgRequest->getVal('diff') ) {
    		// Do not clutter up diffs any further...
		} else if( !is_null($tfrev) ) {
			global $wgParser, $wgLang;
			// Get flags and date
			$flags = parent::getFlagsForRevision( $tfrev->fr_rev_id );
			$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $tfrev->fr_timestamp), true );
			// Looking at some specific old rev or if flagged revs override only for anons
			if( !$this->pageOverride() ) {
				if( $revid==$tfrev->fr_rev_id ) {
					$tag = wfMsgExt('revreview-isnewest', array('parse'), $time);
					$notes = parent::ReviewNotes( $tfrev );
				} else {
					# Our compare link should have a reasonable time-ordered old->new combination
					$oldid = ($revid > $tfrev->fr_rev_id) ? $tfrev->fr_rev_id : $revid;
					$diff = ($revid > $tfrev->fr_rev_id) ? $revid : $tfrev->fr_rev_id;
					# Is this revision flagged?
					$flags2 = parent::getFlagsForRevision( $revid );
					$app = false;
					foreach ( $flags2 as $f => $v ) {
						if ( $v > 0 ) $app=true;
					}
					if ( $app ) {
						$tag .= wfMsgExt('revreview-old', array('parse'));
						$tag .= parent::addTagRatings( $flags2 );
					}
					$tag .= wfMsgExt('revreview-newest', array('parse'), $tfrev->fr_rev_id, $oldid, $diff, $time);
				}
			# Viewing the page normally: override the page
			} else {
				global $wgUser;

				$skin = $wgUser->getSkin();
				// See if this page is featured
				$featured = parent::isPristine( $flags );
       			# We will be looking at the reviewed revision...
       			$vis_id = $tfrev->fr_rev_id;
       			$revs_since = parent::getRevCountSince( $pageid, $vis_id );
       			if ( $quality )
       				$tag = wfMsgExt('revreview-quality', array('parse'), $vis_id, $article->getLatest(), $revs_since, $time);
				else
					$tag = wfMsgExt('revreview-basic', array('parse'), $vis_id, $article->getLatest(), $revs_since, $time);
				# Try the stable page cache
				$parserOutput = parent::getPageCache( $article );
				# If no cache is available, get the text and parse it
				if ( $parserOutput==false ) {
					$text = parent::getFlaggedRevText( $vis_id );
					$options = ParserOptions::newFromUser($wgUser);
       				$parserOutput = parent::parseStableText( $wgTitle, $text, $vis_id, $options, $tfrev->fr_timestamp );
       				# Update the general cache
       				parent::updatePageCache( $article, $parserOutput );
       			}
       			$wgOut->addHTML( $parserOutput->getText() );
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
			// Construct some tagging
			$tag .= parent::addTagRatings( $flags );
			// Some checks for which tag CSS to use
			if ( $featured )
				$tag = '<div class="flaggedrevs_tag3 plainlinks">'.$tag.'</div>';
			else if ( $quality )
				$tag = '<div class="flaggedrevs_tag2 plainlinks">'.$tag.'</div>';
			else
				$tag = '<div class="flaggedrevs_tag1 plainlinks">'.$tag.'</div>';
			// Set the new body HTML, place a tag on top
			$wgOut->mBodytext = $tag . $wgOut->mBodytext . $notes;
		} else {
			$tag = '<div class="mw-warning plainlinks">'.wfMsgExt('revreview-noflagged', array('parse')).'</div>';
			$wgOut->addHTML( $tag );
		}
		// Show review links for the VISIBLE revision
		// We cannot review deleted revisions
		if( is_object($article->mRevision) && $article->mRevision->mDeleted ) return;
		// Add quick review links IF we did not override, otherwise, they might
		// review a revision that parses out newer templates/images than what they say
		// Note: overrides are never done when viewing with "oldid="
		if( $vis_id==$revid || !$this->pageOverride() ) {
			$this->addQuickReview( $vis_id, false, $out );
		}
    }
    
    function addToEditView( &$editform ) {
		global $wgRequest, $wgTitle, $wgOut;
		// Talk pages cannot be validated
		if( !$editform->mArticle || !$wgTitle->isContentPage() )
           return;
		// Find out revision id
		if( $editform->mArticle->mRevision )
       		$revid = $editform->mArticle->mRevision->mId;
		else
       		$revid = $editform->mArticle->getLatest();
		// Grab the ratings for this revision if any
		if( !$revid ) return;
		// Set new body html text as that of now
		$tag = '';
		// Check the newest stable version
		$tfrev = $this->getLatestStableRev( $editform->mArticle );
		if( is_object($tfrev) ) {
			global $wgParser, $wgLang;		
			$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $tfrev->fr_timestamp), true );
			$flags = parent::getFlagsForRevision( $tfrev->fr_rev_id );
			# Looking at some specific old rev
			if( $wgRequest->getVal('oldid') ) {
				if( $revid==$tfrev->fr_rev_id ) {
					$tag = wfMsgExt('revreview-isnewest', array('parse'),$time);
				} else {
					# Our compare link should have a reasonable time-ordered old->new combination
					$oldid = ($revid > $tfrev->fr_rev_id) ? $tfrev->fr_rev_id : $revid;
					$diff = ($revid > $tfrev->fr_rev_id) ? $revid : $tfrev->fr_rev_id;
					# Is this revision flagged?
					$flags2 = parent::getFlagsForRevision( $revid );
					$app = false;
					foreach ( $flags2 as $f => $v ) {
						if ( $v > 0 ) $app=true;
					}
					if ( $app ) {
						$tag .= wfMsgExt('revreview-old', array('parse'));
						$tag .= parent::addTagRatings( $flags2 );
					}
					$tag .= wfMsgExt('revreview-newest', array('parse'), $tfrev->fr_rev_id, $oldid, $diff, $time);
				}
			# Editing the page normally
			} else {
				if( $revid==$tfrev->fr_rev_id )
					$tag = wfMsgExt('revreview-isnewest', array('parse'), $time);
				else
					$tag = wfMsgExt('revreview-newest', array('parse'), $tfrev->fr_rev_id, $tfrev->fr_rev_id, $revid, $time );
			}
			// Construct some tagging
			$tag .= parent::addTagRatings( $flags );
			$wgOut->addHTML( '<div class="flaggedrevs_tag1 plainlinks">' . $tag . '</div><br/>' );
       }
    }
 
	/**
	 * Get latest quality rev, if not, the latest reviewed one
	 */
	function getOverridingRev( $article=NULL ) {
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
		if ( isset($this->stablefound) ) {
			return ( $this->stablerev ) ? $this->stablerev : null;
		}
		$dbr = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
        $result = $dbr->select(
			array('flaggedrevs', 'revision'),
			array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp'),
			array('fr_namespace' => $title->getNamespace(), 'fr_title' => $title->getDBkey(), 'fr_quality >= 1',
			'fr_rev_id = rev_id', 'rev_page' => $article->getId(), 'rev_deleted=0'),
			__METHOD__,
			array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 ) );
		// Do we have one?
        if ( $row = $dbr->fetchObject($result) ) {
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
		if ( isset($this->latestfound) ) {
			return ( $this->latestrev ) ? $this->latestrev : NULL;
		}
		$dbr = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
        $result = $dbr->select( 
			array('flaggedrevs', 'revision'),
			array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp'),
			array('fr_namespace' => $title->getNamespace(), 'fr_title' => $title->getDBkey(),
			'fr_rev_id = rev_id', 'rev_page' => $article->getId(), 'rev_deleted=0'),
			__METHOD__,
			array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 ) );
		// Do we have one?
        if ( $row = $dbr->fetchObject($result) ) {
        	$this->latestfound = true;
			$this->latestrev = $row;
			return $row;
	    } else {
            $this->latestfound = false;    
            return null;
        }
    }
    
    function setPermaLink( &$sktmp, &$nav_urls, &$oldid, &$revid ) {
    	global $wgArticle, $wgTitle, $action;
		// Only trigger on article view, not for protect/delete/hist
		// Non-content pages cannot be validated
		if( !$wgArticle || !$wgTitle->isContentPage() || !$this->pageOverride() )
			return;
		// Check for an overridabe revision
		$tfrev = $this->getLatestQualityRev();
		if ( !$tfrev ) return;
		$revid = $tfrev->fr_rev_id;
		// Replace "permalink" with an actual permanent link
		$nav_urls['permalink'] = array(
			'text' => wfMsg( 'permalink' ),
			'href' => $sktmp->makeSpecialUrl( 'Stableversions', "oldid=$revid" )
		);
		// Are we using the popular cite extension?
		if ( isset($nav_urls['cite']) ) {
			$nav_urls['cite'] = array(
				'text' => wfMsg( 'cite_article_link' ),
				'href' => $sktmp->makeSpecialUrl( 'Cite', "page=" . wfUrlencode( "{$sktmp->thispage}" ) . "&id=$revid" )
			);
		}
    }
    
    function setCurrentTab( &$sktmp, &$content_actions ) {
    	global $wgRequest, $wgArticle, $action;
		// Only trigger on article view, not for protect/delete/hist
		// Non-content pages cannot be validated
		if( !$wgArticle || !$sktmp->mTitle->exists() || !$sktmp->mTitle->isContentPage() || $action !='view' )
			return;
		// If we are viewing a page normally, and it was overrode
		// change the edit tab to a "current revision" tab
		if( !$wgRequest->getVal('oldid') ) {
       		$tfrev = $this->getOverridingRev( $wgArticle );
       		// No quality revs? Find the last reviewed one
       		if ( !is_object($tfrev) )
       			return;
       		// Note that revisions may not be set to override for users
       		if( $this->pageOverride() ) {
       			# Remove edit option altogether
       			unset( $content_actions['edit']);
       			unset( $content_actions['viewsource']);
				# Straighten out order
				$new_actions = array(); $counter = 0;
				foreach ( $content_actions as $action => $data ) {
					if( $counter==1 ) {
       				# Set current rev tab AFTER the main tab is set
						$new_actions['current'] = array(
							'class' => '',
							'text' => wfMsg('currentrev'),
							'href' => $sktmp->mTitle->getLocalUrl( 'stable=false' )
						);
					}
       			$new_actions[$action] = $data;
       			$counter++;
       			}
       			# Reset static array
       			$content_actions = $new_actions;
    		}
    	}
    }
       
    function addQuickReview( $id, $ontop=false, &$out=false ) {
		global $wgOut, $wgTitle, $wgUser, $wgScript, $wgFlaggedRevComments, $wgArticle, $wgRequest;
		// Hack, we don't want two forms!
		if( isset($this->formCount) && $this->formCount > 0 ) return;
		$this->formCount = 1;
				
		if( !$wgUser->isAllowed( 'review' ) ) return;
		// Already flagged?
		$flags = parent::getFlagsForRevision( $id );
       
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
		foreach ( $this->dimensions as $quality => $levels ) {
			$options = ''; $disabled = '';
			foreach ( $levels as $idx => $label ) {
				$selected = ( $flags[$quality]==$idx ) ? 'selected' : '';
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
        if ( $wgFlaggedRevComments ) {
			$form .= "<br/><p>" . wfMsgHtml( 'revreview-notes' ) . "</p>" .
			"<p><textarea tabindex='1' name='wpNotes' id='wpNotes' rows='2' cols='80' style='width:100%'></textarea>" .	
			"</p>\n";
		}
        $form .= "<p>".wfInputLabel( wfMsgHtml( 'revreview-log' ), 'wpReason', 'wpReason', 60 )." ";
		$form .= Xml::submitButton( wfMsgHtml( 'revreview-submit' ) ) . "</p></fieldset>";
		$form .= Xml::closeElement( 'form' );
		// Hacks, to fiddle around with location a bit
		if( $ontop && $out ) {
			$out->mBodytext = $form . '<hr/>' . $out->mBodytext;
		} else {
			$wgOut->addHTML( $form );
		}

    }

    function addToDiff( &$diff, &$oldrev, &$newrev ) {
       $id = $newrev->getId();
       // We cannot review deleted edits
       if( $newrev->mDeleted ) return;
       $this->addQuickReview( $id );
    }
    
    function addToPageHist( &$article ) {
    	$this->pageFlaggedRevs = array();
    	$rows = $this->getReviewedRevs( $article->getTitle() );
    	if( !$rows ) return;
    	foreach( $rows as $rev => $quality ) {
    		$this->pageFlaggedRevs[$rev] = $quality;
    	}
    }
    
    function addToHistLine( &$row, &$s ) {
    	global $wgUser;
    
    	if( isset($this->pageFlaggedRevs) ) {
    		// Try to keep the skin readily accesible
			static $skin=null;
			if( is_null( $skin ) )
				$skin = $wgUser->getSkin();
			
    		if( array_key_exists( $row->rev_id, $this->pageFlaggedRevs ) ) {
    			$msg = ($this->pageFlaggedRevs[$row->rev_id] >= 1) ? 'hist-quality' : 'hist-stable';
    			$special = SpecialPage::getTitleFor( 'Stableversions' );
    			$s .= ' <tt><small><strong>' . 
				$skin->makeLinkObj( $special, wfMsgHtml($msg), 'oldid='.$row->rev_id ) . 
				'</strong></small></tt>';
    		}
		}
    } 

}

$flaggedrevs = new FlaggedRevs();
$flaggedarticle = new FlaggedArticle();
$wgHooks['ArticleViewHeader'][] = array($flaggedarticle, 'setPageContent');
$wgHooks['DiffViewHeader'][] = array($flaggedarticle, 'addToDiff');
$wgHooks['EditPage::showEditForm:initial'][] = array($flaggedarticle, 'addToEditView');
$wgHooks['SkinTemplateTabs'][] = array($flaggedarticle, 'setCurrentTab');
$wgHooks['PageHistoryBeforeList'][] = array($flaggedarticle, 'addToPageHist');
$wgHooks['PageHistoryLineEnding'][] = array($flaggedarticle, 'addToHistLine');
$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink'][] = array($flaggedarticle, 'setPermaLink');

$wgHooks['ArticleSaveComplete'][] = array($flaggedrevs, 'autoPromoteUser');

$wgHooks['ArticleEditUpdatesDeleteFromRecentchanges'][] = array($flaggedrevs, 'extraLinksUpdate');
$wgHooks['ArticleRevisionVisiblityUpdates'][] = array($flaggedrevs, 'extraLinksUpdate');
$wgHooks['SpecialMovepageAfterMove'][] = array($flaggedrevs, 'updateFromMove');
?>
