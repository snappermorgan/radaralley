<?php
/**
 * This file deals with the importing of design settings.
 *
 * @package Prose
 * @author StudioPress
 * @since 1.0.0
 */

/**
 * This function pulls the design settings from the DB
 * for use/return. Does not cache, so always up to date.
 * 
 * @author StudioPress
 * @return mixed
 * @since 1.0.0
 */
function prose_get_fresh_design_option( $opt ) {
	$setting = get_option( PROSE_SETTINGS_FIELD );
	
	if ( isset( $setting[$opt] ) )
		return $setting[$opt];
	
	return false;
}

/**
 * This function pulls the design settings from the DB
 * for use/return. Uses cache to minimise repeat lookups.
 * 
 * @author StudioPress
 * @return mixed
 * @uses genesis_get_option()
 */
function prose_get_design_option( $opt ) {
	return genesis_get_option( $opt, PROSE_SETTINGS_FIELD );
}

/**
 * Get the current version of Prose.
 * 
 * @author StudioPress
 * @return string
 * @since 1.0.0
 */
function prose_get_version() {
	return CHILD_THEME_VERSION;
}

/**
 * Get the current database version of Prose.
 * 
 * @author StudioPress
 * @return string
 * @since 1.5.0
 */
function prose_get_db_version() {
	return CHILD_DB_VERSION;
}

/**
 * Pull the option from the database to know if we're wanting minified CSS.
 * 
 * @author StudioPress
 * @since 1.0.0
 */
function prose_is_minified() {
	return prose_get_fresh_design_option( 'minify_css' );
}

/**
 * Maps the stored values, to the output formats.
 *
 * @author StudioPress
 * @since 1.0.0
 * @return array Multi-dimensional array mapping CSS selectors to the settings
 */
