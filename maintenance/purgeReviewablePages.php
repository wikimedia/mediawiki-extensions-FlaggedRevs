<?php

if ( getenv( 'MW_INSTALL_PATH' ) ) {
    $IP = getenv( 'MW_INSTALL_PATH' );
} else {
    $IP = dirname(__FILE__).'/../../..';
}
require "$IP/maintenance/commandLine.inc";
require dirname(__FILE__) . '/purgeReviewablePages.inc';

if( isset( $options['help'] ) ) {
	echo <<<TEXT
Purpose:
	Purge squid/file cache for all reviewable pages
Usage:
    php purgeReviewablePages.php --help

    --help               : This help message

TEXT;
	exit(0);
}

error_reporting( E_ALL );

$db = wfGetDB( DB_MASTER );
purge_reviewable_pages( $db );
