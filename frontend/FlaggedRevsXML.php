<?php

use MediaWiki\MediaWikiServices;

/**
 * Class containing utility XML functions for a FlaggedRevs.
 * Includes functions for selectors, icons, notices, CSS, and form aspects.
 */
class FlaggedRevsXML {

	/**
	 * Get a selector of reviewable namespaces
	 * @param int|null $selected namespace selected
	 * @param mixed|null $all Value of an item denoting all namespaces, or null to omit
	 * @return string
	 */
	public static function getNamespaceMenu( $selected = null, $all = null ) {
		$namespaces = FlaggedRevs::getReviewNamespaces();
		$s = "<label for='namespace'>" . wfMessage( 'namespace' )->escaped() . "</label>";
		# No namespace selected; let exact match work without hitting Main
		$selected ??= '';
		if ( $selected !== '' ) {
			# Let input be numeric strings without breaking the empty match.
			$selected = (int)$selected;
		}
		$s .= "\n<select id='namespace' name='namespace' class='namespaceselector'>\n";
		$arr = MediaWikiServices::getInstance()->getContentLanguage()->getFormattedNamespaces();
		if ( $all !== null ) {
			$arr = [ $all => wfMessage( 'namespacesall' )->text() ] + $arr; // should be first
		}
		foreach ( $arr as $index => $name ) {
			# Content pages only (except 'all')
			if ( $index !== $all && !in_array( $index, $namespaces ) ) {
				continue;
			}
			$name = $index !== 0 ? $name : wfMessage( 'blanknamespace' )->text();
			if ( $index === $selected ) {
				$s .= "\t" . Xml::element( "option", [ "value" => $index,
					"selected" => "selected" ], $name ) . "\n";
			} else {
				$s .= "\t" . Xml::element( "option", [ "value" => $index ], $name ) . "\n";
			}
		}
		$s .= "</select>\n";
		return $s;
	}

