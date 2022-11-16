<?php
// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace Mediawiki\Extension\FlaggedRevs\Backend;

use MediaWiki\Hook\ContribsPager__getQueryInfoHook;
use MediaWiki\Hook\SpecialContributions__getForm__filtersHook;

class FlaggedRevsContributionsHooks implements
	SpecialContributions__getForm__filtersHook,
	ContribsPager__getQueryInfoHook
{

	/**
	 * @inheritDoc
	 */
	public function onSpecialContributions__getForm__filters( $sp, &$filters ) {
		$filters[] = [
			'type' => 'check',
			'label-message' => 'flaggedrevs-contributions-filters-unreviewed-only',
			'name' => 'flaggedrevs-only-pending',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function onContribsPager__getQueryInfo( $pager, &$queryInfo ) {
		if ( $pager->getConfig()->get( 'FlaggedRevsProtection' ) ) {
			return;
		}

		if ( $pager->getContext()->getRequest()->getBool( 'flaggedrevs-only-pending' ) ) {
			// only add the flaggedpages table if not already done by
			// FlaggedRevsUIHooks::addToContribsQuery
			if ( !in_array( 'flaggedrevs', $queryInfo['tables'] ) ) {
				$queryInfo['tables'][] = 'flaggedpages';
				$queryInfo['join_conds']['flaggedpages'] = [ 'LEFT JOIN', "fp_page_id = rev_page" ];
			}

			// filter down to pending changes only
			$queryInfo['conds'][] = '(fp_stable < rev_id AND fp_pending_since IS NOT NULL)' .
				' OR (fp_stable IS NULL)';
		}
	}
}
