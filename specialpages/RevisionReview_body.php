<?php
# (c) Aaron Schulz, Joerg Baach, 2007 GPL

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

class RevisionReview extends UnlistedSpecialPage
{
	private $form;
	private $page;

    public function __construct() {
		parent::__construct( 'RevisionReview', 'review' );
    }

    public function execute( $par ) {
        global $wgRequest, $wgUser, $wgOut;
		$confirmed = $wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );
		if ( $wgUser->isAllowed( 'review' ) ) {
			if ( $wgUser->isBlocked( !$confirmed ) ) {
				$wgOut->blockedPage();
				return;
			}
		} else {
			$wgOut->permissionRequired( 'review' );
			return;
		}
		if ( wfReadOnly() ) {
			$wgOut->readOnlyPage();
			return;
		}
		$this->setHeaders();
		
		$this->form = new RevisionReviewForm( $wgUser );
		$form = $this->form; // convenience
		# Our target page
		$this->page = Title::newFromURL( $wgRequest->getVal( 'target' ) );
		if ( !$this->page ) {
			$wgOut->showErrorPage( 'notargettitle', 'notargettext' );
			return;
		}
		$form->setPage( $this->page );
		# Param for sites with binary flagging
		$form->setApprove( $wgRequest->getCheck( 'wpApprove' ) );
		$form->setUnapprove( $wgRequest->getCheck( 'wpUnapprove' ) );
		# Rev ID
		$oldid = $wgRequest->getInt( 'oldid' );
		$form->setOldId( $oldid );
		# Special parameter mapping
		$form->setTemplateParams( $wgRequest->getVal( 'templateParams' ) );
		$form->setFileParams( $wgRequest->getVal( 'imageParams' ) );
		$form->setFileVersion( $wgRequest->getVal( 'fileVersion' ) );
		# Special token to discourage fiddling...
		$form->setValidatedParams( $wgRequest->getVal( 'validatedParams' ) );
		# Tag values
		foreach ( FlaggedRevs::getDimensions() as $tag => $levels ) {
			# This can be NULL if we uncheck a checkbox
			$val = $wgRequest->getInt( "wp$tag" );
			$form->setDim( $tag, $val );
		}
		# Log comment
		$form->setComment( $wgRequest->getText( 'wpReason' ) );
		# Additional notes (displayed at bottom of page)
		$form->setNotes( $wgRequest->getText( 'wpNotes' ) );

		$status = $form->ready();
		# Page must exist and be in reviewable namespace
		if ( $status === 'review_page_unreviewable' ) {
			$wgOut->addWikiText( wfMsg( 'revreview-main' ) );
			return;
		} elseif ( $status === 'review_page_notexists' ) {
			$wgOut->showErrorPage( 'internalerror', 'nopagetext' );
			return;
		}
		# Basic page permission checks...
		$permErrors = $this->page->getUserPermissionsErrors( 'review', $wgUser, false );
		if ( !$permErrors ) {
			$permErrors = $this->page->getUserPermissionsErrors( 'edit', $wgUser, false );
		}
		if ( $permErrors ) {
			$wgOut->showPermissionsErrorPage( $permErrors, 'review' );
			return;
		}

