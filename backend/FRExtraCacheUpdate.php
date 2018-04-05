<?php

use Wikimedia\Rdbms\ResultWrapper;

/**
 * Class containing cache update methods and job construction
 * for the special case of purging pages due to dependancies
 * contained only in the stable version of pages.
 *
 * These dependancies should be limited in number as most pages should
 * have a stable version synced with the current version.
 */
class FRExtraCacheUpdate implements DeferrableUpdate {
	public $mTitle, $mTable;
	public $mRowsPerJob, $mRowsPerQuery;

	public function __construct( Title $titleTo ) {
		global $wgUpdateRowsPerJob, $wgUpdateRowsPerQuery;
		$this->mTitle = $titleTo;
		$this->mTable = 'flaggedrevs_tracking';
		$this->mRowsPerJob = $wgUpdateRowsPerJob;
		$this->mRowsPerQuery = $wgUpdateRowsPerQuery;
	}

	public function doUpdate() {
		# Fetch the IDs
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( $this->mTable, $this->getFromField(),
			$this->getToCondition(), __METHOD__ );
		# Check if there is anything to do...
		if ( $dbr->numRows( $res ) > 0 ) {
			# Do it right now?
			if ( $dbr->numRows( $res ) <= $this->mRowsPerJob ) {
				$this->invalidateIDs( $res );
			# Defer to job queue...
			} else {
				$this->insertJobs( $res );
			}
		}
	}

	protected function insertJobs( ResultWrapper $res ) {
		$numRows = $res->numRows();
		if ( !$numRows ) {
			return; // sanity check
		}
		$numBatches = ceil( $numRows / $this->mRowsPerJob );
		$realBatchSize = ceil( $numRows / $numBatches );
		$jobs = [];
		do {
			$first = $last = false; // first/last page_id of this batch
			# Get $realBatchSize items (or less if not enough)...
			for ( $i = 0; $i < $realBatchSize; $i++ ) {
				$row = $res->fetchRow();
				# Is there another row?
				if ( $row ) {
					$id = $row[0];
					$last = $id; // $id is the last page_id of this batch
					if ( $first === false ) {
						$first = $id; // set first page_id of this batch
					}
				# Out of rows?
				} else {
					$id = false;
					break;
				}
			}
			# Insert batch into the queue if there is anything there
			if ( $first ) {
				$params = [
					'type'  => 'purge',
					'table' => $this->mTable,
					'start' => $first,
					'end'   => $last,
				];
				$jobs[] = new FRExtraCacheUpdateJob( $this->mTitle, $params );
			}
			$start = $id; // Where the last ID left off
		} while ( $start );

		JobQueueGroup::singleton()->push( $jobs );
	}

	public function getFromField() {
		return 'ftr_from';
	}

	public function getToCondition() {
		return [ 'ftr_namespace' => $this->mTitle->getNamespace(),
			'ftr_title' => $this->mTitle->getDBkey() ];
	}

	/**
	 * Invalidate a set of IDs, right now
	 * @param ResultWrapper $res
	 */
	public function invalidateIDs( ResultWrapper $res ) {
		global $wgUseFileCache, $wgUseSquid;
		if ( $res->numRows() == 0 ) {
			return; // sanity check
		}

		$dbw = wfGetDB( DB_MASTER );
		$timestamp = $dbw->timestamp();
		$done = false;

		while ( !$done ) {
			# Get all IDs in this query into an array
			$ids = [];
			for ( $i = 0; $i < $this->mRowsPerQuery; $i++ ) {
				$row = $res->fetchRow();
				if ( $row ) {
					$ids[] = $row[0];
				} else {
					$done = true;
					break;
				}
			}
			if ( count( $ids ) == 0 ) {
				break;
			}
			# Update page_touched
			$dbw->update( 'page', [ 'page_touched' => $timestamp ],
				[ 'page_id' => $ids ], __METHOD__ );
			# Update static caches
			if ( $wgUseSquid || $wgUseFileCache ) {
				$titles = Title::newFromIDs( $ids );
				# Update squid cache
				if ( $wgUseSquid ) {
					$u = CdnCacheUpdate::newFromTitles( $titles );
					$u->doUpdate();
				}
				# Update file cache
				if ( $wgUseFileCache ) {
					foreach ( $titles as $title ) {
						HTMLFileCache::clearFileCache( $title );
					}
				}
			}
		}
	}
}
