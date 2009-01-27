<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

class Stabilization extends UnlistedSpecialPage
{
	public function __construct() {
		UnlistedSpecialPage::UnlistedSpecialPage( 'Stabilization', 'stablesettings' );
		wfLoadExtensionMessages( 'Stabilization' );
		wfLoadExtensionMessages( 'FlaggedRevs' );
    }

	public function execute( $par ) {
		global $wgRequest, $wgUser, $wgOut;
		# Check user token
		$confirm = $wgRequest->wasPosted() &&
			$wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );
		# Allow unprivileged users to at least view the settings
		$this->isAllowed = $wgUser->isAllowed( 'stablesettings' );
		$this->disabledAttrib = !$this->isAllowed ? array( 'disabled' => 'disabled' ) : array();
		# Let anyone view, but not submit...
		if( $wgRequest->wasPosted() ) {
			if( $wgUser->isBlocked( !$confirm ) ) {
				$wgOut->blockedPage();
				return;
			} else if( !$this->isAllowed ) {
				$wgOut->permissionRequired( 'stablesettings' );
				return;
			} else if( wfReadOnly() ) {
				$wgOut->readOnlyPage();
				return;
			}
		}

		$this->setHeaders();
		$this->skin = $wgUser->getSkin();

		$isValid = true;
		# Our target page
		$this->target = $wgRequest->getText( 'page' );
		$this->page = Title::newFromUrl( $this->target );
		# We need a page...
		if( is_null($this->page) ) {
			$wgOut->showErrorPage( 'notargettitle', 'notargettext' );
			return;
		} else if( !$this->page->exists() ) {
			$wgOut->addHTML( wfMsgExt( 'stabilization-notexists', array('parseinline'), $this->page->getPrefixedText() ) );
			return;
		} else if( !FlaggedRevs::isPageReviewable( $this->page ) ) {
			$wgOut->addHTML( wfMsgExt( 'stabilization-notcontent', array('parseinline'), $this->page->getPrefixedText() ) );
			return;
		}

		# Watch checkbox
		$this->watchThis = $wgRequest->getCheck( 'wpWatchthis' );
		# Reason
		$this->reason = $wgRequest->getVal( 'wpReason' );
		$this->reasonSelection = $wgRequest->getText( 'wpReasonSelection' );
		$this->expiry = $wgRequest->getText( 'mwStabilize-expiry' );
		$this->expirySelection = $wgRequest->getVal( 'wpExpirySelection' );
		# Get visiblity settings...
		$this->config = FlaggedRevs::getPageVisibilitySettings( $this->page, true );
		$this->select = $this->config['select'];
		$this->override = $this->config['override'];
		# Make user readable date for GET requests
		$this->oldExpiry = $this->config['expiry'] !== 'infinity' ? 
			wfTimestamp( TS_RFC2822, $this->config['expiry'] ) : 'infinite';
		# Handle submission data
		if( $wgRequest->wasPosted() ) {
			$this->select = $wgRequest->getInt( 'wpStableconfig-select' );
			$this->override = intval( $wgRequest->getBool( 'wpStableconfig-override' ) );
			// Custom expiry takes precedence
			$this->expiry = strlen($this->expiry) ? $this->expiry : $this->expirySelection;
			if( $this->expiry == 'existing' ) $this->expiry = $this->oldExpiry;
			// Custom reason takes precedence
			$this->reason = strlen($this->reason) ? $this->reason : $this->reasonSelection;
			// Validate precedence setting
			$allowed = array(FLAGGED_VIS_NORMAL,FLAGGED_VIS_LATEST,FLAGGED_VIS_PRISTINE);
			if( $this->select && !in_array( $this->select, $allowed ) ) {
				$isValid = false;
			}
		}

