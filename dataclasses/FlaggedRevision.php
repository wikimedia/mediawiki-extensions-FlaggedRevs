<?php
/**
 * Class representing a stable version of a MediaWiki revision
 * 
 * This contains a page revision, a file version, and versions
 * of templates and files (to determine template inclusion and thumbnails)
 */
class FlaggedRevision {
	private $mRevision;			// base revision
	private $mTemplates; 		// included template versions
	private $mFiles;     		// included file versions
	private $mFileSha1;      	// file version sha-1 (for revisions of File pages)
	private $mFileTimestamp;	// file version timestamp (for revisions of File pages)
	/* Flagging metadata */
	private $mTimestamp;
	private $mQuality;
	private $mTags;
	private $mFlags;
	private $mUser;				// reviewing user
	private $mFileName;			// file name when reviewed
	/* Redundant fields for lazy-loading */
	private $mTitle;
	private $mPageId;
	private $mRevId;
	private $mStableTemplates;
	private $mStableFiles;

	/**
	 * @param Row|array $row (DB row or array)
	 * @return void
	 */
	public function __construct( $row ) {
		if ( is_object( $row ) ) {
			$this->mRevId = intval( $row->fr_rev_id );
			$this->mPageId = intval( $row->fr_page_id );
			$this->mTimestamp = $row->fr_timestamp;
			$this->mQuality = intval( $row->fr_quality );
			$this->mTags = self::expandRevisionTags( strval( $row->fr_tags ) );
			$this->mFlags = explode( ',', $row->fr_flags );
			$this->mUser = intval( $row->fr_user );
			# Base Revision object
			$this->mRevision = new Revision( $row );
			# Image page revision relevant params
			$this->mFileName = $row->fr_img_name ? $row->fr_img_name : null;
			$this->mFileSha1 = $row->fr_img_sha1 ? $row->fr_img_sha1 : null;
			$this->mFileTimestamp = $row->fr_img_timestamp ?
				$row->fr_img_timestamp : null;
			# Optional fields
			$this->mTitle = isset( $row->page_namespace ) && isset( $row->page_title )
				? Title::makeTitleSafe( $row->page_namespace, $row->page_title )
				: null;
		} elseif ( is_array( $row ) ) {
			$this->mRevId = intval( $row['rev_id'] );
			$this->mPageId = intval( $row['page_id'] );
			$this->mTimestamp = $row['timestamp'];
			$this->mQuality = intval( $row['quality'] );
			$this->mTags = self::expandRevisionTags( strval( $row['tags'] ) );
			$this->mFlags = explode( ',', $row['flags'] );
			$this->mUser = intval( $row['user'] );
			# Image page revision relevant params
			$this->mFileName = $row['img_name'] ? $row['img_name'] : null;
			$this->mFileSha1 = $row['img_sha1'] ? $row['img_sha1'] : null;
			$this->mFileTimestamp = $row['img_timestamp'] ?
				$row['img_timestamp'] : null;
			# Optional fields
			$this->mTemplates = isset( $row['templateVersions'] ) ?
				$row['templateVersions'] : null;
			$this->mFiles = isset( $row['fileVersions'] ) ?
				$row['fileVersions'] : null;
		} else {
			throw new MWException( 'FlaggedRevision constructor passed invalid row format.' );
		}
	}

