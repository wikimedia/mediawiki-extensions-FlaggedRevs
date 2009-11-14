<?php

class FlaggedRevsXML {
	/**
	 * Get a selector of reviewable namespaces
	 * @param int $selected, namespace selected
	 * @param $all Mixed: Value of an item denoting all namespaces, or null to omit
	 * @returns string
	 */
	public static function getNamespaceMenu( $selected=null, $all=null ) {
		global $wgContLang, $wgFlaggedRevsNamespaces;
		$s = "<label for='namespace'>" . wfMsgHtml('namespace') . "</label>";
		if( $selected !== '' ) {
			if( is_null( $selected ) ) {
				# No namespace selected; let exact match work without hitting Main
				$selected = '';
			} else {
				# Let input be numeric strings without breaking the empty match.
				$selected = intval($selected);
			}
		}
		$s .= "\n<select id='namespace' name='namespace' class='namespaceselector'>\n";
		$arr = $wgContLang->getFormattedNamespaces();
		if( !is_null($all) ) {
			$arr = array( $all => wfMsg( 'namespacesall' ) ) + $arr; // should be first
		}
		foreach( $arr as $index => $name ) {
			# Content pages only (except 'all')
			if( $index !== $all && !in_array($index, $wgFlaggedRevsNamespaces) ) {
				continue;
			}
			$name = $index !== 0 ? $name : wfMsg('blanknamespace');
			if( $index === $selected ) {
				$s .= "\t" . Xml::element("option", array("value" => $index, "selected" => "selected"), $name) . "\n";
			} else {
				$s .= "\t" . Xml::element("option", array("value" => $index), $name) . "\n";
			}
		}
		$s .= "</select>\n";
		return $s;
	}

	/**
	 * Get a selector of review levels
	 * @param int $selected, selected level
	 * @param string $all, all selector msg?
	 * @param int $max max level?
	 * @returns string
	 */
	public static function getLevelMenu( $selected=null, $all='revreview-filter-all', $max=2 ) {
		wfLoadExtensionMessages( 'FlaggedRevs' );
		$s = "<label for='wpLevel'>" . wfMsgHtml('revreview-levelfilter') . "</label>&nbsp;";
		$s .= Xml::openElement( 'select', array('name' => 'level','id' => 'wpLevel') );
		if( $all !== false )
			$s .= Xml::option( wfMsg( $all ), -1, $selected===-1 );
		$s .= Xml::option( wfMsg( 'revreview-lev-sighted' ), 0, $selected===0 );
		if( FlaggedRevs::qualityVersions() )
			$s .= Xml::option( wfMsg( 'revreview-lev-quality' ), 1, $selected===1 );
		if( $max >= 2 && FlaggedRevs::pristineVersions() )
			$s .= Xml::option( wfMsg( 'revreview-lev-pristine' ), 2, $selected===2 );
		# Note: Pristine not tracked at sp:QualityOversight (counts as quality)
		$s .= Xml::closeElement('select')."\n";
		return $s;
	}

	/**
	 * Get a radio options of available precendents
	 * @param int $selected, selected level
	 * @returns string
	 */	
	public static function getPrecedenceMenu( $selected=null ) {
		wfLoadExtensionMessages( 'FlaggedRevs' );
		$s = Xml::openElement( 'select', array('name' => 'precedence','id' => 'wpPrecedence') );
		$s .= Xml::option( wfMsg( 'revreview-lev-sighted' ), FLAGGED_VIS_LATEST, $selected==FLAGGED_VIS_LATEST );
		if( FlaggedRevs::qualityVersions() )
			$s .= Xml::option( wfMsg( 'revreview-lev-quality' ), FLAGGED_VIS_QUALITY, $selected==FLAGGED_VIS_QUALITY );
		if( FlaggedRevs::pristineVersions() )
			$s .= Xml::option( wfMsg( 'revreview-lev-pristine' ), FLAGGED_VIS_PRISTINE, $selected==FLAGGED_VIS_PRISTINE );
		$s .= Xml::closeElement('select')."\n";
		return $s;
	}

	/**
	 * Get a selector of "approved"/"unapproved"
	 * @param int $selected, selected level
	 * @returns string
	 */
	public static function getStatusFilterMenu( $selected=null ) {
		wfLoadExtensionMessages( 'FlaggedRevs' );
		$s = "<label for='wpStatus'>" . wfMsgHtml('revreview-statusfilter') . "</label>&nbsp;";
		$s .= Xml::openElement( 'select', array('name' => 'status','id' => 'wpStatus') );
		$s .= Xml::option( wfMsg( "revreview-filter-all" ), -1, $selected===-1 );
		$s .= Xml::option( wfMsg( "revreview-filter-approved" ), 1, $selected===1 );
		$s .= Xml::option( wfMsg( "revreview-filter-reapproved" ), 2, $selected===2 );
		$s .= Xml::option( wfMsg( "revreview-filter-unapproved" ), 3, $selected===3 );
		$s .= Xml::closeElement('select')."\n";
		return $s;
	}

