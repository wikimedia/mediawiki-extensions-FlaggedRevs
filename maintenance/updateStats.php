<?php
/**
 * This file is run hourly by the following cron job:
 * https://gerrit.wikimedia.org/g/operations/puppet/+/production/modules/profile/manifests/mediawiki/maintenance/update_flaggedrev_stats.pp
 * Do not rename this file without also fixing the file path in Puppet.
 *
 * @ingroup Maintenance
 */

use MediaWiki\Maintenance\Maintenance;

if ( getenv( 'MW_INSTALL_PATH' ) ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

class UpdateFlaggedRevsStats extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( "Update FlaggedRevs statistics table" );
		$this->requireExtension( 'FlaggedRevs' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$this->output( sprintf( '%-30s ', 'ValidationStatistics' ) );

		$time1 = microtime( true );
		FlaggedRevsStats::updateCache();
		$time2 = microtime( true );

		$elapsed = ( $time2 - $time1 );
		$this->output( sprintf( "completed in %.2fs\n", $elapsed ) );
	}
}

$maintClass = UpdateFlaggedRevsStats::class;
require_once RUN_MAINTENANCE_IF_MAIN;
