<?php
/**
 * Debug Bar Post Types, a WordPress plugin
 *
 * @package     WordPress\Plugins\Debug Bar Post Types
 * @author      Juliette Reinders Folmer <wpplugins_nospam@adviesenzo.nl>
 * @link        https://github.com/jrfnl/Debug-Bar-Post-Types
 * @version     1.1.1
 *
 * @copyright   2013-2014 Juliette Reinders Folmer
 * @license     http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Debug Bar Post Types
 * Plugin URI:  http://wordpress.org/extend/plugins/debug-bar-post-types/
 * Description: Debug Bar Post Types adds a new panel to the Debug Bar that displays detailed information about the registered post types for your site. Requires "Debug Bar" plugin.
 * Version:     1.1.1
 * Author:      Juliette Reinders Folmer
 * Author URI:  http://www.adviesenzo.nl/
 * Depends:     Debug Bar
 * Text Domain: debug-bar-post-types
 * Domain Path: /languages/
 * Copyright:   2013-2014 Juliette Reinders Folmer
 */

// Avoid direct calls to this file
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Show admin notice & de-activate itself if debug-bar plugin not active
 */
add_action( 'admin_init', 'dbpt_has_parent_plugin' );

if ( ! function_exists( 'dbpt_has_parent_plugin' ) ) {
	/**
	 * Check for parent plugin
	 */
	function dbpt_has_parent_plugin() {
		if ( is_admin() && ( ! class_exists( 'Debug_Bar' ) && current_user_can( 'activate_plugins' ) ) ) {
			add_action( 'admin_notices', create_function( null, 'echo \'<div class="error"><p>\' . sprintf( __( \'Activation failed: Debug Bar must be activated to use the <strong>Debug Bar Post Types</strong> Plugin. %sVisit your plugins page to activate.\', \'debug-bar-post-types\' ), \'<a href="\' . admin_url( \'plugins.php#debug-bar\' ) . \'">\' ) . \'</a></p></div>\';' ) );

			deactivate_plugins( plugin_basename( __FILE__ ) );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
}


if ( ! function_exists( 'debug_bar_post_types_panel' ) ) {
	/**
	 * Add the Debug Bar Post Types panel to the Debug Bar
	 *
	 * @param   array   $panels     Existing debug bar panels
	 * @return  array
	 */
	function debug_bar_post_types_panel( $panels ) {
		if ( ! class_exists( 'Debug_Bar_Post_Types' ) ) {
			require_once 'class-debug-bar-post-types.php';
		}
		$panels[] = new Debug_Bar_Post_Types();
		return $panels;
	}
	add_filter( 'debug_bar_panels', 'debug_bar_post_types_panel' );
}