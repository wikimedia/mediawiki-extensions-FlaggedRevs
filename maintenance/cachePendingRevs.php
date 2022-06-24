<?php
/**
 * This script precaches parser output related data of pending revs
 *
 * @ingroup Maintenance
 */

if ( getenv( 'MW_INSTALL_PATH' ) ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = __DIR__ . '/../../..';
}

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionFactory;

require_once "$IP/maintenance/Maintenance.php";

class CachePendingRevs extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( "Cache pending revision data" );
		$this->requireExtension( 'FlaggedRevs' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		if ( FlaggedRevs::inclusionSetting() === FR_INCLUDES_CURRENT ) {
			$this->output( "Nothing to do; inclusion mode is FR_INCLUDES_CURRENT." );
			return;
		}

		$dbr = wfGetDB( DB_REPLICA );
		$services = MediaWikiServices::getInstance();
		$revFactory = $services->getRevisionFactory();
		$revQuery = $revFactory->getQueryInfo();
		$pageQuery = WikiPage::getQueryInfo();
		$ret = $dbr->select(
			array_merge( [ 'flaggedpages' ], $revQuery['tables'], $pageQuery['tables'] ),
			array_merge( $revQuery['fields'], $pageQuery['fields'] ),
			[
				'fp_pending_since IS NOT NULL',
				'rev_timestamp >= fp_pending_since'
			],
			__METHOD__,
			[ 'ORDER BY' => 'fp_pending_since DESC' ],
			[
				'revision' => [ 'JOIN', 'rev_page = fp_page_id' ],
				'page' => [ 'JOIN', 'page_id = fp_page_id' ],
			] + $revQuery['joins'] + $pageQuery['joins']
		);

		$user = User::newSystemUser( 'Maintenance script', [ 'steal' => true ] );
		$wikiPageFactory = $services->getWikiPageFactory();
		foreach ( $ret as $row ) {
			$title = Title::newFromRow( $row );
			$wikiPage = $wikiPageFactory->newFromTitle( $title );
			$revRecord = $revFactory->newRevisionFromRow(
				$row,
				RevisionFactory::READ_NORMAL,
				$title
			);
			// Trigger cache regeneration
			$start = microtime( true );

			FRInclusionCache::getRevIncludes(
				$wikiPage,
				$revRecord,
				$user,
				'regen'
			);
			$elapsed = intval( ( microtime( true ) - $start ) * 1000 );
			$this->cachePendingRevsLog(
				$title->getPrefixedDBkey() .
				" rev:" .
				$revRecord->getId() . "
				{$elapsed}ms"
			);
		}
	}

	/**
	 * Log the cache message
	 * @param string $msg The message to log
	 */
	private function cachePendingRevsLog( $msg ) {
		$this->output( wfTimestamp( TS_DB ) . " $msg\n" );
		wfDebugLog( 'cachePendingRevs', $msg );
	}
}

$maintClass = CachePendingRevs::class;
require_once RUN_MAINTENANCE_IF_MAIN;
