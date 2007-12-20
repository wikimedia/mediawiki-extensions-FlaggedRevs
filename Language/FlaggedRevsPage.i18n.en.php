<?php
// English (Aaron Schulz)
$messages = array( 
	'editor'              => 'Editor',
	'group-editor'        => 'Editors',
	'group-editor-member' => 'Editor',
	'grouppage-editor'    => '{{ns:project}}:Editor',

	'reviewer'              => 'Reviewer',
	'group-reviewer'        => 'Reviewers',
	'group-reviewer-member' => 'Reviewer',
	'grouppage-reviewer'    => '{{ns:project}}:Reviewer',

	'revreview-current'   => 'Draft',
	'tooltip-ca-current'  => 'View the current draft of this page',
	'revreview-edit'      => 'Edit draft',
	'revreview-source'    => 'draft source',
	'revreview-stable'    => 'Stable',
	'tooltip-ca-stable'   => 'View the stable version of this page',
	'revreview-oldrating' => 'It was rated:',
	'revreview-noflagged' => 'There are no reviewed revisions of this page, so it may \'\'\'not\'\'\' have been 
	[[{{MediaWiki:validationpage}}|checked]] for quality.',
	'stabilization-tab'   => '(qa)',
	'tooltip-ca-default'  => 'Quality assurance settings',
	
	'validationpage' => '{{ns:help}}:Article validation',
	
	'revreview-quick-none' => '\'\'\'Current\'\'\'. No reviewed revisions.',
	'revreview-quick-see-quality' => '\'\'\'Current\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} see stable revision]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|changes}}])',
	'revreview-quick-see-basic' => '\'\'\'Current\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} see stable revision]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|changes}}])',
	'revreview-quick-basic' => '\'\'\'[[{{MediaWiki:validationpage}}|Sighted]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} see current revision]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|changes}}])',
	'revreview-quick-quality' => '\'\'\'[[{{MediaWiki:validationpage}}|Quality]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} see current revision]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|changes}}])',
	'revreview-newest-basic' => 'The [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} latest sighted revision] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} list all]) was [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved]
	 on <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|changes}}] {{plural:$3|needs|need}} review.',
	'revreview-newest-quality' => 'The [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} latest quality revision] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} list all]) was [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved]
	 on <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|changes}}] {{plural:$3|needs|need}} review.',
	'revreview-basic' => 'This is the latest [[{{MediaWiki:validationpage}}|sighted]] revision, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved] on <i>$2</i>. The [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} draft] 
	can be [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modified]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|changes}}] 
	{{plural:$3|awaits|await}} review.',
	'revreview-quality' => 'This is the latest [[{{MediaWiki:validationpage}}|quality]] revision, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved] on <i>$2</i>. The [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} draft] 
	can be [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modified]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|changes}}] 
	{{plural:$3|awaits|await}} review.',
	'revreview-static' => 'This is a [[{{MediaWiki:validationpage}}|reviewed]] revision of \'\'\'[[:$3|$3]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} approved] on <i>$2</i>.',
	'revreview-toggle' => '(+/-)',
	'revreview-note' => '[[User:$1]] made the following notes [[{{MediaWiki:validationpage}}|reviewing]] this revision:',
	'revreview-update' => 'Please review any changes (shown below) made since the stable revision. Some 
	templates/images were updated:',
	'revreview-update-none' => 'Please review any changes (shown below) made since the stable revision.',
	'revreview-auto' => '(automatic)',
	'revreview-auto-w' => "You are editing the stable revision, any changes will '''automatically be reviewed'''. 
	You may want to preview the page before saving.",
	'revreview-auto-w-old' => "You are editing an old revision, any changes will '''automatically be reviewed'''. 
	You may want to preview the page before saving.",

	'hist-stable'  => '[sighted]',
	'hist-quality' => '[quality]',

    'flaggedrevs'          => 'Flagged Revisions',
    'review-logpage'       => 'Article review log',
	'review-logpagetext'   => 'This is a log of changes to revisions\' [[{{MediaWiki:validationpage}}|approval]] status
	for content pages.',
	'review-logentry-app'  => 'reviewed [[$1]]',
	'review-logentry-dis'  => 'depreciated a version of [[$1]]',
	'review-logaction'     => 'revision ID $1',
	
	'stable-logpage'     => 'Stable version log',
	'stable-logpagetext' => 'This is a log of changes to the [[{{MediaWiki:validationpage}}|stable version]] 
	configuration of content pages.',
	'stable-logentry'    => 'configured stable versioning for [[$1]]',
	'stable-logentry2'   => 'reset stable versioning for [[$1]]',

    'revisionreview'       => 'Review revisions',		
    'revreview-main'       => 'You must select a particular revision from a content page in order to review. 

	See the [[Special:Unreviewedpages]] for a list of unreviewed pages.',	
	'revreview-selected'   => "Selected revision of '''$1:'''",
	'revreview-text'       => "Stable versions are set as the default content on page view rather than the newest revision.",
	'revreview-toolow'     => 'You must at least rate each of the below attributes higher than "unapproved" in order 
	for a revision to be considered reviewed. To depreciate a revision, set all fields to "unapproved".',
	'revreview-flag'       => 'Review this revision (#$1)',
	'revreview-legend'     => 'Rate revision content',
	'revreview-notes'      => 'Observations or notes to display:',
	'revreview-accuracy'   => 'Accuracy',
	'revreview-accuracy-0' => 'Unapproved',
	'revreview-accuracy-1' => 'Sighted',
	'revreview-accuracy-2' => 'Accurate',
	'revreview-accuracy-3' => 'Well sourced',
	'revreview-accuracy-4' => 'Featured',
	'revreview-depth'      => 'Depth',
	'revreview-depth-0'    => 'Unapproved',
	'revreview-depth-1'    => 'Basic',		
	'revreview-depth-2'    => 'Moderate',
	'revreview-depth-3'    => 'High',
	'revreview-depth-4'    => 'Featured',
	'revreview-style'      => 'Readability',
	'revreview-style-0'    => 'Unapproved',
	'revreview-style-1'    => 'Acceptable',
	'revreview-style-2'    => 'Good',
	'revreview-style-3'    => 'Concise',
	'revreview-style-4'    => 'Featured',
	'revreview-log'        => 'Log comment:',
	'revreview-submit'     => 'Submit review',
	'revreview-changed'    => '\'\'\'The requestion action could not be performed on this revision.\'\'\'
	
	A template or image may have been requested when no specific version was specified. This can happen if a 
	dynamic template transcludes another image or template depending on a variable that changed since you started 
	reviewed this page. Refreshing the page and rereviewing can solve this problem.',

	'stableversions'        => 'Stable versions',
	'stableversions-leg1'   => 'List reviewed revisions for a page',
	'stableversions-page'   => 'Page name:',
	'stableversions-none'   => '"[[:$1]]" has no reviewed revisions.',
	'stableversions-list'   => 'The following is a list of revisions of "[[:$1]]" that have been reviewed:',
	'stableversions-review' => 'Reviewed on <i>$1</i> by $2',

    'review-diff2stable'    => 'Diff to the stable revision',

    'unreviewedpages'       => 'Unreviewed pages',
    'viewunreviewed'        => 'List unreviewed content pages',
    'unreviewed-outdated'   => 'Show pages that have unreviewed revisions made to the stable version instead.',
    'unreviewed-category'   => 'Category:',
    'unreviewed-diff'       => 'Changes',
    'unreviewed-list'       => 'This page lists articles that have not been reviewed or have new, unreviewed, revisions.',
    
    'revreview-visibility'  => 'This page has a [[{{MediaWiki:validationpage}}|stable version]], which can be
	[{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} configured].',

    'stabilization'            => 'Page stabilization',
    'stabilization-text'       => 'Change the settings below to adjust how the stable version of [[:$1|$1]] is selected and displayed.',
    'stabilization-perm'       => 'Your account does not have permission to change the stable version configuration.
	Here are the current settings for [[:$1|$1]]:',
	'stabilization-page'       => 'Page name:',
	'stabilization-leg'        => 'Configure the stable version for a page',
	'stabilization-select'     => 'How the stable version is selected',
	'stabilization-select1'    => 'The latest quality revision; if not present, then the latest sighted one',
	'stabilization-select2'    => 'The latest reviewed revision',
	'stabilization-def'        => 'Revision displayed on default page view',
	'stabilization-def1'       => 'The stable revision; if not present, then the current one',
	'stabilization-def2'       => 'The current revision',
	'stabilization-submit'     => 'Confirm',
	'stabilization-notexists'  => 'There is no page called "[[:$1|$1]]". No configuration is possible.',
	'stabilization-notcontent' => 'The page "[[:$1|$1]]" cannot be reviewed. No configuration is possible.',
	'stabilization-comment'     => 'Comment:',
	
	'stabilization-sel-short'   => 'Precedence',
	'stabilization-sel-short-0' => 'Quality',
	'stabilization-sel-short-1' => 'None',
	'stabilization-def-short'   => 'Default',
	'stabilization-def-short-0' => 'Current',
	'stabilization-def-short-1' => 'Stable',
	
	'reviewedpages'             => 'Reviewed pages',
	'reviewedpages-leg'         => 'List pages reviewed to a certain level',
	'reviewedpages-list'        => 'The following pages have been reviewed to the specified level',
	'reviewedpages-none'        => 'There are no pages in this list',
	'reviewedpages-lev-0'       => 'Sighted',
	'reviewedpages-lev-1'       => 'Quality',
	'reviewedpages-lev-2'       => 'Featured',
	'reviewedpages-all'         => 'reviewed versions',
	'reviewedpages-best'        => 'latest highest rated revision',
);
