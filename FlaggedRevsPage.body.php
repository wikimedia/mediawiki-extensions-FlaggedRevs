<?php

#(c) Joerg Baach, Aaron Schulz, 2007 GPL

global $IP;
require_once( "$IP/includes/LogPage.php" );
require_once( "$IP/includes/SpecialLog.php" );

class Revisionreview extends SpecialPage
{

    function Revisionreview() {
        SpecialPage::SpecialPage('Revisionreview', 'review');
    }

    function execute( $par ) {
        global $wgRequest, $wgUser, $wgOut, $wgFlaggedRevComments, $wgFlaggedRevTags;
        
		if( !$wgUser->isAllowed( 'review' ) ) {
			$wgOut->permissionRequired( 'review' );
			return;
		}
		$this->setHeaders();
		// Our target page
		$this->target = $wgRequest->getText( 'target' );
		// Revision ID
		$this->oldid = $wgRequest->getIntOrNull( 'oldid' );
		
		// Must be a valid content page
		$this->page = Title::newFromUrl( $this->target );
		if ( !$this->target || !$this->oldid || !$this->page->isContentPage() ) {
			$wgOut->addHTML( wfMsgExt('revreview-main',array('parse')) );
			return;
		}
		if( is_null($this->page) || is_null($this->oldid) ) {
			$wgOut->showErrorPage('notargettitle', 'notargettext' );
			return;
		}
		// Time of page view when viewd
		$this->timestamp = $wgRequest->getVal( 'wpTimestamp' );
		// Log comment
		$this->comment = $wgRequest->getText( 'wpReason' );
		// Additional notes
		$this->notes = ($wgFlaggedRevComments) ? $wgRequest->getText('wpNotes') : '';
		// Get the revision's current flags, if any
		$this->oflags = FlaggedRevs::getFlagsForRevision( $this->oldid );
		// Get our accuracy/quality dimensions
		$this->dims = array();
		$this->upprovedTags = 0;
		foreach ( array_keys($wgFlaggedRevTags) as $tag ) {
			$this->dims[$tag] = $wgRequest->getIntOrNull( "wp$tag" );
			// Must be greater than zero
			if ( $this->dims[$tag] < 0 ) {
				$wgOut->showErrorPage('notargettitle', 'notargettext' );
				return;
			}
			if ( $this->dims[$tag]==0 )
				$this->upprovedTags++;
			// Check permissions
			if( !$this->userCan( $tag, $this->oflags[$tag] ) ) {
				# Users can't take away a status they can't set
				$wgOut->permissionRequired( 'badaccess-group0' );
				return;
			} else if( !$this->userCan( $tag, $this->dims[$tag] ) ) {
			// Users cannot review to beyond their rights level
				$wgOut->permissionRequired( 'badaccess-group0' );
				return;
			}
		}
		// We must at least rate each category as 1, the minimum
		// Exception: we can rate ALL as unapproved to depreciate a revision
		$this->isValid = true;
		if ( $this->upprovedTags && $this->upprovedTags < count($wgFlaggedRevTags) )
			$this->isValid = false;
		
		if( $this->isValid && $wgRequest->wasPosted() ) {
			$this->submit( $wgRequest );
		} else {
			$this->showRevision( $wgRequest );
		}
	}
	
