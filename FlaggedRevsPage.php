<?php
#(c) Aaron Schulz, Joerg Baach, 2007 GPL

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

class RevisionReview extends UnlistedSpecialPage
{

    function __construct() {
        UnlistedSpecialPage::UnlistedSpecialPage( 'RevisionReview', 'review' );
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgOut;

		$confirm = $wgRequest->wasPosted() && $wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );

		if( $wgUser->isAllowed( 'review' ) ) {
			if( $wgUser->isBlocked( !$confirm ) ) {
				$wgOut->blockedPage();
				return;
			}
		} else {
			$wgOut->permissionRequired( 'review' );
			return;
		}
		if( wfReadOnly() ) {
			$wgOut->readOnlyPage();
			return;
		}

		$this->setHeaders();
		# Our target page
		$this->target = $wgRequest->getVal( 'target' );
		$this->page = Title::newFromUrl( $this->target );
		# Basic patrolling
		$this->patrolonly = $wgRequest->getBool( 'patrolonly' );
		$this->rcid = $wgRequest->getIntOrNull( 'rcid' );

		if( is_null($this->page) ) {
			$wgOut->showErrorPage('notargettitle', 'notargettext' );
			return;
		}
		
		# Patrol the edit if requested
		if( $this->patrolonly && $this->rcid ) {
			$this->markPatrolled( $wgRequest->getVal('token') );
			return;
		}

		global $wgFlaggedRevTags, $wgFlaggedRevValues;
		# Revision ID
		$this->oldid = $wgRequest->getIntOrNull( 'oldid' );
		if( !$this->oldid || !FlaggedRevs::isPageReviewable( $this->page ) ) {
			$wgOut->addHTML( wfMsgExt('revreview-main',array('parse')) );
			return;
		}
		# Check if page is protected
		if( !$this->page->quickUserCan( 'edit' ) ) {
			$wgOut->permissionRequired( 'badaccess-group0' );
			return;
		}
		# Special parameter mapping
		$this->templateParams = $wgRequest->getVal( 'templateParams' );
		$this->imageParams = $wgRequest->getVal( 'imageParams' );
		$this->validatedParams = $wgRequest->getVal( 'validatedParams' );
		
		global $wgReviewCodes;
		# Special token to discourage fiddling...
		$checkCode = FlaggedRevs::getValidationKey( $this->templateParams, $this->imageParams, $wgUser->getID(), $this->oldid );
		# Must match up
		if( $this->validatedParams !== $checkCode ) {
			$this->templateParams = '';
			$this->imageParams = '';
		}
		
		# Log comment
		$this->comment = $wgRequest->getText( 'wpReason' );
		# Additional notes (displayed at bottom of page)
		$this->notes = ( FlaggedRevs::allowComments() && $wgUser->isAllowed('validate') ) ?
			$wgRequest->getText('wpNotes') : '';
		# Get the revision's current flags, if any
		$this->oflags = FlaggedRevs::getRevisionTags( $this->oldid );
		# Get our accuracy/quality dimensions
		$this->dims = array();
		$this->unapprovedTags = 0;
		foreach( $wgFlaggedRevTags as $tag => $minQL ) {
			$this->dims[$tag] = $wgRequest->getIntOrNull( "wp$tag" );
			if( $this->dims[$tag] === 0 ) {
				$this->unapprovedTags++;
			} else if( is_null($this->dims[$tag]) ) {
				# This happens if we uncheck a checkbox
				$this->unapprovedTags++;
				$this->dims[$tag] = 0;
			}
		}
		# Check permissions and validate
		if( !$this->userCanSetFlags( $this->dims, $this->oflags ) ) {
			$wgOut->permissionRequired( 'badaccess-group0' );
			return;
		}
		# We must at least rate each category as 1, the minimum
		# Exception: we can rate ALL as unapproved to depreciate a revision
		$valid = true;
		if( $this->unapprovedTags > 0 ) {
			if( $this->unapprovedTags < count($wgFlaggedRevTags) )
				$valid = false;
		}
		if( !$wgUser->matchEditToken( $wgRequest->getVal('wpEditToken') ) )
			$valid = false;