function prose_get_mapping() {
	// Format:
	// '#selector' => array(
	//      'property1' => 'value1',
	//      'property2' => 'value2',
	//      'property-with-multiple-values-or-units' => array(
	//          array( 'value', 'unit' ),
	//          array( 'value', 'string' ),
	//          array( 'value', 'unit' )
	//      ),
	//      'property4' => 'value4'
	// ),

	$mapping = array (
		'body, p' => array(
			'color' => 'body_font_color',
			'font-family' => 'body_font_family',
			'font-size' => array(
				array( 'body_font_size', 'px' )
			),
			'line-height' => array(
				array( 'body_line_height', '' )
			)
		),
		'a, a:visited' => array(
			'color' => 'body_link_color',
			'text-decoration' => 'body_link_decoration'
		),
		'a:hover' => array(
			'color' => 'body_link_hover',
			'text-decoration' => 'body_link_hover_decoration'
		),
		'#wrap' => array(
			'background-color' => 'wrap_background_color',
			'background-color_select' => 'wrap_background_color_select',
			'margin' => array(
				array( 'wrap_margin_top', 'px' ),
				array( 'auto', 'fixed_string' ),
				array( 'wrap_margin_bottom', 'px' )
			),
			'padding' => array(
				array( 'wrap_padding', 'px' )
			),
			'border' => array(
				array( 'wrap_border', 'px' ),
				array( 'wrap_border_style', 'string' ),
				array( 'wrap_border_color', 'string' )
			),
			'-moz-border-radius' => array(
				array( 'wrap_corner_radius', 'px' )
			),
			'-webkit-border-radius' => array(
				array( 'wrap_corner_radius', 'px' )
			),
			'border-radius' => array(
				array( 'wrap_corner_radius', 'px' )
			),
			'-moz-box-shadow' => 'wrap_background_shadow',
			'-webkit-box-shadow' => 'wrap_background_shadow'
		),
		'#header' => array(
			'height' => array(
				array( 'header_image_height', 'px' )
			),
			'max-width' => array(
				array( 'header_image_width', 'px' )
			),
			'background-color' => array(
				array( 'header_background_color', 'string' )
			)
		),
		'#title-area' => array(
			'width' => array(
				array( 'header_title_area_width', 'px' )
			)
		),
		
		'#title-area #title ' => array(
			'padding-top' => array(
				array( 'header_top_padding', 'px' )
			),
			'padding-left' => array(
				array( 'header_left_padding', 'px' )
			)
		),
		'.header-image #title-area, .header-image #title-area #title, .header-image #title-area #title a' => array(
			'width' => array(
				array( 'header_title_area_width', 'px' )
			),
			'height' => array(
				array( 'header_image_height', 'px' )
			)
		),
		'#title-area #title a, #title-area #title a:hover' => array(
			'color' => 'header_title_font_color',
			'font-family' => 'header_title_font_family',
			'font-size' => array(
				array( 'header_title_font_size', 'px' )
			),
		),
		'#title-area #description' => array(
			'color' => 'header_tagline_font_color',
			'font-family' => 'header_tagline_font_family',
			'font-size' => array(
				array( 'header_tagline_font_size', 'px' )
			),
			'padding-left' => array(
				array( 'header_tagline_left_padding', 'px' )
			),
			'padding-top' => array(
				array( 'header_tagline_top_padding', 'px' )
			),
			'font-style' => 'header_tagline_font_style'
		),
		'#header .widget-area' => array(
			'width' => array(
				array( 'header_widget_area_width', 'px' )
			)
		),
		'#nav' => array(
			'background-color' => 'primary_nav_background_color',
			'background-color_select' => 'primary_nav_background_color_select',
			'font-family' => 'primary_nav_font_family',
			'font-size' => array(
				array( 'primary_nav_font_size', 'px' )
			),
			'text-transform' => 'primary_nav_text_transform',
			'border' => array(
				array( 'primary_nav_border', 'px' ),
				array( 'primary_nav_border_style', 'string' ),
				array( 'primary_nav_border_color', 'string' )
			),
		),
		'#nav .wrap' => array(
			'border' => array(
				array( 'primary_nav_inner_border', 'px' ),
				array( 'primary_nav_inner_border_style', 'string' ),
				array( 'primary_nav_inner_border_color', 'string' )
			)
		),
		'#nav li a' => array(
			'background-color' => 'primary_nav_link_background',
			'background-color_select' => 'primary_nav_link_background_select',
			'color' => 'primary_nav_link_color',
			'text-decoration' => 'primary_nav_link_decoration'
		),
		'#nav li.right a, #nav li.rss a, #nav li.twitter a' => array(
			'color' => 'primary_nav_link_color',
		),
		'#nav li a:hover, #nav li a:active, #nav .current_page_item a, #nav .current-menu-item a' => array(
			'background-color' => 'primary_nav_link_hover_background',
			'background-color_select' => 'primary_nav_link_hover_background_select',
			'color' => 'primary_nav_link_hover',
			'text-decoration' => 'primary_nav_link_hover_decoration'
		),
		'#nav li.right a:hover, #nav li.rss a:hover, #nav li.twitter a:hover' => array(
			'color' => 'primary_nav_link_color',
		),        
		'#nav li li a, #nav li li a:link, #nav li li a:visited' => array(
			'background-color' => 'primary_nav_link_background',
			'background-color_select' => 'primary_nav_link_background_select',
			'color' => 'primary_nav_link_color'
		),
		'#nav li li a:hover, #nav li li a:active' => array(
			'background-color' => 'primary_nav_link_hover_background',
			'background-color_select' => 'primary_nav_link_hover_background_select',
			'color' => 'primary_nav_link_hover'
		),
		'#subnav' => array(
			'background-color' => 'secondary_nav_background_color',
			'background-color_select' => 'secondary_nav_background_color_select',
			'font-family' => 'secondary_nav_font_family',
			'font-size' => array(
				array( 'secondary_nav_font_size', 'px' )
			),
			'text-transform' => 'secondary_nav_text_transform',
			'border' => array(
				array( 'secondary_nav_border', 'px' ),
				array( 'secondary_nav_border_style', 'string' ),
				array( 'secondary_nav_border_color', 'string' )
			),
		),
		'#subnav .wrap' => array(
			'border' => array(
				array( 'secondary_nav_inner_border', 'px' ),
				array( 'secondary_nav_inner_border_style', 'string' ),
				array( 'secondary_nav_inner_border_color', 'string' )
			),
		),
		'#subnav li a' => array(
			'background-color' => 'secondary_nav_link_background',
			'background-color_select' => 'secondary_nav_link_background_select',
			'color' => 'secondary_nav_link_color',
			'text-decoration' => 'secondary_nav_link_decoration'
		),
		'#subnav li a:hover, #subnav li a:active, #subnav .current_page_item a, #subnav .current-menu-item a' => array(
			'background-color' => 'secondary_nav_link_hover_background',
			'background-color_select' => 'secondary_nav_link_hover_background_select',
			'color' => 'secondary_nav_link_hover',
			'text-decoration' => 'secondary_nav_link_hover_decoration'
		),
		'#subnav li li a, #subnav li li a:link, #subnav li li a:visited' => array(
			'background-color' => 'secondary_nav_link_background',
			'background-color_select' => 'secondary_nav_link_background_select',
			'color' => 'secondary_nav_link_color'
		),
		'#subnav li li a:hover, #subnav li li a:active' => array(
			'background-color' => 'secondary_nav_link_hover_background',
			'background-color_select' => 'secondary_nav_link_hover_background_select',
			'color' => 'secondary_nav_link_hover'
		),
		'.breadcrumb' => array(
			'color' => 'breadcrumb_font_color',
			'text-transform' => 'breadcrumb_text_transform',
			'font-size' => array(
				array( 'breadcrumb_font_size', 'px' )
			),
			'border-bottom' => array(
				array( 'breadcrumb_border', 'px' ),
				array( 'breadcrumb_border_style', 'string' ),
				array( 'breadcrumb_border_color', 'string' )
			),
		),
		'.post-info' => array(
			'background-color' => 'post_info_background_color',
			'background-color_select' => 'post_info_background_color_select',
			'color' => 'post_info_font_color',
			'text-transform' => 'post_info_text_transform',
			'font-size' => array(
				array( 'post_info_font_size', 'px' )
			),
		),
		'.post-meta' => array(
			'background-color' => 'post_meta_background_color',
			'background-color_select' => 'post_meta_background_color_select',
			'color' => 'post_meta_font_color',
			'text-transform' => 'post_meta_text_transform',
			'font-size' => array(
				array( 'post_meta_font_size', 'px' )
			),
		),
		'blockquote' => array(
			'font-style' => 'blockquotes_font_style',
			'background-color' => 'blockquotes_background_color',
			'background-color_select' => 'blockquotes_background_color_select',
			'border' => array(
				array( 'blockquotes_border', 'px' ),
				array( 'blockquotes_border_style', 'string' ),
				array( 'blockquotes_border_color', 'string' )
			),
		),
		'blockquote p' => array(
			'color' => 'blockquotes_font_color',
			'font-family' => 'blockquotes_font_family',
			'font-size' => array(
				array( 'blockquotes_font_size', 'px' )
			),
		),
		'p.notice' => array(
			'color' => 'notice_font_color',
			'font-family' => 'notice_font_family',
			'font-size' => array(
				array( 'notice_font_size', 'px' )
			),
			'font-style' => 'notice_font_style',
			'background-color' => 'notice_background_color',
			'background-color_select' => 'notice_background_color_select',
			'border' => array(
				array( 'notice_border', 'px' ),
				array( 'notice_border_style', 'string' ),
				array( 'notice_border_color', 'string' )
			),
		),              
		'h1, h2, h3, h4, h5, h6' => array(
			'font-family' => 'headline_font_family',
			'font-style' => 'headline_font_style',
			'font-weight' => 'headline_font_weight',
			'text-transform' => 'headline_text_transform'
		),
		'h1' => array(
			'font-size' => array(
				array( 'h1_font_size', 'px' )
			),
			'color' => 'h1_font_color'
		),
		'h2' => array(
			'font-size' => array(
				array( 'h2_font_size', 'px' )
			),
			'color' => 'h2_font_color'
		),
		'h2 a, h2 a:visited' => array(
			'color' => 'h2_link_color',
			'text-decoration' => 'h2_link_decoration'
		),
		'h2 a:hover' => array(
			'color' => 'h2_link_hover',
			'text-decoration' => 'h2_link_hover_decoration'
		),
		'h3' => array(
			'font-size' => array(
				array( 'h3_font_size', 'px' )
			),
			'color' => 'h3_font_color'
		),
		'h4' => array(
			'font-size' => array(
				array( 'h4_font_size', 'px' )
			),
			'color' => 'h4_font_color'
		),
		'h5' => array(
			'font-size' => array(
				array( 'h5_font_size', 'px' )
			),
			'color' => 'h5_font_color'
		),
		'h6' => array(
			'font-size' => array(
				array( 'h6_font_size', 'px' )
			),
			'color' => 'h6_font_color'
		),
		'.widget-area h4' => array(
			'color' => 'sidebar_headline_font_color',
			'font-family' => 'sidebar_headline_font_family',
			'font-size' => array(
				array( 'sidebar_headline_font_size', 'px' )
			),
			'font-style' => 'sidebar_headline_font_style',
			'font-weight' => 'sidebar_headline_font_weight',
			'border-bottom' => array(
				array( 'sidebar_headline_border', 'px' ),
				array( 'sidebar_headline_border_style', 'string' ),
				array( 'sidebar_headline_border_color', 'string' )
			),
			'text-transform' => 'sidebar_headline_text_transform'
		),
		'.sidebar, .sidebar p' => array(
			'color' => 'sidebar_font_color',
			'font-family' => 'sidebar_font_family',
			'font-size' => array(
				array( 'sidebar_font_size', 'px' )
			),
		),		
		'#footer-widgets' => array(
			'background-color' => 'footer_background_color',
			'background-color_select' => 'footer_background_color_select',
			'border' => array(
				array( 'footer_border', 'px' ),
				array( 'footer_border_style', 'string' ),
				array( 'footer_border_color', 'string' )
			),
		),
		'#footer-widgets h4' => array(
			'color' => 'footer_headline_font_color',
			'font-family' => 'footer_headline_font_family',
			'font-size' => array(
				array( 'footer_headline_font_size', 'px' )
			),
			'font-style' => 'footer_headline_font_style',
			'font-weight' => 'footer_headline_font_weight',
			'border-bottom' => array(
				array( 'footer_headline_border', 'px' ),
				array( 'footer_headline_border_style', 'string' ),
				array( 'footer_headline_border_color', 'string' )
			),
			'text-transform' => 'footer_headline_text_transform'
		),
		'#footer-widgets a, #footer-widgets a:visited, #footer-widgets li a, #footer-widgets li a:visited' => array(
			'color' => 'footer_widget_link_color',
			'text-decoration' => 'footer_widget_link_decoration'
		),
		'#footer-widgets a:hover, #footer-widgets li a:hover' => array(
			'color' => 'footer_widget_link_hover',
			'text-decoration' => 'footer_widget_link_hover_decoration'
		),
		'#footer, #footer p' => array(
			'color' => 'footer_font_color',
			'font-size' => array(
				array( 'footer_font_size', 'px' )
			),
			'font-weight' => 'footer_font_weight',
			'text-transform' => 'footer_text_transform'
		),
		'#footer a, #footer a:visited' => array(
			'color' => 'footer_link_color',
			'text-decoration' => 'footer_link_decoration'
		),
		'#footer a:hover' => array(
			'color' => 'footer_link_hover',
			'text-decoration' => 'footer_link_hover_decoration'
		),
		'.s, #author, #email, #url, #comment, .enews #subbox, .prose-input' => array(
			'color' => 'input_font_color',
			'font-family' => 'input_font_family',
			'font-style' => 'input_font_style',
			'background-color' => 'input_background_color',
			'background-color_select' => 'input_background_color_select',
			'border' => array(
				array( 'input_border', 'px' ),
				array( 'input_border_style', 'string' ),
				array( 'input_border_color', 'string' )
			),
		),                
		'input[type="button"], input[type="submit"], .prose-button' => array(
			'background-color' => 'button_background_color',
			'background-color_select' => 'button_background_color_select',
			'color' => 'button_font_color',
			'text-transform' => 'button_text_transform',
			'font-family' => 'button_font_family',
			'font-size' => array(
				array( 'button_font_size', 'px' )
			)
		),
		'input:hover[type="button"], input:hover[type="submit"], .prose-button:hover' => array(
			'background-color' => 'button_background_hover_color',
			'background-color_select' => 'button_background_hover_color_select',
		),         
		'minify_css' => 'minify_css'
	);


	return apply_filters( 'prose_get_mapping', $mapping );
}



