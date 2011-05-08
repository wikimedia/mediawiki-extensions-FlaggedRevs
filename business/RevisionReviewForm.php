<?php
/**
 * Class containing revision review form business logic
 */
class RevisionReviewForm extends FRGenericSubmitForm {
	/* Form parameters which can be user given */
	protected $page = null;					# Target page obj
	protected $approve = false;				# Approval requested
	protected $unapprove = false;			# De-approval requested
	protected $reject = false;				# Rejection requested
	protected $oldid = 0;					# ID being reviewed (last "bad" ID for rejection)
	protected $refid = 0;					# Old, "last good", ID (used for rejection)
	protected $templateParams = '';			# Included template versions (flat string)
	protected $imageParams = '';			# Included file versions (flat string)
	protected $fileVersion = '';			# File page file version (flat string)
	protected $validatedParams = '';		# Parameter key
	protected $comment = '';				# Review comments
	protected $dims = array();				# Review flags (for approval)
	protected $lastChangeTime = null; 		# Conflict handling
	protected $newLastChangeTime = null; 	# Conflict handling

	protected $oflags = array();			# Prior flags for Rev with ID $oldid

	protected function initialize() {
		foreach ( FlaggedRevs::getTags() as $tag ) {
			$this->dims[$tag] = 0; // default to "inadequate"
		}
	}

	public function getPage() {
		return $this->page;
	}

	public function setPage( Title $value ) {
		$this->trySet( $this->page, $value );
	}

	public function setApprove( $value ) {
		$this->trySet( $this->approve, $value );
	}

	public function setUnapprove( $value ) {
		$this->trySet( $this->unapprove, $value );
	}

	public function setReject( $value ) {
		$this->trySet( $this->reject, $value );
	}

	public function setLastChangeTime( $value ) {
		$this->trySet( $this->lastChangeTime, $value );
	}

	public function getNewLastChangeTime() {
		return $this->newLastChangeTime;
	}

	public function getRefId() {
		return $this->refid;
	}

	public function setRefId( $value ) {
		$this->trySet( $this->refid, (int)$value );
	}

	public function getOldId() {
		return $this->oldid;
	}

	public function setOldId( $value ) {
		$this->trySet( $this->oldid, (int)$value );
	}

	public function getTemplateParams() {
		return $this->templateParams;
	}

	public function setTemplateParams( $value ) {
		$this->trySet( $this->templateParams, $value );
	}

	public function getFileParams() {
		return $this->imageParams;
	}

	public function setFileParams( $value ) {
		$this->trySet( $this->imageParams, $value );
	}

	public function getFileVersion() {
		return $this->fileVersion;
	}

	public function setFileVersion( $value ) {
		$this->trySet( $this->fileVersion, $value );
	}

	public function getValidatedParams() {
		return $this->validatedParams;
	}

	public function setValidatedParams( $value ) {
		$this->trySet( $this->validatedParams, $value );
	}

	public function getComment() {
		return $this->comment;
	}

	public function setComment( $value ) {
		$this->trySet( $this->comment, $value );
	}

	public function getDims() {
		return $this->dims;
	}

	public function setDim( $tag, $value ) {
		if ( !in_array( $tag, FlaggedRevs::getTags() ) ) {
			throw new MWException( "FlaggedRevs tag $tag does not exist.\n" );
		}
		$this->trySet( $this->dims[$tag], (int)$value );
	}

	/*
	* Check that a target is given (e.g. from GET/POST request)
	* @return mixed (true on success, error string on failure)
	*/
	public function doCheckTargetGiven() {
		if ( is_null( $this->page ) ) {
			return 'review_page_invalid';
		}
		return true;
	}

	/**
	* Load any objects after ready() called
	* @return mixed (true on success, error string on failure)
	*/
	protected function doBuildOnReady() {
		$this->article = FlaggedPage::getTitleInstance( $this->page );
		return true;
	}

	/*
	* Check that the target is valid (e.g. from GET/POST request)
	* @param int $flags FOR_SUBMISSION (set on submit)
	* @return mixed (true on success, error string on failure)
	*/
	protected function doCheckTarget( $flags = 0 ) {
		$flgs = ( $flags & self::FOR_SUBMISSION ) ? Title::GAID_FOR_UPDATE : 0;
		if ( !$this->page->getArticleId( $flgs ) ) {
			return 'review_page_notexists';
		}
		$flgs = ( $flags & self::FOR_SUBMISSION ) ? FR_MASTER : 0;
		if ( !$this->article->isReviewable( $flgs ) ) {
			return 'review_page_unreviewable';
		}
		return true;
	}

