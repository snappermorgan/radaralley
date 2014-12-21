<?php
/*
* Add-on Name: Interactive Banner - 3
*/
if(!class_exists('Ultimate_Interactive_Banner_3')) 
{
	class Ultimate_Interactive_Banner_3{
		function __construct(){
			add_action('admin_init',array($this,'banner_init'));
			add_shortcode('interactive_banner_3',array($this,'banner_shortcode'));
		}
		function banner_init(){
			if(function_exists('vc_map'))
			{
				vc_map(
					array(
					   "name" => __("Interactive Banner 3","smile"),
					   "base" => "interactive_banner_3",
					   "class" => "vc_interactive_icon",
					   "icon" => "vc_icon_interactive",
					   "category" => __("Ultimate VC Addons","smile"),
					   "description" => __("Displays the banner image with Information","smile"),
					   "params" => array(
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Title ","smile"),
								"param_name" => "banner_title",
								"admin_label" => true,
								"value" => "",
								"description" => __("Give a title to this banner","smile")
							),
							array(
								"type" => "textarea",
								"class" => "",
								"heading" => __("Description","smile"),
								"param_name" => "banner_desc",
								"value" => "",
								"description" => __("Text that comes on mouse hover.","smile")
							),
							array(
								"type" => "attach_image",
								"class" => "",
								"heading" => __("Banner Image","smile"),
								"param_name" => "banner_image",
								"value" => "",
								"description" => __("Upload the image for this banner","smile")
							),
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Button Text","smile"),
								"param_name" => "button_text",
								"admin_label" => true,
								"value" => "",
								//"description" => __("Give a title to this banner","smile")
							),
							array(
								"type" => "vc_link",
								"class" => "",
								"heading" => __("Link ","smile"),
								"param_name" => "button_link",
								"value" => "",
								"description" => __("Add link / select existing page to link to this banner","smile"),
							),
						),
					)
				);
			}
		}
		// Shortcode handler function for stats banner
		function banner_shortcode($atts)
		{
			wp_enqueue_style('utl-ib3-style',plugins_url('../assets/css/ib3-style.css',__FILE__));
			$output = '';
			
			extract(shortcode_atts( array(
				'banner_title' => '',
				'banner_desc' => '',
				'banner_image' => '',
				'button_text' => '',
				'button_link' => ''
			),$atts));
			
			$banner_image = wp_get_attachment_image($banner_image,'full',false,array('class'=>'ultb3-img'));
			
			$output .= '<div class="ultb3-box ultb3-img-left ultb3-hover-2" style="background-color: #efdfcf;">
				'.$banner_image.'
				<div class="ultb3-info">
					<div class="ultb3-title">'.$banner_title.'</div>
					<div class="ultb3-desc">'.$banner_desc.'</div>
					<a href="'.$button_link.'" class="ultb3-btn">'.$button_text.'</a>
				</div>
			</div>';
			
			return $output;
		}
	}
}
if(class_exists('Ultimate_Interactive_Banner_3'))
{
	$Ultimate_Interactive_Banner_3 = new Ultimate_Interactive_Banner_3;
}
