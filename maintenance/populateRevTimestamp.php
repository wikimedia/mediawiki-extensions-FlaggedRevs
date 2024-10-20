<?php
/**
 * @ingroup Maintenance
 */

use MediaWiki\Maintenance\Maintenance;
use MediaWiki\WikiMap\WikiMap;

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

		$db = $this->getPrimaryDB();

		if ( $start === null ) {
			$start = $db->newSelectQueryBuilder()
				->select( 'MIN(fr_rev_id)' )
				->from( 'flaggedrevs' )
				->caller( __METHOD__ )
				->fetchField();
		}
		$end = $db->newSelectQueryBuilder()
			->select( 'MAX(fr_rev_id)' )
			->from( 'flaggedrevs' )
			->caller( __METHOD__ )
			->fetchField();
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

		while ( $blockEnd <= $end ) {
			$this->output( "...doing fr_rev_id from $blockStart to $blockEnd\n" );
			$res = $db->newSelectQueryBuilder()
				->select( [ 'fr_rev_id', 'rev_timestamp', 'ar_timestamp' ] )
				->from( 'flaggedrevs' )
				->leftJoin( 'revision', null, 'rev_id = fr_rev_id' )
				->leftJoin( 'archive', null, 'ar_rev_id = fr_rev_id' ) // non-unique but OK
				->where( [
					$db->expr( 'fr_rev_id', '>=', $blockStart ),
					$db->expr( 'fr_rev_id', '<=', $blockEnd ),
					'fr_rev_timestamp' => '',
				] )
				->caller( __METHOD__ )
				->fetchResultSet();
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
					$db->newUpdateQueryBuilder()
						->update( 'flaggedrevs' )
						->set( [ 'fr_rev_timestamp' => $timestamp ] )
						->where( [ 'fr_rev_id' => $row->fr_rev_id ] )
						->caller( __METHOD__ )
						->execute();
					$changed++;
				}
				$count++;
			}
			$db->commit( __METHOD__ );
			$blockStart += $this->mBatchSize;
			$blockEnd += $this->mBatchSize;
			$this->waitForReplication();
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
