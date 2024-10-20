<?php

use MediaWiki\MediaWikiServices;
use Wikimedia\ObjectCache\BagOStuff;
use Wikimedia\Rdbms\IExpression;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Rdbms\RawSQLExpression;
use Wikimedia\Rdbms\SelectQueryBuilder;
use Wikimedia\Rdbms\Subquery;

/**
 * FlaggedRevs stats functions
 */
class FlaggedRevsStats {
	/**
	 * @return array of current FR stats
	 */
	public static function getStats() {
		$data = [
			'reviewLag-anon-sampleSize' => '-',
			'reviewLag-anon-average' => '-',
			'reviewLag-anon-median' => '-',
			'reviewLag-anon-percentile' => [],
			'reviewLag-user-sampleSize' => '-',
			'reviewLag-user-average' => '-',
			'reviewLag-user-median' => '-',
			'reviewLag-user-percentile' => [],
			'totalPages-NS' => [],
			'reviewedPages-NS' => [],
			'syncedPages-NS' => [],
			'pendingLag-average' => '-',
			'statTimestamp' => '-',
		];

		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
		// Latest timestamp recorded
		$timestamp = $dbr->newSelectQueryBuilder()
			->select( 'MAX(frs_timestamp)' )
			->from( 'flaggedrevs_statistics' )
			->caller( __METHOD__ )
			->fetchField();

		if ( $timestamp !== false ) {
			$data['statTimestamp'] = wfTimestamp( TS_MW, $timestamp );

			$res = $dbr->newSelectQueryBuilder()
				->select( [ 'frs_stat_key', 'frs_stat_val' ] )
				->from( 'flaggedrevs_statistics' )
				->where( [ 'frs_timestamp' => $dbr->timestamp( $timestamp ) ] )
				->caller( __METHOD__ )
				->fetchResultSet();
			foreach ( $res as $row ) {
				$key = explode( ':', $row->frs_stat_key );
				switch ( $key[0] ) {
					case 'reviewLag-anon-sampleSize':
					case 'reviewLag-anon-average':
					case 'reviewLag-anon-median':
					case 'reviewLag-user-sampleSize':
					case 'reviewLag-user-average':
					case 'reviewLag-user-median':
					case 'pendingLag-average':
						$data[$key[0]] = (int)$row->frs_stat_val;
						break;
					case 'reviewLag-anon-percentile': // <stat name,percentile>
					case 'reviewLag-user-percentile': // <stat name,percentile>
						$data[$key[0]][$key[1]] = (int)$row->frs_stat_val;
						break;
					case 'totalPages-NS': // <stat name,namespace>
					case 'reviewedPages-NS': // <stat name,namespace>
					case 'syncedPages-NS': // <stat name,namespace>
						$data[$key[0]][$key[1]] = (int)$row->frs_stat_val;
						break;
				}
			}
		}

		return $data;
	}