		if( $isValid && $confirm ) {
			$this->submit();
		} else {
			$this->showSettings();
		}
	}

	protected function showSettings( $err = null ) {
		global $wgOut, $wgLang, $wgUser;
		# Must be a content page
		if( !FlaggedRevs::isPageReviewable( $this->page ) ) {
			$wgOut->addHTML( wfMsgExt('stableversions-none', array('parse'), $this->page->getPrefixedText() ) );
			return;
		}
		# Add any error messages
		if( "" != $err ) {
			$wgOut->setSubtitle( wfMsgHtml( 'formerror' ) );
			$wgOut->addHTML( "<p class='error'>{$err}</p>\n" );
		}
		# Add header text
		if( !$this->isAllowed ) {
			$form = wfMsgExt( 'stabilization-perm', array('parse'), $this->page->getPrefixedText() );
		} else {
			$form = wfMsgExt( 'stabilization-text', array('parse'), $this->page->getPrefixedText() );
		}
		# Add some script
		$wgOut->addScript( 
			"<script type=\"text/javascript\">
				function updateStabilizationDropdowns() {
					val = document.getElementById('mwExpirySelection').value;
					if( val == 'existing' )
						document.getElementById('mwStabilize-expiry').value = ".Xml::encodeJsVar($this->oldExpiry).";
					else if( val == 'othertime' )
						document.getElementById('mwStabilize-expiry').value = '';
					else
						document.getElementById('mwStabilize-expiry').value = val;
				}
			</script>"
		);
		# Borrow some protection messages for dropdowns
		$reasonDropDown = Xml::listDropDown( 'wpReasonSelection',
			wfMsgForContent( 'protect-dropdown' ),
			wfMsgForContent( 'protect-otherreason-op' ), 
			$this->reasonSelection,
			'mwStabilize-reason', 4
		);
		$scExpiryOptions = wfMsgForContent( 'protect-expiry-options' );
		$showProtectOptions = ($scExpiryOptions !== '-' && $this->isAllowed);
		# Add the current expiry as an option
		$expiryFormOptions = '';
		if( $this->config['expiry'] && $this->config['expiry'] != 'infinity' ) {
			$timestamp = $wgLang->timeanddate( $this->config['expiry'] );
			$d = $wgLang->date( $this->config['expiry'] );
			$t = $wgLang->time( $this->config['expiry'] );
			$expiryFormOptions .= 
				Xml::option( 
					wfMsg( 'protect-existing-expiry', $timestamp, $d, $t ),
					'existing',
					$this->config['expiry'] == 'existing'
				) . "\n";
		}
		$expiryFormOptions .= Xml::option( wfMsg( 'protect-othertime-op' ), "othertime" ) . "\n";
		# Add custom levels
		foreach( explode(',',$scExpiryOptions) as $option ) {
			if( strpos($option,":") === false ) {
				$show = $value = $option;
			} else {
				list($show, $value) = explode(":",$option);
			}
			$show = htmlspecialchars($show);
			$value = htmlspecialchars($value);
			$expiryFormOptions .= Xml::option( $show, $value, $this->config['expiry'] === $value ) . "\n";
		}
		# Add stable version override and selection options
		$special = SpecialPage::getTitleFor( 'Stabilization' );
		$form .= Xml::openElement( 'form', array( 'name' => 'stabilization',
			'action' => $special->getLocalUrl(), 'method' => 'post' ) ) .
			Xml::fieldset( wfMsg( 'stabilization-def' ), false ) . "\n" .
			Xml::radioLabel( wfMsg( 'stabilization-def1' ), 'wpStableconfig-override', 1,
				'default-stable', 1 == $this->override, $this->disabledAttrib ) . '<br />' . "\n" .
			Xml::radioLabel( wfMsg( 'stabilization-def2' ), 'wpStableconfig-override', 0,
				'default-current', 0 == $this->override, $this->disabledAttrib ) . "\n" .
			Xml::closeElement( 'fieldset' ) .

			Xml::fieldset( wfMsg( 'stabilization-select' ), false ) .
			Xml::radioLabel( wfMsg( 'stabilization-select3' ), 'wpStableconfig-select', FLAGGED_VIS_PRISTINE,
				'stable-select3', FLAGGED_VIS_PRISTINE == $this->select, $this->disabledAttrib ) . '<br />' . "\n" .
			Xml::radioLabel( wfMsg( 'stabilization-select1' ), 'wpStableconfig-select', FLAGGED_VIS_NORMAL,
				'stable-select1', FLAGGED_VIS_NORMAL == $this->select, $this->disabledAttrib ) . '<br />' . "\n" .
			Xml::radioLabel( wfMsg( 'stabilization-select2' ), 'wpStableconfig-select', FLAGGED_VIS_LATEST,
				'stable-select2', FLAGGED_VIS_LATEST == $this->select, $this->disabledAttrib ) . '<br />' . "\n" .
			Xml::closeElement( 'fieldset' ) .

			Xml::fieldset( wfMsg( 'stabilization-leg' ), false ) .
			Xml::openElement( 'table' );
		# Add expiry dropdown
		if( $showProtectOptions && $this->isAllowed ) {
			$form .= "
				<tr>
					<td class='mw-label'>" .
						Xml::label( wfMsg('stabilization-expiry'), 'mwExpirySelection' ) .
					"</td>
					<td class='mw-input'>" .
						Xml::tags( 'select',
							array(
								'id' => 'mwExpirySelection',
								'name' => 'wpExpirySelection',
								'onchange' => 'updateStabilizationDropdowns()',
							) + $this->disabledAttrib,
							$expiryFormOptions ) .
					"</td>
				</tr>";
		}
		# Add custom expiry field
		$attribs = array( 'id' => "mwStabilize-expiry",
			'onkeyup' => 'updateStabilizationDropdowns()' ) + $this->disabledAttrib;
		$form .= "
			<tr>
				<td class='mw-label'>" .
					Xml::label( wfMsg('stabilization-othertime'), 'mwStabilize-expiry' ) .
				'</td>
				<td class="mw-input">' .
					Xml::input( "mwStabilize-expiry", 50, $this->expiry ? $this->expiry : $this->oldExpiry, $attribs ) .
				'</td>
			</tr>';
		# Add comment input and submit button
		if( $this->isAllowed ) {
			$watchLabel = wfMsgExt( 'watchthis', array('parseinline') );
			$watchAttribs = array('accesskey' => wfMsg( 'accesskey-watch' ), 'id' => 'wpWatchthis');
			$watchChecked = ( $wgUser->getOption( 'watchdefault' ) || $this->page->userIsWatching() );

			$form .= ' <tr>
					<td class="mw-label">' .
						xml::label( wfMsg('stabilization-comment'), 'wpReasonSelection' ) .
					'</td>
					<td class="mw-input">' .
						$reasonDropDown .
					'</td>
				</tr>
				<tr>
					<td class="mw-label">' .
						Xml::label( wfMsg( 'stabilization-otherreason' ), 'wpReason' ) .
					'</td>
					<td class="mw-input">' .
						Xml::input( 'wpReason', 70, $this->reason, array( 'id' => 'wpReason' ) ) .
					'</td>
				</tr>
				<tr>
					<td></td>
					<td class="mw-input">' .
						Xml::check( 'wpWatchthis', $watchChecked, $watchAttribs ) .
						"<label for='wpWatchthis'" . $this->skin->tooltipAndAccesskey( 'watch' ) . ">{$watchLabel}</label>" .
					'</td>
				</tr>
				<tr>
					<td></td>
					<td class="mw-submit">' .
						Xml::submitButton( wfMsg( 'stabilization-submit' ) ) .
					'</td>
				</tr>' . Xml::closeElement( 'table' ) .
				Xml::hidden( 'title', $this->getTitle()->getPrefixedDBKey() ) .
				Xml::hidden( 'page', $this->page->getPrefixedText() ) .
				Xml::hidden( 'wpEditToken', $wgUser->editToken() );
		} else {
			$form .= Xml::closeElement( 'table' );
		}
		$form .= Xml::closeElement( 'fieldset' ) . Xml::closeElement( 'form' );

		$wgOut->addHTML( $form );

		$wgOut->addHTML( Xml::element( 'h2', NULL, htmlspecialchars( LogPage::logName( 'stable' ) ) ) );
		LogEventsList::showLogExtract( $wgOut, 'stable', $this->page->getPrefixedText() );
	}

	protected function submit() {
		global $wgOut, $wgUser, $wgParser, $wgFlaggedRevsOverride;

		$changed = $reset = false;
		$defaultPrecedence = FlaggedRevs::getPrecedence();
		if( $this->select == $defaultPrecedence && $this->override == $wgFlaggedRevsOverride ) {
			$reset = true; // we are going back to site defaults
		}
		# Take this opportunity to purge out expired configurations
		FlaggedRevs::purgeExpiredConfigurations();

		if( $reset || $this->expiry == 'infinite' || $this->expiry == 'indefinite' ) {
			$expiry = Block::infinity();
		} else {
			# Convert GNU-style date, on error returns -1 for PHP <5.1 and false for PHP >=5.1
			$expiry = strtotime( $this->expiry );
			if( $expiry < 0 || $expiry === false ) {
				$this->showSettings( wfMsg( 'stabilize_expiry_invalid' ) );
				return false;
			}
			$expiry = wfTimestamp( TS_MW, $expiry );
			if( $expiry < wfTimestampNow() ) {
				$this->showSettings( wfMsg( 'stabilize_expiry_old' ) );
				return false;
			}
		}

		$dbw = wfGetDB( DB_MASTER );
		# Get current config
		$row = $dbw->selectRow( 'flaggedpage_config',
			array( 'fpc_select', 'fpc_override', 'fpc_expiry' ),
			array( 'fpc_page_id' => $this->page->getArticleID() ),
			__METHOD__ );
		# If setting to site default values, erase the row if there is one...
		if( $row && $reset ) {
			$dbw->delete( 'flaggedpage_config',
				array( 'fpc_page_id' => $this->page->getArticleID() ),
				__METHOD__ );
			$changed = ($dbw->affectedRows() != 0); // did this do anything?
		# Otherwise, add a row unless we are just setting it as the site default, or it is the same the current one...
		} else if( $this->select !=0 || $this->override != $wgFlaggedRevsOverride ) {
			if( !$row || $row->fpc_select != $this->select || $row->fpc_override != $this->override || $row->fpc_expiry != $expiry ) {
				$changed = true;
				$dbw->replace( 'flaggedpage_config',
					array( 'PRIMARY' ),
					array( 'fpc_page_id' => $this->page->getArticleID(),
						'fpc_select'   => $this->select,
						'fpc_override' => $this->override,
						'fpc_expiry'   => $expiry ),
					__METHOD__ );
			}
		}
		# Log if changed
		# @FIXME: do this better
		if( $changed ) {
			global $wgContLang;

			$log = new LogPage( 'stable' );
			# ID, accuracy, depth, style
			$set = array();
			$set[] = wfMsgForContent( "stabilization-sel-short" ) . wfMsgForContent( 'colon-separator' ) .
				wfMsgForContent("stabilization-sel-short-{$this->select}");
			$set[] = wfMsgForContent( "stabilization-def-short" ) . wfMsgForContent( 'colon-separator' ) .
				wfMsgForContent("stabilization-def-short-{$this->override}");
			$settings = '[' . implode(', ',$set). ']';

			$reason = '';
			# Append comment with settings (other than for resets)
			if( !$reset ) {
				$reason = $this->reason ? "{$this->reason} $settings" : "$settings";

				$encodedExpiry = Block::encodeExpiry($expiry, $dbw );
				if( $encodedExpiry != 'infinity' ) {
					$expiry_description = ' (' . wfMsgForContent( 'stabilize-expiring',
						$wgContLang->timeanddate($expiry, false, false) ,
						$wgContLang->date($expiry, false, false) ,
						$wgContLang->time($expiry, false, false) ) . ')';
					$reason .= "$expiry_description";
				}
			}

			if( $reset ) {
				$log->addEntry( 'reset', $this->page, $reason );
				$type = "stable-logentry2";
			} else {
				$log->addEntry( 'config', $this->page, $reason );
				$type = "stable-logentry";
			}
			$comment = $wgContLang->ucfirst( wfMsgForContent( $type, $this->page->getPrefixedText() ) );
			if( $reason ) {
				$comment .= ": $reason";
			}

			$id = $this->page->getArticleId();
			$latest = $this->page->getLatestRevID( GAID_FOR_UPDATE );
			# Insert a null revision
			$nullRevision = Revision::newNullRevision( $dbw, $id, $comment, true );
			$nullRevId = $nullRevision->insertOn( $dbw );
			# Update page record and touch page
			$article = new Article( $this->page );
			$article->updateRevisionOn( $dbw, $nullRevision, $latest );
			wfRunHooks( 'NewRevisionFromEditComplete', array($article, $nullRevision, $latest) );
		}
		# Update the links tables as the stable version may now be the default page...
		FlaggedRevs::titleLinksUpdate( $this->page );

		if( $this->watchThis ) {
			$wgUser->addWatch( $this->page );
		} else {
			$wgUser->removeWatch( $this->page );
		}

		$query = '';
		# Take the user to the diff to make sure an outdated version isn't
		# being set at the default. This is really an issue with configs
		# that only let certain pages be reviewed.
		if( $this->select == FLAGGED_VIS_NORMAL ) {
			$frev = FlaggedRevision::newFromStable( $this->page, FR_MASTER );
			if( $frev && $frev->getRevId() != $latest ) {
				$query = "oldid={$frev->getRevId()}&diff=cur&diffonly=0"; // override diff-only
			}
		}
		$wgOut->redirect( $this->page->getFullUrl( $query ) );

		return true;
	}
}
