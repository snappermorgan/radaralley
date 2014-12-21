(function($) {
  "use strict";
	jQuery(window).scroll(function(){
		animate_block();
	});
	
	jQuery(document).ready(function() {
		animate_block();
	});

	// CSS3 Transitions.
	function animate_block(){
		jQuery('.ult-animation').each(function(){
			if(jQuery(this).attr('data-animate')) {
				//var child = jQuery(this).children('div');
				var child2 = jQuery(this).children('*');
				//var child = jQuery('.ult-animation > *');
				//console.log(child);
				var animationName = jQuery(this).attr('data-animate'),
					animationDuration = jQuery(this).attr('data-animation-duration')+'s',
					animationIteration = jQuery(this).attr('data-animation-iteration'),
					animationDelay = jQuery(this).attr('data-animation-delay');
				var style = 'opacity:1;-webkit-animation-delay:'+animationDelay+'s;-webkit-animation-duration:'+animationDuration+';-webkit-animation-iteration-count:'+animationIteration+';';
				var container_style = 'opacity:1;-webkit-transition-delay: '+(animationDelay)+'s; -moz-transition-delay: '+(animationDelay)+'s; transition-delay: '+(animationDelay)+'s;';
				if(isAppear(jQuery(this))){
					var p_st = jQuery(this).attr('style');
					if(typeof(p_st) == 'undefined'){
						p_st = 'test';
					}
					if(p_st == 'opacity:0;'){
						if( p_st.indexOf(container_style) !== 0 ){
							jQuery(this).attr('style',container_style);
						}
					}
				}
				//jQuery(this).bsf_appear(function() {
				jQuery.each(child2,function(index,value){
					var $this = jQuery(value);
					var prev_style = $this.attr('style');
					if(typeof(prev_style) == 'undefined'){
						prev_style = 'test';
					}
					var new_style = '';
					if( prev_style.indexOf(style) == 0 ){
						new_style = prev_style;
					} else {
						new_style = style+prev_style;
					}
					$this.attr('style',new_style);
					if(isAppear($this)){
						$this.addClass('animated').addClass(animationName);
					}
				});
			} 
		});
	}

	function isAppear(id){
		var win = jQuery(window);
		var viewport = {
			top : win.scrollTop(),
			left : win.scrollLeft()
		};
		var productHeight = jQuery(id).outerHeight()-80;
		viewport.right = viewport.left + win.width();
		viewport.bottom = viewport.top + win.height() - productHeight;
		var bounds = jQuery(id).offset();
		bounds.right = bounds.left + jQuery(id).outerWidth();
		bounds.bottom = bounds.top + jQuery(id).outerHeight();
		return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));
	};

})(jQuery);
//ready