	/**
	 * Run a stats update and update the DB
	 * Note: this can easily be too expensive to run live
	 *
	 * @return void
	 */
	public static function updateCache() {
		$rNamespaces = FlaggedRevs::getReviewNamespaces();
		$cache = MediaWikiServices::getInstance()
			->getObjectCacheFactory()->getLocalClusterInstance();
		if ( !$rNamespaces ) {
			return; // no SQL errors please :)
		}

		// Get total, reviewed, and synced page count for each namespace
		[ $ns_total, $ns_reviewed, $ns_synced ] = self::getPerNamespaceTotals();

		// Getting mean pending edit time
		// @TODO: percentiles?
		$avePET = self::getMeanPendingEditTime();

		# Get wait (till review) time samples for anon edits...
		$reviewDataAnon = self::getEditReviewTimes( $cache, 'anons' );
		# Get wait (till review) time samples for logged-in user edits...
		$reviewDataUser = self::getEditReviewTimes( $cache, 'users' );

		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();
		// The timestamp to identify this whole batch of data
		$encDataTimestamp = $dbw->timestamp();

		$dataSet = [];
		// Data range for samples...
		$dataSet[] = [
			'frs_stat_key'  => 'reviewLag-anon-sampleStartTimestamp',
			'frs_stat_val'  => $reviewDataAnon['sampleStartTS'], // unix
			'frs_timestamp' => $encDataTimestamp ];
		$dataSet[] = [
			'frs_stat_key'  => 'reviewLag-user-sampleStartTimestamp',
			'frs_stat_val'  => $reviewDataUser['sampleStartTS'], // unix
			'frs_timestamp' => $encDataTimestamp ];
		$dataSet[] = [
			'frs_stat_key'  => 'reviewLag-anon-sampleEndTimestamp',
			'frs_stat_val'  => $reviewDataAnon['sampleEndTS'], // unix
			'frs_timestamp' => $encDataTimestamp ];
		$dataSet[] = [
			'frs_stat_key'  => 'reviewLag-user-sampleEndTimestamp',
			'frs_stat_val'  => $reviewDataUser['sampleEndTS'], // unix
			'frs_timestamp' => $encDataTimestamp ];
		// All-namespace percentiles...
		foreach ( $reviewDataAnon['percTable'] as $percentile => $seconds ) {
			$dataSet[] = [
				'frs_stat_key'  => 'reviewLag-anon-percentile:' . (int)$percentile,
				'frs_stat_val'  => $seconds,
				'frs_timestamp' => $encDataTimestamp ];
		}
		foreach ( $reviewDataUser['percTable'] as $percentile => $seconds ) {
			$dataSet[] = [
				'frs_stat_key'  => 'reviewLag-user-percentile:' . (int)$percentile,
				'frs_stat_val'  => $seconds,
				'frs_timestamp' => $encDataTimestamp ];
		}
		// Sample sizes...
		$dataSet[] = [
			'frs_stat_key'  => 'reviewLag-anon-sampleSize',
			'frs_stat_val'  => $reviewDataAnon['sampleSize'],
			'frs_timestamp' => $encDataTimestamp ];
		$dataSet[] = [
			'frs_stat_key'  => 'reviewLag-user-sampleSize',
			'frs_stat_val'  => $reviewDataUser['sampleSize'],
			'frs_timestamp' => $encDataTimestamp ];

		// All-namespace ave/med review lag & ave pending lag stats...
		$dataSet[] = [
			'frs_stat_key'  => 'reviewLag-anon-average',
			'frs_stat_val'  => $reviewDataAnon['average'],
			'frs_timestamp' => $encDataTimestamp ];
		$dataSet[] = [
			'frs_stat_key'  => 'reviewLag-user-average',
			'frs_stat_val'  => $reviewDataUser['average'],
			'frs_timestamp' => $encDataTimestamp ];
		$dataSet[] = [
			'frs_stat_key'  => 'reviewLag-anon-median',
			'frs_stat_val'  => $reviewDataAnon['median'],
			'frs_timestamp' => $encDataTimestamp ];
		$dataSet[] = [
			'frs_stat_key'  => 'reviewLag-user-median',
			'frs_stat_val'  => $reviewDataUser['median'],
			'frs_timestamp' => $encDataTimestamp ];
		$dataSet[] = [
			'frs_stat_key'  => 'pendingLag-average',
			'frs_stat_val'  => $avePET,
			'frs_timestamp' => $encDataTimestamp ];

		// Per-namespace total/reviewed/synced stats...
		foreach ( $rNamespaces as $namespace ) {
			$dataSet[] = [
				'frs_stat_key'  => 'totalPages-NS:' . (int)$namespace,
				'frs_stat_val'  => $ns_total[$namespace] ?? 0,
				'frs_timestamp' => $encDataTimestamp ];
			$dataSet[] = [
				'frs_stat_key'  => 'reviewedPages-NS:' . (int)$namespace,
				'frs_stat_val'  => $ns_reviewed[$namespace] ?? 0,
				'frs_timestamp' => $encDataTimestamp ];
			$dataSet[] = [
				'frs_stat_key'  => 'syncedPages-NS:' . (int)$namespace,
				'frs_stat_val'  => $ns_synced[$namespace] ?? 0,
				'frs_timestamp' => $encDataTimestamp ];
		}

		// Save the data...
		$dbw->newInsertQueryBuilder()
			->insertInto( 'flaggedrevs_statistics' )
			->ignore()
			->rows( $dataSet )
			->caller( __METHOD__ )
			->execute();
	}

