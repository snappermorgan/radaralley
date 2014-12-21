<?php
//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );
add_theme_support( 'genesis-connect-woocommerce' );
//* Set Localization (do not remove)
load_child_theme_textdomain( 'metro', apply_filters( 'child_theme_textdomain', get_stylesheet_directory() . '/languages', 'metro' ) );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', __( 'Metro Pro Theme', 'metro' ) );
define( 'CHILD_THEME_URL', 'http://my.studiopress.com/themes/metro/' );
define( 'CHILD_THEME_VERSION', '2.0.0' );

//* Add HTML5 markup structure
add_theme_support( 'html5' );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Enqueue Google fonts
add_action( 'wp_enqueue_scripts', 'metro_google_fonts' );
function metro_google_fonts() {
	wp_enqueue_style( 'google-font', '//fonts.googleapis.com/css?family=Oswald:400', array(), CHILD_THEME_VERSION );
}

//* Enqueue Backstretch script and prepare images for loading
add_action( 'wp_enqueue_scripts', 'metro_enqueue_scripts' );
function metro_enqueue_scripts() {

	//* Load scripts only if custom background is being used
	if ( ! get_background_image() )
		return;

	wp_enqueue_script( 'metro-pro-backstretch', get_bloginfo( 'stylesheet_directory' ) . '/js/backstretch.js', array( 'jquery' ), '1.0.0' );
	wp_enqueue_script( 'metro-pro-backstretch-set', get_bloginfo('stylesheet_directory').'/js/backstretch-set.js' , array( 'jquery', 'metro-pro-backstretch' ), '1.0.0' );

	wp_localize_script( 'metro-pro-backstretch-set', 'BackStretchImg', array( 'src' => get_background_image() ) );

}

//* Add custom background callback for background color
function metro_background_callback() {

	if ( ! get_background_color() )
		return;

	printf( '<style>body { background-color: #%s; }</style>' . "\n", get_background_color() );

}

//* Add new image sizes
add_image_size( 'home-bottom', 150, 150, TRUE );
add_image_size( 'home-middle', 332, 190, TRUE );
add_image_size( 'home-top', 700, 400, TRUE );

//* Add support for custom background
add_theme_support( 'custom-background', array( 'wp-head-callback' => 'metro_background_callback' ) );

//* Add support for custom header
add_theme_support( 'custom-header', array(
	'width'           => 270,
	'height'          => 80,
	'header-selector' => '.site-title a',
	'header-text'     => false
) );

//* Add support for additional color style options
add_theme_support( 'genesis-style-selector', array(
	'metro-pro-blue'  => __( 'Blue', 'metro' ),
	'metro-pro-green' => __( 'Green', 'metro' ),
	'metro-pro-pink'  => __( 'Pink', 'metro' ),
	'metro-pro-red'   => __( 'Red', 'metro' ),
) );

//* Add support for 3-column footer widgets
add_theme_support( 'genesis-footer-widgets', 3 );

//* Remove the site description
remove_action( 'genesis_site_description', 'genesis_seo_site_description' );

//* Reposition the secondary navigation
remove_action( 'genesis_after_header', 'genesis_do_subnav' );
add_action( 'genesis_before', 'genesis_do_subnav' );

//* Hooks after-entry widget area to single posts
add_action( 'genesis_entry_footer', 'metro_after_post'  ); 
function metro_after_post() {

    if ( ! is_singular( 'post' ) )
    	return;

    genesis_widget_area( 'after-entry', array(
		'before' => '<div class="after-entry widget-area"><div class="wrap">',
		'after'  => '</div></div>',
    ) );

}

//* Reposition the footer widgets
remove_action( 'genesis_before_footer', 'genesis_footer_widget_areas' );
add_action( 'genesis_after', 'genesis_footer_widget_areas' );

//* Reposition the footer
remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
remove_action( 'genesis_footer', 'genesis_do_footer' );
remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );
add_action( 'genesis_after', 'genesis_footer_markup_open', 11 );
add_action( 'genesis_after', 'genesis_do_footer', 12 );
add_action( 'genesis_after', 'genesis_footer_markup_close', 13 );

//* Register widget areas
genesis_register_sidebar( array(
	'id'          => 'home-top',
	'name'        => __( 'Home - Top', 'metro' ),
	'description' => __( 'This is the top section of the homepage.', 'metro' ),
) );
genesis_register_sidebar( array(
	'id'          => 'home-middle-left',
	'name'        => __( 'Home - Middle Left', 'metro' ),
	'description' => __( 'This is the middle left section of the homepage.', 'metro' ),
) );
genesis_register_sidebar( array(
	'id'          => 'home-middle-right',
	'name'        => __( 'Home - Middle Right', 'metro' ),
	'description' => __( 'This is the middle right section of the homepage.', 'metro' ),
) );
genesis_register_sidebar( array(
	'id'          => 'home-bottom',
	'name'        => __( 'Home - Bottom', 'metro' ),
	'description' => __( 'This is the bottom section of the homepage.', 'metro' ),
) );
genesis_register_sidebar( array(
	'id'          => 'after-entry',
	'name'        => __( 'After Entry', 'metro' ),
	'description' => __( 'This is the after entry section.', 'metro' ),
) );