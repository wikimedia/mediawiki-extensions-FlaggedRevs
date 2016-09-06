<?php
/**
 * (c) Aaron Schulz, Joerg Baach, 2007-2008 GPL
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

# Stable constant to let extensions be aware that this is enabled
define( 'FLAGGED_REVISIONS', true );

$wgExtensionCredits['specialpage'][] = [
	'path'           => __FILE__,
	'name'           => 'Flagged Revisions',
	'author'         => [ 'Aaron Schulz', 'Joerg Baach' ],
	'url'            => 'https://www.mediawiki.org/wiki/Extension:FlaggedRevs',
	'descriptionmsg' => 'flaggedrevs-desc',
	'license-name'   => 'GPL-2.0-or-later',
];

# Load global constants
require __DIR__ . '/FlaggedRevs.defines.php';

# This will only distinguish "checked", "quality", and unreviewed
# A small icon will show in the upper right hand corner
$wgSimpleFlaggedRevsUI = true; // @TODO: remove when ready
# For visitors, only show tags/icons for unreviewed/outdated pages
$wgFlaggedRevsLowProfile = true; // @TODO: remove with new icon UI?

# Allowed namespaces of reviewable pages
$wgFlaggedRevsNamespaces = [ NS_MAIN, NS_FILE, NS_TEMPLATE ];
# Pages exempt from reviewing. No flagging UI will be shown for them.
$wgFlaggedRevsWhitelist = [];
# $wgFlaggedRevsWhitelist = [ 'Main_Page' ];

# Is a "stable version" used as the default display
# version for all pages in reviewable namespaces?
$wgFlaggedRevsOverride = true;
# Below are groups that see the current revision by default.
# This makes editing easier since the users always start off
# viewing the latest version of pages.
$wgFlaggedRevsExceptions = [ 'user' ]; // @TODO: remove when ready (and expand pref)

# Auto-review settings for edits/new pages:
# FR_AUTOREVIEW_NONE
#   Don't auto-review any edits or new pages
# FR_AUTOREVIEW_CHANGES
#   Auto-review the following types of edits (to existing pages):
#   (a) changes directly to the stable version by users with 'autoreview'/'bot'
#   (b) reversions to old reviewed versions by users with 'autoreview'/'bot'
#   (c) self-reversions back to the stable version by any user
# FR_AUTOREVIEW_CREATION
#   Auto-review new pages as minimally "checked"
# FR_AUTOREVIEW_CREATION_AND_CHANGES
#   Combines FR_AUTOREVIEW_CHANGES and FR_AUTOREVIEW_CREATION
$wgFlaggedRevsAutoReview = FR_AUTOREVIEW_CREATION_AND_CHANGES;

# Define the tags we can use to rate an article, number of levels,
# and set the minimum level to have it become a "quality" or "pristine" version.
# NOTE: When setting up new dimensions or levels, you will need to add some
#       MediaWiki messages for the UI to show properly; any sysop can do this.
$wgFlaggedRevsTags = [
	'accuracy' => [ 'levels' => 3, 'quality' => 2, 'pristine' => 4 ],
	'depth'    => [ 'levels' => 3, 'quality' => 1, 'pristine' => 4 ],
	'style'    => [ 'levels' => 3, 'quality' => 1, 'pristine' => 4 ],
];
# For each tag, define the highest tag level that is unlocked by
# having certain rights. For example, having 'review' rights may
# allow for "depth" to be rated up to second level.
# NOTE: Users cannot lower revision tags from a level they can't set.
# NOTE: Users with 'validate' (Reviewers) can set all tags to all levels.
$wgFlaggedRevsTagsRestrictions = [
	'accuracy' => [ 'review' => 1, 'autoreview' => 1 ],
	'depth'    => [ 'review' => 2, 'autoreview' => 2 ],
	'style'    => [ 'review' => 3, 'autoreview' => 3 ],
];
# For each tag, what is the highest level that it can be auto-reviewed to?
# $wgFlaggedRevsAutoReview must be enabled for this to apply.
$wgFlaggedRevsTagsAuto = [
	'accuracy' => 1, 'depth' => 1, 'style' => 1
];

# Restriction levels for 'autoreview'/'review' rights.
# When a level is selected for a page, an edit made by a user
# will not be auto-reviewed if the user lacks the specified permission.
# Levels are set at the Stabilization special page.
$wgFlaggedRevsRestrictionLevels = [ '', 'sysop' ];
# Set this to use FlaggedRevs *only* as a protection-like mechanism.
# This will disable Stabilization and show the above restriction levels
# on the protection form of pages. Each level has the stable version shown by default.
# A "none" level will appear in the form as well, to disable the review process.
# Pages will only be reviewable if manually restricted to a level above "none".
$wgFlaggedRevsProtection = false;

# Define our basic reviewer class of established editors (Editors)
$wgGroupPermissions['editor']['review']            = true;
$wgGroupPermissions['editor']['autoreview']        = true;
$wgGroupPermissions['editor']['autoconfirmed']     = true;
$wgGroupPermissions['editor']['editsemiprotected'] = true;
$wgGroupPermissions['editor']['unreviewedpages']   = true;

# Define rights granted to consumers
$wgGrantPermissions['basic']['autoreview'] = true;
$wgGrantPermissions['basic']['autoreviewrestore'] = true;
$wgGrantPermissions['basic']['unreviewedpages'] = true;
$wgGrantPermissions['patrol']['review'] = true;
$wgGrantPermissions['patrol']['validate'] = true;
$wgGrantPermissions['protect']['stablesettings'] = true;
$wgGrantPermissions['editprotected']['movestable'] = true;

# Define when users get automatically promoted to Editors. Set as false to disable.
# Once users meet these requirements they will be promoted, unless previously demoted.
$wgFlaggedRevsAutopromote = [
	'days'                  => 60, # days since registration
	'edits'                 => 250, # total edit count
	'excludeLastDays'       => 1, # exclude the last X days of edits from below edit counts
	'benchmarks'            => 15, # number of "spread out" edits
	'spacing'               => 3, # number of days between these edits (the "spread")
	// Either totalContentEdits reqs OR totalCheckedEdits requirements needed
	'totalContentEdits'     => 300, # edits to pages in $wgContentNamespaces
	'totalCheckedEdits'     => 200, # edits before the stable version of pages
	'uniqueContentPages'    => 14, # unique pages in $wgContentNamespaces edited
	'editComments'          => 50, # number of manual edit summaries used
	'userpageBytes'         => 0, # size of userpage (use 0 to not require a userpage)
	'neverBlocked'          => true, # username was never blocked before?
	'maxRevertedEditRatio'  => 0.03, # max fraction of edits reverted via "rollback"/"undo"
];

# Define when users get to have their own edits auto-reviewed. Set to false to disable.
# This can be used for newer, semi-trusted users to improve workflow.
# It is done by granting some users the implicit 'autoreview' group.
$wgFlaggedRevsAutoconfirm = false;

/**
 * @example
 * $wgFlaggedRevsAutoconfirm = [
 * 	'days'                  => 30, # days since registration
 * 	'edits'                 => 50, # total edit count
 * 	'excludeLastDays'       => 2, # exclude the last X days of edits from below edit counts
 * 	'benchmarks'            => 7, # number of "spread out" edits
 * 	'spacing'               => 3, # number of days between these edits (the "spread")
 * 	// Either totalContentEdits reqs OR totalCheckedEdits requirements needed
 * 	'totalContentEdits'     => 150, # $wgContentNamespaces edits OR...
 * 	'totalCheckedEdits'     => 50, # ...Edits before the stable version of pages
 * 	'uniqueContentPages'    => 8, # $wgContentNamespaces unique pages edited
 * 	'editComments'          => 20, # how many edit comments used?
 * 	'email'                 => false, # user must be emailconfirmed?
 * 	'neverBlocked'          => true, # Can users that were blocked be promoted?
 * ];
 */

