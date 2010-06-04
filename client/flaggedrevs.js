/* -- (c) Aaron Schulz, Daniel Arnold 2008 */

/* Every time you change this JS please bump $wgFlaggedRevStyleVersion in FlaggedRevs.php */

var FlaggedRevs = {
	'messages': {
		'diffToggleShow'	: '(show changes)',
		'diffToggleHide'	: '(hide changes)',
		'logToggleShow'		: '(show log)',
		'logToggleHide'		: '(hide log)',
		'logDetailsShow'	: '(show details)',
		'logDetailsHide'	: '(hide details)',
		'toggleShow'    	: '(+)',
		'toggleHide'    	: '(-)'
	},
	/* Hide rating/diff clutter */
	'enableShowhide': function() {
		// Rating detail box
		var toggle = document.getElementById('mw-fr-revisiontoggle');
		if( toggle ) {
			toggle.style.display = 'inline';
			var ratings = document.getElementById('mw-fr-revisiondetails');
			if( ratings ) {
				ratings.style.display = 'none';
			}
		}
		// Diff detail box
		toggle = document.getElementById('mw-fr-difftoggle');
		if( toggle ) {
			toggle.style.display = 'inline';
			var diff = document.getElementById('mw-fr-stablediff');
			if( diff ) {
				diff.style.display = 'none';
			}
		}
		// Log detail box
		toggle = document.getElementById('mw-fr-logtoggle');
		if( toggle ) {
			toggle.style.display = 'inline';
			var log = document.getElementById('mw-fr-logexcerpt');
			if( log ) {
				log.style.display = 'none';
			}
		}
	},
	
	/* Expands flag info box details */
	'showBoxDetails': function() {
		var ratings = document.getElementById('mw-fr-revisiondetails');
		if( !ratings ) return;
		var toggle = document.getElementById('mw-fr-revisiontoggle');
		if( !toggle ) return;
		ratings.style.display = 'block';
		toggle.innerHTML = this.messages.toggleHide;
	},
	
	/* Collapses flag info box details */
	'hideBoxDetails': function( event ) {
		var ratings = document.getElementById('mw-fr-revisiondetails');
		if( !ratings ) return;
		var toggle = document.getElementById('mw-fr-revisiontoggle');
		if( !toggle ) return;
		ratings.style.display = 'none';
		toggle.innerHTML = this.messages.toggleShow;
	},
	
	/* Toggles flag info box details */
	'toggleBoxDetails': function() {
		var ratings = document.getElementById('mw-fr-revisiondetails');
		if( !ratings ) return;
		// Collapsed -> expand
		if( ratings.style.display == 'none' ) {
			this.showBoxDetails();
		// Expanded -> collapse
		} else {
			this.hideBoxDetails();
		}
	},
	
	/* Hides flag info box details on mouseOut *except* for event bubbling */
	'onBoxMouseOut': function( event ) {
		if( !this.isMouseOutBubble( event, 'mw-fr-revisiontag' ) ) {
			this.hideBoxDetails();
		}
	},
	
	/* Checks is mouseOut event is for a child of parentId */
	'isMouseOutBubble': function( event, parentId ) {
		var toNode = null;
		if( event.relatedTarget === undefined ) {
            toNode = event.toElement; // IE
        } else {
            toNode = event.relatedTarget; // FF/Opera/Safari
        }
		if( toNode ) {
			var nextParent = toNode.parentNode;
			while( nextParent ) {
				if( nextParent.id == parentId ) {
					return true; // event bubbling
				}
				nextParent = nextParent.parentNode; // next up
			}
		}
		return false;
	},
	
	/* Toggles diffs */
	'toggleDiff': function() {
		var diff = document.getElementById('mw-fr-stablediff');
		if( !diff ) return;
		var toggle = document.getElementById('mw-fr-difftoggle');
		if( !toggle ) return;
		if( diff.style.display == 'none' ) {
			diff.style.display = 'block';
			toggle.getElementsByTagName('a')[0].innerHTML = this.messages.diffToggleHide;
		} else {
			diff.style.display = 'none';
			toggle.getElementsByTagName('a')[0].innerHTML = this.messages.diffToggleShow;
		}
	},
	
	/* Toggles log excerpts */
	'toggleLog': function() {
		var log = document.getElementById('mw-fr-logexcerpt');
		if( !log ) return;
		var toggle = document.getElementById('mw-fr-logtoggle');
		if( !toggle ) return;
		if( log.style.display == 'none' ) {
			log.style.display = 'block';
			toggle.getElementsByTagName('a')[0].innerHTML = this.messages.logToggleHide;
		} else {
			log.style.display = 'none';
			toggle.getElementsByTagName('a')[0].innerHTML = this.messages.logToggleShow;
		}
	},
	
	/* Toggles log excerpts */
	'toggleLogDetails': function() {
		var log = document.getElementById('mw-fr-logexcerpt');
		if( !log ) return;
		var toggle = document.getElementById('mw-fr-logtoggle');
		if( !toggle ) return;
		if( log.style.display == 'none' ) {
			log.style.display = 'block';
			toggle.getElementsByTagName('a')[0].innerHTML = this.messages.logDetailsHide;
		} else {
			log.style.display = 'none';
			toggle.getElementsByTagName('a')[0].innerHTML = this.messages.logDetailsShow;
		}
	}
};

FlaggedRevs.setCheckTrigger = function() {
	var checkbox = document.getElementById("wpReviewEdit");
	if( checkbox ) {
		checkbox.onclick = FlaggedRevs.updateSaveButton;
	}
}

FlaggedRevs.updateSaveButton = function() {
	var checkbox = document.getElementById("wpReviewEdit");
	var save = document.getElementById("wpSave");
	if( checkbox && save ) {
		// Review pending changes
		if ( checkbox.checked ) {
			save.value = FlaggedRevs.messages.saveArticle;
			save.title = FlaggedRevs.messages.tooltipSave;
		// Submit for review
		} else {
			save.value = FlaggedRevs.messages.submitArticle;
			save.title = FlaggedRevs.messages.tooltipSubmit;
		}
	}
}

FlaggedRevs.setJSTriggers = function() {
	FlaggedRevs.enableShowhide();
	FlaggedRevs.setCheckTrigger();
}

hookEvent("load", FlaggedRevs.setJSTriggers);
