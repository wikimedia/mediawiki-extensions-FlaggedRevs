<?php
/**
 * @ingroup Maintenance
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionStore;

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

		$db = $this->getDB( DB_PRIMARY );
		$revisionStore = MediaWikiServices::getInstance()->getRevisionStore();

		if ( $start === null ) {
			$start = $db->selectField( 'page', 'MIN(page_id)', '', __METHOD__ );
		}
		$end = $db->selectField( 'page', 'MAX(page_id)', '', __METHOD__ );
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
			$cond = "page_id BETWEEN $blockStart AND $blockEnd";

			$this->beginTransaction( $db, __METHOD__ );
			$res = $db->select( 'page',
				[ 'page_id', 'page_namespace', 'page_title', 'page_latest' ],
				$cond, __METHOD__ );
			# Go through and update the de-normalized references...
			foreach ( $res as $row ) {
				$title = Title::newFromRow( $row );
				$article = FlaggableWikiPage::newInstance( $title );
				$oldFrev = FlaggedRevision::newFromStable( $title, FR_MASTER );
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
				$revRow = $db->selectRow(
					$queryInfo['tables'],
					$queryInfo['fields'],
					[ 'rev_page' => $row->page_id ],
					__METHOD__,
					[ 'ORDER BY' => 'rev_timestamp DESC' ],
					$queryInfo['joins']
				);
				# Correct page_latest if needed (import/files made plenty of bad rows)
				if ( $revRow ) {
					$latestRevId = $article->getLatest();
					if ( $latestRevId ) {
						// If not found (false), cast to 0 so that the
						// page is updated, just to be on the safe side,
						// even though it should always be found
						$latestTimestamp = (int)$revisionStore->getTimestampFromId(
							$latestRevId,
							RevisionStore::READ_LATEST
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
							RevisionStore::READ_LATEST,
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
			$db->delete( 'flaggedpage_config',
				[ "fpc_page_id BETWEEN $blockStart AND $blockEnd",
					'fpc_override'  => intval( FlaggedRevs::isStableShownByDefault() ),
					'fpc_level'     => ''
				],
				__METHOD__
			);
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
