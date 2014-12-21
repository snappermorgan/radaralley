jQuery(document).ready(function($) {

	// Frontend Chosen selects
	$("select.checkout_chosen_select,.form-row .select").chosen();

	$( ".checkout-date-picker" ).datepicker({
		dateFormat: wc_checkout_fields.date_format,
		numberOfMonths: 1,
		showButtonPanel: true,
		changeMonth: true,
      	changeYear: true
	});

});