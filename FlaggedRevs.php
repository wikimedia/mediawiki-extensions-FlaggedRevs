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

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Flagged Revisions',
	'author' => array( 'Aaron Schulz', 'Joerg Baach' ),
	'version' => '1.032',
	'url' => 'http://www.mediawiki.org/wiki/Extension:FlaggedRevs',
	'descriptionmsg' => 'flaggedrevs-desc',
);

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
# Do quality revisions show instead of sighted if present by default?
$wgFlaggedRevsPrecedence = true;
# Revision tagging can slow development...
# For example, the main user base may become complacent, perhaps treat flagged
# pages as "done", or just be too lazy to click "current". We may just want non-user
# visitors to see reviewed pages by default.
# Below are groups that see the current revision by default.
$wgFlaggedRevsExceptions = array( 'user' );

# Flagged revisions are always visible to users in the groups below.
# Use '*' for non-user accounts.
$wgFlaggedRevsVisible = array();

# Can users make comments that will show up below flagged revisions?
$wgFlaggedRevComments = false;
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

# Similar to above...
$wgUseStableImages = false;
$wgUseCurrentImages = true;

# When setting up new dimensions or levels, you will need to add some
# MediaWiki messages for the UI to show properly; any sysop can do this.
# Define the tags we can use to rate an article, and set the minimum level
# to have it become a "quality" version. "Quality" revisions take precedence
# over other reviewed revisions
$wgFlaggedRevTags = array( 'accuracy'=>2, 'depth'=>1, 'style'=>1 );
# How high can we rate these revisions?
$wgFlaggedRevValues = 4;
# A revision with all tages rated at least to this level is considered "pristine"/"featured"
$wgFlaggedRevPristine = 4;
# Who can set what flags to what level? (use -1 or 0 for not at all)
# Users cannot lower tags from a level they can't set
# Users with 'validate' can do anything regardless
# This is mainly for custom, less experienced, groups
$wgFlagRestrictions = array(
	'accuracy' => array( 'review' => 1 ),
	'depth'	   => array( 'review' => 2 ),
	'style'	   => array( 'review' => 3 ),
);

# Mark all previous edits as "patrolled" when an edit is reviewed.
# This just sets markers on recent changes.
$wgFlaggedRevsCascade = true;

# Please set these as something different. Any text will do, though it probably
# shouldn't be very short (less secure) or very long (waste of resources).
# There must be four codes, and only the first four are checked.
$wgReviewCodes = array( 'first one', 'second code', 'yet another', 'the last' );

# Lets some users access the review UI and set some flags
$wgAvailableRights[] = 'review';
# Let some users set higher settings
$wgAvailableRights[] = 'validate';

# Define our basic reviewer class
$wgGroupPermissions['editor']['review']		  	= true;
$wgGroupPermissions['editor']['unwatchedpages'] 	= true;
$wgGroupPermissions['editor']['autoconfirmed']  	= true;
$wgGroupPermissions['editor']['patrolmarks']	    = true;
$wgGroupPermissions['editor']['autopatrolother']	= true;
$wgGroupPermissions['editor']['unreviewedpages']	= true;
$wgGroupPermissions['editor']['rollback']	        = true;

# Defines extra rights for advanced reviewer class
$wgGroupPermissions['reviewer']['validate'] = true;
# Let this stand alone just in case...
$wgGroupPermissions['reviewer']['review']   = true;

# Stable version selection and default page revision selection can be set per page.
$wgGroupPermissions['sysop']['stablesettings'] = true;

# Try to avoid flood by having autoconfirmed user edits to non-reviewable
# namespaces autopatrolled.
$wgGroupPermissions['autoconfirmed']['autopatrolother'] = true;

# Define when users get automatically promoted to editors. Set as false to disable.
# 'spacing' and 'benchmarks' require edits to be spread out. Users must have X (benchmark)
# edits Y (spacing) days apart.
$wgFlaggedRevsAutopromote = array(
	'days'	              => 60, # days since registration
	'edits'	              => 150, # total edit count
	'excludeDeleted'      => true, # exclude deleted edits from 'edits' count above?
	'spacing'	          => 3, # spacing of edit intervals
	'benchmarks'          => 15, # how many edit intervals are needed?
	'recentContentEdits'  => 10, # $wgContentNamespaces edits in recent changes
	'totalContentEdits'   => 30, # $wgContentNamespaces edits
	'uniqueContentPages'  => 10, # $wgContentNamespaces unique pages edited
	'editComments'        => 5, # how many edit comments used?
	'email'	              => true, # user must be emailconfirmed?
	'userpage'            => true, # user must have a userpage?
	'userpageBytes'       => 100, # if userpage is needed, what is the min size?
	'uniqueIPAddress'     => true, # If $wgPutIPinRC is true, users sharing IPs won't be promoted
	'noSorbsMatches'      => false, # If $wgSorbsUrl is set, do not promote users that match
);

# Special:Userrights settings
## Basic rights for Sysops
$wgAddGroups['sysop'] = array( 'editor' );
$wgRemoveGroups['sysop'] = array( 'editor' );
## Extra ones for Bureaucrats
## Add UI page rights just in case we have non-sysop bcrats
$wgAddGroups['bureaucrat'] = array( 'reviewer' );
$wgRemoveGroups['bureaucrat'] = array( 'reviewer' );

# If you want to use a storage group specifically for this
# software, set this array
$wgFlaggedRevsExternalStore = false;

# End of configuration variables.
#########

# Bump this number every time you change flaggedrevs.css/flaggedrevs.js
$wgFlaggedRevStyleVersion = 13;

$wgExtensionFunctions[] = 'efLoadFlaggedRevs';

$dir = dirname(__FILE__) . '/';
$wgExtensionMessagesFiles['FlaggedRevsPage'] = $dir . 'FlaggedRevsPage.i18n.php';

