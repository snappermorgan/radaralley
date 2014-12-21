<?php 
/**
 * WP Avengers Updater class
 *
 * @class 		WPA_Updater
 * @package		WPA Dashboard
 * @category	Class
 * @since		1.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPA_Updater_Skin extends WP_Upgrader_Skin {

	var $response;
	var $error;
	var $options;

	function __construct($args = array()) {
		parent::__construct($args);
		$this->response = array();
		$this->error = '';
	}

	function header() {
		if ( $this->done_header )
			return;
		$this->done_header = true;
	}

	function footer() {
		//echo json_encode($this->response);
	}

	function get_error() {
		return is_wp_error($this->error) ? $this->error : false;
	}

	function error($error) {
		if ( is_string($error) && isset( $this->upgrader->strings[$error] ) )
			$this->error = new WP_Error($error, $this->upgrader->strings[$error]);

		if ( is_wp_error($error) ) {
			$this->error = $error;
		}
	}

	function feedback($string) {
		if ( isset( $this->upgrader->strings[$string] ) )
			$string = $this->upgrader->strings[$string];

		if ( strpos($string, '%') !== false ) {
			$args = func_get_args();
			$args = array_splice($args, 1);
			if ( !empty($args) )
				$string = vsprintf($string, $args);
		}
		if ( empty($string) )
			return;
		$this->response[] = $string;
	}
	function before() {}
	function after() {}
}


class WPA_Updater {

	function __construct() {
   	}

   	public function stringify_error( $errors ) {
   		$result = array();
   		if ( is_wp_error($errors) && $errors->get_error_code() ) {
			foreach ( $errors->get_error_messages() as $message ) {
				if ( $errors->get_error_data() )
					$result[] = $message . ' ' . $errors->get_error_data();
				else
					$result[] = $message;
			}
		} else if ( false == $errors ) {
			$result[] = __("Operation failed.");
		}
		return implode( "\n", $result );
   	}

   	public function check_fsconnect( ) {
   		set_current_screen( 'wpa_dashboard' );
   		ob_clean();
   		ob_start();

   		$wp_upgrader = new WP_Upgrader( null );
   		$wp_upgrader->init();
        $connect_result = $wp_upgrader->fs_connect( array( WP_CONTENT_DIR, WP_PLUGIN_DIR ) );
        if (false == $connect_result || is_wp_error($connect_result)) {
        	ob_flush();
        	die();
        } 
        ob_end_clean();
   	}

   	public function install($from) {
   		$response = array();
   		$response['result'] = false;
   		
   		if ( ! current_user_can('install_plugins') )
			$response['msg'] = __( 'You do not have sufficient permissions to install plugins on this site.' );

		if (!empty($from)) {
			$skin = new WPA_Updater_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$result = $upgrader->install($from);
			if ($skin->get_error()) {
				$result = $skin->get_error();
			}
			if ($result == null) {
				$response['msg'] = __( 'Plugin is already installed.' );
			}
			else if ( ! $result || is_wp_error($result) ) {
				$response['msg'] = $this->stringify_error( $result );
			} else {
				// Everything went well
				$response['result'] = true;
				$response['msg'] = __( 'Plugin installed successfully.' );
			}
		} else {
			$response['msg'] = __( 'Please select a plugin to install.' ) ;
		}
		return $response;
   	}

   	public function upgrade($plugin) {
   		$response = array();
   		$response['result'] = false;
   		
   		if ( ! current_user_can('update_plugins') )
			$response['msg'] = __( 'You do not have sufficient permissions to update plugins for this site.' );

		if (!empty($plugin)) {
			$skin = new WPA_Updater_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$result = $upgrader->upgrade($plugin);
			if ($skin->get_error()) {
				$result = $skin->get_error();
			}
			if ( is_wp_error($result) ) {
				$response['msg'] = $this->stringify_error( $result );
			} else if ($result === false) {
				$response['result'] = false;
				$response['msg'] = __( 'The plugin could not be upgraded.' );
			} else {
				$response['result'] = true;
				$response['msg'] = __( 'Plugin updated successfully.' );
			}
		} else {
			$response['msg'] = __( 'Please select a plugin to update.' ) ;
		}
		return $response;
   	}

   	public function activate($plugin) {
   		$response = array();
   		$response['result'] = false;

   		if ( ! current_user_can('activate_plugins') )
			$response['msg'] = __( 'You do not have sufficient permissions to manage plugins for this site.' );
		else {
	   		$result = activate_plugin($plugin, '', ( is_multisite() && is_super_admin() ));
			if ( is_wp_error($result) ) {
				if ( 'unexpected_output' == $result->get_error_code() ) {
					$response['result'] = true;
					$response['msg'] = __( 'Plugin activated successfully. But generated unexpected output. '.$result->get_error_data() );
				} else {
					$response['msg'] = $this->stringify_error( $result );
				}
			} else {
				if ( ! ( is_multisite() && is_super_admin() ) ) {
					$recent = (array) get_option( 'recently_activated' );
					unset( $recent[ $plugin ] );
					update_option( 'recently_activated', $recent );
				}
				$response['result'] = true;
				$response['msg'] = __( 'Plugin activated successfully.' );
			}
		}
		return $response;
   	}

   	public function deactivate($plugin) {
   		$response = array();
   		$response['result'] = false;

   		if ( ! current_user_can('activate_plugins') ) {
			$response['msg'] = __( 'You do not have sufficient permissions to deactivate plugins for this site.' );
   		}
		else {
	   		deactivate_plugins($plugin, false, ( is_multisite() && is_super_admin() ));
			if ( ! ( is_multisite() && is_super_admin() ) ) {
				if (is_array($plugin)) {
					$deactivated = array();
					foreach ( $plugin as $slug ) {
						$deactivated[ $slug ] = time();
					}
				} else {
					$deactivated = array( $plugin => time() );
				}
				update_option( 'recently_activated', $deactivated + (array) get_option( 'recently_activated' ) );
			}
			$response['result'] = true;
			$response['msg'] = __( 'Plugin deactivated successfully.' );
		}
		return $response;
   	}
}