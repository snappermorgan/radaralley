(function($){

	var tourPointerTargets = [];

	function windowLoad() {
		$.each(wpzlbAdminPointers, function(id,args){
			if ( !args.hasOwnProperty('target') || !args.hasOwnProperty('content') ) return;

			var $target = $(args.target + '').first();

			if ( args.hasOwnProperty('classes') && /wpzlb-pointer-tour/i.test(args.classes) ) tourPointerTargets.push($target);

			$target.pointer({
				pointerClass: 'wp-pointer wpzlb-pointer' + ( args.hasOwnProperty('classes') ? ' ' + $.trim( args.classes + '' ) : '' ),
				content: args.content + '',
				position: ( args.hasOwnProperty('position') ? args.position : 'top' ),
				buttons: function(event,t){
					if ( t.pointer.hasClass('wpzlb-pointer-tour') ) {
						if ( t.pointer.hasClass('wpzlb-pointer-tour-start') ) {
							var $startBtn = $('<a class="start" href="#">' + wpzlbAdminPointersL10n.tourStart + '</a>'),
							    $skipBtn = $('<a class="skip" href="#">' + wpzlbAdminPointersL10n.tourSkip + '</a>');

							$startBtn.bind('click.pointer', function(e){
								e.preventDefault();
								var $step1 = t.pointer.fadeOut(300).siblings('.wpzlb-pointer-tour-step-1').fadeIn(300);
								centerElementInViewport($step1);
							});

							$skipBtn.bind('click.pointer', function(e){
								e.preventDefault();
								$(this).add($startBtn).blur().css({ pointerEvents: 'none', opacity: 0.5 });
								$.each(tourPointerTargets, function(){ $(this).pointer('close'); });
							});

							return $('<div class="wp-pointer-buttons-inner"/>').append($startBtn, $skipBtn);
						} else if ( t.pointer.hasClass('wpzlb-pointer-tour-end') ) {
							var $endBtn = $('<a class="end" href="#">' + wpzlbAdminPointersL10n.tourEnd + '</a>');

							$endBtn.bind('click.pointer', function(e){
								e.preventDefault();
								$(this).blur().css({ pointerEvents: 'none', opacity: 0.5 });
								$.each(tourPointerTargets, function(){ $(this).pointer('close'); });
							});

							return $endBtn;
						} else {
							var $nextBtn = $('<a class="next" href="#">' + wpzlbAdminPointersL10n.tourNext + '</a>');

							$nextBtn.bind('click.pointer', function(e){
								e.preventDefault();
								var matches = t.pointer.attr('class').match(/wpzlb-pointer-tour-step-([0-9]+)/i);
								if ( matches != null && typeof matches[1] != 'undefined' && t.pointer.siblings( '.wpzlb-pointer-tour-step-' + ( parseInt( matches[1] , 10 ) + 1 ) ).length > 0 ) {
									var $nextStep = t.pointer.fadeOut(300).siblings( '.wpzlb-pointer-tour-step-' + ( parseInt( matches[1] , 10 ) + 1 ) ).fadeIn(300);
									centerElementInViewport($nextStep);
								} else {
									var $end = t.pointer.fadeOut(300).siblings('.wpzlb-pointer-tour-end').fadeIn(300);
									centerElementInViewport($end);
								}
							});

							return $nextBtn;
						}
					} else {
						return $('<a class="close" href="#">' + ( wpPointerL10n ? wpPointerL10n.dismiss : 'Dismiss' ) + '</a>').bind('click.pointer', function(e){
							e.preventDefault();
							t.element.pointer('close');
						});
					}
				},
				close: function(){ $.ajax({ type: 'POST', url: ajaxurl, async: false, data: { pointer: id + '', action: 'dismiss-wp-pointer' } }); }
			}).pointer('open');

			if ( args.hasOwnProperty('classes') && /wpzlb-pointer-tour/i.test(args.classes) && /wpzlb-pointer-tour-start/i.test(args.classes) === false && typeof $target.data('wpPointer') !== 'undefined' ) {
				$target.data('wpPointer').pointer.hide();
			}

			$(window).on('resize', function(){ updatePointerPosition($target); });
			$('#collapse-menu').on('click.collapse-menu', function(){ updatePointerPosition($target); });
		});
	}

	function updatePointerPosition( $target ) {
		if ( typeof $target.data('wpPointer') === 'undefined' ) return;
		var isHidden = $target.data('wpPointer').pointer.is(':hidden');
		$target.pointer('reposition');
		if ( isHidden ) $target.data('wpPointer').pointer.hide();
	}

	function isElementInViewport( el ) {
		var rect = el.getBoundingClientRect();

		return (
			rect.top >= 0 &&
			rect.left >= 0 &&
			rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
			rect.right <= (window.innerWidth || document.documentElement.clientWidth)
		);
	}

	function centerElementInViewport( $element ) {
		if ( !isElementInViewport( $element[0] ) ) {
			$('html, body').animate({ scrollTop: ( $element.offset().top + ($element.height()/2) - ($(window).height()/2) ) }, 1000);
		}
	}

	$(function(){

		$(window).on('load.wp-pointers', windowLoad);

	});

})(jQuery);