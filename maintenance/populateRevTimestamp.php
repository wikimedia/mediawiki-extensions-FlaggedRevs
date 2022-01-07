<?php
/**
 * @ingroup Maintenance
 */

use MediaWiki\MediaWikiServices;

if ( getenv( 'MW_INSTALL_PATH' ) ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

class PopulateFRRevTimestamp extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Populates fr_rev_timestamp column in the flaggedrevs table.' );
		$this->addOption( 'startrev', 'The ID of the starting rev', false, true );
		$this->setBatchSize( 1000 );
		$this->requireExtension( 'FlaggedRevs' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$startRev = $this->getOption( 'startrev' );
		if ( $startRev === 'prev' ) {
			$startRev = file_get_contents( $this->lastPosFile() );
		}
		if ( $startRev !== null ) {
			$startRev = (int)$startRev;
		}
		$this->populateFRRevTimestamp( $startRev );
	}

	/**
	 * @param int|null $start Revision ID
	 */
	private function populateFRRevTimestamp( $start = null ) {
		$this->output( "Populating and correcting flaggedrevs columns from $start\n" );

		$db = wfGetDB( DB_PRIMARY );

		if ( $start === null ) {
			$start = $db->selectField( 'flaggedrevs', 'MIN(fr_rev_id)', false, __METHOD__ );
		}
		$end = $db->selectField( 'flaggedrevs', 'MAX(fr_rev_id)', false, __METHOD__ );
		if ( $start === null || $end === null ) {
			$this->output( "...flaggedrevs table seems to be empty.\n" );
			return;
		}
		# Do remaining chunk
		$end += $this->mBatchSize - 1;
		$blockStart = (int)$start;
		$blockEnd = (int)$start + $this->mBatchSize - 1;
		$count = 0;
		$changed = 0;

		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();

		while ( $blockEnd <= $end ) {
			$this->output( "...doing fr_rev_id from $blockStart to $blockEnd\n" );
			$cond = "fr_rev_id BETWEEN $blockStart AND $blockEnd AND fr_rev_timestamp = ''";
			$res = $db->select(
				[ 'flaggedrevs', 'revision', 'archive' ],
				[ 'fr_rev_id', 'rev_timestamp', 'ar_timestamp' ],
				$cond,
				__METHOD__,
				[],
				[ 'revision' => [ 'LEFT JOIN', 'rev_id = fr_rev_id' ],
					'archive' => [ 'LEFT JOIN', 'ar_rev_id = fr_rev_id' ] ] // non-unique but OK
			);
			$db->begin( __METHOD__ );
			# Go through and clean up missing items
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
						[ 'fr_rev_timestamp'   => $timestamp ],
						[ 'fr_rev_id'          => $row->fr_rev_id ],
						__METHOD__
					);
					$changed++;
				}
				$count++;
			}
			$db->commit( __METHOD__ );
			$blockStart += $this->mBatchSize;
			$blockEnd += $this->mBatchSize;
			$lbFactory->waitForReplication( [ 'ifWritesSince' => 5 ] );
		}
		file_put_contents( $this->lastPosFile(), $end );
		$this->output( "fr_rev_timestamp columns update complete ..." .
			" {$count} rows [{$changed} changed]\n" );
	}

	/**
	 * @return string
	 */
	private function lastPosFile() {
		return __DIR__ . "/popRevTimestampLast-" . WikiMap::getCurrentWikiId();
	}
}

$maintClass = PopulateFRRevTimestamp::class;
require_once RUN_MAINTENANCE_IF_MAIN;
