<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

class Stabilization extends UnlistedSpecialPage
{
	public function __construct() {
		parent::__construct( 'Stabilization', 'stablesettings' );
    }

	public function execute( $par ) {
		global $wgRequest, $wgUser, $wgOut;
		# Check user token
		$confirm = $wgRequest->wasPosted() &&
			$wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );
		# Allow unprivileged users to at least view the settings
		$this->isAllowed = $wgUser->isAllowed( 'stablesettings' );
		# Let anyone view, but not submit...
		if ( $wgRequest->wasPosted() ) {
			if ( $wgUser->isBlocked( !$confirm ) ) {
				return $wgOut->blockedPage();
			} elseif ( !$this->isAllowed ) {
				return $wgOut->permissionRequired( 'stablesettings' );
			} elseif ( wfReadOnly() ) {
				return $wgOut->readOnlyPage();
			}
		}
		# Set page title
		$this->setHeaders();
		
		$this->skin = $wgUser->getSkin();
		# Our target page
		$this->target = $wgRequest->getText( 'page', $par );
		# Watch checkbox
		$this->watchThis = (bool)$wgRequest->getCheck( 'wpWatchthis' );
		# Reason
		$this->reason = $wgRequest->getText( 'wpReason' );
		$this->reasonSelection = $wgRequest->getText( 'wpReasonSelection' );
		# Expiry
		$this->expiry = $wgRequest->getText( 'mwStabilize-expiry' );
		$this->expirySelection = $wgRequest->getVal( 'wpExpirySelection' );
		# Precedence
		$this->select = $wgRequest->getInt( 'wpStableconfig-select' );
		$this->override = (int)$wgRequest->getBool( 'wpStableconfig-override' );
		# Get autoreview restrictions...
		$this->autoreview = $wgRequest->getVal( 'mwProtect-level-autoreview' );
		# Get auto-review option...
		$this->reviewThis = $wgRequest->getBool( 'wpReviewthis', true );
		$this->wasPosted = $wgRequest->wasPosted();

		# Fill in & validate some parameters
		$isValid = $this->handleParams();

		# We need a page...
		if ( is_null( $this->page ) ) {
			return $wgOut->showErrorPage( 'notargettitle', 'notargettext' );
		} elseif ( !$this->page->exists() ) {
			return $wgOut->addHTML( wfMsgExt( 'stabilization-notexists', array( 'parseinline' ),
				$this->page->getPrefixedText() ) );
		} elseif ( !FlaggedRevs::inReviewNamespace( $this->page ) ) {
			return $wgOut->addHTML( wfMsgExt( 'stabilization-notcontent', array( 'parseinline' ),
				$this->page->getPrefixedText() ) );
		}

