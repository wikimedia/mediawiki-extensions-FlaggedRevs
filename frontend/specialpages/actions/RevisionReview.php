<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;

class RevisionReview extends UnlistedSpecialPage {
	/** @var RevisionReviewForm|null */
	private $form;
	/** @var Title|null */
	private $page;

	public function __construct() {
		parent::__construct( 'RevisionReview', 'review' );
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

		$confirmed = $user->matchEditToken( $request->getVal( 'wpEditToken' ) );

		# Our target page
		$this->page = Title::newFromText( $request->getVal( 'target' ) );
		if ( !$this->page ) {
			$out->showErrorPage( 'notargettitle', 'notargettext' );
			return;
		}

		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		if ( !$pm->userHasRight( $user, 'review' ) ) {
			throw new PermissionsError( 'review' );
		}

		if ( $pm->isBlockedFrom( $user, $this->page, !$confirmed ) ) {
			throw new UserBlockedError( $user->getBlock( !$confirmed ) );
		}

		$this->checkReadOnly();

		$this->setHeaders();

		# Basic page permission checks...
		$permErrors = $pm->getPermissionErrors( 'review', $user,
			$this->page, PermissionManager::RIGOR_QUICK );
		if ( $permErrors ) {
			$out->showPermissionsErrorPage( $permErrors, 'review' );
			return;
		}

		$this->form = new RevisionReviewForm( $user );
		$form = $this->form; // convenience

		$form->setPage( $this->page );
		# Param for sites with binary flagging
		$form->setApprove( $request->getCheck( 'wpApprove' ) );
		$form->setUnapprove( $request->getCheck( 'wpUnapprove' ) );
		$form->setReject( $request->getCheck( 'wpReject' ) );
		# Rev ID
		$form->setOldId( $request->getInt( 'oldid' ) );
		$form->setRefId( $request->getInt( 'refid' ) );
		# Special parameter mapping
		$form->setTemplateParams( $request->getVal( 'templateParams' ) );
		$form->setFileParams( $request->getVal( 'imageParams' ) );
		$form->setFileVersion( $request->getVal( 'fileVersion' ) );
		# Special token to discourage fiddling...
		$form->setValidatedParams( $request->getVal( 'validatedParams' ) );
		# Conflict handling
		$form->setLastChangeTime( $request->getVal( 'changetime' ) );
		# Session key
		$form->setSessionKey( $request->getSessionData( 'wsFlaggedRevsKey' ) );
		# Tag values
		foreach ( FlaggedRevs::getTags() as $tag ) {
			# This can be NULL if we uncheck a checkbox
			$val = $request->getInt( "wp$tag" );
			$form->setDim( $tag, $val );
		}
		# Log comment
		$form->setComment( $request->getText( 'wpReason' ) );
		$form->ready();

		# Review the edit if requested (POST)...
		if ( $request->wasPosted() ) {
			// Check the edit token...
			if ( !$confirmed ) {
				$out->addWikiMsg( 'sessionfailure' );
				$out->returnToMain( false, $this->page );
				return;
			}
			// Use confirmation screen for reject...
			if ( $form->getAction() == 'reject' && !$request->getBool( 'wpRejectConfirm' ) ) {
				$rejectForm = new RejectConfirmationFormUI( $form );
				list( $html, $status ) = $rejectForm->getHtml();
				// Success...
				if ( $status === true ) {
					$out->addHTML( $html );
				// Failure...
				} else {
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
			// Otherwise submit...
			} else {
				$status = $form->submit();
				// Success...
				if ( $status === true ) {
					$out->setPageTitle( $this->msg( 'actioncomplete' ) );
					if ( $form->getAction() === 'approve' ) {
						$out->addHTML( $this->approvalSuccessHTML( true ) );
					} elseif ( $form->getAction() === 'unapprove' ) {
						$out->addHTML( $this->deapprovalSuccessHTML( true ) );
					} elseif ( $form->getAction() === 'reject' ) {
						$query = $this->page->isRedirect() ? [ 'redirect' => 'no' ] : [];
						$out->redirect( $this->page->getFullURL( $query ) );
					}
				// Failure...
				} else {
					if ( $status === 'review_page_unreviewable' ) {
						$out->addWikiMsg( 'revreview-main' );
						return;
					} elseif ( $status === 'review_page_notexists' ) {
						$out->showErrorPage( 'internalerror', 'nopagetext' );
						return;
					} elseif ( $status === 'review_denied' ) {
						throw new PermissionsError( 'badaccess-group0' ); // protected?
					} elseif ( $status === 'review_bad_key' ) {
						throw new PermissionsError( 'badaccess-group0' ); // fiddling
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
		// No form to view (GET)
		} else {
			$out->returnToMain( false, $this->page );
		}
	}

	private function approvalSuccessHTML( $showlinks = false ) {
		$title = $this->form->getPage();
		# Show success message
		$s = "<div class='plainlinks'>";
		$s .= $this->msg( 'revreview-successful',
			$title->getPrefixedText(), $title->getPrefixedUrl() )->parseAsBlock();
		$s .= $this->msg( 'revreview-stable1',
			$title->getPrefixedUrl(), $this->form->getOldId() )->parseAsBlock();
		$s .= "</div>";
		# Handy links to special pages
		if ( $showlinks && MediaWikiServices::getInstance()->getPermissionManager()
				->userHasRight( $this->getUser(), 'unreviewedpages' )
		) {
			$s .= $this->getSpecialLinks();
		}
		return $s;
	}

	private function deapprovalSuccessHTML( $showlinks = false ) {
		$title = $this->form->getPage();
		# Show success message
		$s = "<div class='plainlinks'>";
		$s .= $this->msg( 'revreview-successful2',
			$title->getPrefixedText(), $title->getPrefixedUrl() )->parseAsBlock();
		$s .= $this->msg( 'revreview-stable2',
			$title->getPrefixedUrl(), $this->form->getOldId() )->parseAsBlock();
		$s .= "</div>";
		# Handy links to special pages
		if ( $showlinks && MediaWikiServices::getInstance()->getPermissionManager()
				->userHasRight( $this->getUser(), 'unreviewedpages' ) ) {
			$s .= $this->getSpecialLinks();
		}
		return $s;
	}

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

	public static function AjaxReview( /*$args...*/ ) {
		$context = RequestContext::getMain();
		$user = $context->getUser();
		$out = $context->getOutput();
		$request = $context->getRequest();

		$args = func_get_args();
		if ( wfReadOnly() ) {
			return '<err#>' . wfMessage( 'revreview-failed' )->parse() .
				wfMessage( 'revreview-submission-invalid' )->parse();
		}
		$tags = FlaggedRevs::getTags();
		// Make review interface object
		$form = new RevisionReviewForm( $user );
		$title = null; // target page
		$editToken = ''; // edit token
		// Each ajax url argument is of the form param|val.
		// This means that there is no ugly order dependence.
		foreach ( $args as $arg ) {
			$set = explode( '|', $arg, 2 );
			if ( count( $set ) != 2 ) {
				return '<err#>' . wfMessage( 'revreview-failed' )->parse() .
					wfMessage( 'revreview-submission-invalid' )->parse();
			}
			list( $par, $val ) = $set;
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
				case "imageParams":
					$form->setFileParams( $val );
					break;
				case "fileVersion":
					$form->setFileVersion( $val );
					break;
				case "wpApprove":
					$form->setApprove( $val );
					break;
				case "wpUnapprove":
					$form->setUnapprove( $val );
					break;
				case "wpReject":
					$form->setReject( $val );
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
					$p = preg_replace( '/^wp/', '', $par ); // kill any "wp" prefix
					if ( in_array( $p, $tags ) ) {
						$form->setDim( $p, $val );
					}
					break;
			}
		}
		# Valid target title?
		if ( !$title ) {
			return '<err#>' . wfMessage( 'notargettext' )->parse();
		}
		$form->setPage( $title );
		$form->setSessionKey( $request->getSessionData( 'wsFlaggedRevsKey' ) );

		$form->ready(); // all params loaded
		# Check session via user token
		if ( !$user->matchEditToken( $editToken ) ) {
			return '<err#>' . wfMessage( 'sessionfailure' )->parse();
		}
		# Basic permission checks...
		$permErrors = MediaWikiServices::getInstance()->getPermissionManager()
			->getPermissionErrors( 'review', $user, $title, PermissionManager::RIGOR_QUICK );
		if ( $permErrors ) {
			return '<err#>' . $out->parseAsInterface(
				$out->formatPermissionsErrorMessage( $permErrors, 'review' )
			);
		}
		# Try submission...
		$status = $form->submit();
		# Success...
		if ( $status === true ) {
			# Sent new lastChangeTime TS to client for later submissions...
			$changeTime = $form->getNewLastChangeTime();
			if ( $form->getAction() === 'approve' ) { // approve
				return "<suc#><lct#$changeTime>";
			} elseif ( $form->getAction() === 'unapprove' ) { // de-approve
				return "<suc#><lct#$changeTime>";
			} elseif ( $form->getAction() === 'reject' ) { // revert
				return "<suc#><lct#$changeTime>";
			}
		# Failure...
		} else {
			return '<err#>' . wfMessage( 'revreview-failed' )->parseAsBlock() .
				'<p>' . wfMessage( $status )->escaped() . '</p>';
		}

		return '';
	}
}
