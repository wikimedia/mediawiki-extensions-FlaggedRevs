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

class UpdateFRAutoPromote extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = "Update autopromote table";
		$this->setBatchSize( 50 );
	}

	public function execute() {
		global $wgFlaggedRevsAutopromote;
		$this->output( "Populating and updating flaggedrevs_promote table\n" );

		$revQuery = Revision::getQueryInfo();
		$revPageQuery = Revision::getQueryInfo( [ 'page' ] );
		$dbr = wfGetDB( DB_REPLICA );
		$dbw = wfGetDB( DB_MASTER );
		$start = $dbr->selectField( 'user', 'MIN(user_id)', false, __METHOD__ );
		$end = $dbr->selectField( 'user', 'MAX(user_id)', false, __METHOD__ );
		if ( is_null( $start ) || is_null( $end ) ) {
			$this->output( "...user table seems to be empty.\n" );
			return;
		}
		$count = 0;
		$changed = 0;
		for ( $blockStart = $start; $blockStart <= $end; $blockStart += $this->mBatchSize ) {
			$blockEnd = min( $end, $blockStart + $this->mBatchSize - 1 );
			$this->output( "...doing user_id from $blockStart to $blockEnd\n" );
			$cond = "user_id BETWEEN $blockStart AND $blockEnd\n";
			$res = $dbr->select( 'user', '*', $cond, __METHOD__ );
			# Go through and clean up missing items, as well as correct fr_quality...
			foreach ( $res as $row ) {
				$this->beginTransaction( $dbw, __METHOD__ );
				$user = User::newFromRow( $row );
				$p = FRUserCounters::getUserParams( $user->getId(), FR_FOR_UPDATE );
				$oldp = $p;
				# Get edit comments used
				$revWhere = ActorMigration::newMigration()->getWhere( $dbr, 'rev_user', $user );
				$sres = $dbr->select(
					$revQuery['tables'],
					'1',
					[
						$revWhere['conds'],
						// @todo Should there be a "rev_comment != ''" here too?
						$revWhere['fields']['rev_comment_text'] . " NOT LIKE '/*%*/'", // manual comments only
					],
					__METHOD__,
					[ 'LIMIT' => max( $wgFlaggedRevsAutopromote['editComments'], 500 ) ],
					$revQuery['joins']
				);
				$p['editComments'] = $dbr->numRows( $sres );
				# Get content page edits
				$sres = $dbr->select(
					$revPageQuery['tables'],
					'1',
					[
						$revWhere['conds'],
						'page_namespace' => MWNamespace::getContentNamespaces() ],
					__METHOD__,
					[ 'LIMIT' => max( $wgFlaggedRevsAutopromote['totalContentEdits'], 500 ) ],
					$revPageQuery['joins']
				);
				$p['totalContentEdits'] = $dbr->numRows( $sres );
				# Get unique content pages edited
				$sres = $dbr->select(
					$revPageQuery['tables'],
					'DISTINCT(rev_page)',
					[
						$revWhere['conds'],
						'page_namespace' => MWNamespace::getContentNamespaces() ],
					__METHOD__,
					[ 'LIMIT' => max( $wgFlaggedRevsAutopromote['uniqueContentPages'], 50 ) ],
					$revPageQuery['joins']
				);
				$p['uniqueContentPages'] = [];
				foreach ( $sres as $innerRow ) {
					$p['uniqueContentPages'][] = (int)$innerRow->rev_page;
				}
				# Save the new params...
				if ( $oldp != $p ) {
					FRUserCounters::saveUserParams( $user->getId(), $p );
					$changed++;
				}

				$count++;
				$this->commitTransaction( $dbw, __METHOD__ );
			}
			wfWaitForSlaves( 5 );
		}
		$this->output( "flaggedrevs_promote table update complete ..." .
			" {$count} rows [{$changed} changed or added]\n" );
	}
}

$maintClass = "UpdateFRAutoPromote";
require_once RUN_MAINTENANCE_IF_MAIN;
