<?php
/**
 * This file has the Prose Child Theme Custom Code editor.
 *
 * @author StudioPress
 */

/**
 * Registers a new admin page, providing content and corresponding menu item
 * for the Custom Code page.
 *
 * @since 1.5.0
 */
class Prose_Custom_Code extends Genesis_Admin_Boxes {

	/**
	 * Create an admin menu item and settings page.
	 *
	 * @since 1.5.0
	 *
	 * @uses PROSE_SETTINGS_FIELD settings field key
	 *
	 */
	function __construct() {

		$page_id = 'prose-custom';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'genesis',
				'page_title'  => __( 'Custom Code', 'prose' ),
				'menu_title'  => __( 'Custom Code', 'prose' ),
				'capability'  => 'unfiltered_html',
			),
		);

		$page_ops = array(
			'screen_icon' => 'themes',
			'save_button_text' => __( 'Save Changes', 'prose' ),
			'reset_button_text' => __( 'Reset All', 'prose' ),
		);

		$settings_field = 'prose-custom';
		$default_settings = array(
			'css' => "/** Do not remove this line. Edit CSS below. */\n",
			'php' => "<?php\n/** Do not remove this line. Edit functions below. */\n"
		);

		$this->create( $page_id, $menu_ops, $page_ops, $settings_field, $default_settings );

	}


	/**
	 * Load the necessary scripts for this admin page.
	 *
	 * @uses genesis_load_admin_js()
	 *
	 * @since 1.5.0
	 *
	 */
	function scripts() {

		/** Load parent scripts as well as Genesis admin scripts */
		parent::scripts();
		genesis_load_admin_js();

	}

	/**
	 * Save the Custom CSS and PHP files
	 *
	 * This function highjacks the option on the way & saves the CSS/PHP to the custom file. We don't actually
	 * want to save the custom code to the DB, just to the files in the media folder.
	 *
	 * @param array $newvalue Values submitted from the design settings page.
	 * @param array $oldvalue Unused
	 * @return false
	 * @since 1.5.0
	 */
	function save( $newvalue, $oldvalue = '' ) {

		/** Permission check */
		if ( ! current_user_can( 'unfiltered_html' ) )
			return false;

		/** Don't load custom.php when trying to save custom.php */
		remove_action( 'after_setup_theme', 'prose_do_custom_php' );

		if ( prose_make_stylesheet_path_writable() ) {

			/** Write CSS */
			$f = fopen( prose_get_custom_stylesheet_path(), 'w+' );
			if ( $f !== FALSE ) {

				fwrite( $f, stripslashes( $newvalue['css'] ) );
				fclose( $f );
				prose_create_stylesheets();

			}

			/** Write PHP */
			prose_edit_custom_php( $newvalue['php'] );

		}

		/** Retain only the reset value, if necessary, otherwise just revert to defaults */
		if ( isset( $newvalue['reset'] ) )
			return wp_parse_args( $newvalue, $this->default_settings );
		else
			return $this->default_settings;

	}

	/**
	 * Add notices to the top of the page when certain actions take place.
	 *
	 * Add default notices via parent::notices() as well as a few custom ones.
	 *
	 * @since 1.5.0
	 *
	 */
	function notices() {

		/** Check to verify we're on the right page */
		if ( ! genesis_is_menu_page( $this->page_id ) )
			return;

		/** Show error if can't write to server */
		if ( ! prose_make_stylesheet_path_writable() ) {

			if ( ! is_multisite() || is_super_admin() )
				$message = __( 'The %s folder does not exist or is not writeable. Please create it or <a href="http://codex.wordpress.org/Changing_File_Permissions">change file permissions</a> to 777.', 'prose' );
			else
				$message = __( 'The %s folder does not exist or is not writeable. Please contact your network administrator.', 'prose' );

			$css_path =  prose_get_stylesheet_location( 'path' );

			echo '<div id="message-unwritable" class="error"><p><strong>'. sprintf( $message, _get_template_edit_filename( $css_path, dirname( $css_path ) ) ) . '</strong></p></div>';
		}

		/** Genesis_Admin notices */
		parent::notices();

	}

	/**
 	 * Register meta boxes on the Custom Code page.
 	 *
 	 * @since 1.5.0
 	 *
 	 */
	function metaboxes() {

		add_meta_box( 'prose-custom-css', __( 'Custom CSS', 'prose' ), array( $this, 'custom_css' ), $this->pagehook, 'main' );
		add_meta_box( 'prose-custom-php', __( 'Custom Functions', 'prose' ), array( $this, 'custom_PHP' ), $this->pagehook, 'main' );

	}

	/**
	 * CSS to edit.
	 *
	 * @author StudioPress
	 * @since 1.5.0
	 */
	function custom_css() {

		$custom_css = file_get_contents( prose_get_custom_stylesheet_path() );

		printf( '<textarea name="%s" id="%s" cols="80" rows="22">%s</textarea>', $this->get_field_name( 'css' ), $this->get_field_id( 'css' ), esc_textarea( $custom_css ) );

	}

	/**
	 * PHP to edit.
	 *
	 * @author StudioPress
	 * @since 1.5.0
	 */
	function custom_php() {

		$custom_php = file_get_contents( prose_get_custom_php_path() );

		printf( '<textarea name="%s" id="%s" cols="80" rows="22">%s</textarea>', $this->get_field_name( 'php' ), $this->get_field_id( 'php' ), esc_textarea( $custom_php ) );

	}

}

add_action( 'genesis_admin_menu', 'prose_custom_code_menu' );
/**
 * Instantiate the class to create the menu.
 *
 * @author StudioPress
 * @since 1.5.0
 */
function prose_custom_code_menu() {

	global $_prose_custom_code;

	/** Don't add submenu items if Prose menu is disabled */
	if( ! current_theme_supports( 'prose-design-settings' ) )
		return;

	$_prose_custom_code = new Prose_Custom_Code;

}