/**
 * Used to create the actual markup of options.
 *
 * @author StudioPress
 * @param string Used as comparison to see which option should be selected.
 * @param string $type One of 'border', 'family', 'style', 'variant', 'weight', 'align', 'decoration', 'transform'.
 * @since 1.0.0
 * @return string HTML markup of dropdown options
 */
function prose_create_options( $compare, $type ) {
	
	switch( $type ) {
		case "border":
			// border styles
			$options = array(
				array( 'None', 'none' ),
				array( 'Solid', 'solid' ),
				array( 'Dashed', 'dashed' ),
				array( 'Dotted', 'dotted' ),
				array( 'Double', 'double' ),
				array( 'Groove', 'groove' ),
				array( 'Ridge', 'ridge' ),
				array( 'Inset', 'inset' ),
				array( 'Outset', 'outset' )
			);
			break;
		case "family":
			//font-family sets
			$options = array(
				array( 'Arial', 'Arial, Helvetica, sans-serif' ),
				array( 'Arial Black', "'Arial Black', Gadget, sans-serif" ),
				array( 'Century Gothic', "'Century Gothic', sans-serif" ),
				array( 'Courier New', "'Courier New', Courier, monospace" ),
				array( 'Georgia', 'Georgia, serif' ),
				array( 'Lucida Console', "'Lucida Console', Monaco, monospace" ),
				array( 'Lucida Sans Unicode', "'Lucida Sans Unicode', 'Lucida Grande', sans-serif" ),
				array( 'Palatino Linotype', "'Palatino Linotype', 'Book Antiqua', Palatino, serif" ),
				array( 'Tahoma', 'Tahoma, Geneva, sans-serif' ),
				array( 'Times New Roman', "'Times New Roman', serif" ),
				array( 'Trebuchet MS', "'Trebuchet MS', Helvetica, sans-serif" ),
				array( 'Verdana', 'Verdana, Geneva, sans-serif' )
			);
			$options = apply_filters( 'prose_font_family_options', $options );
			sort( $options );
			array_unshift( $options, array( 'Inherit', 'inherit' ) ); // Adds Inherit option as first option.
			break;
		case "style":
			// font-style options
			$options = array(
				array( 'Normal', 'normal' ),
				array( 'Italic', 'italic' )
			);
			break;
		case "variant":
			// font-variant options
			$options = array(
				array( 'Normal', 'normal' ),
				array( 'Small-Caps', 'small-caps' )
			);
			break;
		case "weight":
			// font-weight options
			$options = array(
				array( 'Normal', 'normal' ),
				array( 'Bold', 'bold' )
			);
			break;
		case "align":
			// text-align options
			$options = array(
				array( 'Left', 'left' ),
				array( 'Center', 'center' ),
				array( 'Right', 'right' ),
				array( 'Justify', 'justify' )
			);
			break;
		case "decoration":
			// text-decoration options
			$options = array(
				array( 'None', 'none' ),
				array( 'Underline', 'underline' ),
				array( 'Overline', 'overline' )
				// Include line-through?
			);
			break;
		case "transform":
			// text-transform options
			$options = array(
				array( 'None', 'none' ),
				array( 'Capitalize', 'capitalize' ),
				array( 'Lowercase', 'lowercase' ),
				array( 'Uppercase', 'uppercase' )
			);
			break;
		case "background":
			// background color options
			$options = array(
				array( 'Color (Hex)', 'hex' ),
				array( 'Inherit', 'inherit' ),
				array( 'Transparent', 'transparent' )
			);
			break;
		case "color":
			// font color options
			$options = array(
				array( 'Color (Hex)', 'hex' ),
				array( 'Inherit', 'inherit' )
			);
			break;
		default:
			$options = '';
	}
	if ( is_array($options) ) {
		$output = '';
		foreach ($options as $option) {
			$output .= '<option value="'. esc_attr($option[1]) . '" title="' . esc_attr($option[1]) . '" ' . selected(esc_attr($option[1]), esc_attr($compare), false) . '>' . __($option[0], 'prose' ) . '</option>';
		}
	} else {
		$output = '<option>'.__( 'Select type was not valid.', 'prose' ).'</option>';
	}
	return $output;
}

