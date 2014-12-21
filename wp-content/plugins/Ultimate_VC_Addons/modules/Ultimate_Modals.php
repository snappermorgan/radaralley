<?php
/*
* Add-on Name: Ultimate Modals
* Add-on URI: https://www.brainstormforce.com
*/
if(!class_exists('Ultimate_Modals'))
{
	class Ultimate_Modals
	{
		function __construct()
		{
			// Add shortcode for modal popup
			add_shortcode('ultimate_modal', array(&$this, 'modal_shortcode' ) );
			// Initialize the modal popup component for Visual Composer
			add_action('admin_init', array( &$this, 'ultimate_modal_init' ) );
		}
		// Add shortcode for icon-box
		function modal_shortcode($atts, $content = null)
		{
			$row_setting = '';
			// enqueue js
			wp_enqueue_script('ultimate-appear');			
			wp_enqueue_script('ultimate-custom');
			// enqueue css
			wp_enqueue_style('ultimate-animate');
			wp_enqueue_style('ultimate-style');
			wp_enqueue_script('ultimate-modernizr',plugins_url('../assets/js/',__FILE__).'modernizr.custom.js','1.0',array('jquery'));
			wp_enqueue_script('ultimate-classie',plugins_url('../assets/js/',__FILE__).'classie.js','1.0',array('jquery'));
			wp_enqueue_script('ultimate-snap-svg',plugins_url('../assets/js/',__FILE__).'snap.svg-min.js','1.0',array('jquery'));
			wp_enqueue_script('ultimate-frongloop',plugins_url('../assets/js/',__FILE__).'froogaloop2.min.js','1.0',array('jquery'),true);
			wp_enqueue_script('ultimate-modal',plugins_url('../assets/js/',__FILE__).'modal.js','1.0',array('jquery'),true);
			wp_enqueue_style('ultimate-modal',plugins_url('../assets/css/',__FILE__).'modal.css');
			$icon = $modal_on = $modal_contain = $btn_size = $btn_bg_color = $btn_txt_color = $btn_text = $read_text = $txt_color = $modal_title = $modal_size = $el_class = $modal_style = $icon_type = $icon_img = $btn_img = $overlay_bg_color = $overlay_bg_opacity = $modal_on_align = $content_bg_color = $content_text_color = $header_bg_color = $header_text_color = $modal_border_style = $modal_border_width = $modal_border_color = $modal_border_radius = '';
			extract(shortcode_atts(array(
				'icon_type' => '',
				'icon' => '',
				'icon_img' => '',
				'modal_on' => '',
				'modal_contain' => '',
				'onload_delay'=>'',
				'btn_size' => '',
				'overlay_bg_color' => '',
				'overlay_bg_opacity' => '80',
				'btn_bg_color' => '',
				'btn_txt_color' => '',
				'btn_text' => '',				
				'read_text' => '',
				'txt_color' => '',
				'btn_img' => '',
				'modal_title' => '',
				'modal_size' => '',
				'modal_style' => '',
				'content_bg_color' => '',
				'content_text_color' => '',
				'header_bg_color' => '',
				'header_text_color' => '',
				'modal_on_align' => '',
				'modal_border_style' => '',
				'modal_border_width' => '',
				'modal_border_color' => '',
				'modal_border_radius' => '',
				'el_class' => '',
				),$atts,'ultimate_modal'));
			$html = $style = $box_icon = $modal_class = $modal_data_class = $uniq = $overlay_bg = $content_style = $header_style = $border_style = '';
			if($modal_on == "ult-button"){
				$modal_on = "button";
			}
			// Create style for content background color
			if($content_bg_color !== '')
				$content_style .= 'background:'.$content_bg_color.';';
			// Create style for content text color
			if($content_text_color !== '')
				$content_style .= 'color:'.$content_text_color.';';
			// Create style for header background color
			if($header_bg_color !== '')
				$header_style .= 'background:'.$header_bg_color.';';
			// Create style for header text color
			if($header_text_color !== '')
				$header_style .= 'color:'.$header_text_color.';';
			if($modal_border_style !== ''){
				$border_style .= 'border-style:'.$modal_border_style.';';
				$border_style .= 'border-width:'.$modal_border_width.'px;';
				$border_style .= 'border-radius:'.$modal_border_radius.'px;';
				$border_style .= 'border-color:'.$modal_border_color.';';
				$header_style .= 'border-color:'.$modal_border_color.';';
			}
			$overlay_bg_opacity = ($overlay_bg_opacity/100);
			if($overlay_bg_color !== ''){
				if(strlen($overlay_bg_color) <= 7)
					$overlay_bg = ultimate_hex2rgb($overlay_bg_color,$overlay_bg_opacity);
				else
					$overlay_bg = $overlay_bg_color;
					
				if($modal_style != 'overlay-show-cornershape' && $modal_style != 'overlay-show-genie' && $modal_style != 'overlay-show-boxes'){
					$overlay_bg = 'background:'.$overlay_bg.';';
				} else {
					$overlay_bg = 'fill:'.$overlay_bg.';';
				}
			}
			$uniq = uniqid();
			if($icon_type == 'custom'){
				$ico_img = wp_get_attachment_image_src( $icon_img, 'large');
				$box_icon = '<div class="modal-icon"><img src="'.$ico_img[0].'" class="ult-modal-inside-img"></div>';
			} elseif($icon_type == 'selector'){
				if($icon !== '')
					$box_icon = '<div class="modal-icon"><i class="'.$icon.'"></i></div>';
			}
			if($modal_style != 'overlay-show-cornershape' && $modal_style != 'overlay-show-genie' && $modal_style != 'overlay-show-boxes'){
				$modal_class = 'overlay-show';
				$modal_data_class = 'data-overlay-class="'.$modal_style.'"';
			} else {
				$modal_class = $modal_style;
				$modal_data_class = '';
			}
			if($modal_on == "button"){
				if($btn_bg_color !== ''){
					$style .= 'background:'.$btn_bg_color.';';
					$style .= 'border-color:'.$btn_bg_color.';';
				}
				if($btn_txt_color !== ''){
					$style .= 'color:'.$btn_txt_color.';';
				}
				$html .= '<button style="'.$style.'" data-class-id="content-'.$uniq.'" class="btn btn-primary btn-'.$btn_size.' '.$modal_class.' ult-align-'.$modal_on_align.'" '.$modal_data_class.'>'.$btn_text.'</button>';
			} elseif($modal_on == "image"){
				if($btn_img !==''){
					$img = wp_get_attachment_image_src( $btn_img, 'large');
					$html .= '<img src="'.$img[0].'" data-class-id="content-'.$uniq.'" class="ult-modal-img '.$modal_class.' ult-align-'.$modal_on_align.'" '.$modal_data_class.'/>';
				}
			} 
			elseif($modal_on == "onload"){				
				$html .= '<div data-class-id="content-'.$uniq.'" class="ult-onload '.$modal_class.' " '.$modal_data_class.' data-onload-delay="'.$onload_delay.'"></div>';				
			} 
			else {
				if($txt_color !== ''){
					$style .= 'color:'.$txt_color.';';
					$style .= 'cursor:pointer;';
				}
				$html .= '<span style="'.$style.'" data-class-id="content-'.$uniq.'" class="'.$modal_class.' ult-align-'.$modal_on_align.'" '.$modal_data_class.'>'.$read_text.'</span>';
			}
			if($modal_style == 'overlay-show-cornershape') {
				$html .= "\n".'<div class="ult-overlay overlay-cornershape content-'.$uniq.' '.$el_class.'" style="display:none" data-class="content-'.$uniq.'" data-path-to="m 0,0 1439.999975,0 0,805.99999 -1439.999975,0 z">';
            	$html .= "\n\t".'<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 1440 806" preserveAspectRatio="none">
                					<path class="overlay-path" d="m 0,0 1439.999975,0 0,805.99999 0,-805.99999 z" style="'.$overlay_bg.'"/>
            					</svg>';
			} elseif($modal_style == 'overlay-show-genie') {
				$html .= "\n".'<div class="ult-overlay overlay-genie content-'.$uniq.' '.$el_class.'" style="display:none" data-class="content-'.$uniq.'" data-steps="m 701.56545,809.01175 35.16718,0 0,19.68384 -35.16718,0 z;m 698.9986,728.03569 41.23353,0 -3.41953,77.8735 -34.98557,0 z;m 687.08153,513.78234 53.1506,0 C 738.0505,683.9161 737.86917,503.34193 737.27015,806 l -35.90067,0 c -7.82727,-276.34892 -2.06916,-72.79261 -14.28795,-292.21766 z;m 403.87105,257.94772 566.31246,2.93091 C 923.38284,513.78233 738.73561,372.23931 737.27015,806 l -35.90067,0 C 701.32034,404.49318 455.17312,480.07689 403.87105,257.94772 z;M 51.871052,165.94772 1362.1835,168.87863 C 1171.3828,653.78233 738.73561,372.23931 737.27015,806 l -35.90067,0 C 701.32034,404.49318 31.173122,513.78234 51.871052,165.94772 z;m 52,26 1364,4 c -12.8007,666.9037 -273.2644,483.78234 -322.7299,776 l -633.90062,0 C 359.32034,432.49318 -6.6979288,733.83462 52,26 z;m 0,0 1439.999975,0 0,805.99999 -1439.999975,0 z">';
				$html .= "\n\t".'<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 1440 806" preserveAspectRatio="none">
							<path class="overlay-path" d="m 701.56545,809.01175 35.16718,0 0,19.68384 -35.16718,0 z" style="'.$overlay_bg.'"/>
						</svg>';
			} elseif($modal_style == 'overlay-show-boxes') {
				$html .= "\n".'<div class="ult-overlay overlay-boxes content-'.$uniq.' '.$el_class.'" style="display:none" data-class="content-'.$uniq.'">';
				$html .= "\n\t".'<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="101%" viewBox="0 0 1440 806" preserveAspectRatio="none">';
				$html .= "\n\t\t".'<path d="m0.005959,200.364029l207.551124,0l0,204.342453l-207.551124,0l0,-204.342453z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m0.005959,400.45401l207.551124,0l0,204.342499l-207.551124,0l0,-204.342499z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m0.005959,600.544067l207.551124,0l0,204.342468l-207.551124,0l0,-204.342468z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m205.752151,-0.36l207.551163,0l0,204.342437l-207.551163,0l0,-204.342437z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m204.744629,200.364029l207.551147,0l0,204.342453l-207.551147,0l0,-204.342453z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m204.744629,400.45401l207.551147,0l0,204.342499l-207.551147,0l0,-204.342499z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m204.744629,600.544067l207.551147,0l0,204.342468l-207.551147,0l0,-204.342468z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m410.416046,-0.36l207.551117,0l0,204.342437l-207.551117,0l0,-204.342437z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m410.416046,200.364029l207.551117,0l0,204.342453l-207.551117,0l0,-204.342453z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m410.416046,400.45401l207.551117,0l0,204.342499l-207.551117,0l0,-204.342499z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m410.416046,600.544067l207.551117,0l0,204.342468l-207.551117,0l0,-204.342468z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m616.087402,-0.36l207.551086,0l0,204.342437l-207.551086,0l0,-204.342437z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m616.087402,200.364029l207.551086,0l0,204.342453l-207.551086,0l0,-204.342453z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m616.087402,400.45401l207.551086,0l0,204.342499l-207.551086,0l0,-204.342499z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m616.087402,600.544067l207.551086,0l0,204.342468l-207.551086,0l0,-204.342468z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m821.748718,-0.36l207.550964,0l0,204.342437l-207.550964,0l0,-204.342437z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m821.748718,200.364029l207.550964,0l0,204.342453l-207.550964,0l0,-204.342453z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m821.748718,400.45401l207.550964,0l0,204.342499l-207.550964,0l0,-204.342499z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m821.748718,600.544067l207.550964,0l0,204.342468l-207.550964,0l0,-204.342468z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m1027.203979,-0.36l207.550903,0l0,204.342437l-207.550903,0l0,-204.342437z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m1027.203979,200.364029l207.550903,0l0,204.342453l-207.550903,0l0,-204.342453z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m1027.203979,400.45401l207.550903,0l0,204.342499l-207.550903,0l0,-204.342499z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m1027.203979,600.544067l207.550903,0l0,204.342468l-207.550903,0l0,-204.342468z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m1232.659302,-0.36l207.551147,0l0,204.342437l-207.551147,0l0,-204.342437z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m1232.659302,200.364029l207.551147,0l0,204.342453l-207.551147,0l0,-204.342453z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m1232.659302,400.45401l207.551147,0l0,204.342499l-207.551147,0l0,-204.342499z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m1232.659302,600.544067l207.551147,0l0,204.342468l-207.551147,0l0,-204.342468z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t\t".'<path d="m-0.791443,-0.360001l207.551163,0l0,204.342438l-207.551163,0l0,-204.342438z" style="'.$overlay_bg.'"/>';
				$html .= "\n\t".'</svg>';
			} else {
				$html .= "\n".'<div class="ult-overlay content-'.$uniq.' '.$el_class.'" data-class="content-'.$uniq.'" id="button-click-overlay" style="'.$overlay_bg.' display:none;">';
			}
			$html .= "\n\t".'<div class="ult_modal ult-fade ult-'.$modal_size.'">';
			$html .= "\n\t\t".'<div class="ult_modal-content" style="'.$border_style.'">';
			if($modal_title !== ''){
				$html .= "\n\t\t\t".'<div class="ult_modal-header" style="'.$header_style.'">';
				$html .= "\n\t\t\t\t".$box_icon.'<h3 class="ult_modal-title">'.$modal_title.'</h3>';
				$html .= "\n\t\t\t".'</div>';
			}
			$html .= "\n\t\t\t".'<div class="ult_modal-body '.$modal_contain.'" style="'.$content_style.'">';
			$html .= "\n\t\t\t".do_shortcode($content);
			$html .= "\n\t\t\t".'</div>';
			$html .= "\n\t".'</div>';
			$html .= "\n\t".'</div>';
			$html .= "\n\t".'<div class="ult-overlay-close">Close</div>';
			$html .= "\n".'</div>';
			return $html;
		}
		/* Add modal popup Component*/
		function ultimate_modal_init()
		{
			if ( function_exists('vc_map'))
			{
				vc_map( 
					array(
						"name"		=> __("Modal Box", "smile"),
						"base"		=> "ultimate_modal",
						"icon"		=> "vc_modal_box",
						"class"	   => "modal_box",
						"category"  => __("Ultimate VC Addons", "smile"),
						"description" => "Adds bootstrap modal box in your content",
						"controls" => "full",
						"show_settings_on_create" => true,
						"params" => array(
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Icon to display:", "smile"),
								"param_name" => "icon_type",
								"value" => array(
									"No Icon" => "none",
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
								"description" => __("Click and select icon of your choice. If you can't find the one that suits for your purpose, you can <a href='admin.php?page=AIO_Icon_Manager' target='_blank'>add new here</a>.", "smile"),
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
							// Modal Title
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Modal Box Title", "smile"),
								"param_name" => "modal_title",
								"admin_label" => true,
								"value" => "",
								"description" => __("Provide the title for modal box.", "smile"),
							),
							// Add some description
							array(
								"type" => "textarea_html",
								"class" => "",
								"heading" => __("Modal Content", "smile"),
								"param_name" => "content",
								"value" => "",
								"description" => __("Content that will be displayed in Modal Popup.", "smile")
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("What's in Modal Popup?", "smile"),
								"param_name" => "modal_contain",
								"value" => array(
									"Miscellaneous Things" => "ult-html",
									"Youtube Video" => "ult-youtube",
									"Vimeo Video" => "ult-vimeo",
								),
								"description" => ""
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Display Modal On -", "smile"),
								"param_name" => "modal_on",
								"value" => array(
									"Button" => "ult-button",
									"Image" => "image",
									"Text" => "text",
									"On Page Load" => "onload",
								),
								"description" => __("When should the popup be initiated?", "smile")
							),
							array(
								"type"=>"number",
								"class"=>'',
								"heading"=>"Delay in Popup Display",
								"param_name"=>"onload_delay",
								"value"=>"2",
								"suffix"=>"seconds",
								"description"=>__("Time delay before modal popup on page load (in seconds)","smile"),
								"dependency"=>Array("element"=>"modal_on","value"=>array("onload"))
								),
							array(
								"type" => "attach_image",
								"class" => "",
								"heading" => __("Upload Image", "smile"),
								"param_name" => "btn_img",
								"admin_label" => true,
								"value" => "",
								"description" => __("Upload the custom image / image banner.", "smile"),
								"dependency" => Array("element" => "modal_on","value" => array("image")),
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Button Size", "smile"),
								"param_name" => "btn_size",
								"value" => array(
									"Small" => "sm",
									"Medium" => "md",
									"Large" => "lg",
									"Block" => "block",
								),
								"description" => __("How big the button would you like?", "smile"),
								"dependency" => Array("element" => "modal_on","value" => array("ult-button")),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Button Background Color", "smile"),
								"param_name" => "btn_bg_color",
								"value" => "#333333",
								"description" => __("Give it a nice paint!", "smile"),
								"dependency" => Array("element" => "modal_on","value" => array("ult-button")),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Button Text Color", "smile"),
								"param_name" => "btn_txt_color",
								"value" => "#FFFFFF",
								"description" => __("Give it a nice paint!", "smile"),
								"dependency" => Array("element" => "modal_on","value" => array("ult-button")),
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Alignment", "smile"),
								"param_name" => "modal_on_align",
								"value" => array(
									"Center" => "center",
									"Left" => "left",
									"Right" => "right",
								),
								"dependency"=>Array("element"=>"modal_on","value"=>array("button","image","text")),
								"description" => __("Selector the alignment of button/text/image", "smile")
							),
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Text on Button", "smile"),
								"param_name" => "btn_text",
								"admin_label" => true,
								"value" => "",
								"description" => __("Provide the title for this button.", "smile"),
								"dependency" => Array("element" => "modal_on","value" => array("ult-button")),
							),
							// Custom text for modal trigger
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Enter Text", "smile"),
								"param_name" => "read_text",
								"value" => "",
								"description" => __("Enter the text on which the modal box will be triggered.", "smile"),
								"dependency" => Array("element" => "modal_on","value" => array("text")),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Text Color", "smile"),
								"param_name" => "txt_color",
								"value" => "#f60f60",
								"description" => __("Give it a nice paint!", "smile"),
								"dependency" => Array("element" => "modal_on","value" => array("text")),
							),
							// Modal box size
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Modal Size", "smile"),
								"param_name" => "modal_size",
								"value" => array(
									"Small" => "small",
									"Medium" => "medium",
									"Large" => "container",
									"Block" => "block",
								),
								"description" => __("How big the modal box would you like?", "smile"),
							),
							// Modal Style
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => "Modal Box Style",
								"param_name" => "modal_style",
								"value" => array(
									"Corner Bottom Left" => "overlay-cornerbottomleft",
									"Corner Bottom Right" => "overlay-cornerbottomright",
									"Corner Top Left" => "overlay-cornertopleft",
									"Corner Top Right" => "overlay-cornertopright",
									"Corner Shape" => "overlay-show-cornershape",
									"Door Horizontal" => "overlay-doorhorizontal",
									"Door Vertical" => "overlay-doorvertical",
									"Fade" => "overlay-fade",
									"Genie" => "overlay-show-genie",
									"Little Boxes" => "overlay-show-boxes",
									"Simple Genie" => "overlay-simplegenie",
									"Slide Down" => "overlay-slidedown",
									"Slide Up" => "overlay-slideup",
									"Slide Left" => "overlay-slideleft",
									"Slide Right" => "overlay-slideright",
									"Zoom in" => "overlay-zoomin",
									"Zoom out" => "overlay-zoomout",
								),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Overlay Background Color", "smile"),
								"param_name" => "overlay_bg_color",
								"value" => "#333333",
								"description" => __("Give it a nice paint!", "smile"),
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Overlay Background Opacity", "smile"),
								"param_name" => "overlay_bg_opacity",
								"value" => 80,
								"min" => 10,
								"max" => 100,
								"suffix" => "%",
								"description" => __("Select opacity of overlay background.", "smile"),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Content Background Color", "smile"),
								"param_name" => "content_bg_color",
								"value" => "",
								"description" => __("Give it a nice paint!", "smile"),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Content Text Color", "smile"),
								"param_name" => "content_text_color",
								"value" => "",
								"description" => __("Give it a nice paint!", "smile"),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Header Background Color", "smile"),
								"param_name" => "header_bg_color",
								"value" => "",
								"description" => __("Give it a nice paint!", "smile"),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Header Text Color", "smile"),
								"param_name" => "header_text_color",
								"value" => "#333333",
								"description" => __("Give it a nice paint!", "smile"),
							),
							// Modal box size
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Modal Box Border", "smile"),
								"param_name" => "modal_border_style",
								"value" => array(
									"None" => "",
									"Solid" => "solid",
									"Double" => "double",
									"Dashed" => "dashed",
									"Dotted" => "dotted",
									"Inset" => "inset",
									"Outset" => "outset",
								),
								"description" => __("Do you want to give border to the modal content box?", "smile"),
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Border Width", "smile"),
								"param_name" => "modal_border_width",
								"value" => 2,
								"min" => 1,
								"max" => 25,
								"suffix" => "px",
								"description" => __("Select size of border.", "smile"),
								"dependency" => Array("element" => "modal_border_style","not_empty" => true),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Border Color", "smile"),
								"param_name" => "modal_border_color",
								"value" => "#333333",
								"description" => __("Give it a nice paint!", "smile"),
								"dependency" => Array("element" => "modal_border_style","not_empty" => true),
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Border Radius", "smile"),
								"param_name" => "modal_border_radius",
								"value" => 0,
								"min" => 1,
								"max" => 500,
								"suffix" => "px",
								"description" => __("Want to shape the modal content box?.", "smile"),
								"dependency" => Array("element" => "modal_border_style","not_empty" => true),
							),
							// Customize everything
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Extra Class", "smile"),
								"param_name" => "el_class",
								"value" => "",
								"description" => __("Add extra class name that will be applied to the modal popup, and you can use this class for your customizations.", "smile"),
							),
						) // end params array
					) // end vc_map array
				); // end vc_map
			} // end function check 'vc_map'
		}// end function icon_box_init
	}//Class Ultimate_Modals end
}
if(class_exists('Ultimate_Modals'))
{
	$Ultimate_Modals = new Ultimate_Modals;
}