		if( $valid && $wgRequest->wasPosted() ) {
			$this->submit();
		} else {
			$this->showRevision();
		}
	}

	/**
	 * Returns true if a user can do something
	 * @param string $tag
	 * @param int $value
	 * @returns bool
	 */
	public static function userCan( $tag, $value ) {
		global $wgFlagRestrictions, $wgUser;

		if( !isset($wgFlagRestrictions[$tag]) )
			return true;
		# Validators always have full access
		if( $wgUser->isAllowed('validate') )
			return true;
		# Check if this user has any right that lets him/her set
		# up to this particular value
		foreach( $wgFlagRestrictions[$tag] as $right => $level ) {
			if( $value <= $level && $level > 0 && $wgUser->isAllowed($right) ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Returns true if a user can set $flags.
	 * This checks if the user has the right to review
	 * to the given levels for each tag.
	 * @param array $flags, suggested flags
	 * @param array $oldflags, pre-existing flags
	 * @returns bool
	 */
	public static function userCanSetFlags( $flags, $oldflags = array() ) {
		global $wgUser, $wgFlaggedRevTags, $wgFlaggedRevValues;
		
		if( !$wgUser->isAllowed('review') ) {
			return false;
		}
		# Check if all of the required site flags have a valid value
		# that the user is allowed to set.
		foreach( $wgFlaggedRevTags as $qal => $minQL ) {
			$level = isset($flags[$qal]) ? $flags[$qal] : 0;
			if( !self::userCan($qal,$level) ) {
				return false;
			} else if( isset($oldflags[$qal]) && !self::userCan($qal,$oldflags[$qal]) ) {
				return false;
			} else if( $level < 0 || $level > $wgFlaggedRevValues ) {
				return false;
			}
		}
		return true;
	}

	private function markPatrolled( $token ) {
		global $wgOut, $wgUser;

		$wgOut->setPageTitle( wfMsg( 'markedaspatrolled' ) );
		# Prevent hijacking
		if( !$wgUser->matchEditToken( $token, $this->page->getPrefixedText(), $this->rcid ) ) {
			$wgOut->addWikiText( wfMsg('sessionfailure') );
			return;
		}
		# Make sure page is not reviewable. This can be spoofed in theory,
		# but the token is salted with the id and title and this should
		# be a trusted user...so it is not really worth doing extra query
		# work over.
		if( FlaggedRevs::isPageReviewable( $this->page ) ) {
			$wgOut->showErrorPage('notargettitle', 'notargettext' );
			return;
		}
		# Mark as patrolled
		$changed = RecentChange::markPatrolled( $this->rcid );
		if( $changed ) {
			PatrolLog::record( $this->rcid );
		}
		# Inform the user
		$wgOut->addWikiText( wfMsgNoTrans( 'revreview-patrolled', $this->page->getPrefixedText() ) );
		$wgOut->returnToMain( false, SpecialPage::getTitleFor( 'Recentchanges' ) );
	}

	/**
	 * Show revision review form
	 */
	private function showRevision() {
		global $wgOut, $wgUser, $wgTitle, $wgFlaggedRevComments, $wgFlaggedRevsOverride,
			$wgFlaggedRevTags, $wgFlaggedRevValues;

		if( $this->unapprovedTags )
			$wgOut->addWikiText( '<strong>' . wfMsg( 'revreview-toolow' ) . '</strong>' );

		$wgOut->addWikiText( wfMsg( 'revreview-selected', $this->page->getPrefixedText() ) );

		$this->skin = $wgUser->getSkin();
		$rev = Revision::newFromTitle( $this->page, $this->oldid );
		# Check if rev exists
		# Do not mess with deleted revisions
		if( !isset( $rev ) || $rev->mDeleted ) {
			$wgOut->showErrorPage( 'internalerror', 'notargettitle', 'notargettext' );
			return;
		}

		$wgOut->addHtml( "<ul>" );
		$wgOut->addHtml( $this->historyLine( $rev ) );
		$wgOut->addHtml( "</ul>" );

		if( $wgFlaggedRevsOverride )
			$wgOut->addWikiText( wfMsg('revreview-text') );

		$formradios = array();
		# Dynamically contruct our radio options
		foreach( $wgFlaggedRevTags as $tag => $minQL ) {
			$formradios[$tag] = array();
			for ($i=0; $i <= $wgFlaggedRevValues; $i++) {
				$formradios[$tag][] = array( "revreview-$tag-$i", "wp$tag", $i );
			}
		}
		$hidden = array(
			Xml::hidden( 'wpEditToken', $wgUser->editToken() ),
			Xml::hidden( 'target', $this->page->getPrefixedText() ),
			Xml::hidden( 'oldid', $this->oldid ) );

		$action = $wgTitle->escapeLocalUrl( 'action=submit' );
		$form = "<form name='RevisionReview' action='$action' method='post'>";
		$form .= '<fieldset><legend>' . wfMsgHtml( 'revreview-legend' ) . '</legend><table><tr>';
		# Dynamically contruct our review types
		foreach( $wgFlaggedRevTags as $tag => $minQL ) {
			$form .= '<td><strong>' . wfMsgHtml( "revreview-$tag" ) . '</strong></td><td width=\'20\'></td>';
		}
		$form .= '</tr><tr>';
		foreach( $formradios as $set => $ratioset ) {
			$form .= '<td>';
			foreach( $ratioset as $item ) {
				list( $message, $name, $field ) = $item;
				# Don't give options the user can't set unless its the status quo
				$attribs = array('id' => $name.$field);
				if( !$this->userCan($set,$field) )
					$attribs['disabled'] = 'true';
				$form .= "<div>";
				$form .= Xml::radio( $name, $field, ($field==$this->dims[$set]), $attribs );
				$form .= Xml::label( wfMsg($message), $name.$field );
				$form .= "</div>\n";
			}
			$form .= '</td><td width=\'20\'></td>';
		}
		$form .= '</tr></table></fieldset>';
		# Add box to add live notes to a flagged revision
		if( $wgFlaggedRevComments && $wgUser->isAllowed( 'validate' ) ) {
			$form .= "<fieldset><legend>" . wfMsgHtml( 'revreview-notes' ) . "</legend>" .
			"<textarea tabindex='1' name='wpNotes' id='wpNotes' rows='3' cols='80' style='width:100%'>" .
			htmlspecialchars( $this->notes ) .
			"</textarea>" .
			"</fieldset>";
		}

		$form .= '<fieldset><legend>' . wfMsgHtml('revisionreview') . '</legend>';
		$form .= '<p>'.Xml::inputLabel( wfMsg( 'revreview-log' ), 'wpReason', 'wpReason', 60 ).'</p>';
		$form .= '<p>'.Xml::submitButton( wfMsg( 'revreview-submit' ) ).'</p>';
		foreach( $hidden as $item ) {
			$form .= $item;
		}
		# Hack, versioning params
		$form .= Xml::hidden( 'templateParams', $this->templateParams ) . "\n";
		$form .= Xml::hidden( 'imageParams', $this->imageParams ) . "\n";
		$form .= Xml::hidden( 'rcid', $this->rcid ) . "\n";
		# Special token to discourage fiddling...
		$checkCode = FlaggedRevs::getValidationKey( $this->templateParams, $this->imageParams, $wgUser->getID(), $rev->getId() );
		$form .= Xml::hidden( 'validatedParams', $checkCode );
		$form .= '</fieldset>';

		$form .= '</form>';
		$wgOut->addHtml( $form );
	}

	/**
	 * @param Revision $rev
	 * @return string
	 */
	private function historyLine( $rev ) {
		global $wgContLang;
		$date = $wgContLang->timeanddate( $rev->getTimestamp() );

		$difflink = '(' . $this->skin->makeKnownLinkObj( $this->page, wfMsgHtml('diff'),
		'&diff=' . $rev->getId() . '&oldid=prev' ) . ')';

		$revlink = $this->skin->makeLinkObj( $this->page, $date, 'oldid=' . $rev->getId() );

		return
			"<li> $difflink $revlink " . $this->skin->revUserLink( $rev ) . " " . $this->skin->revComment( $rev ) . "</li>";
	}

	private function submit() {
		global $wgOut, $wgUser, $wgFlaggedRevTags;
		# If all values are set to zero, this has been unapproved
		$approved = empty($wgFlaggedRevTags);
		foreach( $this->dims as $quality => $value ) {
			if( $value ) {
				$approved = true;
				break;
			}
		}
		# We can only approve actual revisions...
		if( $approved ) {
			$rev = Revision::newFromTitle( $this->page, $this->oldid );
			# Do not mess with archived/deleted revisions
			if( is_null($rev) || $rev->mDeleted ) {
				$wgOut->showErrorPage( 'internalerror', 'revnotfoundtext' );
				return;
			}
		# We can only unapprove approved revisions...
		} else {
			$frev = FlaggedRevs::getFlaggedRev( $this->page, $this->oldid );
			# If we can't find this flagged rev, return to page???
			if( is_null($frev) ) {
				$wgOut->redirect( $this->page->getFullUrl() );
				return;
			}
		}

		$success = $approved ? $this->approveRevision( $rev ) : $this->unapproveRevision( $frev );
		# Return to our page
		if( $success ) {
			global $wgFlaggedRevsOverride;

			$wgOut->setPageTitle( wfMsgHtml('actioncomplete') );
			
			# Show success message
			$msg = $approved ? 'revreview-successful' : 'revreview-successful2';
			$wgOut->addHtml( "<div class='plainlinks'>" .wfMsgExt( $msg, array('parseinline'), 
				$this->page->getPrefixedText() ) );
			if( $wgFlaggedRevsOverride ) {
				$wgOut->addHtml( '<p>'.wfMsgExt( 'revreview-text', array('parseinline') ).'</p>' );
			} else {
				$wgOut->addHtml( '<p>'.wfMsgExt( 'revreview-text2', array('parseinline') ).'</p>' );
			}
			$msg = $approved ? 'revreview-stable1' : 'revreview-stable2';
			$id = $approved ? $rev->getId() : $frev->getRevId();
			$wgOut->addHtml( '<p>'.wfMsgExt( $msg, array('parseinline'), $this->page->getPrefixedUrl(), $id ).'</p>' );
			$wgOut->addHtml( "</div>" );
			
			$wgOut->returnToMain( false, SpecialPage::getTitleFor( 'RecentChanges' ) );
			if( $wgUser->isAllowed( 'unreviewedpages' ) )
				$wgOut->returnToMain( false, SpecialPage::getTitleFor( 'UnreviewedPages' ) );
			# Watch page if set to do so
			if( $wgUser->getOption('flaggedrevswatch') && !$this->page->userIsWatching() ) {
				$wgUser->addWatch( $this->page );
			}
		} else {
			$wgOut->showErrorPage( 'internalerror', 'revreview-changed', array($this->page->getPrefixedText()) );
		}
	}

	/**
	 * Adds or updates the flagged revision table for this page/id set
	 * @param Revision $rev
	 */
	private function approveRevision( $rev ) {
		global $wgUser, $wgParser, $wgRevisionCacheExpiry, $wgMemc;

		wfProfileIn( __METHOD__ );
		# Get the page this corresponds to
		$title = $rev->getTitle();

		$quality = 0;
		if( FlaggedRevs::isQuality($this->dims) ) {
			$quality = FlaggedRevs::isPristine($this->dims) ? 2 : 1;
		}
		# Our flags
		$flags = $this->dims;

		# Some validation vars to make sure nothing changed during
		$lastTempID = 0;
		$lastImgTime = "0";

		# Our template version pointers
		$tmpset = array();
		$templateMap = explode('#',trim($this->templateParams) );
		foreach( $templateMap as $template ) {
			if( !$template )
				continue;

			$m = explode('|',$template,2);
			if( !isset($m[0]) || !isset($m[1]) || !$m[0] )
				continue;

			list($prefixed_text,$rev_id) = $m;

			$tmp_title = Title::newFromText( $prefixed_text ); // Normalize this to be sure...
			if( is_null($title) )
				continue; // Page must be valid!

			if( $rev_id > $lastTempID )
				$lastTempID = $rev_id;

			$tmpset[] = array(
				'ft_rev_id' => $rev->getId(),
				'ft_namespace' => $tmp_title->getNamespace(),
				'ft_title' => $tmp_title->getDBkey(),
				'ft_tmp_rev_id' => $rev_id
			);
		}
		# Our image version pointers
		$imgset = array();
		$imageMap = explode('#',trim($this->imageParams) );
		foreach( $imageMap as $image ) {
			if( !$image )
				continue;
			$m = explode('|',$image,3);
			# Expand our parameters ... <name>#<timestamp>#<key>
			if( !isset($m[0]) || !isset($m[1]) || !isset($m[2]) || !$m[0] )
				continue;

			list($dbkey,$timestamp,$key) = $m;

			$img_title = Title::makeTitle( NS_IMAGE, $dbkey ); // Normalize
			if( is_null($img_title) )
				continue; // Page must be valid!

			if( $timestamp > $lastImgTime )
				$lastImgTime = $timestamp;

			$imgset[] = array(
				'fi_rev_id' => $rev->getId(),
				'fi_name' => $img_title->getDBkey(),
				'fi_img_timestamp' => $timestamp,
				'fi_img_sha1' => $key
			);
		}

		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin();
		# Clear out any previous garbage.
		# We want to be able to use this for tracking...
		$dbw->delete( 'flaggedtemplates',
			array('ft_rev_id' => $rev->getId() ),
			__METHOD__ );
		$dbw->delete( 'flaggedimages',
			array('fi_rev_id' => $rev->getId() ),
			__METHOD__ );
		# Update our versioning params
		if( !empty($tmpset) ) {
			$dbw->insert( 'flaggedtemplates', $tmpset, __METHOD__, 'IGNORE' );
		}
		if( !empty($imgset) ) {
			$dbw->insert( 'flaggedimages', $imgset, __METHOD__, 'IGNORE' );
		}
        # Get the expanded text and resolve all templates.
		# Store $templateIDs and add it to final parser output later...
        list($fulltext,$tmps,$tmpIDs,$ok,$maxID) = FlaggedRevs::expandText( $rev->getText(), $rev->getTitle(), $rev->getId() );
        if( !$ok || $maxID > $lastTempID ) {
        	$dbw->rollback(); // All versions must be specified, 0 for none
			wfProfileOut( __METHOD__ );
        	return false;
        }
		
		$article = new Article( $this->page );
		# Parse the rest and check if it matches up
		$stableOutput = FlaggedRevs::parseStableText( $article, $fulltext, $rev->getId(), false );
		if( !$stableOutput->fr_includesMatched || $stableOutput->fr_newestImageTime > $lastImgTime ) {
        	$dbw->rollback(); // All versions must be specified, 0 for none
			wfProfileOut( __METHOD__ );
        	return false;
        }
		# Merge in template params from first phase of parsing...
		$this->mergeTemplateParams( $stableOutput, $tmps, $tmpIDs, $maxID );
		
        # Compress $fulltext, passed by reference
        $textFlags = FlaggedRevs::compressText( $fulltext );

		# Write to external storage if required
		$storage = FlaggedRevs::getExternalStorage();
		if( $storage ) {
			if( is_array($storage) ) {
				# Distribute storage across multiple clusters
				$store = $storage[mt_rand(0, count( $storage ) - 1)];
			} else {
				$store = $storage;
			}
			# Store and get the URL
			$fulltext = ExternalStore::insert( $store, $fulltext );
			if( !$fulltext ) {
				# This should only happen in the case of a configuration error, where the external store is not valid
				$dbw->rollback();
				wfProfileOut( __METHOD__ );
				throw new MWException( "Unable to store text to external storage $store" );
			}
			if( $textFlags ) {
				$textFlags .= ',';
			}
			$textFlags .= 'external';
		}

		# Our review entry
 		$revset = array(
 			'fr_rev_id'    => $rev->getId(),
 			'fr_page_id'   => $title->getArticleID(),
			'fr_user'      => $wgUser->getId(),
			'fr_timestamp' => $dbw->timestamp( wfTimestampNow() ),
			'fr_comment'   => $this->notes,
			'fr_quality'   => $quality,
			'fr_tags'      => FlaggedRevs::flattenRevisionTags( $flags ),
			'fr_text'      => $fulltext, # Store expanded text for speed
			'fr_flags'     => $textFlags
		);
		
		# Update flagged revisions table
		$dbw->replace( 'flaggedrevs', array( array('fr_page_id','fr_rev_id') ), $revset, __METHOD__ );
		$dbw->commit();
		
		# Kill any text cache
		if( $wgRevisionCacheExpiry ) {
			$key = wfMemcKey( 'flaggedrevisiontext', 'revid', $rev->getId() );
			$wgMemc->delete( $key );
		}
		
		# Update recent changes
		$this->updateRecentChanges( $title, $dbw, $rev, $this->rcid );

		# Update the article review log
		$this->updateLog( $this->page, $this->dims, $this->oflags, $this->comment, $this->oldid, true );

		# Update the links tables as the stable version may now be the default page.
		# Try using the parser cache first since we didn't actually edit the current version.
		$parserCache = ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if( $poutput==false ) {
			$text = $article->getContent();
			$options = ParserOptions::newFromUser($wgUser);
			$options->setTidy(true);
			$poutput = $wgParser->parse( $text, $article->mTitle, $options );
		}
		# If we know that this is now the new stable version 
		# (which it probably is), save it to the stable cache...
		$sv = FlaggedRevs::getStablePageRev( $this->page, false, true );
		if( $sv && $sv->getRevId() == $rev->getId() ) {
			# Clear the cache...
			$this->page->invalidateCache();
			# Update stable cache with the revision we reviewed
			FlaggedRevs::updatePageCache( $article, $stableOutput );
		} else {
			# Get the old stable cache
			$stableOutput = FlaggedRevs::getPageCache( $article );
			# Clear the cache...(for page histories)
			$this->page->invalidateCache();
			if( $stableOutput !== false ) {
				# Reset stable cache if it existed, since we know it is the same.
				FlaggedRevs::updatePageCache( $article, $stableOutput );
			}
		}
		$u = new LinksUpdate( $this->page, $poutput );
		$u->doUpdate(); // Will trigger our hook to add stable links too...
		# Might as well save the cache, since it should be the same
		$parserCache->save( $poutput, $article, $wgUser );
		# Purge squid for this page only
		$article->getTitle()->purgeSquid();

		wfProfileOut( __METHOD__ );
        return true;
    }

	/**
	 * @param FlaggedRevision $frev
	 * Removes flagged revision data for this page/id set
	 */
	private function unapproveRevision( $frev ) {
		global $wgUser, $wgParser, $wgRevisionCacheExpiry, $wgMemc;

		$user = $wgUser->getId();

		wfProfileIn( __METHOD__ );
		
        $dbw = wfGetDB( DB_MASTER );
		# Delete from flaggedrevs table
		$dbw->delete( 'flaggedrevs',
			array( 'fr_page_id' => $this->page->getArticleID(), 'fr_rev_id' => $frev->getRevId() ) );
		# Wipe versioning params
		$dbw->delete( 'flaggedtemplates', array( 'ft_rev_id' => $frev->getRevId() ) );
		$dbw->delete( 'flaggedimages', array( 'fi_rev_id' => $frev->getRevId() ) );

		# Update the article review log
		$this->updateLog( $this->page, $this->dims, $this->oflags, $this->comment, $this->oldid, false );

		# Kill any text cache
		if( $wgRevisionCacheExpiry ) {
			$key = wfMemcKey( 'flaggedrevisiontext', 'revid', $frev->getRevId() );
			$wgMemc->delete( $key );
		}

		$article = new Article( $this->page );
		# Update the links tables as a new stable version
		# may now be the default page.
		$parserCache = ParserCache::singleton();
		$poutput = $parserCache->get( $article, $wgUser );
		if( $poutput==false ) {
			$text = $article->getContent();
			$poutput = $wgParser->parse($text, $article->mTitle, ParserOptions::newFromUser($wgUser));
		}
		$u = new LinksUpdate( $this->page, $poutput );
		$u->doUpdate();

		# Clear the cache...
		$this->page->invalidateCache();
		# Might as well save the cache
		$parserCache->save( $poutput, $article, $wgUser );
		# Purge squid for this page only
		$this->page->purgeSquid();

		wfProfileOut( __METHOD__ );

        return true;
    }
	
	private function updateRecentChanges( $title, $dbw, $rev, $rcid ) {
		wfProfileIn( __METHOD__ );
		# Should olders edits be marked as patrolled now?
		global $wgFlaggedRevsCascade;
		if( $wgFlaggedRevsCascade ) {
			$dbw->update( 'recentchanges',
				array( 'rc_patrolled' => 1 ),
				array( 'rc_namespace' => $title->getNamespace(),
					'rc_title' => $title->getDBKey(),
					'rc_this_oldid <= ' . $rev->getId() ),
				__METHOD__,
				array( 'USE INDEX' => 'rc_namespace_title', 'LIMIT' => 50 ) );
		} else {
			# Mark this edit as patrolled...
			$dbw->update( 'recentchanges',
				array( 'rc_patrolled' => 1 ),
				array( 'rc_this_oldid' => $rev->getId(),
					'rc_user_text' => $rev->getRawUserText(),
					'rc_timestamp' => $dbw->timestamp( $rev->getTimestamp() ) ),
				__METHOD__,
				array( 'USE INDEX' => 'rc_user_text', 'LIMIT' => 1 ) );
			# New page patrol may be enabled. If so, the rc_id may be the first
			# edit and not this one. If it is different, mark it too.
			if( $rcid && $rcid != $rev->getId() ) {
				$dbw->update( 'recentchanges',
					array( 'rc_patrolled' => 1 ),
					array( 'rc_id' => $rcid,
						'rc_type' => RC_NEW ),
					__METHOD__ );
			}
		}
		wfProfileOut( __METHOD__ );
	}
	
	private function mergeTemplateParams( $pout, $tmps, $tmpIds, $maxID ) {
		foreach( $tmps as $ns => $dbkey_id ) {
			foreach( $dbkey_id as $dbkey => $pageid ) {
				if( !isset($pout->mTemplates[$ns]) )
					$pout->mTemplates[$ns] = array();
				# Add in this template; overrides
				$pout->mTemplates[$ns][$dbkey] = $pageid;
			}
		}
		# Merge in template params from first phase of parsing...
		foreach( $tmpIds as $ns => $dbkey_id ) {
			foreach( $dbkey_id as $dbkey => $revid ) {
				if( !isset($pout->mTemplateIds[$ns]) )
					$pout->mTemplateIds[$ns] = array();
				# Add in this template; overrides
				$pout->mTemplateIds[$ns][$dbkey] = $revid;
			}
		}
		if( $maxID > $pout->fr_newestTemplateID ) {
			$pout->fr_newestTemplateID = $maxID;
		}
	}

	/**
	 * Record a log entry on the action
	 * @param Title $title
	 * @param array $dims
	 * @param array $oldDims
	 * @param string $comment
	 * @param int $revid
	 * @param bool $approve
	 * @param bool $RC, add to recentchanges (kind of spammy)
	 */
	public static function updateLog( $title, $dims, $oldDims, $comment, $oldid, $approve, $RC=false ) {
		$log = new LogPage( 'review', $RC );
		# ID, accuracy, depth, style
		$ratings = array();
		foreach( $dims as $quality => $level ) {
			$ratings[] = wfMsgForContent( "revreview-$quality" ) . ": " . wfMsgForContent("revreview-$quality-$level");
		}
		# Append comment with ratings
		if( $approve ) {
			$rating = !empty($ratings) ? '[' . implode(', ',$ratings). ']' : '';
			$comment .= $comment ? " $rating" : $rating;
		}
		if( $approve ) {
			$action = (FlaggedRevs::isQuality($dims) || FlaggedRevs::isQuality($oldDims)) ? 'approve2' : 'approve';
			$log->addEntry( $action, $title, $comment, array($oldid) );
		} else {
			$action = FlaggedRevs::isQuality($oldDims) ? 'unapprove2' : 'unapprove';
			$log->addEntry( $action, $title, $comment, array($oldid) );
		}
	}
}

class StableVersions extends UnlistedSpecialPage
{

    function __construct() {
        UnlistedSpecialPage::UnlistedSpecialPage( 'StableVersions' );
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgOut;

		$this->setHeaders();
		$this->skin = $wgUser->getSkin();
		# Our target page
		$this->target = $wgRequest->getText( 'page' );
		$this->page = Title::newFromUrl( $this->target );
		# Revision ID
		$this->oldid = $wgRequest->getVal( 'oldid' );
		$this->oldid = ($this->oldid=='best') ? 'best' : intval($this->oldid);
		# We need a page...
		if( is_null($this->page) ) {
			$wgOut->showErrorPage( 'notargettitle', 'notargettext' );
			return;
		}

		$this->showStableList();
	}

	function showStableList() {
		global $wgOut, $wgUser;
		# Must be a content page
		if( !FlaggedRevs::isPageReviewable( $this->page ) ) {
			$wgOut->addHTML( wfMsgExt('stableversions-none', array('parse'),
				$this->page->getPrefixedText() ) );
			return;
		}
		$pager = new StableRevisionsPager( $this, array(), $this->page );
		if( $pager->getNumRows() ) {
			$wgOut->addHTML( wfMsgExt('stableversions-list', array('parse'),
				$this->page->getPrefixedText() ) );
			$wgOut->addHTML( $pager->getNavigationBar() );
			$wgOut->addHTML( "<ul>" . $pager->getBody() . "</ul>" );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHTML( wfMsgExt('stableversions-none', array('parse'),
				$this->page->getPrefixedText() ) );
		}
	}

	function formatRow( $row ) {
		global $wgLang, $wgUser;

		$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $row->rev_timestamp), true );
		$ftime = $wgLang->timeanddate( wfTimestamp(TS_MW, $row->fr_timestamp), true );
		$review = wfMsg( 'stableversions-review', $ftime,
			$this->skin->userLink( $row->fr_user, $row->user_name ) .
			' ' . $this->skin->userToolLinks( $row->fr_user, $row->user_name ) );

		$lev = ( $row->fr_quality >=1 ) ? wfMsg('hist-quality') : wfMsg('hist-stable');
		$link = $this->skin->makeKnownLinkObj( $this->page, $time,
			'stableid='.$row->fr_rev_id );

		return '<li>'.$link.' ('.$review.') <strong>['.$lev.']</strong></li>';
	}
}

