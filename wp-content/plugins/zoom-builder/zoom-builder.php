<?php
/*
Plugin Name: ZOOM Builder 
Plugin URI: http://www.wpzoom.com/builder/
Description: A simple drag & drop interface inside the WordPress admin for building unique layouts for pages with widgets.
Version: 1.1.3
Author: WPZOOM
Author URI: http://www.wpzoom.com
Text Domain: zoom-builder
License: GPLv3 or later

	Copyright (C) 2014 WPZOOM <hello@wpzoom.com> - http://www.wpzoom.com

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

# Don't ever load this file directly
defined('ABSPATH') or die;

# On plugin activation
register_activation_hook( __FILE__, array( 'ZOOM_Builder', 'activate' ) );

# Hook into WordPress
add_action( 'after_setup_theme', array( 'ZOOM_Builder', 'setup' ) );

# Do language stuff
add_action( 'plugins_loaded', array( 'ZOOM_Builder', 'i18n' ) );

/**
 * ZOOM_Builder Class
 *
 * Handles initial hooking into WordPress and other basic things.
 *
 * @package ZOOM_Builder
 */

class ZOOM_Builder {

	public static $version, $classes_path, $assets_url, $modules_url, $modules_path, $predefined_layouts_paths, $max_columns, $builder_widgets, $layout_position, $dark_theme, $modules, $post_types, $exlcude_widgets, $saved_layouts, $predefined_layouts, $exclude_layouts, $admin_pointers, $theme_group_locations, $_builder_sidebars_temp;

	/**
	 * Setup important variables, include all modules, and do initial hooking into WordPress
	 */
	public static function setup() {

		self::$version = self::get_plugin_version(); # The plugin version
		self::$classes_path = plugin_dir_path( __FILE__ ) . 'classes'; # The absolute file system path to the plugin /classes/ directory
		self::$assets_url = plugins_url( '/assets', __FILE__ ); # The absolute URL to the plugin /assets/ directory
		self::$modules_url = plugins_url( '/modules', __FILE__ ); # The absolute URL to the plugin /modules/ directory
		self::$modules_path = plugin_dir_path( __FILE__ ) . 'modules'; # The absolute file system path to the plugin /modules/ directory
		self::$predefined_layouts_paths = array( plugin_dir_path( __FILE__ ) . 'predefined-layouts', get_template_directory() . '/functions/theme/builder-layouts' ); # An array of absolute file system paths to directories where predefined layout JSON files can be found
		self::$max_columns = 5; # Amount of columns allowed in the builder [ABSOLUTE MAX IS 10]

		self::include_classes();

		self::$modules = ZOOM_Builder_Utils::sanitize_modules_array( get_option( 'wpzlb-settings-general-modules' ) ); # The option from the database which specifies which modules should be enabled for use with the builder
		self::$post_types = ZOOM_Builder_Utils::sanitize_post_types_array( get_option( 'wpzlb-settings-general-posttypes' ) ); # The option from the database which specifies which post types have the layout builder enabled
		self::$theme_group_locations = array(); # The theme group locations global used to store registered locations
		self::$_builder_sidebars_temp = array(); # Temporarily stores the builder sidebars when the theme is switched so we can restore them after

		self::include_modules();

		add_action( 'init', array( __CLASS__, 'init' ) );
		add_action( 'widgets_init', array( __CLASS__, 'widgets_init' ) );

	}

	/**
	 * Called on plugin activation so we can show the welcome page if needed
	 */
	public static function activate() {

		add_option( 'wpzlb-activate', 'true' );

	}

	/**
	 * Setup stuff for internationalization of the plugin
	 */
	public static function i18n() {

		load_plugin_textdomain( 'zoom-builder', false, plugin_dir_path( __FILE__ ) . 'languages/' );

	}

	/**
	 * Gets the version of this plugin
	 */
	public static function get_plugin_version() {

		if ( !function_exists( 'get_plugins' ) ) require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		foreach ( (array)get_plugins() as $key => $val ) {

			if ( preg_match( '/zoom-builder\.php$/i', $key ) && isset( $val[ 'Version' ] ) )
				return $val[ 'Version' ];

		}

		return '0.0.0';

	}