	/*
	* Validate and clean up parameters (e.g. from POST request).
	* @return mixed (true on success, error string on failure)
	*/
	protected function doCheckParameters() {
		if ( !$this->oldid ) {
			return 'review_no_oldid'; // bad revision target
		}
		# Check that an action is specified (approve, reject, de-approve)
		if ( $this->getAction() === null ) {
			return 'review_param_missing'; // user didn't say
		}
		# Get the revision's current flags, if any
		$this->oflags = FlaggedRevision::getRevisionTags( $this->page, $this->oldid, FR_MASTER );
		# Set initial value for newLastChangeTime (if unchanged on submit)
		$this->newLastChangeTime = $this->lastChangeTime;
		# Fill in implicit tag data for binary flag case
		$iDims = $this->implicitDims();
		if ( $iDims ) {
			$this->dims = $iDims; // binary flag case
		}
		if ( $this->getAction() === 'approve' ) {
			# We must at least rate each category as 1, the minimum
			if ( in_array( 0, $this->dims, true ) ) {
				return 'review_too_low';
			}
			# Special token to discourage fiddling with templates/files...
			$k = self::validationKey(
				$this->templateParams, $this->imageParams, $this->fileVersion, $this->oldid );
			if ( $this->validatedParams !== $k ) {
				return 'review_bad_key';
			}
			# Sanity check tags
			if ( !FlaggedRevs::flagsAreValid( $this->dims ) ) {
				return 'review_bad_tags';
			}
			# Check permissions with tags
			if ( !FlaggedRevs::userCanSetFlags( $this->user, $this->dims, $this->oflags ) ) {
				return 'review_denied';
			}
		} elseif ( $this->getAction() === 'unapprove' ) {
			# Check permissions with old tags
			if ( !FlaggedRevs::userCanSetFlags( $this->user, $this->oflags ) ) {
				return 'review_denied';
			}
		}
		return true;
	}

	public function isAllowed() {
		// Basic permission check
		return ( $this->page
			&& $this->page->userCan( 'review' )
			&& $this->page->userCan( 'edit' )
		);
	}

	// implicit dims for binary flag case
	private function implicitDims() {
		$tag = FlaggedRevs::binaryTagName();
		if ( $tag ) {
			if ( $this->approve ) {
				return array( $tag => 1 );
			} else if ( $this->unapprove ) {
				return array( $tag => 0 );
			}
		}
		return null;
	}

	/*
	* What are we doing?
	* @return string (approve,unapprove,reject)
	*/
	public function getAction() {
		if ( !$this->reject && !$this->unapprove && $this->approve ) {
			return 'approve';
		} elseif ( !$this->reject && $this->unapprove && !$this->approve ) {
			return 'unapprove';
		} elseif ( $this->reject && !$this->unapprove && !$this->approve ) {
			return 'reject';
		}
		return null; // nothing valid asserted
	}

	/**
	* Submit the form parameters for the page config to the DB.
	* 
	* @return mixed (true on success, error string on failure)
	*/
	public function doSubmit() {
		# Double-check permissions
		if ( !$this->isAllowed() ) {
			return 'review_denied';
		}
		# We can only approve actual revisions...
		if ( $this->getAction() === 'approve' ) {
			$rev = Revision::newFromTitle( $this->page, $this->oldid );
			# Check for archived/deleted revisions...
			if ( !$rev || $rev->mDeleted ) {
				return 'review_bad_oldid';
			}
			$oldFrev = FlaggedRevision::newFromTitle( $this->page, $this->oldid, FR_MASTER );
			# Check for review conflicts...
			if ( $this->lastChangeTime !== null ) { // API uses null
				$lastChange = $oldFrev ? $oldFrev->getTimestamp() : '';
				if ( $lastChange !== $this->lastChangeTime ) {
					return 'review_conflict_oldid';
				}
			}
			$status = $this->approveRevision( $rev, $oldFrev );
		# We can only unapprove approved revisions...
		} elseif ( $this->getAction() === 'unapprove' ) {
			$oldFrev = FlaggedRevision::newFromTitle( $this->page, $this->oldid, FR_MASTER );
			# Check for review conflicts...
			if ( $this->lastChangeTime !== null ) { // API uses null
				$lastChange = $oldFrev ? $oldFrev->getTimestamp() : '';
				if ( $lastChange !== $this->lastChangeTime ) {
					return 'review_conflict_oldid';
				}
			}
			# Check if we can find this flagged rev...
			if ( !$oldFrev ) {
				return 'review_not_flagged';
			}
			$status = $this->unapproveRevision( $oldFrev );
		} elseif ( $this->getAction() === 'reject' ) {
			$newRev = Revision::newFromTitle( $this->page, $this->oldid );
			$oldRev = Revision::newFromTitle( $this->page, $this->refid );
			# Do not mess with archived/deleted revisions
			if ( !$oldRev || $oldRev->isDeleted( Revision::DELETED_TEXT ) ) {
				return 'review_bad_oldid';
			} elseif ( !$newRev || $newRev->isDeleted( Revision::DELETED_TEXT ) ) {
				return 'review_bad_oldid';
			}
			$srev = FlaggedRevision::newFromStable( $this->page, FR_MASTER );
			if ( $srev && $srev->getRevId() > $oldRev->getId() ) {
				return 'review_cannot_reject'; // not really a use case
			}
			$article = new Article( $this->page );
			# Get text with changes after $oldRev up to and including $newRev removed
			$new_text = $article->getUndoText( $newRev, $oldRev );
			if ( $new_text === false ) {
				return 'review_cannot_undo';
			}
			$baseRevId = $newRev->isCurrent() ? $oldRev->getId() : 0;
			$article->doEdit( $new_text, $this->getComment(), 0, $baseRevId, $this->user );
			# If this undid one edit by another logged-in user, update user tallies
			if ( $newRev->getParentId() == $oldRev->getId() && $newRev->getRawUser() ) {
				if ( $newRev->getRawUser() != $this->user->getId() ) { // no self-reverts
					FRUserCounters::incCount( $newRev->getRawUser(), 'revertedEdits' );
				}
			}
		}
		# Watch page if set to do so
		if ( $status === true ) {
			if ( $this->user->getOption( 'flaggedrevswatch' ) && !$this->page->userIsWatching() ) {
				$this->user->addWatch( $this->page );
			}
		}
		return $status;
	}

