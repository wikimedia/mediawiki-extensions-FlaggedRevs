<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;

/**
 * Class containing revision review form business logic
 */
class RevisionReviewForm extends FRGenericSubmitForm {
	/** @var Title|null Target title object */
	private $page = null;
	/** @var FlaggableWikiPage|null Target page object */
	private $article = null;
	/** @var bool Approval requested */
	private $approve = false;
	/** @var bool De-approval requested */
	private $unapprove = false;
	/** @var bool Rejection requested */
	private $reject = false;
	/** @var int ID being reviewed (last "bad" ID for rejection) */
	private $oldid = 0;
	/** @var int Old, "last good", ID (used for rejection) */
	private $refid = 0;
	/** @var string Included template versions (flat string) */
	private $templateParams = '';
	/** @var string Included file versions (flat string) */
	private $imageParams = '';
	/** @var string File page file version (flat string) */
	private $fileVersion = '';
	/** @var string Parameter key */
	private $validatedParams = '';
	/** @var string Review comments */
	private $comment = '';
	/** @var int[] Review flags (for approval) */
	private $dims = [];
	/** @var string|null Conflict handling */
	private $lastChangeTime = null;
	/** @var string|null Conflict handling */
	private $newLastChangeTime = null;

	/** @var FlaggedRevision|null Prior FlaggedRevision for Rev with ID $oldid */
	private $oldFrev = null;
	/** @var int[] Prior flags for Rev with ID $oldid */
	private $oldFlags = [];

	/** @var string User session key */
	private $sessionKey = '';
	/** @var bool Skip validatedParams check */
	private $skipValidationKey = false;

	protected function initialize() {
		foreach ( FlaggedRevs::getTags() as $tag ) {
			$this->dims[$tag] = 0; // default to "inadequate"
		}
	}

	/**
	 * @return Title|null
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * @param Title $value
	 */
	public function setPage( Title $value ) {
		$this->trySet( $this->page, $value );
	}

	/**
	 * @param bool $value
	 */
	public function setApprove( $value ) {
		$this->trySet( $this->approve, $value );
	}

	/**
	 * @param bool $value
	 */
	public function setUnapprove( $value ) {
		$this->trySet( $this->unapprove, $value );
	}

	/**
	 * @param bool $value
	 */
	public function setReject( $value ) {
		$this->trySet( $this->reject, $value );
	}

	/**
	 * @param string|null $value
	 */
	public function setLastChangeTime( $value ) {
		$this->trySet( $this->lastChangeTime, $value );
	}

	/**
	 * @return string|null
	 */
	public function getNewLastChangeTime() {
		return $this->newLastChangeTime;
	}

	/**
	 * @return int
	 */
	public function getRefId() {
		return $this->refid;
	}

	/**
	 * @param int $value
	 */
	public function setRefId( $value ) {
		$this->trySet( $this->refid, (int)$value );
	}

	/**
	 * @return int
	 */
	public function getOldId() {
		return $this->oldid;
	}

	/**
	 * @param int $value
	 */
	public function setOldId( $value ) {
		$this->trySet( $this->oldid, (int)$value );
	}

	/**
	 * @param string $value
	 */
	public function setTemplateParams( $value ) {
		$this->trySet( $this->templateParams, $value );
	}

	/**
	 * @param string $value
	 */
	public function setFileParams( $value ) {
		$this->trySet( $this->imageParams, $value );
	}

	/**
	 * @param string $value
	 */
	public function setFileVersion( $value ) {
		$this->trySet( $this->fileVersion, $value );
	}

	/**
	 * @param string $value
	 */
	public function setValidatedParams( $value ) {
		$this->trySet( $this->validatedParams, $value );
	}

	/**
	 * @return string
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * @param string $value
	 */
	public function setComment( $value ) {
		$this->trySet( $this->comment, $value );
	}

	/**
	 * @param string $tag
	 * @param int $value
	 */
	public function setDim( $tag, $value ) {
		if ( !in_array( $tag, FlaggedRevs::getTags() ) ) {
			throw new Exception( "FlaggedRevs tag $tag does not exist.\n" );
		}
		$this->trySet( $this->dims[$tag], (int)$value );
	}

	/**
	 * @param string $sessionId
	 */
	public function setSessionKey( $sessionId ) {
		$this->sessionKey = $sessionId;
	}

	public function bypassValidationKey() {
		$this->skipValidationKey = true;
	}