	/**
	 * Get a selector of "auto"/"manual"
	 * @param int $selected, selected level
	 * @returns string
	 */
	public static function getAutoFilterMenu( $selected=null ) {
		wfLoadExtensionMessages( 'FlaggedRevs' );
		$s = "<label for='wpApproved'>" . wfMsgHtml('revreview-typefilter') . "</label>&nbsp;";
		$s .= Xml::openElement( 'select', array('name' => 'automatic','id' => 'wpApproved') );
		$s .= Xml::option( wfMsg( "revreview-filter-all" ), -1, $selected===-1 );
		$s .= Xml::option( wfMsg( "revreview-filter-manual" ), 0, $selected===0 );
		$s .= Xml::option( wfMsg( "revreview-filter-auto" ), 1, $selected===1 );
		$s .= Xml::closeElement('select')."\n";
		return $s;
	}

	/**
	 * @param int $quality
	 * @returns string, css color for this quality
	 */
	public static function getQualityColor( $quality ) {
		if( $quality === false )
			return 'flaggedrevs-color-0';
		switch( $quality ) {
			case 2:
				$css = 'flaggedrevs-color-3';
				break;
			case 1:
				$css = 'flaggedrevs-color-2';
				break;
			case 0:
				$css = 'flaggedrevs-color-1';
				break;
		}
		return $css;
	}