# Defines extra rights for advanced reviewer class (Reviewers)
$wgGroupPermissions['reviewer']['validate'] = true;
# Let this stand alone just in case...
$wgGroupPermissions['reviewer']['review']            = true;
$wgGroupPermissions['reviewer']['autoreview']        = true;
$wgGroupPermissions['reviewer']['autoconfirmed']     = true;
$wgGroupPermissions['reviewer']['editsemiprotected'] = true;
$wgGroupPermissions['reviewer']['unreviewedpages']   = true;

# Sysops have their edits autoreviewed
$wgGroupPermissions['sysop']['autoreview'] = true;
# Stable version selection and default page revision selection can be set per page.
$wgGroupPermissions['sysop']['stablesettings'] = true;
# Sysops can always move stable pages
$wgGroupPermissions['sysop']['movestable'] = true;

# "Auto-checked"/semi-trusted user group
$wgGroupPermissions['autoreview']['autoreview'] = true;

# Special:Userrights settings
# # Basic rights for Sysops
$wgAddGroups['sysop'][] = 'editor'; // promote to basic reviewer (established editors)
$wgRemoveGroups['sysop'][] = 'editor'; // demote from basic reviewer (established editors)
$wgAddGroups['sysop'][] = 'autoreview'; // promote to basic auto-reviewer (semi-trusted users)
$wgRemoveGroups['sysop'][] = 'autoreview'; // demote from basic auto-reviewer (semi-trusted users)

