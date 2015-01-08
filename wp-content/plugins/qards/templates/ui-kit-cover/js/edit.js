(function(component, edit) {
	//COVER 
	if (component.find('.cover').exists()) {

			
		//resize covers
		$(window).on('resizeEnd load blur', function() {
			resizeCover();
		});
		$('body').on('keyup', '[contenteditable]', function() {
			resizeCover();
		});
		function resizeCover() {
			if ( component.find('.container').length ) {
				var paddingTop = component.find('.holder').css('padding-top').replace('px', '');
			}
			var contentHeight = component.find('.content').outerHeight();

			component.find('.cover').css({minHeight: contentHeight + (paddingTop * 2)});
		}
		
		//if it's not edit mode
		if (!edit) {
			// EFFECTS FOR COVER
			if (!window.isMobile) {
				if (component.find('.parallax').exists()) {
					//prepare
					$('.background').wrap('<div class="background-wrapper"/>')
					var once = 0;
					$(window).on('scroll load', function() {
						component.find('.parallax').each(function(index, element) {

							var elementsPosition = $(element).offset();
							var scrollTop = $(document).scrollTop();
							var windowHeight = $(window).height();
							var scale = (scrollTop - elementsPosition.top) / windowHeight;
							var propotion = windowHeight / 10;
							var contentElement = $(element).find('.content');

							if ((scale > -1) && (scale < 1)) {
								//paralax effect on scroll
								var textPosition = (propotion * (scrollTop - elementsPosition.top) / windowHeight);
								$(contentElement).css('-webkit-transform', 'translateY(' + textPosition + 'px)')
									.css('-moz-transform', 'translateY(' + textPosition + 'px)')
									.css('-o-transform', 'translateY(' + textPosition + 'px)')
									.css('transform', 'translateY(' + textPosition + 'px)')
									.css('opacity', 1 - scale * 0.5);
								if (scale > 0) {
									$(element).find('.background-wrapper').css('opacity', Math.abs(scale - 1));
									$(contentElement).css('opacity', Math.abs(scale - 1));
								} else {
									$(element).find('.background-wrapper').css('opacity', 1 - Math.abs(scale));
									$(contentElement).css('opacity', 1 - Math.abs(scale));
								}
							}

						});
					});
				}
			} else {
				//parallax for mobile only
				if (window.DeviceMotionEvent!=undefined) {
					window.ondevicemotion = function(event) {
						ax = event.accelerationIncludingGravity.x
						ay = event.accelerationIncludingGravity.y
						az = event.accelerationIncludingGravity.z
						rotation = event.rotationRate;
						if (rotation != null) {
							arAlpha = Math.round(rotation.alpha);
							arBeta = Math.round(rotation.beta);
							arGamma = Math.round(rotation.gamma);
						}
					}
					
					window.ondeviceorientation = function(event) {
						alpha = Math.round(event.alpha);
						beta = Math.round(event.beta);
						gamma = Math.round(event.gamma);
						component.find('.background').css("-webkit-transform",'translateX(' + (gamma*.2)+"px) scale(1.2)");
					}
				}
			}
		}
	}
});

function preventDefault(e) {
  e = e || window.event;
  if (e.preventDefault)
      e.preventDefault();
  e.returnValue = false;  
}