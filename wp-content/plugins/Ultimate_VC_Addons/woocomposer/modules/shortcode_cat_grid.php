<?php
/*
@Module: Category Grid Layout
@Since: 1.0
@Package: WooComposer
*/
if(!class_exists('WooComposer_Cat_Grid')){
	class WooComposer_Cat_Grid
	{
		function __construct(){
			add_action('admin_init',array($this,'woocomposer_init_grid'));
			add_shortcode('woocomposer_grid_cat',array($this,'woocomposer_grid_shortcode'));
		} /* end constructor */
		function woocomposer_init_grid(){
			if(function_exists('vc_map')){
				$orderby_arr = array(
					"Date" => "date",
					"Title" => "title",
					"Product ID" => "ID",
					"Name" => "name",
					"Price" => "price",
					"Sales" => "sales",
					"Random" => "rand",
				);
				vc_map(
					array(
						"name"		=> __("Categories Grid [Beta]", "woocomposer"),
						"base"		=> "woocomposer_grid_cat",
						"icon"		=> "woo_grid",
						"class"	   => "woo_grid",
						"category"  => __("WooComposer [ Beta ]", "woocomposer"),
						"description" => "Display categories in grid view",
						"controls" => "full",
						"wrapper_class" => "clearfix",
						"show_settings_on_create" => true,
						"params" => array(
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Number of Categories", "woocomposer"),
								"param_name" => "number",
								"value" => "",
								"min" => 1,
								"max" => 500,
								"suffix" => "",
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Number of Columns", "woocomposer"),
								"param_name" => "columns",
								"value" => "",
								"min" => 1,
								"max" => 4,
								"suffix" => "",
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Orderby", "woocomposer"),
								"param_name" => "orderby",
								"admin_label" => true,
								"value" => $orderby_arr,
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Order", "woocomposer"),
								"param_name" => "order",
								"admin_label" => true,
								"value" =>  array(
										"Asending" => "asc",
										"Desending" => "desc",
									),
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "chk-switch",
								"class" => "",
								"heading" => __("Options", "woocomposer"),
								"param_name" => "options",
								"admin_label" => true,
								"value" => "",
								"options" => array(
										"hide_empty" => array(
													"label" => "Hide empty categories",
													"on" => "Yes",
													"off" => "No",
												),
										"parent" => array(
													"label" => "Display Child Categories if availabe in the loop",
													"on" => "Yes",
													"off" => "No",
												),
										"sel_cat" => array(
													"label" => "Select custom categories to display",
													"on" => "Yes",
													"off" => "No",
												),
									),
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "product_categories",
								"class" => "",
								"heading" => __("Select Categories", "woocomposer"),
								"param_name" => "ids",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "textfield",
								"class" => "",
								"heading" => __("Category count text", "woocomposer"),
								"param_name" => "cat_count",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Design Style", "woocomposer"),
								"param_name" => "design_style",
								"admin_label" => true,
								"value" => array(
										"Style 1" => "style01",
										"Style 2" => "style02",
										"Style 3" => "style03",
									),
								"description" => __("", "woocomposer"),
								"group" => "Initial Settings",
							),
							/*
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
							*/
							array(
								"type" => "dropdown",
								"class" => "",
								"heading" => __("Text Alignment", "woocomposer"),
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
								"heading" => __("Border Size", "woocomposer"),
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
								"description" => __("","smile"),
								"group" => "Initial Settings",
						  	),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Categories Title Background Color", "woocomposer"),
								"param_name" => "color_categories_bg",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "colorpicker",
								"class" => "",
								"heading" => __("Categories Title Color", "woocomposer"),
								"param_name" => "color_categories",
								"value" => "",
								"description" => __("", "woocomposer"),
								"group" => "Style Settings",
							),
							array(
								"type" => "number",
								"class" => "",
								"heading" => __("Categories Title", "woocomposer"),
								"param_name" => "size_cat",
								"value" => "",
								"min" => 10,
								"max" => 72,
								"suffix" => "px",
								"description" => __("", "woocomposer"),
								"group" => "Size Settings",
							),
						)/* vc_map params array */
					)/* vc_map parent array */ 
				); /* vc_map call */ 
			} /* vc_map function check */
		} /* end woocomposer_init_grid */
		function woocomposer_grid_shortcode($atts){
			global $woocommerce_loop;
			
			$number = $orderby = $order = $columns = $options = $parent = $design_style = $text_align = $border_style = $border_color = '';
			$border_size = $border_radius = $product_animation = $color_categories = $size_cat = $img_animate = $color_categories_bg = '';
			$color_cat_count_color = $color_cat_count_bg = $cat_count = '';
			extract( shortcode_atts( array(
				'number'     => null,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'columns' 	 => '4',
				'ids'		=> '',
				'options' => '',
				'cat_count' => '',
				'design_style' => '',
				'text_align' => '',
				'border_style' => '',
				'border_color' => '',
				'border_size' => '',
				'border_radius' => '',
				'product_animation' => '',
				'color_categories_bg' => '',
				'color_categories' => '',
				'color_cat_count_bg' => '',
				'color_cat_count_color' => '',
				'size_cat' => '',
				'img_animate' => '',
			), $atts ) );
	
			$border = $size = $count_style = '';
			$opts = explode(",",$options);
			
			if($color_categories !== ''){
				$size .= 'color:'.$color_categories.';';
			}
			if($color_categories_bg !== ''){
				$size .= 'background:'.$color_categories_bg.';';
			}
			if($size_cat !== ''){
				$size .= 'font-size:'.$size_cat.'px;';
			}
			
			if($color_cat_count_bg !== ''){
				$count_style .= 'background:'.$color_cat_count_bg.';';
			}
			if($color_cat_count_color !== ''){
				$count_style .= 'color:'.$color_cat_count_color.';';
			}
			
			if ( isset( $atts[ 'ids' ] ) ) {
				$ids = explode( ',', $atts[ 'ids' ] );
				$ids = array_map( 'trim', $ids );
			} else {
				$ids = array();
			}
	
			$hide_empty = in_array('hide_empty',$opts) ? 1 : 0;
			$parent = in_array('parent',$opts) ? '' : 0;
	
			if($border_style !== ''){
				$border .= 'border:'.$border_size.'px '.$border_style.' '.$border_color.';';
				$border .= 'border-radius:'.$border_radius.'px;';
			}
			// get terms and workaround WP bug with parents/pad counts
			$args = array(
				'orderby'    => $orderby,
				'order'      => $order,
				'hide_empty' => $hide_empty,
				'include'    => $ids,
				'pad_counts' => true,
				'child_of'   => $parent
			);
	
			$product_categories = get_terms( 'product_cat', $args );
	
			if ( $parent !== "" ) {
				$product_categories = wp_list_filter( $product_categories, array( 'parent' => $parent ) );
			}
	
			if ( $hide_empty ) {
				foreach ( $product_categories as $key => $category ) {
					if ( $category->count == 0 ) {
						unset( $product_categories[ $key ] );
					}
				}
			}
	
			if ( $number ) {
				$product_categories = array_slice( $product_categories, 0, $number );
			}
	
			$woocommerce_loop['columns'] = $columns;
	
			ob_start();
	
			// Reset loop/columns globals when starting a new loop
			$woocommerce_loop['loop'] = $woocommerce_loop['column'] = '';
	
			if ( $product_categories ) {
	
				//woocommerce_product_loop_start();
				
				echo '<ul class="wcmp-cat-grid products">';
	
				foreach ( $product_categories as $category ) {
	
					// Store loop count we're currently on
					if ( empty( $woocommerce_loop['loop'] ) )
						$woocommerce_loop['loop'] = 0;
					
					// Store column count for displaying the grid
					if ( empty( $woocommerce_loop['columns'] ) )
						$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
					
					// Increase loop count
					$woocommerce_loop['loop']++;
					?>
					<li class="product-category product<?php
						if ( ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] == 0 || $woocommerce_loop['columns'] == 1 )
							echo ' first';
						if ( $woocommerce_loop['loop'] % $woocommerce_loop['columns'] == 0 )
							echo ' last';
						?>">
                        
                        <div class="wcmp-product wcmp-img-<?php echo $img_animate; ?> wcmp-cat-<?php echo $design_style.' animated '.$product_animation; ?>" style="<?php echo $border; ?>">     
					
						<?php do_action( 'woocommerce_before_subcategory', $category ); ?>                        
					
                          <a href="<?php echo get_term_link( $category->slug, 'product_cat' ); ?>" style="text-align:<?php echo $text_align; ?>;">
							
                            <div class="wcmp-product-image">
							<?php
								/**
								 * woocommerce_before_subcategory_title hook
								 *
								 * @hooked woocommerce_subcategory_thumbnail - 10
								 */
								do_action( 'woocommerce_before_subcategory_title', $category );
							?>
                            </div><!--.wcmp-product-image-->
					
							<h3 style="<?php echo $size; ?>">
								<?php
									echo $category->name;
					
									if ( $category->count > 0 )
										echo apply_filters( 'woocommerce_subcategory_count_html', ' <mark class="count" style="'.$count_style.'">' . $category->count . ' '. $cat_count.'</mark>', $category );
								?>
							</h3>
					
							<?php
								/**
								 * woocommerce_after_subcategory_title hook
								 */
								do_action( 'woocommerce_after_subcategory_title', $category );
							?>
					
						</a>
                        
						<?php do_action( 'woocommerce_after_subcategory', $category ); ?>
                        
                        </div><!--.wcmp-product-->
					
					</li>	
	<?php
				}
	
				woocommerce_product_loop_end();
	
			}
	
			woocommerce_reset_loop();
	
			return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
		}/* end woocomposer_grid_shortcode */
	} /* end class WooComposer_Cat_Grid */
	new WooComposer_Cat_Grid;
}