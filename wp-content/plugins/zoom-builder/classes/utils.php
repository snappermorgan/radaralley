<?php defined('ABSPATH') or die; # Don't ever load this file directly

/**
 * ZOOM_Builder_Utils Class
 *
 * Various utility functions used throughout the plugin.
 *
 * @package ZOOM_Builder
 * @subpackage Utilities
 */

class ZOOM_Builder_Utils {

	/**
	 * Try to set the global $typenow variable if it is not already set
	 */
	public static function fix_typenow() {

		global $pagenow, $typenow;

		if ( is_null( $typenow ) || empty( $typenow ) ) {

			if ( isset( $_GET['post'] ) && null !== ( $post = get_post( intval( $_GET['post'] ) ) ) ) {
				$typenow = $post->post_type;
			} elseif ( isset( $_GET['post_type'] ) && post_type_exists( $_GET['post_type'] ) ) {
				$typenow = $_GET['post_type'];
			} elseif ( $pagenow == 'post-new.php' && !isset( $_GET['post_type'] ) ) {
				$typenow = 'post';
			}

		}

	}

	/**
	 * Returns whether the current admin screen is a Add/Edit Post screen and has the builder enabled for that post type
	 */
	public static function screen_is_builder() {

		global $pagenow, $typenow;

		self::fix_typenow(); # Sometimes the global $typenow variable isn't set early enough for our needs, so we try to set it ourselves

		return ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) && in_array( $typenow, ZOOM_Builder::$post_types );

	}

	/**
	 * Gets an array of widget IDs that are only to be shown in the builder based on if they have the `wpzlb_widget` value present
	 */
	public static function get_builder_widgets() {

		global $wp_registered_widgets;

		$done = array();

		foreach ( $wp_registered_widgets as $widget ) {

			if ( !isset( $widget['wpzlb_widget'] ) ) continue;

			$widget_id = preg_replace( array( '/^wpzoom-/i', '/-[0-9]+$/' ), '', $widget['id'] );

			if ( !in_array( $widget_id, $done ) ) $done[] = $widget_id;

		}

		return $done;

	}

	/**
	 * Takes a "layout array" and makes sure it conforms to what we expect
	 */
	public static function sanitize_layout_array( $layout_in, $with_widget_settings = false ) {

		if ( !is_array( $layout_in ) || empty( $layout_in ) ) $layout_in = array( array() );

		$layout = array();

		foreach ( $layout_in as $group_index => $group ) {

			$layout[$group_index]['groupname'] = isset( $group['groupname'] ) && !empty( $group['groupname'] ) ? sanitize_text_field( trim( $group['groupname'] ) ) : '';

			if ( !isset( $group['settings'] ) || !is_array( $group['settings'] ) ) $group['settings'] = array();
			$layout[$group_index]['settings'] = self::get_valid_group_settings( $group['settings'] );

			if ( !isset( $group['rows'] ) || !is_array( $group['rows'] ) ) $group['rows'] = array( array( 'type' => 1, 'columns' => array() ) );

			foreach ( $group['rows'] as $row_index => $row ) {

				if ( isset( $row['type'] ) && !empty( $row['type'] ) ) {
					switch ( $row['type'] ) {
						case 'one':
							$type = 1;
							break;

						case 'two':
							$type = 2;
							break;

						case 'three':
							$type = 3;
							break;

						case 'divider':
							$type = 'divider';
							break;

						default:
							$typeint = absint( $row['type'] );
							$type = max( 1, min( $typeint, ZOOM_Builder::$max_columns ) );
							break;
					}
				} else {
					$type = 1;
				}
				$layout[$group_index]['rows'][$row_index]['type'] = $type;
				if ( $type == 'divider' ) continue;

				$cols = isset( $row['columns'] ) && is_array( $row['columns'] ) && !empty( $row['columns'] ) ? array_values( $row['columns'] ) : array();

				for ( $col_index = 0; $col_index < min( $type, ZOOM_Builder::$max_columns ); $col_index++ ) {

					$col = isset( $cols[$col_index] ) ? $cols[$col_index] : array();

					if ( isset( $col['width'] ) && 0 < ( $width = round( floatval( $col['width'] ), 1 ) ) ) $layout[$group_index]['rows'][$row_index]['columns'][$col_index]['width'] = (string)$width;

					$layout[$group_index]['rows'][$row_index]['columns'][$col_index]['widgets'] = array();

					if ( $with_widget_settings === true ) {

						$widgets = isset( $col['widgets'] ) && is_array( $col['widgets'] ) ? $col['widgets'] : array();

						foreach ( $widgets as $widget ) {#$layout[$group_index]['rows'][$row_index]['columns'][$col_index]['widgets'][]=$widget;continue;
							if ( isset( $widget['id'] ) && isset( $widget['class'] ) && isset( $widget['settings'] ) && is_array( $widget['settings'] ) ) {
								$layout[$group_index]['rows'][$row_index]['columns'][$col_index]['widgets'][] = array( 'id' => (string)$widget['id'], 'class' => (string)$widget['class'], 'settings' => $widget['settings'] );
							}
						}

					} else {

						$widgets = isset( $col['widgets'] ) && is_array( $col['widgets'] ) ? array_filter( $col['widgets'], create_function( '$a', 'return is_string($a) && !empty($a);' ) ) : array();
						$layout[$group_index]['rows'][$row_index]['columns'][$col_index]['widgets'] = is_array( $widgets ) && !empty( $widgets ) ? array_values( $widgets ) : array();

					}

				}

			}

		}

		return $layout;

	}

	/**
	 * Helper functions for sanitize_layout_array() above
	 */
	# Checks if the passed value looks like a valid CSS hex color code
	public static function is_valid_css_hex_color( $val ) { return preg_match( '/^#[a-f0-9]{6}$/i', trim( (string)$val ) ); }

	# Checks if the passed value looks like a valid image URL
	public static function is_valid_image_url( $val ) { return '' != ( $url = trim( (string)$val ) ) && filter_var( $url, FILTER_VALIDATE_URL ) !== false && false !== ( $path = parse_url( $url, PHP_URL_PATH ) ) && in_array( pathinfo( $path, PATHINFO_EXTENSION ), array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' ) ); }

	# Checks if the passed value looks like a valid image position string (left, center, right)
	public static function is_valid_image_position( $val ) { return in_array( strtolower( trim( (string)$val ) ), array( 'left', 'center', 'right' ) ); }

	# Checks if the passed value looks like a valid image repeat string (No Repeat, Tile, Tile Horizontally, Tile Vertically)
	public static function is_valid_image_repeat( $val ) { return in_array( strtolower( trim( (string)$val ) ), array( 'norepeat', 'tile', 'tileh', 'tilev' ) ); }

	# Checks if the passed value looks like a valid image attachment string (scroll, fixed)
	public static function is_valid_image_attachment( $val ) { return in_array( strtolower( trim( (string)$val ) ), array( 'scroll', 'fixed' ) ); }

	# Checks if the passed value looks like a valid CSS border style (solid, dotted, dashed, double, etc.)
	public static function is_valid_css_border_style( $val ) { return in_array( strtolower( trim( (string)$val ) ), array( 'none', 'solid', 'dotted', 'dashed', 'double', 'groove', 'ridge', 'inset', 'outset' ) ); }

	# Takes an array of group settings and returns an array containing only the valid values
	public static function get_valid_group_settings( $settings ) {

		$clean_settings = array();

		if ( isset( $settings['font'] ) && self::is_valid_css_hex_color( $settings['font'] ) )
			$clean_settings['font'] = trim( (string)$settings['font'] );

		if ( isset( $settings['background'] ) && is_array( $settings['background'] ) && !empty( $settings['background'] ) ) {
			$background = $settings['background'];
			if ( isset( $background['color'] ) && self::is_valid_css_hex_color( $background['color'] ) ) $clean_settings['background']['color'] = trim( (string)$background['color'] );
			if ( isset( $background['image'] ) && is_array( $background['image'] ) && !empty( $background['image'] ) ) {
				$bgimg = $background['image'];
				if ( isset( $bgimg['id'] ) && intval( $bgimg['id'] ) > 0 && isset( $bgimg['url'] ) && self::is_valid_image_url( $bgimg['url'] ) ) $clean_settings['background']['image']['id'] = intval( $bgimg['id'] );
				if ( isset( $bgimg['url'] ) && self::is_valid_image_url( $bgimg['url'] ) ) $clean_settings['background']['image']['url'] = trim( (string)$bgimg['url'] );
				if ( isset( $bgimg['position'] ) && self::is_valid_image_position( $bgimg['position'] ) ) $clean_settings['background']['image']['position'] = trim( (string)$bgimg['position'] );
				if ( isset( $bgimg['repeat'] ) && self::is_valid_image_repeat( $bgimg['repeat'] ) ) $clean_settings['background']['image']['repeat'] = trim( (string)$bgimg['repeat'] );
				if ( isset( $bgimg['attachment'] ) && self::is_valid_image_attachment( $bgimg['attachment'] ) ) $clean_settings['background']['image']['attachment'] = trim( (string)$bgimg['attachment'] );
			}
		}

		if ( isset( $settings['padding'] ) && is_array( $settings['padding'] ) && !empty( $settings['padding'] ) ) {
			$padding = $settings['padding'];
			if ( isset( $padding['top'] ) && 0 <= ( $pt = intval( $padding['top'] ) ) ) $clean_settings['padding']['top'] = $pt;
			if ( isset( $padding['left'] ) && 0 <= ( $pl = intval( $padding['left'] ) ) ) $clean_settings['padding']['left'] = $pl;
			if ( isset( $padding['right'] ) && 0 <= ( $pr = intval( $padding['right'] ) ) ) $clean_settings['padding']['right'] = $pr;
			if ( isset( $padding['bottom'] ) && 0 <= ( $pb = intval( $padding['bottom'] ) ) ) $clean_settings['padding']['bottom'] = $pb;
		}

		if ( isset( $settings['border'] ) && is_array( $settings['border'] ) && !empty( $settings['border'] ) ) {
			$border = $settings['border'];

			if ( isset( $border['top'] ) && is_array( $border['top'] ) && !empty( $border['top'] ) ) {
				$border_top = $border['top'];
				if ( isset( $border_top['width'] ) && 0 <= ( $bwt = intval( $border_top['width'] ) ) ) $clean_settings['border']['top']['width'] = $bwt;
				if ( isset( $border_top['color'] ) && self::is_valid_css_hex_color( $border_top['color'] ) ) $clean_settings['border']['top']['color'] = trim( (string)$border_top['color'] );
				if ( isset( $border_top['style'] ) && self::is_valid_css_border_style( $border_top['style'] ) ) $clean_settings['border']['top']['style'] = trim( (string)$border_top['style'] );
				if ( isset( $border_top['radius'] ) && 0 <= ( $brt = intval( $border_top['radius'] ) ) ) $clean_settings['border']['top']['radius'] = $brt;
			}

			if ( isset( $border['left'] ) && is_array( $border['left'] ) && !empty( $border['left'] ) ) {
				$border_left = $border['left'];
				if ( isset( $border_left['width'] ) && 0 <= ( $bwl = intval( $border_left['width'] ) ) ) $clean_settings['border']['left']['width'] = $bwl;
				if ( isset( $border_left['color'] ) && self::is_valid_css_hex_color( $border_left['color'] ) ) $clean_settings['border']['left']['color'] = trim( (string)$border_left['color'] );
				if ( isset( $border_left['style'] ) && self::is_valid_css_border_style( $border_left['style'] ) ) $clean_settings['border']['left']['style'] = trim( (string)$border_left['style'] );
				if ( isset( $border_left['radius'] ) && 0 <= ( $brl = intval( $border_left['radius'] ) ) ) $clean_settings['border']['left']['radius'] = $brl;
			}

			if ( isset( $border['right'] ) && is_array( $border['right'] ) && !empty( $border['right'] ) ) {
				$border_right = $border['right'];
				if ( isset( $border_right['width'] ) && 0 <= ( $bwr = intval( $border_right['width'] ) ) ) $clean_settings['border']['right']['width'] = $bwr;
				if ( isset( $border_right['color'] ) && self::is_valid_css_hex_color( $border_right['color'] ) ) $clean_settings['border']['right']['color'] = trim( (string)$border_right['color'] );
				if ( isset( $border_right['style'] ) && self::is_valid_css_border_style( $border_right['style'] ) ) $clean_settings['border']['right']['style'] = trim( (string)$border_right['style'] );
				if ( isset( $border_right['radius'] ) && 0 <= ( $brr = intval( $border_right['radius'] ) ) ) $clean_settings['border']['right']['radius'] = $brr;
			}

			if ( isset( $border['bottom'] ) && is_array( $border['bottom'] ) && !empty( $border['bottom'] ) ) {
				$border_bottom = $border['bottom'];
				if ( isset( $border_bottom['width'] ) && 0 <= ( $bwb = intval( $border_bottom['width'] ) ) ) $clean_settings['border']['bottom']['width'] = $bwb;
				if ( isset( $border_bottom['color'] ) && self::is_valid_css_hex_color( $border_bottom['color'] ) ) $clean_settings['border']['bottom']['color'] = trim( (string)$border_bottom['color'] );
				if ( isset( $border_bottom['style'] ) && self::is_valid_css_border_style( $border_bottom['style'] ) ) $clean_settings['border']['bottom']['style'] = trim( (string)$border_bottom['style'] );
				if ( isset( $border_bottom['radius'] ) && 0 <= ( $brb = intval( $border_bottom['radius'] ) ) ) $clean_settings['border']['bottom']['radius'] = $brb;
			}
		}

		if ( isset( $settings['margin'] ) && is_array( $settings['margin'] ) && !empty( $settings['margin'] ) ) {
			$margin = $settings['margin'];
			if ( isset( $margin['top'] ) && 0 <= ( $mt = intval( $margin['top'] ) ) ) $clean_settings['margin']['top'] = $mt;
			if ( isset( $margin['left'] ) && 0 <= ( $ml = intval( $margin['left'] ) ) ) $clean_settings['margin']['left'] = $ml;
			if ( isset( $margin['right'] ) && 0 <= ( $mr = intval( $margin['right'] ) ) ) $clean_settings['margin']['right'] = $mr;
			if ( isset( $margin['bottom'] ) && 0 <= ( $mb = intval( $margin['bottom'] ) ) ) $clean_settings['margin']['bottom'] = $mb;
		}

		return $clean_settings;

	}

	/**
	 * Takes a widget ID and returns whether it is a WPZOOM widget or not
	 */
	public static function is_wpzoom_widget( $widget_id ) {

		return stripos( $widget_id, 'wpzoom' ) !== false && !self::is_wpzoom_builder_widget( $widget_id );

	}

	/**
	 * Takes a widget ID and returns whether it is a WPZOOM layout builder widget or not
	 */
	public static function is_wpzoom_builder_widget( $widget_id ) {

		foreach ( ZOOM_Builder::$builder_widgets as $builder_widget )
			if ( stripos( $widget_id, 'wpzoom-' . $builder_widget ) !== false ) return true;

		return false;

	}

	/**
	 * Takes a widget ID and a specific HTML string containing a class="" attribute and inserts special WPZOOM classes if needed
	 * Used in list_widgets() and list_widget_controls()
	 */
	public static function insert_wpzoom_classes( $widget_id, $html ) {

		if ( self::is_wpzoom_widget( $widget_id ) ) $html = str_ireplace( "class='", "class='wpzoom-widget wpzoom-widget-" . preg_replace( array( '/^wpzoom-/i', '/-[0-9]+$/i' ), '', $widget_id ) . " ", $html );
		if ( self::is_wpzoom_builder_widget( $widget_id ) ) $html = str_ireplace( "class='", "class='wpzoom-builder-widget wpzoom-builder-widget-" . preg_replace( array( '/^wpzoom-/i', '/-[0-9]+$/i' ), '', $widget_id ) . " ", $html );

		return $html;

	}

	/**
	 * Takes an array and makes sure every item is a valid module
	 */
	public static function sanitize_modules_array( $array ) {

		$all_modules = array();
		foreach ( glob( ZOOM_Builder::$modules_path . '/*', GLOB_ONLYDIR ) as $dir ) {

			$basename = basename( $dir );
			$file = sprintf( '%s/%s.php', $dir, $basename );

			if ( is_readable( $file ) ) $all_modules[] = $basename;

		}

		if ( $array === false ) return $all_modules;

		$new_array = array();

		foreach ( (array)$array as $module )
			if ( in_array( $module, $all_modules ) ) $new_array[] = $module;

		return $new_array;

	}

	/**
	 * Takes an array and makes sure every item is a valid registered post type
	 */
	public static function sanitize_post_types_array( $array ) {

		if ( $array === false ) return array( 'post', 'page' );

		$new_array = array();

		foreach ( (array)$array as $item )
			if ( !empty( $item ) && $item != 'attachment' && '' != ( $post_type = sanitize_key( trim( (string)$item ) ) ) ) $new_array[] = $post_type;

		return $new_array;

	}

	/**
	 * Takes a string and makes sure it is a valid layout position value (above, below, replace)
	 */
	public static function sanitize_layout_position( $value ) {

		$value = strtolower( trim( (string)$value ) );

		return !empty( $value ) && in_array( $value, array( 'above', 'below', 'replace' ) ) ? $value : 'below';

	}

	/**
	 * Takes an array and makes sure every item is a valid registered widget
	 */
	public static function sanitize_widget_names_array( $array ) {

		global $wp_registered_widgets;

		$widgets = array();
		foreach ( $wp_registered_widgets as $widget ) {
			$id = preg_replace( '/^(.+)-[0-9]+$/i', '$1', $widget['id'] );
			if ( in_array( $id, $widgets, true ) ) continue;
			$widgets[] = $id;
		}

		$new_array = array();
		foreach ( (array)$array as $item )
			if ( in_array( $item, $widgets ) ) $new_array[] = $item;

		return $new_array;

	}

	/**
	 * Takes an array and makes sure every item is a valid saved layout ID
	 */
	public static function sanitize_saved_layouts_ids_array( $array ) {

		$saved_layouts = ZOOM_Builder::$saved_layouts;
		$new_array = array();

		foreach ( (array)$array as $id )
			if ( array_key_exists( $id, $saved_layouts ) ) $new_array[] = $id;

		return $new_array;

	}

	/**
	 * Takes an array and makes sure every item is a valid saved layout
	 */
	public static function sanitize_saved_layouts_array( $array ) {

		if ( !is_array( $array ) || empty( $array ) ) return array();

		$new_array = array();

		foreach ( (array)$array as $item ) {
			if ( isset( $item['name'] ) && !empty( $item['name'] ) && isset( $item['layout'] ) && is_array( $item['layout'] ) && !empty( $item['layout'] ) ) {
				$new_array[ sanitize_title( trim( $item['name'] ) ) ] = array( 'name' => sanitize_text_field( trim( $item['name'] ) ), 'layout' => self::sanitize_layout_array( $item['layout'], true ) );
			}
		}

		ksort( $new_array );

		return $new_array;

	}

	/**
	 * Returns an array of saved layout names
	 */
	public static function get_saved_layouts_names() {

		$saved_layouts = self::filter_excluded_layouts( ZOOM_Builder::$saved_layouts );
		$output = array();

		ksort( $saved_layouts );

		foreach ( $saved_layouts as $id => $item )
			$output[] = array( 'id' => $id, 'label' => $item['name'], 'value' => $item['name'] );

		return $output;

	}

	/**
	 * Returns an array of predefined layouts if there are any
	 */
	public static function get_predefined_layouts() {

		$output = array();

		if ( empty( ZOOM_Builder::$predefined_layouts_paths ) ) return $output;

		foreach ( (array)ZOOM_Builder::$predefined_layouts_paths as $path ) {

			$files = glob( $path . '/*.json' );

			if ( $files === false || empty( $files ) ) continue;

			foreach ( $files as $file ) {

				if ( @is_readable( $file ) && false !== ( $contents = @file_get_contents( $file ) ) && !empty( $contents ) &&
				     !is_null( $decoded = json_decode( $contents, true ) ) && isset( $decoded['name'] ) && isset( $decoded['layout'] ) ) {

					$output[ sanitize_title( trim( $decoded['name'] ) ) ] = array( 'name' => $decoded['name'], 'layout' => self::sanitize_layout_array( $decoded['layout'], true ) );

				}

			}

		}

		if ( !empty( $output ) ) uasort( $output, array( __CLASS__, 'sort_by_name' ) );

		return $output;

	}

	/**
	 * Used with the usort() PHP function to sort an array by a 'name' sub-key
	 */
	public static function sort_by_name( $a, $b ) {

		return strcmp( $a['name'], $b['name'] );

	}

	/**
	 * Takes a layout array and updates any widget IDs contained in it using an array of old ids => new ids from the second parameter
	 */
	public static function update_widget_ids( $layout, $ids ) {

		if ( empty( $layout ) || !is_array( $layout ) ) return array();
		if ( empty( $ids ) || !is_array( $ids ) ) return $layout;

		foreach ( $layout as $ig => $group ) {

			foreach ( $group['rows'] as $ir => $row ) {

				if ( $row['type'] == 'divider' ) continue;

				foreach ( $row['columns'] as $ic => $column ) {

					foreach ( $column['widgets'] as $iw => $widget ) {

						if ( isset( $ids[ $widget['id'] ] ) )
							$layout[$ig]['rows'][$ir]['columns'][$ic]['widgets'][$iw]['id'] = $ids[ $widget['id'] ];

					}

				}

			}

		}

		return $layout;

	}

	/**
	 * Filters out saved layouts that have been excluded in the settings
	 */
	public static function filter_excluded_layouts( $array ) {

		foreach ( $array as $id => $saved_layout )
			if ( in_array( $id, ZOOM_Builder::$exclude_layouts ) ) unset( $array[ $id ] );

		return $array;

	}

	/**
	 * Returns whether the given widget id is in the excluded widget list
	 */
	public static function is_excluded( $widget_id ) {

		return in_array( preg_replace( '/^(.+)-[0-9]+$/i', '$1', $widget_id ), ZOOM_Builder::$exlcude_widgets );

	}

	/**
	 * Video Embed Code Fix
	 */
	public static function embed_fix( $embed, $width = null, $height = null, $wmode = true ) {

		if ( trim( $embed ) == '' ) return $embed;

		if ( !class_exists( 'DOMDocument' ) ) return $embed;
		libxml_use_internal_errors( true );
		$DOM = new DOMDocument;
		if ( $DOM->loadHTML( $embed ) === false ) return $embed;

		$html = null !== ( $iframe = $DOM->getElementsByTagName('iframe')->item(0) ) ? $iframe : ( null !== ( $video = $DOM->getElementsByTagName('video')->item(0) ) ? $video : false );
		unset( $iframe, $video );

		if ( $html !== false ) {

			if ( $wmode == true && $html->nodeName == 'iframe' && '' != ( $src = trim( $html->getAttribute( 'src' ) ) ) ) {
				$html->setAttribute( 'src', add_query_arg( 'wmode', 'transparent', ZOOM_Builder_Video_API::normalize_url_protocol( $src ) ) );
			}

			if ( !is_null( $width ) ) $html->setAttribute( 'width', absint( $width ) );
			if ( !is_null( $height ) ) $html->setAttribute( 'height', absint( $height ) );

			if ( version_compare( phpversion(), '5.3.6', '>=' ) ) {
				$embed = $DOM->saveHTML( $html );
			} else {
				$embed = preg_replace( '~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $DOM->saveHTML() );
			}

		}

		return $embed;

	}

	/**
	 * Takes an image URL (or attachment ID) and returns a URL to a version of that same image that is equal in dimensions to the passed $width and $height parameters
	 * The image will be resized on-the-fly, saved, and returned if an image of that same size doesn't already exist in the media library
	 *
	 * @param string|int $image The image URL or attachment ID whose resized version the function should return
	 * @param int $width The desired width of the returned image
	 * @param int $height The desired height of the returned image (or 'auto' to maintain aspect ratio)
	 * @param boolean $crop Should the image be cropped to the desired dimensions (Defaults to false in which case the image is scaled down, rather than cropped)
	 * @param boolean $array Should the function return a single string with the image URL or an array with the URL, image height, and image width (Defaults to false which means it only returns a string with the image URL)
	 * @return string
	 */
	public static function thumbIt( $image, $width, $height = 'auto', $crop = true, $array = false ) {

		if ( empty( $image ) || empty( $width ) || absint( $width ) < 1 ) return $image;

		global $wpdb;

		if ( is_int( $image ) ) {

			$attachment_id = $image > 0 ? $image : false;

		} else {

			$img_url = esc_url( $image );
			$upload_dir = wp_upload_dir();
			$base_url = $upload_dir['baseurl'];
			if ( substr( $img_url, 0, strlen( $base_url ) ) !== $base_url ) return $image;
			$result = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE %s LIMIT 1;", '%' . like_escape( str_replace( trailingslashit( $base_url ), '', $img_url ) ) . '%' ) );
			$attachment_id = absint( $result ) > 0 ? absint( $result ) : false;

		}

		if ( $attachment_id === false ) return $image;

		$image = wp_get_attachment_url( $attachment_id );

		$attachment_meta = wp_get_attachment_metadata( $attachment_id );
		if ( $attachment_meta === false ) return $image;

		$width = absint( $width );
		$height = absint( $height == 'auto' ? ( $attachment_meta['height'] * $width / $attachment_meta['width'] ) : $height );
		$needs_resize = true;

		if ( $width > $attachment_meta['width'] && $height > $attachment_meta['height'] ) {

			$needs_resize = false;

		} else {

			foreach ( $attachment_meta['sizes'] as $size ) {
				if ( ( $width == $size['width'] && $size['height'] <= $height ) || ( $height == $size['height'] && $size['width'] <= $width ) ) {
					$image = str_replace( basename( $image ), $size['file'], $image );
					$needs_resize = false;
					break;
				}
			}

		}

		if ( $needs_resize ) {

			$attached_file = get_attached_file( $attachment_id );
			$resized = image_make_intermediate_size( $attached_file, $width, $height, (bool)$crop );

			if ( !is_wp_error( $resized ) && $resized !== false ) {

				$key = sprintf( 'resized-%dx%d', $width, $height );
				$attachment_meta['sizes'][ $key ] = $resized;
				$image = str_replace( basename( $image ), $resized['file'], $image );
				wp_update_attachment_metadata( $attachment_id, $attachment_meta );

				$backup_sizes = get_post_meta( $attachment_id, '_wp_attachment_backup_sizes', true );
				if ( !is_array( $backup_sizes ) ) $backup_sizes = array();
				$backup_sizes[ $key ] = $resized;
				update_post_meta( $attachment_id, '_wp_attachment_backup_sizes', $backup_sizes );

			}

		}

		if ( $array === true && false !== ( $image_size = @getimagesize( $image ) ) ) {
			return array( $image, $image_size[0], $image_size[1] );
		}

		return $image;

	}

}