<?php
/**
 * @ingroup Maintenance
 */

use MediaWiki\Maintenance\Maintenance;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\IDBAccessObject;
use Wikimedia\Rdbms\SelectQueryBuilder;

if ( getenv( 'MW_INSTALL_PATH' ) ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

class UpdateFRTracking extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( "Correct the page data in the flaggedrevs tracking tables. " );
		$this->addOption( 'startpage', 'Page ID to start on', false, true );
		$this->requireExtension( 'FlaggedRevs' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$startPage = $this->getOption( 'startpage' );
		$this->updateFlaggedPages( $startPage );
	}

	/**
	 * @param int|null $start Page ID
	 */
	private function updateFlaggedPages( $start = null ) {
		$this->output( "Populating and correcting flaggedpages/flaggedpage_config columns\n" );

		$BATCH_SIZE = 300;

		$db = $this->getPrimaryDB();
		$revisionStore = $this->getServiceContainer()->getRevisionStore();

		if ( $start === null ) {
			$start = $db->newSelectQueryBuilder()
				->select( 'MIN(page_id)' )
				->from( 'page' )
				->caller( __METHOD__ )
				->fetchField();
		}
		$end = $db->newSelectQueryBuilder()
			->select( 'MAX(page_id)' )
			->from( 'page' )
			->caller( __METHOD__ )
			->fetchField();
		if ( $start === null || $end === null ) {
			$this->output( "...flaggedpages table seems to be empty.\n" );
			return;
		}
		# Do remaining chunk
		$end += $BATCH_SIZE - 1;
		$blockStart = (int)$start;
		$blockEnd = (int)$start + $BATCH_SIZE - 1;
		$count = 0;
		$deleted = 0;
		$fixed = 0;
		while ( $blockEnd <= $end ) {
			$this->output( "...doing page_id from $blockStart to $blockEnd\n" );

			$this->beginTransaction( $db, __METHOD__ );
			$res = $db->newSelectQueryBuilder()
				->select( [ 'page_id', 'page_namespace', 'page_title', 'page_latest' ] )
				->from( 'page' )
				->where( $db->expr( 'page_id', '>=', $blockStart )->and( 'page_id', '<=', $blockEnd ) )
				->caller( __METHOD__ )
				->fetchResultSet();
			# Go through and update the de-normalized references...
			foreach ( $res as $row ) {
				$title = Title::newFromRow( $row );
				$article = FlaggableWikiPage::newInstance( $title );
				$oldFrev = FlaggedRevision::newFromStable( $title, IDBAccessObject::READ_LATEST );
				$frev = FlaggedRevision::determineStable( $title );
				# Update fp_stable, fp_quality, and fp_reviewed
				if ( $frev ) {
					$article->updateStableVersion( $frev, $row->page_latest );
					$changed = ( !$oldFrev || $oldFrev->getRevId() != $frev->getRevId() );
				# Somethings broke? Delete the row...
				} else {
					$changed = (bool)$oldFrev;
					$deleted += (int)$changed;
				}
				# Get the latest revision
				$queryInfo = $revisionStore->getQueryInfo();
				$revRow = $db->newSelectQueryBuilder()
					->queryInfo( $queryInfo )
					->where( [ 'rev_page' => $row->page_id ] )
					->orderBy( 'rev_timestamp', SelectQueryBuilder::SORT_DESC )
					->caller( __METHOD__ )
					->fetchRow();
				# Correct page_latest if needed (import/files made plenty of bad rows)
				if ( $revRow ) {
					$latestRevId = $article->getLatest();
					if ( $latestRevId ) {
						// If not found (false), cast to 0 so that the
						// page is updated, just to be on the safe side,
						// even though it should always be found
						$latestTimestamp = (int)$revisionStore->getTimestampFromId(
							$latestRevId,
							IDBAccessObject::READ_LATEST
						);
					} else {
						$latestTimestamp = 0;
					}
					if ( $revRow->rev_timestamp > $latestTimestamp ) {
						// Most recent revision, based on timestamp, is
						// newer than the page_latest
						// update page_latest accordingly
						$revRecord = $revisionStore->newRevisionFromRow(
							$revRow,
							IDBAccessObject::READ_LATEST,
							$title
						);
						if ( $article->updateRevisionOn(
							$db,
							$revRecord,
							$latestRevId
						) ) {
							$fixed++;
						}
					}
				}
				if ( $changed ) {
					# Lazily rebuild dependencies on next parse (we invalidate below)
					FlaggedRevs::clearStableOnlyDeps( $title->getArticleID() );
					$title->invalidateCache();
				}
				$count++;
			}
			# Remove manual config settings that simply restate the site defaults
			$db->newDeleteQueryBuilder()
				->deleteFrom( 'flaggedpage_config' )
				->where( [
					$db->expr( 'fpc_page_id', '>=', $blockStart ),
					$db->expr( 'fpc_page_id', '<=', $blockEnd ),
					'fpc_override'  => intval( FlaggedRevs::isStableShownByDefault() ),
					'fpc_level'     => ''
				] )
				->caller( __METHOD__ )
				->execute();
			$deleted += $db->affectedRows();
			$this->commitTransaction( $db, __METHOD__ );

			$blockStart += $BATCH_SIZE;
			$blockEnd += $BATCH_SIZE;
		}
		$this->output( "flaggedpage columns update complete ..." .
			" {$count} rows [{$fixed} fixed] [{$deleted} deleted]\n" );
	}
}

$maintClass = UpdateFRTracking::class;
require_once RUN_MAINTENANCE_IF_MAIN;
