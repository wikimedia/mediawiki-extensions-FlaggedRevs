<?php
// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
// phpcs:disable MediaWiki.Commenting.FunctionComment.MissingDocumentationPublic

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiQueryRevisions;
use MediaWiki\Api\Hook\APIGetAllowedParamsHook;
use MediaWiki\Api\Hook\APIQueryAfterExecuteHook;
use MediaWiki\MediaWikiServices;

class FlaggedRevsApiHooks implements
	APIGetAllowedParamsHook,
	APIQueryAfterExecuteHook
{

	/**
	 * @inheritDoc
	 */
	public function onAPIGetAllowedParams( $module, &$params, $flags ) {
		if ( !$module instanceof ApiQueryRevisions ) {
			return;
		}
		$params['prop'][ApiBase::PARAM_TYPE][] = 'flagged';
	}

	/**
	 * @inheritDoc
	 */
	public function onAPIQueryAfterExecute( $module ) {
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
		$data = (array)$result->getResultData( [ 'query', 'pages' ], [ 'Strip' => 'all' ] );
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
		$db = MediaWikiServices::getInstance()->getConnectionProvider()
			->getReplicaDatabase( false, 'api' );

		$qb = $db->newSelectQueryBuilder()
			->select( [
				'fr_page_id',
				'fr_rev_id',
				'fr_timestamp',
				'fr_tags',
				'user_name'
			] )
			->from( 'flaggedrevs' )
			->join( 'user', null, 'fr_user=user_id' );

		$where = [];
		// Construct WHERE-clause to avoid multiplying the number of scanned rows
		// as flaggedrevs table has composite primary key (fr_page_id,fr_rev_id)
		foreach ( $pageids as $pageid => $revids ) {
			$where[] = $db->andExpr( [
				'fr_page_id' => $pageid,
				'fr_rev_id' => array_keys( $revids ),
			] );
		}
		$qb->where( $db->orExpr( $where ) );

		$res = $qb->caller( __METHOD__ )->fetchResultSet();

		// Add flagging data to result array
		foreach ( $res as $row ) {
			$index = $pageids[$row->fr_page_id][$row->fr_rev_id];
			$tags = FlaggedRevision::expandRevisionTags( $row->fr_tags );
			$data = [
				'user' 			=> $row->user_name,
				'timestamp' 	=> wfTimestamp( TS_ISO_8601, $row->fr_timestamp ),
				'level' 		=> 0,
				'level_text' 	=> 'stable',
				'tags' => array_merge( FlaggedRevision::getDefaultTags(), $tags ),
			];
			$result->addValue(
				[ 'query', 'pages', $row->fr_page_id, 'revisions', $index ],
				'flagged',
				$data
			);
		}
	}
}
