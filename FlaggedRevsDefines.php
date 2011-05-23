<?php
/*
* Defines global constants, some of which are used in LocalSettings.php
*/

# Query SELECT parameters...
define( 'FR_FOR_UPDATE', 1 );
define( 'FR_MASTER', 2 );

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
define( 'FR_AUTOREVIEW_CREATION_AND_CHANGES', FR_AUTOREVIEW_CHANGES | FR_AUTOREVIEW_CREATION );

# Autopromote conds (F=70,R=82)
# @TODO: move these 5 to core
define( 'APCOND_FR_EDITSUMMARYCOUNT', 70821 );
define( 'APCOND_FR_NEVERBOCKED', 70822 );
define( 'APCOND_FR_UNIQUEPAGECOUNT', 70823 );
define( 'APCOND_FR_EDITSPACING', 70824 );
define( 'APCOND_FR_CONTENTEDITCOUNT', 70825 );

define( 'APCOND_FR_CHECKEDEDITCOUNT', 70826 );
