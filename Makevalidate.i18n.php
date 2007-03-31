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
	'makevalidate'       => 'Promote/demote editors',
	'makevalidate-header'   => '<strong>This form is used by sysops and bureaucrats to promote users to stable 
	version reviewers.</strong><br> Type the name of the user in the box and press the button 
	to make the user a validator. Granting users reviewer status will automatically grant them editor status as well.',
	'makevalidate-username'  => 'Name of the user:',
	'makevalidate-search' => 'Go',
	'makevalidate-iseditor' => '[[User:$1|$1]] has editor status.',
	'makevalidate-noteditor' => '[[User:$1|$1]] does not have editor status.',
	'makevalidate-isvalidator' => '[[User:$1|$1]] has reviewer status.',
	'makevalidate-notvalidator' => '[[User:$1|$1]] does not have reviewer status.',
	'makevalidate-change-e' => 'Change editor status:',
	'makevalidate-change-r' => 'Change reviewer status:',
	'makevalidate-grantE' => 'Grant',
	'makevalidate-revokeE' => 'Revoke',
	'makevalidate-grantR' => 'Grant',
	'makevalidate-revokeR' => 'Revoke',
	'makevalidate-comment' => 'Comment:',
	'makevalidate-granted-e' => '[[User:$1|$1]] now has editor status.',
	'makevalidate-revoked-e' => '[[User:$1|$1]] no longer has editor status.',
	'makevalidate-granted-r' => '[[User:$1|$1]] now has reviewer status.',
	'makevalidate-revoked-r' => '[[User:$1|$1]] no longer has reviewer status.',
	'makevalidate-logpage' => 'Reviewer status log',
	'makevalidate-logpagetext' => 'This is a log of changes to users\' [[Help:Article validation|article validation]] status.',
	'makevalidate-logentrygrant-e' => 'granted editor status to [[$1]]',
	'makevalidate-logentryrevoke-e' => 'removed editor status from [[$1]]',
	'makevalidate-logentrygrant-r' => 'granted reviewer status to [[$1]]',
	'makevalidate-logentryrevoke-r' => 'removed reviewer status from [[$1]]',
);

return $messages;
}
?>