# Load general UI
$wgAutoloadClasses['FlaggedArticle'] = $dir . 'FlaggedArticle.php';
# Load FlaggedRevision object class
$wgAutoloadClasses['FlaggedRevision'] = $dir . 'FlaggedRevision.php';
# Load review UI
$wgSpecialPages['RevisionReview'] = 'RevisionReview';
$wgAutoloadClasses['RevisionReview'] = $dir . 'FlaggedRevsPage.php';
# Load stableversions UI
$wgSpecialPages['StableVersions'] = 'StableVersions';
$wgAutoloadClasses['StableVersions'] = $dir . 'FlaggedRevsPage.php';
# Load unreviewed pages list
$wgSpecialPages['UnreviewedPages'] = 'UnreviewedPages';
$wgAutoloadClasses['UnreviewedPages'] = $dir . 'FlaggedRevsPage.php';
# Load reviewed pages list
$wgSpecialPages['ReviewedPages'] = 'ReviewedPages';
$wgAutoloadClasses['ReviewedPages'] = $dir . 'FlaggedRevsPage.php';
# Load stable pages list
$wgSpecialPages['StablePages'] = 'StablePages';
$wgAutoloadClasses['StablePages'] = $dir . 'FlaggedRevsPage.php';
# Stable version config
$wgSpecialPages['Stabilization'] = 'Stabilization';
$wgAutoloadClasses['Stabilization'] = $dir . 'FlaggedRevsPage.php';
# Too oversee quality revisions
$wgSpecialPages['QualityOversight'] = 'QualityOversight';
$wgAutoloadClasses['QualityOversight'] = $dir . 'FlaggedRevsPage.php';

# Remove stand-alone patrolling
$wgHooks['UserGetRights'][] = 'FlaggedRevs::stripPatrolRights';

function efLoadFlaggedRevs() {
	global $wgOut, $wgHooks, $wgLang, $wgFlaggedArticle, $wgUseRCPatrol;
	# Initialize
	FlaggedRevs::load();
	$wgFlaggedArticle = new FlaggedArticle();

	wfLoadExtensionMessages( 'FlaggedRevsPage' );

	# Use RC Patrolling to check for vandalism
	# When revisions are flagged, they count as patrolled
	$wgUseRCPatrol = true;

	global $wgScriptPath, $wgFlaggedRevStyleVersion;
	if( !defined( 'FLAGGED_CSS' ) )
		define( 'FLAGGED_CSS', $wgScriptPath . '/extensions/FlaggedRevs/flaggedrevs.css?' . $wgFlaggedRevStyleVersion );
	if( !defined( 'FLAGGED_JS' ) )
		define( 'FLAGGED_JS', $wgScriptPath . '/extensions/FlaggedRevs/flaggedrevs.js?' . $wgFlaggedRevStyleVersion );

	######### Hook attachments #########
	$wgHooks['OutputPageParserOutput'][] = 'FlaggedRevs::InjectStyleAndJS';
	$wgHooks['EditPage::showEditForm:initial'][] = 'FlaggedRevs::InjectStyleAndJS';
	$wgHooks['PageHistoryBeforeList'][] = 'FlaggedRevs::InjectStyleAndJS';
	# Main hooks, overrides pages content, adds tags, sets tabs and permalink
	$wgHooks['SkinTemplateTabs'][] = array( $wgFlaggedArticle, 'setActionTabs' );
	# Update older, incomplete, page caches (ones that lack template Ids/image timestamps)
	$wgHooks['ArticleViewHeader'][] = array( $wgFlaggedArticle, 'maybeUpdateMainCache' );
	$wgHooks['ArticleViewHeader'][] = array( $wgFlaggedArticle, 'setPageContent' );
	$wgHooks['ArticleViewHeader'][] = array( $wgFlaggedArticle, 'addPatrolLink' );
	# Set image version
	$wgHooks['ArticleFromTitle'][] = array( $wgFlaggedArticle, 'setImageVersion' );
	# Add page notice
	$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink'][] = array( $wgFlaggedArticle, 'setPermaLink' );
	# Add tags do edit view
	$wgHooks['EditPage::showEditForm:initial'][] = array( $wgFlaggedArticle, 'addToEditView' );
	# Add review form
	$wgHooks['BeforePageDisplay'][] = array( $wgFlaggedArticle, 'addReviewForm' );
	$wgHooks['BeforePageDisplay'][] = array( $wgFlaggedArticle, 'addVisibilityLink' );
	# Mark of items in page history
	$wgHooks['PageHistoryLineEnding'][] = array( $wgFlaggedArticle, 'addToHistLine' );
	# Autopromote Editors
	$wgHooks['ArticleSaveComplete'][] = 'FlaggedRevs::autoPromoteUser';
	# Adds table link references to include ones from the stable version
	$wgHooks['LinksUpdateConstructed'][] = 'FlaggedRevs::extraLinksUpdate';
	# Empty flagged page settings row on delete
	$wgHooks['ArticleDeleteComplete'][] = 'FlaggedRevs::deleteVisiblitySettings';
	# Check on undelete/merge/revisiondelete for changes to stable version
	$wgHooks['ArticleUndelete'][] = 'FlaggedRevs::titleLinksUpdate';
	$wgHooks['ArticleRevisionVisiblitySet'][] = 'FlaggedRevs::titleLinksUpdate';
	$wgHooks['ArticleMergeComplete'][] = 'FlaggedRevs::updateFromMerge';
	# Clean up after undeletion
	$wgHooks['ArticleRevisionUndeleted'][] = 'FlaggedRevs::updateFromRestore';
	# Parser hooks, selects the desired images/templates
	$wgHooks['ParserClearState'][] = 'FlaggedRevs::parserAddFields';
	$wgHooks['BeforeParserrenderImageGallery'][] = 'FlaggedRevs::parserMakeGalleryStable';
	$wgHooks['BeforeGalleryFindFile'][] = 'FlaggedRevs::galleryFindStableFileTime';
	$wgHooks['BeforeParserFetchTemplateAndtitle'][] = 'FlaggedRevs::parserFetchStableTemplate';
	$wgHooks['BeforeParserMakeImageLinkObj'][] = 'FlaggedRevs::parserMakeStableImageLink';
	# Additional parser versioning
	$wgHooks['ParserAfterTidy'][] = 'FlaggedRevs::parserInjectTimestamps';
	$wgHooks['OutputPageParserOutput'][] = 'FlaggedRevs::outputInjectTimestamps';
	# Page review on edit
	$wgHooks['ArticleUpdateBeforeRedirect'][] = array( $wgFlaggedArticle, 'injectReviewDiffURLParams' );
	$wgHooks['DiffViewHeader'][] = array( $wgFlaggedArticle, 'addPatrolAndDiffLink' );
	$wgHooks['DiffViewHeader'][] = array( $wgFlaggedArticle, 'addDiffNoticeAndIncludes' );
	# Autoreview stuff
	$wgHooks['EditPage::showEditForm:fields'][] = array( $wgFlaggedArticle, 'addRevisionIDField' );
	$wgHooks['ArticleInsertComplete'][] = array( $wgFlaggedArticle, 'maybeMakeNewPageReviewed' );
	$wgHooks['ArticleSaveComplete'][] = array( $wgFlaggedArticle, 'maybeMakeEditReviewed' );
	$wgHooks['ArticleRollbackComplete'][] = array( $wgFlaggedArticle, 'maybeMakeRollbackReviewed' );
	$wgHooks['ArticleSaveComplete'][] = 'FlaggedRevs::autoMarkPatrolled';
	$wgHooks['RevisionInsertComplete'][] = 'FlaggedRevs::maybeMakeNullEditReviewed';
	# Disallow moves of stable pages
	$wgHooks['userCan'][] = 'FlaggedRevs::userCanMove';
	$wgHooks['userCan'][] = 'FlaggedRevs::userCanView';
	# Log parameter
	$wgHooks['LogLine'][] = 'FlaggedRevs::reviewLogLine';
	# Disable auto-promotion
	$wgHooks['UserRights'][] = 'FlaggedRevs::recordDemote';
	# Local user account preference
	$wgHooks['RenderPreferencesForm'][] = 'FlaggedRevs::injectPreferences';
	$wgHooks['InitPreferencesForm'][] = 'FlaggedRevs::injectFormPreferences';
	$wgHooks['ResetPreferences'][] = 'FlaggedRevs::resetPreferences';
	$wgHooks['SavePreferences'][] = 'FlaggedRevs::savePreferences';
	#########
}

