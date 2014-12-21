<?php
/*
@Module: List view
@Since: 1.0
@Package: WooComposer
*/
if(!class_exists('WooComposer_ViewCarousel')){
	class WooComposer_ViewCarousel
	{
		function __construct(){
			add_action('admin_init',array($this,'WooComposer_Init_Carousel'));
			add_shortcode('woocomposer_carousel',array($this,'WooComposer_Carousel'));
		} // end constructor
		function WooComposer_Init_Carousel(){
			if(function_exists('vc_map')){
				$categories = get_terms( 'product_cat', array(
					'orderby'    => 'count',
					'hide_empty' => 0,
				 ) );
				$cat_arr = array();
				if(is_array($categories)){
					foreach($categories as $cats){
						$cat_arr[$cats->name] = $cats->slug;
					}
				}
				vc_map(
					array(
						"name"		=> __("Products Carousel [Beta]", "woocommerce"),
						"base"		=> "woocomposer_carousel",
						"icon"		=> "woo_carousel",
						"class"	   => "woo_carousel",
						"category"  => __("WooComposer [ Beta ]", "woocommerce"),
						"description" => "Display products in carousel slider",
						"controls" => "full",
						"show_settings_on_create" => true,
						"params" => array(
							array(
								"type" => "woocomposer",
								"class" => "",
								"heading" => __("Query Builder", "woocomposer"),
								"param_name" => "shortcode",
								"value" => "",
								"module" => "grid",
								"labels" => array(
										"products_from"   => __("Display:","woocomposer"),
										"per_page"		=> __("How Many:","woocomposer"),
										"columns"		 => __("Columns:","woocomposer"),
										"order_by"		=> __("Order By:","woocomposer"),
										"order"		   => __("Display Order:","woocomposer"),
										"category" 		=> __("Category:","woocomposer"),
								),
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Display Style", "woocommerce"),
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
								"heading" => __("Select options to display", "woocomposer"),
								"param_name" => "display_elements",
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
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Sale Notification Label", "woocomposer"),
								"param_name" => "label_on_sale",
								"value" => "",
								"description" => __("", "woocomposer"),
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
										"Square" => "wcmp-sale-rectangle",
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
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Animation","smile"),
								"param_name" => "product_animation",
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
								"description" => __("", "woocomposer"),
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
								"heading" => __("Quick View Icon Background Color", "woocomposer"),
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
								"heading" => __("Cart Icon Background Color", "woocomposer"),
								"param_name" => "color_cart_bg",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Sale Notification Label Color", "woocomposer"),
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
								"group" => "Size Settings",
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
								"group" => "Size Settings",
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
								"group" => "Size Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Sale Notifications", "woocomposer"),
								"param_name" => "sale_price",
								"value" => "",
								"min" => 10,
								"max" => 72,
								"suffix" => "px",
								"description" => __("", "woocomposer"),
								"group" => "Size Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Slide to Scroll Setting ", "woocomposer"),
								"param_name" => "scroll_opts",
								"value" => array(
										"Auto" => "auto",
										"Custom" => "custom",
									),
								"description" => __("", "woocomposer"),
								"group" => "Carousel Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Number of Slides to Scroll", "woocomposer"),
								"param_name" => "slides_to_scroll",
								"value" => "1",
								"min" => 1,
								"max" => 10,
								"suffix" => "",
								"description" => __("The number of slides to move on transition", "woocomposer"),
								"dependency" => Array("element" => "scroll_opts", "value" => array("custom")),
								"group" => "Carousel Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Slide Scrolling Speed", "woocomposer"),
								"param_name" => "scroll_speed",
								"value" => "1000",
								"min" => 100,
								"max" => 10000,
								"suffix" => "ms",
								"description" => __("Slide transition duration (in ms)", "woocomposer"),
								"group" => "Carousel Settings",
							),
							array(
								"type" => "checkbox",
								"class" => "",
								"heading" => __("Advanced settings -", "woocomposer"),
								"param_name" => "advanced_opts",
								"value" => array(
										"Enable infinite scroll<br>" => "infinite",
										"Enable navigation dots<br>" => "dots",
										"Enable auto play" => "autoplay",
									),
								"description" => __("", "woocomposer"),
								"group" => "Carousel Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Autoplay Speed", "woocomposer"),
								"param_name" => "autoplay_speed",
								"value" => "500",
								"min" => 100,
								"max" => 10000,
								"suffix" => "ms",
								"description" => __("The amount of time (in ms) between each auto transition", "woocomposer"),
								"group" => "Carousel Settings",
								"dependency" => Array("element" => "advanced_opts", "value" => array("autoplay")),
							),
						)
					)
				);
			}
		} // end WooComposer_Init_Carousel
		function WooComposer_Carousel($atts){
			$product_style = $slides_to_scroll = $scroll_speed = $advanced_opts = $output = $autoplay_speed = $scroll_opts = '';
			extract(shortcode_atts(array(
				"product_style" => "",
				"slides_to_scroll" => "1",
				"scroll_speed" => "1000",
				"advanced_opts" => "",
				"autoplay_speed" => "500",
				"scroll_opts" => "",
			),$atts));
			
			$infinite = $autoplay = $dots = 'false';
			$advanced_opts = explode(",", $advanced_opts);
			if(in_array("infinite",$advanced_opts)){
				$infinite = 'true';
			}
			if(in_array("autoplay",$advanced_opts)){
				$autoplay = 'true';
			}
			if(in_array("dots",$advanced_opts)){
				$dots = 'true';
			}
			ob_start();
			$output .= '<div class="woocommerce">';
			wc_print_notices();
			$output .= ob_get_clean();
			$output .= '</div>';
			$uid = uniqid();
			
			$output .= '<div id="woo-carousel-'.$uid.'" class="woocomposer_carousel">';
			$template = 'design-loop-'.$product_style.'.php';
			require_once($template);
			$function = 'WooComposer_Loop_'.$product_style;
			$output .= $function($atts,'carousel');
			$output .= '</div>';
			$output .= '<script>
						jQuery(document).ready(function(){
							var columns = jQuery("#woo-carousel-'.$uid.' > .woocomposer").data("columns");
							var slides_scroll_opt = "'.$scroll_opts.'";
							var slides_to_scroll;
							if(slides_scroll_opt == "custom"){
								slides_to_scroll = '.$slides_to_scroll.';
							} else {
								slides_to_scroll = columns;
							}
							var inline_vc = jQuery(".woocomposer_carousel").find(".wcmp_vc_inline").length;
							
							if(inline_vc == 0){
								jQuery("#woo-carousel-'.$uid.' > .woocomposer").slick({
											infinite: '.$infinite.',
											slidesToShow: columns,
											slidesToScroll: slides_to_scroll,
											speed: '.$scroll_speed.',
											dots: '.$dots.',
											autoplay: '.$autoplay.',
											autoplaySpeed: '.$autoplay_speed.',
											responsive: [{
												breakpoint: 1024,
												settings: {
													slidesToShow: 3,
													slidesToScroll: 3,
													infinite: true,
													dots: true
												}
											}, {
												breakpoint: 600,
												settings: {
													slidesToShow: 2,
													slidesToScroll: 2
												}
											}, {
												breakpoint: 480,
												settings: {
													slidesToShow: 1,
													slidesToScroll: 1
												}
											}]
									});
							}
							
							var carousel_set = "{infinite: '.$infinite.',\
								slidesToShow: columns,\
								slidesToScroll: slides_to_scroll,\
								speed: '.$scroll_speed.',\
								dots: '.$dots.',\
								autoplay: '.$autoplay.',\
								autoplaySpeed: '.$autoplay_speed.',\
								responsive: [{\
									breakpoint: 1024,\
									settings: {\
										slidesToShow: 3,\
										slidesToScroll: 3,\
										infinite: true,\
										dots: true\
									}\
								}, {\
									breakpoint: 600,\
									settings: {\
										slidesToShow: 2,\
										slidesToScroll: 2\
									}\
								}, {\
									breakpoint: 480,\
									settings: {\
										slidesToShow: 1,\
										slidesToScroll: 1\
									}\
								}]\
							});}";
							jQuery("#woo-carousel-'.$uid.'").attr("data-slick", carousel_set);
						});
						jQuery(window).load(function(){
							//jQuery("[data-save=true]").trigger("click");
						});
				</script>';
			return $output;
						
		} // end WooComposer_Carousel
	}
	new WooComposer_ViewCarousel;
}