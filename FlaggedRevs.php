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

$wgExtensionFunctions[] = 'efLoadReviewMessages';

$wgExtensionCredits['other'][] = array(
	'author' => 'Aaron Schulz, Joerg Baach',
	'name' => 'Flagged Revisions',
	'url' => 'http://www.mediawiki.org/wiki/Extension:FlaggedRevs',
	'description' => 'Gives editors/reviewers the ability to validate revisions and stablize pages'
);

# Internationilization
function efLoadReviewMessages() {
	global $wgMessageCache, $RevisionreviewMessages, $wgOut;
	require( dirname( __FILE__ ) . '/FlaggedRevsPage.i18n.php' );
	
	foreach ( $RevisionreviewMessages as $lang => $langMessages ) {
		$wgMessageCache->addMessages( $langMessages, $lang );
	}
	# Set the CSS
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
$wgUseExpandedCache = true;

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

# Define when users get automatically promoted
$wgFlaggedRevsAutopromote = array('editor' => array('days' => 60,
                                                    'edits' => 1000,
													'email' => true) );
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
				return $row->fr_text;
			}
		}
		return NULL;
    }
    
	/**
	 * @param int $rev_id
	 * Returns a revision row
	 */		
	public static function getFlaggedRev( $rev_id ) {
		wfProfileIn( __METHOD__ );
    	
		$db = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
		$result = $db->select(
			array('flaggedrevs','revision'),
			array('fr_page_id', 'fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment','rev_timestamp'),
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
	 */
    public static function getReviewedRevs( $page_id ) {
		wfProfileIn( __METHOD__ );
		  
		$db = wfGetDB( DB_SLAVE ); 
		$rows = array();
		// Skip deleted revisions
		$result = $db->select(
			array('flaggedrevs'),
			array('fr_rev_id'),
			array('fr_page_id' => $page_id),
			__METHOD__ ,
			array('ORDER BY' => 'fr_rev_id DESC') );
		while ( $row = $db->fetchObject($result) ) {
        	$rows[] = $row;
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
    
    public function addTagRatings( $flags ) {
    	$tag = "<p>";
		foreach ( $this->dimensions as $quality => $value ) {
			$value = wfMsgHtml('revreview-' . $this->dimensions[$quality][$flags[$quality]]);
			$tag .= "&nbsp;<strong>" . wfMsgHtml("revreview-$quality") . "</strong>: $value&nbsp;\n";    
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
    		$notes .= '<i>' . $skin->formatComment( $row->fr_comment ) . '</i></div></p>';
    	}
    	return $notes;
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
    	$key = 'stable-' . ParserCache::getKey( $article, $wgUser );
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
    	
    	// Make sure it is valid
    	if ( is_null($parserOutput) || !$article || !$article->getId() ) 
			return false;
    	$key = 'stable-' . ParserCache::getKey( $article, $wgUser );
    	// Add cache mark to HTML
    	$timestamp = wfTimestampNow();
    	$parserOutput->mText .= "\n<!-- Saved in stable version parser cache with key $key and timestamp $timestamp -->";
		// Set expire time
		if( $parserOutput->containsOldMagic() ){
			$expire = 3600; # 1 hour
		} else {
			$expire = $wgFlaggedRevsExpire;
		}
		// Save to objectcache
		$parserMemc->set( $key, $parserOutput, $expire );
		// Update the cache...
		$article->mTitle->invalidateCache();
		// Purge squid for this page only
		$article->mTitle->purgeSquid();
		
		return true;
    }

	/**
	* Callback that autopromotes user according to the setting in 
    * $wgFlaggedRevsAutopromote
	*/
	private static function autoPromoteUser( $article, $user, $text, $summary, $isminor, $iswatch, $section ) {
		global $wgUser, $wgFlaggedRevsAutopromote;
		
		$groups = $user->getGroups();
		$now = time();
		$usercreation = wfTimestamp(TS_UNIX,$user->mRegistration);
		$userage = floor(($now-$usercreation) / 86400);
		// Check if we need to promote?
		foreach ($wgFlaggedRevsAutopromote as $group=>$vars) {
			if ( !in_array($group,$groups) && $userage >= $vars['days'] && $user->getEditCount() >= $vars['edits']
				&& ( !$vars['email'] || $wgUser->isAllowed('emailconfirmed') ) ) {
    			# Do not re-add status if it was previously removed...
    			$fname = 'FlaggedRevs::autoPromoteUser';
				$db = wfGetDB( DB_SLAVE );
    			$result = $db->select(
					array('logging'),
					array('log_user'),
					array("log_type='validate'", "log_action='revoke1'", 'log_namespace' => NS_USER, 'log_title' => $user->getName() ),
					$fname,
					'LIMIT = 1');
				# Add rights if they were never removed
				if ( !$db->numRows($result) ) {
					$user->addGroup($group);
					# Lets NOT spam RC, set $RC to false
					$log = new LogPage( 'validate', false );
					$log->addEntry('grant1', $user->getUserPage(), wfMsgHtml('makevalidate-autosum') );
				}
			}
		}
    }
    
	/**
	* @param Array $flags
	* @output bool, is this revision at optimal condition?
	*/
    function isFeatured( $flags ) {
    	global $wgFlaggedRevValues;
    	
    	foreach ( $flags as $f => $v ) {
    		if ( $v < $wgFlaggedRevValues ) return false;
    	}
    	return true;
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
	function setPageContent( &$out ) {
		global $wgArticle, $wgRequest, $wgTitle, $action;
		// Only trigger on article view, not for protect/delete/hist
		// Talk pages cannot be validated
		if( !$wgArticle || !$wgTitle->isContentPage() || $action !='view' ) return;
		// Grab page and rev ids
		$pageid = $wgArticle->getId();
		$revid = ( $wgArticle->mRevision ) ? $wgArticle->mRevision->mId : $wgArticle->getLatest();
		// There must be a valid rev ID
		if( !$revid ) return;
		
		$vis_id = $revid;
		$tag = ''; $notes = ''; $newbody = $out->mBodytext;
		// Check the newest stable version...
		$quality = false; $featured = false;
		if ( $this->pageOverride() ) {
			// getLatestStableRev() is slower, don't use it if we won't need to
			$tfrev = $this->getLatestStableRev();
			if ( is_null($tfrev) ) {
				$tfrev = $this->getLatestFlaggedRev();
			} else {
				$quality = true;
			}
		} else {
			$tfrev = $this->getLatestFlaggedRev();
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
				$featured = parent::isFeatured( $flags );
       			# We will be looking at the reviewed revision...
       			$vis_id = $tfrev->fr_rev_id;
       			$revs_since = parent::getRevCountSince( $pageid, $vis_id );
       			if ( $quality )
       				$tag = wfMsgExt('revreview-quality', array('parse'), $vis_id, $wgArticle->getLatest(), $revs_since, $time);
				else
					$tag = wfMsgExt('revreview-basic', array('parse'), $vis_id, $wgArticle->getLatest(), $revs_since, $time);
				# Try the stable page cache
				$newbody = parent::getPageCache( $wgArticle );
				# If no cache is available, get the text and parse it
				if ( $newbody==false ) {
					$text = parent::getFlaggedRevText( $vis_id );
					$options = ParserOptions::newFromUser($wgUser);
       				$parserOutput = parent::parseStableText( $wgTitle, $text, $vis_id, $options, $tfrev->fr_timestamp );
       				# Update the general cache
       				parent::updatePageCache( $wgArticle, $parserOutput );
       				$newbody = $parserOutput->getText();
       			}
				$notes = parent::ReviewNotes( $tfrev );
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
			$out->mBodytext = $tag . $newbody . $notes;
			// Show notice about categories and other unreviewed things
			if ( count($out->mCategoryLinks) ) {
				$out->mBodytext .= '<hr/><p><div class="flaggedrevs_notice plainlinks">' . wfMsg('revreview-warning') . '</div></p>';
			}
		} else {
			$tag = '<div class="mw-warning plainlinks">'.wfMsgExt('revreview-noflagged', array('parse')).'</div>';
			$out->mBodytext = $tag . $out->mBodytext;
		}
		// Show review links for the VISIBLE revision
		// We cannot review deleted revisions
		if( is_object($wgArticle->mRevision) && $wgArticle->mRevision->mDeleted ) return;
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
		$tfrev = $this->getLatestFlaggedRev( $editform->mArticle );
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
	 * Get latest flagged revision
	 * This passes rev_deleted revisions
	 * This is based on the current article and caches results
	 * Accepts an argument because of the fact that the article
	 * object for edit mode is part of the editform; insert only
	 * the article object for the current page!
	 * The cache here doesn't make sense for arbitrary articles
	 * @output array ( rev, flags )
	 */
	function getLatestFlaggedRev( $article=NULL ) {
		global $wgArticle;
		
		wfProfileIn( __METHOD__ );
		
		$article = ($article) ? $article : $wgArticle;
		if ( !$article ) return;
		
		$page_id = $article->getId();
		if( !$page_id ) return NULL;
		// Cached results available?
		if ( isset($this->latestfound) ) {
			return ( $this->latestfound ) ? $this->latestrev : NULL;
		}
		
		$dbr = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
		$result = $dbr->select(
			array('flaggedrevs', 'revision'),
			array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp'),
			array('fr_page_id' => $page_id, 'rev_id=fr_rev_id', 'rev_deleted=0'),
			__METHOD__ ,
			array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1) );
		// Sorted from highest to lowest, so just take the first one if any
		if ( $row = $dbr->fetchObject($result) ) {
			// Try to store results
			$this->latestfound = true;
			$this->latestrev = $row;
			return $row;
		}
		$this->latestfound = false;
		return NULL;
	}
    
	/**
	 * Get latest flagged revision that meets requirments
	 * per the $wgFlaggedRevTags variable
	 * This passes rev_deleted revisions
	 * This is based on the current article and caches results
	 * @output array ( rev, flags )
	 */
	function getLatestStableRev() {
		global $wgFlaggedRevTags, $wgArticle;
		
		wfProfileIn( __METHOD__ );
		
		$page_id = $wgArticle->getId();
        if( !$page_id ) return NULL;
		
        // Cached results available?
		if ( isset($this->stablefound) ) {
			return ( $this->stablefound ) ? $this->stablerev : NULL;
		}

        $rev = $this->getStableRevs( $page_id, null, 1 );
        
        if ( count($rev) ) {
            $this->stablefound = true;
			$this->stablerev = $rev[0];
            return $rev[0];
        } else {
            $this->stablefound = false;    
            return Null;
        }
	}

	/**
	 * Get all the revisions that meet the requirments
	 * per the $wgFlaggedRevTags variable
     * @param int $page_id
     * @param int $max_rev_id the revision ids should be less than this
	 */
	function getStableRevs( $page_id, $max_rev_id=NULL, $limit=1 ) {
        global $wgFlaggedRevTags;

		wfProfileIn( __METHOD__ );
		
		$dbr = wfGetDB( DB_SLAVE );
		$tagwhere = array();
        // Look for $wgFlaggedRevTags key flags only
		foreach ( $wgFlaggedRevTags as $flag => $min) {
			$tagwhere[] = '(frt_dimension = ' . $dbr->addQuotes($flag) . ' AND frt_value >= ' . intval($min) . ')';
		}
		$tagwhere = implode(' OR ', $tagwhere);
		$maxrevid = $max_rev_id ? "frt_rev_id < $max_rev_id" : '1 = 1';
        // Skip archived/deleted revs
		// Get group rows of the newest flagged revs and the number
		// of key flags that were fulfilled
        $result = $dbr->select(
			array('flaggedrevtags','flaggedrevs','revision'),
			array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'rev_timestamp', 'COUNT(*) as app_flags'),
			array('frt_page_id' => $page_id, $tagwhere, $maxrevid, 'frt_rev_id=fr_rev_id', 'fr_rev_id=rev_id', 'rev_deleted=0'),
			__METHOD__,
			array('GROUP BY' => 'frt_rev_id', 'ORDER BY' => 'frt_rev_id DESC') );
		// Iterate through each flagged revision row
        $out = array();
        $counter = 0;
        while ( $row = $dbr->fetchObject($result) ) {
			// Only return so many results
			if ( $counter > $limit ) break;
			// If all of our flags are up to par, we have a stable version
			else if ( $row->app_flags==count($wgFlaggedRevTags) ) {
                $out[] = $row;
                $counter++;
            }
	    }
        return $out;
    }
    
    function setPermaLink( &$sktmp, &$nav_urls, &$oldid, &$revid ) {
    	global $wgArticle, $wgTitle, $action;
		// Only trigger on article view, not for protect/delete/hist
		// Non-content pages cannot be validated
		if( !$wgArticle || !$wgTitle->isContentPage() || !$this->pageOverride() )
			return;
		// Check for an overridabe revision
		$tfrev = $this->getLatestStableRev();
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
       		$tfrev = $this->getLatestStableRev();
       		// No quality revs? Find the last reviewed one
       		if ( !is_object($tfrev) )
       			$tfrev = $this->getLatestFlaggedRev();
       		// Note that revisions may not be set to override for users
       		if( is_object($tfrev) && $this->pageOverride() ) {
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
    	$rows = $this->getReviewedRevs( $article->getID() );
    	if( !$rows ) return;
    	foreach( $rows as $row => $data ) {
    		$this->pageFlaggedRevs[] = $data->fr_rev_id;
    	}
    }
    
    function addToHistLine( &$row, &$s ) {
    	global $wgUser;
    
    	if( isset($this->pageFlaggedRevs) ) {
    		// Try to keep the skin readily accesible
			static $skin=null;
			if( is_null( $skin ) )
				$skin = $wgUser->getSkin();
			
    		if( in_array( $row->rev_id, $this->pageFlaggedRevs ) ) {
    			$special = SpecialPage::getTitleFor( 'Stableversions' );
    			$s .= ' <tt><small><strong>' . 
				$skin->makeLinkObj( $special, wfMsgHtml('revreview-hist'), 'oldid='.$row->rev_id ) . 
				'</strong></small></tt>';
    		}
		}
    }
    
}

# Load promotion UI
include_once('SpecialMakevalidate.php');

if( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}
extAddSpecialPage( dirname(__FILE__) . '/FlaggedRevsPage.body.php', 'Revisionreview', 'Revisionreview' );
extAddSpecialPage( dirname(__FILE__) . '/FlaggedRevsPage.body.php', 'Stableversions', 'Stableversions' );

# Load approve/unapprove UI
$wgHooks['LoadAllMessages'][] = 'efLoadReviewMessages';

$flaggedrevs = new FlaggedRevs();
$flaggedarticle = new FlaggedArticle();
$wgHooks['BeforePageDisplay'][] = array($flaggedarticle, 'setPageContent');
$wgHooks['DiffViewHeader'][] = array($flaggedarticle, 'addToDiff');
$wgHooks['EditPage::showEditForm:initial'][] = array($flaggedarticle, 'addToEditView');
$wgHooks['SkinTemplateTabs'][] = array($flaggedarticle, 'setCurrentTab');
$wgHooks['PageHistoryBeforeList'][] = array($flaggedarticle, 'addToPageHist');
$wgHooks['PageHistoryLineEnding'][] = array($flaggedarticle, 'addToHistLine');
$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink'][] = array($flaggedarticle, 'setPermaLink');
$wgHooks['ArticleSaveComplete'][] = array($flaggedrevs, 'autoPromoteUser');
?>
