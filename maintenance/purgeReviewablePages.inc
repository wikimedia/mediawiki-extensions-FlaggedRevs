<?php

function purge_reviewable_pages( $db ) {
	global $wgFlaggedRevsNamespaces, $wgUseSquid, $wgUseFileCache;
	echo "Purging squid cache for all reviewable pages...\n";
	if ( !$wgUseSquid || !$wgUseFileCache ) {
		echo "Squid/file cache not enabled...nothing to purge.\n";
	}
	$BATCH_SIZE = 500;
	$start = $db->selectField( 'page', 'MIN(page_id)', false, __FUNCTION__ );
	$end = $db->selectField( 'page', 'MAX(page_id)', false, __FUNCTION__ );
	if ( is_null( $start ) || is_null( $end ) ){
		echo "...page table seems to be empty.\n";
		return;
	}
	# Do remaining chunk
	$end += $BATCH_SIZE - 1;
	$blockStart = $start;
	$blockEnd = $start + $BATCH_SIZE - 1;
	$count = 0;
	while ( $blockEnd <= $end ) {
		echo "...doing page_id from $blockStart to $blockEnd\n";
		$res = $db->select( 'page', '*', 
			array(
				"page_id BETWEEN $blockStart AND $blockEnd",
				'page_namespace' => $wgFlaggedRevsNamespaces ),
			__FUNCTION__
		);
		# Go through and protect each page...
		foreach ( $res as $row ) {
			$title = Title::newFromRow( $row );
			$fa = FlaggedArticle::getTitleInstance( $title );
			if ( $fa->isReviewable( FR_MASTER ) ) {
				$title->purgeSquid(); // send PURGE
				HTMLFileCache::clearFileCache( $title ); // purge poor-mans's squid
				$count++;
			}
		}
		$db->freeResult( $res );
		$blockStart += $BATCH_SIZE - 1;
		$blockEnd += $BATCH_SIZE - 1;
		wfWaitForSlaves( 5 );
	}
	echo "Squid/file cache purge of all reviewable pages complete ... {$count} pages\n";
}
