<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Session\CsrfTokenSet;

class RevisionReview extends UnlistedSpecialPage {
	/** @var RevisionReviewForm|null */
	private $form;
	/** @var Title|null */
	private $page;

	/** @var PermissionManager */
	private $permissionManager;

	public function __construct() {
		parent::__construct( 'RevisionReview', 'review' );

		// TODO use dependency injection
		$this->permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
	}

	/**
	 * @inheritDoc
	 */
	public function doesWrites() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		$out = $this->getOutput();
		$user = $this->getUser();
		$request = $this->getRequest();

		# Our target page
		$this->page = Title::newFromText( $request->getVal( 'target' ) );
		if ( !$this->page ) {
			$out->showErrorPage( 'notargettitle', 'notargettext' );
			return;
		}

		if ( !$this->permissionManager->userHasRight( $user, 'review' ) ) {
			throw new PermissionsError( 'review' );
		}

		$confirmed = $user->matchEditToken( $request->getVal( 'wpEditToken' ) );
		if ( $this->permissionManager->isBlockedFrom( $user, $this->page, !$confirmed ) ) {
			// @phan-suppress-next-line PhanTypeMismatchArgumentNullable Guaranteed via isBlockedFrom() above
			throw new UserBlockedError( $user->getBlock( !$confirmed ) );
		}

		$this->checkReadOnly();
		$this->setHeaders();

		# Basic page permission checks...
		$permErrors = $this->permissionManager->getPermissionErrors(
			'review',
			$user,
			$this->page,
			PermissionManager::RIGOR_QUICK
		);
		if ( $permErrors ) {
			$out->showPermissionsErrorPage( $permErrors, 'review' );
			return;
		}

		$form = new RevisionReviewForm( $user );
		$this->form = $form;

		$form->setPage( $this->page );
		# Param for sites with binary flagging
		if ( $request->getCheck( 'wpApprove' ) ) {
			$form->setAction( RevisionReviewForm::ACTION_APPROVE );
		} elseif ( $request->getCheck( 'wpUnapprove' ) ) {
			$form->setAction( RevisionReviewForm::ACTION_UNAPPROVE );
		} elseif ( $request->getCheck( 'wpReject' ) ) {
			$form->setAction( RevisionReviewForm::ACTION_REJECT );
		}
		# Rev ID
		$form->setOldId( $request->getInt( 'oldid' ) );
		$form->setRefId( $request->getInt( 'refid' ) );
		# Special parameter mapping
		$form->setTemplateParams( $request->getVal( 'templateParams' ) );
		# Special token to discourage fiddling...
		$form->setValidatedParams( $request->getVal( 'validatedParams' ) );
		# Conflict handling
		$form->setLastChangeTime( $request->getVal( 'changetime' ) );
		# Session key
		$form->setSessionKey( $request->getSessionData( 'wsFlaggedRevsKey' ) );
		# Tag values
		# This can be NULL if we uncheck a checkbox
		if ( !FlaggedRevs::useOnlyIfProtected() ) {
			$form->setDim( FlaggedRevs::getTagName(), $request->getInt( 'wp' . FlaggedRevs::getTagName() ) );
		}
		# Log comment
		$form->setComment( $request->getText( 'wpReason' ) );
		$form->ready();

		if ( !$request->wasPosted() ) {
			// No form to view (GET)
			$out->returnToMain( false, $this->page );
			return;
		}
		// Review the edit if requested (POST)...

		// Check the edit token...
		if ( !$confirmed ) {
			$out->addWikiMsg( 'sessionfailure' );
			$out->returnToMain( false, $this->page );
			return;
		}