/**
 * Query to list out stable versions for a page
 */
class StableRevisionsPager extends ReverseChronologicalPager {
	public $mForm, $mConds;

	function __construct( $form, $conds = array(), $title ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->namespace = $title->getNamespace();
		$this->pageID = $title->getArticleID();

		parent::__construct();
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		global $wgFlaggedRevsNamespaces;

		$conds = $this->mConds;
		# Must be in a reviewable namespace
		if( !in_array($this->namespace, $wgFlaggedRevsNamespaces) ) {
			$conds[] = "1 = 0";
		}
		$conds["fr_page_id"] = $this->pageID;
		$conds[] = "fr_rev_id = rev_id";
		$conds[] = "fr_user = user_id";
		$conds[] = 'rev_deleted & '.Revision::DELETED_TEXT.' = 0';
		return array(
			'tables'  => array('flaggedrevs','revision','user'),
			'fields'  => 'fr_rev_id,fr_timestamp,rev_timestamp,fr_quality,fr_user,user_name',
			'conds'   => $conds,
			'options' => array('USE INDEX' => 'PRIMARY')
		);
	}

	function getIndexField() {
		return 'fr_rev_id';
	}
}

/**
 * Special page to list unreviewed pages
 */
class UnreviewedPages extends SpecialPage
{

