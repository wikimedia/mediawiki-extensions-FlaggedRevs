<?php

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\IDBAccessObject;

/**
 * Class containing stability settings form business logic
 */
abstract class PageStabilityForm extends FRGenericSubmitForm {

	/** @var Title|false Target page obj */
	protected $title = false;

	/** @var bool|null Watch checkbox */
	protected $watchThis = null;

	/** @var bool|null Auto-review option */
	protected $reviewThis = null;

	/** @var string Custom/extra reason */
	protected $reasonExtra = '';

	/** @var string Reason dropdown key */
	protected $reasonSelection = '';

	/** @var string Custom expiry */
	protected $expiryCustom = '';

	/** @var string Expiry dropdown key */
	protected $expirySelection = '';

	/** @var int Default version */
	protected $override = -1;

	/** @var string Autoreview restrictions */
	protected $autoreview = '';

	/** @var array Old page config */
	protected $oldConfig = [];

	/**
	 * @return Title|false
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param Title $value
	 */
	public function setTitle( Title $value ) {
		$this->trySet( $this->title, $value );
	}

	/**
	 * @param bool|null $value
	 */
	public function setWatchThis( $value ) {
		$this->trySet( $this->watchThis, $value );
	}

	/**
	 * @return string
	 */
	public function getReasonExtra() {
		return $this->reasonExtra;
	}

	/**
	 * @param string $value
	 */
	public function setReasonExtra( $value ) {
		$this->trySet( $this->reasonExtra, $value );
	}

	/**
	 * @return string
	 */
	public function getReasonSelection() {
		return $this->reasonSelection;
	}

	/**
	 * @param string $value
	 */
	public function setReasonSelection( $value ) {
		$this->trySet( $this->reasonSelection, $value );
	}

	/**
	 * @return string
	 */
	public function getExpiryCustom() {
		return $this->expiryCustom;
	}

	/**
	 * @param string $value
	 */
	public function setExpiryCustom( $value ) {
		$this->trySet( $this->expiryCustom, $value );
	}

	/**
	 * @return string
	 */
	public function getExpirySelection() {
		return $this->expirySelection;
	}

	/**
	 * @param string $value
	 */
	public function setExpirySelection( $value ) {
		$this->trySet( $this->expirySelection, $value );
	}

	/**
	 * @return string
	 */
	public function getAutoreview() {
		return $this->autoreview;
	}

	/**
	 * @param string $value
	 */
	public function setAutoreview( $value ) {
		$this->trySet( $this->autoreview, $value );
	}

	/**
	 * Get the final expiry, all inputs considered
	 * Note: does not check if the expiration is less than wfTimestampNow()
	 * @return string|bool 14-char timestamp or "infinity", or false if the input was invalid
	 */
	public function getExpiry() {
		$oldConfig = $this->getOldConfig();
		if ( $this->expirySelection == 'existing' ) {
			return $oldConfig['expiry'];
		} elseif ( $this->expirySelection == 'othertime' ) {
			$value = $this->expiryCustom;
		} else {
			$value = $this->expirySelection;
		}
		if ( $value == 'infinite' || $value == 'indefinite' || $value == 'infinity' ) {
			$time = 'infinity';
		} else {
			$unix = strtotime( $value );
			# On error returns -1 for PHP <5.1 and false for PHP >=5.1
			if ( !$unix || $unix === -1 ) {
				return false;
			}
			// FIXME: non-qualified absolute times are not in users
			// specified timezone and there isn't notice about it in the ui
			$time = wfTimestamp( TS_MW, $unix );
		}
		return $time;
	}

	/**
	 * Get the final reason, all inputs considered
	 * @return string
	 */
	private function getReason() {
		# Custom reason replaces dropdown
		if ( $this->reasonSelection != 'other' ) {
			$comment = $this->reasonSelection; // start with dropdown reason
			if ( $this->reasonExtra != '' ) {
				# Append custom reason
				$comment .= wfMessage( 'colon-separator' )->inContentLanguage()->text() .
					$this->reasonExtra;
			}
		} else {
			$comment = $this->reasonExtra; // just use custom reason
		}
		return $comment;
	}

	/**
	 * Check that a target is given (e.g. from GET/POST request)
	 * @return true|string true on success, error string on failure
	 */
	protected function doCheckTargetGiven() {
		if ( $this->title === null ) {
			return 'stabilize_page_invalid';
		}
		return true;
	}

