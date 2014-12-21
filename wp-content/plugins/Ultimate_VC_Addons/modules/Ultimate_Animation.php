<?php
/*
* Module - Animation Block
*/
if(!class_exists('Ultimate_Animation')){
	class Ultimate_Animation{
		function __construct(){
			add_shortcode('ult_animation_block',array($this,'animate_shortcode'));
			add_action('admin_init',array($this,'animate_shortcode_mapper'));
		}/* end constructor*/
		function animate_shortcode($atts, $content=null){
			wp_enqueue_script('ultimate-appear');
			wp_enqueue_script('ultimate-custom');
			wp_enqueue_style('ultimate-animate');
			$output = $animation = $opacity = $opacity_start_effect = $animation_duration = $animation_delay = $animation_iteration_count = $inline_disp = $el_class = '';
			$opacity_start_effect_data = '';
			extract(shortcode_atts(array(
				"animation" => "",
				"opacity" => "",
				"opacity_start_effect" => "",
				"animation_duration" => "",
				"animation_delay" => "",
				"animation_iteration_count" => "",
				"inline_disp" => "",
				"el_class" => "",
			),$atts));
			$style = $infi = $mobile_opt = '';
			$ultimate_animation = get_option('ultimate_animation');
			if($ultimate_animation == "enable"){
				$mobile_opt = 'ult-no-mobile';
			}

			if($inline_disp !== ''){
				$style .= 'display:inline-block;';
			}
			if($opacity == "set"){
				$style .= 'opacity:0;';
				$el_class .= 'ult-animate-viewport';
				$opacity_start_effect_data = 'data-opacity_start_effect="'.$opacity_start_effect.'"';
			}
			$inifinite_arr = array("InfiniteRotate", "InfiniteDangle","InfiniteSwing","InfinitePulse","InfiniteHorizontalShake","InfiniteBounce","InfiniteFlash",	"InfiniteTADA");
			if($animation_iteration_count == 0 || in_array($animation,$inifinite_arr)){
				$animation_iteration_count = 'infinite';
				$animation = 'infinite '.$animation;
			}
			$output .= '<div class="ult-animation '.$el_class.' '.$mobile_opt.'" data-animate="'.$animation.'" data-animation-delay="'.$animation_delay.'" data-animation-duration="'.$animation_duration.'" data-animation-iteration="'.$animation_iteration_count.'" style="'.$style.'" '.$opacity_start_effect_data.'>';
			$output .= do_shortcode($content);
			$output .= '</div>';
			return $output;
		} /* end animate_shortcode()*/
		function animate_shortcode_mapper(){
			if(function_exists('vc_map')){
				vc_map( 
					array(
						"name" => __("Animation Block", "js_composer"),
						"base" => "ult_animation_block",
						"icon" => "animation_block",
						"class" => "animation_block",
						"as_parent" => array('except' => 'ult_animation_block'),
						"content_element" => true,
						"controls" => "full",
						"show_settings_on_create" => true,
						"category" => "Ultimate VC Addons",
						"description" => "Apply animations everywhere.",
						"params" => array(
							// add params same as with any other content element
							array(
								"type" => "animator",
								"class" => "",
								"heading" => __("Animation","smile"),
								"param_name" => "animation",
								"value" => "",
								"description" => __("","smile"),
						  	),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Animation Duration","smile"),
								"param_name" => "animation_duration",
								"value" => 3,
								"min" => 1,
								"max" => 100,
								"suffix" => "s",
								"description" => __("","smile"),
						  	),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Animation Delay","smile"),
								"param_name" => "animation_delay",
								"value" => 1,
								"min" => 1,
								"max" => 100,
								"suffix" => "s",
								"description" => __("","smile"),
						  	),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Animation Iteration Count","smile"),
								"param_name" => "animation_iteration_count",
								"value" => 1,
								"min" => 0,
								"max" => 100,
								"suffix" => "",
								"description" => __("0 means infinite. Not necessary if infinite animation is selected.","smile"),
						  	),
							array(
								"type" => "chk-switch",
								"class" => "",
								"heading" => __("Viewport Effect", "woocomposer"),
								"param_name" => "opacity",
								"admin_label" => true,
								"value" => "",
								"options" => array(
										"set" => array(
												"label" => "If set to yes, block will appear with animation effect once user comes on the particular screen position.",
												"on" => "Yes",
												"off" => "No",
											),
									),
								"description" => __("", "woocomposer"),
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Viewport Position", "smile"),
								"param_name" => "opacity_start_effect",
								"suffix" => "%",
								//"admin_label" => true,
								"value" => "90",
								"description" => __("The area of screen from top where viewport effect will start to work.", "upb_parallax"),
							),
							/*array(
								"type" => "chk-switch",
								"class" => "",
								"heading" => __("Inline Content", "woocomposer"),
								"param_name" => "inline_disp",
								"admin_label" => true,
								"value" => "",
								"options" => array(
										"inline" => array(
												"label" => "If set to yes, 'display:inline-block' CSS property will be applied",
												"on" => "Yes",
												"off" => "No",
											),
									),
								"description" => __("", "woocomposer"),
							),*/
							array(
								"type" => "textfield",
								"heading" => __("Extra class name", "js_composer"),
								"param_name" => "el_class",
								"description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "js_composer")
							)
						),
						"js_view" => 'VcColumnView'
					)
				);/* end vc_map*/
			} /* end vc_map check*/
		}/*end animate_shortcode_mapper()*/
	} /* end class Ultimate_Animation*/
	// Instantiate the class
	new Ultimate_Animation;
	if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
		class WPBakeryShortCode_ult_animation_block extends WPBakeryShortCodesContainer {
		}
	}
}