# Add review log and such
$wgLogTypes[] = 'review';
$wgLogNames['review'] = 'review-logpage';
$wgLogHeaders['review'] = 'review-logpagetext';
$wgLogActions['review/approve']  = 'review-logentry-app';
$wgLogActions['review/approve2']  = 'review-logentry-app';
$wgLogActions['review/unapprove'] = 'review-logentry-dis';
$wgLogActions['review/unapprove2'] = 'review-logentry-dis';

$wgLogTypes[] = 'stable';
$wgLogNames['stable'] = 'stable-logpage';
$wgLogHeaders['stable'] = 'stable-logpagetext';
$wgLogActions['stable/config'] = 'stable-logentry';
$wgLogActions['stable/reset'] = 'stable-logentry2';

$wgLogActions['rights/erevoke']  = 'rights-editor-revoke';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'efFlaggedRevsSchemaUpdates';

function efFlaggedRevsSchemaUpdates() {
	global $wgDBtype, $wgExtNewFields, $wgExtPGNewFields, $wgExtNewIndexes, $wgExtNewTables;

	$base = dirname(__FILE__);
	if( $wgDBtype == 'mysql' ) {
		$wgExtNewFields[] = array( 'flaggedpage_config', 'fpc_expiry', "$base/archives/patch-fpc_expiry.sql" );
		$wgExtNewIndexes[] = array('flaggedpage_config', 'fpc_expiry', "$base/archives/patch-expiry-index.sql" );
		$wgExtNewTables[] = array( 'flaggedrevs_promote', "$base/archives/patch-flaggedrevs_promote.sql" );
	} else if( $wgDBtype == 'postgres' ) {
		$wgExtPGNewFields[] = array('flaggedpage_config', 'fpc_expiry', "TIMESTAMPTZ NULL" );
		$wgExtNewIndexes[] = array('flaggedpage_config', 'fpc_expiry', "$base/postgres/patch-expiry-index.sql" );
		$wgExtNewTables[] = array( 'flaggedrevs_promote', "$base/postgres/patch-flaggedrevs_promote.sql" );
	}

	return true;
}

class FlaggedRevs {
	public static $dimensions = array();
	public static $styleLoaded = false;