	/**
	 * @return int[][]
	 */
	private static function getPerNamespaceTotals() {
		$ns_total = [];
		$ns_reviewed = [];
		$ns_synced = [];
		// Get total, reviewed, and synced page count for each namespace
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase( false, 'vslow' );
		$res = $dbr->newSelectQueryBuilder()
			->select( [
				'page_namespace',
				'total' => 'COUNT(*)',
				'reviewed' => 'COUNT(fp_page_id)',
				'pending' => 'COUNT(fp_pending_since)'
			] )
			->from( 'page' )
			->leftJoin( 'flaggedpages', null, 'fp_page_id = page_id' )
			->where( [
				'page_is_redirect' => 0,
				'page_namespace' => FlaggedRevs::getReviewNamespaces()
			] )
			->groupBy( 'page_namespace' )
			->caller( __METHOD__ )
			->fetchResultSet();
		foreach ( $res as $row ) {
			$ns_total[$row->page_namespace] = (int)$row->total;
			$ns_reviewed[$row->page_namespace] = (int)$row->reviewed;
			$ns_synced[$row->page_namespace] = (int)$row->reviewed - (int)$row->pending;
		}
		return [ $ns_total, $ns_reviewed, $ns_synced ];
	}

	/**
	 * @param IReadableDatabase $db
	 * @param string $column
	 *
	 * @return string
	 */
	private static function dbUnixTime( IReadableDatabase $db, $column ) {
		return $db->getType() === 'sqlite' ? "strftime('%s',$column)" : "UNIX_TIMESTAMP($column)";
	}

	/**
	 * @return int
	 */
	private static function getMeanPendingEditTime() {
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase( false, 'vslow' );
		$nowUnix = wfTimestamp();
		$unixTimeCall = self::dbUnixTime( $dbr, 'fp_pending_since' );
		return (int)$dbr->newSelectQueryBuilder()
			->select( "AVG( $nowUnix - $unixTimeCall )" )
			->from( 'flaggedpages' )
			->join( 'page', null, 'fp_page_id = page_id' )
			->where( [
				$dbr->expr( 'fp_pending_since', '!=', null ),
				'page_namespace' => FlaggedRevs::getReviewNamespaces() // sanity
			] )
			->caller( __METHOD__ )
			->fetchField();
	}

	/**
	 * Get edit review time statistics (as recent as possible)
	 * @param BagOStuff $cache
	 * @param string $users string "anons" or "users"
	 * @return array associative
	 */
	private static function getEditReviewTimes( $cache, $users ) {
		$result = [
			'average'       => 0,
			'median'        => 0,
			'percTable'     => [],
			'sampleSize'    => 0,
			'sampleStartTS' => null,
			'sampleEndTS'   => null
		];
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			return $result; // disabled
		}

		$rPerTable = []; // review wait percentiles
		# Only go so far back...otherwise we will get garbage values due to
		# the fact that FlaggedRevs wasn't enabled until after a while.
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase( false, 'vslow' );
		$installedUnix = $dbr->newSelectQueryBuilder()
			->select( self::dbUnixTime( $dbr, 'MIN(log_timestamp)' ) )
			->from( 'logging' )
			->where( [ 'log_type' => 'review' ] )
			->caller( __METHOD__ )
			->fetchField();
		$dbInstalled = $dbr->timestamp( $installedUnix ?: wfTimestamp() );
		# Skip the most recent recent revs as they are likely to just
		# be WHERE condition misses. This also gives us more data to use.
		# Lastly, we want to avoid bias that would make the time too low
		# since new revisions could not have "took a long time to sight".
		$worstLagTS = $dbr->timestamp(); // now
		$lastTS = $dbInstalled;
		while ( true ) { // should almost always be ~1 pass
			# Get the page with the worst pending lag...
			$row = $dbr->newSelectQueryBuilder()
				->select( [ 'fp_page_id', 'fr_rev_id', 'fp_pending_since', 'fr_timestamp' ] )
				->from( 'flaggedpages' )
				->join( 'flaggedrevs', null, [ 'fr_page_id = fp_page_id', 'fr_rev_id = fp_stable' ] )
				->where( [
					$dbr->expr( 'fp_pending_since', '!=', null ),
					$dbr->expr( 'fp_pending_since', '>', $lastTS ), // skip failed rows
				] )
				->orderBy( 'fp_pending_since' )
				->caller( __METHOD__ )->fetchRow();
			if ( !$row ) {
				break;
			}
			# Find the newest revision at the time the page was reviewed,
			# this is the one that *should* have been reviewed.
			$idealRev = (int)$dbr->newSelectQueryBuilder()
				->select( 'rev_id' )
				->from( 'revision' )
				->where( [
					'rev_page' => $row->fp_page_id,
					$dbr->expr( 'rev_timestamp', '<', $row->fr_timestamp ),
				] )
				->caller( __METHOD__ )
				->orderBy( 'rev_timestamp', SelectQueryBuilder::SORT_DESC )
				->fetchField();
			if ( $row->fr_rev_id >= $idealRev ) {
				$worstLagTS = $row->fp_pending_since;
				break; // sane $worstLagTS found
			# Fudge factor to prevent deliberate reviewing of non-current revisions
			# from squeezing the range. Shouldn't effect anything otherwise.
			} else {
				$lastTS = $row->fp_pending_since; // next iteration
			}
		}