	/**
	 * Add all the various hooks for all the different plugin functions
	 */
	public static function init() {

		add_action( 'after_switch_theme', array( __CLASS__, 'after_switch_theme_before' ), 1 );
		add_action( 'after_switch_theme', array( __CLASS__, 'after_switch_theme_after' ), 999 );

		self::$builder_widgets = ZOOM_Builder_Utils::get_builder_widgets(); # These are widgets used ONLY in the layout builder
		self::$layout_position = ZOOM_Builder_Utils::sanitize_layout_position( (string)get_option( 'wpzlb-settings-frontend-layoutposition', 'below' ) ); # The option from the database which specifies the position, relative to the post content, of the layout on the frontend
		self::$dark_theme = (string)get_option( 'wpzlb-settings-frontend-darktheme', 'false' ) == 'true'; # The option from the database which specifies whether to use a dark theme for the builder on the frontend

		ZOOM_Builder_Display::init();

		add_filter( 'widget_text', 'do_shortcode' );

		if ( is_admin() ) {

			ZOOM_Builder_Updater::init( __FILE__ );

			self::$exlcude_widgets = ZOOM_Builder_Utils::sanitize_widget_names_array( (array)get_option( 'wpzlb-settings-widgets-exclude', array() ) ); # The option from the database which specifies any widgets that should be excluded from being used in the layout builder
			self::$saved_layouts = ZOOM_Builder_Utils::sanitize_saved_layouts_array( (array)get_option( 'wpzlb-saved-layouts', array() ) ); # The option from the database that contains saved layouts
			self::$predefined_layouts = ZOOM_Builder_Utils::get_predefined_layouts(); # All of the predefined layouts loaded from the currently active theme directory, if it has any
			self::$exclude_layouts = ZOOM_Builder_Utils::sanitize_saved_layouts_ids_array( (array)get_option( 'wpzlb-savedlayouts-exclude', array() ) ); # The option from the database which specifies any saved layouts that should be excluded from being used in the layout builder
			self::$admin_pointers = array(); # The admin pointers/hints for the plugin

			ZOOM_Builder_Main::init();
			ZOOM_Builder_Settings::init();

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts_global' ) );

			add_action( 'admin_init', array( __CLASS__, 'maybe_activate_redirect' ) );
			add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts_welcome' ) );
			add_filter( 'parent_file', array( __CLASS__, 'filter_welcome_page_parent_menu' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'plugin_action_links' ) );

			add_action( 'widgets_admin_page', array( __CLASS__, 'widgets_admin_page' ) );
			add_action( 'customize_register', array( __CLASS__, 'widgets_admin_page' ), 0 );
			add_action( 'wp_ajax_widgets-order', array( __CLASS__, 'ajax_widgets_order' ), 0 );

			self::add_admin_pointers();
			$filtered_pointers = self::filtered_admin_pointers();
			if ( !empty( $filtered_pointers ) ) {
				add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts_pointers' ) );
			}

		}

	}

	/**
	 * Include all class files
	 */
	public static function include_classes() {

		foreach ( array( 'utils', 'main', 'settings', 'display', 'updater', 'video-api' ) as $class ) {
			require_once( self::$classes_path . "/$class.php" );
		}

	}

	/**
	 * Include all module files
	 */
	public static function include_modules() {

		foreach ( glob( self::$modules_path . '/*', GLOB_ONLYDIR ) as $dir ) {

			$basename = basename( $dir );
			$file = sprintf( '%s/%s.php', $dir, $basename );

			if ( is_readable( $file ) && in_array( $basename, self::$modules ) ) include_once( $file );

		}

	}

	/**
	 * Register needed menus
	 */
	public static function admin_menu() {

		add_submenu_page( 'wpzlb-settings', __( 'Welcome to ZOOM Builder', 'zoom-builder' ), __( 'Welcome to ZOOM Builder', 'zoom-builder' ), 'manage_options', 'wpzlb-welcome', array( __CLASS__, 'display_welcome_page' ) );

	}

