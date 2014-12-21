<?php
/*
Plugin Name: Animation Block for Visual Composer
Plugin URI: https://www.brainstormforce.com/demos/ultimate/animation-block/
Author: Brainstorm Force
Author URI: https://www.brainstormforce.com
Version: 1.0.0
Description: Add stunning animation effects to eveything in Visual Composer
Text Domain: smile
*/
if(!class_exists('Ultimate_Animation')){
	class Ultimate_Animation{
		function __construct(){
			add_shortcode('ult_animation_block',array($this,'animate_shortcode'));
			add_action('admin_init',array($this,'animate_shortcode_mapper'));
			add_action('wp_enqueue_scripts',array($this,'front_scripts'));
			add_action('admin_enqueue_scripts',array($this,'admin_scripts'));
			// Generate param type "datetimepicker"
			if ( function_exists('add_shortcode_param'))
			{
				add_shortcode_param('animator' , array($this, 'animator_param')) ;
			}
			// Generate param type "number"
			if ( function_exists('add_shortcode_param'))
			{
				add_shortcode_param('number' , array(&$this, 'number_settings_field' ) );
			}
		}/* end constructor*/
		// Function generate param type "number"
		function number_settings_field($settings, $value)
		{
			$dependency = vc_generate_dependencies_attributes($settings);
			$param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
			$type = isset($settings['type']) ? $settings['type'] : '';
			$min = isset($settings['min']) ? $settings['min'] : '';
			$max = isset($settings['max']) ? $settings['max'] : '';
			$suffix = isset($settings['suffix']) ? $settings['suffix'] : '';
			$class = isset($settings['class']) ? $settings['class'] : '';
			$output = '<input type="number" min="'.$min.'" max="'.$max.'" class="wpb_vc_param_value ' . $param_name . ' ' . $type . ' ' . $class . '" name="' . $param_name . '" value="'.$value.'" style="max-width:100px; margin-right: 10px;" />'.$suffix;
			return $output;
		}
		function animator_param($settings, $value){
			$param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
			$type = isset($settings['type']) ? $settings['type'] : '';
			$class = isset($settings['class']) ? $settings['class'] : '';
			$json = file_get_contents(plugins_url('js/animate-config.json',__FILE__));
			$jsonIterator = new RecursiveIteratorIterator(
				new RecursiveArrayIterator(json_decode($json, TRUE)),
				RecursiveIteratorIterator::SELF_FIRST);
			
			$animators = '<select name="'.$param_name.'" class="wpb_vc_param_value ' . $param_name . ' ' . $type . ' ' . $class . '">';
			foreach ($jsonIterator as $key => $val) {
				if(is_array($val)) {
					$labels = str_replace('_',' ', $key);
					$animators .= '<optgroup label="'.ucwords($labels).'">';
				} else {
					if($key == $value)
						$animators .= "<option selected value=".$key.">".$key."</option>";
					else
						$animators .= "<option value=".$key.">".$key."</option>";
				}
			}
			$animators .= '<select>';
			
			$output = '';
			$output .= '<div class="select_anim" style="width: 45%; float: left;">';
			$output .= $animators;
			$output .= '</div>';
			$output .= '<div class="anim_prev" style=" padding: 8px; width: 45%; float: left; text-align: center; margin-left: 15px;"> <span id="animate-me" style="padding: 15px; background: #1C8FCF; color: #FFF;">Animation Preview</span></div>';
			$output .= '<script type="text/javascript">
					jQuery(document).ready(function(){
						var animator = jQuery(".'.$param_name.'");
						var anim_target = jQuery("#animate-me");
						animator.on("change",function(){
							var anim = jQuery(this).val();
							anim_target.removeClass().addClass(anim + " animated").one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function(){jQuery(this).removeClass();
							});
						});
					});
				</script>';
			return $output;
		}
		function front_scripts()
		{
			wp_enqueue_script('animate-js',plugins_url('/js/custom.js',__FILE__),array('jquery'),'1.0',true);
			wp_enqueue_style('animate',plugins_url('css/animate.css',__FILE__));
		}
		function admin_scripts()
		{
			// enqueue js files on backend
			wp_enqueue_style('animate-admin',plugins_url('css/animate.css',__FILE__));
			wp_enqueue_style('animate-admin-style',plugins_url('css/admin.css',__FILE__));
		}
		function animate_shortcode($atts, $content=null){
			$output = $animation = $opacity = $animation_duration = $animation_delay = $animation_iteration_count = $inline_disp = $el_class = '';
			extract(shortcode_atts(array(
				"animation" => "",
				"opacity" => "",
				"animation_duration" => "",
				"animation_delay" => "",
				"animation_iteration_count" => "",
				"inline_disp" => "",
				"el_class" => "",
			),$atts));
			$style = $infi = '';
			if($inline_disp !== ''){
				$style .= 'display:inline-block;';
			}
			if($opacity == "set"){
				$style .= 'opacity:0;';
				$el_class .= 'ult-animate-viewport';
			}
			$inifinite_arr = array("InfiniteRotate", "InfiniteDangle","InfiniteSwing","InfinitePulse","InfiniteHorizontalShake","InfiniteBounce","InfiniteFlash",	"InfiniteTADA");
			if($animation_iteration_count == 0 || in_array($animation,$inifinite_arr)){
				$animation_iteration_count = 'infinite';
				$animation = 'infinite '.$animation;
			}
			$output .= '<div class="ult-animation '.$el_class.'" data-animate="'.$animation.'" data-animation-delay="'.$animation_delay.'" data-animation-duration="'.$animation_duration.'" data-animation-iteration="'.$animation_iteration_count.'" style="'.$style.'">';
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
								"type" => "checkbox",
								"class" => "",
								"heading" => __("Viewport Effect", "woocomposer"),
								"param_name" => "opacity",
								"value" => array("If set to yes, block will appear with animation effect once user comes on the particular screen position." => "set"),
								"description" => __("", "woocomposer"),
							),
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