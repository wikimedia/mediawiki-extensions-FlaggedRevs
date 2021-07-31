<?php

abstract class FlaggedRevsApiHooks extends ApiQueryBase {

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/APIGetAllowedParams
	 *
	 * @param ApiBase $module
	 * @param array[] &$params
	 */
	public static function addApiRevisionParams( $module, &$params ) {
		if ( !$module instanceof ApiQueryRevisions ) {
			return;
		}
		$params['prop'][ApiBase::PARAM_TYPE][] = 'flagged';
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/APIQueryAfterExecute
	 *
	 * @param ApiBase $module
	 */
	public static function addApiRevisionData( $module ) {
		if ( !$module instanceof ApiQueryRevisions ) {
			return;
		}
		$params = $module->extractRequestParams( false );
		if ( !in_array( 'flagged', $params['prop'] ?? [] ) ) {
			return;
		}
		if ( !in_array( 'ids', $params['prop'] ) ) {
			$module->dieWithError(
				[ 'apierror-invalidparammix-mustusewith', 'rvprop=flagged', 'rvprop=ids' ], 'missingparam'
			);
		}
		// Get all requested pageids/revids in a mapping:
		// pageid => revid => array_index of the revision
		// we will need this later to add data to the result array
		$result = $module->getResult();
		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$data = (array)$result->getResultData( [ 'query', 'pages' ], [ 'Strip' => 'all' ] );
		} else {
			// @phan-suppress-next-line PhanUndeclaredMethod
			$data = $result->getData();
			if ( !isset( $data['query']['pages'] ) ) {
				return;
			}
			$data = $data['query']['pages'];
		}
		$pageids = [];
		foreach ( $data as $pageid => $page ) {
			if ( is_array( $page ) && array_key_exists( 'revisions', $page ) ) {
				foreach ( $page['revisions'] as $index => $rev ) {
					if ( is_array( $rev ) && array_key_exists( 'revid', $rev ) ) {
						$pageids[$pageid][$rev['revid']] = $index;
					}
				}
			}
		}
		if ( $pageids === [] ) {
			return;
		}

		// Construct SQL Query
		$db = $module->getDB();
		$module->resetQueryParams();
		$module->addTables( [ 'flaggedrevs', 'user' ] );
		$module->addFields( [
			'fr_page_id',
			'fr_rev_id',
			'fr_timestamp',
			'fr_tags',
			'user_name'
		] );
		$module->addWhere( 'fr_user=user_id' );

		$where = [];
		// Construct WHERE-clause to avoid multiplying the number of scanned rows
		// as flaggedrevs table has composite primary key (fr_page_id,fr_rev_id)
		foreach ( $pageids as $pageid => $revids ) {
			$where[] = $db->makeList( [ 'fr_page_id' => $pageid,
				'fr_rev_id' => array_keys( $revids ) ], LIST_AND );
		}
		$module->addWhere( $db->makeList( $where, LIST_OR ) );

		$res = $module->select( __METHOD__ );

		// Add flagging data to result array
		foreach ( $res as $row ) {
			$index = $pageids[$row->fr_page_id][$row->fr_rev_id];
			$data = [
				'user' 			=> $row->user_name,
				'timestamp' 	=> wfTimestamp( TS_ISO_8601, $row->fr_timestamp ),
				'level' 		=> 0,
				'level_text' 	=> 'stable',
				'tags' 			=> FlaggedRevision::expandRevisionTags( $row->fr_tags )
			];
			$result->addValue(
				[ 'query', 'pages', $row->fr_page_id, 'revisions', $index ],
				'flagged',
				$data
			);
		}
	}
}