	/**
	 * Check that a target is given (e.g. from GET/POST request)
	 * @return true|string true on success, error string on failure
	 */
	protected function doCheckTargetGiven() {
		if ( $this->page === null ) {
			return 'review_page_invalid';
		}
		return true;
	}

	/**
	 * Load any objects after ready() called
	 */
	protected function doBuildOnReady() {
		$this->article = FlaggableWikiPage::getTitleInstance( $this->page );
	}

	/**
	 * Check that the target is valid (e.g. from GET/POST request)
	 * @param int $flags FOR_SUBMISSION (set on submit)
	 * @return true|string true on success, error string on failure
	 */
	protected function doCheckTarget( $flags = 0 ) {
		$flgs = ( $flags & self::FOR_SUBMISSION ) ? Title::GAID_FOR_UPDATE : 0;
		if ( !$this->page->getArticleID( $flgs ) ) {
			return 'review_page_notexists';
		}
		if ( !$this->article->isReviewable() ) {
			return 'review_page_unreviewable';
		}
		return true;
	}

	/**
	 * Validate and clean up parameters (e.g. from POST request).
	 * @return true|string true on success, error string on failure
	 */
	protected function doCheckParameters() {
		$action = $this->getAction();
		if ( $action === null ) {
			return 'review_param_missing'; // no action specified (approve, reject, de-approve)
		} elseif ( !$this->oldid ) {
			return 'review_no_oldid'; // no revision target
		}
		# Get the revision's current flags (if any)
		$this->oldFrev = FlaggedRevision::newFromTitle( $this->page, $this->oldid, FR_MASTER );
		$this->oldFlags = ( $this->oldFrev )
			? $this->oldFrev->getTags()
			: FlaggedRevision::expandRevisionTags( '' ); // default
		# Set initial value for newLastChangeTime (if unchanged on submit)
		$this->newLastChangeTime = $this->lastChangeTime;
		# Fill in implicit tag data for binary flag case
		$iDims = $this->implicitDims();
		if ( $iDims ) {
			$this->dims = $iDims; // binary flag case
		}
		if ( $action === 'approve' ) {
			# We must at least rate each category as 1, the minimum
			if ( in_array( 0, $this->dims, true ) ) {
				return 'review_too_low';
			}
			# Special token to discourage fiddling with templates/files...
			if ( !$this->skipValidationKey ) {
				$k = self::validationKey(
					$this->templateParams, $this->imageParams, $this->fileVersion,
					$this->oldid, $this->sessionKey );
				if ( $this->validatedParams !== $k ) {
					return 'review_bad_key';
				}
			}
			# Sanity check tags
			if ( !FlaggedRevs::flagsAreValid( $this->dims ) ) {
				return 'review_bad_tags';
			}
			# Check permissions with tags
			if ( !FlaggedRevs::userCanSetFlags( $this->user, $this->dims, $this->oldFlags ) ) {
				return 'review_denied';
			}
		} elseif ( $action === 'unapprove' ) {
			# Check permissions with old tags
			if ( !FlaggedRevs::userCanSetFlags( $this->user, $this->oldFlags ) ) {
				return 'review_denied';
			}
		}
		return true;
	}

	private function isAllowed() {
		// Basic permission check
		return ( $this->page && MediaWikiServices::getInstance()->getPermissionManager()
			->userCan( 'review', $this->user, $this->page ) );
	}

	/**
	 * implicit dims for binary flag case
	 * @return int[]|null
	 */
	private function implicitDims() {
		$tag = FlaggedRevs::binaryTagName();
		if ( $tag ) {
			if ( $this->approve ) {
				return [ $tag => 1 ];
			} elseif ( $this->unapprove ) {
				return [ $tag => 0 ];
			}
		}
		return null;
	}