	/**
	 * @param string $tag
	 * @param int $val
	 * Returns true if a user can do something
	 */	
	function userCan( $tag, $value ) {
		global $wgFlagRestrictions, $wgUser;
		
		if ( !isset($wgFlagRestrictions[$tag]) )
			return true;
		// Validators always have full access
		if ( $wgUser->isAllowed('validate') )
			return true;
		// Check if this user has any right that lets him/her set
		// up to this particular value
		foreach ( $wgFlagRestrictions[$tag] as $right => $level ) {
			if ( $value <= $level && $wgUser->isAllowed($right) ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @param webrequest $request
	 */
	function showRevision( $request ) {
		global $wgOut, $wgUser, $wgTitle, 
		$wgFlaggedRevComments, $wgFlaggedRevTags, $wgFlaggedRevValues;
		
		if ( !$this->isValid )
			$wgOut->addWikiText( '<strong>' . wfMsg( 'revreview-toolow' ) . '</strong>' );
		
		$wgOut->addWikiText( wfMsg( 'revreview-selected', $this->page->getPrefixedText() ) );
		
		$this->skin = $wgUser->getSkin();
		$rev = Revision::newFromTitle( $this->page, $this->oldid );
		// Check if rev exists
		// Do not mess with deleted revisions
		if( !isset( $rev ) || $rev->mDeleted ) {
			$wgOut->showErrorPage( 'internalerror', 'notargettitle', 'notargettext' );
			return;
		}
		$wgOut->addHtml( "<ul>" );
		$wgOut->addHtml( $this->historyLine( $rev ) );
		$wgOut->addHtml( "</ul>" );
		
		$wgOut->addWikiText( wfMsg('revreview-text') );
		$formradios = array();
		// Dynamically contruct our radio options
		foreach ( array_keys($wgFlaggedRevTags) as $tag ) {
			$formradios[$tag] = array();
			for ($i=0; $i <= $wgFlaggedRevValues; $i++) {
				$formradios[$tag][] = array( "revreview-$tag-$i", "wp$tag", $i );
			}
		}
		$items = array(
			wfInputLabel( wfMsgHtml( 'revreview-log' ), 'wpReason', 'wpReason', 60 ),
			wfSubmitButton( wfMsgHtml( 'revreview-submit' ) ) );
		$hidden = array(
			wfHidden( 'wpEditToken', $wgUser->editToken() ),
			wfHidden( 'target', $this->page->getPrefixedText() ),
			wfHidden( 'oldid', $this->oldid ) );	
		
		$action = $wgTitle->escapeLocalUrl( 'action=submit' );		
		$form = "<form name='revisionreview' action='$action' method='post'>";
		$form .= '<fieldset><legend>' . wfMsgHtml( 'revreview-legend' ) . '</legend><table><tr>';
		// Dynamically contruct our review types
		foreach ( array_keys($wgFlaggedRevTags) as $tag ) {
			$form .= '<td><strong>' . wfMsgHtml( "revreview-$tag" ) . '</strong></td><td width=\'20\'></td>';
		}
		$form .= '</tr><tr>';
		foreach ( $formradios as $set => $ratioset ) {
			$form .= '<td>';
			foreach( $ratioset as $item ) {
				list( $message, $name, $field ) = $item;
				// Don't give options the user can't set unless its the status quo
				$disabled = ( !$this->userCan($set,$field) ) ? array('disabled' => 'true') : array();
				$form .= "<div>";
				$form .= Xml::radio( $name, $field, ($field==$this->dims[$set]), $disabled ) . ' ' . wfMsg($message);
				$form .= "</div>\n";
			}
			$form .= '</td><td width=\'20\'></td>';
		}
		$form .= '</tr></table></fieldset>';
		// Add box to add live notes to a flagged revision
		if ( $wgFlaggedRevComments ) {
			$form .= "<fieldset><legend>" . wfMsgHtml( 'revreview-notes' ) . "</legend>" .
			"<textarea tabindex='1' name='wpNotes' id='wpNotes' rows='3' cols='80' style='width:100%'>$this->notes</textarea>" .	
			"</fieldset>";
		}
		
		foreach( $items as $item ) {
			$form .= '<p>' . $item . '</p>';
		}	
		foreach( $hidden as $item ) {
			$form .= $item;
		}
		
		$form .= '</form>';
		$wgOut->addHtml( $form );
	}
	
	/**
	 * @param Revision $rev
	 * @returns string
	 */
	function historyLine( $rev ) {
		global $wgContLang;
		$date = $wgContLang->timeanddate( $rev->getTimestamp() );
		
		$difflink = '(' . $this->skin->makeKnownLinkObj( $this->page, wfMsgHtml('diff'), 
		'&diff=' . $rev->getId() . '&oldid=prev' ) . ')';
		
		$revlink = $this->skin->makeLinkObj( $this->page, $date, 'oldid=' . $rev->getId() );
		
		return
			"<li> $difflink $revlink " . $this->skin->revUserLink( $rev ) . " " . $this->skin->revComment( $rev ) . "</li>";
	}
	
	function submit( $request ) {
		global $wgOut;
		
		$approved = false;
		# If all values are set to zero, this has been unapproved
		foreach( $this->dims as $quality => $value ) {
			if( $value ) $approved = true;
		}
		// We can only approve actually revs
		if ( $approved ) {
			$rev = Revision::newFromTitle( $this->page, $this->oldid );
			// Do not mess with archived/deleted revisions
			if ( is_null($rev) || $rev->mDeleted ) {
				$wgOut->showErrorPage( 'internalerror', 'badarticleerror' ); 
				return;
			}
		} else {
			$frev = FlaggedRevs::getFlaggedRev( $this->oldid );
			if ( is_null($frev) ) {
				$wgOut->showErrorPage( 'internalerror', 'badarticleerror' ); 
				return;
			}
		}
		
		$success = ( $approved ) ? 
			$this->approveRevision( $rev, $this->notes ) : $this->unapproveRevision( $frev );
		// Return to our page			
		if ( $success ) {
        	$wgOut->redirect( $this->page->escapeLocalUrl() );
		} else {
			$wgOut->showErrorPage( 'internalerror', 'badarticleerror' ); 
		}
	}

	/**
	 * @param Revision $rev
	 * Adds or updates the flagged revision table for this page/id set
	 */
	function approveRevision( $rev=NULL, $notes='' ) {
		global $wgUser, $wgParser;
		
		wfProfileIn( __METHOD__ );
		
		if( is_null($rev) ) return false;
		// No bogus timestamps
		if ( $this->timestamp && ($this->timestamp < $rev->getTimestamp() || $this->timestamp > wfTimestampNow()) )
			return false;
			
		$timestamp = $this->timestamp ? $this->timestamp : wfTimestampNow();
        // Get the page text and esolve all templates
        $fulltext = FlaggedRevs::expandText( $rev->getText(), $rev->getTitle() );
		
		$quality = 0;
		if ( FlaggedRevs::isQuality($this->dims) ) {
			$quality = FlaggedRevs::getLCQuality($this->dims);
			$quality = ($quality > 1) ? $quality : 1;
		}
		
		$title = $rev->getTitle();
		
		$dbw = wfGetDB( DB_MASTER );
		// Our review entry
 		$revset = array(
 			'fr_namespace' => $title->getNamespace(),
 			'fr_title'     => $title->getDBkey(),
			'fr_rev_id'    => $rev->getId(),
			'fr_user'      => $wgUser->getId(),
			'fr_timestamp' => $timestamp,
			'fr_comment'   => $notes,
			'fr_text'      => $fulltext, // Store expanded text for good-measure
			'fr_quality'   => $quality
		);
		// Our flags
		$flagset = array();
		foreach ( $this->dims as $tag => $value ) {
			$flagset[] = array(
				'frt_dimension' => $tag, 
				'frt_rev_id' => $rev->getId(), 
				'frt_value' => $value 
			);
		}
		// Update flagged revisions table
		$dbw->replace( 'flaggedrevs', array( array('fr_namespace','fr_title','fr_rev_id') ), $revset, __METHOD__ );
		// Set all of our flags
		$dbw->replace( 'flaggedrevtags', array( array('frt_rev_id','frt_dimension') ), $flagset, __METHOD__ );

		// Update the article review log
		$this->updateLog( $this->page, $this->dims, $this->comment, $this->oldid, true );
		
		# Clear the cache...
		$this->page->invalidateCache();
		# Purge squid for this page only
		$this->page->purgeSquid();
		
        return true;
    }

	/**
	 * @param Revision $rev
	 * Removes flagged revision data for this page/id set
	 */  
	function unapproveRevision( $row=NULL ) {
		global $wgUser;
		
		wfProfileIn( __METHOD__ );
	
		if( is_null($row) ) return false;
		
		$user = $wgUser->getId();
		$timestamp = wfTimestampNow();
		
        $dbw = wfGetDB( DB_MASTER );
		// Delete from table
		$dbw->delete( 'flaggedrevs', array( 'fr_rev_id' => $row->fr_rev_id ) );
		// And the flags...
		$dbw->delete( 'flaggedrevtags', array( 'frt_rev_id' => $row->fr_rev_id ) );
		
		// Update the article review log
		$this->updateLog( $this->page, $this->dims, $this->comment, $this->oldid, false );
		
		# Clear the cache...
		$this->page->invalidateCache();
		# Purge squid for this page only
		$this->page->purgeSquid();
		
        return true;
    }

	/**
	 * Record a log entry on the action
	 * @param Title $title
	 * @param array $dimensions
	 * @param string $comment
	 * @param int $revid
	 * @param bool $approve
	 */	
	function updateLog( $title, $dimensions, $comment, $oldid, $approve ) {
		$log = new LogPage( 'review' );
		// ID, accuracy, depth, style
		$ratings = array();
		foreach( $dimensions as $quality => $level ) {
			$ratings[] = wfMsg( "revreview-$quality" ) . ": " . wfMsg("revreview-$quality-$level");
		}
		$rating = ($approve) ? ' [' . implode(', ',$ratings). ']' : '';
		// Append comment with action
		$action = wfMsgExt('review-logaction', array('parsemag'), $oldid );
		$comment = ($comment) ? "$action: $comment$rating" : "$action $rating"; 
			
		if ( $approve ) {
			$log->addEntry( 'approve', $title, $comment );
		} else {
			$log->addEntry( 'unapprove', $title, $comment );
		}
	}
}

class Stableversions extends SpecialPage
{

    function Stableversions() {
        SpecialPage::SpecialPage('Stableversions');
    }

    function execute( $par ) {
        global $wgRequest;

		$this->setHeaders();
		// Our target page
		$this->page = $wgRequest->getText( 'page' );
		// Revision ID
		$this->oldid = $wgRequest->getIntOrNull( 'oldid' );
		
		if( $this->oldid ) {
			$this->showStableRevision( $wgRequest );
		} else if( $this->page ) {
			$this->showStableList( $wgRequest );
		} else {
			$this->showForm( $wgRequest );
		}
	}
	
	function showForm( $wgRequest ) {
		global $wgOut, $wgTitle, $wgScript;
	
		$encPage = $this->page;
		$encId = $this->oldid;
				
		$form = "<form name='stableversions' action='$wgScript' method='get'>";
		$form .= "<fieldset><legend>".wfMsg('stableversions-leg1')."</legend>";
		$form .= "<table><tr>";
		$form .= "<td>".Xml::hidden( 'title', $wgTitle->getPrefixedText() )."</td>";
		$form .= "<td>".wfMsgHtml("stableversions-page").":</td>";
		$form .= "<td>".Xml::input('page', 50, $encPage, array( 'id' => 'page' ) )."</td>";
		$form .= "<td>".wfSubmitButton( wfMsgHtml( 'go' ) )."</td>";
		$form .= "</tr></table>";
		$form .= "</fieldset></form>\n";
		
		$form .= "<form name='stableversion' action='$wgScript' method='get'>";
		$form .= "<fieldset><legend>".wfMsg('stableversions-leg2')."</legend>";
		$form .= "<table><tr>";
		$form .= "<td>".Xml::hidden( 'title', $wgTitle->getPrefixedDBkey() )."</td>";
		$form .= "<td>".wfMsgHtml("stableversions-rev").":</td>";
		$form .= "<td>".Xml::input('oldid', 15, $encId, array( 'id' => 'oldid' ) )."</td>";
		$form .= "<td>".wfSubmitButton( wfMsgHtml( 'go' ) )."</td>";
		$form .= "</tr></table>";
		$form .= "</fieldset></form>";
		$wgOut->addHTML( $form );
	}
	
	function showStableRevision( $frev ) {
		global $wgParser, $wgLang, $wgUser, $wgOut, $wgTitle;
			
		// Get the revision
		$frev = FlaggedRevs::getFlaggedRev( $this->oldid );
		// Revision must exists
		if( is_null($frev) ) {
			$wgOut->showErrorPage('notargettitle', 'badarticleerror' );
			return;
		}
		// Must be a valid page/Id
		$page = Title::makeTitle( $frev->fr_namespace, $frev->fr_title );
		if( is_null($page) ) {
			$wgOut->showErrorPage('notargettitle', 'allpagesbadtitle' );
			return;
		}
		// Must be a content page
		$article = new Article( $page );
		if( is_null($article) ) {
			$wgOut->showErrorPage('notargettitle', 'allpagesbadtitle' );
			return;
		}
		$wgOut->setPagetitle( $page->getPrefixedText() );
		// Modifier instance
		$RevFlagging = new FlaggedRevs();
		// Get flags and date
		$flags = $RevFlagging->getFlagsForRevision( $frev->fr_rev_id );
		$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $frev->fr_timestamp), true );
       	// We will be looking at the reviewed revision...
       	$tag = wfMsgExt('revreview-static', array('parse'), urlencode($page->getPrefixedText()), $time, $page->getPrefixedText());
		$tag .= $RevFlagging->addTagRatings( $flags );
		// Parse the text...
		$text = $RevFlagging->getFlaggedRevText( $this->oldid );
		$options = ParserOptions::newFromUser($wgUser);
       	$parserOutput = $RevFlagging->parseStableText( $page, $text, $this->oldid, $options, $frev->fr_timestamp );
		$notes = $RevFlagging->ReviewNotes( $frev );
		// Set the new body HTML, place a tag on top
		$wgOut->addHTML('<div class="mw-warning plainlinks"><small>'.$tag.'</small></div>' . $parserOutput->getText() . $notes);
		# Show stable categories and interwiki links only
       	$wgOut->addCategoryLinks( $parserOutput->getCategories() );
	}
	
	function showStableList() {
		global $wgOut, $wgUser, $wgLang;
	
		$skin = $wgUser->getSkin();
		// Must be a valid page/Id
		$page = Title::newFromUrl( $this->page );
		if( is_null($page) || !$page->isContentPage() ) {
			$wgOut->showErrorPage('notargettitle', 'allpagesbadtitle' );
			return;
		}
		$article = new Article( $page );
		if( !$article ) {
			$wgOut->showErrorPage('notargettitle', 'allpagesbadtitle' );
			return;
		}
		$pager = new StableRevisionsPager( $this, array(), $page->getNamespace(), $page->getDBkey() );	
		if ( $pager->getNumRows() ) {
			$wgOut->addHTML( wfMsgExt('stableversions-list', array('parse'), $page->getPrefixedText() ) );
			$wgOut->addHTML( $pager->getNavigationBar() );
			$wgOut->addHTML( "<ul>" . $pager->getBody() . "</ul>" );
			$wgOut->addHTML( $pager->getNavigationBar() );
		} else {
			$wgOut->addHTML( wfMsgExt('stableversions-none', array('parse'), $page->getPrefixedText() ) );
		}
	}
	
	function formatRow( $row ) {
		global $wgLang, $wgUser;
		
		static $skin=null;
		if( is_null( $skin ) )
			$skin = $wgUser->getSkin();
	
		$SV = SpecialPage::getTitleFor( 'Stableversions' );
		$time = $wgLang->timeanddate( wfTimestamp(TS_MW, $row->rev_timestamp), true );
		$ftime = $wgLang->timeanddate( wfTimestamp(TS_MW, $row->fr_timestamp), true );
		$review = wfMsg( 'stableversions-review', $ftime );
		
		$lev = wfMsg('hist-stable');
		if( $row->fr_quality >=1 ) $lev = wfMsg('hist-quality');
		
		return '<li>'.$skin->makeKnownLinkObj( $SV, $time, 'oldid='.$row->fr_rev_id ).' ('.$review.') <b>'.$lev.'</b></li>';	
	}
}

/**
 *
 *
 */
class StableRevisionsPager extends ReverseChronologicalPager {
	public $mForm, $mConds;

