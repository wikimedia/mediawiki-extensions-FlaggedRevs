/*!
 * FlaggedRevs Review JavaScript
 *
 * @author Aaron Schulz
 * @author Daniel Arnold 2008
 */
'use strict';

var wgFlaggedRevsParams = mw.config.get( 'wgFlaggedRevsParams' ) || {};

/**
 * Update radios/checkboxes.
 *
 * Originally introduced as part of T15744.
 *
 * Visually update the revision rating form on change:
 * - Disable submit in case of invalid input.
 * - Update colors when <select> changes.
 * - Also remove comment box clutter in case of invalid input.
 *
 * NOTE: all buttons should exist (perhaps hidden though)
 *
 * @param {jQuery} $form
 */
function updateReviewForm( $form ) {
	if ( $form.prop( 'disabled' ) ) {
		return;
	}

	var somezero = false;
	// Determine if this is a "quality" or "incomplete" review
	for ( var tag in wgFlaggedRevsParams.tags ) {
		// Get the element or elements for selecting the tag level.
		// We might get back a select, a checkbox, or *several* radios.
		var $tagLevelSelects = $form.find( '[name="wp' + tag + '"]' );
		if ( !$tagLevelSelects.length ) {
			continue; // none found; binary flagging?
		}
		var tagLevelSelect = $tagLevelSelects.get( 0 ); // convenient for select and checkbox

		var selectedlevel = 0; // default
		if ( tagLevelSelect.nodeName === 'SELECT' ) {
			selectedlevel = tagLevelSelect.selectedIndex;
		} else if ( tagLevelSelect.type === 'checkbox' ) {
			selectedlevel = tagLevelSelect.checked ? 1 : 0;
		} else if ( tagLevelSelect.type === 'radio' ) {
			// Go through each radio option and find the selected one...
			for ( var i = 0; i < $tagLevelSelects.length; i++ ) {
				if ( $tagLevelSelects.get( i ).checked ) {
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
}

/**
 * Lock review form from submissions (using during AJAX requests)
 *
 * @param {jQuery} $form
 */
function lockReviewForm( $form ) {
	$form.find( 'input, textarea, select' ).prop( 'disabled', true );
}

/**
 * Unlock review form from submissions (using after AJAX requests)
 *
 * @param {jQuery} $form
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

/**
 * Update form elements after review.
 *
 * @param {jQuery} $form
 * @param {Object} respObj
 */
function postSubmitRevisionReview( $form, respObj ) {
	// Review form elements
	var asubmit = document.querySelector( '#mw-fr-submit-accept' ); // ACCEPT
	var usubmit = document.querySelector( '#mw-fr-submit-unaccept' ); // UNACCEPT
	var rsubmit = document.querySelector( '#mw-fr-submit-reject' ); // REJECT
	var $diffNotice = $( '#mw-fr-difftostable' );
	// FlaggedRevs rating box
	var $tagBox = $( '#mw-fr-revisiontag' );
	// Diff parameters
	var $diffUIParams = $( '#mw-fr-diff-dataform' );

	// On success... (change-time can be an empty string for 'unapproved')
	if ( Object.prototype.hasOwnProperty.call( respObj, 'change-time' ) ) {
		// (a) Update document title and form buttons...
		if ( asubmit && usubmit ) {
			// Revision was flagged
			if ( asubmit.value === mw.msg( 'revreview-submitting' ) ) {
				asubmit.value = mw.msg( 'revreview-submit-reviewed' ); // done!
				asubmit.style.fontWeight = 'bold';
				// Unlock and reset *unflag* button
				usubmit.value = mw.msg( 'revreview-submit-unreview' );
				// Undo any previous bolding
				usubmit.style.fontWeight = '';
				// Undo dislay:none from RevisionReviewFormUI.php
				usubmit.style.display = '';
				usubmit.disabled = false; // unlock
				// lock if present
				if ( rsubmit ) {
					rsubmit.disabled = true;
				}
			// Revision was unflagged
			} else if ( usubmit.value === mw.msg( 'revreview-submitting' ) ) {
				usubmit.value = mw.msg( 'revreview-submit-unreviewed' ); // done!
				usubmit.style.fontWeight = 'bold';
				// Unlock and reset *flag* button
				asubmit.value = mw.msg( 'revreview-submit-review' );
				asubmit.style.fontWeight = ''; // back to normal
				asubmit.disabled = false; // unlock
				// unlock if present
				if ( rsubmit ) {
					rsubmit.disabled = false;
				}
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
		if ( asubmit && usubmit ) {
			// Revision was flagged
			if ( asubmit.value === mw.msg( 'revreview-submitting' ) ) {
				asubmit.value = mw.msg( 'revreview-submit-review' ); // back to normal
				asubmit.disabled = false; // unlock
			// Revision was unflagged
			} else if ( usubmit.value === mw.msg( 'revreview-submitting' ) ) {
				usubmit.value = mw.msg( 'revreview-submit-unreview' ); // back to normal
				usubmit.disabled = false; // unlock
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

/**
 * Submit a revision review via AJAX and update form elements.
 *
 * Note: requestArgs build-up from radios/checkboxes
 * based on patch by Daniel Arnold (bug 13744)
 *
 * @param {HTMLElement} button
 * @param {jQuery} $form
 */
function submitRevisionReview( button, $form ) {
	lockReviewForm( $form ); // disallow submissions

	// Build up API call, and update submit button text...
	var postData = {};
	var $inputs = $form.find( 'input' );
	var target;
	for ( var i = 0; i < $inputs.length; i++ ) {
		var input = $inputs.get( i );
		if ( input.name === 'target' ) {
			target = input.value;
		}
		// Different input types may occur depending on tags...
		if ( input.name === 'title' || input.name === 'action' ) {
			continue; // No need to send these...
		}
		switch ( input.type ) {
			case 'submit':
				if ( input.id === button.id ) {
					postData[ input.name ] = '1';
					// Show that we are submitting via this button
					input.value = mw.msg( 'revreview-submitting' );
				}
				break;
			case 'checkbox':
				postData[ input.name ] = input.checked ? input.value : 0;
				break;
			case 'radio':
				if ( input.checked ) { // must be checked
					postData[ input.name ] = input.value;
				}
				break;
			default:
				postData[ input.name ] = input.value; // text/hiddens...
		}
	}
	var $selects = $form.find( 'select' );
	for ( var j = 0; j < $selects.length; j++ ) {
		var select = $selects.get( j );
		// Get the selected tag level...
		if ( select.selectedIndex >= 0 ) {
			var soption = select.options[ select.selectedIndex ];
			postData[ select.name ] = soption.value;
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

/**
 * Update save button in the edit form when "review this" checkbox changes
 *
 * @this {jQuery}
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
	// FIXME the button should be re-disabled if the user re-selects the status quo option
	if ( typeof jsReviewNeedsChange !== 'undefined' && jsReviewNeedsChange === 1 ) {
		$( '#mw-fr-submit-accept' ).prop( 'disabled', true );
	}

	// Enables changing of save button when "review this" checkbox changes
	$( '#wpReviewEdit' ).on( 'click', updateSaveButton );

	// Update review form on change
	$form.find( 'input, select' ).on( 'change', function () {
		updateReviewForm( $form );
	} );
}

$( init );