	/**
	 * Get the action this submission is requesting
	 * @return string|null (approve,unapprove,reject)
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
	 * @return true|string true on success, error string on failure
	 */
	protected function doSubmit() {
		# Double-check permissions
		if ( !$this->isAllowed() ) {
			return 'review_denied';
		}
		$status = null;
		$user = $this->user;
		# We can only approve actual revisions...
		$revLookup = MediaWikiServices::getInstance()->getRevisionLookup();
		if ( $this->getAction() === 'approve' ) {
			$revRecord = $revLookup->getRevisionByTitle( $this->page, $this->oldid );
			# Check for archived/deleted revisions...
			if ( !$revRecord || $revRecord->getVisibility() ) {
				return 'review_bad_oldid';
			}
			# Check for review conflicts...
			if ( $this->lastChangeTime !== null ) { // API uses null
				$lastChange = $this->oldFrev ? $this->oldFrev->getTimestamp() : '';
				if ( $lastChange !== $this->lastChangeTime ) {
					return 'review_conflict_oldid';
				}
			}
			$this->approveRevision( $revRecord, $this->oldFrev );
			$status = true;
		# We can only unapprove approved revisions...
		} elseif ( $this->getAction() === 'unapprove' ) {
			# Check for review conflicts...
			if ( $this->lastChangeTime !== null ) { // API uses null
				$lastChange = $this->oldFrev ? $this->oldFrev->getTimestamp() : '';
				if ( $lastChange !== $this->lastChangeTime ) {
					return 'review_conflict_oldid';
				}
			}
			# Check if we can find this flagged rev...
			if ( !$this->oldFrev ) {
				return 'review_not_flagged';
			}
			$this->unapproveRevision( $this->oldFrev );
			$status = true;
		} elseif ( $this->getAction() === 'reject' ) {
			$newRevRecord = $revLookup->getRevisionByTitle( $this->page, $this->oldid );
			$oldRevRecord = $revLookup->getRevisionByTitle( $this->page, $this->refid );
			# Do not mess with archived/deleted revisions
			if ( !$oldRevRecord ||
				$oldRevRecord->isDeleted( RevisionRecord::DELETED_TEXT ) ||
				!$newRevRecord ||
				$newRevRecord->isDeleted( RevisionRecord::DELETED_TEXT )
			) {
				return 'review_bad_oldid';
			}
			# Check that the revs are in order
			if ( $oldRevRecord->getTimestamp() > $newRevRecord->getTimestamp() ) {
				return 'review_cannot_undo';
			}
			# Make sure we are only rejecting pending changes
			$srev = FlaggedRevision::newFromStable( $this->page, FR_MASTER );
			if ( $srev && $oldRevRecord->getTimestamp() < $srev->getRevTimestamp() ) {
				return 'review_cannot_reject'; // not really a use case
			}
			$article = WikiPage::factory( $this->page );
			# Get text with changes after $oldRev up to and including $newRev removed
			if ( WikiPage::hasDifferencesOutsideMainSlot( $newRevRecord, $oldRevRecord ) ) {
				return 'review_cannot_undo';
			}
			$undoHandler = MediaWikiServices::getInstance()
				->getContentHandlerFactory()
				->getContentHandler(
					$newRevRecord->getSlot( SlotRecord::MAIN )->getModel()
				);
			$new_content = $undoHandler->getUndoContent(
				$article->getRevisionRecord()->getContent( SlotRecord::MAIN ),
				$newRevRecord->getContent( SlotRecord::MAIN ),
				$oldRevRecord->getContent( SlotRecord::MAIN ),
				$newRevRecord->isCurrent()
			);
			if ( $new_content === false ) {
				return 'review_cannot_undo';
			}

			$baseRevId = $newRevRecord->isCurrent() ? $oldRevRecord->getId() : 0;

			$comment = $this->getComment();

			// Actually make the edit...
			// Note: this should be changed to use the $undidRevId parameter so that the
			// edit is properly marked as an undo. Do this only after T153570 is merged
			// into Echo, otherwise we would get duplicate revert notifications.
			$editStatus = $article->doEditContent(
				$new_content,
				$comment,
				0,
				$baseRevId,
				$user
			);

			$status = $editStatus->isOK() ? true : 'review_cannot_undo';

			// Notify Echo about the revert.
			// This is due to the lack of appropriate EditResult handling in Echo, in the
			// future, when T153570 is merged, this entire code block should be removed.
			if ( $status === true &&
				// @phan-suppress-next-line PhanTypeArraySuspiciousNullable
				$editStatus->value['revision-record'] &&
				ExtensionRegistry::getInstance()->isLoaded( 'Echo' )
			) {
				$affectedRevisions = []; // revid -> userid
				$revQuery = MediaWikiServices::getInstance()
					->getRevisionStore()
					->getQueryInfo();
				$dbr = wfGetDB( DB_REPLICA );
				$revisions = $dbr->select(
					$revQuery['tables'],
					[ 'rev_id', 'rev_user' => $revQuery['fields']['rev_user'] ],
					[
						'rev_id <= ' . $newRevRecord->getId(),
						'rev_timestamp <= ' . $dbr->addQuotes( $dbr->timestamp( $newRevRecord->getTimestamp() ) ),
						'rev_id > ' . $oldRevRecord->getId(),
						'rev_timestamp > ' . $dbr->addQuotes( $dbr->timestamp( $oldRevRecord->getTimestamp() ) ),
						'rev_page' => $article->getId(),
					],
					__METHOD__,
					[],
					$revQuery['joins']
				);
				foreach ( $revisions as $row ) {
					$affectedRevisions[$row->rev_id] = $row->rev_user;
				}

				EchoEvent::create( [
					'type' => 'reverted',
					'title' => $this->page,
					'extra' => [
						// @phan-suppress-next-line PhanTypeArraySuspiciousNullable
						'revid' => $editStatus->value['revision-record']->getId(),
						'reverted-users-ids' => array_values( $affectedRevisions ),
						'reverted-revision-ids' => array_keys( $affectedRevisions ),
						'method' => 'flaggedrevs-reject',
					],
					'agent' => $user,
				] );
			}

			# If this undid one edit by another logged-in user, update user tallies
			if ( $status === true
				&& $newRevRecord->getParentId() == $oldRevRecord->getId()
				&& $newRevRecord->getUser( RevisionRecord::RAW )
				&& $newRevRecord->getUser( RevisionRecord::RAW )->isRegistered()
			) {
				if ( !( $newRevRecord->getUser( RevisionRecord::RAW )->equals( $user ) ) ) { // no self-reverts
					FRUserCounters::incCount(
						$newRevRecord->getUser( RevisionRecord::RAW )->getId(),
						'revertedEdits'
					);
				}
			}
		}
		# Watch page if set to do so
		if ( $status === true ) {
			if ( $user->getOption( 'flaggedrevswatch' ) && !$user->isWatched( $this->page ) ) {
				$user->addWatch( $this->page );
			}
		}

		FlaggedRevsHookRunner::getRunner()->onFlaggedRevsRevisionReviewFormAfterDoSubmit(
			$this,
			$status
		);

		return $status;
	}

