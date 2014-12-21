<?php
/**
 * Code specifically that handles showing custom templates for units.
 */


/**
 * Intercept the code that chooses what template to show for the unit.
 *  
 * @param String $template The current template.
 * @return String The path of the template file to use.
 */
function WPCW_templates_units_filterTemplateForUnit($template)
{
	// What type of post are we showing? Only interested in course units.
	global $post;	
	if ('course_unit' != $post->post_type) {
		return $template;
	}

	// Now we know we have a course unit, we need to see if there's a post template
	// associated with it.
	$templateFile = get_post_meta($post->ID, WPCW_TEMPLATE_META_ID, true);
	if (!$templateFile) {
		return $template; // Return default
	}
	
	// If there's a tpl in a (child theme or theme with no child)
	if (file_exists(trailingslashit(STYLESHEETPATH) . $templateFile)) {
		return STYLESHEETPATH . DIRECTORY_SEPARATOR . $templateFile;
	}
			
	// If there's a tpl in the parent of the current child theme
	else if (file_exists(TEMPLATEPATH . DIRECTORY_SEPARATOR . $templateFile)) {
		return TEMPLATEPATH . DIRECTORY_SEPARATOR . $templateFile;
	}

	// Use the default template.
	return $template;
}



?>