/*
 * The next few functions are all you need to add new options to a meta box (either here, or via functions.php). 
 * You can also use no function, and just output a translatable string in prose_setting_line().
 */

/**
 * Adds a dropdown setting - label and select.
 *
 * @author StudioPress
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @param string $type One of the types allowed in {@link prose_create_options()}
 * @since 1.0.0
 * @return string HTML markup
 */
function prose_add_select_setting( $id, $label, $type ) {
	return prose_add_label( $id, $label ) . '<select id="' . $id . '" name="' . PROSE_SETTINGS_FIELD . '[' . $id . ']" class="' . $type . '-option-types">' . prose_create_options( prose_get_fresh_design_option( $id ), $type ) . '</select>';
}

/**
 * Adds a color setting - label and input.
 *
 * @author StudioPress
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @since 1.0.0
 * @return string HTML markup
 */
function prose_add_color_setting( $id, $label ) { 
	return prose_add_label( $id, $label ) . '<input type="text" id="' . $id . '" name="' . PROSE_SETTINGS_FIELD . '[' . $id . ']" size="8" maxsize="7" value="' . esc_attr( prose_get_fresh_design_option( $id ) ) . '" class="color-picker" />';
}

/**
 * Adds a background color setting - label, select and input.
 *
 * @author StudioPress
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @since 1.0.0
 * @return string HTML markup
 */
