<?php

if ( getenv( 'MW_INSTALL_PATH' ) ) {
    $IP = getenv( 'MW_INSTALL_PATH' );
} else {
    $IP = dirname(__FILE__).'/../../..';
}
require "$IP/maintenance/commandLine.inc";
require dirname(__FILE__) . '/updateLinks.inc';

error_reporting( E_ALL );

update_flaggedrevs();

update_flaggedpages();

update_flaggedtemplates();

update_flaggedimages();
