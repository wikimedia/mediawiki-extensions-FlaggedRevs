<?php

define( 'BATCH_SIZE', 25 );

function update_autopromote() {
	global $wgContentNamespaces, $wgFlaggedRevsAutopromote;
	echo "Populating and updating autopromote table\n";
	$db = wfGetDB( DB_SLAVE );
	$start = $db->selectField( 'user', 'MIN(user_id)', false, __FUNCTION__ );
	$end = $db->selectField( 'user', 'MAX(user_id)', false, __FUNCTION__ );
	if( is_null( $start ) || is_null( $end ) ){
		echo "...user table seems to be empty.\n";
		return;
	}
	# Do remaining chunk
	$end += BATCH_SIZE - 1;
	$blockStart = $start;
	$blockEnd = $start + BATCH_SIZE - 1;
	$count = 0;
	$changed = 0;
	while( $blockEnd <= $end ) {
		echo "...doing user_id from $blockStart to $blockEnd\n";
		$cond = "user_id BETWEEN $blockStart AND $blockEnd\n";
		$res = $db->select( array('user'), '*', $cond, __FUNCTION__ );
		# Go through and clean up missing items, as well as correct fr_quality...
		foreach( $res as $row ) {
			$user = User::newFromRow( $row );
			$p = FlaggedRevs::getUserParams( $user );
			$oldp = $p;
			# Get edit comments used
			$sres = $db->select( 'revision', '1',
				array( 'rev_user' => $user->getID(),
					'rev_comment != ""' ),
				__METHOD__,
				array( 'LIMIT' => $wgFlaggedRevsAutopromote['editComments'] )
			);
			$p['editComments'] = $db->numRows( $sres );
			$db->freeResult( $sres );
			# Get content page edits
			$sres = $db->select( array('revision','page'), '1',
				array( 'rev_user' => $user->getID(),
					'page_id = rev_page',
					'page_namespace' => $wgContentNamespaces ),
				__METHOD__,
				array( 'LIMIT' => $wgFlaggedRevsAutopromote['totalContentEdits'] )
			);
			$p['totalContentEdits'] = $db->numRows( $sres );
			$db->freeResult( $sres );
			# Get unique content pages edited
			$sres = $db->select( array('revision','page'), 'DISTINCT (rev_page)',
				array( 'rev_user' => $user->getID(),
					'page_id = rev_page', 
					'page_namespace' => $wgContentNamespaces ),
				__METHOD__,
				array( 'LIMIT' => $wgFlaggedRevsAutopromote['uniqueContentPages'] )
			);
			$p['uniqueContentPages'] = $db->numRows( $sres );
			$db->freeResult( $sres );
		
			FlaggedRevs::saveUserParams( $user, $p );
			if( $oldp != $p )
				$changed++;
		
			$count++;
		}
		$db->freeResult( $res );
		$blockStart += BATCH_SIZE - 1;
		$blockEnd += BATCH_SIZE - 1;
		wfWaitForSlaves( 5 );
	}
	echo "autopromote table update complete ... {$count} rows [{$changed} changed or added]\n";
}