	/**
	 * Check that the target page is valid
	 * @param int $flags FOR_SUBMISSION (set on submit)
	 * @return true|string true on success, error string on failure
	 */
	protected function doCheckTarget( $flags = 0 ) {
		$flgs = ( $flags & self::FOR_SUBMISSION ) ? IDBAccessObject::READ_LATEST : 0;
		if ( !$this->title->getArticleID( $flgs ) ) {
			return 'stabilize_page_notexists';
		} elseif ( !FlaggedRevs::inReviewNamespace( $this->title ) ) {
			return 'stabilize_page_unreviewable';
		}
		return true;
	}

	/**
	 * Verify and clean up parameters (e.g. from POST request)
	 * @return true|string true on success, error string on failure
	 */
	protected function doCheckParameters() {
		# Load old config settings from the primary DB
		$this->oldConfig = FRPageConfig::getStabilitySettings( $this->title, IDBAccessObject::READ_LATEST );
		if ( $this->expiryCustom != '' ) {
			// Custom expiry takes precedence
			$this->expirySelection = 'othertime';
		}
		// check other params...
		return $this->reallyDoCheckParameters();
	}

	/**
	 * @return true|string true on success, error string on failure
	 */
	protected function reallyDoCheckParameters() {
		return true;
	}

	/**
	 * Can the user change the settings for this page?
	 * Note: if the current autoreview restriction is too high for this user
	 *       then this will return false. Useful for form selectors.
	 * @return bool
	 */
	public function isAllowed() {
		# Users who cannot edit or review the page cannot set this
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		return ( $this->getTitle()
			&& $pm->userCan( 'stablesettings', $this->getUser(), $this->getTitle() )
			&& $pm->userCan( 'review', $this->getUser(), $this->getTitle() )
		);
	}

	/**
	 * Preload existing page settings (e.g. from GET request).
	 */
	protected function doPreloadParameters() {
		$oldConfig = $this->getOldConfig();
		if ( $oldConfig['expiry'] == 'infinity' ) {
			$this->expirySelection = 'infinite'; // no settings set OR indefinite
		} else {
			$this->expirySelection = 'existing'; // settings set and NOT indefinite
		}
		$this->reallyDoPreloadParameters();
	}

	/**
	 * Override this in subclasses to preload parameters other than expirySelection
	 */
	abstract protected function reallyDoPreloadParameters();

	/**
	 * Submit the form parameters for the page config to the DB.
	 *
	 * @return true|string true on success, error string on failure
	 */
	protected function doSubmit() {
		# Double-check permissions
		if ( !$this->isAllowed() ) {
			return 'stabilize_denied';
		}
		# Parse and cleanup the expiry time given...
		$expiry = $this->getExpiry();
		if ( $expiry === false ) {
			return 'stabilize_expiry_invalid';
		} elseif ( $expiry !== 'infinity' && $expiry < wfTimestampNow() ) {
			return 'stabilize_expiry_old';
		}
		# Update the DB row with the new config...
		$changed = FRPageConfig::setStabilitySettings( $this->title, $this->getNewConfig() );
		# Log if this actually changed anything...
		if ( $changed ) {
			$article = FlaggableWikiPage::newInstance( $this->title );
			if ( FlaggedRevs::useOnlyIfProtected() ) {
				# Config may have changed to allow stable versions, so refresh
				# the tracking table to account for any hidden reviewed versions...
				$frev = FlaggedRevision::determineStable( $this->title );
				if ( $frev ) {
					$article->updateStableVersion( $frev );
				} else {
					$article->clearStableVersion();
				}
			}
			# Update logs and make a null edit
			$nullRevRecord = $this->updateLogsAndHistory( $article );
			# Null edit may have been auto-reviewed already
			$frev = FlaggedRevision::newFromTitle(
				$this->title,
				$nullRevRecord->getId(),
				IDBAccessObject::READ_LATEST
			);
			$updatesDone = (bool)$frev; // stableVersionUpdates() already called?
			# Check if this null edit is to be reviewed...
			if ( $this->reviewThis && !$frev ) {
				$flags = null;
				# Review this revision of the page...
				$ok = FlaggedRevs::autoReviewEdit(
					$article,
					$this->user,
					$nullRevRecord,
					$flags
				);
				if ( $ok ) {
					FlaggedRevs::markRevisionPatrolled( $nullRevRecord ); // reviewed -> patrolled
					$updatesDone = true; // stableVersionUpdates() already called
				}
			}
			# Update page and tracking tables and clear cache.
			if ( !$updatesDone ) {
				FlaggedRevs::stableVersionUpdates( $this->title );
			}
		}
		# Apply watchlist checkbox value (may be NULL)
		$this->updateWatchlist();
		return true;
	}

