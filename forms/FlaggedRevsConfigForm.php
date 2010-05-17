<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}
/**
 * Class containing stability settings form business logic
 *
 * Note: handleParams() must be called after the user parameters are set
 */
abstract class FlaggedRevsConfigForm
{
	/* Form parameters which can be user given */
	protected $target = null; # Target page text
	protected $watchThis = null; # Watch checkbox
	protected $reviewThis = null; # Auto-review option...
	protected $reason = ''; # Custom/extra reason
	protected $reasonSelection = ''; # Reason dropdown key
	protected $expiry = ''; # Custom expiry
	protected $expirySelection = ''; # Expiry dropdown key
	protected $override = -1; # Default version
	protected $autoreview = ''; # Autoreview restrictions...
	protected $wasPosted = false; # POST request?

	protected $page = false; # Target page obj (of $target)
	protected $oldConfig = array(); # Old page config
	protected $oldExpiry = ''; # Old page config expiry (GMT)
	protected $isAllowed = null; # $wgUser can submit?

	protected $submitLock = 1; # Disallow bad submissions

	public function getTarget() {
		return $this->target;
	}

	public function setTarget( $value ) {
		$this->trySet( $this->target, $value );
	}

	public function getWatchThis() {
		return $this->watchThis;
	}

	public function setWatchThis( $value ) {
		$this->trySet( $this->watchThis, $value );
	}

	public function getReason() {
		return $this->reason;
	}

	public function setReason( $value ) {
		$this->trySet( $this->reason, $value );
	}

	public function getReasonSelection() {
		return $this->reasonSelection;
	}

	public function setReasonSelection( $value ) {
		$this->trySet( $this->reasonSelection, $value );
	}

	public function getExpiry() {
		return $this->expiry;
	}
	
	public function setExpiry( $value ) {
		$this->trySet( $this->expiry, $value );
	}

	public function getExpirySelection() {
		return $this->expirySelection;
	}

	public function setExpirySelection( $value ) {
		$this->trySet( $this->expirySelection, $value );
	}

	public function getAutoreview() {
		return $this->autoreview;
	}	
	
	public function setAutoreview( $value ) {
		$this->trySet( $this->autoreview, $value );
	}

	public function setWasPosted( $value ) {
		$this->trySet( $this->wasPosted, $value );
	}

	/**
	* Set a member field to a value if the fields are unlocked
	*/
	protected function trySet( &$field, $value ) {
		if ( $this->submitLock ) {
			$field = $value; // submission locked => still allowing input
		} else {
			throw new MWException( "FlaggedRevsConfigForm fields cannot be set after validation.\n");
		}
	}

	/**
	* Verify and clean up parameters and preload data from DB.
	* Locks the member fields from being set.
	*
	* Note: some items may not all be set on failure.
	* @return mixed (true on success, error string on failure)
	*/
	public function handleParams() {
		$this->page = Title::newFromURL( $this->target );
		if ( is_null( $this->page ) ) {
			return 'stabilize_page_invalid'; // page title is invalid
		} elseif ( !$this->page->exists() ) {
			return 'stabilize_page_notexists'; // page must exist
		} elseif ( !FlaggedRevs::inReviewNamespace( $this->page ) ) {
			return 'stabilize_page_unreviewable';
		}
		# Get the current page config and GMT expiry
		$this->oldConfig = FlaggedRevs::getPageVisibilitySettings( $this->page, FR_MASTER );
		$this->oldExpiry = $this->oldConfig['expiry'] === 'infinity'
			? 'infinite'
			: wfTimestamp( TS_RFC2822, $this->oldConfig['expiry'] );
		# Handle views (GET)
		if ( !$this->wasPosted ) {
			# Fill in existing settings
			$ok = $this->preloadSettings();
		# Handle submission data (POST)
		} else {
			$ok = $this->handlePostedParams();
		}
		if ( $ok === true && $this->wasPosted ) {
			$this->submitLock = 0; // allow calling of submit()
		}
		return $ok;
	}

	/*
	* Preload existing page settings
	* @return mixed (true on success, error string on failure)
	*/
	protected function preloadSettings() {
		return true;
	}

