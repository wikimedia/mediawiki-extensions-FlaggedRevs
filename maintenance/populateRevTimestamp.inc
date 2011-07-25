<?php

function populate_fr_rev_timestamp( $start = null ) {
	echo "Populating and correcting flaggedrevs columns from $start\n";

	$BATCH_SIZE = 1000;

	$db = wfGetDB( DB_MASTER );

	if ( $start === null ) {
		$start = $db->selectField( 'flaggedrevs', 'MIN(fr_rev_id)', false, __FUNCTION__ );
	}
	$end = $db->selectField( 'flaggedrevs', 'MAX(fr_rev_id)', false, __FUNCTION__ );
	if ( is_null( $start ) || is_null( $end ) ) {
		echo "...flaggedrevs table seems to be empty.\n";
		return;
	}
	# Do remaining chunk
	$end += $BATCH_SIZE - 1;
	$blockStart = $start;
	$blockEnd = $start + $BATCH_SIZE - 1;
	$count = 0;
	$changed = 0;
	while ( $blockEnd <= $end ) {
		echo "...doing fr_rev_id from $blockStart to $blockEnd\n";
		$cond = "fr_rev_id BETWEEN $blockStart AND $blockEnd AND fr_rev_timestamp = ''";
		$res = $db->select(
			array( 'flaggedrevs', 'revision', 'archive' ),
			array( 'fr_rev_id', 'rev_timestamp', 'ar_timestamp' ),
			$cond,
			__FUNCTION__,
			array(),
			array( 'revision' => array( 'LEFT JOIN', 'rev_id = fr_rev_id' ),
				'archive' => array( 'LEFT JOIN', 'ar_rev_id = fr_rev_id' ) ) // non-unique but OK
		);
		$db->begin();
		# Go through and clean up missing items, as well as correct fr_quality...
		foreach ( $res as $row ) {
			$timestamp = '';
			if ( $row->rev_timestamp ) {
				$timestamp = $row->rev_timestamp;
			} elseif ( $row->ar_timestamp ) {
				$timestamp = $row->ar_timestamp;
			}
			if ( $timestamp != '' ) {
				# Update the row...
				$db->update( 'flaggedrevs',
					array( 'fr_rev_timestamp' 	=> $timestamp ),
					array( 'fr_rev_id' 			=> $row->fr_rev_id ),
					__FUNCTION__
				);
				$changed++;
			}
			$count++;
		}
		$db->commit();
		$db->freeResult( $res );
		$blockStart += $BATCH_SIZE;
		$blockEnd += $BATCH_SIZE;
		wfWaitForSlaves( 5 );
	}
	file_put_contents( last_pos_file(), $end );
	echo "fr_rev_timestamp columns update complete ... {$count} rows [{$changed} changed]\n";
}

function last_pos_file() {
	return dirname( __FILE__ ) . "/popRevTimestampLast-" . wfWikiID();
}
