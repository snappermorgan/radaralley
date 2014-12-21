<?php
/**
 * Handles Prose updates.
 *
 * @since    1.5.0
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/prose
 */

/**
 * Pings http://api.genesistheme.com/update-themes/prose/ asking if
 * a new version of this theme is available.
 *
 * If not, it returns false.
 *
 * If so, the external server passes serialized data back to this function,
 * which gets unserialized and returned for use.
 *
 * @since 1.5.0
 *
 * @uses CHILD_THEME_VERSION Prose version string
 *
 * @global string $wp_version WordPress version string
 * @return mixed Unserialized data, or false on failure
 */
function prose_update_check() {

	global $wp_version;

	/**	If updates are disabled */
	if ( ! current_theme_supports( 'prose-auto-updates' ) )
		return false;

	/** Get time of last update check */
	$prose_update = get_transient( 'prose-update' );

	/** If it has expired, do an update check */
	if ( ! $prose_update ) {
		$url     = 'http://api.genesistheme.com/update-themes/prose/';
		$options = apply_filters(
			'prose_update_remote_post_options',
			array(
				'body' => array(
					'prose_version'   => CHILD_THEME_VERSION,
					'genesis_version' => PARENT_THEME_VERSION,
					'wp_version'      => $wp_version,
					'php_version'     => phpversion(),
					'uri'             => home_url(),
					'user-agent'      => "WordPress/$wp_version;",
				),
			)
		);

		$response = wp_remote_post( $url, $options );
		$prose_update = wp_remote_retrieve_body( $response );

		/** If an error occurred, return FALSE, store for 1 hour */
		if ( 'error' == $prose_update || is_wp_error( $prose_update ) || ! is_serialized( $prose_update ) ) {
			set_transient( 'prose-update', array( 'new_version' => CHILD_THEME_VERSION ), 60 * 60 );
			return false;
		}

		/** Else, unserialize */
		$prose_update = maybe_unserialize( $prose_update );

		/** And store in transient for 24 hours */
		set_transient( 'prose-update', $prose_update, 60 * 60 * 24 );
	}

	/** If we're already using the latest version, return false */
	if ( version_compare( CHILD_THEME_VERSION, $prose_update['new_version'], '>=' ) )
		return false;

	return $prose_update;

}

/**
 * Upgrade the database to version 1500.
 *
 * @since 1.5.0
 *
 * @uses _genesis_update_settings()
 */
function prose_update_1500() {

	/** Calculate new unitless line height */
	$body_line_height = absint( prose_get_design_option( 'body_line_height' ) ) / absint( prose_get_design_option( 'body_font_size' ) );

	/** Update Settings */
	_genesis_update_settings(
		array(
			'theme_version'    => '1.5.0',
			'db_version'       => '1500',
			'body_line_height' => round( $body_line_height, 3 )
		),
		PROSE_SETTINGS_FIELD
	);

	/** write CSS to media folder */
	prose_create_stylesheets();
	
	/** write custom.php to media folder */
	prose_create_custom_php();

}

/**
 * Upgrade the database to version 1502.
 *
 * @since 1.5.2
 *
 * @uses _genesis_update_settings()
 */
function prose_update_1501() {

	/** Update Settings */
	_genesis_update_settings(
		array(
			'theme_version'    => '1.5.1',
			'db_version'       => '1501',
		),
		PROSE_SETTINGS_FIELD
	);

	/** Regenerate combined CSS file */
	prose_create_stylesheets();

}

/**
 * Upgrade the database to version 1502.
 *
 * @since 1.5.2
 *
 * @uses _genesis_update_settings()
 */
function prose_update_1502() {

	/** Update Settings */
	_genesis_update_settings(
		array(
			'theme_version'    => '1.5.2',
			'db_version'       => '1502',
		),
		PROSE_SETTINGS_FIELD
	);

	/** Regenerate combined CSS file */
	prose_create_stylesheets();

}

add_action( 'admin_init', 'prose_update', 20 );
/**
 * Update Prose to the latest version.
 *
 * This iterative update function will take a Prose installation, no matter
 * how old, and update its options to the latest version.
 *
 * @since 1.5.0
 *
 * @uses CHILD_DB_VERSION
 * @uses PROSE_SETTINGS_FIELD
 *
 * @return null Returns early if we're already on the latest version.
 */
