<?php

class FlaggedRevsHooks {
	
	/**
	* Remove 'patrol' and 'autopatrol' rights. Reviewing revisions will patrol them as well.
	*/
	public static function stripPatrolRights( $user, &$rights ) {
		# Use only our extension mechanisms
		foreach( $rights as $n => $right ) {
			if( $right == 'patrol' || $right == 'autopatrol' ) {
				unset($rights[$n]);
			}
		}
		return true;
	}

	/**
	* Add FlaggedRevs css/js.
	*/
	public static function injectStyleAndJS() {
		global $wgOut;
		# Don't double-load
		if( $wgOut->hasHeadItem( 'FlaggedRevs' ) || !$wgOut->isArticleRelated() ) {
			return true;
		}
		$fa = FlaggedArticle::getGlobalInstance();
		# Try to only add to relevant pages
		if( !$fa || (!$fa->isReviewable(true) && !$fa->isRateable() ) ) {
			return true;
		}
		global $wgScriptPath, $wgJsMimeType, $wgFlaggedRevsStylePath, $wgFlaggedRevStyleVersion;
		# Load required messages
		wfLoadExtensionMessages( 'FlaggedRevs' );
		
		$stylePath = str_replace( '$wgScriptPath', $wgScriptPath, $wgFlaggedRevsStylePath );
		$rTags = FlaggedRevs::getJSTagParams();
		$fTags = FlaggedRevs::getJSFeedbackParams();
		$frev = $fa->getStableRev();
		$stableId = $frev ? $frev->getRevId() : 0;

		$encCssFile = htmlspecialchars( "$stylePath/flaggedrevs.css?$wgFlaggedRevStyleVersion" );
		$encJsFile = htmlspecialchars( "$stylePath/flaggedrevs.js?$wgFlaggedRevStyleVersion" );

		$wgOut->addExtensionStyle( $encCssFile );

		$ajaxFeedback = Xml::encodeJsVar( (object) array( 
			'sendingMsg' => wfMsgHtml('readerfeedback-submitting'), 
			'sentMsg' => wfMsgHtml('readerfeedback-finished') 
			)
		);
		$ajaxReview = Xml::encodeJsVar( (object) array( 
			'sendingMsg' => wfMsgHtml('revreview-submitting'), 
			'sentMsg' => wfMsgHtml('revreview-finished'),
			'actioncomplete' => wfMsgHtml('actioncomplete')
			)
		);

		$head = <<<EOT
<script type="$wgJsMimeType">
var wgFlaggedRevsParams = $rTags;
var wgFlaggedRevsParams2 = $fTags;
var wgStableRevisionId = $stableId;
var wgAjaxFeedback = $ajaxFeedback
var wgAjaxReview = $ajaxReview
</script>
<script type="$wgJsMimeType" src="$encJsFile"></script>

EOT;
		$wgOut->addHeadItem( 'FlaggedRevs', $head );
		return true;
	}
	
	/**
	* Add FlaggedRevs css for relevant special pages.
	*/
	public static function InjectStyleForSpecial() {
		global $wgTitle, $wgOut, $wgUser;
		if( empty($wgTitle) || $wgTitle->getNamespace() !== -1 ) {
			return true;
		}
		$spPages = array( 'UnreviewedPages', 'OldReviewedPages', 'Watchlist', 'Recentchanges', 
			'Contributions', 'RatingHistory' );
		foreach( $spPages as $n => $key ) {
			if( $wgTitle->isSpecial( $key ) ) {
				global $wgScriptPath, $wgFlaggedRevsStylePath, $wgFlaggedRevStyleVersion;
				$stylePath = str_replace( '$wgScriptPath', $wgScriptPath, $wgFlaggedRevsStylePath );
				$encCssFile = htmlspecialchars( "$stylePath/flaggedrevs.css?$wgFlaggedRevStyleVersion" );
				$wgOut->addExtensionStyle( $encCssFile );
				break;
			}
		}
		return true;
	}

	/**
	* Update flaggedrevs table on revision restore
	*/
	public static function updateFromRestore( $title, $revision, &$oldPageID ) {
		$dbw = wfGetDB( DB_MASTER );
		# Some revisions may have had null rev_id values stored when deleted.
		# This hook is called after insertOn() however, in which case it is set
		# as a new one.
		$dbw->update( 'flaggedrevs',
			array( 'fr_page_id' => $revision->getPage() ),
			array( 'fr_page_id' => $oldPageID,
				'fr_rev_id' => $revision->getID() ),
			__METHOD__ );

		return true;
	}

	/**
	* Update flaggedrevs table on article history merge
	*/
	public static function updateFromMerge( $sourceTitle, $destTitle ) {
		wfProfileIn( __METHOD__ );
	
		$oldPageID = $sourceTitle->getArticleID();
		$newPageID = $destTitle->getArticleID();
		# Get flagged revisions from old page id that point to destination page
		$dbw = wfGetDB( DB_MASTER );
		$result = $dbw->select( array('flaggedrevs','revision'),
			array( 'fr_rev_id' ),
			array( 'fr_page_id' => $oldPageID,
				'fr_rev_id = rev_id',
				'rev_page' => $newPageID ),
			__METHOD__ );
		# Update these rows
		$revIDs = array();
		while( $row = $dbw->fetchObject($result) ) {
			$revIDs[] = $row->fr_rev_id;
		}
		if( !empty($revIDs) ) {
			$dbw->update( 'flaggedrevs',
				array( 'fr_page_id' => $newPageID ),
				array( 'fr_page_id' => $oldPageID,
					'fr_rev_id' => $revIDs ),
				__METHOD__ );
		}
		# Update pages
		FlaggedRevs::titleLinksUpdate( $sourceTitle );
		FlaggedRevs::titleLinksUpdate( $destTitle );

		wfProfileOut( __METHOD__ );
		return true;
	}
	
	public static function onArticleDelete( &$article, &$user, $reason, $id ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'flaggedpage_config',
			array( 'fpc_page_id' => $article->getID() ),
			__METHOD__ );