	/**
	 * Adds or updates the flagged revision table for this page/id set
	 * @param Revision $rev The revision to be accepted
	 * @param FlaggedRevision $oldFrev Currently accepted version of $rev or null
	 * @return true on success, array of errors on failure
	 */
	private function approveRevision( Revision $rev, FlaggedRevision $oldFrev = null ) {
		wfProfileIn( __METHOD__ );
		# Revision rating flags
		$flags = $this->dims;
		$quality = 0; // quality tier from flags
		if ( FlaggedRevs::isQuality( $flags ) ) {
			$quality = FlaggedRevs::isPristine( $flags ) ? 2 : 1;
		}
		# Our template/file version pointers
		list( $tmpVersions, $fileVersions ) = self::getIncludeVersions(
			$this->templateParams, $this->imageParams
		);
		# If this is an image page, store corresponding file info
		$fileData = array( 'name' => null, 'timestamp' => null, 'sha1' => null );
		if ( $this->page->getNamespace() == NS_FILE && $this->fileVersion ) {
			# Stable upload version for file pages...
			$data = explode( '#', $this->fileVersion, 2 );
			if ( count( $data ) == 2 ) {
				$fileData['name'] = $this->page->getDBkey();
				$fileData['timestamp'] = $data[0];
				$fileData['sha1'] = $data[1];
			}
		}

		# Get current stable version ID (for logging)
		$oldSv = FlaggedRevision::newFromStable( $this->page, FR_MASTER );

		# Is this a duplicate review?
		if ( $oldFrev &&
			$oldFrev->getTags() == $flags && // tags => quality
			$oldFrev->getFileSha1() == $fileData['sha1'] &&
			$oldFrev->getFileTimestamp() == $fileData['timestamp'] &&
			$oldFrev->getTemplateVersions( FR_MASTER ) == $tmpVersions &&
			$oldFrev->getFileVersions( FR_MASTER ) == $fileVersions )
		{
			wfProfileOut( __METHOD__ );
			return true; // don't record if the same
		}

		# The new review entry...
 		$flaggedRevision = new FlaggedRevision( array(
			'rev'        		=> $rev,
			'user_id'          	=> $this->user->getId(),
			'timestamp'     	=> wfTimestampNow(),
			'quality'       	=> $quality,
			'tags'          	=> FlaggedRevision::flattenRevisionTags( $flags ),
			'img_name'      	=> $fileData['name'],
			'img_timestamp' 	=> $fileData['timestamp'],
			'img_sha1'      	=> $fileData['sha1'],
			'templateVersions' 	=> $tmpVersions,
			'fileVersions'     	=> $fileVersions,
			'flags'				=> ''
		) );
		# Delete the old review entry if it exists...
		if ( $oldFrev ) {
			$oldFrev->delete();
		}
		# Insert the new review entry...
		$flaggedRevision->insert();
		# Update recent changes...
		$rcId = $rev->isUnpatrolled(); // int
		self::updateRecentChanges( $this->page, $rev->getId(), $rcId, true );

		# Update the article review log...
		$oldSvId = $oldSv ? $oldSv->getRevId() : 0;
		FlaggedRevsLog::updateReviewLog( $this->page, $this->dims, $this->oflags,
			$this->comment, $this->oldid, $oldSvId, true );

		# Get the new stable version as of now
		$sv = FlaggedRevision::determineStable( $this->page, FR_MASTER/*consistent*/ );
		# Update page and tracking tables and clear cache
		$changed = FlaggedRevs::stableVersionUpdates( $this->page, $sv, $oldSv );
		if ( $changed ) {
			FlaggedRevs::HTMLCacheUpdates( $this->page ); // purge pages that use this page
		}

		# Caller may want to get the change time
		$this->newLastChangeTime = $flaggedRevision->getTimestamp();

		wfProfileOut( __METHOD__ );
        return true;
    }

