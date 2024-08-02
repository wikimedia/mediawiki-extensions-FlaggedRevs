<?php

use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;

/**
 * Class containing utility XML functions for a FlaggedRevs.
 * Includes functions for selectors, icons, notices, CSS, and form aspects.
 */
class FlaggedRevsXML {

	/**
	 * Get a selector of reviewable namespaces
	 * @param int|null $selected namespace selected
	 * @param string|null $all Value of an item denoting all namespaces, or null to omit
	 */
	public static function getNamespaceMenu( ?int $selected = null, ?string $all = null ): string {
		$s = Html::rawElement( 'div', [ 'class' => 'cdx-field__item' ],
			Html::rawElement( 'div', [ 'class' => 'cdx-label' ],
				Html::label(
					wfMessage( 'namespace' )->text(),
					'namespace',
					[ 'class' => 'cdx-label__label' ]
				)
			)
		);

		# No namespace selected; let exact match work without hitting Main
		$selected ??= '';
		$s .= "\n<select id='namespace' name='namespace' class='cdx-select namespaceselector'>\n";
		$arr = MediaWikiServices::getInstance()->getContentLanguage()->getFormattedNamespaces();
		if ( $all !== null ) {
			$arr = [ $all => wfMessage( 'namespacesall' )->text() ] + $arr; // should be first
		}
		foreach ( $arr as $index => $name ) {
			# Content pages only (except 'all')
			if ( $index !== $all && !FlaggedRevs::isReviewNamespace( $index ) ) {
				continue;
			}
			$name = $index !== 0 ? $name : wfMessage( 'blanknamespace' )->text();
			if ( $index === $selected ) {
				$s .= "\t" . Html::element( 'option', [ 'value' => $index,
						"selected" => "selected" ], $name ) . "\n";
			} else {
				$s .= "\t" . Html::element( 'option', [ 'value' => $index ], $name ) . "\n";
			}
		}
		$s .= "</select>\n";
		return $s;
	}

	/**
	 * Get a <select> of default page version (stable or draft). Used for filters.
	 * @param int|null $selected (0=draft, 1=stable, null=either )
	 */
	public static function getDefaultFilterMenu( ?int $selected = null ): string {
		$s = Html::rawElement( 'div', [ 'class' => 'cdx-field__item' ],
			Html::rawElement( 'div', [ 'class' => 'cdx-label' ],
				Html::label(
					wfMessage( 'revreview-defaultfilter' )->text(),
					'wpStable',
					[ 'class' => 'cdx-label__label' ]
				)
			)
		);

		$selectOptions = Html::element( 'option', [ 'value' => '', 'selected' => $selected === null ],
			wfMessage( 'revreview-def-all' )->text() );
		$selectOptions .= Html::element( 'option', [ 'value' => '1', 'selected' => $selected === 1 ],
			wfMessage( 'revreview-def-stable' )->text() );
		$selectOptions .= Html::element( 'option', [ 'value' => '0', 'selected' => $selected === 0 ],
			wfMessage( 'revreview-def-draft' )->text() );

		$s .= Html::rawElement( 'select', [
			'name' => 'stable',
			'id' => 'wpStable',
			'class' => 'cdx-select filterselector'
		], $selectOptions );

		return $s;
	}

	/**
	 * Get a <select> of options of 'autoreview' restriction levels. Used for filters.
	 * @param string|null $selected (null or empty string for "any", 'none' for none)
	 */
	public static function getRestrictionFilterMenu( ?string $selected = '' ): string {
		$s = Html::rawElement( 'div', [ 'class' => 'cdx-field__item' ],
			Html::rawElement( 'div', [ 'class' => 'cdx-label' ],
				Html::label(
					wfMessage( 'revreview-restrictfilter' )->text(),
					'wpRestriction',
					[ 'class' => 'cdx-label__label' ]
				)
			)
		);

		$selectOptions = Html::element( 'option',
			[ 'value' => '', 'selected' => ( $selected ?? '' ) === '' ],
			wfMessage( 'revreview-restriction-any' )->text()
		);

		if ( !FlaggedRevs::useProtectionLevels() ) {
			# All "protected" pages have a protection level, not "none"
			$selectOptions .= Html::element( 'option',
				[ 'value' => 'none', 'selected' => $selected === 'none' ],
				wfMessage( 'revreview-restriction-none' )->text()
			);
		}

		foreach ( FlaggedRevs::getRestrictionLevels() as $perm ) {
			// Give grep a chance to find the usages:
			// revreview-restriction-any, revreview-restriction-none
			$key = "revreview-restriction-$perm";
			$msg = wfMessage( $key )->isDisabled() ? $perm : wfMessage( $key )->text();
			$selectOptions .= Html::element( 'option',
				[ 'value' => $perm, 'selected' => $selected == $perm ],
				$msg
			);
		}

		$s .= Html::rawElement( 'select', [
			'name' => 'restriction',
			'id' => 'wpRestriction',
			'class' => 'cdx-select restrictionselector'
		], $selectOptions );

		return $s;
	}

