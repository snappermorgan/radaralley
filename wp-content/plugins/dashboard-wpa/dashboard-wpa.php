<?php
/*
Plugin Name: WP Avengers Dashboard
Plugin URI: http://wpavengers.com/download/
Description: Discover, install and update all our WooCommerce plugins from a single place
Version: 2.3.2
Author: WP Avengers
Author URI: http://wpavengers.com/

	Copyright: 2013 WP Avengers.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WPA_Utils' ) ) {
	require_once 'classes/class-wpa-utils.php' ;
}
if ( ! function_exists( 'is_woocommerce_active' ) ) {
	function is_woocommerce_active() {
		return WPA_Utils::is_plugin_active( 'woocommerce/woocommerce.php' );
	}
}

// Load dashboard if in admin
if ( is_admin() ) { 
	require_once 'classes/class-wpa-dashboard.php' ;
	$wpa_dashboard = new WPA_Dashboard( __FILE__ );
}