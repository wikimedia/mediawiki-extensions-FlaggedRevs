<?php

/**
 * Created on Dec 20, 2008
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
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module to review revisions
 *
 * @ingroup FlaggedRevs
 */
class ApiReview extends ApiBase {

	/**
	 * The method checks basic permissions of the user to interact
	 * with the page. Then it submits the form of the revision on
	 * approve or unapprove action. It also generates the template
	 * parameters itself.
	 */
	public function execute() {
		$params = $this->extractRequestParams();
		// Check basic permissions
		$this->checkUserRightsAny( 'review' );

		// Get target rev and title
		$revid = (int)$params['revid'];
		$revRecord = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionById( $revid );
		if ( !$revRecord ) {
			$this->dieWithError( [ 'apierror-nosuchrevid', $revid ], 'notarget' );
		}

		$linkTarget = $revRecord->getPageAsLinkTarget();
		if ( $this->getPermissionManager()->isBlockedFrom( $this->getUser(), $linkTarget, false ) ) {
			// @phan-suppress-next-line PhanTypeMismatchArgumentNullable Guaranteed via isBlockedFrom() above
			$this->dieBlocked( $this->getUser()->getBlock() );
		}

		$title = Title::newFromLinkTarget( $linkTarget );

		// Construct submit form...
		$form = new RevisionReviewForm( $this->getUser() );
		$form->setTitle( $title );
		$form->setOldId( $revid );
		$form->setAction( $params['unapprove'] ?
			RevisionReviewForm::ACTION_UNAPPROVE :
			RevisionReviewForm::ACTION_APPROVE );
		if ( isset( $params['comment'] ) ) {
			$form->setComment( $params['comment'] );
		}
		// The flagging parameter has the form 'flag_$name' ($name varies
		// by wiki). Extract it and put the value into $form->tag.
		$form->setTag( FlaggedRevs::binaryFlagging() ?
			1 :
			(int)$params['flag_' . FlaggedRevs::getTagName() ]
		);
		if ( $form->getAction() === RevisionReviewForm::ACTION_APPROVE ) {
			$form->bypassValidationKey(); // always OK; uses current templates
		}

		$form->ready(); // all params set

		# Try to do the actual review
		$status = $form->submit();
		# Approve/de-approve success
		if ( $status === true ) {
			$this->getResult()->addValue(
				null, $this->getModuleName(), [ 'result' => 'Success' ] );
		# Generic failures
		} elseif ( $status === 'review_page_notexists' ) {
			$this->dieWithError( 'apierror-flaggedrevs-pagedoesnotexist', 'notarget' );
		} elseif ( $status === 'review_page_unreviewable' ) {
			$this->dieWithError( 'apierror-flaggedrevs-notreviewable', 'notreviewable' );
		# Approve-specific failures
		} elseif ( $form->getAction() === RevisionReviewForm::ACTION_APPROVE ) {
			if ( $status === 'review_denied' ) {
				$this->dieWithError( 'apierror-flaggedrevs-cantreview', 'permissiondenied' );
			} elseif ( $status === 'review_too_low' ) {
				$this->dieWithError( 'apierror-flaggedrevs-toolow', 'mixedapproval' );
			} elseif ( $status === 'review_bad_key' ) {
				$this->dieWithError( 'apierror-flaggedrevs-cantreview', 'permissiondenied' );
			} elseif ( $status === 'review_bad_tags' ) {
				$this->dieWithError( 'apierror-flaggedrevs-badflags', 'invalidtags' );
			} elseif ( $status === 'review_bad_oldid' ) {
				$this->dieWithError( [ 'apierror-nosuchrevid', $revid ], 'notarget' );
			} else {
				// FIXME: review_param_missing? better msg?
				$this->dieWithError( [ 'apierror-unknownerror-nocode' ], 'unknownerror' );
			}
		# De-approve specific failure
		} elseif ( $form->getAction() === RevisionReviewForm::ACTION_UNAPPROVE ) {
			if ( $status === 'review_denied' ) {
				$this->dieWithError( 'apierror-flaggedrevs-cantunreview', 'permissiondenied' );
			} elseif ( $status === 'review_not_flagged' ) {
				$this->dieWithError( 'apierror-flaggedrevs-noflaggedrev', 'notarget' );
			} else {
				// FIXME: review_param_missing? better msg?
				$this->dieWithError( [ 'apierror-unknownerror-nocode' ], 'unknownerror' );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function isWriteMode() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function getAllowedParams() {
		$pars = [
			'revid' => null,
			'comment' => null,
			'unapprove' => false
		];
		if ( !FlaggedRevs::binaryFlagging() && !FlaggedRevs::useOnlyIfProtected() ) {
			$strLevels = array_map( 'strval', range( 0, FlaggedRevs::getMaxLevel() ) );
			$pars['flag_' . FlaggedRevs::getTagName()] = [
				ParamValidator::PARAM_DEFAULT => '1', // default
				ParamValidator::PARAM_TYPE => $strLevels, // array of allowed values
				ApiBase::PARAM_HELP_MSG => [ 'apihelp-review-param-flag', FlaggedRevs::getTagName() ],
			];
		}
		return $pars;
	}

	/**
	 * @return string
	 */
	public function needsToken() {
		return 'csrf';
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=review&revid=12345&token=123AB&flag_accuracy=1&comment=Ok'
				=> 'apihelp-review-example-1',
		];
	}
}