	/**
	 * Get a FlaggedRevision for a title and rev ID.
	 * Note: will return NULL if the revision is deleted.
	 * @param Title $title
	 * @param int $revId
	 * @param int $flags (FR_MASTER, FR_FOR_UPDATE)
	 * @return FlaggedRevision|null (null on failure)
	 */
	public static function newFromTitle( Title $title, $revId, $flags = 0 ) {
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
			return null; // short-circuit
		}
		$options = array();
		# User master/slave as appropriate...
		if ( $flags & FR_FOR_UPDATE || $flags & FR_MASTER ) {
			$db = wfGetDB( DB_MASTER );
			if ( $flags & FR_FOR_UPDATE ) $options[] = 'FOR UPDATE';
			$pageId = $title->getArticleID( Title::GAID_FOR_UPDATE );
		} else {
			$db = wfGetDB( DB_SLAVE );
			$pageId = $title->getArticleID();
		}
		if ( !$pageId || !$revId ) {
			return null; // short-circuit query
		}
		# Skip deleted revisions
		$row = $db->selectRow(
			array( 'flaggedrevs', 'revision' ),
			self::selectFields(),
			array(
				'fr_page_id' => $pageId,
				'fr_rev_id' => $revId,
				'rev_id = fr_rev_id',
				'rev_page = fr_page_id',
				'rev_deleted & ' . Revision::DELETED_TEXT => 0
			),
			__METHOD__,
			$options
		);
		# Sorted from highest to lowest, so just take the first one if any
		if ( $row ) {
			$frev = new self( $row );
			$frev->mTitle = $title;
			return $frev;
		}
		return null;
	}

	/**
	 * Get a FlaggedRevision of the stable version of a title.
	 * @param Title $title, page title
	 * @param int $flags (FR_MASTER, FR_FOR_UPDATE)
	 * @return FlaggedRevision|null (null on failure)
	 */
	public static function newFromStable( Title $title, $flags = 0 ) {
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
			return null; // short-circuit
		}
		$options = array();
		# User master/slave as appropriate...
		if ( $flags & FR_FOR_UPDATE || $flags & FR_MASTER ) {
			$db = wfGetDB( DB_MASTER );
			if ( $flags & FR_FOR_UPDATE ) $options[] = 'FOR UPDATE';
			$pageId = $title->getArticleID( Title::GAID_FOR_UPDATE );
		} else {
			$db = wfGetDB( DB_SLAVE );
			$pageId = $title->getArticleID();
		}
		if ( !$pageId ) {
			return null; // short-circuit query
		}
		# Check tracking tables
		$row = $db->selectRow(
			array( 'flaggedpages', 'flaggedrevs', 'revision' ),
			self::selectFields(),
			array(
				'fp_page_id' => $pageId,
				'fr_page_id = fp_page_id',
				'fr_rev_id = fp_stable',
				'rev_id = fr_rev_id'
			),
			__METHOD__,
			$options
		);
		if ( $row ) {
			$frev = new self( $row );
			$frev->mTitle = $title;
			return $frev;
		}
		return null;
	}

	/**
	 * Get a FlaggedRevision of the stable version of a title.
	 * Skips tracking tables to figure out new stable version.
	 * @param Title $title, page title
	 * @param int $flags (FR_MASTER, FR_FOR_UPDATE)
	 * @param array $config, optional page config (use to skip queries)
	 * @param string $precedence (latest,quality,pristine)
	 * @return FlaggedRevision|null (null on failure)
	 */
	public static function determineStable(
		Title $title, $flags = 0, $config = array(), $precedence = 'latest'
	) {
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
			return null; // short-circuit
		}
		$options = array();
		# User master/slave as appropriate...
		if ( $flags & FR_FOR_UPDATE || $flags & FR_MASTER ) {
			$db = wfGetDB( DB_MASTER );
			if ( $flags & FR_FOR_UPDATE ) $options[] = 'FOR UPDATE';
			$pageId = $title->getArticleID( Title::GAID_FOR_UPDATE );
		} else {
			$db = wfGetDB( DB_SLAVE );
			$pageId = $title->getArticleID();
		}
		if ( !$pageId ) {
			return null; // short-circuit query
		}
		# Get visiblity settings...
		if ( empty( $config ) ) {
		   $config = FlaggedPageConfig::getStabilitySettings( $title, $flags );
		}
		if ( !$config['override'] && FlaggedRevs::useOnlyIfProtected() ) {
			return null; // page is not reviewable; no stable version
		}
		$row = null;
		$options['ORDER BY'] = 'fr_rev_id DESC';
		# Look for the latest pristine revision...
		if ( FlaggedRevs::pristineVersions() && $precedence !== 'latest' ) {
			$prow = $db->selectRow(
				array( 'flaggedrevs', 'revision' ),
				self::selectFields(),
				array( 'fr_page_id' => $pageId,
					'fr_quality = ' . FR_PRISTINE,
					'rev_id = fr_rev_id',
					'rev_page = fr_page_id',
					'rev_deleted & ' . Revision::DELETED_TEXT => 0
				),
				__METHOD__,
				$options
			);
			# Looks like a plausible revision
			$row = $prow ? $prow : $row;
		}
		if ( $row && $precedence === 'pristine' ) {
			// we have what we want already
		# Look for the latest quality revision...
		} elseif ( FlaggedRevs::qualityVersions() && $precedence !== 'latest' ) {
			// If we found a pristine rev above, this one must be newer...
			$newerClause = $row ? "fr_rev_id > {$row->fr_rev_id}" : "1 = 1";
			$qrow = $db->selectRow(
				array( 'flaggedrevs', 'revision' ),
				self::selectFields(),
				array( 'fr_page_id' => $pageId,
					'fr_quality = ' . FR_QUALITY,
					$newerClause,
					'rev_id = fr_rev_id',
					'rev_page = fr_page_id',
					'rev_deleted & ' . Revision::DELETED_TEXT => 0
				),
				__METHOD__,
				$options
			);
			$row = $qrow ? $qrow : $row;
		}
		# Do we have one? If not, try the latest reviewed revision...
		if ( !$row ) {
			$row = $db->selectRow(
				array( 'flaggedrevs', 'revision' ),
				self::selectFields(),
				array( 'fr_page_id' => $pageId,
					'rev_id = fr_rev_id',
					'rev_page = fr_page_id',
					'rev_deleted & ' . Revision::DELETED_TEXT => 0
				),
				__METHOD__,
				$options
			);
			if ( !$row ) return null;
		}
		$frev = new self( $row );
		$frev->mTitle = $title;
		return $frev;
	}

	/*
	* Insert a FlaggedRevision object into the database
	*
	* @return bool success
	*/
	public function insertOn() {
		$dbw = wfGetDB( DB_MASTER );
		# Set any flagged revision flags
		$this->mFlags = array_merge( $this->mFlags, array( 'dynamic' ) ); // legacy
		# Build the inclusion data chunks
		$tmpInsertRows = array();
		foreach ( (array)$this->mTemplates as $namespace => $titleAndID ) {
			foreach ( $titleAndID as $dbkey => $id ) {
				$tmpInsertRows[] = array(
					'ft_rev_id' 	=> $this->getRevId(),
					'ft_namespace'  => (int)$namespace,
					'ft_title' 		=> $dbkey,
					'ft_tmp_rev_id' => (int)$id
				);
			}
		}
		$fileInsertRows = array();
		foreach ( (array)$this->mFiles as $dbkey => $timeSHA1 ) {
			$fileInsertRows[] = array(
				'fi_rev_id' 		=> $this->getRevId(),
				'fi_name' 			=> $dbkey,
				'fi_img_sha1' 		=> strval( $timeSHA1['sha1'] ),
				'fi_img_timestamp'  => $timeSHA1['time'] ? // false => NULL
					$dbw->timestamp( $timeSHA1['time'] ) : null
			);
		}
		# Our review entry
		$revRow = array(
			'fr_page_id'       => $this->getPage(),
			'fr_rev_id'	       => $this->getRevId(),
			'fr_user'	       => $this->getUser(),
			'fr_timestamp'     => $dbw->timestamp( $this->getTimestamp() ),
			'fr_comment'       => '', # not used anymore
			'fr_quality'       => $this->getQuality(),
			'fr_tags'	       => self::flattenRevisionTags( $this->getTags() ),
			'fr_text'	       => '', # not used anymore
			'fr_flags'	       => implode( ',', $this->mFlags ),
			'fr_img_name'      => $this->getFileName(),
			'fr_img_timestamp' => $dbw->timestampOrNull( $this->getFileTimestamp() ),
			'fr_img_sha1'      => $this->getFileSha1()
		);
		# Update flagged revisions table
		$dbw->replace( 'flaggedrevs',
			array( array( 'fr_page_id', 'fr_rev_id' ) ), $revRow, __METHOD__ );
		# Clear out any previous garbage...
		$dbw->delete( 'flaggedtemplates',
			array( 'ft_rev_id' => $this->getRevId() ), __METHOD__ );
		# ...and insert template version data
		if ( $tmpInsertRows ) {
			$dbw->insert( 'flaggedtemplates', $tmpInsertRows, __METHOD__, 'IGNORE' );
		}
		# Clear out any previous garbage...
		$dbw->delete( 'flaggedimages',
			array( 'fi_rev_id' => $this->getRevId() ), __METHOD__ );
		# ...and insert file version data
		if ( $fileInsertRows ) {
			$dbw->insert( 'flaggedimages', $fileInsertRows, __METHOD__, 'IGNORE' );
		}
		return true;
	}

	/**
	 * Get select fields for FlaggedRevision DB row (flaggedrevs/revision tables)
	 * @return array
	 */
	public static function selectFields() {
		return array_merge(
			Revision::selectFields(),
			array( 'fr_rev_id', 'fr_page_id', 'fr_user', 'fr_timestamp', 'fr_quality',
				'fr_tags', 'fr_flags', 'fr_img_name', 'fr_img_sha1', 'fr_img_timestamp' )
		);
	}

	/**
	 * @return integer revision ID
	 */
	public function getRevId() {
		return $this->mRevId;
	}

	/**
	 * @return Title title
	 */
	public function getTitle() {
		if ( is_null( $this->mTitle ) ) {
			$this->mTitle = Title::newFromId( $this->mPageId );
		}
		return $this->mTitle;
	}

	/**
	 * @return integer page ID
	 */
	public function getPage() {
		return $this->mPageId;
	}

	/**
	 * Get timestamp of review
	 * @return string revision timestamp in MW format
	 */
	public function getTimestamp() {
		return wfTimestamp( TS_MW, $this->mTimestamp );
	}

	/**
	 * Get the corresponding revision
	 * @return Revision
	 */
	public function getRevision() {
		if ( is_null( $this->mRevision ) ) {
			# Get corresponding revision
			$rev = Revision::newFromId( $this->mRevId );
			# Save to cache
			$this->mRevision = $rev ? $rev : false;
		}
		return $this->mRevision;
	}

	/**
	 * Check if the corresponding revision is the current revision
	 * Note: here for convenience
	 * @return bool
	 */
	public function revIsCurrent() {
		$rev = $this->getRevision(); // corresponding revision
		return ( $rev ? $rev->isCurrent() : false );
	}

	/**
	 * Get timestamp of the corresponding revision
	 * Note: here for convenience
	 * @return string revision timestamp in MW format
	 */
	public function getRevTimestamp() {
		$rev = $this->getRevision(); // corresponding revision
		return ( $rev ? $rev->getTimestamp() : "0" );
	}

	/**
	 * @return integer the user ID of the reviewer
	 */
	public function getUser() {
		return $this->mUser;
	}

	/**
	 * @return integer quality level (FR_CHECKED,FR_QUALITY,FR_PRISTINE)
	 */
	public function getQuality() {
		return $this->mQuality;
	}

	/**
	 * @return array tag metadata
	 */
	public function getTags() {
		return $this->mTags;
	}

	/**
	 * @return string, filename accosciated with this revision.
	 * This returns NULL for non-image page revisions.
	 */
	public function getFileName() {
		return $this->mFileName;
	}

	/**
	 * @return string, sha1 key accosciated with this revision.
	 * This returns NULL for non-image page revisions.
	 */
	public function getFileSha1() {
		return $this->mFileSha1;
	}

	/**
	 * @return string, timestamp accosciated with this revision.
	 * This returns NULL for non-image page revisions.
	 */
	public function getFileTimestamp() {
		return wfTimestampOrNull( TS_MW, $this->mFileTimestamp );
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function userCanSetFlags( $user ) {
		return FlaggedRevs::userCanSetFlags( $user, $this->mTags );
	}

	/**
	 * Get original template versions at time of review
	 * @param int $flags FR_MASTER
	 * @return array template versions (ns -> dbKey -> rev Id)
	 * Note: 0 used for template rev Id if it didn't exist
	 */
	public function getTemplateVersions( $flags = 0 ) {
		if ( $this->mTemplates == null ) {
			$this->mTemplates = array();
			$db = ( $flags & FR_MASTER ) ?
				wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
			$res = $db->select( 'flaggedtemplates',
				array( 'ft_namespace', 'ft_title', 'ft_tmp_rev_id' ),
				array( 'ft_rev_id' => $this->getRevId() ),
				__METHOD__
			);
			foreach ( $res as $row ) {
				if ( !isset( $this->mTemplates[$row->ft_namespace] ) ) {
					$this->mTemplates[$row->ft_namespace] = array();
				}
				$this->mTemplates[$row->ft_namespace][$row->ft_title] = $row->ft_tmp_rev_id;
			}
		}
		return $this->mTemplates;
	}

	/**
	 * Get original template versions at time of review
	 * @param int $flags FR_MASTER
	 * @return array file versions (dbKey => array('time' => MW timestamp,'sha1' => sha1) )
	 * Note: false used for file timestamp/sha1 if it didn't exist
	 */
	public function getFileVersions( $flags = 0 ) {
		if ( $this->mFiles == null ) {
			$this->mFiles = array();
			$db = ( $flags & FR_MASTER ) ?
				wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
			$res = $db->select( 'flaggedimages',
				array( 'fi_name', 'fi_img_timestamp', 'fi_img_sha1' ),
				array( 'fi_rev_id' => $this->getRevId() ),
				__METHOD__
			);
			foreach ( $res as $row ) {
				$reviewedTS = $reviewedSha1 = false;
				$fi_img_timestamp = trim( $row->fi_img_timestamp ); // may have \0's
				if ( $fi_img_timestamp ) {
					$reviewedTS = wfTimestamp( TS_MW, $fi_img_timestamp );
					$reviewedSha1 = strval( $row->fi_img_sha1 );
				}
				$this->mFiles[$row->fi_name] = array();
				$this->mFiles[$row->fi_name]['time'] = $reviewedTS;
				$this->mFiles[$row->fi_name]['sha1'] = $reviewedSha1;
			}
		}
		return $this->mFiles;
	}

	/**
	 * Get the current stable version of the templates used at time of review
	 * @param int $flags FR_MASTER
	 * @return array template versions (ns -> dbKey -> rev Id)
	 * Note: 0 used for template rev Id if it doesn't exist
	 */
	public function getStableTemplateVersions( $flags = 0 ) {
		if ( $this->mStableTemplates == null ) {
			$this->mStableTemplates = array();
			$db = ( $flags & FR_MASTER ) ?
				wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
			$res = $db->select(
				array( 'flaggedtemplates', 'page', 'flaggedpages' ),
				array( 'ft_namespace', 'ft_title', 'fp_stable' ),
				array( 'ft_rev_id' => $this->getRevId() ),
				__METHOD__,
				array(),
				array(
					'page' => array( 'LEFT JOIN',
						'page_namespace = ft_namespace AND page_title = ft_title'),
					'flaggedpages' => array( 'LEFT JOIN', 'fp_page_id = page_id' )
				)
			);
			foreach ( $res as $row ) {
				if ( !isset( $this->mStableTemplates[$row->ft_namespace] ) ) {
					$this->mStableTemplates[$row->ft_namespace] = array();
				}
				$revId = (int)$row->fp_stable; // 0 => none
				$this->mStableTemplates[$row->ft_namespace][$row->ft_title] = $revId;
			}
		}
		return $this->mStableTemplates;
	}

	/**
	 * Get the current stable version of the files used at time of review
	 * @param int $flags FR_MASTER
	 * @return array file versions (dbKey => array('time' => MW timestamp,'sha1' => sha1) )
	 * Note: false used for file timestamp/sha1 if it didn't exist
	 */
	public function getStableFileVersions( $flags = 0 ) {
		if ( $this->mStableFiles == null ) {
			$this->mStableFiles = array();
			$db = ( $flags & FR_MASTER ) ?
				wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
			$res = $db->select(
				array( 'flaggedimages', 'page', 'flaggedpages', 'flaggedrevs' ),
				array( 'fi_name', 'fr_img_timestamp', 'fr_img_sha1' ),
				array( 'fi_rev_id' => $this->getRevId() ),
				__METHOD__,
				array(),
				array(
					'page' 			=> array( 'LEFT JOIN',
					'page_namespace = ' . NS_FILE . ' AND page_title = fi_name' ),
					'flaggedpages' 	=> array( 'LEFT JOIN', 'fp_page_id = page_id' ),
					'flaggedrevs' 	=> array( 'LEFT JOIN',
						'fr_page_id = fp_page_id AND fr_rev_id = fp_stable' )
				)
			);
			foreach ( $res as $row ) {
				$reviewedTS = $reviewedSha1 = false;
				if ( $row->fr_img_timestamp ) {
					$reviewedTS = wfTimestamp( TS_MW, $row->fr_img_timestamp );
					$reviewedSha1 = strval( $row->fr_img_sha1 );
				}
				$this->mStableFiles[$row->fi_name] = array();
				$this->mStableFiles[$row->fi_name]['time'] = $reviewedTS;
				$this->mStableFiles[$row->fi_name]['sha1'] = $reviewedSha1;
			}
		}
		return $this->mStableFiles;
	}

	/*
	 * Fetch pending template changes for this reviewed page version.
	 * For each template, the "version used" (for stable parsing) is:
	 *    (a) (the latest rev) if FR_INCLUDES_CURRENT. Might be non-existing.
	 *    (b) newest( stable rev, rev at time of review ) if FR_INCLUDES_STABLE
	 *    (c) ( rev at time of review ) if FR_INCLUDES_FREEZE
	 * Pending changes exist for a template iff the template is used in
	 * the current rev of this page and one of the following holds:
	 *    (a) Current template is newer than the "version used" above (updated)
	 *    (b) Current template exists and the "version used" was non-existing (created)
	 *    (c) Current template doesn't exist and the "version used" existed (deleted)
	 *
	 * @return array of (template title, rev ID in reviewed version) tuples
	 */
	public function findPendingTemplateChanges() {
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_CURRENT ) {
			return array(); // short-circuit
		}
		$dbr = wfGetDB( DB_SLAVE );
		# Only get templates with stable or "review time" versions.
		# Note: ft_tmp_rev_id is nullable (for deadlinks), so use ft_title
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
			$reviewed = "ft_title IS NOT NULL OR fp_stable IS NOT NULL";
		} else {
			$reviewed = "ft_title IS NOT NULL";
		}
		$ret = $dbr->select(
			array( 'templatelinks', 'flaggedtemplates', 'page', 'flaggedpages' ),
			array( 'tl_namespace', 'tl_title', 'fp_stable', 'ft_tmp_rev_id', 'page_latest' ),
			array( 'tl_from' => $this->getPage(), $reviewed ), // current version templates
			__METHOD__,
			array(), /* OPTIONS */
			array(
				'flaggedtemplates'  => array( 'LEFT JOIN',
					array( 'ft_rev_id' => $this->getRevId(),
						'ft_namespace = tl_namespace AND ft_title = tl_title' ) ),
				'page' 			    => array( 'LEFT JOIN',
					'page_namespace = tl_namespace AND page_title = tl_title' ),
				'flaggedpages' 	    => array( 'LEFT JOIN', 'fp_page_id = page_id' )
			)
		);
		$tmpChanges = array();
		foreach ( $ret as $row ) {
			$title = Title::makeTitleSafe( $row->tl_namespace, $row->tl_title );
			$revIdDraft = (int)$row->page_latest; // may be NULL
			if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
				# Select newest of (stable rev, rev when reviewed) as "version used"
				$revIdStable = (int)max( $row->fp_stable, $row->ft_tmp_rev_id );
			} else {
				$revIdStable = (int)$row->ft_tmp_rev_id; // may be NULL
			}
			# Compare to current...
			$updated = false; // edited/created
			if ( $revIdDraft && $revIdDraft != $revIdStable ) {
				$dRev = Revision::newFromId( $revIdDraft );
				$sRev = Revision::newFromId( $revIdStable );
				# Don't do this for null edits (like protection) (bug 25919)
				if ( $dRev && $sRev && $dRev->getTextId() != $sRev->getTextId() ) {
					$updated = true;
				}
			}
			$deleted = ( !$revIdDraft && $revIdStable ); // later deleted
			if ( $deleted || $updated ) {
				$tmpChanges[] = array( $title, $revIdStable );
			}
		}
		return $tmpChanges;
	}

	/*
	 * Fetch pending file changes for this reviewed page version.
	 * For each file, the "version used" (for stable parsing) is:
	 *    (a) (the latest rev) if FR_INCLUDES_CURRENT. Might be non-existing.
	 *    (b) newest( stable rev, rev at time of review ) if FR_INCLUDES_STABLE
	 *    (c) ( rev at time of review ) if FR_INCLUDES_FREEZE
	 * Pending changes exist for a file iff the file is used in
	 * the current rev of this page and one of the following holds:
	 *    (a) Current file is newer than the "version used" above (updated)
	 *    (b) Current file exists and the "version used" was non-existing (created)
	 *    (c) Current file doesn't exist and the "version used" existed (deleted)
	 *
	 * @param string $noForeign Using 'noForeign' skips foreign file updates (bug 15748)
	 * @return array of (file title, MW file timestamp in reviewed version) tuples
	 */
	public function findPendingFileChanges( $noForeign = false ) {
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_CURRENT ) {
			return array(); // short-circuit
		}
		$dbr = wfGetDB( DB_SLAVE );
		# Only get templates with stable or "review time" versions.
		# Note: fi_img_timestamp is nullable (for deadlinks), so use fi_name
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
			$reviewed = "fi_name IS NOT NULL OR fr_img_timestamp IS NOT NULL";
		} else {
			$reviewed = "fi_name IS NOT NULL";
		}
		$ret = $dbr->select(
			array( 'imagelinks', 'flaggedimages', 'page', 'flaggedpages', 'flaggedrevs' ),
			array( 'il_to', 'fi_img_timestamp', 'fr_img_timestamp' ),
			array( 'il_from' => $this->getPage(), $reviewed ), // current version files
				__METHOD__,
			array(), /* OPTIONS */
			array(
				'flaggedimages' => array( 'LEFT JOIN',
					array( 'fi_rev_id' => $this->getRevId(), 'fi_name = il_to' ) ),
				'page' 			=> array( 'LEFT JOIN',
					'page_namespace = ' . NS_FILE . ' AND page_title = il_to' ),
				'flaggedpages' 	=> array( 'LEFT JOIN', 'fp_page_id = page_id' ),
				'flaggedrevs' 	=> array( 'LEFT JOIN',
					'fr_page_id = fp_page_id AND fr_rev_id = fp_stable' )
			)
		);
		$fileChanges = array();
		foreach ( $ret as $row ) {
			$title = Title::makeTitleSafe( NS_FILE, $row->il_to );
			$reviewedTS = trim( $row->fi_img_timestamp ); // may have \0's
			$reviewedTS = $reviewedTS ? wfTimestamp( TS_MW, $reviewedTS ) : null;
			if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
				$stableTS = wfTimestampOrNull( TS_MW, $row->fr_img_timestamp );
				# Select newest of (stable rev, rev when reviewed) as "version used"
				$tsStable = max( $stableTS, $reviewedTS );
			} else {
				$tsStable = $reviewedTS;
			}
			# Compare this version to the current version and check for things
			# that would make the stable version unsynced with the draft...
			$file = wfFindFile( $title ); // current file version
			if ( $file ) { // file exists
				if ( $noForeign === 'noForeign' && !$file->isLocal() ) {
					# Avoid counting edits to Commons files, which can effect
					# many pages, as there is no expedient way to review them.
					$updated = !$tsStable; // created (ignore new versions)
				} else {
					$updated = ( $file->getTimestamp() > $tsStable ); // edited/created
				}
				$deleted = $tsStable // included file deleted after review
					&& $file->getTimestamp() != $tsStable
					&& !wfFindFile( $title, array( 'time' => $tsStable ) );
			} else { // file doesn't exists
				$updated = false;
				$deleted = (bool)$tsStable; // included file deleted after review
			}
			if ( $deleted || $updated ) {
				$fileChanges[] = array( $title, $tsStable );
			}
		}
		return $fileChanges;
	}

	/**
	 * Get text of the corresponding revision
	 * @return string|false revision timestamp in MW format
	 */
	public function getRevText() {
		# Get corresponding revision
		$rev = $this->getRevision();
		$text = $rev ? $rev->getText() : false;
		return $text;
	}

	/**
	 * Get flags for a revision
	 * @param Title $title
	 * @param int $rev_id
	 * @param $flags, FR_MASTER
	 * @return array
	*/
	public static function getRevisionTags( Title $title, $rev_id, $flags = 0 ) {
		$db = ( $flags & FR_MASTER ) ?
			wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		$tags = (string)$db->selectField( 'flaggedrevs',
			'fr_tags',
			array( 'fr_rev_id' => $rev_id,
				'fr_page_id' => $title->getArticleId() ),
			__METHOD__
		);
		return FlaggedRevision::expandRevisionTags( strval( $tags ) );
	}

	/**
	 * @param int $page_id
	 * @param int $rev_id
	 * @param $flags, FR_MASTER
	 * @return mixed (int or false)
	 * Get quality of a revision
	 */
	public static function getRevQuality( $page_id, $rev_id, $flags = 0 ) {
		$db = ( $flags & FR_MASTER ) ?
			wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		return $db->selectField( 'flaggedrevs',
			'fr_quality',
			array( 'fr_page_id' => $page_id, 'fr_rev_id' => $rev_id ),
			__METHOD__,
			array( 'USE INDEX' => 'PRIMARY' )
		);
	}

	/**
	 * @param Title $title
	 * @param int $rev_id
	 * @param $flags, FR_MASTER
	 * @return bool
	 * Useful for quickly pinging to see if a revision is flagged
	 */
	public static function revIsFlagged( Title $title, $rev_id, $flags = 0 ) {
		$quality = self::getRevQuality( $title->getArticleId(), $rev_id, $flags );
		return ( $quality !== false );
	}

	/**
	 * Get flags for a revision
	 * @param string $tags
	 * @return array
	*/
	public static function expandRevisionTags( $tags ) {
		$flags = array();
		foreach ( FlaggedRevs::getTags() as $tag ) {
			$flags[$tag] = 0; // init all flags values to zero
		}
		$tags = str_replace( '\n', "\n", $tags ); // B/C, old broken rows
		// Tag string format is <tag:val\ntag:val\n...>
		$tags = explode( "\n", $tags );
		foreach ( $tags as $tuple ) {
			$set = explode( ':', $tuple, 2 );
			if ( count( $set ) == 2 ) {
				list( $tag, $value ) = $set;
				$value = max( 0, (int)$value ); // validate
				# Add only currently recognized tags
				if ( isset( $flags[$tag] ) ) {
					$levels = FlaggedRevs::getTagLevels( $tag );
					# If a level was removed, default to the highest...
					$flags[$tag] = min( $value, count( $levels ) - 1 );
				}
			}
		}
		return $flags;
	}

	/**
	 * Get flags for a revision
	 * @param array $tags
	 * @return string
	*/
	public static function flattenRevisionTags( array $tags ) {
		$flags = '';
		foreach ( $tags as $tag => $value ) {
			# Add only currently recognized ones
			if ( FlaggedRevs::getTagLevels( $tag ) ) {
				$flags .= $tag . ':' . intval( $value ) . "\n";
			}
		}
		return $flags;
	}
}