    function __construct() {
        SpecialPage::SpecialPage( 'UnreviewedPages', 'unreviewedpages' );
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgOut;
		
		$this->setHeaders();
		
		if( !$wgUser->isAllowed( 'unreviewedpages' ) ) {
			$wgOut->permissionRequired( 'unreviewedpages' );
			return;
		}
		$this->skin = $wgUser->getSkin();
		
		$this->showList( $wgRequest );
	}

	function showList( $wgRequest ) {
		global $wgOut, $wgUser, $wgScript, $wgTitle;

		$namespace = $wgRequest->getIntOrNull( 'namespace' );
		$category = trim( $wgRequest->getVal( 'category' ) );

		$action = htmlspecialchars( $wgScript );
		$wgOut->addHTML( "<form action=\"$action\" method=\"get\">\n" .
			'<fieldset><legend>' . wfMsg('unreviewed-legend') . '</legend>' .
			Xml::hidden( 'title', $wgTitle->getPrefixedText() ) .
			'<p>' . Xml::label( wfMsg("namespace"), 'namespace' ) .
			FlaggedRevs::getNamespaceMenu( $namespace ) .
			'&nbsp;' . Xml::label( wfMsg("unreviewed-category"), 'category' ) .
			' ' . Xml::input( 'category', 35, $category, array('id' => 'category') ) .
			'&nbsp;&nbsp;' . Xml::submitButton( wfMsg( 'allpagessubmit' ) ) . "</p>\n" .
			"</fieldset></form>"
		);
		
		$pager = new UnreviewedPagesPager( $this, $namespace, $category );
		if( $pager->getNumRows() ) {
			$wgOut->addHTML( wfMsgExt('unreviewed-list', array('parse') ) );
			$wgOut->addHTML( $pager->getNavigationBar() );
			$wgOut->addHTML( "<ul>" . $pager->getBody() . "</ul>" );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHTML( wfMsgExt('unreviewed-none', array('parse') ) );
		}
	}
	
