<?php

use MediaWiki\CommentStore\CommentStore;
use MediaWiki\Html\Html;
use MediaWiki\Linker\Linker;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\UnlistedSpecialPage;
use MediaWiki\Title\Title;
use MediaWiki\Xml\Xml;

/** Assumes $wgFlaggedRevsProtection is off */
class Stabilization extends UnlistedSpecialPage {
	/** @var PageStabilityGeneralForm|null */
	private $form = null;

	public function __construct() {
		parent::__construct( 'Stabilization', 'stablesettings' );
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

		# Target page
		$title = Title::newFromText( $request->getVal( 'page', $par ) );
		if ( !$title ) {
			$out->showErrorPage( 'notargettitle', 'notargettext' );
			return;
		}

		# Let anyone view, but not submit...
		if ( $request->wasPosted() ) {
			$pm = MediaWikiServices::getInstance()->getPermissionManager();
			if ( !$pm->userHasRight( $user, 'stablesettings' ) ) {
				throw new PermissionsError( 'stablesettings' );
			}
			if ( $pm->isBlockedFrom( $user, $title, !$confirmed ) ) {
				// @phan-suppress-next-line PhanTypeMismatchArgumentNullable Guaranteed via isBlockedFrom() above
				throw new UserBlockedError( $user->getBlock( !$confirmed ) );
			}
			$this->checkReadOnly();
		}

		# Set page title
		$this->setHeaders();

		$this->getSkin()->setRelevantTitle( $title );

		$this->form = new PageStabilityGeneralForm( $user );
		$form = $this->form; // convenience

		$form->setTitle( $title );
		# Watch checkbox
		$form->setWatchThis( $request->getCheck( 'wpWatchthis' ) );
		# Get auto-review option...
		$form->setReviewThis( $request->getCheck( 'wpReviewthis' ) );
		# Reason
		$form->setReasonExtra( $request->getText( 'wpReason' ) );
		$form->setReasonSelection( $request->getVal( 'wpReasonSelection' ) );
		# Expiry
		$form->setExpiryCustom( $request->getText( 'mwStabilize-expiry' ) );
		$form->setExpirySelection( $request->getVal( 'wpExpirySelection' ) );
		# Default version
		$form->setOverride( (int)$request->getBool( 'wpStableconfig-override' ) );
		# Get autoreview restrictions...
		$form->setAutoreview( $request->getVal( 'mwProtect-level-autoreview' ) );
		$form->ready(); // params all set

		$status = $form->checkTarget();
		if ( $status === 'stabilize_page_notexists' ) {
			$out->addWikiMsg( 'stabilization-notexists', $title->getPrefixedText() );
			return;
		} elseif ( $status === 'stabilize_page_unreviewable' ) {
			$out->addWikiMsg( 'stabilization-notcontent', $title->getPrefixedText() );
			return;
		}

		# Form POST request...
		if ( $request->wasPosted() && $confirmed && $form->isAllowed() ) {
			$status = $form->submit();
			if ( $status === true ) {
				$out->redirect( $title->getFullURL() );
			} else {
				$this->showForm( $this->msg( $status )->escaped() );
			}
		# Form GET request...
		} else {
			$form->preload();
			$this->showForm();
		}
	}

