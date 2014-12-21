<?php
/*
@Module: Single Product view
@Since: 1.0
@Package: WooComposer
*/
if(!class_exists('WooComposer_ViewProduct')){
	class WooComposer_ViewProduct
	{
		function __construct(){
			add_action('admin_init',array($this,'WooComposer_Init_Product'));
			add_shortcode('woocomposer_product',array($this,'WooComposer_Product'));
		} 
		function WooComposer_Init_Product(){
			if(function_exists('vc_map')){
				$params =
					array(
						"name"		=> __("Single Product [Beta]", "woocomposer"),
						"base"		=> "woocomposer_product",
						"icon"		=> "woo_product",
						"class"	   => "woo_product",
						"category"  => __("WooComposer [ Beta ]", "woocomposer"),
						"description" => "Display single product from list",
						"controls" => "full",
						"show_settings_on_create" => true,
						"params" => array(
							array(
								"type" => "product_search",
								"class" => "",
								"heading" => __("Select Product", "woocomposer"),
								"param_name" => "product_id",
								"admin_label" => true,
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Select Product Style", "woocomposer"),
								"param_name" => "product_style",
								"admin_label" => true,
								"value" => array(
										"Style 01" => "style01",
										"Style 02" => "style02",
										"Style 03" => "style03",
									),
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "chk-switch",
								"class" => "",
								"heading" => __("Select Options to Display", "woocomposer"),
								"param_name" => "options",
								"admin_label" => true,
								"value" => "",
								"options" => array(
										"category" => array(
													"label" => "Category",
													"on" => "Yes",
													"off" => "No",
												),
										"reviews" => array(
													"label" => "Reviews",
													"on" => "Yes",
													"off" => "No",
												),
										"quick" => array(
													"label" => "Quick View",
													"on" => "Yes",
													"off" => "No",
												),
										"description" => array(
													"label" => "Description",
													"on" => "Yes",
													"off" => "No",
												),
									),
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Product Text Alignment", "woocomposer"),
								"param_name" => "text_align",
								"value" => array(
									"Left"=> "left",
									"Center"=> "center",
									"Right" => "right",
								),
								"description" => __("","smile"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Sale Notification Label", "woocomposer"),
								"param_name" => "label_on_sale",
								"value" => "",
								"description" => __("Enter custom text for Product On Sale label. Default is - Sale!.", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Sale Notification Style", "woocomposer"),
								"param_name" => "on_sale_style",
								"admin_label" => true,
								"value" => array(
										"Circle" => "wcmp-sale-circle",
										"Rectangle" => "wcmp-sale-rectangle",
									),
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Sale Notification Alignment", "woocomposer"),
								"param_name" => "on_sale_alignment",
								"admin_label" => true,
								"value" => array(
										"Right" => "wcmp-sale-right",
										"Left" => "wcmp-sale-left",
									),
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Product Image Setting", "woocomposer"),
								"param_name" => "product_img_disp",
								"value" => array(
									"Display product featured image" => "single",
									"Display product gallery in carousel slider" => "carousel",
								),
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Image Hover Animation", "woocomposer"),
								"param_name" => "img_animate",
								"value" => array(
									"Rotate Clock"=> "rotate-clock",
									"Rotate Anti-clock"=> "rotate-anticlock",
									"Zoom-In" => "zoomin",
									"Zoom-Out" => "zoomout",
									"Fade" => "fade",
									"Gray Scale" => "grayscale",
									"Shadow" => "imgshadow",
									"Blur" => "blur",
									"Anti Grayscale" => "antigrayscale",
								),
								"description" => __("","smile"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Product Border Style", "woocomposer"),
								"param_name" => "border_style",
								"value" => array(
									"None"=> "",
									"Solid"=> "solid",
									"Dashed" => "dashed",
									"Dotted" => "dotted",
									"Double" => "double",
									"Inset" => "inset",
									"Outset" => "outset",
								),
								"description" => __("","smile"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Border Color", "woocomposer"),
								"param_name" => "border_color",
								"value" => "#333333",
								"description" => __("", "woocomposer"),	
								"dependency" => Array("element" => "border_style", "not_empty" => true),
								"group" => "Initial Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Border Width", "woocomposer"),
								"param_name" => "border_size",
								"value" => 1,
								"min" => 1,
								"max" => 10,
								"suffix" => "px",
								"description" => __("", "woocomposer"),
								"dependency" => Array("element" => "border_style", "not_empty" => true),
								"group" => "Initial Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Border Radius", "woocomposer"),
								"param_name" => "border_radius",
								"value" => 5,
								"min" => 1,
								"max" => 500,
								"suffix" => "px",
								"description" => __("", "woocomposer"),
								"dependency" => Array("element" => "border_style", "not_empty" => true),
								"group" => "Initial Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Product Title Color", "woocomposer"),
								"param_name" => "color_heading",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Categories Color", "woocomposer"),
								"param_name" => "color_categories",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Price Color", "woocomposer"),
								"param_name" => "color_price",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Star Ratings Color", "woocomposer"),
								"param_name" => "color_rating",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Star Rating Background Color", "woocomposer"),
								"param_name" => "color_rating_bg",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Quick View Icon Color", "woocomposer"),
								"param_name" => "color_quick",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Quick View Background Color", "woocomposer"),
								"param_name" => "color_quick_bg",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Cart Icon Color", "woocomposer"),
								"param_name" => "color_cart",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Cart Button Background Color", "woocomposer"),
								"param_name" => "color_cart_bg",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Sale Notification Text Color", "woocomposer"),
								"param_name" => "color_on_sale",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Sale Notification Background Color", "woocomposer"),
								"param_name" => "color_on_sale_bg",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Product Description Text Color", "woocomposer"),
								"param_name" => "color_product_desc",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Product Description Background Color", "woocomposer"),
								"param_name" => "color_product_desc_bg",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Product Title", "woocomposer"),
								"param_name" => "size_title",
								"value" => "",
								"min" => 10,
								"max" => 72,
								"suffix" => "px",
								"description" => __("", "woocomposer"),
								"group" => "Font Sizes",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Categories", "woocomposer"),
								"param_name" => "size_cat",
								"value" => "",
								"min" => 10,
								"max" => 72,
								"suffix" => "px",
								"description" => __("", "woocomposer"),
								"group" => "Font Sizes",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Price", "woocomposer"),
								"param_name" => "size_price",
								"value" => "",
								"min" => 10,
								"max" => 72,
								"suffix" => "px",
								"description" => __("", "woocomposer"),
								"group" => "Font Sizes",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Sale Notification", "woocomposer"),
								"param_name" => "sale_price",
								"value" => "",
								"min" => 10,
								"max" => 72,
								"suffix" => "px",
								"description" => __("", "woocomposer"),
								"group" => "Font Sizes",
							),
						)
					);
				vc_map($params);
			}
		} 
		function WooComposer_Product($atts){
			$product_style = '';
			extract(shortcode_atts(array(
				"product_style" => "",
			),$atts));
			
			$output = '';
			
			ob_start();
			$output .= '<div class="woocommerce woo-msg">';
			wc_print_notices();
			$output .= ob_get_clean();
			$output .= '</div>';
			
			$template = 'design-single-'.$product_style.'.php';
			require_once($template);
			$function = 'WooComposer_Single_'.$product_style;
			$output .= $function($atts);
			
			return $output;
						
		} 
	}
	new WooComposer_ViewProduct;
}