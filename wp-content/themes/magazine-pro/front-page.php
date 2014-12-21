<?php
/**
 * This file adds the Home Page to the Magazine Pro Child Theme.
 *
 * @author StudioPress
 * @package Magazine Pro
 * @subpackage Customizations
 */

add_action( 'genesis_meta', 'magazine_home_genesis_meta' );
/**
 * Add widget support for homepage. If no widgets active, display the default loop.
 *
 */
function magazine_home_genesis_meta() {

	if ( is_active_sidebar( 'home-top' ) || is_active_sidebar( 'home-middle' ) || is_active_sidebar( 'home-bottom' ) ) {

		// Force content-sidebar layout setting
		add_filter( 'genesis_site_layout', '__genesis_return_content_sidebar' );

		// Add magazine-home body class
		add_filter( 'body_class', 'magazine_body_class' );

		// Remove the default Genesis loop
		remove_action( 'genesis_loop', 'genesis_do_loop' );

		// Add homepage widgets
		add_action( 'genesis_loop', 'magazine_homepage_widgets' );

	}
}

function magazine_body_class( $classes ) {

	$classes[] = 'magazine-home';
	return $classes;
	
}

function magazine_homepage_widgets() {

	genesis_widget_area( 'home-top', array(
		'before' => '<div class="home-top widget-area">',
		'after'  => '</div>',
	) );

	genesis_widget_area( 'home-middle', array(
		'before' => '<div class="home-middle widget-area">',
		'after'  => '</div>',
	) );

	genesis_widget_area( 'home-bottom', array(
		'before' => '<div class="home-bottom widget-area">',
		'after'  => '</div>',
	) );

}

genesis();
