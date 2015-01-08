(function(component, edit) {
	
	//if it's not edit mode
	if (!edit) {
		//show / hide menu
		var lastScrollTop, incr = 0;
		var menuSpace = 20, startsOn = 80, showsOn = 200;
		$(window).scroll(function(event){
			var st = $(this).scrollTop();
			var element = component.find('.menu.sticky');
			
			if (st > showsOn){
				//$('body').removeClass('spaceforsticky');
			}
			
			if (st > startsOn){
				if (st > lastScrollTop){
					incr++;
					if (incr >= menuSpace){
						$(element).addClass('hide');
						incr = menuSpace;
					}
				} else {
					incr -= 1;
					if (incr <= 5){
						$(element).removeClass('hide');
						incr = 0;
					}
				}
			} else {
				$(element).removeClass('hide');
			}
			lastScrollTop = st;
		});
	}
	window.responsiveMenu = function() {
		$('.designmodo-wrapper, #previewHolder').find('.menu').each(function(index, element) {
			
			var totalwidth = 0;
			
			//check for items
			if ( $(element).find('.items').exists() ) {
				var items = $(element).find('.items').width();
				totalwidth += items;
			}
			
			//check for button
			if ( $(element).find('.button').exists() ) {
				var button = $(element).find('.button').width();
				totalwidth += button;
			}
			
			//check for logo
			if ( $(element).find('.logo').exists() ) {
				var logo = $(element).find('.logo > *').width();
				totalwidth += logo;
			}
			
			//add compact class if menu not fits
			if ((totalwidth + 152) > $(window).width()){
				$(element).addClass('compact');
			} else {
				$(element).removeClass('compact');
			}
		});
	}
	responsiveMenu();
	
	//responsive magic
	$(window).on('resize load blur scroll', function(){
		responsiveMenu();
		if (component.find('.menu').exists()) {
			
			//add inner menus
			component.find('.menu .burger').each(function(i, menuButton) {
				
				var parentMenu = $(menuButton).parents('.menu');
				
				//generate new compactMenu
				if (!component.find('#cm-'+i).exists()){
					
					//check for content
					var items = "", button = "";
					
					if ( $(parentMenu).find('.sections .items').exists() ){
						var items = $(parentMenu).find('.items').parent().html();
					}
					
					if ( $(parentMenu).find('.sections .button').exists() ){
						var button = $(parentMenu).find('.button').parent().html();
					}
					
					$(parentMenu).after('<nav id="cm-'+i+'" class="compactMenu"><div class="table"><div class="cell">'  + button + items + '</div></div></nav>');
				}
				
				//remove window on orientation change
				$(window).on('resize onorientationchange', function(){
					component.find('.compactMenuVisible').removeClass('compactMenuVisible');
					$('html').removeClass('noscroll');
				});
				
				//compact menu toggle 
				$(menuButton).unbind().click(function(){
					if ( $('body').hasClass('edit-mode') && $(menuButton).closest('.designmodo-wrapper').length === 0 ) {
						var menu = $(this).parents('.menu');
						
						if ($('body').hasClass('compactMenuVisible')){
							$('body').removeClass('compactMenuVisible');
							$('html').removeClass('noscroll');
						} else {
							$('body').addClass('compactMenuVisible');
							component.find('.compactMenu').scrollTop(0);
							$('html').addClass('noscroll');
						}
					}
					else if ( !$('body').hasClass('edit-mode') && $(menuButton).closest('.designmodo-wrapper').length ) {
						var menu = $(this).parents('.menu');
						
						if ($('body').hasClass('compactMenuVisible')){
							$('body').removeClass('compactMenuVisible');
							$('html').removeClass('noscroll');
						} else {
							$('body').addClass('compactMenuVisible');
							component.find('.compactMenu').scrollTop(0);
							$('html').addClass('noscroll');
						}
					}
				});
				$(window).on('resize', function() {
					$('body').removeClass('compactMenuVisible');
					$('html').removeClass('noscroll');
				});
			});
		}
	});
	
});