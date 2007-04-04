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

$wgExtensionFunctions[] = 'efLoadReviewMessages';

# Internationilization
function efLoadReviewMessages() {
	global $wgMessageCache, $RevisionreviewMessages;
	require( dirname( __FILE__ ) . '/FlaggedRevsPage.i18n.php' );
	foreach ( $RevisionreviewMessages as $lang => $langMessages ) {
		$wgMessageCache->addMessages( $langMessages, $lang );
	}
}

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
# and set the minimum level have it concerned a stable version
$wgFlaggedRevTags = array( 'accuracy'=>1, 'depth'=>1, 'style'=>1 );
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
# In order for stable versions to override
# we need some minimum flag requirements
## changed, so that we don't configure at two different places - jhb
# $wgMinFlagLevels = array('accuracy' => 1, 'depth' => 1, 'style' => 1);


# Lets users access the review UI and set some flags
$wgAvailableRights[] = 'review';
# Lets users set higher settings and freeze images
# Images are pooled, so they must be of the highest quality
$wgAvailableRights[] = 'validate';

# Define our basic reviewer class
$wgGroupPermissions['editor']['review']      = true;
$wgGroupPermissions['editor']['autopatrol']  = true;
$wgGroupPermissions['editor']['patrol']      = true;
$wgGroupPermissions['editor']['unwatched']   = true;

# Defines extra rights for advanced reviewer class
$wgGroupPermissions['reviewer']['validate']  = true;


# Define when users get automatically promoted

