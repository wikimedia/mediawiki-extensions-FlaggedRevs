<?php

# Assume normal setup...
require dirname(__FILE__) . '/../../../maintenance/commandLine.inc';
require dirname(__FILE__) . '/updateAutoPromote.inc';

error_reporting( E_ALL );

update_autopromote();

