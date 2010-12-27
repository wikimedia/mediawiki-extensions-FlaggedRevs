<?php

function update_autopromote() {
	global $wgContentNamespaces, $wgFlaggedRevsAutopromote;
	$batchSize = 50;
	echo "Populating and updating flaggedrevs_promote table\n";

	$dbr = wfGetDB( DB_SLAVE );
	$dbw = wfGetDB( DB_MASTER );
	$start = $dbr->selectField( 'user', 'MIN(user_id)', false, __FUNCTION__ );
	$end = $dbr->selectField( 'user', 'MAX(user_id)', false, __FUNCTION__ );
	if( is_null( $start ) || is_null( $end ) ){
		echo "...user table seems to be empty.\n";
		return;
	}
	$count = 0;
	$changed = 0;
	for ( $blockStart = $start; $blockStart <= $end; $blockStart += $batchSize ) {
		$blockEnd = min( $end, $blockStart + $batchSize - 1 );
		echo "...doing user_id from $blockStart to $blockEnd\n";
		$cond = "user_id BETWEEN $blockStart AND $blockEnd\n";
		$res = $dbr->select( 'user', '*', $cond, __FUNCTION__ );
		# Go through and clean up missing items, as well as correct fr_quality...
		foreach( $res as $row ) {
			$dbw->begin();
			$user = User::newFromRow( $row );
			$p = FRUserCounters::getUserParams( $user->getId(), FR_FOR_UPDATE );
			$oldp = $p;
			# Get edit comments used
			$sres = $dbr->select( 'revision', '1',
				array( 'rev_user' => $user->getID(),
					"rev_comment NOT LIKE '/*%*/'" ), // manual comments only
				__METHOD__,
				array( 'LIMIT' => max($wgFlaggedRevsAutopromote['editComments'],500) )
			);
			$p['editComments'] = $dbr->numRows( $sres );
			# Get content page edits
			$sres = $dbr->select( array('revision','page'), '1',
				array( 'rev_user' => $user->getID(),
					'page_id = rev_page',
					'page_namespace' => $wgContentNamespaces ),
				__METHOD__,
				array( 'LIMIT' => max($wgFlaggedRevsAutopromote['totalContentEdits'],500) )
			);
			$p['totalContentEdits'] = $dbr->numRows( $sres );
			# Get unique content pages edited
			$sres = $dbr->select( array('revision','page'), 'DISTINCT(rev_page)',
				array( 'rev_user' => $user->getID(),
					'page_id = rev_page', 
					'page_namespace' => $wgContentNamespaces ),
				__METHOD__,
				array( 'LIMIT' => max($wgFlaggedRevsAutopromote['uniqueContentPages'],50) )
			);
			$p['uniqueContentPages'] = array();
			foreach( $sres as $row ) {
				$p['uniqueContentPages'][] = (int)$row->rev_page;
			}
			# Save the new params...
			if( $oldp != $p ) {
				FRUserCounters::saveUserParams( $user->getId(), $p );
				$changed++;
			}
		
			$count++;
			$dbw->commit();
		}
		wfWaitForSlaves( 5 );
	}
	echo "flaggedrevs_promote table update complete ... {$count} rows [{$changed} changed or added]\n";
}
