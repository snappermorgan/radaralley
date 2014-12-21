<?php
/**
 * OptinMonster plugin class.
 *
 * @package   OptinMonster
 * @author    Thomas Griffin
 * @license   GPL-2.0+
 * @copyright 2013 Retyp, LLC. All rights reserved.
 */

// Define some class constants.
define( 'OPTINMONSTER_APIURL', plugins_url( 'inc/js/om.js', __FILE__ ) );

/**
 * Main plugin class.
 *
 * @package OptinMonster
 */
class optin_monster {

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $version = '1.0.8';

    /**
     * The name of the plugin.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_name = 'OptinMonster';

    /**
     * Unique plugin identifier.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_slug = 'optin-monster';

    /**
     * Plugin textdomain.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $domain = 'optin-monster';

    /**
     * Plugin file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Instance of this class.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance = null;

    /**
     * Slug of the plugin screen.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $hook = null;

    /**
     * Holds any plugin error messages.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $errors = array();

    /**
     * Flag to determine if the script has been localized.
     *
     * @since 1.0.0
     *
     * @var bool
     */
    public $is_localized = false;

    /**
     * Initialize the plugin class object.
     *
     * @since 1.0.0
     */
    private function __construct() {

        // Load plugin text domain.
        add_action( 'plguins_loaded', array( $this, 'load_plugin_textdomain' ) );

        // Load the plugin.
        add_action( 'init', array( $this, 'init' ) );

        // Handle any report downloads.
        if ( $this->is_reporting_active() )
            add_action( 'init', array( $this, 'reports' ) );

    }

    /**
     * Return an instance of this class.
     *
     * @since 1.0.0
     *
     * @return object A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance )
            self::$instance = new self;

        return self::$instance;

    }

    /**
     * Fired when the plugin is activated.
     *
     * @since 1.0.0
     *
     * @global int $wp_version The current version of WP on this install.
     *
     * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    public static function activate( $network_wide ) {

        global $wp_version;
        $instance = optin_monster::get_instance();

        // If not WP 3.5 or greater, bail.
        if ( version_compare( $wp_version, '3.5.1', '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( 'Sorry, but your version of WordPress, <strong>' . $wp_version . '</strong>, does not meet the required version of <strong>3.5.1</strong> to run OptinMonster properly. The plugin has been deactivated. <a href="' . get_admin_url() . '">Click here to return to the Dashboard</a>.' );
		}

    	if ( is_multisite() ) :
	    	global $wpdb;
	      	$site_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->blogs ORDER BY blog_id" ) );
			foreach ( (array) $site_list as $site ) :
				switch_to_blog( $site->blog_id );

				// Set default license option.
		        $license_option = get_option( 'optin_monster_license' );
		        if ( ! $license_option || empty( $license_option ) )
		            update_option( 'optin_monster_license', optin_monster::default_options( 'license' ) );

                // Set default provider option.
		        $provider_option = get_option( 'optin_monster_providers' );
		        if ( ! $provider_option || empty( $provider_option ) )
		            update_option( 'optin_monster_providers', optin_monster::default_options( 'provider' ) );

                // Set default updates option.
		        $updates_option = get_option( 'optin_monster_updates_' . $instance->plugin_slug );
		        if ( ! $updates_option || empty( $updates_option ) )
		            update_option( 'optin_monster_updates_' . $instance->plugin_slug, optin_monster::default_options( 'updates' ) );

				restore_current_blog();
			endforeach;
		else :
	        // Set default license option.
	        $license_option = get_option( 'optin_monster_license' );
	        if ( ! $license_option || empty( $license_option ) )
	            update_option( 'optin_monster_license', optin_monster::default_options( 'license' ) );

            // Set default provider option.
	        $provider_option = get_option( 'optin_monster_providers' );
	        if ( ! $provider_option || empty( $provider_option ) )
	            update_option( 'optin_monster_providers', optin_monster::default_options( 'provider' ) );

            // Set default updates option.
	        $updates_option = get_option( 'optin_monster_updates_' . $instance->plugin_slug );
	        if ( ! $updates_option || empty( $updates_option ) )
	            update_option( 'optin_monster_updates_' . $instance->plugin_slug, optin_monster::default_options( 'updates' ) );
	    endif;

	    // Add in the custom database table if the user wants reporting.
	    if ( $instance->is_reporting_active() ) :
    	    require_once plugin_dir_path( __FILE__ ) . 'inc/admin/db.php';
            $optin_monster_hits = new optin_monster_hits();
            $optin_monster_hits->activate();

            // Add in cron scheduling for clearing reporting data at set intervals.
            wp_schedule_event( strtotime( '+30 days'), 'om-clear-report', 'optin_monster_clear_reporting' );
        endif;

    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @since 1.0.0
     *
     * @param boolean $network_wide True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
     */
    public static function deactivate( $network_wide ) {

        // Remove any scheduled cron events.
        if ( optin_monster::get_instance()->is_reporting_active() )
            wp_clear_scheduled_hook( 'optin_monster_clear_reporting' );


    }

