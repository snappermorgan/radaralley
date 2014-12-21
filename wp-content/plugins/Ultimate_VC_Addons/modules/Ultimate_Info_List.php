<?php
/*
* Add-on Name: Info List for Visual Composer
* Add-on URI: http://dev.brainstormforce.com
*/
if(!class_exists('AIO_Info_list'))
{
	class AIO_Info_list
	{
		var $connector_animate;
		var $connect_color;
		var $icon_font;
		var $border_col;
		var $icon_style;
		function __construct()
		{
			$this->connector_animate = '';
			$this->connect_color = '';
			$this->icon_style = '';
			$this->icon_style = '';
			add_action('admin_init', array($this, 'add_info_list'));
			if(function_exists('vc_is_inline')){
				if(!vc_is_inline()){
					add_shortcode( 'info_list', array($this, 'info_list' ) );
					add_shortcode( 'info_list_item', array($this, 'info_list_item' ) );
				}
			} else {
				add_shortcode( 'info_list', array($this, 'info_list' ) );
				add_shortcode( 'info_list_item', array($this, 'info_list_item' ) );
			}
		}
		function info_list($atts, $content = null)
		{
			// enqueue js
			wp_enqueue_script('ultimate-appear');
			wp_enqueue_script('ultimate-custom');
			// enqueue css
			wp_enqueue_style('ultimate-animate');
			wp_enqueue_style('ultimate-style');
			$position = $style = $icon_color = $icon_bg_color = $connector_animation = $font_size_icon = $icon_border_style = $icon_border_size = $connector_color = $border_color = $el_class = '';
			extract(shortcode_atts(array(
				'position' => '', 
				'style' => '',
				'connector_animation' => '',
				'icon_color' =>'',
				'icon_bg_color' =>'',
				'connector_color' => '',
				'border_color' => '',
				'font_size_icon' => '24',
				'icon_border_style' => '',
				'icon_border_size' => '',
				'el_class' => '',
			), $atts));
			$this->connect_color = $connector_color;
			$this->border_col = $border_color;
			if($style == 'square with_bg' || $style == 'circle with_bg' || $style == 'hexagon'){
				$this->icon_font = 'font-size:'.($font_size_icon*3).'px;';
				if($icon_border_size !== ''){
					$this->icon_style .= 'font-size:'.$font_size_icon.'px;';
					$this->icon_style .= 'border-width:0px;';
					$this->icon_style .= 'border-style:none;';
					$this->icon_style .= 'background:'.$icon_bg_color.';';
					$this->icon_style .= 'color:'.$icon_color.';';
					if($style =="hexagon")
						$this->icon_style .= 'border-color:'.$icon_bg_color.';';
					else
						$this->icon_style .= 'border-color:'.$border_color.';';
				}
			} else {
				$big_size = ($font_size_icon*3)+($icon_border_size*2);
				if($icon_border_size !== ''){
					$this->icon_font = 'font-size:'.$big_size.'px;';
					$this->icon_style .= 'font-size:'.$font_size_icon.'px;';
					$this->icon_style .= 'border-width:'.$icon_border_size.'px;';
					$this->icon_style .= 'border-style:'.$icon_border_style.';';
					$this->icon_style .= 'color:'.$icon_color.';';
					$this->icon_style .= 'border-color:'.$border_color.';';
				}
			}
			if($position == "top")
				$this->connector_animate = "fadeInLeft";
			else
				$this->connector_animate = $connector_animation;
			$output = '<div class="smile_icon_list_wrap '.$el_class.'">';
			$output .= '<ul class="smile_icon_list '.$position.' '.$style.'">';
			$output .= do_shortcode($content);
			$output .= '</ul>';
			$output .= '</div>';
			return $output;
		}
		function info_list_item($atts,$content = null)
		{
			// Do nothing
			$list_title = $list_icon = $animation = $icon_color = $icon_bg_color = $icon_img = $icon_type = '';
			extract(shortcode_atts(array(
				'list_title' => '',
				'animation' => '',
				'list_icon' => '',
				'icon_img' => '',
				'icon_type' => '',
			), $atts));
			//$content =  wpb_js_remove_wpautop($content);
			$css_trans = $style = $ico_col = $connector_trans = $icon_html = '';
			if($animation !== 'none')
			{
				$css_trans = 'data-animation="'.$animation.'" data-animation-delay="03"';
			}
			if($this->connector_animate)
			{
				$connector_trans = 'data-animation="'.$this->connector_animate.'" data-animation-delay="02"';
			}
			if($icon_color !=''){
				$ico_col = 'style="color:'.$icon_color.'";';
			}
			if($icon_bg_color != ''){
				$style .= 'background:'.$icon_bg_color.';  color:'.$icon_bg_color.';';	
			}
			if($icon_bg_color != ''){
				$style .= 'border-color:'.$this->border_col.';';
			}
			if($icon_type == "selector"){		
				$icon_html .= '<div class="icon_list_icon" '.$css_trans.' style="'.$this->icon_style.'">';
				$icon_html .= '<i class="'.$list_icon.'" '.$ico_col.'></i>';
				$icon_html .= '</div>';
			} else {
				$img = wp_get_attachment_image_src( $icon_img, 'large');
				$icon_html .= '<div class="icon_list_icon" '.$css_trans.' style="'.$this->icon_style.'">';
				$icon_html .= '<img class="list-img-icon" src="'.$img[0].'"/>';
				$icon_html .= '</div>';
			}
			$output = '<li class="icon_list_item" style=" '.$this->icon_font.'">';
			$output .= $icon_html;
			$output .= '<div class="icon_description">';
			$output .= '<h3>'.$list_title.'</h3>';
			$output .= '<span class="icon_description_text">'.wpb_js_remove_wpautop($content, true).'</span>';
			$output .= '</div>';
			$output .= '<div class="icon_list_connector" '.$connector_trans.' style="border-color:'.$this->connect_color.';"></div>';
			$output .= '</li>';
			return $output;
		}
	// Shortcode Functions for frontend editor
		function front_info_list($atts, $content = null)
		{
			// Do nothing
			$position = $style = $icon_color = $icon_bg_color = $connector_animation = $font_size_icon = $icon_border_style = $icon_border_size = $connector_color = $border_color = $el_class = '';
			extract(shortcode_atts(array(
				'position' => '', 
				'style' => '',
				'connector_animation' => '',
				'icon_color' =>'',
				'icon_bg_color' =>'',
				'connector_color' => '',
				'border_color' => '',
				'font_size_icon' => '24',
				'icon_border_style' => '',
				'icon_border_size' => '',
				'el_class' => '',
			), $atts));
			$this->connect_color = $connector_color;
			$this->border_col = $border_color;
			if($style == 'square with_bg' || $style == 'circle with_bg' || $style == 'hexagon'){
				$this->icon_font = 'font-size:'.($font_size_icon*3).'px;';
				if($icon_border_size !== ''){
					$this->icon_style = 'font-size:'.$font_size_icon.'px;';
					$this->icon_style .= 'border-width:0px;';
					$this->icon_style .= 'border-style:none;';
					$this->icon_style .= 'background:'.$icon_bg_color.';';
					$this->icon_style .= 'color:'.$icon_color.';';
					if($style =="hexagon")
						$this->icon_style .= 'border-color:'.$icon_bg_color.';';
					else
						$this->icon_style .= 'border-color:'.$border_color.';';
				}
			} else {
				$big_size = ($font_size_icon*3)+($icon_border_size*2);
				if($icon_border_size !== ''){
					$this->icon_font = 'font-size:'.$big_size.'px;';
					$this->icon_style = 'font-size:'.$font_size_icon.'px;';
					$this->icon_style .= 'border-width:'.$icon_border_size.'px;';
					$this->icon_style .= 'border-style:'.$icon_border_style.';';
					$this->icon_style .= 'color:'.$icon_color.';';
					$this->icon_style .= 'border-color:'.$border_color.';';
				}
			}
			if($position == "top")
				$this->connector_animate = "fadeInLeft";
			else
				$this->connector_animate = $connector_animation;
			$output = '<div class="smile_icon_list_wrap '.$el_class.'">';
			$output .= '<ul class="smile_icon_list '.$position.' '.$style.'" data-style="'.$this->icon_style.'" data-fonts="'.$this->icon_font.'" data-connector="'.$connector_color.'">';
			$output .= do_shortcode($content);
			$output .= '</ul>';
			$output .= '</div>';
			return $output;
		}
		function front_info_list_item($atts,$content = null)
		{
			// Do nothing
			$list_title = $list_icon = $animation = $icon_color = $icon_bg_color = $icon_img = $icon_type = '';
			extract(shortcode_atts(array(
				'list_title' => '',
				'animation' => '',
				'list_icon' => '',
				'icon_img' => '',
				'icon_type' => '',
			), $atts));
			//$content =  wpb_js_remove_wpautop($content);
			$css_trans = $style = $ico_col = $connector_trans = $icon_html = '';
			if($animation !== 'none')
			{
				$css_trans = 'data-animation="'.$animation.'" data-animation-delay="03"';
			}
			if($this->connector_animate)
			{
				$connector_trans = 'data-animation="'.$this->connector_animate.'" data-animation-delay="02"';
			}
			if($icon_color !=''){
				$ico_col = 'style="color:'.$icon_color.'";';
			}
			if($icon_bg_color != ''){
				$style .= 'background:'.$icon_bg_color.';  color:'.$icon_bg_color.';';	
			}
			if($icon_bg_color != ''){
				$style .= 'border-color:'.$this->border_col.';';
			}
			if($icon_type == "selector"){		
				$icon_html .= '<div class="icon_list_icon" '.$css_trans.'>';
				$icon_html .= '<i class="'.$list_icon.'" '.$ico_col.'></i>';
				$icon_html .= '</div>';
			} else {
				$img = wp_get_attachment_image_src( $icon_img, 'large');
				$icon_html .= '<div class="icon_list_icon" '.$css_trans.'>';
				$icon_html .= '<img class="list-img-icon" src="'.$img[0].'"/>';
				$icon_html .= '</div>';
			}
			$output = '<li class="icon_list_item">';
			$output .= $icon_html;
			$output .= '<div class="icon_description">';
			$output .= '<h3>'.$list_title.'</h3>';
			$output .= wpb_js_remove_wpautop($content, true);
			$output .= '</div>';
			$output .= '<div class="icon_list_connector" '.$connector_trans.' style="border-color:'.$this->connect_color.';"></div>';
			$output .= '</li>';
			return $output;
		}
		function add_info_list()
		{
			if(function_exists('vc_map'))
			{
				vc_map(
				array(
				   "name" => __("Info List","smile"),
				   "base" => "info_list",
				   "class" => "vc_info_list",
				   "icon" => "vc_icon_list",
				   "category" => __("Ultimate VC Addons","smile"),
				   "as_parent" => array('only' => 'info_list_item'),
				   "description" => __("Text blocks connected together in one list.","smile"),
				   "content_element" => true,
				   "show_settings_on_create" => true,
				   "params" => array(
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Icon or Image Position","smile"),
							"param_name" => "position",
							"value" => array(
								'Icon to the Left' => 'left',
								'Icon to the Right' => 'right',
								'Icon at Top' => 'top',
								),
							"description" => __("Select the icon position for icon list.","smile")
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Style of Image or Icon + Color","smile"),
							"param_name" => "style",
							"value" => array(
								'Square With Background' => 'square with_bg',
								'Square Without Background' => 'square no_bg',
								'Circle With Background' => 'circle with_bg',
								'Circle Without Background' => 'circle no_bg',
								'Hexagon With Background' => 'hexagon',
								),
							"description" => __("Select the icon style for icon list.","smile")
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Border Style", "smile"),
							"param_name" => "icon_border_style",
							"value" => array(
								"None" => "none",
								"Solid"	=> "solid",
								"Dashed" => "dashed",
								"Dotted" => "dotted",
								"Double" => "double",
								"Inset" => "inset",
								"Outset" => "outset",
							),
							"description" => __("Select the border style for icon.","smile"),
							"dependency" => Array("element" => "style", "value" => array("square no_bg","circle no_bg")),
						),
						array(
							"type" => "number",
							"class" => "",
							"heading" => __("Border Width", "smile"),
							"param_name" => "icon_border_size",
							"value" => 1,
							"min" => 0,
							"max" => 10,
							"suffix" => "px",
							"description" => __("Thickness of the border.", "smile"),
							"dependency" => Array("element" => "icon_border_style", "not_empty" => true),
						),
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Border Color:", "smile"),
							"param_name" => "border_color",
							"value" => "#333333",
							"description" => __("Select the color border.", "smile"),
							"dependency" => Array("element" => "icon_border_style", "not_empty" => true),								
						),
						array(
							"type" => "checkbox",
							"class" => "",
							"heading" => __("Connector Line Animation: ","smile"),
							"param_name" => "connector_animation",
							"value" => array (
							'Enabled' => 'fadeInUp',
							),
							"description" => __("Select wheather to animate connector or not","smile")
						),
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Connector Line Color:", "smile"),
							"param_name" => "connector_color",
							"value" => "#333333",
							"description" => __("Select the color for connector line.", "smile"),								
						),
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Icon Background Color:", "smile"),
							"param_name" => "icon_bg_color",
							"value" => "#ffffff",
							"description" => __("Select the color for icon background.", "smile"),								
						),
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Icon Color:", "smile"),
							"param_name" => "icon_color",
							"value" => "#333333",
							"description" => __("Select the color for icon.", "smile"),								
						),
						array(
							"type" => "number",
							"class" => "",
							"heading" => __("Icon Font Size", "smile"),
							"param_name" => "font_size_icon",
							"value" => 24,
							"min" => 12,
							"max" => 72,
							"suffix" => "px",
							"description" => __("Enter value in pixels.", "smile")
						),
						// Customize everything
						array(
							"type" => "textfield",
							"class" => "",
							"heading" => __("Extra Class", "smile"),
							"param_name" => "el_class",
							"value" => "",
							"description" => __("Add extra class name that will be applied to the info list, and you can use this class for your customizations.", "smile"),
						),
						array(
							"type" => "heading",
							"sub_heading" => "This is a global setting page for the whole \"Info List\" block. Add some \"Info List Items\" in the container row to make it complete. <a href=\"http://youtu.be/8N3LGsOloWA\" target=\"_blank\"> Learn how. </a>",
							"param_name" => "notification",
						),
					),
					"js_view" => 'VcColumnView'
				));
				// Add list item
				vc_map(
					array(
					   "name" => __("Info List Item"),
					   "base" => "info_list_item",
					   "class" => "vc_info_list",
					   "icon" => "vc_icon_list",
					   "category" => __("Ultimate VC Addons",'smile'),
					   "content_element" => true,
					   "as_child" => array('only' => 'info_list'),
					   "params" => array(
						array(
							"type" => "textfield",
							"class" => "",
							"heading" => __("Title","smile"),
							"admin_label" => true,
							"param_name" => "list_title",
							"value" => "",
							"description" => __("Provide a title for this icon list item.","smile")
						),
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
							"heading" => __("Select List Icon ","smile"),
							"param_name" => "list_icon",
							"value" => "",
							"description" => __("Click and select icon of your choice. If you can't find the one that suits for your purpose, you can <a href='admin.php?page=font-icon-Manager' target='_blank'>add new here</a>.", "smile"),
							"dependency" => Array("element" => "icon_type","value" => array("selector")),
						),
						array(
							"type" => "attach_image",
							"class" => "",
							"heading" => __("Upload Image Icon:", "smile"),
							"param_name" => "icon_img",
							"value" => "",
							"description" => __("Upload the custom image icon.", "smile"),
							"dependency" => Array("element" => "icon_type","value" => array("custom")),
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Icon Animation","smile"),
							"param_name" => "animation",
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
							"description" => __("Select the animation style for icon.","smile")
						),
						array(
							"type" => "textarea_html",
							"class" => "",
							"heading" => __("Description","smile"),
							"param_name" => "content",
							"value" => "",
							"description" => __("Description about this list item","smile")
						),
					   )
					) 
				);
			}//endif
		}
	}
}
global $AIO_Info_list; // WPB: Beter to create singleton in AIO_Info_list I think, but it also work
if(class_exists('WPBakeryShortCodesContainer'))
{
	class WPBakeryShortCode_info_list extends WPBakeryShortCodesContainer {
        function content( $atts, $content = null ) {
            global $AIO_Info_list;
            return $AIO_Info_list->front_info_list($atts, $content);
        }
	}
	class WPBakeryShortCode_info_list_item extends WPBakeryShortCode {
        function content($atts, $content = null ) {
            global $AIO_Info_list;
            return $AIO_Info_list->front_info_list_item($atts, $content);
        }
	}
}
if(class_exists('AIO_Info_list'))
{
	$AIO_Info_list = new AIO_Info_list;
}