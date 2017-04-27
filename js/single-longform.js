(function () {

	// scroll-based actions
	var $ = jQuery;
	var win_height;

	function setStickyNav(scrollY){
		var $ = jQuery;
		// sticky nav
		var nav = $('#site-navigation.lf-sticky');
		if(scrollY >= win_height) {
			nav.show();
		} else {
			nav.hide();
		}

	}

	function setEmbeds(){
		var embeds = $('.full-width-visual');
		embeds.each(function(){
			var $this = $(this);
			var spacer = '<div class="spacer" style="height:' + $this.height() + 'px;"></div>';
			$this.nextAll('.spacer').first().remove();
			$this.after(spacer);
		});
	}

	function stripInlineStyles(){
		//$('.module.image').attr('style','');
		$('img.size-full').parent().attr('style','');
	}

	function adjustFullImages(){
		var full_width = $('#main').width();
		var imgs = $('img.size-full');

		imgs.each(function(){
			var $this = $(this);
			var $parent = $this.parent()
			$this.attr('width','100%');
			$this.attr('height','');
			var margin = 0;

			var win_width = $(window).width();
			var content_width = $('#content').width();

			if (win_width>1170) {
				margin = -(full_width - content_width)/2;
			} else if (win_width>550) {
				margin = -(win_width - content_width)/2;
			} else {
				margin = 0;
			}
			$parent.css({
				'width': full_width,
				'margin-left': margin
			});
			$parent.find('.wp-caption-text').css('max-width',content_width);
		})
		
	}

	jQuery(document).ready(function(){
		win_height = jQuery(window).height();
		setStickyNav(window.scrollY);
		setEmbeds();
		stripInlineStyles();
		adjustFullImages();
	});

	jQuery(window).resize(function(){
		win_height = jQuery(window).height();
		setStickyNav(window.scrollY);
		setEmbeds();
		adjustFullImages();
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

})();
