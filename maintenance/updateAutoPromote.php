<?php
/**
 * @ingroup Maintenance
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\User\ActorMigration;
use MediaWiki\User\User;

if ( getenv( 'MW_INSTALL_PATH' ) ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

class UpdateFRAutoPromote extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( "Update autopromote table" );
		$this->setBatchSize( 50 );
		$this->requireExtension( 'FlaggedRevs' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$this->output( "Populating and updating flaggedrevs_promote table\n" );

		$services = MediaWikiServices::getInstance();
		$commentQuery = $services->getCommentStore()->getJoin( 'rev_comment' );
		$revisionStore = $services->getRevisionStore();
		$revQuery = $revisionStore->getQueryInfo();
		$revPageQuery = $revisionStore->getQueryInfo( [ 'page' ] );
		$dbr = wfGetDB( DB_REPLICA );
		$dbw = wfGetDB( DB_PRIMARY );
		$start = $dbr->selectField( 'user', 'MIN(user_id)', false, __METHOD__ );
		$end = $dbr->selectField( 'user', 'MAX(user_id)', false, __METHOD__ );
		if ( $start === null || $end === null ) {
			$this->output( "...user table seems to be empty.\n" );
			return;
		}
		$count = 0;
		$changed = 0;

		$contentNamespaces = $services->getNamespaceInfo()->getContentNamespaces();
		$autopromote = $this->getConfig()->get( 'FlaggedRevsAutopromote' );

		for ( $blockStart = (int)$start; $blockStart <= $end; $blockStart += (int)$this->mBatchSize ) {
			$blockEnd = (int)min( $end, $blockStart + $this->mBatchSize - 1 );
			$this->output( "...doing user_id from $blockStart to $blockEnd\n" );
			$cond = "user_id BETWEEN $blockStart AND $blockEnd\n";
			$res = $dbr->select( 'user', '*', $cond, __METHOD__ );
			# Go through and clean up missing items
			foreach ( $res as $row ) {
				$this->beginTransaction( $dbw, __METHOD__ );
				$user = User::newFromRow( $row );
				$p = FRUserCounters::getUserParams( $user->getId(), IDBAccessObject::READ_EXCLUSIVE );
				$oldp = $p;
				# Get edit comments used
				$revWhere = ActorMigration::newMigration()->getWhere( $dbr, 'rev_user', $user );
				$sres = $dbr->select(
					$revQuery['tables'] + $commentQuery['tables'],
					'1',
					[
						$revWhere['conds'],
						// @todo Should there be a "rev_comment != ''" here too?
						$commentQuery['fields']['rev_comment_text'] . " NOT LIKE '/*%*/'", // manual comments only
					],
					__METHOD__,
					[ 'LIMIT' => max( $autopromote['editComments'], 500 ) ],
					$commentQuery['joins']
				);
				$p['editComments'] = $sres->numRows();
				# Get content page edits
				$sres = $dbr->select(
					$revPageQuery['tables'],
					'1',
					[
						$revWhere['conds'],
						'page_namespace' => $contentNamespaces ],
					__METHOD__,
					[ 'LIMIT' => max( $autopromote['totalContentEdits'], 500 ) ],
					$revPageQuery['joins']
				);
				$p['totalContentEdits'] = $sres->numRows();
				# Get unique content pages edited
				$sres = $dbr->select(
					$revPageQuery['tables'],
					'DISTINCT(rev_page)',
					[
						$revWhere['conds'],
						'page_namespace' => $contentNamespaces ],
					__METHOD__,
					[ 'LIMIT' => max( $autopromote['uniqueContentPages'], 50 ) ],
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
			$this->waitForReplication();
		}
		$this->output( "flaggedrevs_promote table update complete ..." .
			" {$count} rows [{$changed} changed or added]\n" );
	}
}

$maintClass = UpdateFRAutoPromote::class;
require_once RUN_MAINTENANCE_IF_MAIN;