	/**
	 * @param string|null $err
	 */
	private function showForm( $err = null ) {
		$out = $this->getOutput();
		$user = $this->getUser();

		$form = $this->form; // convenience
		$title = $this->form->getTitle();
		$oldConfig = $form->getOldConfig();

		$s = ''; // form HTML string
		# Add any error messages
		if ( $err !== '' ) {
			$out->setSubtitle( $this->msg( 'formerror' ) );
			$out->addHTML( "<p class='error'>{$err}</p>\n" );
		}
		# Add header text
		$msg = $form->isAllowed() ? 'stabilization-text' : 'stabilization-perm';
		$s .= $this->msg( $msg, $title->getPrefixedText() )->parseAsBlock();
		# Traditionally, the list of reasons for stabilization is the same as
		# for protection.  In some cases, however, it might be desirable to
		# use a different list for stabilization.
		$defaultReasons = $this->msg( 'stabilization-dropdown' );
		if ( $defaultReasons->isDisabled() ) {
			$defaultReasons = $this->msg( 'protect-dropdown' );
		}
		$reasonDropdown = Xml::listDropdown(
			'wpReasonSelection',
			$defaultReasons->inContentLanguage()->text(),
			$this->msg( 'protect-otherreason-op' )->inContentLanguage()->escaped(),
			$form->getReasonSelection(),
			'mwStabilize-reason',
			4
		);
		$scExpiryOptions = $this->msg( 'protect-expiry-options' )->inContentLanguage()->text();
		$showProtectOptions = ( $scExpiryOptions !== '-' && $form->isAllowed() );
		$dropdownOptions = []; // array of <label,value>
		# Add the current expiry as a dropdown option
		if ( $oldConfig['expiry'] && $oldConfig['expiry'] != 'infinity' ) {
			$timestamp = $this->getLanguage()->timeanddate( $oldConfig['expiry'] );
			$d = $this->getLanguage()->date( $oldConfig['expiry'] );
			$t = $this->getLanguage()->time( $oldConfig['expiry'] );
			$dropdownOptions[] = [
				$this->msg( 'protect-existing-expiry', $timestamp, $d, $t )->text(), 'existing' ];
		}
		# Add "other time" expiry dropdown option
		$dropdownOptions[] = [ $this->msg( 'protect-othertime-op' )->text(), 'othertime' ];
		# Add custom expiry dropdown options (from MediaWiki message)
		foreach ( explode( ',', $scExpiryOptions ) as $option ) {
			$pair = explode( ':', $option, 2 );
			$show = $pair[0];
			$value = $pair[1] ?? $show;
			$dropdownOptions[] = [ $show, $value ];
		}

		# Actually build the options HTML...
		$expiryFormOptions = '';
		foreach ( $dropdownOptions as [ $show, $value ] ) {
			$expiryFormOptions .= Html::element( 'option',
				[ 'value' => $value, 'selected' => $form->getExpirySelection() === $value ],
				$show
			) . "\n";
		}

		# Build up the form...
		$s .= Html::openElement( 'form', [ 'name' => 'stabilization',
			'action' => $this->getPageTitle()->getLocalURL(), 'method' => 'post' ] );
		# Add stable version override and selection options
		$s .=
			Xml::fieldset( $this->msg( 'stabilization-def' )->text() ) . "\n" .
			Xml::radioLabel( $this->msg( 'stabilization-def1' )->text(), 'wpStableconfig-override', '1',
				'default-stable', $form->getOverride() == 1, $this->disabledAttr() ) .
				'<br />' . "\n" .
			Xml::radioLabel( $this->msg( 'stabilization-def2' )->text(), 'wpStableconfig-override', '0',
				'default-current', $form->getOverride() == 0, $this->disabledAttr() ) . "\n" .
			Html::closeElement( 'fieldset' );
		# Add autoreview restriction select
		$s .= Xml::fieldset( $this->msg( 'stabilization-restrict' )->text() ) .
			$this->buildSelector( $form->getAutoreview() ) .
			Html::closeElement( 'fieldset' ) .

			Xml::fieldset( $this->msg( 'stabilization-leg' )->text() ) .
			Html::openElement( 'table' );
		# Add expiry dropdown to form...
		if ( $showProtectOptions && $form->isAllowed() ) {
			$s .= "
				<tr>
					<td class='mw-label'>" .
						Html::label( $this->msg( 'stabilization-expiry' )->text(),
							'mwStabilizeExpirySelection' ) .
					"</td>
					<td class='mw-input'>" .
						Html::rawElement( 'select',
							[
								'id'        => 'mwStabilizeExpirySelection',
								'name'      => 'wpExpirySelection',
								'onchange'  => 'onFRChangeExpiryDropdown()',
							] + $this->disabledAttr(),
							$expiryFormOptions ) .
					"</td>
				</tr>";
		}
		# Add custom expiry field to form...
		$attribs = [ 'id' => "mwStabilizeExpiryOther",
			'size' => 50,
			'onkeyup' => 'onFRChangeExpiryField()' ] + $this->disabledAttr();
		$s .= "
			<tr>
				<td class='mw-label'>" .
					Html::label( $this->msg( 'stabilization-othertime' )->text(),
						'mwStabilizeExpiryOther' ) .
				'</td>
				<td class="mw-input">' .
					Html::input( 'mwStabilize-expiry', $form->getExpiryCustom(), 'text', $attribs ) .
				'</td>
			</tr>';
		# Add comment input and submit button
		if ( $form->isAllowed() ) {
			$watchAttribs = [ 'accesskey' => $this->msg( 'accesskey-watch' )->text(),
				'id' => 'wpWatchthis' ];
			$services = MediaWikiServices::getInstance();
			$userOptionsLookup = $services->getUserOptionsLookup();
			$watchlistManager = $services->getWatchlistManager();
			$watchChecked = ( $userOptionsLookup->getOption( $user, 'watchdefault' )
				|| $watchlistManager->isWatched( $user, $title ) );

			$s .= ' <tr>
					<td class="mw-label">' .
						Html::label( $this->msg( 'stabilization-comment' )->text(),
							'wpReasonSelection' ) .
					'</td>
					<td class="mw-input">' .
						$reasonDropdown .
					'</td>
				</tr>
				<tr>
					<td class="mw-label">' .
						Html::label( $this->msg( 'stabilization-otherreason' )->text(), 'wpReason' ) .
					'</td>
					<td class="mw-input">' .
						Html::input( 'wpReason', $form->getReasonExtra(), 'text', [
							'id' => 'wpReason',
							'size' => 70,
							'maxlength' => CommentStore::COMMENT_CHARACTER_LIMIT
						] ) .
					'</td>
				</tr>
				<tr>
					<td></td>
					<td class="mw-input">' .
						Html::check( 'wpReviewthis', $form->getReviewThis(),
							[ 'id' => 'wpReviewthis' ] ) .
						Html::label( $this->msg( 'stabilization-review' )->text(), 'wpReviewthis' ) .
						'&#160;&#160;&#160;&#160;&#160;' .
						Html::check( 'wpWatchthis', $watchChecked, $watchAttribs ) .
						"&#160;" .
						Html::label( $this->msg( 'watchthis' )->text(), 'wpWatchthis',
							[ 'title' => Linker::titleAttrib( 'watch', 'withaccess' ) ] ) .
					'</td>
				</tr>
				<tr>
					<td></td>
					<td class="mw-submit">' .
						Html::submitButton( $this->msg( 'stabilization-submit' )->text() ) .
					'</td>
				</tr>' . Html::closeElement( 'table' ) .
				Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBkey() ) .
				Html::hidden( 'page', $title->getPrefixedText() ) .
				Html::hidden( 'wpEditToken', $this->getUser()->getEditToken() );
		} else {
			$s .= Html::closeElement( 'table' );
		}
		$s .= Html::closeElement( 'fieldset' ) . Html::closeElement( 'form' );

