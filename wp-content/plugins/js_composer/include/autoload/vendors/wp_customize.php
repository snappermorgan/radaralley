<?php

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 *
 * Used to initialize plugin wp-customizer vendor
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); // Require plugin.php to use is_plugin_active() below
if ( is_plugin_active( 'wp-customizer/wp-customizer.php' ) ) {
// Remove scripts from the Visual Composer while in the Customizer = Temp Fix
	if ( ! function_exists( 'vc_wpex_remove_vc_scripts' ) ) {
		function vc_wpex_remove_vc_scripts() {
			if ( is_customize_preview() ) {
				wp_deregister_script( 'wpb_composer_front_js' );
				wp_dequeue_script( 'wpb_composer_front_js' );
			}
		}
	}
	add_action( 'wp_enqueue_scripts', 'vc_wpex_remove_vc_scripts' );
}