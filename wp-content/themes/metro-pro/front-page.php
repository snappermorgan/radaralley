<?php
/**
 * This file adds the Home Page to the Metro Pro Child Theme.
 *
 * @author StudioPress
 * @package Generate
 * @subpackage Customizations
 */

add_action( 'genesis_meta', 'metro_home_genesis_meta' );
/**
 * Add widget support for homepage. If no widgets active, display the default loop.
 *
 */
function metro_home_genesis_meta() {

	if ( is_active_sidebar( 'home-top' ) || is_active_sidebar( 'home-middle-left' ) || is_active_sidebar( 'home-middle-right' ) || is_active_sidebar( 'home-bottom' ) ) {

		// Force content-sidebar layout setting
		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_content_sidebar' );

		// Add metro-pro-home body class
		add_filter( 'body_class', 'metro_body_class' );
		function metro_body_class( $classes ) {
   			$classes[] = 'metro-pro-home';
  			return $classes;
		}

		// Remove the default Genesis loop
		remove_action( 'genesis_loop', 'genesis_do_loop' );

		// Add homepage widgets
		add_action( 'genesis_loop', 'metro_homepage_widgets' );

	}
}

function metro_homepage_widgets() {

	genesis_widget_area( 'home-top', array(
		'before' => '<div class="home-top widget-area">',
		'after'  => '</div>',
	) );
	
	if ( is_active_sidebar( 'home-middle-left' ) || is_active_sidebar( 'home-middle-right' ) ) {

		echo '<div class="home-middle">';

		genesis_widget_area( 'home-middle-left', array(
			'before' => '<div class="home-middle-left widget-area">',
			'after'  => '</div>',
		) );

		genesis_widget_area( 'home-middle-right', array(
			'before' => '<div class="home-middle-right widget-area">',
			'after'  => '</div>',
		) );

		echo '</div>';
	
	}

	genesis_widget_area( 'home-bottom', array(
		'before' => '<div class="home-bottom widget-area">',
		'after'  => '</div>',
	) );

}

genesis();