# How far the logs for overseeing quality revisions and depreciations go
$wgFlaggedRevsOversightAge = 30 * 24 * 3600;

# How long before Special:ValidationStatistics is updated.
# Set to false to disable (perhaps using a cron job instead).
$wgFlaggedRevsStatsAge = 2 * 3600; // 2 hours

# Configurable information to collect and display at Special:ValidationStatistics
$wgFlaggedRevsStats = [
	'topReviewersCount' => 5, # how many top reviewers to list
	'topReviewersHours' => 1, # how many hours of the last reviews to count
];

# How to handle templates and files used in stable versions:
# FR_INCLUDES_CURRENT
#   Always use the current version of templates/files
# FR_INCLUDES_FREEZE
#   Use the version of templates/files that the page used when reviewed
# FR_INCLUDES_STABLE
#   For each template/file, check if a version of it was used when the page was reviewed
#   and if the template/file itself has a stable version; use the newest those versions
# NOTE: We may have templates that do not have stable version. Also, given situational
# inclusion of templates (e.g. parser functions selecting template X or Y based on date),
# there may also be no "review time version" revision ID for a template used on a page.
# In such cases, we select the current (unreviewed) revision. Likewise for files.
$wgFlaggedRevsHandleIncludes = FR_INCLUDES_STABLE;

$dir = __DIR__;

# Basic directory layout
$backendDir       = "$dir/backend";
$schemaDir        = "$dir/backend/schema";
$businessDir      = "$dir/business";
$apiDir           = "$dir/api";
$apiActionDir     = "$dir/api/actions";
$apiReportDir     = "$dir/api/reports";
$frontendDir      = "$dir/frontend";
$langDir          = "$dir/frontend/language";
$spActionDir      = "$dir/frontend/specialpages/actions";
$spReportDir      = "$dir/frontend/specialpages/reports";
$scribuntoDir     = "$dir/scribunto";
$testDir          = "$dir/tests";

$wgAutoloadClasses['FlaggedRevsSetup'] = "$dir/FlaggedRevs.setup.php";

### Backend classes ###
# Utility classes...
$wgAutoloadClasses['FlaggedRevs'] = "$backendDir/FlaggedRevs.class.php";
$wgAutoloadClasses['FRUserCounters'] = "$backendDir/FRUserCounters.php";
$wgAutoloadClasses['FRUserActivity'] = "$backendDir/FRUserActivity.php";
$wgAutoloadClasses['FRPageConfig'] = "$backendDir/FRPageConfig.php";
$wgAutoloadClasses['FlaggedRevsLog'] = "$backendDir/FlaggedRevsLog.php";
$wgAutoloadClasses['FRInclusionCache'] = "$backendDir/FRInclusionCache.php";
$wgAutoloadClasses['FlaggedRevsStats'] = "$backendDir/FlaggedRevsStats.php";
# Data access object classes...
$wgAutoloadClasses['FRExtraCacheUpdate'] = "$backendDir/FRExtraCacheUpdate.php";
$wgAutoloadClasses['FRExtraCacheUpdateJob'] = "$backendDir/FRExtraCacheUpdateJob.php";
$wgAutoloadClasses['FRDependencyUpdate'] = "$backendDir/FRDependencyUpdate.php";
$wgAutoloadClasses['FRInclusionManager'] = "$backendDir/FRInclusionManager.php";
$wgAutoloadClasses['FlaggableWikiPage'] = "$backendDir/FlaggableWikiPage.php";
$wgAutoloadClasses['FlaggedRevision'] = "$backendDir/FlaggedRevision.php";
$wgAutoloadClasses['FRParserCacheStable'] = "$backendDir/FRParserCacheStable.php";
### End ###

### Business object classes ###
$wgAutoloadClasses['FRGenericSubmitForm'] = "$businessDir/FRGenericSubmitForm.php";
$wgAutoloadClasses['RevisionReviewForm'] = "$businessDir/RevisionReviewForm.php";
$wgAutoloadClasses['PageStabilityForm'] = "$businessDir/PageStabilityForm.php";
$wgAutoloadClasses['PageStabilityGeneralForm'] = "$businessDir/PageStabilityForm.php";
$wgAutoloadClasses['PageStabilityProtectForm'] = "$businessDir/PageStabilityForm.php";
### End ###