    /**
     * Fired when the plugin is uninstalled.
     *
     * @since 1.0.0
     *
     * @param boolean $network_wide True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
     */
    public static function uninstall( $network_wide ) {

        $instance = optin_monster::get_instance();

		if ( is_multisite() ) :
			global $wpdb;
	      	$site_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->blogs ORDER BY blog_id" ) );
			foreach ( (array) $site_list as $site ) :
				switch_to_blog( $site->blog_id );
				delete_option( 'optin_monster_license' );
				delete_option( 'optin_monster_providers' );
				delete_option( 'optin_monster_updates_' . $instance->plugin_slug );
				restore_current_blog();
			endforeach;
		else :
	    	delete_option( 'optin_monster_license' );
	    	delete_option( 'optin_monster_providers' );
	    	delete_option( 'optin_monster_updates_' . $instance->plugin_slug );
	    endif;

	    // Remove the custom database table.
	    if ( $instance->is_reporting_active() ) :
    	    require_once plugin_dir_path( __FILE__ ) . 'inc/admin/db.php';
            $optin_monster_hits = new optin_monster_hits();
            $optin_monster_hits->uninstall();
        endif;

    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {

        $domain = $this->domain;
        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

        load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

    }

    /**
     * Loads the plugin.
     *
     * @since 1.0.6
     */
    public function init() {

        // Load necessary global class files.
        require_once plugin_dir_path( __FILE__ ) . 'inc/admin/post-type.php';
        require_once plugin_dir_path( __FILE__ ) . 'inc/common/account.php';
        require_once plugin_dir_path( __FILE__ ) . 'inc/admin/ajax.php';

        // If trying to download data, do that now.
        if ( ! empty( $_GET['download_csv'] ) && $this->is_reporting_active() )
            $this->reports();

        // Load the license option.
        global $optin_monster_license;
		$optin_monster_license = get_option( 'optin_monster_license' );

        // Load necessary admin files.
        if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) :
            require_once plugin_dir_path( __FILE__ ) . 'inc/admin/tab-optins.php';
            require_once plugin_dir_path( __FILE__ ) . 'inc/admin/tab-integrations.php';
            require_once plugin_dir_path( __FILE__ ) . 'inc/admin/tab-rewards.php';
            require_once plugin_dir_path( __FILE__ ) . 'inc/admin/tab-misc.php';

            // Load reporting if active.
            if ( $this->is_reporting_active() )
                require_once plugin_dir_path( __FILE__ ) . 'inc/admin/tab-reports.php';

            // Load the plugin updater.
            if ( ! empty( $optin_monster_license['key'] ) ) {
                require_once plugin_dir_path( __FILE__ ) . 'inc/admin/updater.php';
                require_once plugin_dir_path( __FILE__ ) . 'inc/admin/updates.php';

				$args = array(
					'remote_url' 	=> 'http://optinmonster.com/',
					'version' 		=> $this->version,
					'plugin_name'	=> $this->plugin_name,
					'plugin_slug' 	=> $this->plugin_slug,
					'plugin_path' 	=> plugin_basename( dirname( __FILE__ ) . '/' . $this->plugin_slug . '.php' ),
					'plugin_url' 	=> WP_PLUGIN_URL . '/' . $this->plugin_slug,
					'time' 			=> 43200,
					'key' 			=> $optin_monster_license['key']
				);

				// Load the updater class.
				$optin_monster_updater = new optin_monster_updater( $args );

				// Load the updates page.
				$optin_monster_updates = new optin_monster_updates();

				// Load the Addons page.
				require_once plugin_dir_path( __FILE__ ) . 'inc/admin/tab-addons.php';
			}
        endif;

        // Add in the custom cron scheduler.
        if ( $this->is_reporting_active() ) {
            add_action( 'optin_monster_clear_reporting', array( $this, 'clear_reporting' ) );
            add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );
            add_action( 'wp', array( $this, 'check_cron' ) );
        }

