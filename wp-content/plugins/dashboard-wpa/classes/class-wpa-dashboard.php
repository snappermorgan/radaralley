<?php
/**
 * WP Avengers Dashboard class
 *
 * @class   WPA_Dashboard
 * @package  WPA Dashboard
 * @category Class
 * @since  2.0
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPA_Dashboard {

	private $api_endpoint;
	private $version = '2.3.2';
	private $updater;
	private $update_check_interval;
	private $classes_dir;
	private $is_batch = false;
	private $batch_responses = array();
	private $ajax_actions = array();
	private $batch_actions = array();
	private $batch_operation_index = 0;
	private $plugin_url;
	private $secure_ajax = true;
	private $active_plugins = null;
	private $plugin_file;
	private $plugin_basename;

	function __construct( $file ) {
		$this->api_endpoint = trailingslashit( "http://api.wpavengers.com" );
		$this->update_check_interval = 24 * 60 * 60;
		$this->classes_dir = plugin_dir_path ( __FILE__ );
		$this->plugin_url = plugins_url('', dirname(__FILE__));
		$this->plugin_file = $file;
		$this->plugin_basename = plugin_basename( $this->plugin_file );

		$this->ajax_actions = array(
			'install-plugin' => 'install',
			'upgrade-plugin' => 'upgrade',
			'activate' => 'activate',
			'deactivate' => 'deactivate',
			'wpa-batch' => 'batch',
			'refresh-products' => 'on_cron'
		);

		$secure_ajax_actions = array('wpa-batch');

		foreach ( $this->ajax_actions as $action => $callback ) {
			if ($this->secure_ajax == true && !in_array($action, $secure_ajax_actions)) {
				continue;
			}
			add_action( 'wp_ajax_'.$action, array( &$this, $callback ) );
		}

		$this->batch_actions = array_diff( array_keys( $this->ajax_actions ), array( 'wpa-batch', 'refresh-products' ) );

		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( &$this, 'menu_links' ) );
		} else {
			add_action( 'admin_menu', array( &$this, 'menu_links' ) );
		}
		add_action( 'admin_enqueue_scripts', array( &$this, 'load_js_css' ) );
		add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'update_plugin_url_in_site_transient' ), 999 );

		add_filter( 'pre_update_option_active_plugins', array( &$this, 'filter_active_plugins' ), 1 );
		if ( is_multisite() ) {
			add_filter( 'pre_update_option_active_sitewide_plugins', array( &$this, 'filter_active_plugins' ), 1 );
		}
		register_activation_hook( $file, array( &$this, 'on_activation' ) );
		register_deactivation_hook( $file, array( &$this, 'on_deactivation' ) );
	}

	public function on_activation() {
	}

	public function on_deactivation() {
		wp_clear_scheduled_hook( 'wpad_daily' );
	}

	public function filter_active_plugins ( $new_plugins ) {
		if ( ! is_multisite() ) {
			$old_plugins = get_option('active_plugins', array() );
		} else {
			$old_plugins = get_option('active_sitewide_plugins', array());
		}
		if ( $new_plugins == $old_plugins ) {
			return $new_plugins;
		}
		if ( !is_array( $new_plugins ) || !is_array( $old_plugins ) ) {
			return $new_plugins;
		}
		if ( ( in_array( $this->plugin_basename, $old_plugins  ) && ! in_array( $this->plugin_basename, $new_plugins ) ) 
		|| (is_multisite( ) && array_key_exists( $this->plugin_basename, $old_plugins  ) && ! array_key_exists( $this->plugin_basename, $new_plugins )  ) ) {
			$products = $this->get_products( );
			$our_plugins = array();
			if (!empty($products) && isset($products['plugins'])) {
				foreach ((array) $products['plugins'] as $key => $info) {
					if ( strpos($key, 'dashboard-wpa') === false ) {
						if ( array_key_exists( $info['slug'], $new_plugins ) ) {
							unset( $new_plugins [ $info['slug'] ] );
							$our_plugins[ $info['slug'] ] = time();
						} else if (in_array( $info['slug'], $new_plugins ) ) {
							$key = array_search( $info['slug'], $new_plugins );
							unset( $new_plugins[ $key ] );
							$our_plugins[ $info['slug'] ] = time();
						}
					}
				}
			}
			if ( sizeof( $our_plugins ) > 0 ) {
				if (!( is_multisite() && is_super_admin() )) {
					update_option( 'recently_activated', $our_plugins + (array) get_option( 'recently_activated' ) );
				}
				update_site_option( 'wpavengers_plugins_deactivated', array_keys( $our_plugins ) );
			}
		} else if ( ( ! in_array( $this->plugin_basename, $old_plugins  ) && in_array( $this->plugin_basename, $new_plugins ) ) 
			|| (is_multisite() && ! array_key_exists( $this->plugin_basename, $old_plugins  ) && array_key_exists( $this->plugin_basename, $new_plugins )  ) ) {
			
			// Reactivate our plugins
			$our_plugins = get_site_option( 'wpavengers_plugins_deactivated', array() ); 
			if (!( is_multisite() && is_super_admin() )) {
				$recent = (array) get_option('recently_activated' );
			}
			if (! empty( $our_plugins ) ) {
				foreach ( $our_plugins as $plugin ) {
					if ( !is_wp_error( validate_plugin( $plugin ) ) ) {
						if ( is_multisite() ) {
							$new_plugins[ $plugin ] = time();
						} else {
							$new_plugins[] = $plugin;
							unset( $recent[ $plugin ] );
						}

					}
				}
				if (!( is_multisite() && is_super_admin() )) {
					update_option( 'recently_activated', $recent );
				}
				delete_site_option( 'wpavengers_plugins_deactivated' ); 
			}
		}
		if (! is_multisite() && in_array($this->plugin_basename, $new_plugins ) ) {
			$position = array_search($this->plugin_basename, $new_plugins);
			if ($position) { 
				array_splice($new_plugins, $position, 1);
				array_unshift($new_plugins, $this->plugin_basename);
			}
		}
		return $new_plugins;
	}

	public function on_cron() {
		$products = $this->get_products( true );
		if (!empty($products) && isset($products['plugins'])) {
			$update_counts = $this->detectUpdateStatus( $products );
			update_site_option('wpavengers_update_counts', $update_counts);
		}
	}

	public function menu_links() {
		if ( !defined('WPAD_MENU_LOCATION') ) define('WPAD_MENU_LOCATION', '2.02');

		// Perform update check if due
		if (current_time( 'timestamp', 1 ) > $this->update_check_interval + get_site_option( 'wpavengers_last_update_check_at', 0 )) {
			$this->on_cron();
			update_site_option( 'wpavengers_last_update_check_at', current_time( 'timestamp', 1 ) );
		}

		$update_badge = '';
		$update_counts = get_site_option('wpavengers_update_counts', array() );
		if (array_key_exists('new', $update_counts) && array_key_exists('updated', $update_counts) && ($update_counts['new'] > 0 || $update_counts['updated'] > 0) ) {
			$total = 0;
			$update_messages = array();
			if ($update_counts['new'] > 0) {
				$total += $update_counts['new'];
				$update_messages[] = sprintf(_n("%d New Plugin", "%d New Plugins", $update_counts['new']), $update_counts['new']);
			}
			if ($update_counts['updated'] > 0) {
				$total += $update_counts['updated'];
				$update_messages[] = sprintf(_n("One Update", "%d Updates", $update_counts['updated']), $update_counts['updated']);
			}
			if ($total > 0) {
				$update_badge = '<span class="update-plugins count-'.$total.'" title="'.implode(", ", $update_messages).'"><span class="update-count">'.$total.'</span></span>';	
			}			
		}

		$page = add_menu_page( __( 'WP Avengers Dashboard' ), __( 'WP Avengers' ).$update_badge, 'install_plugins', 'wpa_dashboard', array( &$this, 'page_dashboard' ), $this->plugin_url.'/assets/images/icon.png', WPAD_MENU_LOCATION );
		add_action( 'admin_print_styles-' . $page, array(&$this, 'styles_dashboard') );
		add_action( 'admin_print_scripts-' . $page, array(&$this, 'scripts_dashboard') );
	}

	public function load_js_css( $hook ) {
		if ( $hook != 'toplevel_page_wpa_dashboard') {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// JQuery
		if ( !wp_script_is('jquery') ) {
            wp_enqueue_script('jquery');
            wp_enqueue_style('jquery');
        }

		// CSS
		wp_enqueue_style( 'wpad-admin-css', $this->plugin_url.'/assets/css/admin'.'.css', array(), $this->version);

		// JS
		wp_enqueue_script( 'wpa_mixitup', $this->plugin_url.'/assets/js/jquery.mixitup'.$suffix.'.js', array( 'jquery' ), $this->version );
		wp_enqueue_script( 'wpa_dashboard', $this->plugin_url.'/assets/js/wpa.dashboard'.'.js', array( 'jquery' ), $this->version );
        
        // Clean page of all notices
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'network_admin_notices' );
		remove_all_actions( 'all_admin_notices' );
	}

	public function styles_dashboard() {
		
	}

	public function scripts_dashboard() {	
		echo "<script type='text/javascript'>\n";
		echo 'var wpa_api_endpoint = "'. untrailingslashit($this->api_endpoint) .'";';
		echo 'var wpa_nonce = "'. wp_create_nonce("wpad-dashboard-ajax-nonce") .'";';
		echo "\n</script>";
	}

	public function page_dashboard() {
		
		if ( !current_user_can( 'install_plugins' ) ) {
			return;
		}

		$force_reload = !empty($_REQUEST['reload']);

		$products = $this->get_products( $force_reload );
		if (!empty($products) && isset($products['plugins'])) {
			$update_counts = $this->detectUpdateStatus( $products );
			update_site_option('wpavengers_update_counts', $update_counts);
			$plugins = $products['plugins'];
		}
?>		
	<div id="wpad-dashboard" class="wrap">
		<form method="POST">
		<?php screen_icon( 'index' ); ?>
		<h2><?php _e( 'WP Avengers Dashboard' ); ?></h2>
<?php
		// Requirement checks...
		if (! is_woocommerce_active() ) {
			echo '<div class="updated fade"><p>'.__('<strong>WooCommerce is not installed / active.</strong> Please make sure WooCommerce is working before installing any of our plugins.').'</p></div>';
		}
?>
		<div class="tablenav top">	
		<div class="alignleft actions">
			<ul id="filter-options">
			  <li class="filter button" data-filter="all"><?php _e( 'all' ); ?></li> 
			  <li class="filter button" data-filter="us_new"><?php _e( 'new' ); ?></li> 
			  <li class="filter button" data-filter="us_updated"><?php _e( 'updated' ); ?></li> 
			</ul>
		</div>

		<div class="alignleft actions">
			<input id="cb-select-all" type="checkbox"> <?php _e( 'Select All' ); ?>
		</div>

		<div class="alignleft actions">
			<select name="bulk_actions">
				<option value=""><?php _e('Bulk Actions'); ?></option>
				<option value="install_update_activate"><?php _e('Install/Upgrade and Activate'); ?></option>
				<option value="deactivate"><?php _e('Deactivate'); ?></option>
				<option value="activate"><?php _e('Activate'); ?></option>
			</select>
			<input type="submit" name="" id="doaction" class="button" value="<?php _e('Apply'); ?>">
		</div>

		<div class="alignleft actions">
			<div id="actions_progressbar">
				<img src="<?php echo $this->plugin_url; ?>/assets/images/loader.gif" />
			</div>
		</div>

		<div class="alignright actions">
			<?php 
			$reload_url = (is_multisite()) ?  network_admin_url('admin.php?page=wpa_dashboard&reload=1') : admin_url('admin.php?page=wpa_dashboard&reload=1');
			?>
			<a href="<?php echo $reload_url; ?>"><?php _e('Check for updates');?></a>
		</div>
		</div>

		<div id="message"></div>

		<div id="ftp-wrap"></div>

		<ul id="products_grid" class="wrap">
		<?php
		foreach ( (array) $plugins as $key => $info ) {
			$filters = array();
			$summary = $info['description'];
			$verbose_status = '';
			if (isset($info['update_status'])) {
				$filters[] = 'us_'.$info['update_status'];
				switch ($info['update_status']) {
					case 'updated':
						$verbose_status = __('Update available');
						if (isset($info['changelog'])) {
							$summary = $info['changelog'];
						}
						break;
					case 'new':
						$verbose_status = __('New');
						break;
					case 'latest':
					case 'server_outdated':
						$verbose_status = __('Latest version installed');
					default:
						break;
				}
			}
			if (isset($info['activation_status'])) {
				$filters[] = 'as_'.$info['activation_status'];
				if ($info['activation_status'] == 'inactive') {
					$verbose_status = __('Inactive');
				}
			}
			$additional_classes = (sizeof($filters)) ? ' '.implode(' ', $filters) : '';
			$summary = strip_tags($summary, '<p><br><strong><a><ul><li><em><i>');

?><li class="mix product-card<?php echo $additional_classes; ?>">
	<div class="status-action clear">
	  <span class="alignleft status<?php echo $additional_classes; ?>"><?php echo $verbose_status; ?></span>
	  <input type="checkbox" id="cb-select-<?php echo $key; ?>" name="checked_products[]" value="<?php echo $info['slug']; ?>" data-version="<?php echo $info['version']; ?>" data-file="<?php echo $info['download_url']; ?>" data-conflicts="<?php echo $info['conflicts']; ?>" data-activation-status="<?php echo isset($info['activation_status']) ? $info['activation_status'] : ''; ?>" data-update-status="<?php echo isset($info['update_status']) ? $info['update_status'] : ''; ?>"/>
	</div>
	<h2><?php echo str_replace('WPA ', '', $info['name']); ?></h2>
	<div class="summary"><?php echo $summary; ?></div>
</li> <?php
		}
		$gap_needed = 4 - sizeof($plugins)%4;
		for($i = 0; $i < $gap_needed; $i++) {
			echo '<li class="gap"></li> ';
		}
?>
</ul>
	</form>
	</div>
		  <?php
	}

	private function detectUpdateStatus( &$products ) {
		// Compare product versions with what we have
		$wp_plugins = get_plugins();
		$result = array('new' => 0, 'updated' => 0);
		foreach ((array) $products['plugins'] as $key => $info) {
			if (isset($wp_plugins[$info['slug']])) {
				$version_check = version_compare($wp_plugins[$info['slug']]['Version'], $info['version']);
				switch ($version_check) {
					case -1:
						$products['plugins'][$key]['update_status'] = 'updated';
						$result['updated']++;
						break;
					case 0:
						$products['plugins'][$key]['update_status'] = 'latest';
						break;
					case 1:
						$products['plugins'][$key]['update_status'] = 'server_outdated';
						break;
				}

				$is_active = is_plugin_active( $info['slug'] );
				$products['plugins'][$key]['activation_status'] = ( $is_active === true ) ? 'active' : 'inactive';
			} else {
				$products['plugins'][$key]['update_status'] = 'new';
				$products['plugins'][$key]['activation_status'] = 'new';
				$result['new']++;
			}
		}
		return $result;
	}

	public function get_products( $refresh = false ) {
		$products = array();
		if ( $refresh == false ) {
			$products = get_site_option( 'wpavengers_plugins_list', null );
		}
		if ( null == $products || $refresh == true ) {
			$products = $this->makeRequest( 'plugins/list.json' );
			if ( $products !== false ) {
				update_site_option( 'wpavengers_plugins_list', $products );
				wp_update_plugins();
			} else {
				$products = array();
			}
		}
		return $products;
	}

	public function update_plugin_url_in_site_transient( $info ) {
        if ( !isset( $info->checked ) ) {
        	return $info;
        }
        $products = $this->get_products();
        if ( empty( $products['plugins'] ) ) {
        	return $info;
        }
        $plugins = $products['plugins'];

        foreach ( (array) $plugins as $key => $data ) {
            if ( !array_key_exists( $data['slug'], $info->checked ) ) {
            	continue;
            }
            // Add to response if an update is available
            if ( version_compare( $data['version'], $info->checked[$data['slug']], '>' ) ) {
            	$response = new stdClass();
            	$response->slug = $key;
            	$response->new_version = $data['version'];
            	$response->url = $data['info_url'];
            	$response->package = untrailingslashit($this->api_endpoint).$data['download_url'].'?v='.urlencode($response->new_version);
                $info->response[$data['slug']] = $response;
            }
        }
        return $info;
    }

	private function init( $component ) {
		switch ( $component ) {
		case 'updater':
			if ( ! ( $this->updater instanceof WPA_Updater ) ) {
				if ( !class_exists( 'Plugin_Upgrader' ) )
					include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				require_once $this->classes_dir . 'class-wpa-updater.php';
				$this->updater = new WPA_Updater();
			}
			break;

		default:
			// code...
			break;
		}
	}

	public function batch() {
		if ($this->secure_ajax == true) {
			check_ajax_referer( 'wpad-dashboard-ajax-nonce', 'wpa_nonce' );
		}

		$batch = json_decode( stripslashes( $_REQUEST['batch'] ), true );
		$install_upgrade = false;
        foreach ( $batch as $operation ) {
            if ( in_array( 'install-plugin', $operation ) || in_array( 'upgrade-plugin', $operation ) ) {
                $install_upgrade = true;
                break;
            }
        }
        if ( $install_upgrade ) {
        	$this->init( 'updater' );
            $this->updater->check_fsconnect();
        } 

		$this->is_batch = true;
		$this->batch_responses = array();
		$this->batch_operation_index = 0;
		if ( is_array( $batch ) && sizeof( $batch ) > 0 ) {
			foreach ( $batch as $operation ) {
				$action = ( isset( $operation['action'] ) ? $operation['action'] : '' );
				$method = ( isset( $this->ajax_actions[$action] ) ? $this->ajax_actions[$action] : '' );
				if ( in_array( $action, $this->batch_actions ) && !empty( $method ) && is_callable( array( $this, $method ) ) ) {
					$args = ( isset( $operation['args'] ) ? $operation['args'] : array() );
					$this->$method( $args );
				} else {
					$this->batch_responses[$this->batch_operation_index] = array( 'result' => false, 'msg' => __( '%s is not a valid action.', $action ) );
				}
				$this->batch_operation_index++;
			}
		}
		$this->is_batch = false;
		$this->respond( $this->batch_responses );
	}

	private function respond( $response ) {
		if ( $this->is_batch ) {
			$this->batch_responses[$this->batch_operation_index] = $response;
		} else {
			die ( json_encode( $response ) );
		}
	}

	/*************** PLUGIN INSTALL / UPGRADE / ACTIVATE / DEACTIVATE ***************/
	public function install( $args = null ) {
		$this->init( 'updater' );
		if ( $args == null ) $args = $_REQUEST;
        $this->updater->check_fsconnect();
		$this->respond( $this->updater->install( $args['from'] ) );
	}

	public function upgrade( $args = null ) {
		$this->init( 'updater' );
		if ( $args == null ) $args = $_REQUEST;
        $this->updater->check_fsconnect();
		$this->respond( $this->updater->upgrade( $args['plugin'] ) );
	}

	public function activate( $args = null ) {
		$this->init( 'updater' );
		if ( $args == null ) $args = $_REQUEST;
		$this->respond( $this->updater->activate( $args['plugin'] ) );
	}

	public function deactivate( $args = null ) {
		$this->init( 'updater' );
		if ( $args == null ) $args = $_REQUEST;
		$this->respond( $this->updater->deactivate( $args['plugin'] ) );
	}

	/*************** API REQUESTS TO SERVER ***************/
	private function makeRequest( $path, $params = array(), $method = 'GET', $headers = array() ) {

		$url = $this->api_endpoint . $path;
		$qs = http_build_query( $params );

		$headers['X-Site-URL'] = network_site_url();
		$headers['X-Site-Name'] = get_bloginfo('name');
		$headers['X-Site-Admin'] = get_bloginfo('admin_email');

		$options = array(
			'timeout' => 15,
			'user-agent' => 'WPA Dashboard Client/' . $this->version,
			'method' => $method,
			'headers' => $headers
		);

		if ( $method == 'POST' ) {
			$options['body'] = $qs;
		} else {
			if ( strpos( $url, '?' ) !== false ) {
				$url .= '&'.$qs;
			} else {
				$url .= '?'.$qs;
			}
		}

		$response = wp_remote_request( $url, $options );
		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$data = $response['body'];
			if ( $data != 'error' ) {
				$data = json_decode( $data, true );
				if ( is_array( $data ) ) {
					return $data;
				}
			}
		}
		return false;
	}
}
