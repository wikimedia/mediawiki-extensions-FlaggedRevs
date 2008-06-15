<?php
#(c) Aaron Schulz, Joerg Baach, 2007-2008 GPL

if( !defined('MEDIAWIKI') ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

# This messes with dump HTML...
if( defined('MW_HTML_FOR_DUMP') ) {
	return;
}

# Quality -> Sighted (default)
if( !defined('FLAGGED_VIS_NORMAL') )
	define('FLAGGED_VIS_NORMAL',0);
# No precedence
if( !defined('FLAGGED_VIS_LATEST') )
	define('FLAGGED_VIS_LATEST',1);
# Pristine -> Quality -> Sighted
if( !defined('FLAGGED_VIS_PRISTINE') )
	define('FLAGGED_VIS_PRISTINE',2);

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Flagged Revisions',
	'author' => array( 'Aaron Schulz', 'Joerg Baach' ),
	'version' => '1.08',
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
# Patrollable namespaces
$wgFlaggedRevsPatrolNamespaces = array( NS_CATEGORY, NS_IMAGE, NS_TEMPLATE );

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

# Can users make comments that will show up below flagged revisions?
$wgFlaggedRevsComments = false;
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
# A revision with all tags rated at least to this level is considered "pristine"/"featured"
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
$wgReviewCodes = array();

# URL location for flaggedrevs.css and flaggedrevs.js
# Use a literal $wgScriptPath as a placeholder for the runtime value of $wgScriptPath
$wgFlaggedRevsStylePath = '$wgScriptPath/extensions/FlaggedRevs';

# Lets some users access the review UI and set some flags
$wgAvailableRights[] = 'review';
# Let some users set higher settings
$wgAvailableRights[] = 'validate';
$wgAvailableRights[] = 'autoreview';
$wgAvailableRights[] = 'patrolmarks';
$wgAvailableRights[] = 'autopatrolother';
$wgAvailableRights[] = 'unreviewedpages';

# Define our basic reviewer class
$wgGroupPermissions['editor']['review']          = true;
$wgGroupPermissions['editor']['autoreview']      = true;
$wgGroupPermissions['editor']['autoconfirmed']   = true;
$wgGroupPermissions['editor']['patrolmarks']     = true;
$wgGroupPermissions['editor']['autopatrolother'] = true;
$wgGroupPermissions['editor']['unreviewedpages'] = true;

# Defines extra rights for advanced reviewer class
$wgGroupPermissions['reviewer']['validate'] = true;
# Let this stand alone just in case...
$wgGroupPermissions['reviewer']['review'] = true;

$wgGroupPermissions['bot']['autoreview'] = true;

# Stable version selection and default page revision selection can be set per page.
$wgGroupPermissions['sysop']['stablesettings'] = true;
# Sysops can always move stable pages
$wgGroupPermissions['sysop']['movestable'] = true;

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
	'neverBlocked'        => true, # Can users that were blocked be promoted?
	'noSorbsMatches'      => false, # If $wgSorbsUrl is set, do not promote users that match
);

# Special:Userrights settings
## Basic rights for Sysops
$wgAddGroups['sysop'][] = 'editor';
$wgRemoveGroups['sysop'][] = 'editor';
## Extra ones for Bureaucrats
## Add UI page rights just in case we have non-sysop bcrats
$wgAddGroups['bureaucrat'][] = 'reviewer';
$wgRemoveGroups['bureaucrat'][] = 'reviewer';

# If you want to use a storage group specifically for this
# software, set this array
$wgFlaggedRevsExternalStore = false;

# Show reviews in recentchanges? Disabled by default, often spammy...
$wgFlaggedRevsLogInRC = false;

# How far the logs for overseeing quality revisions and depreciations go
$wgFlaggedRevsOversightAge = 7 * 24 * 3600;

