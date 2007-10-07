<?php
#(c) Joerg Baach, Aaron Schulz 2007 GPL

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

if( !defined( 'FLAGGED_CSS' ) ) 
	define('FLAGGED_CSS', $wgScriptPath.'/extensions/FlaggedRevs/flaggedrevs.css' );
if( !defined( 'FLAGGED_JS' ) )
	define('FLAGGED_JS', $wgScriptPath.'/extensions/FlaggedRevs/flaggedrevs.js' );
if( !defined( 'FLAGGED_VIS_NORMAL' ) )
	define('FLAGGED_VIS_NORMAL',0);
if( !defined( 'FLAGGED_VIS_LATEST' ) )
	define('FLAGGED_VIS_LATEST',1);
	
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

$wgExtensionFunctions[] = 'efLoadFlaggedRevs';

# Load general UI
$wgAutoloadClasses['FlaggedArticle'] = dirname( __FILE__ ) . '/FlaggedArticle.php';
# Load promotion UI
include_once( dirname( __FILE__ ) . '/SpecialMakevalidate.php' );
# Load review UI
$wgSpecialPages['Revisionreview'] = 'Revisionreview';
$wgAutoloadClasses['Revisionreview'] = dirname(__FILE__) . '/FlaggedRevsPage_body.php';
# Load stableversions UI
$wgSpecialPages['Stableversions'] = 'Stableversions';
$wgAutoloadClasses['Stableversions'] = dirname(__FILE__) . '/FlaggedRevsPage_body.php';
# Load unreviewed pages list
$wgSpecialPages['Unreviewedpages'] = 'Unreviewedpages';
$wgAutoloadClasses['Unreviewedpages'] = dirname(__FILE__) . '/FlaggedRevsPage_body.php';
# Stable version config
$wgSpecialPages['Stabilization'] = 'Stabilization';
$wgAutoloadClasses['Stabilization'] = dirname(__FILE__) . '/FlaggedRevsPage_body.php';


function efLoadFlaggedRevs() {
	global $wgMessageCache, $RevisionreviewMessages, $wgOut, $wgJsMimeType, $wgHooks, 
		$wgFlaggedRevs, $wgFlaggedArticle;
	# Out global page modifier objects
	$wgFlaggedRevs = new FlaggedRevs();
	$wgFlaggedArticle = new FlaggedArticle();
	
	# Internationalization
	require_once( dirname( __FILE__ ) . '/FlaggedRevsPage.i18n.php' );
	foreach( $RevisionreviewMessages as $lang => $langMessages ) {
		$wgMessageCache->addMessages( $langMessages, $lang );
	}
	
	// @fixme this is totally in the wrong place
	# UI CSS
	$wgOut->addLink( array(
		'rel'	=> 'stylesheet',
		'type'	=> 'text/css',
		'media'	=> 'screen, projection',
		'href'	=> FLAGGED_CSS,
	) );
	# UI JS
	$wgOut->addScript( "<script type=\"{$wgJsMimeType}\" src=\"" . FLAGGED_JS . "\"></script>\n" );
	
	global $wgGroupPermissions, $wgUseRCPatrol;
	# Use RC Patrolling to check for vandalism
	# When revisions are flagged, they count as patrolled
	$wgUseRCPatrol = true;
	# Use only our extension mechanisms
	$wgGroupPermissions['sysop']['autopatrol'] = false;
	$wgGroupPermissions['sysop']['patrol']     = false;
	# Visiblity settings
	$wgGroupPermissions['sysop']['stablesettings'] = true;

	######### Hook attachments #########
	# Main hooks, overrides pages content, adds tags, sets tabs and permalink
	$wgHooks['SkinTemplateTabs'][] = array( $wgFlaggedArticle, 'setActionTabs' );
	# Update older, incomplete, page caches (ones that lack template Ids/image timestamps)
	$wgHooks['ArticleViewHeader'][] = array( $wgFlaggedArticle, 'maybeUpdateMainCache' );
	$wgHooks['ArticleViewHeader'][] = array( $wgFlaggedArticle, 'setPageContent' );
	$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink'][] = array( $wgFlaggedArticle, 'setPermaLink' );
	# Add tags do edit view
	$wgHooks['EditPage::showEditForm:initial'][] = array( $wgFlaggedArticle, 'addToEditView' );
	# Add review form
	$wgHooks['BeforePageDisplay'][] = array( $wgFlaggedArticle, 'addReviewForm' );
	$wgHooks['BeforePageDisplay'][] = array( $wgFlaggedArticle, 'addVisibilityLink' );
	# Mark of items in page history
	$wgHooks['PageHistoryLineEnding'][] = array( $wgFlaggedArticle, 'addToHistLine' );
	# Autopromote Editors
	$wgHooks['ArticleSaveComplete'][] = array( $wgFlaggedRevs, 'autoPromoteUser' );
	# Adds table link references to include ones from the stable version
	$wgHooks['LinksUpdateConstructed'][] = array( $wgFlaggedRevs, 'extraLinksUpdate' );
	# Empty flagged page settings row on delete
	$wgHooks['ArticleDeleteComplete'][] = array( $wgFlaggedArticle, 'deleteVisiblitySettings' );
	# Check on undelete/merge/revisiondelete for changes to stable version
	$wgHooks['ArticleUndelete'][] = array( $wgFlaggedArticle, 'articleLinksUpdate2' );
	$wgHooks['ArticleRevisionVisiblitySet'][] = array( $wgFlaggedArticle, 'articleLinksUpdate2' );
	$wgHooks['ArticleMergeComplete'][] = array( $wgFlaggedArticle, 'articleLinksUpdate' );
	# Update our table NS/Titles when things are moved
	$wgHooks['SpecialMovepageAfterMove'][] = array( $wgFlaggedArticle, 'updateFromMove' );
	# Parser hooks, selects the desired images/templates
	$wgHooks['BeforeParserrenderImageGallery'][] = array( $wgFlaggedRevs, 'parserMakeGalleryStable' );
	$wgHooks['BeforeGalleryFindFile'][] = array( $wgFlaggedArticle, 'galleryFindStableFileTime' );
	$wgHooks['BeforeParserFetchTemplateAndtitle'][] = array( $wgFlaggedRevs, 'parserFetchStableTemplate' );
	$wgHooks['BeforeParserMakeImageLinkObj'][] = array( $wgFlaggedRevs, 'parserMakeStableImageLink' );
	# Additional parser versioning
	$wgHooks['ParserAfterTidy'][] = array( $wgFlaggedRevs, 'parserInjectImageTimestamps' );
	$wgHooks['OutputPageParserOutput'][] = array( $wgFlaggedRevs, 'outputInjectImageTimestamps');
	# Page review on edit
	$wgHooks['ArticleUpdateBeforeRedirect'][] = array($wgFlaggedRevs, 'injectReviewDiffURLParams');
	$wgHooks['DiffViewHeader'][] = array($wgFlaggedArticle, 'addDiffNoticeAfterEdit' );
	$wgHooks['DiffViewHeader'][] = array($wgFlaggedRevs, 'addPatrolLink' );
    # Autoreview stuff
    $wgHooks['ArticleInsertComplete'][] = array( $wgFlaggedArticle, 'maybeMakeNewPageReviewed' );
	$wgHooks['ArticleSaveComplete'][] = array( $wgFlaggedArticle, 'maybeMakeEditReviewed' );
	$wgHooks['ArticleSaveComplete'][] = array( $wgFlaggedArticle, 'autoMarkPatrolled' );
	# Disallow moves of stable pages
	$wgHooks['userCan'][] = array( $wgFlaggedRevs, 'userCanMove' );
	#########
}

