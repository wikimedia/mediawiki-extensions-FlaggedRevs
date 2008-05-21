<?php

# Assume normal setup...
require dirname(__FILE__) . '/../../../maintenance/commandLine.inc';
require dirname(__FILE__) . '/reviewAllPages.inc';

if( isset($options['help']) || !isset($args[0]) ) {
	echo <<<TEXT
Usage:
    php refreshLinks.php --help
    php refreshLinks.php <username>

    --help               : This help message
    --<userid>           : The ID of the existing user to use as the "reviewer" (you can find your ID at Special:Preferences)

TEXT;
	exit(0);
}

error_reporting( E_ALL & (~E_NOTICE) );

$db = wfGetDB( DB_MASTER );
$user = User::newFromId( intval($args[0]) );

autoreview_current( $user, $db );
