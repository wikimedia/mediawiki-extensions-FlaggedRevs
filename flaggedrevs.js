/* -- (c) Aaron Schulz, Daniel Arnold 2008 */

/* Every time you change this JS please bump $wgFlaggedRevStyleVersion in FlaggedRevs.php */

/* Hide rating clutter */
function enable_showhide() {
	var toggle = document.getElementById('mw-revisiontoggle');
	if( !toggle ) return;
	toggle.style.display = 'inline';
	var ratings = document.getElementById('mw-revisionratings');
	if( !ratings ) return;
	ratings.style.display = 'none';
}

/* Toggles ratings */
function toggleRevRatings() {
	var ratings = document.getElementById('mw-revisionratings');
	if( !ratings ) return;
	if( ratings.style.display == 'none' ) {
		ratings.style.display = 'inline';
	} else {
		ratings.style.display = 'none';
	}
}

/*
* a) Disable submit in case of invalid input.
* b) Update colors when select changes (Opera already does this).
* c) Also remove comment box clutter in case of invalid input.
*/
function updateRatingForm() {
	var ratingform = document.getElementById('mw-ratingselects');
	if( !ratingform ) return;
	var disabled = document.getElementById('fr-rating-controls-disabled');
	if( disabled ) return;

	var quality = true;
	var allzero = true;
	var somezero = false;

	for( tag in wgFlaggedRevsParams.tags ) {
		var controlName = "wp" + tag;
		var levels = document.getElementsByName(controlName);
		var selectedlevel = 0; // default

		if( levels[0].nodeName == 'SELECT' ) {
			selectedlevel = levels[0].selectedIndex;
			// Update color. Opera does this already, and doing so
			// seems to kill custom pretty opera skin form styling.
			if( navigator.appName != 'Opera') {
				levels[0].className = 'fr-rating-option-' + selectedlevel;
			}
		} else if( levels[0].type == 'radio' ) {
			for( i = 0; i < levels.length; i++ ) {
				if( levels[i].checked ) {
					selectedlevel = i;
					break;
				}
			}
		} else if( levels[0].type == 'checkbox' ) {
			selectedlevel = (levels[0].checked) ? 1: 0;
		} else {
			return; // error: should not happen
		}

		// Get quality level for this tag
		qualityLevel = wgFlaggedRevsParams.tags[tag];

		if( selectedlevel < qualityLevel ) {
			quality = false; // not a quality review
		}
		if( selectedlevel > 0 ) {
			allzero = false;
		} else {
			somezero = true;
		}
	}

	showComment = (quality || allzero) ? true : false;
	// Show comment box only for quality revs or depreciated ones
	var commentbox = document.getElementById('mw-commentbox');
	if( commentbox ) {
		commentbox.style.display = showComment ? 'inline' : 'none';
	}
	// Show note box only for quality revs
	var notebox = document.getElementById('mw-notebox');
	if( notebox ) {
		notebox.style.display = quality ? 'inline' : 'none';
	}
	// If only a few levels are zero, don't show submit link
	var submit = document.getElementById('submitreview');
	submit.disabled = ( somezero && !allzero ) ? 'disabled' : '';
	var comment = document.getElementById('wpReason');
	comment.disabled = ( somezero && !allzero ) ? 'disabled' : '';
	
	// Clear comment box data if not shown
	var comment = document.getElementById('wpReason');
	if( comment ) {
		comment.value = showComment ? comment.value : '';
	}
	// Clear note box data if not shown
	var notes = document.getElementById('wpNotes');
	if( notes ) {
		notes.value = quality ? notes.value : '';
	}
}

addOnloadHook(enable_showhide);
addOnloadHook(updateRatingForm);