function prose_add_background_color_setting( $id, $label ) { 
	return prose_add_select_setting( $id.'_select', $label, 'background' ) . '<input type="text" id="' . $id . '_hex" name="' . PROSE_SETTINGS_FIELD . '[' . $id . ']" size="8" maxsize="7" value="' . esc_attr( prose_get_fresh_design_option( $id ) ) . '" class="color-picker" />';
}


/**
 * Adds a size setting - label and input.
 *
 * @author StudioPress
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @param int $size Value for the size attribute (default = 1)
 * @since 1.0.0
 * @return string HTML markup
 */
function prose_add_size_setting( $id, $label, $size = 1 ) { 
	return prose_add_label( $id, $label, false ) . '<input class="numeric" type="text" id="' . $id . '" name="' . PROSE_SETTINGS_FIELD . '[' . $id . ']" size="' . $size . '" value="' . esc_attr( prose_get_fresh_design_option( $id ) ) . '" /><abbr title="pixels">px</abbr></label>';
}

/**
 * Adds an em size setting - label and input.
 *
 * @author StudioPress
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @param int $size Value for the size attribute (default = 3)
 * @since 1.5.0
 * @return string HTML markup
 */
function prose_add_em_size_setting( $id, $label, $size = 3 ) { 
	return prose_add_label( $id, $label, false ) . '<input class="numeric" type="text" id="' . $id . '" name="' . PROSE_SETTINGS_FIELD . '[' . $id . ']" size="' . $size . '" value="' . esc_attr( prose_get_fresh_design_option( $id ) ) . '" /></label>';
}

