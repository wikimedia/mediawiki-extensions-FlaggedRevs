/**
 * FlaggedRevs Stylesheet
 * @author Aaron Schulz
 * @author Daniel Arnold 2008
 */

window.FlaggedRevsReview = {
	/*
	* Updates for radios/checkboxes on patch by Daniel Arnold (bug 13744).
	* Visually update the revision rating form on change.
	* - Disable submit in case of invalid input.
	* - Update colors when <select> changes.
	* - Also remove comment box clutter in case of invalid input.
	* NOTE: all buttons should exist (perhaps hidden though)
	*/
	'updateRatingForm': function() {
		var ratingform = document.getElementById('mw-fr-ratingselects');
		if( !ratingform ) return;
		var disabled = document.getElementById('fr-rating-controls-disabled');
		if( disabled ) return;
		
		var quality = true;
		var somezero = false;
		
		// Determine if this is a "quality" or "incomplete" review
		for( tag in wgFlaggedRevsParams.tags ) {
			var controlName = "wp" + tag;
			var levels = document.getElementsByName(controlName);
			if( !levels.length ) continue;
		
			var selectedlevel = 0; // default
			if( levels[0].nodeName == 'SELECT' ) {
				selectedlevel = levels[0].selectedIndex;
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
			qualityLevel = wgFlaggedRevsParams.tags[tag]['quality'];
			
			if( selectedlevel < qualityLevel ) {
				quality = false; // not a quality review
			}
			if( selectedlevel <= 0 ) {
				somezero = true;
			}
		}
	
		// (a) If only a few levels are zero ("incomplete") then disable submission.
		// (b) Re-enable submission for already accepted revs when ratings change.
		var asubmit = document.getElementById('mw-fr-submit-accept');
		if( asubmit ) {
			asubmit.disabled = somezero ? 'disabled' : '';
			asubmit.value = mediaWiki.msg('revreview-submit-review'); // reset to "Accept"
		}
	
		// Update colors of <select>
		FlaggedRevsReview.updateRatingFormColors( ratingform );
	},
	
	/*
	* Update <select> color for the selected item
	*/
	'updateRatingFormColors': function() {
		for( tag in wgFlaggedRevsParams.tags ) {
			var controlName = "wp" + tag;
			var levels = document.getElementsByName(controlName);
			if( levels.length && levels[0].nodeName == 'SELECT' ) {
				selectedlevel = levels[0].selectedIndex;
				value = levels[0].getElementsByTagName('option')[selectedlevel].value;
				levels[0].className = 'fr-rating-option-' + value;
			}
		}
	},
	
	/*
	* Disable 'accept' button if the revision was already reviewed
	* NOTE: this is used so that they can be re-enabled if a rating changes
	*/
	'maybeDisableAcceptButton': function() {
		if( typeof(jsReviewNeedsChange) != 'undefined' && jsReviewNeedsChange == 1 ) {
			var asubmit = document.getElementById('mw-fr-submit-accept');
			if( asubmit ) {
				asubmit.disabled = 'disabled';
			}
		}
	},
	
	/*
	* Enable AJAX-based submit functionality to the review form on this page
	*/
	'enableAjaxReview': function() {
		var asubmit = document.getElementById("mw-fr-submit-accept");
		if( asubmit ) {
			asubmit.onclick = FlaggedRevsReview.submitRevisionReview;
		}
		var usubmit = document.getElementById("mw-fr-submit-unaccept");
		if( usubmit ) {
			usubmit.onclick = FlaggedRevsReview.submitRevisionReview;
		}
	},
	
	/*
	* Lock review form from submissions (using during AJAX requests)
	*/
	'lockReviewForm': function( form ) {
		var inputs = form.getElementsByTagName("input");
		for( var i=0; i < inputs.length; i++) {
			inputs[i].disabled = "disabled";
		}
		var textareas = document.getElementsByTagName("textarea");
		for( var i=0; i < textareas.length; i++) {
			textareas[i].disabled = "disabled";
		}
		var selects = form.getElementsByTagName("select");
		for( var i=0; i < selects.length; i++) {
			selects[i].disabled = "disabled";
		}
	},
	
	/*
	* Unlock review form from submissions (using after AJAX requests)
	*/	
	'unlockReviewForm': function( form ) {
		var inputs = form.getElementsByTagName("input");
		for( var i=0; i < inputs.length; i++) {
			if( inputs[i].type != 'submit' ) { // not all buttons can be enabled
				inputs[i].disabled = "";
			} else {
				inputs[i].blur(); // focus off element (bug 24013)
			}
		}
		var textareas = document.getElementsByTagName("textarea");
		for( var i=0; i < textareas.length; i++) {
			textareas[i].disabled = "";
		}
		var selects = form.getElementsByTagName("select");
		for( var i=0; i < selects.length; i++) {
			selects[i].disabled = "";
		}
	},
	
	/*
	* Submit a revision review via AJAX and update form elements.
	*
	* Note: requestArgs build-up from radios/checkboxes
	* based on patch by Daniel Arnold (bug 13744)
	*/
	'submitRevisionReview': function() {
		var form = document.getElementById("mw-fr-reviewform");
		if( !form ) {
			return true; // do normal behavoir (shouldn't happen)
		}
		FlaggedRevsReview.lockReviewForm( form ); // disallow submissions
		
		var notes = document.getElementById("wpNotes");
		// Build up arguments array and update submit button text...
		var requestArgs = []; // array of strings of the format <"pname|pval">.
		var inputs = form.getElementsByTagName("input");
		for( var i=0; i < inputs.length; i++) {
			// Different input types may occur depending on tags...
			if( inputs[i].name == "title" || inputs[i].name == "action" ) {
				continue; // No need to send these...
			} else if( inputs[i].type == "submit" ) {
				if( inputs[i].id == this.id ) {
					requestArgs.push( inputs[i].name + "|1" );
					// Show that we are submitting via this button
					inputs[i].value = mediaWiki.msg('revreview-submitting');
				}
			} else if( inputs[i].type == "checkbox" ) {
				requestArgs.push( inputs[i].name + "|" +
					(inputs[i].checked ? inputs[i].value : 0) );
			} else if( inputs[i].type == "radio" ) {
				if( inputs[i].checked ) { // must be checked
					requestArgs.push( inputs[i].name + "|" + inputs[i].value );
				}
			} else {
				requestArgs.push( inputs[i].name + "|" + inputs[i].value ); // text/hiddens...
			}
		}
		if( notes ) {
			requestArgs.push( notes.name + "|" + notes.value );
		}
		var selects = form.getElementsByTagName("select");
		for( var i=0; i < selects.length; i++) {
			// Get the selected tag level...
			if( selects[i].selectedIndex >= 0 ) {
				var soption = selects[i].getElementsByTagName("option")[selects[i].selectedIndex];
				requestArgs.push( selects[i].name + "|" + soption.value );
			}
		}
		// Send encoded function plus all arguments...
		post_data = 'action=ajax&rs=RevisionReview::AjaxReview';
		for( var i=0; i<requestArgs.length; i++ ) {
			post_data += '&rsargs[]=' + encodeURIComponent( requestArgs[i] );
		}
		// Send POST request via AJAX!
		var call = jQuery.ajax({
			url		: wgScriptPath + '/index.php',
			type	: "POST",
			data	: post_data,
			dataType: "html", // response type
			success	: function( response ) {
				FlaggedRevsReview.updateReviewForm( form, response ); },
			error	: function( response ) {
				FlaggedRevsReview.unlockReviewForm( form ); }
		});
		return false; // don't do normal non-AJAX submit
	},
	
	/*
	* Update form elements after AJAX review.
	*/
	'updateReviewForm': function( form, response ) {
		var msg = response.substr(6); // remove <err#> or <suc#>
		// Read new "last change time" timestamp for conflict handling
		// @TODO: pass last-chage-time data using JSON or something not retarded
		var m = msg.match(/^<lct#(\d*)>(.*)/m);
		if( m ) msg = m[2]; // remove tag from msg
		var changeTime = m ? m[1] : null; // MW TS
		// Some form elements...
		var asubmit = document.getElementById('mw-fr-submit-accept');
		var usubmit = document.getElementById('mw-fr-submit-unaccept');
		var legend = document.getElementById('mw-fr-reviewformlegend');
		var diffNotice = document.getElementById('mw-fr-difftostable');
		var tagBox = document.getElementById('mw-fr-revisiontag');
		// On success...
		if( response.indexOf('<suc#>') == 0 ) {
			// (a) Update document title and form buttons...
			document.title = mediaWiki.msg('actioncomplete');
			if( asubmit && usubmit ) {
				// Revision was flagged
				if( asubmit.value == mediaWiki.msg('revreview-submitting') ) {
					asubmit.value = mediaWiki.msg('revreview-submit-reviewed'); // done!
					asubmit.style.fontWeight = 'bold';
					// Unlock and reset *unflag* button
					usubmit.value = mediaWiki.msg('revreview-submit-unreview');
					usubmit.removeAttribute( 'style' ); // back to normal
					usubmit.disabled = '';
				// Revision was unflagged
				} else if( usubmit.value == mediaWiki.msg('revreview-submitting') ) {
					usubmit.value = mediaWiki.msg('revreview-submit-unreviewed'); // done!
					usubmit.style.fontWeight = 'bold';
					// Unlock and reset *flag* button
					asubmit.value = mediaWiki.msg('revreview-submit-review');
					asubmit.removeAttribute( 'style' ); // back to normal
					asubmit.disabled = '';
				}
			}
			// (b) Remove review tag from drafts
			if( tagBox ) tagBox.style.display = 'none';
			// (c) Update diff-related items...
			var diffUIParams = document.getElementById('mw-fr-diff-dataform');
			if( diffUIParams ) {
				// Hide "review this" box on diffs
				if( diffNotice ) diffNotice.style.display = 'none';
				// Update the contents of the mw-fr-diff-headeritems div
				var requestArgs = []; // <oldid, newid>
				requestArgs.push( diffUIParams.getElementsByTagName('input')[0].value );
				requestArgs.push( diffUIParams.getElementsByTagName('input')[1].value );
				// Send encoded function plus all arguments...
				url_pars = '?action=ajax&rs=FlaggedPageView::AjaxBuildDiffHeaderItems';
				for( var i=0; i<requestArgs.length; i++ ) {
					url_pars += '&rsargs[]=' + encodeURIComponent(requestArgs[i]);
				}
				// Send GET request via AJAX!
				var call = jQuery.ajax({
					url		: wgScriptPath + '/index.php' + url_pars,
					type	: "GET",
					dataType: "html", // response type
					success	: FlaggedRevsReview.processDiffHeaderItemsResult
				});
			}
		// On failure...
		} else {
			// (a) Update document title and form buttons...
			document.title = mediaWiki.msg('actionfailed');
			if( asubmit && usubmit ) {
				// Revision was flagged
				if( asubmit.value == mediaWiki.msg('revreview-submitting') ) {
					asubmit.value = mediaWiki.msg('revreview-submit-review'); // back to normal
					asubmit.disabled = ''; // unlock flag button
				// Revision was unflagged
				} else if( usubmit.value == mediaWiki.msg('revreview-submitting') ) {
					usubmit.value = mediaWiki.msg('revreview-submit-unreview'); // back to normal
					usubmit.disabled = ''; // unlock
				}
			}
			// (b) Output any error response message
			if( response.indexOf('<err#>') == 0 ) {
				mediaWiki.util.jsMessage( msg, 'review' ); // failure notice
			} else {
				mediaWiki.util.jsMessage( response, 'review' ); // fatal notice
			}
			window.scroll(0,0); // scroll up to notice
		}
		// Update changetime for conflict handling
		if( changeTime != null ) {
			document.getElementById('mw-fr-input-changetime').value = changeTime;
		}
		FlaggedRevsReview.unlockReviewForm( form );
	},
	
	// update the contents of the mw-fr-diff-headeritems div
	'processDiffHeaderItemsResult': function( response ) {
		var diffHeaderItems = document.getElementById("mw-fr-diff-headeritems");
		if( diffHeaderItems && response != '' ) {
			diffHeaderItems.innerHTML = response;
		}
	},
	
	/*
	* Flag users as "no longer reviewing"
	*/
	'deadvertiseReviewing': function() {
		var form = document.getElementById('mw-fr-reviewform');
		if( form && jsReviewingStatus ) {
			var oRevId = document.getElementById('mw-fr-input-refid').value;
			var nRevId = document.getElementById('mw-fr-input-oldid').value;
		} else if( location.href.indexOf('&reviewing=1') != -1 ) {
			var oRevId = 0;
			var nRevId = mw.config.get('wgCurRevisionId');
		}
		if ( nRevId > 0 ) {
			// Send GET request via AJAX!
			var call = jQuery.ajax({
				url		: wgScriptPath + '/api.php',
				data	: {
					action		: 'reviewactivity',
					previd		: oRevId,
					oldid		: nRevId,
					reviewing	: 0
				},
				type	: "POST",
				dataType: "html", // response type
				timeout : 2000, // don't delay user exiting
				async	: false
			});
		}
		return;
	}
};

// Perform some onload (which is when this script is included) events:
FlaggedRevsReview.maybeDisableAcceptButton();
FlaggedRevsReview.updateRatingFormColors();
FlaggedRevsReview.enableAjaxReview();

// Flag users as "no longer reviewing" on navigate-away
window.onbeforeunload = FlaggedRevsReview.deadvertiseReviewing;