	/*
	* Verify and clean up parameters from POST request.
	* @return mixed (true on success, error string on failure)
	*/
	protected function handlePostedParams() {
		return true;
	}

	/*
	* Gets the target page Obj
	* @return mixed (Title or null)
	*/
	public function getPage() {
		if ( $this->page === false ) {
			$this->handleParams(); // handleParams() not called first
		}
		return $this->page;
	}

	/*
	* Gets the current config expiry in GMT (or 'infinite')
	* @return string
	*/
	public function getOldExpiryGMT() {
		if ( $this->page === false ) {
			$this->handleParams(); // handleParams() not called first
		}
		return $this->oldExpiry;
	}

	/*
	* Can the user change the settings for this page?
	* Note: if the current autoreview restriction is too high for this user
	*		then this will return false. Use for form selectors.
	* @return bool
	*/
	public function isAllowed() {
		if ( $this->page === false ) {
			$this->handleParams(); // handleParams() not called first
		}
		if ( $this->isAllowed === null ) {
			# Users who cannot edit or review the page cannot set this
			$this->isAllowed = ( $this->page
				&& $this->page->userCan( 'stablesettings' )
				&& $this->page->userCan( 'edit' )
				&& $this->page->userCan( 'review' )
			);
		}
		return $this->isAllowed;
	}

	/**
	* Verify and clean up parameters and preload data from DB.
	* Note: some items may not all be set on failure.
	* @return mixed (true on success, error string on failure)
	*/
	public function submit() {
		global $wgUser;
		if ( $this->submitLock ) {
			throw new MWException( "FlaggedRevsConfigForm::submit() called either " .
				"without calling handleParams() or called in spite of its failure.\n" );
		}
		# Are we are going back to site defaults?
		$reset = $this->newConfigIsReset();
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
			# Update logs and make a null edit
			$nullRev = $this->updateLogsAndHistory( $reset );
			# Null edit may have been autoreviewed already
			$frev = FlaggedRevision::newFromTitle( $this->page, $nullRev->getId(), FR_MASTER );
			# We may need to invalidate the page links after changing the stable version.
			# Only do so if not already done, such as by an auto-review of the null edit.
			$invalidate = !$frev;
			# Check if this null edit is to be reviewed...
			if ( !$frev && $this->reviewThis ) {
				$flags = null;
				$article = new Article( $this->page );
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
		$this->updateWatchlist();
		# Take this opportunity to purge out expired configurations
		FlaggedRevs::purgeExpiredConfigurations();
		return true;
	}

	/*
	* Do history & log updates:
	* (a) Add a new stability log entry
	* (b) Add a null edit like the log entry
	* @return Revision
	*/
	protected function updateLogsAndHistory( $reset ) {
		global $wgContLang;
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
			$settings = ''; // no level, expiry info
		} else {
			$params = $this->getLogParams();
			$log->addEntry( 'config', $this->page, $this->reason,
				FlaggedRevsLogs::collapseParams( $params ) );
			$type = "stable-logentry-config";
			// Settings message in text form (e.g. [x=a,y=b,z])
			$settings = FlaggedRevsLogs::stabilitySettings( $params, true /*content*/ );
		}
		# Build null-edit comment...<action: reason [settings] (expiry)>
		$comment = $wgContLang->ucfirst(
			wfMsgForContent( $type, $this->page->getPrefixedText() ) ); // action
		if ( $this->reason != '' ) {
			$comment .= wfMsgForContent( 'colon-separator' ) . $this->reason; // add reason
		}
		if ( $settings != '' ) {
			$comment .= " {$settings}"; // add settings
		}
		# Insert a null revision...
		$dbw = wfGetDB( DB_MASTER );
		$nullRev = Revision::newNullRevision( $dbw, $article->getId(), $comment, true );
		$nullRevId = $nullRev->insertOn( $dbw );
		# Update page record and touch page
		$article->updateRevisionOn( $dbw, $nullRev, $latest );
		wfRunHooks( 'NewRevisionFromEditComplete', array( $article, $nullRev, $latest ) );
		# Return null Revision object for autoreview check
		return $nullRev;
	}

	/*
	* Checks if new config is the same as the site default
	* @return bool
	*/
	protected function newConfigIsReset() {
		return false;
	}

