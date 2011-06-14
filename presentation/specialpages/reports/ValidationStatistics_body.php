<?php

class ValidationStatistics extends IncludableSpecialPage {
	protected $latestData = null;

	public function __construct() {
		parent::__construct( 'ValidationStatistics' );
	}

	public function execute( $par ) {
		global $wgUser, $wgOut, $wgLang, $wgContLang, $wgFlaggedRevsStats;
		$this->setHeaders();
		$this->skin = $wgUser->getSkin();
		$this->db = wfGetDB( DB_SLAVE );

		$this->maybeUpdate();

		$ec = $this->getEditorCount();
		$rc = $this->getReviewerCount();
		$mt = $this->getMeanReviewWait();
		$mdt = $this->getMedianReviewWait();
		$pt = $this->getMeanPendingWait();
		$pData = $this->getPercentiles();
		$timestamp = $this->getLastUpdate();

		$wgOut->addWikiMsg( 'validationstatistics-users',
			$wgLang->formatnum( $ec ), $wgLang->formatnum( $rc )
		);
		# Most of the output depends on background queries
		if ( !$this->readyForQuery() ) {
			return false;
		}

		# Is there a review time table available?
		if ( is_array( $pData ) && count( $pData ) ) {
			$headerRows = $dataRows = '';
			foreach ( $pData as $percentile => $perValue ) {
				$headerRows .= "<th>P<sub>" . intval( $percentile ) . "</sub></th>";
				$dataRows .= '<td>' . $wgLang->formatTimePeriod( $perValue ) . '</td>';
			}
			$css = 'wikitable flaggedrevs_stats_table';
			$reviewChart = "<table class='$css' style='white-space: nowrap;'>\n";
			$reviewChart .= "<tr align='center'>$headerRows</tr>\n";
			$reviewChart .= "<tr align='center'>$dataRows</tr>\n";
			$reviewChart .= "</table>\n";
		} else {
			$reviewChart = '';
		}

		if ( $timestamp != '-' ) {
			# Show "last updated"...
			$wgOut->addWikiMsg( 'validationstatistics-lastupdate',
				 $wgLang->date( $timestamp, true ),
				 $wgLang->time( $timestamp, true )
			);
		}
		$wgOut->addHtml( '<hr/>' );
		# Show pending time stats...
		$wgOut->addWikiMsg( 'validationstatistics-pndtime', $wgLang->formatTimePeriod( $pt ) );
		# Show review time stats...
		if ( !FlaggedRevs::useOnlyIfProtected() ) {
			$wgOut->addWikiMsg( 'validationstatistics-revtime',
				$wgLang->formatTimePeriod( $mt ),
				$wgLang->formatTimePeriod( $mdt ),
				$reviewChart
			);
		}
		# Show per-namespace stats table...
		$wgOut->addWikiMsg( 'validationstatistics-table' );
		$wgOut->addHTML(
			Xml::openElement( 'table', array( 'class' => 'wikitable flaggedrevs_stats_table' ) )
		);
		$wgOut->addHTML( "<tr>\n" );
		// Headings (for a positive grep result):
		// validationstatistics-ns, validationstatistics-total, validationstatistics-stable,
		// validationstatistics-latest, validationstatistics-synced, validationstatistics-old
		$msgs = array( 'ns', 'total', 'stable', 'latest', 'synced', 'old' ); // our headings
		foreach ( $msgs as $msg ) {
			$wgOut->addHTML( '<th>' .
				wfMsgExt( "validationstatistics-$msg", 'parseinline' ) . '</th>' );
		}
		$wgOut->addHTML( "</tr>\n" );
		$namespaces = FlaggedRevs::getReviewNamespaces();
		foreach ( $namespaces as $namespace ) {
			$total = $this->getTotalPages( $namespace );
			$reviewed = $this->getReviewedPages( $namespace );
			$synced = $this->getSyncedPages( $namespace );
			if ( $total === '-' || $reviewed === '-' || $synced === '-' ) {
				continue; // NS added to config recently?
			}

			$NsText = $wgContLang->getFormattedNsText( $namespace );
			$NsText = $NsText ? $NsText : wfMsgHTML( 'blanknamespace' );

			$percRev = intval( $total ) == 0
				? '-' // devision by zero
				: wfMsg( 'parentheses',
					wfMsgExt( 'percent', array( 'escapenoentities' ),
						$wgLang->formatnum( sprintf( '%4.2f',
							100 * intval( $reviewed ) / intval( $total ) ) )
					)
				);
			$percLatest = intval( $total ) == 0
				? '-' // devision by zero
				: wfMsg( 'parentheses', 
					wfMsgExt( 'percent', array( 'escapenoentities' ),
						$wgLang->formatnum( sprintf( '%4.2f',
							100 * intval( $synced ) / intval( $total ) ) )
					)
				);
			$percSynced = intval( $reviewed ) == 0
				? '-' // devision by zero
				: wfMsgExt( 'percent', array( 'escapenoentities' ),
					$wgLang->formatnum( sprintf( '%4.2f',
						100 * intval( $synced ) / intval( $reviewed ) ) )
				);
			$outdated = intval( $reviewed ) - intval( $synced );
			$outdated = $wgLang->formatnum( max( 0, $outdated ) ); // lag between queries

			$wgOut->addHTML(
				"<tr align='center'>
					<td>" .
						htmlspecialchars( $NsText ) .
					"</td>
					<td>" .
						htmlspecialchars( $wgLang->formatnum( $total ) ) .
					"</td>
					<td>" .
						htmlspecialchars( $wgLang->formatnum( $reviewed ) .
							$wgContLang->getDirMark() ) . " <i>$percRev</i>
					</td>
					<td>" .
						htmlspecialchars( $wgLang->formatnum( $synced ) .
							$wgContLang->getDirMark() ) . " <i>$percLatest</i>
					</td>
					<td>" .
						$percSynced .
					"</td>
					<td>" .
						$this->skin->linkKnown( SpecialPage::getTitleFor( 'PendingChanges' ),
							htmlspecialchars( $outdated ),
							array(),
							array( 'namespace' => $namespace )
						) .
					"</td>
				</tr>"
			);
		}
		$wgOut->addHTML( Xml::closeElement( 'table' ) );
		# Is there a top X user list? If so, then show it...
		$data = $this->getTopReviewers();
		if ( is_array( $data ) && count( $data ) ) {
			$wgOut->addWikiMsg( 'validationstatistics-utable',
				$wgLang->formatNum( $wgFlaggedRevsStats['topReviewersCount'] ),
				$wgLang->formatNum( $wgFlaggedRevsStats['topReviewersHours'] )
			);
			$css = 'wikitable flaggedrevs_stats_table';
			$reviewChart = "<table class='$css' style='white-space: nowrap;'>\n";
			$reviewChart .= '<tr><th>' . wfMsgHtml( 'validationstatistics-user' ) .
				'</th><th>' . wfMsgHtml( 'validationstatistics-reviews' ) . '</th></tr>';
			foreach ( $data as $userId => $reviews ) {
				$reviewChart .= '<tr><td>' . htmlspecialchars( User::whois( $userId ) ) .
					'</td><td>' . $wgLang->formatNum( $reviews ) . '</td></tr>';
			}
			$reviewChart .= "</table>\n";
			$wgOut->addHTML( $reviewChart );
		}
	}