# How many hours pending review is considering long?
$wgFlaggedRevsLongPending = array( 3, 12, 24 );
# How many pages count as a backlog?
$wgFlaggedRevsBacklog = 1000;

# Flagged revisions are always visible to users with rights below.
# Use '*' for non-user accounts.
$wgFlaggedRevsVisible = array();
$wgFlaggedRevsTalkVisible = true;

# End of configuration variables.
#########

# Bump this number every time you change flaggedrevs.css/flaggedrevs.js
$wgFlaggedRevStyleVersion = 25;

$wgExtensionFunctions[] = 'efLoadFlaggedRevs';

$dir = dirname(__FILE__) . '/';
$langDir = dirname(__FILE__) . '/language/';

$wgAutoloadClasses['FlaggedRevs'] = $dir.'FlaggedRevs.class.php';
$wgExtensionMessagesFiles['FlaggedRevs'] = $langDir . 'FlaggedRevs.i18n.php';
$wgExtensionMessagesFiles['FlaggedRevsAliases'] = $langDir . 'FlaggedRevsAliases.i18n.php';

# Load general UI
$wgAutoloadClasses['FlaggedRevsXML'] = $dir . 'FlaggedRevsXML.php';
# Load context article stuff
$wgAutoloadClasses['FlaggedArticle'] = $dir . 'FlaggedArticle.php';
# Load FlaggedRevision object class
$wgAutoloadClasses['FlaggedRevision'] = $dir . 'FlaggedRevision.php';
# Load review UI
$wgSpecialPages['RevisionReview'] = 'RevisionReview';
$wgAutoloadClasses['RevisionReview'] = $dir . 'FlaggedRevsPage.php';

# Load stableversions UI
$wgSpecialPages['StableVersions'] = 'StableVersions';
$wgAutoloadClasses['StableVersions'] = $dir . '/specialpages/StableVersions_body.php';
$wgExtensionMessagesFiles['StableVersions'] = $langDir . 'StableVersions.i18n.php';
# Stable version config
$wgSpecialPages['Stabilization'] = 'Stabilization';
$wgAutoloadClasses['Stabilization'] = $dir . '/specialpages/Stabilization_body.php';
$wgExtensionMessagesFiles['Stabilization'] = $langDir . 'Stabilization.i18n.php';
# Load unreviewed pages list
$wgSpecialPages['UnreviewedPages'] = 'UnreviewedPages';
$wgAutoloadClasses['UnreviewedPages'] = $dir . '/specialpages/UnreviewedPages_body.php';
$wgExtensionMessagesFiles['UnreviewedPages'] = $langDir . 'UnreviewedPages.i18n.php';
$wgSpecialPageGroups['UnreviewedPages'] = 'maintenance';
# Load "in need of re-review" pages list
$wgSpecialPages['OldReviewedPages'] = 'OldReviewedPages';
$wgAutoloadClasses['OldReviewedPages'] = $dir . '/specialpages/OldReviewedPages_body.php';
$wgExtensionMessagesFiles['OldReviewedPages'] = $langDir . 'OldReviewedPages.i18n.php';
$wgSpecialPageGroups['OldReviewedPages'] = 'maintenance';
# Load reviewed pages list
$wgSpecialPages['ReviewedPages'] = 'ReviewedPages';
$wgAutoloadClasses['ReviewedPages'] = $dir . '/specialpages/ReviewedPages_body.php';
$wgExtensionMessagesFiles['ReviewedPages'] = $langDir . 'ReviewedPages.i18n.php';
$wgSpecialPageGroups['ReviewedPages'] = 'quality';
# Load stable pages list
$wgSpecialPages['StablePages'] = 'StablePages';
$wgAutoloadClasses['StablePages'] = $dir . '/specialpages/StablePages_body.php';
$wgExtensionMessagesFiles['StablePages'] = $langDir . 'StablePages.i18n.php';
$wgSpecialPageGroups['StablePages'] = 'quality';
# To oversee quality revisions
$wgSpecialPages['QualityOversight'] = 'QualityOversight';
$wgAutoloadClasses['QualityOversight'] = $dir . '/specialpages/QualityOversight_body.php';
$wgExtensionMessagesFiles['QualityOversight'] = $langDir . 'QualityOversight.i18n.php';
$wgSpecialPageGroups['QualityOversight'] = 'quality';
# To oversee depreciations
$wgSpecialPages['DepreciationOversight'] = 'DepreciationOversight';
$wgAutoloadClasses['DepreciationOversight'] = $dir . '/specialpages/DepreciationOversight_body.php';
$wgExtensionMessagesFiles['DepreciationOversight'] = $langDir . 'DepreciationOversight.i18n.php';
$wgSpecialPageGroups['DepreciationOversight'] = 'quality';

