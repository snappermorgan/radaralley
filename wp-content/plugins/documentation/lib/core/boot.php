<?php
/**
 * boot.php
 *
 * Copyright (c) 2013 "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 * 
 * @author Karim Rahimpur
 * @package documentation
 * @since documentation 1.0.0
 */
class Documentation_Controller {

	public static $admin_messages = array();

	/**
	 * Boots the plugin.
	 */
	public static function boot() {
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		load_plugin_textdomain( DOCUMENTATION_PLUGIN_DOMAIN, null, 'documentation/languages' );
		require_once( DOCUMENTATION_CORE_LIB . '/class-documentation.php' );
		require_once( DOCUMENTATION_EXT_LIB . '/class-documentation-post-type.php' );
		require_once( DOCUMENTATION_EXT_LIB . '/class-documentation-taxonomy.php' );
		if ( !is_admin() ) {
			require_once( DOCUMENTATION_VIEWS_LIB . '/class-documentation-shortcodes.php' );
		} else {
			require_once( DOCUMENTATION_ADMIN_LIB . '/class-documentation-settings.php' );
		}
		require_once( DOCUMENTATION_VIEWS_LIB . '/class-documentation-documents-widget.php' );
		require_once( DOCUMENTATION_VIEWS_LIB . '/class-documentation-document-children-widget.php' );
		require_once( DOCUMENTATION_VIEWS_LIB . '/class-documentation-document-hierarchy-widget.php' );
		require_once( DOCUMENTATION_CORE_LIB . '/class-documentation-search.php' );
	}

	/**
	 * Prints admin notices.
	 */
	public static function admin_notices() {
		if ( !empty( self::$admin_messages ) ) {
			foreach ( self::$admin_messages as $msg ) {
				echo $msg;
			}
		}
	}
}
Documentation_Controller::boot();