	public static function load() {
		global $wgFlaggedRevTags, $wgFlaggedRevValues;

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
		}
	}
	
	/**
	 * Get external storage array. Default to main storage.
	 */
	public static function getExternalStorage() {
		global $wgFlaggedRevsExternalStore, $wgDefaultExternalStore;
		
		$s = $wgFlaggedRevsExternalStore ? $wgFlaggedRevsExternalStore : $wgDefaultExternalStore;
		
		return $s;
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
		global $wgFlaggedRevComments;

		return $wgFlaggedRevComments;
	}

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
		global $wgParser;
		# Default options for anons if not logged in
		$options = new ParserOptions();
		# Make our hooks to trigger
		$wgParser->fr_isStable = true;
		$wgParser->fr_includesMatched = true;
		# Fix bad HTML
		$options->setTidy( true );
		# Don't show section-edit links, they can be old and misleading
		$options->setEditSection( $id==$article->getLatest() );
		# Parse the new body, wikitext -> html
		$title = $article->getTitle(); // avoid pass-by-reference error
	   	$parserOut = $wgParser->parse( $text, $title, $options, true, true, $id );
		$parserOut->fr_includesMatched = $wgParser->fr_includesMatched;
	   	# Done with parser!
	   	$wgParser->fr_isStable = false;
		$wgParser->fr_includesMatched = false;
		# Do we need to set the template uses via DB?
		if( $reparsed ) {
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select( array('flaggedtemplates', 'revision'), 
				array( 'ft_namespace', 'ft_title', 'ft_tmp_rev_id', 'rev_page' ),
				array( 'ft_rev_id' => $id, 'rev_id = ft_rev_id' ),
				__METHOD__ );
			# Add template metadata to output
			$maxTempID = 0;
			while( $row = $res->fetchObject() ) {
				if( !isset($parserOut->mTemplates[$row->ft_namespace]) ) {
					$parserOut->mTemplates[$row->ft_namespace] = array();
				}
				$parserOut->mTemplates[$row->ft_namespace][$row->ft_title] = $row->rev_page;

				if( !isset($parserOut->mTemplateIds[$row->ft_namespace]) ) {
					$parserOut->mTemplateIds[$row->ft_namespace] = array();
				}
				$parserOut->mTemplateIds[$row->ft_namespace][$row->ft_title] = $row->ft_tmp_rev_id;
				if( $row->ft_tmp_rev_id > $maxTempID ) {
					$maxTempID = $row->ft_tmp_rev_id;
				}
			}
			$parserOut->fr_newestTemplateID = $maxTempID;
		}
	   	return $parserOut;
	}
	
	/**
	* @param FlaggedRevision $frev
	* @param Article $article
	* @param ParserOutput $flaggedOutput, will fetch if not given
	* @param ParserOutput $currentOutput, will fetch if not given
	* @return bool
	* See if a flagged revision is synced with the current
	*/	
	public static function flaggedRevIsSynced( $frev, $article, $flaggedOutput=null, $currentOutput=null ) {
		# Must be the same revision
		if( $frev->getRevId() != $article->getLatest() ) {
			return false;
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
		if( is_null($flaggedOutput) || !isset($flaggedOutput->fr_newestTemplateID) ) {
			# Get parsed stable version
			$flaggedOutput = FlaggedRevs::getPageCache( $article );
			if( $flaggedOutput==false ) {
				global $wgUseStableTemplates;
				if( $wgUseStableTemplates ) {
					$rev = Revision::newFromId( $frev->getRevId() );
					$text = $rev->getText();
				} else {
					$text = $frev->getText();
				}
       			$flaggedOutput = FlaggedRevs::parseStableText( $article, $text, $frev->getRevId() );
       			# Update the stable version cache
       			FlaggedRevs::updatePageCache( $article, $flaggedOutput );
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
				$currentOutput = $wgParser->parse( $text, $title, ParserOptions::newFromUser($wgUser) );
				# Might as well save the cache while we're at it
				$parserCache->save( $currentOutput, $article, $wgUser );
			}
		}
		# Only current of revisions of inclusions can be reviewed. Since the stable and current revisions
		# have the same text, the only thing that can make them different is updating a template or image.
		# If this is the case, the current revision will have a newer template or image version used somewhere. 
		if( $currentOutput->fr_newestImageTime > $flaggedOutput->fr_newestImageTime ) {
			$synced = false;
		} else if( $currentOutput->fr_newestTemplateID > $flaggedOutput->fr_newestTemplateID ) {
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
	* @param string $text
	* @return string, flags
	* Compress pre-processed text, passed by reference
	*/
	public static function compressText( &$text ) {
		global $wgCompressRevisions;
		$flags = array( 'utf-8' );
		if( $wgCompressRevisions ) {
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
		if( !is_array($flags) ) {
			$flags = explode( ',', $flags );
		}
		# Lets not mix up types here
		if( is_null($text) )
			return null;
		if( $text !== false && in_array( 'gzip', $flags ) ) {
			# Deal with optional compression if $wgCompressRevisions is set.
			$text = gzinflate( $text );
		}
		return $text;
	}
	
	/**
	* Get a validation key from versioning metadata
	* @param string $tmpP
	* @param string $imgP
	* @param integer $uid user ID
	* @param integer $rid rev ID
	* @return string
	*/
	public static function getValidationKey( $tmpP, $imgP, $uid, $rid ) {
		global $wgReviewCodes;
		$p = MD5($uid.$imgP.$wgReviewCodes[0]).sha1($wgReviewCodes[1].$rid.$wgReviewCodes[3]).MD5($uid.$tmpP.$wgReviewCodes[2]);
		return $p;
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

	/**
	 * @param Title $title
	 * @param int $rev_id
	 * @param bool $getText, fetch fr_text and fr_flags too?
	 * @returns mixed FlaggedRevision (null on failure)
	 * Will not return a revision if deleted
	 */
	public static function getFlaggedRev( $title, $rev_id, $getText=false ) {
		$columns = array('fr_rev_id','fr_page_id','fr_user','fr_timestamp','fr_comment','fr_quality','fr_tags');
		if( $getText ) {
			$columns[] = 'fr_text';
			$columns[] = 'fr_flags';
		}
		$dbr = wfGetDB( DB_SLAVE );
		# Skip deleted revisions
		$row = $dbr->selectRow( array('flaggedrevs','revision'),
			$columns,
			array( 'fr_page_id' => $title->getArticleID(),
				'fr_rev_id' => $rev_id, 'fr_rev_id = rev_id',
				'rev_deleted & '.Revision::DELETED_TEXT => 0 ),
			__METHOD__ );
		# Sorted from highest to lowest, so just take the first one if any
		if( $row ) {
			return new FlaggedRevision( $title, $row );
		}

		return null;
	}
	
	/**
	 * @param Title $title
	 * @param int $rev_id
	 * @param Database $db, optional
	 * @returns mixed (int or false)
	 */
	public static function getRevQuality( $title, $rev_id, $db = NULL ) {
		$db = $db ? $db : wfGetDB( DB_SLAVE );
		$quality = $db->selectField( 'flaggedrevs', 'fr_quality',
			array( 'fr_page_id' => $title->getArticleID(),
				'fr_rev_id' => $rev_id ),
			__METHOD__,
			array( 'FORCE INDEX' => 'PRIMARY' ) );
		return $quality;
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

	/**
	 * Get latest quality rev, if not, the latest reviewed one.
	 * @param Title $title, page title
	 * @param bool $getText, fetch fr_text and fr_flags too?
	 * @param bool $forUpdate, use master DB and avoid using page_ext_stable?
	 * @returns mixed FlaggedRevision (null on failure)
	 */
	public static function getStablePageRev( $title, $getText=false, $forUpdate=false ) {
		$columns = array('fr_rev_id','fr_page_id','fr_user','fr_timestamp','fr_comment','fr_quality','fr_tags');
		if( $getText ) {
			$columns[] = 'fr_text';
			$columns[] = 'fr_flags';
		}
		$row = null;
		# If we want the text, then get the text flags too
		if( !$forUpdate ) {
			$dbr = wfGetDB( DB_SLAVE );
			$row = $dbr->selectRow( array('page','flaggedrevs'),
				$columns,
				array( 'page_namespace' => $title->getNamespace(),
					'page_title' => $title->getDBkey(),
					'page_ext_stable = fr_rev_id' ),
				__METHOD__  );
			if( !$row )
				return null;
		} else {
			# Get visiblity settings...
			$config = self::getPageVisibilitySettings( $title, $forUpdate );
			$dbw = wfGetDB( DB_MASTER );
			# Look for the latest quality revision
			if( $config['select'] != FLAGGED_VIS_LATEST ) {
				$row = $dbw->selectRow( array('flaggedrevs','revision'),
					$columns,
					array( 'fr_page_id' => $title->getArticleID(),
						'fr_quality >= 1',
						'fr_rev_id = rev_id',
						'rev_deleted & '.Revision::DELETED_TEXT => 0),
					__METHOD__,
					array( 'ORDER BY' => 'fr_rev_id DESC') );
				$row = $row ? $row : null;
			}
			# Do we have one? If not, try the latest reviewed revision...
			if( !$row ) {
				$row = $dbw->selectRow( array('flaggedrevs','revision'),
					$columns,
					array( 'fr_page_id' => $title->getArticleID(),
						'fr_rev_id = rev_id',
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
	 * @param Article $article
	 * @param int $from_rev
	 * @return int
	 * Get number of revs since a certain revision
	 */
	public static function getRevCountSince( $article, $from_rev ) {
		# Check if the count is zero by using $article->getLatest().
		# I don't trust using memcache and PHP for values like '0'
		# as it may confuse "expired" with "0". -aaron
		if( $article->getLatest()==$from_rev ) {
			return 0;
		}
		global $wgMemc;
		# Try the cache
		$key = wfMemcKey( 'flaggedrevs', 'unreviewedrevs', $article->getId() );
		$count = intval( $wgMemc->get($key) );

		if( !$count ) {
			$dbr = wfGetDB( DB_SLAVE );
			$count = $dbr->selectField('revision', 'COUNT(*)',
				array('rev_page' => $article->getId(), "rev_id > " . intval($from_rev) ),
				__METHOD__ );
			# Save to cache
			$wgMemc->set( $key, $count, 3600*24*7 );
		}

		return $count;
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
			$select = $wgFlaggedRevsPrecedence ? 0 : 1;
			return array('select' => $select, 'override' => $override, 'expiry' => 'infinity');
		}

		return array('select' => $row->fpc_select, 'override' => $row->fpc_override, 'expiry' => $row->fpc_expiry);
	}

	/**
	 * Get flags for a revision
	 * @param string $tags
	 * @return Array
	*/
	public static function expandRevisionTags( $tags ) {
		# Set all flags to zero
		$flags = array();
		foreach( self::$dimensions as $tag => $levels ) {
			$flags[$tag] = 0;
		}
		$tags = explode('\n',$tags);
		foreach( $tags as $tuple ) {
			$set = explode(':',$tuple,2);
			if( count($set) == 2 ) {
				list($tag,$value) = $set;
				$value = intval($value);
				# Add only currently recognized ones
				if( isset($flags[$tag]) ) {
					# If a level was removed, default to the highest
					$flags[$tag] = $value < count($levels) ? $value : count($levels)-1;
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
		$tags = $dbr->selectField( 'flaggedrevs', 'fr_tags',
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
		# Treat NS_MEDIA as NS_IMAGE
		$ns = ( $title->getNamespace() == NS_MEDIA ) ? NS_IMAGE : $title->getNamespace();
		return ( in_array($ns,$wgFlaggedRevsNamespaces) && !$title->isTalkPage() );
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
	* Add FlaggedRevs css/js. Attached to two different hooks, neglect inputs.
	*/
	public static function InjectStyleAndJS( $a=false, $b=false ) {
		global $wgOut, $wgJsMimeType, $wgFlaggedArticle;
		# Don't double-load
		if( self::$styleLoaded )
			return true;
		# UI CSS
		$wgOut->addLink( array(
			'rel'	=> 'stylesheet',
			'type'	=> 'text/css',
			'media'	=> 'screen, projection',
			'href'	=> FLAGGED_CSS,
		) );
		# Handle onload parameters
		$JSparams = self::getJSParams();
		if( $JSparams ) {
			$wgOut->addScript( "<script type=\"{$wgJsMimeType}\">var wgFlaggedRevsJSparams = {". $JSparams . "};</script>\n" );
		}
		# UI JS
		$wgOut->addScript( "<script type=\"{$wgJsMimeType}\" src=\"" . FLAGGED_JS . "\"></script>\n" );

		self::$styleLoaded = true;

		return true;
	}
	
	/**
	 * Get JS script params for onloading
	 */
	public function getJSParams() {
		# Param to pass to JS function to know if tags are at quality level
		global $wgFlaggedRevTags;
		$JSparams = '';
		foreach( $wgFlaggedRevTags as $tag => $QL ) {
			$valuepair = ($JSparams) ? ", \"wp$tag\": $QL" : "\"wp$tag\": $QL";
			$JSparams .= $valuepair;
		}
		return trim($JSparams);
	}

	/**
	* Automatically review an edit and add a log entry in the review log.
	* LinksUpdate was already called via edit operations, so the page
	* fields will be up to date. This updates the stable version.
	*/
	public static function autoReviewEdit( $article, $user, $text, $rev, $flags ) {
		global $wgParser, $wgFlaggedRevsAutoReview;

		wfProfileIn( __METHOD__ );

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
		list($fulltext,$templateIDs,$complete,$maxID) = self::expandText( $text, $article->getTitle(), $rev->getId() );

		# Compress $fulltext, passed by reference
		$textFlags = self::compressText( $fulltext );

		# Write to external storage if required
		$storage = FlaggedRevs::getExternalStorage();
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

		# Our review entry
		$revisionset = array(
			'fr_page_id'   => $rev->getPage(),
			'fr_rev_id'	   => $rev->getId(),
			'fr_user'	   => $user->getId(),
			'fr_timestamp' => $dbw->timestamp( wfTimestampNow() ),
			'fr_comment'   => "",
			'fr_quality'   => $quality,
			'fr_tags'	   => self::flattenRevisionTags( $flags ),
			'fr_text'	   => $fulltext, # Store expanded text for speed
			'fr_flags'	   => $textFlags
		);
		# Update flagged revisions table
		$dbw->replace( 'flaggedrevs',
			array( array('fr_page_id','fr_rev_id') ), $revisionset,
			__METHOD__ );
		# Mark as patrolled
		$dbw->update( 'recentchanges',
			array( 'rc_patrolled' => 1 ),
			array( 'rc_this_oldid' => $rev->getId(),
				'rc_user_text' => $rev->getRawUserText(),
				'rc_timestamp' => $dbw->timestamp( $rev->getTimestamp() ) ),
			__METHOD__,
			array( 'LIMIT' => 1 ) );
		$dbw->commit();

		# Update the article review log
		RevisionReview::updateLog( $article->getTitle(), $flags, array(), wfMsg('revreview-auto'), $rev->getID(), true );

		# If we know that this is now the new stable version 
		# (which it probably is), save it to the cache...
		$sv = self::getStablePageRev( $article->getTitle(), false, true );
		if( $sv && $sv->getRevId() == $rev->getId() ) {
			# Update stable cache
			FlaggedRevs::updatePageCache( $article, $poutput );
			# Update page fields
			self::updateArticleOn( $article, $rev->getID() );
			# Purge squid for this page only
			$article->getTitle()->purgeSquid();
		}
		
		wfProfileOut( __METHOD__ );

		return true;
	}

 	/**
	* @param Article $article
	* @param Integer $rev_id, the stable version rev_id
	* Updates the page_ext_stable and page_ext_reviewed fields
	*/
	public static function updateArticleOn( $article, $rev_id ) {
		global $wgMemc;

		wfProfileIn( __METHOD__ );

		$dbw = wfGetDB( DB_MASTER );
		# Get the highest quality revision (not necessarily this one).
		$maxQuality = $dbw->selectField( array('flaggedrevs','revision'),
			'fr_quality',
			array( 'fr_page_id' => $article->getTitle()->getArticleID(),
				'fr_rev_id = rev_id',
				'rev_deleted & '.Revision::DELETED_TEXT => 0 ),
			__METHOD__,
			array( 'ORDER BY' => 'fr_quality DESC', 'LIMIT' => 1 ) );
		$maxQuality = $maxQuality===false ? null : $maxQuality;
		# Alter table metadata
		$dbw->update( 'page',
			array( 'page_ext_stable' => $rev_id,
				'page_ext_reviewed' => ($article->getLatest() == $rev_id) ? 1 : 0,
				'page_ext_quality' => $maxQuality ),
			array( 'page_namespace' => $article->getTitle()->getNamespace(),
				'page_title' => $article->getTitle()->getDBkey() ),
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
	public static function titleLinksUpdate( $title, $a=null, $b=null ) {
		return self::articleLinksUpdate( new Article( $title ), $a, $b );
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
			if( $wgUseStableTemplates ) {
				$rev = Revision::newFromId( $sv->getRevId() );
				$text = $rev->getText();
			} else {
				$text = $sv->getText();
			}
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
		$ill = $parserOut->getLanguageLinks();
		foreach( $ill as $link ) {
			list( $key, $title ) = explode( ':', $link, 2 );
			$linksUpdate->mInterlangs[$key] = $title;
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
			$id = $dbw->selectField( 'page', 'page_ext_stable',
				array( 'page_namespace' => $title->getNamespace(),
					'page_title' => $title->getDBkey() ),
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
	public static function parserMakeStableImageLink( $parser, $nt, &$skip, &$time ) {
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
			$row = $dbw->selectRow( array('page', 'flaggedimages'),
				array( 'fi_img_timestamp', 'fi_img_sha1' ),
				array( 'page_namespace' => NS_IMAGE,
					'page_title' => $nt->getDBkey(),
					'page_ext_stable = fi_rev_id',
					'fi_name' => $nt->getDBkey() ),
				__METHOD__ );
			$time = $row ? $row->fi_img_timestamp : $time;
			$sha1 = $row ? $row->fi_img_sha1 : $sha1;
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
	public static function galleryFindStableFileTime( $ig, $nt, &$time ) {
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
			$row = $dbw->selectRow( array('page', 'flaggedimages'),
				array( 'fi_img_timestamp', 'fi_img_sha1' ),
				array( 'page_namespace' => NS_IMAGE,
					'page_title' => $nt->getDBkey(),
					'page_ext_stable = fi_rev_id',
					'fi_name' => $nt->getDBkey() ),
				__METHOD__ );
			$time = $row ? $row->fi_img_timestamp : $time;
			$sha1 = $row ? $row->fi_img_sha1 : $sha1;
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
		global $wgTitle;
	
		if( $action != 'move' || !self::isPageReviewable( $title ) )
			return true;
		# See if there is a stable version
		if( $wgTitle && $wgTitle->equals( $title ) ) {
			global $wgFlaggedArticle;
			// Cache stable version while we are at it.
			$frev = $wgFlaggedArticle->getStableRev( true );
		} else {
			$frev = self::getStablePageRev( $title );
		}
		if( !$frev )
			return true;
		# Allow for only editors/reviewers to move this
		$right = $frev->getQuality() ? 'validate' : 'review';
		if( !$user->isAllowed( $right ) ) {
			$result = false;
			return false;
		}
		return true;
	}
	
	/**
	* Allow users to view reviewed pages.
	*/
	public static function userCanView( $title, $user, $action, $result ) {
		global $wgFlaggedRevsVisible, $wgTitle;
		# Assume $action may still not be set, in which case, treat it as 'view'...
		if( $action != 'read' )
			return true;
		# Admin may set this to false, rather than array()...
		$groups = $user->getGroups();
		$groups[] = '*';
		if( empty($wgFlaggedRevsVisible) || !array_intersect($groups,$wgFlaggedRevsVisible) )
			return true;
		# See if there is a stable version. Also, see if, given the page 
		# config and URL params, the page can be overriden.
		if( $wgTitle && $wgTitle->equals( $title ) ) {
			global $wgFlaggedArticle;
			// Cache stable version while we are at it.
			if( $wgFlaggedArticle->pageOverride() && $wgFlaggedArticle->getStableRev( true ) ) {
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
	* When a null edit is made, autoreview if necessary
	*/
	public static function maybeMakeNullEditReviewed( $rev ) {
		$title = $rev->getTitle();
		# GetTitle() for revisions uses slaves and wants page_id,rev_id to
		# match...this is bad if we *just* added it.
		$title = $title ? $title : Title::newFromID( $rev->getPage() );
		if( !$title || !self::isPageReviewable( $title ) ) {
			return true;
		}
		# page_latest has not been updated yet...
		$prevRev = Revision::newFromTitle( $title );
		# Check for null edits
		if( $prevRev && $prevRev->getTextId() == $rev->getTextId() ) {
			$frev = FlaggedRevs::getFlaggedRev( $title, $prevRev->getId() );
			if( !is_null($frev) ) {
				$article = new Article( $title );
				$flags = $frev->getTags();
				# Check if user is allowed to renew the stable version.
				if( !RevisionReview::userCanSetFlags( $flags ) ) {
					# Assume basic flagging level
					$flags = array();
					foreach( FlaggedRevs::$dimensions as $tag => $minQL ) {
						$flags[$tag] = 1;
					}
				}
				$user = User::newFromId( $rev->getUser() );
				FlaggedRevs::autoReviewEdit( $article, $user, $rev->getText(), $rev, $flags );
			}
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

		if( !self::isPageReviewable( $article->getTitle() ) && $user->isAllowed('autopatrolother') ) {
			$dbw = wfGetDB( DB_MASTER );
			$dbw->update( 'recentchanges',
				array( 'rc_patrolled' => 1 ),
				array( 'rc_this_oldid' => $rev->getID(),
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

		if( empty($wgFlaggedRevsAutopromote) )
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
		# A special entry is made in the log whenever an editor looses their rights.
		$p = self::getUserParams( $user );
		if( isset($params['demoted']) && $params['demoted'] ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Update any special counters for non-null revisions
		$changed = false;
		$pages = array();
		$p['uniqueContentPages'] = isset($p['uniqueContentPages']) ? $p['uniqueContentPages'] : '';
		$p['totalContentEdits'] = isset($p['totalContentEdits']) ? $p['totalContentEdits'] : 0;
		$p['editComments'] = isset($p['editComments']) ? $p['editComments'] : 0;
		if( $rev && $article->getTitle()->isContentPage() ) {
			$pages = explode( ',', trim($p['uniqueContentPages']) ); // page IDs
			# Don't let this get bloated for no reason
			if( count($pages) < $wgFlaggedRevsAutopromote['uniqueContentPages'] && !in_array($article->getId(),$pages) ) {
				$pages[] = $article->getId();
				$p['uniqueContentPages'] = implode(',',$pages);
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
		$usercreation = wfTimestamp(TS_UNIX,$user->mRegistration);
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
		# See if the page actually has sufficient content...
		if( $wgFlaggedRevsAutopromote['userpage'] ) {
			if( !$user->getUserPage()->exists() ) {
				wfProfileOut( __METHOD__ );
				return true;
			}
			$dbr = wfGetDB( DB_SLAVE );
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
				$key = wfMemcKey( 'flaggedrevs', 'autopromote-skip', $user->getID() );
				$wgMemc->set( $key, 'true', 3600*24*$spacing($benchmarks - $needed - 1) );
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
				$key = wfMemcKey( 'flaggedrevs', 'autopromote-skip', $user->getID() );
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
				$key = wfMemcKey( 'flaggedrevs', 'autopromote-skip', $user->getID() );
				$wgMemc->set( $key, 'true', 3600*24*7 );
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Check if the user has any recent content edits
		if( $wgFlaggedRevsAutopromote['recentContentEdits'] > 0 ) {
			global $wgContentNamespaces;
		
			$dbr = isset($dbr) ? $dbr : wfGetDB( DB_SLAVE );
			$dbr->select( 'recentchanges', '1', 
				array( 'rc_user_text' => $user->getName(),
					'rc_namespace' => $wgContentNamespaces ), 
				__METHOD__, 
				array( 'USE INDEX' => 'rc_ns_usertext', 
					'LIMIT' => $wgFlaggedRevsAutopromote['recentContent'] ) );
			if( $dbr->numRows() >= $wgFlaggedRevsAutopromote['recentContent'] ) {
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
				$dbr->select( 'archive', '1', 
					array( 'ar_user_text' => $user->getName() ), 
					__METHOD__, 
					array( 'USE INDEX' => 'usertext_timestamp', 'LIMIT' => $minDiff ) );
				$deletedEdits = $dbr->numRows();
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
	* Get params for a user
	* @param User $user
	*/
	public static function getUserParams( $user ) {
		$dbw = wfGetDB( DB_MASTER );
		$row = $dbw->selectRow( 'flaggedrevs_promote', 'frp_user_params',
			array( 'frp_user_id' => $user->getId() ),
			__METHOD__ );
			
		$params = array();
		if( $row ) {
			$flatPars = explode( '\n', trim($row->frp_user_params) );
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
		$flatParams = trim($flatParams);
	
		$dbw = wfGetDB( DB_MASTER );
		$row = $dbw->replace( 'flaggedrevs_promote', 
			array( 'frp_user_id' ),
			array( 'frp_user_id' => $user->getId(), 
				'frp_user_params' => $flatParams ),
			__METHOD__ );

		return ( $dbw->affectedRows() > 0 );
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
		global $wgUser;
	
		$out->addHTML( 
			Xml::openElement( 'fieldset' ) .
			Xml::element( 'legend', null, wfMsgHtml('flaggedrevs-prefs') ) .
			Xml::openElement( 'table' ) . 
			Xml::openElement( 'tr' ) .
				'<td>' . wfCheck( 'wpFlaggedRevsStable', $form->mFlaggedRevsStable, 
					array('id' => 'wpFlaggedRevsStable') ) . '</td><td> ' .
					wfLabel( wfMsg( 'flaggedrevs-prefs-stable' ), 'wpFlaggedRevsStable' ) . '</td>' .
			Xml::closeElement( 'tr' ) .
			Xml::openElement( 'tr' ) .
				'<td>' .
					Xml::radio( 'wpFlaggedRevsSUI', 0, $form->mFlaggedRevsSUI==0, array('id' => 'standardUI') ) .
				'</td><td> ' .
					Xml::label( wfMsgHtml('flaggedrevs-pref-UI-0'), 'standardUI' ) .
				'</td>' .
			Xml::closeElement( 'tr' ) . 
			Xml::openElement( 'tr' ) .
				'<td>' .
					Xml::radio( 'wpFlaggedRevsSUI', 1, $form->mFlaggedRevsSUI==1, array('id' => 'simpleUI') ) .
				'</td><td> ' .
					Xml::label( wfMsgHtml('flaggedrevs-pref-UI-1'), 'simpleUI' ) .
				'</td>'
		);
		if( $wgUser->isAllowed( 'review' ) ) {
			$out->addHTML( 
				Xml::closeElement( 'tr' ) . 
				Xml::openElement( 'tr' ) . '<td><br/></td>' . Xml::closeElement( 'tr' ) .
				Xml::openElement( 'tr' ) .
					'<td>' . wfCheck( 'wpFlaggedRevsWatch', $form->mFlaggedRevsWatch, array('id' => 'wpFlaggedRevsWatch') ) . 
					'</td><td> ' . wfLabel( wfMsg( 'flaggedrevs-prefs-watch' ), 'wpFlaggedRevsWatch' ) . '</td>'
			);
		}
		$out->addHTML( 
			Xml::closeElement( 'tr' ) . 
			Xml::closeElement( 'table' ) .
			Xml::closeElement( 'fieldset' )
		);

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
	* Get a selector of reviewable namespaces
	* @param int $selected, namespace selected
	*/
	public static function getNamespaceMenu( $selected=null ) {
		global $wgContLang, $wgFlaggedRevsNamespaces;

		$selector = "<label for='namespace'>" . wfMsgHtml('namespace') . "</label>";
		if( $selected !== '' ) {
			if( is_null( $selected ) ) {
				# No namespace selected; let exact match work without hitting Main
				$selected = '';
			} else {
				# Let input be numeric strings without breaking the empty match.
				$selected = intval($selected);
			}
		}
		$s = "\n<select id='namespace' name='namespace' class='namespaceselector'>\n";
		$arr = $wgContLang->getFormattedNamespaces();

		foreach( $arr as $index => $name ) {
			# Content only
			if($index < NS_MAIN || !in_array($index, $wgFlaggedRevsNamespaces) )
				continue;

			$name = $index !== 0 ? $name : wfMsg('blanknamespace');

			if($index === $selected) {
				$s .= "\t" . Xml::element("option", array("value" => $index, "selected" => "selected"), $name) . "\n";
			} else {
				$s .= "\t" . Xml::element("option", array("value" => $index), $name) . "\n";
			}
		}
		$s .= "</select>\n";
		return $s;
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
}


