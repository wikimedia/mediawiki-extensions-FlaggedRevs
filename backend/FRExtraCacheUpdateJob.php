<?php
/**
 * Job class for handling deferred FRExtraCacheUpdates
 * @ingroup JobQueue
 */
class FRExtraCacheUpdateJob extends Job {
	/**
	 * Construct a job
	 * @param Title $title The title linked to
	 * @param array $params Job parameters (table, start and end page_ids)
	 */
	function __construct( $title, $params ) {
		parent::__construct( 'flaggedrevs_CacheUpdate', $title, $params );

		$this->params['type'] = isset( $this->params['type'] )
			? $this->params['type']
			: 'purge';
	}

	function run() {
		if ( $this->params['type'] === 'purge' ) {
			$this->doBacklinkPurge();
		} elseif ( $this->params['type'] === 'updatelinks' ) {
			$this->doUpdateLinks();
		} elseif ( $this->params['type'] === 'updatesyncstate' ) {
			$this->doUpdateSyncState();
		} else {
			throw new InvalidArgumentException( "Missing 'type' parameter." );
		}
	}

	protected function doBacklinkPurge() {
		$update = new FRExtraCacheUpdate( $this->title );
		# Get query conditions
		$fromField = $update->getFromField();
		$conds = $update->getToCondition();
		if ( $this->params['start'] ) {
			$conds[] = "$fromField >= {$this->params['start']}";
		}
		if ( $this->params['end'] ) {
			$conds[] = "$fromField <= {$this->params['end']}";
		}
		# Run query to get page Ids
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( $this->params['table'], $fromField, $conds, __METHOD__ );
		# Invalidate the pages
		$update->invalidateIDs( $res );
		return true;
	}

	protected function doUpdateLinks() {
		$fpage = FlaggableWikiPage::getTitleInstance( $this->title );
		$srev = $fpage->getStableRev();
		if ( $srev ) {
			$pOpts = $fpage->makeParserOptions( 'canonical' );
			$stableOut = FlaggedRevs::parseStableRevision( $srev, $pOpts );

			if ( $stableOut ) {
				// Update the stable-only dependency links right now
				$frDepUpdate = new FRDependencyUpdate( $this->title, $stableOut );
				$frDepUpdate->doUpdate( FRDependencyUpdate::IMMEDIATE );

				return;
			}
		}

		// If not page or revision was found, remove the stable-only links
		FlaggedRevs::clearStableOnlyDeps( $fpage->getId() );
	}

	protected function doUpdateSyncState() {
		$fpage = FlaggableWikiPage::getTitleInstance( $this->title );
		if ( !$fpage->getId() || !$fpage->getStable() ) {
			return;
		}

		$synced = $fpage->stableVersionIsSynced();
		if ( $fpage->syncedInTracking() != $synced ) {
			$dbw = wfGetDB( DB_MASTER );
			$dbw->update( 'flaggedpages',
				array( 'fp_reviewed' => $synced ? 1 : 0 ),
				array( 'fp_page_id' => $fpage->getId() ),
				__METHOD__
			);
		}
	}
}
