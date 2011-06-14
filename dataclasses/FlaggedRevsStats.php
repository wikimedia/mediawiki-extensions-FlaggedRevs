<?php
/**
 * FlaggedRevs stats functions
 */
class FlaggedRevsStats {
	/*
	 * Get latest FR-related stats
	 * @return Array of current FR stats
	 */
	public static function getLatestStats() {
		$dbr = wfGetDB( DB_SLAVE );
		$dbTs = $dbr->selectField( 'flaggedrevs_statistics', 'MAX(frs_timestamp)' );
		$res = $dbr->select( 'flaggedrevs_statistics',
			array( 'frs_stat_key', 'frs_stat_val' ),
			array( 'frs_timestamp' => $dbTs ),
			__METHOD__
		);

		$data = array();
		$data['reviewLag-sampleSize'] = '-';
		$data['reviewLag-average'] = '-';
		$data['reviewLag-median'] = '-';
		$data['reviewLag-percentiles'] = array();
		$data['totalPages-NS'] = array();
		$data['reviewedPages-NS'] = array();
		$data['syncedPages-NS'] = array();
		$data['pendingLag-average'] = '-';
		$data['statTimestamp'] = wfTimestamp( TS_MW, $dbTs );

		foreach( $res as $row ) {
			$key = explode( ':', $row->frs_stat_key );
			switch ( $key[0] ) {
				case 'reviewLag-sampleSize':
				case 'reviewLag-average':
				case 'reviewLag-median':
				case 'pendingLag-average':
					$data[$key[0]] = (int)$row->frs_stat_val;
					break;
				case 'reviewLag-percentile': // <stat name,percentile)
					$data[$key[0]][$key[1]] = (int)$row->frs_stat_val;
					break;
				case 'totalPages-NS': // <stat name,namespace)
				case 'reviewedPages-NS': // <stat name,namespace)
				case 'syncedPages-NS': // <stat name,namespace)
					$data[$key[0]][$key[1]] = (int)$row->frs_stat_val;
					break;
			}
		}
		return $data;
	}
}
