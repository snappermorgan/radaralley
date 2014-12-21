<?php
//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );

//* Set Localization (do not remove)
load_child_theme_textdomain( 'magazine', apply_filters( 'child_theme_textdomain', get_stylesheet_directory() . '/languages', 'magazine' ) );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', __( 'Magazine Pro Theme', 'magazine' ) );
define( 'CHILD_THEME_URL', 'http://my.studiopress.com/themes/magazine/' );
define( 'CHILD_THEME_VERSION', '3.0.1' );
add_theme_support( 'genesis-connect-woocommerce' );
//* Enqueue Google Fonts and JS script
add_action( 'wp_enqueue_scripts', 'magazine_enqueue_scripts' );
function magazine_enqueue_scripts() {

	wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Roboto:300,400|Raleway:400,500,900', array(), CHILD_THEME_VERSION );

	wp_enqueue_script( 'magazine-entry-date', get_bloginfo( 'stylesheet_directory' ) . '/js/entry-date.js', array( 'jquery' ), '1.0.0' );
	wp_enqueue_script( 'magazine-responsive-menu', get_bloginfo( 'stylesheet_directory' ) . '/js/responsive-menu.js', array( 'jquery' ), '1.0.0' );

}

//* Add HTML5 markup structure
add_theme_support( 'html5' );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Add new image sizes
add_image_size( 'home-middle', 360, 200, true );
add_image_size( 'home-top', 750, 420, true );
add_image_size( 'sidebar-thumbnail', 100, 100, true );

//* Add support for additional color styles
add_theme_support( 'genesis-style-selector', array(
	'magazine-pro-blue'   => __( 'Magazine Pro Blue', 'magazine' ),
	'magazine-pro-green'  => __( 'Magazine Pro Green', 'magazine' ),
	'magazine-pro-orange' => __( 'Magazine Pro Orange', 'magazine' ),
) );

//* Add support for custom header
add_theme_support( 'custom-header', array(
	'default-text-color'     => '000000',
	'header-selector'        => '.site-title a',
	'header-text'            => false,
	'height'                 => 90,
	'width'                  => 380,
) );

//* Reposition the primary navigation menu
remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_before_header', 'genesis_do_nav' );

//* Add primary-nav class if primary navigation is used
add_filter( 'body_class', 'backcountry_no_nav_class' );
function backcountry_no_nav_class( $classes ) {

	$menu_locations = get_theme_mod( 'nav_menu_locations' );

	if ( ! empty( $menu_locations['primary'] ) ) {
		$classes[] = 'primary-nav';
	}
	return $classes;
}

//* Customize search form input box text
add_filter( 'genesis_search_text', 'magazine_search_text' );
function magazine_search_text( $text ) {

	return esc_attr( __( 'Search the site ...', 'magazine' ) );
	
}

//* Modify the size of the Gravatar in the author box
add_filter( 'genesis_author_box_gravatar_size', 'magazine_author_box_gravatar' );
function magazine_author_box_gravatar( $size ) {

	return 140;

}

//* Modify the size of the Gravatar in the entry comments
add_filter( 'genesis_comment_list_args', 'magazine_comments_gravatar' );
function magazine_comments_gravatar( $args ) {

	$args['avatar_size'] = 100;
	return $args;

}

//* Remove entry meta in entry footer
add_action( 'genesis_before_entry', 'magazine_remove_entry_meta' );
function magazine_remove_entry_meta() {
	
	//* Remove if not single post
	if ( ! is_single() ) {
		remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_open', 5 );
		remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
		remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_close', 15 );
	}

}

//* Hooks after-entry widget area to single posts
add_action( 'genesis_entry_footer', 'magazine_after_entry_widget'  ); 
function magazine_after_entry_widget() {

    if ( ! is_singular( 'post' ) )
    	return;

    genesis_widget_area( 'after-entry', array(
		'before' => '<div class="after-entry widget-area"><div class="wrap">',
		'after'  => '</div></div>',
    ) );

}

//* Remove comment form allowed tags
add_filter( 'comment_form_defaults', 'magazine_remove_comment_form_allowed_tags' );
function magazine_remove_comment_form_allowed_tags( $defaults ) {
	
	$defaults['comment_notes_after'] = '';
	return $defaults;

}

//* Add support for 3-column footer widgets
add_theme_support( 'genesis-footer-widgets', 3 );

//* Register widget areas
genesis_register_sidebar( array(
	'id'          => 'home-top',
	'name'        => __( 'Home - Top', 'magazine' ),
	'description' => __( 'This is the top section of the homepage.', 'magazine' ),
) );
genesis_register_sidebar( array(
	'id'          => 'home-middle',
	'name'        => __( 'Home - Middle', 'magazine' ),
	'description' => __( 'This is the middle section of the homepage.', 'magazine' ),
) );
genesis_register_sidebar( array(
	'id'          => 'home-bottom',
	'name'        => __( 'Home - Bottom', 'magazine' ),
	'description' => __( 'This is the bottom section of the homepage.', 'magazine' ),
) );
genesis_register_sidebar( array(
	'id'          => 'after-entry',
	'name'        => __( 'After Entry', 'magazine' ),
	'description' => __( 'This is the after entry section.', 'magazine' ),
) );
