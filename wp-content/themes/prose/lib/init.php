<?php
/**
 * This file controls all parts of the Prose Child Theme Initialization.
 *
 * @package Prose
 * @author StudioPress
 */

/**
 * Child theme constants.
 *
 * These constants are used throughout the Prose theme.
 *
 */
define( 'CHILD_THEME_NAME', 'Prose Theme' );
define( 'CHILD_THEME_URL', 'http://www.studiopress.com/themes/prose' );
define( 'CHILD_THEME_VERSION', '1.5.2' );
define( 'CHILD_DB_VERSION', '1502' );
define( 'PROSE_SETTINGS_FIELD', 'prose_settings' );
define( 'PROSE_DOMAIN', 'prose' );

/**
 * Custom background.
 *
 * Enable the WordPress custom background function.
 *
 */
add_theme_support( 'custom-background' );

/**
 * Theme support.
 *
 * Enable support for various Prose and Genesis features.
 *
 */
add_theme_support( 'genesis-custom-header', array( 'width' => genesis_get_option( 'header_image_width', PROSE_SETTINGS_FIELD ), 'height' => genesis_get_option( 'header_image_height', PROSE_SETTINGS_FIELD ) ) );
add_theme_support( 'genesis-footer-widgets', 3 );
add_theme_support( 'prose-design-settings' );
add_theme_support( 'prose-auto-updates' );

add_action( 'genesis_meta', 'prose_add_viewport_meta_tag' );
/**
 * Viewport meta tag.
 *
 * Output the viewport meta tag to enable responsive features.
 *
 * @since 1.5.0
 */
function prose_add_viewport_meta_tag() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
}

add_action( 'genesis_after_post_content', 'prose_after_post' );
/**
 * After post widget area.
 *
 * Output widget area after the post content.
 *
 * @uses genesis_widget_area()
 *
 * @since 1.5.0
 */
function prose_after_post() {

	if ( ! is_singular( 'post' ) )
		return;

	genesis_widget_area( 'after-post', array(
		'before' => '<div class="after-post widget-area">',
	) );

}

/** Reposition the primary navigation */
remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_before_header', 'genesis_do_nav' );

/** Reposition the footer */
remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
remove_action( 'genesis_footer', 'genesis_do_footer' );
remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );
add_action( 'genesis_after', 'genesis_footer_markup_open', 5 );
add_action( 'genesis_after', 'genesis_do_footer');
add_action ('genesis_after', 'genesis_footer_markup_close', 15 );

/** Register widget areas */
genesis_register_sidebar( array(
	'id'          => 'after-post',
	'name'        => __( 'After Post', 'prose' ),
	'description' => __( 'This is the after post section.', 'prose' ),
) );

/** Functions */
require_once( CHILD_DIR . '/lib/functions/I18n.php' );
require_once( CHILD_DIR . '/lib/functions/design-settings.php' );
require_once( CHILD_DIR . '/lib/functions/update.php' );

/** Structure */
require_once( CHILD_DIR . '/lib/structure/custom.php' );
require_once( CHILD_DIR . '/lib/structure/stylesheets.php' );
require_once( CHILD_DIR . '/lib/structure/import-export.php' );

/** Settings pages */
if ( is_admin() ) {

	require_once( CHILD_DIR . '/lib/admin/design-settings.php' );
	require_once( CHILD_DIR . '/lib/admin/custom-code.php' );

}