<?php

/*
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
		global $wgUser;
		$params = $this->extractRequestParams();
		// Check basic permissions
		if ( !$wgUser->isAllowed( 'review' ) ) {
			// FIXME: better msg?
			$this->dieUsageMsg( array( 'badaccess-group0' ) );
		} elseif ( $wgUser->isBlocked( false ) ) {
			$this->dieUsageMsg( array( 'blockedtext' ) );
		}
		// Construct submit form
		$form = new RevisionReviewForm();
		$revid = (int)$params['revid'];
		$rev = Revision::newFromId( $revid );
		if ( !$rev ) {
			$this->dieUsage( "Cannot find a revision with the specified ID.", 'notarget' );
		}
		$title = $rev->getTitle();
		$form->setPage( $title );
		$form->setOldId( $revid );
		if ( FlaggedRevs::dimensionsEmpty() ) {
			$form->setApprove( empty( $params['unapprove'] ) );
			$form->setUnapprove( !empty( $params['unapprove'] ) );
		}
		if ( isset( $params['comment'] ) )
			$form->setComment( $params['comment'] );
		if ( isset( $params['notes'] ) )
			$form->setNotes( $params['notes'] );
		// The flagging parameters have the form 'flag_$name'.
		// Extract them and put the values into $form->dims
		foreach ( FlaggedRevs::getDimensions() as $tag => $levels ) {
			$form->setDim( $tag, intval( $params['flag_' . $tag] ) );
		}
		if ( $form->isApproval() ) {
			// Now get the template and image parameters needed
			// If it is the current revision, try the parser cache first
			$article = new FlaggedArticle( $title, $revid );
			if ( $rev->isCurrent() ) {
				$parserCache = ParserCache::singleton();
				$parserOutput = $parserCache->get( $article, $wgUser );
			}
			if ( empty( $parserOutput ) ) {
				// Miss, we have to reparse the page
				global $wgParser;
				$text = $article->getContent();
				$options = FlaggedRevs::makeParserOptions();
				$parserOutput = $wgParser->parse( $text, $title, $options );
			}
			// Set version parameters for review submission
			list( $templateParams, $imageParams, $fileVersion ) =
				FlaggedRevs::getIncludeParams( $article,
					$parserOutput->mTemplateIds, $parserOutput->fr_ImageSHA1Keys );
			$form->setTemplateParams( $templateParams );
			$form->setFileParams( $imageParams );
			$form->setFileVersion( $fileVersion );
			$key = RevisionReviewForm::validationKey(
				$templateParams, $imageParams, $fileVersion, $revid );
			$form->setValidatedParams( $key ); # always OK
		}

		$status = $form->ready(); // all params set
		if ( $status === 'review_page_unreviewable' ) {
			$this->dieUsage( "Provided revision or page can not be reviewed.",
				'notreviewable' );
		// Check basic page permissions
		} elseif ( !$title->quickUserCan( 'review' ) || !$title->quickUserCan( 'edit' ) ) {
			$this->dieUsage( "You don't have the necessary rights to set the specified flags.",
				'permissiondenied' );
		}

		# Try to do the actual review
		$status = $form->submit();
		# Approve/de-approve success
		if ( $status === true ) {
			$this->getResult()->addValue(
				null, $this->getModuleName(), array( 'result' => 'Success' ) );
		# De-approve failure
		} elseif ( !$form->isApproval() ) {
			$this->dieUsage( "Cannot find a flagged revision with the specified ID.", 'notarget' );
		# Approval failures
		} else {
			if ( is_array( $status ) ) {
				$this->dieUsage( "A sync failure has occured while reviewing. Please try again.",
					'syncfailure' );
			} elseif ( $status === 'review_too_low' ) {
				$this->dieUsage( "Either all or none of the flags have to be set to zero.",
					'mixedapproval' );
			} elseif ( $status === 'review_denied' ) {
				$this->dieUsage( "You don't have the necessary rights to set the specified flags.",
					'permissiondenied' );
			} elseif ( $status === 'review_bad_key' ) {
				$this->dieUsage( "You don't have the necessary rights to set the specified flags.",
					'permissiondenied' );
			} else {
				// FIXME: review_param_missing? better msg?
				$this->dieUsage( array( 'unknownerror' ) );
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
		$pars = array(
			'revid'   => null,
			'token'   => null,
			'comment' => null,
		);
		if ( FlaggedRevs::allowComments() )
			$pars['notes'] = null;
		if ( FlaggedRevs::dimensionsEmpty() ) {
			$pars['unapprove'] = false;
		} else {
			foreach ( FlaggedRevs::getDimensions() as $flagname => $levels ) {
				$pars['flag_' . $flagname] = array(
					ApiBase::PARAM_DFLT => 1, // default
					ApiBase::PARAM_TYPE => array_keys( $levels ) // array of allowed values
				);
			}
		}
		return $pars;
	}

	public function getParamDescription() {
		$desc = array(
			'revid'   => 'The revision ID for which to set the flags',
			'token'   => 'An edit token retrieved through prop=info',
			'comment' => 'Comment for the review (optional)'
		);
		if ( FlaggedRevs::allowComments() )
			$desc['notes'] = "Additional notes for the review. The ''validate'' right is needed to set this parameter.";
		if ( FlaggedRevs::dimensionsEmpty() ) {
			$desc['unapprove'] = "If set, revision will be unapproved rather than approved.";
		} else {
			foreach ( FlaggedRevs::getDimensions() as $flagname => $levels ) {
				$desc['flag_' . $flagname] = "Set the flag ''{$flagname}'' to the specified value";
			}
		}
		return $desc;
	}

	public function getDescription() {
		return 'Review a revision via FlaggedRevs.';
	}
	
	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'badaccess-group0' ),
			array( 'blockedtext' ),
			array( 'code' => 'notarget', 'info' => 'Provided revision or page can not be reviewed.' ),
			array( 'code' => 'notreviewable', 'info' => 'Provided revision or page can not be reviewed.' ),
			array( 'code' => 'mixedapproval', 'info' => 'Either all or none of the flags have to be set to zero.' ),
			array( 'code' => 'permissiondenied', 'info' => 'You don\'t have the necessary rights to set the specified flags.' ),
			array( 'code' => 'syncfailure', 'info' => 'A sync failure has occured while reviewing. Please try again.' ),
		) );
	}
	
	public function getTokenSalt() {
		return '';
	}

	protected function getExamples() {
		return 'api.php?action=review&revid=12345&token=123AB&flag_accuracy=1&comment=Ok';
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
