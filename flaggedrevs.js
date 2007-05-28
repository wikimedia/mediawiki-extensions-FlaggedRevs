function toggleRevRatings() {
	var ratings = document.getElementById('mwrevisionratings');
	if( !ratings ) return;
	if( ratings.style.display == 'none' ) {
		ratings.style.display = 'block';
	} else {
		ratings.style.display = 'none';
	}
}