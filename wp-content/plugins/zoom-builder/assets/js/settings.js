(function($){

	function savedLayoutDeleteClick( event ) {
		event.preventDefault();

		if ( !confirm(wpzlbL10n.confirmSettingsSavedLayoutDelete) ) return;

		var $listItem = $(this).closest('li'),
		    $parent = $listItem.closest('ul'),
		    listId = $listItem.attr('id'),
		    layoutId = listId.replace(/^wpzlb-savedlayout-/i, '');

		$.post(ajaxurl, { action: 'wpzlb-delete-saved-layout', deletelayout: $('#_wpzlbnonce_saved_layout_delete').val(), layoutid: layoutId });

		$listItem.animate({ backgroundColor: '#fcc' }, 'fast', function(){
			$(this).animate({ opacity: 'hide', height: 'hide' }, 'slow', function(){
				$(this).remove();
				if ( $parent.find('li').length < 1 ) $parent.append('<li><em class="nonessential">' + wpzlbL10n.textSettingsSavedLayoutNone + '</em></li>');
			});
		});
	}

	$(function(){

		$(window).on('hashchange', function(){
			var hash = window.location.hash;

			if ( hash != '' && $('.settings-section' + hash).length > 0 ) {
				$('.nav-tab-wrapper a.nav-tab').removeClass('nav-tab-active').filter('[href="' + hash + '"]').addClass('nav-tab-active');
				$('.settings-section').removeClass('section-active').filter(hash).addClass('section-active');
			}
		}).triggerHandler('hashchange');

		$('.nav-tab-wrapper a.nav-tab').click(function(e){
			e.preventDefault();
			window.location = $(this).attr('href');

			$(this).addClass('nav-tab-active').siblings('a.nav-tab').removeClass('nav-tab-active');
			$('.settings-section').removeClass('section-active').filter($(this).attr('href')).addClass('section-active');
		});

		$('<span class="delete-layout" title="' + wpzlbL10n.tooltipSettingsSavedLayoutDelete + '">&times;</span>')
			.appendTo('.zoom-builder_page_wpzlb-saved-layouts #wpzlb-savedlayouts-settings table.form-table td .wp-tab-panel ul li')
			.click(savedLayoutDeleteClick);

		$('#wpzlb-savedlayouts-import input[type="file"]').on('change', function(){
			if ( /\.json$/i.test($(this).val()) == false ) $(this).closest('form').get(0).reset();
			$('input#submit').prop('disabled', ($(this).val() == ''));
		});

		$('form#wpzlb-widgets-reset').on('submit', function(event){
			if ( !confirm(wpzlbL10n.confirmWidgetsReset) ) {
				event.preventDefault();
				return false;
			} else {
				var $btn = $(this).find('input.button-primary');
				$btn.width($btn.width()).val(wpzlbL10n.inputLabelResetWorking).addClass('working');
			}
		});

	});

})(jQuery);