######### Hook attachments #########
# Remove stand-alone patrolling
$wgHooks['UserGetRights'][] = 'FlaggedRevs::stripPatrolRights';

# Autopromote Editors
$wgHooks['ArticleSaveComplete'][] = 'FlaggedRevs::autoPromoteUser';
# Adds table link references to include ones from the stable version
$wgHooks['LinksUpdateConstructed'][] = 'FlaggedRevs::extraLinksUpdate';
# Empty flagged page settings row on delete
$wgHooks['ArticleDeleteComplete'][] = 'FlaggedRevs::deleteVisiblitySettings';
# Check on undelete/merge/revisiondelete for changes to stable version
$wgHooks['ArticleRevisionVisiblitySet'][] = 'FlaggedRevs::titleLinksUpdate';
$wgHooks['ArticleMergeComplete'][] = 'FlaggedRevs::updateFromMerge';
# Clean up after undeletion
$wgHooks['ArticleRevisionUndeleted'][] = 'FlaggedRevs::updateFromRestore';
# Parser hooks, selects the desired images/templates
$wgHooks['ParserClearState'][] = 'FlaggedRevs::parserAddFields';
$wgHooks['BeforeGalleryFindFile'][] = 'FlaggedRevs::galleryFindStableFileTime';
$wgHooks['BeforeParserFetchTemplateAndtitle'][] = 'FlaggedRevs::parserFetchStableTemplate';
$wgHooks['BeforeParserMakeImageLinkObj'][] = 'FlaggedRevs::parserMakeStableImageLink';
# Additional parser versioning
$wgHooks['ParserAfterTidy'][] = 'FlaggedRevs::parserInjectTimestamps';
$wgHooks['OutputPageParserOutput'][] = 'FlaggedRevs::outputInjectTimestamps';
# Auto-reviewing
$wgHooks['ArticleSaveComplete'][] = 'FlaggedRevs::autoMarkPatrolled';
$wgHooks['NewRevisionFromEditComplete'][] = 'FlaggedRevs::maybeMakeEditReviewed';
# Disallow moves of stable pages
$wgHooks['userCan'][] = 'FlaggedRevs::userCanMove';
# Log parameter
$wgHooks['LogLine'][] = 'FlaggedRevs::reviewLogLine';
# Disable auto-promotion
$wgHooks['UserRights'][] = 'FlaggedRevs::recordDemote';
# Local user account preference
$wgHooks['RenderPreferencesForm'][] = 'FlaggedRevs::injectPreferences';
$wgHooks['InitPreferencesForm'][] = 'FlaggedRevs::injectFormPreferences';
$wgHooks['ResetPreferences'][] = 'FlaggedRevs::resetPreferences';
$wgHooks['SavePreferences'][] = 'FlaggedRevs::savePreferences';
# Special page CSS
$wgHooks['BeforePageDisplay'][] = 'FlaggedRevs::InjectStyleForSpecial';
# Image version display
$wgHooks['ImagePageFindFile'][] = 'FlaggedRevs::imagePageFindFile';
# Show unreviewed pages links
$wgHooks['CategoryPageView'][] = 'FlaggedRevs::unreviewedPagesLinks';
# Backlog notice
$wgHooks['SiteNoticeAfter'][] = 'FlaggedRevs::addBacklogNotice';