### Presentation classes ###
# Main i18n file and special page alias file
$wgMessagesDirs['FlaggedRevs'] = __DIR__ . '/i18n/flaggedrevs';
$wgExtensionMessagesFiles['FlaggedRevsMagic'] = "$langDir/FlaggedRevs.i18n.magic.php";
$wgExtensionMessagesFiles['FlaggedRevsAliases'] = "$langDir/FlaggedRevs.alias.php";
# UI setup, forms, and HTML elements
$wgAutoloadClasses['FlaggedRevsUISetup'] = "$frontendDir/FlaggedRevsUI.setup.php";
$wgAutoloadClasses['FlaggablePageView'] = "$frontendDir/FlaggablePageView.php";
$wgAutoloadClasses['FlaggedRevsReviewLogFormatter'] =
	"$frontendDir/FlaggedRevsReviewLogFormatter.php";
$wgAutoloadClasses['FlaggedRevsStableLogFormatter'] =
	"$frontendDir/FlaggedRevsStableLogFormatter.php";
$wgAutoloadClasses['FlaggedRevsXML'] = "$frontendDir/FlaggedRevsXML.php";
$wgAutoloadClasses['RevisionReviewFormUI'] = "$frontendDir/RevisionReviewFormUI.php";
$wgAutoloadClasses['RejectConfirmationFormUI'] = "$frontendDir/RejectConfirmationFormUI.php";
# Revision review UI
$wgAutoloadClasses['RevisionReview'] = "$spActionDir/RevisionReview_body.php";
$wgMessagesDirs['RevisionReview'] = __DIR__ . '/i18n/revisionreview';
# Stable version config UI
$wgAutoloadClasses['Stabilization'] = "$spActionDir/Stabilization_body.php";
$wgMessagesDirs['Stabilization'] = __DIR__ . '/i18n/stabilization';
# Reviewed versions list
$wgAutoloadClasses['ReviewedVersions'] = "$spReportDir/ReviewedVersions_body.php";
$wgAutoloadClasses['ReviewedVersionsPager'] = "$spReportDir/ReviewedVersions_body.php";
$wgMessagesDirs['ReviewedVersions'] = __DIR__ . '/i18n/reviewedversions';
# Unreviewed pages list
$wgAutoloadClasses['UnreviewedPages'] = "$spReportDir/UnreviewedPages_body.php";
$wgAutoloadClasses['UnreviewedPagesPager'] = "$spReportDir/UnreviewedPages_body.php";
$wgMessagesDirs['UnreviewedPages'] = __DIR__ . '/i18n/unreviewedpages';
# Pages with pending changes list
$wgAutoloadClasses['PendingChanges'] = "$spReportDir/PendingChanges_body.php";
$wgAutoloadClasses['PendingChangesPager'] = "$spReportDir/PendingChanges_body.php";
$wgMessagesDirs['PendingChanges'] = __DIR__ . '/i18n/pendingchanges';
# Pages with tagged pending changes list
$wgAutoloadClasses['ProblemChanges'] = "$spReportDir/ProblemChanges_body.php";
$wgAutoloadClasses['ProblemChangesPager'] = "$spReportDir/ProblemChanges_body.php";
$wgMessagesDirs['ProblemChanges'] = __DIR__ . '/i18n/problemchanges';
# Reviewed pages list
$wgAutoloadClasses['ReviewedPages'] = "$spReportDir/ReviewedPages_body.php";
$wgAutoloadClasses['ReviewedPagesPager'] = "$spReportDir/ReviewedPages_body.php";
$wgMessagesDirs['ReviewedPages'] = __DIR__ . '/i18n/reviewedpages';
# Stable pages list (for protection config)
$wgAutoloadClasses['StablePages'] = "$spReportDir/StablePages_body.php";
$wgAutoloadClasses['StablePagesPager'] = "$spReportDir/StablePages_body.php";
$wgMessagesDirs['StablePages'] = __DIR__ . '/i18n/stablepages';
# Configured pages list (non-protection config)
$wgAutoloadClasses['ConfiguredPages'] = "$spReportDir/ConfiguredPages_body.php";
$wgAutoloadClasses['ConfiguredPagesPager'] = "$spReportDir/ConfiguredPages_body.php";
$wgMessagesDirs['ConfiguredPages'] = __DIR__ . '/i18n/configuredpages';
# Filterable review log page to oversee reviews
$wgAutoloadClasses['QualityOversight'] = "$spReportDir/QualityOversight_body.php";
$wgMessagesDirs['QualityOversight'] = __DIR__ . '/i18n/qualityoversight';
# Review statistics
$wgAutoloadClasses['ValidationStatistics'] = "$spReportDir/ValidationStatistics_body.php";
$wgMessagesDirs['ValidationStatistics'] = __DIR__ . '/i18n/validationstatistics';
### End ###

