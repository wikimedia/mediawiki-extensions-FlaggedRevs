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

use MediaWiki\MediaWikiServices;

/**
 * API module to review revisions
 *
 * @ingroup FlaggedRevs
 */
class ApiReview extends ApiBase {

	/**
	 * This function does essentially the same as RevisionReview::AjaxReview,
	 * except that it generates the template and image parameters itself.
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
			$this->dieBlocked( $this->getUser()->getBlock() );
		}

		$title = Title::newFromLinkTarget( $linkTarget );

		// Construct submit form...
		$form = new RevisionReviewForm( $this->getUser() );
		$form->setPage( $title );
		$form->setOldId( $revid );
		$form->setApprove( empty( $params['unapprove'] ) );
		$form->setUnapprove( !empty( $params['unapprove'] ) );
		if ( isset( $params['comment'] ) ) {
			$form->setComment( $params['comment'] );
		}
		// The flagging parameters have the form 'flag_$name'.
		// Extract them and put the values into $form->dims
		foreach ( FlaggedRevs::getTags() as $tag ) {
			if ( FlaggedRevs::binaryFlagging() ) {
				$form->setDim( $tag, 1 );
			} else {
				$form->setDim( $tag, (int)$params['flag_' . $tag] );
			}
		}
		if ( $form->getAction() === 'approve' ) {
			$article = new FlaggableWikiPage( $title );
			// Get the file version used for File: pages
			$file = $article->getFile();
			if ( $file ) {
				$fileVer = [ 'time' => $file->getTimestamp(), 'sha1' => $file->getSha1() ];
			} else {
				$fileVer = null;
			}
			// Now get the template and image parameters needed
			if ( FlaggedRevs::inclusionSetting() === FR_INCLUDES_CURRENT ) {
				$templateIds = []; // unused
				$fileTimeKeys = []; // unused
			} else {
				list( $templateIds, $fileTimeKeys ) =
					FRInclusionCache::getRevIncludes( $article, $revRecord, $this->getUser() );
			}
			// Get version parameters for review submission (flat strings)
			list( $templateParams, $imageParams, $fileParam ) =
				RevisionReviewForm::getIncludeParams( $templateIds, $fileTimeKeys, $fileVer );
			// Set the version parameters...
			$form->setTemplateParams( $templateParams );
			$form->setFileParams( $imageParams );
			$form->setFileVersion( $fileParam );
			$form->bypassValidationKey(); // always OK; uses current templates/files
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
		} elseif ( $form->getAction() === 'approve' ) {
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
		} elseif ( $form->getAction() === 'unapprove' ) {
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
			'revid'   	=> null,
			'token'   	=> null,
			'comment' 	=> null,
			'unapprove' => false
		];
		if ( !FlaggedRevs::binaryFlagging() ) {
			foreach ( FlaggedRevs::getDimensions() as $flagname => $levels ) {
				$strLevels = array_map( 'strval', array_keys( $levels ) );
				$pars['flag_' . $flagname] = [
					ApiBase::PARAM_DFLT => '1', // default
					ApiBase::PARAM_TYPE => $strLevels, // array of allowed values
					ApiBase::PARAM_HELP_MSG => [ 'apihelp-review-param-flag', $flagname ],
				];
			}
		}
		return $pars;
	}

	/**
	 * @inheritDoc
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