	/**
	 * Register sidebars for every page, including drafts and auto-drafts so we can add widgets to even new pages
	 */
	public static function widgets_init() {

		$pages = get_posts( array( 'post_type' => self::$post_types, 'post_status' => 'any', 'nopaging' => true, 'posts_per_page' => -1 ) );

		if ( !is_array( $pages ) || empty( $pages ) ) return;

		foreach ( $pages as $page ) register_sidebar( "id=_wpzlb-page-{$page->ID}-widgets" );

	}

	/**
	 * We hide the page sidebars, and builder-specific widgets, so they don't appear on the core widget management screen, which would probably be weird
	 */
	public static function widgets_admin_page() {

		global $wp_registered_sidebars, $wp_registered_widgets;

		foreach ( $wp_registered_sidebars as $k1 => $v1 ) {
			if ( stripos( $k1, '_wpzlb-page-' ) !== false )
				unset( $wp_registered_sidebars[$k1] );
		}

		foreach ( $wp_registered_widgets as $k2 => $v2 ) {
			if ( isset( $v2['wpzlb_widget'] ) )
				unset( $wp_registered_widgets[$k2] );
		}

	}

	/**
	 * Scripts/styles that should be enqueued on every admin page
	 */
	public static function admin_enqueue_scripts_global() {

		wp_register_style( 'wpzlb-style-global', ZOOM_Builder::$assets_url . '/css/global.css', array(), self::$version );
		wp_enqueue_style( 'wpzlb-style-global' );

	}