# Visibility - experimental
$wgHooks['userCan'][] = 'FlaggedRevs::userCanView';

# Main hooks, overrides pages content, adds tags, sets tabs and permalink
$wgHooks['SkinTemplateTabs'][] = 'FlaggedRevs::setActionTabs';
# Change last-modified footer
$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 'FlaggedRevs::setLastModified';
# Override current revision, add patrol links, set cache...
$wgHooks['ArticleViewHeader'][] = 'FlaggedRevs::onArticleViewHeader';
# Add page notice
$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink'][] = 'FlaggedRevs::setPermaLink';
# Add tags do edit view
$wgHooks['EditPage::showEditForm:initial'][] = 'FlaggedRevs::addToEditView';
# Add review form
$wgHooks['BeforePageDisplay'][] = 'FlaggedRevs::addReviewForm';
$wgHooks['BeforePageDisplay'][] = 'FlaggedRevs::addVisibilityLink';
# Mark of items in page history
$wgHooks['PageHistoryPager::getQueryInfo'][] = 'FlaggedRevs::addToHistQuery';
$wgHooks['PageHistoryLineEnding'][] = 'FlaggedRevs::addToHistLine';
$wgHooks['LocalFile::getHistory'][] = 'FlaggedRevs::addToFileHistQuery';
$wgHooks['ImagePageFileHistoryLine'][] = 'FlaggedRevs::addToFileHistLine';
# Page review on edit
$wgHooks['ArticleUpdateBeforeRedirect'][] = 'FlaggedRevs::injectReviewDiffURLParams';
$wgHooks['DiffViewHeader'][] = 'FlaggedRevs::onDiffViewHeader';
# Autoreview stuff
$wgHooks['EditPage::showEditForm:fields'][] = 'FlaggedRevs::addRevisionIDField';
# Add CSS/JS
$wgHooks['OutputPageParserOutput'][] = 'FlaggedRevs::injectStyleAndJS';
$wgHooks['EditPage::showEditForm:initial'][] = 'FlaggedRevs::injectStyleAndJS';
$wgHooks['PageHistoryBeforeList'][] = 'FlaggedRevs::injectStyleAndJS';

# Set aliases
$wgHooks['LanguageGetSpecialPageAliases'][] = 'FlaggedRevs::addLocalizedSpecialPageNames';
#########

function efLoadFlaggedRevs() {
	global $wgUseRCPatrol;
	wfLoadExtensionMessages( 'FlaggedRevs' );
	wfLoadExtensionMessages( 'FlaggedRevsAliases' );
	# Use RC Patrolling to check for vandalism
	# When revisions are flagged, they count as patrolled
	$wgUseRCPatrol = true;
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
		$wgExtNewTables[] = array( 'flaggedpages', "$base/archives/patch-flaggedpages.sql" );
		$wgExtNewFields[] = array( 'flaggedrevs', 'fr_img_name', "$base/archives/patch-fr_img_name.sql" );
	} else if( $wgDBtype == 'postgres' ) {
		$wgExtPGNewFields[] = array('flaggedpage_config', 'fpc_expiry', "TIMESTAMPTZ NULL" );
		$wgExtNewIndexes[] = array('flaggedpage_config', 'fpc_expiry', "$base/postgres/patch-expiry-index.sql" );
		$wgExtNewTables[] = array( 'flaggedrevs_promote', "$base/postgres/patch-flaggedrevs_promote.sql" );
		$wgExtNewTables[] = array( 'flaggedpages', "$base/postgres/patch-flaggedpages.sql" );
		$wgExtNewIndexes[] = array('flaggedrevs', 'key_timestamp', "$base/postgres/patch-fr_img_name.sql" );
	}
	return true;
}