	protected function maybeUpdate() {
		global $wgFlaggedRevsStatsAge;
		if ( !$wgFlaggedRevsStatsAge ) {
			return false;
		}
		$dbCache = wfGetCache( CACHE_DB );
		$key = wfMemcKey( 'flaggedrevs', 'statsUpdated' );
		$keySQL = wfMemcKey( 'flaggedrevs', 'statsUpdating' );
		// If a cache update is needed, do so asynchronously.
		// Don't trigger query while another is running.
		if ( $dbCache->get( $key ) ) {
			wfDebugLog( 'ValidationStatistics', __METHOD__ . " skipping, got data" );
		} elseif ( $dbCache->get( $keySQL ) ) {
			wfDebugLog( 'ValidationStatistics', __METHOD__ . " skipping, in progress" );
		} else {
			global $wgPhpCli;
			$ext = !empty( $wgPhpCli ) ? $wgPhpCli : 'php';
			$path = wfEscapeShellArg( dirname( __FILE__ ) . '/../maintenance/updateStats.php' );
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
			array( 'ug_group' => 'editor' ),
			__METHOD__ );
	}

	protected function getReviewerCount() {
		return $this->db->selectField( 'user_groups', 'COUNT(*)',
			array( 'ug_group' => 'reviewer' ),
			__METHOD__ );
	}

