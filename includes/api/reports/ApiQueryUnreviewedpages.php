<?php

/**
 * Created on June 29, 2009
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
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\IntegerDef;

/**
 * Query module to list pages unreviewed pages
 *
 * @ingroup FlaggedRevs
 */
class ApiQueryUnreviewedpages extends ApiQueryGeneratorBase {

	/**
	 * @param ApiQuery $query
	 * @param string $moduleName
	 */
	public function __construct( ApiQuery $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'ur' );
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
		$this->addTables( [ 'page', 'flaggedpages' ] );
		$this->addWhereFld( 'page_namespace', $params['namespace'] );
		if ( $params['filterredir'] == 'redirects' ) {
			$this->addWhereFld( 'page_is_redirect', 1 );
		}
		if ( $params['filterredir'] == 'nonredirects' ) {
			$this->addWhereFld( 'page_is_redirect', 0 );
		}

		$dir = ( $params['dir'] == 'descending' ? 'older' : 'newer' );
		$this->addWhereRange(
			'page_title',
			$dir,
			$params['start'],
			$params['end']
		);
		$this->addJoinConds(
			[ 'flaggedpages' => [ 'LEFT JOIN', 'fp_page_id=page_id' ] ]
		);
		$this->addWhere( $this->getDB()->expr( 'fp_page_id', '=', null )
			->or( 'fp_quality', '<', intval( $params['filterlevel'] ) ) );

		$this->addOption(
			'USE INDEX',
			[ 'page' => 'page_name_title', 'flaggedpages' => 'PRIMARY' ]
		);

		if ( $resultPageSet === null ) {
			$this->addFields( [
				'page_id',
				'page_namespace',
				'page_title',
				'page_len',
				'page_latest',
			] );
		} else {
			$this->addFields( $resultPageSet->getPageTableFields() );
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
				$this->setContinueEnumParameter( 'start', $row->page_title );
				break;
			}

			if ( $resultPageSet === null ) {
				$title = Title::newFromRow( $row );
				$data[] = [
					'pageid'        => intval( $row->page_id ),
					'ns'            => $title->getNamespace(),
					'title'         => $title->getPrefixedText(),
					'revid'         => intval( $row->page_latest ),
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
		return 'public';
	}

	/**
	 * @inheritDoc
	 */
	protected function getAllowedParams() {
		return [
			'start' => [
				ParamValidator::PARAM_TYPE => 'string'
			],
			'end' => [
				ParamValidator::PARAM_TYPE => 'string'
			],
			'dir' => [
				ParamValidator::PARAM_DEFAULT => 'ascending',
				ParamValidator::PARAM_TYPE => [ 'ascending', 'descending' ],
			],
			'namespace' => [
				ParamValidator::PARAM_DEFAULT => FlaggedRevs::getFirstReviewNamespace(),
				ParamValidator::PARAM_TYPE => 'namespace',
				ParamValidator::PARAM_ISMULTI => true,
			],
			'filterredir' => [
				ParamValidator::PARAM_DEFAULT => 'all',
				ParamValidator::PARAM_TYPE => [
					'redirects',
					'nonredirects',
					'all'
				]
			],
			'filterlevel' => [
				ParamValidator::PARAM_DEFAULT => 0,
				ParamValidator::PARAM_TYPE => 'integer',
				IntegerDef::PARAM_MIN  => 0,
				IntegerDef::PARAM_MAX  => 2,
			],
			'limit' => [
				ParamValidator::PARAM_DEFAULT => 10,
				ParamValidator::PARAM_TYPE => 'limit',
				IntegerDef::PARAM_MIN => 1,
				IntegerDef::PARAM_MAX => ApiBase::LIMIT_BIG1,
				IntegerDef::PARAM_MAX2 => ApiBase::LIMIT_BIG2
			]
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=query&list=unreviewedpages&urnamespace=0&urfilterlevel=0'
				=> 'apihelp-query+unreviewedpages-example-1',
			'action=query&generator=unreviewedpages&urnamespace=0&gurlimit=4&prop=info'
				=> 'apihelp-query+unreviewedpages-example-2',
		];
	}
}
