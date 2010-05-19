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

	protected $skin = null;

	public function __construct() {
		global $wgUser;
		$this->skin = $wgUser->getSkin();
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
		return $this->page;
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
		global $wgUser;
		if ( !FlaggedRevs::allowComments() || !$wgUser->isAllowed( 'validate' ) ) {
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
		# Special token to discourage fiddling...
		$k = self::validationKey(
			$this->templateParams, $this->imageParams, $this->fileVersion, $this->oldid );
		if ( $this->validatedParams !== $k ) {
			return 'review_bad_key';
		}
		# Check permissions and validate
		# FIXME: invalid vs denied
		if ( !FlaggedRevs::userCanSetFlags( $this->dims, $this->oflags ) ) {
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
		global $wgUser;
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
				return 'review_bad_oldid';
			}
			$status = $this->unapproveRevision( $frev );
		}
		# Watch page if set to do so
		if ( $status === true ) {
			if ( $wgUser->getOption( 'flaggedrevswatch' ) && !$this->page->userIsWatching() ) {
				$wgUser->addWatch( $this->page );
			}
		}
		return $status;
	}

	/**
	 * Adds or updates the flagged revision table for this page/id set
	 * @param Revision $rev
	 * @returns true on success, array of errors on failure
	 */
	private function approveRevision( $rev ) {
		global $wgUser, $wgMemc, $wgParser, $wgEnableParserCache;
		wfProfileIn( __METHOD__ );
		
		$article = new Article( $this->page );

		$quality = 0;
		if ( FlaggedRevs::isQuality( $this->dims ) ) {
			$quality = FlaggedRevs::isPristine( $this->dims ) ? 2 : 1;
		}
		# Our flags
		$flags = $this->dims;
		# Some validation vars to make sure nothing changed during
		$lastTempId = 0;
		$lastImgTime = "0";
		# Our template version pointers
		$tmpset = $tmpParams = array();
		$templateMap = explode( '#', trim( $this->templateParams ) );
		foreach ( $templateMap as $template ) {
			if ( !$template )
				continue;

			$m = explode( '|', $template, 2 );
			if ( !isset( $m[0] ) || !isset( $m[1] ) || !$m[0] )
				continue;

			list( $prefixed_text, $rev_id ) = $m;

			$tmp_title = Title::newFromText( $prefixed_text ); // Normalize this to be sure...
			if ( is_null( $tmp_title ) )
				continue; // Page must be valid!

			if ( $rev_id > $lastTempId )
				$lastTempId = $rev_id;

			$tmpset[] = array(
				'ft_rev_id' 	=> $rev->getId(),
				'ft_namespace'  => $tmp_title->getNamespace(),
				'ft_title' 		=> $tmp_title->getDBkey(),
				'ft_tmp_rev_id' => $rev_id
			);
			if ( !isset( $tmpParams[$tmp_title->getNamespace()] ) ) {
				$tmpParams[$tmp_title->getNamespace()] = array();
			}
			$tmpParams[$tmp_title->getNamespace()][$tmp_title->getDBkey()] = $rev_id;
		}
		# Our image version pointers
		$imgset = $imgParams = array();
		$imageMap = explode( '#', trim( $this->imageParams ) );
		foreach ( $imageMap as $image ) {
			if ( !$image )
				continue;
			$m = explode( '|', $image, 3 );
			# Expand our parameters ... <name>#<timestamp>#<key>
			if ( !isset( $m[0] ) || !isset( $m[1] ) || !isset( $m[2] ) || !$m[0] )
				continue;

			list( $dbkey, $timestamp, $key ) = $m;

			$img_title = Title::makeTitle( NS_IMAGE, $dbkey ); // Normalize
			if ( is_null( $img_title ) )
				continue; // Page must be valid!

			if ( $timestamp > $lastImgTime )
				$lastImgTime = $timestamp;

			$imgset[] = array(
				'fi_rev_id' 		=> $rev->getId(),
				'fi_name' 			=> $img_title->getDBkey(),
				'fi_img_timestamp'  => $timestamp,
				'fi_img_sha1' 		=> $key
			);
			if ( !isset( $imgParams[$img_title->getDBkey()] ) ) {
				$imgParams[$img_title->getDBkey()] = array();
			}
			$imgParams[$img_title->getDBkey()][$timestamp] = $key;
		}
		# If this is an image page, store corresponding file info
		$fileData = array();
		if ( $this->page->getNamespace() == NS_IMAGE && $this->fileVersion ) {
			$data = explode( '#', $this->fileVersion, 2 );
			if ( count( $data ) == 2 ) {
				$fileData['name'] = $this->page->getDBkey();
				$fileData['timestamp'] = $data[0];
				$fileData['sha1'] = $data[1];
			}
		}
		
		# Get current stable version ID (for logging)
		$oldSv = FlaggedRevision::newFromStable( $this->page, FR_MASTER );
		$oldSvId = $oldSv ? $oldSv->getRevId() : 0;
		
		# Is this rev already flagged?
		$flaggedOutput = false;
		$oldfrev = FlaggedRevision::newFromTitle( $this->page, $rev->getId(), FR_MASTER );
		if ( $oldfrev ) {
			$flaggedOutput = FlaggedRevs::parseStableText( $article,
				$oldfrev->getRevText(), $oldfrev->getRevId() );
		}
		
		# Be loose on templates that includes other files/templates dynamically.
		# Strict checking breaks randomized images/metatemplates...(bug 14580)
		global $wgUseCurrentTemplates, $wgUseCurrentImages;
		$mustMatch = !( $wgUseCurrentTemplates && $wgUseCurrentImages );
		
		# Set our versioning params cache
		FlaggedRevs::setIncludeVersionCache( $rev->getId(), $tmpParams, $imgParams );
		# Parse the text and check if all templates/files match up
		$text = $rev->getText();
		$stableOutput = FlaggedRevs::parseStableText( $article, $text, $rev->getId() );
		$err =& $stableOutput->fr_includeErrors;
		if ( $mustMatch ) { // if template/files must all be specified...
			if ( !empty( $err )
				|| $stableOutput->fr_newestImageTime > $lastImgTime
				|| $stableOutput->fr_newestTemplateID > $lastTempId )
			{
				wfProfileOut( __METHOD__ );
				return $err; // return templates/files with no version specified
			}
        }
		# Clear our versioning params cache
		FlaggedRevs::clearIncludeVersionCache( $rev->getId() );
		
		# Is this a duplicate review?
		if ( $oldfrev && $flaggedOutput ) {
			$synced = true;
			if ( $stableOutput->fr_newestImageTime != $flaggedOutput->fr_newestImageTime )
				$synced = false;
			elseif ( $stableOutput->fr_newestTemplateID != $flaggedOutput->fr_newestTemplateID )
				$synced = false;
			elseif ( $oldfrev->getTags() != $flags )
				$synced = false;
			elseif ( $oldfrev->getFileSha1() != @$fileData['sha1'] )
				$synced = false;
			elseif ( $oldfrev->getComment() != $this->notes )
				$synced = false;
			elseif ( $oldfrev->getQuality() != $quality )
				$synced = false;
			# Don't review if the same
			if ( $synced ) {
				wfProfileOut( __METHOD__ );
				return true;
			}
		}

		$dbw = wfGetDB( DB_MASTER );
		# Our review entry
 		$flaggedRevision = new FlaggedRevision( array(
			'fr_rev_id'        => $rev->getId(),
			'fr_page_id'       => $rev->getPage(),
			'fr_user'          => $wgUser->getId(),
			'fr_timestamp'     => wfTimestampNow(),
			'fr_comment'       => $this->notes,
			'fr_quality'       => $quality,
			'fr_tags'          => FlaggedRevision::flattenRevisionTags( $flags ),
			'fr_img_name'      => $fileData ? $fileData['name'] : null,
			'fr_img_timestamp' => $fileData ? $fileData['timestamp'] : null,
			'fr_img_sha1'      => $fileData ? $fileData['sha1'] : null
		) );

		$dbw->begin();
		$flaggedRevision->insertOn( $tmpset, $imgset );
		# Avoid any lag issues
		$this->page->resetArticleId( $rev->getPage() );
		# Update recent changes
		self::updateRecentChanges( $this->page, $rev->getId(), $this->rcid, true );
		# Update the article review log
		FlaggedRevsLogs::updateLog( $this->page, $this->dims, $this->oflags,
			$this->comment, $this->oldid, $oldSvId, true );

		# Update the links tables as the stable version may now be the default page.
		# Try using the parser cache first since we didn't actually edit the current version.
		$parserCache = ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if ( !$poutput
			|| !isset( $poutput->fr_newestTemplateID )
			|| !isset( $poutput->fr_newestImageTime ) )
		{
			$options = FlaggedRevs::makeParserOptions();
			$poutput = $wgParser->parse( $article->getContent(), $article->mTitle, $options );
		}
		# Prepare for a link tracking update
		$u = new LinksUpdate( $this->page, $poutput );
		# If we know that this is now the new stable version 
		# (which it probably is), save it to the stable cache...
		$sv = FlaggedRevision::newFromStable( $this->page, FR_MASTER/*consistent*/ );
		if ( $sv && $sv->getRevId() == $rev->getId() ) {
			global $wgParserCacheExpireTime;
			$this->page->invalidateCache();
			# Update stable cache with the revision we reviewed.
			# Don't cache redirects; it would go unused and complicate things.
			if ( !Title::newFromRedirect( $text ) ) {
				FlaggedRevs::updatePageCache( $article, $wgUser, $stableOutput );
			}
			# We can set the sync cache key already
			$includesSynced = true;
			if ( $poutput->fr_newestImageTime > $stableOutput->fr_newestImageTime ) {
				$includesSynced = false;
			} elseif ( $poutput->fr_newestTemplateID > $stableOutput->fr_newestTemplateID ) {
				$includesSynced = false;
			}
			$u->fr_stableRev = $sv; // no need to re-fetch this!
			$u->fr_stableParserOut = $stableOutput; // no need to re-fetch this!
			# We can set the sync cache key already.
			$key = wfMemcKey( 'flaggedrevs', 'includesSynced', $article->getId() );
			$data = FlaggedRevs::makeMemcObj( $includesSynced ? "true" : "false" );
			$wgMemc->set( $key, $data, $wgParserCacheExpireTime );
		} else {
			# Get the old stable cache
			$stableOutput = FlaggedRevs::getPageCache( $article, $wgUser );
			# Clear the cache...(for page histories)
			$this->page->invalidateCache();
			if ( $stableOutput !== false ) {
				# Reset stable cache if it existed, since we know it is the same.
				FlaggedRevs::updatePageCache( $article, $wgUser, $stableOutput );
			}
		}
		# Update link tracking. This will trigger extraLinksUpdate()...
		$u->doUpdate();

		$dbw->commit();
		# Purge cache/squids for this page and any page that uses it
		Article::onArticleEdit( $this->page );

		wfProfileOut( __METHOD__ );
        return true;
    }

	/**
	 * @param FlaggedRevision $frev
	 * Removes flagged revision data for this page/id set
	 */
	private function unapproveRevision( $frev ) {
		global $wgUser, $wgParser, $wgMemc;
		wfProfileIn( __METHOD__ );
		
        $dbw = wfGetDB( DB_MASTER );
		$dbw->begin();
		# Delete from flaggedrevs table
		$dbw->delete( 'flaggedrevs',
			array( 'fr_page_id' => $frev->getPage(), 'fr_rev_id' => $frev->getRevId() ) );
		# Wipe versioning params
		$dbw->delete( 'flaggedtemplates', array( 'ft_rev_id' => $frev->getRevId() ) );
		$dbw->delete( 'flaggedimages', array( 'fi_rev_id' => $frev->getRevId() ) );
		# Update recent changes
		self::updateRecentChanges( $this->page, $frev->getRevId(), false, false );

		# Get current stable version ID (for logging)
		$oldSv = FlaggedRevision::newFromStable( $this->page, FR_MASTER );
		$oldSvId = $oldSv ? $oldSv->getRevId() : 0;

		# Update the article review log
		FlaggedRevsLogs::updateLog( $this->page, $this->dims, $this->oflags,
			$this->comment, $this->oldid, $oldSvId, false );

		$article = new Article( $this->page );
		# Update the links tables as a new stable version
		# may now be the default page.
		$parserCache = ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if ( $poutput == false ) {
			$text = $article->getContent();
			$options = FlaggedRevs::makeParserOptions();
			$poutput = $wgParser->parse( $text, $article->mTitle, $options );
		}
		$u = new LinksUpdate( $this->page, $poutput );
		$u->doUpdate();

		# Clear the cache...
		$this->page->invalidateCache();
		# Purge cache/squids for this page and any page that uses it
		$dbw->commit();
		Article::onArticleEdit( $article->getTitle() );

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
		global $wgReviewCodes;
		# Fall back to $wgSecretKey/$wgProxyKey
		if ( empty( $wgReviewCodes ) ) {
			global $wgSecretKey, $wgProxyKey;
			$key = $wgSecretKey ? $wgSecretKey : $wgProxyKey;
			$p = md5( $key . $imgP . $tmpP . $rid . $imgV );
		} else {
			$p = md5( $wgReviewCodes[0] . $imgP . $rid . $tmpP . $imgV . $wgReviewCodes[1] );
		}
		return $p;
	}

	public static function updateRecentChanges( $title, $revId, $rcId = false, $patrol = true ) {
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
	
	########## Common form elements ##########

	public function approvalSuccessHTML( $showlinks = false ) {
		global $wgUser;
		# Show success message
		$form = "<div class='plainlinks'>";
		$form .= wfMsgExt( 'revreview-successful', 'parse',
			$this->page->getPrefixedText(), $this->page->getPrefixedUrl() );
		$form .= wfMsgExt( 'revreview-stable1', 'parse',
			$this->page->getPrefixedUrl(), $this->getOldId() );
		$form .= "</div>";
		# Handy links to special pages
		if ( $showlinks && $wgUser->isAllowed( 'unreviewedpages' ) ) {
			$form .= $this->getSpecialLinks();
		}
		return $form;
	}

	public function deapprovalSuccessHTML( $showlinks = false ) {
		global $wgUser;
		# Show success message
		$form = "<div class='plainlinks'>";
		$form .= wfMsgExt( 'revreview-successful2', 'parse',
			$this->page->getPrefixedText(), $this->page->getPrefixedUrl() );
		$form .= wfMsgExt( 'revreview-stable2', 'parse',
			$this->page->getPrefixedUrl(), $this->getOldId() );
		$form .= "</div>";
		# Handy links to special pages
		if ( $showlinks && $wgUser->isAllowed( 'unreviewedpages' ) ) {
			$form .= $this->getSpecialLinks();
		}
		return $form;
	}

	public function syncFailureHTML( array $status, $showlinks = false ) {
		$form = wfMsgExt( 'revreview-changed', 'parse', $this->page->getPrefixedText() );
		$form .= "<ul>";
		foreach ( $status as $n => $text ) {
			$form .= "<li><i>$text</i></li>\n";
		}
		$form .= "</ul>";
		if ( $showlinks ) {
			$form .= wfMsg( 'returnto', $this->skin->makeLinkObj( $this->page ) );
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
