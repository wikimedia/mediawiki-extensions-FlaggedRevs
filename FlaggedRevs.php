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
$wgFlaggedRevValues = 3;

$wgAvailableRights[] = 'review';
# Define our reviewer class
$wgGroupPermissions['reviewer']['rollback']    = true;
$wgGroupPermissions['reviewer']['autopatrol']  = true;
$wgGroupPermissions['reviewer']['patrol']      = true;
$wgGroupPermissions['reviewer']['review']      = true;

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
    	global $wgFlaggedRevsAnonOnly, $wgUser;
    	return !( $wgFlaggedRevsAnonOnly && !$wgUser->isAnon() );
    }
	
    function getFlaggedRevision( $rev_id ) {
 		$db = wfGetDB( DB_SLAVE );
 		// select a row, this should be unique
		$result = $db->select( 'flaggedrevs', array('*'), array('fr_rev_id' => $rev_id) );
		if( $row = $db->fetchObject($result) ) {
			return $row;
		}
		return NULL;
    }
    
    function getFlaggedRevText( $rev_id ) {
 		$db = wfGetDB( DB_SLAVE );
 		// select a row, this should be unique
		$result = $db->select( 'flaggedtext', array('ft_text'), array('ft_rev_id' => $rev_id) );
		if( $row = $db->fetchObject($result) ) {
			return $row->ft_text;
		}
       return NULL;
    }

    function getFlagsForRevision( $rev_id ) {
    	global $wgFlaggedRevTags;
    	// Set all flags to zero
    	$flags = array();
    	foreach( array_keys($wgFlaggedRevTags) as $tag ) {
    		$flags[$tag] = 0;
    	}
    	
		$db = wfGetDB( DB_SLAVE );
		// select a row, this should be unique
		// JOIN on the tag table and grab all the tags for this revision
		$result = $db->select(
			array('flaggedrevs', 'flaggedrevtags'),
			array('frt_dimension', 'frt_value'), 
			array('fr_rev_id' => $rev_id, 'fr_rev_id = frt_rev_id' ) );
		// Iterate through each tag
		while ( $row = $db->fetchObject($result) ) {
			$flags[$row->frt_dimension] = $row->frt_value;
		}
		return $flags;
	}

	function getLatestFlaggedRev_new( $page_id ) {
		wfProfileIn( __METHOD__ );
		  
		$db = wfGetDB( DB_SLAVE );
		// Skip deleted revisions
		$result = $db->select(
			array('flaggedrevs', 'revision'),
			array('*'),
			array( 'fr_page_id' => $page_id, 'rev_id = fr_rev_id', 'rev_deleted = 0'),
			__METHOD__ ,
			array('ORDER BY' => 'fr_rev_id DESC') );
		// Sorted from highest to lowest, so just take the first one if any
		if ( $row = $db->fetchObject( $result ) ) {

			return $row;
		}
		return NULL;
	}
   
    function getLatestFlaggedRev( $page_id) {
        global $wgFlaggedRevTags;
        wfProfileIn( __METHOD__ );  
		$db = wfGetDB( DB_SLAVE );
        $tables=array('flaggedrevs','revision');
        $vars = array('flaggedrevs.*');
        $conds=array("fr_page_id = $page_id", 'rev_id = fr_rev_id', 'rev_deleted = 0');
        $i = 1;
        foreach ($wgFlaggedRevTags as $dimension=>$minvalue) {
            $alias = 'd'.$i; 
            $vars[] = "$alias.frt_value as '$dimension'";
            $tables[] = "flaggedrevtags as $alias";
            $conds[] = "fr_rev_id = $alias.frt_rev_id";
            $conds[] = "$alias.frt_dimension = '$dimension'";
            $conds[] = "$alias.frt_value >= $minvalue";
            $i++;
        }
        
        /*$result = $db->select(
			$tables,
			$vars,
			$conds,
			__METHOD__ ,
			array('ORDER BY' => 'fr_rev_id DESC') );    */
        
        $sql = " SELECT ".implode(',',$vars);
        $sql.= " FROM ".implode(',',$tables);
        $sql.= " WHERE ".implode(' and ',$conds);
        $sql.= " ORDER BY fr_rev_id DESC";
        
        #print "$sql\n<br>\n";
        $result = $db->query($sql,__METHOD__);
        // Sorted from highest to lowest, so just take the first one if any
		if ( $row = $db->fetchObject( $result ) ) {
			return $row;
		}
		return NULL;
    }

    

    function getReviewedRevs( $page_id ) {   
		wfProfileIn( __METHOD__ );
		  
       $db = wfGetDB( DB_SLAVE ); 
       $rows = array();
       // Skip deleted revisions
       $result = $db->select(
			array('flaggedrevs', 'revision'),
			array('*'),
			array( 'fr_page_id' => $page_id, 'rev_id = fr_rev_id', 'rev_deleted = 0'),
			__METHOD__ ,
			array('ORDER BY' => 'fr_rev_id DESC') );
       while ( $row = $db->fetchObject( $result ) ) {
           $rows[] = $row;
       }
       return $rows;
    }
    
    function getUnreviewedRevCount( $page_id, $from_rev ) {   
		wfProfileIn( __METHOD__ );
		  
		$db = wfGetDB( DB_SLAVE );  
		$result = $db->select(
			array('revision'),
			array('rev_page'),
			array( 'rev_page' => $page_id, "rev_id > $from_rev" ),
			__METHOD__ ,
			array('ORDER BY' => 'rev_id DESC') );
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

       	$HTMLout = $parserOut->getText();
       	# goddamn hack...
       	# Thumbnails are stored based on width, don't do any unscaled resizing
       	# MW will add height/width based on the size data in the db for the *current* image
		$HTMLout = preg_replace( '/(<img[^<>]+ )height="\d+" ([^<>]+>)/i','$1$2', $HTMLout);
       	
       	# Reset our image directories
       	$wgUploadPath = $uploadPath;
       	$wgUploadDirectory = $uploadDir;
       	$wgUseSharedUploads = $useSharedUploads;
       	
       	return $HTMLout;
    }

	 /**
	 * Replaces a page with the last stable version if possible
	 * Adds stable version status/info tags
	 * Adds a quick review form on the bottom if needed
	 */
	function setPageContent( &$out ) {
		global $wgArticle, $wgRequest, $wgTitle, $action;
		// Only trigger on article view, not for protect/delete/hist
		// Talk pages cannot be validated
		if( !$wgArticle || !$wgTitle->isContentPage() || $action !='view' )
			return;
		// Find out revision id
		$revid = ( $wgArticle->mRevision ) ? $wgArticle->mRevision->mId : $wgArticle->getLatest();
		// Grab the ratings for this revision if any
		if( !$revid ) return;
		$visible_id = $revid;
       
		// Set new body html text as that of now
		$flaghtml = ''; $notes = ''; $newbodytext = $out->mBodytext;
		// Check the newest stable version
		$top_frev = $this->getLatestFlaggedRev( $wgArticle->getId() );
		if( $wgRequest->getVal('diff') ) {
		// Do not clutter up diffs any further...
		} else if( $top_frev ) {
			global $wgParser, $wgLang;
			
			$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $top_frev->fr_timestamp), true );
			// Grab the flags
			$flags = $this->getFlagsForRevision( $top_frev->fr_rev_id );
			# Looking at some specific old rev or if flagged revs override only for anons
			if( $wgRequest->getVal('oldid') || !$this->pageOverride() ) {
				if( $revid==$top_frev->rev_id ) {
					$flaghtml = wfMsgExt('revreview-isnewest', array('parse'),$time);
					$notes = $this->ReviewNotes( $top_frev );
				} else {
					# Our compare link should have a reasonable time-ordered old->new combination
					$oldid = ($revid > $top_frev->fr_rev_id) ? $top_frev->fr_rev_id : $revid;
					$diff = ($revid > $top_frev->fr_rev_id) ? $revid : $top_frev->fr_rev_id;
					$flaghtml = wfMsgExt('revreview-newest', array('parse'), $top_frev->fr_rev_id, $oldid, $diff, $time );
				}
			} # Viewing the page normally: override the page
			else {
				global $wgUser;
       			# We will be looking at the reviewed revision...
       			$visible_id = $top_frev->fr_rev_id;
       			$revs_since = $this->getUnreviewedRevCount( $wgArticle->getId(), $visible_id );
       			$flaghtml = wfMsgExt('revreview-replaced', array('parse'), $visible_id, $wgArticle->getLatest(), $revs_since, $time );		
				$newbodytext = NULL;
				# Try the stable cache
				$newbodytext = $this->getPageCache( $wgArticle );
				# If no cache is available, get the text and parse it
				if ( is_null($newbodytext) ) {
					$text = $this->getFlaggedRevText( $visible_id );
					$options = ParserOptions::newFromUser($wgUser);
					# Parsing this text is kind of funky...
       				$newbodytext = $this->parseStableText( $wgTitle, $text, $visible_id, $options );
       				# Update the general cache for non-users
       				$this->updatePageCache( $wgArticle, $newbodytext );
       			}
				$notes = $this->ReviewNotes( $top_frev );
			}
			// Construct some tagging
			$flaghtml .= "<table align='center' cellspadding=\'0\'><tr>";
			foreach ( $this->dimensions as $quality => $value ) {
				$value = wfMsgHtml('revreview-' . $this->dimensions[$quality][$flags[$quality]]);
				$flaghtml .= "<td>&nbsp;<strong>" . wfMsgHtml("revreview-$quality") . "</strong>: $value&nbsp;</td>\n";    
			}
			$flaghtml .= '</tr></table>';
			// Should use CSS?
			$flaghtml = "<small>$flaghtml</small>";
           
			// Set the new body HTML, place a tag on top
			$out->mBodytext = '<div class="mw-warning plainlinks">' . $flaghtml . '</div>' . $newbodytext . $notes;
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
		$top_frev = $this->getLatestFlaggedRev( $editform->mArticle->getId() );
		if( is_object($top_frev) ) {
			global $wgParser, $wgLang;		
			$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $top_frev->fr_timestamp), true );
			$flags = $this->getFlagsForRevision( $top_frev->fr_rev_id );
			# Looking at some specific old rev
			if( $wgRequest->getVal('oldid') ) {
				if( $revid==$top_frev->rev_id ) {
					$flaghtml = wfMsgExt('revreview-isnewest', array('parse'),$time);
				} else {
					# Our compare link should have a reasonable time-ordered old->new combination
					$oldid = ($revid > $top_frev->fr_rev_id) ? $top_frev->fr_rev_id : $revid;
					$diff = ($revid > $top_frev->fr_rev_id) ? $revid : $top_frev->fr_rev_id;
					$flaghtml = wfMsgExt('revreview-newest', array('parse'), $top_frev->fr_rev_id, $oldid, $diff, $time );
				}
           } # Editing the page normally   
       	else {
				if( $revid==$top_frev->rev_id )
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

    function addToDiff( &$diff, &$oldrev, &$newrev ) {
       $id = $newrev->getId();
       // We cannot review deleted edits
       if( $newrev->mDeleted ) return;
       $this->addQuickReview( $id, true );
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
       	$top_frev = $this->getLatestFlaggedRev( $wgArticle->getId() );
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
							'href' => $sktmp->mTitle->getLocalUrl( 'oldid=' . $wgArticle->getLatest() )
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
    
    function addToPageHist( &$article ) {
    	$this->pageFlaggedRevs = array();
    	$rows = $this->getReviewedRevs( $article->getID() );
    	if( !$rows ) return;
    	foreach( $rows as $row => $data ) {
    		$this->pageFlaggedRevs[] = $data->rev_id;
    	}
    }
    
    function addToHistLine( &$row, &$s ) {
    	if( isset($this->pageFlaggedRevs) ) {
    		if( in_array( $row->rev_id, $this->pageFlaggedRevs ) )
    			$s .= ' <small><strong>' . wfMsgHtml('revreview-hist') . '</strong></small>';
    	}
    }
       
    function addQuickReview( $id, $ontop=false, &$out=false ) {
		global $wgOut, $wgTitle, $wgUser, $wgScript;
		// We don't want two forms!
		if( isset($this->formCount) && $this->formCount > 0 ) return;
		$this->formCount = 1;
		
		if( !$wgUser->isAllowed( 'review' ) ) return; 

		$flags = $this->getFlagsForRevision( $id );
       
		$reviewtitle = SpecialPage::getTitleFor( 'Revisionreview' );
		$form = Xml::openElement( 'form', array( 'method' => 'get', 'action' => $wgScript ) );
		$form .= "<fieldset><legend>" . wfMsgHtml( 'revreview-flag', $id ) . "</legend>\n";
		$form .= wfHidden( 'title', $reviewtitle->getPrefixedText() );
		$form .= wfHidden( 'target', $wgTitle->getPrefixedText() );
		$form .= wfHidden( 'oldid', $id );
		foreach ( $this->dimensions as $quality => $levels ) {
			$form .= wfMsgHtml("revreview-$quality") . ": <select name='wp$quality'>\n";
			foreach ( $levels as $idx => $label ) {
				if( $flags[$quality]==$idx )
					$selected = 'selected';
				else
					$selected = '';
				$form .= "<option value='$idx' $selected>" . wfMsgHtml("revreview-$label") . "</option>\n";    
			}
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
	* $output array, list of string names of images sucessfully cloned
	*/
    function makeStableImages( $imagelist ) {
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
    				// Check if our stable dir exists
    				// Make it if it doesn't
    				if( !is_dir($stableDir . $hash) ) {
    					wfMkdirParents($stableDir . $hash);
    				}
    				copy("{$path}{$name}","{$stableDir}{$hash}{$name}");
    				$usedimages[] = $name;
    			}
    		} // Try shared repository
			else if( is_dir($sharedpath) ) {
    			if( file_exists("{$sharedpath}{$name}") ) {
    				// Check if our stable dir exists
    				// Make it if it doesn't
    				if( !is_dir($stableDir . $hash) ) {
    					wfMkdirParents($stableDir . $hash);
    				}
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
    	global $wgSharedUploadDirectory;
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
		
       $db = wfGetDB( DB_MASTER );
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
			$db->replace( 'flaggedimages', array( array('fi_rev_id', 'fi_name') ), $set, __METHOD__ );	
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
		$db = wfGetDB( DB_MASTER );
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
			$result = $db->select( 'flaggedimages', array('fi_id'), array( 'fi_name' => $imagename ) );
			// If only one, then delete the image
			// since it's about to be remove from that one
			if( $db->numRows($result)==1 ) {
				$unusedimages[] = $imagename;
			}
			// Clear out this revision's entry
			$db->delete( 'flaggedimages', $where );
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
    	
    	$db = wfGetDB( DB_SLAVE );
    	$cutoff = $db->timestamp( time() - $wgFlaggedRevsExpire );
    	// Replace the page cache if it is out of date
    	$result = $db->select(
			array('flaggedcache'),
			array('fc_cache'),
			array('fc_key' => $cachekey, 'fc_timestamp >= ' . $article->getTouched(), 'fc_timestamp >= ' . $cutoff ),
			__METHOD__);
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
			__METHOD__);
		
		return true;
    }
}

# Load expert promotion UI
include_once('SpecialMakevalidate.php');

if( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}
extAddSpecialPage( dirname(__FILE__) . '/FlaggedRevsPage.body.php', 'Revisionreview', 'Revisionreview' );

# Load approve/unapprove UI
$wgHooks['LoadAllMessages'][] = 'efLoadReviewMessages';

$flaggedrevs = new FlaggedRevs();
$wgHooks['BeforePageDisplay'][] = array($flaggedrevs, 'setPageContent');
$wgHooks['DiffViewHeader'][] = array($flaggedrevs, 'addToDiff');
$wgHooks['EditPage::showEditForm:initial'][] = array($flaggedrevs, 'addToEditView');
$wgHooks['SkinTemplateTabs'][] = array($flaggedrevs, 'setCurrentTab');
$wgHooks['PageHistoryBeforeList'][] = array($flaggedrevs, 'addToPageHist');
$wgHooks['PageHistoryLineEnding'][] = array($flaggedrevs, 'addToHistLine');
?>