	function __construct( $form, $conds = array(), $namespace, $title ) {
		$this->mForm = $form;
		$this->mConds = $conds;
		$this->namespace = $namespace;
		$this->title = $title;
		parent::__construct();
	}
	
	function formatRow( $row ) {
		$block = new Block;
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$conds = $this->mConds;
		$conds["fr_namespace"] = $this->namespace;
		$conds["fr_title"] = $this->title;
		$conds[] = "fr_rev_id = rev_id";
		$conds["rev_deleted"] = 0;
		return array(
			'tables' => array('flaggedrevs','revision'),
			'fields' => 'fr_rev_id,fr_timestamp,rev_timestamp,fr_quality',
			'conds' => $conds
		);
	}

	function getIndexField() {
		return 'fr_rev_id';
	}
}

class Unreviewedpages extends SpecialPage
{

    function Unreviewedpages() {
        SpecialPage::SpecialPage('Unreviewedpages');
    }

    function execute( $par ) {
        global $wgRequest;

		$this->setHeaders();
		
		$this->showList( $wgRequest );
	}
	
	function showList( $wgRequest ) {
		global $wgOut, $wgUser, $wgScript, $wgTitle;
	
		$skin = $wgUser->getSkin();
		$namespace = $wgRequest->getIntOrNull( 'namespace' );
		$nonquality = $wgRequest->getVal( 'includenonquality' );
		
		$action = htmlspecialchars( $wgScript );
		$wgOut->addHTML( "<form action=\"$action\" method=\"get\">\n" .
			'<fieldset><legend>' . wfMsg('viewunreviewed') . '</legend>' .
			$this->getNamespaceMenu( $namespace ) . "\n" .
			Xml::submitButton( wfMsg( 'allpagessubmit' ) ) . "\n" .
			'<p>' . Xml::check( 'includenonquality', $nonquality, array('id' => 'includenonquality') ) . 
			' ' . Xml::label( wfMsgHtml("included-nonquality"), 'includenonquality' ) . "</p>\n" .
			Xml::hidden( 'title', $wgTitle->getPrefixedText() ) .
			"</fieldset></form>");
		
		list( $limit, $offset ) = wfCheckLimits();
		
		$sdr = new UnreviewedPagesPage( $namespace, $nonquality );
		$sdr->doQuery( $offset, $limit );
	}
	
