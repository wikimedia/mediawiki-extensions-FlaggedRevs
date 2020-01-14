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

class PurgeReviewablePages extends Maintenance {

	public function __construct() {
		$this->addDescription( "Use to purge CDN/file cache for all reviewable pages" );
		$this->addOption( 'makelist',
			"Build the list of reviewable pages to pagesToPurge.list", false, false );
		$this->addOption( 'purgelist',
			"Purge the list of pages in pagesToPurge.list", false, false );
		$this->setBatchSize( 1000 );
		$this->requireExtension( 'FlaggedRevs' );
	}

	public function execute() {
		$fileName = "pagesToPurge.list";
		// Build the list file...
		if ( $this->getOption( 'makelist' ) ) {
			$fileHandle = fopen( $fileName, 'w+' );
			if ( !$fileHandle ) {
				$this->fatalError( "Can't open file to create purge list." );
			}
			$this->list_reviewable_pages( $fileHandle );
			fclose( $fileHandle );
		// Purge pages on the list file...
		} elseif ( $this->getOption( 'purgelist' ) ) {
			$fileHandle = fopen( $fileName, 'r' );
			if ( !$fileHandle ) {
				$this->fatalError( "Can't open file to read purge list." );
			}
			$this->purge_reviewable_pages( $fileHandle );
			fclose( $fileHandle );
		} else {
			$this->fatalError( "No purge list action specified." );
		}
	}

	protected function list_reviewable_pages( $fileHandle ) {
		global $wgFlaggedRevsNamespaces, $wgUseCdn, $wgUseFileCache;

		$this->output( "Building list of all reviewable pages to purge ...\n" );
		if ( !$wgUseCdn && !$wgUseFileCache ) {
			$this->output( "CDN/file cache not enabled ... nothing to purge.\n" );
			return;
		} elseif ( empty( $wgFlaggedRevsNamespaces ) ) {
			$this->output( "There are no reviewable namespaces ... nothing to purge.\n" );
			return;
		}

		$db = wfGetDB( DB_MASTER );

		$start = $db->selectField( 'page', 'MIN(page_id)', false, __FUNCTION__ );
		$end = $db->selectField( 'page', 'MAX(page_id)', false, __FUNCTION__ );
		if ( $start === null || $end === null ) {
			$this->output( "... page table seems to be empty.\n" );
			return;
		}
		# Do remaining chunk
		$end += $this->mBatchSize - 1;
		$blockStart = (int)$start;
		$blockEnd = (int)( $start + $this->mBatchSize - 1 );

		$count = 0;
		while ( $blockEnd <= $end ) {
			$this->output( "... doing page_id from $blockStart to $blockEnd\n" );
			$res = $db->select( 'page', '*',
				[
					"page_id BETWEEN $blockStart AND $blockEnd",
					'page_namespace' => $wgFlaggedRevsNamespaces ],
				__FUNCTION__
			);
			# Go through and append each purgeable page...
			foreach ( $res as $row ) {
				$title = Title::newFromRow( $row );
				$fa = FlaggableWikiPage::getTitleInstance( $title );
				if ( $fa->isReviewable() ) {
					# Need to purge this page - add to list
					fwrite( $fileHandle, $title->getPrefixedDBKey() . "\n" );
					$count++;
				}
			}
			$db->freeResult( $res );
			$blockStart += $this->mBatchSize - 1;
			$blockEnd += $this->mBatchSize - 1;
			wfWaitForSlaves( 5 ); // not really needed
		}
		$this->output( "List of reviewable pages to purge complete ... {$count} pages\n" );
	}

	protected function purge_reviewable_pages( $fileHandle ) {
		global $wgUseCdn, $wgUseFileCache;
		$this->output( "Purging CDN cache for list of pages to purge ...\n" );
		if ( !$wgUseCdn && !$wgUseFileCache ) {
			$this->output( "CDN/file cache not enabled ... nothing to purge.\n" );
			return;
		}

		$count = 0;
		while ( !feof( $fileHandle ) ) {
			$dbKey = trim( fgets( $fileHandle ) );
			if ( $dbKey == '' ) {
				continue; // last line?
			}
			$title = Title::newFromDBkey( $dbKey );
			if ( $title ) {
				$title->purgeSquid(); // send PURGE
				HTMLFileCache::clearFileCache( $title ); // purge poor-mans's CDN
				$this->output( "... $dbKey\n" );

				$count++;
				if ( ( $count % $this->mBatchSize ) == 0 ) {
					wfWaitForSlaves( 5 ); // not really needed
				}
			} else {
				$this->output( "Invalid title - cannot purge: $dbKey\n" );
			}
		}
		$this->output( "CDN/file cache purge of page list complete ... {$count} pages\n" );
	}
}

$maintClass = PurgeReviewablePages::class;
require_once RUN_MAINTENANCE_IF_MAIN;
