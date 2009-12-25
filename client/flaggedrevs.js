/* -- (c) Aaron Schulz, Daniel Arnold 2008 */

/* Every time you change this JS please bump $wgFlaggedRevStyleVersion in FlaggedRevs.php */

var FlaggedRevs = {
	/* Hide rating clutter */
	'enableShowhide': function() {
		var toggle = document.getElementById('mw-revisiontoggle');
		if( !toggle ) return;
		toggle.style.display = 'inline';
		var ratings = document.getElementById('mw-revisionratings');
		if( !ratings ) return;
		ratings.style.display = 'none';
	},
	
	/* Toggles ratings */
	'toggleRevRatings': function() {
		var ratings = document.getElementById('mw-revisionratings');
		if( !ratings ) return;
		if( ratings.style.display == 'none' ) {
			ratings.style.display = 'inline';
		} else {
			ratings.style.display = 'none';
		}
	}
};

addOnloadHook(FlaggedRevs.enableShowhide);