	/**
	 * @param FlaggedRevision $frev
	 * Removes flagged revision data for this page/id set
	 */
	private function unapproveRevision( FlaggedRevision $frev ) {
		wfProfileIn( __METHOD__ );

		# Get current stable version ID (for logging)
		$oldSv = FlaggedRevision::newFromStable( $this->page, FR_MASTER );

		# Delete from flaggedrevs table
		$frev->delete();
		# Update recent changes
		self::updateRecentChanges( $this->page, $frev->getRevId(), false, false );

		# Update the article review log
		$oldSvId = $oldSv ? $oldSv->getRevId() : 0;
		FlaggedRevsLog::updateReviewLog( $this->page, $this->dims, $this->oflags,
			$this->comment, $this->oldid, $oldSvId, false );

		# Get the new stable version as of now
		$sv = FlaggedRevision::determineStable( $this->page, FR_MASTER /*consistent*/ );
		# Update page and tracking tables and clear cache
		$changed = FlaggedRevs::stableVersionUpdates( $this->page, $sv, $oldSv );
		if ( $changed ) {
			FlaggedRevs::HTMLCacheUpdates( $this->page ); // purge pages that use this page
		}

		# Caller may want to get the change time
		$this->newLastChangeTime = '';

		wfProfileOut( __METHOD__ );
        return true;
    }

	/**
	* Get a validation key from versioning metadata
	* @param string $tmpP
	* @param string $imgP
	* @param string $imgV
	* @param integer $rid rev ID
	* @return string
	*/
	public static function validationKey( $tmpP, $imgP, $imgV, $rid ) {
		global $wgSecretKey, $wgProxyKey;
		$key = $wgSecretKey ? $wgSecretKey : $wgProxyKey; // fall back to $wgProxyKey
		$p = md5( $key . $imgP . $tmpP . $rid . $imgV );
		return $p;
	}

	public static function updateRecentChanges(
		Title $title, $revId, $rcId = false, $patrol = true
	) {
		wfProfileIn( __METHOD__ );
		$revId = intval( $revId );
		$dbw = wfGetDB( DB_MASTER );
		# Olders edits be marked as patrolled now...
		$dbw->update( 'recentchanges',
			array( 'rc_patrolled' => $patrol ? 1 : 0 ),
			array( 'rc_cur_id' => $title->getArticleId(),
				$patrol ? "rc_this_oldid <= $revId" : "rc_this_oldid = $revId" ),
			__METHOD__,
			// Performance
			array( 'USE INDEX' => 'rc_cur_id', 'LIMIT' => 50 )
		);
		# New page patrol may be enabled. If so, the rc_id may be the first
		# edit and not this one. If it is different, mark it too.
		if ( $rcId && $rcId != $revId ) {
			$dbw->update( 'recentchanges',
				array( 'rc_patrolled' => 1 ),
				array( 'rc_id' => $rcId,
					'rc_type' => RC_NEW ),
				__METHOD__
			);
		}
		wfProfileOut( __METHOD__ );
	}

