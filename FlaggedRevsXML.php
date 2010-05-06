<?php
/**
 * Class containing utility XML functions for a FlaggedRevs.
 * Includes functions for selectors, icons, notices, CSS, and form aspects.
 */
class FlaggedRevsXML {
	/**
	 * Get a selector of reviewable namespaces
	 * @param int $selected, namespace selected
	 * @param $all Mixed: Value of an item denoting all namespaces, or null to omit
	 * @returns string
	 */
	public static function getNamespaceMenu( $selected = null, $all = null ) {
		global $wgContLang;
		$namespaces = FlaggedRevs::getReviewNamespaces();
		$s = "<label for='namespace'>" . wfMsgHtml( 'namespace' ) . "</label>";
		if ( $selected !== '' ) {
			if ( is_null( $selected ) ) {
				# No namespace selected; let exact match work without hitting Main
				$selected = '';
			} else {
				# Let input be numeric strings without breaking the empty match.
				$selected = intval( $selected );
			}
		}
		$s .= "\n<select id='namespace' name='namespace' class='namespaceselector'>\n";
		$arr = $wgContLang->getFormattedNamespaces();
		if ( !is_null( $all ) ) {
			$arr = array( $all => wfMsg( 'namespacesall' ) ) + $arr; // should be first
		}
		foreach ( $arr as $index => $name ) {
			# Content pages only (except 'all')
			if ( $index !== $all && !in_array( $index, $namespaces ) ) {
				continue;
			}
			$name = $index !== 0 ? $name : wfMsg( 'blanknamespace' );
			if ( $index === $selected ) {
				$s .= "\t" . Xml::element( "option", array( "value" => $index,
					"selected" => "selected" ), $name ) . "\n";
			} else {
				$s .= "\t" . Xml::element( "option", array( "value" => $index ), $name ) . "\n";
			}
		}
		$s .= "</select>\n";
		return $s;
	}

