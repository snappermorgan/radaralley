<?php
/*
* Add-on Name: Icons Block for Visual Composer
*/
if(!class_exists('Ultimate_List_Icon')) 
{
	class Ultimate_List_Icon
	{
		function __construct()
		{
			add_action('admin_init',array($this,'list_icon_init'));
			add_shortcode('ultimate_icon_list',array($this,'ultimate_icon_list_shortcode'));
			add_shortcode('ultimate_icon_list_item',array($this,'icon_list_item_shortcode'));
		}
		function list_icon_init()
		{
			if(function_exists('vc_map'))
			{
				vc_map(
					array(
						"name" => __("List Icon"),
						"base" => "ultimate_icon_list",
						"class" => "ultimate_icon_list",
						"icon" => "ultimate_icon_list",
						"category" => __("Ultimate VC Addons","smile"),
						"description" => __("Add a set of multiple icons and give some custom style.","smile"),
						"as_parent" => array('only' => 'ultimate_icon_list_item'), 
						"content_element" => true,
						"show_settings_on_create" => true,
						"js_view" => 'VcColumnView',
						"params" => array(							
							// Play with icon selector
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Extra Class","smile"),
								"param_name" => "el_class",
								"value" => "",
								"description" => __("Write your own CSS and mention the class name here.", "flip-box"),
							),
						)
					)
				);
				vc_map(
					array(
					   "name" => __("List Icon Item"),
					   "base" => "ultimate_icon_list_item",
					   "class" => "icon_list_item",
					   "icon" => "icon_list_item",
					   "category" => __("Ultimate VC Addons","smile"),
					   "description" => __("Add a list of icons with some content and give some custom style.","smile"),
					   "as_child" => array('only' => 'ultimate_icon_list'), 
					   "show_settings_on_create" => true,
					   "params" => array(
							// Play with icon selector
							array(
								"type" => "icon_manager",
								"class" => "",
								"heading" => __("Select Icon","smile"),
								"param_name" => "icon",
								"value" => "",
								"admin_label" => true,
								"description" => __("Click and select icon of your choice. If you can't find the one that suits for your purpose, you can <a href='admin.php?page=font-icon-Manager' target='_blank'>add new here</a>.", "flip-box"),
								"group"=> "Initial Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Size of Icon", "smile"),
								"param_name" => "icon_size",
								"value" => 32,
								"min" => 12,
								"max" => 72,
								"suffix" => "px",
								"description" => __("How big would you like it?", "smile"),
								"group"=> "Initial Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Space after Icon", "smile"),
								"param_name" => "icon_margin",
								"value" => 5,
								"min" => 0,
								"max" => 100,
								"suffix" => "px",
								"description" => __("How much distance would you like in two icons?", "smile"),
								"group" => "Initial Settings"
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Color", "smile"),
								"param_name" => "icon_color",
								"value" => "#333333",
								"description" => __("Give it a nice paint!", "smile"),
								"group"=> "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Icon Style", "smile"),
								"param_name" => "icon_style",
								"value" => array(
									"Simple" => "none",
									"Circle Background" => "circle",
									"Square Background" => "square",
									"Design your own" => "advanced",
								),
								"description" => __("We have given three quick preset if you are in a hurry. Otherwise, create your own with various options.", "smile"),
								"group"=> "Initial Settings"
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Background Color", "smile"),
								"param_name" => "icon_color_bg",
								"value" => "#ffffff",
								"description" => __("Select background color for icon.", "smile"),	
								"dependency" => Array("element" => "icon_style", "value" => array("circle","square","advanced")),
								"group"=> "Initial Settings"
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Icon Border Style", "smile"),
								"param_name" => "icon_border_style",
								"value" => array(
									"None"=> "",
									"Solid"=> "solid",
									"Dashed" => "dashed",
									"Dotted" => "dotted",
									"Double" => "double",
									"Inset" => "inset",
									"Outset" => "outset",
								),
								"description" => __("Select the border style for icon.","smile"),
								"dependency" => Array("element" => "icon_style", "value" => array("advanced")),
								"group"=> "Initial Settings"
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Border Color", "smile"),
								"param_name" => "icon_color_border",
								"value" => "#333333",
								"description" => __("Select border color for icon.", "smile"),	
								"dependency" => Array("element" => "icon_border_style", "not_empty" => true),
								"group"=> "Initial Settings"
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Border Width", "smile"),
								"param_name" => "icon_border_size",
								"value" => 1,
								"min" => 1,
								"max" => 10,
								"suffix" => "px",
								"description" => __("Thickness of the border.", "smile"),
								"dependency" => Array("element" => "icon_border_style", "not_empty" => true),
								"group"=> "Initial Settings"
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Border Radius", "smile"),
								"param_name" => "icon_border_radius",
								"value" => 500,
								"min" => 1,
								"max" => 500,
								"suffix" => "px",
								"description" => __("0 pixel value will create a square border. As you increase the value, the shape convert in circle slowly. (e.g 500 pixels).", "smile"),
								"dependency" => Array("element" => "icon_border_style", "not_empty" => true),
								"group"=> "Initial Settings"
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Background Size", "smile"),
								"param_name" => "icon_border_spacing",
								"value" => 50,
								"min" => 30,
								"max" => 500,
								"suffix" => "px",
								"description" => __("Spacing from center of the icon till the boundary of border / background", "smile"),
								"dependency" => Array("element" => "icon_border_style", "not_empty" => true),
								"group"=> "Initial Settings"
							),
							array(
								"type" => "textarea_html",
								"class" => "",
								"heading" => __("List content", "smile"),
								"param_name" => "content",
								"value" => "",
								"description" => __("Enter the list content here.", "smile"),
								"group"=> "List Content"
							),
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Custom CSS Class", "smile"),
								"param_name" => "el_class",
								"value" => "",
								"description" => __("Ran out of options? Need more styles? Write your own CSS and mention the class name here.", "smile"),
								"group"=> "Initial Settings"
							),
						),
					)
				);
			}
		}
		// Shortcode handler function for list Icon
		function ultimate_icon_list_shortcode($atts,$content = null)
		{
			$el_class = '';
			extract(shortcode_atts(array(
				"el_class" => ""
			),$atts));
			// enqueue js
			wp_enqueue_script('ultimate-custom');
			// enqueue css
			wp_enqueue_style('ultimate-animate');
			wp_enqueue_style('ultimate-style');
			wp_enqueue_script('aio-tooltip',plugins_url('../assets/js/',__FILE__).'tooltip.js',array('jquery'));
			wp_enqueue_style('aio-tooltip',plugins_url('../assets/css/',__FILE__).'tooltip.css');
			$output = '<div class="uavc-list-icon '.$el_class.'">';
			$output .= '<ul class="uavc-list">';
			$output .= do_shortcode($content);
			$output .= '</ul>';
			$output .= '</div>';
			
			return $output;
		}
		
		function icon_list_item_shortcode($atts, $content = null){
			
			$icon_type = $icon_img = $img_width = $icon = $icon_color = $icon_color_bg = $icon_size = $icon_style = $icon_border_style = $icon_border_radius = $icon_color_border = $icon_border_size = $icon_border_spacing = $icon_link = $el_class = $icon_animation =  $tooltip_disp = $tooltip_text = $icon_margin = '';
			extract(shortcode_atts( array(
				'icon'=> '',				
				'icon_size' => '',				
				'icon_color' => '',
				'icon_style' => '',
				'icon_color_bg' => '',
				'icon_color_border' => '',			
				'icon_border_style' => '',
				'icon_border_size' => '',
				'icon_border_radius' => '',
				'icon_border_spacing' => '',
				'icon_margin' => '',
				'el_class'=>'',
			),$atts));
			if($icon_animation !== 'none')
			{
				$css_trans = 'data-animation="'.$icon_animation.'" data-animation-delay="03"';
			}
			$output = $style = $link_sufix = $link_prefix = $target = $href = $icon_align_style = '';
						
			if($icon_color !== '')
				$style .= 'color:'.$icon_color.';';
			if($icon_style !== 'none'){
				if($icon_color_bg !== '')
					$style .= 'background:'.$icon_color_bg.';';
			}
			if($icon_style == 'advanced'){
				$style .= 'border-style:'.$icon_border_style.';';
				$style .= 'border-color:'.$icon_color_border.';';
				$style .= 'border-width:'.$icon_border_size.'px;';
				$style .= 'width:'.$icon_border_spacing.'px;';
				$style .= 'height:'.$icon_border_spacing.'px;';
				$style .= 'line-height:'.$icon_border_spacing.'px;';
				$style .= 'border-radius:'.$icon_border_radius.'px;';
			}
			if($icon_size !== '')
				$style .='font-size:'.$icon_size.'px;';
			
			if($icon_margin !== '')
				$style .= 'padding-right:'.$icon_margin.'px;';
			
			$output .= '<div class="uavc-list-content">';
			if($icon !== ""){
				$output .= "\n".'<div class="uavc-list-icon aio-icon '.$icon_style.' '.$el_class.'" '.$css_trans.' style="'.$style.'">';				
				$output .= "\n\t".'<i class="'.$icon.'"></i>';	
				$output .= "\n".'</div>';
			}
			$output .= '<span class="uavc-list-desc">'.do_shortcode($content).'</span>';
			$output .= '</div>';
			
			$output = '<li>'.$output.'</li>';
			return $output;
		}
	}
}
if(class_exists('Ultimate_List_Icon'))
{
	$Ultimate_List_Icon = new Ultimate_List_Icon;
}
//Extend WPBakeryShortCodesContainer class to inherit all required functionality
if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
    class WPBakeryShortCode_ultimate_icon_list extends WPBakeryShortCodesContainer {
    }
}
if ( class_exists( 'WPBakeryShortCode' ) ) {
    class WPBakeryShortCode_ultimate_icon_list_item extends WPBakeryShortCode {
    }
}