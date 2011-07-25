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
    --<ID>             : The ID of the starting rev or 'prev' (from last run)

TEXT;
	exit(0);
}

error_reporting( E_ALL );

$startRev = null;
if ( isset( $options['startrev'] ) ) {
	if ( $options['startrev'] === 'prev' ) {
		$startRev = (int)file_get_contents( last_pos_file() );
	} else {
		$startRev = (int)$options['startrev'];
	}
}

populate_fr_rev_timestamp( $startRev );
