<?php
/**
 * @ingroup Maintenance
 */

use MediaWiki\MediaWikiServices;

if ( getenv( 'MW_INSTALL_PATH' ) ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

class FlagProtectToSemiProtect extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Convert flag-protected pages to semi-protection.' );
		$this->addOption( 'user', 'The name of the admin user to use as the "protector"', true, true );
		$this->addOption( 'reason', 'The reason for the conversion', false, true );
		$this->setBatchSize( 500 );
		$this->requireExtension( 'FlaggedRevs' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		global $wgFlaggedRevsProtection;

		if ( !$wgFlaggedRevsProtection ) {
			$this->output( "\$wgFlaggedRevsProtection not enabled.\n" );
			return;
		}

		$user = User::newFromName( $this->getOption( 'user' ) );
		if ( !$user || !$user->isRegistered() ) {
			$this->fatalError( "Invalid user specified!" );
		}
		$reason = $this->getOption( 'reason',
			"Converting flagged protection settings to edit protection settings." );

		$this->output( "Protecter username: \"" . $user->getName() . "\"\n" );
		$this->output( "Running in 5 seconds...Press ctrl-c to abort.\n" );
		sleep( 5 );

		$this->flagToSemiProtect( $user, $reason );
	}

	/**
	 * @param User $user
	 * @param string $reason
	 */
	private function flagToSemiProtect( User $user, $reason ) {
		global $wgFlaggedRevsNamespaces;

		$this->output( "Semi-protecting all flag-protected pages...\n" );
		if ( !$wgFlaggedRevsNamespaces ) {
			$this->output( "\$wgFlaggedRevsNamespaces is empty.\n" );
			return;
		}

		$db = wfGetDB( DB_PRIMARY );
		$start = $db->selectField( 'flaggedpage_config', 'MIN(fpc_page_id)', false, __METHOD__ );
		$end = $db->selectField( 'flaggedpage_config', 'MAX(fpc_page_id)', false, __METHOD__ );
		if ( $start === null || $end === null ) {
			$this->output( "...flaggedpage_config table seems to be empty.\n" );
			return;
		}
		# Do remaining chunk
		$end += $this->mBatchSize - 1;
		$blockStart = (int)$start;
		$blockEnd = (int)( $start + $this->mBatchSize - 1 );
		$count = 0;

		$services = MediaWikiServices::getInstance();
		$lbFactory = $services->getDBLoadBalancerFactory();
		$restrictionStore = $services->getRestrictionStore();
		$wikiPageFactory = $services->getWikiPageFactory();

		while ( $blockEnd <= $end ) {
			$this->output( "...doing fpc_page_id from $blockStart to $blockEnd\n" );
			$res = $db->select(
				[ 'flaggedpage_config', 'page' ],
				[ 'fpc_page_id', 'fpc_level', 'fpc_expiry' ],
				[ "fpc_page_id BETWEEN $blockStart AND $blockEnd",
					'page_namespace' => $wgFlaggedRevsNamespaces,
					'page_id = fpc_page_id',
					'fpc_level != ' . $db->addQuotes( '' ) ],
				__METHOD__
			);
			# Go through and protect each page...
			foreach ( $res as $row ) {
				$title = Title::newFromID( $row->fpc_page_id );
				if ( $restrictionStore->isProtected( $title, 'edit' ) ) {
					continue; // page already has edit protection - skip it
				}
				# Flagged protection settings
				$frLimit = trim( $row->fpc_level );
				$frExpiry = ( $row->fpc_expiry === $db->getInfinity() )
					? 'infinity'
					: wfTimestamp( TS_MW, $row->fpc_expiry );
				# Build the new protection settings
				$cascade = false;
				$limit = [];
				$expiry = [];
				foreach ( $restrictionStore->listApplicableRestrictionTypes( $title ) as $type ) {
					# Get existing restrictions for this action
					$oldLimit = $restrictionStore->getRestrictions( $title, $type ); // array
					$oldExpiry = $restrictionStore->getRestrictionExpiry( $title, $type ); // MW_TS
					# Move or Edit rights - take highest of (flag,type) settings
					if ( $type == 'edit' || $type == 'move' ) {
						# Sysop flag-protect -> full protect
						if ( $frLimit == 'sysop' || in_array( 'sysop', $oldLimit ) ) {
							$newLimit = 'sysop';
						# Reviewer/autoconfirmed flag-protect -> semi-protect
						} else {
							$newLimit = 'autoconfirmed';
						}
						# Take highest expiry of (flag,type) settings
						$newExpiry = ( !$oldLimit || $frExpiry >= $oldExpiry )
							? $frExpiry // note: 'infinity' > '99999999999999'
							: $oldExpiry;
					# Otherwise - maintain original limits
					} else {
						$newLimit = $oldLimit;
						$newExpiry = $oldExpiry;
					}
					$limit[$type] = $newLimit;
					$expiry[$type] = $newExpiry;
				}

				$db->begin( __METHOD__ );
				$wikiPage = $wikiPageFactory->newFromTitle( $title );
				$ok = $wikiPage->doUpdateRestrictions( $limit, $expiry, $cascade, $reason, $user );
				if ( $ok ) {
					$count++;
				} else {
					$this->output( "Could not protect: " . $title->getPrefixedText() . "\n" );
				}
				$db->commit( __METHOD__ );
			}
			$blockStart += $this->mBatchSize - 1;
			$blockEnd += $this->mBatchSize - 1;
			$lbFactory->waitForReplication( [ 'ifWritesSince' => 5 ] );
		}
		$this->output( "Protection of all flag-protected pages complete ... {$count} pages\n" );
	}
}

$maintClass = FlagProtectToSemiProtect::class;
require_once RUN_MAINTENANCE_IF_MAIN;