#########
# IMPORTANT:
# When configuring globals, add them to localsettings.php and edit them THERE

# This will only distinguish "sigted", "quality", and unreviewed
# A small icon will show in the upper right hand corner
$wgSimpleFlaggedRevsUI = false;
# Add stable/draft revision tabs. May be redundant due to the tags.
# If you have an open wiki, with the simple UI, you may want to enable these.
$wgFlaggedRevTabs = false;

# Allowed namespaces of reviewable pages
$wgFlaggedRevsNamespaces = array( NS_MAIN );

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
# Automatically checks the 'watch' box on the review form if they set 
# "watch pages I edit" as true at [[Special:Preferences]].
$wgFlaggedRevsWatch = true;
# Redirect users out to review changes since stable version on save?
$wgReviewChangesAfterEdit = true;
# Auto-review edits directly to the stable version by reviewers?
# Depending on how often templates are edited and by whom, this can possibly
# allow for vandalism to slip in :/
# Users should preview changes perhaps. This doesn't help much for section
# editing, so they may also want to review the page afterwards.
$wgFlaggedRevsAutoReview = true;
# Auto-review new pages with the minimal level?
$wgFlaggedRevsAutoReviewNew = false;

# How long to cache stable versions? (seconds)
$wgFlaggedRevsExpire = 7 * 24 * 3600;
# Compress pre-processed flagged revision text?
$wgFlaggedRevsCompression = false;

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
	'accuracy' => array( 'review' => 1 ),
	'depth'    => array( 'review' => 2 ),
	'style'    => array( 'review' => 3 ),
);

# Lets some users access the review UI and set some flags
$wgAvailableRights[] = 'review';
# Let some users set higher settings
$wgAvailableRights[] = 'validate';

# Define our basic reviewer class
$wgGroupPermissions['editor']['review']         = true;
$wgGroupPermissions['editor']['unwatchedpages'] = true;
$wgGroupPermissions['editor']['autoconfirmed']  = true;
$wgGroupPermissions['editor']['patrolmarks']    = true;
$wgGroupPermissions['editor']['patrolother']    = true;

# Defines extra rights for advanced reviewer class
$wgGroupPermissions['reviewer']['validate'] = true;
# Let this stand alone just in case...
$wgGroupPermissions['reviewer']['review']   = true;

# Define when users get automatically promoted to editors. Set as false to disable.
$wgFlaggedRevsAutopromote = array(
	'days'       => 60,
	'edits'      => 200,
	'spacing'    => 5,
	'benchmarks' => 5, // keep this small
	'email'      => true,
	'userpage'   => true
);

# Variables below this point should probably not be modified
#########

# Add review log and such
$wgLogTypes[] = 'review';
$wgLogNames['review'] = 'review-logpage';
$wgLogHeaders['review'] = 'review-logpagetext';
$wgLogActions['review/approve']  = 'review-logentry-app';
$wgLogActions['review/unapprove'] = 'review-logentry-dis';

$wgLogTypes[] = 'stable';
$wgLogNames['stable'] = 'stable-logpage';
$wgLogHeaders['stable'] = 'stable-logpagetext';
$wgLogActions['stable/config'] = 'stable-logentry';
$wgLogActions['stable/reset'] = 'stable-logentry2';

