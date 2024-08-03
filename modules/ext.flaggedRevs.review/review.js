/*!
 * FlaggedRevs Review JavaScript
 *
 * @author Aaron Schulz
 * @author Daniel Arnold 2008
 */
'use strict';

/**
 * Lock review form from submissions (using during AJAX requests)
 *
 * @param {HTMLFormElement} form
 */
function lockReviewForm( form ) {
	$( 'input', form ).prop( 'disabled', true );
}

/**
 * Unlock review form from submissions (using after AJAX requests)
 *
 * @param {HTMLFormElement} form
 */
function unlockReviewForm( form ) {
	var inputs = form.getElementsByTagName( 'input' );
	for ( var i = 0; i < inputs.length; i++ ) {
		if ( inputs[ i ].type !== 'submit' ) { // not all buttons can be enabled
			inputs[ i ].disabled = false;
		} else {
			// focus off element (T26013)
			$( inputs[ i ] ).trigger( 'blur' );
		}
	}
}

/**
 * Update form elements after review.
 *
 * @param {HTMLFormElement} form
 * @param {Object} respObj
 */
function postSubmitRevisionReview( form, respObj ) {
	// Review form elements
	var asubmit = document.querySelector( '#mw-fr-submit-accept' ); // ACCEPT
	var usubmit = document.querySelector( '#mw-fr-submit-unaccept' ); // UNACCEPT
	var rsubmit = document.querySelector( '#mw-fr-submit-reject' ); // REJECT
	var $diffNotice = $( '#mw-fr-difftostable' );
	// FlaggedRevs rating box
	var $tagBox = $( '#mw-fr-revision-tag' );
	// Diff parameters
	var $diffUIParams = $( '#mw-fr-diff-dataform' );

	// On success... (change-time can be an empty string for 'unapproved')
	if ( respObj && Object.prototype.hasOwnProperty.call( respObj, 'change-time' ) ) {
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
		// Update changetime for conflict handling
		form.elements.namedItem( 'changetime' ).value = respObj[ 'change-time' ];
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
	unlockReviewForm( form );
}

/**
 * Submit a revision review via AJAX and update form elements.
 *
 * Note: requestArgs build-up from radios/checkboxes
 * based on patch by Daniel Arnold (bug 13744)
 *
 * @param {HTMLElement} button
 * @param {HTMLFormElement} form
 */
function submitRevisionReview( button, form ) {
	lockReviewForm( form ); // disallow submissions

	// Build up API call, and update submit button text...
	var postData = {};
	var inputs = form.getElementsByTagName( 'input' );
	var target;
	for ( var i = 0; i < inputs.length; i++ ) {
		var input = inputs[ i ];
		if ( input.name === 'target' ) {
			target = input.value;
			continue;
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
			case 'radio':
				if ( input.checked ) { // must be checked
					postData[ input.name ] = input.value;
				}
				break;
			default:
				postData[ input.name ] = input.value; // text/hiddens...
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
			postSubmitRevisionReview( form, response );
		},
		error: function ( response ) {
			postSubmitRevisionReview( form, response.responseJSON );
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
	var form = document.getElementById( 'mw-fr-reviewform' );
	if ( form ) {
		// Enable submit functionality to the review form on this page
		$( '#mw-fr-submit-accept, #mw-fr-submit-unaccept' ).on( 'click', function () {
			submitRevisionReview( this, form );
			return false; // don't do normal non-AJAX submit
		} );

		// Hide review form in VE (T344091)
		mw.hook( 've.activationComplete' ).add( function () {
			form.style.display = 'none';
		} );

		// Disable 'accept' button if the revision was already reviewed.
		// This is used so that they can be re-enabled if a rating changes.
		// FIXME the button should be re-disabled if the user re-selects the status quo option
		var acceptButton = document.getElementById( 'mw-fr-submit-accept' );
		if ( 'mwFrReviewNeedsChange' in acceptButton.dataset ) {
			acceptButton.disabled = true;
		}

		// Update review form on change
		$( 'input', form ).on( 'change', function () {
			acceptButton.disabled = false;
			acceptButton.value = mw.msg( 'revreview-submit-review' ); // reset to "Accept"
		} );
	}

	// Enables changing of save button when "review this" checkbox changes
	$( '#wpReviewEdit' ).on( 'click', updateSaveButton );
}

$( init );
