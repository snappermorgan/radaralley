<?php
class Ultimate_Auto_Update
{
    /**
     * The plugin remote version
     * @var string
     */
    public $remote_version;
	/**
     * The plugin current version
     * @var string
     */
    public $current_version;
    /**
     * The plugin remote update path
     * @var string
     */
    public $update_path;
    /**
     * Plugin Slug (plugin_directory/plugin_file.php)
     * @var string
     */
    public $plugin_slug;
    /**
     * Plugin name (plugin_file)
     * @var string
     */
    public $slug;
    /**
     * Initialize a new instance of the WordPress Auto-Update class
     * @param string $current_version
     * @param string $update_path
     * @param string $plugin_slug
     */
    function __construct($current_version, $update_path, $plugin_slug)
    {
		// Get the remote version
        $this->remote_version = $this->ult_getRemote_version();
		// Set the class public variables
        $this->current_version = $current_version;
        $this->update_path = $update_path;
        $this->plugin_slug = $plugin_slug;
        $t = explode('/', $plugin_slug);
        $this->slug = str_replace('.php', '', $t[1]);
        // define the alternative API for updating checking
        //add_filter('pre_set_site_transient_update_plugins', array(&$this, 'ult_check_update'));
		add_action( 'admin_init',  array(&$this, 'ult_check_update_notify'));
        // Define the alternative response for information checking
        add_filter('plugins_api', array(&$this, 'ult_check_info'), 10, 3);
		
		//adds plugin updates to update-core.php
		add_action( 'core_upgrade_preamble', array( &$this, 'list_ultimate_updates' ) );
		$notify_update = get_option("ultimate_notify_update");
		if ($notify_update) {
			 add_action( 'admin_enqueue_scripts', array($this,'admin_update_script'),100);
		}

	}
	function list_ultimate_updates(){
		//$this->remote_version = $this->ult_getRemote_version();
		$remote_version = get_option("ultimate_notify_update");
		$upgradeLink = Ultimate_Admin_Area::getUltimateUpgradeLink();
		$transient = get_transient('ultimate_update_checked');
		echo '<h3 id="brainstormforce-plugins">' . __( 'Brainstorm Force - Plugins', 'smile' ) . '</h3>';
		
		//if (!version_compare($this->current_version, $this->remote_version, '<')) {
		if(!$remote_version){
			echo '<p>' . __( 'Your plugins from Brainstorm Force are all up to date.', 'smile' ) . '</p>';
		} else {
			echo '<p>'. __( 'The following plugins from Brainstorm Force have new versions available.', 'smile' ).'</p>';
		?>
        <table class="widefat" cellspacing="0" id="update-plugins-table">
			<thead>
			<tr>
				<th scope="col" class="manage-column"><label><?php _e( 'Name', 'smile' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Installed Version', 'smile' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Latest Version', 'smile' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Actions', 'smile' ); ?></label></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<th scope="col" class="manage-column"><label><?php _e( 'Name', 'smile' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Installed Version', 'smile' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Latest Version', 'smile' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Actions', 'smile' ); ?></label></th>
			</tr>
			</tfoot>
			<tbody class="plugins">
			<tr class='active'>
				<td class='plugin-title'><strong>Ultimate Addons for Visual Composer</strong><?php _e( 'You have version '.$this->current_version.' installed. Update to '.$remote_version );?></td>
				<td style='vertical-align:middle'><strong><?php echo $this->current_version; ?></strong></td>
				<td style='vertical-align:middle'><strong><a href='<?php echo admin_url(); ?>plugin-install.php?tab=plugin-information&amp;plugin=Ultimate_VC_Addons&amp;section=changelog&amp;TB_iframe=true&amp;width=830&amp;height=319' class='thickbox' title='View version <?php echo $remote_version; ?> details'><?php echo $remote_version; ?></a></strong></td>
				<td style='vertical-align:middle'><?php echo $upgradeLink; ?></td>
				</tr>
            </tbody>
		</table>
        <?php
		}
	}
    /**
     * Add our self-hosted autoupdate plugin to the filter transient
     *
     * @param $transient
     * @return object $ transient
	 * @Deprecated from 3.3.1
     */
    public function ult_check_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }
        // Get the remote version
        $remote_version = $this->ult_getRemote_version();
        // If a newer version is available, add the update
        if (version_compare($this->current_version, $remote_version, '<')) {
        	// Force refresh of plugin update information
            delete_site_transient('update_plugins');
            wp_cache_delete( 'plugins', 'plugins' );
            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $remote_version;
            $obj->url = '';//$this->update_path;
            $obj->package = '';//$this->update_path;
            $transient->response[$this->plugin_slug] = $obj;
        }
        return $transient;
    }
	function ult_check_update_notify(){
		// Get the remote version
		if(false === ( get_transient( 'ultimate_update_checked' ) )){
        	$remote_version = $this->ult_getRemote_version();
			// If a newer version is available, add the update
			if (version_compare($this->current_version, $remote_version, '<')) {
				update_option("ultimate_notify_update",$remote_version);
				delete_transient( 'ultimate_update_checked' );
				set_transient( "ultimate_update_checked", true, 60*60*12);
			} else {
				delete_option("ultimate_notify_update");
			}
		}
	}
    /**
     * Add our self-hosted description to the filter
     *
     * @param boolean $false
     * @param array $action
     * @param object $arg
     * @return bool|object
     */
    public function ult_check_info($false, $action, $arg)
    {
		if (isset($arg->slug) && $arg->slug === $this->slug) {
			$information = unserialize($this->ult_getRemote_information());
			$information->sections['changelog'] = '<div>'.$information->sections['changelog'].'</div>';
			//$information->sections['Live_Demos'] = '<div>'.$information->sections['Live_Demos'].'</div>';
			//$information->sections['Video_Tutorials'] = '<div>'.$information->sections['Video_Tutorials'].'</div>';
			//$information->sections['Request_Support'] = '<div>'.$information->sections['Request_Support'].'</div>';
			return $information;
		}
		return $false;
    }
    /**
     * Return the remote version
     * @return string $remote_version
     */
    public function ult_getRemote_version()
    {
        /*$request = wp_remote_post($this->update_path, array('body' => array('action' => 'version')));
		
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        }
		*/
        return ultimate_remote_get($this->update_path); 
    }
    /**
     * Get information about the remote version
     * @return bool|object
     */
    public function ult_getRemote_information()
    {
		/*
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'info')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return @unserialize($request['body']);
        }
        return false;
		*/

        return ultimate_remote_get($this->update_path.'&action=info'); 
    }
    /**
     * Return the status of the plugin licensing
     * @return boolean $remote_license
     */
    public function getRemote_license()
    {
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'license')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        }
        return 0;
    }
	
	function admin_update_script(){
		wp_enqueue_script( 'update-admin-script', plugins_url('../js/admin-update.js',__FILE__),null,null,true);
	}

}