	/**
	 * Get a selector of review levels. Used for filters.
	 * @param int $selected, selected level
	 * @param string $all, all selector msg?
	 * @param int $max max level?
	 * @returns string
	 */
	public static function getLevelMenu(
		$selected = null, $all = 'revreview-filter-all', $max = 2
	) {
		$s = "<label for='wpLevel'>" . wfMsgHtml( 'revreview-levelfilter' ) . "</label>\n";
		$s .= Xml::openElement( 'select', array( 'name' => 'level', 'id' => 'wpLevel' ) );
		if ( $all !== false )
			$s .= Xml::option( wfMsg( $all ), - 1, $selected === - 1 );
		$s .= Xml::option( wfMsg( 'revreview-lev-basic' ), 0, $selected === 0 );
		if ( FlaggedRevs::qualityVersions() )
			$s .= Xml::option( wfMsg( 'revreview-lev-quality' ), 1, $selected === 1 );
		if ( $max >= 2 && FlaggedRevs::pristineVersions() )
			$s .= Xml::option( wfMsg( 'revreview-lev-pristine' ), 2, $selected === 2 );
		# Note: Pristine not tracked at sp:QualityOversight (counts as quality)
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * Get a <select> of options of available precendents. Used for filters.
	 * @param int $selected selected level, null for "all"
	 * @returns string
	 */
	public static function getPrecedenceFilterMenu( $selected = null ) {
		if ( is_null( $selected ) ) {
			$selected = ''; // "all"
		}
		$s = Xml::label( wfMsg( 'revreview-precedencefilter' ), 'wpPrecedence' ) . "\n";
		$s .= Xml::openElement( 'select',
			array( 'name' => 'precedence', 'id' => 'wpPrecedence' ) );
		$s .= Xml::option( wfMsg( 'revreview-lev-all' ), '', $selected === '' );
		$s .= Xml::option( wfMsg( 'revreview-lev-basic' ), FLAGGED_VIS_LATEST,
			$selected === FLAGGED_VIS_LATEST );
		if ( FlaggedRevs::qualityVersions() ) {
			$s .= Xml::option( wfMsg( 'revreview-lev-quality' ), FLAGGED_VIS_QUALITY,
				$selected === FLAGGED_VIS_QUALITY );
		}
		if ( FlaggedRevs::pristineVersions() ) {
			$s .= Xml::option( wfMsg( 'revreview-lev-pristine' ), FLAGGED_VIS_PRISTINE,
				$selected === FLAGGED_VIS_PRISTINE );
		}
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * Get a <select> of default page version (stable or draft). Used for filters.
	 * @param int $selected (0=draft, 1=stable, null=either )
	 * @returns string
	 */
	public static function getDefaultFilterMenu( $selected = null ) {
		if ( is_null( $selected ) ) {
			$selected = ''; // "all"
		}
		$s = Xml::label( wfMsg( 'revreview-defaultfilter' ), 'wpStable' ) . "\n";
		$s .= Xml::openElement( 'select',
			array( 'name' => 'stable', 'id' => 'wpStable' ) );
		$s .= Xml::option( wfMsg( 'revreview-def-all' ), '', $selected == '' );
		$s .= Xml::option( wfMsg( 'revreview-def-stable' ), 1, $selected === 1 );
		$s .= Xml::option( wfMsg( 'revreview-def-draft' ), 0, $selected === 0 );
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * Get a <select> of options of 'autoreview' restriction levels. Used for filters.
	 * @param string $selected ('' for "any", 'none' for none)
	 * @returns string
	 */
	public static function getRestrictionFilterMenu( $selected = '' ) {
		if ( is_null( $selected ) ) {
			$selected = ''; // "all"
		}
		$s = Xml::label( wfMsg( 'revreview-restrictfilter' ), 'wpRestriction' ) . "\n";
		$s .= Xml::openElement( 'select',
			array( 'name' => 'restriction', 'id' => 'wpRestriction' ) );
		$s .= Xml::option( wfMsg( 'revreview-restriction-any' ), '', $selected == '' );
		if ( !FlaggedRevs::useProtectionLevels() ) {
			# All "protected" pages have a protection level, not "none"
			$s .= Xml::option( wfMsg( 'revreview-restriction-none' ),
				'none', $selected == 'none' );
		}
		foreach ( FlaggedRevs::getRestrictionLevels() as $perm ) {
			$key = "revreview-restriction-{$perm}";
			$msg = wfMsg( $key );
			if ( wfEmptyMsg( $key, $msg ) ) {
				$msg = $perm; // fallback to user right key
			}
			$s .= Xml::option( $msg, $perm, $selected == $perm );
		}
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * Get a selector of "approved"/"unapproved". Used for filters.
	 * @param int $selected, selected level
	 * @returns string
	 */
	public static function getStatusFilterMenu( $selected = null ) {
		$s = "<label for='wpStatus'>" . wfMsgHtml( 'revreview-statusfilter' ) . "</label>\n";
		$s .= Xml::openElement( 'select', array( 'name' => 'status', 'id' => 'wpStatus' ) );
		$s .= Xml::option( wfMsg( "revreview-filter-all" ), - 1, $selected === - 1 );
		$s .= Xml::option( wfMsg( "revreview-filter-approved" ), 1, $selected === 1 );
		$s .= Xml::option( wfMsg( "revreview-filter-reapproved" ), 2, $selected === 2 );
		$s .= Xml::option( wfMsg( "revreview-filter-unapproved" ), 3, $selected === 3 );
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * Get a selector of "auto"/"manual". Used for filters.
	 * @param int $selected, selected level
	 * @returns string
	 */
	public static function getAutoFilterMenu( $selected = null ) {
		$s = "<label for='wpApproved'>" . wfMsgHtml( 'revreview-typefilter' ) . "</label>\n";
		$s .= Xml::openElement( 'select', array( 'name' => 'automatic', 'id' => 'wpApproved' ) );
		$s .= Xml::option( wfMsg( "revreview-filter-all" ), - 1, $selected === - 1 );
		$s .= Xml::option( wfMsg( "revreview-filter-manual" ), 0, $selected === 0 );
		$s .= Xml::option( wfMsg( "revreview-filter-auto" ), 1, $selected === 1 );
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * @param int $quality
	 * @returns string, css color for this quality
	 */
	public static function getQualityColor( $quality ) {
		if ( $quality === false )
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
    public static function addTagRatings( $flags, $prettyBox = false, $css = '' ) {
        $tag = '';
        if ( $prettyBox ) {
        	$tag .= "<table id='mw-fr-revisionratings-box' align='center' class='$css' cellpadding='0'>";
		}
		foreach ( FlaggedRevs::getDimensions() as $quality => $x ) {
			$level = isset( $flags[$quality] ) ? $flags[$quality] : 0;
			$encValueText = wfMsgHtml( "revreview-$quality-$level" );
            $level = $flags[$quality];
            $minlevel = FlaggedRevs::getMinQL( $quality );
            if ( $level >= $minlevel ) {
                $classmarker = 2;
            } elseif ( $level > 0 ) {
                $classmarker = 1;
            } else {
                $classmarker = 0;
			}
            $levelmarker = $level * 20 + 20;
            if ( $prettyBox ) {
            	$tag .= "<tr><td class='fr-text' valign='middle'>" .
					wfMsgHtml( "revreview-$quality" ) .
					"</td><td class='fr-value$levelmarker' valign='middle'>" .
					$encValueText . "</td></tr>\n";
            } else {
				$tag .= "&nbsp;<span class='fr-marker-$levelmarker'><strong>" .
					wfMsgHtml( "revreview-$quality" ) .
					"</strong>: <span class='fr-text-value'>$encValueText&nbsp;</span>&nbsp;" .
					"</span>\n";
			}
		}
		if ( $prettyBox ) {
			$tag .= '</table>';
		}
		return $tag;
    }

	/**
	 * @param FlaggedRevision $frev, the reviewed version
	 * @param string $html, the short message HTML
	 * @param int $revsSince, revisions since review
	 * @param string $type (stable/draft/oldstable)
	 * @param bool $stable, are we referring to the stable revision?
	 * @param bool $synced, does stable=current and this is one of them?
	 * @returns string
	 * Generates a review box using a table using FlaggedRevsXML::addTagRatings()
	 */
	public static function prettyRatingBox(
		$frev, $shtml, $revsSince, $type = 'oldstable', $synced = false
	) {
		global $wgLang;
		# Get quality level
		$flags = $frev->getTags();
		$quality = FlaggedRevs::isQuality( $flags );
		$pristine = FlaggedRevs::isPristine( $flags );
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Some checks for which tag CSS to use
		if ( $pristine ) {
			$color = 'flaggedrevs-color-3';
		} elseif ( $quality ) {
			$color = 'flaggedrevs-color-2';
		} else {
			$color = 'flaggedrevs-color-1';
		}
        # Construct some tagging
		if ( $synced && ( $type == 'stable' || $type == 'draft' ) ) {
			$msg = $quality ?
				'revreview-quality-same' : 'revreview-basic-same';
			$html = wfMsgExt( $msg, array( 'parseinline' ),
				$frev->getRevId(), $time, $revsSince );
		} elseif ( $type == 'oldstable' ) {
			$msg = $quality ?
				'revreview-quality-old' : 'revreview-basic-old';
			$html = wfMsgExt( $msg, array( 'parseinline' ), $frev->getRevId(), $time );
		} else {
			if ( $type == 'stable' ) {
				$msg = $quality ?
					'revreview-quality' : 'revreview-basic';
			} else { // draft
				$msg = $quality ?
					'revreview-newest-quality' : 'revreview-newest-basic';
			}
			# For searching: uses messages 'revreview-quality-i', 'revreview-basic-i',
			# 'revreview-newest-quality-i', 'revreview-newest-basic-i'
			$msg .= ( $revsSince == 0 ) ? '-i' : '';
			$html = wfMsgExt( $msg, array( 'parseinline' ),
				$frev->getRevId(), $time, $revsSince );
		}
		# Make fancy box...
		$box = '<div class="flaggedrevs_short_basic">' . $shtml .
			'&nbsp;' . self::ratingArrow() . "</div>\n";
		$box .= '<div style="position: relative;">'; // for rel-absolute child div
		$box .= '<div id="mw-fr-revisionratings" class="flaggedrevs_short_details">';
		$box .= $html; // details text
		# Add any rating tags as needed...
		if ( $flags && !FlaggedRevs::binaryFlagging() ) {
			# Don't show the ratings on draft views
			if ( $type == 'stable' || $type == 'oldstable' ) {
				$box .= '<p>' . self::addTagRatings( $flags, true, $color ) . '</p>';
			}
		}
		$box .= "</div></div>\n";
        return $box;
	}

	/**
	 * Generates JS toggle arrow icon
	 * @returns string
	 */
	public static function ratingArrow() {
		$encPath = htmlspecialchars( FlaggedRevs::styleUrlPath() . '/img' );
		return "<img id=\"mw-fr-revisiontoggle\" class=\"fr-toggle-arrow\"" .
			" src=\"{$encPath}/arrow-up.png\" style=\"display:none;\" " .
			" onclick=\"FlaggedRevs.toggleRevRatings()\" title=\"" .
			wfMsgHtml( 'revreview-toggle-title' ) . "\" alt=\"" .
			wfMsgHtml( 'revreview-toggle-show' ) . "\" />";
	}

	/**
	 * Generates (+/-) JS toggle HTML (monospace to keep things in place)
	 * @returns string
	 */
	public static function ratingToggle() {
		return '<a id="mw-fr-revisiontoggle" class="fr-toggle-symbol"' .
			' style="display:none;" onclick="FlaggedRevs.toggleRevRatings()" title="' .
			wfMsgHtml( 'revreview-toggle-title' ) . '" >' .
			wfMsgHtml( 'revreview-toggle-show' ) . '</a>';
	}

	/**
	 * Generates (show/hide) JS toggle HTML
	 * @returns string
	 */
	public static function diffToggle() {
		$toggle = '<a id="mw-fr-difftoggle" class="fr-toggle-text" style="display:none;"' .
			' onclick="FlaggedRevs.toggleDiff()" title="' .
			wfMsgHtml( 'revreview-diff-toggle-title' ) . '" >' .
			wfMsgHtml( 'revreview-diff-toggle-show' ) . '</a>';
		return wfMsgHtml( 'parentheses', $toggle );
	}

	/**
	 * Generates (show/hide) JS toggle HTML
	 * @returns string
	 */
	public static function logToggle() {
		$toggle = '<a id="mw-fr-logtoggle" class="fr-toggle-text" style="display:none;"' .
			' onclick="FlaggedRevs.toggleLog()" title="' .
			wfMsgHtml( 'revreview-log-toggle-show' ) . '" >' .
			wfMsgHtml( 'revreview-log-toggle-show' ) . '</a>';
		return wfMsgHtml( 'parentheses', $toggle );
	}

	/**
	 * Generates (show/hide) JS toggle HTML
	 * @returns string
	 */
	public static function logDetailsToggle() {
		$toggle = '<a id="mw-fr-logtoggle" class="fr-toggle-text" style="display:none;"' .
			' onclick="FlaggedRevs.toggleLogDetails()" title="' .
			wfMsgHtml( 'revreview-log-details-show' ) . '" >' .
			wfMsgHtml( 'revreview-log-details-show' ) . '</a>';
		return wfMsgHtml( 'parentheses', $toggle );
	}

	/**
	 * @param array $flags, selected flags
	 * @param array $config, page config
	 * @param bool $disabled, form disabled
	 * @param bool $reviewed, rev already reviewed
	 * @returns string
	 * Generates a main tag inputs (checkboxes/radios/selects) for review form
	 */
	public static function ratingInputs( $flags, $config, $disabled, $reviewed ) {
		$form = '';
		# Get all available tags for this page/user
		list( $labels, $minLevels ) = self::ratingFormTags( $flags, $config );
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
		$form .= implode( '&nbsp;&nbsp;&nbsp;', $items );
		$form .= Xml::closeElement( 'span' ) . "\n";
		return $form;
	}
	
	protected static function ratingFormTags( $selected, $config ) {
		$labels = array();
		$minLevels = array();
		# Build up all levels available to user
		foreach ( FlaggedRevs::getDimensions() as $tag => $levels ) {
			if ( isset( $selected[$tag] ) &&
				!RevisionReview::userCan( $tag, $selected[$tag], $config ) )
			{
				return array( false, false ); // form will have to be disabled
			}
			$labels[$tag] = array(); // applicable tag levels
			$minLevels[$tag] = false; // first non-zero level number
			foreach ( $levels as $i => $msg ) {
				# Some levels may be restricted or not applicable...
				if ( !RevisionReview::userCan( $tag, $i, $config ) ) {
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
	public static function ratingSubmitButtons( $frev, $disabled, $rereview = false ) {
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

	/*
	* Creates CSS draft page icon
	* @returns string
	*/
	public static function draftStatusIcon() {
		$encPath = htmlspecialchars( FlaggedRevs::styleUrlPath() . '/img' );
		$encTitle = wfMsgHtml( 'revreview-draft-title' );
		return "<img class=\"flaggedrevs-icon\" src=\"$encPath/1.png\"" .
			" width=\"16px\" alt=\"$encTitle\" title=\"$encTitle\" />";
	}
	
	/*
	* Creates CSS stable page icon
	* @param bool $isQuality
	* @returns string
	*/
	public static function stableStatusIcon( $isQuality ) {
		$encPath = htmlspecialchars( FlaggedRevs::styleUrlPath() . '/img' );
		$file = $isQuality ? '3.png' : '2.png';
		$encTitle = $isQuality
			? wfMsgHtml( 'revreview-quality-title' )
			: wfMsgHtml( 'revreview-basic-title' );
		return "<img class=\"flaggedrevs-icon\" src=\"$encPath/$file\"" .
			" width=\"16px\" alt=\"$encTitle\" title=\"$encTitle\" />";
	}

	/*
	* Creates CSS lock icon if page is locked/unlocked
	* @param FlaggedArticle $flaggedArticle
	* @returns string
	*/
	public static function lockStatusIcon( $flaggedArticle ) {
		$encPath = htmlspecialchars( FlaggedRevs::styleUrlPath() . '/img' );
		if ( $flaggedArticle->isPageLocked() ) {
			$encTitle = wfMsgHtml( 'revreview-locked-title' );
			return "<img class=\"flaggedrevs-icon\" src=\"$encPath/lock-closed.png\"" .
				" width=\"16px\" alt=\"$encTitle\" title=\"$encTitle\" />";
		} elseif ( $flaggedArticle->isPageUnlocked() ) {
			$encTitle = wfMsgHtml( 'revreview-unlocked-title' );
			return "<img class=\"flaggedrevs-icon\" src=\"$encPath/lock-open.png\"" .
				" width=\"16px\" alt=\"$encTitle\" title=\"$encTitle\" />";
		}
	}

	/*
	* @param FlaggedArticle $flaggedArticle
	* @param FlaggedRevision $frev
	* @param int $revsSince
	* @returns string
	* Creates "stable rev reviewed on"/"x pending edits" message
	*/
	public static function pendingEditNotice( $flaggedArticle, $frev, $revsSince ) {
		global $wgLang;
		$flags = $frev->getTags();
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Add message text for pending edits
		$msg = FlaggedRevs::isQuality( $flags )
			? 'revreview-pending-quality'
			: 'revreview-pending-basic';
		$tag = wfMsgExt( $msg, array( 'parseinline' ), $frev->getRevId(), $time, $revsSince );
		return $tag;
	}

	/*
	* @param Article $article
	* @returns string
	* Creates a stability log excerpt
	*/
	public static function stabilityLogExcerpt( $article ) {
		$logHtml = '';
		$params = array(
			'lim'   => 1,
			'flags' => LogEventsList::NO_EXTRA_USER_LINKS
		);
		LogEventsList::showLogExtract( $logHtml, 'stable',
			$article->getTitle()->getPrefixedText(), '', $params );
		return "<div id=\"mw-fr-logexcerpt\">$logHtml</div>";
	}
	

	 /**
	 * Generates a brief review form for a page.
	 * @param FlaggedArticle $article
	 * @param Revision $rev
	 * @param array $templateIDs
	 * @param array $imageSHA1Keys
	 * @param bool $stableDiff this is a diff-to-stable 
	 * @return mixed (string/false)
	 */
	public static function buildQuickReview(
		$article, $rev, $templateIDs, $imageSHA1Keys, $stableDiff = false
	) {
		global $wgUser, $wgRequest;
		# The revision must be valid and public
		if ( !$rev || $rev->isDeleted( Revision::DELETED_TEXT ) ) {
			return false;
		}
		$id = $rev->getId();
		$skin = $wgUser->getSkin();
		# Do we need to get inclusion IDs from parser output?
		$getPOut = ( $templateIDs && $imageSHA1Keys );

		$config = $article->getVisibilitySettings();
		# Variable for sites with no flags, otherwise discarded
		$approve = $wgRequest->getBool( 'wpApprove' );
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
			if ( !RevisionReview::userCanSetFlags( $oldFlags ) ) {
				$flags = $oldFlags;
			}
			$reviewNotes = $srev->getComment();
			# Re-review button is need for template/file only review case
			$allowRereview = ( $srev->getRevId() == $id )
				&& !FlaggedRevs::stableVersionIsSynced( $srev, $article );
		} else {
			$flags = $oldFlags;
			// Get existing notes to pre-fill field
			$reviewNotes = $frev ? $frev->getComment() : "";
			$allowRereview = false; // re-review button
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

		# Disable form for unprivileged users
		$uneditable = !$article->getTitle()->quickUserCan( 'edit' );
		$disabled = !RevisionReview::userCanSetFlags( $flags ) || $uneditable;
		if ( $disabled ) {
			$form .= Xml::openElement( 'div', array( 'class' => 'fr-rating-controls-disabled',
				'id' => 'fr-rating-controls-disabled' ) );
			$toggle = array( 'disabled' => "disabled" );
		} else {
			$form .= Xml::openElement( 'div', array( 'class' => 'fr-rating-controls',
				'id' => 'fr-rating-controls' ) );
			$toggle = array();
		}

		# Add main checkboxes/selects
		$form .= Xml::openElement( 'span', array( 'id' => 'mw-fr-ratingselects' ) );
		$form .= FlaggedRevsXML::ratingInputs( $flags, $config, $disabled, (bool)$frev );
		$form .= Xml::closeElement( 'span' );
		# Add review notes input
		if ( FlaggedRevs::allowComments() && $wgUser->isAllowed( 'validate' ) ) {
			$form .= "<div id='mw-fr-notebox'>\n";
			$form .= "<p>" . wfMsgHtml( 'revreview-notes' ) . "</p>\n";
			$form .= Xml::openElement( 'textarea',
				array( 'name' => 'wpNotes', 'id' => 'wpNotes',
					'class' => 'fr-notes-box', 'rows' => '2', 'cols' => '80' ) ) .
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
				$pOutput = $parserCache->get( $article, $wgUser );
			}
			# Otherwise (or on cache miss), parse the rev text...
			if ( $pOutput == false ) {
				global $wgParser, $wgEnableParserCache;
				$text = $rev->getText();
				$title = $article->getTitle();
				$options = FlaggedRevs::makeParserOptions();
				$pOutput = $wgParser->parse( $text, $title, $options );
				# Might as well save the cache while we're at it
				if ( $rev->isCurrent() && $wgEnableParserCache ) {
					$parserCache->save( $pOutput, $article, $wgUser );
				}
			}
			$templateIDs = $pOutput->mTemplateIds;
			$imageSHA1Keys = $pOutput->fr_ImageSHA1Keys;
		}
		list( $templateParams, $imageParams, $fileVersion ) =
			FlaggedRevs::getIncludeParams( $article, $templateIDs, $imageSHA1Keys );

		$form .= Xml::openElement( 'span', array( 'style' => 'white-space: nowrap;' ) );
		# Hide comment input if needed
		if ( !$disabled ) {
			if ( count( FlaggedRevs::getDimensions() ) > 1 )
				$form .= "<br />"; // Don't put too much on one line
			$form .= "<span id='mw-fr-commentbox' style='clear:both'>" .
				Xml::inputLabel( wfMsg( 'revreview-log' ), 'wpReason', 'wpReason', 35, '',
					array( 'class' => 'fr-comment-box' ) ) . "&nbsp;&nbsp;&nbsp;</span>";
		}
		# Add the submit buttons
		$form .= FlaggedRevsXML::ratingSubmitButtons( $frev, (bool)$toggle, $allowRereview );
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
		$form .= Xml::hidden( 'wpEditToken', $wgUser->editToken() ) . "\n";
		# Add review parameters
		$form .= Xml::hidden( 'templateParams', $templateParams ) . "\n";
		$form .= Xml::hidden( 'imageParams', $imageParams ) . "\n";
		$form .= Xml::hidden( 'fileVersion', $fileVersion ) . "\n";
		# Pass this in if given; useful for new page patrol
		$form .= Xml::hidden( 'rcid', $wgRequest->getVal( 'rcid' ) ) . "\n";
		# Special token to discourage fiddling...
		$checkCode = RevisionReview::validationKey(
			$templateParams, $imageParams, $fileVersion, $id
		);
		$form .= Xml::hidden( 'validatedParams', $checkCode ) . "\n";

		$form .= Xml::closeElement( 'fieldset' );
		$form .= Xml::closeElement( 'form' );
		return $form;
	}
}