	function getNamespaceMenu( $selected=NULL, $allnamespaces = null, $includehidden=false ) {
		global $wgContLang, $wgContentNamespaces;
		
		$selector = "<label for='namespace'>" . wfMsgHtml('namespace') . "</label>";
		if( $selected !== '' ) {
			if( is_null( $selected ) ) {
				// No namespace selected; let exact match work without hitting Main
				$selected = '';
			} else {
				// Let input be numeric strings without breaking the empty match.
				$selected = intval( $selected );
			}
		}
		$s = "\n<select id='namespace' name='namespace' class='namespaceselector'>\n";
		$arr = $wgContLang->getFormattedNamespaces();
		if( !is_null($allnamespaces) ) {
			$arr = array($allnamespaces => wfMsg('namespacesall')) + $arr;
		}
		foreach ($arr as $index => $name) {
			# Content only
			if ($index < NS_MAIN || !isset($wgContentNamespaces[$index]) ) continue;

			$name = $index !== 0 ? $name : wfMsg('blanknamespace');

			if ($index === $selected) {
				$s .= "\t" . Xml::element("option",
						array("value" => $index, "selected" => "selected"),
						$name) . "\n";
			} else {
				$s .= "\t" . Xml::element("option", array("value" => $index), $name) . "\n";
			}
		}
		$s .= "</select>\n";
		return $s;
	}
}

class UnreviewedPagesPage extends PageQueryPage {
	
