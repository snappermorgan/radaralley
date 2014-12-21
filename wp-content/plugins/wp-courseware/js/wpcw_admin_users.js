/**
 * JS shown on all pages in the admin area.
 */
var $j = jQuery.noConflict();
$j(function()
{	
	// ### Users section
	// Prompt for the bulk reset.
	$j('#wpcw_user_progress_reset_point_bulk_btn').click(function(e)
	{
		if (!confirm(wpcw_js_consts_usr.confirm_bulk_change)) {
			e.preventDefault();
			return false;
		} 
		
		return true;
	});
	
	// Prompt for the single reset.
	$j('.wpcw_user_progress_reset_point_single').change(function(e)
	{
		if (!confirm(wpcw_js_consts_usr.confirm_single_change)) {
			e.preventDefault();
			return false;
		} 
		
		$j(this).closest('form').submit();
		
		return true;
	});
	
	// ### Posts	
	// AJAX to duplicate a post
	$j('.wpcw_units_admin_duplicate').click(function(e)
	{
		e.preventDefault();
	
		// Create the data to pass
		var data = {
			action: 	'wpcw_handle_unit_duplication',
			source_id: 		$j(this).attr('data-postid'),
			security_id: 	$j(this).attr('data-nonce')
		};
		
		// Change message to show something is happening.
		var originalLinkText = $j(this).text();
		var originalLinkItem = $j(this);
		$j(this).text(wpcw_js_consts_usr.status_copying);
		
		// Do the AJAX request
		$j.post(ajaxurl, data, function(response) 
		{
			// Reload the page if successful, show error if not.
			if (response.success) {
				location.reload();
			} else {
				alert(response.errormsg);
				originalLinkItem.text(originalLinkText);
			}
		});
	});
});