	protected function getLatestStats() {
		if ( $this->latestData !== null ) {
			return $this->latestData;
		}
		$this->latestData = FlaggedRevsStats::getLatestStats();
		return $this->latestData;
	}

	protected function getMeanReviewWait() {
		$stats = $this->getLatestStats();
		return $stats['reviewLag-average'];
	}
	
	protected function getMedianReviewWait() {
		$stats = $this->getLatestStats();
		return $stats['reviewLag-median'];
	}
	
	protected function getMeanPendingWait() {
		$stats = $this->getLatestStats();
		return $stats['pendingLag-average'];
	}

	protected function getTotalPages( $ns ) {
		$stats = $this->getLatestStats();
		return isset( $stats['totalPages-NS'][$ns] )
			? $stats['totalPages-NS'][$ns]
			: '-';
	}
	
	protected function getReviewedPages( $ns ) {
		$stats = $this->getLatestStats();
		return isset( $stats['reviewedPages-NS'][$ns] )
			? $stats['reviewedPages-NS'][$ns]
			: '-';
	}

	protected function getSyncedPages( $ns ) {
		$stats = $this->getLatestStats();
		return isset( $stats['syncedPages-NS'][$ns] )
			? $stats['syncedPages-NS'][$ns]
			: '-';
	}

	protected function getPercentiles() {
		$stats = $this->getLatestStats();
		return $stats['reviewLag-percentile'];
	}

	protected function getLastUpdate() {
		$stats = $this->getLatestStats();
		return $stats['statTimestamp'];
	}
	
	// top X reviewers in the last Y hours
	protected function getTopReviewers() {
		global $wgFlaggedRevsStats;
		
		$key = wfMemcKey( 'flaggedrevs', 'reviewTopUsers' );
		$dbCache = wfGetCache( CACHE_DB );
		$data = $dbCache->get( $key );
		if ( is_array( $data ) ) {
			return $data; // cache hit
		}
		$limit = (int)$wgFlaggedRevsStats['topReviewersCount'];
		$seconds = 3600*$wgFlaggedRevsStats['topReviewersHours'];

		$dbr = wfGetDB( DB_SLAVE );
		$cutoff = $dbr->timestamp( time() - $seconds );
		$res = $dbr->select( 'logging',
			array( 'log_user', 'COUNT(*) AS reviews' ),
			array(
				'log_type' => 'review', // page reviews
				// manual approvals (filter on log_action)
				'log_action' => array( 'approve', 'approve2', 'approve-i', 'approve2-i' ),
				'log_timestamp >= ' . $dbr->addQuotes( $cutoff ) // last hour
			),
			__METHOD__,
			array( 'GROUP BY' => 'log_user', 'ORDER BY' => 'reviews DESC', 'LIMIT' => $limit )
		);
		$data = array();
		foreach ( $res as $row ) {
			$data[$row->log_user] = $row->reviews;
		}
		// Save/cache users
		$dbCache->set( $key, $data, 3600 );
		return $data;
	}
}