$wgFlaggedRevsAutopromote = array('editor' => array('days' => 0,
                                                    'edits' => 1),
                                'reviewer' => array('days' => 0,
                                                    'edits' => 3));

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
	
    function pageOverride() {
    	global $wgFlaggedRevsAnonOnly, $wgUser, $wgRequest;
    	return !( ( $wgFlaggedRevsAnonOnly && !$wgUser->isAnon() ) || 
			$wgRequest->getVal('oldid') || $wgRequest->getVal('diff') || $wgRequest->getText('stable')=='false' );
    }
	/**
	 * @param int $rev_id
	 * Get the text of a stable version
	 */	
    function getFlaggedRevText( $rev_id ) {
    	wfProfileIn( __METHOD__ );
    	
 		$db = wfGetDB( DB_SLAVE );
 		// select a row, this should be unique
		$result = $db->select('flaggedrevs', array('fr_text'), array('fr_rev_id' => $rev_id), __METHOD__);
		if( $row = $db->fetchObject($result) ) {
			return $row->fr_text;
		}
       return NULL;
    }

	/**
	 * @param int $rev_id
	 * Return an array output of the flags for a given revision
	 */	
    function getFlagsForRevision( $rev_id ) {
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
		// select a row, this should be unique
		// JOIN on the tag table and grab all the tags for this revision
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
	 * @param int $rev_id
	 * Returns a revision row
	 */		
	function getFlaggedRev( $rev_id ) {
		wfProfileIn( __METHOD__ );
    	
		$db = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
		$result = $db->select(
			array('flaggedrevs'),
			array('fr_page_id', 'fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment'),
			array('fr_rev_id' => $rev_id),
			__METHOD__ );
		// Sorted from highest to lowest, so just take the first one if any
		if ( $row = $db->fetchObject( $result ) ) {
			return $row;
		}
		return NULL;
	}

	/**
	 * Get latest flagged revision
	 * This passes rev_deleted revisions
	 * This is based on the current article and caches results
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
		
		$db = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
		$result = $db->select(
			array('flaggedrevs', 'revision'),
			array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment'),
			array('fr_page_id' => $page_id, 'rev_id=fr_rev_id', 'rev_deleted=0'),
			__METHOD__ ,
			array('ORDER BY' => 'fr_rev_id DESC') );
		// Sorted from highest to lowest, so just take the first one if any
		if ( $row = $db->fetchObject( $result ) ) {
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
    function getLatestStableRev_alt() {
        global $wgFlaggedRevTags, $wgArticle;
        
        wfProfileIn( __METHOD__ );

		$page_id = $wgArticle->getId();
		if( !$page_id ) return NULL;       
        // Cached results available?
		if ( isset($this->stablefound) ) {
			return ( $this->stablefound ) ? $this->stablerev : NULL;
		}
        
		$db = wfGetDB( DB_SLAVE );
		$tables = $db->tableNames('flaggedrevs','revision');
		$flaggedrevs = $db->tableName('flaggedrevs');
		$flaggedrevtags = $db->tableName('flaggedrevtags');
        $vars = array("$flaggedrevs.*");
        $conds=array("fr_page_id = $page_id", 'rev_id = fr_rev_id', 'rev_deleted = 0');
        $i = 1;
        foreach ($wgFlaggedRevTags as $dimension=>$minvalue) {
            $alias = 'd'.$i; 
            $vars[] = "$alias.frt_value as '$dimension'";
            $tables[] = "$flaggedrevtags as $alias";
            $conds[] = "fr_rev_id = $alias.frt_rev_id";
            $conds[] = "$alias.frt_dimension = '$dimension'";
            $conds[] = "$alias.frt_value >= $minvalue";
            $i++;
        }
        
        $sql = " SELECT ".implode(',',$vars);
        $sql.= " FROM ".implode(',',$tables);
        $sql.= " WHERE ".implode(' and ',$conds);
        $sql.= " ORDER BY fr_rev_id DESC";
        $result = $db->query($sql,__METHOD__ );
        // Sorted from highest to lowest, so just take the first one if any
		if ( $row = $db->fetchObject( $result ) ) {
			// Try to store results
			$this->stablefound = true;
			$this->stablerev = $row;

			return $row;
		}
		$this->stablefound = false;
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
		$db = wfGetDB( DB_SLAVE );
		$tagwhere = array();
		// Look for $wgFlaggedRevTags key flags only
		foreach ( $wgFlaggedRevTags as $flag => $minimum ) {
			$tagwhere[] = '(frt_dimension = ' . $db->addQuotes( $flag ) . ' AND frt_value >= ' . intval($minimum) . ')';
		}
		$tagwhere = implode(' OR ', $tagwhere);
		// Skip archived/deleted revs
		// Get group rows of the newest flagged revs and the number
		// of key flags that were fulfilled
		$result = $db->select(
			array('flaggedrevtags','flaggedrevs','revision'),
			array('fr_rev_id', 'fr_user', 'fr_timestamp', 'fr_comment', 'COUNT(*) as app_flag_count'),
			array('frt_page_id' => $page_id, $tagwhere, 'frt_rev_id=fr_rev_id', 'frt_rev_id=rev_id', 'rev_deleted=0'),
			__METHOD__,
			array('GROUP BY' => 'frt_rev_id', 'ORDER BY' => 'frt_rev_id DESC') );
		// Iterate through each flagged revision row
		while ( $row = $db->fetchObject( $result ) ) {
			// If all of our flags are up to par, we have a stable version
			if ( $row->app_flag_count==count($wgFlaggedRevTags) ) {
				// Try to store results
				$this->stablefound = true;
				$this->stablerev = $row;
				return $row;
			}
		}
		$this->stablefound = false;
		return NULL;
	}

	/**
	 * @param int $page_id
	 * Get rev ids of reviewed revs for a page
	 */
    function getReviewedRevs( $page_id ) {
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
		while ( $row = $db->fetchObject( $result ) ) {
        	$rows[] = $row;
		}
		return $rows;
    }

	/**
	 * @param int $page_id
	 * @param int $from_rev
	 * Get number of revs since a certain revision
	 */
    function getRevCountSince( $page_id, $from_rev ) {   
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
	 * @param string $text
	 * @returns string
	 * All included pages are expanded out to keep this text frozen
	 */
    function expandText( $text ) {
    	global $wgParser, $wgTitle;
    	// Do not treat this as parsing an article on normal view
    	// enter the title object as wgTitle
    	$options = new ParserOptions;
		$options->setRemoveComments( true );
		$options->setMaxIncludeSize( self::MAX_INCLUDE_SIZE );
		$output = $wgParser->preprocess( $text, $wgTitle, $options );
		return $output;
	}
    
    function parseStableText( $title, $text, $id=NULL, $options ) {
    	global $wgUser, $wgParser, $wgUploadDirectory, $wgUseSharedUploads, $wgUploadPath;
    	# hack...temporarily change image directories
		# There is no nice option to set this for each parse.
		# This lets the parser know where to look...
		$uploadPath = $wgUploadPath;
		$uploadDir = $wgUploadDirectory;
		$useSharedUploads = $wgUseSharedUploads;
		
		# Stable thumbnails need to have the right path
		$wgUploadPath = ($wgUploadPath) ? "{$uploadPath}/stable" : false;
		# Create <img> tags with the right url
		$wgUploadDirectory = "{$uploadDir}/stable";
		# Stable images are never stored at commons
		$wgUseSharedUploads = false;
		
		$options->setTidy(true);
		# Don't show section-edit links
		# They can be old and misleading
		$options->setEditSection(false);
		# Parse the new body, wikitext -> html
       	$parserOut = $wgParser->parse( $text, $title, $options, true, true, $id );
       	
       	# Reset our image directories
       	$wgUploadPath = $uploadPath;
       	$wgUploadDirectory = $uploadDir;
       	$wgUseSharedUploads = $useSharedUploads;
       	
       	$HTMLout = $parserOut->getText();      	
       	# goddamn hack...
       	# Thumbnails are stored based on width, don't do any unscaled resizing
       	# MW will add height/width based on the size data in the db for the *current* image
		$HTMLout = preg_replace( '/(<img[^<>]+ )height="\d+" ([^<>]+>)/i','$1$2', $HTMLout);
		 	
       	return $HTMLout;
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
		if( !$wgArticle || !$wgTitle->isContentPage() || $action !='view' )
			return;
		// Grab page and rev ids
		$pageid = $wgArticle->getId();
		$revid = ( $wgArticle->mRevision ) ? $wgArticle->mRevision->mId : $wgArticle->getLatest();
		if( !$revid ) return;
		$visible_id = $revid;
		$flaghtml = ''; $notes = ''; $newbody = $out->mBodytext;
		// Check the newest stable version...
		// getLatestStableRev() is slower, don't use it if we won't
		// insert the stable page anyway, such as for oldids or diffs
		$stable = false;	
		if ( $this->pageOverride() ) {
			$top_frev = $this->getLatestStableRev();
			if ( is_null($top_frev) ) {
				$top_frev = $this->getLatestFlaggedRev();
			} else {
				$stable = true;
			}
		} else {
			$top_frev = $this->getLatestFlaggedRev();
		}
		if( $wgRequest->getVal('diff') ) {
    		// Do not clutter up diffs any further...
		} else if( !is_null($top_frev) ) {
			global $wgParser, $wgLang;
			// Get flags and date
			$flags = $this->getFlagsForRevision( $top_frev->fr_rev_id );
			$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $top_frev->fr_timestamp), true );
			// Looking at some specific old rev or if flagged revs override only for anons
			if( !$this->pageOverride() || !$stable ) {
				if( $revid==$top_frev->fr_rev_id ) {
					$flaghtml = wfMsgExt('revreview-isnewest', array('parse'),$time);
					$notes = $this->ReviewNotes( $top_frev );
				} else {
					# Our compare link should have a reasonable time-ordered old->new combination
					$oldid = ($revid > $top_frev->fr_rev_id) ? $top_frev->fr_rev_id : $revid;
					$diff = ($revid > $top_frev->fr_rev_id) ? $revid : $top_frev->fr_rev_id;
					$flaghtml = wfMsgExt('revreview-newest', array('parse'), $top_frev->fr_rev_id, $oldid, $diff, $time);
				}
			} # Viewing the page normally: override the page
			else {
				global $wgUser;
       			# We will be looking at the reviewed revision...
       			$visible_id = $top_frev->fr_rev_id;
       			$revs_since = $this->getRevCountSince( $pageid, $visible_id );
       			$flaghtml = wfMsgExt('revreview-replaced', array('parse'), $visible_id, $wgArticle->getLatest(), $revs_since, $time);
				# Try the stable page cache
				$newbody = $this->getPageCache( $wgArticle );
				# If no cache is available, get the text and parse it
				if ( is_null($newbody) ) {
					$text = $this->getFlaggedRevText( $visible_id );
					$options = ParserOptions::newFromUser($wgUser);
					# Parsing this text is kind of funky...
       				$newbody = $this->parseStableText( $wgTitle, $text, $visible_id, $options );
       				# Update the general cache for non-users
       				$this->updatePageCache( $wgArticle, $newbody );
       			}
				$notes = $this->ReviewNotes( $top_frev );
			}
			// Construct some tagging
			$flaghtml .= $this->addTagRatings( $flags );
			// Set the new body HTML, place a tag on top
			$out->mBodytext = '<div class="mw-warning plainlinks"><small>' . $flaghtml . '</small></div>' . $newbody . $notes;
		} else {
			$flaghtml = wfMsgExt('revreview-noflagged', array('parse'));
			$out->mBodytext = '<div class="mw-warning plainlinks">' . $flaghtml . '</div>' . $out->mBodytext;
		}
		// Override our reference ID for permalink/citation hooks
		$wgArticle->mRevision = Revision::newFromId( $visible_id );
		// Show review links for the VISIBLE revision
		// We cannot review deleted revisions
		if( is_object($wgArticle->mRevision) && $wgArticle->mRevision->mDeleted ) return;
		// Add quick review links IF we did not override, otherwise, they might
		// review a revision that parses out newer templates/images than what they say
		// Note: overrides are never done when viewing with "oldid="
		if( $visible_id==$revid || !$this->pageOverride() ) {
			$this->addQuickReview( $visible_id, false, $out );
		}
    }
    
    function addTagRatings( $flags ) {
    	$flaghtml = "<table align='center' cellspadding=\'0\'><tr>";
		foreach ( $this->dimensions as $quality => $value ) {
			$value = wfMsgHtml('revreview-' . $this->dimensions[$quality][$flags[$quality]]);
			$flaghtml .= "<td>&nbsp;<strong>" . wfMsgHtml("revreview-$quality") . "</strong>: $value&nbsp;</td>\n";    
		}
		$flaghtml .= '</tr></table>';
		return $flaghtml;
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
		$flaghtml = '';
		// Check the newest stable version
		$top_frev = $this->getLatestFlaggedRev( $editform->mArticle );
		if( is_object($top_frev) ) {
			global $wgParser, $wgLang;		
			$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $top_frev->fr_timestamp), true );
			$flags = $this->getFlagsForRevision( $top_frev->fr_rev_id );
			# Looking at some specific old rev
			if( $wgRequest->getVal('oldid') ) {
				if( $revid==$top_frev->fr_rev_id ) {
					$flaghtml = wfMsgExt('revreview-isnewest', array('parse'),$time);
				} else {
					# Our compare link should have a reasonable time-ordered old->new combination
					$oldid = ($revid > $top_frev->fr_rev_id) ? $top_frev->fr_rev_id : $revid;
					$diff = ($revid > $top_frev->fr_rev_id) ? $revid : $top_frev->fr_rev_id;
					$flaghtml = wfMsgExt('revreview-newest', array('parse'), $top_frev->fr_rev_id, $oldid, $diff, $time );
				}
           } # Editing the page normally   
       	else {
				if( $revid==$top_frev->fr_rev_id )
					$flaghtml = wfMsgExt('revreview-isnewest', array('parse'), $time);
				else
					$flaghtml = wfMsgExt('revreview-newest', array('parse'), $top_frev->fr_rev_id, $top_frev->fr_rev_id, $revid, $time );
			}
			// Construct some tagging
			$flaghtml .= "<table align='center' cellpadding=\'0\'><tr>";
			foreach ( $this->dimensions as $quality => $value ) {
				$value = wfMsgHtml('revreview-' . $this->dimensions[$quality][$flags[$quality]]);
				$flaghtml .= "<td>&nbsp;<strong>" . wfMsgHtml("revreview-$quality") . "</strong>: $value&nbsp;</td>\n";    
			}
			$flaghtml .= '</tr></table>';
			// Should use CSS?
			$flaghtml = "<small>$flaghtml</small>";
			$wgOut->addHTML( '<div class="mw-warning plainlinks">' . $flaghtml . '</div><br/>' );
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
       		$top_frev = $this->getLatestStableRev();
       		// Note that revisions may not be set to override for users
       		if( is_object($top_frev) && $this->pageOverride() ) {
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

    function addToDiff( &$diff, &$oldrev, &$newrev ) {
       $id = $newrev->getId();
       // We cannot review deleted edits
       if( $newrev->mDeleted ) return;
       $this->addQuickReview( $id, true );
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
    	if( isset($this->pageFlaggedRevs) ) {
    		if( in_array( $row->rev_id, $this->pageFlaggedRevs ) )
    			$s .= ' <tt><small><strong>' . wfMsgHtml('revreview-hist') . '</strong></small></tt>';
    	}
    }
       
    function addQuickReview( $id, $ontop=false, &$out=false ) {
		global $wgOut, $wgTitle, $wgUser, $wgScript;
		// Hack, we don't want two forms!
		if( isset($this->formCount) && $this->formCount > 0 ) return;
		$this->formCount = 1;
				
		if( !$wgUser->isAllowed( 'review' ) ) return;
		// Already flagged?
		$flags = $this->getFlagsForRevision( $id );
       
		$reviewtitle = SpecialPage::getTitleFor( 'Revisionreview' );
		$form = Xml::openElement( 'form', array( 'method' => 'get', 'action' => $wgScript ) );
		$form .= "<fieldset><legend>" . wfMsgHtml( 'revreview-flag', $id ) . "</legend>\n";
		$form .= wfHidden( 'title', $reviewtitle->getPrefixedText() );
		$form .= wfHidden( 'target', $wgTitle->getPrefixedText() );
		$form .= wfHidden( 'oldid', $id );
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
		$form .= Xml::submitButton( wfMsgHtml( 'go' ) ) . "</fieldset>";
		$form .= Xml::closeElement( 'form' );
		// Hacks, to fiddle around with location a bit
		if( $ontop && $out ) {
			$out->mBodytext = $form . '<hr/>' . $out->mBodytext;
		} else {
			$wgOut->addHTML( $form );
		}
    }
    
    function ReviewNotes( $row, $breakline=true ) {
    	global $wgUser, $wgFlaggedRevComments;
    	$notes = '';
    	if( !$row || !$wgFlaggedRevComments) return $notes;
    	
    	$this->skin = $wgUser->getSkin();
    	if( $row->fr_comment ) {
    		$notes = ($breakline) ? '<hr/>' : '';
    		$notes .= '<p><div class="mw-warning plainlinks">';
    		$notes .= wfMsgExt('revreview-note', array('parse'), User::whoIs( $row->fr_user ) );
    		$notes .= '<i>' . $this->skin->formatComment( $row->fr_comment ) . '</i></div></p>';
    	}
    	return $notes;
    }
	
	/**
	* Get all local image files and generate an array of them
	* @param string $s, wikitext
	* $output array, (string title array, string thumbnail array)
	*/
    function findLocalImages( $s ) {
    	global $wgUploadPath;
    	
    	$imagelist = array(); $thumblist = array();
    	
    	if( !$s || !strval($s) ) return $imagelist;

		static $tc = FALSE;
		# the % is needed to support urlencoded titles as well
		if( !$tc ) { $tc = Title::legalChars() . '#%'; }
		
		# split the entire text string on occurences of [[
		$a = explode( '[[', $s );
		
		# Ignore things that start with colons, they are image links, not images
		$e1_img = "/^([:{$tc}]+)(.+)$/sD";
		# Loop for each link
		for ($k = 0; isset( $a[$k] ); $k++) {
			if( preg_match( $e1_img, $a[$k], $m ) ) { 
				# page with normal text or alt of form x or ns:x
				$nt = Title::newFromText( $m[1] );
				$ns = $nt->getNamespace();
				# add if this is an image
				if( $ns == NS_IMAGE ) {
					$imagelist[] = $nt->getPrefixedText();
				}
				$image = $nt->getDBKey();
				# check for data for thumbnails
				$part = array_map( 'trim', explode( '|', $m[2]) );
				foreach( $part as $val ) {
					if( preg_match( '/^([0-9]+)px$/', $val, $n ) ) {
						$width = intval( $n[1] );
						$thumblist[$image] = $width;
					} else if( preg_match( '/^([0-9]+)x([0-9]+)$/', $val, $n ) ) {
						$width = intval( $n[1] );
						$thumblist[$image] = $width;
					}
				}
			}
		}
		return array( $imagelist, $thumblist );
    }

	/**
	* Showtime! Copy all used images to a stable directory
	* This updates (overwrites) any existing stable images
	* Won't work for sites with unhashed dirs that have subfolders protected
	* The future FileStore migration might effect this, not sure...
	* @param array $imagelist, list of string names
	* @param bool $updateImgs, update existing images?
	* $output array, list of string names of images sucessfully cloned
	*/
    function makeStableImages( $imagelist, $updateImgs ) {
    	global $wgUploadDirectory, $wgSharedUploadDirectory;
    	// All stable images are local, not shared
    	// Otherwise, we could have some nasty cross language/wiki conflicts
    	$stableDir = "$wgUploadDirectory/stable";
    	// Copy images to stable dir
    	$usedimages = array();
    	// We need valid input
    	if( !is_array($imagelist) ) return $usedimages;
    	foreach ( $imagelist as $name ) {
    		// We want a clean and consistant title entry
			$nt = Title::newFromText( $name );
			if( is_null($nt) ) {
			// If this title somehow doesn't work, ignore it
			// this shouldn't happen...
				continue;
			}
			$name = $nt->getDBkey();
    		$hash = wfGetHashPath($name);
    		$path = $wgUploadDirectory . $hash;
    		$sharedpath = $wgSharedUploadDirectory . $hash;
    		// Try local repository
    		if( is_dir($path) ) {
    			if( file_exists("{$path}{$name}") ) {
    				// Make stable dir if it doesn't exist
    				if( !is_dir($stableDir . $hash) ) {
    					wfMkdirParents($stableDir . $hash);
    				}
    				// Is user allowed to overwrite?
    				if ( !$updateImgs && file_exists("{$stableDir}{$hash}{$name}") )
    					continue;
    				copy("{$path}{$name}","{$stableDir}{$hash}{$name}");
    				$usedimages[] = $name;
    			}
    		} // Try shared repository
			else if( is_dir($sharedpath) ) {
    			if( file_exists("{$sharedpath}{$name}") ) {
    				// Make stable dir if it doesn't exist
    				if( !is_dir($stableDir . $hash) ) {
    					wfMkdirParents($stableDir . $hash);
    				}
    				// Is user allowed to overwrite?
    				if ( !$updateImgs && file_exists("{$stableDir}{$hash}{$name}") )
    					continue;
    				copy("{$sharedpath}{$name}","{$stableDir}{$hash}{$name}");
    				$usedimages[] = $name;
    			}
    		}
    	}
    	return $usedimages;
    }
    
	/**
	* Delete an a list of stable image files
	* @param array $imagelist, list of string names
	* $output array, list of string names of images to be deleted
	*/
    function deleteStableImages( $imagelist ) {
    	global $wgUploadDirectory, $wgSharedUploadDirectory;
    	// All stable images are local, not shared
    	// Otherwise, we could have some nasty cross language/wiki conflicts
    	$stableDir = "$wgUploadDirectory/stable";
    	// Copy images to stable dir
    	$deletedimages = array();
    	// We need valid input
    	if( !is_array($imagelist) ) return $usedimages;
    	foreach ( $imagelist as $name ) {
    	    // We want a clean and consistant title entry
			$nt = Title::newFromText( $name );
			if( is_null($nt) ) {
			// If this title somehow doesn't work, ignore it
			// this shouldn't happen...
				continue;
			}
			$name = $nt->getDBkey();
    		$hash = wfGetHashPath($name);
    		$path = $stableDir . $hash;
    		// Try the stable repository
    		if( is_dir($path) ) {
    			if( file_exists("{$path}{$name}") ) {
    				// Delete!
    				unlink("{$path}{$name}");
    				$deletedimages[] = $name;
    			}
    		}
    	}
    	return $deletedimages;
    }

	/**
	* Delete an a list of stable image thumbnails
	* New thumbnails don't normally override old ones, causing outdated images
	* This allows for tagged revisions to be re-reviewed with newer images
	* @param array $imagelist, list of string names
	* $output array, list of string names of images to be deleted
	*/ 
	function purgeStableThumbnails( $thumblist ) {
		global $wgUploadDirectory, $wgUseImageResize;
		// Are thumbs even enabled?
		if ( !$wgUseImageResize ) return true;
		// We need valid input
		if( !is_array($thumblist) ) return false;
    	foreach ( $thumblist as $name => $width ) {
    		$thumburl = "{$wgUploadDirectory}/stable/thumb" . wfGetHashPath( $name, false ) . "$name/". $width."px-".$name;
			if( file_exists($thumburl) ) {
    			unlink($thumburl);
    		}
    	}
    	return true;
    }

	/**
	* Update the stable image usage table
	* Add some images if not redundant
	* @param array $imagelist, list of string names
	* $output bool, on succeed
	*/	     
    function insertStableImages( $revid, $imagelist ) {
		wfProfileIn( __METHOD__ );
		
		if( !is_array($imagelist) ) return false;
		
       $dbw = wfGetDB( DB_MASTER );
       foreach( $imagelist as $name ) {
			// We want a clean and consistant title entry
			$nt = Title::newFromText( $name );
			if( is_null($nt) ) {
			// If this title somehow doesn't work, ignore it
			// this shouldn't happen...
				continue;
			}
			$imagename = $nt->getDBkey();
			// Add image and the revision that uses it
 			$set = array('fi_rev_id' => $revid, 'fi_name' => $imagename);
			// Add entries or replace any that have the same rev_id
			$dbw->replace( 'flaggedimages', array( array('fi_rev_id', 'fi_name') ), $set, __METHOD__ );	
		}
		return true;	
    }
    
	/**
	* Update the stable image usage table
	* Clean out unused images if needed
	* @param array $imagelist, list of string names
	* $output bool, on succeed
	*/	     
    function removeStableImages( $revid, $imagelist ) {
		wfProfileIn( __METHOD__ );
		
		if( !is_array($imagelist) ) return false;
		$unusedimages = array();
		$dbw = wfGetDB( DB_MASTER );
		foreach( $imagelist as $name ) {
			// We want a clean and consistant title entry
			$nt = Title::newFromText( $name );
			if( is_null($nt) ) {
			// If this title somehow doesn't work, ignore it
			// this shouldn't happen...
				continue;
			}
			$imagename = $nt->getDBkey();
 			$where = array( 'fi_rev_id' => $revid, 'fi_name' => $imagename );
			// See how many revisions use this image total...
			$result = $dbw->select( 'flaggedimages', array('fi_id'), array( 'fi_name' => $imagename ),__METHOD__ );
			// If only one, then delete the image
			// since it's about to be remove from that one
			if( $db->numRows($result)==1 ) {
				$unusedimages[] = $imagename;
			}
			// Clear out this revision's entry
			$dbw->delete( 'flaggedimages', $where );
		}
		FlaggedRevs::deleteStableImages( $unusedimages );
		return true;
    }
    
    function getPageCache( $article ) {
    	global $wgUser, $wgFlaggedRevsExpire;
    	
    	wfProfileIn( __METHOD__ );
    	
    	// Make sure it is valid
    	if ( !$article || !$article->getId() ) return NULL;
    	$cachekey = ParserCache::getKey( $article, $wgUser );
    	
		wfSeedRandom();
		if ( 0 == mt_rand( 0, 999 ) ) {
			# Periodically flush old cache entries
			global $wgFlaggedRevsExpire;

			$dbw = wfGetDB( DB_MASTER );
			$cutoff = $dbw->timestamp( time() - $wgFlaggedRevsExpire );
			$flaggedcache = $dbw->tableName( 'flaggedcache' );
			$sql = "DELETE FROM $flaggedcache WHERE fc_timestamp < '{$cutoff}'";
			$dbw->query( $sql );
		}
    	
    	$db = wfGetDB( DB_SLAVE );
    	// Replace the page cache if it is out of date
    	$result = $db->select(
			array('flaggedcache'),
			array('fc_cache'),
			array('fc_key' => $cachekey, 'fc_timestamp >= ' . $article->getTouched() ),
			__METHOD__ );
		if ( $row = $db->fetchObject($result) ) {
			return $row->fc_cache;
		}
		return NULL;		
    }
    
    function updatePageCache( $article, $value=NULL ) {
    	global $wgUser;
    	wfProfileIn( __METHOD__ );
    	
    	// Make sure it is valid
    	if ( is_null($value) || !$article || !$article->getId() ) return false;
    	$cachekey = ParserCache::getKey( $article, $wgUser );
    	// Add cache mark
    	$timestamp = wfTimestampNow();
    	$value .= "\n<!-- Saved in stable version parser cache with key $cachekey and timestamp $timestamp -->";
    	
		$dbw = wfGetDB( DB_MASTER );
    	// Replace the page cache if it is out of date
		$dbw->replace('flaggedcache',
    		array('fc_key'),
			array('fc_key' => $cachekey, 'fc_cache' => $value, 'fc_timestamp' => $timestamp),
			__METHOD__ );
		
		return true;
    }

    
	/**
	* Callback that autopromotes user according to the setting in 
    * $wgFlaggedRevsAutopromote
	*/


    function autoPromoteUser ($article,$user,$text,$summary,$isminor,$iswatch,$section) {
        global $wgFlaggedRevsAutopromote;

        $groups = $user->getGroups();
        $now = time();
        $usercreation = wfTimestamp(TS_UNIX,$user->mRegistration);
        $userage = floor(($now-$usercreation) / 86400);
        foreach ($wgFlaggedRevsAutopromote as $group=>$vars) {
            if (!in_array($group,$groups) && $userage >= $vars['days'] && $user->getEditCount() >= $vars['edits']) {
                    $user->addGroup($group);
            }
        }
        $newgroups = $user->getGroups();
        if (1 || $groups != $newgroups) {
            $log = new LogPage( 'rights' );
		    $log->addEntry('rights', $user->getUserPage(), "", array("(".implode(',',$groups).")",
                                                                     "(".implode(',',$newgroups).")"));
    
        }
    }


}

# Load expert promotion UI
include_once('SpecialMakevalidate.php');

if( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}
extAddSpecialPage( dirname(__FILE__) . '/FlaggedRevsPage.body.php', 'Revisionreview', 'Revisionreview' );
extAddSpecialPage( dirname(__FILE__) . '/FlaggedRevsPage.body.php', 'Stableversions', 'Stableversions' );

# Load approve/unapprove UI
$wgHooks['LoadAllMessages'][] = 'efLoadReviewMessages';

$flaggedrevs = new FlaggedRevs();
$wgHooks['BeforePageDisplay'][] = array($flaggedrevs, 'setPageContent');
$wgHooks['DiffViewHeader'][] = array($flaggedrevs, 'addToDiff');
$wgHooks['EditPage::showEditForm:initial'][] = array($flaggedrevs, 'addToEditView');
$wgHooks['SkinTemplateTabs'][] = array($flaggedrevs, 'setCurrentTab');
$wgHooks['PageHistoryBeforeList'][] = array($flaggedrevs, 'addToPageHist');
$wgHooks['PageHistoryLineEnding'][] = array($flaggedrevs, 'addToHistLine');
$wgHooks['ArticleSaveComplete'][] = array($flaggedrevs, 'autoPromoteUser');
?>
