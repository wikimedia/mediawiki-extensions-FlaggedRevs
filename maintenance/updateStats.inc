<?php

if( php_sapi_name() != 'cli' ) {
	print( 'Invalid entry' );
	die( 1 );
}

function update_flaggedrevs_stats() {
	global $wgFlaggedRevsNamespaces, $wgFlaggedRevsStatsAge;
	// Set key to limit duplicate updates...
	$dbCache = wfGetCache( CACHE_DB );
	$keySQL = wfMemcKey( 'flaggedrevs', 'statsUpdating' );
	$dbCache->set( $keySQL, '1', $wgFlaggedRevsStatsAge );

	$dbr = wfGetDB( DB_SLAVE );
	list($page,$flaggedpages) = $dbr->tableNamesN('page','flaggedpages');
	$ns = $dbr->makeList( $wgFlaggedRevsNamespaces );
	if( empty($ns) ) return; // no SQL errors please :)

	print( "ValidationStatistics..." );

	$ns_total = array();
	$ns_reviewed = array();
	$ns_synced = array();
	// Get page count for each namespace
	$ret = $dbr->query( 
		"SELECT page_namespace AS namespace, COUNT(*) AS total 
		FROM $page WHERE page_is_redirect = 0 AND page_namespace IN($ns) 
		GROUP BY page_namespace"
	);
	while( $row = $dbr->fetchObject( $ret ) ) {
		$ns_total[$row->namespace] = intval($row->total);
	}
	// Get reviewed page count for each namespace
	$ret = $dbr->query( 
		"SELECT page_namespace AS namespace, COUNT(*) AS reviewed 
		FROM $flaggedpages, $page WHERE fp_page_id = page_id 
		AND page_is_redirect = 0 AND page_namespace IN($ns) 
		GROUP BY page_namespace"
	);
	while( $row = $dbr->fetchObject( $ret ) ) {
		$ns_reviewed[$row->namespace] = intval($row->reviewed);
	}
	// Get synced page count for each namespace
	$ret = $dbr->query( 
		"SELECT page_namespace AS namespace, COUNT(*) AS synced 
		FROM $flaggedpages, $page WHERE fp_page_id = page_id 
		AND fp_reviewed = 1 AND page_is_redirect = 0 AND page_namespace IN($ns) 
		GROUP BY page_namespace"
	);
	while( $row = $dbr->fetchObject( $ret ) ) {
		$ns_synced[$row->namespace] = intval($row->synced);
	}
	// Getting mean pending edit time
	$now = time(); // current time in UNIX TS
	$avePET = (int)$dbr->selectField( array('flaggedpages'),
		"AVG( $now - UNIX_TIMESTAMP(fp_pending_since) )",
		array( 'fp_pending_since IS NOT NULL' ),
		__METHOD__
	);
	// Get the mean edit review time
	$size = 1500; // Sample size
	$seconds = $aveRT = 0;
	# Only go so far back...otherwise we will get garbage values due to 
	# the fact that FlaggedRevs wasn't enabled until after a while.
	$installed = $dbr->selectField( 'logging', 'MIN(log_timestamp)', array('log_type' => 'review') );
	if( !$installed ) $installed = $dbr->timestamp(); // now
	# Skip the most recent recent revs as they are likely to just 
	# be WHERE condition misses. This also gives us more data to use.
	# Lastly, we want to avoid bais that would make the time too low
	# since new revisions could not have "took a long time to sight".
	$worstLagTS = $dbr->timestamp(); // now
	$last = '0';
	while( true ) { // should almost always be ~1 pass
		# Get the page with the worst pending lag...
		$row = $dbr->selectRow( array('flaggedpages','flaggedrevs'),
			array( 'fp_page_id', 'fp_stable', 'fp_pending_since', 'fr_timestamp' ),
			array( 
				'fp_pending_since > '.$dbr->addQuotes($installed), // needs actual display lag
				'fr_page_id = fp_page_id AND fr_rev_id = fp_stable',
				'fp_pending_since > '.$dbr->addQuotes($last), // skip failed rows
			),
			__METHOD__,
			array( 'ORDER BY' => 'fp_pending_since ASC',
				'USE INDEX' => array('flaggedpages' => 'fp_pending_since') )
		);
		if( !$row ) break;
		# Find the newest revision at the time the page was reviewed,
		# this is the one that *should* have been reviewed.
		$idealRev = (int)$dbr->selectField( 'revision', 'rev_id', 
			array( 'rev_page' => $row->fp_page_id, 
				'rev_timestamp < '.$dbr->addQuotes($row->fr_timestamp) ),
			__METHOD__,
			array( 'ORDER BY' => 'rev_timestamp DESC', 'LIMIT' => 1 )
		);
		# Fudge factor to prevent deliberate reviewing of non-current revisions
		# from squeezing the range. Shouldn't effect anything otherwise.
		if( $row->fp_stable >= $idealRev ) {
			$worstLagTS = $row->fp_pending_since;
			break;
		} else {
			$last = $row->fp_pending_since; // next iteration
		}
	}
	# Add a day for good measure to avoid bais
	$maxTS = $dbr->timestamp( wfTimestamp(TS_UNIX,$worstLagTS) - 86400 );
	# Use a one week time range
	$minTS = $dbr->timestamp( wfTimestamp(TS_UNIX,$worstLagTS) - 7*86400 );
	# Approximate the number rows to scan
	$rows = $dbr->estimateRowCount( 'revision', '1',
		'rev_timestamp BETWEEN '.$dbr->addQuotes($minTS).' AND '.$dbr->addQuotes($maxTS)
	);
	# If the range doesn't have many rows (like on small wikis), use 30 days
	if( $rows <= (2*$size) ) {
		$minTS = $dbr->timestamp( wfTimestamp(TS_UNIX,$worstLagTS) - 30*86400 );
		# Approximate rows to scan
		$rows = $dbr->estimateRowCount( 'revision', '1',
			'rev_timestamp BETWEEN '.$dbr->addQuotes($minTS).' AND '.$dbr->addQuotes($maxTS)
		);
		# If the range doesn't have many rows (like on really small wikis), use 90 days
		if( $rows <= (2*$size) ) {
			$minTS = $dbr->timestamp( wfTimestamp(TS_UNIX,$worstLagTS) - 90*86400 );
		}
	}
	# Sanity check the staring timestamp
	$minTS = max($minTS,$installed);
	# Get correspond rev_ids to boundaries
	$row = $dbr->selectRow( 'revision', array('MAX(rev_id) AS maxid','MIN(rev_id) AS minid'),
		array( 'rev_timestamp BETWEEN '.$dbr->addQuotes($minTS).' AND '.$dbr->addQuotes($maxTS) ),
		__METHOD__
	);
	$minRev = isset($row->minid) ? intval($row->minid) : 0;
	$maxRev = isset($row->maxid) ? intval($row->maxid) : 0;
	# Modulus
	$mod = intval($rows/$size);
	$mod = max($mod/2,1); # Not all rows have rev_user = 0; also $mod >= 1
	# For edits that started off pending, how long do they take to get reviewed?
	# Get the *first* reviewed rev *after* each RC item and get the average difference.
	# Only do this for revisions to pages that *were* already logged as reviewed.
	$sql = $dbr->selectSQLText( array('revision','flaggedrevs','page','logging'),
		array( 'rev_timestamp AS rt', 'MIN(fr_timestamp) AS ft', 'MIN(log_timestamp) AS lt' ),
		array(
			"rev_id BETWEEN $minRev AND $maxRev", // Better INDEX usage
			'rev_timestamp BETWEEN '.$dbr->addQuotes($minTS).' AND '.$dbr->addQuotes($maxTS),
			'rev_user = 0', // IP edits (should start off unreviewed)
			($mod > 1) ? "(rev_id % $mod) = 0" : "1 = 1", // Better spread
			'fr_page_id = rev_page AND fr_rev_id >= rev_id AND fr_timestamp > rev_timestamp', // later reviewed
			// Check that this page was stable at the time.
			// Assumes that reviewed pages stay reviewed (reasonable).
			'page_id = rev_page', // get the title
			'log_namespace = page_namespace AND log_title = page_title',
			'log_type = "review" AND log_timestamp < rev_timestamp',
		),
		__METHOD__,
		array(
			'GROUP BY'  => 'rev_id',
			'ORDER BY'  => 'rev_id ASC', // slight bias avoidance, if any
			'LIMIT'     => $size, // sample size
			'USE INDEX' => array('flaggedrevs' => 'PRIMARY','logging' => 'page_time')
		)
	);
	# Actually run the query...
	$res = $dbr->doQuery( $sql, __METHOD__ );
	if( $count = $dbr->numRows($res) ) {
		# Get the sum of elapsed times
		while( $row = $dbr->fetchObject($res) ) {
			$seconds += wfTimestamp(TS_UNIX,$row->ft) - wfTimestamp(TS_UNIX,$row->rt);
			#echo( "\nedit:".$row->rt . "\treviewed:" . $row->ft );
		}
		$aveRT = $seconds/$count; // average
	}
	// Save the data
	$dbw = wfGetDB( DB_MASTER );
	$dbw->begin();
	// Create small stats tables if not present
	list($flaggedrevs_stats,$flaggedrevs_stats2) = $dbr->tableNamesN('flaggedrevs_stats','flaggedrevs_stats2');
	if( !$dbw->tableExists( 'flaggedrevs_stats' ) ) {
		createFlaggedRevsStatsTable( $dbw, $flaggedrevs_stats );
	}
	if( !$dbw->tableExists( 'flaggedrevs_stats2' ) ) {
		createFlaggedRevsStatsTable2( $dbw, $flaggedrevs_stats2 );
	}
	foreach( $wgFlaggedRevsNamespaces as $namespace ) {
		$dbw->replace( 'flaggedrevs_stats',
			array( 'namespace' ),
			array( 'namespace' => intval($namespace),
				'total'    => isset($ns_total[$namespace]) ? $ns_total[$namespace] : 0,
				'reviewed' => isset($ns_reviewed[$namespace]) ? $ns_reviewed[$namespace] : 0,
				'synced'   => isset($ns_synced[$namespace]) ? $ns_synced[$namespace] : 0 ),
			__METHOD__
		);
	}
	$dbw->replace( 'flaggedrevs_stats2', array('stat_id'),
		array('stat_id' => 1, 'ave_review_time' => $aveRT, 'ave_pending_time' => $avePET),
		__METHOD__
	);
	$dbw->commit();
	// Stats are not up to date!
	$key = wfMemcKey( 'flaggedrevs', 'statsUpdated' );
	$dbCache->set( $key, '1', $wgFlaggedRevsStatsAge );
	$dbCache->delete( $keySQL );
	print( "done updating!\n" );
}

function createFlaggedRevsStatsTable( $dbw, $flaggedrevs_stats ) {
	$dbw->query( 
		"CREATE TABLE $flaggedrevs_stats (
			namespace  INTEGER UNSIGNED NOT NULL DEFAULT 0 PRIMARY KEY,
			total      INTEGER UNSIGNED NOT NULL DEFAULT 0,
			reviewed   INTEGER UNSIGNED NOT NULL DEFAULT 0,
			synced     INTEGER UNSIGNED NOT NULL DEFAULT 0
		);"
	);
}

function createFlaggedRevsStatsTable2( $dbw, $flaggedrevs_stats2 ) {
	$dbw->query( 
		"CREATE TABLE $flaggedrevs_stats2 (
			stat_id          INTEGER UNSIGNED NOT NULL DEFAULT 1 PRIMARY KEY,
			ave_review_time  INTEGER UNSIGNED NOT NULL DEFAULT 0,
			ave_pending_time INTEGER UNSIGNED NOT NULL DEFAULT 0
		);"
	);
}
