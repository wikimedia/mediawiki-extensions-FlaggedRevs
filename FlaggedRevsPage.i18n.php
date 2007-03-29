<?php
$RevisionreviewMessages = array();

// English (Aaron Schulz)
$RevisionreviewMessages['en'] = array( 
	'reviewer'           => 'Reviewer',
	'group-reviewer'     => 'Reviewers',
	'group-reviewer'     => 'Reviewer',
	'grouppage-reviewer' => '{{ns:project}}:Reviewer',
	
	'revreview-noflagged' => 'There are no reviewed revisions of this page, so it has \'\'\'not\'\'\' been
	 [[Help:Article validation|checked]] for quality.',
	'revreview-isnewest'  => 'This is the latest [[Help:Article validation|reviewed]] revision of this page (with 
	updated images and templates) [{{fullurl:Special:Log/review|page={{FULLPAGENAMEE}}}} approved] on <i>$1</i>.',
	'revreview-newest'    => 'The [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1}} latest reviewed revision] 
	([{{fullurl:{{FULLPAGENAMEE}}|oldid=$2&diff=$3}} compare]) was [{{fullurl:Special:Log/review|page={{FULLPAGENAMEE}}}} approved]
	 on <i>$4</i>, rated as:',
	'revreview-replaced'  => 'This is the latest [[Help:Article validation|reviewed]] revision of this page, 
	[{{fullurl:Special:Log/review|page={{FULLPAGENAMEE}}}} approved] on <i>$4</i>. The [{{fullurl:{{FULLPAGENAMEE}}|oldid=$2}} current revision]
	is editable and may be more up to date. There {{plural:$3|is $3 revision|are $3 revisions}} 
	([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} changes]) awaiting review.',

    'revisionreview'     => 'Review revisions',
    
    'flaggedrevs'        => 'Flagged Revisions',
    'review-logpage'     => 'Article review log',
	'review-logpagetext' => 'This is a log of changes to revisions\' [[Help:Article validation|approval]] status
	for content pages.',
	'review-logentrygrant'   => 'approved [[$1]]',
	'review-logentryrevoke'  => 'unapproved [[$1]]',
	'review-logaction'  => 'reviewed revision $1',
		
	'revreview-selected' => "Selected revision of '''$1:'''",
	'revreview-text'     => "Approved revisions are set as the default revision shown upon page view rather
	than the top revision. The content of this approved revision will remain constant regardless of any transcluded
	pages or internal images. Users on this wiki will still be able to access 
	unreviewed content through the page history.",
	'revreview-images'   => 'Internal images on this page will be copied to the stable image directory, updating
	existing versions, and stored there until no reviewed revisions use them. The following images are transcluded onto this page:',
	
	'revreview-hist'     => '[reviewed]',
	
	'revreview-note'     => '[[User:$1]] made the following notes [[Help:Article validation|reviewing]] this revision:',

	'revreview-flag'     => 'Review this revision (#$1):',
	'revreview-legend'   => 'Rate revision content:',
	'revreview-notes'    => 'Observations or notes to display:',
	'revreview-acc'      => 'Accuracy',
	'revreview-acc-0'    => 'Unapproved',
	'revreview-acc-1'    => 'Not vandalized',
	'revreview-acc-2'    => 'Accurate',
	'revreview-acc-3'    => 'Well sourced',
	'revreview-depth'    => 'Depth',
	'revreview-depth-0'  => 'Unapproved',
	'revreview-depth-1'  => 'Stub',		
	'revreview-depth-2'  => 'Moderate',
	'revreview-depth-3'  => 'Complete',
	'revreview-style'    => 'Readability',
	'revreview-style-0'  => 'Unapproved',
	'revreview-style-1'  => 'Acceptable',
	'revreview-style-2'  => 'Good',
	'revreview-style-3'  => 'Concise',
	'revreview-log'      => 'Log comment:',
	'revreview-submit'   => 'Apply to selected revision',
);
?>