function prose_update() {

	/** Don't do anything if we're on the latest version */
	if ( prose_get_fresh_design_option( 'db_version' ) >= CHILD_DB_VERSION )
		return;

	###########################
	# UPDATE DB TO VERSION 1500
	###########################
	if ( prose_get_fresh_design_option( 'db_version' ) < '1500' )
		prose_update_1500();

	###########################
	# UPDATE DB TO VERSION 1502
	###########################
	if ( prose_get_fresh_design_option( 'db_version' ) < '1501' )
		prose_update_1501();
		
	###########################
	# UPDATE DB TO VERSION 1502
	###########################
	if ( prose_get_fresh_design_option( 'db_version' ) < '1502' )
		prose_update_1502();

	do_action( 'prose_update' );

}

add_action( 'prose_update', 'prose_update_redirect' );
/**
 * Redirects the user back to the design settings page, refreshing the data and
 * notifying the user that they have successfully updated.
 *
 * @since 1.5.0
 *
 * @uses genesis_admin_redirect()
 *
 * @return null Returns early if not an admin page.
 */
function prose_update_redirect() {

	if ( ! is_admin() )
		return;

	genesis_admin_redirect( 'design-settings', array( 'upgraded' => 'true' ) );
	exit;

}

add_action( 'admin_notices', 'prose_updated_notice' );
/**
 * Displays the notice that the design settings were successfully updated to the
 * latest version.
 *
 * @since 1.5.0
 *
 * @uses prose_get_design_option()
 *
 * @return null Returns early if not on the Design Settings page.
 */
function prose_updated_notice() {

	if ( ! genesis_is_menu_page( 'design-settings' ) )
		return;

	if ( isset( $_REQUEST['upgraded'] ) && 'true' == $_REQUEST['upgraded'] )
		echo '<div id="message" class="updated highlight" id="message"><p><strong>' . sprintf( __( 'Congratulations! You are now rocking Prose %s', 'prose' ), prose_get_design_option( 'theme_version' ) ) . '</strong></p></div>';

}

add_filter( 'update_theme_complete_actions', 'prose_update_action_links', 10, 2 );
/**
 * Filters the action links at the end of an update.
 *
 * This function filters the action links that are presented to the
 * user at the end of a theme update. If the theme being updated is
 * not Prose, the filter returns the default values. Otherwise,
 * it will provide a link to the Prose Design Settings page, which
 * will trigger the database/settings upgrade.
 *
 * @since 1.5.0
 *
 * @param array $actions Existing array of action links
 * @param string $theme Theme name
 * @return string Removes all existing action links in favor of a single link.
 */
function prose_update_action_links( $actions, $theme ) {

	if ( 'prose' != $theme )
		return $actions;

	return sprintf( '<a href="%s">%s</a>', menu_page_url( 'design-settings', 0 ), __( 'Click here to complete the upgrade', 'prose' ) );

}

add_filter( 'site_transient_update_themes', 'prose_update_push' );
add_filter( 'transient_update_themes', 'prose_update_push' );
/**
 * Integrate the Prose update check into the WordPress update checks.
 *
 * This function filters the value that is returned when WordPress tries to pull
 * theme update transient data.
 *
 * It uses prose_update_check() to check to see if we need to do an update,
 * and if so, adds the proper array to the $value->response object. WordPress
 * handles the rest.
 *
 * @since 1.5.0
 *
 * @uses prose_update_check()
 *
 * @param object $value
 * @return object
 */
function prose_update_push( $value ) {

	$prose_update = prose_update_check();

	if ( $prose_update )
		$value->response['prose'] = $prose_update;

	return $value;

}

add_action( 'load-update-core.php', 'prose_clear_update_transient' );
/**
 * Delete Prose update transient after updates or when viewing the Dashboard -> Updates page.
 *
 * The server will then do a fresh version check.
 *
 * @since 1.5.0
 *
 */
function prose_clear_update_transient() {

	delete_transient( 'prose-update' );

}