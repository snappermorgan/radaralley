<?php
/*
* Add-on Name: Ultimate Headings
* Add-on URI: http://dev.brainstormforce.com
*/
if(!class_exists("Ultimate_Headings")){
	class Ultimate_Headings{
		static $add_plugin_script;
		function __construct(){
			add_action("admin_init",array($this,"ultimate_headings_init"));
			add_shortcode("ultimate_heading",array($this,"ultimate_headings_shortcode"));
			add_action("wp_enqueue_scripts", array($this, "register_headings_assets"));
			if(function_exists('add_shortcode_param'))
			{
				add_shortcode_param('ultimate_margins', array($this, 'ultimate_margins_param'), plugins_url('../admin/vc_extend/js/vc-headings-param.js',__FILE__));
			}
		}
		function register_headings_assets()
		{
			wp_register_style("ultimate-headings-style",plugins_url("../assets/css/headings.css",__FILE__));
			wp_register_script("ultimate-headings-script",plugins_url("../assets/js/headings.js",__FILE__));
		}
		function ultimate_margins_param($settings, $value)
		{
			$dependency = vc_generate_dependencies_attributes($settings);
			$positions = $settings['positions'];
			$html = '<div class="ultimate-margins">
						<input type="hidden" name="'.$settings['param_name'].'" class="wpb_vc_param_value ultimate-margin-value '.$settings['param_name'].' '.$settings['type'].'_field" value="'.$value.'" '.$dependency.'/>';
					foreach($positions as $key => $position)
						$html .= $key.' <input type="text" style="width:50px;padding:3px" data-hmargin="'.$position.'" class="ultimate-margin-inputs" id="margin-'.$key.'" /> &nbsp;&nbsp;';
			$html .= '</div>';
			return $html;
		}
		function ultimate_headings_init(){
			if(function_exists("vc_map")){
				vc_map(
					array(
					   "name" => __("Headings"),
					   "base" => "ultimate_heading",
					   "class" => "vc_ultimate_heading",
					   "icon" => "vc_ultimate_heading",
					   "category" => __("Ultimate VC Addons",'smile'),
					   "description" => __("Awesome heading styles.","smile"),
					   "params" => array(
							array(
								'type' => 'textfield',
								'heading' => __( 'Title', 'js_composer' ),
								'param_name' => 'main_heading',
								'holder' => 'div',
								'value' => ''
							),
							array(
								"type" => "text",
								"heading" => __("<h2>Heading Settings</h2>"),
								"param_name" => "main_heading_typograpy",
								//"dependency" => Array("element" => "main_heading", "not_empty" => true),
								"group" => "Typography",
							),
							array(
								"type" => "ultimate_google_fonts",
								"heading" => __("Font Family", "smile"),
								"param_name" => "main_heading_font_family",
								"description" => __("Select the font of your choice. You can <a target='_blank' href='".admin_url('admin.php?page=ultimate-font-manager')."'>add new in the collection here</a>.", "smile"),
								//"dependency" => Array("element" => "main_heading", "not_empty" => true),
								"group" => "Typography"
							),
							array(
								"type" => "ultimate_google_fonts_style",
								"heading" 		=>	__("Font Style", "smile"),
								"param_name"	=>	"main_heading_style",
								//"description"	=>	__("Main heading font style", "smile"),
								//"dependency" => Array("element" => "main_heading", "not_empty" => true),
								"group" => "Typography"
							),
							array(
								"type" => "number",
								"class" => "font-size",
								"heading" => __("Font Size", "smile"),
								"param_name" => "main_heading_font_size",
								"min" => 14,
								"suffix" => "px",
								//"description" => __("Main heading font size", "smile"),
								//"dependency" => Array("element" => "main_heading", "not_empty" => true),
								"group" => "Typography"
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Font Color", "smile"),
								"param_name" => "main_heading_color",
								"value" => "",
								//"description" => __("Main heading color", "smile"),	
								//"dependency" => Array("element" => "main_heading", "not_empty" => true),
								"group" => "Typography"
							),
							array(
								"type" => "text",
								"heading" => __("<h4>Enter values with respective unites. Example - 10px, 10em, 10%, etc.</h4>"),
								"param_name" => "margin_design_tab_text",
								"group" => "Design",
							),
							array(
								"type" => "ultimate_margins",
								"heading" => "Heading Margins",
								"param_name" => "main_heading_margin",
								"positions" => array(
									"Top" => "top",
									"Bottom" => "bottom"
								),
								//"dependency" => Array("element" => "main_heading", "not_empty" => true),
								"group" => "Design"
							),
							array(
								"type" => "textarea_html",
								"class" => "",
								"heading" => __("Sub Heading (Optional)", "smile"),
								"param_name" => "content",
								"value" => "",
								//"description" => __("Sub heading text", "smile"),
							),
							array(
								"type" => "text",
								"heading" => __("<h2>Sub Heading Settings</h2>"),
								"param_name" => "sub_heading_typograpy",
								//"dependency" => Array("element" => "content", "not_empty" => true),
								"group" => "Typography",
							),
							array(
								"type" => "ultimate_google_fonts",
								"heading" => __("Font Family", "smile"),
								"param_name" => "sub_heading_font_family",
								"description" => __("Select the font of your choice. You can <a target='_blank' href='".admin_url('admin.php?page=ultimate-font-manager')."'>add new in the collection here</a>.", "smile"),
								//"dependency" => Array("element" => "content", "not_empty" => true),
								"group" => "Typography",
							),
							array(
								"type" => "ultimate_google_fonts_style",
								"heading" 		=>	__("Font Style", "smile"),
								"param_name"	=>	"sub_heading_style",
								//"description"	=>	__("Sub heading font style", "smile"),
								//"dependency" => Array("element" => "content", "not_empty" => true),
								"group" => "Typography",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Font Size", "smile"),
								"param_name" => "sub_heading_font_size",
								"min" => 14,
								"suffix" => "px",
								//"description" => __("Sub heading font size", "smile"),
								"dependency" => Array("element" => "content", "not_empty" => true),
								"group" => "Typography",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Font Color", "smile"),
								"param_name" => "sub_heading_color",
								"value" => "",
								//"description" => __("Sub heading color", "smile"),	
								//"dependency" => Array("element" => "content", "not_empty" => true),
								"group" => "Typography",
							),
							array(
								"type" => "ultimate_margins",
								"heading" => "Sub Heading Margins",
								"param_name" => "sub_heading_margin",
								"positions" => array(
									"Top" => "top",
									"Bottom" => "bottom"
								),
								//"dependency" => Array("element" => "sub_heading", "not_empty" => true),
								"group" => "Design"
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Alignment", "smile"),
								"param_name" => "alignment",
								"value" => array(
									"Center"	=>	"center",
									"Left"		=>	"left",
									"Right"		=>	"right"
								),
								//"description" => __("Text alignment", "smile"),
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Seperator", "smile"),
								"param_name" => "spacer",
								"value" => array(
									"No Seperator"			=>	"no_spacer",
									"Line"			=>	"line_only",
									"Icon"			=>	"icon_only",
									"Image" => "image_only",
									"Line with icon/image"	=>	"line_with_icon",
								),
								"description" => __("Horizontal line, icon or image to divide sections", "smile"),
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Seperator Position", "smile"),
								"param_name" => "spacer_position",
								"value" => array(
									"Top"		=>	"top",
									"Between Heading & Sub-Heading"	=>	"middle",
									"Bottom"	=>	"bottom"
								),
								//"description" => __("Alignment of seperator", "smile"),
								"dependency" => Array("element" => "spacer", "value" => array("line_with_icon","line_only","icon_only","image_only")),
							),
							array(
								"type" => "attach_image",
								"heading" => __("Select Image", "smile"),
								"param_name" => "spacer_img",
								//"description" => __("Alignment of spacer", "smile"),
								"dependency" => Array("element" => "spacer", "value" => array("image_only")),
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Image Width", "smile"),
								"param_name" => "spacer_img_width",
								"value" => 48,
								"suffix" => "px",
								"description" => __("Provide image width (optional)", "smile"),
								"dependency" => Array("element" => "spacer", "value" => array("image_only")),
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Line Style", "smile"),
								"param_name" => "line_style",
								"value" => array(
									"Solid"=> "solid",
									"Dashed" => "dashed",
									"Dotted" => "dotted",
									"Double" => "double",
									"Inset" => "inset",
									"Outset" => "outset",
								),
								//"description" => __("Select the line style.","smile"),
								"dependency" => Array("element" => "spacer", "value" => array("line_with_icon","line_only")),
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Line Width (optional)", "smile"),
								"param_name" => "line_width",
								//"value" => 250,
								//"min" => 150,
								//"max" => 500,
								"suffix" => "px",
								//"description" => __("Set line width", "smile"),
								"dependency" => Array("element" => "spacer", "value" => array("line_with_icon","line_only")),
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Line Height", "smile"),
								"param_name" => "line_height",
								"value" => 1,
								"min" => 1,
								"max" => 500,
								"suffix" => "px",
								//"description" => __("Set line height", "smile"),
								"dependency" => Array("element" => "spacer", "value" => array("line_with_icon","line_only")),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Line Color", "smile"),
								"param_name" => "line_color",
								"value" => "#333333",
								//"description" => __("Select color for line.", "smile"),	
								"dependency" => Array("element" => "spacer", "value" => array("line_with_icon","line_only")),
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
								"description" => __("Use an existing font icon or upload a custom image.", "smile"),
								"dependency" => Array("element" => "spacer", "value" => array("line_with_icon","icon_only")),
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
								"value" => "",
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
								"dependency" => Array("element" => "icon_type","value" => array("selector")),
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Background Color", "smile"),
								"param_name" => "icon_color_bg",
								"value" => "",
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
								"min" => 30,
								"max" => 500,
								"suffix" => "px",
								"description" => __("Spacing from center of the icon till the boundary of border / background", "smile"),
								"dependency" => Array("element" => "icon_style", "value" => array("advanced")),
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
								"type" => "ultimate_margins",
								"heading" => "Seperator Margins",
								"param_name" => "spacer_margin",
								"positions" => array(
									"Top" => "top",
									"Bottom" => "bottom"
								),
								"dependency" => Array("element" => "spacer", "value" => array("line_with_icon","line_only","icon_only","image_only")),
								"group" => "Design"
							),
							array(
								"type" => "number",
								"heading" => "Space between Line & Icon/Image",
								"param_name" => "line_icon_fixer",
								"value" => "10",
								"suffix" => "px",
								"dependency" => Array("element" => "spacer", "value" => array("line_with_icon")),
							)
						)
					)
				);
			}
		}
		function ultimate_headings_shortcode($atts, $content = null){
			wp_enqueue_style("ultimate-headings-style");
			wp_enqueue_script("ultimate-headings-script");
			$wrapper_style = $main_heading_style_inline = $sub_heading_style_inline = $line_style_inline = $icon_inline = $output = '';
			extract(shortcode_atts(array(
				'main_heading' => '',
				"main_heading_font_size"	=> 	"",
				"main_heading_font_family" => "",
				"main_heading_style"		=>	"",
				"main_heading_color"		=>	"",
				"main_heading_margin" => "",
				"sub_heading"				=>	"",
				"sub_heading_font_size"	=> 	"",
				"sub_heading_font_family" => "",
				"sub_heading_style"		=>	"",
				"sub_heading_color"		=>	"",
				"sub_heading_margin" => "",
				"spacer"					=>	"",
				"spacer_position"			=>	"",
				"spacer_img"				=>	"",
				"spacer_img_width"				=>	"",
				"line_style"				=>	"solid",
				"line_width"				=>	"auto",
				"line_height"				=>	"1",
				"line_color"				=>	"#ccc",
				"icon_type"					=>	"",
				"icon"						=>	"",
				"icon_color"				=>	"",
				"icon_style"				=>	"",
				"icon_color_bg"				=>	"",
				"icon_border_style"			=>	"",
				"icon_color_border"			=>	"",
				"icon_border_size"			=>	"",
				"icon_border_radius"		=>	"",
				"icon_border_spacing"		=>	"",
				"icon_img"					=>	"",
				"img_width"					=>	"60",
				"icon_size"					=>	"",
				"alignment"					=>	"center",
				"spacer_margin" => "",
				"line_icon_fixer" => ""
			),$atts));
			$wrapper_class = $spacer;
			/* ---- main heading styles ---- */
			if($main_heading_font_family != '')
			{
				$mhfont_family = get_ultimate_font_family($main_heading_font_family);
				$main_heading_style_inline .= 'font-family:\''.$mhfont_family.'\';';
			}
			// main heading font style
			$main_heading_style_inline .= get_ultimate_font_style($main_heading_style);
			//attach font size if set
			if($main_heading_font_size != '')
				$main_heading_style_inline .= 'font-size:'.$main_heading_font_size.'px;';
			//attach font color if set	
			if($main_heading_color != '')
				$main_heading_style_inline .= 'color:'.$main_heading_color.';';
			//attach margins for main heading
			if($main_heading_margin != '')
				$main_heading_style_inline .= $main_heading_margin;
			/* ----- sub heading styles ----- */
			if($sub_heading_font_family != '')
			{
				$shfont_family = get_ultimate_font_family($sub_heading_font_family);
				$sub_heading_style_inline .= 'font-family:\''.$shfont_family.'\';';
			}
			//sub heaing font style
			$sub_heading_style_inline .= get_ultimate_font_style($sub_heading_style);
			//attach font size if set
			if($sub_heading_font_size != '')
				$sub_heading_style_inline .= 'font-size:'.$sub_heading_font_size.'px;';
			//attach font color if set	
			if($sub_heading_color != '')
				$sub_heading_style_inline .= 'color:'.$sub_heading_color.';';	
			//attach margins for sub heading
			if($sub_heading_margin != '')
				$sub_heading_style_inline .= $sub_heading_margin;
			if($spacer != '')
				$wrapper_style .= $spacer_margin;
			if($spacer == 'line_with_icon')
			{
				if($line_width < $icon_size)
					$wrap_width = $icon_size;
				else
					$wrap_width = $line_width;
				if($icon_type == 'selector')
				{
					if($icon_style == 'advanced')
					{
						//if($icon_border_spacing != '')
							//$wrapper_style .= 'padding:'.$icon_border_spacing.'px 0;';
					}
					else
					{
						$wrapper_style .= 'height:'.$icon_size.'px;';
					}
				}
				$icon_style_inline = 'font-size:'.$icon_size.'px;';
			}
			else if($spacer == 'line_only')
			{
				$wrap_width = $line_width;
				$line_style_inline = 'border-style:'.$line_style.';';
				$line_style_inline .= 'border-bottom-width:'.$line_height.'px;';
				$line_style_inline .= 'border-color:'.$line_color.';';
				$line_style_inline .= 'width:'.$wrap_width.'px;';
				$wrapper_style .= 'height:'.$line_height.'px;';
				$line = '<span class="uvc-headings-line" style="'.$line_style_inline.'"></span>';
				$icon_inline = $line;
			}
			else if($spacer == 'icon_only')
			{
				$icon_style_inline = 'font-size:'.$icon_size.'px;';
			}
			else if($spacer == 'image_only')
			{
				if(!empty($spacer_img_width))
					$siwidth = array($spacer_img_width, $spacer_img_width);
				else
					$siwidth = 'full';
				$icon_inline = wp_get_attachment_image( $spacer_img, $siwidth, false, array("class"=>"ultimate-headings-icon-image") );
			}
			//if spacer type is line with icon or only icon show icon or image respectively
			if($spacer == 'line_with_icon' || $spacer == 'icon_only')	
			{
				$icon_animation = '';
				$icon_inline = do_shortcode('[just_icon icon_align="'.$alignment.'" icon_type="'.$icon_type.'" icon="'.$icon.'" icon_img="'.$icon_img.'" img_width="'.$img_width.'" icon_size="'.$icon_size.'" icon_color="'.$icon_color.'" icon_style="'.$icon_style.'" icon_color_bg="'.$icon_color_bg.'" icon_color_border="'.$icon_color_border.'"  icon_border_style="'.$icon_border_style.'" icon_border_size="'.$icon_border_size.'" icon_border_radius="'.$icon_border_radius.'" icon_border_spacing="'.$icon_border_spacing.'" icon_animation="'.$icon_animation.'"]');
			}
			if($spacer == 'line_with_icon')
			{
				$data = 'data-hline_width="'.$wrap_width.'" data-hicon_type="'.$icon_type.'" data-hborder_style="'.$line_style.'" data-hborder_height="'.$line_height.'" data-hborder_color="'.$line_color.'"';
				if($icon_type == 'selector')
					$data .= ' data-icon_width="'.$icon_size.'"';
				else
					$data .= ' data-icon_width="'.$img_width.'"';
				if($line_icon_fixer != '')
					$data .= ' data-hfixer="'.$line_icon_fixer.'" ';
			}
			else
				$data = '';
			$id = uniqid('ultimate-heading');			
			$output = '<div id="'.$id.'" class="uvc-heading" data-hspacer="'.$spacer.'" '.$data.' data-halign="'.$alignment.'" style="text-align:'.$alignment.'">';
				if($spacer_position == 'top')
					$output .= $this->ultimate_heading_spacer($wrapper_class, $wrapper_style, $icon_inline);
				$output .= '<div class="uvc-main-heading"><h2 style="'.$main_heading_style_inline.'">'.$main_heading.'</h2></div>';
				if($spacer_position == 'middle')
					$output .= $this->ultimate_heading_spacer($wrapper_class, $wrapper_style, $icon_inline);
				if($content != '')
					$output .= '<div class="uvc-sub-heading" style="'.$sub_heading_style_inline.'">'.do_shortcode($content).'</div>';
				if($spacer_position == 'bottom')
					$output .= $this->ultimate_heading_spacer($wrapper_class, $wrapper_style, $icon_inline);
			$output .= '</div>';
			//enqueue google font
			$args = array(
				$main_heading_font_family, $sub_heading_font_family
			);
			enquque_ultimate_google_fonts($args);
			return $output;
		}
		function ultimate_heading_spacer($wrapper_class, $wrapper_style, $icon_inline)
		{
			$spacer = '<div class="uvc-heading-spacer '.$wrapper_class.'" style="'.$wrapper_style.'">'.$icon_inline.'</div>';
			return $spacer;
		}
	} // end class
	new Ultimate_Headings;
}