	/**
	 * Filters out dismissed WP Pointers for the current user so they do not show again
	 */
	public static function filtered_admin_pointers() {

		$all_pointers = self::$admin_pointers;

		if ( empty( $all_pointers ) ) return array();

		$dismissed = explode( ',', (string)get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

		foreach ( $all_pointers as $screen => $pointers ) {
			foreach ( $pointers as $pointer => $args ) {
				if ( in_array( $pointer, $dismissed ) ) unset( $all_pointers[ $screen ][ $pointer ] );
			}
		}

		return array_filter( $all_pointers );

	}

	/**
	 * An abstraction function for adding WP Pointers
	 */
	public static function add_admin_pointer( $id, $target, $content, $position = 'top', $classes = '', $screen = 'global' ) {

		if ( empty( $id ) || empty( $target ) || empty( $content ) ) return false;

		if ( empty( $screen ) ) $screen = 'global';
		if ( empty( $position ) ) $position = 'top';

		self::$admin_pointers[ $screen ][ $id ] = array( 'target' => $target, 'content' => $content, 'position' => $position );

		if ( !empty( $classes ) ) self::$admin_pointers[ $screen ][ $id ]['classes'] = $classes;

		return true;

	}

	/**
	 * Adds the main WP Pointers for the plugin (settings hint, builder tour, etc)
	 */
	public static function add_admin_pointers() {

		self::add_admin_pointer( 'wpzlb10_pointer_builder_interface', '#wpzlb', sprintf( '<h3>%s</h3><p>%s</p><div class="count">1/4</div>', __( 'ZOOM Builder', 'zoom-builder' ), __( 'This is the main ZOOM Builder interface. Here you can create groups, rows within those groups, and columns within those rows. Inside these columns is where you can place any widgets you would like. You can also resize columns and change some basic styles associated with each group. The interface is designed to be as user-friendly as possible.', 'zoom-builder' ) ), array( 'edge' => 'bottom', 'align' => 'center', 'at' => 'center top-10' ), 'wpzlb-pointer-tour wpzlb-pointer-tour-step-1', 'builder' );
		self::add_admin_pointer( 'wpzlb10_pointer_loadsave_layouts', '#wpzlb-loadlayout', sprintf( '<h3>%s</h3><p>%s</p><div class="count">2/4</div>', __( 'Loading &amp; Saving Layouts', 'zoom-builder' ), __( 'You can also load previously saved layouts to overwrite the current layout. This could be useful if you want to have a similar layout to one you have made before. You can even save the current layout so that you may use it later.', 'zoom-builder' ) ), array( 'edge' => 'top', 'align' => 'left-23' ), 'wpzlb-pointer-tour wpzlb-pointer-tour-step-2', 'builder' );
		self::add_admin_pointer( 'wpzlb10_pointer_auto_save', '#wpzlb #widgets-right .group:first-child .row:first-child .row-controls', sprintf( '<h3>%s</h3><p>%s</p><div class="count">3/4</div>', __( 'Automatic Saving', 'zoom-builder' ), __( 'When you add/change groups, rows, and columns, and also when you move widgets, the changes are automatically saved. So you can immediately view changes on the frontend of your site by simply browsing to or refreshing the associated page on the frontend. No need to manually save the page!', 'zoom-builder' ) ), array( 'edge' => 'left', 'align' => 'center', 'at' => 'right+20 center' ), 'wpzlb-pointer-tour wpzlb-pointer-tour-step-3', 'builder' );
		self::add_admin_pointer( 'wpzlb10_pointer_plugin_settings', '#toplevel_page_wpzlb-settings', sprintf( '<h3>%s</h3><p>%s</p>', __( 'Plugin Settings', 'zoom-builder' ), __( 'Go here to view and modify the various plugin settings like: which post types have the builder enabled, any modules you want to be available for use with the plugin, and more&hellip;', 'zoom-builder' ) ), array( 'edge' => 'left', 'align' => 'center', 'at' => 'right-5 center' ) );
		self::add_admin_pointer( 'wpzlb10_pointer_tour_start', '#wpzoom_layout_builder', sprintf( '<h3>%s</h3><p>%s</p><div class="count">0/4</div>', __( 'ZOOM Builder Tour', 'zoom-builder' ), __( 'Want to take a quick tour of a couple of the great features the ZOOM Builder has to offer? Just click &ldquo;<em>Start Tour</em>&rdquo; to get started&hellip;', 'zoom-builder' ) ), array( 'edge' => 'bottom', 'align' => 'center', 'at' => 'center top-10' ), 'wpzlb-pointer-tour wpzlb-pointer-tour-start', 'builder' );
		self::add_admin_pointer( 'wpzlb10_pointer_tour_end', '#wpzoom_layout_builder > h3.hndle', sprintf( '<h3>%s</h3><p>%s</p><div class="count">4/4</div>', __( 'ZOOM Builder Tour', 'zoom-builder' ), __( 'There are many more features available, so feel free to play around and try some things out yourself. Now go have fun building some interesting layouts with the ZOOM Builder!', 'zoom-builder' ) ), array( 'edge' => 'top', 'align' => 'center' ), 'wpzlb-pointer-tour wpzlb-pointer-tour-end', 'builder' );

	}

	/**
	 * Maybe redirect to the plugin welcome page if this looks like the first time the plugin has been activated
	 */
	public static function maybe_activate_redirect() {

		if ( get_option( 'wpzlb-activate' ) === 'true' ) {

			update_option( 'wpzlb-activate', 'false' );
			wp_redirect( admin_url( 'admin.php?page=wpzlb-welcome' ) );
			exit;

		}

	}

	/**
	 * Enqueue stuff for the WP Pointers used in the plugin
	 */
	public static function admin_enqueue_scripts_pointers() {

		$filtered_pointers = self::filtered_admin_pointers();

		if ( empty( $filtered_pointers ) ) return;

		global $pagenow;

		$all_pointers = array();

		foreach ( $filtered_pointers as $screen => $pointers ) {
			if ( $screen == 'global' || ( $screen == 'builder' && ZOOM_Builder_Utils::screen_is_builder() ) || $screen == $pagenow ) {
				$all_pointers = array_merge( $all_pointers, $pointers );
			}
		}

		if ( empty( $all_pointers ) ) return;

		wp_register_script( 'wpzlb-script-pointers', ZOOM_Builder::$assets_url . '/js/pointers.js', array( 'jquery', 'wp-pointer' ), self::$version, true );
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_script( 'wpzlb-script-pointers' );
		wp_localize_script( 'wpzlb-script-pointers', 'wpzlbAdminPointers', $all_pointers );
		wp_localize_script( 'wpzlb-script-pointers', 'wpzlbAdminPointersL10n', array(
			'tourStart' => __( 'Start Tour', 'zoom-builder' ),
			'tourSkip' => __( 'Skip Tour', 'zoom-builder' ),
			'tourEnd' => __( 'End Tour', 'zoom-builder' ),
			'tourNext' => __( 'Next', 'zoom-builder' ),
		) );

	}

	/**
	 * Outputs the builder welcome page
	 */
	public static function display_welcome_page() {

		?><div class="wrap about-wrap">

			<h1><?php _e( 'Welcome to <strong>ZOOM Builder</strong>', 'zoom-builder' ); ?></h1>

			<div class="about-text">
				<p><?php _e( 'Thank you for installing ZOOM Builder, the feature-rich easy-to-use page layout builder plugin for WordPress, from <a href="http://wpzoom.com" target="_blank">WPZOOM</a>.', 'zoom-builder' ); ?></p>
				<p class="buttons">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpzlb-settings' ) ); ?>" class="button button-primary"><?php _e( 'Settings', 'zoom-builder' ); ?></a>
					<a href="http://www.wpzoom.com/docs/" target="_blank" class="button"><?php _e( 'Documentation', 'zoom-builder' ); ?></a>


				</p>
			</div>

			<div class="wp-badge"><?php printf( __( 'Version %s', 'zoom-builder' ), self::$version ); ?></div>

			<hr>

 			<div class="changelog">
				<h2 class="about-headline-callout"><?php _e( 'Drag & Drop Page Layout Builder' ); ?></h2>
				<img class="about-overview-img" src="http://www.wpzoom.com/images/builder_assets/builder-overview.png" />
				<div class="feature-section col three-col about-updates">
					<div class="col-1">
						<img src="http://www.wpzoom.com/images/builder_assets/style.png" />
						<h3><?php _e( 'Style Customizer' ); ?></h3>
						<p><?php _e( 'Want a section of your layout to have different styles than everything else? No problem. Just use the group styles options to style it how you would like.' ); ?></p>
					</div>
					<div class="col-2">
						<img src="http://www.wpzoom.com/images/builder_assets/dragdrop.png" />
						<h3><?php _e( 'Easy to Use for Everyone!' ); ?></h3>
						<p><?php _e( 'An intuitive design makes the ZOOM Builder easy to use for anyone! With several ways of placing widgets, you can find your preferred way to use the ZOOM Builder interface.' ); ?></p>
					</div>
					<div class="col-3 last-feature">
						<img src="http://www.wpzoom.com/images/builder_assets/layouts.png" />
						<h3><?php _e( 'Layout Import/Export' ); ?></h3>
						<p><?php _e( 'The ZOOM Builder comes with 10 unique premade layouts ready for use. Simply select any of them from the load layout dropdown in the builder interface.' ); ?></p>
					</div>
				</div>
			</div>

			<hr>

			<div class="changelog">
				<div class="feature-section col two-col">
					<div>
						<h3><?php _e( 'Works with any Theme and Widget' ); ?></h3>
						<p><?php _e( 'The ZOOM Builder has been tested with many popular themes and theme frameworks to ensure that you can use its advanced functionality no matter which WordPress theme you may use.' ); ?></p>

						<p><?php _e( 'Are you ready to take your site to the next level?' ); ?></p>
						<h4><?php _e( 'Supercharge your site with ZOOM Builder today!' ); ?></h4>
 					</div>
					<div class="last-feature about-colors-img">
						<img src="http://www.wpzoom.com/images/builder_assets/macbook-theme.png" />
					</div>
				</div>
			</div>

			<hr>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpzlb-settings' ) ); ?>"><?php _e( 'Go to ZOOM Builder settings', 'zoom-builder' ); ?></a>
				<?php _e('or'); ?>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>"><?php _e( 'Create a New Page', 'zoom-builder' ); ?></a>
			</div>

		</div><?php

	}

	/**
	 * Enqueue the welcome page styles if needed
	 */
	public static function admin_enqueue_scripts_welcome( $hook ) {

		if ( $hook != 'zoom-builder_page_wpzlb-welcome' ) return;

		wp_register_style( 'wpzlb-style-welcome', ZOOM_Builder::$assets_url . '/css/welcome.css', array(), self::$version );
		wp_enqueue_style( 'wpzlb-style-welcome' );

	}

	/**
	 * Filters the WordPress admin menu so the welcome page does not show up in the menu, but the ZOOM Builder main menu item is still highlighted when we are on the welcome page
	 */
	public static function filter_welcome_page_parent_menu( $parent ) {

		global $current_screen, $submenu;

		if ( isset( $current_screen->id ) && $current_screen->id == 'zoom-builder_page_wpzlb-welcome' ) {
			global $submenu_file;
			$submenu_file = '';
		}

		if ( isset( $submenu['wpzlb-settings'] ) ) {
			foreach ( $submenu['wpzlb-settings'] as $k => $v ) {
				if ( array_search( 'wpzlb-welcome', $v ) ) {
					unset( $submenu['wpzlb-settings'][$k] );
					break;
				}
			}
		}

		return $parent;

	}

	/**
	 * Add a "Settings" link for this plugin on the core WordPress plugins screen
	 */
	public static function plugin_action_links( $links ) {

		$links[] = sprintf( '<a href="%s" title="%s">%s</a>', admin_url( 'admin.php?page=wpzlb-settings' ), __( 'The plugin settings', 'zoom-builder' ), __( 'Settings', 'zoom-builder' ) );

		return $links;

	}

	/**
	 * Called before the core `wp_ajax_widgets_order` hook to patch the $_POST['sidebars'] array so layout builder sidebars widgets are not lost due to the changes from widgets_admin_page() above
	 */
	public static function ajax_widgets_order() {

		foreach ( (array)wp_get_sidebars_widgets() as $sidebar => $widgets ) {
			if ( preg_match( '/^_wpzlb-page-[0-9]+-widgets$/i', $sidebar ) )
				$_POST['sidebars'][$sidebar] = empty( $widgets ) ? '' : 'widget-0_' . implode( ',widget-0_', (array)$widgets );
		}

	}

	/**
	 * Temporarily store the builder sidebars after theme switch but before the sidebars are changed in the db
	 */
	public static function after_switch_theme_before() {

		foreach ( (array)wp_get_sidebars_widgets() as $sidebar => $widgets ) {
			if ( preg_match( '/^_wpzlb-page-[0-9]+-widgets$/i', $sidebar ) && !empty( $widgets ) )
				self::$_builder_sidebars_temp[ $sidebar ] = (array)$widgets;
		}

	}

	/**
	 * Restore the builder sidebars after theme switch
	 */
	public static function after_switch_theme_after() {

		if ( empty( self::$_builder_sidebars_temp ) ) return;

		$all_widgets = array();
		foreach ( (array)self::$_builder_sidebars_temp as $sidebar ) {
			if ( !empty( $sidebar ) )
				$all_widgets = array_merge( $all_widgets, (array)$sidebar );
		}

		$sidebars_widgets = (array)wp_get_sidebars_widgets();

		if ( !empty( $sidebars_widgets ) ) {
			foreach ( $sidebars_widgets as $sidebar => $widgets ) {
				if ( !preg_match( '/^_wpzlb-page-[0-9]+-widgets$/i', $sidebar ) && !empty( $widgets ) ) {
					foreach ( $widgets as $index => $widget_id ) {
						if ( in_array( $widget_id, $all_widgets ) )
							unset( $sidebars_widgets[ $sidebar ][ $index ] );
					}
				}
			}
		}

		$sidebars_widgets = array_merge( $sidebars_widgets, (array)self::$_builder_sidebars_temp );

		wp_set_sidebars_widgets( $sidebars_widgets );

		self::$_builder_sidebars_temp = array();

	}

}

# Used in a theme to register a location that can have a specific layout builder group assigned to it to be displayed
function wpzlb_register_theme_group_location( $location ) { return ZOOM_Builder_Display::register_theme_group_location( $location ); }

# Used in a theme to display the layout builder group assigned to the given registered location
function wpzlb_display_theme_group_location( $location ) { return ZOOM_Builder_Display::display_theme_group_location( $location ); }