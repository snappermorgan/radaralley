<?php 
/**
 * WP Avengers Utils class
 *
 * @class 		WPA_Utils
 * @package		WPA Dashboard
 * @category	Class
 * @since		2.1
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPA_Utils {

	private static $active_plugins;

	function __construct() {
		
	}

	public static function is_plugin_active( $plugin ) {
		if ( ! self::$active_plugins ) {
			self::$active_plugins = (array) get_option( 'active_plugins', array() );
			if ( is_multisite() ) {
				self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
			}
		}
		return in_array( $plugin, self::$active_plugins ) || array_key_exists( $plugin, self::$active_plugins );
	}
}