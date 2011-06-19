<?php

if( php_sapi_name() != 'cli' ) {
	print( 'Invalid entry' );
	die( 1 );
}

function update_flaggedrevs_querycache() {
	print sprintf( '%-30s ', 'UnreviewedPages' );

    $time1 = microtime( true );
    UnreviewedPages::updateQueryCache();
    $time2 = microtime( true );

	$ellapsed = ( $time2 - $time1 );
	print sprintf( "completed in %.2fs\n", $ellapsed );
}