	/**
	 * Adds or updates the flagged revision table for this page/id set
	 * @param RevisionRecord $revRecord The revision to be accepted
	 * @param FlaggedRevision|null $oldFrev Currently accepted version of $rev or null
	 * @throws Exception
	 */
	private function approveRevision(
		RevisionRecord $revRecord,
		FlaggedRevision $oldFrev = null
	) {
		# Revision rating flags
		$flags = $this->dims;
		$quality = 0; // quality tier from flags
		if ( FlaggedRevs::isQuality( $flags ) ) {
			$quality = FlaggedRevs::isPristine( $flags ) ? 2 : 1;
		}
		# Our template/file version pointers
		list( $tmpVersions, $fileVersions ) = $this->getIncludeVersions(
			$this->templateParams, $this->imageParams
		);
		# If this is an image page, store corresponding file info
		$fileData = [ 'name' => null, 'timestamp' => null, 'sha1' => null ];
		if ( $this->page->getNamespace() === NS_FILE && $this->fileVersion ) {
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
			$oldFrev->getFileVersions( FR_MASTER ) == $fileVersions
		) {
			return; // don't record if the same
		}

		# The new review entry...
		$flaggedRevision = new FlaggedRevision( [
			'revrecord'         => $revRecord,
			'user_id'           => $this->user->getId(),
			'timestamp'         => wfTimestampNow(),
			'quality'           => $quality,
			'tags'              => FlaggedRevision::flattenRevisionTags( $flags ),
			'img_name'          => $fileData['name'],
			'img_timestamp'     => $fileData['timestamp'],
			'img_sha1'          => $fileData['sha1'],
			'templateVersions'  => $tmpVersions,
			'fileVersions'      => $fileVersions,
			'flags'             => ''
		] );
		# Delete the old review entry if it exists...
		if ( $oldFrev ) {
			$oldFrev->delete();
		}
		# Insert the new review entry...
		if ( !$flaggedRevision->insert() ) {
			throw new Exception(
				'Flagged revision with ID ' .
				(string)$revRecord->getId() .
				' exists with unexpected fr_page_id'
			);
		}

		$flaggedRevision->approveRevertedTagUpdate();

		# Update the article review log...
		$oldSvId = $oldSv ? $oldSv->getRevId() : 0;
		FlaggedRevsLog::updateReviewLog( $this->page, $this->dims, $this->oldFlags,
			$this->comment, $this->oldid, $oldSvId, true, false, $this->user );

		# Get the new stable version as of now
		$sv = FlaggedRevision::determineStable( $this->page, FR_MASTER /*consistent*/ );
		# Update recent changes...
		self::updateRecentChanges( $revRecord, 'patrol', $sv );
		# Update page and tracking tables and clear cache
		$changed = FlaggedRevs::stableVersionUpdates( $this->page, $sv, $oldSv );
		if ( $changed ) {
			FlaggedRevs::HTMLCacheUpdates( $this->page ); // purge pages that use this page
		}

		# Caller may want to get the change time
		$this->newLastChangeTime = $flaggedRevision->getTimestamp();
	}

