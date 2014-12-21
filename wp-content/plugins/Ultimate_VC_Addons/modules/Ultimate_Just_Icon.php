<?php
/*
* Add-on Name: Just Icon for Visual Composer
* Add-on URI: http://dev.brainstormforce.com
*/
if(!class_exists('AIO_Just_Icon')) 
{
	class AIO_Just_Icon
	{
		function __construct()
		{
			add_action('admin_init',array($this,'just_icon_init'));
			add_shortcode('just_icon',array($this,'just_icon_shortcode'));
		}
		function just_icon_init()
		{
			if(function_exists('vc_map'))
			{
				vc_map(
					array(
					   "name" => __("Just Icon"),
					   "base" => "just_icon",
					   "class" => "vc_simple_icon",
					   "icon" => "vc_just_icon",
					   "category" => __("Ultimate VC Addons","smile"),
					   "description" => __("Add a simple icon and give some custom style.","smile"),
					   "params" => array(							
							// Play with icon selector
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Icon to display:", "smile"),
								"param_name" => "icon_type",
								"value" => array(
									"Font Icon Manager" => "selector",
									"Custom Image Icon" => "custom",
								),
								"description" => __("Use <a href='admin.php?page=font-icon-Manager' target='_blank'>existing font icon</a> or upload a custom image.", "smile")
							),
							array(
								"type" => "icon_manager",
								"class" => "",
								"heading" => __("Select Icon ","smile"),
								"param_name" => "icon",
								"value" => "",
								"description" => __("Click and select icon of your choice. If you can't find the one that suits for your purpose, you can <a href='admin.php?page=font-icon-Manager' target='_blank'>add new here</a>.", "flip-box"),
								"dependency" => Array("element" => "icon_type","value" => array("selector")),
							),
							array(
								"type" => "attach_image",
								"class" => "",
								"heading" => __("Upload Image Icon:", "smile"),
								"param_name" => "icon_img",
								"admin_label" => true,
								"value" => "",
								"description" => __("Upload the custom image icon.", "smile"),
								"dependency" => Array("element" => "icon_type","value" => array("custom")),
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Image Width", "smile"),
								"param_name" => "img_width",
								"value" => 48,
								"min" => 16,
								"max" => 512,
								"suffix" => "px",
								"description" => __("Provide image width", "smile"),
								"dependency" => Array("element" => "icon_type","value" => array("custom")),
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
								"dependency" => Array("element" => "icon_type","value" => array("selector")),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Color", "smile"),
								"param_name" => "icon_color",
								"value" => "#333333",
								"description" => __("Give it a nice paint!", "smile"),
								"dependency" => Array("element" => "icon_type","value" => array("selector")),						
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Icon or Image Style", "smile"),
								"param_name" => "icon_style",
								"value" => array(
									"Simple" => "none",
									"Circle Background" => "circle",
									"Square Background" => "square",
									"Design your own" => "advanced",
								),
								"description" => __("We have given three quick preset if you are in a hurry. Otherwise, create your own with various options.", "smile"),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Background Color", "smile"),
								"param_name" => "icon_color_bg",
								"value" => "#ffffff",
								"description" => __("Select background color for icon.", "smile"),	
								"dependency" => Array("element" => "icon_style", "value" => array("circle","square","advanced")),
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
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Border Color", "smile"),
								"param_name" => "icon_color_border",
								"value" => "#333333",
								"description" => __("Select border color for icon.", "smile"),	
								"dependency" => Array("element" => "icon_border_style", "not_empty" => true),
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
							),
							array(
								"type" => "vc_link",
								"class" => "",
								"heading" => __("Link ","smile"),
								"param_name" => "icon_link",
								"value" => "",
								"description" => __("Add a custom link or select existing page. You can remove existing link as well.","smile")
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
								"description" => __("Like CSS3 Animations? We have several options for you!","smile")
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
							),							
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Tooltip Text", "smile"),
								"param_name" => "tooltip_text",
								"value" => "",
								"description" => __("Enter your tooltip text here.", "smile"),
								"dependency" => Array("element" => "tooltip_disp", "not_empty" => true),
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Alignment", "smile"),
								"param_name" => "icon_align",
								"value" => array(
									"Center"	=>	"center",
									"Left"		=>	"left",
									"Right"		=>	"right"
								)
							),
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Custom CSS Class", "smile"),
								"param_name" => "el_class",
								"value" => "",
								"description" => __("Ran out of options? Need more styles? Write your own CSS and mention the class name here.", "smile"),
							),
						),
					)
				);
			}
		}
		// Shortcode handler function for stats Icon
		function just_icon_shortcode($atts)
		{
			// enqueue js
			wp_enqueue_script('ultimate-appear');
			wp_enqueue_script('ultimate-custom');
			// enqueue css
			wp_enqueue_style('ultimate-animate');
			wp_enqueue_style('ultimate-style');
			wp_enqueue_script('aio-tooltip',plugins_url('../assets/js/',__FILE__).'tooltip.js',array('jquery'));
			wp_enqueue_style('aio-tooltip',plugins_url('../assets/css/',__FILE__).'tooltip.css');
			$icon_type = $icon_img = $img_width = $icon = $icon_color = $icon_color_bg = $icon_size = $icon_style = $icon_border_style = $icon_border_radius = $icon_color_border = $icon_border_size = $icon_border_spacing = $icon_link = $el_class = $icon_animation =  $tooltip_disp = $tooltip_text = $icon_align = '';
			extract(shortcode_atts( array(				
				'icon_type' => '',
				'icon' => '',
				'icon_img' => '',
				'img_width' => '',
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
				'icon_animation' => '',
				'tooltip_disp' => '',
				'tooltip_text' => '',
				'el_class'=>'',
				'icon_align' => ''
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
			
			/* position fix */
			if($icon_align == 'right')
				$icon_align_style .= 'text-align:right;';
			elseif($icon_align == 'center')
				$icon_align_style .= 'text-align:center;';
			elseif($icon_align == 'left')
				$icon_align_style .= 'text-align:left;';
			
			if($icon_type == 'custom'){
				$img = wp_get_attachment_image_src( $icon_img, 'large');
				$alt = get_post_meta($icon_img, '_wp_attachment_image_alt', true);
				if($icon_style !== 'none'){
					if($icon_color_bg !== '')
						$style .= 'background:'.$icon_color_bg.';';
				}
				if($icon_style == 'circle'){
					$el_class.= ' uavc-circle ';
				}
				if($icon_style == 'square'){
					$el_class.= ' uavc-square ';
				}
				if($icon_style == 'advanced' && $icon_border_style !== '' ){
					$style .= 'border-style:'.$icon_border_style.';';
					$style .= 'border-color:'.$icon_color_border.';';
					$style .= 'border-width:'.$icon_border_size.'px;';
					$style .= 'padding:'.$icon_border_spacing.'px;';
					$style .= 'border-radius:'.$icon_border_radius.'px;';
				}
				if(!empty($img[0])){
					$output .= "\n".$link_prefix.'<div class="aio-icon-img '.$el_class.'" style="font-size:'.$img_width.'px;'.$style.'" '.$css_trans.'>';
					$output .= "\n\t".'<img class="img-icon" alt="'.$alt.'" src="'.$img[0].'"/>';	
					$output .= "\n".'</div>'.$link_sufix;
				}
				$output = $output;
			} else {
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
				if($icon_align !== 'left'){
					$style .= 'display:inline-block;';
				}
				if($icon !== ""){
					$output .= "\n".$link_prefix.'<div class="aio-icon '.$icon_style.' '.$el_class.'" '.$css_trans.' style="'.$style.'">';				
					$output .= "\n\t".'<i class="'.$icon.'"></i>';	
					$output .= "\n".'</div>'.$link_sufix;
				}
				$output = $output;
			}
			if($tooltip_disp !== ""){
				$output .= '<script>
					jQuery(function () {
						jQuery(".'.$uniqid.'").bsf_tooltip("hide");
					})
				</script>';
			}
			/* alignment fix */
			if($icon_align_style !== ''){
				$output = '<div class="align-icon" style="'.$icon_align_style.'">'.$output.'</div>';
			}
			
			return $output;
		}
	}
}
if(class_exists('AIO_Just_Icon'))
{
	$AIO_Just_Icon = new AIO_Just_Icon;
}