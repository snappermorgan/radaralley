<?php
/**
 * class-documentation-shortcodes.php
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
 * Shortcode initialization.
 */
class Documentation_Shortcodes {

	/**
	 * Registers shortcode handlers.
	 */
	public static function init() {
		add_shortcode( 'documentation_documents', array( __CLASS__, 'documentation_documents' ) );
		add_shortcode( 'documentation_list_children', array( __CLASS__, 'documentation_list_children' ) );
		add_shortcode( 'documentation_hierarchy', array( __CLASS__, 'documentation_hierarchy' ) );
	}

	/**
	 * 
	 * @param array $atts
	 * @param string $content
	 */
	public static function documentation_documents( $atts, $content = null ) {
		require_once DOCUMENTATION_VIEWS_LIB . '/class-documentation-renderer.php';
		return Documentation_Renderer::documents( $atts );
	}

	/**
	 * List children shortcode.
	 * 
	 * @param array $atts
	 * @param string $content (not used)
	 * @return string
	 */
	public static function documentation_list_children( $atts, $content = null ) {
		require_once DOCUMENTATION_VIEWS_LIB . '/class-documentation-renderer.php';
		return Documentation_Renderer::list_children( $atts );
	}

	/**
	 * Shortcode handler that produces a documentation hierarchy.
	 * 
	 * The following options are accepted through $atts:
	 * 
	 * - root_depth : number of levels to include from the root level, defaults to 1 including all documents at root level (without parents); set to 0 to hide all documents at root level except the parent of the current document
	 * - supernode_height : number of levels to include above the current document, defaults to 1
	 * - supernode_subnode_depth : number of levels to include below each supernode, defaults to 1
	 * - subnode_depth : number of levels to include below the current document, defaults to 1
	 * 
	 * @see Documentation_Renderer::document_hierarchy()
	 * 
	 * @param array $atts
	 * @param string $content (not used)
	 * @return string
	 */
	public static function documentation_hierarchy( $atts, $content = null ) {
		require_once DOCUMENTATION_VIEWS_LIB . '/class-documentation-renderer.php';
		return Documentation_Renderer::document_hierarchy( $atts );
	}
}
Documentation_Shortcodes::init();