	/*
	* Get assoc. array of log params
	* @return array
	*/
	protected function getLogParams() {
		return array();
	}

	/*
	* (a) Watch page if $watchThis is true
	* (b) Unwatch if $watchThis is false
	*/
	protected function updateWatchlist() {
		global $wgUser;
		# Apply watchlist checkbox value (may be NULL)
		if ( $this->watchThis === true ) {
			$wgUser->addWatch( $this->page );
		} elseif ( $this->watchThis === false ) {
			$wgUser->removeWatch( $this->page );
		}
	}

	protected function loadExpiry() {
		# Custom expiry takes precedence
		if ( $this->expiry == '' ) {
			$this->expiry = $this->expirySelection;
			if ( $this->expiry == 'existing' ) {
				$this->expiry = $this->oldExpiry;
			}
		}
	}

	protected function loadReason() {
		# Custom reason takes precedence
		if ( $this->reasonSelection != 'other' ) {
			$comment = $this->reasonSelection; // start with dropdown reason
			if ( $this->reason != '' ) {
				# Append custom reason
				$comment .= wfMsgForContent( 'colon-separator' ) . $this->reason;
			}
		} else {
			$comment = $this->reason; // just use custom reason
		}
		$this->reason = $comment;
	}

	// Same JS used for expiry for either $wgFlaggedRevsProtection case
	public static function addProtectionJS() {
		global $wgOut;
		$wgOut->addScript(
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
}

// Assumes $wgFlaggedRevsProtection is off
class PageStabilityForm extends FlaggedRevsConfigForm {
	/* Form parameters which can be user given */
	public $select = -1; # Precedence

	public function getReviewThis() {
		return $this->reviewThis;
	}

	public function setReviewThis( $value ) {
		$this->trySet( $this->reviewThis, $value );
	}

	public function getPrecedence() {
		return $this->select;
	}

	public function setPrecedence( $value ) {
		$this->trySet( $this->select, $value );
	}	

	public function getOverride() {
		return $this->override;
	}

	public function setOverride( $value ) {
		$this->trySet( $this->override, $value );
	}

	public function handlePostedParams() {
		$this->loadReason();
		$this->loadExpiry();
		$this->override = $this->override ? 1 : 0; // default version settings is 0 or 1
		if ( !FlaggedRevs::isValidPrecedence( $this->select ) ) {
			return 'stabilize_invalid_precedence'; // invalid precedence value
		}
		// Check autoreview restriction setting
		if ( !FlaggedRevs::userCanSetAutoreviewLevel( $this->autoreview ) ) {
			return 'stabilize_invalid_autoreview'; // invalid value
		}
		return true;
	}

	protected function getLogParams() {
		return array(
			'override'   => $this->override,
			'autoreview' => $this->autoreview,
			'expiry'     => $this->expiry, // TS_MW/infinity
			'precedence' => $this->select
		);
	}

	// Return current config array
	public function getOldConfig() {
		if ( $this->page === false ) {
			$this->handleParams(); // handleParams() not called first
		}
		return $this->oldConfig;
	}

	protected function preloadSettings() {
		# Get visiblity settings...
		$this->select = $this->oldConfig['select'];
		$this->override = $this->oldConfig['override'];
		# Get autoreview restrictions...
		$this->autoreview = $this->oldConfig['autoreview'];
		return true;
	}

	// returns whether row changed
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
			# Get current config...
			$oldRow = $dbw->selectRow( 'flaggedpage_config',
				array( 'fpc_select', 'fpc_override', 'fpc_level', 'fpc_expiry' ),
				array( 'fpc_page_id' => $this->page->getArticleID() ),
				__METHOD__,
				'FOR UPDATE'
			);
			# Check if this is not the same config as the existing row (if any)
			$changed = self::configIsDifferent( $oldRow,
				$this->select, $this->override, $this->autoreview, $dbExpiry );
			# If the new config is different, replace the old row...
			if ( $changed ) {
				$dbw->replace( 'flaggedpage_config',
					array( 'PRIMARY' ),
					array(
						'fpc_page_id'  => $this->page->getArticleID(),
						'fpc_select'   => (int)$this->select,
						'fpc_override' => (int)$this->override,
						'fpc_level'    => $this->autoreview,
						'fpc_expiry'   => $dbExpiry
					),
					__METHOD__
				);
			}
		}
		return $changed;
	}