		// Use confirmation screen for reject...
		if ( $form->getAction() == RevisionReviewForm::ACTION_REJECT && !$request->getBool( 'wpRejectConfirm' ) ) {
			$rejectForm = new RejectConfirmationFormUI( $form );
			list( $html, $status ) = $rejectForm->getHtml();
			if ( $status === true ) {
				// Success...
				$out->addHTML( $html );
			} else {
				// Failure...
				if ( $status === 'review_page_unreviewable' ) {
					$out->addWikiMsg( 'revreview-main' );
					return;
				} elseif ( $status === 'review_page_notexists' ) {
					$out->showErrorPage( 'internalerror', 'nopagetext' );
					return;
				} elseif ( $status === 'review_bad_oldid' ) {
					$out->showErrorPage( 'internalerror', 'revreview-revnotfound' );
				} else {
					$out->showErrorPage( 'internalerror', $status );
				}
				$out->returnToMain( false, $this->page );
			}
			return;
		}

		// Otherwise submit...
		$status = $form->submit();
		if ( $status === true ) {
			// Success...
			$out->setPageTitle( $this->msg( 'actioncomplete' ) );
			if ( $form->getAction() === RevisionReviewForm::ACTION_APPROVE ) {
				$out->addHTML( $this->approvalSuccessHTML() );
			} elseif ( $form->getAction() === RevisionReviewForm::ACTION_UNAPPROVE ) {
				$out->addHTML( $this->deapprovalSuccessHTML() );
			} elseif ( $form->getAction() === RevisionReviewForm::ACTION_REJECT ) {
				$query = $this->page->isRedirect() ? [ 'redirect' => 'no' ] : [];
				$out->redirect( $this->page->getFullURL( $query ) );
			}
		} else {
			// Failure...
			if ( $status === 'review_page_unreviewable' ) {
				$out->addWikiMsg( 'revreview-main' );
				return;
			} elseif ( $status === 'review_page_notexists' ) {
				$out->showErrorPage( 'internalerror', 'nopagetext' );
				return;
			} elseif ( $status === 'review_denied' || $status === 'review_bad_key' ) {
				throw new PermissionsError( 'badaccess-group0' );
			} elseif ( $status === 'review_bad_oldid' ) {
				$out->showErrorPage( 'internalerror', 'revreview-revnotfound' );
			} elseif ( $status === 'review_not_flagged' ) {
				$out->redirect( $this->page->getFullURL() ); // already unflagged
			} elseif ( $status === 'review_too_low' ) {
				$out->addWikiMsg( 'revreview-toolow' );
			} else {
				$out->showErrorPage( 'internalerror', $status );
			}
			$out->returnToMain( false, $this->page );
		}
	}

	/**
	 * @return string HTML
	 */
	private function approvalSuccessHTML() {
		$title = $this->form->getPage();
		# Show success message
		$s = "<div class='plainlinks'>";
		$s .= $this->msg( 'revreview-successful',
			$title->getPrefixedText(), $title->getPrefixedURL() )->parseAsBlock();
		$s .= $this->msg( 'revreview-stable1',
			$title->getPrefixedURL(), $this->form->getOldId() )->parseAsBlock();
		$s .= "</div>";
		# Handy links to special pages
		if ( $this->permissionManager->userHasRight( $this->getUser(), 'unreviewedpages' ) ) {
			$s .= $this->getSpecialLinks();
		}
		return $s;
	}

	/**
	 * @return string HTML
	 */
	private function deapprovalSuccessHTML() {
		$title = $this->form->getPage();
		# Show success message
		$s = "<div class='plainlinks'>";
		$s .= $this->msg( 'revreview-successful2',
			$title->getPrefixedText(), $title->getPrefixedURL() )->parseAsBlock();
		$s .= $this->msg( 'revreview-stable2',
			$title->getPrefixedURL(), $this->form->getOldId() )->parseAsBlock();
		$s .= "</div>";
		# Handy links to special pages
		if ( $this->permissionManager->userHasRight( $this->getUser(), 'unreviewedpages' ) ) {
			$s .= $this->getSpecialLinks();
		}
		return $s;
	}

	/**
	 * @return string HTML
	 */
	private function getSpecialLinks() {
		$linkRenderer = $this->getLinkRenderer();
		$s = '<p>' . $this->msg( 'returnto' )->rawParams(
			$linkRenderer->makeKnownLink( SpecialPage::getTitleFor( 'UnreviewedPages' ) )
		)->escaped() . '</p>';
		$s .= '<p>' . $this->msg( 'returnto' )->rawParams(
			$linkRenderer->makeKnownLink( SpecialPage::getTitleFor( 'PendingChanges' ) )
		)->escaped() . '</p>';
		return $s;
	}

	/**
	 * @param array $argsMap
	 * @return array
	 */
	public static function doReview( $argsMap ) {
		$context = RequestContext::getMain();
		$user = $context->getUser();
		$out = $context->getOutput();
		$request = $context->getRequest();
		if ( MediaWikiServices::getInstance()->getReadOnlyMode()->isReadOnly() ) {
			return [ 'error-html' => wfMessage( 'revreview-failed' )->parse() .
				wfMessage( 'revreview-submission-invalid' )->parse() ];
		}
		// Make review interface object
		$form = new RevisionReviewForm( $user );
		$title = null; // target page
		$editToken = ''; // edit token

		foreach ( $argsMap as $par => $val ) {
			switch ( $par ) {
				case "target":
					$title = Title::newFromURL( $val );
					break;
				case "oldid":
					$form->setOldId( $val );
					break;
				case "refid":
					$form->setRefId( $val );
					break;
				case "validatedParams":
					$form->setValidatedParams( $val );
					break;
				case "templateParams":
					$form->setTemplateParams( $val );
					break;
				case "wpApprove":
					if ( $val ) {
						$form->setAction( RevisionReviewForm::ACTION_APPROVE );
					}
					break;
				case "wpUnapprove":
					if ( $val ) {
						$form->setAction( RevisionReviewForm::ACTION_UNAPPROVE );
					}
					break;
				case "wpReject":
					if ( $val ) {
						$form->setAction( RevisionReviewForm::ACTION_REJECT );
					}
					break;
				case "wpReason":
					$form->setComment( $val );
					break;
				case "changetime":
					$form->setLastChangeTime( $val );
					break;
				case "wpEditToken":
					$editToken = $val;
					break;
				default:
					if ( $par === 'wp' . FlaggedRevs::getTagName() && !FlaggedRevs::useOnlyIfProtected() ) {
						$form->setDim( FlaggedRevs::getTagName(), $val );
					}
					break;
			}
		}

		# Valid target title?
		if ( !$title ) {
			return [ 'error-html' => wfMessage( 'notargettext' )->parse() ];
		}

		$form->setPage( $title );
		$form->setSessionKey( $request->getSessionData( 'wsFlaggedRevsKey' ) );

		$form->ready(); // all params loaded
		# Check session via user token
		$userToken = new CsrfTokenSet( $request );
		if ( !$userToken->matchToken( $editToken ) ) {
			return [ 'error-html' => wfMessage( 'sessionfailure' )->parse() ];
		}
		# Basic permission checks...
		$permErrors = MediaWikiServices::getInstance()->getPermissionManager()
			->getPermissionErrors( 'review', $user, $title, PermissionManager::RIGOR_QUICK );
		if ( $permErrors ) {
			return [ 'error-html' => $out->parseAsInterface(
				$out->formatPermissionsErrorMessage( $permErrors, 'review' )
			) ];
		}
		# Try submission...
		$status = $form->submit();
		# Failure...
		if ( $status !== true ) {
			return [ 'error-html' => wfMessage( 'revreview-failed' )->parseAsBlock() .
				'<p>' . wfMessage( $status )->escaped() . '</p>' ];
		} elseif ( !$form->getAction() ) {
			return [ 'error-html' => wfMessage( 'revreview-failed' )->parse() ];
		}

		# Sent new lastChangeTime TS to client for later submissions...
		return [ 'change-time' => $form->getNewLastChangeTime() ];
	}
}
