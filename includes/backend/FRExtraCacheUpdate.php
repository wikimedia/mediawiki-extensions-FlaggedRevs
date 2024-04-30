<?php

use MediaWiki\Deferred\DeferrableUpdate;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

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
	private $mTitle;

	/** @var int Copy of $wgUpdateRowsPerJob */
	private $mRowsPerJob;
	/** @var int Copy of $wgUpdateRowsPerQuery */
	private $mRowsPerQuery;

	/**
	 * @param Title $titleTo
	 */
	public function __construct( Title $titleTo ) {
		global $wgUpdateRowsPerJob, $wgUpdateRowsPerQuery;
		$this->mTitle = $titleTo;
		$this->mRowsPerJob = $wgUpdateRowsPerJob;
		$this->mRowsPerQuery = $wgUpdateRowsPerQuery;
	}

	/**
	 * @inheritDoc
	 */
	public function doUpdate() {
		# Fetch the IDs
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$pageIds = $dbr->newSelectQueryBuilder()
			->select( 'ftr_from' )
			->from( 'flaggedrevs_tracking' )
			->where( $this->getToCondition() )
			->caller( __METHOD__ )
			->fetchFieldValues();
		# Check if there is anything to do...
		if ( $pageIds ) {
			# Do it right now?
			if ( count( $pageIds ) <= $this->mRowsPerJob ) {
				$this->invalidateIDs( $pageIds );
			# Defer to job queue...
			} else {
				$this->insertJobs( $pageIds );
			}
		}
	}

	/**
	 * @param int[] $pageIds
	 */
	private function insertJobs( array $pageIds ) {
		if ( !$pageIds ) {
			return; // sanity check
		}
		$jobs = [];
		sort( $pageIds );
		$start = reset( $pageIds );
		foreach ( $pageIds as $i => $id ) {
			$next = $pageIds[$i + 1] ?? null;
			if ( !$next || $next > $id + 1 || $next - $start >= $this->mRowsPerJob ) {
				$jobs[] = new FRExtraCacheUpdateJob( $this->mTitle, [
					'type' => 'purge',
					'start' => $start,
					'end' => $id,
				] );
				$start = $next;
			}
		}

		MediaWikiServices::getInstance()->getJobQueueGroup()->push( $jobs );
	}

	/**
	 * @return array
	 */
	public function getToCondition() {
		return [
			'ftr_namespace' => $this->mTitle->getNamespace(),
			'ftr_title' => $this->mTitle->getDBkey(),
		];
	}

	/**
	 * Invalidate a set of IDs, right now
	 * @param int[] $pageIds
	 */
	public function invalidateIDs( array $pageIds ) {
		if ( !$pageIds ) {
			return; // sanity check
		}

		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

		$timestamp = $dbw->timestamp();

		$hcu = MediaWikiServices::getInstance()->getHtmlCacheUpdater();

		foreach ( array_chunk( $pageIds, $this->mRowsPerQuery ) as $ids ) {
			# Update page_touched
			$dbw->newUpdateQueryBuilder()
				->update( 'page' )
				->set( [ 'page_touched' => $timestamp ] )
				->where( [ 'page_id' => $ids ] )
				->caller( __METHOD__ )
				->execute();

			# Update CDN
			$titles = MediaWikiServices::getInstance()
				->getPageStore()
				->newSelectQueryBuilder()
				->wherePageIds( $ids )
				->caller( __METHOD__ )
				->fetchPageRecords();
			$hcu->purgeTitleUrls( $titles, $hcu::PURGE_INTENT_TXROUND_REFLECTED );
		}
	}
}
