<?php

class FlaggedRevsUI {
   	/**
	* Get a selector of reviewable namespaces
	* @param int $selected, namespace selected
	*/
	public static function getNamespaceMenu( $selected=null ) {
		global $wgContLang, $wgFlaggedRevsNamespaces;

		$selector = "<label for='namespace'>" . wfMsgHtml('namespace') . "</label>";
		if( $selected !== '' ) {
			if( is_null( $selected ) ) {
				# No namespace selected; let exact match work without hitting Main
				$selected = '';
			} else {
				# Let input be numeric strings without breaking the empty match.
				$selected = intval($selected);
			}
		}
		$s = "\n<select id='namespace' name='namespace' class='namespaceselector'>\n";
		$arr = $wgContLang->getFormattedNamespaces();

		foreach( $arr as $index => $name ) {
			# Content only
			if($index < NS_MAIN || !in_array($index, $wgFlaggedRevsNamespaces) )
				continue;

			$name = $index !== 0 ? $name : wfMsg('blanknamespace');

			if($index === $selected) {
				$s .= "\t" . Xml::element("option", array("value" => $index, "selected" => "selected"), $name) . "\n";
			} else {
				$s .= "\t" . Xml::element("option", array("value" => $index), $name) . "\n";
			}
		}
		$s .= "</select>\n";
		return $s;
	}
	
	/**
	 * @param array $flags
	 * @param bool $prettybox
	 * @param string $css, class to wrap box in
	 * @return string
	 * Generates a review box/tag
	 */
    public static function addTagRatings( $flags, $prettyBox = false, $css='' ) {
        global $wgFlaggedRevTags;

        $tag = '';
        if( $prettyBox )
        	$tag .= "<table id='mw-revisionratings-box' align='center' class='$css' cellpadding='0'>";
		foreach( FlaggedRevs::$dimensions as $quality => $value ) {
			$level = isset( $flags[$quality] ) ? $flags[$quality] : 0;
			$encValueText = wfMsgHtml("revreview-$quality-$level");
            $level = $flags[$quality];
            $minlevel = $wgFlaggedRevTags[$quality];
            if( $level >= $minlevel )
                $classmarker = 2;
            elseif( $level > 0 )
                $classmarker = 1;
            else
                $classmarker = 0;

            $levelmarker = $level * 20 + 20;
            if( $prettyBox ) {
            	$tag .= "<tr><td class='fr-text' valign='middle'>" . wfMsgHtml("revreview-$quality") .
					"</td><td class='fr-value$levelmarker' valign='middle'>" .
					$encValueText . "</td></tr>\n";
            } else {
				$tag .= "&nbsp;<span class='fr-marker-$levelmarker'><strong>" .
					wfMsgHtml("revreview-$quality") .
					"</strong>: <span class='fr-text-value'>$encValueText&nbsp;</span>&nbsp;" .
					"</span>\n";
			}
		}
		if( $prettyBox )
			$tag .= '</table>';

		return $tag;
    }
	
	/**
	 * @param Row $trev, flagged revision row
	 * @param string $html, the short message HTML
	 * @param int $revs_since, revisions since review
	 * @param bool $stable, are we referring to the stable revision?
	 * @param bool $synced, does stable=current and this is one of them?
	 * @param bool $old, is this an old stable version?
	 * @return string
	 * Generates a review box using a table using FlaggedRevsUI::addTagRatings()
	 */
	public static function prettyRatingBox( $frev, $shtml, $revs_since, $stable=true, $synced=false, $old=false ) {
		global $wgLang;
		# Get quality level
		$flags = $frev->getTags();
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Some checks for which tag CSS to use
		if( $pristine ) {
			$tagClass = 'flaggedrevs-box3';
			$color = 'flaggedrevs-color-3';
		} else if( $quality ) {
			$tagClass = 'flaggedrevs-box2';
			$color = 'flaggedrevs-color-2';
		} else {
			$tagClass = 'flaggedrevs-box1';
			$color = 'flaggedrevs-color-1';
		}
        # Construct some tagging
		if( $synced ) {
			$msg = $quality ? 'revreview-quality-same' : 'revreview-basic-same';
			$html = wfMsgExt($msg, array('parseinline'), $frev->getRevId(), $time, $revs_since );
		} else if( $old ) {
			$msg = $quality ? 'revreview-quality-old' : 'revreview-basic-old';
			$html = wfMsgExt($msg, array('parseinline'), $frev->getRevId(), $time );
		} else {
			$msg = $stable ? 'revreview-' : 'revreview-newest-';
			$msg .= $quality ? 'quality' : 'basic';
			$html = wfMsgExt($msg, array('parseinline'), $frev->getRevId(), $time, $revs_since );
		}
		# Make fancy box...
		$box = "<table border='0' cellspacing='0' style='background: none;'>\n";
		$box .= "<tr><td>$shtml</td><td>&nbsp;</td><td align='right'>\n";
		$box .= "<span id='mw-revisiontoggle' class='flaggedrevs_toggle' style='display:none;'
			onclick='toggleRevRatings()' title='" . wfMsgHtml('revreview-toggle-title') . "'>" . 
			wfMsgHtml( 'revreview-toggle' ) . "</span></td></tr>\n";
		$box .= "<tr><td id='mw-revisionratings'>\n";
		$box .= $html;
		# Add ratings if there are any...
		if( $stable && !empty($flags) ) {
			$box .= FlaggedRevsUI::addTagRatings( $flags, true, $color );
		}
		$box .= "</td></tr></table>";

        return $box;
	}
	
