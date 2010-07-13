<?php
# (c) Aaron Schulz 2010 GPL
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}
/**
 * Class containing revision review form business logic
 * Note: edit tokens are the responsibility of caller
 * Usage: (a) set ALL form params before doing anything else
 *		  (b) call ready() when all params are set
 *		  (c) call submit() as needed
 */
class RevisionReviewForm
{
	/* Form parameters which can be user given */
	protected $page = null;
	protected $rcid = 0;
	protected $approve = false;
	protected $unapprove = false;
	protected $oldid = 0;
	protected $templateParams = '';
	protected $imageParams = '';
	protected $fileVersion = '';
	protected $validatedParams = '';
	protected $notes = '';
	protected $comment = '';
	protected $dims = array();

	protected $unapprovedTags = 0;
	protected $oflags = array();
	protected $inputLock = 0; # Disallow bad submissions

	protected $user = null;
	protected $skin = null;

	public function __construct( $user ) {
		$this->user = $user;
		$this->skin = $user->getSkin();
		foreach ( FlaggedRevs::getTags() as $tag ) {
			$this->dims[$tag] = 0;
		}
	}

	public function getPage() {
		return $this->page;
	}

	public function setPage( Title $value ) {
		$this->trySet( $this->page, $value );
	}

	public function getRCId() {
		return $this->rcid;
	}

	public function setRCId( $value ) {
		$this->trySet( $this->rcid, (int)$value );
	}

	public function setApprove( $value ) {
		$this->trySet( $this->approve, $value );
	}

