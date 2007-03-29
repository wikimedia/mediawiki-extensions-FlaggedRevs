<?php
/**
 * Internationalisation file for makevalidate extension.
 *
 * @package MediaWiki
 * @subpackage Extensions
*/

function efMakeValidateMessages() {
	$messages = array();
	
/* English (Aaron Schulz) */
$messages['en'] = array(
	'makevalidate'       => 'Promote/demote reviewers',
	'makevalidate-header'        => '<strong>This form is used by bureaucrats to turn ordinary users into stable version validators.</strong><br> Type the name of the user in the box and press the button to make the user a validator.',
	'makevalidate-username'        => 'Name of the user:',
	'makevalidate-search' => 'Go',
	'makevalidate-isvalidator' => '[[User:$1|$1]] has reviewer status.',
	'makevalidate-notvalidator' => '[[User:$1|$1]] does not have reviewer status.',
	'makevalidate-change' => 'Change status:',
	'makevalidate-grant' => 'Grant',
	'makevalidate-revoke' => 'Revoke',
	'makevalidate-comment' => 'Comment:',
	'makevalidate-granted' => '[[User:$1|$1]] now has validator status.',
	'makevalidate-revoked' => '[[User:$1|$1]] no longer has validator status.',
	'makevalidate-logpage' => 'Reviewer status log',
	'makevalidate-logpagetext' => 'This is a log of changes to users\' [[Help:Article validation|article validation]] status.',
	'makevalidate-logentrygrant' => 'granted validator status to [[$1]]',
	'makevalidate-logentryrevoke' => 'removed validator status from [[$1]]',
);

return $messages;
}
?>