	/**
	 * Do history & log updates:
	 * (a) Add a new stability log entry
	 * (b) Add a null edit like the log entry
	 * @param FlaggableWikiPage $article
	 * @return RevisionRecord
	 */
	private function updateLogsAndHistory( FlaggableWikiPage $article ) {
		$newConfig = $this->getNewConfig();
		$oldConfig = $this->getOldConfig();
		$reason = $this->getReason();

		# Insert stability log entry...
		FlaggedRevsLog::updateStabilityLog( $this->title, $newConfig, $oldConfig, $reason, $this->user );

		# Build null-edit comment...<action: reason [settings] (expiry)>
		if ( FRPageConfig::configIsReset( $newConfig ) ) {
			$type = "stable-logentry-reset";
			$settings = ''; // no level, expiry info
		} else {
			$type = "stable-logentry-config";
			// Settings message in text form (e.g. [x=a,y=b,z])
			$params = FlaggedRevsLog::stabilityLogParams( $newConfig );
			$settings = FlaggedRevsStableLogFormatter::stabilitySettings( $params, true /*content*/ );
		}
		// action
		$services = MediaWikiServices::getInstance();
		$comment = $services->getContentLanguage()->ucfirst(
			wfMessage( $type, $this->title->getPrefixedText() )->inContentLanguage()->text()
		);
		if ( $reason != '' ) {
			$comment .= wfMessage( 'colon-separator' )->inContentLanguage()->text() . $reason; // add reason
		}
		if ( $settings != '' ) {
			$comment .= ' ' . $settings;
		}

		# Insert a null revision...
		$revStore = $services->getRevisionStore();
		$dbw = $services->getConnectionProvider()->getPrimaryDatabase();
		$nullRevRecord = $revStore->newNullRevision(
			$dbw,
			$article->getTitle(),
			CommentStoreComment::newUnsavedComment( $comment ),
			true, // minor
			$this->user
		);
		$insertedRevRecord = $revStore->insertRevisionOn( $nullRevRecord, $dbw );
		# Update page record and touch page
		$oldLatest = $insertedRevRecord->getParentId();

		$article->updateRevisionOn( $dbw, $insertedRevRecord, $oldLatest );

		$tags = []; // passed by reference
		$hookContainer = $services->getHookContainer();

		$hookRunner = new FlaggedRevsHookRunner( $hookContainer );
		$hookRunner->onRevisionFromEditComplete(
			$article, $insertedRevRecord, $oldLatest, $this->user, $tags
		);

		# Return null RevisionRecord object for autoreview check
		return $insertedRevRecord;
	}

	/**
	 * Get current stability config array
	 * @return array
	 */
	public function getOldConfig() {
		if ( $this->getState() == self::FORM_UNREADY ) {
			throw new LogicException( __CLASS__ . " input fields not set yet.\n" );
		}
		if ( $this->oldConfig === [] && $this->title ) {
			$this->oldConfig = FRPageConfig::getStabilitySettings( $this->title );
		}
		return $this->oldConfig;
	}

	/**
	 * Get proposed stability config array
	 * @return array
	 */
	public function getNewConfig() {
		return [
			'override'   => $this->override,
			'autoreview' => $this->autoreview,
			'expiry'     => $this->getExpiry(), // TS_MW/infinity
		];
	}

	/**
	 * (a) Watch page if $watchThis is true
	 * (b) Unwatch if $watchThis is false
	 */
	private function updateWatchlist() {
		# Apply watchlist checkbox value (may be NULL)
		$watchlistManager = MediaWikiServices::getInstance()->getWatchlistManager();
		if ( $this->watchThis === true ) {
			$watchlistManager->addWatch( $this->user, $this->title );
		} elseif ( $this->watchThis === false ) {
			$watchlistManager->removeWatch( $this->user, $this->title );
		}
	}
}
