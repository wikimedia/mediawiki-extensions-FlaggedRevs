/**
 * FlaggedRevs Review JavaScript
 *
 * @author Aaron Schulz
 * @author Daniel Arnold 2008
 */
( function () {
	'use strict';

	var wgFlaggedRevsParams = mw.config.get( 'wgFlaggedRevsParams', {} );

	/*
	 * Update <select> color for the selected item/option
	 */
	function updateReviewFormColors( $form ) {
		var $select, selectedlevel, value;
		for ( var tag in wgFlaggedRevsParams.tags ) { // for each tag
			$select = $form.find( '[name="wp' + tag + '"]' ).eq( 0 );
			// Look for a selector for this tag
			if ( $select.length && $select.prop( 'nodeName' ) === 'SELECT' ) {
				selectedlevel = $select.prop( 'selectedIndex' );
				value = $select.children( 'option' ).eq( selectedlevel ).val();
				$select.prop( 'className', 'fr-rating-option-' + value );
				// Fix FF one-time jitter bug of changing an <option>
				$select.prop( 'selectedIndex', null );
				$select.prop( 'selectedIndex', selectedlevel );
			}
		}
	}

	/*
	 * Updates for radios/checkboxes on patch by Daniel Arnold (bug 13744).
	 * Visually update the revision rating form on change.
	 * - Disable submit in case of invalid input.
	 * - Update colors when <select> changes.
	 * - Also remove comment box clutter in case of invalid input.
	 * NOTE: all buttons should exist (perhaps hidden though)
	 */
	function updateReviewForm( $form ) {
		if ( $form.prop( 'disabled' ) ) {
			return;
		}

		var somezero = false;
		var $tagLevelSelects, $tagLevelSelect, selectedlevel;
		// Determine if this is a "quality" or "incomplete" review
		for ( var tag in wgFlaggedRevsParams.tags ) {
			// Get the element or elements for selecting the tag level.
			// We might get back a select, a checkbox, or *several* radios.
			$tagLevelSelects = $form.find( '[name="wp' + tag + '"]' );
			if ( !$tagLevelSelects.length ) {
				continue; // none found; binary flagging?
			}
			$tagLevelSelect = $tagLevelSelects.eq( 0 ); // convenient for select and checkbox

			selectedlevel = 0; // default
			if ( $tagLevelSelect.prop( 'nodeName' ) === 'SELECT' ) {
				selectedlevel = $tagLevelSelect.prop( 'selectedIndex' );
			} else if ( $tagLevelSelect.prop( 'type' ) === 'checkbox' ) {
				selectedlevel = $tagLevelSelect.prop( 'checked' ) ? 1 : 0;
			} else if ( $tagLevelSelect.prop( 'type' ) === 'radio' ) {
				// Go through each radio option and find the selected one...
				for ( var i = 0; i < $tagLevelSelects.length; i++ ) {
					if ( $tagLevelSelects.eq( i ).prop( 'checked' ) ) {
						selectedlevel = i;
						break;
					}
				}
			} else {
				return; // error: should not happen
			}

			if ( selectedlevel <= 0 ) {
				somezero = true;
			}
		}

		// (a) If only a few levels are zero ("incomplete") then disable submission.
		// (b) Re-enable submission for already accepted revs when ratings change.
		$( '#mw-fr-submit-accept' )
			.prop( 'disabled', somezero )
			.val( mw.msg( 'revreview-submit-review' ) ); // reset to "Accept"

		// Update colors of <select>
		updateReviewFormColors( $form );
	}

	/*
	 * Lock review form from submissions (using during AJAX requests)
	 */
	function lockReviewForm( $form ) {
		$form.find( 'input, textarea, select' ).prop( 'disabled', true );
	}

	/*
	 * Unlock review form from submissions (using after AJAX requests)
	 */
	function unlockReviewForm( $form ) {
		var $inputs = $form.find( 'input' );
		for ( var i = 0; i < $inputs.length; i++ ) {
			if ( $inputs.eq( i ).prop( 'type' ) !== 'submit' ) { // not all buttons can be enabled
				$inputs.eq( i ).prop( 'disabled', false );
			} else {
				// focus off element (T26013)
				$inputs.eq( i ).trigger( 'blur' );
			}
		}
		$form.find( 'textarea, select' ).prop( 'disabled', false );
	}

	/*
	 * Update form elements after review.
	 */
	function postSubmitRevisionReview( $form, respObj ) {
		// Review form elements
		var $asubmit = $( '#mw-fr-submit-accept' ); // ACCEPT
		var $usubmit = $( '#mw-fr-submit-unaccept' ); // UNACCEPT
		var $rsubmit = $( '#mw-fr-submit-reject' ); // REJECT
		var $diffNotice = $( '#mw-fr-difftostable' );
		// FlaggedRevs rating box
		var $tagBox = $( '#mw-fr-revisiontag' );
		// Diff parameters
		var $diffUIParams = $( '#mw-fr-diff-dataform' );

		// On success... (change-time can be an empty string for 'unapproved')
		if ( Object.prototype.hasOwnProperty.call( respObj, 'change-time' ) ) {
			// (a) Update document title and form buttons...
			if ( $asubmit.length && $usubmit.length ) {
				// Revision was flagged
				if ( $asubmit.val() === mw.msg( 'revreview-submitting' ) ) {
					$asubmit.val( mw.msg( 'revreview-submit-reviewed' ) ); // done!
					$asubmit.css( 'fontWeight', 'bold' );
					// Unlock and reset *unflag* button
					$usubmit.val( mw.msg( 'revreview-submit-unreview' ) );
					$usubmit.css( 'fontWeight', '' ); // back to normal
					$usubmit.show(); // now available
					$usubmit.prop( 'disabled', false ); // unlock
					$rsubmit.prop( 'disabled', true ); // lock if present
				// Revision was unflagged
				} else if ( $usubmit.val() === mw.msg( 'revreview-submitting' ) ) {
					$usubmit.val( mw.msg( 'revreview-submit-unreviewed' ) ); // done!
					$usubmit.css( 'fontWeight', 'bold' );
					// Unlock and reset *flag* button
					$asubmit.val( mw.msg( 'revreview-submit-review' ) );
					$asubmit.css( 'fontWeight', '' ); // back to normal
					$asubmit.prop( 'disabled', false ); // unlock
					$rsubmit.prop( 'disabled', false ); // unlock if present
				}
			}
			// (b) Remove review tag from drafts
			$tagBox.css( 'display', 'none' );
			// (c) Update diff-related items...
			if ( $diffUIParams.length ) {
				// Hide "review this" box on diffs
				$diffNotice.hide();
				// Update the contents of the mw-fr-diff-headeritems div
				var oldId = $diffUIParams.find( 'input' ).eq( 0 ).val();
				var newId = $diffUIParams.find( 'input' ).eq( 1 ).val();

				var restPath = '/flaggedrevs/internal/diffheader/' +
					encodeURIComponent( oldId ) + '/' +
					encodeURIComponent( newId );
				$.ajax( {
					url: mw.util.wikiScript( 'rest' ) + restPath,
					type: 'GET',
					// response type
					dataType: 'html',
					success: function ( html ) {
						// Update the contents of the mw-fr-diff-headeritems div
						$( '#mw-fr-diff-headeritems' ).html( html );
					}
				} );
			}
		// On failure...
		} else {
			// (a) Update document title and form buttons...
			if ( $asubmit.length && $usubmit.length ) {
				// Revision was flagged
				if ( $asubmit.val() === mw.msg( 'revreview-submitting' ) ) {
					$asubmit.val( mw.msg( 'revreview-submit-review' ) ); // back to normal
					$asubmit.prop( 'disabled', false ); // unlock
				// Revision was unflagged
				} else if ( $usubmit.val() === mw.msg( 'revreview-submitting' ) ) {
					$usubmit.val( mw.msg( 'revreview-submit-unreview' ) ); // back to normal
					$usubmit.prop( 'disabled', false ); // unlock
				}
			}
			// (b) Output any error response message
			mw.notify( $.parseHTML( respObj[ 'error-html' ] ), { tag: 'review' } ); // failure notice
		}
		// Update changetime for conflict handling
		if ( Object.prototype.hasOwnProperty.call( respObj, 'change-time' ) ) {
			$( '#mw-fr-input-changetime' ).val( respObj[ 'change-time' ] );
		}
		unlockReviewForm( $form );
	}

	/*
	 * Submit a revision review via AJAX and update form elements.
	 *
	 * Note: requestArgs build-up from radios/checkboxes
	 * based on patch by Daniel Arnold (bug 13744)
	 */
	function submitRevisionReview( button, $form ) {
		lockReviewForm( $form ); // disallow submissions

		// Build up API call, and update submit button text...
		var postData = {};
		var $inputs = $form.find( 'input' );
		var target;
		for ( var i = 0; i < $inputs.length; i++ ) {
			var $input = $inputs.eq( i );
			if ( $input.prop( 'name' ) === 'target' ) {
				target = $input.val();
			}
			// Different input types may occur depending on tags...
			if ( $input.prop( 'name' ) === 'title' || $input.prop( 'name' ) === 'action' ) {
				continue; // No need to send these...
			} else if ( $input.prop( 'type' ) === 'submit' ) {
				if ( $input.prop( 'id' ) === button.id ) {
					postData[ $input.prop( 'name' ) ] = '1';
					// Show that we are submitting via this button
					$input.val( mw.msg( 'revreview-submitting' ) );
				}
			} else if ( $input.prop( 'type' ) === 'checkbox' ) {
				postData[ $input.prop( 'name' ) ] = $input.prop( 'checked' ) ? $input.val() : 0;
			} else if ( $input.prop( 'type' ) === 'radio' ) {
				if ( $input.prop( 'checked' ) ) { // must be checked
					postData[ $input.prop( 'name' ) ] = $input.val();
				}
			} else {
				postData[ $input.prop( 'name' ) ] = $input.val(); // text/hiddens...
			}
		}
		var $selects = $form.find( 'select' );
		for ( var j = 0; j < $selects.length; j++ ) {
			var $select = $selects.eq( j );
			// Get the selected tag level...
			if ( $select.prop( 'selectedIndex' ) >= 0 ) {
				var $soption = $select.find( 'option' ).eq( $select.prop( 'selectedIndex' ) );
				postData[ $select.prop( 'name' ) ] = $soption.val();
			}
		}

		var restPath = '/flaggedrevs/internal/review/' + encodeURIComponent( target );
		$.ajax( {
			url: mw.util.wikiScript( 'rest' ) + restPath,
			type: 'POST',
			data: JSON.stringify( postData ),
			contentType: 'application/json',
			// response type
			dataType: 'json',
			success: function ( response ) {
				postSubmitRevisionReview( $form, response );
			},
			error: function ( response ) {
				postSubmitRevisionReview( $form, response.responseJSON );
			}
		} );
	}

	/* Startup function */
	function init() {
		var $form = $( '#mw-fr-reviewform' );

		// Enable submit functionality to the review form on this page
		$( '#mw-fr-submit-accept, #mw-fr-submit-unaccept' ).on( 'click', function () {
			submitRevisionReview( this, $form );
			return false; // don't do normal non-AJAX submit
		} );

		// Disable 'accept' button if the revision was already reviewed.
		// This is used so that they can be re-enabled if a rating changes.
		/* global jsReviewNeedsChange */
		// wtf? this is set in frontend/RevisionReviewFormUI by outputting JS
		if ( typeof jsReviewNeedsChange !== 'undefined' && jsReviewNeedsChange === 1 ) {
			$( '#mw-fr-submit-accept' ).prop( 'disabled', true );
		}

		// Setup <select> form option colors
		updateReviewFormColors( $form );
		// Update review form on change
		$form.find( 'input, select' ).on( 'change', function () {
			updateReviewForm( $form );
		} );
	}

	// Perform some onload events:
	$( init );

}() );