	function formatRow( $result ) {
		global $wgLang;

		$title = Title::makeTitle( $result->page_namespace, $result->page_title );
		$link = $this->skin->makeKnownLinkObj( $title );
		$stxt = $review = '';
		if( !is_null($size = $result->page_len) ) {
			if($size == 0)
				$stxt = ' <small>' . wfMsgHtml('historyempty') . '</small>';
			else
				$stxt = ' <small>' . wfMsgHtml('historysize', $wgLang->formatNum( $size ) ) . '</small>';
		}
		$unwatched = is_null($result->wl_user) ? wfMsgHtml("unreviewed-unwatched") : "";

		return( "<li>{$link} {$stxt} {$review}{$unwatched}</li>" );
	}
}

/**
 * Query to list out unreviewed pages
 */
class UnreviewedPagesPager extends AlphabeticPager {
	public $mForm, $mConds;
	private $namespace, $category;

	function __construct( $form, $namespace, $category=NULL, $conds = array() ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		# Must be a content page...
		global $wgFlaggedRevsNamespaces;
		if( !is_null($namespace) ) {
			$namespace = intval($namespace);
		}
		if( is_null($namespace) || !in_array($namespace,$wgFlaggedRevsNamespaces) ) {
			$namespace = empty($wgFlaggedRevsNamespaces) ? -1 : $wgFlaggedRevsNamespaces[0]; 	 
		}
		$this->namespace = $namespace;
		$this->category = $category ? str_replace(' ','_',$category) : NULL;
		
		parent::__construct();
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$conds = $this->mConds;
		$tables = array( 'page', 'flaggedpages' );
		$fields = array('page_namespace','page_title','page_len','fp_stable');
		$conds[] = 'fp_reviewed IS NULL';
		# Reviewable pages only
		$conds['page_namespace'] = $this->namespace;
		# No redirects
		$conds['page_is_redirect'] = 0;
		# Filter by category
		if( $this->category ) {
			$tables[] = 'categorylinks';
			$fields[] = 'cl_sortkey';
			$conds['cl_to'] = $this->category;
			$conds[] = 'cl_from = page_id';
			$this->mIndexField = 'cl_sortkey';
		} else {
			$fields[] = 'page_id';
			$this->mIndexField = 'page_title';
		}
		return array(
			'tables'  => $tables,
			'fields'  => $fields,
			'conds'   => $conds,
			'options' => array()
		);
	}
	
	/**
	 * Do a query with specified parameters, rather than using the object
	 * context
	 *
	 * @param string $offset Index offset, inclusive
	 * @param integer $limit Exact query limit
	 * @param boolean $descending Query direction, false for ascending, true for descending
	 * @return ResultWrapper
	 */
	function reallyDoQuery( $offset, $limit, $descending ) {
		$fname = __METHOD__ . ' (' . get_class( $this ) . ')';
		$info = $this->getQueryInfo();
		$tables = $info['tables'];
		$fields = $info['fields'];
		$conds = isset( $info['conds'] ) ? $info['conds'] : array();
		$options = isset( $info['options'] ) ? $info['options'] : array();
		if ( $descending ) {
			$options['ORDER BY'] = $this->mIndexField;
			$operator = '>';
		} else {
			$options['ORDER BY'] = $this->mIndexField . ' DESC';
			$operator = '<';
		}
		if ( $offset != '' ) {
			$conds[] = $this->mIndexField . $operator . $this->mDb->addQuotes( $offset );
		}
		$options['LIMIT'] = intval( $limit );
		# Get table names
		list($flaggedpages,$page,$categorylinks,$watchlist) = 
			$this->mDb->tableNamesN('flaggedpages','page','categorylinks','watchlist');
		# Are we filtering via category?
		if( in_array('categorylinks',$tables) ) {
			$index = $this->mDb->useIndexClause('cl_sortkey'); // *sigh*...
			$fromClause = "$categorylinks $index, $page";
		} else {
			$index = $this->mDb->useIndexClause('name_title'); // *sigh*...
			$fromClause = "$page $index";
		}
		$fields[] = 'wl_user';
		$sql = "SELECT ".implode(',',$fields).
			" FROM $fromClause".
			" LEFT JOIN $flaggedpages ON (fp_page_id=page_id)".
			" LEFT JOIN $watchlist ON (wl_namespace=page_namespace AND wl_title=page_title)".
			" WHERE ".$this->mDb->makeList($conds,LIST_AND).
			" ORDER BY ".$options['ORDER BY']." LIMIT ".$options['LIMIT'];
		# Do query!
		$res = $this->mDb->query( $sql );
		return new ResultWrapper( $this->mDb, $res );
	}

