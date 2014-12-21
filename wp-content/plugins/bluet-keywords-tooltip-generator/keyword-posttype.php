<?php
defined('ABSPATH') or die("No script kiddies please!");

if ( ! defined( 'BT_KW_BASE_FILE' ) )
    define( 'BT_KW_BASE_FILE', __FILE__ );
if ( ! defined( 'BT_KW_BASE_DIR' ) )
    define( 'BT_KW_BASE_DIR', dirname( BT_KW_BASE_FILE ) );
if ( ! defined( 'BT_KW_PLUGIN_URL' ) )
    define( 'BT_KW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	
	
add_filter( 'template_include', 'BT_KW_template_chooser');

function BT_KW_template_chooser( $template ) {
 
    // Post ID
    $post_id = get_the_ID();
 
    // For all other CPT
    if ( get_post_type( $post_id ) != 'my_keywords' ) {
        return $template;
    }
 
    // Else use custom template
    if ( is_single() ) {
        return BT_KW_get_template_hierarchy( 'single-kw' );
    }
}

function BT_KW_get_template_hierarchy($template){
    // Get the template slug
    $template_slug = rtrim( $template, '.php' );
    $template = $template_slug . '.php';
 
    // Check if a custom template exists in the theme folder, if not, load the plugin template file
    if ( $theme_file = locate_template( array( 'plugin_template/' . $template ) ) ) {
        $file = $theme_file;
    }
    else {
        $file = BT_KW_BASE_DIR . '/templates/' . $template;
    }
 
    return apply_filters( 'kw_repl_template_' . $template, $file );
}

class bluet_keyword{
	
	function __construct(){
		$this->register_my_post_type();
		
		$this->add_columns();
	}
	
	public function register_my_post_type(){
	global $bluet_kw_capability;

		$args=array(
			'labels'=>array(
				'name'=>__('KeyWords','bluet-kw'),
				'singular_name'=>__('KeyWord','bluet-kw'),
				'menu_name'=>__('My KeyWords','bluet-kw'),
				'name_admin_bar'=>__('My KeyWords','bluet-kw'),
				'all_items'=>__('All my KeyWords','bluet-kw'),
				'add_new' =>__('Add new one','bluet-kw'),
				'add_new_item'=>__('New KeyWord','bluet-kw'),
				'edit_item'=>__('Edit KeyWord','bluet-kw'),
				'new_item'=>__('New KeyWord','bluet-kw'),
				'view_item'=>__('View KeyWord','bluet-kw'),
				'search_items'=>__('Search for KeyWords','bluet-kw'),
				'not_found'=>__('KeyWords not found','bluet-kw'),
				'not_found_in_trash'=>__('KeyWords not found in trash','bluet-kw'),
				'parent_item_colon' =>__('Parent KeyWords colon','bluet-kw')
				),
			'public'=>true,
			'supports'=>array('title','editor','thumbnail'),
			'menu_icon'=>plugins_url('assets/ico_16x16.png',__FILE__),	
			
		);
		
		//modify capabilities if bluet_kw_capability hook has been called
		
		
		if($bluet_kw_capability!='manage_options'){
		$args['capabilities']=array(
		        'edit_post' => $bluet_kw_capability,
				'edit_posts' => $bluet_kw_capability,
				'publish_posts' => $bluet_kw_capability,
				'delete_post' => $bluet_kw_capability,
			);
			
		}
		
	
		register_post_type('my_keywords',$args);		
	}
	public function add_columns(){
		
		// add the picture among columns
		add_filter('manage_my_keywords_posts_columns', function($defaults){		

			$defaults['nbr_posts_related'] =__('Posts related','bluet-kw');
			$defaults['the_picture']=__('Picture','bluet-kw');
			
			//we want to rearrange the columns apearance
			$reArr['the_picture']=$defaults['the_picture'];
			$reArr['title']=$defaults['title'];
			$reArr['nbr_posts_related']=$defaults['nbr_posts_related'];
			$reArr['date']=$defaults['date'];
			
			//return the rearranged array
			return $reArr;
		});
		
		add_action('manage_my_keywords_posts_custom_column', function($column_name,$post_id){

			if ($column_name == 'the_picture') {
				// show content of 'directors_name' column
				the_post_thumbnail(array(75,75));
			}else if($column_name == 'nbr_posts_related'){				
				$nbr=get_post_meta(get_the_id(),'bluet_posts_in_concern',true);	
				/*if(empty($nbr)){
					$nbr=0;
				}*/
				$posts_related=bluet_Kw_posts_related();
				$tmp_max=$posts_related['max_related_posts'];

				$progressbar=(int)(($nbr/$tmp_max)*100);
		
				
				?>		
				<div style=" background-color: aquamarine; width: <?php echo $progressbar ?>%; border-radius: 7px; padding-left: 2px;">
					<b><?php echo $nbr; ?></b>
				</div>
				<?php
			}
		},10,2); //10 priority, 2 arguments
		
		add_filter('manage_edit-my_keywords_sortable_columns','bluet_kw_manage_sortable_columns');
		
		function bluet_kw_manage_sortable_columns( $sortable_columns ) {

		   // Let's also make the nbr_posts_related column sortable
			$sortable_columns[ 'nbr_posts_related' ] = 'nbr_posts_related';
			
			return $sortable_columns;
		}
		
		add_action( 'pre_get_posts', 'bluet_kw_manage_wp_posts_pre_get_posts', 1 );
		function bluet_kw_manage_wp_posts_pre_get_posts( $query ) {
		   if ( $query->is_main_query() && ( $orderby = $query->get( 'orderby' ) ) ) {		   
			  switch( $orderby ) {
				 // If we're ordering by 'film_rating'
				 case 'nbr_posts_related':
					// set our query's meta_key, which is used for custom fields
					$query->set( 'meta_key', 'bluet_posts_in_concern' );
						
					$query->set( 'orderby', 'meta_value_num' );					
						
					break;
			  }
		   }
		}
	}
}

?>