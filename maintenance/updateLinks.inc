<?php

define( 'BATCH_SIZE',500 );

function update_flaggedrevs() {
	echo "Populating and correcting flaggedrevs columns\n";
	$db = wfGetDB( DB_MASTER );
	$start = $db->selectField( 'flaggedrevs', 'MIN(fr_rev_id)', false, __FUNCTION__ );
	$end = $db->selectField( 'flaggedrevs', 'MAX(fr_rev_id)', false, __FUNCTION__ );
	if( is_null( $start ) || is_null( $end ) ){
		echo "...flaggedrevs table seems to be empty.\n";
		return;
	}
	# Do remaining chunk
	$end += BATCH_SIZE - 1;
	$blockStart = $start;
	$blockEnd = $start + BATCH_SIZE - 1;
	$count = 0;
	$changed = 0;
	while( $blockEnd <= $end ) {
		echo "...doing fr_rev_id from $blockStart to $blockEnd\n";
		$cond = "fr_rev_id BETWEEN $blockStart AND $blockEnd AND page_id = fr_rev_id";
		$res = $db->select( array('flaggedrevs', 'page'),
			array('fr_rev_id','fr_tags','fr_quality','page_namespace','page_title',
				'fr_img_name','fr_img_timestamp','fr_img_sha1'), 
			$cond, __FUNCTION__ );
		# Go through and clean up missing items, as well as correct fr_quality...
		foreach( $res as $row ) {
			$tags = FlaggedRevision::expandRevisionTags( $row->fr_tags );
			# Quality rating levels may have changed due to config tweaks...
			if( FlaggedRevs::isPristine( $tags ) ) {
				$quality = 2;
			} else {
				$quality = FlaggedRevs::isQuality( $tags ) ? 1 : 0;
			}
			$time = $sha1 = $file = null;
			# Check for file version to see if stored the old way...
			if( !$row->fr_img_name && $row->page_namespace == NS_IMAGE ) {
				$irow = $db->selectRow( 'flaggedimages',
					array( 'fi_img_timestamp', 'fi_img_sha1' ),
					array( 'fi_rev_id' => $row->fr_rev_id,
						'fi_name' => $row->page_title ),
					__METHOD__ );
				$time = $row ? $row->fi_img_timestamp : null;
				$sha1 = $row ? $row->fi_img_sha1 : null;
				$file = $row ? $row->page_title : null;
			}
			if( $file && ($file != $row->fr_img_name || $time != $row->fr_img_timestamp || $sha1 != $row->fr_img_sha1) ) {
				$changed++;
			} else if ( $quality != $row->fr_quality ) {
				$changed++;
			}
			# Update the row...
			$db->update( 'flaggedrevs',
				array( 'fr_quality' => $quality,
					'fr_img_name' => $file,
					'fr_img_sha1' => $sha1,
					'fr_img_timestamp' => $time ),
				array( 'fr_rev_id' => $row->fr_rev_id ),
				__FUNCTION__ );
			$count++;
		}
		$db->freeResult( $res );
		$blockStart += BATCH_SIZE - 1;
		$blockEnd += BATCH_SIZE - 1;
		wfWaitForSlaves( 5 );
	}
	echo "fr_quality and fr_img_* columns update complete ... {$count} rows [{$changed} changed]\n";
}

function update_flaggedpages() {
	echo "Populating and correcting flaggedpages columns\n";
	$db = wfGetDB( DB_MASTER );
	$start = $db->selectField( 'page', 'MIN(page_id)', false, __FUNCTION__ );
	$end = $db->selectField( 'page', 'MAX(page_id)', false, __FUNCTION__ );
	if( is_null( $start ) || is_null( $end ) ){
		echo "...flaggedpages table seems to be empty.\n";
		return;
	}
	# Do remaining chunk
	$end += BATCH_SIZE - 1;
	$blockStart = $start;
	$blockEnd = $start + BATCH_SIZE - 1;
	$count = 0;
	$deleted = 0;
	while( $blockEnd <= $end ) {
		echo "...doing page_id from $blockStart to $blockEnd\n";
		$cond = "page_id BETWEEN $blockStart AND $blockEnd";
		$res = $db->select( 'page', array('page_id','page_namespace','page_title','page_latest'), $cond, __FUNCTION__ );
		# Go through and update the de-normalized references...
		foreach( $res as $row ) {
			$title = Title::makeTitle( $row->page_namespace, $row->page_title );
			# Replaces new fields into flaggedpages
			$frev = FlaggedRevs::getStablePageRev( $title, false, true );
			# Update fp_stable, fp_quality, and fp_reviewed
			if( $frev ) {
				$article = new Article( $title );
				FlaggedRevs::updateArticleOn( $article, $frev->getRevId(), $row->page_latest );
			# Somethings broke? Delete the row...
			} else {
				$db->delete( 'flaggedpages', 
					array( 'fp_page_id' => $row->fp_page_id ),
					__FUNCTION__ );
				if( $db->affectedRows() > 0 )
					$deleted++;
			}
			$count++;
		}
		$blockStart += BATCH_SIZE - 1;
		$blockEnd += BATCH_SIZE - 1;
		wfWaitForSlaves( 5 );
	}
	echo "flaggedpage columns update complete ... {$count} rows [{$deleted} deleted]\n";
}
