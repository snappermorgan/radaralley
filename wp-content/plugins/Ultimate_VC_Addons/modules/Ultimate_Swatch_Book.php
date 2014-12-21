<?php
/*
* Add-on Name: Swatch Book for Visual Composer
* Add-on URI: http://.brainstormforce.com/demos/ultimate/swatch-book
*/
if(!class_exists('Ultimate_Swatch_Book')){
	class Ultimate_Swatch_Book{
		var $swatch_trans_bg_img;
		var $swatch_width;
		var $swatch_height;
		function __construct(){
			add_action( 'admin_init', array($this, 'swatch_book_init'));
			//add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts') );
			if(function_exists('vc_is_inline')){
				if(!vc_is_inline()){
					add_shortcode( 'swatch_container', array($this, 'swatch_container' ) );
					add_shortcode( 'swatch_item', array($this, 'swatch_item' ) );
				}
			} else {
				add_shortcode( 'swatch_container', array($this, 'swatch_container' ) );
				add_shortcode( 'swatch_item', array($this, 'swatch_item' ) );
			}
		}
		
		/*function frontend_scripts($post){
			global $post;
			$count = 0;
			$content = $post->the_content;
			$shortcodes = array('swatch_container','swatch_item');
			foreach($shortcodes as $shortcode){
				if(has_shortcode($content, $shortcode)) {
					$count++;	
				}
			}
			if($count !== 0){
				wp_enqueue_script('modernizr-79639-js',plugins_url('../assets/js/modernizr.custom.js',__FILE__));
				wp_enqueue_script('swatchbook-js',plugins_url('../assets/js/jquery.swatchbook.js',__FILE__));
				wp_enqueue_style('swatchbook-css',plugins_url('../assets/css/swatchbook.css',__FILE__));
			}
		}*/
		
		function swatch_book_init(){
			if(function_exists('vc_map'))
			{
				vc_map(
					array(
						"name" => __("Swatch Book","smile"),
						"base" => "swatch_container",
						"class" => "vc_swatch_container",
						"icon" => "vc_swatch_container",
						"category" => __("Ultimate VC Addons","smile"),
						"as_parent" => array('only' => 'swatch_item'),
						"description" => __("Interactive swatch strips.","smile"),
						"content_element" => true,
						"show_settings_on_create" => true,
						"js_view" => 'VcColumnView',
						"params" => array(
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Swatch Book Style", "smile"),
								"param_name" => "swatch_style",
								"value" => array(
									"Style 1" => "style-1",
									"Style 2" => "style-2",
									"Style 3" => "style-3",
									"Style 4" => "style-4",
									"Style 5" => "style-5",
									"Custom Style" => "custom",
								),
								"description" => __("","smile"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Index of Center Strip", "smile"),
								"param_name" => "swatch_index_center",
								"value" => 1,
								"min" => 1,
								"max" => 100,
								"suffix" => "",
								"description" => __("The index of the “centered” item, the one that will have an angle of 0 degrees when the swatch book is opened", "smile"),
								"dependency" => Array("element" => "swatch_style", "value" => "custom"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Space Between Two Swatches", "smile"),
								"param_name" => "swatch_space_degree",
								"value" => 1,
								"min" => 1,
								"max" => 1000,
								"suffix" => "",
								"description" => __("The space between the items (in degrees)", "smile"),
								"dependency" => Array("element" => "swatch_style", "value" => "custom"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Transition Speed", "smile"),
								"param_name" => "swatch_trans_speed",
								"value" => 1,
								"min" => 1,
								"max" => 10000,
								"suffix" => "",
								"description" => __("The speed and transition timing functions", "smile"),
								"dependency" => Array("element" => "swatch_style", "value" => "custom"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Distance From Open Item To Its Next Sibling", "smile"),
								"param_name" => "swatch_distance_sibling",
								"value" => 1,
								"min" => 1,
								"max" => 10000,
								"suffix" => "",
								"description" => __("Distance From Opened item’s next siblings (neighbor : 4)", "smile"),
								"dependency" => Array("element" => "swatch_style", "value" => "custom"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "chk-switch",
								"class" => "",
								"heading" => __("Swatch book will be initially closed", "upb_parallax"),
								"param_name" => "swatch_init_closed",
								"value" => "",
								"options" => array(
										"closed" => array(
											"label" => "",
											"on" => "Yes",
											"off" => "No",
										)
									),
								"description" => "",
								"dependency" => Array("element" => "swatch_style", "value" => "custom"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Index of the item that will be opened initially", "smile"),
								"param_name" => "swatch_open_at",
								"value" => 1,
								"min" => 1,
								"max" => 100,
								"suffix" => "",
								"description" => __("", "smile"),
								"dependency" => Array("element" => "swatch_style", "value" => "custom"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Width", "smile"),
								"param_name" => "swatch_width",
								"value" => 130,
								"min" => 100,
								"max" => 1000,
								"suffix" => "",
								"description" => __("", "smile"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Height", "smile"),
								"param_name" => "swatch_height",
								"value" => 400,
								"min" => 100,
								"max" => 1000,
								"suffix" => "",
								"description" => __("", "smile"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "attach_image",
								"class" => "",
								"heading" => __("Background Transparent Pattern", "smile"),
								"param_name" => "swatch_trans_bg_img",
								"value" => "",
								"description" => "",
								"group" => "Initial Settings",
							),
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Main Strip Title Text", "smile"),
								"param_name" => "swatch_main_strip_text",
								"value" => "",
								"description" => "",
								"group" => "Initial Settings",
							),
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Main Strip Highlight Text", "smile"),
								"param_name" => "swatch_main_strip_highlight_text",
								"value" => "",
								"description" => "",
								"group" => "Initial Settings",
							),
							array(
								"type" => "ultimate_google_fonts",
								"heading" => __("Font Family", "smile"),
								"param_name" => "main_strip_font_family",
								"description" => __("Select the font of your choice. You can <a target='_blank' href='".admin_url('admin.php?page=ultimate-font-manager')."'>add new in the collection here</a>.", "smile"),
								"group" => "Advanced Settings",
							),
							array(
								"type" 		=> "ultimate_google_fonts_style",
								"heading" 	 =>	__("Font Style", "smile"),
								"param_name"  =>	"main_strip_font_style",
								"group" => "Advanced Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Main Strip Title Font Size", "smile"),
								"param_name" => "swatch_main_strip_font_size",
								"value" => 16,
								"min" => 1,
								"max" => 72,
								"suffix" => "px",
								"description" => __("", "smile"),
								"group" => "Advanced Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Main Strip Title Font Style", "smile"),
								"param_name" => "swatch_main_strip_font_style",
								"value" => array(
									"Normal" => "normal",
									"Bold" => "bold",
									"Italic" => "italic",
								),
								"description" => __("", "smile"),
								"group" => "Advanced Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Main Strip Title Color:", "smile"),
								"param_name" => "swatch_main_strip_color",
								"value" => "",
								"description" => "",
								"group" => "Advanced Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Main Strip Title Background Color:", "smile"),
								"param_name" => "swatch_main_strip_bg_color",
								"value" => "",
								"description" => "",
								"group" => "Advanced Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Main Strip Title Highlight Font Size", "smile"),
								"param_name" => "swatch_main_strip_highlight_font_size",
								"value" => 16,
								"min" => 1,
								"max" => 72,
								"suffix" => "px",
								"description" => __("", "smile"),
								"group" => "Advanced Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Main Strip Title Highlight Font Weight", "smile"),
								"param_name" => "swatch_main_strip_highlight_font_weight",
								"value" => array(
									"Normal" => "normal",
									"Bold" => "bold",
									"Italic" => "italic",
								),
								"description" => __("", "smile"),
								"group" => "Advanced Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Main Strip Title Highlight Color", "smile"),
								"param_name" => "swatch_main_strip_highlight_color",
								"value" => "",
								"description" => "",
								"group" => "Advanced Settings",
							),
						)
					)
				); // vc_map
				
				vc_map( 
					array(
						"name" => __("Swatch Book Item", "js_composer"),
						"base" => "swatch_item",
						"class" => "vc_swatch_item",
						"icon" => "vc_swatch_item",
						"content_element" => true,
						"as_child" => array('only' => 'swatch_container'),
						"params" => array(
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Strip Title Text", "smile"),
								"param_name" => "swatch_strip_text",
								"value" => "",
								"description" => "",
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
								"description" => __("Use an existing font icon</a> or upload a custom image.", "smile")
							),
							array(
								"type" => "icon_manager",
								"class" => "",
								"heading" => __("Select Icon ","smile"),
								"param_name" => "icon",
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
								"heading" => __("Icon Style", "smile"),
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
									"None" => "",
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
								"dependency" => Array("element" => "icon_style", "value" => array("advanced")),
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
								"type" => "number",
								"class" => "",
								"heading" => __("Strip Title Font Size", "smile"),
								"param_name" => "swatch_strip_font_size",
								"value" => 16,
								"min" => 1,
								"max" => 72,
								"suffix" => "px",
								"description" => __("", "smile"),
								"group" => "Advanced Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Strip Title Font Weight", "smile"),
								"param_name" => "swatch_strip_font_weight",
								"value" => array(
									"Normal" => "normal",
									"Bold" => "bold",
									"Italic" => "italic",
								),
								"description" => __("", "smile"),
								"group" => "Advanced Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Strip Title Color:", "smile"),
								"param_name" => "swatch_strip_font_color",
								"value" => "",
								"description" => "",
								"group" => "Advanced Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Strip Title Background Color:", "smile"),
								"param_name" => "swatch_strip_title_bg_color",
								"value" => "",
								"description" => "",
								"group" => "Advanced Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Strip Background Color:", "smile"),
								"param_name" => "swatch_strip_bg_color",
								"value" => "",
								"description" => "",
								"group" => "Advanced Settings",
							),
						)
					)
				); // vc_map
			}
		}
		
		function swatch_container($atts,$content=null){
			$swatch_style = $swatch_index_center = $swatch_space_degree = $swatch_trans_speed = $swatch_distance_sibling = $swatch_init_closed = $swatch_open_at
 = $swatch_width = $swatch_height = $swatch_trans_bg_img = $swatch_main_strip_text = $swatch_main_strip_highlight_text = $swatch_main_strip_font_size = $swatch_main_strip_font_style = $swatch_main_strip_color = $swatch_main_strip_highlight_font_size = $swatch_main_strip_highlight_font_weight = $swatch_main_strip_highlight_color = $swatch_main_strip_bg_color = $main_strip_font_family = $main_strip_font_style = '';
 
 			wp_enqueue_script('modernizr-79639-js',plugins_url('../assets/js/modernizr.custom.js',__FILE__));
			wp_enqueue_script('swatchbook-js',plugins_url('../assets/js/jquery.swatchbook.js',__FILE__));
			wp_enqueue_style('swatchbook-css',plugins_url('../assets/css/swatchbook.css',__FILE__));
 
			extract(shortcode_atts(array(
				'swatch_style' => '',
				'swatch_index_center' => '',
				'swatch_space_degree' => '',
				'swatch_trans_speed' => '',
				'swatch_distance_sibling' => '',
				'swatch_init_closed' => '',
				'swatch_open_at' => '',
				'swatch_width' => '',
				'swatch_height' => '',
				'swatch_trans_bg_img' => '',
				'swatch_main_strip_text' => '',
				'swatch_main_strip_highlight_text' => '',
				'swatch_main_strip_font_size' => '',
				'swatch_main_strip_font_style' => '',
				'swatch_main_strip_color' => '',
				'swatch_main_strip_highlight_font_size' => '',
				'swatch_main_strip_highlight_font_weight' => '',
				'swatch_main_strip_highlight_color' => '',
				'swatch_main_strip_bg_color' => '',
				'main_strip_font_family' => '',
				'main_strip_font_style' => '',
			),$atts));
			$output = $img = $style = $highlight_style = $main_style = '';
			$uid = uniqid();
			if($swatch_trans_bg_img !== ''){
				$img = wp_get_attachment_image_src( $swatch_trans_bg_img, 'large');
				$img = $img[0];
				$this->swatch_trans_bg_img = $swatch_trans_bg_img;
				$style .= 'background-image: url('.$img.');';
			}
			if($swatch_width !== ''){
				$style .= 'width:'.$swatch_width.'px;';
				$this->swatch_width = $swatch_width;
			}
			if($swatch_height !== ''){
				$style .= 'height:'.$swatch_height.'px;';
				$this->swatch_height = $swatch_height;
			}
			
			if($swatch_main_strip_highlight_font_size !== ''){
				$highlight_style .= 'font-size:'.$swatch_main_strip_highlight_font_size.'px;';
			}
			if($swatch_main_strip_highlight_font_weight !== ''){
				$highlight_style .= 'font-weight:'.$swatch_main_strip_highlight_font_weight.';';
			}
			if($swatch_main_strip_highlight_color !== ''){
				$highlight_style .= 'color:'.$swatch_main_strip_highlight_color.';';
			}
			
			if($main_strip_font_family != '')
			{
				$mhfont_family = get_ultimate_font_family($main_strip_font_family);
				$main_style .= 'font-family:\''.$mhfont_family.'\';';
			}
			$main_style .= get_ultimate_font_style($main_strip_font_style);

			if($swatch_main_strip_font_size !== ''){
				$main_style .= 'font-size:'.$swatch_main_strip_font_size.'px;';
			}
			if($swatch_main_strip_font_style !== ''){
				$main_style .= 'font-weight:'.$swatch_main_strip_font_style.';';
			}
			if($swatch_main_strip_color !== ''){
				$main_style .= 'color:'.$swatch_main_strip_color.';';
			}
			if($swatch_main_strip_bg_color !== ''){
				$main_style .= 'background:'.$swatch_main_strip_bg_color.';';
			}
			
			$output .= '<div id="ulsb-container-'.$uid.'" class="ulsb-container ulsb-'.$swatch_style.'" style="width:'.$swatch_width.'px; height:'.$swatch_height.'px;">';
			$output .= do_shortcode($content);
			$output .= '<div class="ulsb-strip highlight-strip" style="'.$style.'">';
			$output .= '<h4 class="strip_main_text" style="'.$main_style.'"><span>'.$swatch_main_strip_text.'</span></h4>';
			$output .= '<h5 class="strip_highlight_text" style="'.$highlight_style.'"><span>'.$swatch_main_strip_highlight_text.'</span></h5>';
			$output .= '</div>';
			$output .= '</div>';
			$output .= '<script type="text/javascript">
						jQuery(function() {';
			if($swatch_style == 'style-1'){
					$output .= 'jQuery( "#ulsb-container-'.$uid.'" ).swatchbook();';
			}
			if($swatch_style == 'style-2'){
					$output .= 'jQuery( "#ulsb-container-'.$uid.'" ).swatchbook( {
									angleInc : -10,
									proximity : -45,
									neighbor : -4,
									closeIdx : 11
								} );';
			}
			if($swatch_style == 'style-3'){
					$output .= 'jQuery( "#ulsb-container-'.$uid.'" ).swatchbook( {
									angleInc : 15,
									neighbor : 15,
									initclosed : true,
									closeIdx : 11
								} );';
			}
			if($swatch_style == 'style-4'){
					$output .= 'jQuery( "#ulsb-container-'.$uid.'" ).swatchbook( {
									speed : 500,
									easing : "ease-out",
									center : 7,
									angleInc : 14,
									proximity : 40,
									neighbor : 2
								} );';
			}
			if($swatch_style == 'style-5'){
					$output .= 'jQuery( "#ulsb-container-'.$uid.'" ).swatchbook( {	openAt : 0	} );';
			}
			if($swatch_style == 'custom'){
					$output .= 'jQuery( "#ulsb-container-'.$uid.'" ).swatchbook( {
									speed : '.$swatch_trans_speed.',
									easing : "ease-out",
									center : '.$swatch_index_center.',
									angleInc : '.$swatch_space_degree.',
									proximity : 40,
									neighbor : '.$swatch_distance_sibling.',
									openAt : '.$swatch_open_at.',
									closeIdx : '.$swatch_init_closed.'
								} );';
			}
			$output .= '});';
			$output .= 'jQuery(document).ready(function(e) {
						var ult_strip = jQuery(".highlight-strip");
						ult_strip.each(function(index, element) {
							var strip_main_text = jQuery(this).children(".strip_main_text").outerHeight();
							var height = '.$swatch_height.'-strip_main_text;
							jQuery(this).children(".strip_highlight_text").css("height",height);
						});
					});';
			$output .= '</script>';
			return $output;
		}
		
		function swatch_item($atts,$content=null){
			$icon_type = $icon_img = $img_width = $icon = $icon_color = $icon_color_bg = $icon_size = $icon_style = $icon_border_style = $icon_border_radius = $icon_color_border = $icon_border_size = $icon_border_spacing = $el_class = $icon_animation = $swatch_strip_font_size = $swatch_strip_font_weight =  $swatch_strip_font_color = $swatch_strip_bg_color = $swatch_strip_title_bg_color = '';
			extract(shortcode_atts(array(
				'swatch_strip_text' => '',
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
				'icon_animation' => '',
				'swatch_strip_font_size' => '',
				'swatch_strip_font_weight' => '',
				'swatch_strip_font_color' => '',
				'swatch_strip_bg_color' => '',
				'swatch_strip_title_bg_color' => ''
			),$atts));
			$output = '';
			$box_icon = do_shortcode('[just_icon icon_type="'.$icon_type.'" icon="'.$icon.'" icon_img="'.$icon_img.'" img_width="'.$img_width.'" icon_size="'.$icon_size.'" icon_color="'.$icon_color.'" icon_style="'.$icon_style.'" icon_color_bg="'.$icon_color_bg.'" icon_color_border="'.$icon_color_border.'"  icon_border_style="'.$icon_border_style.'" icon_border_size="'.$icon_border_size.'" icon_border_radius="'.$icon_border_radius.'" icon_border_spacing="'.$icon_border_spacing.'" icon_animation="'.$icon_animation.'"]');
			$style = '';
			if($this->swatch_trans_bg_img !== ''){
				$img = wp_get_attachment_image_src( $this->swatch_trans_bg_img, 'large');
				$img = $img[0];
				$style .= 'background-image: url('.$img.');';
			}
			if($swatch_strip_bg_color !== ''){
				$style .= 'background-color: '.$swatch_strip_bg_color.';';
			}
			if($this->swatch_width !== ''){
				$style .= 'width:'.$this->swatch_width.'px;';
			}
			if($this->swatch_height!== ''){
				$style .= 'height:'.$this->swatch_height.'px;';
			}
			$output .= '<div class="ulsb-strip" style="'.$style.'">';
        	$output .= '<span class="ulsb-icon">'.$box_icon.'</span>';
        	$output .= '<h4 style="color:'.$swatch_strip_font_color.'; background:'.$swatch_strip_title_bg_color.'; font-size:'.$swatch_strip_font_size.'px; font-style: '.$swatch_strip_font_weight.';"><span>'.$swatch_strip_text.'</span></h4>';
    		$output .= '</div>';
			return $output;
		}
	}
	new Ultimate_Swatch_Book;
}
if(class_exists('WPBakeryShortCodesContainer'))
{
	class WPBakeryShortCode_swatch_container extends WPBakeryShortCodesContainer {

	}
	class WPBakeryShortCode_swatch_item extends WPBakeryShortCode {

	}
}

