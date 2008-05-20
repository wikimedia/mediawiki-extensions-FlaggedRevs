<?php

# Assume normal setup...
require dirname(__FILE__) . '/../../../maintenance/commandLine.inc';
require dirname(__FILE__) . '/updateLinks.inc';

error_reporting( E_ALL & (~E_NOTICE) );

$db = wfGetDB( DB_MASTER );

update_flaggedrevs( $db );

update_flaggedpages( $db );