	/**
	 * Generates a review box/tag
	 * @param array<string,int> $flags
	 */
	public static function addTagRatings( array $flags ): string {
		$tag = Html::openElement(
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
	 * Generates a dropdown menu for edit tag filters
	 *
	 * @param string|null $selected (null or empty string for "any")
	 * @since 1.43
	 */
	public static function getEditTagFilterMenu( ?string $selected = '' ): string {
		$s = Html::rawElement( 'div', [ 'class' => 'cdx-field__item' ],
			Html::rawElement( 'div', [ 'class' => 'cdx-label' ],
				Html::label(
					wfMessage( 'pendingchanges-edit-tag' )->text(),
					'wpTagFilter',
					[ 'class' => 'cdx-label__label' ]
				)
			)
		);

		$selectOptions = Html::element( 'option',
			[ 'value' => '', 'selected' => ( $selected ?? '' ) === '' ],
			wfMessage( 'pendingchanges-edit-tag-any' )->text()
		);

		$tagDefs = ChangeTags::getChangeTagList( RequestContext::getMain(), RequestContext::getMain()->getLanguage() );
		foreach ( $tagDefs as $tagInfo ) {
			$tagName = $tagInfo['name'];
			$selectOptions .= Html::element( 'option',
				[ 'value' => $tagName, 'selected' => $selected == $tagName ],
				$tagName
			);
		}

		$s .= Html::rawElement( 'select', [
			'name' => 'tagFilter',
			'id' => 'wpTagFilter',
			'class' => 'cdx-select'
		], $selectOptions );

		return $s;
	}

	/**
	 * Get a selector for limit options
	 *
	 * @param int $selected The currently selected limit
	 * @since 1.43
	 */
	public static function getLimitSelector( int $selected = 20 ): string {
		$s = Html::rawElement( 'div', [ 'class' => 'cdx-field__item' ],
			Html::rawElement( 'div', [ 'class' => 'cdx-label' ],
				Html::label(
					wfMessage( 'pendingchanges-limit' )->text(),
					'wpLimit',
					[ 'class' => 'cdx-label__label' ]
				)
			)
		);

		$options = [ 20, 50, 100 ];
		$selectOptions = '';
		foreach ( $options as $option ) {
			$selectOptions .= Html::element( 'option', [
				'value' => $option,
				'selected' => $selected == $option
			], (string)$option );
		}

		$s .= Html::rawElement( 'select', [
			'name' => 'limit',
			'id' => 'wpLimit',
			'class' => 'cdx-select'
		], $selectOptions );

		return $s;
	}

	/**
	 * Generates a review box using a table using FlaggedRevsXML::addTagRatings()
	 * @param FlaggedRevision $frev the reviewed version
	 * @param string $shtml Short message HTML
	 * @param int $revsSince revisions since review
	 * @param string $type (stable/draft/oldstable)
	 * @param bool $synced does stable=current and this is one of them?
	 */
	public static function prettyRatingBox(
		FlaggedRevision $frev,
		string $shtml,
		int $revsSince,
		string $type = 'oldstable',
		bool $synced = false
	): string {
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
		$box .= Html::openElement(
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
		$box .= Html::closeElement( 'div' ) . "\n";
		$box .= "</div>\n";
		return $box;
	}

	/**
	 * Generates JS toggle arrow icon
	 */
	private static function ratingArrow(): string {
		return ( Html::element( 'span',
			[
				'class' => [ 'fr-toggle-arrow flaggedrevs-icon flaggedrevs-icon-expand' ],
				'id' => 'mw-fr-revisiontoggle',
				'title' => wfMessage( 'revreview-toggle-title' )->text(),
			],
			''
		) );
	}

	/**
	 * Generates the "(show/hide)" diff toggle. With JS disabled, it functions as a link to the diff.
	 * @param Title $title
	 * @param int $fromrev
	 * @param int $torev
	 * @param string|null $multiNotice Message about intermediate revisions
	 */
	public static function diffToggle( Title $title, int $fromrev, int $torev, string $multiNotice = null ): string {
		// Construct a link to the diff
		$href = $title->getFullURL( [ 'diff' => $torev, 'oldid' => $fromrev ] );

		$toggle = Html::element( 'a', [
			'class' => 'fr-toggle-text',
			'title' => wfMessage( 'revreview-diff-toggle-title' )->text(),
			'href' => $href,
			'data-mw-fromrev' => $fromrev,
			'data-mw-torev' => $torev,
			'data-mw-multinotice' => $multiNotice,
		], wfMessage( 'revreview-diff-toggle-show' )->text() );

		return '<span id="mw-fr-difftoggle">' .
			wfMessage( 'parentheses' )->rawParams( $toggle )->escaped() . '</span>';
	}

	/**
	 * Creates CSS draft page icon
	 */
	public static function draftStatusIcon(): string {
		$encTitle = wfMessage( 'revreview-draft-title' )->text();
		return ( Html::element( 'span',
			[
				'class' => [ 'flaggedrevs-icon', 'flaggedrevs-icon-block', 'skin-invert' ],
				'title' => $encTitle,
			],
			''
		) );
	}

	/**
	 * Creates CSS stable page icon
	 */
	public static function stableStatusIcon(): string {
		$encTitle = wfMessage( 'revreview-basic-title' )->text();
		return ( Html::element( 'span',
			[
				'class' => [ 'flaggedrevs-icon', 'flaggedrevs-icon-eye', 'skin-invert' ],
				'title' => $encTitle,
			],
			''
		) );
	}

	/**
	 * Creates CSS lock icon if page is locked/unlocked
	 */
	public static function lockStatusIcon( FlaggableWikiPage $flaggedArticle ): string {
		if ( $flaggedArticle->isPageLocked() ) {
			$encTitle = wfMessage( 'revreview-locked-title' )->text();
			$icon = 'articleSearch';
		} elseif ( $flaggedArticle->isPageUnlocked() ) {
			$encTitle = wfMessage( 'revreview-unlocked-title' )->text();
			$icon = 'articleCheck';
		} else {
			return '';
		}
		return ( Html::element( 'span',
			[
				'class' => [ 'flaggedrevs-icon', 'flaggedrevs-icon-' . $icon ],
				'title' => $encTitle,
			],
			''
		) );
	}

	/**
	 * Creates "stable rev reviewed on"/"x pending edits" message
	 */
	public static function pendingEditNotice( FlaggedRevision $frev, int $revsSince ): string {
		$msg = self::pendingEditNoticeMessage( $frev, $revsSince );
		return $msg->parse();
	}

	/**
	 * Same as pendingEditNotice(), but returns a Message object.
	 */
	public static function pendingEditNoticeMessage( FlaggedRevision $frev, int $revsSince ): Message {
		global $wgLang;
		$time = $wgLang->date( $frev->getTimestamp(), true );
		# Add message text for pending edits
		return wfMessage( 'revreview-pending-basic', $frev->getRevId(), $time )->numParams( $revsSince );
	}

	/**
	 * Creates a stability log excerpt
	 */
	public static function stabilityLogExcerpt( Title $title ): string {
		$logHtml = '';
		$params = [
			'lim'   => 1,
			'flags' => LogEventsList::NO_EXTRA_USER_LINKS
		];
		LogEventsList::showLogExtract( $logHtml, 'stable',
			$title->getPrefixedText(), '', $params );
		return Html::rawElement(
			'div',
			[ 'id' => 'mw-fr-logexcerpt' ],
			$logHtml
		);
	}

}
