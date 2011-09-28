<?php

if ( getenv( 'MW_INSTALL_PATH' ) ) {
    $IP = getenv( 'MW_INSTALL_PATH' );
} else {
    $IP = dirname(__FILE__).'/../../..';
}

$options = array( 'updateonly', 'help', 'startrev', 'startpage' );
require "$IP/maintenance/commandLine.inc";
require dirname(__FILE__) . '/updateTracking.inc';

if ( isset($options['help']) ) {
	echo <<<TEXT
Purpose:
	Correct the page data in the flaggedrevs tracking tables.
	Update the quality tier of revisions based on their rating tags.
	Migrate flagged revision file version data to proper table.
Usage:
    php updateTracking.php --help
    php updateTracking.php [--startpage <ID> | --startrev <ID> | --updateonly <CALL> ]

    --help             : This help message
    --<ID>             : The ID of the starting rev/page
    --<CALL>           : One of (revs, pages, images)

TEXT;
	exit(0);
}

error_reporting( E_ALL );

$startPage = isset( $options['startpage'] ) ?
	(int)$options['startpage'] : null;
$startRev = isset( $options['startrev'] ) ?
	(int)$options['startrev'] : null;
$updateonly = isset( $options['updateonly'] ) ?
	$options['updateonly'] : null;

if ( $updateonly ) {
	switch ( $updateonly ) {
		case 'revs':
			update_flaggedrevs( $startRev );
			break;
		case 'pages':
			update_flaggedpages( $startPage );
			break;
		case 'images':
			update_flaggedimages( $startRev );
			break;
		default:
			echo "Invalidate operation specified.\n";
	}
	exit( 0 );
}

update_flaggedrevs( $startRev );

update_flaggedpages( $startPage );

update_flaggedimages( $startRev );
