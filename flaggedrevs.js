/* Every time you change this JS please bump $wgFlaggedRevStyleVersion in FlaggedRevs.php */

/* Hide rating clutter */
function enable_showhide() {
	var toggle = document.getElementById('mw-revisiontoggle');
	if( !toggle ) return;
	toggle.style.display = '';
	var ratings = document.getElementById('mw-revisionratings');
	if( !ratings ) return;
	ratings.style.display = 'none';
}

/* Toggles ratings */
function toggleRevRatings() {
	var ratings = document.getElementById('mw-revisionratings');
	if( !ratings ) return;
	if( ratings.style.display == 'none' ) {
		ratings.style.display = 'block';
	} else {
		ratings.style.display = 'none';
	}
}

/* 
* Update colors when select changes (Opera already does this) .
* Also remove comment box clutter.
*/
function updateRatingForm() {
	var selects = document.getElementById('mw-ratingselects');
	if( !selects ) return;
	
	var quality = true;
	var allzero = true;
	var somezero = false;
	var allowed = true;
	
	var select = selects.getElementsByTagName( 'select' );
	for( i = 0; i < select.length; i++ ) {
		// Update color
		select[i].className = 'fr-rating-option-' + select[i].selectedIndex;
		// Get quality level for this tag
		QL = wgFlaggedRevsJSparams.match( new RegExp(select[i].name + ':(\\d+)') );
		if( !QL ) continue;
		QL = QL[1];
		if( select[i].selectedIndex < QL ) {
			quality = false; // not a quality review
		}
		if( select[i].selectedIndex > 0 ) {
			allzero = false;
		} else {
			somezero = true;
		}
		// Check if disabled. If so, disable form.
		if( select[i].getAttribute('disabled') == 'disabled' ) {
			allowed = false;
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
	var submit = document.getElementById('mw-submitbutton');
	submit.disabled = ( (somezero && !allzero) || !allowed ) ? 'disabled' : '';
	var comment = document.getElementById('wpReason');
	comment.disabled = ( (somezero && !allzero) || !allowed ) ? 'disabled' : '';
	
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