		# Review the edit if requested (POST)...
		if ( $wgRequest->wasPosted() ) {
			// Check the edit token...
			if ( !$confirmed ) {
				$wgOut->addWikiText( wfMsg( 'sessionfailure' ) );
				$wgOut->returnToMain( false, $this->page );
				return;
			}
			$status = $form->submit();
			// Success for either flagging or unflagging
			if ( $status === true ) {
				$wgOut->setPageTitle( wfMsgHtml( 'actioncomplete' ) );
				if ( $form->isApproval() ) {
					$wgOut->addHTML( $form->approvalSuccessHTML( true ) );
				} else {
					$wgOut->addHTML( $form->deapprovalSuccessHTML( true ) );
				}
			// Failure for flagging or unflagging
			} else {
				if ( $status === 'review_denied' ) {
					$wgOut->permissionRequired( 'badaccess-group0' ); // protected?
				} elseif ( $status === 'review_bad_key' ) {
					$wgOut->permissionRequired( 'badaccess-group0' ); // fiddling
				} elseif ( $status === 'review_bad_oldid' ) {
					$wgOut->showErrorPage( 'internalerror', 'revreview-revnotfound' );
				} elseif ( $status === 'review_not_flagged' ) {
					$wgOut->redirect( $this->page->getFullUrl() ); // already unflagged
				} elseif ( $status === 'review_too_low' ) {
					$wgOut->addWikiText( wfMsg( 'revreview-toolow' ) );
				} else {
					$wgOut->showErrorPage( 'internalerror', $status );
				}
				$wgOut->returnToMain( false, $this->page );
			}
		// No form to view (GET)
		} else {
			$wgOut->returnToMain( false, $this->page );
		}
	}

	public static function AjaxReview( /*$args...*/ ) {
		global $wgUser, $wgOut;
		$args = func_get_args();
		if ( wfReadOnly() ) {
			return '<err#>' . wfMsgExt( 'revreview-failed', 'parseinline' );
		}
		$tags = FlaggedRevs::getDimensions();
		// Make review interface object
		$form = new RevisionReviewForm( $wgUser );
		$title = null; // target page
		$editToken = ''; // edit token
		// Each ajax url argument is of the form param|val.
		// This means that there is no ugly order dependance.
		foreach ( $args as $x => $arg ) {
			$set = explode( '|', $arg, 2 );
			if ( count( $set ) != 2 ) {
				return '<err#>' . wfMsgExt( 'revreview-failed', 'parseinline' );
			}
			list( $par, $val ) = $set;
			switch( $par )
			{
				case "target":
					$title = Title::newFromURL( $val );
					break;
				case "oldid":
					$form->setOldId( $val );
					break;
				case "rcid":
					$form->setRCId( $val );
					break;
				case "validatedParams":
					$form->setValidatedParams( $val );
					break;
				case "templateParams":
					$form->setTemplateParams( $val);
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
				case "wpReason":
					$form->setComment( $val );
					break;
				case "wpNotes":
					$form->setNotes( $val );
					break;
				case "wpEditToken":
					$editToken = $val;
					break;
				default:
					$p = preg_replace( '/^wp/', '', $par ); // kill any "wp" prefix
					if ( array_key_exists( $p, $tags ) ) {
						$form->setDim( $p, $val );
					}
					break;
			}
		}
		# Valid target title?
		if ( !$title ) {
			return '<err#>' . wfMsgExt( 'notargettext', 'parseinline' );
		}
		$form->setPage( $title );

		$status = $form->ready(); // all params loaded
		# Page must exist and be in reviewable namespace
		if ( $status === 'review_page_unreviewable' ) {
			return '<err#>' . wfMsgExt( 'revreview-main', 'parseinline' );
		} elseif ( $status === 'review_page_notexists' ) {
			return '<err#>' . wfMsgExt( 'nopagetext', 'parseinline' );
		}
		# Check session via user token
		if ( !$wgUser->matchEditToken( $editToken ) ) {
			return '<err#>' . wfMsgExt( 'sessionfailure', 'parseinline' );
		}
		# Basic permission checks...
		$permErrors = $title->getUserPermissionsErrors( 'review', $wgUser, false );
		if ( !$permErrors ) {
			$permErrors = $title->getUserPermissionsErrors( 'edit', $wgUser, false );
		}
		if ( $permErrors ) {
			return '<err#>' . $wgOut->parse(
				$wgOut->formatPermissionsErrorMessage( $permErrors, 'review' )
			);
		}
		# Try submission...
		$status = $form->submit();
		# Success...
		if ( $status === true ) {
			$tier = FlaggedRevs::getLevelTier( $form->getDims() ) + 1; // shift to 0-3
			if ( $form->isApproval() ) { // approve
				return "<suc#><t#$tier>" . $form->approvalSuccessHTML( false );
			} else { // de-approve
				return "<suc#><t#$tier>" . $form->deapprovalSuccessHTML( false );
			}
		# Failure...
		} else {
			return '<err#>' . wfMsgExt( 'revreview-failed', 'parse' ) .
				'<p>' . wfMsgHtml( $status ) . '</p>';
		}
	}
}
