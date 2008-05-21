<?php

# Assume normal setup...
require dirname(__FILE__) . '/../../../maintenance/commandLine.inc';
require dirname(__FILE__) . '/updateLinks.inc';

error_reporting( E_ALL );

update_flaggedrevs();

update_flaggedpages();