	 /**
	 * Adds a brief review form to a page.
	 * @param OutputPage $out
	 * @param Title $title
	 * @param bool $top, should this form always go on top?
	 */
    public static function addQuickReview( $out, $title, $top = false ) {
		global $wgOut, $wgUser, $wgRequest, $wgFlaggedRevComments, $wgFlaggedRevsOverride;
		# User must have review rights
		if( !$wgUser->isAllowed( 'review' ) ) {
			return;
		}
		# Revision being displayed
		$id = $out->mRevisionId;
		# Must be a valid non-printable output
		if( !$id || $out->isPrintable() ) {
			return;
		}
		if( !isset($out->mTemplateIds) || !isset($out->fr_ImageSHA1Keys) ) {
			return; // something went terribly wrong...
		}
		$skin = $wgUser->getSkin();
		
		# See if the version being displayed is flagged...
		$oldflags = $this->getFlagsForRevision( $id );
		# If we are reviewing updates to a page, start off with the stable revision's
		# flags. Otherwise, we just fill them in with the selected revision's flags.
		if( $this->isDiffFromStable ) {
			$srev = $this->getStableRev( true );
			$flags = $srev->getTags();
			# Check if user is allowed to renew the stable version. 
			# If not, then get the flags for the new revision itself.
			if( !RevisionReview::userCanSetFlags( $oldflags ) ) {
				$flags = $oldflags;
			}
		} else {
			$flags = $this->getFlagsForRevision( $id );
		}

		$reviewtitle = SpecialPage::getTitleFor( 'RevisionReview' );
		$action = $reviewtitle->getLocalUrl( 'action=submit' );
		$form = Xml::openElement( 'form', array( 'method' => 'post', 'action' => $action ) );
		$form .= Xml::openElement( 'fieldset', array('class' => 'flaggedrevs_reviewform') );
		$form .= "<legend>" . wfMsgHtml( 'revreview-flag' ) . "</legend>\n";

		if( $wgFlaggedRevsOverride ) {
			$form .= '<p>'.wfMsgExt( 'revreview-text', array('parseinline') ).'</p>';
		} else {
			$form .= '<p>'.wfMsgExt( 'revreview-text2', array('parseinline') ).'</p>';
		}

		# Current user has too few rights to change at least one flag, thus entire form disabled
		$disabled = !RevisionReview::userCanSetFlags( $flags );
		if( $disabled ) {
			$form .= Xml::openElement( 'div', array('class' => 'fr-rating-controls-disabled',
				'id' => 'fr-rating-controls-disabled') );
			$toggle = array( 'disabled' => "disabled" );
		} else {
			$form .= Xml::openElement( 'div', array('class' => 'fr-rating-controls', 'id' => 'fr-rating-controls') );
			$toggle = array();
		}
		$size = count(FlaggedRevs::$dimensions,1) - count(FlaggedRevs::$dimensions);

		$form .= Xml::openElement( 'span', array('id' => 'mw-ratingselects') );
		# Loop through all different flag types
		foreach( FlaggedRevs::$dimensions as $quality => $levels ) {
			$label = array();
			$selected = ( isset($flags[$quality]) ) ? $flags[$quality] : 1;
			if( $disabled ) {
				$label[$selected] = $levels[$selected];
			# else collect all quality levels of a flag current user can set
			} else {
				foreach( $levels as $i => $name ) {
					if ( !RevisionReview::userCan($quality, $i) ) {
						break;
					}
					$label[$i] = $name;
				}
			}
			$quantity = count( $label );
			$form .= Xml::openElement( 'span', array('class' => 'fr-rating-options') ) . "\n";
			$form .= "<b>" . wfMsgHtml("revreview-$quality") . ":</b> ";
			# if the sum of qualities of all flags is above 6, use drop down boxes
			# 6 is an arbitrary value choosen according to screen space and usability
			if( $size > 6 ) {
				$attribs = array( 'name' => "wp$quality", 'onchange' => "updateRatingForm()" ) + $toggle;
				$form .= Xml::openElement( 'select', $attribs );
				foreach( $label as $i => $name ) {
					$optionClass = array( 'class' => "fr-rating-option-$i" );
					$form .= Xml::option( wfMsg( "revreview-$name" ), $i, ($i == $selected), $optionClass )
						."\n";
				}
				$form .= Xml::closeElement('select')."\n";
			# if there are more than two qualities (none, 1 and more) current user gets radio buttons
			} else if( $quantity > 2 ) {
				foreach( $label as $i => $name ) {
					$attribs = array( 'class' => "fr-rating-option-$i", 'onchange' => "updateRatingForm()" );
					$form .= Xml::radioLabel( wfMsg( "revreview-$name" ), "wp$quality", $i, "wp$quality".$i,
						($i == $selected), $attribs ) . "\n";
				}
			# else make checkboxes (two qualities available for current user
			# and disabled fields in case we are below the magic 6)
			} else {
				$i = ( $disabled ) ? $selected : 1;
				$attribs = array( 'class' => "fr-rating-option-$i", 'onchange' => "updateRatingForm()" )
					+ $toggle;
				$form .= Xml::checkLabel( wfMsg( "revreview-$label[$i]" ), "wp$quality", "wp$quality".$i,
					($selected == $i), $attribs ) . "\n";
			}
			$form .= Xml::closeElement( 'span' );
		}
		$form .= Xml::closeElement( 'span' );
		
		if( $wgFlaggedRevComments && $wgUser->isAllowed( 'validate' ) ) {
			$form .= "<div id='mw-notebox'>\n";
			$form .= "<p>" . wfMsgHtml( 'revreview-notes' ) . "</p>\n";
			$form .= "<p>" . Xml::openElement( 'textarea', array('name' => 'wpNotes', 'id' => 'wpNotes',
				'class' => 'fr-notes-box', 'rows' => '2', 'cols' => '80') ) . Xml::closeElement('textarea') . "</p>\n";
			$form .= "</div>\n";
		}

		$imageParams = $templateParams = '';
		# Hack, add NS:title -> rev ID mapping
		foreach( $out->mTemplateIds as $namespace => $title ) {
			foreach( $title as $dbkey => $revid ) {
				$title = Title::makeTitle( $namespace, $dbkey );
				$templateParams .= $title->getPrefixedText() . "|" . $revid . "#";
			}
		}
		# Hack, image -> timestamp mapping
		foreach( $out->fr_ImageSHA1Keys as $dbkey => $timeAndSHA1 ) {
			foreach( $timeAndSHA1 as $time => $sha1 ) {
				$imageParams .= $dbkey . "|" . $time . "|" . $sha1 . "#";
			}
		}
		# For image pages, note the current image version
		if( $title->getNamespace() == NS_IMAGE ) {
			$file = wfFindFile( $title );
			if( $file ) {
				$imageParams .= $title->getDBkey() . "|" . $file->getTimestamp() . "|" . $file->getSha1() . "#";
			}
		}
		
		# Hidden params
		$form .= Xml::hidden( 'title', $reviewtitle->getPrefixedText() ) . "\n";
		$form .= Xml::hidden( 'target', $title->getPrefixedText() ) . "\n";
		$form .= Xml::hidden( 'oldid', $id ) . "\n";
		$form .= Xml::hidden( 'action', 'submit') . "\n";
		$form .= Xml::hidden( 'wpEditToken', $wgUser->editToken() ) . "\n";
		# Add review parameters
		$form .= Xml::hidden( 'templateParams', $templateParams ) . "\n";
		$form .= Xml::hidden( 'imageParams', $imageParams ) . "\n";
		# Pass this in if given; useful for new page patrol
		$form .= Xml::hidden( 'rcid', $wgRequest->getVal('rcid') ) . "\n";
		# Special token to discourage fiddling...
		$checkCode = FlaggedRevs::getValidationKey( $templateParams, $imageParams, $wgUser->getID(), $id );
		$form .= Xml::hidden( 'validatedParams', $checkCode ) . "\n";

		$form .= Xml::openElement( 'span', array('style' => 'white-space: nowrap;') );
		# Hide comment if needed
		if( !$disabled ) {
			$form .= "<span id='mw-commentbox'><br/>" . Xml::inputLabel( wfMsg('revreview-log'), 'wpReason', 
				'wpReason', 50, '', array('class' => 'fr-comment-box') ) . "&nbsp;&nbsp;&nbsp;</span>";
		}
		$form .= Xml::submitButton( wfMsgHtml('revreview-submit'), array('id' => 'mw-submitbutton')+$toggle);
		$form .= Xml::closeElement( 'span' );
		
		$form .= Xml::closeElement( 'div' );
		$form .= Xml::closeElement( 'fieldset' );
		$form .= Xml::closeElement( 'form' );

		if( $top ) {
			$out->mBodytext = $form . $out->mBodytext;
		} else {
			$wgOut->addHTML( $form );
		}
    }
}
