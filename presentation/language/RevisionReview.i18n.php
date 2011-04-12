<?php
/**
 * Internationalisation file for FlaggedRevs extension, section RevisionReview
 *
 * @file
 * @ingroup Extensions
 */

$messages = array();

/** English (en)
 * @author Purodha
 * @author Raimond Spekking
 * @author Siebrand
 */

$messages['en'] = array(
	'revisionreview'               => 'Review revisions',
	'revreview-failed'             => "'''Unable to review this revision.'''",
	'revreview-submission-invalid' => "The submission was incomplete or otherwise invalid.",

	'review_page_invalid'      => 'The target page title is invalid.',
	'review_page_notexists'    => 'The target page does not exist.',
	'review_page_unreviewable' => 'The target page is not reviewable.',
	'review_no_oldid'          => 'No revision ID specified.',
	'review_bad_oldid'         => 'The target revision does not exist.',
	'review_conflict_oldid'    => 'Someone already accepted or unaccepted this revision while you were viewing it.',
	'review_not_flagged'       => 'The target revision is not currently marked as reviewed.',
	'review_too_low'           => 'Revision cannot be reviewed with some fields left "inadequate".',
	'review_bad_key'           => 'Invalid inclusion parameter key.',
	'review_bad_tags'          => 'Some of the specified tag values are invalid.',
	'review_denied'            => 'Permission denied.',
	'review_param_missing'     => 'A parameter is missing or invalid.',
	'review_cannot_undo'       => 'Cannot undo these changes because further pending edits changed the same areas.',
	'review_cannot_reject'     => 'Cannot reject these changes because someone already accepted some (or all) of the edits.',
	'review_reject_excessive'  => 'Cannot reject this many edits at once.',

	'revreview-check-flag-p'       => 'Accept this version (includes $1 pending {{PLURAL:$1|change|changes}})',
	'revreview-check-flag-p-title' => 'Accept the result of the pending changes and the changes you made here. Use this only if you have already seen the entire pending changes diff.',
	'revreview-check-flag-u'       => 'Accept this unreviewed page',
	'revreview-check-flag-u-title' => 'Accept this version of the page. Only use this if you have already seen the entire page.',
	'revreview-check-flag-y'       => 'Accept my changes',
	'revreview-check-flag-y-title' => 'Accept all the changes that you have made here.',

	'revreview-flag'               => 'Review this revision',
	'revreview-reflag'             => 'Re-review this revision',
	'revreview-invalid'            => '\'\'\'Invalid target:\'\'\' no [[{{MediaWiki:Validationpage}}|reviewed]] revision corresponds to the given ID.',
	'revreview-legend'             => 'Rate revision content',
	'revreview-log'                => 'Comment:',
	'revreview-main'               => 'You must select a particular revision of a content page in order to review.

See the [[Special:Unreviewedpages|list of unreviewed pages]].',
	'revreview-stable1'            => 'You may want to view [{{fullurl:$1|stableid=$2}} this flagged version] and see if it is now the [{{fullurl:$1|stable=1}} stable version] of this page.',
	'revreview-stable2'            => 'You may want to view the [{{fullurl:$1|stable=1}} stable version] of this page.',
	'revreview-submit'             => 'Submit',
	'revreview-submitting'         => 'Submitting...',
	'revreview-submit-review'      => 'Accept revision',
	'revreview-submit-unreview'    => 'Unaccept revision',
	'revreview-submit-reject'      => 'Reject changes',
	'revreview-submit-reviewed'    => 'Done. Accepted!',
	'revreview-submit-unreviewed'  => 'Done. Unaccepted!',
	'revreview-successful'         => '\'\'\'Revision of [[:$1|$1]] successfully flagged. ([{{fullurl:{{#Special:ReviewedVersions}}|page=$2}} view reviewed versions])\'\'\'',
	'revreview-successful2'        => '\'\'\'Revision of [[:$1|$1]] successfully unflagged.\'\'\'',
	'revreview-poss-conflict-p'    => '\'\'\'Warning: [[User:$1|$1]] started reviewing this page on $2 at $3.\'\'\'',
	'revreview-poss-conflict-c'    => '\'\'\'Warning: [[User:$1|$1]] started reviewing these changes on $2 at $3.\'\'\'',
	'revreview-toolow'             => '\'\'\'You must rate each of the attributes higher than "inadequate" in order for a revision to be considered reviewed.\'\'\'

To remove the review status of a revision, click "unaccept".

Please hit the "back" button in your browser and try again.',
	'revreview-update'             => '\'\'\'Please [[{{MediaWiki:Validationpage}}|review]] any pending changes \'\'(shown below)\'\' made since the stable version.\'\'\'',
	'revreview-update-edited'      => '<span class="flaggedrevs_important">Your changes are not yet in the stable version.</span>

Please review all the changes shown below to make your edits appear in the stable version.',
	'revreview-update-edited-prev'  => '<span class="flaggedrevs_important">Your changes are not yet in the stable version. There are previous changes pending review.</span>

Please review all the changes shown below to make your edits appear in the stable version.',
	'revreview-update-includes'    => '\'\'\'Some templates/files were updated:\'\'\'',
	'revreview-update-use'         => '\'\'\'NOTE:\'\'\' The stable version of each of these templates/files is used in the stable version of this page.',

	'revreview-reject-header'      => 'Reject changes for $1',
	'revreview-reject-text-list'   => 'By completing this action, you will be \'\'\'rejecting\'\'\' the following {{PLURAL:$1|change|changes}}:',
	'revreview-reject-text-revto'  => 'This will revert the page back to the [{{fullurl:$1|oldid=$2}} version as of $3].',
	'revreview-reject-summary'     => 'Summary:',
	'revreview-reject-confirm'     => 'Reject these changes',
	'revreview-reject-cancel'      => 'Cancel',
	'revreview-reject-summary-cur' => 'Rejected the last {{PLURAL:$1|change|$1 changes}} (by $2) and restored revision $3 by $4',
	'revreview-reject-summary-old' => 'Rejected the first {{PLURAL:$1|change|$1 changes}} (by $2) that followed revision $3 by $4',
	'revreview-reject-summary-cur-short' => 'Rejected the last {{PLURAL:$1|change|$1 changes}} and restored revision $2 by $3',
	'revreview-reject-summary-old-short' => 'Rejected the first {{PLURAL:$1|change|$1 changes}} that followed revision $2 by $3',
	'revreview-reject-usercount'    => '{{PLURAL:$1|one user|$1 users}}',

	'revreview-tt-flag'            => 'Accept this revision by marking it as "checked"',
	'revreview-tt-unflag'		   => 'Unaccept this revision by marking it as "unchecked"',
	'revreview-tt-reject'		   => 'Reject these changes by reverting them',
);
