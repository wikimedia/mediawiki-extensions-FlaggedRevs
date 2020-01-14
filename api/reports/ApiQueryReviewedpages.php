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

/**
 * Query module to list pages reviewed pages
 *
 * @ingroup FlaggedRevs
 */
class ApiQueryReviewedpages extends ApiQueryGeneratorBase {

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'rp' );
	}

	public function execute() {
		$this->run();
	}

	public function executeGenerator( $resultPageSet ) {
		$this->run( $resultPageSet );
	}

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
		if ( $params['filterlevel'] !== null ) {
			$this->addWhereFld( 'fp_quality', $params['filterlevel'] );
		}
		$this->addWhereRange(
			'fp_page_id',
			$params['dir'],
			$params['start'],
			$params['end']
		);
		$this->addWhere( 'page_id=fp_page_id' );

		if ( $resultPageSet === null ) {
			$this->addFields( [
				'page_id',
				'page_namespace',
				'page_title',
				'page_len',
				'page_latest',
				'fp_page_id',
				'fp_quality',
				'fp_stable'
			] );
		} else {
			$this->addFields( $resultPageSet->getPageTableFields() );
			$this->addFields( 'fp_page_id' );
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
				$this->setContinueEnumParameter( 'start', $row->fp_page_id );
				break;
			}

			if ( $resultPageSet === null ) {
				$title = Title::newFromRow( $row );
				$data[] = [
					'pageid' 		=> intval( $row->page_id ),
					'ns' 			=> intval( $title->getNamespace() ),
					'title' 		=> $title->getPrefixedText(),
					'revid' 		=> intval( $row->page_latest ),
					'stable_revid' 	=> intval( $row->fp_stable ),
					'flagged_level' => intval( $row->fp_quality ),
					'flagged_level_text' => FlaggedRevs::getQualityLevelText( $row->fp_quality )
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

	public function getCacheMode( $params ) {
		return 'public';
	}

	public function getAllowedParams() {
		$namespaces = FlaggedRevs::getReviewNamespaces();
		return [
			'start' => [
				ApiBase::PARAM_TYPE => 'integer'
			],
			'end' => [
				ApiBase::PARAM_TYPE => 'integer'
			],
			'dir' => [
				ApiBase::PARAM_DFLT => 'newer',
				ApiBase::PARAM_TYPE => [
					'newer',
					'older'
				],
				/** @todo Once support for MediaWiki < 1.25 is dropped,
				 * just use ApiBase::PARAM_HELP_MSG directly
				 */
				constant( 'ApiBase::PARAM_HELP_MSG' ) ?: '' => 'api-help-param-direction',
			],
			'namespace' => [
				ApiBase::PARAM_DFLT => !$namespaces ?
					NS_MAIN : $namespaces[0],
				ApiBase::PARAM_TYPE => 'namespace',
				ApiBase::PARAM_ISMULTI => true,
			],
			'filterredir' => [
				ApiBase::PARAM_DFLT => 'all',
				ApiBase::PARAM_TYPE => [
					'redirects',
					'nonredirects',
					'all'
				]
			],
			'filterlevel' => [
				ApiBase::PARAM_DFLT => null,
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_MIN  => 0,
				ApiBase::PARAM_MAX  => 2,
			],
			'limit' => [
				ApiBase::PARAM_DFLT => 10,
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_MIN  => 1,
				ApiBase::PARAM_MAX  => ApiBase::LIMIT_BIG1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_BIG2
			]
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=query&list=reviewedpages&rpnamespace=0&rpfilterlevel=0'
				=> 'apihelp-query+reviewedpages-example-1',
			'action=query&generator=reviewedpages&grplimit=4&prop=info'
				=> 'apihelp-query+reviewedpages-example-2',
		];
	}
}
