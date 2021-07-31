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
		$this->addDescription( "Correct the page data in the flaggedrevs tracking tables. " .
			"Update the quality tier of revisions based on their rating tags. " .
			"Migrate flagged revision file version data to proper table."
		);
		$this->addOption( 'startpage', 'Page ID to start on', false, true );
		$this->addOption( 'startrev', 'Rev ID to start on', false, true );
		$this->addOption( 'updateonly', 'One of (revs, pages, images)', false, true );
		$this->requireExtension( 'FlaggedRevs' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$startPage = $this->getOption( 'startpage' );
		$startRev = $this->getOption( 'startrev' );
		$updateonly = $this->getOption( 'updateonly' );
		if ( $updateonly ) {
			switch ( $updateonly ) {
				case 'revs':
					$this->updateFlaggedRevs( $startRev );
					break;
				case 'pages':
					$this->updateFlaggedPages( $startPage );
					break;
				case 'images':
					$this->updateFlaggedImages( $startRev );
					break;
				default:
					$this->fatalError( "Invalidate operation specified.\n" );
			}
		} else {
			$this->updateFlaggedRevs( $startRev );
			$this->updateFlaggedPages( $startPage );
			$this->updateFlaggedImages( $startRev );
		}
	}

	/**
	 * @param int|null $start Revision ID
	 */
	private function updateFlaggedRevs( $start = null ) {
		$this->output( "Populating and correcting flaggedrevs columns\n" );

		$BATCH_SIZE = 1000;

		$db = $this->getDB( DB_PRIMARY );

		if ( $start === null ) {
			$start = $db->selectField( 'revision', 'MIN(rev_id)', false, __METHOD__ );
		}
		$end = $db->selectField( 'revision', 'MAX(rev_id)', false, __METHOD__ );
		if ( $start === null || $end === null ) {
			$this->output( "...revision table seems to be empty.\n" );
			return;
		}
		# Do remaining chunk
		$end += $BATCH_SIZE - 1;
		$blockStart = (int)$start;
		$blockEnd = (int)$start + $BATCH_SIZE - 1;
		$count = 0;
		$changed = 0;
		while ( $blockEnd <= $end ) {
			$this->output( "...doing fr_rev_id from $blockStart to $blockEnd\n" );
			$cond = "rev_id BETWEEN $blockStart AND $blockEnd
				AND fr_rev_id = rev_id AND page_id = rev_page";

			$this->beginTransaction( $db, __METHOD__ );
			$res = $db->select(
				[ 'revision', 'flaggedrevs', 'page' ],
				[ 'fr_rev_id', 'fr_tags', 'page_namespace', 'page_title',
					'fr_img_name', 'fr_img_timestamp', 'fr_img_sha1', 'rev_page' ],
				$cond,
				__METHOD__
			);
			# Go through and clean up missing items
			foreach ( $res as $row ) {
				$file = $row->fr_img_name;
				$fileTime = $row->fr_img_timestamp;
				$fileSha1 = $row->fr_img_sha1;
				# Check for file version to see if it's stored the old way...
				if ( $row->page_namespace === NS_FILE && !$file ) {
					$irow = $db->selectRow( 'flaggedimages',
						[ 'fi_img_timestamp', 'fi_img_sha1' ],
						[ 'fi_rev_id' => $row->fr_rev_id, 'fi_name' => $row->page_title ],
						__METHOD__ );
					$fileTime = $irow ? $irow->fi_img_timestamp : null;
					$fileSha1 = $irow ? $irow->fi_img_sha1 : null;
					$file = $irow ? $row->page_title : null;
					# Fill in from current if broken
					if ( !$irow ) {
						$crow = $db->selectRow( 'image',
							[ 'img_timestamp', 'img_sha1' ],
							[ 'img_name' => $row->page_title ],
							__METHOD__ );
						$fileTime = $crow ? $crow->img_timestamp : null;
						$fileSha1 = $crow ? $crow->img_sha1 : null;
						$file = $crow ? $row->page_title : null;
					}
				}

				# Check if anything needs updating
				if ( $file != $row->fr_img_name
					|| $fileSha1 != $row->fr_img_sha1
					|| $fileTime != $row->fr_img_timestamp
				) {
					# Update the row...
					$db->update( 'flaggedrevs',
						[
							'fr_img_name'       => $file,
							'fr_img_sha1'       => $fileSha1,
							'fr_img_timestamp'  => $fileTime
						],
						[ 'fr_rev_id' => $row->fr_rev_id ],
						__METHOD__
					);
					$changed++;
				}
				$count++;
			}
			$this->commitTransaction( $db, __METHOD__ );

			$db->freeResult( $res );
			$blockStart += $BATCH_SIZE;
			$blockEnd += $BATCH_SIZE;
		}
		$this->output( "fr_img_* columns update complete ..." .
			" {$count} rows [{$changed} changed]\n" );
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
			$start = $db->selectField( 'page', 'MIN(page_id)', false, __METHOD__ );
		}
		$end = $db->selectField( 'page', 'MAX(page_id)', false, __METHOD__ );
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
				$article = new FlaggableWikiPage( $title );
				$oldFrev = FlaggedRevision::newFromStable( $title, FR_MASTER );
				$frev = FlaggedRevision::determineStable( $title, FR_MASTER );
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
			$db->freeResult( $res );
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

	/**
	 * @param int|null $start Revision ID
	 */
	private function updateFlaggedImages( $start = null ) {
		$this->output( "Cleaning up flaggedimages columns\n" );

		$BATCH_SIZE = 1000;

		$db = $this->getDB( DB_PRIMARY );

		if ( $start === null ) {
			$start = $db->selectField( 'flaggedimages', 'MIN(fi_rev_id)', false, __METHOD__ );
		}
		$end = $db->selectField( 'flaggedimages', 'MAX(fi_rev_id)', false, __METHOD__ );
		if ( $start === null || $end === null ) {
			$this->output( "...flaggedimages table seems to be empty.\n" );
			return;
		}
		# Do remaining chunk
		$end += $BATCH_SIZE - 1;
		$blockStart = (int)$start;
		$blockEnd = (int)$start + $BATCH_SIZE - 1;
		$nulled = 0;
		while ( $blockEnd <= $end ) {
			$this->output( "...doing fi_rev_id from $blockStart to $blockEnd\n" );
			$cond = "fi_rev_id BETWEEN $blockStart AND $blockEnd";

			$this->beginTransaction( $db, __METHOD__ );
			# Remove padding garbage and such...turn to NULL instead
			$db->update( 'flaggedimages',
				[ 'fi_img_timestamp' => null ],
				[ $cond, "fi_img_timestamp = '' OR LOCATE( '\\0', fi_img_timestamp )" ],
				__METHOD__
			);
			$nulled += $db->affectedRows();
			$this->commitTransaction( $db, __METHOD__ );

			$blockStart += $BATCH_SIZE;
			$blockEnd += $BATCH_SIZE;
		}
		$this->output( "flaggedimages columns update complete ... [{$nulled} fixed]\n" );
	}
}

$maintClass = UpdateFRTracking::class;
require_once RUN_MAINTENANCE_IF_MAIN;
