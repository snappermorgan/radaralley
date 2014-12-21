<?php
/**
 * Post type class.
 *
 * @package      OptinMonster
 * @since        1.0.0
 * @author       Thomas Griffin <thomas@retyp.com>
 * @copyright    Copyright (c) 2013, Thomas Griffin
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Loads post types.
 *
 * @package      OptinMonster
 * @since        1.0.0
 */
class optin_monster_post_type {

	/**
	 * Prepare any base class properties.
	 *
	 * @since 1.0.0
	 */
	public $base;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

        // Bring base class into scope.
        $this->base = optin_monster::get_instance();

        // Run WordPress API.
		add_action( 'init', array( $this, 'post_type' ), 0 );

		// Add support for thumbnails.
		add_theme_support( 'post-thumbnails' );

    }

	/**
	 * Registers the optin custom post type for the theme.
	 *
	 * @since 1.0.0
	 */
	public function post_type() {

		// Set post type labels.
		$labels = array(
			'name' 					=> 'Optins',
			'singular_name' 		=> 'Optin',
			'add_new' 				=> 'Add New',
			'add_new_item' 			=> 'Add New Optin',
			'edit_item' 			=> 'Edit Optin',
			'new_item' 				=> 'New Optin',
			'view_item' 			=> 'View Optin',
			'search_items' 			=> 'Search Optins',
			'not_found' 			=> 'No Optins found',
			'not_found_in_trash' 	=> 'No Optins found in trash',
			'parent_item_colon' 	=> '',
			'menu_name' 			=> 'Optins'
		);

		// Set post types args.
		$args = array(
			'labels' 				=> $labels,
			'public' 				=> false,
			'show_in_nav_menus'		=> false,
			'show_in_admin_bar'		=> false,
			'show_ui' 				=> false,
			'rewrite' 				=> false,
			'has_archive'			=> false,
			'capability_type' 		=> 'post',
			'hierarchical' 			=> false,
			'show_in_menu'          => false,
			'supports' 				=> array( 'title', 'author', 'custom-fields', 'thumbnail' )
		);

		// Register post type with args.
		register_post_type( 'optin', $args );

	}

}

// Initialize the class.
$optin_monster_post_type = new optin_monster_post_type();