### API classes ###
# Page review module for API
$wgAutoloadClasses['ApiReview'] = "$apiActionDir/ApiReview.php";
# Page review activity module for API
$wgAutoloadClasses['ApiReviewActivity'] = "$apiActionDir/ApiReviewActivity.php";
# Stability config module for API
$wgAutoloadClasses['ApiStabilize'] = "$apiActionDir/ApiStabilize.php";
$wgAutoloadClasses['ApiStabilizeGeneral'] = "$apiActionDir/ApiStabilize.php";
$wgAutoloadClasses['ApiStabilizeProtect'] = "$apiActionDir/ApiStabilize.php";
# OldReviewedPages for API
$wgAutoloadClasses['ApiQueryOldreviewedpages'] = "$apiReportDir/ApiQueryOldreviewedpages.php";
# UnreviewedPages for API
$wgAutoloadClasses['ApiQueryUnreviewedpages'] = "$apiReportDir/ApiQueryUnreviewedpages.php";
# ReviewedPages for API
$wgAutoloadClasses['ApiQueryReviewedpages'] = "$apiReportDir/ApiQueryReviewedpages.php";
# ConfiguredPages for API
$wgAutoloadClasses['ApiQueryConfiguredpages'] = "$apiReportDir/ApiQueryConfiguredpages.php";
# Flag metadata for pages for API
$wgAutoloadClasses['ApiQueryFlagged'] = "$apiReportDir/ApiQueryFlagged.php";
# Site flag config for API
$wgAutoloadClasses['ApiFlagConfig'] = "$apiReportDir/ApiFlagConfig.php";
# i18n
$wgMessagesDirs['FlaggedRevsApi'] = __DIR__ . '/i18n/api';
### End ###

### Scribunto classes ###
$wgAutoloadClasses['Scribunto_LuaFlaggedRevsLibrary'] = "$scribuntoDir/FlaggedRevs.library.php";
### End ###

### Event handler classes ###
$wgAutoloadClasses['FlaggedRevsHooks'] = "$backendDir/FlaggedRevs.hooks.php";
$wgAutoloadClasses['FlaggedRevsUIHooks'] = "$frontendDir/FlaggedRevsUI.hooks.php";
$wgAutoloadClasses['FlaggedRevsApiHooks'] = "$apiDir/FlaggedRevsApi.hooks.php";
$wgAutoloadClasses['FlaggedRevsUpdaterHooks'] = "$schemaDir/FlaggedRevsUpdater.hooks.php";
$wgAutoloadClasses['FlaggedRevsTestHooks'] = "$testDir/FlaggedRevsTest.hooks.php";
### End ###

# Define JS/CSS modules and file locations
$localModulePath = __DIR__ . '/frontend/modules/';
$remoteModulePath = 'FlaggedRevs/frontend/modules';
$wgResourceModules['ext.flaggedRevs.basic'] = [
	'position'		=> 'top',
	'styles'        => [ 'ext.flaggedRevs.basic.css' ],
	'localBasePath' => $localModulePath,
	'remoteExtPath' => $remoteModulePath,
];
$wgResourceModules['ext.flaggedRevs.advanced'] = [
	'scripts'       => [ 'ext.flaggedRevs.advanced.js' ],
	'messages'      => [
		'revreview-toggle-show', 'revreview-toggle-hide',
		'revreview-diff-toggle-show', 'revreview-diff-toggle-hide',
		'revreview-log-toggle-show', 'revreview-log-toggle-hide',
		'revreview-log-details-show', 'revreview-log-details-hide'
	],
	'dependencies'  => [ 'jquery.accessKeyLabel' ],
	'localBasePath' => $localModulePath,
	'remoteExtPath' => $remoteModulePath,
];
$wgResourceModules['ext.flaggedRevs.review'] = [
	'scripts'       => [ 'ext.flaggedRevs.review.js' ],
	'messages'      => [
		'savearticle', 'tooltip-save',
		'revreview-submitedit', 'revreview-submitedit-title',
		'revreview-submit-review', 'revreview-submit-unreview',
		'revreview-submit-reviewed', 'revreview-submit-unreviewed',
		'revreview-submitting', 'actioncomplete', 'actionfailed',
		'revreview-adv-reviewing-p', 'revreview-adv-reviewing-c',
		'revreview-sadv-reviewing-p', 'revreview-sadv-reviewing-c',
		'revreview-adv-stop-link'
	],
	'dependencies'  => [
		'mediawiki.util', 'mediawiki.notify', 'mediawiki.user', 'mediawiki.jqueryMsg'
	],
	'localBasePath' => $localModulePath,
	'remoteExtPath' => $remoteModulePath,
];
$wgResourceModules['ext.flaggedRevs.review.styles'] = [
	'styles'        => [ 'ext.flaggedRevs.review.css' ],
	'localBasePath' => $localModulePath,
	'remoteExtPath' => $remoteModulePath,
	'position' => 'top',
];