	function getIndexField() {
		return $this->mIndexField;
	}
}

/**
 * Special page to list unreviewed pages
 */
class OldReviewedPages extends SpecialPage
{

    function __construct() {
        SpecialPage::SpecialPage( 'OldReviewedPages', 'unreviewedpages' );
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgOut;
		
		$this->setHeaders();
		
		if( !$wgUser->isAllowed( 'unreviewedpages' ) ) {
			$wgOut->permissionRequired( 'unreviewedpages' );
			return;
		}
		$this->skin = $wgUser->getSkin();
		
		$this->showList( $wgRequest );
	}

	function showList( $wgRequest ) {
		global $wgOut, $wgUser, $wgScript, $wgTitle;

		$namespace = $wgRequest->getIntOrNull( 'namespace' );
		$category = trim( $wgRequest->getVal( 'category' ) );

		$action = htmlspecialchars( $wgScript );
		
		$wgOut->addHTML( "<form action=\"$action\" method=\"get\">\n" .
			'<fieldset><legend>' . wfMsg('oldreviewedpages-legend') . '</legend>' .
			Xml::hidden( 'title', $wgTitle->getPrefixedText() ) .
			Xml::label( wfMsg("unreviewed-category"), 'category' ) .
			' ' . Xml::input( 'category', 35, $category, array('id' => 'category') ) .
			'&nbsp;&nbsp;' . Xml::submitButton( wfMsg( 'allpagessubmit' ) ) . "</p>\n" .
			"</fieldset></form>"
		);
		
		$pager = new OldReviewedPagesPager( $this, $category );
		if( $pager->getNumRows() ) {
			$wgOut->addHTML( wfMsgExt('unreviewed-list', array('parse') ) );
			$wgOut->addHTML( $pager->getNavigationBar() );
			$wgOut->addHTML( "<ul>" . $pager->getBody() . "</ul>" );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHTML( wfMsgExt('unreviewed-none', array('parse') ) );
		}
	}
	
	function formatRow( $result ) {
		global $wgLang;

		$title = Title::makeTitle( $result->page_namespace, $result->page_title );
		$link = $this->skin->makeKnownLinkObj( $title );
		$stxt = $review = '';
		if(!is_null($size = $result->page_len)) {
			if($size == 0)
				$stxt = ' <small>' . wfMsgHtml('historyempty') . '</small>';
			else
				$stxt = ' <small>' . wfMsgHtml('historysize', $wgLang->formatNum( $size ) ) . '</small>';
		}
		if( $result->fp_stable )
			$review = ' (' . $this->skin->makeKnownLinkObj( $title, wfMsg('unreviewed-diff'),
				"diff=cur&oldid={$result->fp_stable}" ) . ')';

		return( "<li>{$link} {$stxt} {$review}</li>" );
	}
}

/**
 * Query to list out unreviewed pages
 */
class OldReviewedPagesPager extends AlphabeticPager {
	public $mForm, $mConds;
	private $category;

	function __construct( $form, $category=NULL, $conds = array() ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		
		$this->category = $category ? str_replace(' ','_',$category) : NULL;
		
		parent::__construct();
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$conds = $this->mConds;
		$tables = array( 'flaggedpages', 'page' );
		$fields = array('page_namespace','page_title','page_len','fp_stable');
		$conds['fp_reviewed'] = 0;
		$conds[] = 'fp_page_id = page_id';
		# Reviewable pages only (moves can make oddities, so check here)
		global $wgFlaggedRevsNamespaces;
		$conds['page_namespace'] = $wgFlaggedRevsNamespaces;
		# Filter by category
		if( $this->category ) {
			$tables[] = 'categorylinks';
			$fields[] = 'cl_sortkey';
			$conds['cl_to'] = $this->category;
			$conds[] = 'cl_from = page_id';
			$this->mIndexField = 'cl_sortkey';
			$useIndex['categorylinks'] = 'cl_sortkey'; // *sigh* ...
		} else {
			$fields[] = 'fp_page_id';
			$this->mIndexField = 'fp_page_id';
			$useIndex['page'] = 'PRIMARY';
		}
		return array(
			'tables'  => $tables,
			'fields'  => $fields,
			'conds'   => $conds,
			'options' => array( 'USE INDEX' => $useIndex )
		);
	}

	function getIndexField() {
		return $this->mIndexField;
	}
}

class ReviewedPages extends SpecialPage
{

    function __construct() {
        SpecialPage::SpecialPage( 'ReviewedPages' );
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgFlaggedRevValues, $wgFlaggedRevPristine;

		$this->setHeaders();
		$this->skin = $wgUser->getSkin();

		# Check if there is a featured level
		$maxType = $wgFlaggedRevPristine <= $wgFlaggedRevValues ? 2 : 1;
		$this->type = $wgRequest->getInt( 'level' );
		$this->type = $this->type <= $maxType ? $this->type : 0;
		
		$this->showForm();
		$this->showPageList();
	}

	function showForm() {
		global $wgOut, $wgTitle, $wgScript, $wgFlaggedRevValues, $wgFlaggedRevPristine;

		$form = Xml::openElement( 'form',
			array( 'name' => 'reviewedpages', 'action' => $wgScript, 'method' => 'get' ) );
		$form .= "<fieldset><legend>".wfMsg('reviewedpages-leg')."</legend>\n";

		$form .= Xml::openElement( 'select', array('name' => 'level') );
		$form .= Xml::option( wfMsg( "reviewedpages-lev-0" ), 0, $this->type==0 );
		$form .= Xml::option( wfMsg( "reviewedpages-lev-1" ), 1, $this->type==1 );
		# Check if there is a featured level
		if( $wgFlaggedRevPristine <= $wgFlaggedRevValues ) {
			$form .= Xml::option( wfMsg( "reviewedpages-lev-2" ), 2, $this->type==2 );
		}
		$form .= Xml::closeElement('select')."\n";

		$form .= " ".Xml::submitButton( wfMsg( 'go' ) );
		$form .= Xml::hidden( 'title', $wgTitle->getPrefixedText() );
		$form .= "</fieldset></form>\n";

		$wgOut->addHTML( $form );
	}

	function showPageList() {
		global $wgOut, $wgUser, $wgLang;

		$pager = new ReviewedPagesPager( $this, array(), $this->type );
		if( $pager->getNumRows() ) {
			$wgOut->addHTML( wfMsgExt('reviewedpages-list', array('parse') ) );
			$wgOut->addHTML( $pager->getNavigationBar() );
			$wgOut->addHTML( "<ul>" . $pager->getBody() . "</ul>" );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHTML( wfMsgExt('reviewedpages-none', array('parse') ) );
		}
	}

	function formatRow( $row ) {
		global $wgLang, $wgUser;

		$title = Title::makeTitle( $row->page_namespace, $row->page_title );
		$link = $this->skin->makeKnownLinkObj( $title, $title->getPrefixedText() );

		$SVtitle = SpecialPage::getTitleFor( 'Stableversions' );
		$list = $this->skin->makeKnownLinkObj( $SVtitle, wfMsgHtml('reviewedpages-all'),
			'page=' . $title->getPrefixedUrl() );
		$best = $this->skin->makeKnownLinkObj( $title, wfMsgHtml('reviewedpages-best'),
			'stableid=best' );

		return '<li>'.$link.' ('.$list.') ['.$best.'] </li>';
	}
}

/**
 * Query to list out stable versions for a page
 */
class ReviewedPagesPager extends AlphabeticPager {
	public $mForm, $mConds;