	/**
	 * @param array $flags
	 * @param bool $prettybox
	 * @param string $css, class to wrap box in
	 * @returns string
	 * Generates a review box/tag
	 */
    public static function addTagRatings( $flags, $prettyBox = false, $css='' ) {
		wfLoadExtensionMessages( 'FlaggedRevs' );
        $tag = '';
        if( $prettyBox ) {
        	$tag .= "<table id='mw-revisionratings-box' align='center' class='$css' cellpadding='0'>";
		}
		foreach( FlaggedRevs::getDimensions() as $quality => $x ) {
			$level = isset( $flags[$quality] ) ? $flags[$quality] : 0;
			$encValueText = wfMsgHtml("revreview-$quality-$level");
            $level = $flags[$quality];
            $minlevel = FlaggedRevs::getMinQL( $quality );
            if( $level >= $minlevel ) {
                $classmarker = 2;
            } elseif( $level > 0 ) {
                $classmarker = 1;
            } else {
                $classmarker = 0;
			}
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
		if( $prettyBox ) {
			$tag .= '</table>';
		}
		return $tag;
    }

	/**
	 * @param FlaggedRevision $frev, the stable version
	 * @param string $html, the short message HTML
	 * @param int $revsSince, revisions since review
	 * @param bool $stable, are we referring to the stable revision?
	 * @param bool $synced, does stable=current and this is one of them?
	 * @param bool $old, is this an old stable version?
	 * @returns string
	 * Generates a review box using a table using FlaggedRevsXML::addTagRatings()
	 */
	public static function prettyRatingBox( $frev, $shtml, $revsSince, $stable=true, $synced=false, $old=false ) {
		global $wgLang;
		wfLoadExtensionMessages( 'FlaggedRevs' );
		# Get quality level
		$flags = $frev->getTags();
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Some checks for which tag CSS to use
		if( $pristine ) {
			$tagClass = 'flaggedrevs-box3';
			$color = 'flaggedrevs-color-3';
		} elseif( $quality ) {
			$tagClass = 'flaggedrevs-box2';
			$color = 'flaggedrevs-color-2';
		} else {
			$tagClass = 'flaggedrevs-box1';
			$color = 'flaggedrevs-color-1';
		}
        # Construct some tagging
		if( $synced ) {
			$msg = $quality ? 'revreview-quality-same' : 'revreview-basic-same';
			$html = wfMsgExt($msg, array('parseinline'), $frev->getRevId(), $time, $revsSince );
		} elseif( $old ) {
			$msg = $quality ? 'revreview-quality-old' : 'revreview-basic-old';
			$html = wfMsgExt($msg, array('parseinline'), $frev->getRevId(), $time );
		} else {
			if( $stable ) {
				$msg = $quality ? 'revreview-quality' : 'revreview-basic';
			} else {
				$msg = $quality ? 'revreview-newest-quality' : 'revreview-newest-basic';
			}
			# uses messages 'revreview-quality-i', 'revreview-basic-i', 'revreview-newest-quality-i', 'revreview-newest-basic-i'
			$msg .= ($revsSince == 0) ? '-i' : '';
			$html = wfMsgExt($msg, array('parseinline'), $frev->getRevId(), $time, $revsSince );
		}
		# Make fancy box...
		$box = "<table style='background: none; border-spacing: 0px;'>";
		$box .= "<tr style='white-space:nowrap;'><td>$shtml&nbsp;&nbsp;</td>";
		$box .= "<td style='text-align:right;'>" . self::ratingToggle() . "</td></tr>\n";
		$box .= "<tr><td id='mw-revisionratings'>$html<br />";
		# Add ratings if there are any...
		if( $stable && !empty($flags) ) {
			$box .= self::addTagRatings( $flags, true, $color );
		}
		$box .= "</td><td></td></tr></table>";
        return $box;
	}

	public static function ratingToggle() {
		wfLoadExtensionMessages( 'FlaggedRevs' );
		return "<a id='mw-revisiontoggle' class='flaggedrevs_toggle' style='display:none;'" .
			" onclick='toggleRevRatings()' title='" . wfMsgHtml('revreview-toggle-title') . "' >" .
			wfMsg( 'revreview-toggle' ) . "</a>";
	}
	
	/**
	 * @param array $flags, selected flags
	 * @param array $config, page config
	 * @param bool $disabled, form disabled
	 * @returns string
	 * Generates a main tag inputs (checkboxes/radios/selects) for review form
	 */
	public static function ratingInputs( $flags, $config, $disabled ) {
		$form = '';
		$toggle = $disabled ? array( 'disabled' => "disabled" ) : array();
		$size = count(FlaggedRevs::getDimensions(),1) - count(FlaggedRevs::getDimensions());
		# Loop through all different flag types
		foreach( FlaggedRevs::getDimensions() as $quality => $levels ) {
			$label = array(); // applicable tag levels
			$minLevel = 1; // first non-zero level number
			# Get current flag values or default if none
			$selected = ( isset($flags[$quality]) && $flags[$quality] > 0 ) ?
				$flags[$quality] : 1;
			# Disabled form? Set the selected item label
			if( $disabled ) {
				$label[$selected] = $levels[$selected];
			# Collect all quality levels of a flag current user can set
			} else {
				foreach( $levels as $i => $name ) {
					# Some levels may be restricted or not applicable...
					if( !RevisionReview::userCan($quality,$i,$config) ) {
						if( $selected == $i ) $selected++; // bump default
						continue; // skip this level
					} else if( $i > 0 ) {
						$minLevel = $i; // first non-zero level number
					}
					$label[$i] = $name;
				}
			}
			$numLevels = count( $label );
			$form .= Xml::openElement( 'span', array('class' => 'fr-rating-options') ) . "\n";
			$form .= "<b>" . Xml::tags( 'label', array( 'for' => "wp$quality" ),
				FlaggedRevs::getTagMsg( $quality ) ) . ":</b>\n";
			# If the sum of qualities of all flags is above 6, use drop down boxes
			# 6 is an arbitrary value choosen according to screen space and usability
			if( $size > 6 ) {
				$attribs = array( 'name' => "wp$quality", 'id' => "wp$quality", 'onchange' => "updateRatingForm()" ) + $toggle;
				$form .= Xml::openElement( 'select', $attribs );
				foreach( $label as $i => $name ) {
					$optionClass = array( 'class' => "fr-rating-option-$i" );
					$form .= Xml::option( FlaggedRevs::getTagMsg($name), $i, ($i == $selected), $optionClass )."\n";
				}
				$form .= Xml::closeElement('select')."\n";
			# If there are more than two levels, current user gets radio buttons
			} elseif( $numLevels > 2 ) {
				foreach( $label as $i => $name ) {
					$attribs = array( 'class' => "fr-rating-option-$i", 'onchange' => "updateRatingForm()" );
					$form .= Xml::radioLabel( FlaggedRevs::getTagMsg($name), "wp$quality", $i, "wp$quality".$i,
						($i == $selected), $attribs ) . "\n";
				}
			# Otherwise make checkboxes (two levels available for current user)
			} else {
				# If disable, use the current flags; if none, then use the min flag.
				$i = $disabled ? $selected : $minLevel;
				$attribs = array( 'class' => "fr-rating-option-$i", 'onchange' => "updateRatingForm()" );
				$attribs = $attribs + $toggle + array('value' => $minLevel);
				$form .= Xml::checkLabel( wfMsg( "revreview-{$label[$i]}" ), "wp$quality", "wp$quality",
					($selected == $i), $attribs ) . "\n";
			}
			$form .= Xml::closeElement( 'span' );
		}
		# If there were none, make one checkbox to approve/unapprove
		if( FlaggedRevs::dimensionsEmpty() ) {
			$form .= Xml::openElement( 'span', array('class' => 'fr-rating-options') ) . "\n";
			$form .= Xml::checkLabel( wfMsg( "revreview-approved" ), "wpApprove", "wpApprove", 1 ) . "\n";
			$form .= Xml::closeElement( 'span' );
		}
		return $form;
	}
}
