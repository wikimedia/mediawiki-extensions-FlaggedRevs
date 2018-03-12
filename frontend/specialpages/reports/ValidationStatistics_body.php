<?php

class ValidationStatistics extends IncludableSpecialPage {
	protected $latestData = null;

	public function __construct() {
		parent::__construct( 'ValidationStatistics' );
	}

	public function execute( $par ) {
		global $wgContLang, $wgFlaggedRevsStats, $wgFlaggedRevsProtection;

		$out = $this->getOutput();
		$lang = $this->getLanguage();

		$this->setHeaders();
		$this->db = wfGetDB( DB_REPLICA );

		$this->maybeUpdate();

		$ec = $this->getEditorCount();
		$rc = $this->getReviewerCount();
		$mt = $this->getMeanReviewWaitAnon();
		$mdt = $this->getMedianReviewWaitAnon();
		$pt = $this->getMeanPendingWait();
		$pData = $this->getReviewPercentilesAnon();
		$timestamp = $this->getLastUpdate();

		$out->addWikiMsg( 'validationstatistics-users',
			$lang->formatnum( $ec ), $lang->formatnum( $rc )
		);
		# Most of the output depends on background queries
		if ( !$this->readyForQuery() ) {
			return false;
		}

		# Is there a review time table available?
		if ( count( $pData ) ) {
			$headerRows = $dataRows = '';
			foreach ( $pData as $percentile => $perValue ) {
				$headerRows .= "<th>P<sub>" . intval( $percentile ) . "</sub></th>";
				$dataRows .= '<td>' .
					$lang->formatTimePeriod( $perValue, 'avoidminutes' ) . '</td>';
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
		$out->addHtml( '<hr/>' );
		# Show pending time stats...
		$out->addWikiMsg( 'validationstatistics-pndtime',
			$lang->formatTimePeriod( $pt, 'avoidminutes' ) );
		# Show review time stats...
		if ( !FlaggedRevs::useSimpleConfig() ) {
			$out->addWikiMsg( 'validationstatistics-revtime',
				$lang->formatTimePeriod( $mt, 'avoidminutes' ),
				$lang->formatTimePeriod( $mdt, 'avoidminutes' ),
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
		foreach ( $namespaces as $namespace ) {
			$total = $this->getTotalPages( $namespace );
			$reviewed = $this->getReviewedPages( $namespace );
			$synced = $this->getSyncedPages( $namespace );
			if ( $total === '-' || $reviewed === '-' || $synced === '-' ) {
				continue; // NS added to config recently?
			}

			$NsText = $wgContLang->getFormattedNsText( $namespace );
			$NsText = $NsText ? $NsText : $this->msg( 'blanknamespace' )->escaped();

			$percRev = intval( $total ) == 0
				? '-' // devision by zero
				: $this->msg( 'parentheses',
					$this->msg( 'percent' )
						->numParams( sprintf(
							'%4.2f',
							100 * intval( $reviewed ) / intval( $total )
						) )->escaped()
				)->text();
			$percLatest = intval( $total ) == 0
				? '-' // devision by zero
				: $this->msg( 'parentheses',
					$this->msg( 'percent' )
						->numParams( sprintf( '%4.2f', 100 * intval( $synced ) / intval( $total )
						) )->escaped()
				)->text();
			$percSynced = intval( $reviewed ) == 0
				? '-' // devision by zero
				: $this->msg( 'percent' )
					->numParams( sprintf( '%4.2f', 100 * intval( $synced ) / intval( $reviewed ) ) )
					->escaped();
			$outdated = intval( $reviewed ) - intval( $synced );
			$outdated = $lang->formatnum( max( 0, $outdated ) ); // lag between queries
			$unreviewed = intval( $total ) - intval( $reviewed );
			$unreviewed = $lang->formatnum( max( 0, $unreviewed ) ); // lag between queries

			$linkRenderer = $this->getLinkRenderer();
			$out->addHTML(
				"<tr style='text-align: center;'>
					<td>" .
						htmlspecialchars( $NsText ) .
					"</td>
					<td>" .
						htmlspecialchars( $lang->formatnum( $total ) ) .
					"</td>
					<td>" .
						htmlspecialchars( $lang->formatnum( $reviewed ) .
							$wgContLang->getDirMark() ) . " <i>$percRev</i>
					</td>
					<td>" .
						htmlspecialchars( $lang->formatnum( $synced ) .
							$wgContLang->getDirMark() ) . " <i>$percLatest</i>
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
				$reviewChart .= '<tr><td>' . htmlspecialchars( User::whois( $userId ) ) .
					'</td><td>' . $lang->formatNum( $reviews ) . '</td></tr>';
			}
			$reviewChart .= "</table>\n";
			$out->addHTML( $reviewChart );
		}

		return true;
	}

	protected function maybeUpdate() {
		global $wgFlaggedRevsStatsAge;

		if ( !$wgFlaggedRevsStatsAge ) {
			return false;
		}

		$stash = ObjectCache::getMainStashInstance();
		$key = $stash->makeKey( 'flaggedrevs', 'statsUpdated' );
		$keySQL = $stash->makeKey( 'flaggedrevs', 'statsUpdating' );
		// If a cache update is needed, do so asynchronously.
		// Don't trigger query while another is running.
		if ( $stash->get( $key ) ) {
			wfDebugLog( 'ValidationStatistics', __METHOD__ . " skipping, got data" );
		} elseif ( $stash->get( $keySQL ) ) {
			wfDebugLog( 'ValidationStatistics', __METHOD__ . " skipping, in progress" );
		} else {
			global $wgPhpCli;
			$ext = !empty( $wgPhpCli ) ? $wgPhpCli : 'php';
			$path = wfEscapeShellArg( __DIR__ . '/../maintenance/updateStats.php' );
			$wiki = wfEscapeShellArg( wfWikiId() );
			$devNull = wfIsWindows() ? "NUL:" : "/dev/null";
			$commandLine = "$ext $path --wiki=$wiki > $devNull &";
			wfDebugLog( 'ValidationStatistics', __METHOD__ . " executing: $commandLine" );
			wfShellExec( $commandLine );
			return true;
		}
		return false;
	}

	protected function readyForQuery() {
		if ( !$this->db->tableExists( 'flaggedrevs_statistics' ) ) {
			return false;
		} else {
			return ( 0 != $this->db->selectField( 'flaggedrevs_statistics', 'COUNT(*)' ) );
		}
	}

	protected function getEditorCount() {
		return $this->db->selectField( 'user_groups', 'COUNT(*)',
			[
				'ug_group' => 'editor',
				'ug_expiry IS NULL OR ug_expiry >= ' . $this->db->addQuotes( $this->db->timestamp() )
			],
			__METHOD__ );
	}

	protected function getReviewerCount() {
		return $this->db->selectField( 'user_groups', 'COUNT(*)',
			[
				'ug_group' => 'reviewer',
				'ug_expiry IS NULL OR ug_expiry >= ' . $this->db->addQuotes( $this->db->timestamp() )
			],
			__METHOD__ );
	}

	protected function getStats() {
		if ( $this->latestData === null ) {
			$this->latestData = FlaggedRevsStats::getStats();
		}
		return $this->latestData;
	}

	protected function getMeanReviewWaitAnon() {
		$stats = $this->getStats();
		return $stats['reviewLag-anon-average'];
	}

	protected function getMedianReviewWaitAnon() {
		$stats = $this->getStats();
		return $stats['reviewLag-anon-median'];
	}

	protected function getMeanPendingWait() {
		$stats = $this->getStats();
		return $stats['pendingLag-average'];
	}

	protected function getTotalPages( $ns ) {
		$stats = $this->getStats();
		return isset( $stats['totalPages-NS'][$ns] )
			? $stats['totalPages-NS'][$ns]
			: '-';
	}

	protected function getReviewedPages( $ns ) {
		$stats = $this->getStats();
		return isset( $stats['reviewedPages-NS'][$ns] )
			? $stats['reviewedPages-NS'][$ns]
			: '-';
	}

	protected function getSyncedPages( $ns ) {
		$stats = $this->getStats();
		return isset( $stats['syncedPages-NS'][$ns] )
			? $stats['syncedPages-NS'][$ns]
			: '-';
	}

	protected function getReviewPercentilesAnon() {
		$stats = $this->getStats();
		return $stats['reviewLag-anon-percentile'];
	}

	protected function getLastUpdate() {
		$stats = $this->getStats();
		return $stats['statTimestamp'];
	}

	/**
	 * Get top X reviewers in the last Y hours
	 * @return array
	 */
	protected function getTopReviewers() {
		global $wgFlaggedRevsStats;

		$stash = ObjectCache::getMainStashInstance();
		$key = $stash->makeKey( 'flaggedrevs', 'reviewTopUsers' );
		$data = $stash->get( $key );
		if ( is_array( $data ) ) {
			return $data; // cache hit
		}

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
			__METHOD__,
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

		// Save/cache users
		$stash->set( $key, $data, 3600 );

		return $data;
	}

	protected function getGroupName() {
		return 'quality';
	}
}