	function __construct( $form, $conds = array(), $type=0, $namespace=0 ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->type = $type;
		$this->namespace = $namespace;

		parent::__construct();
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$conds = $this->mConds;
		# Reviewable pages only (moves can make oddities, so check here)
		global $wgFlaggedRevsNamespaces;
		$conds['page_namespace'] = $wgFlaggedRevsNamespaces;
		$conds[] = 'page_id = fp_page_id';
		$conds['fp_quality'] = $this->type;
		return array(
			'tables' => array('flaggedpages','page'),
			'fields' => 'page_namespace,page_title,fp_page_id',
			'conds'  => $conds,
			'options' => array( 'USE INDEX' => array('flaggedpages' => 'fp_quality_page') )
		);
	}

	function getIndexField() {
		return 'fp_page_id';
	}
}

class StablePages extends SpecialPage
{

    function __construct() {
        SpecialPage::SpecialPage( 'StablePages' );
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgFlaggedRevValues, $wgFlaggedRevPristine;

		$this->setHeaders();
		$this->skin = $wgUser->getSkin();
		
		$this->showPageList();
	}

	function showPageList() {
		global $wgOut, $wgUser, $wgLang;

		$wgOut->addHTML( wfMsgExt('stablepages-text', array('parse') ) );
		$pager = new StablePagesPager( $this, array() );
		if( $pager->getNumRows() ) {
			$wgOut->addHTML( $pager->getNavigationBar() );
			$wgOut->addHTML( "<ul>" . $pager->getBody() . "</ul>" );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHTML( wfMsgExt('stablepages-none', array('parse') ) );
		}
	}

	function formatRow( $row ) {
		global $wgLang, $wgUser;

		$title = Title::makeTitle( $row->page_namespace, $row->page_title );
		$link = $this->skin->makeKnownLinkObj( $title, $title->getPrefixedText() );

		$stitle = SpecialPage::getTitleFor( 'Stabilization' );
		$config = $this->skin->makeKnownLinkObj( $stitle, wfMsgHtml('stablepages-config'),
			'page=' . $title->getPrefixedUrl() );
		$best = $this->skin->makeKnownLinkObj( $title, wfMsgHtml('reviewedpages-best'),
			'stableid=best' );

		return '<li>'.$link.' ('.$config.') ['.$best.'] </li>';
	}
}

/**
 * Query to list out stable versions for a page
 */
class StablePagesPager extends AlphabeticPager {
	public $mForm, $mConds;

	function __construct( $form, $conds = array() ) {
		$this->mForm = $form;
		$this->mConds = $conds;

		parent::__construct();
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$conds = $this->mConds;
		$conds[] = 'page_id = fpc_page_id';
		$conds['fpc_override'] = 1;
		return array(
			'tables' => array('flaggedpage_config','page'),
			'fields' => 'page_namespace,page_title,fpc_page_id',
			'conds'  => $conds,
			'options' => array()
		);
	}

	function getIndexField() {
		return 'fpc_page_id';
	}
}

class Stabilization extends UnlistedSpecialPage
{

    function __construct() {
        UnlistedSpecialPage::UnlistedSpecialPage( 'Stabilization', 'stablesettings' );
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgOut;

		$confirm = $wgRequest->wasPosted() &&
			$wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );

		$this->isAllowed = $wgUser->isAllowed( 'stablesettings' );
		# Let anyone view, but not submit...
		if( $wgRequest->wasPosted() ) {
			if( $wgUser->isBlocked( !$confirm ) ) {
				$wgOut->blockedPage();
				return;
			} else if( !$this->isAllowed ) {
				$wgOut->permissionRequired( 'stablesettings' );
				return;
			} else if( wfReadOnly() ) {
				$wgOut->readOnlyPage();
				return;
			}
		}

		$this->setHeaders();
		$this->skin = $wgUser->getSkin();

		$isValid = true;
		# Our target page
		$this->target = $wgRequest->getText( 'page' );
		$this->page = Title::newFromUrl( $this->target );
		# We need a page...
		if( is_null($this->page) ) {
			$wgOut->showErrorPage( 'notargettitle', 'notargettext' );
			return;
		} else if( !$this->page->exists() ) {
			$wgOut->addHTML( wfMsgExt( 'stabilization-notexists', array('parseinline'), $this->page->getPrefixedText() ) );
			return;
		} else if( !FlaggedRevs::isPageReviewable( $this->page ) ) {
			$wgOut->addHTML( wfMsgExt( 'stabilization-notcontent', array('parseinline'), $this->page->getPrefixedText() ) );
			return;
		}

		# Watch checkbox
		$this->watchThis = $wgRequest->getCheck( 'wpWatchthis' );
		# Reason
		$this->comment = $wgRequest->getVal( 'wpReason' );
		# Get visiblity settings...
		$config = FlaggedRevs::getPageVisibilitySettings( $this->page, true );
		$this->select = $config['select'];
		$this->override = $config['override'];
		$this->expiry = $config['expiry'] !== 'infinity' ? wfTimestamp( TS_RFC2822, $config['expiry'] ) : 'infinite';
		if( $wgRequest->wasPosted() ) {
			$this->select = $wgRequest->getInt( 'mwStableconfig-select' );
			$this->override = intval( $wgRequest->getBool( 'mwStableconfig-override' ) );
			$this->expiry = $wgRequest->getText( 'mwStableconfig-expiry' );
			if( strlen( $this->expiry ) == 0 ) {
				$this->expiry = 'infinite';
			}
			# Only 0 or 1
			if( $this->select && ($this->select !==0 && $this->select !==1) ) {
				$isValid = false;
			}
		}