	function __construct( $namespace=NULL, $nonquality=false ) {
		$this->namespace = $namespace;
		$this->nonquality = $nonquality;
	}
	
	function getName() {
		return 'UnreviewedPages';
	}

	function isExpensive( ) { return true; }
	function isSyndicated() { return false; }

	function getPageHeader( ) {
		#FIXME : probably need to add a backlink to the maintenance page.
		return '<p>'.wfMsg("unreviewed-list")."</p><br />\n";
	}

	function getSQLText( &$dbr, $namespace, $nonquality = false ) {
		global $wgContentNamespaces;
		
		list( $page, $flaggedrevs ) = $dbr->tableNamesN( 'page', 'flaggedrevs' );

		$ns = ($namespace !== null) ? "page_namespace=$namespace" : '1 = 1';
		$where = $nonquality ? '1 = 1' : 'fr_rev_id IS NULL';
		$content = array();
		foreach( $wgContentNamespaces as $cns ) {
			$content[] = "page_namespace=$cns";
		}
		$content = implode(' OR ',$content);
		$sql = 
			"SELECT page_namespace,page_title,page_len AS size, MAX(fr_quality) as quality, COUNT(*) as num 
			FROM $page 
			LEFT JOIN $flaggedrevs ON (fr_namespace = page_namespace AND fr_title = page_title) 
			WHERE page_is_redirect=0 AND $ns AND ($where) AND ($content) 
			GROUP BY page_id ";
		return $sql;
	}
	
	function getSQL() {
		$dbr = wfGetDB( DB_SLAVE );
		return $this->getSQLText( $dbr, $this->namespace, $this->nonquality );
	}

	function getOrder() {
		return 'ORDER BY page_id DESC';
	}

	function formatResult( $skin, $result ) {
		global $wgLang;
		
		// If the best revision of the page is quality
		// then skip it...
		if ( $this->nonquality && $result->quality >= 1 ) 
			return false;
		$fname = 'UnreviewedPagesPage::formatResult';
		$title = Title::makeTitle( $result->page_namespace, $result->page_title );
		$link = $skin->makeKnownLinkObj( $title );
		$stxt = '';
		if (!is_null($size = $result->size)) {
			if ($size == 0)
				$stxt = ' <small>' . wfMsgHtml('historyempty') . '</small>';
			else
				$stxt = ' <small>' . wfMsgHtml('historysize', $wgLang->formatNum( $size ) ) . '</small>';
		}

		return( "{$link} {$stxt}" );
	}
}
?>
