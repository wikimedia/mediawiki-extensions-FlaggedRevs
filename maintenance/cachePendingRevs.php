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

require_once "$IP/maintenance/Maintenance.php";

class CachePendingRevs extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = "Cache pending revision data";
	}

	public function execute() {
		global $wgUser;
		$dbr = wfGetDB( DB_REPLICA );
		$revQuery = Revision::getQueryInfo();
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
		foreach ( $ret as $row ) {
			$title = Title::newFromRow( $row );
			$article = new Article( $title );
			$rev = new Revision( $row );
			// Trigger cache regeneration
			$start = microtime( true );
			FRInclusionCache::getRevIncludes( $article, $rev, $wgUser, 'regen' );
			$elapsed = intval( ( microtime( true ) - $start ) * 1000 );
			$this->cachePendingRevsLog(
				$title->getPrefixedDBkey() . " rev:" . $rev->getId() . " {$elapsed}ms" );
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

$maintClass = "CachePendingRevs";
require_once RUN_MAINTENANCE_IF_MAIN;
