/**
 * FlaggedRevs Advanced JavaScript
 *
 * @author Aaron Schulz
 * @author Krinkle <krinklemail@gmail.com> 2011
 */
( function () {
	'use strict';

	/* Dropdown collapse timer */
	var boxCollapseTimer = null;

	/* Expands flag info box details */
	function showBoxDetails() {
		$( '#mw-fr-revisiondetails' ).css( 'display', 'block' );
	}

	/* Collapses flag info box details */
	function hideBoxDetails() {
		$( '#mw-fr-revisiondetails' ).css( 'display', 'none' );
	}

	/**
	 * Toggles flag info box details for (+/-) control
	 *
	 * @context {jQuery}
	 */
	function toggleBoxDetails() {
		var $toggle = $( '#mw-fr-revisiontoggle' ),
			$ratings = $( '#mw-fr-revisiondetails' );

		if ( $toggle.length && $ratings.length ) {
			// Collapsed -> expand
			if ( $ratings.css( 'display' ) === 'none' ) {
				showBoxDetails();
				$toggle.text( mw.msg( 'revreview-toggle-hide' ) );
			// Expanded -> collapse
			} else {
				hideBoxDetails();
				$toggle.text( mw.msg( 'revreview-toggle-show' ) );
			}
		}
	}

	/**
	 * Checks if mouseOut event is for a child of parentId
	 *
	 * @param {jQuery.Event} e
	 * @param {string} parentId
	 * @return {boolean} True if given event object originated from a (direct or indirect)
	 * child element of an element with an id of parentId.
	 */
	function isMouseOutBubble( e, parentId ) {
		var nextParent,
			toNode = e.relatedTarget;

		if ( toNode ) {
			nextParent = toNode.parentNode;
			while ( nextParent ) {
				if ( nextParent.id === parentId ) {
					return true;
				}
				// next up
				nextParent = nextParent.parentNode;
			}
		}
		return false;
	}

	/**
	 * Expands flag info box details on mouseOver
	 *
	 * @context {jQuery}
	 */
	function onBoxMouseOver() {
		window.clearTimeout( boxCollapseTimer );
		boxCollapseTimer = null;
		showBoxDetails();
	}

	/**
	 * Hides flag info box details on mouseOut *except* for event bubbling
	 *
	 * @context {jQuery}
	 * @param {jQuery.Event} e
	 */
	function onBoxMouseOut( e ) {
		if ( !isMouseOutBubble( e, 'mw-fr-revisiontag' ) ) {
			boxCollapseTimer = window.setTimeout( hideBoxDetails, 150 );
		}
	}

	/**
	 * Toggles diffs
	 *
	 * @context {jQuery}
	 */
	function toggleDiff() {
		var $diff = $( '#mw-fr-stablediff' ),
			$toggle = $( '#mw-fr-difftoggle' );

		if ( $diff.length && $toggle.length ) {
			if ( $diff.css( 'display' ) === 'none' ) {
				// FIXME: Use CSS transition
				// eslint-disable-next-line no-jquery/no-animate-toggle
				$diff.show( 'slow' );
				$toggle.children( 'a' ).text( mw.msg( 'revreview-diff-toggle-hide' ) );
			} else {
				// FIXME: Use CSS transition
				// eslint-disable-next-line no-jquery/no-animate-toggle
				$diff.hide( 'slow' );
				$toggle.children( 'a' ).text( mw.msg( 'revreview-diff-toggle-show' ) );
			}
		}
	}

	/**
	 * Toggles log excerpts
	 *
	 * @context {jQuery}
	 */
	function toggleLog() {
		var hideMsg, showMsg,
			$log = $( '#mw-fr-logexcerpt' ),
			$toggle = $( '#mw-fr-logtoggle' );

		if ( $log.length && $toggle.length ) {
			// Two different message sets used here...
			if ( $toggle.hasClass( 'fr-logtoggle-details' ) ) {
				hideMsg = mw.msg( 'revreview-log-details-hide' );
				showMsg = mw.msg( 'revreview-log-details-show' );
			} else {
				hideMsg = mw.msg( 'revreview-log-toggle-hide' );
				showMsg = mw.msg( 'revreview-log-toggle-show' );
			}

			if ( $log.css( 'display' ) === 'none' ) {
				$log.show();
				$toggle.children( 'a' ).text( hideMsg );
			} else {
				$log.hide();
				$toggle.children( 'a' ).text( showMsg );
			}
		}
	}

	/**
	 * Update save button when "review this" checkbox changes
	 *
	 * @context {jQuery}
	 */
	function updateSaveButton() {
		var $save = $( '#wpSave' ),
			$checkbox = $( '#wpReviewEdit' );

		if ( $save.length && $checkbox.length ) {
			// Review pending changes
			if ( $checkbox.prop( 'checked' ) ) {
				if ( mw.config.get( 'wgEditSubmitButtonLabelPublish' ) ) {
					$save
						.val( mw.msg( 'publishchanges' ) )
						.attr( 'title',
							mw.msg( 'tooltip-publish' )
						);
				} else {
					$save
						.val( mw.msg( 'savearticle' ) )
						.attr( 'title',
							mw.msg( 'tooltip-save' )
						);
				}
			// Submit for review
			} else {
				$save
					.val( mw.msg( 'revreview-submitedit' ) )
					.attr( 'title',
						mw.msg( 'revreview-submitedit-title' )
					);
			}
			$save.updateTooltipAccessKeys();
		}
	}

	/**
	 * Startup function
	 */
	function init() {
		// Enables rating detail box
		var $toggle = $( '#mw-fr-revisiontoggle' );

		if ( $toggle.length ) {
			hideBoxDetails(); // hide the initially displayed ratings
		}

		// Bar UI: Toggle the box when the toggle is clicked
		$( '.fr-toggle-symbol#mw-fr-revisiontoggle' ).on( 'click', toggleBoxDetails );

		// Simple UI: Show the box on mouseOver
		$( '.fr-toggle-arrow#mw-fr-revisiontoggle' ).on( 'mouseover', onBoxMouseOver );
		$( '.flaggedrevs_short#mw-fr-revisiontag' ).on( 'mouseout', onBoxMouseOut );

		// Enables diff detail box and toggle
		$toggle = $( '#mw-fr-difftoggle' );
		if ( $toggle.length ) {
			$toggle.css( 'display', 'inline' ); // show toggle control
			$( '#mw-fr-stablediff' ).hide();
		}
		$toggle.children( 'a' ).on( 'click', toggleDiff );

		// Enables log detail box and toggle
		$toggle = $( '#mw-fr-logtoggle' );
		if ( $toggle.length ) {
			$toggle.css( 'display', 'inline' ); // show toggle control
			if ( $toggle.hasClass( 'fr-logtoggle-details' ) ) {
				// hide in edit mode
				$( '#mw-fr-logexcerpt' ).hide();
			}
		}
		$toggle.children( 'a' ).on( 'click', toggleLog );

		// Enables changing of save button when "review this" checkbox changes
		$( '#wpReviewEdit' ).on( 'click', updateSaveButton );
	}

	// Perform some onload events:
	$( init );

}() );