/**
 * Adds a text setting - label and input.
 *
 * @author StudioPress
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @param int $size Value for the size attribute (default = 25)
 * @since 1.0.0
 * @return string HTML markup
 */
function prose_add_text_setting( $id, $label, $size = 25 ) { 
	return prose_add_label( $id, $label ) . '<input type="text" id="' . $id . '" name="' . PROSE_SETTINGS_FIELD . '[' . $id . ']" size="' . $size . '" value="' . esc_attr( prose_get_fresh_design_option( $id ) ) . '" />';
}

/**
 * Adds a checkbox setting - input and label.
 *
 * @author StudioPress
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @since 1.0.0
 * @return string HTML markup
 */
function prose_add_checkbox_setting( $id, $label ) { 
	return '<input type="checkbox" id="' . $id . '" name="' . PROSE_SETTINGS_FIELD . '[' . $id . ']" value="true" class="checkbox" ' . checked(prose_get_fresh_design_option( $id ), 'true', false) . '/> ' . prose_add_label( $id, $label, true, false );
}

/**
 * Adds a textarea setting - label and textarea.
 *
 * @author StudioPress
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @param integer cols Value for the cols attribute (default = 25)
 * @param integer rows Value for the rows attribute (default = 10)
 * @since 1.0.0
 * @return string HTML markup
 */
