<?php
/*
* Defines global constants, some of which are used in LocalSettings.php
*/

# Query SELECT parameters...
define( 'FR_FOR_UPDATE', 1 );
define( 'FR_MASTER', 2 );
define( 'FR_TEXT', 3 );

# Review tier constants...
define( 'FR_CHECKED', 0 ); // "basic"/"checked"
define( 'FR_QUALITY', 1 );
define( 'FR_PRISTINE', 2 );

# Inclusion (templates/files) settings
define( 'FR_INCLUDES_CURRENT', 0 );
define( 'FR_INCLUDES_FREEZE', 1 );
define( 'FR_INCLUDES_STABLE', 2 );

# Autoreview settings for priviledged users
define( 'FR_AUTOREVIEW_NONE', 0 );
define( 'FR_AUTOREVIEW_CHANGES', 1 );
define( 'FR_AUTOREVIEW_CREATION', 2 );
define( 'FR_AUTOREVIEW_BOTH', FR_AUTOREVIEW_CHANGES | FR_AUTOREVIEW_CREATION );
