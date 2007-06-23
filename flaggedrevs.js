function toggleRevRatings() {
	var ratings = document.getElementById('mwrevisionratings');
	if( !ratings ) return;
	if( ratings.style.display == 'none' ) {
		ratings.style.display = 'block';
	} else {
		ratings.style.display = 'none';
	}
}

function hookAnyEvent(hookName, hookFunct, element) {
        if(!element){
           var element = window;
        } 
	if (element.addEventListener) {
		element.addEventListener(hookName, hookFunct, false);
	} else if (element.attachEvent) {
		element.attachEvent("on" + hookName, hookFunct);
	}
}

var open_review_listen = function(evt){
   var review_closed = document.getElementById('mwrevisiontag_closed');
   if( !review_closed ) return;
   hookAnyEvent("click", open_the_review, review_closed);
}

var open_the_review = function(evt){
   var review_open = document.getElementById('mwrevisiontag_open');
   review_open.style.display = 'block';
   var review_closed = document.getElementById('mwrevisiontag_closed');
   review_closed.style.display = 'none';
   var close_review = document.getElementById('close_review');
   if(!close_review){
       var close_review = document.createElement('div');
       close_review.id ="close_review";
       close_review.innerHTML = 'X';
       review_open.insertBefore(close_review, review_open.firstChild);
       hookAnyEvent("click", close_the_review, close_review);
   }
}
var close_the_review = function(evt){
   var review_open = document.getElementById('mwrevisiontag_open');
   review_open.style.display = 'none';
   var review_closed = document.getElementById('mwrevisiontag_closed');
   review_closed.style.display = 'block';
}

addOnloadHook(open_review_listen);