$wgLogActions['rights/erevoke']  = 'rights-editor-revoke';
$wgLogActions['rights/egrant']  = 'rights-editor-grant';

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
		$this->isDiffFromStable = false;
		$this->skipReviewDiff = false;
	}
    
	/**
	 * Should this be using a simple icon-based UI?
	 */	
	static function useSimpleUI() {
		global $wgSimpleFlaggedRevsUI;
		
		return $wgSimpleFlaggedRevsUI;
	}
	
	/**
	 * Should comments be allowed on pages and forms?
	 */	
	static function allowComments() {
		global $wgFlaggedRevComments;
		
		return $wgFlaggedRevComments;
	}
    
    /**
     * @param string $text
     * @param Title $title
     * @param integer $id, revision id
     * @return array( string, bool )
     * All included pages/arguments are expanded out
     */
    public function expandText( $text='', $title, $id=null ) {
    	global $wgParser, $wgUser;
    	# Make our hooks to trigger
    	$wgParser->isStable = true;
    	$wgParser->includesMatched = true;
        # Parse with default options
        $options = new ParserOptions($wgUser);
        $options->setRemoveComments( true ); // Save some bandwidth ;)
        $outputText = $wgParser->preprocess( $text, $title, $options, $id );
        $expandedText = array( $outputText, $wgParser->includesMatched );
        # Done!
        $wgParser->isStable = false;
        $wgParser->includesMatched = false;
        
        return $expandedText;
    }
    
	/**
	 * @param Article $article
	 * @param string $text
	 * @param int $id
	 * @return ParserOutput
	 * Get the HTML of a revision based on how it was during $timeframe
	 */
    public function parseStableText( $article, $text, $id=NULL ) {
    	global $wgParser, $wgUser;
    	# Default options for anons if not logged in
    	$options = ParserOptions::newFromUser($wgUser);
    	# Make our hooks to trigger
    	$wgParser->isStable = true;
		# Don't show section-edit links, they can be old and misleading
		$options->setEditSection(false);
		// $options->setEditSection( $id==$article->getLatest() );
		# Parse the new body, wikitext -> html
		$title = $article->getTitle(); // avoid pass-by-reference error
       	$parserOut = $wgParser->parse( $text, $title, $options, true, true, $id );
       	# Reset $wgParser
       	$wgParser->isStable = false; // Done!
       	
       	return $parserOut;
    }
    
    /**
    * @param string $text
    * @return string, flags
    * Compress pre-processed text, passed by reference
    */
    public function compressText( &$text ) {
    	global $wgFlaggedRevsCompression;
    	# Compress text if $wgFlaggedRevsCompression is set.
		$flags = array( 'utf-8' );
		if( $wgFlaggedRevsCompression ) {
			if( function_exists( 'gzdeflate' ) ) {
				$text = gzdeflate( $text );
				$flags[] = 'gzip';
			} else {
				wfDebug( "FlaggedRevs::compressText() -- no zlib support, not compressing\n" );
			}
		}
    	return implode( ',', $flags );
    }
    
    /**
    * @param string $text
    * @param mixed $flags, either in string or array form
    * @return string
    * Uncompress pre-processed text, using flags
    */
    public function uncompressText( $text, $flags ) {
    	global $wgFlaggedRevsCompression;
    	
    	if( !is_array($flags) ) {
    		$flags = explode( ',', $flags );
    	}
    	# Lets not mix up types here
    	if( is_null($text) )
    		return null;
    	
		if( in_array( 'gzip', $flags ) ) {
			# Deal with optional compression if $wgFlaggedRevsCompression is set.
			$text = gzinflate( $text );
		}
		return $text;
    }
    
	/**
	 * @param Title $title
	 * @param int $rev_id
	 * @param bool $getText, fetch fr_text and fr_flags too?
	 * @return Revision
	 * Will not return if deleted
	 */	
	public function getFlaggedRev( $title, $rev_id, $getText=false ) {
    	$selectColumns = array('fr_rev_id','fr_user','fr_timestamp','fr_comment','rev_timestamp');
    	if( $getText ) {
    		$selectColumns[] = 'fr_text';
    		$selectColumns[] = 'fr_flags';
    	}
	
		$dbr = wfGetDB( DB_SLAVE );
		# Skip deleted revisions
		$result = $dbr->select( array('flaggedrevs','revision'),
			$selectColumns,
			array( 'fr_namespace' => $title->getNamespace(), 
				'fr_title' => $title->getDBKey(),
				'fr_rev_id' => $rev_id, 'fr_rev_id = rev_id', 
				'rev_deleted & '.Revision::DELETED_TEXT.' = 0' ),
			__METHOD__ );
		# Sorted from highest to lowest, so just take the first one if any
		if( $row = $dbr->fetchObject($result) ) {
			return $row;
		}
		
		return NULL;
	}

	/**
	 * @param int $page_id
	 * @param int $from_rev
	 * @return int
	 * Get number of revs since a certain revision
	 */
    public function getRevCountSince( $page_id, $from_rev ) {   
		$dbr = wfGetDB( DB_SLAVE );
		$count = $dbr->selectField('revision', 'COUNT(*)',
			array('rev_page' => $page_id, "rev_id > " . intval( $from_rev ) ),
			__METHOD__ );
		
		return $count;
    }
	
	/**
	 * Get latest quality rev, if not, the latest reviewed one.
	 * @param Title $title, page title
	 * @param bool $getText, fetch fr_text and fr_flags too?
	 * @param bool $forUpdate, use master DB and avoid using page_ext_stable?
	 * @return Row
	*/
    public function getStableRev( $title, $getText=false, $forUpdate=false ) {
		return $this->getStablePageRev( $title, $getText, $forUpdate );
    }
    
	/**
	 * Get latest quality rev, if not, the latest reviewed one.
	 * @param Title $title, page title
	 * @param bool $getText, fetch fr_text and fr_flags too?
	 * @param bool $forUpdate, use master DB and avoid using page_ext_stable?
	 * @param bool $def, is this for the default version of a page?
	 * @returns Row
	*/
    public function getStablePageRev( $title, $getText=false, $forUpdate=false ) {
    	$selectColumns = array( 'fr_rev_id','fr_user','fr_timestamp','fr_comment',
			'rev_timestamp','fr_quality' );
    	if( $getText ) {
    		$selectColumns[] = 'fr_text';
    		$selectColumns[] = 'fr_flags';
    	}
    	$row = null;
    	# If we want the text, then get the text flags too
    	if( !$forUpdate ) {
    		$dbr = wfGetDB( DB_SLAVE );
        	$result = $dbr->select( array('page', 'flaggedrevs', 'revision'),
				$selectColumns,
				array('page_namespace' => $title->getNamespace(),
					'page_title' => $title->getDBkey(),
					'page_ext_stable = fr_rev_id',
					'fr_rev_id = rev_id',
				 	'rev_deleted & '.Revision::DELETED_TEXT.' = 0'),
				__METHOD__,
				array('LIMIT' => 1) );
			if( !$row = $dbr->fetchObject($result) )
				return null;
		} else {
    		// Get visiblity settings...
			$config = $this->getVisibilitySettings( $title, $forUpdate );
			$dbw = wfGetDB( DB_MASTER );
			// Look for the latest quality revision
			if( $config['select'] !== FLAGGED_VIS_LATEST ) {
        		$result = $dbw->select( array('flaggedrevs', 'revision'),
					$selectColumns,
					array('fr_namespace' => $title->getNamespace(), 
						'fr_title' => $title->getDBkey(), 
						'fr_quality >= 1', 
						'fr_rev_id = rev_id', 
						'rev_deleted & '.Revision::DELETED_TEXT.' = 0'),
					__METHOD__,
					array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1) );
				$row = $dbw->fetchObject($result);
			}
			// Do we have one? If not, try the latest reviewed revision...
        	if( !$row ) {
        		$result = $dbw->select( array('flaggedrevs', 'revision'),
					$selectColumns,
					array('fr_namespace' => $title->getNamespace(), 
						'fr_title' => $title->getDBkey(),
						'fr_rev_id = rev_id', 
						'rev_deleted & '.Revision::DELETED_TEXT.' = 0'),
					__METHOD__,
					array('ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1) );
				if( !$row = $dbw->fetchObject($result) )
					return null;
			}
		}
		return $row;
    }
    
    /**
	 * Get visiblity restrictions on page
	 * @param Title $title, page title
	 * @param bool $forUpdate, use master DB?
	 * @returns Array
	*/
    public function getVisibilitySettings( $title, $forUpdate=false ) {
		return $this->getPageVisibilitySettings( $title, $forUpdate );
	}
	
    /**
	 * Get visiblity restrictions on page
	 * @param Title $title, page title
	 * @param bool $forUpdate, use master DB?
	 * @returns Array
	*/
    public function getPageVisibilitySettings( $title, $forUpdate=false ) {
    	$db = $forUpdate ? wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		$row = $db->selectRow( 'flaggedpages', 
			array( 'fp_select', 'fp_override' ),
			array( 'fp_page_id' => $title->getArticleID() ),
			__METHOD__ );
		if( !$row ) {
			global $wgFlaggedRevsOverride;
			// Keep this consistent across settings. 1 -> override, 0 -> don't
			$override = $wgFlaggedRevsOverride ? 1 : 0;
			return array('select' => 0, 'override' => $override);
		}
		return array('select' => $row->fp_select, 'override' => $row->fp_override);
	}
    
	/**
	 * Get flags for a revision
	 * @param int $rev_id
	 * @return Array
	*/
    public function getFlagsForRevision( $rev_id ) {
		return $this->getRevisionTags( $rev_id );
	}
    
	/**
	 * Get flags for a revision
	 * @param int $rev_id
	 * @return Array
	*/
    public function getRevisionTags( $rev_id ) {
    	# Set all flags to zero
    	$flags = array();
    	foreach( array_keys($this->dimensions) as $tag ) {
    		$flags[$tag] = 0;
    	}
    	# Grab all the tags for this revision
		$db = wfGetDB( DB_SLAVE );
		$result = $db->select('flaggedrevtags',
			array('frt_dimension', 'frt_value'), 
			array('frt_rev_id' => $rev_id),
			__METHOD__ );
		# Iterate through each tag result
		while( $row = $db->fetchObject($result) ) {
			# Add only currently recognized ones
			if( isset($flags[$row->frt_dimension]) )
				$flags[$row->frt_dimension] = $row->frt_value;
		}
		return $flags;
	}
	
	/**
	 * @param int $title
	 * @return bool
	 * Is $title the main page?
	 */	
	public function isMainPage( $title ) {
		$mp = Title::newMainPage();
		return ( $title->getNamespace()==$mp->getNamespace() && $title->getDBKey()==$mp->getDBKey() );
	}
    
	/**
	* @param Array $flags
	* @return bool, is this revision at quality condition?
	*/
    public function isQuality( $flags ) {
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
    public function isPristine( $flags ) {
    	global $wgFlaggedRevTags, $wgFlaggedRevValues;
    	
    	if( empty($flags) )
    		return false;
    	
    	foreach( $wgFlaggedRevTags as $f => $v ) {
    		if( !isset($flags[$f]) || $flags[$f] < $wgFlaggedRevValues ) 
				return false;
    	}
    	return true;
    }

	/**
	* Is this page in reviewable namespace?
	* @param Title, $title
	* @return bool
	*/
    public function isReviewable( $title ) {
    	global $wgFlaggedRevsNamespaces;
    	
    	return ( in_array($title->getNamespace(),$wgFlaggedRevsNamespaces) 
			&& !$title->isTalkPage() );
    }
    
	/**
	* @param Article $article
	* @return ParserOutput
	* Get the page cache for the top stable revision of an article
	*/   
    public function getPageCache( $article ) {
    	global $wgUser, $parserMemc, $wgCacheEpoch, $wgFlaggedRevsExpire;
    	
		wfProfileIn( __METHOD__ );
		# Make sure it is valid
    	if( !$article || !$article->getId() ) 
			return NULL;
    	
    	$parserCache = ParserCache::singleton();
    	$key = 'sv-' . $parserCache->getKey( $article, $wgUser );
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
	* @param Article $article
	* @param parerOutput $parserOut
	* Updates the stable cache of a page with the given $parserOut
	*/
    public function updatePageCache( $article, $parserOut=NULL ) {
    	global $wgUser, $parserMemc, $wgFlaggedRevsExpire;
    	# Make sure it is valid
    	if( is_null($parserOut) || !$article ) 
			return false;
    	# Update the cache...
		$article->mTitle->invalidateCache();
		
		$parserCache = ParserCache::singleton();
    	$key = 'sv-' . $parserCache->getKey( $article, $wgUser );
    	# Add cache mark to HTML
		$now = wfTimestampNow();
		$parserOut->setCacheTime( $now );
		# Save the timestamp so that we don't have to load the revision row on view
		$parserOut->mTimestamp = $article->getTimestamp();
    	$parserOut->mText .= "\n<!-- Saved in stable version parser cache with key $key and timestamp $now -->";
		# Set expire time
		if( $parserOut->containsOldMagic() ){
			$expire = 3600; # 1 hour
		} else {
			$expire = $wgFlaggedRevsExpire;
		}
		# Save to objectcache
		$parserMemc->set( $key, $parserOut, $expire );
		# Purge squid for this page only
		$article->mTitle->purgeSquid();
		
		return true;
    }
    
	/**
	* Automatically review an edit and add a log entry in the review log.
	* LinksUpdate was already called via edit operations, so the page
	* fields will be up to date. This updates the stable version.
	*/ 
	public function autoReviewEdit( $article, $user, $text, $rev, $flags ) {
		global $wgParser, $wgFlaggedRevsAutoReview;
		
		$quality = 0;
		if( $this->isQuality($flags) ) {
			$quality = $this->isPristine($flags) ? 2 : 1;
		}
		$flagset = $tmpset = $imgset = array();
		foreach( $flags as $tag => $value ) {
			$flagset[] = array(
				'frt_rev_id' => $rev->getId(),
				'frt_dimension' => $tag,
				'frt_value' => $value 
			);
		}
		# Try the parser cache, should be set on the edit before this is called.
		# If not set or up to date, then parse it...
		$parserCache = ParserCache::singleton();
		$poutput = $parserCache->get( $article, $user );
		if( $poutput==false ) {
			$options = ParserOptions::newFromUser($user);
			$options->setTidy(true);
			$poutput = $wgParser->parse( $text, $article->mTitle, $options, true, true, $rev->getID() );
			# Might as well save the cache while we're at it
			$parserCache->save( $poutput, $article, $user );
		}
		# NS:title -> rev ID mapping
        foreach( $poutput->mTemplateIds as $namespace => $title ) {
        	foreach( $title as $dbkey => $id ) {
				$tmpset[] = array(
					'ft_rev_id' => $rev->getId(),
					'ft_namespace' => $namespace,
					'ft_title' => $dbkey,
					'ft_tmp_rev_id' => $id
				);
        	}
        }
        # Image -> timestamp mapping
        foreach( $poutput->mImageSHA1Keys as $dbkey => $timeAndSHA1 ) {
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
        list($fulltext,$complete) = $this->expandText( $rev->getText(), $rev->getTitle(), $rev->getId() );
        if( !$complete ) {
        	$dbw->rollback(); // All versions must be specified, 0 for none
        	return false;
        }
        # Compress $fulltext, passed by reference
        $textFlags = self::compressText( $fulltext );
		# Our review entry
		$revisionset = array(
			'fr_namespace' => $rev->getTitle()->getNamespace(),
			'fr_title'     => $rev->getTitle()->getDBkey(),
			'fr_rev_id'    => $rev->getId(),
			'fr_user'      => $user->getId(),
			'fr_timestamp' => wfTimestampNow(),
			'fr_comment'   => '',
			'fr_quality'   => $quality,
			'fr_text'      => $fulltext, // Store expanded text for speed
			'fr_flags'     => $textFlags
		);
		# Update flagged revisions table
		$dbw->replace( 'flaggedrevs', 
			array( array('fr_rev_id') ), $revisionset, 
			__METHOD__ );
		# Set all of our flags
		$dbw->replace( 'flaggedrevtags', 
			array( array('frt_rev_id','frt_dimension') ), $flagset, 
			__METHOD__ );
		# Mark as patrolled
		$dbw->update( 'recentchanges',
			array( 'rc_patrolled' => 1 ),
			array( 'rc_this_oldid' => $rev->getId() ),
			__METHOD__ 
		);
		$dbw->commit();
		
		# Update the article review log
		Revisionreview::updateLog( $rev->getTitle(), $flags, wfMsg('revreview-auto'), 
			$rev->getID(), true, false );
		
		# Might as well save the stable version cache
		$this->updatePageCache( $article, $poutput );
    	# Update page fields
    	$this->updateArticleOn( $article, $rev->getID() );
		# Purge squid for this page only
		$article->mTitle->purgeSquid();
		
		return true;
	}
	
 	/**
	* @param Article $article
	* @param Integer $rev_id, the stable version rev_id
	* Updates the page_ext_stable and page_ext_reviewed fields
	*/
    function updateArticleOn( $article, $rev_id ) {
        $dbw = wfGetDB( DB_MASTER );
        $dbw->update( 'page',
			array('page_ext_stable' => $rev_id, 
				'page_ext_reviewed' => ($article->getLatest() == $rev_id) ),
			array('page_namespace' => $article->mTitle->getNamespace(), 
				'page_title' => $article->mTitle->getDBkey() ),
			__METHOD__ );	
    }
    
    public function updateFromMove( $movePageForm, $oldtitle, $newtitle ) {
    	$dbw = wfGetDB( DB_MASTER );
        $dbw->update( 'flaggedrevs',
			array('fr_namespace' => $newtitle->getNamespace(), 
				'fr_title' => $newtitle->getDBkey() ),
			array('fr_namespace' => $oldtitle->getNamespace(), 
				'fr_title' => $oldtitle->getDBkey() ),
			__METHOD__ );
			
		return true;
    }
    
    public function deleteVisiblitySettings( $article, $user, $reason ) {
    	$dbw = wfGetDB( DB_MASTER );
    	$dbw->delete( 'flaggedpages',
    		array('fp_page_id' => $article->getID() ),
    		__METHOD__ );
    		
    	return true;
    }

	/**
	* Clears cache for a page when merges are done.
	* We may have lost the stable revision to another page.
	*/
    public function articleLinksUpdate( $article, $a=null, $b=null ) {
    	global $wgUser, $wgParser;
    	# Update the links tables as the stable version may now be the default page...
		$parserCache = ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if( $poutput==false ) {
			$text = $article->getContent();
			$poutput = $wgParser->parse($text, $article->mTitle, ParserOptions::newFromUser($wgUser));
			# Might as well save the cache while we're at it
			$parserCache->save( $poutput, $article, $wgUser );
		}
		$u = new LinksUpdate( $article->mTitle, $poutput );
		$u->doUpdate(); // this will trigger our hook to add stable links too...
		
		return true;
    }
    
	/**
	* Clears cache for a page when revisiondelete/undelete is used
	*/
    public function articleLinksUpdate2( $title, $a=null, $b=null ) {
    	global $wgUser, $wgParser;
    	
    	$article = new Article( $title );
		# Update the links tables as the stable version may now be the default page...
		$parserCache = ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if( $poutput==false ) {
			$text = $article->getContent();
			$poutput = $wgParser->parse($text, $article->mTitle, ParserOptions::newFromUser($wgUser));
			# Might as well save the cache while we're at it
			$parserCache->save( $poutput, $article, $wgUser );
		}
		$u = new LinksUpdate( $article->mTitle, $poutput );
		$u->doUpdate(); // this will trigger our hook to add stable links too...
		
		return true;
    }

	/**
	* Inject stable links on LinksUpdate
	*/
    public function extraLinksUpdate( $linksUpdate ) {
    	wfProfileIn( __METHOD__ );
		
    	if( !$this->isReviewable( $linksUpdate->mTitle ) ) 
			return true;
    	# Check if this page has a stable version
    	$sv = $this->getStableRev( $linksUpdate->mTitle, true, true );
    	if( !$sv )
			return true;
    	# Parse the revision
    	$article = new Article( $linksUpdate->mTitle );
    	$text = self::uncompressText( $sv->fr_text, $sv->fr_flags );
    	$parserOut = self::parseStableText( $article, $text, $sv->fr_rev_id );
    	# Might as well update the stable cache while we're at it
    	$this->updatePageCache( $article, $parserOut );
    	# Update page fields
    	$this->updateArticleOn( $article, $sv->fr_rev_id );
    	# Update the links tables to include these
    	# We want the UNION of links between the current
		# and stable version. Therefore, we only care about
		# links that are in the stable version and not the regular one.
		$linksUpdate->mLinks += $parserOut->getLinks();
		$linksUpdate->mImages += $parserOut->getImages();
		$linksUpdate->mTemplates += $parserOut->getTemplates();
		$linksUpdate->mExternals += $parserOut->getExternalLinks();
		$linksUpdate->mCategories += $parserOut->getCategories();
		# Interlanguage links
		$ill = $parserOut->getLanguageLinks();
		foreach( $ill as $link ) {
			list( $key, $title ) = explode( ':', $link, 2 );
			$linksUpdate->mInterlangs[$key] = $title;
		}

		wfProfileOut( __METHOD__ );
		return true;
    }
 
	/**
	* Select the desired templates based on the selected stable revision IDs
	*/
	function parserFetchStableTemplate( $parser, $title, &$skip, &$id ) {
    	# Trigger for stable version parsing only
    	if( !isset($parser->isStable) || !$parser->isStable )
    		return true;
    	# Only called to make fr_text, right after template/image specifiers 
    	# are added to the DB. Slaves may not have it yet...
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

	/**
	* Select the desired images based on the selected stable revision times/SHA-1s
	*/  
	function parserMakeStableImageLink( $parser, $nt, &$skip, &$time ) {
    	# Trigger for stable version parsing only
    	if( !isset($parser->isStable) || !$parser->isStable )
    		return true;
    	# Only called to make fr_text, right after template/image specifiers 
    	# are added to the DB. Slaves may not have it yet...
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

	/**
	* Select the desired images based on the selected stable revision times/SHA-1s
	*/  
    function galleryFindStableFileTime( $ig, $nt, &$time ) {
    	# Trigger for stable version parsing only
    	if( !isset($ig->isStable) || !$ig->isStable )
    		return true;
    	# Slaves may not have it yet...
    	$dbr = wfGetDB( DB_MASTER );
        $time = $dbr->selectField('flaggedimages', 'fi_img_timestamp',
			array('fi_rev_id' => $ig->mRevisionId, 'fi_name' => $nt->getDBkey() ),
			__METHOD__ );
		$time = $time ? $time : -1; // hack, will never find this
		
		return true;
    }

	/**
	* Flag of an image galley as stable
	*/  
    function parserMakeGalleryStable( $parser, $ig ) {
    	# Trigger for stable version parsing only
    	if( !isset($parser->isStable) || !$parser->isStable )
    		return true;
    	
    	$ig->isStable = true;
    	
    	return true;
    }

	/**
	* Insert image timestamps/SHA-1 keys into parser output
	*/  
    function parserInjectImageTimestamps( $parser, &$text ) {
		$parser->mOutput->mImageSHA1Keys = array();
		# Fetch the timestamps of the images
		if( !empty($parser->mOutput->mImages) ) {
			$dbr = wfGetDB( DB_SLAVE );
        	$res = $dbr->select('image', array('img_name','img_timestamp','img_sha1'),
				array('img_name IN(' . $dbr->makeList( array_keys($parser->mOutput->mImages) ) . ')'),
				__METHOD__ );
			
			while( $row = $dbr->fetchObject($res) ) {
				$parser->mOutput->mImageSHA1Keys[$row->img_name] = array();
				$parser->mOutput->mImageSHA1Keys[$row->img_name][$row->img_timestamp] = $row->img_sha1;
			}
		}
		return true;
    }

	/**
	* Insert image timestamps/SHA-1s into page output
	*/  
    function outputInjectImageTimestamps( $out, $parserOut ) {
    	$out->mImageSHA1Keys = $parserOut->mImageSHA1Keys;
    	
    	return true;
    }

	/**
	* Redirect users out to review the changes to the stable version.
	* Only for people who can review and for pages that have a stable version.
	*/ 
    public function injectReviewDiffURLParams( $article, &$sectionanchor, &$extraq ) {
    	global $wgUser, $wgReviewChangesAfterEdit;
		# Was this already autoreviewed?
		if( $this->skipReviewDiff )
			return true;
		
    	if( !$wgReviewChangesAfterEdit || !$wgUser->isAllowed( 'review' ) )
    		return true;
    	
		$frev = $this->getStableRev( $article->getTitle() );
		if( $frev )	{
			$frev_id = $frev->fr_rev_id;
			$extraq .= "oldid={$frev_id}&diff=cur&editreview=1";
		}
		
		return true;
	
	}

	/**
	* When comparing the stable revision to the current after editing a page, show
	* a tag with some explaination for the diff.
	*/ 
	public function addDiffNoticeAfterEdit( $diff, $OldRev, $NewRev ) {
		global $wgRequest, $wgUser, $wgOut;
		
		if( !$wgUser->isAllowed( 'review') || !$wgRequest->getBool('editreview') || !$NewRev->isCurrent() )
			return true;
		
		$frev = $this->getStableRev( $diff->mTitle );
		if( !$frev || $frev->fr_rev_id != $OldRev->getID() )
			return true;
			
		$changeList = array();
		$skin = $wgUser->getSkin();
		# Make a list of each changed template...
		$dbr = wfGetDB( DB_SLAVE );
		$ret = $dbr->select( array('flaggedtemplates','page'),
			array( 'ft_namespace', 'ft_title', 'ft_tmp_rev_id' ),
			array( 'ft_rev_id' => $frev->fr_rev_id,
				'ft_namespace = page_namespace',
				'ft_title = page_title',
				'ft_tmp_rev_id != page_latest' ),
			__METHOD__ );
			
		while( $row = $dbr->fetchObject( $ret ) ) {
			$title = Title::makeTitle( $row->ft_namespace, $row->ft_title );
			$changeList[] = $skin->makeKnownLinkObj( $title, 
				$title->GetPrefixedText(),
				"diff=cur&oldid=" . $row->ft_tmp_rev_id );
		}
		# And images...
		$ret = $dbr->select( array('flaggedimages','image'),
			array( 'fi_name' ),
			array( 'fi_rev_id' => $frev->fr_rev_id,
				'fi_name = img_name',
				'fi_img_sha1 != img_sha1' ),
			__METHOD__ );
			
		while( $row = $dbr->fetchObject( $ret ) ) {
			$title = Title::makeTitle( NS_IMAGE, $row->fi_name );
			$changeList[] = $skin->makeKnownLinkObj( $title );
		}
		
		if( empty($changeList) ) {
			$wgOut->addHTML( '<div id="mw-difftostable" class="flaggedrevs_notice plainlinks">' .
				wfMsg('revreview-update-none').'</div>' );
		} else {
			$changeList = implode(', ',$changeList);
			$wgOut->addHTML( '<div id="mw-difftostable" class="flaggedrevs_notice plainlinks"><p>' .
				wfMsg('revreview-update').'</p>'.$changeList.'</div>' );
		}
		# Set flag for review form to tell it to autoselect tag settings from the
		# old revision unless the current one is tagged to.
		if( !self::getFlaggedRev( $diff->mTitle, $NewRev->getID() ) ) {
			global $wgFlaggedArticle;
			$wgFlaggedArticle->isDiffFromStable = true;
		}
		
		return true;
	
	}
	
	/**
	* Add a link to patrol non-reviewable pages
	*/ 
	public function addPatrolLink( $diff, $OldRev, $NewRev ) {
		global $wgUser, $wgOut, $wgFlaggedRevs;
		
		if( $wgFlaggedRevs->isReviewable( $NewRev->getTitle() ) )
			return true;
		// Prepare a change patrol link, if applicable
		if( $wgUser->isAllowed( 'patrolother' ) ) {
			// If we've been given an explicit change identifier, use it; saves time
			if( $diff->mRcidMarkPatrolled ) {
				$rcid = $diff->mRcidMarkPatrolled;
			} else {
				// Look for an unpatrolled change corresponding to this diff
				$dbr = wfGetDB( DB_SLAVE );
				$change = RecentChange::newFromConds(
					array(
						// Add redundant timestamp condition so we can use the
						// existing index
						'rc_timestamp' => $dbr->timestamp( $diff->mNewRev->getTimestamp() ),
						'rc_this_oldid' => $diff->mNewid,
						'rc_last_oldid' => $diff->mOldid,
						'rc_patrolled' => 0,
					),
					__METHOD__
				);
				if( $change instanceof RecentChange ) {
					$rcid = $change->mAttribs['rc_id'];
				} else {
					// None found
					$rcid = 0;
				}
			}
			// Build the link
			if( $rcid ) {
				$skin = $wgUser->getSkin();
			
				$reviewtitle = SpecialPage::getTitleFor( 'Revisionreview' );
				$patrol = ' [' . $skin->makeKnownLinkObj( $reviewtitle,
					wfMsgHtml( 'markaspatrolleddiff' ),
					"patrolonly=1&rcid={$rcid}"
				) . ']';
			} else {
				$patrol = '';
			}
			$wgOut->addHTML( '<div align=center>' . $patrol . '</div>' );
		}
		return true;
	}

	/**
	* When an edit is made by a reviwer, if the current revision is the stable
	* version, try to automatically review it.
	*/ 
	public function maybeMakeEditReviewed( $article, $user, $text, $c, $m, $a, $b, $flags, $rev ) {
		global $wgFlaggedRevsAutoReview;
		
		if( !$wgFlaggedRevsAutoReview || !$user->isAllowed( 'review' ) )
			return true;
		# Revision will be null for null edits
		if( !$rev ) {
			$this->skipReviewDiff = true; // Don't jump to diff...
			return true;
		}
		# Check if this new revision is now the current one.
		# ArticleSaveComplete may trigger even though a confict blocked insertion.
		$prev_id = $article->mTitle->getPreviousRevisionID( $rev->getID() );
		if( !$prev_id )
			return true;
		$frev = $this->getStableRev( $article->mTitle );
		# Is this an edit directly to the stable version?
		if( is_null($frev) || $prev_id != $frev->fr_rev_id )
			return true;
		# Grab the flags for this revision
		$flags = $this->getFlagsForRevision( $frev->fr_rev_id );
		# Check if user is allowed to renew the stable version.
		# If it has been reviewed too highly for this user, abort.
		foreach( $flags as $quality => $level ) {
			if( !Revisionreview::userCan($quality,$level) ) {
				return true;
			}
		}
		self::autoReviewEdit( $article, $user, $text, $rev, $flags );
		
		$this->skipReviewDiff = true; // Don't jump to diff...
		
		return true;
	}

	/**
	* Don't let users vandalize pages by moving them.
	*/ 		
	public function userCanMove( $title, $user, $action, $result ) {
		if( $action != 'move' )
			return true;
		# See if there is a stable version
		$frev = $this->getStableRev( $title );
		if( !$frev )
			return true;
		#  Allow for only editors/reviewers to move this
		$right = $frev->fr_quality ? 'validate' : 'review';
		if( !$user->isAllowed( $right ) ) {
		    $result = false;
			return false;
		}
		
		return true;
	}
	
    /**
    * When an edit is made to a page that can't be reviewed, autopatrol if allowed.
    * This is not loggged for perfomance reasons and no one cares if talk pages and such
    * are autopatrolled.
    */     
    public function autoMarkPatrolled( $article, $user, $text, $c, $m, $a, $b, $flags, $rev ) {
        global $wgUser, $wgFlaggedRevs;
        
        if( !$rev )
        	return true;
        
        if( !$wgFlaggedRevs->isReviewable( $article->getTitle() ) && $wgUser->isAllowed('patrolother') ) {
            $dbw = wfGetDB( DB_MASTER );
            $dbw->update( 'recentchanges',
                array( 'rc_patrolled' => 1 ),
                array( 'rc_this_oldid' => $rev->getID() ),
                __METHOD__ );
        }
        return true;
    }

	/**
	* When a new page is made by a reviwer, try to automatically review it.
	*/ 	
	public function maybeMakeNewPageReviewed( $article, $user, $text, $c, $flags, $a, $b, $flags, $rev ) {
		global $wgFlaggedRevsAutoReviewNew;
	
		if( !$wgFlaggedRevsAutoReviewNew || !$user->isAllowed( 'review' ) )
			return true;
		# Revision will be null for null edits
		if( !$rev ) {
			$this->skipReviewDiff = true; // Don't jump to diff...
			return true;
		}
		# Assume basic flagging level
		$flags = array();
    	foreach( array_keys($this->dimensions) as $tag ) {
    		$flags[$tag] = 1;
    	}
		self::autoReviewEdit( $article, $user, $text, $rev, $flags );
		
		$this->skipReviewDiff = true; // Don't jump to diff...
		
		return true;
	}

	/**
	* Callback that autopromotes user according to the setting in 
    * $wgFlaggedRevsAutopromote. This is not as efficient as it should be
	*/
	public function autoPromoteUser( $article, $user, &$text, &$summary, &$isminor, &$iswatch, &$section ) {
		global $wgUser, $wgFlaggedRevsAutopromote;
		
		if( !$wgFlaggedRevsAutopromote )
			return true;
		# Grab current groups
		$groups = $user->getGroups();
		$now = time();
		$usercreation = wfTimestamp(TS_UNIX,$user->mRegistration);
		$userage = floor(($now-$usercreation) / 86400);
		$userpage = $user->getUserPage();
		# Do not give this to current holders or bots
		if( in_array( array('bot','editor'), $groups ) )
			return true;
		# Check if we need to promote...
		if( $userage < $wgFlaggedRevsAutopromote['days'] )
			return true;
		if( $user->getEditCount() < $wgFlaggedRevsAutopromote['edits'] )
			return true;
		if( $wgFlaggedRevsAutopromote['email'] && !$wgUser->isAllowed('emailconfirmed') )
			return true;
		if( $wgFlaggedRevsAutopromote['userpage'] && !$userpage->exists() )
			return true;
		if( $user->isBlocked() )
			return true;
    	# Do not re-add status if it was previously removed...
		$dbw = wfGetDB( DB_MASTER );
		$removed = $dbw->selectField( 'logging',
			'log_timestamp',
			array( 'log_namespace' => NS_USER,
				'log_title' => $wgUser->getUserPage()->getDBkey(),
				'log_type'  => 'rights',
				'log_action'  => 'erevoke' ),
			__METHOD__,
			array('USE INDEX' => 'page_time') ); 
		if( $removed )
			return true;
		
		// Check for edit spacing. This lets us know that the account has
		// been used over N different days, rather than all in one lump.
		// This can be expensive... so check it last.
		if( $wgFlaggedRevsAutopromote['spacing'] > 0 && $wgFlaggedRevsAutopromote['benchmarks'] > 1 ) {
			// Convert days to seconds...
			$spacing = $wgFlaggedRevsAutopromote['spacing'] * 24 * 3600;
			
			// Check the oldest edit
			$dbr = wfGetDB( DB_SLAVE );
			$lower = $dbr->selectField( 'revision', 'rev_timestamp',
				array( 'rev_user' => $user->getID() ),
				__METHOD__,
				array(
					'ORDER BY' => 'rev_timestamp ASC',
					'USE INDEX' => 'user_timestamp' ) );
			
			// Recursively check for an edit $spacing seconds later, until we are done.
			// The first edit counts, so we have one less scans to do...
			$benchmarks = 0;
			$needed = $wgFlaggedRevsAutopromote['benchmarks'] - 1;
			while( $lower && $benchmarks < $needed ) {
				$next = wfTimestamp( TS_UNIX, $lower ) + $spacing;
				$lower = $dbr->selectField( 'revision', 'rev_timestamp',
					array(
						'rev_user' => $user->getID(),
						'rev_timestamp > ' . $dbr->addQuotes( $dbr->timestamp( $next ) ) ),
					__METHOD__,
					array(
						'ORDER BY' => 'rev_timestamp ASC',
						'USE INDEX' => 'user_timestamp' ) );
				if( $lower !== false )
					$benchmarks++;
			}
			if( $benchmarks < $needed )
				return true;
		}
		# Add editor rights
		$newGroups = $groups ;
		array_push( $newGroups, 'editor');
		# Lets NOT spam RC, set $RC to false
		$log = new LogPage( 'rights', false );
		$log->addEntry('rights', $user->getUserPage(), wfMsg('makevalidate-autosum'), 
			array( implode(', ',$groups), implode(', ',$newGroups) ) );
		$user->addGroup('editor');
		
		return true;
    }
    
	/**
	* Updates parser cache output to included needed versioning params.
	*/
    public function maybeUpdateMainCache( $article, &$outputDone, &$pcache ) {
    	global $wgUser, $action;
		# Only trigger on article view for content pages, not for protect/delete/hist
		if( $action !='view' || !$wgUser->isAllowed( 'review' ) ) 
			return true;
		if( !$article || !$article->exists() || !$this->isReviewable( $article->mTitle ) ) 
			return true;
		
		$parserCache = ParserCache::singleton();
    	$parserOut = $parserCache->get( $article, $wgUser );
		if( $parserOut ) {
			# Clear older, incomplete, cached versions
			# We need the IDs of templates and timestamps of images used
			if( !isset($parserOut->mTemplateIds) || !isset($parserOut->mImageSHA1Keys) )
				$article->mTitle->invalidateCache();
		}
		return true;
    }
	
	######### Stub functions, overridden by subclass #########
    
    function pageOverride() { return false; }
    
    function showStableByDefault() { return false; }
    
    function setPageContent( $article, &$outputDone, &$pcache ) {}
    
    function addToEditView( $editform ) {}
    
    function addReviewForm( $out ) {}
    
    function setPermaLink( $sktmp, &$nav_urls, &$revid, &$revid ) {}
    
    function setActionTabs( $sktmp, &$content_actions ) {}
    
    function addToHistLine( $row, &$s ) {}
    
    function addQuickReview( $id=NULL, $out ) {}
    
    function addVisibilityLink( $out ) {}
    
    public function addTagRatings( $flags, $prettyBox = false, $css='' ) {}
    
    public function prettyRatingBox( $tfrev, $flags, $revs_since, $stable=true ) {}
    
    public function ReviewNotes( $row ) {}
	
	#########
    
}
