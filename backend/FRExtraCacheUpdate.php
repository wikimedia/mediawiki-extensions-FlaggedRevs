<?php

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IResultWrapper;

/**
 * Class containing cache update methods and job construction
 * for the special case of purging pages due to dependencies
 * contained only in the stable version of pages.
 *
 * These dependencies should be limited in number as most pages should
 * have a stable version synced with the current version.
 */
class FRExtraCacheUpdate implements DeferrableUpdate {
	/** @var Title */
	public $mTitle;
	/** @var string */
	public $mTable;

	/** @var int Copy of $wgUpdateRowsPerJob */
	public $mRowsPerJob;
	/** @var int Copy of $wgUpdateRowsPerQuery */
	public $mRowsPerQuery;

	public function __construct( Title $titleTo ) {
		global $wgUpdateRowsPerJob, $wgUpdateRowsPerQuery;
		$this->mTitle = $titleTo;
		$this->mTable = 'flaggedrevs_tracking';
		$this->mRowsPerJob = $wgUpdateRowsPerJob;
		$this->mRowsPerQuery = $wgUpdateRowsPerQuery;
	}

	/**
	 * @inheritDoc
	 */
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

	protected function insertJobs( IResultWrapper $res ) {
		$numRows = $res->numRows();
		if ( !$numRows ) {
			return; // sanity check
		}
		$numBatches = ceil( $numRows / $this->mRowsPerJob );
		$realBatchSize = ceil( $numRows / $numBatches );
		$jobs = [];
		do {
			// First/last page_id of this batch
			$first = false;
			$last = false;
			$id = null;
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
	 * @param IResultWrapper $res
	 */
	public function invalidateIDs( IResultWrapper $res ) {
		if ( $res->numRows() == 0 ) {
			return; // sanity check
		}

		$dbw = wfGetDB( DB_MASTER );
		$timestamp = $dbw->timestamp();
		$done = false;

		$hcu = MediaWikiServices::getInstance()->getHtmlCacheUpdater();

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
			$dbw->update(
				'page',
				[ 'page_touched' => $timestamp ],
				[ 'page_id' => $ids ],
				__METHOD__
			);

			# Update CDN
			$titles = Title::newFromIDs( $ids );
			$hcu->purgeTitleUrls( $titles, $hcu::PURGE_INTENT_TXROUND_REFLECTED );
		}
	}
}
