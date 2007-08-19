function toggleRevRatings() {
	var ratings = document.getElementById('mw-revisionratings');
	if( !ratings ) return;
	if( ratings.style.display == 'none' ) {
		ratings.style.display = 'block';
	} else {
		ratings.style.display = 'none';
	}
}

function enable_showhide() {
	var toggle = document.getElementById('mw-revisiontoggle');
	if( !toggle ) return;
	toggle.style.display = '';
	var ratings = document.getElementById('mw-revisionratings');
	if( !ratings ) return;
	ratings.style.display = 'none';
}

addOnloadHook(enable_showhide);
