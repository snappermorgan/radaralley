<?php
/**
 * Updates class for OptinMonster.
 *
 * @since 1.0.0
 *
 * @package	OptinMonster
 * @author	Thomas Griffin
 */
class optin_monster_updates {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Holds the updates pagehook.
	 *
	 * @since 1.0.0
	 *
	 * @var bool|string
	 */
	public $pagehook = false;

	/**
	 * Flag for determining if in MultiSite.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $is_multisite = false;

	/**
	 * Holds all of the plugin updates.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $plugins;

	/**
	 * Constructor. Prepares the admin menu for updating Soliloquy
	 * and Addons.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		self::$instance = $this;

		/** Return early if the Updates constant is defined and set to false */
		if ( defined( 'OM_UPDATES_PAGE' ) && ! OM_UPDATES_PAGE )
			return;

		/** Set the MultiSite flag */
		if ( is_multisite() )
			$this->is_multisite = true;

		/** Set our plugins as an object */
		$this->plugins = new stdClass;

		/** If we are trying to get update information from a non-network activated plugin, load the iframe update request */
		if ( isset( $_GET['om_iframe_request'] ) && $_GET['om_iframe_request'] )
			add_action( 'admin_init', array( $this, 'load_iframe' ), 1 );

		/** Load the Updates page and MS updates */
		add_action( 'admin_menu', array( $this, 'menu' ), 100 );
		add_action( 'update-core-custom_do-optin-monster-upgrade', array( $this, 'ms_upgrade' ) );

	}

	/**
	 * Loads the iframe request information for plugin updates.
	 *
	 * @since 1.0.0
	 */
	public function load_iframe() {

		$this->load_iframe_updates();

	}

	/**
	 * Creates the submenu page for Soliloquy updates.
	 *
	 * @since 1.0.0
	 */
	public function menu() {

		$this->pagehook = add_submenu_page( 'optin-monster', __( 'OptinMonster Updates', 'optin-monster' ), __( 'Updates', 'optin-monster' ), apply_filters( 'optin_monster_updates_cap', 'manage_options' ), 'optin-monster-updates', array( $this, 'updates_menu' ) );

		/** If we have created our updates page successfully, set all of the plugin update instances as objects and load rest of class */
		if ( $this->pagehook ) {
			/** Set update objects and numbers */
			$this->set_update_instances();
			$this->set_update_number();

			/** Do an update check if it has been called */
			if ( isset( $_GET['check_optin_monster_updates'] ) && $_GET['check_optin_monster_updates'] )
				$this->check_plugin_updates();

			/** Load class assets */
			add_action( 'load-' . $this->pagehook, array( $this, 'load_class' ) );
		}

	}

	/**
	 * Updates menu callback to display the updates area.
	 *
	 * @since 1.0.0
	 */
	public function updates_menu() {

		echo '<div id="optin-monster-updates" class="wrap">';
			screen_icon( 'options-general' );
			echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';

			/** Output the HTML for the plugin updates table if there are any updates available or a message if not */
			if ( optin_monster_updater::get_updates() )
				$this->output_updates_table();
			else
				echo '<div class="alert alert-success" class="updated"><p><strong>' . __( 'There are no updates for OptinMonster at this time. Refresh this page to check for updates.', 'optin-monster' ) . '</strong></p></div>';

		echo '</div>';

	}

	/**
	 * Load assets and other necessary items for the Updates page.
	 *
	 * Automatically check for new updates when loading this page.
	 *
	 * @since 1.0.0
	 */
	public function load_class() {

        add_action( 'admin_head', array( $this, 'icon' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
		$this->check_plugin_updates();

	}

	public function icon() {

    	?>
    	<style type="text/css">#optin-monster-updates .icon32 { background: url(<?php echo plugins_url( 'inc/css/images/mascot.png', dirname( dirname( __FILE__ ) ) ); ?>) no-repeat scroll 0 0; }</style>
    	<?php

	}

	/**
	 * Enqueue custom scripts and styles for the Updates page.
	 *
	 * @since 1.0.0
	 */
	public function load_assets() {

		/** Add plugin install styles, scripts and thickbox */
		wp_enqueue_style( 'plugin-install' );
		wp_enqueue_script( 'plugin-install' );
		add_thickbox();

	}

	/**
	 * Output the necessary HTML for the plugin update table.
	 *
	 * @since 1.0.0
	 *
	 * @global int $wp_version The current version of this particular WP instance
	 */
	public function output_updates_table() {

		global $wp_version;

		/** Start concatenating our HTML */
		$action = $this->is_multisite ? network_admin_url( 'update-core.php?action=do-optin-monster-upgrade' ) : 'update-core.php?action=do-plugin-upgrade';
		$output = '<p>' . __( 'The following plugins have new versions available. Check the ones you want to update and click “Update Plugins”. Update checks are performed automatically when you visit or reload this page.', 'optin-monster' ) . '</p>';
		$output .= '<form method="post" action="' . $action . '" name="upgrade-plugins" class="upgrade">';
			$output .= wp_nonce_field( 'upgrade-core', '_wpnonce', true, false );
			$output .= '<p><input id="upgrade-plugins" class="button" type="submit" value="' . __( 'Update Plugins', 'optin-monster' ) . '" name="upgrade" /></p>';
			$output .= '<table class="widefat" cellspacing="0" id="update-plugins-table">';
				$output .= '<thead>';
					$output .= '<tr>';
						$output .= '<th scope="col" class="manage-column check-column"><input type="checkbox" id="plugins-select-all" /></th>';
						$output .= '<th scope="col" class="manage-column"><label for="plugins-select-all">' . __( 'Select All', 'optin-monster' ) . '</label></th>';
					$output .= '</tr>';
				$output .= '</thead>';
				$output .= '<tfoot>';
					$output .= '<tr>';
						$output .= '<th scope="col" class="manage-column check-column"><input type="checkbox" id="plugins-select-all-2" /></th>';
						$output .= '<th scope="col" class="manage-column"><label for="plugins-select-all-2">' . __( 'Select All', 'optin-monster' ) . '</label></th>';
					$output .= '</tr>';
				$output .= '</tfoot>';
				$output .= '<tbody class="plugins">';
				foreach ( $this->plugins as $slug => $object ) {
					/** Iterate over any plugins that don't have updates */
					if ( ! $object->has_update )
						continue;

					$plugin_name	= isset( $object->plugin_name ) && $object->plugin_name ? $object->plugin_name : ucwords( str_replace( '-', ' ', $slug ) );
					$compat 		= '<br />' . sprintf( __( 'Compatibility with WordPress %1$s: 100%% (according to its author)', 'optin-monster' ), preg_replace( '/-.*$/', '', $wp_version ) );
					if ( is_plugin_active_for_network( $object->plugin_path ) )
						$details_url = network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $slug . '&section=changelog&TB_iframe=true&width=640&height=662' );
					else if ( $this->is_multisite )
						$details_url = self_admin_url( 'admin.php?page=optin-monster&om_iframe_request=true&tab=plugin-information&plugin=' . $slug . '&section=changelog&TB_iframe=true&width=640&height=662' );
					else
						$details_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $slug . '&section=changelog&TB_iframe=true&width=640&height=662' );
					$details_text 	= sprintf( __( 'View version %1$s details', 'optin-monster' ), $object->plugins[$slug]->new_version );
					$details 		= sprintf( '<a href="%1$s" class="thickbox" title="%2$s">%3$s</a>.', esc_url( $details_url ), esc_attr( $plugin_name ), $details_text );

					$output .= '<tr class="active">';
						$output .= '<th scope="row" class="check-column"><input type="checkbox" name="checked[]" value="' . esc_attr( $object->plugin_path ) . '" /></th>';
						$output .= '<td><p><strong>' . $plugin_name . '</strong><br />' . sprintf( __( 'You have version %1$s installed. Update to %2$s.', 'optin-monster' ), $object->version, $object->plugins[$slug]->new_version ) . ' ' . $details . $compat . '</p></td>';
					$output .= '</tr>';
				}
				$output .= '</tbody>';
			$output .= '</table>';
			$output .= '<p><input id="upgrade-plugins-2" class="button" type="submit" value="' . __( 'Update Plugins', 'optin-monster' ) . '" name="upgrade" /></p>';
		$output .= '</form>';

		/** Echo the output */
		echo $output;

	}

	/**
	 * Set our plugins property with all current update instances.
	 *
	 * @since 1.0.0
	 */
	private function set_update_instances() {

		foreach ( optin_monster_updater::get_update_instances() as $instance )
			$this->plugins->{$instance->plugin_slug} = $instance;

	}

	/**
	 * Sets the update count number beside our Updates submenu item.
	 *
	 * @since 1.0.0
	 *
	 * @global array $submenu Array of submenu data for WordPress
	 */
	private function set_update_number() {

		global $submenu;

		/** Generate the update icon information */
		$update_icon = optin_monster_updater::get_updates() ? '<span title="' . __( 'Updates Available', 'optin-monster' ) . '" class="update-plugins count-' . optin_monster_updater::get_updates() . '"><span class="update-count">' . optin_monster_updater::get_updates() . '</span></span>' : false;

		/** Loop through the submenu and output our updates number */
		if ( $update_icon ) {
			foreach ( $submenu as $slug => $array ) {
				if ( preg_match( '|optin-monster$|', $slug ) ) {
					foreach ( $array as $i => $menu_data ) {
						if ( 'optin-monster-updates' == $menu_data[2] && ! isset( $menu_data['update'] ) ) {
							$submenu[$slug][$i][0] 			= $submenu[$slug][$i][0] . ' ' . $update_icon;
							$submenu[$slug][$i]['update'] 	= true;
						}
					}
				}
			}
		}

	}

	/**
	 * Runs plugin update checks for all active OptinMonster plugins.
	 *
	 * @since 1.0.0
	 */
	private function check_plugin_updates() {

		foreach ( $this->plugins as $slug => $object )
			$object->check_for_updates( true );

	}

	/**
	 * Load our iframe update request outside of how WordPress does it if our
	 * plugin is individually activated in a MS instance.
	 *
	 * We have to do this in order to avoid WordPress sending us to the
	 * Network admin page where the plugin will not be activated.
	 *
	 * @since 1.0.0
	 *
	 * @global string $tab Used as iframe div class names, helps with styling
	 * @global string $body_id Used as the iframe body ID, helps with styling
	 */
	private function load_iframe_updates() {

		global $tab, $body_id;
		$tab = $body_id = 'plugin-information';

		/** Load the plugin-install file for updating plugins */
		require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		/** Load our styling assets */
		wp_enqueue_style( 'plugin-install' );
		wp_enqueue_script( 'plugin-install' );

		/** Define the iframe request */
		if ( ! defined( 'IFRAME_REQUEST' ) )
			define( 'IFRAME_REQUEST', true );

		/** Install our plugin information and exit since we no longer need to load any other information */
		install_plugin_information();
		exit;

	}

	/**
	 * Custom upgrade callback for updating Soliloquy and Addons inside of a MS
	 * instance.
	 *
	 * @since 1.0.0
	 */
	public function ms_upgrade() {

		global $optin_monster_license;

		if ( ! current_user_can( 'update_plugins' ) )
			wp_die( __( 'You do not have sufficient permissions to update this site.', 'optin-monster' ) );

		check_admin_referer( 'upgrade-core' );

		require_once ABSPATH . 'wp-admin/admin-header.php';
		echo '<div class="wrap">';
			screen_icon( 'plugins' );
			echo '<h2>' . esc_html( __( 'Update Plugins', 'optin-monster' ) ) . '</h2>';

			/** Store our plugins that need updating in an array */
			if ( isset( $_GET['plugins'] ) )
				$plugins = explode( ',', $_GET['plugins'] );
			elseif ( isset( $_POST['checked'] ) )
				$plugins = (array) $_POST['checked'];
			else
				$plugins = array();
			$objects = array();

			/** Store our plugins that need updating in an array */
			foreach ( $plugins as $plugin ) {
				if ( is_plugin_active_for_network( $plugin ) )
					continue;

				$slug = explode( '/', $plugin );
				$args = array(
					'remote_url' 	=> 'http://optinmonster.com/',
					'version' 		=> $this->get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $plugin, 'version' ),
					'plugin_name'	=> $this->get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $plugin, 'name' ),
					'plugin_slug' 	=> $slug[0],
					'plugin_path' 	=> $plugin,
					'plugin_url' 	=> WP_PLUGIN_URL . '/' . $slug[0],
					'time' 			=> 43200,
					'key' 			=> $optin_monster_license['key']
				);
				$objects[] = new optin_monster_updater( $args );
			}

			/** Prepare the nonce URL */
			$url 	= 'update.php?action=update-selected&plugins=' . urlencode( implode( ',', $plugins ) );
			$nonce 	= 'bulk-update-plugins';

			/** Process the plugin upgrades */
			if ( ! class_exists( 'Plugin_Upgrader' ) )
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			$upgrader = new Plugin_Upgrader( new Bulk_Plugin_Upgrader_Skin( compact( 'nonce', 'url' ) ) );
			$upgrader->bulk_upgrade( $plugins );
		echo '</div>';
		require_once ABSPATH . 'wp-admin/admin-footer.php';

	}

	/**
	 * Getter method for retrieving plugin data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin The plugin path
	 * @param string $type The type of data to retrieve
	 * @return bool|mixed False if nothing set, otherwise requested plugin data
	 */
	private function get_plugin_data( $plugin, $type = '' ) {

		if ( empty( $type ) )
			return false;

		$plugin_data = get_plugin_data( $plugin );

		switch ( $type ) {
			case 'version' :
				$plugin_info = $plugin_data['Version'];
				break;
			case 'name' :
				$plugin_info = $plugin_data['Name'];
				break;
		}

		return $plugin_info;

	}

	/**
	 * Getter method for retrieving the current plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin The plugin basename to use
	 * @return int The name of the plugin
	 */
	private function get_plugin_name( $plugin ) {

		$plugin_data = get_plugin_data( $plugin );
		return $plugin_data['Name'];

	}

	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		return self::$instance;

	}

}