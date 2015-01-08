(function($){

	function updateFieldNumbers() {
		$('#wpzlb #widgets-right .widgets-holder-wrap .widget.wpzoom-builder-widget-contact-form .fields li.field').each(function(i){
			var num = i + 1;

			$(this).find('input[id], select[id], textarea[id]').each(function(){
				$(this).attr('id', $(this).attr('id').replace(/field-[0-9]+-([a-z0-9]+)$/i, 'field-' + num + '-$1'));
			});

			$(this).find('input[name], select[name], textarea[name]').each(function(){
				$(this).attr('name', $(this).attr('name').replace(/\[fields\]\[[0-9]+\]([a-z0-9\[\]]+)$/i, '[fields][' + num + ']$1'));
			});

			$(this).find('label[for]').each(function(){
				$(this).attr('for', $(this).attr('for').replace(/field-[0-9]+-([a-z0-9]+)$/i, 'field-' + num + '-$1'));
			});
		});
	}

	function updateOptionNumbers( $tbody ) {
		$tbody.find('tr:not(.option-add)').each(function(i){
			var num = i + 1;

			$(this).find('input:text[name]').each(function(){
				$(this).attr('name', $(this).attr('name').replace(/\[fields\]\[([0-9]+)\]\[options\]\[names\]\[[0-9]+\]$/i, '[fields][$1][options][names][' + num + ']'));
			});

			$(this).find('input:checkbox[value]').each(function(){
				$(this).val(num);
			});
		});
	}

	function updateSpinners() {
		$(this).find('.fields li.field .field-minmaxlen input:not(.ui-spinner-input)').spinner({ min: 0 });
	}

	$(function(){

		$('#wpzlb #widgets-right .widgets-holder-wrap').on('click', '.widget.wpzoom-builder-widget-contact-form .field-add', function(){
			var $fields = $(this).parent().find('.fields');

			$fields.find('.field-minmaxlen input.ui-spinner-input').spinner('destroy');

			var $newField = $fields.find('> li').last().clone(),
			    $new = $newField.find('.new').show();

			$newField.removeClass().addClass('field type-text adding').hide();
			$newField.find('input:text, textarea').val('');
			$newField.find('.field-type select').val('text');
			$newField.find('.field-options input:checkbox, .field-multiple input:checkbox, .field-required input:checkbox').prop('checked', false);
			$newField.find('.field-show-label input:checkbox').prop('checked', true);
			$newField.find('.field-minlen input:text').val('0');
			$newField.find('.field-advanced').removeClass('show');
			$newField.find('> fieldset > p, > fieldset > div, .field-advanced .field-advanced-inner').removeAttr('style');
			$newField.find('.field-options table tbody tr:not(:first-child):not(.option-add)').remove();

			$fields.append($newField);

			updateFieldNumbers();

			$fields.find('.field-minmaxlen input').spinner({ min: 0 });

			$newField.animate({ height: 'show', opacity: 'show' }, 300, function(){ $(this).removeClass('adding'); $new.fadeOut(300); });
		});

		$('#wpzlb #widgets-right .widgets-holder-wrap')
			.on('click', '.widget.wpzoom-builder-widget-contact-form .fields li.field:not(:only-child) .field-delete', function(){
				var $field = $(this).closest('li.field'),
				    $delete = $field.find('.delete');

				$delete.fadeIn(300, function(){
					$field.addClass('deleting').animate({ height: 'hide', opacity: 'hide' }, 300, function(){ $(this).remove(); updateFieldNumbers(); });
				});
			})
			.on('click', '.widget.wpzoom-builder-widget-contact-form .fields li.field:not(:only-child) .field-move', function(){
				var $field = $(this).closest('li.field'),
				    dir = $(this).hasClass('field-move-up') ? 'up' : 'down',
				    $adjacent = $field[dir=='up'?'prev':'next']();

				if ( $adjacent.length == 0 ) return;

				$adjacent.css({ position: 'relative', zIndex: 999, opacity: 0.5 }).animate({ top: ( dir == 'down' ? '-' : '' ) + $field.height() }, 350);
				$field.css({ position: 'relative', zIndex: 1000 }).addClass('moving').animate({ top: ( dir == 'up' ? '-' : '' ) + $adjacent.height() }, 400, function(){
					$field.removeClass('moving').add($adjacent).css({ position: '', top: '', zIndex: '', opacity: '' });
					$field['insert' + (dir=='up'?'Before':'After')]($adjacent);
					updateFieldNumbers();
				});
			})
			.on('click', '.widget.wpzoom-builder-widget-contact-form .fields li.field .field-options table tbody tr:not(:first-child:nth-last-child(2)) td.option-delete i', function(){
				var $option = $(this).closest('tr'),
				    $tbody = $option.closest('tbody'),
				    height = $option.height(),
				    width = $option.width(),
				    pos = $option.position(),
				    top = pos.top,
				    left = pos.left,
				    $delete = $('<div class="delopt"/>').css({ display: 'none', top: top, left: left, height: height, width: width }).appendTo($option);

				$delete.fadeIn(300, function(){
					$option.fadeOut(300, function(){ $(this).remove(); updateOptionNumbers( $tbody ); });
				});
			})
			.on('change', '.widget.wpzoom-builder-widget-contact-form .fields li.field .field-options input:checkbox[name]', function(){
				var $field = $(this).closest('li.field');

				if ( $(this).prop('checked') === true && ( $field.hasClass('type-radio') || ( $field.hasClass('type-select') && $field.find('.field-multiple input:checkbox').prop('checked') !== true ) ) ) {
					$(this).closest('tbody').find('input:checkbox[name="' + $(this).attr('name') + '"]').not(this).prop('checked', false);
				}
			})
			.on('click', '.widget.wpzoom-builder-widget-contact-form .fields li.field .field-options table tbody tr.option-add td', function(){
				var $last = $(this).closest('tr').prev('tr'),
				    $newOption = $last.clone();

				$newOption.find('div.newopt').remove();
				$newOption.find('input:text').val('');
				$newOption.find('input:checkbox').prop('checked', false);

				$newOption.insertAfter($last);

				var height = $newOption.height(),
				    width = $newOption.width(),
				    pos = $newOption.position(),
				    top = pos.top,
				    left = pos.left;

				$newOption.hide();

				$('<div class="newopt"/>').css({ top: top, left: left, height: height, width: width }).appendTo($newOption);

				updateOptionNumbers( $(this).closest('tbody') );

				$newOption.fadeIn(300, function(){ $(this).find('div.newopt').fadeOut(300, function(){ $(this).remove(); }); });
			})
			.on('click', '.widget.wpzoom-builder-widget-contact-form .fields li.field .field-advanced .field-advanced-label', function(){
				var $advanced = $(this).closest('.field-advanced'),
				    $inner = $advanced.find('.field-advanced-inner');

				if ( $advanced.hasClass('show') ) {
					$inner.slideUp(300, function(){ $advanced.removeClass('show'); });
				} else {
					$advanced.addClass('show');
					$inner.hide().slideDown(300);
				}
			})
			.on('focus', '.widget.wpzoom-builder-widget-contact-form .fields li.field :input', function(){
				$(this).closest('li.field').addClass('focused');
			})
			.on('blur', '.widget.wpzoom-builder-widget-contact-form .fields li.field :input', function(){
				$(this).closest('li.field').removeClass('focused');
			})
			.on('change', '.widget.wpzoom-builder-widget-contact-form .fields li.field .field-type > select', function(){
				var $field = $(this).closest('li.field'),
				    val = $(this).val(),
				    isChkRadSel = ( $.inArray(val, ['checkbox', 'radio', 'select']) !== -1 ),
				    updateFieldClass = function(){
				      $field.removeClass().addClass('field type-' + val);
				      $field.find('.field-options table tbody tr:not(:first-child):not(.option-add)').remove();
							$field.find('.field-options input:text, .field-start-value input:text').val('');
				      $field.find('.field-options input:checkbox, .field-multiple input:checkbox, .field-required input:checkbox').prop('checked', false);
				      $field.find('> fieldset > p, > fieldset > div').removeAttr('style');
				    };

				$field.find('.field-options').animate({ height: ( isChkRadSel ? 'show' : 'hide' ), opacity: ( isChkRadSel ? 'show' : 'hide' ) }, 500);
				$field.find('.field-multiple').animate({ height: ( isChkRadSel && val == 'select' ? 'show' : 'hide' ), opacity: ( isChkRadSel && val == 'select' ? 'show' : 'hide' ) }, 500);
				$field.find('.field-start-value, .field-required, .field-placeholder, .field-minmaxlen').animate({ height: ( isChkRadSel ? 'hide' : 'show' ), opacity: ( isChkRadSel ? 'hide' : 'show' ) }, 500);
				$field.find('.field-options, .field-multiple, .field-start-value, .field-required, .field-placeholder, .field-minmaxlen').promise().done(updateFieldClass);
			});

		$(document.body).on('click.widgets-toggle', '.wpzoom-builder-widget-contact-form', updateSpinners);
		wpzlbWidgetSaveCallbacks.push(updateSpinners);

	});

})(jQuery);