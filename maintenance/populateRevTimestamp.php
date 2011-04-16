<?php

if ( getenv( 'MW_INSTALL_PATH' ) ) {
    $IP = getenv( 'MW_INSTALL_PATH' );
} else {
    $IP = dirname(__FILE__).'/../../..';
}

$options = array( 'help', 'startrev' );
require "$IP/maintenance/commandLine.inc";
require dirname(__FILE__) . '/populateRevTimestamp.inc';

if ( isset($options['help']) ) {
	echo <<<TEXT
Purpose:
	Populates fr_rev_timestamp column in the flaggedrevs table.
Usage:
    php populateRevTimestamp.php --help
    php populateRevTimestamp.php [--startrev <ID>]

    --help             : This help message
    --<ID>             : The ID of the starting rev

TEXT;
	exit(0);
}

error_reporting( E_ALL );

$startRev = isset( $options['startrev'] ) ?
	(int)$options['startrev'] : null;

populate_fr_rev_timestamp( $startRev );
