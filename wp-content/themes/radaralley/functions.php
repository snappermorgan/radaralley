<?php
//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );

//* Setup Theme
include_once( get_stylesheet_directory() . '/lib/theme-defaults.php' );

//* Set Localization (do not remove)
load_child_theme_textdomain( 'news', apply_filters( 'child_theme_textdomain', get_stylesheet_directory() . '/languages', 'news' ) );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', __( 'News Pro Theme', 'news' ) );
define( 'CHILD_THEME_URL', 'http://my.studiopress.com/themes/news/' );
define( 'CHILD_THEME_VERSION', '3.0.2' );

//* Add HTML5 markup structure
add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Enqueue Scripts
add_action( 'wp_enqueue_scripts', 'news_load_scripts' );
function news_load_scripts() {

	wp_enqueue_script( 'news-responsive-menu', get_bloginfo( 'stylesheet_directory' ) . '/js/responsive-menu.js', array( 'jquery' ), '1.0.0' );
	
	wp_enqueue_style( 'dashicons' );

	wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Raleway:400,700|Pathway+Gothic+One', array(), CHILD_THEME_VERSION );
	
}

//* Add new image sizes
add_image_size( 'home-bottom', 150, 150, TRUE );
add_image_size( 'home-middle', 348, 180, TRUE );
add_image_size( 'home-top', 740, 400, TRUE );

//* Add support for custom background
add_theme_support( 'custom-background' );

//* Add support for custom header
add_theme_support( 'custom-header', array(
	'header_image'    => '',
	'header-selector' => '.site-title a',
	'header-text'     => false,
	'height'          => 90,
	'width'           => 260,
) );

//* Add support for additional color style options
add_theme_support( 'genesis-style-selector', array(
	'news-pro-blue'   => __( 'News Pro Blue', 'news' ),
	'news-pro-green'  => __( 'News Pro Green', 'news' ),
	'news-pro-pink'   => __( 'News Pro Pink', 'news' ),
	'news-pro-orange' => __( 'News Pro Orange', 'news' ),
) );

//* Add support for 6-column footer widgets
add_theme_support( 'genesis-footer-widgets', 6 );

//* Reposition the secondary navigation
remove_action( 'genesis_after_header', 'genesis_do_subnav' );
add_action( 'genesis_before_header', 'genesis_do_subnav' );

//* Hook after entry widget after the entry content
add_action( 'genesis_after_entry', 'news_after_entry', 5 );
function news_after_entry() {

	if ( is_singular( 'post' ) )
		genesis_widget_area( 'after-entry', array(
			'before' => '<div class="after-entry" class="widget-area">',
			'after'  => '</div>',
		) );

}

//* Remove comment form allowed tags
add_filter( 'comment_form_defaults', 'news_remove_comment_form_allowed_tags' );
function news_remove_comment_form_allowed_tags( $defaults ) {

	$defaults['comment_notes_after'] = '';
	return $defaults;

}

//* Register widget areas
genesis_register_sidebar( array(
	'id'          => 'home-top',
	'name'        => __( 'Home - Top', 'news' ),
	'description' => __( 'This is the top section of the homepage.', 'news' ),
) );
genesis_register_sidebar( array(
	'id'          => 'home-middle-left',
	'name'        => __( 'Home - Middle Left', 'news' ),
	'description' => __( 'This is the middle left section of the homepage.', 'news' ),
) );
genesis_register_sidebar( array(
	'id'          => 'home-middle-right',
	'name'        => __( 'Home - Middle Right', 'news' ),
	'description' => __( 'This is the middle right section of the homepage.', 'news' ),
) );
genesis_register_sidebar( array(
	'id'          => 'home-bottom',
	'name'        => __( 'Home - Bottom', 'news' ),
	'description' => __( 'This is the bottom section of the homepage.', 'news' ),
) );
genesis_register_sidebar( array(
	'id'          => 'after-entry',
	'name'        => __( 'After Entry', 'news' ),
	'description' => __( 'This is the after entry section.', 'news' ),
) );

add_theme_support( 'genesis-connect-woocommerce' );