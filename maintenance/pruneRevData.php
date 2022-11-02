<?php
/**
 * @ingroup Maintenance
 */

if ( getenv( 'MW_INSTALL_PATH' ) ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

class PruneFRIncludeData extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( "This script clears template data for reviewed versions" .
			"that are 1+ hour old and have 50+ newer versions in page. By default," .
			"it will just output how many rows can be deleted. Use the 'prune' option " .
			"to actually delete them."
		);
		$this->addOption( 'prune', 'Actually do a live run' );
		$this->addOption( 'start', 'The ID of the starting rev', false, true );
		$this->addOption( 'sleep', 'Extra sleep time between each batch', false, true );
		$this->setBatchSize( 500 );
		$this->addOption(
			'rev-age',
			'Revisions older than this age will be deleted in seconds (default: 3600)',
			false,
			true
		);
		$this->addOption(
			'rev-num',
			'Revisions must have at least this number of reviewed revisions on top (default: 50)',
			false,
			true
		);
		$this->requireExtension( 'FlaggedRevs' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$start = $this->getOption( 'start' );
		$prune = $this->getOption( 'prune' );
		$this->pruneFlaggedRevs( $start, $prune );
	}

	/**
	 * @param int|null $start Revision ID
	 * @param bool $prune
	 */
	private function pruneFlaggedRevs( $start = null, $prune = false ) {
		if ( $prune ) {
			$this->output( "Pruning old flagged revision inclusion data...\n" );
		} else {
			$this->output( "Running dry-run of old flagged revision inclusion data pruning...\n" );
		}

		$dbw = wfGetDB( DB_PRIMARY );
		$dbr = wfGetDB( DB_REPLICA );

		if ( $start === null ) {
			$start = $dbr->selectField( 'flaggedpages', 'MIN(fp_page_id)', false, __METHOD__ );
		}
		$end = $dbr->selectField( 'flaggedpages', 'MAX(fp_page_id)', false, __METHOD__ );
		if ( $start === null || $end === null ) {
			$this->output( "...flaggedpages table seems to be empty.\n" );
			return;
		}
		$end += $this->mBatchSize - 1; # Do remaining chunk
		$blockStart = (int)$start;
		$blockEnd = (int)$start + $this->mBatchSize - 1;

		// Tally
		$tDeleted = 0;

		$newerRevs = (int)$this->getOption( 'rev-num', 50 );
		$sleep = (int)$this->getOption( 'sleep', 0 );
		$cutoff = $dbr->timestamp( time() - (int)$this->getOption( 'rev-age', 3600 ) );

		// First clean up revisions that don't exist anymore.
		while ( true ) {
			if ( !$prune ) {
				break;
			}
			$sres = $dbr->selectFieldValues(
				[ 'flaggedtemplates', 'revision' ],
				'ft_rev_id',
				[
					'rev_id' => null,
				],
				__METHOD__,
				[ 'LIMIT' => $this->mBatchSize ],
				[ 'revision' => [ 'LEFT JOIN', 'rev_id = ft_rev_id' ] ]
			);

			if ( !$sres ) {
				break;
			}
			$dbw->delete( 'flaggedtemplates',
				[ 'ft_rev_id' => $sres ],
				__METHOD__
			);
			$rowsCount = $dbw->affectedRows();
			$this->output( "...deleted $rowsCount rows\n" );
			$tDeleted += $rowsCount;
			$this->waitForReplication();
			sleep( $sleep );
		}

		while ( $blockEnd <= $end ) {
			$this->output( "...doing fp_page_id from $blockStart to $blockEnd\n" );
			$cond = "fp_page_id BETWEEN $blockStart AND $blockEnd";
			$res = $dbr->select( 'flaggedpages', 'fp_page_id', $cond, __METHOD__ );
			// Go through a chunk of flagged pages...
			$revsClearIncludes = [];
			foreach ( $res as $row ) {
				// Get the newest X ($newerRevs) flagged revs for this page
				$sres = $dbr->selectFieldValues( 'flaggedrevs',
					'fr_rev_id',
					[ 'fr_page_id' => $row->fp_page_id ],
					__METHOD__,
					[ 'ORDER BY' => 'fr_rev_id DESC', 'LIMIT' => $newerRevs ]
				);
				// See if there are older revs that can be pruned...
				if ( count( $sres ) !== $newerRevs ) {
					continue;
				}
				// Get the oldest of the top X revisions
				$oldestId = (int)$sres[ $newerRevs - 1 ];
				// Get revs not in the top X that were not reviewed recently
				$sres = $dbr->select( 'flaggedrevs',
					'fr_rev_id',
					[
						'fr_page_id' => $row->fp_page_id,
						'fr_rev_id < ' . $oldestId, // not in the newest X
						'fr_timestamp < ' . $dbr->addQuotes( $cutoff ) // not reviewed recently
					],
					__METHOD__,
					[ 'ORDER BY' => 'fr_rev_id ASC', 'LIMIT' => 5000 ]
				);
				// Build an array of these rev Ids
				foreach ( $sres as $srow ) {
					$revsClearIncludes[] = $srow->fr_rev_id;
				}
			}
			$blockStart += $this->mBatchSize;
			$blockEnd += $this->mBatchSize;
			if ( !$revsClearIncludes ) {
				continue;
			} elseif ( $prune ) {
				foreach ( array_chunk( $revsClearIncludes, $this->mBatchSize ) as $batch ) {
					$dbw->delete( 'flaggedtemplates',
						[ 'ft_rev_id' => $batch ],
						__METHOD__
					);
					$tDeleted += $dbw->affectedRows();
					$this->waitForReplication();
					sleep( $sleep );
				}
			} else {
				// Dry run: say how many includes rows would have been cleared
				$tDeleted += $dbr->selectField( 'flaggedtemplates',
					'COUNT(*)',
					[ 'ft_rev_id' => $revsClearIncludes ],
					__METHOD__
				);
			}
		}
		if ( $prune ) {
			$this->output( "Flagged revision inclusion prunning complete ...\n" );
		} else {
			$this->output( "Flagged revision inclusion prune test complete ...\n" );
		}
		$this->output( "Rows: \tflaggedtemplates:$tDeleted\n" );
	}
}

$maintClass = PruneFRIncludeData::class;
require_once RUN_MAINTENANCE_IF_MAIN;
