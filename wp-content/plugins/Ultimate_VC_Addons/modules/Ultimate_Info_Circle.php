<?php
/*
* Add-on Name: Info Circle for Visual Composer 
* Add-on URI: http://dev.brainstormforce.com
*/
if(!class_exists('Ultimate_Info_Circle'))
{
	class Ultimate_Info_Circle
	{
		function __construct()
		{
			add_action('admin_init', array($this, 'add_info_circle'));
			add_shortcode( 'info_circle', array($this, 'info_circle' ) );
			add_shortcode( 'info_circle_item', array($this, 'info_circle_item' ) );
		}
		function info_circle($atts, $content = null)
		{
			// enqueue js
			wp_enqueue_script('ultimate-appear');
			wp_enqueue_script('ultimate-custom');
			// enqueue css
			wp_enqueue_style('ultimate-animate');
			wp_enqueue_style('ultimate-style');
			wp_enqueue_script('info-circle',plugins_url('../assets/js/',__FILE__).'info-circle.js');
			wp_enqueue_style('info-circle',plugins_url('../assets/css/',__FILE__).'info-circle.css');
			wp_enqueue_script('info-circle-ui-effect',plugins_url('../assets/js/',__FILE__).'jquery.ui.effect.js');			
			$edge_radius = $eg_padding = $circle_type = $icon_position = $eg_br_width = $eg_br_style = $eg_border_color = $cn_br_style = $highlight_style ='';
			$icon_size = $cn_br_width =$cn_border_color = $icon_diversion = $icon_show = $content_bg = $content_color = $el_class = '';
			$icon_launch = $icon_launch_duration = $icon_launch_delay ='';
			extract(shortcode_atts(array(
				'edge_radius' =>'',
				'circle_type' => '', 
				'icon_position' => '',
				'focus_on'=>'',
				'eg_br_width' => '',
				'eg_br_style' =>'',
				'eg_border_color' =>'',
				'cn_br_style' => '',
				'cn_br_width' => '',
				'cn_border_color' => '',
				'highlight_style'=>'',
				'icon_size' =>'',
				'eg_padding'=>'',
				'icon_diversion'=>'',
				'icon_show' =>'',
				'content_icon_size'=>'',
				'content_color'=>'',
				'content_bg'=>'',
				'responsive'=>'',
				'auto_slide'=>'',
				'auto_slide_duration'=>'',
				'icon_launch'=>'',
				'icon_launch_duration'=>'',
				'icon_launch_delay' =>'',
				'el_class' =>'',
			), $atts));
			$style = $style1 = $style3 = $ex_class ='';			
			if($eg_br_style!='none' && $eg_br_width!='' && $eg_border_color!=''){
				$style.='border:'.$eg_br_width.'px '.$eg_br_style.' '.$eg_border_color.';';				
			}
			if($cn_br_style!='none' && $cn_br_width!='' && $cn_border_color!=''){
				$style1.='border:'.$cn_br_width.'px '.$cn_br_style.' '.$cn_border_color.';';
			}			
			//$style .='border-style:'.$eg_br_style.';';
			$style1 .='background-color:'.$content_bg.';color:'.$content_color.';';
			$style1 .='width:'.$eg_padding.'%;height:'.$eg_padding.'%;margin:'.((100-$eg_padding)/2).'%;';
			if($el_class!='')
				$ex_class = $el_class;
			if($responsive=='on')
				$ex_class .= ' info-circle-responsive';			
			if($icon_show=='show'){
				$content_icon_size = $content_icon_size;
			}
			else{
				$content_icon_size='';
			}
			if($edge_radius!=''){
				$style .= 'width:'.$edge_radius.'%;';
			}
			$style .='opacity:0;';
			if($circle_type=='') $circle_type= 'info-c-full-br';
			$output ='<div class="info-circle-wrapper '.$ex_class.'">';
			$output .= '<div class="'.$circle_type.'" style=\''.$style.'\' data-divert="'.$icon_diversion.'" data-info-circle-angle="'.$icon_position.'" data-responsive-circle="'.$responsive.'" data-launch="'.$icon_launch.'" data-launch-duration="'.$icon_launch_duration.'" data-launch-delay="'.$icon_launch_delay.'" data-slide-true="'.$auto_slide.'" data-slide-duration="'.$auto_slide_duration.'" data-icon-size="'.$icon_size.'" data-icon-show="'.$icon_show.'" data-icon-show-size="'.$content_icon_size.'" data-highlight-style="'.$highlight_style.'" data-focus-on="'.$focus_on.'">';
			$output .= '<div class="icon-circle-list">';			
			//$content = str_replace('[info_circle_item', '[info_circle_item  icon_size="'.$icon_size.'"', $content);
			$output .= do_shortcode($content);
			if($icon_position!='full'){
				$output .='<div class="info-circle-icons suffix-remove"></div>';
			}
			$output .= '</div>';			
			$output .='<div class="info-c-full" style="'.$style1.'"><div class="info-c-full-wrap"></div>';
			$output .='</div>';
			$output .= '</div>';			
			if($responsive=='on'){
				$output .='<div class="smile_icon_list_wrap " data-content_bg="'.ultimate_hex2rgb($content_bg,0.8).'" data-content_color="'.$content_color.'">
							<ul class="smile_icon_list left circle with_bg">
								<li class="icon_list_item" style="font-size:'.($icon_size*3).'px;">
									<div class="icon_list_icon" style="font-size:'.$icon_size.'px;">
										<i class="smt-pencil"></i>
									</div>
									<div class="icon_description">
										<h3></h3>
										<p></p>
									</div>
									<div class="icon_list_connector" style="border-style:'.$eg_br_style.';border-color:'.$eg_border_color.'">
									</div>
								</li>
							</ul>
						</div>';
			}
			$output .='</div>';
			return $output;
		}
		function info_circle_item($atts,$content = null)
		{
			// Do nothing
			$info_title = $icon_type = $info_icon = $icon_color = $icon_bg_color = $info_img = $icon_type  = $contents = $radius = $icon_size = $icon_html = $style = $output = $style = '';
			extract(shortcode_atts(array(
				'info_title' => '',
				'icon_type' => '',
				'info_icon' => '',
				'icon_color' => '',
				'icon_bg_color' => '',
				'info_img' => '',
				'icon_type' => '',				
				'icon_br_style'=>'',				
				'icon_br_width'=>'',
				'icon_border_color'=>'',
				'contents' => '',
				'el_class' =>'',
			), $atts));					
			$icon_html = $output = '';
			if($icon_type == "selector"){						
				$icon_html .= '<i class="'.$info_icon.'" ></i>';
			} else {
				$img = wp_get_attachment_image_src( $info_img, 'large');				
				$icon_html .= '<img class="info-circle-img-icon" src="'.$img[0].'"/>';				
			}			
			if($icon_bg_color!=''){
				$style .='background:'.$icon_bg_color.';';				
			}
			if($icon_color!=''){
				$style .='color:'.$icon_color.';';
			}
			if($icon_br_style!='none' && $icon_br_width!='' && $icon_border_color!=''){
				$style.='border-style:'.$icon_br_style.';';
				$style.='border-width:'.$icon_br_width.'px;';
				$style.='border-color:'.$icon_border_color.';';
			}
			$output .= '<div class="info-circle-icons '.$el_class.'" style="'.$style.'">';			
			$output .= $icon_html;
			$output .="</div>";
			$output .='<div class="info-details">';		
			//$output .=$icon_html;
			$output .='<div class="info-circle-def"><div class="info-circle-sub-def">'.$icon_html.'<h3 class="info-circle-heading">'.$info_title.'</h3><div class="info-circle-text">'.do_shortcode($content).'</div></div></div></div>';
						//$output .= wpb_js_remove_wpautop($content, true);
			return $output;
		}
		function add_info_circle()
		{
			if(function_exists('vc_map'))
			{
				vc_map(
				array(
				   "name" => __("Info Circle","smile"),
				   "base" => "info_circle",
				   "class" => "vc_info_circle",
				   "icon" => "vc_info_circle",
				   "category" => __("Ultimate VC Addons","smile"),
				   "as_parent" => array('only' => 'info_circle_item'),
				   "description" => __("Infomraion Circle","smile"),
				   "content_element" => true,
				   "show_settings_on_create" => true,				   
				   "params" => array(
						/*array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Circle Type","smile"),
							"param_name" => "circle_type",
							"admin_label" => true,
							"value" => array(
								'Circle' => 'info-c-full-br',
								'Semi Circle' => 'info-c-semi-br',
								),
							"description" => __("Select the Circle Style.","smile")
						),
						*/
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Select area to display thumbnail icon","smile"),
							"param_name" => "icon_position",
							"value" => array(								
								'Complete' => 'full',
								'Top' => '180',
								'Right' => '270',
								'Left' => '90',
								'Bottom' => '0',
								),
							//"description" => __("Select area to display thumbnail icon .","smile")
						),
						/*array(
							"type" => "number",
							"class" => "",
							"heading" => __("Deviation", "smile"),
							"param_name" => "icon_diversion",
							"value" => 0,							
							"suffix" => "px",
							"description" => __("Deviation from initial point.", "smile"),
						),*/
						array(
							"type" => "number",
							"class" => "",
							"heading" => __("Size of Information Circle", "smile"),
							"param_name" => "edge_radius",
							"value" => 80,							
							"suffix" => "%",
							"description" => __("Size of circle relative to container width.", "smile"),
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Distance Between Thumbnail & Information Circle", "smile"),
							"param_name" => "eg_padding",
							"value" => array(
								"Extra large"=>"50",
								"Large"=>"60",
								"Medium"=>"70",
								"Small"=>"80",
							),							
							//"description" => __("Distance between Information Cirlce and Thumbnails.", "smile"),
						),						
						array(
							"type" => "number",
							"class" => "",
							"heading" => __("Thumbnail Icon Size", "smile"),
							"param_name" => "icon_size",
							"value" => 32,							
							"suffix" => "px",
							//"description" => __("Size of the thumbnails.", "smile"),
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Display Icon in Information Circle","smile"),
							"param_name" => "icon_show",
							"value" => array(								
								'Yes' => 'show',
								'No' => 'not-show',
								),
							"description" => __("Select whether you want to show icon in information circle.","smile")
						),
						array(
							"type" => "number",
							"class" => "",
							"heading" => __("Information Circle Icon Size", "smile"),
							"param_name" => "content_icon_size",
							"value" => "32",
							"suffix"=>"px",
							"dependency" => Array("element" => "icon_show","value" => array("show")),
							//"description" => __("Select the icon size inside information circle.", "smile"),	
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Thumbnail Connecting Line Style", "smile"),
							"param_name" => "eg_br_style",
							"value" => array(
								"None" => "none",
								"Solid"	=> "solid",
								"Dashed" => "dashed",
								"Dotted" => "dotted",
								/*"Double" => "double",
								"Inset" => "inset",
								"Outset" => "outset",*/
							),
							//"description" => __("Select the style for Thumbnail Connector.","smile"),							
						),
						array(
							"type" => "number",
							"class" => "",
							"heading" => __("Thumbnail Connecting Line Thickness", "smile"),
							"param_name" => "eg_br_width",
							"value" => 1,
							"min" => 0,
							"max" => 10,
							"suffix" => "px",
							//"description" => __("Thickness of the Thumbnail Connector line.", "smile"),
							"dependency" => Array("element" => "eg_br_style","value" => array("solid","dashed","dotted")),
						),
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Thumbnail Connecting Line Color", "smile"),
							"param_name" => "eg_border_color",
							"value" => "",
							//"description" => __("Select the color for thumbnail connector.", "smile"),
							"dependency" => Array("element" => "eg_br_style","value" => array("solid","dashed","dotted")),							
						),											
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Information Circle Border Style", "smile"),
							"param_name" => "cn_br_style",
							"value" => array(
								"None" => "none",
								"Solid"	=> "solid",
								"Dashed" => "dashed",
								"Dotted" => "dotted",
								"Double" => "double",
								"Inset" => "inset",
								"Outset" => "outset",
							),
							//"description" => __("Select the border style for information circle.","smile"),							
						),
						array(
							"type" => "number",
							"class" => "",
							"heading" => __("Information Circle Border Thickness", "smile"),
							"param_name" => "cn_br_width",
							"value" => 1,
							"min" => 0,
							"max" => 10,
							"suffix" => "px",
							//"description" => __("Thickness of information Cirlce border.", "smile"),	
							"dependency" => Array("element" => "cn_br_style","value" => array("solid","dashed","dotted","double","inset","outset")),
						),
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Information Circle Border color", "smile"),
							"param_name" => "cn_border_color",
							"value" => "",
							//"description" => __("Border color of information circle.", "smile"),	
							"dependency" => Array("element" => "cn_br_style","value" => array("solid","dashed","dotted","double","inset","outset")),
						),	
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Information Circle Background Color", "smile"),
							"param_name" => "content_bg",
							"value" => "",
							//"description" => __("Select the background color for information circle.", "smile"),							
						),
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Information Circle Text Color", "smile"),
							"param_name" => "content_color",
							"value" => "",
							//"description" => __("Select the text color for information circle.", "smile"),							
						),	
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Appear Information Circle on","smile"),
							"param_name" => "focus_on",
							"value" => array(								
								'Hover' => 'hover',
								'Click' => 'click',
								//	'None' => '',
								),
							"description" => __("Select on which event information should appear in information circle.","smile")
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Do you want to change information circle content automatically ?", "smile"),
							"param_name" => "auto_slide",
							"value" => array(								
								"No"	=> "off",
								"Yes" => "on",
							),
							//"description" => __("Select whether information will be shown into circle.","smile"),
						),
						array(
							"type" => "number",
							"class" => "",
							"heading" => __("Automatic Information Display Delay ", "smile"),
							"param_name" => "auto_slide_duration",
							"value" => 3,	
							"suffix" => "seconds",
							"description" => __("Duration before info circle should display next information on thumbnails.", "smile"),
							"dependency" => Array("element" => "auto_slide","value" => array("on")),
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Thumbnail Animation on Active", "smile"),
							"param_name" => "highlight_style",
							"value" => array(
								"None" =>'info-circle-highlight-style',
								//"Buzz Out"=>"info-circle-buzz-out",
								"Zoom InOut"=>"info-circle-pulse",
								"Zoom Out"=>"info-circle-push",
								"Zoom In"=>"info-circle-pop",
								//"Rotate"=>"info-circle-rotate",								
								),
							"description" => __("Select animation style for active thumbnails.", "smile"),
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Thumbnail Animation on Page Load", "smile"),
							"param_name" => "icon_launch",
							"value" => array(
								"None" =>'',
								"Linear"=>"linear",
								/*"Swing"=>"swing",
								"EaseInQuad"=>"easeInQuad",
								"EaseOutQuad"=>"easeOutQuad",
								"EaseInOutQuad"=>"easeInOutQuad",
								"EaseInCubic"=>"easeInCubic",
								"EaseOutCubic"=>"easeOutCubic",
								"EaseInOutCubic"=>"easeInOutCubic",
								"EaseInQuart"=>"easeInQuart",
								"EaseOutQuart"=>"easeOutQuart",
								"EaseInOutQuart"=>"easeInOutQuart",
								"EaseInQuint"=>"easeInQuint",
								"EaseOutQuint"=>"easeOutQuint",
								"EaseInOutQuint"=>"easeInOutQuint",
								"EaseInExpo"=>"easeInExpo",
								"EaseOutExpo"=>"easeOutExpo",
								"EaseInOutExpo"=>"easeInOutExpo",
								"EaseInSine"=>"easeInSine",
								"EaseOutSine"=>"easeOutSine",
								"EaseInOutSine"=>"easeInOutSine",
								"EaseInCirc"=>"easeInCirc",
								"EaseOutCirc"=>"easeOutCirc",
								"EaseInOutCirc"=>"easeInOutCirc",
								"EaseInElastic"=>"easeInElastic",*/
								"Elastic"=>"easeOutElastic",
								/*"EaseInOutElastic"=>"easeInOutElastic",
								"EaseInBack"=>"easeInBack",
								"EaseOutBack"=>"easeOutBack",
								"EaseInOutBack"=>"easeInOutBack",
								"EaseInBounce"=>"easeInBounce",*/
								"Bounce"=>"easeOutBounce",
								//"EaseInOutBounce"=>"easeInOutBounce",
								),
							"description" => __("Select Animation Style.", "smile"),
						),
						array(
							"type" => "number",
							"class" => "",
							"heading" => __("Animation Duration", "smile"),
							"param_name" => "icon_launch_duration",
							"value" => 1,							
							"suffix" => "seconds",
							"description" => __("Specify animation duration.", "smile"),
							"dependency" => Array("element" => "icon_launch","not_empty"=>true),
						),
						array(
							"type" => "number",
							"class" => "",
							"heading" => __("Animation Delay", "smile"),
							"param_name" => "icon_launch_delay",
							"value" => .2,							
							"suffix" => "seconds",
							"description" => __("Delay of animatin start in-between thumbnails.", "smile"),
							"dependency" => Array("element" => "icon_launch","not_empty"=>true),
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Responsive Nature", "smile"),
							"param_name" => "responsive",
							"value" => array(								
								'True' => 'on',
								'False' => 'off',
								),
							"description" => __("Select true to change its display style on low resolution.", "smile"),							
						),
						array(
							"type" => "textfield",
							"class" => "",
							"heading" => __("Extra Class", "smile"),
							"param_name" => "el_class",
							"value" => "",
							"description" => __("Custom class.", "smile"),							
						),						
					),
					"js_view" => 'VcColumnView',
				));
				// Add list item
				vc_map(
					array(
					   "name" => __("Info Circle Item"),
					   "base" => "info_circle_item",
					   "class" => "vc_info_circle_item",
					   "icon" => "vc_info_circle_item",
					   "category" => __("Ultimate VC Addons",'smile'),
					   "content_element" => true,
					   "as_child" => array('only' => 'info_circle'),
					   "params" => array(
						array(
							"type" => "textfield",
							"class" => "",
							"heading" => __("Title for Information Circle.","smile"),
							"param_name" => "info_title",
							"value" => "",
							"admin_label" => true,
							//"description" => __("Provide a title for this info circle item.","smile")
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Icon to display", "smile"),
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
							"heading" => __("Select Icon For Information Circle & Thumbnail ","smile"),
							"param_name" => "info_icon",
							"value" => "",
							"description" => __("Click and select icon of your choice. If you can't find the one that suits for your purpose, you can <a href='admin.php?page=font-icon-Manager' target='_blank'>add new here</a>.", "smile"),
							"dependency" => Array("element" => "icon_type","value" => array("selector")),
						),
						array(
							"type" => "attach_image",
							"class" => "",
							"heading" => __("Upload Image Icon", "smile"),
							"param_name" => "info_img",
							"admin_label" => true,
							"value" => "",
							"description" => __("Upload the custom image icon.", "smile"),
							"dependency" => Array("element" => "icon_type","value" => array("custom")),
						),
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Thumbnail Icon Background Color", "smile"),
							"param_name" => "icon_bg_color",
							"value" => "",
							"description" => __("Select the color for icon background.", "smile"),
						),
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Thumbnail Icon Color", "smile"),
							"param_name" => "icon_color",
							"value" => "",
							"description" => __("Select the color for icon.", "smile"),								
						),						
						array(
							"type" => "textarea_html",
							"class" => "",
							"heading" => __("Description for Information Circle","smile"),
							"param_name" => "content",
							"value" => "",
							//"description" => __("Description about this  item","smile")
						),
						array(
							"type" => "dropdown",
							"class" => "",
							"heading" => __("Thumbnail Icon Border Style", "smile"),
							"param_name" => "icon_br_style",
							"value" => array(
								"None" => "none",
								"Solid"	=> "solid",
								"Dashed" => "dashed",
								"Dotted" => "dotted",
								"Double" => "double",
								"Inset" => "inset",
								"Outset" => "outset",
							),
							//"description" => __("Select the border style for icon.","smile"),							
						),
						array(
							"type" => "number",
							"class" => "",
							"heading" => __("Thumbnail Icon Border Thickness", "smile"),
							"param_name" => "icon_br_width",
							"value" => 1,
							"min" => 0,
							"max" => 10,
							"suffix" => "px",
							//"description" => __("Thickness of the border.", "smile"),
							"dependency" => Array("element" => "icon_br_style","value" => array("solid","dashed","dotted","double","inset","outset")),
						),
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Thumbnail Icon Border Color", "smile"),
							"param_name" => "icon_border_color",
							"value" => "",
							//"description" => __("Select the color border.", "smile"),
							"dependency" => Array("element" => "icon_br_style","value" => array("solid","dashed","dotted","double","inset","outset")),		
						),
						array(
							"type" => "textfield",
							"class" => "",
							"heading" => __("Extra Class", "smile"),
							"param_name" => "el_class",
							"value" => "",
							"description" => __("Custom class.", "smile"),							
						),
					   )
					)
				);
			}//endif
		}
	}
}
if(class_exists('WPBakeryShortCodesContainer'))
{
	class WPBakeryShortCode_info_circle extends WPBakeryShortCodesContainer {
	}
	class WPBakeryShortCode_info_circle_item extends WPBakeryShortCode {
	}
}
if(class_exists('Ultimate_Info_Circle'))
{
	$Ultimate_Info_Circle = new Ultimate_Info_Circle;
}