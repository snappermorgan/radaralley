(function($){

	function ajaxSend( form ) {
		var $form = $(form),
		    submitData = $form.serialize(),
		    $submitBtn = $form.find('.wpzlbcm-submitbtn'),
		    origSubmitBtnVal = $submitBtn.val();

		$submitBtn.val(wpzlbcmL10n.submitLabelSending).prop('disabled', true).addClass('wpzlbcm-submitbtn-sending');

		submitData += '&action=wpzlbcm';

		$.ajax({
			url: wpzlbcmL10n.ajaxurl,
			type: 'post',
			data: submitData,
			complete: function(xhr,status){
				$submitBtn.removeClass('wpzlbcm-submitbtn-sending');

				if ( status == 'success' && xhr.responseText == '1' ) {
					$submitBtn.val(wpzlbcmL10n.submitLabelSent).addClass('wpzlbcm-submitbtn-sent');
					$form[0].reset();
				} else {
					$submitBtn.val(wpzlbcmL10n.submitLabelFailed).addClass('wpzlbcm-submitbtn-failed');
				}

				setTimeout(function(){ $submitBtn.val(origSubmitBtnVal).prop('disabled', false).removeClass('wpzlbcm-submitbtn-sent wpzlbcm-submitbtn-failed'); }, 5000);
			}
		});
	}

	function displayError( errObj ) {
		if ( !$.isPlainObject( errObj ) || !errObj.hasOwnProperty( 'msg' ) || !errObj.hasOwnProperty( 'elem' ) )
			return false;

		var $elem = $(errObj.elem),
		    elemHeight = parseInt( $elem.outerHeight(), 10 ),
		    elemPos = $elem.position(),
		    $errspan = $elem.next('span.wpzlbcm-field-error').text(errObj.msg).show(),
		    errspanHeight = parseInt( $errspan.outerHeight(), 10 ),
		    errspanTop = ( ( ( elemHeight - errspanHeight ) / 2 ) + elemPos.top ) + 'px',
		    errspanLeft = elemPos.left + $elem.outerWidth() + 10;

		$errspan.removeAttr('style');

		if ( !$elem.hasClass('wpzlbcm-field-invalid') ) $elem.addClass('wpzlbcm-field-invalid');
		if ( $errspan.is(':hidden') ) $errspan.css({ top: errspanTop, left: errspanLeft + 30 }).animate({ left: errspanLeft, opacity: 'show' }, 500, function(){ $(this).animate({ opacity: 1 }, 3000, function(){ $(this).animate({ opacity: 'hide' }, 500); }); });
	}

	$(function(){

		var validEmailRegex = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i;

		$('form.wpzlbcm-form').on('submit', function(e){
			e.preventDefault();

			$(this).find('span.wpzlbcm-field-error').stop(true);
			$(this).find('span.wpzlbcm-field-error[style]').removeAttr('style');

			var error = false;

			$(this).find(':input.wpzlbcm-field').removeClass('wpzlbcm-field-invalid').each(function(){
				var type = $(this).prop('type'),
				    required = $(this).data('required') == 'yes',
				    minlen = typeof( $(this).data('minlen') ) !== 'undefined' && parseInt( $(this).data('minlen'), 10 ) > 0 ? parseInt( $(this).data('minlen'), 10 ) : false,
				    maxlen = typeof( $(this).data('maxlen') ) !== 'undefined' && parseInt( $(this).data('maxlen'), 10 ) > 0 ? parseInt( $(this).data('maxlen'), 10 ) : false;

				if ( required && type != 'checkbox' && type != 'radio' && $.trim( '' + $(this).val() ) == '' ) {
					error = { msg: wpzlbcmL10n.fieldInputRequired, elem: this };
					return false;
				}

				if ( required && type == 'email' && !validEmailRegex.test( $(this).val() ) ) {
					error = { msg: wpzlbcmL10n.fieldValidEmail, elem: this };
					return false;
				}

				if ( required && ( type == 'checkbox' || type == 'radio' ) && !$(this).is(':checked') ) {
					error = { msg: wpzlbcmL10n.fieldSelectionRequired, elem: this };
					return false;
				}

				if ( minlen !== false && $(this).val().length < minlen ) {
					error = { msg: wpzlbcmL10n.fieldTooFewChar.replace('%d', minlen), elem: this };
					return false;
				}

				if ( maxlen !== false && $(this).val().length > maxlen ) {
					error = { msg: wpzlbcmL10n.fieldTooManyChar.replace('%d', maxlen), elem: this };
					return false;
				}

				if ( ( type == 'select-one' || type == 'select-multiple' ) && $(this).find('option[data-required="yes"]:not(:selected)').length > 0 ) {
					$(this).find('option[data-required="yes"]:not(:selected)').each(function(){
						error = { msg: wpzlbcmL10n.fieldOptionRequired.replace('%s', $(this).text()), elem: $(this).closest('select') };
						return false;
					});
					return false;
				}
			});

			if ( error === false )
				ajaxSend( this );
			else
				displayError( error );
		});

	});

})(jQuery);