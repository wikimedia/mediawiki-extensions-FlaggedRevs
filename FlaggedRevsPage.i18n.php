<?php
$RevisionreviewMessages = array();

// English (Aaron Schulz)
$RevisionreviewMessages['en'] = array( 
	'editor'              => 'Editor',
	'group-editor'        => 'Editors',
	'group-editor-member' => 'Editor',
	'grouppage-editor'    => '{{ns:project}}:Editor',
	
	'reviewer'              => 'reviewer',
	'group-reviewer'        => 'reviewers',
	'group-reviewer-member' => 'reviewer',
	'grouppage-reviewer'    => '{{ns:project}}:reviewer',
	
	'revreview-noflagged' => 'There are no reviewed revisions of this page, so it has \'\'\'not\'\'\' been
	 [[Help:Article validation|checked]] for quality.',
	'revreview-isnewest'  => 'This is the latest [[Help:Article validation|reviewed]] revision of this page (with 
	updated images and templates) [{{fullurl:Special:Log/review|page={{FULLPAGENAMEE}}}} approved] on <i>$1</i>.',
	'revreview-newest'    => 'The [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1}} latest reviewed revision] 
	([{{fullurl:{{FULLPAGENAMEE}}|oldid=$2&diff=$3}} compare]) was [{{fullurl:Special:Log/review|page={{FULLPAGENAMEE}}}} approved]
	 on <i>$4</i>, rated as:',
	'revreview-replaced'  => 'This is the latest [[Help:Article validation|stable]] revision of this page, 
	[{{fullurl:Special:Log/review|page={{FULLPAGENAMEE}}}} approved] on <i>$4</i>. The [{{fullurl:{{FULLPAGENAMEE}}|stable=false}} current revision]
	is editable and may be more up to date. There {{plural:$3|is $3 revision|are $3 revisions}} 
	([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} changes]) awaiting review.',
	'revreview-stable'  => 'This is a [[Help:Article validation|reviewed]] revision of the page \'\'\'$4\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} approved] on <i>$2</i>. The [{{fullurl:$4|oldid=$3}} current revision] 
	is editable and may be more up to date.',
	'revreview-note' => '[[User:$1]] made the following notes [[Help:Article validation|reviewing]] this revision:',

	'revreview-hist' => '[reviewed]',
    
    'flaggedrevs'        => 'Flagged Revisions',
    'review-logpage'     => 'Article review log',
	'review-logpagetext' => 'This is a log of changes to revisions\' [[Help:Article validation|approval]] status
	for content pages.',
	'review-logentrygrant'   => 'reviewed a version of [[$1]]',
	'review-logentryrevoke'  => 'depreciated a version of [[$1]]',
	'review-logaction'  => 'revision $1',
	

    'revisionreview'     => 'Review revisions',		
	'revreview-selected' => "Selected revision of '''$1:'''",
	'revreview-text'     => "Approved revisions are set as the default content on page view rather than the newest
	 revision. Their content will not be affected by changes to transcluded pages and they use the 
	''stable directory'' for internal images.",
	'revreview-images'   => "The following images on this page will be copied to the ''stable directory'' (updating 
	existing versions if you have reviewer status) and stored there until no reviewed revisions use them:",
	'revreview-flag'       => 'Review this revision (#$1):',
	'revreview-legend'     => 'Rate revision content:',
	'revreview-notes'      => 'Observations or notes to display:',
	'revreview-accuracy'   => 'Accuracy',
	'revreview-accuracy-0' => 'Unapproved',
	'revreview-accuracy-1' => 'Draft level',
	'revreview-accuracy-2' => 'Accurate',
	'revreview-accuracy-3' => 'Well sourced',
	'revreview-accuracy-4' => 'Featured',
	'revreview-depth'      => 'Depth',
	'revreview-depth-0'    => 'Unapproved',
	'revreview-depth-1'    => 'Stub',		
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
	'revreview-submit'     => 'Apply to selected revision',
	
	
	'stableversions'        => 'Stable versions',
	'stableversions-leg1'   => 'List reviewed revisions for a page',
	'stableversions-leg2'   => 'View a stable revision',
	'stableversions-page'   => 'Page name',
	'stableversions-rev'    => 'Revision ID',
	'stableversions-none'   => '[[$1]] has no reviewed revisions',
	'stableversions-list'   => 'The following revisions of [[$1]] have been reviewed:',
	'stableversions-review' => 'Reviewed on <i>$1</i>'
);
?>
