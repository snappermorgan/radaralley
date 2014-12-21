<?php
/**
 * documentation.php
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
 *
 * Plugin Name: Documentation
 * Plugin URI: http://www.itthinx.com/plugins/documentation
 * Description: A documentation management system.
 * Version: 1.3.1
 * Author: itthinx
 * Author URI: http://www.itthinx.com
 * Donate-Link: http://www.itthinx.com
 * License: GPLv3
 */
define( 'DOCUMENTATION_CORE_VERSION', '1.3.1' );
define( 'DOCUMENTATION_PLUGIN_FILE', __FILE__ );
define( 'DOCUMENTATION_PLUGIN_DOMAIN', 'documentation' );
define( 'DOCUMENTATION_PLUGIN_URL', plugin_dir_url( DOCUMENTATION_PLUGIN_FILE ) );
if ( !defined( 'DOCUMENTATION_CORE_DIR' ) ) {
	define( 'DOCUMENTATION_CORE_DIR', WP_PLUGIN_DIR . '/documentation' );
}
if ( !defined( 'DOCUMENTATION_CORE_LIB' ) ) {
	define( 'DOCUMENTATION_CORE_LIB', DOCUMENTATION_CORE_DIR . '/lib/core' );
}
if ( !defined( 'DOCUMENTATION_ADMIN_LIB' ) ) {
	define( 'DOCUMENTATION_ADMIN_LIB', DOCUMENTATION_CORE_DIR . '/lib/admin' );
}
if ( !defined( 'DOCUMENTATION_VIEWS_LIB' ) ) {
	define( 'DOCUMENTATION_VIEWS_LIB', DOCUMENTATION_CORE_DIR . '/lib/views' );
}
if ( !defined( 'DOCUMENTATION_EXT_LIB' ) ) {
	define( 'DOCUMENTATION_EXT_LIB', DOCUMENTATION_CORE_DIR . '/lib/ext' );
}
if ( !defined( 'DOCUMENTATION_CORE_URL' ) ) {
	define( 'DOCUMENTATION_CORE_URL', WP_PLUGIN_URL . '/documentation' );
}
require_once( DOCUMENTATION_CORE_LIB . '/boot.php');