# Define user rights
$wgAvailableRights[] = 'review'; # review pages to basic quality levels
$wgAvailableRights[] = 'validate'; # review pages to all quality levels
$wgAvailableRights[] = 'autoreview'; # auto-review one's own edits (including rollback)
$wgAvailableRights[] = 'autoreviewrestore'; # auto-review one's own rollbacks
$wgAvailableRights[] = 'unreviewedpages'; # view the list of unreviewed pages
$wgAvailableRights[] = 'movestable'; # move pages with stable versions
$wgAvailableRights[] = 'stablesettings'; # change page stability settings

# Bots are granted autoreview via hooks, mark in rights
# array so that it shows up in sp:ListGroupRights...
$wgGroupPermissions['bot']['autoreview'] = true;

# Define user preferences
$wgDefaultUserOptions['flaggedrevssimpleui'] = (int)$wgSimpleFlaggedRevsUI;
$wgDefaultUserOptions['flaggedrevsstable'] = FR_SHOW_STABLE_DEFAULT;
$wgDefaultUserOptions['flaggedrevseditdiffs'] = true;
$wgDefaultUserOptions['flaggedrevsviewdiffs'] = false;

# Add review log
$wgLogTypes[] = 'review';
$wgActionFilteredLogs['review'] = [
	'accept' => [ 'approve', 'approve2', 'approve-i', 'approve2-i' ],
	'autoaccept' => [ 'approve-a', 'approve-ia' ],
	'unaccept' => [ 'unapprove', 'unapprove2' ],
];
# Add stable version log
$wgLogTypes[] = 'stable';
$wgActionFilteredLogs['stable'] = [
	'config' => [ 'config' ],
	'modify' => [ 'modify' ],
	'reset' => [ 'reset' ],
	'move_stable' => [ 'move_stable' ],
];

# Log name and description as well as action handlers
$wgLogNames['review'] = 'review-logpage';
$wgLogHeaders['review'] = 'review-logpagetext';

$wgLogNames['stable'] = 'stable-logpage';
$wgLogHeaders['stable'] = 'stable-logpagetext';

$wgFilterLogTypes['review'] = true;

# Various actions are used for log filtering ...
$wgLogActionsHandlers['review/approve']  = 'FlaggedRevsReviewLogFormatter'; // checked (again)
$wgLogActionsHandlers['review/approve2']  = 'FlaggedRevsReviewLogFormatter'; // quality (again)
$wgLogActionsHandlers['review/approve-i']  =
	'FlaggedRevsReviewLogFormatter'; // checked (first time)
$wgLogActionsHandlers['review/approve2-i']  =
	'FlaggedRevsReviewLogFormatter'; // quality (first time)
$wgLogActionsHandlers['review/approve-a']  = 'FlaggedRevsReviewLogFormatter'; // checked (auto)
$wgLogActionsHandlers['review/approve2-a']  = 'FlaggedRevsReviewLogFormatter'; // quality (auto)
$wgLogActionsHandlers['review/approve-ia']  =
	'FlaggedRevsReviewLogFormatter'; // checked (initial & auto)
$wgLogActionsHandlers['review/approve2-ia']  =
	'FlaggedRevsReviewLogFormatter'; // quality (initial & auto)
$wgLogActionsHandlers['review/unapprove'] = 'FlaggedRevsReviewLogFormatter'; // was checked
$wgLogActionsHandlers['review/unapprove2'] = 'FlaggedRevsReviewLogFormatter'; // was quality

$wgLogActionsHandlers['stable/config'] = 'FlaggedRevsStableLogFormatter'; // customize
$wgLogActionsHandlers['stable/modify'] = 'FlaggedRevsStableLogFormatter'; // re-customize
$wgLogActionsHandlers['stable/reset'] = 'FlaggedRevsStableLogFormatter'; // reset
$wgLogActionsHandlers['stable/move_stable'] = 'FlaggedRevsStableLogFormatter'; // move

# B/C ...
$wgLogActions['rights/erevoke'] = 'rights-editor-revoke';

# AJAX functions
$wgAjaxExportList[] = 'RevisionReview::AjaxReview';
$wgAjaxExportList[] = 'FlaggablePageView::AjaxBuildDiffHeaderItems';

# Special case page cache invalidations
$wgJobClasses['flaggedrevs_CacheUpdate'] = 'FRExtraCacheUpdateJob';

