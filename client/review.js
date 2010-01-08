/* -- (c) Aaron Schulz, Daniel Arnold 2008 */

/* Every time you change this JS please bump $wgFlaggedRevStyleVersion in FlaggedRevs.php */

/*
* a) Disable submit in case of invalid input.
* b) Update colors when select changes (Opera already does this).
* c) Also remove comment box clutter in case of invalid input.
*/
FlaggedRevs.updateRatingForm = function() {
	var ratingform = document.getElementById('mw-fr-ratingselects');
	if( !ratingform ) return;
	var disabled = document.getElementById('fr-rating-controls-disabled');
	if( disabled ) return;

	var quality = true;
	var allzero = true;
	var somezero = false;
	
	for( tag in wgFlaggedRevsParams.tags ) {
		var controlName = "wp" + tag;
		var levels = document.getElementsByName(controlName);
		if( !levels.length ) continue;
		var selectedlevel = 0; // default
	
		if( levels[0].nodeName == 'SELECT' ) {
			selectedlevel = levels[0].selectedIndex;
			// Update color. Opera does this already, and doing so
			// seems to kill custom pretty opera skin form styling.
				if( navigator.appName != 'Opera') {
				value = levels[0].getElementsByTagName('option')[selectedlevel].value;
				levels[0].className = 'fr-rating-option-' + value;
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
	// Show note box only for quality revs
	var notebox = document.getElementById('mw-fr-notebox');
	if( notebox ) {
		notebox.style.display = quality ? 'inline' : 'none';
	}
	// If only a few levels are zero, don't show submit link
	var submit = document.getElementById('mw-fr-submitreview');
	if( submit ) {
		submit.disabled = ( somezero && !allzero ) ? 'disabled' : '';
	}
	// Clear note box data if not shown
	var notes = document.getElementById('wpNotes');
	if( notes ) {
		notes.value = quality ? notes.value : '';
	}
}

addOnloadHook(FlaggedRevs.updateRatingForm);

// dependencies:
// * ajax.js:
  /*extern sajax_init_object, sajax_do_call */
// * wikibits.js:
  /*extern hookEvent, jsMsg */
// These should have been initialized in the generated js
if( typeof wgAjaxReview === "undefined" || !wgAjaxReview ) {
	wgAjaxReview = {};
}

wgAjaxReview.supported = true; // supported on current page and by browser
wgAjaxReview.inprogress = false; // ajax request in progress
wgAjaxReview.timeoutID = null; // see wgAjaxReview.ajaxCall

wgAjaxReview.ajaxCall = function() {
	if( !wgAjaxReview.supported ) {
		return true;
	} else if( wgAjaxReview.inprogress ) {
		return false;
	}
	if( !wfSupportsAjax() ) {
		// Lazy initialization so we don't toss up
		// ActiveX warnings on initial page load
		// for IE 6 users with security settings.
		wgAjaxReview.supported = false;
		return true;
	}
	var form = document.getElementById("mw-fr-reviewform");
	var notes = document.getElementById("wpNotes");
	var reason = document.getElementById("wpReason");
	if( !form ) {
		return false;
	}
	wgAjaxReview.inprogress = true;
	// Build up arguments
	var args = [];
	var inputs = form.getElementsByTagName("input");
	for( var i=0; i < inputs.length; i++) {
		// Different input types may occur depending on tags...
		if( inputs[i].name == "title" || inputs[i].name == "action" ) {
			continue; // No need to send these...
		} else if( inputs[i].type == "submit" ) {
			if( inputs[i].id == this.id ) {
				inputs[i].value = wgAjaxReview.sendingMsg; // show that we are submitting
				args.push( inputs[i].name + "|1" );
			}
		} else if( inputs[i].type == "checkbox" ) {
			args.push( inputs[i].name + "|" + (inputs[i].checked ? inputs[i].value : 0) );
		} else if( inputs[i].type == "radio" ) {
			if( inputs[i].checked ) { // must be checked
				args.push( inputs[i].name + "|" + inputs[i].value );
			}
		} else {
			args.push( inputs[i].name + "|" + inputs[i].value ); // text/hiddens...
		}
		inputs[i].disabled = "disabled";
	}
	if( notes ) {
		args.push( notes.name + "|" + notes.value );
		notes.disabled = "disabled";
	}
	var selects = form.getElementsByTagName("select");
	for( var i=0; i < selects.length; i++) {
		// Get the selected tag level...
		if( selects[i].selectedIndex >= 0 ) {
			var soption = selects[i].getElementsByTagName("option")[selects[i].selectedIndex];
			args.push( selects[i].name + "|" + soption.value );
		}
		selects[i].disabled = "disabled";
	}
	// Send!
	var old = sajax_request_type;
	sajax_request_type = "POST";
	sajax_do_call( "RevisionReview::AjaxReview", args, wgAjaxReview.processResult );
	sajax_request_type = old;
	// If the request isn't done in 30 seconds, allow user to try again
	wgAjaxReview.timeoutID = window.setTimeout(
		function() { wgAjaxReview.inprogress = false; wgAjaxReview.unlockForm(); },
		30000
	);
	return false;
};

wgAjaxReview.unlockForm = function() {
	var form = document.getElementById("mw-fr-reviewform");
	var submit = document.getElementById("mw-fr-submitreview");
	var notes = document.getElementById("wpNotes");
	var reason = document.getElementById("wpReason");
	if( !form || !submit ) {
		return false;
	}
	var inputs = form.getElementsByTagName("input");
	for( var i=0; i < inputs.length; i++) {
		if( inputs[i].type != 'submit' ) {
			inputs[i].disabled = "";
		}
	}
	if( notes ) {
		notes.disabled = "";
	}
	if( reason ) {
		reason.disabled = "";
	}
	var selects = form.getElementsByTagName("select");
	for( var i=0; i < selects.length; i++) {
		selects[i].disabled = "";
	}
	return true;
};

wgAjaxReview.processResult = function(request) {
	if( !wgAjaxReview.supported ) {
		return;
	}
	var response = request.responseText;
	if( (msg = response.substr(6)) ) {
		jsMsg( msg, 'review' ); // success notice
		window.scroll(0,0); // scroll up to notice
		tagBox = document.getElementById('mw-fr-revisiontag');
		if( tagBox ) tagBox.style.display = 'none'; // remove tag from draft
	}
	wgAjaxReview.inprogress = false;
	if( wgAjaxReview.timeoutID ) {
		window.clearTimeout(wgAjaxReview.timeoutID);
	}
	var rsubmit = document.getElementById("mw-fr-submitreview");
	var usubmit = document.getElementById("mw-fr-submitunreview");
	var binaryState = document.getElementById("mw-fr-reviewstate");
	var legend = document.getElementById("mw-fr-reviewformlegend");
	var diffNotice = document.getElementById("mw-fr-difftostable");
	// On success...
	if( response.indexOf('<suc#>') == 0 ) {
		document.title = wgAjaxReview.actioncomplete;
		if( rsubmit ) {
			rsubmit.disabled = ''; // unlock flag button
			// If flagging is just binary, flip the form
			if( usubmit ) {
				// Revision was flagged
				if( rsubmit.value == wgAjaxReview.sendingMsg ) {
					legend.innerHTML = '<strong>'+wgAjaxReview.reflagLegMsg+'</strong>';
					rsubmit.value = wgAjaxReview.flagMsg; // back to normal
					usubmit.disabled = ''; // unlock unflag button
				// Revision was unflagged
				} else if( usubmit.value == wgAjaxReview.sendingMsg ) {
					legend.innerHTML = '<strong>'+wgAjaxReview.flagLegMsg+'</strong>';
					usubmit.value = wgAjaxReview.unflagMsg; // back to normal
				}
			} else {
				rsubmit.value = wgAjaxReview.sendMsg; // back to normal
			}
		}
		// Hide "review this" box on diffs
		if( diffNotice ) diffNotice.style.display = 'none';
	// On failure...
	} else {
		document.title = wgAjaxReview.actionfailed;
		if( rsubmit ) {
			rsubmit.disabled = ''; // unlock flag button
			// Revision was flagged
			if( rsubmit.value == wgAjaxReview.sendingMsg ) {
				rsubmit.value = wgAjaxReview.flagMsg; // back to normal
			// Revision was unflagged
			} else if( usubmit.value == wgAjaxReview.sendingMsg ) {
				usubmit.value = wgAjaxReview.unflagMsg; // back to normal
				usubmit.disabled = ''; // unlock
			}
		}
	}
	wgAjaxReview.unlockForm();
};

wgAjaxReview.onLoad = function() {
	var rsubmit = document.getElementById("mw-fr-submitreview");
	if( rsubmit ) {
		rsubmit.onclick = wgAjaxReview.ajaxCall;
	}
	var usubmit = document.getElementById("mw-fr-submitunreview");
	if( usubmit ) {
		usubmit.onclick = wgAjaxReview.ajaxCall;
	}
};

hookEvent("load", wgAjaxReview.onLoad);
