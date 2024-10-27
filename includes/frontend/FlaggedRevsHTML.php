<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;

/**
 * Class containing utility HTML functions for a FlaggedRevs.
 * Includes functions for selectors, icons, notices, CSS, and form aspects.
 */
class FlaggedRevsHTML {

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
	 * Generates a review box/tag displaying the quality level based on flags.
	 *
	 * This method creates a simple HTML table with two cells: one for the quality label
	 * and the other for the corresponding rating. The table is only generated if the page
	 * is not protected by FlaggedRevs settings.
	 *
	 * @param array $flags An associative array containing the flag ratings.
	 *
	 * @return string The generated HTML string for the review box/tag.
	 */
	public static function addTagRatings( array $flags ): string {
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			return '';
		}

		$quality = FlaggedRevs::getTagName();
		$level = $flags[$quality] ?? 0;
		$encValueText = wfMessage( "revreview-$quality-$level" )->text();
		$levelClass = 'fr-value' . ( $level * 20 + 20 );

		return Html::rawElement( 'table', [
			'id' => 'mw-fr-revisionratings-box',
			'class' => 'flaggedrevs-color-1',
			'style' => 'margin: auto;',
			'cellpadding' => '0',
		],
			Html::rawElement( 'tr', [],
				Html::element( 'td', [ 'class' => 'fr-text', 'style' => 'vertical-align: middle;' ],
					wfMessage( "revreview-$quality" )->text()
				) .
				Html::element( 'td', [ 'class' => $levelClass, 'style' => 'vertical-align: middle;' ],
					$encValueText
				)
			)
		);
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
	 * Generates a review box using a table using FlaggedRevsHTML::addTagRatings()
	 *
	 * @param FlaggedRevision|null $frev the reviewed version
	 * @param int $revisionId the revision ID
	 * @param int $revsSince revisions since review
	 * @param string $type (stable/draft/oldstable)
	 * @param bool $synced does stable=current and this is one of them?
	 *
	 * @return string
	 */
	public static function reviewDialog(
		?FlaggedRevision $frev,
		int $revisionId,
		int $revsSince,
		string $type = 'oldstable',
		bool $synced = false
	): string {
		global $wgLang;
		$href = '';
		$context = RequestContext::getMain();
		$user = $context->getAuthority();
		$skin = $context->getSkin();

		// If $frev is null, show a dialog with a "no flagged revision" message
		if ( $frev === null ) {
			$subtitleMessageKey = 'revreview-unchecked-title';
			$msg = 'revreview-noflagged';
			$subtitle = wfMessage( $subtitleMessageKey )->text();
			$html = wfMessage( $msg )->parse();
		} else {
			// Regular case when $frev is not null
			$flags = $frev->getTags();
			$time = $wgLang->date( $frev->getTimestamp(), true );

			$subtitleMessageKey = ( $type === 'stable' || $synced )
				? 'revreview-basic-title' // This is a checked version of this page
				: 'revreview-draft-title'; // Pending changes are displayed on this page

			$subtitle = wfMessage( $subtitleMessageKey )->text();

			// Construct some tagging
			if ( $synced && ( $type == 'stable' || $type == 'draft' ) ) {
				$msg = 'revreview-basic-same';
				$html = wfMessage( $msg, $frev->getRevId(), $time )->numParams( $revsSince )->parse();
			} elseif ( $type == 'oldstable' ) {
				$msg = 'revreview-basic-old';
				$html = wfMessage( $msg, $frev->getRevId(), $time )->parse();
			} else {
				$msg = $type === 'stable' ? 'revreview-basic' : 'revreview-newest-basic';
				$msg .= !$revsSince ? '-i' : '';
				$html = wfMessage( $msg, $frev->getRevId(), $time )->numParams( $revsSince )->parse();
			}

			// Add any rating tags as needed...
			if ( $flags && !FlaggedRevs::binaryFlagging() ) {
				if ( $skin->getSkinName() !== 'minerva' ) {
					// Don't show the ratings on draft views
					if ( $type == 'stable' || $type == 'oldstable' ) {
						$html .= '<p>' . self::addTagRatings( $flags ) . '</p>';
					}
				}
			}

			$title = $frev->getTitle();
			$href = $title->getFullURL( [ 'diff' => 'cur', 'oldid' => $revisionId ] );
		}

		if ( $skin && $skin->getSkinName() === 'minerva' ) {
			return self::addMessageBox( 'inline', $html, [
				'class' => 'mw-fr-mobile-message-inline',
			] );
		} else {
			return Html::rawElement(
				'div',
				[
					'id' => 'mw-fr-revision-details',
					'class' => 'mw-fr-revision-details-dialog',
					'style' => 'display:none;'
				],
				Html::rawElement( 'div', [ 'tabindex' => '0' ] ) .
				Html::rawElement(
					'div',
					[ 'class' => 'cdx-dialog cdx-dialog--horizontal-actions' ],
					Html::rawElement(
						'header',
						[ 'class' => 'cdx-dialog__header cdx-dialog__header--default' ],
						Html::rawElement(
							'div',
							[ 'class' => 'cdx-dialog__header__title-group' ],
							Html::element( 'h2', [ 'class' => 'cdx-dialog__header__title' ],
								wfMessage( 'revreview-dialog-title' ) ) .
							Html::element( 'p', [ 'class' => 'cdx-dialog__header__subtitle' ], $subtitle )
						) .
						Html::rawElement( 'button', [
							'class' => 'cdx-button cdx-button--action-default cdx-button--weight-quiet
							cdx-button--size-medium cdx-button--icon-only cdx-dialog__header__close-button',
							'aria-label' => wfMessage( 'fr-revision-info-dialog-close-aria-label' ),
							'onclick' => 'document.getElementById("mw-fr-revision-details").style.display = "none";'
						],
							Html::rawElement( 'span', [ 'class' => 'cdx-icon cdx-icon--medium
							cdx-fr-css-icon--close' ] )
						)
					) .
					Html::rawElement(
						'div',
						[ 'class' => 'cdx-dialog__body' ],
						$html
					) .
					( $frev !== null && $user->isAllowed( 'review' ) ?
						Html::rawElement(
							'footer',
							[ 'class' => 'cdx-dialog__footer cdx-dialog__footer--default' ],
							Html::rawElement( 'div', [ 'class' => 'cdx-dialog__footer__actions' ],
								Html::element(
									'a',
									[
										'href' => $href,
										'class' =>
											'cdx-button cdx-button--action-progressive cdx-button--weight-primary
											cdx-button--size-medium cdx-dialog__footer__primary-action
											cdx-button--fake-button cdx-button--fake-button--enabled'
									],
									wfMessage( 'fr-revision-info-dialog-review-button' )
								) .
								Html::element(
									'button',
									[
										'class' => 'cdx-dialog__footer__default-action cdx-button cdx-button--default',
										'onclick' =>
											'document.getElementById("mw-fr-revision-details").style.display = "none";'
									],
									wfMessage( 'fr-revision-info-dialog-cancel-button' )
								)
							)
						) : '' )
				) .
				Html::rawElement( 'div', [ 'tabindex' => '0' ] )
			);
		}
	}

	/**
	 * Generates a custom message box using the `cdx-message` class.
	 *
	 * This method creates a message box with the `cdx-message` class, which can be configured
	 * as either `inline` or `block`. The method allows for additional attributes to be passed
	 * to further customize the appearance and behavior of the message box.
	 *
	 * The message box will include:
	 * - An outer `div` element with the appropriate `cdx-message` classes and any additional
	 *   classes or attributes specified in the `$attrs` parameter.
	 * - A `span` element for the message icon, using the `cdx-message__icon` class.
	 * - A nested `div` element to contain the message content, using the `cdx-message__content` class.
	 *
	 * This method is useful for creating consistent, styled message boxes across the application.
	 *
	 * @param string $type The type of message box to create, either 'inline' or 'block'.
	 *                     This determines the overall structure and style of the message box.
	 * @param string $message The content to display inside the message box. This can include
	 *                        HTML or plain text, depending on the context.
	 * @param array $attrs Optional. An associative array of additional HTML attributes to
	 *                     apply to the outer `div` element of the message box. The `class`
	 *                     attribute can be extended by passing additional classes in this array.
	 * @return string The generated HTML string for the complete message box, ready to be
	 *                rendered in the output.
	 */
	public static function addMessageBox( string $type, string $message, array $attrs = [] ): string {
		// Base classes for the message box, including type and notice class
		$baseClass = 'cdx-message mw-fr-message-box cdx-message--' . $type . ' cdx-message--notice';

		// Merge custom attributes with the default class
		$attrs['class'] = isset( $attrs['class'] ) ? $baseClass . ' ' . $attrs['class'] : $baseClass;

		// Generate and return the complete HTML for the message box
		return Html::rawElement(
			'div',
			$attrs,
			Html::element( 'span', [ 'class' => 'cdx-message__icon' ] ) .
			Html::rawElement( 'div', [ 'class' => 'cdx-message__content' ], $message )
		);
	}

	/**
	 * Generates the "(show/hide)" diff toggle. With JS disabled, it functions as a link to the diff.
	 *
	 * @param Title $title
	 * @param int $fromrev
	 * @param int $torev
	 * @param string|null $multiNotice Message about intermediate revisions
	 *
	 * @return string
	 */
	public static function diffToggle( Title $title, int $fromrev, int $torev, ?string $multiNotice = null ): string {
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

		return '<span id="mw-fr-diff-toggle">' .
			wfMessage( 'parentheses' )->rawParams( $toggle )->escaped() . '</span>';
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