	protected function newConfigIsReset() {
		return ( $this->select == FlaggedRevs::getPrecedence()
			&& $this->override == FlaggedRevs::isStableShownByDefault()
			&& $this->autoreview == '' );
	}

	// Checks if new config is different than the existing row
	protected function configIsDifferent( $oldRow, $select, $override, $autoreview, $dbExpiry ) {
		if( !$oldRow ) {
			return true; // no previous config
		}
		return ( $oldRow->fpc_select != $select // ...precedence changed, or...
			|| $oldRow->fpc_override != $override // ...override changed, or...
			|| $oldRow->fpc_level != $autoreview // ...autoreview level changed, or...
			|| $oldRow->fpc_expiry != $dbExpiry // ...expiry changed
		);
	}
}

// Assumes $wgFlaggedRevsProtection is on
class PageStabilityProtectForm extends FlaggedRevsConfigForm {
	public function handlePostedParams() {
		$this->loadReason();
		$this->loadExpiry();
		# Autoreview only when protecting currently unprotected pages
		$this->reviewThis = ( FlaggedRevs::getProtectionLevel( $this->oldConfig ) == 'none' );
		# Check autoreview restriction setting
		if ( !FlaggedRevs::userCanSetAutoreviewLevel( $this->autoreview ) ) {
			return 'stabilize_invalid_level'; // invalid value
		}
		# Autoreview restriction => use stable
		# No autoreview restriction => site default
		$this->override = ( $this->autoreview != '' )
			? 1 // edits require review before being published
			: (int)FlaggedRevs::isStableShownByDefault(); // site default
		# Check that settings are a valid protection level...
		$newConfig = array(
			'override'   => $this->override,
			'autoreview' => $this->autoreview
		);
		if ( FlaggedRevs::getProtectionLevel( $newConfig ) == 'invalid' ) {
			return 'stabilize_invalid_level'; // double-check configuration
		}
		return true;
	}

	// Doesn't include 'precedence'; checked in FlaggedRevsLogs
	protected function getLogParams() {
		return array(
			'override'   => $this->override, // in case of site changes
			'autoreview' => $this->autoreview,
			'expiry'     => $this->expiry // TS_MW/infinity
		);
	}

	protected function preloadSettings() {
		# Get autoreview restrictions...
		$this->autoreview = $this->oldConfig['autoreview'];
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
			# Get current config...
			$oldRow = $dbw->selectRow( 'flaggedpage_config',
				array( 'fpc_override', 'fpc_level', 'fpc_expiry' ),
				array( 'fpc_page_id' => $this->page->getArticleID() ),
				__METHOD__,
				'FOR UPDATE'
			);
			# Check if this is not the same config as the existing row (if any)
			$changed = self::configIsDifferent( $oldRow,
				$this->override, $this->autoreview, $dbExpiry );
			# If the new config is different, replace the old row...
			if ( $changed ) {
				$dbw->replace( 'flaggedpage_config',
					array( 'PRIMARY' ),
					array(
						'fpc_page_id'  => $this->page->getArticleID(),
						'fpc_select'   => -1, // ignored
						'fpc_override' => (int)$this->override,
						'fpc_level'    => $this->autoreview,
						'fpc_expiry'   => $dbExpiry
					),
					__METHOD__
				);
			}
		}
		return $changed;
	}

	protected function newConfigIsReset() {
		# For protection config, just ignore the fpc_select column
		return ( $this->autoreview == '' );
	}

	// Checks if new config is different than the existing row
	protected function configIsDifferent( $oldRow, $override, $autoreview, $dbExpiry ) {
		if ( !$oldRow ) {
			return true; // no previous config
		}
		# For protection config, just ignore the fpc_select column
		return ( $oldRow->fpc_override != $override // ...override changed, or...
			|| $oldRow->fpc_level != $autoreview // ...autoreview level changed, or...
			|| $oldRow->fpc_expiry != $dbExpiry // ...expiry changed
		);
	}
}