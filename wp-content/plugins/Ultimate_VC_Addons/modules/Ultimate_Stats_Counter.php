<?php
/*
* Add-on Name: Stats Counter for Visual Composer
* Add-on URI: http://dev.brainstormforce.com
*/
if(!class_exists('AIO_Stats_Counter'))
{
	class AIO_Stats_Counter
	{
		// constructor
		function __construct()
		{
			add_action('admin_init',array($this,'counter_init'));
			add_shortcode('stat_counter',array($this,'counter_shortcode'));
		}
		// initialize the mapping function
		function counter_init()
		{
			if(function_exists('vc_map'))
			{
				// map with visual
				vc_map(
					array(
					   "name" => __("Counter"),
					   "base" => "stat_counter",
					   "class" => "vc_stats_counter",
					   "icon" => "vc_icon_stats",
					   "category" => __("Ultimate VC Addons",'smile'),
					   "description" => __("Your milestones, achievements, etc.","smile"),
					   "params" => array(
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
								"description" => __("Click and select icon of your choice. If you can't find the one that suits for your purpose, you can <a href='admin.php' target='_blank'>add new here</a>.", "flip-box"),
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
								//"dependency" => Array("element" => "icon_type","value" => array("selector")),
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
									"Solid" => "solid",
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
								"min" => 0,
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
							 "type" => "dropdown",
							 "class" => "",
							 "heading" => __("Icon Position", "icon-box"),
							 "param_name" => "icon_position",
							 "value" => array(
							 		'Top' => 'top',
									'Right' => 'right',
									'Left' => 'left',	
							 		),							
							 "description" => __("Enter Position of Icon", "icon-box")
							 ),
						  array(
							 "type" => "textfield",
							 "class" => "",
							 "heading" => __("Counter Title ", "smile"),
							 "param_name" => "counter_title",
							 "admin_label" => true,
							 "value" => "",
							 "description" => __("Enter title for stats counter block", "smile")
						  ),
						  array(
							 "type" => "textfield",
							 "class" => "",
							 "heading" => __("Counter Value", "smile"),
							 "param_name" => "counter_value",
							 "value" => "1250",
							 "description" => __("Enter number for counter without any special character. You may enter a decimal number. Eg 12.76", "smile")
						  ),
						  array(
							 "type" => "textfield",
							 "class" => "",
							 "heading" => __("Thousands Separator", "smile"),
							 "param_name" => "counter_sep",
							 "value" => ",",
							 "description" => __("Enter character for thousanda separator. e.g. ',' will separate 125000 into 125,000", "smile")
						  ),
						  array(
							 "type" => "textfield",
							 "class" => "",
							 "heading" => __("Replace Decimal Point With", "smile"),
							 "param_name" => "counter_decimal",
							 "value" => ".",
							 "description" => __("Did you enter a decimal number (Eg - 12.76) The decimal point '.' will be replaced with value that you will enter above.", "smile"),
						  ),
						  array(
							 "type" => "textfield",
							 "class" => "",
							 "heading" => __("Counter Value Prefix", "smile"),
							 "param_name" => "counter_prefix",
							 "value" => "",
							 "description" => __("Enter prefix for counter value", "smile")
						  ),
						  array(
							 "type" => "textfield",
							 "class" => "",
							 "heading" => __("Counter Value Suffix", "smile"),
							 "param_name" => "counter_suffix",
							 "value" => "",
							 "description" => __("Enter suffix for counter value", "smile")
						  ),
						  array(
								"type" => "number",
								"class" => "",
								"heading" => __("Counter rolling time", "smile"),
								"param_name" => "speed",
								"value" => 3,
								"min" => 1,
								"max" => 10,
								"suffix" => "seconds",
								"description" => __("How many seconds the counter should roll?", "smile")
							),
						  array(
								"type" => "number",
								"class" => "",
								"heading" => __("Title Font Size", "smile"),
								"param_name" => "font_size_title",
								"value" => 18,
								"min" => 10,
								"max" => 72,
								"suffix" => "px",
								"description" => __("Enter value in pixels.", "smile")
							),
						  array(
								"type" => "number",
								"class" => "",
								"heading" => __("Counter Font Size", "smile"),
								"param_name" => "font_size_counter",
								"value" => 28,
								"min" => 12,
								"max" => 72,
								"suffix" => "px",
								"description" => __("Enter value in pixels.", "smile")
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Counter Text Color", "smile"),
								"param_name" => "counter_color_txt",
								"value" => "",
								"description" => __("Select text color for counter title and digits.", "smile"),	
							),
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Extra Class",  "smile"),
								"param_name" => "el_class",
								"value" => "",
								"description" => __("Add extra class name that will be applied to the icon process, and you can use this class for your customizations.",  "smile"),
							),
						),
					)
				);
			}
		}
		// Shortcode handler function for stats counter
		function counter_shortcode($atts)
		{
			// enqueue js
			wp_enqueue_script('ultimate-appear');
			wp_enqueue_script('ultimate-custom');
			// enqueue css
			wp_enqueue_style('ultimate-animate');
			wp_enqueue_style('ultimate-style');
			wp_enqueue_script('front-js',plugins_url('../assets/js/countUp.js',__FILE__));
			wp_enqueue_style('stats-counter-style',plugins_url('../assets/css/',__FILE__).'stats-counter.css');
			$icon_type = $icon_img = $img_width = $icon = $icon_color = $icon_color_bg = $icon_size = $icon_style = $icon_border_style = $icon_border_radius = $icon_color_border = $icon_border_size = $icon_border_spacing = $icon_link = $el_class = $icon_animation = $counter_title = $counter_value = $icon_position = $counter_style = $font_size_title = $font_size_counter = $counter_font = $title_font = $speed = $el_class = $counter_sep = $counter_suffix = $counter_prefix = $counter_decimal = $counter_color_txt = '';
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
				'counter_title' => '',
				'counter_value' => '',
				'counter_sep' => '',
				'counter_suffix' => '',
				'counter_prefix' => '',
				'counter_decimal' => '',
				'icon_position'=>'',
				'counter_style'=>'',
				'speed'=>'',
				'font_size_title' => '',
				'font_size_counter' => '',
				'counter_color_txt' => '',
				'el_class'=>'',
			),$atts));			 
			$class = $style = '';
			$stats_icon = do_shortcode('[just_icon icon_type="'.$icon_type.'" icon="'.$icon.'" icon_img="'.$icon_img.'" img_width="'.$img_width.'" icon_size="'.$icon_size.'" icon_color="'.$icon_color.'" icon_style="'.$icon_style.'" icon_color_bg="'.$icon_color_bg.'" icon_color_border="'.$icon_color_border.'"  icon_border_style="'.$icon_border_style.'" icon_border_size="'.$icon_border_size.'" icon_border_radius="'.$icon_border_radius.'" icon_border_spacing="'.$icon_border_spacing.'" icon_link="'.$icon_link.'" icon_animation="'.$icon_animation.'"]');
			if($counter_color_txt !== ''){
				$counter_color = 'color:'.$counter_color_txt.';';
			} else {
				$counter_color = '';
			}
			if($icon_color != '')
				$style.='color:'.$icon_color.';';
			if($icon_animation !== 'none')
			{
				$css_trans = 'data-animation="'.$icon_animation.'" data-animation-delay="03"';
			}
			$counter_font = 'font-size:'.$font_size_counter.'px;';
			$title_font = 'font-size:'.$font_size_title.'px;';
			if($counter_style !=''){
				$class = $counter_style;
				if(strpos($counter_style, 'no_bg')){
					$style.= "border:2px solid ".$counter_icon_bg_color.';';
				}
				elseif(strpos($counter_style, 'with_bg')){
					if($counter_icon_bg_color != '')
						$style.='background:'.$counter_icon_bg_color.';';
				}
			}
			if($el_class != '')
				$class.= ' '.$el_class;
			$ic_position = 'stats-'.$icon_position;
			$ic_class = 'aio-icon-'.$icon_position;
			$output = '<div class="stats-block '.$ic_position.' '.$class.'">';
				//$output .= '<div class="stats-icon" style="'.$style.'">
				//				<i class="'.$stats_icon.'"></i>
				//			</div>';
				$id = 'counter_'.uniqid();
				if($counter_sep == ""){
					$counter_sep = 'none';
				}
				if($counter_decimal == ""){
					$counter_decimal = 'none';
				}
				if($icon_position !== "right")
					$output .= '<div class="'.$ic_class.'">'.$stats_icon.'</div>';
				$output .= '<div class="stats-desc">';
					if($counter_prefix !== ''){
						$output .= '<div class="counter_prefix" style="'.$counter_font.'">'.$counter_prefix.'</div>';
					}
					$output .= '<div id="'.$id.'" data-id="'.$id.'" class="stats-number" style="'.$counter_font.' '.$counter_color.'" data-speed="'.$speed.'" data-counter-value="'.$counter_value.'" data-separator="'.$counter_sep.'" data-decimal="'.$counter_decimal.'">0</div>';
					if($counter_suffix !== ''){
						$output .= '<div class="counter_suffix" style="'.$counter_font.' '.$counter_color.'">'.$counter_suffix.'</div>';
					}
					$output .= '<div class="stats-text" style="'.$title_font.' '.$counter_color.'">'.$counter_title.'</div>';
				$output .= '</div>';
				if($icon_position == "right")
					$output .= '<div class="'.$ic_class.'">'.$stats_icon.'</div>';
			$output .= '</div>';				
			return $output;		
		}
	}
}
if(class_exists('AIO_Stats_Counter'))
{
	$AIO_Stats_Counter = new AIO_Stats_Counter;
}