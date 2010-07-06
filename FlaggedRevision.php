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
	private $mComment;
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
	 * @param mixed $row (DB row or array)
     * @return void
	 */
	public function __construct( $row ) {
		if ( is_object( $row ) ) {
			$this->mRevId = intval( $row->fr_rev_id );
			$this->mPageId = intval( $row->fr_page_id );
			$this->mTimestamp = $row->fr_timestamp;
			$this->mComment = $row->fr_comment;
			$this->mQuality = intval( $row->fr_quality );
			$this->mTags = self::expandRevisionTags( strval( $row->fr_tags ) );
			# Image page revision relevant params
			$this->mFileName = $row->fr_img_name ? $row->fr_img_name : null;
			$this->mFileSha1 = $row->fr_img_sha1 ? $row->fr_img_sha1 : null;
			$this->mFileTimestamp = $row->fr_img_timestamp ?
				$row->fr_img_timestamp : null;
			$this->mUser = intval( $row->fr_user );
			# Optional fields
			$this->mTitle = isset( $row->page_namespace ) && isset( $row->page_title )
				? Title::makeTitleSafe( $row->page_namespace, $row->page_title )
				: null;
			$this->mFlags = isset( $row->fr_flags ) ?
				explode( ',', $row->fr_flags ) : null;
		} elseif ( is_array( $row ) ) {
			$this->mRevId = intval( $row['rev_id'] );
			$this->mPageId = intval( $row['page_id'] );
			$this->mTimestamp = $row['timestamp'];
			$this->mComment = $row['comment'];
			$this->mQuality = intval( $row['quality'] );
			$this->mTags = self::expandRevisionTags( strval( $row['tags'] ) );
			# Image page revision relevant params
			$this->mFileName = $row['img_name'] ? $row['img_name'] : null;
			$this->mFileSha1 = $row['img_sha1'] ? $row['img_sha1'] : null;
			$this->mFileTimestamp = $row['img_timestamp'] ?
				$row['img_timestamp'] : null;
			$this->mUser = intval( $row['user'] );
			# Optional fields
			$this->mFlags = isset( $row['flags'] ) ?
				explode( ',', $row['flags'] ) : null;
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
	 * @param int $flags FR_MASTER
	 * @return mixed FlaggedRevision (null on failure)
	 */
	public static function newFromTitle( Title $title, $revId, $flags = 0 ) {
        if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
            return null; // short-circuit
        }
		$columns = self::selectFields();
		$options = array();
		# User master/slave as appropriate
		if ( $flags & FR_FOR_UPDATE || $flags & FR_MASTER ) {
			$db = wfGetDB( DB_MASTER );
			if ( $flags & FR_FOR_UPDATE ) $options[] = 'FOR UPDATE';
		} else {
			$db = wfGetDB( DB_SLAVE );
		}
		$pageId = $title->getArticleID( $flags & FR_FOR_UPDATE ? GAID_FOR_UPDATE : 0 );
		# Short-circuit query
		if ( !$pageId ) {
			return null;
		}
		# Skip deleted revisions
		$row = $db->selectRow( array( 'flaggedrevs', 'revision' ),
			$columns,
			array( 'fr_page_id' => $pageId,
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
	 * @param int $flags FR_MASTER
     * @param array $config, optional page config (use to skip queries)
	 * @return mixed FlaggedRevision (null on failure)
	 */
	public static function newFromStable( Title $title, $flags = 0, $config = array() ) {
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
            return null; // short-circuit
        }
        $columns = self::selectFields();
		$options = array();
		$pageId = $title->getArticleID( $flags & FR_MASTER ? GAID_FOR_UPDATE : 0 );
		if ( !$pageId ) {
			return null; // short-circuit query
		}
		# User master/slave as appropriate
		if ( $flags & FR_FOR_UPDATE || $flags & FR_MASTER ) {
			$db = wfGetDB( DB_MASTER );
			if ( $flags & FR_FOR_UPDATE ) $options[] = 'FOR UPDATE';
		} else {
			$db = wfGetDB( DB_SLAVE );
		}
		# Check tracking tables
		$row = $db->selectRow(
			array( 'flaggedpages', 'flaggedrevs' ),
			$columns,
			array( 'fp_page_id' => $pageId,
				'fr_page_id = fp_page_id',
				'fr_rev_id = fp_stable'
			),
			__METHOD__,
			$options
		);
		if ( !$row ) {
			return null;
		}
		$frev = new self( $row );
		$frev->mTitle = $title;
		return $frev;
	}

	/**
     * Get a FlaggedRevision of the stable version of a title.
	 * Skips tracking tables to figure out new stable version.
	 * @param Title $title, page title
	 * @param int $flags FR_MASTER
     * @param array $config, optional page config (use to skip queries)
	 * @return mixed FlaggedRevision (null on failure)
	 */
	public static function determineStable( Title $title, $flags = 0, $config = array() ) {
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
            return null; // short-circuit
        }
        $columns = self::selectFields();
		$options = array();
		$pageId = $title->getArticleID( $flags & FR_FOR_UPDATE ? GAID_FOR_UPDATE : 0 );
		if ( !$pageId ) {
			return null; // short-circuit query
		}
		# User master/slave as appropriate
		if ( $flags & FR_FOR_UPDATE || $flags & FR_MASTER ) {
			$db = wfGetDB( DB_MASTER );
			if ( $flags & FR_FOR_UPDATE ) $options[] = 'FOR UPDATE';
		} else {
			$db = wfGetDB( DB_SLAVE );
		}
		# Get visiblity settings...
        if ( empty( $config ) ) {
           $config = FlaggedRevs::getPageVisibilitySettings( $title, $flags );
        }
		if ( !$config['override'] && FlaggedRevs::useOnlyIfProtected() ) {
			return null; // page is not reviewable; no stable version
		}
		$row = null;
		$options['ORDER BY'] = 'fr_rev_id DESC';
		# Look for the latest pristine revision...
		if ( FlaggedRevs::pristineVersions() && $config['select'] != FLAGGED_VIS_LATEST ) {
			$prow = $db->selectRow(
				array( 'flaggedrevs', 'revision' ),
				$columns,
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
		if ( $row && $config['select'] == FLAGGED_VIS_PRISTINE ) {
			// we have what we want already
		# Look for the latest quality revision...
		} elseif ( FlaggedRevs::qualityVersions() && $config['select'] != FLAGGED_VIS_LATEST ) {
			// If we found a pristine rev above, this one must be newer...
			$newerClause = $row ? "fr_rev_id > {$row->fr_rev_id}" : "1 = 1";
			$qrow = $db->selectRow(
				array( 'flaggedrevs', 'revision' ),
				$columns,
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
				$columns,
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
	* @param array $tmpRows template version rows
	* @param array $fileRows file version rows
	* @param bool $auto autopatrolled
	* @return bool success
	*/
	public function insertOn( $auto = false ) {
        $dbw = wfGetDB( DB_MASTER );
        # Set any text flags
        $textFlags = 'dynamic';
		if ( $auto ) $textFlags .= ',auto';
		$this->mFlags = explode( ',', $textFlags );
        # Build the inclusion data chunks
        $tmpInsertRows = array();
		foreach ( $this->getTemplateVersions() as $namespace => $titleAndID ) {
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
		foreach ( $this->getFileVersions() as $dbkey => $timeSHA1 ) {
			$fileInsertRows[] = array(
				'fi_rev_id' 		=> $this->getRevId(),
				'fi_name' 			=> $dbkey,
				'fi_img_sha1' 		=> strval( $timeSHA1['sha1'] ),
				// b/c: fi_img_timestamp DEFAULT either NULL (new) or '' (old)
				'fi_img_timestamp'  => $timeSHA1['ts'] ? $dbw->timestamp( $timeSHA1['ts'] ) : ''
			);
		}
		# Our review entry
		$revRow = array(
			'fr_page_id'       => $this->getPage(),
			'fr_rev_id'	       => $this->getRevId(),
			'fr_user'	       => $this->getUser(),
			'fr_timestamp'     => $dbw->timestamp( $this->getTimestamp() ),
			'fr_comment'       => $this->getComment(),
			'fr_quality'       => $this->getQuality(),
			'fr_tags'	       => self::flattenRevisionTags( $this->getTags() ),
			'fr_text'	       => '', # not used anymore
			'fr_flags'	       => $textFlags,
			'fr_img_name'      => $this->getFileName(),
			'fr_img_timestamp' => $dbw->timestampOrNull( $this->getFileTimestamp() ),
			'fr_img_sha1'      => $this->getFileSha1()
		);
		# Update flagged revisions table
		$dbw->replace( 'flaggedrevs', array( array( 'fr_page_id', 'fr_rev_id' ) ),
            $revRow, __METHOD__ );
		# Clear out any previous garbage.
		# We want to be able to use this for tracking...
		$dbw->delete( 'flaggedtemplates',
            array( 'ft_rev_id' => $this->getRevId() ), __METHOD__ );
		$dbw->delete( 'flaggedimages',
            array( 'fi_rev_id' => $this->getRevId() ), __METHOD__ );
		# Update our versioning params
		if ( $tmpInsertRows ) {
			$dbw->insert( 'flaggedtemplates', $tmpInsertRows, __METHOD__, 'IGNORE' );
		}
		if ( $fileInsertRows ) {
			$dbw->insert( 'flaggedimages', $fileInsertRows, __METHOD__, 'IGNORE' );
		}
		return true;
	}
	
	/**
	 * @return Array basic select fields (not including text/text flags)
	 */
	public static function selectFields() {
		return array(
			'fr_rev_id', 'fr_page_id', 'fr_user', 'fr_timestamp',
            'fr_comment', 'fr_quality', 'fr_tags', 'fr_img_name',
			'fr_img_sha1', 'fr_img_timestamp', 'fr_flags'
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
	 * Get timestamp of the corresponding revision
	 * @return string revision timestamp in MW format
	 */
	public function getRevTimestamp() {
		# Get corresponding revision
		$rev = $this->getRevision();
		$timestamp = $rev ? $rev->getTimestamp() : "0";
		return $timestamp;
	}

	/**
	 * @return string review comment
	 */
	public function getComment() {
		return $this->mComment;
	}
	
	/**
	 * @return integer the user ID of the reviewer
	 */
	public function getUser() {
		return $this->mUser;
	}

	/**
	 * @return integer revision timestamp in MW format
	 */
	public function getQuality() {
		return $this->mQuality;
	}

	/**
	 * @return Array tag metadata
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
		return $this->mFileTimestamp;
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
	 * @return Array template versions (ns -> dbKey -> rev Id)
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
			while ( $row = $res->fetchObject() ) {
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
	 * @return Array file versions (dbKey => array('ts' => MW timestamp,'sha1' => sha1) )
     * Note: '0' used for file timestamp if it didn't exist ('' for sha1)
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
			while ( $row = $res->fetchObject() ) {
                $reviewedTS = trim( $row->fi_img_timestamp ); // may be ''/NULL
                $reviewedTS = $reviewedTS ? wfTimestamp( TS_MW, $reviewedTS ) : '0';
				$this->mFiles[$row->fi_name] = array();
                $this->mFiles[$row->fi_name]['ts'] = $reviewedTS;
                $this->mFiles[$row->fi_name]['sha1'] = $row->fi_img_sha1;
			}
		}
		return $this->mFiles;
	}
	
	/**
	 * Get the current stable version of the templates used at time of review
	 * @param int $flags FR_MASTER
	 * @return Array template versions (ns -> dbKey -> rev Id)
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
			while ( $row = $res->fetchObject() ) {
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
	 * @return Array file versions (dbKey => array('ts' => MW timestamp,'sha1' => sha1) )
     * Note: '0' used for file timestamp if it doesn't exist ('' for sha1)
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
			while ( $row = $res->fetchObject() ) {
                $reviewedTS = '0';
                $reviewedSha1 = '';
                if ( $row->fr_img_timestamp ) {
                    $reviewedTS = wfTimestamp( TS_MW, $reviewedTS );
                    $reviewedSha1 = strval( $row->fr_img_sha1 );
                }
				$this->mStableFiles[$row->fi_name] = array();
                $this->mStableFiles[$row->fi_name]['ts'] = $reviewedTS;
                $this->mStableFiles[$row->fi_name]['sha1'] = $reviewedSha1;
			}
		}
		return $this->mStableFiles;
	}
    
	/*
	 * Fetch pending template changes for this reviewed page version.
	 * For each template, the "version used" is:
	 *    (a) (the latest rev) if FR_INCLUDES_CURRENT. Might be non-existing.
	 *    (b) newest( stable rev, rev at time of review ) if FR_INCLUDES_STABLE
	 *    (c) ( rev at time of review ) if FR_INCLUDES_FREEZE
	 * Pending changes exist for a template iff the template is used in
	 * the current rev of this page and one of the following holds:
	 *	  (a) Current template is newer than the "version used" above (updated)
	 *	  (b) Current template exists and the "version used" was non-existing (created)
	 *    (c) Current template doesn't exist and the "version used" existed (deleted)
	 *
	 * @return Array of (template title, rev ID in reviewed version) tuples
	 */
	public function findPendingTemplateChanges() {
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_CURRENT ) {
			return array(); // short-circuit
		}
		$dbr = wfGetDB( DB_SLAVE );
		$ret = $dbr->select(
			array( 'flaggedtemplates', 'templatelinks', 'page', 'flaggedpages' ),
			array( 'ft_namespace', 'ft_title', 'fp_stable', 'ft_tmp_rev_id', 'page_latest' ),
			array( 'ft_rev_id' => $this->getRevId() ), // template was in reviewed rev
			__METHOD__,
			array(), /* OPTIONS */
			array(
				'templatelinks' => array( 'INNER JOIN', // used in current rev
					array( 'tl_from' => $this->getPage(),
						'tl_namespace = ft_namespace AND tl_title = ft_title' ) ),
				'page' 			=> array( 'LEFT JOIN',
					'page_namespace = ft_namespace AND page_title = ft_title' ),
				'flaggedpages' 	=> array( 'LEFT JOIN', 'fp_page_id = page_id' )
			)
		);
		$tmpChanges = array();
		while ( $row = $dbr->fetchObject( $ret ) ) {
			$title = Title::makeTitleSafe( $row->ft_namespace, $row->ft_title );
			$revIdDraft = (int)$row->page_latest; // may be NULL
			if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
				# Select newest of (stable rev, rev when reviewed) when parsing
				$revIdStable = max( $row->fp_stable, $row->ft_tmp_rev_id );
			} else {
				$revIdStable = (int)$row->ft_tmp_rev_id;
			}
			# Compare to current...
			$deleted = ( !$revIdDraft && $revIdStable ); // later deleted
			$updated = ( $revIdDraft && $revIdDraft > $revIdStable ); // updated/created
			if ( $deleted || $updated ) {
				$tmpChanges[] = array( $title, $revIdStable );
			}
		}
		return $tmpChanges;
	}
	
	/*
	 * Fetch pending file changes for this reviewed page version.
	 * For each file, the version used is:
	 *    (a) (the latest rev) if FR_INCLUDES_CURRENT. Might be non-existing.
	 *    (b) newest( stable rev, rev at time of review ) if FR_INCLUDES_STABLE
	 *    (c) ( rev at time of review ) if FR_INCLUDES_FREEZE
	 * Pending changes exist for a file iff the file is used in
	 * the current rev of this page and one of the following holds:
	 *	  (a) Current file is newer than the "version used" above (updated)
	 *	  (b) Current file exists and the "version used" was non-existing (created)
	 *    (c) Current file doesn't exist and the "version used" existed (deleted)
	 *
	 * @param string $noForeign Use 'noForeign' to skip Commons images (bug 15748)
	 * @return Array of (file title, MW file timestamp in reviewed version) tuples
	 */
	public function findPendingFileChanges( $noForeign = false ) {
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_CURRENT ) {
			return array(); // short-circuit
		}
		$dbr = wfGetDB( DB_SLAVE );
		$ret = $dbr->select(
			array( 'flaggedimages', 'imagelinks', 'page', 'flaggedpages', 'flaggedrevs' ),
			array( 'fi_name', 'fi_img_timestamp', 'fr_img_timestamp' ),
			array( 'fi_rev_id' => $this->getRevId() ), // template was in reviewed rev
				__METHOD__,
			array(), /* OPTIONS */
			array(
				'imagelinks' 	=> array( 'INNER JOIN', // used in current rev
					array( 'il_from' => $this->getPage(), 'il_to = fi_name' ) ),
				'page' 			=> array( 'LEFT JOIN',
					'page_namespace = ' . NS_FILE . ' AND page_title = fi_name' ),
				'flaggedpages' 	=> array( 'LEFT JOIN', 'fp_page_id = page_id' ),
				'flaggedrevs' 	=> array( 'LEFT JOIN',
					'fr_page_id = fp_page_id AND fr_rev_id = fp_stable' ) )
		);
		$fileChanges = array();
		while ( $row = $dbr->fetchObject( $ret ) ) {
			$title = Title::makeTitleSafe( NS_FILE, $row->fi_name );
			$reviewedTS = trim( $row->fi_img_timestamp ); // may be ''/NULL
			if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
				# Select newest of (stable rev, rev when reviewed) when parsing
				$tsStable = $row->fr_img_timestamp >= $reviewedTS
					? $row->fr_img_timestamp
					: $reviewedTS;
			} else {
				$tsStable = $reviewedTS;
			}
			# Compare to current...
			$file = wfFindFile( $title ); // current file version
			$deleted = ( !$file && $tsStable ); // later deleted
			if ( $file && ( $noForeign !== 'noForeign' || $file->isLocal() ) ) {
				$updated = ( $file->getTimestamp() > $tsStable ); // updated/created
			} else {
				$updated = false;
			}
			if ( $deleted || $updated ) {
				$fileChanges[] = array( $title, $tsStable );
			}
		}
		return $fileChanges;
	}
	
	/**
	 * Get text of the corresponding revision
	 * @return mixed (string/false) revision timestamp in MW format
	 */
	public function getRevText() {
		# Get corresponding revision
		$rev = $this->getRevision();
		$text = $rev ? $rev->getText() : false;
		return $text;
	}
	
	/**
	 * Get flags for a revision
	 * @param string $tags
	 * @return Array
	*/
	public static function expandRevisionTags( $tags ) {
		# Set all flags to zero
		$flags = array();
		foreach ( FlaggedRevs::getDimensions() as $tag => $levels ) {
			$flags[$tag] = 0;
		}
		$tags = str_replace( '\n', "\n", $tags ); // B/C, old broken rows
		$tags = explode( "\n", $tags );
		foreach ( $tags as $tuple ) {
			$set = explode( ':', $tuple, 2 );
			if ( count( $set ) == 2 ) {
				list( $tag, $value ) = $set;
				$value = intval( $value );
				# Add only currently recognized ones
				if ( isset( $flags[$tag] ) ) {
					# If a level was removed, default to the highest
					$flags[$tag] = $value < count( $levels ) ?
						$value : count( $levels ) - 1;
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
