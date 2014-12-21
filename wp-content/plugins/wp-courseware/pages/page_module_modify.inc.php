<?php
/**
 * WP Courseware
 * 
 * Functions relating to allowing you to modify the settings and details of a module.
 */


/**
 * Function that allows a module to be created or edited.
 */
function WPCW_showPage_ModifyModule_load() 
{
	$page = new PageBuilder(true);
	
	$moduleDetails 	= false;
	$moduleID 		= false;
	$adding			= false;
	
	// Trying to edit a course	
	if (isset($_GET['module_id'])) 
	{
		$moduleID 		= $_GET['module_id'] + 0;
		$moduleDetails 	= WPCW_modules_getModuleDetails($moduleID);
		
		// Abort if module not found.
		if (!$moduleDetails)
		{
			$page->showPageHeader(__('Edit Module', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
			$page->showMessage(__('Sorry, but that module could not be found.', 'wp_courseware'), true);
			$page->showPageFooter();
			return;
		}
		
		// Editing a module, and it was found
		else {
			$page->showPageHeader(__('Edit Module', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
		}
	}
	
	// Adding module
	else {
		$page->showPageHeader(__('Add Module', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
		
		$adding = true;
	}
	
	
	global $wpcwdb;
	
	$formDetails = array(
		'module_title' => array(
				'label' 	=> __('Module Title', 'wp_courseware'),
				'type'  	=> 'text',
				'required'  => true,
				'cssclass'	=> 'wpcw_module_title',
				'desc'  	=> __('The title of your module. You <b>do not need to number the modules</b> - this is done automatically based on the order that they are arranged.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 150,
					'minlen'	=> 1,
					'regexp'	=> '/^[^<>]+$/',
					'error'		=> __('Please specify a name for your module, up to a maximum of 150 characters, just no angled brackets (&lt; or &gt;). Your trainees will be able to see this module title.', 'wp_courseware')
				)	
			),				
			
		'parent_course_id' => array(
				'label' 	=> __('Associated Course', 'wp_courseware'),
				'type'  	=> 'select',
				'required'  => true,
				'cssclass'	=> 'wpcw_associated_course',
				'desc'  	=> __('The associated training course that this module belongs to.', 'wp_courseware'),
				'data'		=> WPCW_courses_getCourseList(__('-- Select a Training Course --', 'wp_courseware'))	
			),	

		'module_desc' => array(
				'label' 	=> __('Module Description', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => true,
				'cssclass'	=> 'wpcw_module_desc',
				'desc'  	=> __('The description of this module. Your trainees will be able to see this module description.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 5000,
					'minlen'	=> 1,
					'error'		=> __('Please limit the description of your module to 5000 characters.', 'wp_courseware')
				)	 	
			),		
	);
		
	
	$form = new RecordsForm(
		$formDetails,			// List of form elements
		$wpcwdb->modules, 		// Table for main details
		'module_id' 			// Primary key column name
	);	
	
	$form->customFormErrorMsg = __('Sorry, but unfortunately there were some errors saving the module details. Please fix the errors and try again.', 'wp_courseware');
	$form->setAllTranslationStrings(WPCW_forms_getTranslationStrings());
	
	// Useful place to go
	$directionMsg = '<br/></br>' . sprintf(__('Do you want to return to the <a href="%s">course summary page</a>?', 'wp_courseware'),
		admin_url('admin.php?page=WPCW_wp_courseware')
	);	
	
	// Override success messages
	$form->msg_record_created = __('Module details successfully created.', 'wp_courseware') . $directionMsg;
	$form->msg_record_updated = __('Module details successfully updated.', 'wp_courseware') . $directionMsg;

	$form->setPrimaryKeyValue($moduleID);	
	$form->setSaveButtonLabel(__('Save ALL Details', 'wp_courseware'));
		
	
	// See if we have a course ID to pre-set.
	if ($adding && $courseID = WPCW_arrays_getValue($_GET, 'course_id')) {
		$form->loadDefaults(array(
			'parent_course_id' => $courseID			
		));
	}
	
	// Call to re-order modules once they've been created
	$form->afterSaveFunction = 'WPCW_actions_modules_afterModuleSaved_formHook'; 
	
	$form->show();
	
	$page->showPageMiddle('20%');
	
	// Editing a module?
	if ($moduleDetails) 	
	{
		// ### Include a link to delete the module
		$page->openPane('wpcw-deletion-module', __('Delete Module?', 'wp_courseware'));
		
		printf('<a href="%s&action=delete_module&module_id=%d" class="wpcw_delete_item" title="%s">%s</a>',
			admin_url('admin.php?page=WPCW_wp_courseware'),
			$moduleID,
			__("Are you sure you want to delete the this module?\n\nThis CANNOT be undone!", 'wp_courseware'),			 
			__('Delete this Module', 'wp_courseware')
		);	
		
		printf('<p>%s</p>', __('Units will <b>not</b> be deleted, they will <b>just be disassociated</b> from this module.', 'wp_courseware'));
		
		$page->closePane();
		
		
		// #### Show a list of all sub-units 
		$page->openPane('wpcw-units-module', __('Units in this Module', 'wp_courseware'));
		
		$unitList = WPCW_units_getListOfUnits($moduleID);
		if ($unitList)
		{
			printf('<ul class="wpcw_unit_list">');
			foreach ($unitList as $unitID => $unitObj)
			{
				printf('<li>%s %d - %s</li>',
					__('Unit', 'wp_courseware'),
					$unitObj->unit_meta->unit_number,
					$unitObj->post_title
				);
			}
			printf('</ul>');
		}
		
		else {
			printf('<p>%s</p>', __('There are currently no units in this module.', 'wp_courseware'));
		}
	}
	
	$page->showPageFooter();
}




?>