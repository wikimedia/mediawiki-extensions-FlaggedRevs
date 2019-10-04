<?php

/**
 * Created on June 13, 2011
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
 * API module to set the "currently reviewing" status of revisions
 *
 * @ingroup FlaggedRevs
 */
class ApiReviewActivity extends ApiBase {
	/**
	 * This function does essentially the same as RevisionReview::AjaxReview,
	 * except that it generates the template and image parameters itself.
	 */
	public function execute() {
		$user = $this->getUser();
		$params = $this->extractRequestParams();
		// Check basic permissions
		$this->checkUserRightsAny( 'review' );

		$newRev = Revision::newFromId( $params['oldid'] );
		if ( !$newRev || !$newRev->getTitle() ) {
			$this->dieWithError( [ 'apierror-nosuchrevid', $params['oldid'] ], 'notarget' );
		}
		$title = $newRev->getTitle();

		if ( $this->getPermissionManager()->isBlockedFrom( $user, $title, false ) ) {
			$this->dieBlocked( $user->getBlock() );
		}

		$fa = FlaggableWikiPage::getTitleInstance( $title );
		if ( !$fa->isReviewable() ) {
			$this->dieWithError( 'apierror-flaggedrevs-notreviewable', 'notreviewable' );
		}

		if ( $params['previd'] ) { // changes
			$oldRev = Revision::newFromId( $params['previd'] );
			if ( !$oldRev || $oldRev->getPage() != $newRev->getPage() ) {
				$this->dieWithError( 'apierror-flaggedrevs-notsamepage', 'notarget' );
			}
			// Mark as reviewing...
			if ( $params['reviewing'] ) {
				$status = FRUserActivity::setUserReviewingDiff(
					$user, $params['previd'], $params['oldid'] );
			// Unmark as reviewing...
			} else {
				$status = FRUserActivity::clearUserReviewingDiff(
					$user, $params['previd'], $params['oldid'] );
			}
		} else {
			// Mark as reviewing...
			if ( $params['reviewing'] ) {
				$status = FRUserActivity::setUserReviewingPage( $user, $newRev->getPage() );
			// Unmark as reviewing...
			} else {
				$status = FRUserActivity::clearUserReviewingPage( $user, $newRev->getPage() );
			}
		}

		# Success in setting flag...
		if ( $status === true ) {
			$this->getResult()->addValue(
				null, $this->getModuleName(), [ 'result' => 'Success' ] );
		# Failure...
		} else {
			$this->getResult()->addValue(
				null, $this->getModuleName(), [ 'result' => 'Failure' ] );
		}
	}

	public function mustBePosted() {
		return true;
	}

	public function isWriteMode() {
			return true;
	}

	public function getAllowedParams() {
		return [
			'previd'   	=> null,
			'oldid' 	=> null,
			'reviewing' => [ ApiBase::PARAM_TYPE => [ '0', '1' ] ],
			'token' 	=> null,
		];
	}

	public function needsToken() {
		return 'csrf';
	}

	public function getTokenSalt() {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=reviewactivity&previd=12345&reviewing=1'
				=> 'apihelp-reviewactivity-example-1',
		];
	}
}
