/**
 * FlaggedRevs Stylesheet
 * @author Aaron Schulz
 * @author Krinkle <krinklemail@gmail.com> 2011
 */

window.FlaggedRevs = {
	/* Dropdown collapse timer */
	'boxCollapseTimer': null,

	/* Startup function */
	'init': function() {
		// Enables rating detail box
		var toggle = $('#mw-fr-revisiontoggle');
		if ( toggle.length ) {
			toggle.css('display','inline'); /* show toggle control */
			FlaggedRevs.hideBoxDetails(); /* hide the initially displayed ratings */
		}
		// Bar UI: Toggle the box when the toggle is clicked
		$('.fr-toggle-symbol#mw-fr-revisiontoggle').click( FlaggedRevs.toggleBoxDetails );
		// Simple UI: Show the box on mouseOver
		$('.fr-toggle-arrow#mw-fr-revisiontoggle').mouseover( FlaggedRevs.onBoxMouseOver );
		$('.flaggedrevs_short#mw-fr-revisiontag').mouseout( FlaggedRevs.onBoxMouseOut );
		
		// Enables diff detail box and toggle
		toggle = $('#mw-fr-difftoggle');
		if ( toggle.length ) {
			toggle.css('display','inline'); /* show toggle control */
			$('#mw-fr-stablediff').hide();
		}
		toggle.children('a').click( FlaggedRevs.toggleDiff );
		
		// Enables log detail box and toggle
		toggle = $('#mw-fr-logtoggle');
		if ( toggle.length ) {
			toggle.css('display','inline'); /* show toggle control */
			$('#mw-fr-logexcerpt').hide();
		}
		toggle.children('a').click( FlaggedRevs.toggleLog );
		
		// Enables changing of save button when "review this" checkbox changes
		$('#wpReviewEdit').click( FlaggedRevs.updateSaveButton );
	},

	/* Expands flag info box details */
	'showBoxDetails': function() {
		$('#mw-fr-revisiondetails').css('display','block');
	},

	/* Collapses flag info box details */
	'hideBoxDetails': function( event ) {
		$('#mw-fr-revisiondetails').css('display','none');
	},

	/* Toggles flag info box details for (+/-) control */
	'toggleBoxDetails': function() {
		var toggle = $('#mw-fr-revisiontoggle');
		var ratings = $('#mw-fr-revisiondetails');
		if ( toggle.length && ratings.length ) {
			// Collapsed -> expand
			if ( ratings.css('display') == 'none' ) {
				FlaggedRevs.showBoxDetails();
				toggle.text( mw.msg('revreview-toggle-hide') );
			// Expanded -> collapse
			} else {
				FlaggedRevs.hideBoxDetails();
				toggle.text( mw.msg('revreview-toggle-show') );
			}
		}
	},

	/* Expands flag info box details on mouseOver */
	'onBoxMouseOver': function( event ) {
		window.clearTimeout( FlaggedRevs.boxCollapseTimer );
		FlaggedRevs.boxCollapseTimer = null;
		FlaggedRevs.showBoxDetails();
	},

	/* Hides flag info box details on mouseOut *except* for event bubbling */
	'onBoxMouseOut': function( event ) {
		if ( !FlaggedRevs.isMouseOutBubble( event, 'mw-fr-revisiontag' ) ) {
			FlaggedRevs.boxCollapseTimer = window.setTimeout( FlaggedRevs.hideBoxDetails, 150 );
		}
	},

	/* Checks if mouseOut event is for a child of parentId */
	'isMouseOutBubble': function( event, parentId ) {
		var toNode = null;
		if ( event.relatedTarget !== undefined ) {
			toNode = event.relatedTarget; // FF/Opera/Safari
		} else {
			toNode = event.toElement; // IE
		}
		if ( toNode ) {
			var nextParent = toNode.parentNode;
			while ( nextParent ) {
				if ( nextParent.id == parentId ) {
					return true; // event bubbling
				}
				nextParent = nextParent.parentNode; // next up
			}
		}
		return false;
	},

	/* Toggles diffs */
	'toggleDiff': function() {
		var diff = $('#mw-fr-stablediff');
		var toggle = $('#mw-fr-difftoggle');
		if ( diff.length && toggle.length ) {
			if ( diff.css('display') == 'none' ) {
				diff.show( 'slow' );
				toggle.children('a').text( mw.msg('revreview-diff-toggle-hide') );
			} else {
				diff.hide( 'slow' );
				toggle.children('a').text( mw.msg('revreview-diff-toggle-show') );
			}
		}
	},

	/* Toggles log excerpts */
	'toggleLog': function() {
		var log = $('#mw-fr-logexcerpt');
		var toggle = $('#mw-fr-logtoggle');
		if ( log.length && toggle.length ) {
			// Two different message sets used here...
			if ( toggle.hasClass('fr-logtoggle-details') ) {
				var hideMsg = mw.msg('revreview-log-details-hide');
				var showMsg = mw.msg('revreview-log-details-show');
			} else {
				var hideMsg = mw.msg('revreview-log-toggle-hide');
				var showMsg = mw.msg('revreview-log-toggle-show');
			}
			if ( log.css('display') == 'none' ) {
				log.show();
				toggle.children('a').text( hideMsg );
			} else {
				log.hide();
				toggle.children('a').text( showMsg );
			}
		}
	},

	/* Update save button when "review this" checkbox changes */
	'updateSaveButton': function() {
		var save = $('#wpSave');
		var checkbox = $('#wpReviewEdit');
		if ( save.length && checkbox.length ) {
			// Review pending changes
			if ( checkbox.attr('checked') ) {
				save.val( mw.msg('savearticle') );
				save.attr( 'title',
					mw.msg('tooltip-save') + ' [' + mw.msg('accesskey-save') + ']' );
			// Submit for review
			} else {
				save.val( mw.msg('revreview-submitedit') );
				save.attr( 'title',
					mw.msg('revreview-submitedit-title') + ' [' + mw.msg('accesskey-save') + ']' );
			}
		}
		mw.util.updateTooltipAccessKeys( [ save ] ); // update accesskey in save.title
	}
};

// Perform some onload (which is when this script is included) events:
FlaggedRevs.init();
