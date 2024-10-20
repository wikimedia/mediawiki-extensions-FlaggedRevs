<?php
/**
 * @ingroup Maintenance
 */

use MediaWiki\Maintenance\Maintenance;
use MediaWiki\User\ActorMigration;
use MediaWiki\User\User;
use Wikimedia\Rdbms\IDBAccessObject;
use Wikimedia\Rdbms\IExpression;
use Wikimedia\Rdbms\LikeValue;

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

		$services = $this->getServiceContainer();
		$commentQuery = $services->getCommentStore()->getJoin( 'rev_comment' );
		$revisionStore = $services->getRevisionStore();
		$revQuery = $revisionStore->getQueryInfo();
		$revPageQuery = $revisionStore->getQueryInfo( [ 'page' ] );
		$dbr = $this->getReplicaDB();
		$dbw = $this->getPrimaryDB();
		$start = $dbr->newSelectQueryBuilder()
			->select( 'MIN(user_id)' )
			->from( 'user' )
			->caller( __METHOD__ )
			->fetchField();
		$end = $dbr->newSelectQueryBuilder()
			->select( 'MAX(user_id)' )
			->from( 'user' )
			->caller( __METHOD__ )
			->fetchField();
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
			$res = $dbr->newSelectQueryBuilder()
				->select( '*' )
				->from( 'user' )
				->where( $dbr->expr( 'user_id', '>=', $blockStart )->and( 'user_id', '<=', $blockEnd ) )
				->caller( __METHOD__ )
				->fetchResultSet();
			# Go through and clean up missing items
			foreach ( $res as $row ) {
				$this->beginTransaction( $dbw, __METHOD__ );
				$user = User::newFromRow( $row );
				$p = FRUserCounters::getUserParams( $user->getId(), IDBAccessObject::READ_EXCLUSIVE );
				$oldp = $p;
				# Get edit comments used
				$revWhere = ActorMigration::newMigration()->getWhere( $dbr, 'rev_user', $user );
				$sres = $dbr->newSelectQueryBuilder()
					->select( '1' )
					->tables( $revQuery['tables'] )
					->tables( $commentQuery['tables'] )
					->where( [
						$revWhere['conds'],
						// @todo Should there be a "rev_comment != ''" here too?
						$dbw->expr(
							$commentQuery['fields']['rev_comment_text'],
							IExpression::NOT_LIKE,
							new LikeValue( '/*', $dbw->anyString(), '*/' ) // manual comments only
						),
					] )
					->limit( max( $autopromote['editComments'], 500 ) )
					->joinConds( $commentQuery['joins'] )
					->caller( __METHOD__ )
					->fetchResultSet();
				$p['editComments'] = $sres->numRows();
				# Get content page edits
				$sres = $dbr->newSelectQueryBuilder()
					->select( '1' )
					->tables( $revPageQuery['tables'] )
					->where( [
						$revWhere['conds'],
						'page_namespace' => $contentNamespaces,
					] )
					->limit( max( $autopromote['totalContentEdits'], 500 ) )
					->joinConds( $revPageQuery['joins'] )
					->caller( __METHOD__ )
					->fetchResultSet();
				$p['totalContentEdits'] = $sres->numRows();
				# Get unique content pages edited
				$sres = $dbr->newSelectQueryBuilder()
					->select( 'rev_page' )
					->distinct()
					->tables( $revPageQuery['tables'] )
					->where( [
						$revWhere['conds'],
						'page_namespace' => $contentNamespaces,
					] )
					->limit( max( $autopromote['uniqueContentPages'], 50 ) )
					->joinConds( $revPageQuery['joins'] )
					->caller( __METHOD__ )
					->fetchResultSet();
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
