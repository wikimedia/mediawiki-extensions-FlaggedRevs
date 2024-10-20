<?php

/**
 * Created on Sep 17, 2008
 *
 * API module for MediaWiki's FlaggedRevs extension
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiPageSet;
use MediaWiki\Api\ApiQuery;
use MediaWiki\Api\ApiQueryGeneratorBase;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentityUtils;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\IntegerDef;

/**
 * Query module to list pages with outdated review flag.
 *
 * @ingroup FlaggedRevs
 */
class ApiQueryOldreviewedpages extends ApiQueryGeneratorBase {
	private UserIdentityUtils $userIdentityUtils;

	/**
	 * @param ApiQuery $query
	 * @param string $moduleName
	 * @param UserIdentityUtils $userIdentityUtils
	 */
	public function __construct(
		ApiQuery $query,
		$moduleName,
		UserIdentityUtils $userIdentityUtils
	) {
		parent::__construct( $query, $moduleName, 'or' );
		$this->userIdentityUtils = $userIdentityUtils;
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$this->run();
	}

	/**
	 * @param ApiPageSet $resultPageSet
	 */
	public function executeGenerator( $resultPageSet ) {
		$this->run( $resultPageSet );
	}

	/**
	 * @param ApiPageSet|null $resultPageSet
	 */
	private function run( $resultPageSet = null ) {
		$params = $this->extractRequestParams();

		// Construct SQL Query
		$this->addTables( [ 'page', 'flaggedpages', 'revision' ] );
		$this->addWhereFld( 'page_namespace', $params['namespace'] );
		if ( $params['filterredir'] == 'redirects' ) {
			$this->addWhereFld( 'page_is_redirect', 1 );
		}
		if ( $params['filterredir'] == 'nonredirects' ) {
			$this->addWhereFld( 'page_is_redirect', 0 );
		}
		if ( $params['maxsize'] !== null ) {
			# Get absolute difference for comparison. ABS(x-y)
			# is broken due to mysql unsigned int design.
			$this->addWhere( 'GREATEST(page_len,rev_len)-LEAST(page_len,rev_len) <= ' .
				intval( $params['maxsize'] ) );
		}
		if ( $params['filterwatched'] == 'watched' ) {
			$performer = $this->getAuthority();
			if (
				!$performer->isRegistered() ||
				(
					$this->userIdentityUtils->isTemp( $performer->getUser() ) &&
					!$performer->isAllowed( 'viewmywatchlist' )
				)
			) {
				$this->dieWithError( 'watchlistanontext', 'notloggedin' );
			}

			$this->checkUserRightsAny( 'viewmywatchlist' );

			$this->addTables( 'watchlist' );
			$this->addWhereFld( 'wl_user', $performer->getUser()->getId() );
			$this->addWhere( 'page_namespace = wl_namespace' );
			$this->addWhere( 'page_title = wl_title' );
		}
		if ( $params['category'] != '' ) {
			$this->addTables( 'categorylinks' );
			$this->addWhere( 'cl_from = fp_page_id' );
			$this->addWhereFld( 'cl_to', $params['category'] );
		}

		$this->addWhereRange(
			'fp_pending_since',
			$params['dir'],
			$params['start'],
			$params['end']
		);
		$this->addWhere( 'page_id=fp_page_id' );
		$this->addWhere( 'rev_id=fp_stable' );
		if ( !isset( $params['start'] ) && !isset( $params['end'] ) ) {
			$this->addWhere( $this->getDB()->expr( 'fp_pending_since', '!=', null ) );
		}

		if ( $resultPageSet === null ) {
			$this->addFields( [
				'page_id',
				'page_namespace',
				'page_title',
				'page_latest',
				'page_len',
				'rev_len',
				'fp_stable',
				'fp_pending_since',
				'fp_quality'
			] );
		} else {
			$this->addFields( $resultPageSet->getPageTableFields() );
			$this->addFields( 'fp_pending_since' );
		}

		$limit = $params['limit'];
		$this->addOption( 'LIMIT', $limit + 1 );
		$res = $this->select( __METHOD__ );

		$data = [];
		$count = 0;
		foreach ( $res as $row ) {
			if ( ++$count > $limit ) {
				// We've reached the one extra which shows that there are
				// additional pages to be had. Stop here...
				$this->setContinueEnumParameter(
					'start',
					wfTimestamp( TS_ISO_8601, $row->fp_pending_since )
				);
				break;
			}

			if ( $resultPageSet === null ) {
				$title = Title::newFromRow( $row );
				$data[] = [
					'pageid' 			=> intval( $row->page_id ),
					'ns' 				=> intval( $row->page_namespace ),
					'title' 			=> $title->getPrefixedText(),
					'revid' 			=> intval( $row->page_latest ),
					'stable_revid' 		=> intval( $row->fp_stable ),
					'pending_since' 	=> wfTimestamp( TS_ISO_8601, $row->fp_pending_since ),
					'flagged_level' 	=> intval( $row->fp_quality ),
					'flagged_level_text' => 'stable',
					'diff_size' 		=> (int)$row->page_len - (int)$row->rev_len,
				];
			} else {
				$resultPageSet->processDbRow( $row );
			}
		}

		if ( $resultPageSet === null ) {
			$result = $this->getResult();
			$result->setIndexedTagName( $data, 'p' );
			$result->addValue( 'query', $this->getModuleName(), $data );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getCacheMode( $params ) {
		return $params['filterwatched'] === 'watched' ? 'private' : 'public';
	}

	/**
	 * @inheritDoc
	 */
	protected function getAllowedParams() {
		return [
			'start' => [
				ParamValidator::PARAM_TYPE => 'timestamp'
			],
			'end' => [
				ParamValidator::PARAM_TYPE => 'timestamp'
			],
			'dir' => [
				ParamValidator::PARAM_DEFAULT => 'newer',
				ParamValidator::PARAM_TYPE => [ 'newer', 'older' ],
				ApiBase::PARAM_HELP_MSG => 'api-help-param-direction',
			],
			'maxsize' => [
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_DEFAULT => null,
				IntegerDef::PARAM_MIN 	=> 0
			],
			'filterwatched' => [
				ParamValidator::PARAM_DEFAULT => 'all',
				ParamValidator::PARAM_TYPE => [ 'watched', 'all' ]
			],
			'namespace' => [
				ParamValidator::PARAM_DEFAULT => FlaggedRevs::getFirstReviewNamespace(),
				ParamValidator::PARAM_TYPE => 'namespace',
				ParamValidator::PARAM_ISMULTI => true,
			],
			'category' => [
				ParamValidator::PARAM_TYPE => 'string'
			],
			'filterredir' => [
				ParamValidator::PARAM_DEFAULT => 'all',
				ParamValidator::PARAM_TYPE => [ 'redirects', 'nonredirects', 'all' ]
			],
			'limit' => [
				ParamValidator::PARAM_DEFAULT => 10,
				ParamValidator::PARAM_TYPE => 'limit',
				IntegerDef::PARAM_MIN 	=> 1,
				IntegerDef::PARAM_MAX 	=> ApiBase::LIMIT_BIG1,
				IntegerDef::PARAM_MAX2 => ApiBase::LIMIT_BIG2
			]
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=query&list=oldreviewedpages&ornamespace=0'
				=> 'apihelp-query+oldreviewedpages-example-1',
			'action=query&generator=oldreviewedpages&gorlimit=4&prop=info'
				=> 'apihelp-query+oldreviewedpages-example-2',
		];
	}
}
