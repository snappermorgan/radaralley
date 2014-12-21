<?php
/*
* Add-on Name: Icons Block for Visual Composer
*/
if(!class_exists('Ultimate_Icons')) 
{
	class Ultimate_Icons
	{
		function __construct()
		{
			add_action('admin_init',array($this,'ultimate_icon_init'));
			add_shortcode('ultimate_icons',array($this,'ultimate_icons_shortcode'));
			add_shortcode('single_icon',array($this,'single_icon_shortcode'));
		}
		function ultimate_icon_init()
		{
			if(function_exists('vc_map'))
			{
				vc_map(
					array(
						"name" => __("Icons"),
						"base" => "ultimate_icons",
						"class" => "ultimate_icons",
						"icon" => "ultimate_icons",
						"category" => __("Ultimate VC Addons","smile"),
						"description" => __("Add a set of multiple icons and give some custom style.","smile"),
						"as_parent" => array('only' => 'single_icon'), // Use only|except attributes to limit child shortcodes (separate multiple values with comma)
						"content_element" => true,
						"show_settings_on_create" => true,
						"js_view" => 'VcColumnView',
						"params" => array(							
							// Play with icon selector
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Alignment","smile"),
								"param_name" => "align",
								"value" => array("Left Align" => "uavc-icons-left", "Right Align" => "uavc-icons-right", "Center Align" => "uavc-icons-center"),
								"description" => __("", "smile"),
							),
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Extra Class","smile"),
								"param_name" => "el_class",
								"value" => "",
								"description" => __("Write your own CSS and mention the class name here.", "smile"),
							),
						)
					)
				);
				vc_map(
					array(
					   "name" => __("Icon Item"),
					   "base" => "single_icon",
					   "class" => "vc_simple_icon",
					   "icon" => "vc_just_icon",
					   "category" => __("Ultimate VC Addons","smile"),
					   "description" => __("Add a set of multiple icons and give some custom style.","smile"),
					   "as_child" => array('only' => 'ultimate_icons'), // Use only|except attributes to limit child shortcodes (separate multiple values with comma)
					   "show_settings_on_create" => true,
					   "params" => array(							
							// Play with icon selector
							array(
								"type" => "icon_manager",
								"class" => "",
								"heading" => __("Select Icon ","smile"),
								"param_name" => "icon",
								"value" => "",
								"admin_label" => true,
								"description" => __("Click and select icon of your choice. If you can't find the one that suits for your purpose, you can <a href='admin.php?page=font-icon-Manager' target='_blank'>add new here</a>.", "smile"),
								"group"=> "Select Icon",
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
								"group"=> "Select Icon",
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
								"group" => "Other Settings"
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Color", "smile"),
								"param_name" => "icon_color",
								"value" => "#333333",
								"description" => __("Give it a nice paint!", "smile"),
								"group"=> "Select Icon",
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
								"group" => "Select Icon"
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Background Color", "smile"),
								"param_name" => "icon_color_bg",
								"value" => "#ffffff",
								"description" => __("Select background color for icon.", "smile"),	
								"dependency" => Array("element" => "icon_style", "value" => array("circle","square","advanced")),
								"group" => "Select Icon"
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
								"group" => "Select Icon"
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Border Color", "smile"),
								"param_name" => "icon_color_border",
								"value" => "#333333",
								"description" => __("Select border color for icon.", "smile"),	
								"dependency" => Array("element" => "icon_border_style", "not_empty" => true),
								"group" => "Select Icon"
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
								"group" => "Select Icon"
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
								"group" => "Select Icon"
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
								"group" => "Select Icon"
							),
							array(
								"type" => "vc_link",
								"class" => "",
								"heading" => __("Link ","smile"),
								"param_name" => "icon_link",
								"value" => "",
								"description" => __("Add a custom link or select existing page. You can remove existing link as well.","smile"),
								"group" => "Other Settings"
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Animation","smile"),
								"param_name" => "icon_animation",
								"value" => array(
							 		__("No Animation","smile") => "",
									__("Swing","smile") => "swing",
									__("Pulse","smile") => "pulse",
									__("Fade In","smile") => "fadeIn",
									__("Fade In Up","smile") => "fadeInUp",
									__("Fade In Down","smile") => "fadeInDown",
									__("Fade In Left","smile") => "fadeInLeft",
									__("Fade In Right","smile") => "fadeInRight",
									__("Fade In Up Long","smile") => "fadeInUpBig",
									__("Fade In Down Long","smile") => "fadeInDownBig",
									__("Fade In Left Long","smile") => "fadeInLeftBig",
									__("Fade In Right Long","smile") => "fadeInRightBig",
									__("Slide In Down","smile") => "slideInDown",
									__("Slide In Left","smile") => "slideInLeft",
									__("Slide In Left","smile") => "slideInLeft",
									__("Bounce In","smile") => "bounceIn",
									__("Bounce In Up","smile") => "bounceInUp",
									__("Bounce In Down","smile") => "bounceInDown",
									__("Bounce In Left","smile") => "bounceInLeft",
									__("Bounce In Right","smile") => "bounceInRight",
									__("Rotate In","smile") => "rotateIn",
									__("Light Speed In","smile") => "lightSpeedIn",
									__("Roll In","smile") => "rollIn",
									),
								"description" => __("Like CSS3 Animations? We have several options for you!","smile"),
								"group" => "Other Settings"
						  	),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Tooltip", "smile"),
								"param_name" => "tooltip_disp",
								"value" => array(
									"None"=> "",
									"Tooltip from Left" => "left",
									"Tooltip from Right" => "right",
									"Tooltip from Top" => "top",
									"Tooltip from Bottom" => "bottom",
								),
								"description" => __("Select the tooltip position","smile"),
								"group" => "Other Settings"
							),							
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Tooltip Text", "smile"),
								"param_name" => "tooltip_text",
								"value" => "",
								"description" => __("Enter your tooltip text here.", "smile"),
								"dependency" => Array("element" => "tooltip_disp", "not_empty" => true),
								"group" => "Other Settings"
							),
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Custom CSS Class", "smile"),
								"param_name" => "el_class",
								"value" => "",
								"description" => __("Ran out of options? Need more styles? Write your own CSS and mention the class name here.", "smile"),
								"group" => "Select Icon"
							),
						),
					)
				);
			}
		}
		// Shortcode handler function for stats Icon
		function ultimate_icons_shortcode($atts,$content = null)
		{
			$align = $el_class = '';
			extract(shortcode_atts(array(
				'align' => '',
				'el_class' => ''
			),$atts));
			// enqueue js
			wp_enqueue_script('ultimate-custom');
			// enqueue css
			wp_enqueue_style('ultimate-animate');
			wp_enqueue_style('ultimate-style');
			wp_enqueue_script('aio-tooltip',plugins_url('../assets/js/',__FILE__).'tooltip.js',array('jquery'));
			wp_enqueue_style('aio-tooltip',plugins_url('../assets/css/',__FILE__).'tooltip.css');
			$output = '<div class="'.$align.' uavc-icons '.$el_class.'">';
			$output .= do_shortcode($content);
			$output .= '</div>';
			
			return $output;
		}
		
		function single_icon_shortcode($atts){
			
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
				'icon_link' => '',
				'icon_margin' => '',
				'icon_animation' => '',
				'tooltip_disp' => '',
				'tooltip_text' => '',
				'el_class'=>'',
			),$atts));
			if($icon_animation !== 'none')
			{
				$css_trans = 'data-animation="'.$icon_animation.'" data-animation-delay="03"';
			}
			$output = $style = $link_sufix = $link_prefix = $target = $href = $icon_align_style = '';
			$uniqid = uniqid();
			if($icon_link !== ''){
				$href = vc_build_link($icon_link);
				$target = (isset($href['target'])) ? "target='".$href['target']."'" : '';
				$link_prefix .= '<a class="aio-tooltip '.$uniqid.'" href = "'.$href['url'].'" '.$target.' data-toggle="tooltip" data-placement="'.$tooltip_disp.'" title="'.$tooltip_text.'">';
				$link_sufix .= '</a>';
			} else {
				if($tooltip_disp !== ""){
					$link_prefix .= '<span class="aio-tooltip '.$uniqid.'" href = "'.$href.'" '.$target.' data-toggle="tooltip" data-placement="'.$tooltip_disp.'" title="'.$tooltip_text.'">';
					$link_sufix .= '</span>';
				}
			}
						
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
				$style .= 'margin-right:'.$icon_margin.'px;';
			
			if($icon !== ""){
				$output .= "\n".$link_prefix.'<div class="aio-icon '.$icon_style.' '.$el_class.'" '.$css_trans.' style="'.$style.'">';				
				$output .= "\n\t".'<i class="'.$icon.'"></i>';	
				$output .= "\n".'</div>'.$link_sufix;
			}
			//$output .= do_shortcode($content);
			if($tooltip_disp !== ""){
				$output .= '<script>
					jQuery(function () {
						jQuery(".'.$uniqid.'").bsf_tooltip("hide");
					})
				</script>';
			}			
			return $output;
		}
	}
}
if(class_exists('Ultimate_Icons'))
{
	$Ultimate_Icons = new Ultimate_Icons;
}
//Extend WPBakeryShortCodesContainer class to inherit all required functionality
if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
    class WPBakeryShortCode_ultimate_icons extends WPBakeryShortCodesContainer {
    }
}
if ( class_exists( 'WPBakeryShortCode' ) ) {
    class WPBakeryShortCode_single_icon extends WPBakeryShortCode {
    }
}