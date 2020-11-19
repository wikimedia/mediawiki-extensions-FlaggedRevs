<?php

use MediaWiki\MediaWikiServices;

class ValidationStatistics extends IncludableSpecialPage {
	/** @var array|null */
	private $latestData = null;

	public function __construct() {
		parent::__construct( 'ValidationStatistics' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		global $wgFlaggedRevsStats, $wgFlaggedRevsProtection;

		$out = $this->getOutput();
		$lang = $this->getLanguage();

		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:FlaggedRevs' );

		$this->maybeUpdate();

		$ec = $this->getEditorCount();
		$rc = $this->getReviewerCount();
		$mt = $this->getMeanReviewWaitAnon();
		$mdt = $this->getMedianReviewWaitAnon();
		$pt = $this->getMeanPendingWait();
		$pData = $this->getReviewPercentilesAnon();
		$timestamp = $this->getLastUpdate();

		$out->addWikiMsg( 'validationstatistics-users',
			$lang->formatNum( $ec ), $lang->formatNum( $rc )
		);
		# Most of the output depends on background queries
		if ( !$this->readyForQuery() ) {
			return;
		}

		# Is there a review time table available?
		if ( count( $pData ) ) {
			$headerRows = '';
			$dataRows = '';
			foreach ( $pData as $percentile => $perValue ) {
				$headerRows .= "<th>P<sub>" . intval( $percentile ) . "</sub></th>";
				$dataRows .= '<td>' .
					htmlspecialchars( $lang->formatTimePeriod( $perValue, [ 'avoid' => 'avoidminutes' ] ) ) .
					'</td>';
			}
			$css = 'wikitable flaggedrevs_stats_table';
			$reviewChart = "<table class='$css' style='white-space: nowrap;'>\n";
			$reviewChart .= "<tr style='text-align: center;'>$headerRows</tr>\n";
			$reviewChart .= "<tr style='text-align: center;'>$dataRows</tr>\n";
			$reviewChart .= "</table>\n";
		} else {
			$reviewChart = '';
		}

		if ( $timestamp != '-' ) {
			# Show "last updated"...
			$out->addWikiMsg( 'validationstatistics-lastupdate',
				$lang->date( $timestamp, true ),
				$lang->time( $timestamp, true )
			);
		}
		$out->addHTML( '<hr/>' );
		# Show pending time stats...
		$out->addWikiMsg( 'validationstatistics-pndtime',
			$lang->formatTimePeriod( $pt, [ 'avoid' => 'avoidminutes' ] ) );
		# Show review time stats...
		if ( !FlaggedRevs::useSimpleConfig() ) {
			$out->addWikiMsg( 'validationstatistics-revtime',
				$lang->formatTimePeriod( $mt, [ 'avoid' => 'avoidminutes' ] ),
				$lang->formatTimePeriod( $mdt, [ 'avoid' => 'avoidminutes' ] ),
				$reviewChart
			);
		}
		# Show per-namespace stats table...
		$out->addWikiMsg( 'validationstatistics-table' );
		$out->addHTML(
			Xml::openElement( 'table', [ 'class' => 'wikitable flaggedrevs_stats_table' ] )
		);
		$out->addHTML( "<tr>\n" );
		// Headings (for a positive grep result):
		// validationstatistics-ns, validationstatistics-total, validationstatistics-stable,
		// validationstatistics-latest, validationstatistics-synced, validationstatistics-old,
		// validationstatistics-unreviewed
		$msgs = [ 'ns', 'total', 'stable', 'latest', 'synced', 'old' ]; // our headings
		if ( !$wgFlaggedRevsProtection ) {
			$msgs[] = 'unreviewed';
		}
		foreach ( $msgs as $msg ) {
			$out->addHTML( '<th>' .
				$this->msg( "validationstatistics-$msg" )->parse() . '</th>' );
		}
		$out->addHTML( "</tr>\n" );
		$namespaces = FlaggedRevs::getReviewNamespaces();
		$contLang = MediaWikiServices::getInstance()->getContentLanguage();
		foreach ( $namespaces as $namespace ) {
			$total = $this->getTotalPages( $namespace );
			$reviewed = $this->getReviewedPages( $namespace );
			$synced = $this->getSyncedPages( $namespace );
			if ( $total === '-' || $reviewed === '-' || $synced === '-' ) {
				continue; // NS added to config recently?
			}

			$NsText = $contLang->getFormattedNsText( $namespace );
			$NsText = $NsText ?: $this->msg( 'blanknamespace' )->text();

			$percRev = intval( $total ) == 0
				? '-' // devision by zero
				: $this->msg( 'parentheses',
					$this->msg( 'percent' )
						->numParams( sprintf(
							'%4.2f',
							100 * intval( $reviewed ) / intval( $total )
						) )->escaped()
				)->escaped();
			$percLatest = intval( $total ) == 0
				? '-' // devision by zero
				: $this->msg( 'parentheses',
					$this->msg( 'percent' )
						->numParams( sprintf( '%4.2f', 100 * intval( $synced ) / intval( $total )
						) )->escaped()
				)->escaped();
			$percSynced = intval( $reviewed ) == 0
				? '-' // devision by zero
				: $this->msg( 'percent' )
					->numParams( sprintf( '%4.2f', 100 * intval( $synced ) / intval( $reviewed ) ) )
					->escaped();
			$outdated = intval( $reviewed ) - intval( $synced );
			$outdated = $lang->formatNum( max( 0, $outdated ) ); // lag between queries
			$unreviewed = intval( $total ) - intval( $reviewed );
			$unreviewed = $lang->formatNum( max( 0, $unreviewed ) ); // lag between queries

			$linkRenderer = $this->getLinkRenderer();
			$out->addHTML(
				"<tr style='text-align: center;'>
					<td>" .
						htmlspecialchars( $NsText ) .
					"</td>
					<td>" .
						htmlspecialchars( $lang->formatNum( $total ) ) .
					"</td>
					<td>" .
						htmlspecialchars( $lang->formatNum( $reviewed ) .
							$contLang->getDirMark() ) . " <i>$percRev</i>
					</td>
					<td>" .
						htmlspecialchars( $lang->formatNum( $synced ) .
							$contLang->getDirMark() ) . " <i>$percLatest</i>
					</td>
					<td>" .
						$percSynced .
					"</td>
					<td>" .
						$linkRenderer->makeKnownLink(
							SpecialPage::getTitleFor( 'PendingChanges' ),
							$outdated,
							[],
							[ 'namespace' => $namespace ]
						) .
					"</td>"
			);
			if ( !$wgFlaggedRevsProtection ) {
				$out->addHTML( "
					<td>" .
						$linkRenderer->makeKnownLink(
							SpecialPage::getTitleFor( 'UnreviewedPages' ),
							$unreviewed,
							[],
							[ 'namespace' => $namespace ]
						) .
					"</td>"
				);
			}
			$out->addHTML( "
				</tr>"
			);
		}
		$out->addHTML( Xml::closeElement( 'table' ) );
		# Is there a top X user list? If so, then show it...
		$data = $this->getTopReviewers();
		if ( is_array( $data ) && count( $data ) ) {
			$out->addWikiMsg( 'validationstatistics-utable',
				$lang->formatNum( $wgFlaggedRevsStats['topReviewersCount'] ),
				$lang->formatNum( $wgFlaggedRevsStats['topReviewersHours'] )
			);
			$css = 'wikitable flaggedrevs_stats_table';
			$reviewChart = "<table class='$css' style='white-space: nowrap;'>\n";
			$reviewChart .= '<tr><th>' . $this->msg( 'validationstatistics-user' )->escaped() .
				'</th><th>' . $this->msg( 'validationstatistics-reviews' )->escaped() . '</th></tr>';
			foreach ( $data as $userId => $reviews ) {
				$reviewChart .= '<tr><td>' . htmlspecialchars( User::whoIs( $userId ) ) .
					'</td><td>' . htmlspecialchars( $lang->formatNum( $reviews ) ) . '</td></tr>';
			}
			$reviewChart .= "</table>\n";
			$out->addHTML( $reviewChart );
		}
	}

	private function maybeUpdate() {
		global $wgFlaggedRevsStatsAge;

		if ( !$wgFlaggedRevsStatsAge ) {
			return;
		}

		$cache = ObjectCache::getLocalClusterInstance();
		$key = $cache->makeKey( 'flaggedrevs', 'statsUpdated' );
		$keySQL = $cache->makeKey( 'flaggedrevs', 'statsUpdating' );
		// If a cache update is needed, do so asynchronously.
		// Don't trigger query while another is running.
		if ( $cache->get( $key ) ) {
			wfDebugLog( 'ValidationStatistics', __METHOD__ . " skipping, got data" );
		} elseif ( $cache->get( $keySQL ) ) {
			wfDebugLog( 'ValidationStatistics', __METHOD__ . " skipping, in progress" );
		} else {
			global $wgPhpCli;
			$ext = !empty( $wgPhpCli ) ? $wgPhpCli : 'php';
			$path = wfEscapeShellArg( __DIR__ . '/../maintenance/updateStats.php' );
			$wiki = wfEscapeShellArg( wfWikiID() );
			$devNull = wfIsWindows() ? "NUL:" : "/dev/null";
			$commandLine = "$ext $path --wiki=$wiki > $devNull &";
			wfDebugLog( 'ValidationStatistics', __METHOD__ . " executing: $commandLine" );
			wfShellExec( $commandLine );
		}
	}

	private function readyForQuery() {
		$dbr = wfGetDB( DB_REPLICA );

		if ( !$dbr->tableExists( 'flaggedrevs_statistics', __METHOD__ ) ) {
			return false;
		} else {
			return $dbr->selectField( 'flaggedrevs_statistics', 'COUNT(*)', [], __METHOD__ ) != 0;
		}
	}

	private function getEditorCount() {
		$dbr = wfGetDB( DB_REPLICA );

		return $dbr->selectField( 'user_groups', 'COUNT(*)',
			[
				'ug_group' => 'editor',
				'ug_expiry IS NULL OR ug_expiry >= ' . $dbr->addQuotes( $dbr->timestamp() )
			],
			__METHOD__ );
	}

	private function getReviewerCount() {
		$dbr = wfGetDB( DB_REPLICA );

		return $dbr->selectField( 'user_groups', 'COUNT(*)',
			[
				'ug_group' => 'reviewer',
				'ug_expiry IS NULL OR ug_expiry >= ' . $dbr->addQuotes( $dbr->timestamp() )
			],
			__METHOD__ );
	}

	/**
	 * @return array
	 */
	private function getStats() {
		if ( $this->latestData === null ) {
			$this->latestData = FlaggedRevsStats::getStats();
		}
		return $this->latestData;
	}

	private function getMeanReviewWaitAnon() {
		$stats = $this->getStats();
		return $stats['reviewLag-anon-average'];
	}

	private function getMedianReviewWaitAnon() {
		$stats = $this->getStats();
		return $stats['reviewLag-anon-median'];
	}

	private function getMeanPendingWait() {
		$stats = $this->getStats();
		return $stats['pendingLag-average'];
	}

	private function getTotalPages( $ns ) {
		$stats = $this->getStats();
		return $stats['totalPages-NS'][$ns] ?? '-';
	}

	private function getReviewedPages( $ns ) {
		$stats = $this->getStats();
		return $stats['reviewedPages-NS'][$ns] ?? '-';
	}

	private function getSyncedPages( $ns ) {
		$stats = $this->getStats();
		return $stats['syncedPages-NS'][$ns] ?? '-';
	}

	private function getReviewPercentilesAnon() {
		$stats = $this->getStats();
		return $stats['reviewLag-anon-percentile'];
	}

	private function getLastUpdate() {
		$stats = $this->getStats();
		return $stats['statTimestamp'];
	}

	/**
	 * Get top X reviewers in the last Y hours
	 * @return array
	 */
	private function getTopReviewers() {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$fname = __METHOD__;

		return $cache->getWithSetCallback(
			$cache->makeKey( 'flaggedrevs', 'reviewTopUsers' ),
			$cache::TTL_HOUR,
			function () use ( $fname ) {
				global $wgFlaggedRevsStats;

				$dbr = wfGetDB( DB_REPLICA, 'vslow' );

				$limit = (int)$wgFlaggedRevsStats['topReviewersCount'];
				$seconds = 3600 * $wgFlaggedRevsStats['topReviewersHours'];
				$cutoff = $dbr->timestamp( time() - $seconds );
				$actorQuery = ActorMigration::newMigration()->getJoin( 'log_user' );
				$res = $dbr->select(
					[ 'logging' ] + $actorQuery['tables'],
					[ 'user' => $actorQuery['fields']['log_user'], 'COUNT(*) AS reviews' ],
					[
						'log_type' => 'review', // page reviews
						// manual approvals (filter on log_action)
						'log_action' => [ 'approve', 'approve2', 'approve-i', 'approve2-i' ],
						'log_timestamp >= ' . $dbr->addQuotes( $cutoff ) // last hour
					],
					$fname,
					[
						'GROUP BY' => $actorQuery['fields']['log_user'],
						'ORDER BY' => 'reviews DESC',
						'LIMIT' => $limit
					],
					$actorQuery['joins']
				);

				$data = [];
				foreach ( $res as $row ) {
					$data[$row->user] = $row->reviews;
				}

				return $data;
			},
			[
				'lockTSE' => 300,
				'staleTTL' => $cache::TTL_MINUTE
			]
		);
	}

	/**
	 * @return string
	 */
	protected function getGroupName() {
		return 'quality';
	}
}
