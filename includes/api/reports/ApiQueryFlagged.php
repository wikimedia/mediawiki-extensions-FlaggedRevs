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

use MediaWiki\Api\ApiQueryBase;
use MediaWiki\MediaWikiServices;

/**
 * Query module to get flagging information about pages via 'prop=flagged'
 *
 * @ingroup FlaggedRevs
 */
class ApiQueryFlagged extends ApiQueryBase {

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$pageSet = $this->getPageSet();
		$pageids = array_keys( $pageSet->getGoodPages() );
		if ( !$pageids ) {
			return;
		}

		// Construct SQL Query
		$this->addTables( 'flaggedpages' );
		$this->addFields( [
			'fp_page_id', 'fp_stable', 'fp_quality', 'fp_pending_since'
		] );
		$this->addWhereFld( 'fp_page_id', $pageids );
		$res = $this->select( __METHOD__ );

		$result = $this->getResult();
		foreach ( $res as $row ) {
			$data = [
				'stable_revid' 	=> intval( $row->fp_stable ),
				'level' 		=> intval( $row->fp_quality ),
				'level_text' 	=> 'stable'
			];
			if ( $row->fp_pending_since ) {
				$data['pending_since'] = wfTimestamp( TS_ISO_8601, $row->fp_pending_since );
			}

			$result->addValue( [ 'query', 'pages', $row->fp_page_id ], 'flagged', $data );
		}

		$this->resetQueryParams();
		$this->addTables( 'flaggedpage_config' );
		$this->addFields( [ 'fpc_page_id', 'fpc_level', 'fpc_expiry' ] );
		$this->addWhereFld( 'fpc_page_id', $pageids );

		$contLang = MediaWikiServices::getInstance()->getContentLanguage();
		foreach ( $this->select( __METHOD__ ) as $row ) {
			$result->addValue(
				[ 'query', 'pages', $row->fpc_page_id, 'flagged' ],
				'protection_level',
				$row->fpc_level
			);
			$result->addValue(
				[ 'query', 'pages', $row->fpc_page_id, 'flagged' ],
				'protection_expiry',
				$contLang->formatExpiry( $row->fpc_expiry, TS_ISO_8601 )
			);
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
	protected function getExamplesMessages() {
		return [
			'action=query&prop=info|flagged&titles=Main%20Page'
				=> 'apihelp-query+flagged-example-1',
			'action=query&generator=allpages&gapfrom=K&prop=flagged'
				=> 'apihelp-query+flagged-example-2',
		];
	}
}
