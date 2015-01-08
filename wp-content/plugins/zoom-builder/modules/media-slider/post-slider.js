jQuery(function($){

	var frame = wp.media({
		title: 'Pick an Image for This Slide',
		multiple: false,
		library: { type: 'image' },
		button: { text: 'Use This Image' }
	});

	frame.on('open', function(){
		if ( typeof frame.currID == 'undefined' || isNaN(frame.currID) || frame.currID < 1 ) return;

		var selection = frame.state().get('selection');
		attachment = wp.media.attachment(frame.currID);
		attachment.fetch();
		selection.add( attachment ? [ attachment ] : [] );

		return;
	});

	frame.on('close', function(){
		if ( !('currSlide' in frame) ) return;

		var $thisSlide = $('#' + frame.currSlide),
		    selection = frame.state().get('selection').first(),
		    attr = typeof selection != 'undefined' && typeof selection.attributes != 'undefined' ? selection.attributes : {},
		    id = 'id' in attr && !isNaN(attr.id) && attr.id > 0 ? attr.id : '',
		    url = 'sizes' in attr && 'medium' in attr.sizes && 'url' in attr.sizes.medium ? $.trim(attr.sizes.medium.url) : '';

		if ( url == '' ) url = 'sizes' in attr && 'full' in attr.sizes && 'url' in attr.sizes.full ? $.trim(attr.sizes.full.url) : '';

		$('.wpzoom_slide_upload_image', $thisSlide).val(id);
		$('.wpzoom_slide_preview_image', $thisSlide).attr('src', url != '' ? url : $('.wpzoom_slide_preview_image', $thisSlide).data('defaultimg'));

		$('.wpzoom_slide_clear_image_button', $thisSlide).removeClass('button-disabled');
		if ( $.trim($('.wpzoom_slide_upload_image', $thisSlide).val()) == '' && !$('.wpzoom_slide_clear_image_button', $thisSlide).hasClass('button-disabled') )
			$('.wpzoom_slide_clear_image_button', $thisSlide).addClass('button-disabled');

		frame.reset();

		return;
	});

	$('.wpzoom_slide_upload_image_button').click(function(e){
		e.preventDefault();

		var id = parseInt($(this).prev('.wpzoom_slide_upload_image').val());
		frame.currID = !isNaN(id) && id > 0 ? id : 0;
		frame.currSlide = $(this).closest('li').attr('id');

		frame.open();

		return;
	});

	$('.wpzoom_slide_clear_image_button').click(function(e){
		e.preventDefault();

		if ( $(this).hasClass('button-disabled') ) return;

		$('.wpzoom_slide_upload_image', $(this).closest('.wpzoom_slide_preview')).val('');
		$('.wpzoom_slide_preview_image', $(this).closest('.wpzoom_slide_preview')).attr('src', $('.wpzoom_slide_preview_image', $(this).closest('.wpzoom_slide_preview')).data('defaultimg'));

		if ( !$(this).hasClass('button-disabled') ) $(this).addClass('button-disabled');

		return;
	});
	
	var wpzSlideEmbedInputTimeout,
	    wpzValidIframeRegex = /<iframe[^>]* src="[^"]+"[^>]*><\/iframe>/i; // This isn't super strict... It just loosely checks to see if the string kinda looks like it contains an embed code.

	$('.wpzoom_slide_embed_code').on('input', function(){
		clearTimeout(wpzSlideEmbedInputTimeout);

		var thisVal = $(this).val(),
		    $thisParent = $(this).closest('.wpzoom_slide_preview');

		if ( $.trim(thisVal) != '' && wpzValidIframeRegex.test(thisVal) ) {

			wpzSlideEmbedInputTimeout = setTimeout(function(){
				$.ajax({
					url: ajaxurl,
					type: 'post',
					data: { action: 'wpzoom_sliderthumb_get', wpzoom_sliderthumb_embedcode: thisVal, wpzoom_sliderthumb_postid: $('#post_ID').val() },
					dataType: 'json',
					success: function(response) {
						if (response.success && response.data.thumb_url) {
							$thisParent.css('background-image', 'url(' + response.data.thumb_url + ')');
						} else {
							$thisParent.removeAttr('style');
						}
					},
					error: function() {
						$thisParent.removeAttr('style');
					}
				});

				return;
			}, 1000);

		} else {

			wpzSlideEmbedInputTimeout = setTimeout(function(){ $thisParent.removeAttr('style'); }, 1000);

		}
	});

	$('.wpzoom_slide_add').click(function(e){
		e.preventDefault();

		var $lastSlide = $('.wpzoom_slider li:last', $(this).closest('.inside')),
		    $newSlide = $lastSlide.clone(true);

		function incrementNew(index, name) {
			return name.replace(/(\d+)/, function(fullMatch, n) {
				return Number(n) + 1;
			});
		}

		$newSlide.attr('id', incrementNew).removeClass('image video').addClass('image');
		$('input, textarea', $newSlide).val('').attr('name', incrementNew);
		$('.wpzoom_slide_type_input', $newSlide).val('image');

		$('.wpzoom_slide_preview', $newSlide).removeAttr('style');
		$('.wpzoom_slide_preview_image', $newSlide).attr('src', $('.wpzoom_slide_preview_image', $newSlide).data('defaultimg'));

		if ( !$('.wpzoom_slide_clear_image_button', $newSlide).hasClass('button-disabled') ) $('.wpzoom_slide_clear_image_button', $newSlide).addClass('button-disabled');

		$newSlide.insertAfter($lastSlide);

		if ( $('.wpzoom_slider li').length > 1 ) $('.wpzoom_slider').removeClass('onlyone');

		return;
	});

	$('.wpzoom_slide_type_image, .wpzoom_slide_type_video').click(function(e){
		e.preventDefault();

		var $li = $(this).closest('li').removeClass('image video');

		if ( $(this).hasClass('wpzoom_slide_type_image') ) {

			$li.addClass('image');
			$('.wpzoom_slide_type_input', $(this).closest('.wpzoom_slide_type')).val('image');

		} else if ( $(this).hasClass('wpzoom_slide_type_video') ) {

			$li.addClass('video');
			$('.wpzoom_slide_type_input', $(this).closest('.wpzoom_slide_type')).val('video');

		}

		return;
	});

	$('.wpzoom_slide_remove').click(function(e){
		e.preventDefault();

		$(this).parent().remove();

		if ( $('.wpzoom_slider li').length <= 1 ) $('.wpzoom_slider').addClass('onlyone');

		return;
	});

	$('.wpzoom_slider').sortable({
		opacity: 0.6,
		revert: true,
		cursor: 'move',
		handle: '.sort'
	});

});