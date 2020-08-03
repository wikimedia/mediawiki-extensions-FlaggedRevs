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

use MediaWiki\MediaWikiServices;

class FixBug28348 extends Maintenance {

	public function __construct() {
		$this->addDescription( "Correct bad fi_img_timestamp rows due to bug 28348" );
		$this->addOption( 'startrev', 'The ID of the starting rev', false, true );
		$this->setBatchSize( 1000 );
		$this->requireExtension( 'FlaggedRevs' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$startRev = $this->getOption( 'startrev' );
		$this->update_images_bug_28348( $startRev );
	}

	protected function update_images_bug_28348( $start = null ) {
		$this->output( "Correcting fi_img_timestamp column in flaggedimages\n" );

		$db = wfGetDB( DB_MASTER );

		if ( $start === null ) {
			$start = $db->selectField( 'flaggedimages', 'MIN(fi_rev_id)', false, __METHOD__ );
		}
		$end = $db->selectField( 'flaggedimages', 'MAX(fi_rev_id)', false, __METHOD__ );
		if ( $start === null || $end === null ) {
			$this->output( "...flaggedimages table seems to be empty.\n" );
			return;
		}
		# Do remaining chunk
		$end += $this->mBatchSize - 1;
		$blockStart = (int)$start;
		$blockEnd = (int)$start + $this->mBatchSize - 1;
		$repoGroup = MediaWikiServices::getInstance()->getRepoGroup();

		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();

		$count = 0;
		$changed = 0;
		while ( $blockEnd <= $end ) {
			$this->output( "...doing fi_rev_id from $blockStart to $blockEnd\n" );
			$cond = "fi_rev_id BETWEEN $blockStart AND $blockEnd AND fi_img_timestamp IS NOT NULL" .
				" AND img_name IS NULL AND oi_name IS NULL"; // optimize
			$res = $db->select( [ 'flaggedimages', 'image', 'oldimage' ],
				'*',
				$cond,
				__METHOD__,
				[],
				[ // skip OK references to local files
					'image'    => [ 'LEFT JOIN',
						'img_sha1 = fi_img_sha1 AND img_timestamp = fi_img_timestamp' ],
					'oldimage' => [ 'LEFT JOIN',
						'oi_sha1 = fi_img_sha1 AND oi_timestamp = fi_img_timestamp' ]
				]
			);

			$db->begin( __METHOD__ );
			# Go through and clean up missing items, as well as correct fr_quality...
			foreach ( $res as $row ) {
				$count++;
				$fi_img_timestamp = trim( $row->fi_img_timestamp ); // clear pad garbage
				if ( !$fi_img_timestamp ) {
					continue; // nothing to check
				}
				$time = wfTimestamp( TS_MW, $fi_img_timestamp );
				$sha1 = $row->fi_img_sha1;
				# Check if the specified file exists...
				$file = $repoGroup->findFileFromKey( $sha1, [ 'time' => $time ] );
				if ( !$file ) { // doesn't exist?
					$time = wfTimestamp( TS_MW, wfTimestamp( TS_UNIX, $time ) + 1 );
					# Check if the fi_img_timestamp value is off by 1 second...
					$file = $repoGroup->findFileFromKey( $sha1, [ 'time' => $time ] );
					if ( $file ) {
						$this->output(
							"fixed file {$row->fi_name} reference in rev ID {$row->fi_rev_id}\n"
						);
						# Fix the fi_img_timestamp value...
						$db->update( 'flaggedimages',
							[ 'fi_img_timestamp' => $db->timestamp( $time ) ],
							[ 'fi_rev_id' => $row->fi_rev_id, 'fi_name' => $row->fi_name ],
							__METHOD__
						);
						$changed++;
					}
				}
			}
			$db->commit( __METHOD__ );
			$db->freeResult( $res );
			$blockStart += $this->mBatchSize;
			$blockEnd += $this->mBatchSize;
			$lbFactory->waitForReplication( [ 'ifWritesSince' => 5 ] );
		}
		$this->output(
			"fi_img_timestamp column fixes complete ... {$count} rows [{$changed} changed]\n"
		);
	}
}

$maintClass = FixBug28348::class;
require_once RUN_MAINTENANCE_IF_MAIN;