# Register our special pages
$wgHooks['SpecialPage_initList'][] = 'FlaggedRevsUIHooks::onSpecialPage_initList';

# Add flagging data to ApiQueryRevisions
$wgHooks['APIGetAllowedParams'][] = 'FlaggedRevsApiHooks::addApiRevisionParams';
$wgHooks['APIQueryAfterExecute'][] = 'FlaggedRevsApiHooks::addApiRevisionData';
# ########

# ######## Parser #########
# Parser hooks, selects the desired images/templates
$wgHooks['BeforeParserFetchTemplateAndtitle'][] = 'FlaggedRevsHooks::parserFetchStableTemplate';
$wgHooks['BeforeParserFetchFileAndTitle'][] = 'FlaggedRevsHooks::parserFetchStableFile';
# ########

# ######## DB write operations #########
# Autopromote Editors
$wgHooks['PageContentSaveComplete'][] = 'FlaggedRevsHooks::onPageContentSaveComplete';
# Auto-reviewing
$wgHooks['RecentChange_save'][] = 'FlaggedRevsHooks::autoMarkPatrolled';
$wgHooks['NewRevisionFromEditComplete'][] = 'FlaggedRevsHooks::maybeMakeEditReviewed';
# Null edit review via checkbox
$wgHooks['PageContentSaveComplete'][] = 'FlaggedRevsHooks::maybeNullEditReview';
# User edit tallies
$wgHooks['ArticleRollbackComplete'][] = 'FlaggedRevsHooks::incrementRollbacks';
$wgHooks['NewRevisionFromEditComplete'][] = 'FlaggedRevsHooks::incrementReverts';
# Update fr_page_id and tracking rows on revision restore and merge
$wgHooks['ArticleRevisionUndeleted'][] = 'FlaggedRevsHooks::onRevisionRestore';
$wgHooks['ArticleMergeComplete'][] = 'FlaggedRevsHooks::onArticleMergeComplete';

# Update tracking rows and cache on page changes (@TODO: this sucks):
# Article edit/create
$wgHooks['ArticleEditUpdates'][] = 'FlaggedRevsHooks::onArticleEditUpdates';
# Article delete/restore
$wgHooks['ArticleDeleteComplete'][] = 'FlaggedRevsHooks::onArticleDelete';
$wgHooks['ArticleUndelete'][] = 'FlaggedRevsHooks::onArticleUndelete';
# Revision delete/restore
$wgHooks['ArticleRevisionVisibilitySet'][] = 'FlaggedRevsHooks::onRevisionDelete';
# Article move
$wgHooks['TitleMoveComplete'][] = 'FlaggedRevsHooks::onTitleMoveComplete';
# File upload
$wgHooks['FileUpload'][] = 'FlaggedRevsHooks::onFileUpload';
# ########

# ######## Other #########
# Determine what pages can be moved and patrolled
$wgHooks['getUserPermissionsErrors'][] = 'FlaggedRevsHooks::onGetUserPermissionsErrors';
# Implicit autoreview rights group
$wgHooks['AutopromoteCondition'][] = 'FlaggedRevsHooks::checkAutoPromoteCond';
$wgHooks['UserLoadAfterLoadFromSession'][] = 'FlaggedRevsHooks::setSessionKey';

# Stable dump hook
$wgHooks['WikiExporter::dumpStableQuery'][] = 'FlaggedRevsHooks::stableDumpQuery';

# GNSM category hooks
$wgHooks['GoogleNewsSitemap::Query'][] = 'FlaggedRevsHooks::gnsmQueryModifier';

# UserMerge hooks
$wgHooks['UserMergeAccountFields'][] = 'FlaggedRevsHooks::onUserMergeAccountFields';
$wgHooks['MergeAccountFromTo'][] = 'FlaggedRevsHooks::onMergeAccountFromTo';
$wgHooks['DeleteAccount'][] = 'FlaggedRevsHooks::onDeleteAccount';

# Duplicate flagged* tables in parserTests.php
$wgHooks['ParserTestTables'][] = 'FlaggedRevsTestHooks::onParserTestTables';
# Integration tests
$wgHooks['UnitTestsList'][] = 'FlaggedRevsTestHooks::getUnitTests';

# Database schema changes
$wgHooks['LoadExtensionSchemaUpdates'][] = 'FlaggedRevsUpdaterHooks::addSchemaUpdates';

# Add our library to Scribunto
$wgHooks['ScribuntoExternalLibraries'][] = 'FlaggedRevsHooks::onScribuntoExternalLibraries';

