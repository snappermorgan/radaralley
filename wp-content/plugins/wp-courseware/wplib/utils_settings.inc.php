<?php
/**
 * Wordpress Settings Form Builder Utility Class
 * 
 * A set of functions for fetching and saving settings to a single setting row in 
 * the WordPress database by storing all of the plugin settings in an array. 
 * 
 * This code is very much in alpha phase, and should not be distributed with plugins 
 * other than by Dan Harrison.
 * 
 *
 * Version History
 * 
 * V0.01 - 26th Jun 2011 - Initial version released.
 * V0.02 - 12th Apr 2013 - Added translation override for message strings.
 */

// Required for extending behaviour
include_once('utils_easyform.inc.php');


/**
 * Saves the inputted data to the WordPress settings table.
 */
if (!class_exists('SettingsForm')) { class SettingsForm extends EasyForm 
{
	/**
	 * The string used when saving the settings to the database.
	 * @var String
	 */
	protected $settingPrefix;
	
	/**
	 * The message to show when saving a setting.
	 */
	public $msg_settingsSaved;
	
	/**
	 * The message to show when there's an issue with a setting.
	 */	
	public $msg_settingsProblem;
	
	/**
	 * Default constructor that takes in initial parameters and setting prefix.
	 * @param Array $paramList The list of parameters to create the form from.
	 * @param String $settingPrefix The prefix to use for the settings.
	 * @param String $formID The optional ID to give to the form. Ideal for when there's more than 1 form on a page. 
	 */	
	public function __construct ($paramList, $settingPrefix, $formID = false)
	{
		parent::__construct($paramList, $formID);
		
		// Default save text reflects this is a settings form
		$this->buttonText = __('Save Settings');
		
		// Store the setting prefix.
		$this->settingPrefix = $settingPrefix;
		
		// Messages
		$this->msg_settingsSaved = __('Settings successfully saved.');
		$this->msg_settingsProblem = __('There was a problem saving the settings.');
		
		// Load default values
		parent::loadDefaults(TidySettings_getSettings($settingPrefix));		
	}
	
	
	/**
	 * Method called when settings form details are being saved.
	 * @param Array $formValues The list of settings being saved.
	 * @see wplib/EasyForm::handleSave()
	 */
	protected function handleSave($formValues)
	{		
		// Get existing settings first (in case we don't have all the setting to play with 
		// on a certain page), then merge changes.
		$originalSettings = TidySettings_getSettings($this->settingPrefix);		
		foreach ($formValues as $name => $value) {
			$originalSettings[$name] = $value;
		}						
		
		$saveSuccess = TidySettings_saveSettings($originalSettings, $this->settingPrefix);		
		if ($saveSuccess) {
			$this->messages = $this->showMessage($this->msg_settingsSaved);
		} else {
			$this->messages = $this->showMessage($this->msg_settingsProblem, true);
		}
	}
		
}}


/**
 * Save the settings to the WordPress settings table.
 * @param Array $settingDetails The list of settings to be saved.
 * @param String $settingPrefix The string key used to save the array of settings.
 * @return Boolean True if the settings were saved, false otherwise.
 */
if (!function_exists('TidySettings_saveSettings')) { function TidySettings_saveSettings($settingDetails, $settingPrefix)
{
	if (!($settingDetails && is_array($settingDetails)))  {
		error_log('SettingsForm: Settings details are null or not in an array.');
		return false;
	}
	
	if (!$settingPrefix) {
		error_log('SettingsForm: Settings Prefix is empty, so nothing can be saved.');
		return false;
	}
	
	// Convert array to string for saving, then store in settings table.
	update_option($settingPrefix, serialize($settingDetails));
	
	return true;
}}


/**
 * Get all of the settings as an array.
 * @param String $settingPrefix The string key used to store the array of settings.
 * @return Array The list of settings as an associative array. 
 */
if (!function_exists('TidySettings_getSettings')) { function TidySettings_getSettings($settingPrefix) 
{
	$rawSettings = get_option($settingPrefix);
	if ($rawSettings) {
		
		// Sometimes data is not serialised yet
		if (is_array($rawSettings)) {
			return $rawSettings;
		}
		
		return unserialize($rawSettings);
	}
	return false;
}}


/**
 * Get just a single setting from the settings list.
 * @param String $settingPrefix The string key used to store the array of settings.
 * @param String $settingName The name of the setting key for the individual setting to retrieve.
 * @param String $defaultValue The value to return if the setting was not found.
 * @return String The value of the setting.
 */
if (!function_exists('TidySettings_getSettingSingle')) { function TidySettings_getSettingSingle($settingPrefix, $settingName, $defaultValue = false) 
{
	$settings = TidySettings_getSettings($settingPrefix);	
	if (isset($settings[$settingName])) {
		return $settings[$settingName];
	} 
	
	// What to return if value is not set.
	else {
		return $defaultValue;
	}
}}

?>