	/**
	 * @param FlaggedRevision $frev
	 * Removes flagged revision data for this page/id set
	 */
	private function unapproveRevision( FlaggedRevision $frev ) {
		# Get current stable version ID (for logging)
		$oldSv = FlaggedRevision::newFromStable( $this->page, FR_MASTER );

		# Delete from flaggedrevs table
		$frev->delete();

		# Get the new stable version as of now
		$sv = FlaggedRevision::determineStable( $this->page, FR_MASTER /*consistent*/ );

		# Update the article review log
		$svId = $sv ? $sv->getRevId() : 0;
		FlaggedRevsLog::updateReviewLog( $this->page, $this->dims, $this->oldFlags,
			$this->comment, $this->oldid, $svId, false, false, $this->user );

		# Update recent changes
		self::updateRecentChanges( $frev->getRevisionRecord(), 'unpatrol', $sv );
		# Update page and tracking tables and clear cache
		$changed = FlaggedRevs::stableVersionUpdates( $this->page, $sv, $oldSv );
		if ( $changed ) {
			FlaggedRevs::HTMLCacheUpdates( $this->page ); // purge pages that use this page
		}

		# Caller may want to get the change time
		$this->newLastChangeTime = '';
	}

	/**
	 * Get a validation key from template/file versioning metadata
	 * @param string $tmpP
	 * @param string $imgP
	 * @param string $imgV
	 * @param int $rid rev ID
	 * @param string $sessKey Session key
	 * @return string
	 */
	public static function validationKey( $tmpP, $imgP, $imgV, $rid, $sessKey ) {
		global $wgSecretKey;
		$key = md5( $wgSecretKey );
		$keyBits = $key[3] . $key[9] . $key[13] . $key[19] . $key[26];
		return md5( "{$imgP}{$tmpP}{$imgV}{$rid}{$sessKey}{$keyBits}" );
	}

	/**
	 * Update rc_patrolled fields in recent changes after (un)accepting a rev.
	 * This maintains the patrolled <=> reviewed relationship for reviewable namespaces.
	 *
	 * RecentChange should only be passed in when an RC item is saved.
	 *
	 * @param RevisionRecord|RecentChange $rev
	 * @param string $patrol "patrol" or "unpatrol"
	 * @param FlaggedRevision|null $srev The new stable version
	 * @return void
	 */
	public static function updateRecentChanges( $rev, $patrol, $srev ) {
		if ( $rev instanceof RecentChange ) {
			$pageId = $rev->getAttribute( 'rc_cur_id' );
		} else {
			$pageId = $rev->getPageId();
		}
		$sTimestamp = $srev ? $srev->getRevTimestamp() : null;

		$dbw = wfGetDB( DB_MASTER );
		$limit = 100; // sanity limit to avoid replica lag (most useful when FR is first enabled)
		$conds = [ 'rc_cur_id' => $pageId ];

		$newPatrolState = null; // set rc_patrolled to this value
		# If we accepted this rev, then mark prior revs as patrolled...
		if ( $patrol === 'patrol' ) {
			if ( $sTimestamp ) { // sanity check; should always be set
				$conds[] = 'rc_timestamp <= ' . $dbw->addQuotes( $dbw->timestamp( $sTimestamp ) );
				$newPatrolState = 1;
			}
		# If we un-accepted this rev, then mark now-pending revs as unpatrolled...
		} elseif ( $patrol === 'unpatrol' ) {
			if ( $sTimestamp ) {
				$conds[] = 'rc_timestamp > ' . $dbw->addQuotes( $dbw->timestamp( $sTimestamp ) );
			}
			$newPatrolState = 0;
		}

		if ( $newPatrolState === null ) {
			return; // leave alone
		}

		// Only update rows that need it
		$conds['rc_patrolled'] = $newPatrolState ? 0 : 1;
		// SELECT and update by PK to avoid lag
		$rcIds = $dbw->selectFieldValues(
			'recentchanges',
			'rc_id',
			$conds,
			__METHOD__,
			[ 'LIMIT' => $limit ]
		);
		if ( $rcIds ) {
			$dbw->update(
				'recentchanges',
				[ 'rc_patrolled' => $newPatrolState ],
				[ 'rc_id' => $rcIds ],
				__METHOD__
			);
		}
	}