$wgHooks['UserGetRights'][] = 'FlaggedRevsHooks::onUserGetRights';
$wgHooks['EchoGetDefaultNotifiedUsers'][] = 'FlaggedRevsHooks::onEchoGetDefaultNotifiedUsers';

# Add notice tags to edit view
$wgHooks['EditPage::showEditForm:initial'][] = 'FlaggedRevsUIHooks::addToEditView';
$wgHooks['TitleGetEditNotices'][] = 'FlaggedRevsUIHooks::getEditNotices';
# Tweak submit button name/title
$wgHooks['EditPageBeforeEditButtons'][] = 'FlaggedRevsUIHooks::onBeforeEditButtons';
# Autoreview information from form
if ( version_compare( $wgVersion, '1.29', '<' ) ) {
	$wgHooks['EditPageBeforeEditChecks'][] = 'FlaggedRevsUIHooks::onEditPageBeforeEditChecks';
} else {
	$wgHooks['EditPageGetCheckboxesDefinition'][] =
		'FlaggedRevsUIHooks::onEditPageGetCheckboxesDefinition';
}
$wgHooks['EditPage::showEditForm:fields'][] = 'FlaggedRevsUIHooks::addRevisionIDField';
# Add draft link to section edit error
$wgHooks['EditPageNoSuchSection'][] = 'FlaggedRevsUIHooks::onNoSuchSection';
# Page review on edit
$wgHooks['ArticleUpdateBeforeRedirect'][] = 'FlaggedRevsUIHooks::injectPostEditURLParams';

# Mark items in page history
$wgHooks['PageHistoryPager::getQueryInfo'][] = 'FlaggedRevsUIHooks::addToHistQuery';
$wgHooks['PageHistoryLineEnding'][] = 'FlaggedRevsUIHooks::addToHistLine';
# Select extra info & filter items on Special:NewPages
$wgHooks['ChangesListSpecialPageQuery'][] = 'FlaggedRevsUIHooks::modifyChangesListSpecialPageQuery';
$wgHooks['SpecialNewpagesConditions'][] = 'FlaggedRevsUIHooks::modifyNewPagesQuery';
# Mark items in RC
$wgHooks['ChangesListInsertArticleLink'][] = 'FlaggedRevsUIHooks::addToChangeListLine';

# RC filter UIs
$wgHooks['SpecialNewPagesFilters'][] = 'FlaggedRevsUIHooks::addHideReviewedUnstructuredFilter';
$wgHooks['ChangesListSpecialPageStructuredFilters'][] = 'FlaggedRevsUIHooks::addHideReviewedFilter';
# Add notice tags to history
$wgHooks['PageHistoryBeforeList'][] = 'FlaggedRevsUIHooks::addToHistView';
# Diff-to-stable
$wgHooks['DiffViewHeader'][] = 'FlaggedRevsUIHooks::onDiffViewHeader';
# Add diff=review url param alias
$wgHooks['NewDifferenceEngine'][] = 'FlaggedRevsUIHooks::checkDiffUrl';
# Local user account preference
$wgHooks['GetPreferences'][] = 'FlaggedRevsUIHooks::onGetPreferences';
# Add global JS vars
$wgHooks['MakeGlobalVariablesScript'][] = 'FlaggedRevsUIHooks::injectGlobalJSVars';

# Page review module for API
$wgAPIModules['review'] = 'ApiReview';
# Page review activity module for API
$wgAPIModules['reviewactivity'] = 'ApiReviewActivity';
# OldReviewedPages for API
$wgAPIListModules['oldreviewedpages'] = 'ApiQueryOldreviewedpages';
# Flag metadata for pages for API
$wgAPIPropModules['flagged'] = 'ApiQueryFlagged';
# Site flag config for API
$wgAPIModules['flagconfig'] = 'ApiFlagConfig';

/**
 * This function is for setup that has to happen in Setup.php
 * when the functions in $wgExtensionFunctions get executed.
 * Note: avoid calls to FlaggedRevs class here for performance.
 * @return void
 */
$wgExtensionFunctions[] = function () {
	# LocalSettings.php loaded, safe to load config
	FlaggedRevsSetup::setReady();

	# Conditional autopromote groups
	FlaggedRevsSetup::setAutopromoteConfig();

	# Register special pages (some are conditional)
	FlaggedRevsSetup::setSpecialPageCacheUpdates();
	# Conditional API modules
	FlaggedRevsSetup::setAPIModules();
	# Load hooks that aren't always set
	FlaggedRevsSetup::setConditionalHooks();
	# Remove conditionally applicable rights
	FlaggedRevsSetup::setConditionalRights();
	# Defaults for user preferences
	FlaggedRevsSetup::setConditionalPreferences();
};
