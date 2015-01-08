<?php defined('ABSPATH') or die; # Don't ever load this file directly

/**
 * ZOOM_Builder_Display Class
 *
 * Functions related to displaying built layouts on the frontend of a WordPress site.
 *
 * @package ZOOM_Builder
 * @subpackage Display
 */

class ZOOM_Builder_Display {

	/**
	 * Hook some stuff up to WordPress
	 */
	public static function init() {

		add_action( 'template_redirect', array( __CLASS__, 'output_groups_css' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wp_enqueue_scripts' ) );

	}

	/**
	 * Enqueue styles needed on the frontend
	 */
	public static function wp_enqueue_scripts() {

		if ( is_singular( ZOOM_Builder::$post_types ) && get_post_meta( get_the_ID(), '_wpzoom_layout', true ) != '' ) {

			wp_register_style( 'wpzlb-styles', ZOOM_Builder::$assets_url . '/css/zoom-builder.css', array(), ZOOM_Builder::$version );
			if ( ZOOM_Builder::$dark_theme ) wp_register_style( 'wpzlb-styles-dark', ZOOM_Builder::$assets_url . '/css/zoom-builder-dark.css', array( 'wpzlb-styles' ), ZOOM_Builder::$version );
			wp_register_style( 'wpzlb-groups-styles', add_query_arg( 'wpzlb_groups_css', '', get_permalink() ), array(), ZOOM_Builder::$version );
			wp_enqueue_style( 'wpzlb-styles' );
			if ( ZOOM_Builder::$dark_theme ) wp_enqueue_style( 'wpzlb-styles-dark' );
			wp_enqueue_style( 'wpzlb-groups-styles' );

			wp_register_script( 'fitvids', ZOOM_Builder::$assets_url . '/js/fitvids.js', array(), ZOOM_Builder::$version );
			wp_enqueue_script( 'fitvids' );

			add_filter( 'the_content', array( __CLASS__, 'append_layout_the_content' ), 9999 );

		}

	}

	/**
	 * Displays the layout HTML for use on the frontend (copies a lot of code from the core dynamic_sidebar() function)
	 */
	public static function display_layout( $page_id = null, $return = false ) {

		global $wp_registered_sidebars, $wp_registered_widgets;

		$id = is_null( $page_id ) ? get_the_ID() : intval( $page_id );
		if ( $id < 1 ) return false;

		$page = get_post( $id );
		if ( $page === null || !( $page instanceof WP_Post ) || !in_array( $page->post_type, ZOOM_Builder::$post_types ) ) return false;

		$layout = get_post_meta( $id, '_wpzoom_layout', true );
		if ( empty( $layout ) || !is_array( $layout ) ) return false;
		$layout = ZOOM_Builder_Utils::sanitize_layout_array( $layout );

		$sidebar_id = "_wpzlb-page-$id-widgets";

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( empty( $sidebars_widgets ) ) return false;

		if ( empty( $wp_registered_sidebars[$sidebar_id] ) || !array_key_exists( $sidebar_id, $sidebars_widgets ) || !is_array( $sidebars_widgets[$sidebar_id] ) || empty( $sidebars_widgets[$sidebar_id] ) ) return false;

		$sidebar = $wp_registered_sidebars[$sidebar_id];

		$locations = (array)get_post_meta( $id, '_wpzlb_theme_locations', true );

		$did_one = false;

		$out = '<div class="wpzlb-layout">';

		foreach ( $layout as $groupi => $group ) {

			if ( isset( $group['groupname'] ) && !empty( $group['groupname'] ) && in_array( sanitize_title( trim( $group['groupname'] ) ), $locations ) ) continue;

			$out .= '<div id="group' . ( $groupi + 1 ) . '" class="wpzlb-group">';

			foreach ( $group['rows'] as $rowi => $row ) {

				$out .= '<div id="group' . ( $groupi + 1 ) . '-row' . ( $rowi + 1 ) . '" class="wpzlb-row wpzlb-row-' . ( $rowi + 1 ) . ' wpzlb-row-type-' . $row['type'] . '"><div class="wpzlb-inner-wrap">';

				if ( $row['type'] != 'divider' ) {

					foreach ( $row['columns'] as $coli => $column ) {

						$out .= '<ul id="group' . ( $groupi + 1 ) . '-row' . ( $rowi + 1 ) . '-col' . ( $coli + 1 ) . '" class="wpzlb-column wpzlb-column-' . ( $coli + 1 ) . '"' . ( $row['type'] != 1 && isset( $column['width'] ) ? ' style="width:' . round( floatval( $column['width'] ), 1 ) . '%"' : '' ) . '>';

						$widgets_out = '';

						foreach ( $column['widgets'] as $widget ) {

							if ( !isset( $wp_registered_widgets[$widget] ) ) continue;

							$params = array_merge(
								array( array_merge( $sidebar, array( 'widget_id' => $widget, 'widget_name' => $wp_registered_widgets[$widget]['name'] ) ) ),
								(array)$wp_registered_widgets[$widget]['params']
							);

							$classname_ = '';
							foreach ( (array)$wp_registered_widgets[$widget]['classname'] as $cn ) {
								if ( is_string($cn) )
									$classname_ .= '_' . $cn;
								elseif ( is_object($cn) )
									$classname_ .= '_' . get_class($cn);
							}
							$classname_ = ltrim( $classname_, '_' );
							$params[0]['before_widget'] = sprintf( $params[0]['before_widget'], $widget, $classname_ );

							$params = apply_filters( 'dynamic_sidebar_params', $params );

							$callback = $wp_registered_widgets[$widget]['callback'];

							do_action( 'dynamic_sidebar', $wp_registered_widgets[$widget] );

							if ( is_callable( $callback ) ) {
								ob_start();
								call_user_func_array( $callback, $params );
								$widgets_out .= ob_get_clean();
								$did_one = true;
							}

						}

						$out .= trim( $widgets_out ) != '' ? $widgets_out : '<li class="no-widgets">&nbsp;</li>';

						$out .= '</ul>';

					}

				}

				$out .= '<div class="wpzlb-clearfix"></div></div></div>';

			}

			$out .= '</div>';

		}

		$out .= '</div>';

		if ( !$return ) echo trim( $out );
		return $return ? trim( $out ) : $did_one;

	}

	/**
	 * Displays the layout HTML of a given group for use on the frontend
	 */
	public static function get_layout_group( $group = null, $page_id = null ) {

		global $wp_registered_sidebars, $wp_registered_widgets;

		if ( is_null( $group ) || !is_array( $group ) || empty( $group ) ) return false;

		$id = is_null( $page_id ) ? get_the_ID() : intval( $page_id );
		if ( $id < 1 ) return false;

		$sidebar_id = "_wpzlb-page-$id-widgets";

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( empty( $sidebars_widgets ) ) return false;

		if ( empty( $wp_registered_sidebars[$sidebar_id] ) || !array_key_exists( $sidebar_id, $sidebars_widgets ) || !is_array( $sidebars_widgets[$sidebar_id] ) || empty( $sidebars_widgets[$sidebar_id] ) ) return false;

		$sidebar = $wp_registered_sidebars[$sidebar_id];

		$out = '<div id="group_' . sanitize_title( trim( $group['groupname'] ) ) . '" class="wpzlb-group">';

		foreach ( $group['rows'] as $rowi => $row ) {

			$out .= '<div id="row' . ( $rowi + 1 ) . '" class="wpzlb-row wpzlb-row-type-' . $row['type'] . '"><div class="wpzlb-inner-wrap">';

			if ( $row['type'] != 'divider' ) {

				foreach ( $row['columns'] as $coli => $column ) {

					$out .= '<ul id="col' . ( $coli + 1 ) . '" class="wpzlb-column wpzlb-column-' . ( $coli + 1 ) . '"' . ( $row['type'] != 1 && isset( $column['width'] ) ? ' style="width:' . round( floatval( $column['width'] ), 1 ) . '%"' : '' ) . '>';

					$widgets_out = '';

					foreach ( $column['widgets'] as $widget ) {

						if ( !isset( $wp_registered_widgets[$widget] ) ) continue;

						$params = array_merge(
							array( array_merge( $sidebar, array( 'widget_id' => $widget, 'widget_name' => $wp_registered_widgets[$widget]['name'] ) ) ),
							(array)$wp_registered_widgets[$widget]['params']
						);

						$classname_ = '';
						foreach ( (array)$wp_registered_widgets[$widget]['classname'] as $cn ) {
							if ( is_string($cn) )
								$classname_ .= '_' . $cn;
							elseif ( is_object($cn) )
								$classname_ .= '_' . get_class($cn);
						}
						$classname_ = ltrim( $classname_, '_' );
						$params[0]['before_widget'] = sprintf( $params[0]['before_widget'], $widget, $classname_ );

						$params = apply_filters( 'dynamic_sidebar_params', $params );

						$callback = $wp_registered_widgets[$widget]['callback'];

						do_action( 'dynamic_sidebar', $wp_registered_widgets[$widget] );

						if ( is_callable( $callback ) ) {
							ob_start();
							call_user_func_array( $callback, $params );
							$widgets_out .= ob_get_clean();
							$did_one = true;
						}

					}

					$out .= trim( $widgets_out ) != '' ? $widgets_out : '<li class="no-widgets">&nbsp;</li>';

					$out .= '</ul>';

				}

			}

			$out .= '<div class="wpzlb-clearfix"></div></div></div>';

		}

		$out .= '</div>';

		return trim( $out );

	}

	/**
	 * Outputs all the CSS styles (background, padding, border, etc.) for all groups of the requested page
	 * Used on single pages on the frontend, and only if the page actually has a layout
	 */
	public static function output_groups_css() {

		if ( is_singular( ZOOM_Builder::$post_types ) && isset( $_GET['wpzlb_groups_css'] ) ) {

			header('Content-type: text/css');

			$layout = get_post_meta( get_the_ID(), '_wpzoom_layout', true );
			if ( empty( $layout ) || !is_array( $layout ) ) exit;
			$layout = ZOOM_Builder_Utils::sanitize_layout_array( $layout );

			$locations = (array)get_post_meta( get_the_ID(), '_wpzlb_theme_locations', true );

			$output = '';

			foreach ( $layout as $groupi => $group ) {

				$settings = isset( $group['settings'] ) && is_array( $group['settings'] ) && !empty( $group['settings'] ) ? $group['settings'] : array();
				if ( empty( $settings ) ) continue;

				$group_id = isset( $group['groupname'] ) && !empty( $group['groupname'] ) ? sanitize_title( trim( $group['groupname'] ) ) : '';
				$location = !empty( $group_id ) ? array_search( $group_id, $locations ) : false;
				$selector = $location !== false ? ".{$location}_group #group_$group_id" : '.wpzlb-layout #group' . ( $groupi + 1 );

				$output .= "$selector{";

				foreach ( $settings as $name => $value ) {

					switch ( $name ) {

						case 'font':
							$output .= "color:$value;";

							break;

						case 'background':
							if ( isset( $value['color'] ) ) $output .= "background-color:{$value['color']};";
							if ( isset( $value['image']['url'] ) ) $output .= "background-image:url('{$value['image']['url']}');";
							if ( isset( $value['image']['position'] ) ) $output .= 'background-position:' . ( $value['image']['position'] == 'left' || $value['image']['position'] == 'right' ? 'top ' . $value['image']['position'] : 'center' ) . ';';
							if ( isset( $value['image']['repeat'] ) ) $output .= 'background-repeat:' . ( $value['image']['repeat'] == 'norepeat' ? 'no-repeat' : ( $value['image']['repeat'] == 'tileh' ? 'repeat-x' : ( $value['image']['repeat'] == 'tilev' ? 'repeat-y' : 'repeat' ) ) ) . ';';
							if ( isset( $value['image']['attachment'] ) ) $output .= "background-attachment:{$value['image']['attachment']};";

							break;

						case 'padding':
							$padding_top = isset( $value['top'] ) ? intval( $value['top'] ) : 0;
							$padding_left = isset( $value['left'] ) ? intval( $value['left'] ) : 0;
							$padding_right = isset( $value['right'] ) ? intval( $value['right'] ) : 0;
							$padding_bottom = isset( $value['bottom'] ) ? intval( $value['bottom'] ) : 0;

							$output .= 'padding:';
							$output .= $padding_top == $padding_left && $padding_top == $padding_right && $padding_top == $padding_bottom ? "{$padding_top}px" : "{$padding_top}px {$padding_right}px {$padding_bottom}px {$padding_left}px";
							$output .= ';';

							break;

						case 'border':
							$border_top = isset( $value['top'] ) ? $value['top'] : array();
							$border_top_width = isset( $border_top['width'] ) ? intval( $border_top['width'] ) : 0;
							$border_top_color = isset( $border_top['color'] ) ? trim( $border_top['color'] ) : 'transparent';
							$border_top_style = isset( $border_top['style'] ) ? trim( $border_top['style'] ) : 'none';
							$border_top_radius = isset( $border_top['radius'] ) ? intval( $border_top['radius'] ) : 0;

							$border_left = isset( $value['left'] ) ? $value['left'] : array();
							$border_left_width = isset( $border_left['width'] ) ? intval( $border_left['width'] ) : 0;
							$border_left_color = isset( $border_left['color'] ) ? trim( $border_left['color'] ) : 'transparent';
							$border_left_style = isset( $border_left['style'] ) ? trim( $border_left['style'] ) : 'none';
							$border_left_radius = isset( $border_left['radius'] ) ? intval( $border_left['radius'] ) : 0;

							$border_right = isset( $value['right'] ) ? $value['right'] : array();
							$border_right_width = isset( $border_right['width'] ) ? intval( $border_right['width'] ) : 0;
							$border_right_color = isset( $border_right['color'] ) ? trim( $border_right['color'] ) : 'transparent';
							$border_right_style = isset( $border_right['style'] ) ? trim( $border_right['style'] ) : 'none';
							$border_right_radius = isset( $border_right['radius'] ) ? intval( $border_right['radius'] ) : 0;

							$border_bottom = isset( $value['bottom'] ) ? $value['bottom'] : array();
							$border_bottom_width = isset( $border_bottom['width'] ) ? intval( $border_bottom['width'] ) : 0;
							$border_bottom_color = isset( $border_bottom['color'] ) ? trim( $border_bottom['color'] ) : 'transparent';
							$border_bottom_style = isset( $border_bottom['style'] ) ? trim( $border_bottom['style'] ) : 'none';
							$border_bottom_radius = isset( $border_bottom['radius'] ) ? intval( $border_bottom['radius'] ) : 0;

							$output .= 'border-width:';
							$output .= $border_top_width == $border_left_width && $border_top_width == $border_right_width && $border_top_width == $border_bottom_width ? "{$border_top_width}px" : "{$border_top_width}px {$border_right_width}px {$border_bottom_width}px {$border_left_width}px";
							$output .= ';border-color:';
							$output .= $border_top_color == $border_left_color && $border_top_color == $border_right_color && $border_top_color == $border_bottom_color ? $border_top_color : "$border_top_color $border_right_color $border_bottom_color $border_left_color";
							$output .= ';border-style:';
							$output .= $border_top_style == $border_left_style && $border_top_style == $border_right_style && $border_top_style == $border_bottom_style ? $border_top_style : "$border_top_style $border_right_style $border_bottom_style $border_left_style";
							$output .= ';border-radius:';
							$output .= $border_top_radius == $border_left_radius && $border_top_radius == $border_right_radius && $border_top_radius == $border_bottom_radius ? "{$border_top_radius}px" : "{$border_left_radius}px {$border_top_radius}px {$border_right_radius}px {$border_bottom_radius}px";
							$output .= ';';

							break;

						case 'margin':
							$margin_top = isset( $value['top'] ) ? intval( $value['top'] ) : 0;
							$margin_left = isset( $value['left'] ) ? intval( $value['left'] ) : 0;
							$margin_right = isset( $value['right'] ) ? intval( $value['right'] ) : 0;
							$margin_bottom = isset( $value['bottom'] ) ? intval( $value['bottom'] ) : 0;

							$output .= 'margin:';
							$output .= $margin_top == $margin_left && $margin_top == $margin_right && $margin_top == $margin_bottom ? "{$margin_top}px" : "{$margin_top}px {$margin_right}px {$margin_bottom}px {$margin_left}px";
							$output .= ';';

							break;

					}

				}

				$output .= '}';

				if ( isset( $settings['font'] ) ) {
					$output .= "{$selector} .widgettitle,{$selector} p{color:{$settings['font']}}";
				}

			}

			echo trim( $output );
			exit;

		}

	}

	/**
	 * Appends the layout from the current post/page (if any) to the end of the_content if on a single post/page
	 */
	public static function append_layout_the_content( $content ) {

		if ( is_singular( ZOOM_Builder::$post_types ) && get_post_meta( get_the_ID(), '_wpzoom_layout', true ) != '' && '' != ( $layout = trim( (string)self::display_layout( null, true ) ) ) ) {

			$add = "\n\n$layout\n\n";

			if ( ZOOM_Builder::$layout_position == 'above' )
				$content = $add . $content;
			elseif ( ZOOM_Builder::$layout_position == 'replace' )
				$content = $add;
			else
				$content .= $add;

		}

		return $content;

	}

	/**
	 * Registers a new location that can have a specific layout builder group assigned to it to be displayed on the frontend
	 */
	public static function register_theme_group_location( $location ) {

		$location_id = sanitize_title( trim( $location ) );

		if ( !isset( ZOOM_Builder::$theme_group_locations[ $location_id ] ) ) {

			ZOOM_Builder::$theme_group_locations[ $location_id ] = sanitize_text_field( trim( $location ) );

			return true;

		}

		return false;

	}

	/**
	 * Displays the layout builder group assigned to the given registered location on the frontend
	 */
	public static function display_theme_group_location( $location, $page_id = null ) {

		$id = is_null( $page_id ) ? get_the_ID() : intval( $page_id );
		if ( $id < 1 ) return false;

		$location = sanitize_title( trim( $location ) );
		if ( empty( $location ) || !isset( ZOOM_Builder::$theme_group_locations[ $location ] ) ) return false;

		$locations = (array)get_post_meta( $id, '_wpzlb_theme_locations', true );
		if ( empty( $locations ) || !isset( $locations[ $location ] ) ) return false;

		$layout = get_post_meta( $id, '_wpzoom_layout', true );
		if ( empty( $layout ) || !is_array( $layout ) ) return false;
		$layout = ZOOM_Builder_Utils::sanitize_layout_array( $layout );

		foreach ( $layout as $groupi => $group ) {

			if ( $locations[ $location ] == sanitize_title( trim( $group['groupname'] ) ) ) {

				echo self::get_layout_group( $group );
				break;

			}

		}

		return true;

	}

}