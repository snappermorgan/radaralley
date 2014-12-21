<?php
/**
 * "Addons" tab class.
 *
 * @package      OptinMonster
 * @since        1.0.0
 * @author       Thomas Griffin <thomas@retyp.com>
 * @copyright    Copyright (c) 2013, Thomas Griffin
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Loads addons tab.
 *
 * @package      OptinMonster
 * @since        1.0.0
 */
class optin_monster_tab_addons {

	/**
	 * Prepare any base class properties.
	 *
	 * @since 1.0.0
	 */
	public $base, $tab;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

        // Bring base class into scope.
        global $optin_monster_account, $wpdb, $optin_monster_license;
        $this->base    = optin_monster::get_instance();
        $this->tab     = 'addons';
        $this->account = $optin_monster_account;
        $this->license = get_option( 'optin_monster_license' );

        add_action( 'optin_monster_tab_' . $this->tab, array( $this, 'do_tab' ) );

    }

	/**
	 * Outputs the tab content.
	 *
	 * @since 1.0.0
	 */
	public function do_tab() {

        // Gather the addon data.
        $addon_data = get_transient( 'optinmonster_addons_data' );
        if ( false === $addon_data || isset( $_POST['om-retry-addons'] ) ) {
			$addon_data = optin_monster_updater::perform_outside_remote_request( 'get-addons-data', array( 'key' => $this->license['key'] ) );
			set_transient( 'optinmonster_addons_data', $addon_data, 60*60*12 );
		}

		// If there is an error with retrieving the addon data, set option to retry.
		if ( is_wp_error( $addon_data ) || false === $addon_data ) {
    		echo '<form id="om-addons-form-retry" method="post">';
    		    echo '<div class="alert alert-error"><p><strong>' . __ ( 'Sorry, but there was an error retrieving Addons data for the supplied license key. Please verify that your license key is set and try again.', 'optin-monster' ) . '</strong></p></div>';
    		    echo '<p><input class="button button-secondary" type="submit" name="om-retry-addons" value="' . __( 'Click Here to Retry Grabbing Addon Data for OptinMonster', 'optin-monster' ) . '" /></p>';
    		echo '</form>';
    		return;
		}

		// Load the addons view.
		echo '<div id="om-addons-form">';
		    /** We've successfully grabbed the data, so let's start manipulating it */
		    if ( empty( $addon_data ) ) {
    		    echo '<div class="alert alert-success"><p><strong>' . sprintf( '%s <a href="https://optinmonster.com/account/?utm_source=plugin&utm_medium=link&utm_campaign=addon-upgrade-link" title="%s" target="_blank">%s</a>', __( 'The license associated with this install does not contain any addons. To gain access to OptinMonster Addons,', 'optin-monster' ), __( 'Click Here to Upgrade and Get Access to Addons', 'optin-monster' ), __( 'click here to upgrade and get access to OptinMonster Addons!', 'optin-monster' ) ) . '</strong></p></div>';
		    } else {
		        echo '<div id="optinmonster-addon-area" class="om-addons-wrap">';
		        echo '<form id="om-addons-form-retry" method="post">';
        		    echo '<div class="alert alert-success"><p class="addon-intro"><strong>' . __( 'Need to refresh the addon data below?', 'optin-monster' ) . '</strong> <input class="button button-primary" type="submit" name="om-retry-addons" value="' . __( 'Click Here to Refresh Addon Data', 'optin-monster' ) . '" /></p></div>';
        		echo '</form>';
    			foreach ( (array) $addon_data as $i => $addon ) {
    				/** Attempt to get the plugin basename if it is installed or active */
    				$plugin_basename 	= $this->get_plugin_basename_from_slug( $addon->slug );
    				$installed_plugins 	= get_plugins();
    				$last				= ( 0 == $i%3 ) ? 'last' : '';

    				echo '<div class="optinmonster-addon ' . $last . '">';
    					echo '<img class="optinmonster-addon-thumb" src="' . esc_url( $addon->image ) . '" width="300px" height="250px" alt="' . esc_attr( $addon->title ) . '" />';
    					echo '<h3 class="optinmonster-addon-title">' . esc_html( $addon->title ) . '</h3>';

    					/** If the plugin is active, display an active message and deactivate button */
    					if ( is_plugin_active( $plugin_basename ) ) {
    						echo '<div class="optinmonster-addon-active optinmonster-addon-message">';
    							echo '<span class="addon-status">' . __( 'Status: Active', 'optin-monster' ) . '</span>';
    							echo '<div class="optinmonster-addon-action">';
    								echo '<a class="button button-secondary optinmonster-addon-action-button optinmonster-deactivate-addon" href="#" rel="' . esc_attr( $plugin_basename ) . '">' . __( 'Deactivate', 'optin-monster' ) . '</a>';
    							echo '</div>';
    						echo '</div>';
    					}

    					/** If the plugin is not installed, display an install message and install button */
    					if ( ! isset( $installed_plugins[$plugin_basename] ) ) {
    						echo '<div class="optinmonster-addon-not-installed optinmonster-addon-message">';
    							echo '<span class="addon-status">' . __( 'Status: Not Installed', 'optin-monster' ) . '</span>';
    							echo '<div class="optinmonster-addon-action">';
    								echo '<a class="button button-secondary optinmonster-addon-action-button optinmonster-install-addon" href="#" rel="' . esc_url( $addon->url ) . '">' . __( 'Install Addon', 'optin-monster' ) . '</a>';
    							echo '</div>';
    						echo '</div>';
    					}
    					/** If the plugin is installed but not active, display an activate message and activate button */
    					elseif ( is_plugin_inactive( $plugin_basename ) ) {
    						echo '<div class="optinmonster-addon-inactive optinmonster-addon-message">';
    							echo '<span class="addon-status">' . __( 'Status: Inactive', 'optin-monster' ) . '</span>';
    							echo '<div class="optinmonster-addon-action">';
    								echo '<a class="button button-secondary optinmonster-addon-action-button optinmonster-activate-addon" href="#" rel="' . esc_attr( $plugin_basename ) . '">' . __( 'Activate', 'optin-monster' ) . '</a>';
    							echo '</div>';
    						echo '</div>';
    					}

    					echo '<p class="optinmonster-addon-excerpt">' . esc_html( $addon->excerpt ) . '</p>';
    				echo '</div>';
    				$i++;
    			}
    			echo '</div>';
            }
		echo '</div>';

	}

	/**
	 * Helper function to retrieve the plugin basename from the plugin slug.
	 *
	 * @since 1.2.0
	 *
	 * @param string $slug The plugin slug
	 * @return string The plugin basename if found, else the plugin slug
	 */
	private function get_plugin_basename_from_slug( $slug ) {

		$keys = array_keys( get_plugins() );

		foreach ( $keys as $key )
			if ( preg_match( '|^' . $slug . '|', $key ) )
				return $key;

		return $slug;

	}

}

// Initialize the class.
$optin_monster_tab_addons = new optin_monster_tab_addons();