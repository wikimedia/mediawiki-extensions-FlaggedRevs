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
		if ( is_callable( [ $this, 'checkUserRightsAny' ] ) ) {
			$this->checkUserRightsAny( 'review' );
		} else {
			if ( !$this->getUser()->isAllowed( 'review' ) ) {
				$this->dieUsage( "You don't have the right to review revisions.",
					'permissiondenied' );
			}
		}

		if ( $this->getUser()->isBlocked( false ) ) {
			if ( is_callable( [ $this, 'dieBlocked' ] ) ) {
				$this->dieBlocked( $this->getUser()->getBlock() );
			} else {
				$this->dieUsageMsg( [ 'blockedtext' ] );
			}
		}

		// Get target rev and title
		$revid = (int)$params['revid'];
		$rev = Revision::newFromId( $revid );
		if ( !$rev ) {
			if ( is_callable( [ $this, 'dieWithError' ] ) ) {
				$this->dieWithError( [ 'apierror-nosuchrevid', $revid ], 'notarget' );
			} else {
				$this->dieUsage( "Cannot find a revision with the specified ID.", 'notarget' );
			}
		}
		$title = $rev->getTitle();

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
			list( $templateIds, $fileTimeKeys ) =
				FRInclusionCache::getRevIncludes( $article, $rev, $this->getUser() );
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
			if ( is_callable( [ $this, 'dieWithError' ] ) ) {
				$this->dieWithError( 'apierror-flaggedrevs-pagedoesnotexist', 'notarget' );
			} else {
				$this->dieUsage( "Provided page does not exist.", 'notarget' );
			}
		} elseif ( $status === 'review_page_unreviewable' ) {
			if ( is_callable( [ $this, 'dieWithError' ] ) ) {
				$this->dieWithError( 'apierror-flaggedrevs-notreviewable', 'notreviewable' );
			} else {
				$this->dieUsage( "Provided page is not reviewable.", 'notreviewable' );
			}
		# Approve-specific failures
		} elseif ( $form->getAction() === 'approve' ) {
			if ( $status === 'review_denied' ) {
				if ( is_callable( [ $this, 'dieWithError' ] ) ) {
					$this->dieWithError( 'apierror-flaggedrevs-cantreview', 'permissiondenied' );
				} else {
					$this->dieUsage(
						"You don't have the necessary rights to set the specified flags.",
						'permissiondenied'
					);
				}
			} elseif ( $status === 'review_too_low' ) {
				if ( is_callable( [ $this, 'dieWithError' ] ) ) {
					$this->dieWithError( 'apierror-flaggedrevs-toolow', 'mixedapproval' );
				} else {
					$this->dieUsage( "Either all or none of the flags have to be set to zero.",
						'mixedapproval' );
				}
			} elseif ( $status === 'review_bad_key' ) {
				if ( is_callable( [ $this, 'dieWithError' ] ) ) {
					$this->dieWithError( 'apierror-flaggedrevs-cantreview', 'permissiondenied' );
				} else {
					$this->dieUsage(
						"You don't have the necessary rights to set the specified flags.",
						'permissiondenied'
					);
				}
			} elseif ( $status === 'review_bad_tags' ) {
				if ( is_callable( [ $this, 'dieWithError' ] ) ) {
					$this->dieWithError( 'apierror-flaggedrevs-badflags', 'invalidtags' );
				} else {
					$this->dieUsage( "The specified flags are not valid.", 'invalidtags' );
				}
			} elseif ( $status === 'review_bad_oldid' ) {
				if ( is_callable( [ $this, 'dieWithError' ] ) ) {
					$this->dieWithError( [ 'apierror-nosuchrevid', $revid ], 'notarget' );
				} else {
					$this->dieUsage( "No revision with the specified ID.", 'notarget' );
				}
			} else {
				// FIXME: review_param_missing? better msg?
				if ( is_callable( [ $this, 'dieWithError' ] ) ) {
					$this->dieWithError( [ 'apierror-unknownerror-nocode' ], 'unknownerror' );
				} else {
					$this->dieUsageMsg( [ 'unknownerror', '' ] );
				}
			}
		# De-approve specific failure
		} elseif ( $form->getAction() === 'unapprove' ) {
			if ( $status === 'review_denied' ) {
				if ( is_callable( [ $this, 'dieWithError' ] ) ) {
					$this->dieWithError( 'apierror-flaggedrevs-cantunreview', 'permissiondenied' );
				} else {
					$this->dieUsage( "You don't have the necessary rights to remove the flags.",
						'permissiondenied' );
				}
			} elseif ( $status === 'review_not_flagged' ) {
				if ( is_callable( [ $this, 'dieWithError' ] ) ) {
					$this->dieWithError( 'apierror-flaggedrevs-noflaggedrev', 'notarget' );
				} else {
					$this->dieUsage( "No flagged revision with the specified ID.", 'notarget' );
				}
			} else {
				// FIXME: review_param_missing? better msg?
				if ( is_callable( [ $this, 'dieWithError' ] ) ) {
					$this->dieWithError( [ 'apierror-unknownerror-nocode' ], 'unknownerror' );
				} else {
					$this->dieUsageMsg( [ 'unknownerror', '' ] );
				}
			}
		}
	}

	public function mustBePosted() {
		return true;
	}

	public function isWriteMode() {
			return true;
	}

	public function getAllowedParams() {
		$pars = [
			'revid'   	=> null,
			'token'   	=> null,
			'comment' 	=> null,
			'unapprove' => false
		];
		if ( !FlaggedRevs::binaryFlagging() ) {
			/** @todo Once support for MediaWiki < 1.25 is dropped,
			 * just use ApiBase::PARAM_HELP_MSG directly */
			$key = constant( 'ApiBase::PARAM_HELP_MSG' ) ?: '';
			foreach ( FlaggedRevs::getDimensions() as $flagname => $levels ) {
				$strLevels = array_map( 'strval', array_keys( $levels ) );
				$pars['flag_' . $flagname] = [
					ApiBase::PARAM_DFLT => 1, // default
					ApiBase::PARAM_TYPE => $strLevels, // array of allowed values
					$key => [ 'apihelp-review-param-flag', $flagname ],
				];
			}
		}
		return $pars;
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		$desc = [
			'revid'  	=> 'The revision ID for which to set the flags',
			'token'   	=> 'An edit token retrieved through prop=info',
			'comment' 	=> 'Comment for the review (optional)',
			'unapprove' => 'If set, revision will be unapproved rather than approved.'
		];
		if ( !FlaggedRevs::binaryFlagging() ) {
			foreach ( FlaggedRevs::getTags() as $flagname ) {
				$desc['flag_' . $flagname] = "Set the flag ''{$flagname}'' to the specified value";
			}
		}
		return $desc;
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Review a revision by approving or de-approving it';
	}

	public function needsToken() {
		return 'csrf';
	}

	public function getTokenSalt() {
		return '';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return 'api.php?action=review&revid=12345&token=123AB&flag_accuracy=1&comment=Ok';
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