		$tempUserConfig = MediaWikiServices::getInstance()->getTempUserConfig();

		# User condition (anons/users)
		if ( $users === 'anons' ) {
			$anonConds = [ new RawSQLExpression( 'actor_user IS NULL' ) ];
			if ( $tempUserConfig->isKnown() ) {
				$anonConds[] = $tempUserConfig->getMatchCondition( $dbr, 'actor_name', IExpression::LIKE );
			}

			$userCondition = $dbr->orExpr( $anonConds );
		} elseif ( $users === 'users' ) {
			$userConds = [ new RawSQLExpression( 'actor_user IS NOT NULL' ) ];
			if ( $tempUserConfig->isKnown() ) {
				$userConds[] = $tempUserConfig->getMatchCondition( $dbr, 'actor_name', IExpression::NOT_LIKE );
			}

			$userCondition = $dbr->andExpr( $userConds );
		} else {
			throw new InvalidArgumentException( 'Invalid $users param given.' );
		}
		# Avoid having to censor data
		# Note: if no edits pending, $worstLagTS is the cur time just before we checked
		# for the worst lag. Thus, new edits *right* after the check are properly excluded.
		$maxTSUnix = (int)wfTimestamp( TS_UNIX, $worstLagTS ) - 1; // all edits later reviewed
		$dbMaxTS = $dbr->timestamp( $maxTSUnix );
		# Use a one week time range
		$days = 7;
		$minTSUnix = $maxTSUnix - $days * 86400;
		# Approximate the number rows to scan
		$rows = self::estimateRevisionRowCount( $dbr, $maxTSUnix, 7, $userCondition );
		# If the range doesn't have many rows (like on small wikis), use 30 days
		if ( $rows < 500 ) {
			$rows = self::estimateRevisionRowCount( $dbr, $maxTSUnix, 30, $userCondition );
			# If the range doesn't have many rows (like on really tiny wikis), use 90 days
			if ( $rows < 500 ) {
				$days = 90;
				$minTSUnix = $maxTSUnix - $days * 86400;
			}
		}

		$sampleSize = 1500; // sample size
		# Sanity check the starting timestamp
		$minTSUnix = max( $minTSUnix, $installedUnix );
		$dbMinTS = $dbr->timestamp( $minTSUnix );
		# Get timestamp boundaries
		$timeCondition = [
			$dbr->expr( 'rev_timestamp', '>=', $dbMinTS ),
			$dbr->expr( 'rev_timestamp', '<=', $dbMaxTS ),
		];
		# Get mod for edit spread
		$fname = __METHOD__;
		$edits = $cache->getWithSetCallback(
			$cache->makeKey( 'flaggedrevs', 'rcEditCount', $users, $days ),
			$cache::TTL_WEEK * 2,
			static function () use ( $dbr, $fname, $userCondition, $timeCondition ) {
				return (int)$dbr->newSelectQueryBuilder()
					->select( 'COUNT(*)' )
					->from( 'revision' )
					->join( 'page', null, 'page_id = rev_page' )
					->join( 'actor', null, 'rev_actor = actor_id' )
					->where( $timeCondition )
					->andWhere( [
						$userCondition,
						'page_namespace' => FlaggedRevs::getReviewNamespaces()
					] )
					->caller( $fname )
					->fetchField();
			}
		);
		$mod = max( floor( $edits / $sampleSize ), 1 ); # $mod >= 1
		# For edits that started off pending, how long do they take to get reviewed?
		# Edits started off pending if made when a flagged rev of the page already existed.
		# Get the *first* reviewed rev *after* each edit and get the time difference.
		$res = $dbr->newSelectQueryBuilder()
			->select( [
				'rt' => 'rev_timestamp', // time revision was made
				'nft' => new Subquery( $dbr->newSelectQueryBuilder()
					->select( 'MIN(fr_timestamp)' )
					->from( 'flaggedrevs' )
					->where( [
						'fr_page_id = rev_page',
						'fr_rev_timestamp >= rev_timestamp'
					] )
					->caller( __METHOD__ )
					->getSQL()
				) // time when revision was first reviewed
			] )
			->from( 'revision' )
			->join( 'actor', null, 'rev_actor = actor_id' )
			->where( [
				$userCondition,
				"(rev_id % $mod) = 0",
				$dbr->expr( 'rev_parent_id', '>', 0 ), // optimize (exclude new pages)
				'EXISTS (' . $dbr->newSelectQueryBuilder()
					->select( '*' )
					->from( 'flaggedrevs' )
					->where( [ // page was reviewed when this revision was made
						'fr_page_id = rev_page',
						'fr_rev_timestamp < rev_timestamp', // before this revision
						'fr_rev_id < rev_id', // not imported later
						'fr_timestamp < rev_timestamp', // page reviewed before revision
					] )
					->caller( __METHOD__ )
					->getSQL() .
				')'
			] )
			->andWhere( $timeCondition )
			->caller( __METHOD__ )
			->fetchResultSet();