		$out->addHTML( $s );

		$log = new LogPage( 'stable' );
		$out->addHTML( Html::element( 'h2', [],
			$log->getName()->setContext( $this->getContext() )->text() ) );
		LogEventsList::showLogExtract( $out, 'stable',
			$title->getPrefixedText(), '', [ 'lim' => 25 ] );

		# Add some javascript for expiry dropdowns
		$out->addScript(
			"<script type=\"text/javascript\">
				function onFRChangeExpiryDropdown() {
					document.getElementById('mwStabilizeExpiryOther').value = '';
				}
				function onFRChangeExpiryField() {
					document.getElementById('mwStabilizeExpirySelection').value = 'othertime';
				}
			</script>"
		);
	}

	/**
	 * @param string $selected
	 * @return string HTML
	 */
	private function buildSelector( $selected ) {
		$allowedLevels = [];
		// Add a "none" level
		$levels = [ '', ...FlaggedRevs::getRestrictionLevels() ];
		foreach ( $levels as $key ) {
			# Don't let them choose levels they can't set,
			# but *show* them all when the form is disabled.
			if ( $this->form->isAllowed()
				&& !FlaggedRevs::userCanSetAutoreviewLevel( $this->getUser(), $key )
			) {
				continue;
			}
			$allowedLevels[] = $key;
		}
		$id = 'mwProtect-level-autoreview';
		$attribs = [
			'id' => $id,
			'name' => $id,
			'size' => count( $allowedLevels ),
		] + $this->disabledAttr();

		$out = '';
		foreach ( $allowedLevels as $key ) {
			$out .= Html::element( 'option',
				[ 'value' => $key, 'selected' => $key == $selected ],
				$this->getOptionLabel( $key )
			);
		}
		return Html::rawElement( 'select', $attribs, $out );
	}

	/**
	 * Prepare the label for a protection selector option
	 *
	 * @param string $permission Permission required
	 * @return string
	 */
	private function getOptionLabel( $permission ) {
		if ( !$permission ) {
			return $this->msg( 'stabilization-restrict-none' )->text();
		}

		$msg = $this->msg( "protect-level-$permission" );
		if ( $msg->isDisabled() ) {
			$msg = $this->msg( 'protect-fallback', $permission );
		}
		return $msg->text();
	}

	/**
	 * If the this form is disabled, then return the "disabled" attr array
	 * @return string[]
	 */
	private function disabledAttr() {
		return $this->form->isAllowed()
			? []
			: [ 'disabled' => 'disabled' ];
	}
}
