<?php
#(c) Aaron Schulz, Joerg Baach, 2007 GPL

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

if( !defined('FLAGGED_VIS_NORMAL') )
	define('FLAGGED_VIS_NORMAL',0);
if( !defined('FLAGGED_VIS_LATEST') )
	define('FLAGGED_VIS_LATEST',1);

$wgExtensionCredits['parserhook'][] = array(
	'author' => 'Aaron Schulz',
	'version' => '0.5',
	'name' => 'Flagged Revisions',
	'url' => 'http://www.mediawiki.org/wiki/Extension:FlaggedRevs',
	'description' => 'Allows for revisions of pages to be made static regardless of internal templates and images',
);

$wgExtensionCredits['specialpage'][] = array(
	'author' => 'Aaron Schulz, Joerg Baach',
	'version' => '0.5',
	'name' => 'Review revisions',
	'url' => 'http://www.mediawiki.org/wiki/Extension:FlaggedRevs',
	'description' => 'Gives editors/reviewers the ability to validate revisions and stabilize pages',
);

$wgExtensionFunctions[] = 'efLoadFlaggedRevs';

# Load general UI
$wgAutoloadClasses['FlaggedArticle'] = dirname( __FILE__ ) . '/FlaggedArticle.php';
# Load review UI
$wgSpecialPages['Revisionreview'] = 'Revisionreview';
$wgAutoloadClasses['Revisionreview'] = dirname(__FILE__) . '/FlaggedRevsPage.php';
# Load stableversions UI
$wgSpecialPages['Stableversions'] = 'Stableversions';
$wgAutoloadClasses['Stableversions'] = dirname(__FILE__) . '/FlaggedRevsPage.php';
# Load unreviewed pages list
$wgSpecialPages['Unreviewedpages'] = 'Unreviewedpages';
$wgAutoloadClasses['Unreviewedpages'] = dirname(__FILE__) . '/FlaggedRevsPage.php';
# Load reviewed pages list
$wgSpecialPages['Reviewedpages'] = 'Reviewedpages';
$wgAutoloadClasses['Reviewedpages'] = dirname(__FILE__) . '/FlaggedRevsPage.php';
# Stable version config
$wgSpecialPages['Stabilization'] = 'Stabilization';
$wgAutoloadClasses['Stabilization'] = dirname(__FILE__) . '/FlaggedRevsPage.php';
# Load promotion UI
include_once( dirname( __FILE__ ) . '/SpecialMakeReviewer.php' );

