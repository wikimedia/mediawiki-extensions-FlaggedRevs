<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

/**
 * Job class for handling deferred FRExtraCacheUpdates
 * @ingroup JobQueue
 */
class FRExtraCacheUpdateJob extends Job {
	/**
	 * @param Title $title The title linked to
	 * @param array $params Job parameters (start and end page_ids)
	 */
	public function __construct( $title, $params ) {
		parent::__construct( 'flaggedrevs_CacheUpdate', $title, $params );

		$this->params['type'] ??= 'purge';
		// The range (start/end) make 'purge' jobs a bad candidate for de-duplication.
		$this->removeDuplicates = in_array(
			$this->params['type'], [ 'updatelinks', 'updatesyncstate' ]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function run() {
		if ( $this->params['type'] === 'purge' ) {
			return $this->doBacklinkPurge();
		} elseif ( $this->params['type'] === 'updatelinks' ) {
			return $this->doUpdateLinks();
		} elseif ( $this->params['type'] === 'updatesyncstate' ) {
			return $this->doUpdateSyncState();
		} else {
			throw new InvalidArgumentException( "Missing 'type' parameter." );
		}
	}

	/**
	 * @return bool
	 */
	private function doBacklinkPurge() {
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$update = new FRExtraCacheUpdate( $this->title );
		# Get query conditions
		$conds = $update->getToCondition();
		if ( $this->params['start'] ) {
			$conds[] = $dbr->expr( 'ftr_from', '>=', $this->params['start'] );
		}
		if ( $this->params['end'] ) {
			$conds[] = $dbr->expr( 'ftr_from', '<=', $this->params['end'] );
		}
		// Run query to get page Ids
		$pageIds = $dbr->newSelectQueryBuilder()
			->select( 'ftr_from' )
			->from( 'flaggedrevs_tracking' )
			->where( $conds )
			->caller( __METHOD__ )
			->fetchFieldValues();
		// Invalidate the pages
		$update->invalidateIDs( $pageIds );
		return true;
	}

	private function doUpdateLinks() {
		$fpage = FlaggableWikiPage::getTitleInstance( $this->title );
		$srev = $fpage->getStableRev();
		if ( $srev ) {
			$pOpts = $fpage->makeParserOptions( 'canonical' );
			$stableOut = FlaggedRevs::parseStableRevision( $srev, $pOpts );

			if ( $stableOut ) {
				// Update the stable-only dependency links right now
				$frDepUpdate = new FRDependencyUpdate( $this->title, $stableOut );
				$frDepUpdate->doUpdate( FRDependencyUpdate::IMMEDIATE );

				return true;
			}
		}

		// If not page or revision was found, remove the stable-only links
		FlaggedRevs::clearStableOnlyDeps( $fpage->getId() );
		return true;
	}

	private function doUpdateSyncState() {
		$fpage = FlaggableWikiPage::getTitleInstance( $this->title );
		if ( !$fpage->getId() || !$fpage->getStable() ) {
			return true;
		}

		$synced = $fpage->stableVersionIsSynced();
		if ( $fpage->syncedInTracking() != $synced ) {
			$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();

			$dbw->newUpdateQueryBuilder()
				->update( 'flaggedpages' )
				->set( [ 'fp_reviewed' => $synced ? 1 : 0 ] )
				->where( [ 'fp_page_id' => $fpage->getId() ] )
				->caller( __METHOD__ )
				->execute();
		}
		return true;
	}
}