	/**
	 * Get template and image parameters from parser output to use on forms.
	 * @param FlaggedPage $article
	 * @param array $templateIDs (from ParserOutput/OutputPage->mTemplateIds)
	 * @param array $imageSHA1Keys (from ParserOutput/OutputPage->mImageTimeKeys)
	 * @return array( templateParams, imageParams, fileVersion )
	 */
	public static function getIncludeParams(
		FlaggedPage $article, array $templateIDs, array $imageSHA1Keys
	) {
		$templateParams = $imageParams = $fileVersion = '';
		# NS -> title -> rev ID mapping
		foreach ( $templateIDs as $namespace => $t ) {
			foreach ( $t as $dbKey => $revId ) {
				$temptitle = Title::makeTitle( $namespace, $dbKey );
				$templateParams .= $temptitle->getPrefixedDBKey() . "|" . $revId . "#";
			}
		}
		# Image -> timestamp -> sha1 mapping
		foreach ( $imageSHA1Keys as $dbKey => $timeAndSHA1 ) {
			$imageParams .= $dbKey . "|" . $timeAndSHA1['time'] . "|" . $timeAndSHA1['sha1'] . "#";
		}
		# For image pages, note the displayed image version
		if ( $article->getTitle()->getNamespace() == NS_FILE ) {
			$file = $article->getDisplayedFile(); // File obj
			if ( $file ) {
				$fileVersion = $file->getTimestamp() . "#" . $file->getSha1();
			}
		}
		return array( $templateParams, $imageParams, $fileVersion );
	}

	/**
	 * Get template and image versions from form value for parser output.
	 * @param string $templateParams
	 * @param string $imageParams
	 * @return array( templateIds, fileSHA1Keys )
	 * templateIds like ParserOutput->mTemplateIds
	 * fileSHA1Keys like ParserOutput->mImageTimeKeys
	 */
	public static function getIncludeVersions( $templateParams, $imageParams ) {
		$templateIds = array();
		$templateMap = explode( '#', trim( $templateParams ) );
		foreach ( $templateMap as $template ) {
			if ( !$template ) {
				continue;
			}
			$m = explode( '|', $template, 2 );
			if ( !isset( $m[0] ) || !isset( $m[1] ) || !$m[0] ) {
				continue;
			}
			list( $prefixed_text, $rev_id ) = $m;
			# Get the template title
			$tmp_title = Title::newFromText( $prefixed_text ); // Normalize this to be sure...
			if ( is_null( $tmp_title ) ) {
				continue; // Page must be valid!
			}
			if ( !isset( $templateIds[$tmp_title->getNamespace()] ) ) {
				$templateIds[$tmp_title->getNamespace()] = array();
			}
			$templateIds[$tmp_title->getNamespace()][$tmp_title->getDBkey()] = $rev_id;
		}
		# Our image version pointers
		$fileSHA1Keys = array();
		$imageMap = explode( '#', trim( $imageParams ) );
		foreach ( $imageMap as $image ) {
			if ( !$image ) {
				continue;
			}
			$m = explode( '|', $image, 3 );
			# Expand our parameters ... <name>#<timestamp>#<key>
			if ( !isset( $m[0] ) || !isset( $m[1] ) || !isset( $m[2] ) || !$m[0] ) {
				continue;
			}
			list( $dbkey, $time, $key ) = $m;
			# Get the file title
			$img_title = Title::makeTitle( NS_FILE, $dbkey ); // Normalize
			if ( is_null( $img_title ) ) {
				continue; // Page must be valid!
			}
			$fileSHA1Keys[$img_title->getDBkey()] = array();
			$fileSHA1Keys[$img_title->getDBkey()]['time'] = $time ? $time : false;
			$fileSHA1Keys[$img_title->getDBkey()]['sha1'] = strlen( $key ) ? $key : false;
		}
		return array( $templateIds, $fileSHA1Keys );
	}

	/**
	 * Get template and image versions from parsing a revision.
	 * @param Article $article
	 * @param Revision $rev
	 * @param User $user
	 * @return array( templateIds, fileSHA1Keys )
	 * templateIds like ParserOutput->mTemplateIds
	 * fileSHA1Keys like ParserOutput->mImageTimeKeys
	 */
	public static function getRevIncludes( Article $article, Revision $rev, User $user ) {
		global $wgParser, $wgOut, $wgEnableParserCache;
		wfProfileIn( __METHOD__ );
		$pOutput = false;
		$pOpts = $article->makeParserOptions( $user );
		$parserCache = ParserCache::singleton();
		# Current version: try parser cache
		if ( $rev->isCurrent() ) {
			$pOutput = $parserCache->get( $article, $pOpts );
		}
		# Otherwise (or on cache miss), parse the rev text...
		if ( !$pOutput ) {
			$text = $rev->getText();
			$title = $article->getTitle();
			$pOutput = $wgParser->parse( $text, $title, $pOpts, true, true, $rev->getId() );
			# Might as well save the cache while we're at it
			if ( $rev->isCurrent() && $wgEnableParserCache ) {
				$parserCache->save( $pOutput, $article, $pOpts );
			}
		}
		wfProfileOut( __METHOD__ );
		return array( $pOutput->getTemplateIds(), $pOutput->getImageTimeKeys() );
	}
}
