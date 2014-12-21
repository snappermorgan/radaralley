<?php
/**
 * Wordpress Premium Plugin Updated Class
 * 
 * A class designed to allow the plugin to update itself using a remote server different
 * from WordPress.org. WordPress.org does not support premium plugins, so this allows
 * access to be controlled to users who have paid for premium updates.
 * 
 * This code is very much in alpha phase, and should not be distributed with plugins 
 * other than by Dan Harrison.
 * 
 *
 * Version History
 * 
 * V0.01 - 24th Jul 2011 - Initial version released. 
 * V0.02 - 11th Sep 2012 - Added code to flush transients when key is changed, so that if key is changed
 * 						   then the latest version or changelog is loaded.
 * V0.03 - 11th Dec 2012 - When in debug mode, transient is not checked to allow for easier debugging.
 * 						 - Fixed how errors are shown when in debug mode.
 * V0.04 - 2nd  Jan 2013 - Added Multi-Site proof way of getting WP version.
 * V0.05 - 26th Feb 2013 - Added check for version information.
 * V0.06 - 18th Mar 2013 - Fixed issue with other plugins showing blank information when displaying 
 * 						   their change logs.
 */

if (!class_exists('PluginUpdater')) { class PluginUpdater { 

	const UPDATER_VERSION = '0.6';  
	
	/**
	 * The full name of the plugin, typically the name of the folder of the plugin and the primary plugin script name.
	 * @var String
	 */
	protected $pluginName;
	
	/**
	 * The short name of the plugin, typically the name of the folder of the plugin.
	 * @var String
	 */
	protected $pluginSlug;	
	
	/**
	 * The user's access key for updating the plugin, usually 32 alphanumeric characters
	 * @var String
	 */
	protected $accessKey;
	
	/**
	 * URL for the mothership updating server
	 * @var String
	 */
	protected $motherShip;
	
	/**
	 * URL for the plugin info page.
	 * @var String
	 */
	protected $pluginInfoURL;	
	
	/**
	 * ID used for validating that a returned message applies to this plugin.
	 * @var String
	 */
	protected $dataCheck;

	/**
	 * Length of the data check sentinel string.
	 * @var Integer 
	 */	
	protected $dataCheckLen;
	
	
	/**
	 * The results of checking the plugin version.
	 * @var Boolean
	 */	
	protected $stored_version_info;

	/**
	 * The results of getting the plugin changelog.
	 * @var String
	 */	
	protected $stored_changelog;	
	
	/** 
	 * Stores the current version of the plugin.
	 * @var Float
	 */
	protected $pluginVersion;
	
	/**
	 * Allows debug output if true.
	 */
	public $debugmode;
		
	/** 
	 * Message shown in WP Admin underneath plugin if the licence key is invalid. Set to false to not this this message.
	 */
	public $plugin_msg_invalid;
	
	/** 
	 * Message shown in WP Admin underneath plugin if the licence key has expired. Set to false to not this this message.
	 */
	public $plugin_msg_expired;
	
	/** 
	 * Message shown in WP Admin underneath plugin if the licence key usage limit has been reached. Set to false to not this this message.
	 */
	public $plugin_msg_limit_reached;
	
	/** 
	 * Message shown in WP Admin underneath plugin if the licence key has been blocked. Set to false to not this this message.
	 */
	public $plugin_msg_blocked;	
	
	/**
	 * Constructor for the plugin updater.
	 * @param String $pluginSlug The slug for the plugin, usually the plugin folder name.
	 * @param String $pluginName The full name of the plugin, usually the plugin folder and the main plugin filename.
	 * @param String $pluginVersion The current version of the plugin.
	 * @param unknown_type $mothership The URL for the mothership updating server
	 * @param unknown_type $pluginInfoURL The URL for the plugin info page.
	 */
	function __construct($pluginSlug, $pluginName, $pluginVersion, $mothership, $pluginInfoURL) 
	{
		// Company-specific variables for premium plugins.
		$this->motherShip 			= $mothership;
		$this->pluginInfoURL 		= $pluginInfoURL;
				
		// Update plugin specific variables
		$this->pluginName 		= $pluginName;
		$this->pluginSlug 		= $pluginSlug;
		$this->pluginVersion 	= $pluginVersion;
		$this->debugmode		= false;
		
		// Variables used to track what's going on with the plugin
		$this->stored_changelog		= $pluginSlug.'_changelog';
		$this->stored_version_info	= $pluginSlug.'_version_info';
		$this->dataCheck    		= '<!--'.$pluginSlug.'_data-->';
		$this->dataCheckLen 		= strlen($this->dataCheck);
		
		// Access key used for updates
		$this->accessKey = false;
		
		// Set up messages
		$this->plugin_msg_invalid 			= 'Enter valid licence key in order to get updates.'; 
		$this->plugin_msg_expired 			= 'Your licence key has expired.';
		$this->plugin_msg_limit_reached 	= 'You\'ve reached the maximum number of websites permitted by your licence key.';
		$this->plugin_msg_blocked			= 'Your licence key has been blocked. Please contact the plugin developers.';
		
		// Plugin updating hooks
		add_filter("transient_update_plugins", array(&$this, 'checkForUpdate'));	
		add_filter("site_transient_update_plugins", array(&$this, 'checkForUpdate'));

		// Hook if there's any plugin information to show
		add_action('install_plugins_pre_plugin-information', array(&$this, 'showPluginChangelog'));
		
		// Customise the plugin details row based on the state of the update.
		add_action('after_plugin_row_'.$this->pluginName, array(&$this, 'showCustomMessage'));
		
	} 
	
	
	/**
	 * Show a custom message on the plugin row based on the status of the licence.
	 */
	function showCustomMessage()
	{
		$message = false;
		
		// See if access was granted, or we've used up our usage.
		$versionInfo = $this->getVersionInfo();		
		
		// Just in case something has failed.
		if (!$versionInfo) {
			return false;
		}
		switch ($versionInfo['access']) 
		{
			case 'denied':
			case 'invalid':
					$message = $this->plugin_msg_invalid;
				break;
			
			case 'expired':
					$message = $this->plugin_msg_expired;
				break;
				
			case 'limit_reached':
					$message = $this->plugin_msg_limit_reached;
				break;
				
			case 'blocked':
					$message = $this->plugin_msg_blocked;
				break;
				
			default:
				return false;
				break;
		}
		
		if ($message) {
			echo '</tr><tr class="plugin-update-tr"><td colspan="5" class="plugin-update"><div class="update-message">' . $message . '</div></td>';
		}
	}
	

	/**
	 * Set the access key for the plugin updater.
	 * 
	 * @param String $key The access key to use.
	 * @param Boolean $changed Boolean If true, the access key has just changed, so update the plugin info.
	 */
	public function setAccessKey($key, $changed = false)
	{
		$this->accessKey = $key;
		
		// Force update of plugin information
		if ($changed) 
		{
			// Clear version caches
			delete_transient($this->stored_version_info);
			delete_transient($this->stored_changelog);
			
			// Get updated version
			$this->getVersionInfo(true);
		}
	}
	
	
	/**
	 * Displays changes to the new release of the plugin on Plugin's version details page.
	 */
	public function showPluginChangelog()
	{
		// Ensure we only render when we have something to show.
		if ($_GET["plugin"] != $this->pluginSlug)
            return; // Return to allow other plugins to show their logs.
		
		echo $this->getPluginChangelog();	
		exit();
	}
	
	
	/**
	 * Displays changes to the new release of the plugin on Plugin's version details page.
	 */
	public function getPluginChangelog()
	{		
		// Ensure we only render when we have something to show.
		if ($_GET["plugin"] != $this->pluginSlug)
            return;
            
		// Change the changelog
		if (false === ($changeLog = get_transient($this->stored_changelog)))
		{
			
			$changeLog = $this->serverGetResponse('plugin_changelog');
			if ($changeLog) { 
				// Store for 12 hours for storing change log, or 10s in debug.
				set_transient($this->stored_changelog, $changeLog, ($this->debugmode ? 10 : 60*60*12)); 
			}
		}
        
		return $changeLog;
	}

	
	/**
	 * Retrieve all of the version information for the currently installed plugin.
	 * @param Boolean $forceCheck If true, force the check of getting the version info from the server.
	 * @return Array The full version information details.
	 */
	public function getVersionInfo($forceCheck = false)
	{		
		// Don't check transient when in debug mode.
		if ($this->debugmode) {
			$forceCheck = true;
		}
		
		// Request version, update and user authorisation data from server
		if ($forceCheck || false === ($versionInfo = get_transient($this->stored_version_info))) 
		{			
			$new_versionInfo = $this->getVersionInfo_Check();
			// Store for 12 hours for storing version info, or 10s in debug.
        	set_transient($this->stored_version_info, $new_versionInfo, ($this->debugmode ? 10 : 60*60*12));
        	
			if ($this->debugmode) {
				error_log('getVersionInfo() - Server Result: ' . print_r($new_versionInfo, true));	
			}
        	
        	return $new_versionInfo;
		}
		
		// Return result from local cached result
		else 
		{			
			if ($this->debugmode) {
				error_log('getVersionInfo() - Cached Result: ' . print_r($versionInfo, true));	
			}
			
			return $versionInfo;
		}
	}
	
	
	/**
	 * Talk to the server to get the current version of the plugin.
	 * @return Array The version details of the current plugin version.
	 */
	public function getVersionInfo_Check()
	{
		$response = trim($this->serverGetResponse('version_check'));
		if (!$response) {
			return false;
		}
		
		$defaults = array(
			'url' 		=> '',
			'access' 	=> 'invalid',
			'version' 	=> '',
		);

		// Safely parse the results from the server into an array.
		$versionInfo = wp_parse_args($response, $defaults);
		if (!$versionInfo['version']) {
			return false;
		}
		
		// We've got a version.
		return $versionInfo;
	}	
	
	/**
	 * Gets the current WordPress version.
	 * @return String The current WordPress version.
	 */
	protected function getWordpressVersion()
	{
		// Reliable way of getting WordPress version that's not overwritten by 
		// security plugins.
		//include_once('../wp-includes/version.php');
		include_once ABSPATH . '/wp-includes/version.php';
		global $wp_version;
		return $wp_version;
	}
	
	
	/**
	 * Get data from the server, automatically checking that the data is valid.
	 * @param String $requestType The type of request to perform.
	 * @return String The response from the server if the request succeeds.
	 */
	protected function serverGetResponse($requestType)
	{
		$args = array(
			'accesskey' 	=> $this->accessKey,
			'requesttype'	=> $requestType,
			'plugin'		=> $this->pluginSlug,
			'wpurl'			=> $this->getHomeURL(),
			'current_ver'	=> $this->pluginVersion,
			'wpver'			=> $this->getWordpressVersion()
		);
		
		$raw_response = wp_remote_post($this->motherShip . '&op=' . md5('test'),  
			array(	'timeout' 		=> 3, 
				 	'user-agent' 	=> 'WordPress Plugin Updater/'.self::UPDATER_VERSION,
					'body'			=> $args, 
				));
				
		$errorHappened = false;
		if (is_wp_error( $raw_response ) || 200 != $raw_response['response']['code']) {
            $message = "";
            $errorHappened = true;
		}
        else {
            $message = $raw_response['body'];
        }
            
        // Debug output for any responses logged to php_error.log
		if ($this->debugmode) 
		{			
			// Add error message if WP error for extra detail.
			if (is_wp_error($raw_response)) {
				$message .= $raw_response->get_error_message();
				$errorcode = 'n/a';
			} 
			else {
				$errorcode = $raw_response['response']['code'];
			}
			error_log(sprintf("serverGetResponse(%s) - Status Code: %s - Error Occured: %s\nData:\n%s\n\n",  $this->motherShip, $errorcode, ($errorHappened ? 'Yes' : 'No'), $message));
        }
        
        // Validating that message is a valid plugin update response. If message is invalid, don't return anything.
        if (substr($message, 0, $this->dataCheckLen) == $this->dataCheck) {
        	// Strip out the sentinel as it's valid.
            $message = substr($message, $this->dataCheckLen, strlen($message) - $this->dataCheckLen);          
        } 
        // No sentinel
        else {
        	$message = false;	
        }
            
        return $message;
	}
	
	
	/**
	 * Determine if the specified version is newer than the current version of the plugin.
	 * @param String $newVersion The version of the new plugin.
	 * @return Boolean True if the new plugin is a newer version, false otherwise.
	 */
	protected function versionIsNewer($newVersion)
	{
		$newVersion = $this->convertVersionToFloat($newVersion);
		return ($newVersion > $this->pluginVersion);
	}
	
	
	/**
	 * Convert the specified string into a float.
	 * @param String $version A version number.
	 * @return Float The version number as a float.
	 */
	protected function convertVersionToFloat($version)
	{
		return (float)preg_replace( "#^([\d\.]*).*$#", '$1', $version);
	}
	
	
	/**
	 * Gets the URL of the homepage of the host website.
	 */
	protected function getHomeURL()
	{
		return home_url('/');
	}

	/**
	 * Function that tells WordPress if there's an update or not for this plugin.
	 * @param Array $option The list of plugins that WordPress knows about.
	 * @return Array The modified list of plugins that need updating or not.
	 */
	public function checkForUpdate($option)
	{	
		if (!is_admin()) {
			return $option;
		}
		
		if ($this->debugmode) {
			error_log(print_r($option, true));
		}
		
		// No version information, so abort.
		if (!$versionInfo = $this->getVersionInfo()) {
			return $option;
		}				
		
		// Ensure we have an object to work with 
		if (empty($option->response[$this->pluginName]))
			$option->response[$this->pluginName] = new stdClass();
			
		// Check that we have access to download the update	
		if ($versionInfo['access'] == 'granted' && $this->versionIsNewer($versionInfo['version']))
		{
			// Construct the URL to fetch the package
			$packageURL = $versionInfo["url"];
			$packageURL = str_replace("{KEY}", $this->accessKey, $packageURL);
			$packageURL = str_replace("{URL}", $this->getHomeURL(), $packageURL);
			
			$option->response[$this->pluginName]->url 			= $this->pluginInfoURL;
			$option->response[$this->pluginName]->slug 			= $this->pluginSlug;
			$option->response[$this->pluginName]->package 		= $packageURL;
			$option->response[$this->pluginName]->new_version 	= $versionInfo['version'];
			$option->response[$this->pluginName]->id 			= "0";

			// Remove this
			//error_log(print_r($option->response, true));
		}
		
		// No access, or we don't have access to download the update
		else {
			unset($option->response[$this->pluginName]);
		}
				
		return $option;
	}
}}

?>