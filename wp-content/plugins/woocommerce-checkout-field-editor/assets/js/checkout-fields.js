jQuery('table#wc_checkout_fields tbody').sortable({
	items:'tr',
	cursor:'move',
	axis:'y',
	handle: 'td',
	scrollSensitivity:40,
	helper:function(e,ui){
		ui.children().each(function(){
			jQuery(this).width(jQuery(this).width());
		});
		ui.css('left', '0');
		return ui;
	},
	start:function(event,ui){
		ui.item.css('background-color','#f6f6f6');
	},
	stop:function(event,ui){
		ui.item.removeAttr('style');
		field_row_indexes();
	}
});

function field_row_indexes() {
	jQuery('#wc_checkout_fields tbody tr').each(function(index, el){
		jQuery('input.field_order', el).val( parseInt( jQuery(el).index('#wc_checkout_fields tbody tr') ) );
	});
};

jQuery('table#wc_checkout_fields tr:not(.new_row) .chosen_select').chosen();

jQuery('a.new_row').click(function() {

	var size = jQuery('#wc_checkout_fields tbody tr').size();

	size ++;

	var new_row = jQuery('tr.new_row').clone();

	html = jQuery( new_row ).html();

	html = html.replace( /\[0\]/g, "[" +  size + "]" );

	jQuery( new_row ).html( html );

	jQuery( new_row ).removeClass('new_row').appendTo('#checkout_fields').show();

	jQuery('table#wc_checkout_fields tr:not(.new_row) .chosen_select').chosen();

	field_row_indexes();

	return false;
});

jQuery('a.enable_row').click(function() {

	var selected_rows = jQuery('#wc_checkout_fields tbody').find('.check-column input:checked');

	jQuery(selected_rows).each( function() {
		var tr = jQuery(this).closest('tr');
		tr.removeClass('disabled');
		tr.find('.field_enabled').val('1');
	});

	return false;
});

jQuery('a.disable_row').click(function() {

	var selected_rows = jQuery('#wc_checkout_fields tbody').find('.check-column input:checked');

	jQuery(selected_rows).each( function() {
		var tr = jQuery(this).closest('tr');
		tr.addClass('disabled');
		tr.find('.field_enabled').val('0');
	});

	return false;
});

jQuery('table#wc_checkout_fields').on('change', 'td.enabled input', function() {
	if ( jQuery(this).is(':checked') ) {
		jQuery(this).closest('tr').removeClass('disabled');
	} else {
		jQuery(this).closest('tr').addClass('disabled');
	}
});

jQuery('table#wc_checkout_fields').on('change', 'select.field_type', function() {

	var val = jQuery(this).val();

	jQuery(this).closest('tr').find('.field-options input.placeholder, .field-options input.options, .field-options .na, .field-validation .options, .field-validation .na').hide();

	if ( val == 'select' || val == 'multiselect' || val == 'radio' ) {
		jQuery(this).closest('tr').find('.field-options .options, .field-validation .options').show();
	} else if ( val == 'checkbox' || val == 'heading' ) {
		jQuery(this).closest('tr').find('.field-options .na, .field-validation .na').show();
	} else {
		jQuery(this).closest('tr').find('.field-options .placeholder, .field-validation .options').show();
	}

});

jQuery('#wc_checkout_fields').find('.field-options input.placeholder, .field-options input.options').hide();
jQuery('#wc_checkout_fields td.enabled input').change();
jQuery('#wc_checkout_fields select.field_type').change();