		$secondsR = 0; // total wait seconds for edits later reviewed
		$secondsP = 0; // total wait seconds for edits still pending
		$times = [];
		if ( $res->numRows() ) {
			# Get the elapsed times revs were pending (flagged time - edit time)
			foreach ( $res as $row ) {
				$time = (int)wfTimestamp( TS_UNIX, $row->nft ) - (int)wfTimestamp( TS_UNIX, $row->rt );
				$time = max( $time, 0 ); // sanity
				$secondsR += $time;
				$times[] = $time;
			}
			$sampleSize = count( $times );
			$aveRT = ( $secondsR + $secondsP ) / $sampleSize; // sample mean
			sort( $times ); // order smallest -> largest
			// Sample median
			$rank = intval( round( count( $times ) / 2 + 0.5 ) - 1 );
			$medianRT = $times[$rank];
			// Make percentile tabulation data
			$doPercentiles = [ 35, 45, 55, 65, 75, 85, 90, 95 ];
			foreach ( $doPercentiles as $percentile ) {
				$rank = intval( round( $percentile * count( $times ) / 100 + 0.5 ) - 1 );
				$rPerTable[$percentile] = $times[$rank];
			}
			$result['average']       = $aveRT;
			$result['median']        = $medianRT;
			$result['percTable']     = $rPerTable;
			$result['sampleSize']    = count( $times );
			$result['sampleStartTS'] = $minTSUnix;
			$result['sampleEndTS']   = $maxTSUnix;
		}

		return $result;
	}

	/**
	 * Convenience function to estimate the number of revisions authored by a given user type
	 * within a given timeframe.
	 *
	 * @param IReadableDatabase $dbr Replica DB connection handle.
	 * @param int $maxTSUnix Only consider revisions created before this UNIX timestamp.
	 * @param int $days Only consider revisions created at most this many days before $maxTSUnix.
	 * @param IExpression|string $userCondition SQL condition to filter revisions by user type.
	 *
	 * @return int The estimated number of revisions matching the given time and user constraints.
	 */
	private static function estimateRevisionRowCount(
		IReadableDatabase $dbr,
		int $maxTSUnix,
		int $days,
		$userCondition
	): int {
		$minTSUnix = $maxTSUnix - $days * 86400;
		$dbMinTS = $dbr->timestamp( $minTSUnix );
		$dbMaxTS = $dbr->timestamp( $maxTSUnix );

		return $dbr->newSelectQueryBuilder()
			->select( '1' )
			->from( 'revision' )
			->join( 'actor', null, 'rev_actor = actor_id' )
			->where( $userCondition )
			->andWhere( [
				$dbr->expr( 'rev_timestamp', '>=', $dbMinTS ),
				$dbr->expr( 'rev_timestamp', '<=', $dbMaxTS ),
			] )
			->caller( __METHOD__ )
			->estimateRowCount();
	}
}
