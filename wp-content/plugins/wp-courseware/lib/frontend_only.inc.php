<?php
/**
 * Only show code on the frontend of the website.
 */







/**
 * Handle showing the box that allows a user to mark a unit as completed.
 */
function WPCW_units_processUnitContent($content)
{
	// #### Ensure we're only showing a course unit, a single item	
	if (!is_single() || 'course_unit' !=  get_post_type()) {
		return $content;
	}
	
	// Use object to handle the rendering of the unit on the frontend.
	include_once WPCW_plugin_getPluginDirPath() . 'classes/class_frontend_unit.inc.php';
	
	global $post;
	$fe = new WPCW_UnitFrontend($post);
	
	
	// #### Get associated data for this unit. No course/module data, then it's not a unit 
	if (!$fe->check_unit_doesUnitHaveParentData()) {
		return $content;
	}
	
	// #### Ensure we're logged in
	if (!$fe->check_user_isUserLoggedIn()) {
		return $fe->message_user_notLoggedIn();
	}
	
	// #### User not allowed access to content, so certainly can't say they've done this unit.
	if (!$fe->check_user_canUserAccessCourse()) {
		return $fe->message_user_cannotAccessCourse();
	}
	
	// #### Is user allowed to access this unit yet?
	if (!$fe->check_user_canUserAccessUnit()) {
		return $fe->message_user_cannotAccessUnit();
	}
	
	
	// ### Do the remaining rendering...
	return $fe->render_detailsForUnit($content);
}



/**
 * If the settings permit, generate the powered by link for WP Courseware.
 * @param Array $settings The list of settings from the database.
 * @return String The HTML for rendering the powered by link.
 */
function WPCW_generatedPoweredByLink($settings)
{
	// Show the credit link by default.
	if (isset($settings['show_powered_by']) && $settings['show_powered_by'] == 'hide_link') {
		return false;
	}
	
	$url = 'https://flyplugins.com/?ref=1/';
	$nofollow = false;
	
	// Have we got a clickbank ID? If so, create an affiliate link
	if (isset($settings['affiliate_id']) && $settings['affiliate_id'])
	{
		$url = str_replace('XXX', $settings['affiliate_id'], 'https://flyplugins.com/?ref=XXX');
		$nofollow = 'rel="nofollow"';
	}
	
	return sprintf('<div class="wpcw_powered_by">%s <a href="%s" %s target="_blank">%s</a></div>',
		__('Powered By', 'wp_courseware'), 
		$url, $nofollow, 		
		__('WP Courseware', 'wp_courseware')
	);
}

?>