        // Load the plugin settings link shortcut.
        add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( __FILE__ ) . 'optin-monster.php' ), array( $this, 'settings_link' ) );

        // Add the options page and menu item.
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

        // Filter the content to add in the optin code.
        add_action( 'wp_footer', array( $this, 'maybe_do_optin_monster' ) );

    }

    /**
     * Clears reporting data at the specified interval.
     *
     * @since 1.0.0
     */
    public function clear_reporting() {

        global $wpdb;
        $table_name = $wpdb->prefix . 'om_hits_log';

        // Delete out data from the specified interval.
        $wpdb->query( "TRUNCATE TABLE $table_name" );

    }

    public function check_cron() {

        if ( ! wp_next_scheduled( 'optin_monster_clear_reporting' ) )
			wp_schedule_event( time(), 'om-clear-report', 'optin_monster_clear_reporting' );

    }

    public function add_schedules( $schedules = array() ) {

        $license = get_option( 'optin_monster_license' );
        $time = time();
        $future = 0 == $license['report'] ? strtotime( '10 January 2035' ) : strtotime( '+' . $license['report'] . ' days' );

        $diff = $future - $time;
        $interval = floor( $diff );
        $schedules['om-clear-report'] = array(
            'interval' => $interval,
            'display'  => __( 'Custom OptinMonster Clear Interval', 'optin-monster' )
        );
        return $schedules;

    }

    /**
     * Creates a CSV download of conversion data.
     *
     * @since 1.0.0
     */
    public function reports() {

        // Return early if not requesting a download or doing ajax.
        if ( empty( $_GET['download_csv'] ) || defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

        // Return early if no user is logged in.
        if ( ! is_user_logged_in() ) return;

        // Return early if the user permission is not high enough to download.
        if ( ! current_user_can( 'manage_options' ) ) return;

        $range  = ! empty( $_GET['range'] )  ? stripslashes( $_GET['range'] ) : 'today';
    	$switch = ! empty( $_GET['switch'] ) ? stripslashes( $_GET['switch'] ) : '';
    	$all    = 'all' == $switch || empty( $_GET['range'] ) && empty( $_GET['switch'] ) ? true : false;

        // Load the downloads data.
        global $optin_monster_account;
		$data = $optin_monster_account->get_downloads_data( $all, $switch );

		// Turn GZIP compression off.
		if ( ini_get( 'zlib.output_compression' ) )
		    ini_set( 'zlib.output_compression', 'Off' );

        if ( ! empty( $switch ) )
		    $fileName = 'om-data-' . $range . '-' . $switch . '-' . time() . '.csv';
        else
            $fileName = 'om-data-' . $range . '-' . time() . '.csv';

		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Description: File Transfer');
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename={$fileName}");
		header("Expires: 0");
		header("Pragma: public");

		$fh = @fopen( 'php://output', 'w' );

		// If we can't open the file, output an error.
		if ( ! $fh ) {
    		$this->set_error['om-error'] = __( 'There was an error creating a CSV stream for your data. Please try again.', 'optin-monster' );
    		return;
		}

		$headerDisplayed = false;
		foreach ( $data as $i => $array ) {
		    foreach ( $array as $i => $obj ) {
		        $input['Hit ID']     = $obj->hit_id;
		        $input['Optin ID']   = $obj->optin_id;
		        $input['Hit Date']   = $obj->hit_date;
		        $input['Hit Type']   = $obj->hit_type;
		        $input['Referer']    = $obj->referer;
		        $input['User Agent'] = $obj->user_agent;
    		    if ( ! $headerDisplayed ) {
    				fputcsv( $fh, array_keys( $input ) );
    				$headerDisplayed = true;
    			}

    			fputcsv( $fh, $input );
		    }
		}

		// Close the file.
		fclose($fh);

		// Make sure nothing else is sent, our file is done.
		exit;

    }

    /**
     * Add Settings page to plugin action links in the Plugins table.
     *
     * @since 1.0.0
     *
     * @param array $links Default plugin action links.
     * @return array $links Amended plugin action links.
     */
    public function settings_link( $links ) {

        $setting_link = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'page' => 'optin-monster' ), admin_url( 'admin.php' ) ), __( 'Settings', 'optin-monster' ) );
        array_unshift( $links, $setting_link );

        return $links;

    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since 1.0.0
     */
    public function add_plugin_admin_menu() {

        // Register the menu.
        $this->hook = add_menu_page(
            __( 'OptinMonster', 'optin-monster' ),
            __( 'OptinMonster', 'optin-monster' ),
            'manage_options',
            $this->plugin_slug,
            array( $this, 'display_plugin_admin_page' ),
            plugins_url( 'inc/css/images/menu.png', __FILE__ ),
            374
        );

        // If successful, load admin assets only on that page.
        if ( $this->hook )
            add_action( 'load-' . $this->hook, array( $this, 'load_plugin_assets' ) );

    }

    /**
     * Loads assets only on this plugin's administration dashboard.
     *
     * @since 1.0.0
     */
    public function load_plugin_assets() {

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

    }

    /**
     * Register and enqueue admin-specific stylesheets.
     *
     * @since 1.0.0
     */
    public function enqueue_admin_styles() {

        wp_enqueue_style( $this->plugin_slug . '-google-fonts', '//fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600|Bree+Serif' );
        wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'inc/css/admin.css', __FILE__ ), array( $this->plugin_slug . '-google-fonts' ), $this->version );

        if ( isset( $_GET['tab'] ) && 'addons' == $_GET['tab'] ) {
            /** Load the CSS for the Addons area */
            wp_register_style( 'optinmonster-addons', plugins_url( 'inc/css/addons.css', optin_monster::get_instance()->file ), array(), optin_monster::get_instance()->version );
            wp_enqueue_style( 'optinmonster-addons' );
        }

    }

    /**
     * Register and enqueue admin-specific JS.
     *
     * @since 1.0.0
     */
    public function enqueue_admin_scripts() {

        // Enqueue scripts.
        wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'inc/js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );

        // Localize scripts.
        wp_localize_script(
            $this->plugin_slug . '-admin-script',
            'optin_monster',
            array(
                'confirm' => __( 'This will remove all API data from this site. Are you sure you want to continue?', 'optin-monster' )
            )
        );

        if ( isset( $_GET['tab'] ) && 'addons' == $_GET['tab'] ) {
            /** Load the JS for the Addons area */
    		wp_register_script( 'optinmonster-addons', plugins_url( 'inc/js/addons.js', optin_monster::get_instance()->file  ), array( 'jquery' ), optin_monster::get_instance()->version, true );

    		/** Prepare args to be passed to wp_localize_script */
    		$args = array(
    			'active'			=> __( 'Status: Active', 'optin-monster' ),
    			'activate'			=> __( 'Activate', 'optin-monster' ),
    			'activating'		=> __( 'Activating...', 'optin-monster' ),
    			'activate_nonce' 	=> wp_create_nonce( 'optinmonster_activate_addon' ),
    			'connect_error'		=> __( 'There was an error connecting. Please try again.', 'optin-monster' ),
    			'deactivate'		=> __( 'Deactivate', 'optin-monster' ),
    			'deactivating'		=> __( 'Deactivating...', 'optin-monster' ),
    			'deactivate_nonce' 	=> wp_create_nonce( 'optinmonster_deactivate_addon' ),
    			'inactive'			=> __( 'Status: Inactive', 'optin-monster' ),
    			'install'			=> __( 'Install Addon', 'optin-monster' ),
    			'installing'		=> __( 'Installing...', 'optin-monster' ),
    			'install_nonce' 	=> wp_create_nonce( 'optinmonster_install_addon' ),
    			'not_installed'		=> __( 'Status: Not Installed', 'optin-monster' ),
    			'proceed'			=> __( 'Proceed', 'optin-monster' ),
    			'spinner'			=> plugins_url( 'inc/css/images/loading.gif', optin_monster::get_instance()->file ),
    		);

    		wp_localize_script( 'optinmonster-addons', 'optinmonster_addon', $args );
    		wp_enqueue_script( 'optinmonster-addons' );
        }

    }

    /**
     * Render the settings page for this plugin.
     *
     * @since 1.0.0
     */
    public function display_plugin_admin_page() {

        ?>
        <div class="wrap optin-monster">
            <?php screen_icon( 'options-general' ); ?>
            <h2><?php echo $this->get_page_title(); ?></h2>

            <?php if ( ! empty( $this->errors ) ) : ?>
                <?php foreach ( $this->errors as $id => $message ) : ?>
                    <?php $class = 'om-success' == $id ? ' alert-success' : ' alert-error'; ?>
                    <div class="alert <?php echo $class; ?> <?php sanitize_html_class( $id ); ?>">
                        <p><strong><?php echo $message; ?></strong></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <h2 class="nav-tab-wrapper">

                <a class="nav-tab<?php echo ( isset( $_GET['tab'] ) && 'optins' == $_GET['tab'] || empty( $_GET['tab'] ) ? ' nav-tab-active' : '' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'optin-monster', 'tab' => 'optins' ), admin_url( 'admin.php' ) ); ?>" title="<?php echo esc_attr_e( 'Optins', 'optin-monster' ); ?>"><?php _e( 'Optins', 'optin-monster' ); ?></a>
                <?php do_action( 'optin_monster_nav_tab_optins' ); ?>

                <?php if ( $this->is_reporting_active() ) : ?>
                <a class="nav-tab<?php echo ( isset( $_GET['tab'] ) && 'reports' == $_GET['tab'] ? ' nav-tab-active' : '' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'optin-monster', 'tab' => 'reports' ), admin_url( 'admin.php' ) ); ?>" title="<?php echo esc_attr_e( 'Reports', 'optin-monster' ); ?>"><?php _e( 'Reports', 'optin-monster' ); ?></a>
                <?php do_action( 'optin_monster_nav_tab_reports' ); ?>
                <?php endif; ?>

                <a class="nav-tab<?php echo ( isset( $_GET['tab'] ) && 'integrations' == $_GET['tab'] ? ' nav-tab-active' : '' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'optin-monster', 'tab' => 'integrations' ), admin_url( 'admin.php' ) ); ?>" title="<?php echo esc_attr_e( 'Integrations', 'optin-monster' ); ?>"><?php _e( 'Integrations', 'optin-monster' ); ?></a>
                <?php do_action( 'optin_monster_nav_tab_integrations' ); ?>

                <a class="nav-tab<?php echo ( isset( $_GET['tab'] ) && 'rewards' == $_GET['tab'] ? ' nav-tab-active' : '' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'optin-monster', 'tab' => 'rewards' ), admin_url( 'admin.php' ) ); ?>" title="<?php echo esc_attr_e( 'Rewards', 'optin-monster' ); ?>"><?php _e( 'Rewards', 'optin-monster' ); ?></a>
                <?php do_action( 'optin_monster_nav_tab_rewards' ); ?>

                <a class="nav-tab<?php echo ( isset( $_GET['tab'] ) && 'addons' == $_GET['tab'] ? ' nav-tab-active' : '' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'optin-monster', 'tab' => 'addons' ), admin_url( 'admin.php' ) ); ?>" title="<?php echo esc_attr_e( 'Addons', 'optin-monster' ); ?>"><?php _e( 'Addons', 'optin-monster' ); ?></a>
                <?php do_action( 'optin_monster_nav_tab_addons' ); ?>

                <a class="nav-tab<?php echo ( isset( $_GET['tab'] ) && 'misc' == $_GET['tab'] ? ' nav-tab-active' : '' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'optin-monster', 'tab' => 'misc' ), admin_url( 'admin.php' ) ); ?>" title="<?php echo esc_attr_e( 'Misc', 'optin-monster' ); ?>"><?php _e( 'Misc', 'optin-monster' ); ?></a>
                <?php do_action( 'optin_monster_nav_tab_misc' ); ?>

            </h2>

            <?php if ( ! empty( $_GET['tab'] ) ) do_action( 'optin_monster_tab_' . $_GET['tab'] ); else do_action( 'optin_monster_tab_optins' ); ?>

        </div>
        <?php

    }

    /**
     * Retrieves the appropriate title based on current admin view.
     *
     * @since 1.0.0
     */
    public function get_page_title() {

        $title = '';

        if ( empty( $_GET['tab'] ) )
            return $title = __( 'OptinMonster Overview', 'optin-monster' );

        switch ( $_GET['tab'] ) {
            case 'reports' :
                $title = __( 'OptinMonster Reports', 'optin-monster' );
            break;
            case 'integrations' :
                $title = __( 'OptinMonster Integrations', 'optin-monster' );
            break;
            case 'rewards' :
                $title = __( 'OptinMonster Rewards', 'optin-monster' );
            break;
            case 'addons' :
                $title = __( 'OptinMonster Addons', 'optin-monster' );
            break;
            case 'misc' :
                $title = __( 'OptinMonster Miscellaneous Settings', 'optin-monster' );
            break;
            case 'optins' :
                if ( isset( $_GET['action'] ) )
                    $title = __( 'OptinMonster Builder', 'optin-monster' );
                else
                    $title = __( 'OptinMonster Overview', 'optin-monster' );
            break;
            default :
                $title = apply_filters( 'optin_monster_title', '', $_GET['tab'] );
            break;
        }

        return $title ? $title : __ ( 'OptinMonster', 'optin-monster' );

    }

    /**
     * Maybe adds the OptinMonster code just before the closing </body> tag.
     *
     * @since 1.0.0
     */
    public function maybe_do_optin_monster() {

        // If a URL suffix is set to not load optinmonster, don't do anything.
        if ( isset( $_GET['omhide'] ) && $_GET['omhide'] )
            return;

        // Prepare variables.
        $post_id      = get_queried_object_id();
        $optins       = get_posts( array( 'post_type' => 'optin', 'posts_per_page' => -1 ) );
        $init         = array();
        $has_lightbox = false; // Flag for ensuring only one lightbox optin is output on any given page.

        if ( ! $optins ) return;

        // Loop through each optin and optionally output it on the site.
        foreach ( $optins as $optin ) :
            $meta = get_post_meta( $optin->ID, '_om_meta', true );
            $slug = $optin->post_name;
            $type = $meta['type'];

            // If this is a clone, skip over it. It will be toggled with the parent.
            if ( ! empty( $meta['is_clone'] ) )
                continue;

            // If the optin is not enabled, pass over to the next optin.
            if ( empty( $meta['display']['enabled'] ) || ! $meta['display']['enabled'] )
                continue;

            // If the optin is to be output in the global scope, get the code and break.
            if ( isset( $meta['display']['global'] ) && $meta['display']['global'] && ! ( 'lightbox' == $type && $has_lightbox ) ) {
                $init[$slug] = $this->get_optin_monster_code( $slug );

                // If this is a lightbox optin, set flag to true.
                if ( 'lightbox' == $type )
                    $has_lightbox = true;

                continue;
            }

            // If the optin is only to be shown on specific post IDs, get the code and break.
            if ( ! empty( $meta['display']['exclusive'] ) && ! ( 'lightbox' == $type && $has_lightbox ) ) {
                if ( in_array( $post_id, $meta['display']['exclusive'] ) ) {
                    $init[$slug] = $this->get_optin_monster_code( $slug );

                    // If this is a lightbox optin, set flag to true.
                    if ( 'lightbox' == $type )
                        $has_lightbox = true;

                    continue;
                }
            }

            // If the optin is only to be shown on particular categories, get the code and break.
            if ( ! empty( $meta['display']['categories'] ) && ! ( 'lightbox' == $type && $has_lightbox ) ) {
                $categories = wp_get_object_terms( $post_id, 'category', array( 'fields' => 'ids' ) );
                foreach ( (array) $categories as $category_id ) {
                    if ( in_array( $category_id, $meta['display']['categories'] ) && ! is_archive() ) {
                        $init[$slug] = $this->get_optin_monster_code( $slug );

                        // If this is a lightbox optin, set flag to true.
                        if ( 'lightbox' == $type )
                            $has_lightbox = true;

                        continue 2;
                    }
                }
            }

            // Finally, just check where to output the code in general.
            if ( ! empty( $meta['display']['show'] ) && ! ( 'lightbox' == $type && $has_lightbox ) ) {
                // If showing on index pages and we are on an index page, show the optin.
                if ( in_array( 'index', (array) $meta['display']['show'] ) ) {
                    if ( is_front_page() || is_home() || is_archive() || is_search() ) {
                        $init[$slug] = $this->get_optin_monster_code( $slug );

                        // If this is a lightbox optin, set flag to true.
                        if ( 'lightbox' == $type )
                            $has_lightbox = true;

                        continue;
                    }
                }

                // Check if we should show on a selected post type.
                if ( in_array( get_post_type(), (array) $meta['display']['show'] ) && ! ( is_front_page() || is_home() || is_archive() || is_search() ) && ! ( 'lightbox' == $type && $has_lightbox ) ) {
                    $init[$slug] = $this->get_optin_monster_code( $slug );

                    // If this is a lightbox optin, set flag to true.
                    if ( 'lightbox' == $type )
                        $has_lightbox = true;

                    continue;
                }
            }
        endforeach;

        // If the init code is empty, do nothing.
        if ( empty( $init ) )
            return;

        // Allow devs to filter the final output for more granular control over optin targeting.
        // Devs should return the value for the slug key as false if the conditions are not met.
        $init = apply_filters( 'optinmonster_output', $init );

        // If the init code is still available, output it.
        if ( $init && ! empty( $init ) ) {
            foreach ( (array) $init as $optin ) {
                if ( $optin )
                    echo $optin;
            }
        }

    }

    /**
     * Returns the init code for the optin.
     *
     * @since 1.0.0
     */
    public function get_optin_monster_code( $slug ) {

        $apiurl = plugins_url( 'inc/js/om.js', __FILE__ );
        return '<div id="om-' . $slug . '"></div><script type="text/javascript">var ' . str_replace( '-', '_', $slug ) . ';(function(d, i){var om = d.createElement(i),omo = {"optin" : "' . $slug . '", "ajax" : "' . admin_url( 'admin-ajax.php' ) . '"};om.src = "' . $apiurl . '";om.onload = om.onreadystatechange = function(){var s = this.readyState;if (s) if (s != "complete") if (s != "loaded") return;try{' . str_replace( '-', '_', $slug ) . ' = new OptinMonster(); ' . str_replace( '-', '_', $slug ) . '.init(omo);}catch(e){}};(d.getElementsByTagName("head")[0] || d.documentElement).appendChild(om);})(document, "script");</script>';

    }

    /**
     * Loads the default plugin options.
     *
     * @since 1.0.0
     *
     * @return array Array of default plugin options.
     */
    public static function default_options( $type ) {

        $ret = '';

        switch ( $type ) {
            case 'license' :
                $ret = array(
                    'key'           => '',
                    'type'          => '',
                    'has_renew'     => false,
                    'needs_renew'   => false,
                    'report'        => 30,
                    'has_error'     => false,
                    'global_cookie' => 0
                );
                break;
            case 'provider' :
            case 'updates' :
                $ret = false;
                break;
        }

        return $ret;

    }

    /**
     * Sets the error message into the $errors property.
     *
     * @since 1.0.0
     */
    public function set_error( $id, $error ) {

        $this->errors[$id] = $error;

    }

    public function get_setting( $field, $subfield = '' ) {

        $option = get_option( 'optin_monster_license' );

		if ( ! empty( $subfield ) )
			return isset( $option[$field][$subfield] ) ? $option[$field][$subfield] : '';
		else
			return isset( $option[$field] ) ? $option[$field] : '';

	}

	public function is_reporting_active() {

        return ! defined( 'OPTINMONSTER_REPORTING' ) || defined( 'OPTINMONSTER_REPORTING' ) && OPTINMONSTER_REPORTING;

	}

}