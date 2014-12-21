<?php
/**
 * class-documentation-post-type.php
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

/**
 * Topic post type.
 */
class Documentation_Post_Type {

	/**
	 * Sets up the init hook.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
		//add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 2 );
		add_filter( 'comments_open', array( __CLASS__, 'comments_open' ), 10, 2 );
		//add_action( 'comment_form_comments_closed', array( __CLASS__, 'comment_form_comments_closed' ) );
	}

	/**
	 * Invoke registration.
	 */
	public static function wp_init() {
		self::post_type();
	}

	/**
	 * Register the documentation post type.
	 */
	public static function post_type() {

		$options = Documentation::get_options();
		$document_comments_open = isset( $options['document_comments_open'] ) ? $options['document_comments_open'] : true;
		$document_slug = isset( $options['document_slug'] ) ? $options['document_slug'] : '';

		$supports = array(
			'author',
			'editor',
			'page-attributes',
			'revisions',
			'title',
			'thumbnail'
		);

		if ( $document_comments_open ) {
			$supports[] = 'comments';
		}

		register_post_type(
			'document',
			array(
				'labels' => array(
					'name'               => __( 'Documents', DOCUMENTATION_PLUGIN_DOMAIN ),
					'singular_name'      => __( 'Document', DOCUMENTATION_PLUGIN_DOMAIN ),
					'all_items'          => __( 'All Documents', DOCUMENTATION_PLUGIN_DOMAIN ),
					'add_new'            => __( 'New Document', DOCUMENTATION_PLUGIN_DOMAIN ),
					'add_new_item'       => __( 'Add New Document', DOCUMENTATION_PLUGIN_DOMAIN ),
					'edit'               => __( 'Edit', DOCUMENTATION_PLUGIN_DOMAIN ),
					'edit_item'          => __( 'Edit Document', DOCUMENTATION_PLUGIN_DOMAIN ),
					'new_item'           => __( 'New Document', DOCUMENTATION_PLUGIN_DOMAIN ),
					'not_found'          => __( 'No Documents found', DOCUMENTATION_PLUGIN_DOMAIN ),
					'not_found_in_trash' => __( 'No Documents found in trash', DOCUMENTATION_PLUGIN_DOMAIN ),
					'parent'             => __( 'Parent Document', DOCUMENTATION_PLUGIN_DOMAIN ),
					'search_items'       => __( 'Search Documents', DOCUMENTATION_PLUGIN_DOMAIN ),
					'view'               => __( 'View Document', DOCUMENTATION_PLUGIN_DOMAIN ),
					'view_item'          => __( 'View Document', DOCUMENTATION_PLUGIN_DOMAIN )
				),
// 				'capability_type'     => 'document', // @todo if used we need to assign them appropriately so at least admins have them, or use roles/groups
				'description'         => __( 'Document', DOCUMENTATION_PLUGIN_DOMAIN ),
				'exclude_from_search' => false, // this option is unreliable, see http://core.trac.wordpress.org/ticket/17592
				'has_archive'         => true,
				'hierarchical'        => true,
				'map_meta_cap'        => true,
// 				'menu_position'       => 10,
				'menu_icon'           => DOCUMENTATION_PLUGIN_URL . '/images/documentation.png',
				'public'              => true,
				'publicly_queryable'  => true,
				'query_var'           => true,
				'rewrite'             => empty( $document_slug ) ? true : array( 'slug' => $document_slug ),
				'show_in_nav_menus'   => true,
				'show_ui'             => true,
				'supports'            => $supports,
				'taxonomies' => array( 'document_category', 'document_tag' )
			)
		);
	}

	/**
	 * Returns true if the current or the indicated post is a document.
	 * @return boolean
	 */
	public static function is_document( $post = null ) {
		global $wp_query;
		$result = false;
		if ( $post === null ) {
			$queried_object = $wp_query->get_queried_object();
			if ( $queried_object instanceof WP_Post ) {
				if ( $queried_object->post_type == 'document' ) {
					$result = true;
				}
			}
		} else {
			$result = 'document' == get_post_type( $post );
		}
		return $result;
	}

	/**
	 * Process data for post being saved.
	 * Currently not used.
	 * @param int $post_id
	 * @param object $post
	 */
	public static function save_post( $post_id = null, $post = null) {
		if ( ! ( ( defined( "DOING_AUTOSAVE" ) && DOING_AUTOSAVE ) ) ) {
			if ( $post->post_type == 'topic' ) {

// 				$foo = isset( $_POST['foo'] ) ? check_foo( $_POST['foo'] : null;
// 				update_post_meta( $post_id, '_foo', $foo );

			}
		}
	}

	/**
	 * Determine whether comments are open.
	 * 
	 * @param boolean $open
	 * @param int $post_id
	 * @return boolean
	 */
	public static function comments_open( $open, $post_id ) {
		if ( self::is_document( $post_id ) ) {
			$options = Documentation::get_options();
			$open = $open && ( isset( $options['document_comments_open'] ) ? $options['document_comments_open'] : true );
		}
		return $open;
	}

	/**
	 * Triggered when comments are closed.
	 * Currently not used.
	 */
	public static function comment_form_comments_closed() {
	}
}
Documentation_Post_Type::init();