		if( $isValid && $confirm ) {
			$this->submit();
		} else {
			$this->showSettings();
		}
	}

	function showSettings( $err = null ) {
		global $wgOut, $wgTitle, $wgUser;

		$wgOut->setRobotpolicy( 'noindex,nofollow' );
		# Must be a content page
		if( !FlaggedRevs::isPageReviewable( $this->page ) ) {
			$wgOut->addHTML( wfMsgExt('stableversions-none', array('parse'), $this->page->getPrefixedText() ) );
			return;
		}

		if ( "" != $err ) {
			$wgOut->setSubtitle( wfMsgHtml( 'formerror' ) );
			$wgOut->addHTML( "<p class='error'>{$err}</p>\n" );
		}

		if( !$this->isAllowed ) {
			$form = wfMsgExt( 'stabilization-perm', array('parse'), $this->page->getPrefixedText() );
			$off = array('disabled' => 'disabled');
		} else {
			$form = wfMsgExt( 'stabilization-text', array('parse'), $this->page->getPrefixedText() );
			$off = array();
		}

		$special = SpecialPage::getTitleFor( 'Stabilization' );
		$form .= Xml::openElement( 'form', array( 'name' => 'stabilization', 'action' => $special->getLocalUrl( ), 'method' => 'post' ) );

		$form .= "<fieldset><legend>".wfMsg('stabilization-def')."</legend>";
		$form .= "<table><tr>";
		$form .= "<td>".Xml::radio( 'mwStableconfig-override', 1, (1==$this->override), array('id' => 'default-stable')+$off)."</td>";
		$form .= "<td>".Xml::label( wfMsg('stabilization-def1'), 'default-stable' )."</td>";
		$form .= "</tr><tr>";
		$form .= "<td>".Xml::radio( 'mwStableconfig-override', 0, (0==$this->override), array('id' => 'default-current')+$off)."</td>";
		$form .= "<td>".Xml::label( wfMsg('stabilization-def2'), 'default-current' )."</td>";
		$form .= "</tr></table></fieldset>";

		$form .= "<fieldset><legend>".wfMsg('stabilization-select')."</legend>";
		$form .= "<table><tr>";
		$form .= "<td>".Xml::radio( 'mwStableconfig-select', 0, (0==$this->select), array('id' => 'stable-select1')+$off )."</td>";
		$form .= "<td>".Xml::label( wfMsg('stabilization-select1'), 'stable-select1' )."</td>";
		$form .= "</tr><tr>";
		$form .= "<td>".Xml::radio( 'mwStableconfig-select', 1, (1==$this->select), array('id' => 'stable-select2')+$off )."</td>";
		$form .= "<td>".Xml::label( wfMsg('stabilization-select2'), 'stable-select2' )."</td>";
		$form .= "</tr></table></fieldset>";

		if( $this->isAllowed ) {
			$form .= "<fieldset><legend>".wfMsgHtml('stabilization-leg')."</legend>";
			$form .= '<table>';
			$form .= '<tr><td>'.Xml::label( wfMsg('stabilization-comment'), 'wpReason' ).'</td>';
			$form .= '<td>'.Xml::input( 'wpReason', 60, $this->comment, array('id' => 'wpReason') )."</td></tr>";
		} else {
			$form .= '<table>';
		}
		$form .= '<tr>';
		$form .= '<td><label for="expires">' . wfMsgExt( 'stabilization-expiry', array( 'parseinline' ) ) . '</label></td>';
		$form .= '<td>' . Xml::input( 'mwStableconfig-expiry', 60, $this->expiry, array('id' => 'expires')+$off ) . '</td>';
		$form .= '</tr>';
		$form .= '</table>';

		if( $this->isAllowed ) {
			$watchLabel = wfMsgExt('watchthis', array('parseinline'));
			$watchAttribs = array('accesskey' => wfMsg( 'accesskey-watch' ), 'id' => 'wpWatchthis');
			$watchChecked = ( $wgUser->getOption( 'watchdefault' ) || $wgTitle->userIsWatching() );

			$form .= "<p>&nbsp;&nbsp;&nbsp;".Xml::check( 'wpWatchthis', $watchChecked, $watchAttribs );
			$form .= "&nbsp;<label for='wpWatchthis'".$this->skin->tooltipAndAccesskey('watch').">{$watchLabel}</label></p>";

			$form .= Xml::hidden( 'title', $wgTitle->getPrefixedText() );
			$form .= Xml::hidden( 'page', $this->page->getPrefixedText() );
			$form .= Xml::hidden( 'wpEditToken', $wgUser->editToken() );

			$form .= '<p>'.Xml::submitButton( wfMsg( 'stabilization-submit' ) ).'</p>';
			$form .= "</fieldset>";
		}

		$form .= '</form>';

		$wgOut->addHTML( $form );

		$wgOut->addHtml( Xml::element( 'h2', NULL, htmlspecialchars( LogPage::logName( 'stable' ) ) ) );
		$logViewer = new LogViewer(
			new LogReader( new FauxRequest(
				array( 'page' => $this->page->getPrefixedText(), 'type' => 'stable' ) ) ) );
		$logViewer->showList( $wgOut );
	}

	function submit() {
		global $wgOut, $wgUser, $wgParser, $wgFlaggedRevsOverride, $wgFlaggedRevsPrecedence;

		$changed = $reset = false;
		# Take this opportunity to purge out expired configurations
		FlaggedRevs::purgeExpiredConfigurations();

		if( $this->expiry == 'infinite' || $this->expiry == 'indefinite' ) {
			$expiry = Block::infinity();
		} else {
			# Convert GNU-style date, on error returns -1 for PHP <5.1 and false for PHP >=5.1
			$expiry = strtotime( $this->expiry );

			if( $expiry < 0 || $expiry === false ) {
				$this->showSettings( wfMsg( 'stabilize_expiry_invalid' ) );
				return false;
			}

			$expiry = wfTimestamp( TS_MW, $expiry );

			if ( $expiry < wfTimestampNow() ) {
				$this->showSettings( wfMsg( 'stabilize_expiry_old' ) );
				return false;
			}
		}

		$dbw = wfGetDB( DB_MASTER );
		# Get current config
		$row = $dbw->selectRow( 'flaggedpage_config',
			array( 'fpc_select', 'fpc_override', 'fpc_expiry' ),
			array( 'fpc_page_id' => $this->page->getArticleID() ),
			__METHOD__ );
		# If setting to site default values, erase the row if there is one
		if( $row && $this->select != $wgFlaggedRevsPrecedence && $this->override == $wgFlaggedRevsOverride ) {
			$reset = true;
			$dbw->delete( 'flaggedpage_config',
				array( 'fpc_page_id' => $this->page->getArticleID() ),
				__METHOD__ );
			$changed = ($dbw->affectedRows() != 0); // did this do anything?
		# Otherwise, add a row unless we are just setting it as the site default
		# or it is the same the current one
		} else if( $this->select !=0 || $this->override !=$wgFlaggedRevsOverride ) {
			if( $row->fpc_select != $this->select || $row->fpc_override != $this->override || $row->fpc_expiry !== $expiry ) {
				$changed = true;
				$dbw->replace( 'flaggedpage_config',
					array( 'PRIMARY' ),
					array( 'fpc_page_id' => $this->page->getArticleID(),
						'fpc_select'   => $this->select,
						'fpc_override' => $this->override,
						'fpc_expiry'   => $expiry ),
					__METHOD__ );
			}
		}

		# Log if changed
		# @FIXME: do this better
		if( $changed ) {
			global $wgContLang;

			$log = new LogPage( 'stable' );
			# ID, accuracy, depth, style
			$set = array();
			$set[] = wfMsg( "stabilization-sel-short" ) . ": " .
				wfMsg("stabilization-sel-short-{$this->select}");
			$set[] = wfMsg( "stabilization-def-short" ) . ": " .
				wfMsg("stabilization-def-short-{$this->override}");
			$settings = '[' . implode(', ',$set). ']';

			$comment = '';
			# Append comment with settings (other than for resets)
			if( !$reset ) {
				$comment = $this->comment ? "{$this->comment} $settings" : "$settings";

				$encodedExpiry = Block::encodeExpiry($expiry, $dbw );
				if( $encodedExpiry != 'infinity' ) {
					$expiry_description = ' (' . wfMsgForContent( 'stabilize-expiring',
						$wgContLang->timeanddate($expiry, false, false) ) . ')';
					$comment .= "$expiry_description";
				}
			}

			if( $reset ) {
				$log->addEntry( 'reset', $this->page, $comment );
			} else {
				$log->addEntry( 'config', $this->page, $comment );
			}
		}

		# Update the links tables as the stable version may now be the default page...
    	$article = new Article( $this->page );
		FlaggedRevs::articleLinksUpdate( $article );

		if( $this->watchThis ) {
			$wgUser->addWatch( $this->page );
		} else {
			$wgUser->removeWatch( $this->page );
		}

		$wgOut->redirect( $this->page->getFullUrl() );

		return true;
	}
}

class QualityOversight extends SpecialPage
{

    function __construct() {
        SpecialPage::SpecialPage( 'QualityOversight' );
    }

    function execute( $par ) {
		global $wgOut, $wgUser, $wgRCMaxAge;
		$this->setHeaders();
		$wgOut->addHTML( wfMsgExt('qualityoversight-list', array('parse') ) );
		# Create a LogPager item to get the results and a LogEventsList
		# item to format them...
		$cutoff = time() - $wgRCMaxAge;
		$loglist = new LogEventsList( $wgUser->getSkin(), $wgOut, 0 );
		$pager = new LogPager( $loglist, 'review', '', '', '', 
			array('log_action' => array('approve2','unapprove2'), "log_timestamp > '$cutoff'" ) );
		# Insert list
		$logBody = $pager->getBody();
		if( $logBody ) {
			$wgOut->addHTML(
				$pager->getNavigationBar() .
				$loglist->beginLogEventsList() .
				$logBody .
				$loglist->endLogEventsList() .
				$pager->getNavigationBar()
			);
		} else {
			$wgOut->addWikiMsg( 'logempty' );
		}
	}
}