function efLoadFlaggedRevs() {
	global $wgMessageCache, $wgOut, $wgHooks, $wgLang, $wgFlaggedArticle;
	# Initialize
	FlaggedRevs::load();
	$wgFlaggedArticle = new FlaggedArticle();
	# Internationalization
	$messages = array();
	$f = dirname( __FILE__ ) . '/Language/FlaggedRevsPage.i18n.en.php';
	include( $f ); // Default to English langauge
	$wgMessageCache->addMessages( $messages, 'en' );

	$f = dirname( __FILE__ ) . '/Language/FlaggedRevsPage.i18n.' . $wgLang->getCode() . '.php';
	if( file_exists( $f ) ) {
		include( $f );
	}
	$wgMessageCache->addMessages( $messages, $wgLang->getCode() );

	global $wgGroupPermissions, $wgUseRCPatrol;
	# Use RC Patrolling to check for vandalism
	# When revisions are flagged, they count as patrolled
	$wgUseRCPatrol = true;
	# Use only our extension mechanisms
	foreach( $wgGroupPermissions as $group => $rights ) {
		$wgGroupPermissions[$group]['patrol'] = false;
		$wgGroupPermissions[$group]['autopatrol'] = false;
	}
	
	global $wgScriptPath;
	if( !defined( 'FLAGGED_CSS' ) )
		define('FLAGGED_CSS', $wgScriptPath.'/extensions/FlaggedRevs/flaggedrevs.css' );
	if( !defined( 'FLAGGED_JS' ) )
		define('FLAGGED_JS', $wgScriptPath.'/extensions/FlaggedRevs/flaggedrevs.js' );
	
	######### Hook attachments #########
	$wgHooks['OutputPageParserOutput'][] = array( 'FlaggedRevs::InjectStyle' );
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
	$wgHooks['ArticleSaveComplete'][] = array( 'FlaggedRevs::autoPromoteUser' );
	# Adds table link references to include ones from the stable version
	$wgHooks['LinksUpdateConstructed'][] = array( 'FlaggedRevs::extraLinksUpdate' );
	# Empty flagged page settings row on delete
	$wgHooks['ArticleDeleteComplete'][] = array( 'FlaggedRevs::deleteVisiblitySettings' );
	# Check on undelete/merge/revisiondelete for changes to stable version
	$wgHooks['ArticleUndelete'][] = array( 'FlaggedRevs::articleLinksUpdate2' );
	$wgHooks['ArticleRevisionVisiblitySet'][] = array( 'FlaggedRevs::articleLinksUpdate2' );
	$wgHooks['ArticleMergeComplete'][] = array( 'FlaggedRevs::updateFromMerge' );
	# Clean up after undeletion
	$wgHooks['ArticleRevisionUndeleted'][] = array( 'FlaggedRevs::updateFromRestore' );
	# Parser hooks, selects the desired images/templates
	$wgHooks['BeforeParserrenderImageGallery'][] = array( 'FlaggedRevs::parserMakeGalleryStable' );
	$wgHooks['BeforeGalleryFindFile'][] = array( 'FlaggedRevs::galleryFindStableFileTime' );
	$wgHooks['BeforeParserFetchTemplateAndtitle'][] = array( 'FlaggedRevs::parserFetchStableTemplate' );
	$wgHooks['BeforeParserMakeImageLinkObj'][] = array( 'FlaggedRevs::parserMakeStableImageLink' );
	# Additional parser versioning
	$wgHooks['ParserAfterTidy'][] = array( 'FlaggedRevs::parserInjectImageTimestamps' );
	$wgHooks['OutputPageParserOutput'][] = array( 'FlaggedRevs::outputInjectImageTimestamps');
	# Page review on edit
	$wgHooks['ArticleUpdateBeforeRedirect'][] = array($wgFlaggedArticle, 'injectReviewDiffURLParams');
	$wgHooks['DiffViewHeader'][] = array($wgFlaggedArticle, 'addDiffNoticeAfterEdit' );
	$wgHooks['DiffViewHeader'][] = array($wgFlaggedArticle, 'addPatrolLink' );
	# Autoreview stuff
	$wgHooks['ArticleInsertComplete'][] = array( $wgFlaggedArticle, 'maybeMakeNewPageReviewed' );
	$wgHooks['ArticleSaveComplete'][] = array( $wgFlaggedArticle, 'maybeMakeEditReviewed' );
	$wgHooks['ArticleRollbackComplete'][] = array( $wgFlaggedArticle, 'maybeMakeRollbackReviewed' );
	$wgHooks['ArticleSaveComplete'][] = array( 'FlaggedRevs::autoMarkPatrolled' );
	# Disallow moves of stable pages
	$wgHooks['userCan'][] = array( 'FlaggedRevs::userCanMove' );
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

# Do flagged revs override the default view?
$wgFlaggedRevsOverride = true;
# Revision tagging can slow development...
# For example, the main user base may become complacent, perhaps treat flagged
# pages as "done", or just be too lazy to click "current". We may just want non-user
# visitors to see reviewed pages by default.
# Below are groups that see the current revision by default.
$wgFlaggedRevsExceptions = array( 'user' );

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

# Compress pre-processed flagged revision text?
$wgFlaggedRevsCompression = false;

# When parsing a reviewed revision, if a template to be transcluded
# has a stable version, use that version. If not present, use the one
# specified when the reviewed revision was reviewed. Note that the 
# fr_text column will not be used, which may reduce performance. It will
# still be populated however, so that these settings can be retroactively
# changed.
$wgUseStableTemplates = false;
# We may have templates that do not have stable version. Given situational
# inclusion of templates (such as parser functions that select template
# X or Y depending), there may also be no revision ID for each template 
# pointed to by the metadata of how the article was when it was reviewed. 
# An example would be an article that selects a template based on time. 
# The template to be selected will change, and the metadata only points 
# to the reviewed revision ID of the old template. This can be a problem if 
# $wgUseStableTemplates is enabled. In such cases, we can select the 
# current (unreviewed) revision.
$wgUseCurrentTemplates = true;

# When setting up new dimensions or levels, you will need to add some
# MediaWiki messages for the UI to show properly; any sysop can do this.
# Define the tags we can use to rate an article, and set the minimum level
# to have it become a "quality" version. "Quality" revisions take precedence
# over other reviewed revisions
$wgFlaggedRevTags = array( 'accuracy'=>2, 'depth'=>1, 'style'=>1 );
# How high can we rate these revisions?
$wgFlaggedRevValues = 4;
# Who can set what flags to what level? (use -1 for not at all)
# Users cannot lower tags from a level they can't set
# Users with 'validate' can do anything regardless
# This is mainly for custom, less experienced, groups
$wgFlagRestrictions = array(
	'accuracy' => array( 'review' => 1 ),
	'depth'	   => array( 'review' => 2 ),
	'style'	   => array( 'review' => 3 ),
);

# Lets some users access the review UI and set some flags
$wgAvailableRights[] = 'review';
# Let some users set higher settings
$wgAvailableRights[] = 'validate';

# Define our basic reviewer class
$wgGroupPermissions['editor']['review']		  = true;
$wgGroupPermissions['editor']['unwatchedpages']  = true;
$wgGroupPermissions['editor']['autoconfirmed']   = true;
$wgGroupPermissions['editor']['patrolmarks']	 = true;
$wgGroupPermissions['editor']['patrolother']	 = true;
$wgGroupPermissions['editor']['unreviewedpages'] = true;

# Defines extra rights for advanced reviewer class
$wgGroupPermissions['reviewer']['validate'] = true;
# Let this stand alone just in case...
$wgGroupPermissions['reviewer']['review']   = true;

# Stable version selection and default page revision selection
# can be set per page.
$wgGroupPermissions['sysop']['stablesettings'] = true;
$wgGroupPermissions['sysop']['patrolother']	 = true;

# Define when users get automatically promoted to editors. Set as false to disable.
$wgFlaggedRevsAutopromote = array(
	'days'	     => 60,
	'edits'	     => 150,
	'spacing'	 => 5, // in days
	'benchmarks' => 10, // keep this small
	'email'	     => true,
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
	public static $dimensions = array();

	public static function load() {
		global $wgFlaggedRevTags, $wgFlaggedRevValues;
		
		foreach( $wgFlaggedRevTags as $tag => $minQL ) {
			if( strpos($tag,':') || strpos($tag,'\n') ) {
				throw new MWException( 'FlaggedRevs given invalid tag name!' );
			} else if( intval($minQL) != $minQL ) {
				throw new MWException( 'FlaggedRevs given invalid tag value!' );
			}
			self::$dimensions[$tag] = array();
			for( $i=0; $i <= $wgFlaggedRevValues; $i++ ) {
				self::$dimensions[$tag][$i] = "{$tag}-{$i}";
			}
		}
	}

	/**
	 * Should this be using a simple icon-based UI?
	 */
	public static function useSimpleUI() {
		global $wgSimpleFlaggedRevsUI;
		
		return $wgSimpleFlaggedRevsUI;
	}

	/**
	 * Should comments be allowed on pages and forms?
	 */
	public static function allowComments() {
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
	public static function expandText( $text='', $title, $id ) {
		global $wgParser;
		# Make our hooks to trigger
		$wgParser->fr_isStable = true;
		$wgParser->fr_includesMatched = true;
		# Parse with default options
		$options = new ParserOptions();
		$options->setRemoveComments( true ); // Save some bandwidth ;)
		$outputText = $wgParser->preprocess( $text, $title, $options, $id );
		$expandedText = array( $outputText, $wgParser->fr_includesMatched );
		# Done!
		$wgParser->fr_isStable = false;
		$wgParser->fr_includesMatched = false;
		
		return $expandedText;
	}

	/**
	 * @param Article $article
	 * @param string $text
	 * @param int $id
	 * @return ParserOutput
	 * Get the HTML output of a revision based on $text
	 */
	public static function parseStableText( $article, $text, $id ) {
		global $wgParser;
		# Default options for anons if not logged in
		$options = new ParserOptions();
		# Make our hooks to trigger
		$wgParser->fr_isStable = true;
		# Don't show section-edit links, they can be old and misleading
		$options->setEditSection(false);
		// $options->setEditSection( $id==$article->getLatest() );
		# Parse the new body, wikitext -> html
		$title = $article->getTitle(); // avoid pass-by-reference error
	   	$parserOut = $wgParser->parse( $text, $title, $options, true, true, $id );
	   	# Reset $wgParser
	   	$wgParser->fr_isStable = false; // Done!
		
	   	return $parserOut;
	}

	/**
	* @param string $text
	* @return string, flags
	* Compress pre-processed text, passed by reference
	*/
	public static function compressText( &$text ) {
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
	public static function uncompressText( $text, $flags ) {
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
	 * @return Row
	 * Will not return a revision if deleted
	 */
	public static function getFlaggedRev( $title, $rev_id, $getText=false ) {
		$columns = array('fr_rev_id','fr_user','fr_timestamp','fr_comment','rev_timestamp','fr_tags');
		if( $getText ) {
			$columns[] = 'fr_text';
			$columns[] = 'fr_flags';
		}
		
		$dbr = wfGetDB( DB_SLAVE );
		# Skip deleted revisions
		$result = $dbr->select( array('flaggedrevs','revision'),
			$columns,
			array( 'fr_page_id' => $title->getArticleID(),
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
	public static function getRevCountSince( $page_id, $from_rev ) {
		$dbr = wfGetDB( DB_SLAVE );
		$count = $dbr->selectField('revision', 'COUNT(*)',
			array('rev_page' => $page_id, "rev_id > " . intval($from_rev) ),
			__METHOD__ );
		
		return $count;
	}

	/**
	 * Get latest quality rev, if not, the latest reviewed one.
	 * @param Title $title, page title
	 * @param bool $getText, fetch fr_text and fr_flags too?
	 * @param bool $forUpdate, use master DB and avoid using page_ext_stable?
	 * @returns Row
	*/
	public static function getStablePageRev( $title, $getText=false, $forUpdate=false ) {
		$columns = array('fr_rev_id','fr_user','fr_timestamp','fr_comment','fr_quality','fr_tags');
		if( $getText ) {
			$columns[] = 'fr_text';
			$columns[] = 'fr_flags';
		}
		$row = null;
		# If we want the text, then get the text flags too
		if( !$forUpdate ) {
			$dbr = wfGetDB( DB_SLAVE );
			$result = $dbr->select( array('page', 'flaggedrevs'),
				$columns,
				array( 'page_namespace' => $title->getNamespace(),
					'page_title' => $title->getDBkey(),
					'page_ext_stable = fr_rev_id' ),
				__METHOD__,
				array('LIMIT' => 1) );
			if( !$row = $dbr->fetchObject($result) )
				return null;
		} else {
			// Get visiblity settings...
			$config = self::getPageVisibilitySettings( $title, $forUpdate );
			$dbw = wfGetDB( DB_MASTER );
			// Look for the latest quality revision
			if( $config['select'] !== FLAGGED_VIS_LATEST ) {
				$result = $dbw->select( array('flaggedrevs', 'revision'),
					$columns,
					array( 'fr_page_id' => $title->getArticleID(),
						'fr_quality >= 1',
						'fr_rev_id = rev_id',
						'rev_deleted & '.Revision::DELETED_TEXT.' = 0'),
					__METHOD__,
					array( 'ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1) );
				$row = $dbw->fetchObject($result);
			}
			// Do we have one? If not, try the latest reviewed revision...
			if( !$row ) {
				$result = $dbw->select( array('flaggedrevs', 'revision'),
					$columns,
					array( 'fr_page_id' => $title->getArticleID(),
						'fr_rev_id = rev_id',
						'rev_deleted & '.Revision::DELETED_TEXT.' = 0'),
					__METHOD__,
					array( 'ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => 1 ) );
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
	 * @returns Array (select,override)
	*/
	public static function getPageVisibilitySettings( $title, $forUpdate=false ) {
		$db = $forUpdate ? wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		$row = $db->selectRow( 'flaggedpage_config',
			array( 'fpc_select', 'fpc_override' ),
			array( 'fpc_page_id' => $title->getArticleID() ),
			__METHOD__ );
		if( !$row ) {
			global $wgFlaggedRevsOverride;
			// Keep this consistent across settings. 1 -> override, 0 -> don't
			$override = $wgFlaggedRevsOverride ? 1 : 0;
			return array('select' => 0, 'override' => $override);
		}
		return array('select' => $row->fpc_select, 'override' => $row->fpc_override);
	}

	/**
	 * Get flags for a revision
	 * @param string $tags
	 * @return Array
	*/
	public static function expandRevisionTags( $tags ) {
		# Set all flags to zero
		$flags = array();
		foreach( self::$dimensions as $tag => $minQL ) {
			$flags[$tag] = 0;
		}
		$tags = explode('\n',$tags);
		foreach( $tags as $tuple ) {
			$set = explode(':',$tuple,2);
			if( count($set) == 2 ) {
				list($tag,$value) = $set;
				# Add only currently recognized ones
				if( isset($flags[$tag]) ) {
					$flags[$tag] = intval($value);
				}
			}
		}
		return $flags;
	}
	
	/**
	 * Get flags for a revision
	 * @param Array $tags
	 * @return string
	*/
	public static function flattenRevisionTags( $tags ) {
		$flags = '';
		foreach( $tags as $tag => $value ) {
			# Add only currently recognized ones
			if( isset(self::$dimensions[$tag]) ) {
				$flags .= $tag . ':' . intval($value) . '\n';
			}
		}
		return $flags;
	}
	
	/**
	 * Get flags for a revision
	 * @param int $rev_id
	 * @return Array
	*/
	public static function getRevisionTags( $rev_id ) {
		$dbr = wfGetDB( DB_SLAVE );
		$tags = $dbr->selectField('flaggedrevs', 'fr_tags',
			array('fr_rev_id' => $rev_id ),
			__METHOD__ );
		if( !$tags )
			return false;
		
		return self::expandRevisionTags( strval($tags) );
	}

	/**
	 * @param Title $title
	 * @return bool, is $title the main page?
	 */
	public static function isMainPage( $title ) {
		$mp = Title::newMainPage();
		return ( $title->getNamespace()==$mp->getNamespace() && $title->getDBKey()==$mp->getDBKey() );
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
		global $wgFlaggedRevTags, $wgFlaggedRevValues;
		
		if( !$flags )
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
	public static function isPageReviewable( $title ) {
		global $wgFlaggedRevsNamespaces;
		
		return ( in_array($title->getNamespace(),$wgFlaggedRevsNamespaces)
			&& !$title->isTalkPage() );
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
			return NULL;
		
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
	public static function updatePageCache( $article, $parserOut=NULL ) {
		global $wgUser, $parserMemc, $wgParserCacheExpireTime;
		# Make sure it is valid
		if( is_null($parserOut) || !$article )
			return false;
		# Update the cache...
		$article->getTitle()->invalidateCache();
		
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
			$expire = 3600; # 1 hour
		} else {
			$expire = $wgParserCacheExpireTime;
		}
		# Save to objectcache
		$parserMemc->set( $key, $parserOut, $expire );
		# Purge squid for this page only
		$article->getTitle()->purgeSquid();

		return true;
	}

	/**
	* Add FlaggedRevs css/js
	*/
	public static function InjectStyle( $out, $parserOut ) {
		global $wgJsMimeType;
		# UI CSS
		$out->addLink( array(
			'rel'	=> 'stylesheet',
			'type'	=> 'text/css',
			'media'	=> 'screen, projection',
			'href'	=> FLAGGED_CSS,
		) );
		# UI JS
		$out->addScript( "<script type=\"{$wgJsMimeType}\" src=\"" . FLAGGED_JS . "\"></script>\n" );

		return true;
	}

	/**
	* Automatically review an edit and add a log entry in the review log.
	* LinksUpdate was already called via edit operations, so the page
	* fields will be up to date. This updates the stable version.
	*/
	public static function autoReviewEdit( $article, $user, $text, $rev, $flags ) {
		global $wgParser, $wgFlaggedRevsAutoReview;
		
		$quality = 0;
		if( self::isQuality($flags) ) {
			$quality = self::isPristine($flags) ? 2 : 1;
		}
		$tmpset = $imgset = array();
		# Try the parser cache, should be set on the edit before this is called.
		# If not set or up to date, then parse it...
		$parserCache = ParserCache::singleton();
		$poutput = $parserCache->get( $article, $user );
		if( $poutput==false ) {
			$options = ParserOptions::newFromUser($user);
			$options->setTidy(true);
			$poutput = $wgParser->parse( $text, $article->getTitle(), $options, true, true, $rev->getID() );
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
		list($fulltext,$complete) = self::expandText( $rev->getText(), $rev->getTitle(), $rev->getId() );
		if( !$complete ) {
			$dbw->rollback(); // All versions must be specified, 0 for none
			return false;
		}
		# Compress $fulltext, passed by reference
		$textFlags = self::compressText( $fulltext );
		# Our review entry
		$revisionset = array(
			'fr_page_id'   => $rev->getPage(),
			'fr_rev_id'	=> $rev->getId(),
			'fr_user'	  => $user->getId(),
			'fr_timestamp' => wfTimestampNow(),
			'fr_comment'   => '',
			'fr_quality'   => $quality,
			'fr_tags'	  => self::flattenRevisionTags( $flags ),
			'fr_text'	  => $fulltext, // Store expanded text for speed
			'fr_flags'	 => $textFlags
		);
		# Update flagged revisions table
		$dbw->replace( 'flaggedrevs',
			array( array('fr_page_id','fr_rev_id') ), $revisionset,
			__METHOD__ );
		# Mark as patrolled
		$dbw->update( 'recentchanges',
			array( 'rc_patrolled' => 1 ),
			array( 'rc_this_oldid' => $rev->getId(),
				'rc_timestamp' => $dbw->timestamp( $rev->getTimestamp() ) ),
			__METHOD__
		);
		$dbw->commit();
		
		# Update the article review log
		Revisionreview::updateLog( $rev->getTitle(), $flags, wfMsg('revreview-auto'),
			$rev->getID(), true, false );

		# Might as well save the stable version cache
		self::updatePageCache( $article, $poutput );
		# Update page fields
		self::updateArticleOn( $article, $rev->getID() );
		# Purge squid for this page only
		$article->getTitle()->purgeSquid();
		
		return true;
	}

 	/**
	* @param Article $article
	* @param Integer $rev_id, the stable version rev_id
	* Updates the page_ext_stable and page_ext_reviewed fields
	*/
	public static function updateArticleOn( $article, $rev_id ) {
		$dbw = wfGetDB( DB_MASTER );
		// Get the highest quality revision (not necessarily this one).
		$maxQuality = $dbw->selectField( array('flaggedrevs', 'revision'),
			'fr_quality',
			array( 'fr_page_id' => $article->getTitle()->getArticleID(),
				'fr_rev_id = rev_id',
				'rev_deleted & '.Revision::DELETED_TEXT.' = 0'),
			__METHOD__,
			array( 'ORDER BY' => 'fr_quality,fr_rev_id DESC', 'LIMIT' => 1) );
		$maxQuality = $maxQuality===false ? null : $maxQuality;
		// Alter table metadata
		$dbw->update( 'page',
			array('page_ext_stable' => $rev_id,
				'page_ext_reviewed' => ($article->getLatest() == $rev_id),
				'page_ext_quality' => $maxQuality ),
			array('page_namespace' => $article->getTitle()->getNamespace(),
				'page_title' => $article->getTitle()->getDBkey() ),
			__METHOD__ );
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
		$oldPageID = $sourceTitle->getArticleID();
		$newPageID = $destTitle->getArticleID();
		// Get flagged revisions from old page id that point to destination page
		$dbw = wfGetDB( DB_MASTER );
		$result = $dbw->select( array('flaggedrevs','revision'),
			array( 'fr_rev_id' ),
			array( 'fr_page_id' => $oldPageID,
				'fr_rev_id = rev_id',
				'rev_page' => $newPageID ),
			__METHOD__ );
		// Update these rows
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
		// Update pages
		self::articleLinksUpdate2( $sourceTitle );
		self::articleLinksUpdate2( $destTitle );
		
		return true;
	}

	/**
	* Clears visiblity settings on page delete
	*/
	public static function deleteVisiblitySettings( $article, $user, $reason ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'flaggedpage_config',
			array('fpc_page_id' => $article->getID() ),
			__METHOD__ );
			
		return true;
	}

	/**
	* Clears cache for a page when merges are done.
	* We may have lost the stable revision to another page.
	*/
	public static function articleLinksUpdate( $article, $a=null, $b=null ) {
		global $wgUser, $wgParser;
		# Update the links tables as the stable version may now be the default page...
		$parserCache = ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if( $poutput==false ) {
			$text = $article->getContent();
			$poutput = $wgParser->parse($text, $article->getTitle(), ParserOptions::newFromUser($wgUser));
			# Might as well save the cache while we're at it
			$parserCache->save( $poutput, $article, $wgUser );
		}
		$u = new LinksUpdate( $article->getTitle(), $poutput );
		$u->doUpdate(); // this will trigger our hook to add stable links too...
		
		return true;
	}

	/**
	* Clears cache for a page when revisiondelete/undelete is used
	*/
	public static function articleLinksUpdate2( $title, $a=null, $b=null ) {
		return self::articleLinksUpdate( new Article( $title ), $a, $b );
	}

	/**
	* Inject stable links on LinksUpdate
	*/
	public static function extraLinksUpdate( $linksUpdate ) {
		wfProfileIn( __METHOD__ );
		
		if( !self::isPageReviewable( $linksUpdate->mTitle ) )
			return true;
		# Check if this page has a stable version by fetching it. Do not
		# get the fr_text field if we are to use the latest stable template revisions.
		global $wgUseStableTemplates;
		$sv = self::getStablePageRev( $linksUpdate->mTitle, !$wgUseStableTemplates, true );
		if( !$sv )
			return true;
		# Get the either the full flagged revision text or the revision text
		$article = new Article( $linksUpdate->mTitle );
		if( $wgUseStableTemplates ) {
			$rev = Revision::newFromId( $sv->fr_rev_id );
			$text = $rev->getText();
		} else {
			$text = self::uncompressText( $sv->fr_text, $sv->fr_flags );
		}
		# Parse the text
		$parserOut = self::parseStableText( $article, $text, $sv->fr_rev_id );
		# Might as well update the stable cache while we're at it
		self::updatePageCache( $article, $parserOut );
		# Update page fields
		self::updateArticleOn( $article, $sv->fr_rev_id );
		# Update the links tables to include these
		# We want the UNION of links between the current
		# and stable version. Therefore, we only care about
		# links that are in the stable version and not the regular one.
		foreach( $parserOut->getLinks() as $ns => $titles ) {
			foreach( $titles as $title => $id )
				$linksUpdate->mLinks[$ns][$title] = $id;
		}
		foreach( $parserOut->getImages() as $image => $n ) {
			$linksUpdate->mImages[$image] = $n;
		}
		foreach( $parserOut->getTemplates() as $ns => $titles ) {
			foreach( $titles as $title => $id )
				$linksUpdate->mTemplates[$ns][$title] = $id;
		}
		foreach( $parserOut->getExternalLinks() as $image => $n ) {
			$linksUpdate->mExternals[$image] = $n;
		}
		foreach( $parserOut->getCategories() as $category => $sort ) {
			$linksUpdate->mCategories[$category] = $sort;
		}
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
	public static function parserFetchStableTemplate( $parser, $title, &$skip, &$id ) {
		# Trigger for stable version parsing only
		if( !isset($parser->fr_isStable) || !$parser->fr_isStable )
			return true;
		# Only called to make fr_text, right after template/image specifiers
		# are added to the DB. Slaves may not have it yet...
		$dbw = wfGetDB( DB_MASTER );
		$id = null;
		# Check for stable version of template if this feature is enabled.
		# Should be in reviewable namespace, this saves unneeded DB checks as
		# well as enforce site settings if they are later changed.
		global $wgUseStableTemplates, $wgFlaggedRevsNamespaces;
		if( $wgUseStableTemplates && in_array($title->getNamespace(), $wgFlaggedRevsNamespaces) ) {
			$id = $dbw->selectField( 'page', 'page_ext_stable',
			array( 'page_namespace' => $title->getNamespace(),
				'page_title' => $title->getDBkey() ),
			__METHOD__ );
		}
		// If there is not stable version (or that feature is not enabled), use
		// the template revision during review time.
		if( !$id ) {
			$id = $dbw->selectField( 'flaggedtemplates', 'ft_tmp_rev_id',
				array( 'ft_rev_id' => $parser->mRevisionId,
					'ft_namespace' => $title->getNamespace(),
					'ft_title' => $title->getDBkey() ),
				__METHOD__ );
		}
		
		if( !$id ) {
			global $wgUseCurrentTemplates;

			if( $id === false )
				$parser->fr_includesMatched = false; // May want to give an error
			if( !$wgUseCurrentTemplates )
				$id = 0; // Zero for not found
			$skip = true;
		}
		return true;
	}

	/**
	* Select the desired images based on the selected stable revision times/SHA-1s
	*/
	public static function parserMakeStableImageLink( $parser, $nt, &$skip, &$time ) {
		# Trigger for stable version parsing only
		if( !isset($parser->fr_isStable) || !$parser->fr_isStable )
			return true;
		# Only called to make fr_text, right after template/image specifiers
		# are added to the DB. Slaves may not have it yet...
		$dbw = wfGetDB( DB_MASTER );
	   	$time = $dbw->selectField( 'flaggedimages', 'fi_img_timestamp',
			array('fi_rev_id' => $parser->mRevisionId,
				'fi_name' => $nt->getDBkey() ),
			__METHOD__ );
		
		if( !$time ) {
			if( $time === false )
				$parser->fr_includesMatched = false; // May want to give an error
			$time = 0; // Zero for not found
			$skip = true;
		}
		return true;
	}

	/**
	* Select the desired images based on the selected stable revision times/SHA-1s
	*/
	public static function galleryFindStableFileTime( $ig, $nt, &$time ) {
		# Trigger for stable version parsing only
		if( !isset($ig->fr_isStable) || !$ig->fr_isStable )
			return true;
		# Slaves may not have it yet...
		$dbr = wfGetDB( DB_MASTER );
		$time = $dbr->selectField( 'flaggedimages', 'fi_img_timestamp',
			array('fi_rev_id' => $ig->mRevisionId,
				'fi_name' => $nt->getDBkey() ),
			__METHOD__ );
		$time = $time ? $time : -1; // hack, will never find this
		
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
		
		return true;
	}

	/**
	* Insert image timestamps/SHA-1 keys into parser output
	*/
	public static function parserInjectImageTimestamps( $parser, &$text ) {
		$parser->mOutput->fr_ImageSHA1Keys = array();
		# Fetch the timestamps of the images
		if( !empty($parser->mOutput->mImages) ) {
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select('image', array('img_name','img_timestamp','img_sha1'),
				array('img_name IN(' . $dbr->makeList( array_keys($parser->mOutput->mImages) ) . ')'),
				__METHOD__ );
			
			while( $row = $dbr->fetchObject($res) ) {
				$parser->mOutput->fr_ImageSHA1Keys[$row->img_name] = array();
				$parser->mOutput->fr_ImageSHA1Keys[$row->img_name][$row->img_timestamp] = $row->img_sha1;
			}
		}
		return true;
	}

	/**
	* Insert image timestamps/SHA-1s into page output
	*/
	public static function outputInjectImageTimestamps( $out, $parserOut ) {
		$out->fr_ImageSHA1Keys = $parserOut->fr_ImageSHA1Keys;

		return true;
	}

	/**
	* Don't let users vandalize pages by moving them.
	*/
	public static function userCanMove( $title, $user, $action, $result ) {
		if( $action != 'move' )
			return true;
		# See if there is a stable version
		$frev = self::getStablePageRev( $title );
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
	public static function autoMarkPatrolled( $article, $user, $text, $c, $m, $a, $b, $flags, $rev ) {
		if( !$rev )
			return true;
		
		if( !self::isPageReviewable( $article->getTitle() ) && $user->isAllowed('patrolother') ) {
			$dbw = wfGetDB( DB_MASTER );
			$dbw->update( 'recentchanges',
				array( 'rc_patrolled' => 1 ),
				array( 'rc_this_oldid' => $rev->getID(),
					'rc_timestamp' => $dbw->timestamp( $rev->getTimestamp() ) ),
				__METHOD__ );
		}
		return true;
	}

	/**
	* Callback that autopromotes user according to the setting in
	* $wgFlaggedRevsAutopromote. This is not as efficient as it should be
	*/
	public static function autoPromoteUser( $article, $user, &$text, &$summary, &$m, &$w, &$s ) {
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
		if( in_array( 'bot', $groups ) || in_array( 'editor', $groups ) )
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
		array_push( $newGroups, 'editor' );
		# Lets NOT spam RC, set $RC to false
		$log = new LogPage( 'rights', false );
		$log->addEntry('rights', $user->getUserPage(), wfMsg('makevalidate-autosum'),
			array( implode(', ',$groups), implode(', ',$newGroups) ) );
		$user->addGroup('editor');
		
		return true;
	}

   	/**
	* Get a selector of reviewable namespaces
	* @param int $selected, namespace selected
	*/
	public static function getNamespaceMenu( $selected=NULL ) {
		global $wgContLang, $wgFlaggedRevsNamespaces;
		
		$selector = "<label for='namespace'>" . wfMsgHtml('namespace') . "</label>";
		if( $selected !== '' ) {
			if( is_null( $selected ) ) {
				// No namespace selected; let exact match work without hitting Main
				$selected = '';
			} else {
				// Let input be numeric strings without breaking the empty match.
				$selected = intval($selected);
			}
		}
		$s = "\n<select id='namespace' name='namespace' class='namespaceselector'>\n";
		$arr = $wgContLang->getFormattedNamespaces();
		
		foreach($arr as $index => $name) {
			# Content only
			if($index < NS_MAIN || !in_array($index, $wgFlaggedRevsNamespaces) )
				continue;

			$name = $index !== 0 ? $name : wfMsg('blanknamespace');

			if($index === $selected) {
				$s .= "\t" . Xml::element("option",
						array("value" => $index, "selected" => "selected"),
						$name) . "\n";
			} else {
				$s .= "\t" . Xml::element("option", array("value" => $index), $name) . "\n";
			}
		}
		$s .= "</select>\n";
		return $s;
	}

}
