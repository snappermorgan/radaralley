<?php
if(!defined("WOOCOMPOSER_VERSION")){
	define("WOOCOMPOSER_VERSION",'1.0');
}
if(!class_exists("WooComposer")){
	class WooComposer{
		var $module_dir;
		function __construct()
		{
			$this->module_dir = plugin_dir_path( __FILE__ ).'modules/';
			add_action('admin_enqueue_scripts',array($this,'admin_scripts'));
			add_action('wp_enqueue_scripts',array($this,'front_scripts'));
			add_action('admin_init',array($this,'generate_shortcode_params'));
		} /* end constructor */
		function generate_shortcode_params(){
			/* Generate param type "woocomposer" */
			if(function_exists('add_shortcode_param'))
			{
				add_shortcode_param('woocomposer', array($this,'woo_query_builder'), plugins_url("admin/js/mapping.js",__FILE__));
			}
			
			/* Generate param type "product_search" */
			if(function_exists('add_shortcode_param'))
			{
				add_shortcode_param('product_search', array($this,'woo_product_search'));
			}
			/* Generate param type "product_categories" */
			if(function_exists('add_shortcode_param'))
			{
				add_shortcode_param('product_categories', array($this,'woo_product_categories'));
			}
			
			/* Generate param type "number" */
			if ( function_exists('add_shortcode_param'))
			{
				add_shortcode_param('number' , array(&$this, 'number_settings_field' ) );
			}
		}
		/* Function generate param type "number" */
		function number_settings_field($settings, $value)
		{
			$dependency = vc_generate_dependencies_attributes($settings);
			$param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
			$type = isset($settings['type']) ? $settings['type'] : '';
			$min = isset($settings['min']) ? $settings['min'] : '';
			$max = isset($settings['max']) ? $settings['max'] : '';
			$step = isset($settings['step']) ? $settings['step'] : '';
			$suffix = isset($settings['suffix']) ? $settings['suffix'] : '';
			$class = isset($settings['class']) ? $settings['class'] : '';
			$output = '<input type="number" min="'.$min.'" max="'.$max.'" step="'.$step.'" class="wpb_vc_param_value ' . $param_name . ' ' . $type . ' ' . $class . '" name="' . $param_name . '" value="'.$value.'" style="max-width:100px; margin-right: 10px;" />'.$suffix;
			return $output;
		}
		/* Function generate param type "number" */
		function woo_query_builder($settings, $value)
		{
			$output = $asc = $desc = $post_count = $shortcode_str = $cat_id = '';
			$labels = isset($settings['labels']) ? $settings['labels'] : ''; 
			$pattern = get_shortcode_regex();
			if($value !== ""){
				$shortcode = rawurldecode( base64_decode( strip_tags( $value ) ) );
				preg_match_all("/".$pattern."/",$shortcode,$matches);
				$shortcode_str = str_replace('"','',str_replace(" ","&",trim($matches[3][0])));
			}
			$short_atts = parse_str($shortcode_str);//explode("&",$shortcode_str);
			if(isset($matches[2][0])): $display_type = $matches[2][0]; else: $display_type = ''; endif;
			if(!isset($columns)): $columns = '4'; endif;
			if(!isset($per_page)): $post_count = '12'; else: $post_count = $per_page; endif;
			if(!isset($number)): $per_page = '12'; else: $post_count = $number; endif;
			if(!isset($order)): $order = 'asc'; endif;
			if(!isset($orderby)): $orderby = 'date'; endif;
			if(!isset($category)): $category = ''; endif;
			$catObj = get_term_by('name',$category,'product_cat');
			if(is_object($catObj)){ 
  				$cat_id = $catObj->term_id;
			}
			$param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
			$type = isset($settings['type']) ? $settings['type'] : '';
			$class = isset($settings['class']) ? $settings['class'] : '';
			$module = isset($settings['module']) ? $settings['module'] : ''; 
			$displays = array(
				"Recent products" => "recent_products",
				"Featured Products" => "featured_products",
				"Top Rated Products" => "top_rated_products",
				"Product Category" => "product_category",
				"Product Categories" => "product_categories",
				"Products on Sale" => "sale_products",
				"Best Selling Products" => "best_selling_products",
			);
			$orderby_arr = array(
				"Date" => "date",
				"Title" => "title",
				"Product ID" => "ID",
				"Name" => "name",
				"Price" => "price",
				"Sales" => "sales",
				"Random" => "rand",
			);
			$output .= '<div class="display_type"><label for="display_type"><strong>'.$labels['products_from'].'</strong></label>';
			$output .='<select id="display_type">';
			foreach($displays as $title => $display){
				if($display == $display_type)
					$output .= '<option value="'.$display.'" selected="selected">'.$title.'</option>';
				else
					$output .= '<option value="'.$display.'">'.$title.'</option>';
			}
			$output .= '</select></div>';
			$output .= '<div class="per_page"><label for="per_page"><strong>'.$labels['per_page'].'</strong></label>';
			$output .= '<input type="number" min="2" max="1000" id="per_page" value="'.$post_count.'"></div>';
			if($module == "grid"){
				$output .= '<div class="columns"><label for="columns"><strong>'.$labels['columns'].'</strong></label>';
				$output .= '<input type="number" min="2" max="4" id="columns" value="'.$columns.'"></div>';
			}
			$output .= '<div class="orderby"><label for="orderby"><strong>'.$labels['order_by'].'</strong></label>';
			$output .= '<select id="orderby">';
				foreach($orderby_arr as $key => $val){
					if($orderby == $val)
						$output .= '<option value="'.$val.'" selected="selected">'.$key.'</option>';
					else
						$output .= '<option value="'.$val.'">'.$key.'</option>';
				}
			$output .= '</select></div>';
			$output .= '<div class="order"><label for="order"><strong>'.$labels['order'].'</strong></label>';
			$output .= '<select id="order">';
				if($order == "asc")
					$asc = 'selected="selected"';
				else
					$desc = 'selected="selected"';
				$output .= '<option value="asc" '.$asc.'>Ascending</option>';
				$output .= '<option value="desc" '.$desc.'>Descending</option>';
			$output .= '</select></div>';
			$output .= '<div class="cat"><label for="cat"><strong>'.$labels['category'].'</strong></label>';
			$output .= wp_dropdown_categories( array('taxonomy'=>'product_cat','selected'=>$cat_id,'echo' => false,)).'</div>';
			$output .= '<!-- '.$value.' -->';
			$output .= "<input type='hidden' name='".$param_name."' value='".$value."' class='wpb_vc_param_value ".$param_name." ".$type." ".$class."' id='shortcode'>";
/*
			$output .= '<script type="text/javascript">
						jQuery(document).ready(function(){
							var inline_form 	= jQuery(".edit_form_line");
							var shortcode 		= jQuery("#shortcode");
							var display_type 	= jQuery("#display_type"),
								per_page 		= jQuery("#per_page"),
								columns 		= jQuery("#columns"),
								orderby 		= jQuery("#orderby"),
								order 			= jQuery("#order"),
								cat 			= jQuery("#cat");
								cat_lbl 		= jQuery("#cat").prev("label");
								cat_div 		= jQuery(".cat");
							var obj = [display_type, per_page, columns, orderby, order, cat, cat_lbl, cat_div];
							jQuery.each(obj,function(index,item){
								item.bind("change",function(){
									generateShortcode();
								});
							});
							if(display_type.val() != "product_category" && display_type.val() != "product_categories"){
								cat.hide();
								cat_lbl.hide();
								cat_div.hide();
								cat.addClass("none");
							}
							if(display_type.val() == "product_categories"){
								cat.attr("multiple","true");
								cat.addClass("multiple");
							} else {
								cat.removeAttr("multiple");
								cat.removeClass("multiple");
							}
							if(jQuery("#shortcode").val() == ""){
								generateShortcode();
							}
							display_type.bind("change",function(){
								if(jQuery(this).val() == "product_category"  || jQuery(this).val() == "product_categories"){
									cat.show();
									cat.removeClass("none");
									cat_lbl.show();
									cat_div.show();
								} else {
									cat.hide();
									cat.addClass("none");
									cat_lbl.hide();
									cat_div.hide();
								};
								if(jQuery(this).val() == "product_categories"){
									cat.attr("multiple","true");
									cat.addClass("multiple");
									cat.select2({
										placeholder: "Select a Category",
										allowClear: true,
									});
									cat_lbl.show();
									cat_div.show();
								} else if(jQuery(this).val() == "product_category"){ 
									cat.removeAttr("multiple");
									cat.removeClass("multiple");
									cat.select2({
										placeholder: "Select a Category",
										allowClear: true
									});
									cat_lbl.show();
									cat_div.show();
								} else {
									cat_lbl.hide();
									cat_div.hide();
								}
							});
							inline_form.click(function(){
								generateShortcode();
							});
						});
						function generateShortcode(){
							var inline_form 	= jQuery(".edit_form_line");
							var shortcode 		= jQuery("#shortcode");
							var display_type 	= jQuery("#display_type"),
								per_page 		= jQuery("#per_page"),
								columns 		= jQuery("#columns"),
								orderby 		= jQuery("#orderby"),
								order 			= jQuery("#order"),
								cat 			= jQuery("#cat");
								cat_lbl 		= jQuery("#cat").prev("label");
								cat_div 		= jQuery(".cat"),
								data 			= "[";
							if(!display_type.hasClass("none")){
								data += display_type.val()+" ";
							}
							if(!per_page.hasClass("none")){
								if(display_type.val() == "product_categories"){
									data += \' number="\'+per_page.val()+\'"\';
								} else {
									data += \' per_page="\'+per_page.val()+\'"\';
								}
							}';
						if($module == "grid"){
							$output .= 'if(!columns.hasClass("none")){
								data += \' columns="\'+columns.val()+\'"\';
							}';
						}
						$output .= 'if(!orderby.hasClass("none")){
								data += \' orderby="\'+orderby.val()+\'"\';
							}
							if(!order.hasClass("none")){
								data += \' order="\'+order.val()+\'"\';
							}
							if(!cat.hasClass("none")){
								if(display_type.val() == "product_categories"){
									data += \' ids="\'+cat.val()+\'"\';
								} else {
									data += \' category="\'+cat.children("option:selected").text().toLowerCase()+\'"\';
								}
							}
							data += "]";
							shortcode.val(base64_encode(rawurlencode(data)));
							//shortcode.val(data);
						}
						</script>';
			*/
			return $output;
		} /* end woo_query_builder */
		function woo_product_search($settings, $value){
			$param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
			$type = isset($settings['type']) ? $settings['type'] : '';
			$class = isset($settings['class']) ? $settings['class'] : '';
			
			$products_array = new WP_Query(array(
								'post_type' => 'product',
								'posts_per_page' => -1,
								'post_status' => 'publish'
							));
			$output = '';
			$output .= '<select id="products" name="'.$param_name.'" class="wpb_vc_param_value '.$param_name.' '.$type.' '.$class.'">';
					while ($products_array->have_posts()) : $products_array->the_post();
						if($value == get_the_ID()){
							$selected = "selected='selected'";
						} else {
							$selected = '';
						}
						$output .= '<option '.$selected.' value="'.get_the_ID().'">'.get_the_title().'</option>';
					endwhile;
			$output .= '</select>';
			$output .= '<script type="text/javascript">
							jQuery("#products").select2({
								placeholder: "Select a Product",
								allowClear: true
							});
						</script>';
			return $output;
		} /* end woo_product_search */
		function woo_product_categories($settings, $value){
			$param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
			$type = isset($settings['type']) ? $settings['type'] : '';
			$class = isset($settings['class']) ? $settings['class'] : '';
			$product_categories = get_terms( 'product_cat', '' );
			$output = $selected = $ids = '';
			if ( $value !== '' ) {
				$ids = explode( ',', $value );
				$ids = array_map( 'trim', $ids );
			} else {
				$ids = array();
			}
			$output .= '<select id="sel2_cat" multiple="multiple" style="min-width:200px;">';
			foreach($product_categories as $cat){
				if(in_array($cat->term_id, $ids)){
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
				$output .= '<option '.$selected.' value="'.$cat->term_id.'">'. $cat->name .'</option>';
			}
			$output .= '</select>';
			
			$output .= "<input type='hidden' name='".$param_name."' value='".$value."' class='wpb_vc_param_value ".$param_name." ".$type." ".$class."' id='sel_cat'>";
			$output .= '<script type="text/javascript">
							jQuery("#sel2_cat").select2({
								placeholder: "Select Categories",
								allowClear: true
							});
							jQuery("#sel2_cat").on("change",function(){
								jQuery("#sel_cat").val(jQuery(this).val());
							});
						</script>';
			return $output;
			
		} /* end woo_product_categories*/
		function admin_scripts($hook)
		{
			if($hook == "post.php" || $hook == "post-new.php"){
				if(defined('WOOCOMMERCE_VERSION') && version_compare( '2.1.0', WOOCOMMERCE_VERSION, '<' )) {
					wp_enqueue_style("woocomposer-admin",plugins_url("admin/css/admin.css",__FILE__));
					wp_enqueue_style("woocomposer-select2-bootstrap",plugins_url("admin/css/select2-bootstrap.css",__FILE__));
					wp_enqueue_style("woocomposer-select2",plugins_url("admin/css/select2.css",__FILE__));
					wp_enqueue_script("woocomposer-select2-js",plugins_url("admin/js/select2.js",__FILE__),false,'',true);
					
					wp_enqueue_script("woocomposer-unveil",plugins_url("assets/js/unveil.js",__FILE__),'jQuery','',true);
					wp_enqueue_script("woocomposer-js",plugins_url("assets/js/custom.js",__FILE__),'jQuery','',true);
					wp_enqueue_script("woocomposer-slick",plugins_url("assets/js/slick.js",__FILE__),'jQuery','',true);
				}
			}
		} /* end admin scripts */
		function front_scripts($post)
		{
			global $post;
			$count = 0;
			$content = $post->the_content;
			$shortcodes = array('woocomposer_product','woocomposer_list','woocomposer_grid','woocomposer_grid_cat','woocomposer_carousel_cat','woocomposer_carousel');
			foreach($shortcodes as $shortcode){
				if(has_shortcode($content, $shortcode)) {
					$count++;	
				}
			}
			if(defined('WOOCOMMERCE_VERSION') && version_compare( '2.1.0', WOOCOMMERCE_VERSION, '<' ) && $count !== 0) {
				wp_enqueue_style("woocomposer-front",plugins_url("assets/css/style.css",__FILE__));
				wp_enqueue_style("woocomposer-front-wooicon",plugins_url("assets/css/wooicon.css",__FILE__));
				wp_enqueue_style("woocomposer-front-slick",plugins_url("assets/css/slick.css",__FILE__));
				wp_enqueue_style("woocomposer-animate",plugins_url("assets/css/animate.min.css",__FILE__));
				
				wp_enqueue_script("woocomposer-unveil",plugins_url("assets/js/unveil.js",__FILE__),'1.0','jQuery',true);
				wp_enqueue_script("woocomposer-js",plugins_url("assets/js/custom.js",__FILE__),'1.0','jQuery',true);
				wp_enqueue_script("woocomposer-slick",plugins_url("assets/js/slick.js",__FILE__),'1.0','jQuery',true);
			}
		}/* end front_scripts */
	}
	new WooComposer;
	add_action('admin_init','init_woocomposer');
	function init_woocomposer()
	{
		$required_vc = '3.7.2';
		if(defined('WPB_VC_VERSION')){
			if( version_compare( $required_vc, WPB_VC_VERSION, '>' )){
				add_action( 'admin_notices', 'woocomposer_admin_notice_for_version');
			}
		} else {
			add_action( 'admin_notices', 'woocomposer_admin_notice_for_vc_activation');
		}
		
	}/* end init_addons */
	function woocomposer_admin_notice_for_version()
	{
		echo '<div class="updated"><p>The <strong>WooComposer </strong> plugin requires <strong>Visual Composer</strong> version 3.7.2 or greater.</p></div>';	
	}
	function woocomposer_admin_notice_for_vc_activation()
	{
		echo '<div class="updated"><p>The <strong>WooComposer </strong> plugin requires <strong>Visual Composer</strong> Plugin installed and activated.</p></div>';
	}
}

// check the current post for the existence of a short code
if(!function_exists("has_shortcode")){
	function has_shortcode($shortcode = '') {
		 
		$post_to_check = get_post(get_the_ID());
		 
		// false because we have to search through the post content first
		$found = false;
		 
		// if no short code was provided, return false
		if (!$shortcode) {
			return $found;
		}
		// check the post content for the short code
		if ( stripos($post_to_check->post_content, '[' . $shortcode) !== false ) {
			// we have found the short code
			$found = true;
		}
		 
		// return our final results
		return $found;
	}
}