		return true;
	}

	/**
	* Inject stable links on LinksUpdate
	*/
	public static function extraLinksUpdate( $linksUpdate ) {
		if( !FlaggedRevs::isPageReviewable( $linksUpdate->mTitle ) ) {
			return true;
		}
		wfProfileIn( __METHOD__ );
		$dbw = wfGetDB( DB_MASTER );
		$pageId = $linksUpdate->mTitle->getArticleId();
		# Check if this page has a stable version...
		$sv = isset($u->fr_stableRev) ? // Try the process cache...
			$u->fr_stableRev : FlaggedRevision::newFromStable( $linksUpdate->mTitle, FR_MASTER );
		# Empty flagged revs data for this page if there is no stable version
		if( !$sv ) {
			$dbw->delete( 'flaggedpages', array( 'fp_page_id' => $pageId ), __METHOD__ );
			$dbw->delete( 'flaggedrevs_tracking', array( 'ftr_from' => $pageId ), __METHOD__ );
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Try the process cache...
		$article = new Article( $linksUpdate->mTitle );
		if( isset($linksUpdate->fr_stableParserOut) ) {
			$parserOut = $linksUpdate->fr_stableParserOut;
		} else {
			# Try stable version cache. This should be updated before this is called.
			$parserOut = FlaggedRevs::getPageCache( $article );
			if( $parserOut==false ) {
				$text = $sv->getTextForParse();
				# Parse the text
				$parserOut = FlaggedRevs::parseStableText( $article, $text, $sv->getRevId() );
			}
		}
		# Update page fields
		FlaggedRevs::updateArticleOn( $article, $sv->getRevId() );
		# We only care about links that are only in the stable version
		$links = array();
		foreach( $parserOut->getLinks() as $ns => $titles ) {
			foreach( $titles as $title => $id ) {
				if( !isset($linksUpdate->mLinks[$ns]) || !isset($linksUpdate->mLinks[$ns][$title]) ) {
					self::addLink( $links, $ns, $title );
				}
			}
		}
		foreach( $parserOut->getImages() as $image => $n ) {
			if( !isset($linksUpdate->mImages[$image]) ) {
				self::addLink( $links, NS_IMAGE, $image );
			}
		}
		foreach( $parserOut->getTemplates() as $ns => $titles ) {
			foreach( $titles as $title => $id ) {
				if( !isset($linksUpdate->mTemplates[$ns]) || !isset($linksUpdate->mTemplates[$ns][$title]) ) {
					self::addLink( $links, $ns, $title );
				}
			}
		}
		foreach( $parserOut->getCategories() as $category => $sort ) {
            if( !isset($linksUpdate->mCategories[$category]) ) {
                self::addLink( $links, NS_CATEGORY, $category );
			}
        }
		# Get any link tracking changes
		$existing = self::getExistingLinks( $pageId );
		$insertions = self::getLinkInsertions( $existing, $links, $pageId );
		$deletions = self::getLinkDeletions( $existing, $links );
		# Delete removed links
		if( $clause = self::makeWhereFrom2d( $deletions ) ) {
			$where = array( 'ftr_from' => $pageId );
			$where[] = $clause;
			$dbw->delete( 'flaggedrevs_tracking', $where, __METHOD__ );
		}
		# Add any new links
		if ( count($insertions) ) {
			$dbw->insert( 'flaggedrevs_tracking', $insertions, __METHOD__, 'IGNORE' );
		}
		wfProfileOut( __METHOD__ );
		return true;
	}
	
	protected static function addLink( &$links, $ns, $dbKey ) {
		if( !isset($links[$ns]) ) {
			$links[$ns] = array();
		}
		$links[$ns][$dbKey] = 1;
	}
	
	protected static function getExistingLinks( $pageId ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 'flaggedrevs_tracking', 
			array( 'ftr_namespace', 'ftr_title' ),
			array( 'ftr_from' => $pageId ), 
			__METHOD__ );
		$arr = array();
		while( $row = $dbr->fetchObject( $res ) ) {
			if( !isset( $arr[$row->ftr_namespace] ) ) {
				$arr[$row->ftr_namespace] = array();
			}
			$arr[$row->ftr_namespace][$row->ftr_title] = 1;
		}
		$dbr->freeResult( $res );
		return $arr;
	}
	
	protected static function makeWhereFrom2d( &$arr ) {
		$lb = new LinkBatch();
		$lb->setArray( $arr );
		return $lb->constructSet( 'ftr', wfGetDB( DB_SLAVE ) );
	}
	
	protected static function getLinkInsertions( $existing, $new, $pageId ) {
		$arr = array();
		foreach( $new as $ns => $dbkeys ) {
			$diffs = isset( $existing[$ns] ) ? array_diff_key( $dbkeys, $existing[$ns] ) : $dbkeys;
			foreach( $diffs as $dbk => $id ) {
				$arr[] = array(
					'ftr_from'      => $pageId,
					'ftr_namespace' => $ns,
					'ftr_title'     => $dbk
				);
			}
		}
		return $arr;
	}
	
	protected static function getLinkDeletions( $existing, $new ) {
		$del = array();
		foreach( $existing as $ns => $dbkeys ) {
			if( isset( $new[$ns] ) ) {
				$del[$ns] = array_diff_key( $existing[$ns], $new[$ns] );
			} else {
				$del[$ns] = $existing[$ns];
			}
		}
		return $del;
	}
	
	/*
	* Update pages where only the stable version links to a page
	* that was just changed in some way.
	*/
	public static function doCacheUpdate( $title ) {
		$update = new FRCacheUpdate( $title, 'flaggedrevs_tracking' );
		$update->doUpdate();
		return true;
	}
	
	/**
	* Add special fields to parser.
	*/
	public static function parserAddFields( $parser ) {
		$parser->mOutput->fr_ImageSHA1Keys = array();
		$parser->mOutput->fr_newestImageTime = "0";
		$parser->mOutput->fr_newestTemplateID = 0;
		$parser->mOutput->fr_includeErrors = array();
		return true;
	}

	/**
	* Select the desired templates based on the selected stable revision IDs
	*/
	public static function parserFetchStableTemplate( $parser, $title, &$skip, &$id ) {
		# Trigger for stable version parsing only
		if( !$parser || empty($parser->fr_isStable) )
			return true;
		# Special namespace ... ?
		if( $title->getNamespace() < 0 ) {
			return true;
		}
		$dbr = wfGetDB( DB_SLAVE );
		# Check for stable version of template if this feature is enabled.
		# Should be in reviewable namespace, this saves unneeded DB checks as
		# well as enforce site settings if they are later changed.
		global $wgUseStableTemplates;
		if( $wgUseStableTemplates && FlaggedRevs::isPageReviewable($title) && $title->getArticleId() ) {
			$id = $dbr->selectField( 'flaggedpages', 'fp_stable',
				array( 'fp_page_id' => $title->getArticleId() ),
				__METHOD__ );
		}
		# Check cache before doing another DB hit...
		$idP = FlaggedRevs::getTemplateIdFromCache( $parser->getRevisionId(), $title->getNamespace(), $title->getDBKey() );
		if( !is_null($idP) && (!$id || $idP > $id) ) {
			$id = $idP;
		}
		# If there is no stable version (or that feature is not enabled), use
		# the template revision during review time. If both, use the newest one.
		if( !FlaggedRevs::useProcessCache( $parser->getRevisionId() ) ) {
			$idP = $dbr->selectField( 'flaggedtemplates', 'ft_tmp_rev_id',
				array( 'ft_rev_id' => $parser->getRevisionId(),
					'ft_namespace' => $title->getNamespace(),
					'ft_title' => $title->getDBkey() ),
				__METHOD__ );
			$id = ($id === false || $idP > $id) ? $idP : $id;
		}
		# If none specified, see if we are allowed to use the current revision
		if( !$id ) {
			global $wgUseCurrentTemplates;
			if( $id === false ) {
				$parser->mOutput->fr_includeErrors[] = $title->getPrefixedDBKey(); // May want to give an error
				if( !$wgUseCurrentTemplates ) {
					$skip = true;
				}
			} else {
				$skip = true; // If ID is zero, don't load it
			}
		}
		if( $id > $parser->mOutput->fr_newestTemplateID ) {
			$parser->mOutput->fr_newestTemplateID = $id;
		}
		return true;
	}

	/**
	* Select the desired images based on the selected stable revision times/SHA-1s
	*/
	public static function parserMakeStableImageLink( $parser, $nt, &$skip, &$time, &$query=false ) {
		# Trigger for stable version parsing only
		if( empty($parser->fr_isStable) ) {
			return true;
		}
		$file = null;
		$dbr = wfGetDB( DB_SLAVE );
		# Normalize NS_MEDIA to NS_IMAGE
		$title = $nt->getNamespace() == NS_IMAGE ? $nt : Title::makeTitle( NS_IMAGE, $nt->getDBKey() );
		# Check for stable version of image if this feature is enabled.
		# Should be in reviewable namespace, this saves unneeded DB checks as
		# well as enforce site settings if they are later changed.
		$sha1 = "";
		global $wgUseStableImages;
		if( $wgUseStableImages && FlaggedRevs::isPageReviewable( $title ) ) {
			if( $srev = FlaggedRevision::newFromStable( $title ) ) {
				$time = $srev->getFileTimestamp();
				$sha1 = $srev->getFileSha1();
			}
		}
		# Check cache before doing another DB hit...
		$params = FlaggedRevs::getFileVersionFromCache( $parser->getRevisionId(), $title->getDBKey() );
		if( is_array($params) ) {
			list($timeP,$sha1) = $params;
			// Take the newest one...
			if( !$time || $timeP > $time ) {
				$time = $timeP;
			}
		}
		# If there is no stable version (or that feature is not enabled), use
		# the image revision during review time. If both, use the newest one.
		if( !FlaggedRevs::useProcessCache( $parser->getRevisionId() ) ) {
			$row = $dbr->selectRow( 'flaggedimages', 
				array( 'fi_img_timestamp', 'fi_img_sha1' ),
				array( 'fi_rev_id' => $parser->getRevisionId(),
					'fi_name' => $title->getDBkey() ),
				__METHOD__ );
			if( $row && ($time === false || $row->fi_img_timestamp > $time) ) {
				$time = $row->fi_img_timestamp;
				$sha1 = $row->fi_img_sha1;
			}
		}
		$query = $time ? "filetimestamp=" . urlencode( wfTimestamp(TS_MW,$time) ) : "";
		# If none specified, see if we are allowed to use the current revision
		if( !$time ) {
			global $wgUseCurrentImages;
			# If the DB found nothing...
			if( $time === false ) {
				$parser->mOutput->fr_includeErrors[] = $title->getPrefixedDBKey(); // May want to give an error
				if( !$wgUseCurrentImages ) {
					$time = "0";
				} else {
					$file = wfFindFile( $title );
					$time = $file ? $file->getTimestamp() : "0"; // Use current
				}
			} else {
				$time = "0";
			}
		}
		# Add image metadata to parser output
		$parser->mOutput->fr_ImageSHA1Keys[$title->getDBkey()] = array();
		$parser->mOutput->fr_ImageSHA1Keys[$title->getDBkey()][$time] = $sha1;
		# Bug 15748, be lax about commons image sync status
		$file = $file ? $file : self::getLocalFile( $title, $time ); # FIXME: would be nice not to double fetch!
		if( $file->exists() && $file->isLocal() && $time > $parser->mOutput->fr_newestImageTime ) {
			$parser->mOutput->fr_newestImageTime = $time;
		}
		return true;
	}

	/**
	* Select the desired images based on the selected stable revision times/SHA-1s
	*/
	public static function galleryFindStableFileTime( $ig, $nt, &$time, &$query=false ) {
		# Trigger for stable version parsing only
		if( empty($ig->mParser->fr_isStable) || $nt->getNamespace() != NS_IMAGE ) {
			return true;
		}
		$file = null;
		$dbr = wfGetDB( DB_SLAVE );
		# Check for stable version of image if this feature is enabled.
		# Should be in reviewable namespace, this saves unneeded DB checks as
		# well as enforce site settings if they are later changed.
		$sha1 = "";
		global $wgUseStableImages;
		if( $wgUseStableImages && FlaggedRevs::isPageReviewable( $nt ) ) {
			if( $srev = FlaggedRevision::newFromStable( $nt ) ) {
				$time = $srev->getFileTimestamp();
				$sha1 = $srev->getFileSha1();
			}
		}
		# Check cache before doing another DB hit...
		$params = FlaggedRevs::getFileVersionFromCache( $ig->mRevisionId, $nt->getDBKey() );
		if( is_array($params) ) {
			list($timeP,$sha1) = $params;
			// Take the newest one...
			if( !$time || $timeP > $time ) {
				$time = $timeP;
			}
		}
		# If there is no stable version (or that feature is not enabled), use
		# the image revision during review time. If both, use the newest one.
		if( !FlaggedRevs::useProcessCache( $ig->mRevisionId ) ) {
			$row = $dbr->selectRow( 'flaggedimages', 
				array( 'fi_img_timestamp', 'fi_img_sha1' ),
				array('fi_rev_id' => $ig->mRevisionId,
					'fi_name' => $nt->getDBkey() ),
				__METHOD__ );
			if( $row && ($time === false || $row->fi_img_timestamp > $time) ) {
				$time = $row->fi_img_timestamp;
				$sha1 = $row->fi_img_sha1;
			}
		}
		$query = $time ? "filetimestamp=" . urlencode( wfTimestamp(TS_MW,$time) ) : "";
		# If none specified, see if we are allowed to use the current revision
		if( !$time ) {
			global $wgUseCurrentImages;
			# If the DB found nothing...
			if( $time === false ) {
				$ig->mParser->mOutput->fr_includeErrors[] = $nt->getPrefixedDBKey(); // May want to give an error
				if( !$wgUseCurrentImages ) {
					$time = "0";
				} else {
					$file = wfFindFile( $nt );
					$time = $file ? $file->getTimestamp() : "0";
				}
			} else {
				$time = "0";
			}
		}
		# Add image metadata to parser output
		$ig->mParser->mOutput->fr_ImageSHA1Keys[$nt->getDBkey()] = array();
		$ig->mParser->mOutput->fr_ImageSHA1Keys[$nt->getDBkey()][$time] = $sha1;
		# Bug 15748, be lax about commons image sync status
		$file = $file ? $file : self::getLocalFile( $nt, $time ); # FIXME: would be nice not to double fetch!
		if( $file->exists() && $file->isLocal() && $time > $ig->mParser->mOutput->fr_newestImageTime ) {
			$ig->mParser->mOutput->fr_newestImageTime = $time;
		}
		return true;
	}
	
	/**
	* Insert image timestamps/SHA-1 keys into parser output
	*/
	public static function parserInjectTimestamps( $parser, &$text ) {
		# Don't trigger this for stable version parsing...it will do it separately.
		if( !empty($parser->fr_isStable) )
			return true;

		wfProfileIn( __METHOD__ );
		$maxRevision = 0;
		# Record the max template revision ID
		if( !empty($parser->mOutput->mTemplateIds) ) {
			foreach( $parser->mOutput->mTemplateIds as $namespace => $DBKeyRev ) {
				foreach( $DBKeyRev as $DBkey => $revID ) {
					if( $revID > $maxRevision ) {
						$maxRevision = $revID;
					}
				}
			}
		}
		$parser->mOutput->fr_newestTemplateID = $maxRevision;

		$maxTimestamp = "0";
		# Fetch the current timestamps of the images.
		if( !empty($parser->mOutput->mImages) ) {
			foreach( $parser->mOutput->mImages as $filename => $x ) {
				# FIXME: it would be nice not to double fetch these!
				$file = wfFindFile( Title::makeTitle( NS_IMAGE, $filename ) );
				$parser->mOutput->fr_ImageSHA1Keys[$filename] = array();
				if( $file ) {
					# Bug 15748, be lax about commons image sync status
					if( $file->isLocal() && $file->getTimestamp() > $maxTimestamp ) {
						$maxTimestamp = $file->getTimestamp();
					}
					$parser->mOutput->fr_ImageSHA1Keys[$filename][$file->getTimestamp()] = $file->getSha1();
				} else {
					$parser->mOutput->fr_ImageSHA1Keys[$filename]["0"] = '';
				}
			}
		}
		# Record the max timestamp
		$parser->mOutput->fr_newestImageTime = $maxTimestamp;

		wfProfileOut( __METHOD__ );
		return true;
	}

	/**
	* Insert image timestamps/SHA-1s into page output
	*/
	public static function outputInjectTimestamps( $out, $parserOut ) {
		# Set first time
		$out->fr_ImageSHA1Keys = isset($out->fr_ImageSHA1Keys) ? $out->fr_ImageSHA1Keys : array();
		# Leave as defaults if missing. Relevant things will be updated only when needed.
		# We don't want to go around resetting caches all over the place if avoidable...
		$imageSHA1Keys = isset($parserOut->fr_ImageSHA1Keys) ? $parserOut->fr_ImageSHA1Keys : array();
		# Add on any new items
		$out->fr_ImageSHA1Keys = wfArrayMerge( $out->fr_ImageSHA1Keys, $imageSHA1Keys );
		return true;
	}

	protected static function getLocalFile( $title, $time ) {
		return RepoGroup::singleton()->getLocalRepo()->findFile( $title, $time );
	}

	/**
	* Don't let users vandalize pages by moving them
	*/
	public static function userCanMove( &$title, $user, &$action, &$result ) {
		if( $action != 'move' || !FlaggedRevs::isPageReviewable( $title ) ) {
			return true;
		}
		$flaggedArticle = FlaggedArticle::getTitleInstance( $title );
		if( !$flaggedArticle->showStableByDefault() ) {
			return true;
		}
		$frev = $flaggedArticle->getStableRev();
		if( $frev ) {
			# Allow for only editors/reviewers to move this
			if( !$user->isAllowed('review') && !$user->isAllowed('movestable') ) {
				$result = false;
				return false;
			}
		}
		return true;
	}
	
    /**
    * Allow users to view reviewed pages
    */
    public static function userCanView( $title, $user, &$action, &$result ) {
        global $wgFlaggedRevsVisible, $wgFlaggedRevsTalkVisible, $wgTitle;
        # Assume $action may still not be set, in which case, treat it as 'view'...
		# Return out if $result set to false by some other hooked call.
        if( !$wgFlaggedRevsVisible || $action != 'read' || $result===false )
            return true;
        # Admin may set this to false, rather than array()...
        $groups = $user->getGroups();
        $groups[] = '*';
        if( empty($wgFlaggedRevsVisible) || !array_intersect($groups,$wgFlaggedRevsVisible) )
            return true;
        # Is this a talk page?
        if( $wgFlaggedRevsTalkVisible && $title->isTalkPage() ) {
            $result = true;
            return true;
        }
        # See if there is a stable version. Also, see if, given the page 
        # config and URL params, the page can be overriden.
		$flaggedArticle = FlaggedArticle::getTitleInstance( $title );
        if( $wgTitle && $wgTitle->equals( $title ) ) {
            // Cache stable version while we are at it.
            if( $flaggedArticle->pageOverride() && $flaggedArticle->getStableRev( FR_TEXT ) ) {
                $result = true;
            }
        } else {
            if( FlaggedRevision::newFromStable( $title ) ) {
                $result = true;
            }
        }
        return true;
    }
	
	/**
	* When an edit is made by a reviewer, if the current revision is the stable
	* version, try to automatically review it.
	*/
	public static function maybeMakeEditReviewed( $article, $rev, $baseRevId = false ) {
		global $wgFlaggedRevsAutoReview, $wgFlaggedRevsAutoReviewNew, $wgRequest;
		# Get the user
		$user = User::newFromId( $rev->getUser() );
		if( !$wgFlaggedRevsAutoReview || !$user->isAllowed('autoreview') )
			return true;
		# If $baseRevId passed in, this is a null edit
		$isNullEdit = $baseRevId ? true : false;
		# Must be in reviewable namespace
		$title = $article->getTitle();
		if( !FlaggedRevs::isPageReviewable( $title ) ) {
			return true;
		}
		$frev = null;
		$reviewableNewPage = false;
		# Avoid extra DB hit and lag issues
		$title->resetArticleID( $rev->getPage() );
		# Get what was just the current revision ID
		$prevRevId = self::getPreviousRevisionId( $rev );
		# Get the revision ID the incoming one was based off...
		if( !$baseRevId && $prevRevId ) {
			$prevTimestamp = Revision::getTimestampFromId( $prevRevId, $rev->getPage() ); // use PK
			# Get edit timestamp. Existance already valided by EditPage.php. If 
			# not present, then it shouldn't be, like null edits.
			$editTimestamp = $wgRequest->getVal('wpEdittime');
			# The user just made an edit. The one before that should have
			# been the current version. If not reflected in wpEdittime, an
			# edit may have been auto-merged in between, in that case, discard
			# the baseRevId given from the client...
			if( !$editTimestamp || $prevTimestamp == $editTimestamp ) {
				$baseRevId = intval( trim( $wgRequest->getVal('baseRevId') ) );
			}
			# If baseRevId not given, assume the previous revision ID.
			# For auto-merges, this also occurs since the given ID is ignored.
			# Also for bots that don't submit everything...
			if( !$baseRevId ) {
				$baseRevId = $prevRevId;
			}
		}
		// New pages
		if( !$prevRevId ) {
			$reviewableNewPage = (bool)$wgFlaggedRevsAutoReviewNew;
		// Edits to existing pages
		} else if( $baseRevId ) {
			$frev = FlaggedRevision::newFromTitle( $title, $baseRevId, FR_MASTER );
			# If the base revision was not reviewed, check if the previous one was.
			# This should catch null edits as well as normal ones.
			if( !$frev ) {
				$frev = FlaggedRevision::newFromTitle( $title, $prevRevId, FR_MASTER );
			}
		}
		# Is this an edit directly to the stable version?
		if( $reviewableNewPage || !is_null($frev) ) {
			# Assume basic flagging level unless this is a null edit
			if( $isNullEdit ) {
				$flags = $frev->getTags();
			} else {
				$flags = array();
				foreach( FlaggedRevs::getDimensions() as $tag => $minQL ) {
					$flags[$tag] = 1;
				}
			}
			# Review this revision of the page. Let articlesavecomplete hook do rc_patrolled bit...
			FlaggedRevs::autoReviewEdit( $article, $user, $rev->getText(), $rev, $flags, false );
		}
		return true;
	}
	
	/**
	* As used, this function should be lag safe
	* @param Revision $revision
	* @return int
	*/
	protected static function getPreviousRevisionID( $revision ) {
		$db = wfGetDB( DB_MASTER );
		return $db->selectField( 'revision', 'rev_id',
			array(
				'rev_page' => $revision->getPage(),
				'rev_id < ' . $revision->getId()
			),
			__METHOD__,
			array( 'ORDER BY' => 'rev_id DESC' )
		);
	}

	/**
	* When an edit is made to a page that can't be reviewed, autopatrol if allowed.
	* This is not loggged for perfomance reasons and no one cares if talk pages and such
	* are autopatrolled.
	*/
	public static function autoMarkPatrolled( $rc ) {
		global $wgUser;
		if( empty($rc->mAttribs['rc_this_oldid']) ) {
			return true;
		}
		$patrol = $record = false;
		// Is the page reviewable?
		if( FlaggedRevs::isPageReviewable( $rc->getTitle() ) ) {
			$patrol = FlaggedRevs::revIsFlagged( $rc->getTitle(), $rc->mAttribs['rc_this_oldid'], GAID_FOR_UPDATE );
		// Can this be patrolled?
		} else if( FlaggedRevs::isPagePatrollable( $rc->getTitle() ) ) {
			$patrol = $wgUser->isAllowed('autopatrolother');
			$record = true;
		} else {
			$patrol = true; // mark by default
		}
		if( $patrol ) {
			RevisionReview::updateRecentChanges( $rc->getTitle(), $rc->mAttribs['rc_this_oldid'] );
			if( $record ) {
				PatrolLog::record( $rc->mAttribs['rc_id'], true );
			}
		}
		return true;
	}

	/**
	* Callback that autopromotes user according to the setting in
	* $wgFlaggedRevsAutopromote. This is not as efficient as it should be
	*/
	public static function autoPromoteUser( $article, $user, &$text, &$summary, &$m, &$a, &$b, &$f, $rev ) {
		global $wgFlaggedRevsAutopromote, $wgMemc;

		if( empty($wgFlaggedRevsAutopromote) || !$rev )
			return true;

		wfProfileIn( __METHOD__ );
		# Grab current groups
		$groups = $user->getGroups();
		# Do not give this to current holders or bots
		if( in_array( 'bot', $groups ) || in_array( 'editor', $groups ) ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Do not re-add status if it was previously removed!
		$p = FlaggedRevs::getUserParams( $user );
		if( isset($p['demoted']) && $p['demoted'] ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Update any special counters for non-null revisions
		$changed = false;
		$pages = array();
		$p['uniqueContentPages'] = isset($p['uniqueContentPages']) ? $p['uniqueContentPages'] : '';
		$p['totalContentEdits'] = isset($p['totalContentEdits']) ? $p['totalContentEdits'] : 0;
		$p['editComments'] = isset($p['editComments']) ? $p['editComments'] : 0;
		if( $article->getTitle()->isContentPage() ) {
			$pages = explode( ',', trim($p['uniqueContentPages']) ); // page IDs
			# Don't let this get bloated for no reason
			if( count($pages) < $wgFlaggedRevsAutopromote['uniqueContentPages'] && !in_array($article->getId(),$pages) ) {
				$pages[] = $article->getId();
				$p['uniqueContentPages'] = preg_replace('/^,/','',implode(',',$pages)); // clear any garbage
			}
			$p['totalContentEdits'] += 1;
			$changed = true;
		}
		if( $summary ) {
			$p['editComments'] += 1;
			$changed = true;
		}
		# Save any updates to user params
		if( $changed ) {
			FlaggedRevs::saveUserParams( $user, $p );
		}
		# Check if user edited enough content pages
		if( $wgFlaggedRevsAutopromote['totalContentEdits'] > $p['totalContentEdits'] ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Check if user edited enough unique pages
		if( $wgFlaggedRevsAutopromote['uniqueContentPages'] > count($pages) ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Check edit comment use
		if( $wgFlaggedRevsAutopromote['editComments'] > $p['editComments'] ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Check if results are cached to avoid DB queries
		$key = wfMemcKey( 'flaggedrevs', 'autopromote-skip', $user->getID() );
		$value = $wgMemc->get( $key );
		if( $value == 'true' ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Check basic, already available, promotion heuristics first...
		$now = time();
		$usercreation = wfTimestampOrNull( TS_UNIX, $user->getRegistration() );
		$userage = $usercreation ? floor(($now - $usercreation) / 86400) : NULL;
		if( !is_null($userage) && $userage < $wgFlaggedRevsAutopromote['days'] ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		if( $user->getEditCount() < $wgFlaggedRevsAutopromote['edits'] ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		if( $wgFlaggedRevsAutopromote['email'] && !$user->isEmailConfirmed() ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Don't grant to currently blocked users...
		if( $user->isBlocked() ) {
			wfProfileOut( __METHOD__ );
			return true;
		}
		# Check if user was ever blocked before
		if( $wgFlaggedRevsAutopromote['neverBlocked'] ) {
			$dbr = wfGetDB( DB_SLAVE );
			$blocked = $dbr->selectField( 'logging', '1',
				array( 'log_namespace' => NS_USER, 
					'log_title' => $user->getUserPage()->getDBKey(),
					'log_type' => 'block',
					'log_action' => 'block' ),
				__METHOD__,
				array( 'USE INDEX' => 'page_time' ) );
			if( $blocked ) {
				# Make a key to store the results
				$wgMemc->set( $key, 'true', 3600*24*7 );
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# See if the page actually has sufficient content...
		if( $wgFlaggedRevsAutopromote['userpage'] ) {
			if( !$user->getUserPage()->exists() ) {
				wfProfileOut( __METHOD__ );
				return true;
			}
			$dbr = isset($dbr) ? $dbr : wfGetDB( DB_SLAVE );
			$size = $dbr->selectField( 'page', 'page_len',
				array( 'page_namespace' => $user->getUserPage()->getNamespace(),
					'page_title' => $user->getUserPage()->getDBKey() ),
				__METHOD__ );
			if( $size < $wgFlaggedRevsAutopromote['userpageBytes'] ) {
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Check for edit spacing. This lets us know that the account has
		# been used over N different days, rather than all in one lump.
		if( $wgFlaggedRevsAutopromote['spacing'] > 0 && $wgFlaggedRevsAutopromote['benchmarks'] > 1 ) {
			# Convert days to seconds...
			$spacing = $wgFlaggedRevsAutopromote['spacing'] * 24 * 3600;
			# Check the oldest edit
			$dbr = isset($dbr) ? $dbr : wfGetDB( DB_SLAVE );
			$lower = $dbr->selectField( 'revision', 'rev_timestamp',
				array( 'rev_user' => $user->getID() ),
				__METHOD__,
				array( 'ORDER BY' => 'rev_timestamp ASC',
					'USE INDEX' => 'user_timestamp' ) );
			# Recursively check for an edit $spacing seconds later, until we are done.
			# The first edit counts, so we have one less scans to do...
			$benchmarks = 0;
			$needed = $wgFlaggedRevsAutopromote['benchmarks'] - 1;
			while( $lower && $benchmarks < $needed ) {
				$next = wfTimestamp( TS_UNIX, $lower ) + $spacing;
				$lower = $dbr->selectField( 'revision', 'rev_timestamp',
					array( 'rev_user' => $user->getID(),
						'rev_timestamp > ' . $dbr->addQuotes( $dbr->timestamp($next) ) ),
					__METHOD__,
					array( 'ORDER BY' => 'rev_timestamp ASC',
						'USE INDEX' => 'user_timestamp' ) );
				if( $lower !== false )
					$benchmarks++;
			}
			if( $benchmarks < $needed ) {
				# Make a key to store the results
				$wgMemc->set( $key, 'true', 3600*24*$spacing*($benchmarks - $needed - 1) );
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Check if this user is sharing IPs with another users
		if( $wgFlaggedRevsAutopromote['uniqueIPAddress'] ) {
			$uid = $user->getId();

			$dbr = isset($dbr) ? $dbr : wfGetDB( DB_SLAVE );
			$shared = $dbr->selectField( 'recentchanges', '1',
				array( 'rc_ip' => wfGetIP(),
					"rc_user != '$uid'" ),
				__METHOD__,
				array( 'USE INDEX' => 'rc_ip' ) );
			if( $shared ) {
				# Make a key to store the results
				$wgMemc->set( $key, 'true', 3600*24*7 );
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Check for bot attacks/sleepers
		global $wgSorbsUrl, $wgProxyWhitelist;
		if( $wgSorbsUrl && $wgFlaggedRevsAutopromote['noSorbsMatches'] ) {
			$ip = wfGetIP();
			if( !in_array($ip,$wgProxyWhitelist) && $user->inDnsBlacklist( $ip, $wgSorbsUrl ) ) {
				# Make a key to store the results
				$wgMemc->set( $key, 'true', 3600*24*7 );
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Check if the user has any recent content edits
		if( $wgFlaggedRevsAutopromote['recentContentEdits'] > 0 ) {
			global $wgContentNamespaces;
		
			$dbr = isset($dbr) ? $dbr : wfGetDB( DB_SLAVE );
			$res = $dbr->select( 'recentchanges', '1', 
				array( 'rc_user_text' => $user->getName(),
					'rc_namespace' => $wgContentNamespaces ), 
				__METHOD__, 
				array( 'USE INDEX' => 'rc_ns_usertext',
					'LIMIT' => $wgFlaggedRevsAutopromote['recentContent'] ) );
			if( $dbr->numRows($res) < $wgFlaggedRevsAutopromote['recentContent'] ) {
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Check to see if the user has so many deleted edits that
		# they don't actually enough live edits. This is because
		# $user->getEditCount() is the count of edits made, not live.
		if( $wgFlaggedRevsAutopromote['excludeDeleted'] ) {
			$dbr = isset($dbr) ? $dbr : wfGetDB( DB_SLAVE );
			$minDiff = $user->getEditCount() - $wgFlaggedRevsAutopromote['days'] + 1;
			# Use an estimate if the number starts to get large
			if( $minDiff <= 100 ) {
				$res = $dbr->select( 'archive', '1', 
					array( 'ar_user_text' => $user->getName() ), 
					__METHOD__, 
					array( 'USE INDEX' => 'usertext_timestamp', 'LIMIT' => $minDiff ) );
				$deletedEdits = $dbr->numRows($res);
			} else {
				$deletedEdits = $dbr->estimateRowCount( 'archive', '1',
					array( 'ar_user_text' => $user->getName() ),
					__METHOD__,
					array( 'USE INDEX' => 'usertext_timestamp' ) );
			}
			if( $deletedEdits >= $minDiff ) {
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		# Add editor rights
		$newGroups = $groups ;
		array_push( $newGroups, 'editor' );

		wfLoadExtensionMessages( 'FlaggedRevs' );
		# Lets NOT spam RC, set $RC to false
		$log = new LogPage( 'rights', false );
		$log->addEntry( 'rights', $user->getUserPage(), wfMsg('rights-editor-autosum'),
			array( implode(', ',$groups), implode(', ',$newGroups) ) );
		$user->addGroup('editor');

		wfProfileOut( __METHOD__ );
		return true;
	}
	
   	/**
	* Record demotion so that auto-promote will be disabled
	*/
	public static function recordDemote( $u, $addgroup, $removegroup ) {
		if( $removegroup && in_array('editor',$removegroup) ) {
			$params = FlaggedRevs::getUserParams( $u );
			$params['demoted'] = 1;
			FlaggedRevs::saveUserParams( $u, $params );
		}
		return true;
	}
	
	/**
	* Add user preference to form HTML
	*/
	public static function injectPreferences( $form, $out ) {
		$prefsHtml = FlaggedRevsXML::stabilityPreferences( $form );
		$out->addHTML( $prefsHtml );
		return true;
	}
	
	/**
	* Add user preference to form object based on submission
	*/
	public static function injectFormPreferences( $form, $request ) {
		global $wgUser;
		$form->mFlaggedRevsStable = $request->getInt( 'wpFlaggedRevsStable' );
		$form->mFlaggedRevsSUI = $request->getInt( 'wpFlaggedRevsSUI' );
		$form->mFlaggedRevsWatch = $wgUser->isAllowed( 'review' ) ? $request->getInt( 'wpFlaggedRevsWatch' ) : 0;
		return true;
	}
	
	/**
	* Set preferences on form based on user settings
	*/
	public static function resetPreferences( $form, $user ) {
		global $wgSimpleFlaggedRevsUI;
		$form->mFlaggedRevsStable = $user->getOption( 'flaggedrevsstable' );
		$form->mFlaggedRevsSUI = $user->getOption( 'flaggedrevssimpleui', intval($wgSimpleFlaggedRevsUI) );
		$form->mFlaggedRevsWatch = $user->getOption( 'flaggedrevswatch' );
		return true;
	}
	
	/**
	* Set user preferences into user object before it is applied to DB
	*/
	public static function savePreferences( $form, $user, &$msg ) {
		$user->setOption( 'flaggedrevsstable', Validate::Int( $form->mFlaggedRevsStable, 0, 1 ) );
		$user->setOption( 'flaggedrevssimpleui', Validate::Int( $form->mFlaggedRevsSUI, 0, 1 ) );
		$user->setOption( 'flaggedrevswatch', Validate::Int( $form->mFlaggedRevsWatch, 0, 1 ) );
		return true;
	}
	
	/**
	* Create revision link for log line entry
	* @param string $type
	* @param string $action
	* @param object $title
	* @param array $paramArray
	* @param string $c
	* @param string $r user tool links
	* @param string $t timestamp of the log entry
	* @return bool true
	*/
	public static function reviewLogLine( $type = '', $action = '', $title = null, $paramArray = array(), &$c = '', &$r = '' ) {
		$actionsValid = array('approve','approve2','approve-a','approve2-a','unapprove','unapprove2');
		# Show link to page with oldid=x
		if( $type == 'review' && in_array($action,$actionsValid) && is_object($title) && isset($paramArray[0]) ) {
			global $wgUser;
			# Load required messages
			wfLoadExtensionMessages( 'FlaggedRevs' );
			# Don't show diff if param missing or rev IDs are the same
			if( !empty($paramArray[1]) && $paramArray[0] != $paramArray[1] ) {
				$r = '(' . $wgUser->getSkin()->makeKnownLinkObj( $title, wfMsgHtml('review-logentry-diff'), 
					"oldid={$paramArray[1]}&diff={$paramArray[0]}") . ') ';
			} else {
				$r = '(' . wfMsgHtml('review-logentry-diff') . ')';
			}
			$r .= ' (' . $wgUser->getSkin()->makeKnownLinkObj( $title, 
				wfMsgHtml('review-logentry-id',$paramArray[0]), "oldid={$paramArray[0]}&diff=prev") . ')';
		}
		return true;
	}

	public static function imagePageFindFile( &$imagePage, &$normalFile, &$displayFile ) {
		$fa = FlaggedArticle::getInstance( $imagePage );
		$fa->imagePageFindFile( $normalFile, $displayFile );
		return true;
	}

	public static function setActionTabs( $skin, &$contentActions ) {
		$fa = FlaggedArticle::getGlobalInstance();
		if ( $fa ) {
			$fa->setActionTabs( $skin, $contentActions );
		}
		return true;
	}

	public static function onArticleViewHeader( &$article, &$outputDone, &$pcache ) {
		$flaggedArticle = FlaggedArticle::getInstance( $article );
		$flaggedArticle->maybeUpdateMainCache( $outputDone, $pcache );
		$flaggedArticle->addStableLink( $outputDone, $pcache );
		$flaggedArticle->setPageContent( $outputDone, $pcache );
		$flaggedArticle->addPatrolLink( $outputDone, $pcache );
		return true;
	}
	
	public static function addRatingLink( &$skintemplate, &$nav_urls, &$oldid, &$revid ) {
		$fa = FlaggedArticle::getTitleInstance( $skintemplate->mTitle );
		# Add rating tab
		if( $fa->isRateable() ) {
			wfLoadExtensionMessages( 'RatingHistory' );
			$nav_urls['ratinghist'] = array( 
				'text' => wfMsg( 'ratinghistory-link' ),
				'href' => $skintemplate->makeSpecialUrl( 'RatingHistory', 
					"target=" . wfUrlencode( "{$skintemplate->thispage}" ) )
			);
		}
		return true;
	}
	
	public static function ratingToolboxLink( &$monobook ) {
		if( isset( $monobook->data['nav_urls']['ratinghist'] ) ) {
			?><li id="t-cite"><?php
				?><a href="<?php echo htmlspecialchars( $monobook->data['nav_urls']['ratinghist']['href'] ) ?>"><?php
					echo $monobook->msg( 'ratinghistory-link' );
				?></a><?php
			?></li><?php
		}
		return true;
	}
	
	public static function overrideRedirect( &$title, $request, &$ignoreRedirect, &$target ) {
		# Get an instance on the title ($wgTitle)
		$fa = FlaggedArticle::getTitleInstance( $title );
		if( !$fa->isReviewable() ) {
			return true;
		}
		if( $request->getVal( 'stableid' ) ) {
			$ignoreRedirect = true;
		} else {
			global $wgMemc, $wgParserCacheExpireTime;
			# Try the cache...
			$key = wfMemcKey( 'flaggedrevs', 'overrideRedirect', $title->getArticleId() );
			$data = $wgMemc->get($key);
			if( is_object($data) && $data->time > $title->getTouched() ) {
				list($ignoreRedirect,$target) = $data->value;
				return true;
			}
			if( $srev = $fa->getStableRev() ) {
				# If synced, nothing special here...
				if( $srev->getRevId() != $title->getLatestRevID() && $fa->pageOverride() ) {
					$text = $srev->getRevText();
					$redirect = $fa->followRedirectText( $text );
					if( $redirect ) {
						$target = $redirect;
					} else {
						$ignoreRedirect = true;
					}
					$data = FlaggedRevs::makeMemcObj( array($ignoreRedirect,$target) );
					$wgMemc->set( $key, $data, $wgParserCacheExpireTime );
				}
			}
		}
		return true;
	}
	
	public static function addToEditView( &$editPage ) {
		return FlaggedArticle::getInstance( $editPage->mArticle )->addToEditView( $editPage );
	}
	
	public static function onCategoryPageView( &$category ) {
		return FlaggedArticle::getInstance( $category )->addToCategoryView();
	}
	
	public static function onSkinAfterContent( &$data ) {
		global $wgOut;
		if( $wgOut->isArticleRelated() && $fa = FlaggedArticle::getGlobalInstance() ) {
			$fa->addReviewNotes( $data );
			$fa->addReviewForm( $data );
			$fa->addFeedbackForm( $data );
			$fa->addVisibilityLink( $data );
		}
		return true;
	}
	
	public static function addToHistQuery( $pager, &$queryInfo ) {
		$flaggedArticle = FlaggedArticle::getTitleInstance( $pager->mPageHistory->getTitle() );
		if( $flaggedArticle->isReviewable() ) {
			$queryInfo['tables'][] = 'flaggedrevs';
			$queryInfo['fields'][] = 'fr_quality';
			$queryInfo['fields'][] = 'fr_user';
			$queryInfo['join_conds']['flaggedrevs'] = array( 'LEFT JOIN', "fr_page_id = rev_page AND fr_rev_id = rev_id" );
		}
		return true;
	}
	
	public static function addToFileHistQuery( $file, &$tables, &$fields, &$conds, &$opts, &$join_conds ) {
		if( $file->isLocal() ) {
			$tables[] = 'flaggedrevs';
			$fields[] = 'fr_quality';
			$join_conds['flaggedrevs'] = array( 'LEFT JOIN', 'oi_sha1 = fr_img_sha1 AND oi_timestamp = fr_img_timestamp' );
		}
		return true;
	}
	
	public static function addToContribsQuery( $pager, &$queryInfo ) {
		$queryInfo['tables'][] = 'flaggedpages';
		$queryInfo['fields'][] = 'fp_stable';
		$queryInfo['join_conds']['flaggedpages'] = array( 'LEFT JOIN', "fp_page_id = rev_page" );
		return true;
	}
	
	public static function addToHistLine( &$history, $row, &$s ) {
		return FlaggedArticle::getInstance( $history->getArticle() )->addToHistLine( $history, $row, $s );
	}
	
	public static function addToFileHistLine( $hist, $file, &$s, &$rowClass ) {
		return FlaggedArticle::getInstance( $hist->getImagePage() )->addToFileHistLine( $hist, $file, $s, $rowClass );
	}
	
	public static function addToContribsLine( $contribs, &$ret, $row ) {
		global $wgFlaggedRevsNamespaces;
		if( !in_array($row->page_namespace,$wgFlaggedRevsNamespaces) ) {
			// do nothing
		} else if( isset($row->fp_stable) && $row->rev_id > $row->fp_stable ) {
			$ret = '<span class="flaggedrevs-unreviewed">'.$ret.'</span>';
		} else if( !isset($row->fp_stable) ) {
			$ret = '<span class="flaggedrevs-unreviewed2">'.$ret.'</span>';
		}
		return true;
	}
	
	public static function injectReviewDiffURLParams( &$article, &$sectionAnchor, &$extraQuery ) {
		return FlaggedArticle::getInstance( $article )->injectReviewDiffURLParams( $sectionAnchor, $extraQuery );
	}

	public static function onDiffViewHeader( $diff, $oldRev, $newRev ) {
		self::injectStyleAndJS();
		$flaggedArticle = FlaggedArticle::getTitleInstance( $diff->getTitle() );
		$flaggedArticle->addPatrolAndDiffLink( $diff, $oldRev, $newRev );
		$flaggedArticle->addDiffNoticeAndIncludes( $diff, $oldRev, $newRev );
		return true;
	}

	public static function addRevisionIDField( $editPage, $out ) {
		return FlaggedArticle::getInstance( $editPage->mArticle )->addRevisionIDField( $editPage, $out );
	}
	
	public static function addBacklogNotice( &$notice ) {
		global $wgUser, $wgTitle, $wgFlaggedRevsBacklog;
		$watchlist = SpecialPage::getTitleFor( 'Watchlist' );
		$recentchanges = SpecialPage::getTitleFor( 'Recentchanges' );
		if ( $wgUser->isAllowed('review') && $wgTitle && ($wgTitle->equals($watchlist) || $wgTitle->equals($recentchanges)) ) {
			$dbr = wfGetDB( DB_SLAVE );
			$unreviewed = $dbr->estimateRowCount( 'flaggedpages', '*', array('fp_reviewed' => 0), __METHOD__ );
			if( $unreviewed >= $wgFlaggedRevsBacklog ) {
				wfLoadExtensionMessages( 'FlaggedRevs' );
				$notice .= "<div id='mw-oldreviewed-notice' class='plainlinks fr-backlognotice'>" . 
					wfMsgExt('flaggedrevs-backlog',array('parseinline')) . "</div>";
			}
		}
		return true;
	}

	public static function onParserTestTables( &$tables ) {
		$tables[] = 'flaggedpages';
		$tables[] = 'flaggedrevs';
		$tables[] = 'flaggedpage_config';
		$tables[] = 'flaggedtemplates';
		$tables[] = 'flaggedimages';
		$tables[] = 'flaggedrevs_promote';
		$tables[] = 'reader_feedback';
		$tables[] = 'reader_feedback_history';
		$tables[] = 'reader_feedback_pages';
		$tables[] = 'flaggedrevs_tracking';
		return true;
	}
}

abstract class FlaggedRevsApiHooks extends ApiQueryBase { 
	
	public static function addApiRevisionParams ( &$module, &$params ) {
		if (!$module instanceof ApiQueryRevisions)
			return true;
		$params['prop'][ApiBase::PARAM_TYPE][] = 'flagged';
		return true;
	}
	
	public static function addApiRevisionData( &$module ) {
		if (!$module instanceof ApiQueryRevisions)
			return true;		
		$params = $module->extractRequestParams( false );
		if ( empty( $params['prop'] ) || !in_array( 'flagged', $params['prop'] ) )
			return true;
		if ( !in_array( 'ids', $params['prop'] ) )
			$module->dieUsage( 'if rvprop=flagged is set, you must also set rvprop=ids', 'missingparam' );

		// Get all requested pageids/revids in a mapping:
		// pageid => revid => array_index of the revision
		// we will need this later to add data to the result array 
		$result = $module->getResult();
		$data = $result->getData();
		if ( !isset( $data['query'] ) || !isset( $data['query']['pages'] ) )
			return true;
		foreach ( $data['query']['pages'] as $pageid => $page )
			if ( array_key_exists( 'revisions', (array)$page ) )
				foreach ( $page['revisions'] as $index => $rev ) 
					if ( array_key_exists( 'revid', (array)$rev ) )
						$pageids[$pageid][$rev['revid']] = $index;
		if ( empty( $pageids ) )
			return true;

		//Construct SQL Query
		$db = $module->getDB();
		$module->resetQueryParams();
		$module->addTables( array( 'flaggedrevs', 'user' ) );
		$module->addFields( array(
			'fr_page_id',
			'fr_rev_id',
			'fr_timestamp',
			'fr_comment',
			'fr_quality',
			'fr_tags',
			'user_name'
		) );
		$module->addWhere( 'fr_user=user_id' );

		//Construct WHERE-clause to avoid multiplying the number of scanned rows
		//as flaggedrevs table has composite primary key (fr_page_id,fr_rev_id)
		foreach ( $pageids as $pageid => $revids )
			$where[] = $db->makeList( array(
					'fr_page_id' => $pageid,
					'fr_rev_id' => array_keys( $revids ) ), LIST_AND );
		$module->addWhere( $db->makeList( $where, LIST_OR ) );
		$module->addOption( 'USE INDEX', array( 'flaggedrevs' => 'PRIMARY' ) );

		$res = $module->select( __METHOD__ );

		//Add flagging data to result array
		while ( $row = $db->fetchObject( $res ) ) {
			$index = $pageids[$row->fr_page_id][$row->fr_rev_id];
			$data = array(
				'user' => $row->user_name,
				'timestamp' => wfTimestamp( TS_ISO_8601, $row->fr_timestamp ),
				'level' => intval( $row->fr_quality ),
				'level_text' => FlaggedRevs::getQualityLevelText( $row->fr_quality ),
				'tags' => FlaggedRevision::expandRevisionTags( $row->fr_tags )
			);	
			if ( $row->fr_comment )
				$data['comment'] = $row->fr_comment;
			$result->addValue(
				array( 'query', 'pages', $row->fr_page_id, 'revisions', $index ),
				'flagged',
				$data
			);
		}
		$db->freeResult( $res );
		return true;
	}
}
