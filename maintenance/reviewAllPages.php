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

class ReviewAllPages extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( "Review all pages in reviewable namespaces. " .
			"A user ID must be given to specifiy the \"reviewer\" who accepted the pages." );
		$this->addOption( 'username',
			'The user name of the existing user to use as the "reviewer"', true, true );
		$this->setBatchSize( 100 );
		$this->requireExtension( 'FlaggedRevs' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$user = User::newFromName( $this->getOption( 'username' ) );
		$this->autoReviewCurrent( $user );
	}

	/**
	 * @param User $user
	 */
	private function autoReviewCurrent( User $user ) {
		$services = MediaWikiServices::getInstance();
		$this->output( "Auto-reviewing all current page versions...\n" );
		if ( !$user->isRegistered() ) {
			$this->output( "Invalid user specified.\n" );
			return;
		} elseif ( !$services->getPermissionManager()->userHasRight( $user, 'review' ) ) {
			$this->output( "User specified (id: {$user->getId()}) does not have \"review\" rights.\n" );
			return;
		}

		$db = wfGetDB( DB_PRIMARY );

		$this->output( "Reviewer username: " . $user->getName() . "\n" );

		$start = $db->selectField( 'page', 'MIN(page_id)', false, __METHOD__ );
		$end = $db->selectField( 'page', 'MAX(page_id)', false, __METHOD__ );
		if ( $start === null || $end === null ) {
			$this->output( "...page table seems to be empty.\n" );
			return;
		}
		# Do remaining chunk
		$end += $this->mBatchSize - 1;
		$blockStart = (int)$start;
		$blockEnd = (int)( $start + $this->mBatchSize - 1 );
		$count = 0;
		$changed = 0;
		$flags = FlaggedRevs::quickTags(); // Assume basic level

		$lbFactory = $services->getDBLoadBalancerFactory();
		$wikiPageFactory = $services->getWikiPageFactory();
		$revisionStore = $services->getRevisionStore();

		while ( $blockEnd <= $end ) {
			$this->output( "...doing page_id from $blockStart to $blockEnd\n" );
			$res = $db->select( [ 'page', 'revision' ],
				'*',
				[ "page_id BETWEEN $blockStart AND $blockEnd",
					'page_namespace' => FlaggedRevs::getReviewNamespaces(),
					'rev_id = page_latest' ],
				__METHOD__
			);
			# Go through and autoreview the current version of every page...
			foreach ( $res as $row ) {
				$title = Title::newFromRow( $row );
				$rev = $revisionStore->newRevisionFromRow( $row, RevisionStore::READ_LATEST );
				# Is it already reviewed?
				$frev = FlaggedRevision::newFromTitle( $title, $row->page_latest, FR_MASTER );
				# Rev should exist, but to be safe...
				if ( !$frev && $rev ) {
					$wikiPage = $wikiPageFactory->newFromTitle( $title );
					$db->startAtomic( __METHOD__ );
					FlaggedRevs::autoReviewEdit(
						$wikiPage,
						$user,
						$rev,
						$flags,
						true,
						true // approve the reverted tag update
					);
					FlaggedRevs::updateHtmlCaches( $wikiPage->getTitle() );
					$db->endAtomic( __METHOD__ );
					$changed++;
				}
				$count++;
			}
			$blockStart += $this->mBatchSize - 1;
			$blockEnd += $this->mBatchSize - 1;

			$lbFactory->waitForReplication( [ 'ifWritesSince' => 5 ] );
		}

		$this->output( "Auto-reviewing of all pages complete ..." .
			"{$count} rows [{$changed} changed]\n" );
	}
}

$maintClass = ReviewAllPages::class;
require_once RUN_MAINTENANCE_IF_MAIN;
