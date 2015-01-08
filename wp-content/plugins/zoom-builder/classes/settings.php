<?php defined('ABSPATH') or die; # Don't ever load this file directly

/**
 * ZOOM_Builder_Settings Class
 *
 * Registers plugin options and handles output of the options pages.
 *
 * @package ZOOM_Builder
 * @subpackage Settings
 */

class ZOOM_Builder_Settings {

	/**
	 * Hook some stuff up to WordPress
	 */
	public static function init() {

		if ( !is_admin() ) return;

		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_post_wpzlb-savedlayout-import', array( __CLASS__, 'process_import' ) );
		add_action( 'admin_post_wpzlb-savedlayout-export', array( __CLASS__, 'process_export' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		if ( is_multisite() ) add_action( 'network_admin_menu', array( __CLASS__, 'network_admin_menu' ) );
		add_action( 'wp_ajax_wpzlb-delete-saved-layout', array( __CLASS__, 'ajax_delete_saved_layout' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts_settings' ) );

	}

	/**
	 * Register all needed plugin settings
	 */
	public static function admin_init() {

		add_settings_section( 'wpzlb-settings-general', false, false, 'wpzlb-settings' );
		add_settings_section( 'wpzlb-settings-widgets', false, false, 'wpzlb-settings' );
		add_settings_section( 'wpzlb-settings-frontend', false, false, 'wpzlb-settings' );
		add_settings_section( 'wpzlb-savedlayouts-settings', false, false, 'wpzlb-savedlayouts' );

		register_setting( 'wpzlb-settings', 'wpzlb-settings-general-modules', array( __CLASS__, 'sanitize_settings_field_modules' ) );
		register_setting( 'wpzlb-settings', 'wpzlb-settings-general-posttypes', array( __CLASS__, 'sanitize_settings_field_posttypes' ) );
		register_setting( 'wpzlb-settings', 'wpzlb-settings-frontend-layoutposition', array( __CLASS__, 'sanitize_settings_field_layoutposition' ) );
		register_setting( 'wpzlb-settings', 'wpzlb-settings-frontend-darktheme', array( __CLASS__, 'sanitize_settings_field_darktheme' ) );
		register_setting( 'wpzlb-settings', 'wpzlb-settings-widgets-exclude', array( __CLASS__, 'sanitize_settings_field_excludewidgets' ) );
		register_setting( 'wpzlb-savedlayouts', 'wpzlb-savedlayouts-exclude', array( __CLASS__, 'sanitize_settings_field_excludesavedlayouts' ) );

		add_settings_field( 'wpzlb-settings-general-modules', __( 'Enabled Modules', 'zoom-builder' ), array( __CLASS__, 'display_settings_field_modules' ), 'wpzlb-settings', 'wpzlb-settings-general' );
		add_settings_field( 'wpzlb-settings-general-posttypes', __( 'Builder-Enabled Post Types', 'zoom-builder' ), array( __CLASS__, 'display_settings_field_posttypes' ), 'wpzlb-settings', 'wpzlb-settings-general' );
		add_settings_field( 'wpzlb-settings-frontend-layoutposition', __( 'Layout Position', 'zoom-builder' ), array( __CLASS__, 'display_settings_field_layoutposition' ), 'wpzlb-settings', 'wpzlb-settings-frontend' );
		add_settings_field( 'wpzlb-settings-frontend-darktheme', __( 'Dark Theme', 'zoom-builder' ), array( __CLASS__, 'display_settings_field_darktheme' ), 'wpzlb-settings', 'wpzlb-settings-frontend' );
		add_settings_field( 'wpzlb-settings-widgets-exclude', __( 'Exclude Widgets', 'zoom-builder' ), array( __CLASS__, 'display_settings_field_excludewidgets' ), 'wpzlb-settings', 'wpzlb-settings-widgets' );
		add_settings_field( 'wpzlb-savedlayouts-exclude', __( 'Exclude Saved Layouts', 'zoom-builder' ), array( __CLASS__, 'display_settings_field_excludesavedlayouts' ), 'wpzlb-savedlayouts', 'wpzlb-savedlayouts-settings' );

	}

	/**
	 * Enqueue all the scripts/styles needed for the settings page
	 */
	public static function admin_enqueue_scripts_settings( $hook ) {

		if ( $hook != 'toplevel_page_wpzlb-settings' && $hook != 'zoom-builder_page_wpzlb-saved-layouts' && $hook != 'zoom-builder_page_wpzlb-reset' && $hook != 'zoom-builder_page_wpzlb-export' )
			return;

		wp_register_style( 'wpzlb-style-settings', ZOOM_Builder::$assets_url . '/css/admin-settings.css', array(), ZOOM_Builder::$version );
		wp_register_script( 'wpzlb-script-settings', ZOOM_Builder::$assets_url . '/js/settings.js', array( 'jquery', 'jquery-color' ), ZOOM_Builder::$version, true );

		wp_enqueue_style( 'wpzlb-style-settings' );
		wp_enqueue_script( 'jquery-color' );
		wp_enqueue_script( 'wpzlb-script-settings' );

		wp_localize_script( 'wpzlb-script-settings', 'wpzlbL10n', array(

			'tooltipSettingsSavedLayoutDelete' => __( 'Delete This Saved Layout (Cannot be undone!)', 'zoom-builder' ),

			'inputLabelSettingsSavedLayoutSave' => __( 'Save Changes', 'zoom-builder' ),
			'inputLabelSettingsSavedLayoutImport' => __( 'Import&hellip;', 'zoom-builder' ),
			'inputLabelSettingsSavedLayoutExport' => __( 'Export&hellip;', 'zoom-builder' ),

			'textSettingsSavedLayoutNone' => __( 'No saved layouts found&hellip;', 'zoom-builder' ),

			'confirmSettingsSavedLayoutDelete' => __( "You are about to delete this saved layout. This cannot be undone!\n\nAre you sure you want to continue?", 'zoom-builder' ),

			'inputLabelResetWorking' => __( 'Working&hellip;', 'zoom-builder' ),
			'confirmWidgetsReset' => __( "You are about to reset/delete all ZOOM Builder widgets!\n\nAre you sure you want to continue? THIS CANNOT BE UNDONE!", 'zoom-builder' )

		) );

	}

	/**
	 * Add all plugin pages
	 */
	public static function admin_menu() {

		add_menu_page( __( 'ZOOM Builder', 'zoom-builder' ), __( 'ZOOM Builder', 'zoom-builder' ), 'manage_options', 'wpzlb-settings', array( __CLASS__, 'display_settings_page' ), 'none' );
		add_submenu_page( 'wpzlb-settings', __( 'ZOOM Builder Settings', 'zoom-builder' ), __( 'Settings', 'zoom-builder' ), 'manage_options', 'wpzlb-settings', array( __CLASS__, 'display_settings_page' ) );
		add_submenu_page( 'wpzlb-settings', __( 'ZOOM Builder Saved Layouts', 'zoom-builder' ), __( 'Saved Layouts', 'zoom-builder' ), 'manage_options', 'wpzlb-saved-layouts', array( __CLASS__, 'display_saved_layouts_page' ) );

		if ( ! is_multisite() ) {
			$hook = add_submenu_page( 'wpzlb-settings', __( 'ZOOM Builder License', 'zoom-builder' ), __( 'License', 'zoom-builder' ), 'manage_options', 'wpzlb-settings-license', array( __CLASS__, 'display_license_settings_page' ) );
			add_action( 'load-' . $hook, array ( ZOOM_Builder_Updater::getInstance(), 'license_activate_request' ) );
			add_action( 'load-' . $hook, array ( ZOOM_Builder_Updater::getInstance(), 'license_deactivate_request' ) );
		}

		$reset_page_hook = add_submenu_page( 'wpzlb-settings', __( 'Reset ZOOM Builder Widgets', 'zoom-builder' ), __( 'Reset Widgets', 'zoom-builder' ), 'manage_options', 'wpzlb-reset', array( __CLASS__, 'display_reset_page' ) );
		add_action( 'load-' . $reset_page_hook, array ( __CLASS__, 'do_widgets_reset' ) );

	}

	/**
	 * Add plugins pages for network admin
	 */
	public static function network_admin_menu() {

		add_menu_page( __( 'ZOOM Builder', 'zoom-builder' ), __( 'ZOOM Builder', 'zoom-builder' ), 'manage_options', 'wpzlb-settings-license', array( __CLASS__, 'display_license_settings_page' ), 'dashicons-hammer' );
		$hook = add_submenu_page( 'wpzlb-settings', __( 'ZOOM Builder License Settings', 'zoom-builder' ), __( 'License', 'zoom-builder' ), 'manage_options', 'wpzlb-settings-license', array( __CLASS__, 'display_license_settings_page' ) );
		add_action( 'load-' . $hook, array ( ZOOM_Builder_Updater::getInstance(), 'license_activate_request' ) );
		add_action( 'load-' . $hook, array ( ZOOM_Builder_Updater::getInstance(), 'license_deactivate_request' ) );

	}

	/**
	 * Settings display callback functions
	 */
	public static function display_settings_field_modules( $args ) {

		?><div class="wp-tab-panel">
			<ul>

				<?php
				foreach ( glob( ZOOM_Builder::$modules_path . '/*', GLOB_ONLYDIR ) as $dir ) {

					$basename = basename( $dir );

					if ( is_readable( sprintf( '%s/%s.php', $dir, $basename ) ) )
						echo '<li><label><input type="checkbox" name="wpzlb-settings-general-modules[]" value="' . esc_attr( $basename ) . '" ' . checked( in_array( $basename, ZOOM_Builder::$modules ), true, false ) . ' /> ' . apply_filters( 'the_title', ucwords( str_replace( array( '-', '_' ), ' ', $basename ) ) ) . '</label></li>';

				}
				?>

			</ul>
		</div>

		<small class="howto"><?php _e( 'Select which modules will be enabled.', 'zoom-builder' ); ?></small><?php

	}

	public static function display_settings_field_posttypes( $args ) {

		?><div class="wp-tab-panel">
			<ul>

				<?php
				foreach ( get_post_types( array( 'public' => true, 'show_ui' => true ), 'objects' ) as $post_type ) {
					if ( $post_type->name == 'attachment' ) continue;
					echo '<li><label><input type="checkbox" name="wpzlb-settings-general-posttypes[]" value="' . esc_attr( $post_type->name ) . '" ' . checked( in_array( $post_type->name, ZOOM_Builder::$post_types ), true, false ) . ' /> ' . apply_filters( 'the_title', $post_type->label ) . '</label></li>';
				}
				?>

			</ul>
		</div>

		<small class="howto"><?php _e( 'Select which post types will have the layout builder enabled.', 'zoom-builder' ); ?></small><?php

	}

	public static function display_settings_field_layoutposition( $args ) {

		?><ul>
			<li><label><input type="radio" name="wpzlb-settings-frontend-layoutposition" value="above"<?php checked( 'above', ZOOM_Builder::$layout_position ); ?> /> <?php _e( 'Above', 'zoom-builder' ); ?></label> <small class="howto"><?php _e( '&mdash; Layout will be placed above/before any post content.', 'zoom-builder' ); ?></small></li>
			<li><label><input type="radio" name="wpzlb-settings-frontend-layoutposition" value="below"<?php checked( 'below', ZOOM_Builder::$layout_position ); ?> /> <?php _e( 'Below', 'zoom-builder' ); ?></label> <small class="howto"><?php _e( '&mdash; Layout will be placed below/after any post content.', 'zoom-builder' ); ?></small></li>
			<li><label><input type="radio" name="wpzlb-settings-frontend-layoutposition" value="replace"<?php checked( 'replace', ZOOM_Builder::$layout_position ); ?> /> <?php _e( 'Replace', 'zoom-builder' ); ?></label> <small class="howto"><?php _e( '&mdash; Layout will take the place of any post content (i.e. No post content will be visible).', 'zoom-builder' ); ?></small></li>
		</ul>

		<small class="howto"><?php _e( 'Select where the layout will appear, in relation to the post content, on the frontend of the site.', 'zoom-builder' ); ?></small><?php

	}

	public static function display_settings_field_darktheme( $args ) {

		?><label><input type="checkbox" name="wpzlb-settings-frontend-darktheme" value="true"<?php checked( ZOOM_Builder::$dark_theme ); ?> /> <?php _e( 'Use Dark Theme', 'zoom-builder' ); ?></label>
		<small class="howto"><?php _e( 'Should builder layouts on the frontend use a dark theme? Useful if you&rsquo;re using a dark WordPress theme.', 'zoom-builder' ); ?></small><?php

	}

	public static function display_settings_field_excludewidgets( $args ) {

		global $wp_registered_widgets;

		$sort = $wp_registered_widgets;
		$wpzoom_widgets = array();
		$builder_widgets = array();

		foreach ( $sort as $i => $w ) {

			if ( ZOOM_Builder_Utils::is_wpzoom_widget( $w['id'] ) || ZOOM_Builder_Utils::is_wpzoom_builder_widget( $w['id'] ) ) {

				if ( ZOOM_Builder_Utils::is_wpzoom_widget( $w['id'] ) ) {
					$w['name'] = '<span class="wpzoom-widget">' . __( 'WPZOOM:', 'zoom-builder' ) . '</span> ' . preg_replace( '/^wpzoom:\s*/i', '', trim( $w['name'] ) );
					$wpzoom_widgets[$i] = $w;
				} elseif ( ZOOM_Builder_Utils::is_wpzoom_builder_widget( $w['id'] ) ) {
					$w['name'] = '<span class="builder-widget">' . __( 'Builder:', 'zoom-builder' ) . '</span> ' . trim( $w['name'] );
					$builder_widgets[$i] = $w;
				}

				unset( $sort[$i] );

			}

		}

		function _sort_name_callback( $a, $b ) { return strnatcasecmp( $a['name'], $b['name'] ); }
		usort( $sort, '_sort_name_callback' );
		usort( $wpzoom_widgets, '_sort_name_callback' );
		usort( $builder_widgets, '_sort_name_callback' );

		$sort = array_merge( $builder_widgets, $wpzoom_widgets, $sort );

		unset( $wpzoom_widgets, $builder_widgets );

		$done = array();

		?><div class="wp-tab-panel">
			<ul>

				<?php
				foreach ( $sort as $widget ) {

					$id = preg_replace( '/^(.+)-[0-9]+$/i', '$1', $widget['id'] );

					if ( in_array( $id, $done, true ) ) continue;

					$done[] = $id;

					echo '<li><label><input type="checkbox" name="wpzlb-settings-widgets-exclude[]" value="' . esc_attr( $id ) . '" ' . checked( in_array( $id, ZOOM_Builder::$exlcude_widgets ), true, false ) . ' /> ' . apply_filters( 'the_title', $widget['name'] ) . '</label></li>';
				}
				?>

			</ul>
		</div>

		<small class="howto"><?php _e( 'Select any widgets that should be excluded from being used in the layout builder.', 'zoom-builder' ); ?></small><?php

	}

	public static function display_settings_field_excludesavedlayouts( $args ) {

		$saved_layouts = ZOOM_Builder::$saved_layouts;

		?><div class="wp-tab-panel">
			<ul>

				<?php
				if ( empty( $saved_layouts ) ) {

					echo '<li><em class="nonessential">' . __( 'No saved layouts found&hellip;', 'zoom-builder' ) . '</em></li>';

				} else {

					foreach ( $saved_layouts as $id => $saved_layout ) {

						echo '<li id="wpzlb-savedlayout-' . esc_attr( $id ) . '"><label><input type="checkbox" name="wpzlb-savedlayouts-exclude[]" value="' . esc_attr( $id ) . '" ' . checked( in_array( $id, ZOOM_Builder::$exclude_layouts ), true, false ) . ' /> ' . apply_filters( 'the_title', $saved_layout['name'] ) . '</label></li>';

					}

				}
				?>

			</ul>
		</div>

		<small class="howto"><?php _e( 'Select any saved layouts that should be excluded from being used in the layout builder.', 'zoom-builder' ); ?></small><?php

		wp_nonce_field( 'delete-saved-layout', '_wpzlbnonce_saved_layout_delete', false );

	}

	/**
	 * Settings sanitize callback functions
	 */
	public static function sanitize_settings_field_modules( $value ) { return ZOOM_Builder_Utils::sanitize_modules_array( (array)$value ); }
	public static function sanitize_settings_field_posttypes( $value ) { return ZOOM_Builder_Utils::sanitize_post_types_array( (array)$value ); }
	public static function sanitize_settings_field_layoutposition( $value ) { return ZOOM_Builder_Utils::sanitize_layout_position( (string)$value ); }
	public static function sanitize_settings_field_darktheme( $value ) { return (string)$value == 'true' ? 'true' : 'false'; }
	public static function sanitize_settings_field_excludewidgets( $value ) { return ZOOM_Builder_Utils::sanitize_widget_names_array( (array)$value ); }
	public static function sanitize_settings_field_excludesavedlayouts( $value ) { return ZOOM_Builder_Utils::sanitize_saved_layouts_ids_array( (array)$value ); }

	/**
	 * Almost the same as the core do_settings_sections() function
	 */
	public static function do_settings_sections( $page ) {

		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections ) || !isset( $wp_settings_sections[$page] ) )
			return;

		$first = true;

		foreach ( (array) $wp_settings_sections[$page] as $section ) {

			echo '<div id="' . esc_attr( $section['id'] ) . '" class="settings-section' . ( $first ? ' section-active' : '' ) . '">';

			if ( $section['title'] )
				echo "<h3>{$section['title']}</h3>\n";

			if ( $section['callback'] )
				call_user_func( $section['callback'], $section );

			if ( isset( $wp_settings_fields ) && isset( $wp_settings_fields[$page] ) && isset( $wp_settings_fields[$page][$section['id']] ) ) {

				echo '<table class="form-table">';
				do_settings_fields( $page, $section['id'] );
				echo '</table>';

			}

			echo '</div>';

			if ( $first === true ) $first = false;

		}

	}

	/**
	 * Display the plugin settings page
	 */
	public static function display_settings_page() {

		?><div class="wrap">

			<?php settings_errors(); ?>

			<h2 class="nav-tab-wrapper">
				<span class="rghtmarg"><?php _e( 'Settings', 'zoom-builder' ); ?></span>
				<a href="#wpzlb-settings-general" class="nav-tab nav-tab-active"><?php _e( 'General', 'zoom-builder' ); ?></a>
				<a href="#wpzlb-settings-widgets" class="nav-tab"><?php _e( 'Widgets', 'zoom-builder' ); ?></a>
				<a href="#wpzlb-settings-frontend" class="nav-tab"><?php _e( 'Frontend', 'zoom-builder' ); ?></a>
			</h2>

			<form method="post" action="<?php echo admin_url( '/options.php' ); ?>">

				<?php settings_fields( 'wpzlb-settings' ); ?>

				<?php self::do_settings_sections( 'wpzlb-settings' ); ?>

				<?php submit_button(); ?>

			</form>

		</div><?php

	}

	/**
	 * Display the plugin saved layouts page
	 */
	public static function display_saved_layouts_page() {

		$saved_layouts = ZOOM_Builder::$saved_layouts;

		$upload_size_unit = $max_upload_size = wp_max_upload_size();
		$sizes = array( 'KB', 'MB', 'GB' );
		for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ ) {
			$upload_size_unit /= 1024;
		}
		if ( $u < 0 ) {
			$upload_size_unit = 0;
			$u = 0;
		} else {
			$upload_size_unit = (int) $upload_size_unit;
		}

		?><div class="wrap">

			<?php settings_errors(); ?>

			<h2 class="nav-tab-wrapper">
				<span class="rghtmarg"><?php _e( 'Saved Layouts', 'zoom-builder' ); ?></span>
				<a href="#wpzlb-savedlayouts-settings" class="nav-tab nav-tab-active"><?php _e( 'Settings', 'zoom-builder' ); ?></a>
				<a href="#wpzlb-savedlayouts-import" class="nav-tab"><?php _e( 'Import', 'zoom-builder' ); ?></a>
				<a href="#wpzlb-savedlayouts-export" class="nav-tab"><?php _e( 'Export', 'zoom-builder' ); ?></a>
			</h2>

			<div id="wpzlb-savedlayouts-settings" class="settings-section section-active">
				<form method="post" action="<?php echo admin_url( '/options.php' ); ?>">

					<?php
					settings_fields( 'wpzlb-savedlayouts' );
					do_settings_sections( 'wpzlb-savedlayouts' );
					submit_button();
					?>

				</form>
			</div>

			<div id="wpzlb-savedlayouts-import" class="settings-section">
				<form method="post" action="<?php echo admin_url( '/admin-post.php' ); ?>" enctype="multipart/form-data">

					<input type="hidden" name="action" value="wpzlb-savedlayout-import" />
					<?php wp_referer_field(); ?>
					<?php wp_nonce_field( 'import-saved-layout', '_wpzlbnonce_saved_layout_import', false ); ?>
					<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo esc_attr( $max_upload_size ); ?>" />

					<h3><?php _e( 'Import Saved Layout', 'zoom-builder' ); ?></h3>
					<p><?php _e( 'Choose a saved layout file from your computer to import:', 'zoom-builder' ); ?></p>
					
					<div class="wp-tab-panel">
						<input type="file" name="wpzlb-savedlayouts-import" accept="application/json" class="widefat" />
						<small class="howto">
							<?php printf( __( 'Maximum upload file size: <strong class="black">%d%s</strong>', 'zoom-builder' ), esc_html( $upload_size_unit ), esc_html( $sizes[$u] ) );
							?>&ensp;|&ensp;<?php
							_e( 'Allowed file types: <strong class="black">.JSON</strong>', 'zoom-builder' ); ?>
						</small>
					</div>

					<?php submit_button( __( 'Import&hellip;', 'zoom-builder' ), 'primary', 'submit', true, array( 'disabled' => 'disabled' ) ); ?>

				</form>
			</div>

			<div id="wpzlb-savedlayouts-export" class="settings-section">
				<form method="post" action="<?php echo admin_url( '/admin-post.php' ); ?>">

					<input type="hidden" name="action" value="wpzlb-savedlayout-export" />
					<?php wp_referer_field(); ?>
					<?php wp_nonce_field( 'export-saved-layout', '_wpzlbnonce_saved_layout_export', false ); ?>

					<h3><?php _e( 'Export Saved Layout', 'zoom-builder' ); ?></h3>
					<p><?php _e( 'Choose a saved layout below to export:', 'zoom-builder' ); ?></p>

					<div class="wp-tab-panel">
						<ul>

							<?php
							if ( empty( $saved_layouts ) ) {

								echo '<li><em class="nonessential">' . __( 'No saved layouts found&hellip;', 'zoom-builder' ) . '</em></li>';

							} else {

								$first = true;
								foreach ( $saved_layouts as $id => $saved_layout ) {
									echo '<li id="wpzlb-savedlayout-' . esc_attr( $id ) . '"><label><input type="radio" name="wpzlb-savedlayouts-export" value="' . esc_attr( $id ) . '" ' . checked( $first, true, false ) . ' /> ' . apply_filters( 'the_title', $saved_layout['name'] ) . '</label></li>';
									if ( $first == true ) $first = false;
								}

							}
							?>

						</ul>
					</div>

					<?php submit_button( __( 'Export&hellip;', 'zoom-builder' ) ); ?>

				</form>
			</div>

		</div><?php

	}

	/**
	 * Display plugin license settings page
	 */
	public static function display_license_settings_page() {
		$license_key = get_site_option( 'wpzlb-settings-license-key' );
		$activated = ! empty( $license_key );
		?><div class="wrap">

			<?php settings_errors(); ?>

			<h2><?php _e( 'License settings', 'zoom-builder' ); ?></h2>

			<form method="post" action="">
				<?php if ( ! $activated ) : ?>
					<input type="hidden" name="action" value="activate-license-key" />
					<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('activate-license-key'); ?>" />
					<table class="form-table"><tbody><tr valign="top"><th scope="row">License Key</th><td>
						<label>
							<input type="text" name="wpzlb-settings-license-key" value="<?php echo esc_attr( $license_key ) ?>" placeholder="<?php _e( 'Insert ZOOM Builder license key here', 'zoom-builder' ); ?>" size="42" />
						</label>
					</td></tr></tbody></table>
				<?php else: ?>
					<input type="hidden" name="action" value="deactivate-license-key" />
					<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('deactivate-license-key'); ?>" />
				<?php endif; ?>

				<?php
				if ($activated) {
					_e('<p>This product is currently <strong>active</strong> and you will recieve updates.</p>', 'zoom-builder');
					_e('<p>However, if you want to deactivate it there is a button for this, but pelase note,
						once clicked, you will not be able to recieve further plugin updates.</p>', 'zoom-builder');

					submit_button( __( 'Deactivate product.', 'zoom-builder' ) );
				} else {
					submit_button( __( 'Activate license', 'zoom-builder' ) );
				}
				?>

			</form>

		</div><?php

	}

	/**
	 * Display the plugin widgets reset page
	 */
	public static function display_reset_page() {

		?><div class="wrap">

			<?php settings_errors(); ?>

			<h2><?php _e( 'Reset Widgets', 'zoom-builder' ); ?></h2>

			<form id="wpzlb-widgets-reset" method="post" action="<?php echo admin_url( 'admin.php?page=wpzlb-reset' ); ?>">

				<input type="hidden" name="action" value="wpzlb-reset-widgets" />
				<?php wp_nonce_field( 'reset-widgets', '_wpzlbnonce_reset_widgets' ); ?>

				<p class="big-action-button">
					<?php submit_button( __( 'Reset All Widgets', 'zoom-builder' ), 'primary', 'submit', false, array( 'title' => __( 'CANNOT BE UNDONE!', 'zoom-builder' ) ) ); ?>
					<span class="info"><?php _e( 'This will reset/delete all widgets currently associated with the ZOOM Builder.<br/> <em>Can be useful when you&rsquo;re planning to deactivate and/or remove the ZOOM Builder plugin.</em><br/> <strong class="error-message">CANNOT BE UNDONE!</strong><br /> <small class="howto">This will <strong>not</strong> affect your regular WordPress theme widgets.</small>', 'zoom-builder' ); ?></span>
				</p>

			</form>

		</div><?php

	}

	/**
	 * If the proper $_POST variables are present, do the widgets reset action and return the result in the form of a WordPress admin notice
	 */
	public static function do_widgets_reset() {

		if ( !isset( $_POST['action'] ) || $_POST['action'] != 'wpzlb-reset-widgets' || !check_admin_referer( 'reset-widgets', '_wpzlbnonce_reset_widgets' ) )
			return;

		global $wp_registered_widgets;

		$success = false;
		$sidebars_widgets = (array)wp_get_sidebars_widgets();

		$widgets_to_delete = array();
		if ( !empty( $sidebars_widgets ) ) {
			foreach ( $sidebars_widgets as $sidebar => $widgets ) {
				if ( !preg_match( '/^_wpzlb-page-[0-9]+-widgets$/i', $sidebar ) ) continue;
				$widgets_to_delete = array_merge( $widgets_to_delete, $widgets );
				$sidebars_widgets[ $sidebar ] = array();
			}
		}

		if ( !empty( $widgets_to_delete ) ) {

			$widgets_to_delete = array_values( array_unique( $widgets_to_delete ) );

			foreach ( $widgets_to_delete as $widget_to_delete ) {

				if ( !isset( $wp_registered_widgets[ $widget_to_delete ]['params'][0]['number'] ) || !isset( $wp_registered_widgets[ $widget_to_delete ]['callback'][0] ) )
					continue;

				$number = $wp_registered_widgets[ $widget_to_delete ]['params'][0]['number'];
				$class = $wp_registered_widgets[ $widget_to_delete ]['callback'][0];
				$settings = $class->get_settings();

				unset( $settings[ $number ] );

				$class->save_settings( $settings );

			}

			wp_set_sidebars_widgets( $sidebars_widgets );

			$success = true;

		}

		add_settings_error(
			'wpzlb-reset',
			'wpzlb-reset',
			( $success ? __( 'All widgets successfully reset!', 'zoom-builder' ) : __( 'Resetting the widgets failed! <em class="nonessential">(Maybe there are no widgets to reset?)</em>', 'zoom-builder' ) ),
			( $success ? 'updated' : 'error' )
		);

	}

	/**
	 * Receives AJAX requests from the layout editor settings page to delete the specified saved layout
	 */
	public static function ajax_delete_saved_layout() {

		check_ajax_referer( 'delete-saved-layout', 'deletelayout' );

		if ( !current_user_can( 'manage_options' ) )
			wp_die( -1 );

		unset( $_POST['deletelayout'], $_POST['action'] );

		if ( isset( $_POST['layoutid'] ) && !empty( $_POST['layoutid'] ) && isset( ZOOM_Builder::$saved_layouts[ $_POST['layoutid'] ] ) ) {

			unset( ZOOM_Builder::$saved_layouts[ $_POST['layoutid'] ] );

			update_option( 'wpzlb-saved-layouts', ZOOM_Builder::$saved_layouts );

			wp_die( 1 );

		}

		wp_die( -1 );

	}

	/**
	 * If the proper POST/FILES variables are set we need to process a saved layout import
	 */
	public static function process_import() {

		if ( !isset( $_FILES['wpzlb-savedlayouts-import'] ) || empty( $_FILES['wpzlb-savedlayouts-import'] ) || ( isset( $_FILES['wpzlb-savedlayouts-import']['name'] ) && empty( $_FILES['wpzlb-savedlayouts-import']['name'] ) ) )
			return;

		check_admin_referer( 'import-saved-layout', '_wpzlbnonce_saved_layout_import' );

		if ( !current_user_can( 'manage_options' ) || !current_user_can( 'upload_files' ) )
			wp_die( __( 'You do not have permission to perform this action.', 'zoom-builder' ) );

		$upload = $_FILES['wpzlb-savedlayouts-import'];
		$back_url = add_query_arg( 'settings-updated', 'true', wp_get_referer() ) . '#wpzlb-savedlayouts-import';

		if ( ( isset( $upload['error'] ) && $upload['error'] != 0 ) ||
		     !isset( $upload['type'] ) || $upload['type'] != 'application/json' ||
		     !isset( $upload['size'] ) || $upload['size'] < 1 ||
		     false === ( $contents = @file_get_contents( $upload['tmp_name'] ) ) ||
		     null === ( $decoded = json_decode( trim( (string)$contents ), true ) ) ||
		     !isset( $decoded['name'] ) || empty( $decoded['name'] ) ||
		     !isset( $decoded['layout'] ) || !is_array( $decoded['layout'] ) || empty( $decoded['layout'] ) ) {
			add_settings_error( 'wpzlb-savedlayouts-import', 'wpzlb-savedlayouts-import-failed', __( 'An invalid ZOOM Builder saved layout file was given&hellip; Import failed!', 'zoom-builder' ), 'error' );
			set_transient( 'settings_errors', get_settings_errors(), 30 );
			wp_safe_redirect( $back_url );
			exit;
		}

		$id = sanitize_title( trim( (string)$decoded['name'] ) );
		$name = sanitize_text_field( trim( (string)$decoded['name'] ) );
		$layout = ZOOM_Builder_Utils::sanitize_layout_array( $decoded['layout'], true );

		if ( empty( $id ) || empty( $name ) || isset( ZOOM_Builder::$saved_layouts[ $id ] ) ) {
			add_settings_error( 'wpzlb-savedlayouts-import', 'wpzlb-savedlayouts-import-failed', __( 'The given ZOOM Builder saved layout file has an invalid name or a layout with that name already exists&hellip; Import failed!', 'zoom-builder' ), 'error' );
			set_transient( 'settings_errors', get_settings_errors(), 30 );
			wp_safe_redirect( $back_url );
			exit;
		}

		ZOOM_Builder::$saved_layouts[ $id ] = array( 'name' => $name, 'layout' => $layout );

		update_option( 'wpzlb-saved-layouts', ZOOM_Builder::$saved_layouts );

		add_settings_error( 'wpzlb-savedlayouts-import', 'wpzlb-savedlayouts-import-success', __( 'The given ZOOM Builder saved layout was successfully imported!', 'zoom-builder' ), 'updated' );
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		wp_safe_redirect( $back_url );
		exit;

	}

	/**
	 * If the proper POST variables are set we need to process a saved layout export
	 */
	public static function process_export() {

		if ( !isset( $_POST['wpzlb-savedlayouts-export'] ) ) return;

		check_admin_referer( 'export-saved-layout', '_wpzlbnonce_saved_layout_export' );

		if ( !current_user_can( 'manage_options' ) || !current_user_can( 'export' ) )
			wp_die( __( 'You do not have permission to perform this action.', 'zoom-builder' ) );

		$saved_layouts = ZOOM_Builder::$saved_layouts;
		$layout = $_POST['wpzlb-savedlayouts-export'];
		$back_url = add_query_arg( 'settings-updated', 'true', wp_get_referer() ) . '#wpzlb-savedlayouts-export';

		if ( !isset( $saved_layouts[ $layout ] ) ) {
			add_settings_error( 'wpzlb-savedlayouts-export', 'wpzlb-savedlayouts-export-failed', __( 'The given ZOOM Builder saved layout does not exist&hellip; Export failed!', 'zoom-builder' ), 'error' );
			set_transient( 'settings_errors', get_settings_errors(), 30 );
			wp_safe_redirect( $back_url );
			exit;
		}

		header( 'Content-Type: application/json' );
		header( sprintf( 'Content-Disposition: attachment; filename="zoom-builder-export_%s.json"', date( 'y-m-d' ) ) );
		echo json_encode( $saved_layouts[ $layout ] );
		exit;

	}

}