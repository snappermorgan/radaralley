<?php
/**
 * Code specifically that handles assigning custom templates to a course unit.
 */


/**
 * Constructs the inner form to allow the user to choose a template for a
 * unit.
 */
function WPCW_metabox_showTemplateSelectionTool()
{
	printf('<p>%s</p>', __('Here you can choose which template to use for this unit.', 'wp_courseware'));
		
	// Get a list of all templates
	$theme = wp_get_theme();

	// N.B. No caching, even though core Page Templates has that. 
	// Nacin advises:
	// "ultimately, "caching" for page templates is not very helpful"
	// "by default, the themes bucket is non-persistent. also, calling 
	//  get_page_templates() no longer requires us to load up all theme 
	//  data for all themes so overall, it's much quicker already."

	$postTemplates = array('' => '--- ' . __('Use default template', 'wp_courseware') .  ' ---');
	


	// Get a list of all PHP files in the theme, so that we can check for theme headers.
	// Allow the search to go into 1 level of folders.
	$fileList = (array) $theme->get_files('php', 2);
	foreach ($fileList as $fileName => $fullFilePath) 
	{
		// Progressively check the headers for each file. The header is called 'Unit Template Name'.
		// e.g. 
		// 
		// Unit Template Name: Your Custom Template
		//
		$headers = get_file_data($fullFilePath, array( 
			'unit_template_name' => 'Unit Template Name' 
		));
		
		// No header found
		if (empty($headers['unit_template_name'])) {
			continue;
		}
		
		// We got one!
		$postTemplates[$fileName] = $headers['unit_template_name'];
	}
	
	// Show form with selected template that the user can choose from.
	global $post;
	$selectedTemplate = get_post_meta($post->ID, WPCW_TEMPLATE_META_ID, true);
	echo WPCW_forms_createDropdown('wpcw_units_choose_template_list', $postTemplates, $selectedTemplate);
}

?>