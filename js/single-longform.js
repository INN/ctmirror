// scroll-based actions
var win_height;

function setStickyNav(scrollY){
	var $ = jQuery;
	// sticky nav
	var nav = $('#site-navigation');
	var logged_in = $('body').hasClass('logged-in');
	var height_check;

	if (logged_in) {
		height_check = win_height-134;
	} else {
		height_check = win_height-108;
	}
	if(scrollY >= height_check) {
		nav.show();
	} else {
		nav.hide();
	}

}

jQuery(document).ready(function(){
	var $ = jQuery;
	var iframes = $('iframe');

	iframes.each(function(){
		var $this = $(this);
		var spacer = '<div class="spacer" style="height:' + $this.height() + 'px;"></div>'
		$this.after(spacer);
	})

	setStickyNav(window.scrollY);
});

jQuery(window).resize(function(){
	win_height = jQuery(window).height();
	setStickyNav(jQuery);
});

var latestKnownScrollY = 0,
	ticking = false;

function onScroll() {
	latestKnownScrollY = window.scrollY;
	requestTick();
}

function requestTick() {
	if(!ticking) {
		requestAnimationFrame(update);
	}
	ticking = true;
}

function update() {
	// reset the tick so we can
	// capture the next onScroll
	ticking = false;

	var currentScrollY = latestKnownScrollY;

	//backgroundCheck(currentScrollY);
	setStickyNav(currentScrollY);
}

window.addEventListener('scroll', onScroll, false);
