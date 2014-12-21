<?php
/**
 * WP Courseware
 * 
 * Functions relating to showing the settings page.
 */


/**
 * Shows the settings page for the plugin.
 */
function WPCW_showPage_Settings_load()
{
	$page = new PageBuilder(true);
	$page->showPageHeader(__('Training Courses - Settings', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	// Check for update flag
	if (isset($_POST['update']) && $_POST['update'] == 'tables_force_upgrade')
	{
		$page->showMessage(__('Upgrading WP Courseware Tables...', 'wp_courseware'));
		flush();		

		$installed_ver  = get_option(WPCW_DATABASE_KEY) + 0;
		
		WPCW_database_upgradeTables($installed_ver, true, true); 
		$page->showMessage(sprintf(__('%s tables have successfully been upgraded.', 'wp_courseware'), 'WP Courseware') );
	}
	
	
	
	$settingsFields = array(
		'section_access_key' 	=> array(
				'type'	  	=> 'break',
				'html'	   	=> WPCW_forms_createBreakHTML(__('Licence Key Settings', 'wp_courseware')),
			),			
			
		'licence_key' => array(
				'label' 	=> __('Licence Key', 'wp_courseware'),
				'type'  	=> 'text',
				'desc'  	=> __('Your licence key for the WP Courseware plugin.', 'wp_courseware'), 
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 32,
					'minlen'	=> 32,
					'regexp'	=> '/^[A-Za-z0-9]+$/',
					'error'		=> __('Please enter your 32 character licence key, which contains only letters and numbers.', 'wp_courseware'),
				)	
			), 	

		'license_activation' => array(
			'label' 	=> __('Licence Activation', 'wp_courseware'),
			'type'  	=> 'radio',
			'required'	=> 'true',
			'data'		=> array(
				'activate_license' 	=> sprintf('<b>%s</b>', __('Activate', 'wp_courseware')),
				'deactivate_license' 	=> sprintf('<b>%s</b>', __('Deactivate', 'wp_courseware')),
			),
			'desc'  	=> __('If you want to receive updates to this plugin, select "Activate". Otherwise, select "Deactivate" to deactivate license. Selecting "Deactivate" will disable any future updates. Deactivating your license allows you to move your plugin to another site.', 'wp_courseware'),
		),		
			
		// Section that deals with CSS
		'section_default_css' 	=> array(
				'type'	  	=> 'break',
				'html'	   	=> WPCW_forms_createBreakHTML(__('Style &amp; Design Settings', 'wp_courseware')),
			),
			
			
		'use_default_css' => array(
				'label' 	=> __('Use Default CSS?', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'	=> 'true',
				'data'		=> array(
					'show_css' 	=> sprintf('<b>%s</b> - %s', __('Yes', 'wp_courseware'), __('Use default stylesheet for the frontend of the website.', 'wp_courseware')),
					'hide_css' 	=> sprintf('<b>%s</b> - %s', __('No', 'wp_courseware'), __('Don\'t use the default stylesheet for the frontend of the website (you\'ll write your own CSS)', 'wp_courseware')),
				),
				'desc'  	=> __('If you want to style your training course material yourself, you can disable the default stylesheet. If in doubt, select <b>Yes</b>.', 'wp_courseware'),
			),	
			
		'section_link' 	=> array(
				'type'	  	=> 'break',
				'html'	   	=> WPCW_forms_createBreakHTML(__('Powered By Link', 'wp_courseware')),
			),			
			
		'show_powered_by' => array(
				'label' 	=> __('Show Powered By Link?', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'	=> 'true',
				'data'		=> array(
					'show_link' 	=> sprintf('<b>%s</b> - %s', __('Yes', 'wp_courseware'), __('Show the <em>\'Powered By WP Courseware\'</em> link.', 'wp_courseware')),
					'hide_link' 	=> sprintf('<b>%s</b> - %s', __('No', 'wp_courseware'), __('Don\'t show any powered-by links.', 'wp_courseware')),
				),
				'desc'  	=> __("Do you want to show a 'Powered By WP Courseware' link at the bottom of course outlines?", 'wp_courseware'),
			),

		'affiliate_id' => array(
				'label' 	=> __('Your Affiliate ID', 'wp_courseware'),
				'type'  	=> 'text',
				'desc'  	=> __("(Optional) Earn some money by providing your Affiliate ID, which will turn the <b>Powered By WP Courseware</b> into an affiliate link that earns you a percentage of every sale! If you are not an affiliate, login to the member portal to register and get your ID.", 'wp_courseware'), 
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 15,
					'minlen'	=> 1,
					'regexp'	=> '/^[A-Za-z0-9\-_]+$/',
					'error'		=> __('Please enter your Affiliate ID, which is only a number..', 'wp_courseware'),
				)	
			),
		);
		
	
	// Remove licence key for child multi-sites
	if (!WPCW_plugin_hasAdminRights()) 
	{
		unset($settingsFields['section_access_key']);
		unset($settingsFields['licence_key']);	
	}
				
	$settings = new SettingsForm($settingsFields, WPCW_DATABASE_SETTINGS_KEY, 'wpcw_form_settings_general');
	$settings->setSaveButtonLabel(__('Save ALL Settings', 'wp_courseware'));
	
	// Update messages for translation
	$settings->msg_settingsSaved   	= __('Settings successfully saved.', 'wp_courseware');
	$settings->msg_settingsProblem 	= __('There was a problem saving the settings.', 'wp_courseware'); 	
	$settings->customFormErrorMsg = __('Sorry, but unfortunately there were some errors saving the course details. Please fix the errors and try again.', 'wp_courseware');
	$settings->setAllTranslationStrings(WPCW_forms_getTranslationStrings());
	
	// Form event handlers - processes the saved settings in some way 
	$settings->afterSaveFunction = 'WPCW_showPage_Settings_afterSave';
	$settings->afterSaveFunction = 'edd_activate_license_WPCW'; 
		
	$settings->show();	
	
	
	// Create little form to force upgrading tables if something went wrong during update.
	echo WPCW_forms_createBreakHTML(__("Upgrade Tables", 'wp_courseware'), false, true, 'wpcw_upgrade_tables');
	?>	
	<p><?php _e("If you're getting any errors with WP Courseware relating to database tables when you've updated, you can force an upgrade of the database tables using the button below.", 'wp_courseware'); ?></p>
	<?php
	
	$form = new FormBuilder('tables_force_upgrade');
	$form->setSubmitLabel(__('Force Table Upgrade', 'wp_courseware'));	
	echo $form->toString();

	
	
	// RHS Support Information
	$page->showPageMiddle('23%');	
	WPCW_docs_showSupportInfo($page);
	WPCW_docs_showSupportInfo_News($page);	
	WPCW_docs_showSupportInfo_Affiliate($page);
	
	$page->showPageFooter();
}


/**
 * Function called after settings are saved.
 * 
 * @param String $formValuesFiltered The data values actually saved to the database after filtering.
 * @param String $originalFormValues The original data values before filtering.
 * @param Object $formObj The form object thats doing the saving.
 */
function WPCW_showPage_Settings_afterSave($formValuesFiltered, $originalFormValues, $formObj)
{
	// Can't update licence key unless admin for site.
	if (!WPCW_plugin_hasAdminRights()) {
		return false;
	}
	
	// Update the licence key for the plugin, in case it's changed.
	// global $updater_wpcw;
	// $updater_wpcw->setAccessKey($formValuesFiltered['licence_key'], true);
}


/**
 * Shows the settings page for the plugin, shown just for the network page.
 */
function WPCW_showPage_Settings_Network_load()
{
	$page = new PageBuilder(true);
	$page->showPageHeader(__('WP Courseware - Settings', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	
	$settingsFields = array(
		'section_access_key' 	=> array(
				'type'	  	=> 'break',
				'html'	   	=> WPCW_forms_createBreakHTML(__('Licence Key Settings', 'wp_courseware'), false, true),
			),			
			
		'licence_key' => array(
				'label' 	=> __('Licence Key', 'wp_courseware'),
				'type'  	=> 'text',
				'desc'  	=> __('Your licence key for the WP Courseware plugin.', 'wp_courseware'), 
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 32,
					'minlen'	=> 32,
					'regexp'	=> '/^[A-Za-z0-9]+$/',
					'error'		=> __('Please enter your 32 character licence key, which contains only letters and numbers.', 'wp_courseware'),
				)	
			), 		
		);
		
				
	$settings = new SettingsForm($settingsFields, WPCW_DATABASE_SETTINGS_KEY, 'wpcw_form_settings_general');
	
	// Set strings and messages
	$settings->setAllTranslationStrings(WPCW_forms_getTranslationStrings());
	$settings->setSaveButtonLabel('Save ALL Settings', 'wp_courseware');
	
	// Form event handlers - processes the saved settings in some way 
	$settings->afterSaveFunction = 'WPCW_showPage_Settings_afterSave';
		
	$settings->show();	
	
	
	// RHS Support Information
	$page->showPageMiddle('23%');	
	WPCW_docs_showSupportInfo($page);
	WPCW_docs_showSupportInfo_News($page);	
	WPCW_docs_showSupportInfo_Affiliate($page);
	
	$page->showPageFooter();
}

?>