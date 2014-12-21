<?php
/**
 * OptinMonster is the #1 lead generation and email list building tool.
 *
 * @package   OptinMonster
 * @author    Thomas Griffin
 * @license   GPL-2.0+
 * @link      http://optinmonster.com/
 * @copyright 2013 Retyp, LLC. All rights reserved.
 *
 * @wordpress-plugin
 * Plugin Name:  OptinMonster
 * Plugin URI:   http://optinmonster.com/
 * Description:  OptinMonster is the #1 lead generation and email list building tool.
 * Version:      1.0.8
 * Author:       Thomas Griffin
 * Author URI:   http://thomasgriffinmedia.com/
 * Text Domain:  optin-monster
 * Contributors: griffinjt
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:  /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;

// Load the main plugin class and widget class.
require_once( plugin_dir_path( __FILE__ ) . 'class-optin-monster.php' );

// Register hooks for activation, deactivation and uninstall instances.
register_activation_hook( 	__FILE__, array( 'optin_monster', 'activate'   ) );
register_deactivation_hook( __FILE__, array( 'optin_monster', 'deactivate' ) );
register_uninstall_hook( 	__FILE__, array( 'optin_monster', 'uninstall'  ) );

// Initialize the plugin.
global $optin_monster;
$optin_monster = optin_monster::get_instance();