	public function setUnapprove( $value ) {
		$this->trySet( $this->unapprove, $value );
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

	public function getNotes() {
		return $this->notes;
	}

	public function setNotes( $value ) {
		if ( !FlaggedRevs::allowComments() || !$this->user->isAllowed( 'validate' ) ) {
			$value = '';
		}
		$this->trySet( $this->notes, $value );
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

	/**
	* Set a member field to a value if the fields are unlocked
	*/
	protected function trySet( &$field, $value ) {
		if ( $this->inputLock ) {
			throw new MWException( __CLASS__ . " fields cannot be set anymore.\n");
		} else {
			$field = $value; // still allowing input
		} 
	}

	/**
	* Signal that inputs are starting
	*/
	public function start() {
		$this->inputLock = 0;
	}

	/**
	* Signal that inputs are done and load old config
	* @return mixed (true on success, error string on target failure)
	*/
	public function ready() {
		$this->inputLock = 1;
		$status = $this->checkTarget();
		if ( $status !== true ) {
			return $status; // bad target
		}
		# Get the revision's current flags, if any
		$this->oflags = FlaggedRevs::getRevisionTags( $this->page, $this->oldid );
		return $status;
	}

	/*
	* Check that the target page is valid
	* @return mixed (true on success, error string on failure)
	*/
	protected function checkTarget() {
		if ( is_null( $this->page ) ) {
			return 'review_page_invalid';
		} elseif ( !$this->page->exists() ) {
			return 'review_page_notexists';
		}
		$fa = FlaggedArticle::getTitleInstance( $this->page );
		if ( !$fa->isReviewable() ) {
			return 'review_page_unreviewable';
		}
		return true;
	}

	/*
	* Verify and clean up parameters (e.g. from POST request).
	* @return mixed (true on success, error string on failure)
	*/
	protected function checkSettings() {
		$status = $this->checkTarget();
		if ( $status !== true ) {
			return $status; // bad target
		}
		if ( !$this->oldid ) {
			return 'review_no_oldid';
		}
		# Check that this is an approval or de-approval
		if ( $this->isApproval() === null ) {
			return 'review_param_missing'; // user didn't say
		}
		# Fill in implicit tag data for binary flag case
		if ( $iDims = $this->implicitDims() ) {
			$this->dims = $iDims;
		} else {
			foreach ( FlaggedRevs::getDimensions() as $tag => $levels ) {
				if ( $this->dims[$tag] === 0 ) {
					$this->unapprovedTags++;
				}
			}
		}
		# We must at least rate each category as 1, the minimum
		# Exception: we can rate ALL as unapproved to depreciate a revision
		if ( $this->unapprovedTags
			&& $this->unapprovedTags < count( FlaggedRevs::getDimensions() ) )
		{
			return 'review_too_low';
		}
		# Special token to discourage fiddling with template/files...
		if ( $this->isApproval() ) {
			$k = self::validationKey(
				$this->templateParams, $this->imageParams, $this->fileVersion, $this->oldid );
			if ( $this->validatedParams !== $k ) {
				return 'review_bad_key';
			}
		}
		# Check permissions and validate
		# FIXME: invalid vs denied
		if ( !FlaggedRevs::userCanSetFlags( $this->user, $this->dims, $this->oflags ) ) {
			return 'review_denied';
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

	public function isApproval() {
		# If all values are set to zero, this has been unapproved
		if ( FlaggedRevs::dimensionsEmpty() ) {
			if ( $this->approve && !$this->unapprove ) {
				return true; // no tags & approve param given
			} elseif ( $this->unapprove && !$this->approve ) {
				return false;
			}
			return null; // nothing valid asserted
		} else {
			foreach ( $this->dims as $quality => $value ) {
				if ( $value ) return true;
			}
			return false;
		}
	}

	/**
	* Submit the form parameters for the page config to the DB.
	* 
	* @return mixed (true on success, error string on failure)
	*/
	public function submit() {
		if ( !$this->inputLock ) {
			throw new MWException( __CLASS__ . " input fields not set yet.\n");
		}
		$status = $this->checkSettings();
		if ( $status !== true ) {
			return $status; // cannot submit - broken params
		}
		# Double-check permissions
		if ( !$this->isAllowed() ) {
			return 'review_denied';
		}
		# We can only approve actual revisions...
		if ( $this->isApproval() ) {
			$rev = Revision::newFromTitle( $this->page, $this->oldid );
			# Do not mess with archived/deleted revisions
			if ( is_null( $rev ) || $rev->mDeleted ) {
				return 'review_bad_oldid';
			}
			$status = $this->approveRevision( $rev );
		# We can only unapprove approved revisions...
		} else {
			$frev = FlaggedRevision::newFromTitle( $this->page, $this->oldid );
			# If we can't find this flagged rev, return to page???
			if ( is_null( $frev ) ) {
				return 'review_not_flagged';
			}
			$status = $this->unapproveRevision( $frev );
		}
		# Watch page if set to do so
		if ( $status === true ) {
			if ( $this->user->getOption( 'flaggedrevswatch' )
				&& !$this->page->userIsWatching() )
			{
				$this->user->addWatch( $this->page );
			}
		}
		return $status;
	}

	/**
	 * Adds or updates the flagged revision table for this page/id set
	 * @param Revision $rev
	 * @returns true on success, array of errors on failure
	 */
	private function approveRevision( Revision $rev ) {
		global $wgMemc, $wgParser;
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

		# Is this rev already flagged? (re-review)
		$oldFrev = null;
		if ( $oldSv ) { // stable rev exists
			if ( $rev->getId() == $oldSv->getRevId() ) {
				$oldFrev = $oldSv; // save a query
			} else {
				$oldFrev = FlaggedRevision::newFromTitle(
					$this->page, $rev->getId(), FR_MASTER );
			}
		}
		# Is this a duplicate review?
		if ( $oldFrev &&
			$oldFrev->getTags() == $flags && // tags => quality
			$oldFrev->getFileSha1() == $fileData['sha1'] &&
			$oldFrev->getFileTimestamp() == $fileData['timestamp'] &&
			$oldFrev->getComment() == $this->notes &&
			$oldFrev->getTemplateVersions( FR_MASTER ) == $tmpVersions &&
			$oldFrev->getFileVersions( FR_MASTER ) == $fileVersions )
		{
			wfProfileOut( __METHOD__ );
			return true; // don't record if the same
		}

		# Insert the review entry...
 		$flaggedRevision = new FlaggedRevision( array(
			'rev_id'        	=> $rev->getId(),
			'page_id'       	=> $rev->getPage(),
			'user'          	=> $this->user->getId(),
			'timestamp'     	=> wfTimestampNow(),
			'comment'       	=> $this->notes,
			'quality'       	=> $quality,
			'tags'          	=> FlaggedRevision::flattenRevisionTags( $flags ),
			'img_name'      	=> $fileData['name'],
			'img_timestamp' 	=> $fileData['timestamp'],
			'img_sha1'      	=> $fileData['sha1'],
			'templateVersions' 	=> $tmpVersions,
			'fileVersions'     	=> $fileVersions,
		) );
		$flaggedRevision->insertOn();
		# Update recent changes...
		self::updateRecentChanges( $this->page, $rev->getId(), $this->rcid, true );

		# Update the article review log...
		$oldSvId = $oldSv ? $oldSv->getRevId() : 0;
		FlaggedRevsLogs::updateLog( $this->page, $this->dims, $this->oflags,
			$this->comment, $this->oldid, $oldSvId, true );

		# Get the new stable version as of now
		$sv = FlaggedRevision::determineStable( $this->page, FR_MASTER/*consistent*/ );
		# Update page and tracking tables and clear cache
		$changed = FlaggedRevs::stableVersionUpdates( $this->page, $sv, $oldSv );
		if ( $changed ) {
			FlaggedRevs::HTMLCacheUpdates( $this->page ); // purge pages that use this page
		}

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

        $dbw = wfGetDB( DB_MASTER );
		# Delete from flaggedrevs table
		$dbw->delete( 'flaggedrevs',
			array( 'fr_page_id' => $frev->getPage(), 'fr_rev_id' => $frev->getRevId() ) );
		# Wipe versioning params
		$dbw->delete( 'flaggedtemplates', array( 'ft_rev_id' => $frev->getRevId() ) );
		$dbw->delete( 'flaggedimages', array( 'fi_rev_id' => $frev->getRevId() ) );
		# Update recent changes
		self::updateRecentChanges( $this->page, $frev->getRevId(), false, false );

		# Update the article review log
		$oldSvId = $oldSv ? $oldSv->getRevId() : 0;
		FlaggedRevsLogs::updateLog( $this->page, $this->dims, $this->oflags,
			$this->comment, $this->oldid, $oldSvId, false );

		# Get the new stable version as of now
		$sv = FlaggedRevision::determineStable( $this->page, FR_MASTER/*consistent*/ );
		# Update page and tracking tables and clear cache
		$changed = FlaggedRevs::stableVersionUpdates( $this->page, $sv, $oldSv );
		if ( $changed ) {
			FlaggedRevs::HTMLCacheUpdates( $this->page ); // purge pages that use this page
		}

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
		# Fall back to $wgProxyKey
		$key = $wgSecretKey ? $wgSecretKey : $wgProxyKey;
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
	 * @param FlaggedArticle $article
	 * @param array $templateIDs (from ParserOutput/OutputPage->mTemplateIds)
	 * @param array $imageSHA1Keys (from ParserOutput/OutputPage->fr_fileSHA1Keys)
	 * @returns array( templateParams, imageParams, fileVersion )
	 */
	public static function getIncludeParams(
		FlaggedArticle $article, array $templateIDs, array $imageSHA1Keys
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
			$imageParams .= $dbKey . "|" . $timeAndSHA1['ts'];
			$imageParams .= "|" . $timeAndSHA1['sha1'] . "#";
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
	 * @returns array( templateIds, fileSHA1Keys )
	 * templateIds like ParserOutput->mTemplateIds
	 * fileSHA1Keys like ParserOutput->fr_fileSHA1Keys
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
			list( $dbkey, $timestamp, $key ) = $m;
			# Get the file title
			$img_title = Title::makeTitle( NS_IMAGE, $dbkey ); // Normalize
			if ( is_null( $img_title ) ) {
				continue; // Page must be valid!
			}
			$fileSHA1Keys[$img_title->getDBkey()] = array();
			$fileSHA1Keys[$img_title->getDBkey()]['ts'] = $timestamp;
			$fileSHA1Keys[$img_title->getDBkey()]['sha1'] = $key;
		}
		return array( $templateIds, $fileSHA1Keys );
	}

	########## Common form & elements ##########
	// @TODO: move to some other class

	 /**
	 * Generates a brief review form for a page.
	 * @param User $user
	 * @param FlaggedArticle $article
	 * @param Revision $rev
	 * @param array $templateIDs
	 * @param array $imageSHA1Keys
	 * @param bool $stableDiff this is a diff-to-stable 
	 * @return mixed (string/false)
	 */
	public static function buildQuickReview(
		$user, FlaggedArticle $article, Revision $rev,
		$templateIDs, $imageSHA1Keys, $stableDiff = false
	) {
		global $wgRequest;
		if ( $rev->isDeleted( Revision::DELETED_TEXT ) ) {
			return false; # The revision must be valid and public
		}
		$id = $rev->getId();
		$skin = $user->getSkin();
		# Do we need to get inclusion IDs from parser output?
		$getPOut = !( $templateIDs && $imageSHA1Keys );

		# See if the version being displayed is flagged...
		$frev = FlaggedRevision::newFromTitle( $article->getTitle(), $id );
		$oldFlags = $frev
			? $frev->getTags() // existing tags
			: FlaggedRevision::expandRevisionTags( '' ); // unset tags
		# If we are reviewing updates to a page, start off with the stable revision's
		# flags. Otherwise, we just fill them in with the selected revision's flags.
		if ( $stableDiff ) {
			$srev = $article->getStableRev();
			$flags = $srev->getTags();
			# Check if user is allowed to renew the stable version.
			# If not, then get the flags for the new revision itself.
			if ( !FlaggedRevs::userCanSetFlags( $user, $oldFlags ) ) {
				$flags = $oldFlags;
			}
			$reviewNotes = $srev->getComment();
			# Re-review button is need for template/file only review case
			$allowRereview = ( $srev->getRevId() == $id && !$article->stableVersionIsSynced() );
		} else {
			$flags = $oldFlags;
			// Get existing notes to pre-fill field
			$reviewNotes = $frev ? $frev->getComment() : "";
			$allowRereview = false; // re-review button
		}

		# Disable form for unprivileged users
		$disabled = array();
		if ( !$article->getTitle()->quickUserCan( 'review' ) ||
			!$article->getTitle()->quickUserCan( 'edit' ) ||
			!FlaggedRevs::userCanSetFlags( $user, $flags ) )
		{
			$disabled = array( 'disabled' => 'disabled' );
		}

		# Begin form...
		$reviewTitle = SpecialPage::getTitleFor( 'RevisionReview' );
		$action = $reviewTitle->getLocalUrl( 'action=submit' );
		$params = array( 'method' => 'post', 'action' => $action, 'id' => 'mw-fr-reviewform' );
		$form = Xml::openElement( 'form', $params );
		$form .= Xml::openElement( 'fieldset',
			array( 'class' => 'flaggedrevs_reviewform noprint' ) );
		# Add appropriate legend text
		$legendMsg = ( FlaggedRevs::binaryFlagging() && $allowRereview )
			? 'revreview-reflag'
			: 'revreview-flag';
		$form .= Xml::openElement( 'legend', array( 'id' => 'mw-fr-reviewformlegend' ) );
		$form .= "<strong>" . wfMsgHtml( $legendMsg ) . "</strong>";
		$form .= Xml::closeElement( 'legend' ) . "\n";
		# Show explanatory text
		if ( !FlaggedRevs::lowProfileUI() ) {
			$form .= wfMsgExt( 'revreview-text', array( 'parse' ) );
		}

		if ( $disabled ) {
			$form .= Xml::openElement( 'div', array( 'class' => 'fr-rating-controls-disabled',
				'id' => 'fr-rating-controls-disabled' ) );
		} else {
			$form .= Xml::openElement( 'div', array( 'class' => 'fr-rating-controls',
				'id' => 'fr-rating-controls' ) );
		}

		# Add main checkboxes/selects
		$form .= Xml::openElement( 'span', array( 'id' => 'mw-fr-ratingselects' ) );
		$form .= self::ratingInputs( $user, $flags, (bool)$disabled, (bool)$frev );
		$form .= Xml::closeElement( 'span' );
		# Add review notes input
		if ( FlaggedRevs::allowComments() && $user->isAllowed( 'validate' ) ) {
			$form .= "<div id='mw-fr-notebox'>\n";
			$form .= "<p>" . wfMsgHtml( 'revreview-notes' ) . "</p>\n";
			$params = array( 'name' => 'wpNotes', 'id' => 'wpNotes',
				'class' => 'fr-notes-box', 'rows' => '2', 'cols' => '80' ) + $disabled;
			$form .= Xml::openElement( 'textarea', $params ) .
				htmlspecialchars( $reviewNotes ) .
				Xml::closeElement( 'textarea' ) . "\n";
			$form .= "</div>\n";
		}

		# Get versions of templates/files used
		$imageParams = $templateParams = $fileVersion = '';
		if ( $getPOut ) {
			$pOutput = false;
			# Current version: try parser cache
			if ( $rev->isCurrent() ) {
				$parserCache = ParserCache::singleton();
				$pOutput = $parserCache->get( $article, $user );
			}
			# Otherwise (or on cache miss), parse the rev text...
			if ( !$pOutput || !isset( $pOutput->fr_fileSHA1Keys ) ) {
				global $wgParser, $wgEnableParserCache;
				$text = $rev->getText();
				$title = $article->getTitle();
				$options = FlaggedRevs::makeParserOptions();
				$pOutput = $wgParser->parse(
					$text, $title, $options, true, true, $article->getLatest() );
				# Might as well save the cache while we're at it
				if ( $rev->isCurrent() && $wgEnableParserCache ) {
					$parserCache->save( $pOutput, $article, $user );
				}
			}
			$templateIDs = $pOutput->mTemplateIds;
			$imageSHA1Keys = $pOutput->fr_fileSHA1Keys;
		}
		list( $templateParams, $imageParams, $fileVersion ) =
			RevisionReviewForm::getIncludeParams( $article, $templateIDs, $imageSHA1Keys );

		$form .= Xml::openElement( 'span', array( 'style' => 'white-space: nowrap;' ) );
		# Hide comment input if needed
		if ( !$disabled ) {
			if ( count( FlaggedRevs::getDimensions() ) > 1 )
				$form .= "<br />"; // Don't put too much on one line
			$form .= "<span id='mw-fr-commentbox' style='clear:both'>" .
				Xml::inputLabel( wfMsg( 'revreview-log' ), 'wpReason', 'wpReason', 35, '',
					array( 'class' => 'fr-comment-box' ) ) . "&#160;&#160;&#160;</span>";
		}
		# Add the submit buttons
		$form .= self::submitButtons( $frev, (bool)$disabled, $allowRereview );
		# Show stability log if there is anything interesting...
		if ( $article->isPageLocked() ) {
			$form .= ' ' . FlaggedRevsXML::logToggle( 'revreview-log-toggle-show' );
		}
		$form .= Xml::closeElement( 'span' );
		# ..add the actual stability log body here
	    if ( $article->isPageLocked() ) {
			$form .= FlaggedRevsXML::stabilityLogExcerpt( $article );
		}
		$form .= Xml::closeElement( 'div' ) . "\n";

		# Hidden params
		$form .= Xml::hidden( 'title', $reviewTitle->getPrefixedText() ) . "\n";
		$form .= Xml::hidden( 'target', $article->getTitle()->getPrefixedDBKey() ) . "\n";
		$form .= Xml::hidden( 'oldid', $id ) . "\n";
		$form .= Xml::hidden( 'action', 'submit' ) . "\n";
		$form .= Xml::hidden( 'wpEditToken', $user->editToken() ) . "\n";
		# Add review parameters
		$form .= Xml::hidden( 'templateParams', $templateParams ) . "\n";
		$form .= Xml::hidden( 'imageParams', $imageParams ) . "\n";
		$form .= Xml::hidden( 'fileVersion', $fileVersion ) . "\n";
		# Pass this in if given; useful for new page patrol
		$form .= Xml::hidden( 'rcid', $wgRequest->getVal( 'rcid' ) ) . "\n";
		# Special token to discourage fiddling...
		$checkCode = self::validationKey(
			$templateParams, $imageParams, $fileVersion, $id
		);
		$form .= Xml::hidden( 'validatedParams', $checkCode ) . "\n";

		$form .= Xml::closeElement( 'fieldset' );
		$form .= Xml::closeElement( 'form' );
		return $form;
	}

	/**
	 * @param User $user
	 * @param array $flags, selected flags
	 * @param bool $disabled, form disabled
	 * @param bool $reviewed, rev already reviewed
	 * @returns string
	 * Generates a main tag inputs (checkboxes/radios/selects) for review form
	 */
	private static function ratingInputs( $user, $flags, $disabled, $reviewed ) {
		$form = '';
		# Get all available tags for this page/user
		list( $labels, $minLevels ) = self::ratingFormTags( $user, $flags );
		if ( $labels === false ) {
			$disabled = true; // a tag is unsettable
		}
		$dimensions = FlaggedRevs::getDimensions();
		$tags = array_keys( $dimensions );
		# If there are no tags, make one checkbox to approve/unapprove
		if ( FlaggedRevs::binaryFlagging() ) {
			return '';
		}
		$items = array();
		# Build rating form...
		if ( $disabled ) {
			// Display the value for each tag as text
			foreach ( $dimensions as $quality => $levels ) {
				$selected = isset( $flags[$quality] ) ? $flags[$quality] : 0;
				$items[] = "<b>" . FlaggedRevs::getTagMsg( $quality ) . ":</b> " .
					FlaggedRevs::getTagValueMsg( $quality, $selected );
			}
		} else {
			$size = count( $labels, 1 ) - count( $labels );
			foreach ( $labels as $quality => $levels ) {
				$item = '';
				$numLevels = count( $levels );
				$minLevel = $minLevels[$quality];
				# Determine the level selected by default
				if ( !empty( $flags[$quality] ) && isset( $levels[$flags[$quality]] ) ) {
					$selected = $flags[$quality]; // valid non-zero value
				} else {
					$selected = $minLevel;
				}
				# Show label as needed
				if ( !FlaggedRevs::binaryFlagging() ) {
					$item .= "<b>" . Xml::tags( 'label', array( 'for' => "wp$quality" ),
						FlaggedRevs::getTagMsg( $quality ) ) . ":</b>\n";
				}
				# If the sum of qualities of all flags is above 6, use drop down boxes.
				# 6 is an arbitrary value choosen according to screen space and usability.
				if ( $size > 6 ) {
					$attribs = array( 'name' => "wp$quality", 'id' => "wp$quality",
						'onchange' => "FlaggedRevs.updateRatingForm()" );
					$item .= Xml::openElement( 'select', $attribs );
					foreach ( $levels as $i => $name ) {
						$optionClass = array( 'class' => "fr-rating-option-$i" );
						$item .= Xml::option( FlaggedRevs::getTagMsg( $name ), $i,
							( $i == $selected ), $optionClass ) . "\n";
					}
					$item .= Xml::closeElement( 'select' ) . "\n";
				# If there are more than two levels, current user gets radio buttons
				} elseif ( $numLevels > 2 ) {
					foreach ( $levels as $i => $name ) {
						$attribs = array( 'class' => "fr-rating-option-$i",
							'onchange' => "FlaggedRevs.updateRatingForm()" );
						$item .= Xml::radioLabel( FlaggedRevs::getTagMsg( $name ), "wp$quality",
							$i,	"wp$quality" . $i, ( $i == $selected ), $attribs ) . "\n";
					}
				# Otherwise make checkboxes (two levels available for current user)
				} else if ( $numLevels == 2 ) {
					$i = $minLevel;
					$attribs = array( 'class' => "fr-rating-option-$i",
						'onchange' => "FlaggedRevs.updateRatingForm()" );
					$attribs = $attribs + array( 'value' => $i );
					$item .= Xml::checkLabel( wfMsg( 'revreview-' . $levels[$i] ),
						"wp$quality", "wp$quality", ( $selected == $i ), $attribs ) . "\n";
				}
				$items[] = $item;
			}
		}
		# Wrap visible controls in a span
		$form = Xml::openElement( 'span', array( 'class' => 'fr-rating-options' ) ) . "\n";
		$form .= implode( '&#160;&#160;&#160;', $items );
		$form .= Xml::closeElement( 'span' ) . "\n";
		return $form;
	}

	private static function ratingFormTags( $user, $selected ) {
		$labels = array();
		$minLevels = array();
		# Build up all levels available to user
		foreach ( FlaggedRevs::getDimensions() as $tag => $levels ) {
			if ( isset( $selected[$tag] ) &&
				!FlaggedRevs::userCanSetTag( $user, $tag, $selected[$tag] ) )
			{
				return array( false, false ); // form will have to be disabled
			}
			$labels[$tag] = array(); // applicable tag levels
			$minLevels[$tag] = false; // first non-zero level number
			foreach ( $levels as $i => $msg ) {
				# Some levels may be restricted or not applicable...
				if ( !FlaggedRevs::userCanSetTag( $user, $tag, $i ) ) {
					continue; // skip this level
				} else if ( $i > 0 && !$minLevels[$tag] ) {
					$minLevels[$tag] = $i; // first non-zero level number
				}
				$labels[$tag][$i] = $msg; // set label
			}
			if ( !$minLevels[$tag] ) {
				return array( false, false ); // form will have to be disabled
			}
		}
		return array( $labels, $minLevels );
	}

	/**
	 * @param FlaggedRevision $frev, the flagged revision, if any
	 * @param bool $disabled, is the form disabled?
	 * @param bool $rereview, force the review button to be usable?
	 * @returns string
	 * Generates one or two button submit for the review form
	 */
	private static function submitButtons( $frev, $disabled, $rereview = false ) {
		$disAttrib = array( 'disabled' => 'disabled' );
		# Add the submit button
		if ( FlaggedRevs::binaryFlagging() ) {
			# We may want to re-review to change the notes ($wgFlaggedRevsComments)
			$s = Xml::submitButton( wfMsg( 'revreview-submit-review' ),
				array(
					'name'  	=> 'wpApprove',
					'id' 		=> 'mw-fr-submitreview',
					'accesskey' => wfMsg( 'revreview-ak-review' ),
					'title' 	=> wfMsg( 'revreview-tt-flag' ) . ' [' .
						wfMsg( 'revreview-ak-review' ) . ']'
				) + ( ( $disabled || ( $frev && !$rereview ) ) ? $disAttrib : array() )
			);
			$s .= ' ';
			$s .= Xml::submitButton( wfMsg( 'revreview-submit-unreview' ),
				array(
					'name'  => 'wpUnapprove',
					'id' 	=> 'mw-fr-submitunreview',
					'title' => wfMsg( 'revreview-tt-unflag' )
				) + ( ( $disabled || !$frev ) ? $disAttrib : array() )
			);
		} else {
			$s = Xml::submitButton( wfMsg( 'revreview-submit' ),
				array(
					'id' 		=> 'mw-fr-submitreview',
					'accesskey' => wfMsg( 'revreview-ak-review' ),
					'title' 	=> wfMsg( 'revreview-tt-review' ) . ' [' .
						wfMsg( 'revreview-ak-review' ) . ']'
				) + ( $disabled ? $disAttrib : array() )
			);
		}
		return $s;
	}

	public function approvalSuccessHTML( $showlinks = false ) {
		# Show success message
		$form = "<div class='plainlinks'>";
		$form .= wfMsgExt( 'revreview-successful', 'parse',
			$this->page->getPrefixedText(), $this->page->getPrefixedUrl() );
		$form .= wfMsgExt( 'revreview-stable1', 'parse',
			$this->page->getPrefixedUrl(), $this->getOldId() );
		$form .= "</div>";
		# Handy links to special pages
		if ( $showlinks && $this->user->isAllowed( 'unreviewedpages' ) ) {
			$form .= $this->getSpecialLinks();
		}
		return $form;
	}

	public function deapprovalSuccessHTML( $showlinks = false ) {
		# Show success message
		$form = "<div class='plainlinks'>";
		$form .= wfMsgExt( 'revreview-successful2', 'parse',
			$this->page->getPrefixedText(), $this->page->getPrefixedUrl() );
		$form .= wfMsgExt( 'revreview-stable2', 'parse',
			$this->page->getPrefixedUrl(), $this->getOldId() );
		$form .= "</div>";
		# Handy links to special pages
		if ( $showlinks && $this->user->isAllowed( 'unreviewedpages' ) ) {
			$form .= $this->getSpecialLinks();
		}
		return $form;
	}

	private function getSpecialLinks() {
		$s = '<p>' . wfMsg( 'returnto',
			$this->skin->makeLinkObj( SpecialPage::getTitleFor( 'UnreviewedPages' ) ) ) . '</p>';
		$s .= '<p>' . wfMsg( 'returnto',
			$this->skin->makeLinkObj( SpecialPage::getTitleFor( 'OldReviewedPages' ) ) ) . '</p>';
		return $s;
	}
}