		# Users who cannot edit or review the page cannot set this
		if ( $this->isAllowed && !( $this->page->userCan( 'edit' )
			&& $this->page->userCan( 'review' ) ) )
		{
			$this->isAllowed = false;
		}
		# Disable some elements as needed
		$this->disabledAttrib = !$this->isAllowed ?
			array( 'disabled' => 'disabled' ) : array();
		# Show form or submit...
		if ( $this->isAllowed && $isValid && $confirm ) {
			$status = $this->submit();
			if ( $status === true ) {
				$wgOut->redirect( $this->page->getFullUrl() );
			} else {
				$this->showSettings( wfMsg( $status ) );
			}
		} else {
			$this->showSettings();
		}
	}
	
	/**
	* Fetch and check parameters. Items may not all be set if false is returned.
	* @return bool success
	*/
	public function handleParams() {
		# Our target page
		$this->page = Title::newFromURL( $this->target );
		# We need a page...
		if ( is_null( $this->page ) ) {
			return false; // can't continue
		}
		# Get old config
		$this->config = FlaggedRevs::getPageVisibilitySettings( $this->page, FR_MASTER );
		# Make user readable date for GET requests
		$this->oldExpiry = $this->config['expiry'] !== 'infinity' ?
			wfTimestamp( TS_RFC2822, $this->config['expiry'] ) : 'infinite';
		# If not posted, then fill in existing values/defaults
		if ( !$this->wasPosted ) {
			# Get visiblity settings...
			$this->select = $this->config['select'];
			$this->override = $this->config['override'];
			# Get autoreview restrictions...
			$this->autoreview = $this->config['autoreview'];
		# Handle submission data
		} else {
			// Custom expiry takes precedence
			$this->expiry = strlen( $this->expiry ) ?
				$this->expiry : $this->expirySelection;
			if ( $this->expiry == 'existing' ) {
				$this->expiry = $this->oldExpiry;
			}
			// Custom reason takes precedence
			if ( $this->reasonSelection != 'other' ) {
				$comment = $this->reasonSelection; // start with dropdown reason
				if ( $this->reason != '' ) {
					// Append custom reason
					$comment .= wfMsgForContent( 'colon-separator' ) . $this->reason;
				}
			} else {
				$comment = $this->reason; // just use custom reason
			}
			$this->reason = $comment;
			// Protection level case...
			if( FlaggedRevs::useProtectionLevels() ) {
				# Autoreview restriction => use stable
				# No autoreview restriction => site default
				$this->override = ($this->autoreview != '')
					? 1 // edits require review before being published
					: (int)FlaggedRevs::isStableShownByDefault();
				# Leave the selection precedence alone
				$this->select = FlaggedRevs::getPrecedence();
			// General case...
			} else {
				if ( $this->override !== 0 && $this->override !== 1 ) {
					return false; // default version settings is 0 or 1
				}
				if ( !FlaggedRevs::isValidPrecedence( $this->select ) ) {
					return false; // invalid precedence value
				}
			}
			// Check autoreview setting
			if ( !self::userCanSetAutoreviewLevel( $this->autoreview ) ) {
				return false; // invalid value
			}
		}
		# If we use protection levels, check that settings match one...
		if ( FlaggedRevs::useProtectionLevels() ) {
			$config = array(
				'select' 	 => $this->select,
				'override'   => $this->override,
				'autoreview' => $this->autoreview
			);
			if ( FlaggedRevs::getProtectionLevel( $config ) == 'invalid' ) {
				return false; // this is not a valid configuration
			}
		}
		return true;
	}

	/**
	* Check if a user can set the autoreview restiction level to $right
	* @param string $right the level
	* @returns bool
	*/
	public static function userCanSetAutoreviewLevel( $right ) {
		global $wgUser;
		if ( $right == '' ) {
			return true; // no restrictions (none)
		}
		if ( !in_array( $right, FlaggedRevs::getRestrictionLevels() ) ) {
			return false; // invalid restriction level
		}
		# Don't let them choose levels above their own rights
		if ( $right == 'sysop' ) {
			// special case, rewrite sysop to protect and editprotected
			if ( !$wgUser->isAllowed( 'protect' ) && !$wgUser->isAllowed( 'editprotected' ) ) {
				return false;
			}
		} else if ( !$wgUser->isAllowed( $right ) ) {
			return false;
		}
		return true;
	}

	protected function showSettings( $err = null ) {
		global $wgOut, $wgLang, $wgUser;
		# Add any error messages
		if ( "" != $err ) {
			$wgOut->setSubtitle( wfMsgHtml( 'formerror' ) );
			$wgOut->addHTML( "<p class='error'>{$err}</p>\n" );
		}
		# Add header text
		if ( !$this->isAllowed ) {
			$form = wfMsgExt( 'stabilization-perm', array( 'parse' ),
				$this->page->getPrefixedText() );
		} else {
			$form = wfMsgExt( 'stabilization-text', array( 'parse' ),
				$this->page->getPrefixedText() );
		}
		# Add some script for expiry dropdowns
		$wgOut->addScript(
			"<script type=\"text/javascript\">
				function updateStabilizationDropdowns() {
					val = document.getElementById('mwExpirySelection').value;
					if( val == 'existing' )
						document.getElementById('mwStabilize-expiry').value = " .
						Xml::encodeJsVar( $this->oldExpiry ) . ";
					else if( val != 'othertime' )
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
		$showProtectOptions = ( $scExpiryOptions !== '-' && $this->isAllowed );
		# Add the current expiry as an option
		$expiryFormOptions = '';
		if ( $this->config['expiry'] && $this->config['expiry'] != 'infinity' ) {
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
		# Add custom levels (from MediaWiki message)
		foreach ( explode( ',', $scExpiryOptions ) as $option ) {
			if ( strpos( $option, ":" ) === false ) {
				$show = $value = $option;
			} else {
				list( $show, $value ) = explode( ":", $option );
			}
			$show = htmlspecialchars( $show );
			$value = htmlspecialchars( $value );
			$expiryFormOptions .= Xml::option( $show, $value,
				$this->config['expiry'] === $value ) . "\n";
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
			Xml::radioLabel( wfMsg( 'stabilization-select3' ), 'wpStableconfig-select',
				FLAGGED_VIS_PRISTINE, 'stable-select3', FLAGGED_VIS_PRISTINE == $this->select,
				$this->disabledAttrib ) . '<br />' . "\n" .
			Xml::radioLabel( wfMsg( 'stabilization-select1' ), 'wpStableconfig-select',
				FLAGGED_VIS_QUALITY, 'stable-select1', FLAGGED_VIS_QUALITY == $this->select,
				$this->disabledAttrib ) . '<br />' . "\n" .
			Xml::radioLabel( wfMsg( 'stabilization-select2' ), 'wpStableconfig-select',
				FLAGGED_VIS_LATEST, 'stable-select2', FLAGGED_VIS_LATEST == $this->select,
				$this->disabledAttrib ) . '<br />' . "\n" .
			Xml::closeElement( 'fieldset' );
		# Add autoreview restriction select
		$form .= Xml::fieldset( wfMsg( 'stabilization-restrict' ), false ) .
			$this->buildSelector( $this->autoreview ) .
			Xml::closeElement( 'fieldset' ) .

			Xml::fieldset( wfMsg( 'stabilization-leg' ), false ) .
			Xml::openElement( 'table' );
		# Add expiry dropdown
		if ( $showProtectOptions && $this->isAllowed ) {
			$form .= "
				<tr>
					<td class='mw-label'>" .
						Xml::label( wfMsg( 'stabilization-expiry' ), 'mwExpirySelection' ) .
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
					Xml::label( wfMsg( 'stabilization-othertime' ), 'mwStabilize-expiry' ) .
				'</td>
				<td class="mw-input">' .
					Xml::input( "mwStabilize-expiry", 50,
						$this->expiry ? $this->expiry : $this->oldExpiry, $attribs ) .
				'</td>
			</tr>';
		# Add comment input and submit button
		if ( $this->isAllowed ) {
			$watchLabel = wfMsgExt( 'watchthis', array( 'parseinline' ) );
			$watchAttribs = array( 'accesskey' => wfMsg( 'accesskey-watch' ),
				'id' => 'wpWatchthis' );
			$watchChecked = ( $wgUser->getOption( 'watchdefault' )
				|| $this->page->userIsWatching() );
			$reviewLabel = wfMsgExt( 'stabilization-review', array( 'parseinline' ) );

			$form .= ' <tr>
					<td class="mw-label">' .
						xml::label( wfMsg( 'stabilization-comment' ), 'wpReasonSelection' ) .
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
						Xml::check( 'wpReviewthis', $this->reviewThis,
							array( 'id' => 'wpReviewthis' ) ) .
						"<label for='wpReviewthis'>{$reviewLabel}</label>" .
						'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
						Xml::check( 'wpWatchthis', $watchChecked, $watchAttribs ) .
						"<label for='wpWatchthis'" . $this->skin->tooltipAndAccesskey( 'watch' ) .
							">{$watchLabel}</label>" .
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

		$wgOut->addHTML( Xml::element( 'h2', null,
			htmlspecialchars( LogPage::logName( 'stable' ) ) ) );
		LogEventsList::showLogExtract( $wgOut, 'stable', $this->page->getPrefixedText() );
	}
	
	protected function buildSelector( $selected ) {
		global $wgUser;
		$allowedLevels = array();
		$levels = FlaggedRevs::getRestrictionLevels();
		array_unshift( $levels, '' ); // Add a "none" level
		foreach ( $levels as $key ) {
			# Don't let them choose levels they can't set, 
			# but *show* them all when the form is disabled.
			if ( $this->isAllowed && !self::userCanSetAutoreviewLevel( $key ) ) {
				continue;
			}
			$allowedLevels[] = $key;
		}
		$id = 'mwProtect-level-autoreview';
		$attribs = array(
			'id' => $id,
			'name' => $id,
			'size' => count( $allowedLevels ),
		) + $this->disabledAttrib;

		$out = Xml::openElement( 'select', $attribs );
		foreach ( $allowedLevels as $key ) {
			$out .= Xml::option( $this->getOptionLabel( $key ), $key, $key == $selected );
		}
		$out .= Xml::closeElement( 'select' );
		return $out;
	}

	/**
	 * Prepare the label for a protection selector option
	 *
	 * @param string $permission Permission required
	 * @return string
	 */
	protected function getOptionLabel( $permission ) {
		if ( $permission == '' ) {
			return wfMsg( 'stabilization-restrict-none' );
		} else {
			$key = "protect-level-{$permission}";
			$msg = wfMsg( $key );
			if ( wfEmptyMsg( $key, $msg ) ) {
				$msg = wfMsg( 'protect-fallback', $permission );
			}
			return $msg;
		}
	}

	public function submit() {
		global $wgUser, $wgContLang;
		# Take this opportunity to purge out expired configurations
		FlaggedRevs::purgeExpiredConfigurations();
		# Are we are going back to site defaults?
		$reset = self::configIsReset( $this->select, $this->override, $this->autoreview );
		# Parse and cleanup the expiry time given...
		if ( $reset || $this->expiry == 'infinite' || $this->expiry == 'indefinite' ) {
			$this->expiry = Block::infinity(); // normalize to 'infinity'
		} else {
			# Convert GNU-style date, on error returns -1 for PHP <5.1 and false for PHP >=5.1
			$this->expiry = strtotime( $this->expiry );
			if ( $this->expiry < 0 || $this->expiry === false ) {
				return 'stabilize_expiry_invalid';
			}
			# Convert date to MW timestamp format
			$this->expiry = wfTimestamp( TS_MW, $this->expiry );
			if ( $this->expiry < wfTimestampNow() ) {
				return 'stabilize_expiry_old';
			}
		}
		# Update the DB row with the new config...
		$changed = $this->updateConfigRow( $reset );
		# Log if this actually changed anything...
		if ( $changed ) {
			$article = new Article( $this->page );
			$latest = $this->page->getLatestRevID( GAID_FOR_UPDATE );
			# Config may have changed to allow stable versions.
			# Refresh tracking to account for any hidden reviewed versions...
			$frev = FlaggedRevision::newFromStable( $this->page, FR_MASTER );
			if ( $frev ) {
				FlaggedRevs::updateStableVersion( $article, $frev->getRevision(), $latest );
			} else {
				FlaggedRevs::clearTrackingRows( $article->getId() );
			}
			# Insert stability log entry...
			$log = new LogPage( 'stable' );
			if ( $reset ) {
				$log->addEntry( 'reset', $this->page, $this->reason );
				$type = "stable-logentry-reset";
			} else {
				$params = array(
					'override'   => $this->override,
					'autoreview' => $this->autoreview,
					'expiry'     => $this->expiry // TS_MW/infinity
				);
				// Precedence unchanged by protection (stabilityLogLinks checks this)
				if( !FlaggedRevs::useProtectionLevels() ) {
					$params['precedence'] = $this->select;
				}
				$log->addEntry( 'config', $this->page, $this->reason,
					FlaggedRevsLogs::collapseParams( $params ) );
				$type = "stable-logentry-config";
			}
			# Build null-edit comment...<action: reason [settings] (expiry)>
			$comment = $wgContLang->ucfirst(
				wfMsgForContent( $type, $this->page->getPrefixedText() ) ); // action
			if ( $this->reason != '' ) {
				$comment .= wfMsgForContent( 'colon-separator' ) . $this->reason; // add reason
			}
			$settings = FlaggedRevsLogs::stabilitySettings( $params, true /*content*/ );
			if ( $settings != '' ) {
				$comment .= " {$settings}"; // add settings
			}
			# Insert a null revision...
			$dbw = wfGetDB( DB_MASTER );
			$nullRev = Revision::newNullRevision( $dbw, $article->getId(), $comment, true );
			$nullRevId = $nullRev->insertOn( $dbw );
			# Update page record and touch page
			$article->updateRevisionOn( $dbw, $nullRev, $latest );
			wfRunHooks( 'NewRevisionFromEditComplete',
				array( $article, $nullRev, $latest ) );

			# Null edit may have been autoreviewed already
			$frev = FlaggedRevision::newFromTitle( $this->page, $nullRevId, FR_MASTER );
			# We may need to invalidate the page links after changing the stable version.
			# Only do so if not already done, such as by an auto-review of the null edit.
			$invalidate = !$frev;
			# Check if this null edit is to be reviewed...
			if ( !$frev && $this->reviewThis ) {
				$flags = null;
				# Review this revision of the page...
				$ok = FlaggedRevs::autoReviewEdit(
					$article, $wgUser, $nullRev->getText(), $nullRev, $flags, true );
				if( $ok ) {
					FlaggedRevs::markRevisionPatrolled( $nullRev ); // reviewed -> patrolled
					$invalidate = false; // links invalidated (with auto-reviewed)
				}
			}
			# Update the links tables as the stable version may now be the default page...
			if ( $invalidate ) {
				FlaggedRevs::titleLinksUpdate( $this->page );
			}
		}
		# Apply watchlist checkbox value (may be NULL)
		if ( $this->watchThis === true ) {
			$wgUser->addWatch( $this->page );
		} else if ( $this->watchThis === false ) {
			$wgUser->removeWatch( $this->page );
		}
		return true;
	}

	protected function updateConfigRow( $reset ) {
		$changed = false;
		$dbw = wfGetDB( DB_MASTER );
		# If setting to site default values and there is a row then erase it
		if ( $reset ) {
			$dbw->delete( 'flaggedpage_config',
				array( 'fpc_page_id' => $this->page->getArticleID() ),
				__METHOD__
			);
			$changed = ( $dbw->affectedRows() != 0 ); // did this do anything?
		# Otherwise, add/replace row if we are not just setting it to the site default
		} elseif ( !$reset ) {
			$dbExpiry = Block::encodeExpiry( $this->expiry, $dbw );
			$precedence = FlaggedRevs::useProtectionLevels()
				? -1 // site default; fpc_select "don't care"
				: $this->select;
			# Get current config...
			$oldRow = $dbw->selectRow( 'flaggedpage_config',
				array( 'fpc_select', 'fpc_override', 'fpc_level', 'fpc_expiry' ),
				array( 'fpc_page_id' => $this->page->getArticleID() ),
				__METHOD__,
				'FOR UPDATE'
			);
			# Check if this is not the same config as the existing row (if any)
			$changed = self::configIsDifferent( $oldRow,
				$precedence, $this->override, $this->autoreview, $dbExpiry );
			# If the new config is different, replace the old row...
			if ( $changed ) {
				$dbw->replace( 'flaggedpage_config',
					array( 'PRIMARY' ),
					array(
						'fpc_page_id'  => $this->page->getArticleID(),
						'fpc_select'   => intval( $precedence ),
						'fpc_override' => intval( $this->override ),
						'fpc_level'    => $this->autoreview,
						'fpc_expiry'   => $dbExpiry
					),
					__METHOD__
				);
			}
		}
		return $changed;
	}

	// Checks if new config is the same as the site default
	protected function configIsReset( $select, $override, $autoreview ) {
		# For protection config, just ignore the fpc_select column
		if( FlaggedRevs::useProtectionLevels() ) {
			return ( $autoreview == '' );
		} else {
			return ( $select == FlaggedRevs::getPrecedence()
				&& $override == FlaggedRevs::isStableShownByDefault()
				&& $autoreview == '' );
		}
	}

	// Checks if new config is different than the existing row
	protected function configIsDifferent( $oldRow, $select, $override, $autoreview, $dbExpiry ) {
		if( !$oldRow ) {
			return true; // no previous config
		}
		# For protection config, just ignore the fpc_select column
		if( FlaggedRevs::useProtectionLevels() ) {
			return ( $oldRow->fpc_override != $override // ...override changed, or...
				|| $oldRow->fpc_level != $autoreview // ...autoreview level changed, or...
				|| $oldRow->fpc_expiry != $dbExpiry // ...expiry changed
			);
		} else {
			return ( $oldRow->fpc_select != $select // ...precedence changed, or...
				|| $oldRow->fpc_override != $override // ...override changed, or...
				|| $oldRow->fpc_level != $autoreview // ...autoreview level changed, or...
				|| $oldRow->fpc_expiry != $dbExpiry // ...expiry changed
			);
		}
	}
}