	/**
	 * Get a <select> of default page version (stable or draft). Used for filters.
	 * @param int|null $selected (0=draft, 1=stable, null=either )
	 * @return string
	 */
	public static function getDefaultFilterMenu( $selected = null ) {
		if ( $selected === null ) {
			$selected = ''; // "all"
		}
		$s = Xml::label( wfMessage( 'revreview-defaultfilter' )->text(), 'wpStable' ) . "\n";
		$s .= Xml::openElement( 'select',
			[ 'name' => 'stable', 'id' => 'wpStable' ] );
		$s .= Xml::option( wfMessage( 'revreview-def-all' )->text(), '', $selected == '' );
		$s .= Xml::option( wfMessage( 'revreview-def-stable' )->text(), '1', $selected === 1 );
		$s .= Xml::option( wfMessage( 'revreview-def-draft' )->text(), '0', $selected === 0 );
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * Get a <select> of options of 'autoreview' restriction levels. Used for filters.
	 * @param string $selected ('' for "any", 'none' for none)
	 * @return string
	 */
	public static function getRestrictionFilterMenu( $selected = '' ) {
		if ( $selected === null ) {
			$selected = ''; // "all"
		}
		$s = Xml::label( wfMessage( 'revreview-restrictfilter' )->text(), 'wpRestriction' ) . "\n";
		$s .= Xml::openElement( 'select',
			[ 'name' => 'restriction', 'id' => 'wpRestriction' ] );
		$s .= Xml::option( wfMessage( 'revreview-restriction-any' )->text(), '', $selected == '' );
		if ( !FlaggedRevs::useProtectionLevels() ) {
			# All "protected" pages have a protection level, not "none"
			$s .= Xml::option( wfMessage( 'revreview-restriction-none' )->text(),
				'none', $selected == 'none' );
		}
		foreach ( FlaggedRevs::getRestrictionLevels() as $perm ) {
			// Give grep a chance to find the usages:
			// revreview-restriction-any, revreview-restriction-none
			$key = "revreview-restriction-$perm";
			$msg = wfMessage( $key )->isDisabled() ? $perm : wfMessage( $key )->text();
			$s .= Xml::option( $msg, $perm, $selected == $perm );
		}
		$s .= Xml::closeElement( 'select' ) . "\n";
		return $s;
	}

	/**
	 * @param int|false $quality FR_CHECKED or false
	 * @return string css color for this quality
	 */
	public static function getQualityColor( $quality ) {
		return $quality === FR_CHECKED ? 'flaggedrevs-color-1' : 'flaggedrevs-color-0';
	}

	/**
	 * @param int[] $flags
	 * @return string
	 * Generates a review box/tag
	 */
	public static function addTagRatings( $flags ) {
		$tag = '';
		$tag .= Html::openElement(
			'table',
			[
				'id' => 'mw-fr-revisionratings-box',
				'style' => 'margin: auto;',
				'class' => 'flaggedrevs-color-1',
				'cellpadding' => '0',
			]
		);
		$quality = FlaggedRevs::getTagName();

		if ( !FlaggedRevs::useOnlyIfProtected() ) {
			// Give grep a chance to find the usages:
			// revreview-accuracy-0, revreview-accuracy-1, revreview-accuracy-2,
			// revreview-accuracy-3, revreview-accuracy-4
			$level = $flags[$quality] ?? 0;
			$encValueText = wfMessage( "revreview-$quality-$level" )->escaped();
			$level = $flags[$quality];

			$levelmarker = $level * 20 + 20;
			// Give grep a chance to find the usages:
			// revreview-accuracy
			$tag .= "<tr><td class='fr-text' style='vertical-align: middle;'>" .
				wfMessage( "revreview-$quality" )->escaped() .
				"</td><td class='fr-value$levelmarker' style='vertical-align: middle;'>" .
				$encValueText . "</td></tr>\n";
			$tag .= Html::closeElement( 'table' );
		}
		return $tag;
	}

	/**
	 * @param FlaggedRevision $frev the reviewed version
	 * @param string $shtml Short message HTML
	 * @param int $revsSince revisions since review
	 * @param string $type (stable/draft/oldstable)
	 * @param bool $synced does stable=current and this is one of them?
	 * @return string
	 * Generates a review box using a table using FlaggedRevsXML::addTagRatings()
	 */
	public static function prettyRatingBox(
		$frev, $shtml, $revsSince, $type = 'oldstable', $synced = false
	) {
		global $wgLang;
		$flags = $frev->getTags();
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Construct some tagging
		if ( $synced && ( $type == 'stable' || $type == 'draft' ) ) {
			$msg = 'revreview-basic-same';
			$html = wfMessage( $msg, $frev->getRevId(), $time )->numParams( $revsSince )->parse();
		} elseif ( $type == 'oldstable' ) {
			$msg = 'revreview-basic-old';
			$html = wfMessage( $msg, $frev->getRevId(), $time )->parse();
		} else {
			$msg = $type === 'stable' ? 'revreview-basic' : 'revreview-newest-basic';
			# For searching: uses messages 'revreview-basic-i', 'revreview-newest-basic-i'
			$msg .= !$revsSince ? '-i' : '';
			$html = wfMessage( $msg, $frev->getRevId(), $time )->numParams( $revsSince )->parse();
		}
		# Make fancy box...
		$box = '<div class="flaggedrevs_short_basic">';
		$box .= $shtml . self::ratingArrow();
		$box .= "</div>\n";
		// For rel-absolute child div (the fly-out)
		$box .= '<div id="mw-fr-revisiondetails-wrapper" style="position:relative;">';
		$box .= Xml::openElement(
			'div',
			[
				'id'    => 'mw-fr-revisiondetails',
				'class' => 'flaggedrevs_short_details',
				'style' => 'display:none'
			]
		);
		$box .= $html; // details text
		# Add any rating tags as needed...
		if ( $flags && !FlaggedRevs::binaryFlagging() ) {
			# Don't show the ratings on draft views
			if ( $type == 'stable' || $type == 'oldstable' ) {
				$box .= '<p>' . self::addTagRatings( $flags ) . '</p>';
			}
		}
		$box .= Xml::closeElement( 'div' ) . "\n";
		$box .= "</div>\n";
		return $box;
	}

	/**
	 * Generates JS toggle arrow icon
	 * @return string
	 */
	private static function ratingArrow() {
		return ( new OOUI\IndicatorWidget(
			[
				'indicator' => 'down',
				'classes' => [ 'fr-toggle-arrow' ],
				'id' => 'mw-fr-revisiontoggle',
				'title' => wfMessage( 'revreview-toggle-title' )->text(),
			]
		) )->toString();
	}

	/**
	 * Generates (show/hide) JS toggle HTML
	 * @param string|null $href If set, make the toggle link link to this URL and don't hide it
	 * @return string
	 */
	public static function diffToggle( $href = null ) {
		$toggle = '<a class="fr-toggle-text" ' .
			'title="' . wfMessage( 'revreview-diff-toggle-title' )->escaped() .
			( $href === null ? '' : '" href="' . htmlspecialchars( $href ) ) .
			'" >' .
			wfMessage( 'revreview-diff-toggle-show' )->escaped() . '</a>';
		return '<span id="mw-fr-difftoggle"' . ( $href === null ? ' style="display:none;"' : '' ) . '>' .
			wfMessage( 'parentheses' )->rawParams( $toggle )->escaped() . '</span>';
	}

	/**
	 * Generates (show/hide) JS toggle HTML
	 * @return string
	 */
	public static function logToggle() {
		$toggle = Html::rawElement(
			'a',
			[
				'class' => 'fr-toggle-text',
				'title' => wfMessage( 'revreview-log-toggle-title' )->text(),
			],
			wfMessage( 'revreview-log-toggle-hide' )->escaped()
		);
		return Html::rawElement(
			'span',
			[
				'id' => 'mw-fr-logtoggle',
				'class' => 'fr-logtoggle-excerpt',
				'style' => 'display:none;',
			],
			wfMessage( 'parentheses' )->rawParams( $toggle )->escaped()
		);
	}

	/**
	 * Generates (show/hide) JS toggle HTML
	 * @return string
	 */
	public static function logDetailsToggle() {
		$toggle = Html::rawElement(
			'a',
			[
				'class' => 'fr-toggle-text',
				'title' => wfMessage( 'revreview-log-details-title' )->text(),
			],
			wfMessage( 'revreview-log-details-show' )->escaped()
		);
		return Html::rawElement(
			'span',
			[
				'id' => 'mw-fr-logtoggle',
				'class' => 'fr-logtoggle-details',
				'style' => 'display:none;',
			],
			wfMessage( 'parentheses' )->rawParams( $toggle )->escaped()
		);
	}

	/**
	 * Creates CSS draft page icon
	 * @return string
	 */
	public static function draftStatusIcon() {
		$encTitle = wfMessage( 'revreview-draft-title' )->text();
		return ( new OOUI\IconWidget(
			[
				'icon' => 'block',
				'classes' => [ 'flaggedrevs-icon' ],
				'title' => $encTitle,
			]
		) )->toString();
	}

	/**
	 * Creates CSS stable page icon
	 * @return string
	 */
	public static function stableStatusIcon() {
		$encTitle = wfMessage( 'revreview-basic-title' )->text();
		return ( new OOUI\IconWidget(
			[
				'icon' => 'eye',
				'classes' => [ 'flaggedrevs-icon' ],
				'title' => $encTitle,
			]
		) )->toString();
	}

	/**
	 * Creates CSS lock icon if page is locked/unlocked
	 * @param FlaggableWikiPage $flaggedArticle
	 * @return string
	 */
	public static function lockStatusIcon( $flaggedArticle ) {
		if ( $flaggedArticle->isPageLocked() ) {
			$encTitle = wfMessage( 'revreview-locked-title' )->text();
			$icon = 'articleSearch';
		} elseif ( $flaggedArticle->isPageUnlocked() ) {
			$encTitle = wfMessage( 'revreview-unlocked-title' )->text();
			$icon = 'articleCheck';
		} else {
			return '';
		}
		return ( new OOUI\IconWidget(
			[
				'icon' => $icon,
				'classes' => [ 'flaggedrevs-icon' ],
				'title' => $encTitle,
			]
		) )->toString();
	}

	/**
	 * @param FlaggedRevision $frev
	 * @param int $revsSince
	 * @return string
	 * Creates "stable rev reviewed on"/"x pending edits" message
	 */
	public static function pendingEditNotice( $frev, $revsSince ) {
		$msg = self::pendingEditNoticeMessage( $frev, $revsSince );
		return $msg->parse();
	}

	/**
	 * Same as pendingEditNotice(), but returns a Message object.
	 * @param FlaggedRevision $frev
	 * @param int $revsSince
	 * @return Message
	 */
	public static function pendingEditNoticeMessage( $frev, $revsSince ) {
		global $wgLang;
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Add message text for pending edits
		return wfMessage( 'revreview-pending-basic', $frev->getRevId(), $time )->numParams( $revsSince );
	}

	/**
	 * @param WikiPage|Article $article
	 * @return string
	 * Creates a stability log excerpt
	 */
	public static function stabilityLogExcerpt( $article ) {
		$logHtml = '';
		$params = [
			'lim'   => 1,
			'flags' => LogEventsList::NO_EXTRA_USER_LINKS
		];
		LogEventsList::showLogExtract( $logHtml, 'stable',
			$article->getTitle()->getPrefixedText(), '', $params );
		return Html::rawElement(
			'div',
			[ 'id' => 'mw-fr-logexcerpt' ],
			$logHtml
		);
	}

}