function prose_add_textarea_setting( $id, $label, $cols = 25, $rows = 10 ) { 
	return prose_add_label( $id, $label ) . '<br /><textarea id="' . $id . '" name="' . PROSE_SETTINGS_FIELD . '[' . $id . ']" cols="39" rows="10">' . prose_get_fresh_design_option( $id ) . '</textarea>';
}

/**
 * Adds border width, color and style settings on one line.
 * 
 * @author StudioPress
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @return string HTML markup 
 * @since 1.0.0
 */
function prose_add_border_setting( $id, $label ) {
	return array( prose_add_size_setting( $id, $label ), prose_add_color_setting($id .'_color', '' ), prose_add_select_setting( $id . '_style', '', 'border' ) );
}

/**
 * Adds a NOTE.
 *
 * @author StudioPress
 * @param string $note Text to display as the note
 * @return string HTML markup
 * @since 1.0.0
 */
function prose_add_note( $note ) {
	return '<span class="description"><strong>' . __( 'Note', 'prose' ) . ':</strong> ' . $note . '</span>';
}


/**
 * Adds the paragraph tags around a setting line and echos the result.
 *
 * @author StudioPress
 * @param array|string $args
 * @since 1.0.0
 */
function prose_setting_line( $args ) {
	if ( is_array( $args ) ) {
		$output = '';
		foreach ( $args as $arg ) {
			$output .= ' ' .$arg;
		}
		prose_setting_line( $output );
	} else { ?>
			<p><?php echo $args; ?></p>
	<?php
	}
}

/**
 * Adds the opening label tag, the for attribute, and the label text itself.
 * 
 * If the label text is at least 1 character long, then it's wrapped as a label element.
 * @author StudioPress
 * @param string $id
 * @param string $label
 * @param boolean $add_end_tag Optionaly add closing label tag (default = true)
 * @param boolean $add_colon Optionaly add colon after the label (default = true)
 * @return string HTML markup for a label
 * @since 1.0.0
 * @todo Fix the label so it's recognisable as being translatable
 */
function prose_add_label( $id, $label, $add_end_tag = true, $add_colon = true ) {
	$return = '';
	if ( strlen( $label ) > 0) {
		$return = sprintf( '<label for="%s">%s', $id, $label);
		if ( $add_colon )
			$return .= ': ';
		if ( $add_end_tag )
			$return .= '</label>';
	}
	
	return $return;
}