	/**
	 * Get template and image parameters from parser output to use on forms.
	 * @param int[][] $templateIds {@see ParserOutput::$mTemplateIds} or
	 *  {@see OutputPage::$mTemplateIds}
	 * @param array[] $fileSha1Keys array with [ time, sha1 ] elements,
	 *  {@see ParserOutput::$mFileSearchOptions} or {@see OutputPage::$mImageTimeKeys}
	 * @param string[]|false $fileVersion Version of file for File: pages (time, sha1)
	 * @return string[] [ templateParams, imageParams, fileVersion ]
	 */
	public static function getIncludeParams(
		array $templateIds,
		array $fileSha1Keys,
		$fileVersion
	) {
		$templateParams = '';
		$imageParams = '';
		$fileParam = '';
		# NS -> title -> rev ID mapping
		foreach ( $templateIds as $namespace => $t ) {
			foreach ( $t as $dbKey => $revId ) {
				$temptitle = Title::makeTitle( $namespace, $dbKey );
				$templateParams .= $temptitle->getPrefixedDBkey() . "|" . $revId . "#";
			}
		}
		# Image -> timestamp -> sha1 mapping
		foreach ( $fileSha1Keys as $dbKey => $timeAndSHA1 ) {
			$imageParams .= $dbKey . "|" . $timeAndSHA1['time'] . "|" . $timeAndSHA1['sha1'] . "#";
		}
		# For File: pages, note the displayed image version
		if ( is_array( $fileVersion ) ) {
			$fileParam = $fileVersion['time'] . "#" . $fileVersion['sha1'];
		}
		return [ $templateParams, $imageParams, $fileParam ];
	}

	/**
	 * Get template and image versions from form value for parser output.
	 * @param string $templateParams
	 * @param string $imageParams
	 * @return array[] [ templateIds, fileSha1Keys ], where
	 *  - templateIds is an int[][] array, {@see ParserOutput::$mTemplateIds} or
	 *    {@see OutputPage::$mTemplateIds}
	 *  - fileSha1Keys is an array with [ time, sha1 ] elements,
	 *    {@see ParserOutput::$mFileSearchOptions} or {@see OutputPage::$mImageTimeKeys}
	 */
	private function getIncludeVersions( $templateParams, $imageParams ) {
		$templateIds = [];
		$templateMap = explode( '#', trim( $templateParams ) );
		foreach ( $templateMap as $template ) {
			if ( !$template ) {
				continue;
			}
			$m = explode( '|', $template, 2 );
			if ( !isset( $m[1] ) || !$m[0] ) {
				continue;
			}
			list( $prefixed_text, $rev_id ) = $m;
			# Get the template title
			$tmp_title = Title::newFromText( $prefixed_text ); // Normalize this to be sure...
			if ( $tmp_title === null ) {
				continue; // Page must be valid!
			}
			$templateIds[$tmp_title->getNamespace()][$tmp_title->getDBkey()] = $rev_id;
		}
		# Our image version pointers
		$fileSha1Keys = [];
		$imageMap = explode( '#', trim( $imageParams ) );
		foreach ( $imageMap as $image ) {
			if ( !$image ) {
				continue;
			}
			$m = explode( '|', $image, 3 );
			# Expand our parameters ... <name>#<timestamp>#<key>
			if ( !isset( $m[2] ) || !$m[0] ) {
				continue;
			}
			list( $dbkey, $time, $key ) = $m;
			# Get the file title
			$img_title = Title::makeTitle( NS_FILE, $dbkey ); // Normalize
			if ( $img_title === null ) {
				continue; // Page must be valid!
			}
			$fileSha1Keys[$img_title->getDBkey()] = [
				'time' => $time ?: false,
				'sha1' => $key ?: false,
			];
		}
		return [ $templateIds, $fileSha1Keys ];
	}
}
