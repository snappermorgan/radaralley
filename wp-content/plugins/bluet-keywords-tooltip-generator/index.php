<?php
/*
Plugin Name: BleuT KeyWord ToolTip Generator
Description: this plugin lets you put illustrated keywords in a dictionary of terms tooltip (automatic generation) ...
Author: Jamel Zarga
Version: 2.1.8
Author URI: http://www.blueskills.net/about-us
*/
defined('ABSPATH') or die("No script kiddies please!");

require_once dirname( __FILE__ ) . '/keyword-posttype.php'; //contain the class that handles the new custom post
require_once dirname( __FILE__ ) . '/settings-page.php';
require_once dirname( __FILE__ ) . '/widget.php';
require_once dirname( __FILE__ ) . '/meta-boxes.php';


/* enqueue js functions for the front side*/
function bluet_kw_load_scripts_front() {
	//
	wp_enqueue_script( 'functions-script', plugins_url('assets/functions_tooltip.js',__FILE__), array(), false, true );
		
	$opt_tmp=get_option('bluet_kw_style');
	if($opt_tmp['bt_kw_alt_img']=='on'){
		//
		wp_enqueue_script( 'functions-alt-img-script', plugins_url('assets/functions_alt_img_tooltip.js',__FILE__), array(), false, true );
	}
}
add_action( 'wp_head', 'bluet_kw_load_scripts_front' );

$bluet_kw_capability=apply_filters('bluet_kw_capability','manage_options');

/*init settings*/
register_activation_hook(__FILE__,'bluet_kw_activation');
function bluet_kw_activation(){
	$style_options=array();
	//initialise style option if bluet_kw_style is empty
	$style_options=array(
		'bt_kw_tt_color'=>'#fff',
		'bt_kw_tt_bg_color'=>'#ad7154',
		
		'bt_kw_desc_color'=>'#fff',
		'bt_kw_desc_bg_color'=>'#c0a599',
		
		'bt_kw_desc_font_size'=>'17'
	);
	
	if(!get_option('bluet_kw_style')){
		add_option('bluet_kw_style',$style_options);
	}
	
	$consern_options=array();
	//initialise settings option if empty
	$consern_options=array(
		'bt_kw_for_posts'=>'on',
		'bt_kw_match_all'=>'on',
		'bt_kw_match_excerpts'=>'on'
	);
	
	if(!get_option('bluet_kw_settings')){
		add_option('bluet_kw_settings',$consern_options);
	}
}

/**** localization ****/
add_action('init',function(){
	load_plugin_textdomain('bluet-kw', false, dirname( plugin_basename( __FILE__ ) ).'/languages/');
	
	//load jQuery once to avoid conflict
	wp_enqueue_script('jquery');
});

//test	
add_action('init',function(){
	if(is_admin()){
		new bluet_keyword();
	}
});
//call add filter for all hooks in need
	//you can pass cutom hooks you'v done
filter_any_content(array('the_content')); //'other content hook' if needed

function filter_any_content($subject_hooks){
	foreach($subject_hooks as $hook){
		add_filter($hook,function($cont){
			
			$exclude_me = get_post_meta(get_the_id(),'bluet_exclude_post_from_matching',true);
			//if the current post tells us to exclude from fetch
			if($exclude_me) return $cont;
			
			$settings=get_option('bluet_kw_settings');

			if((($settings['bt_kw_match_excerpts'] ? true : is_single()) and $settings['bt_kw_for_posts']) or (is_page() and $settings['bt_kw_for_pages'])){ 
				$my_keywords_ids=get_post_meta(get_the_id(),'bluet_matched_keywords',true);
				
				//if user specifies keywords to match
				$bluet_matching_keywords_field=get_post_meta(get_the_id(),'bluet_matching_keywords_field',true);
				if(!empty($bluet_matching_keywords_field)){
					$my_keywords_ids=$bluet_matching_keywords_field;
				}

				if(!empty($my_keywords_ids)){
					
					$my_keywords_terms=array();
					
					// The Query
					$wk_args=array(
						'post__in'=>$my_keywords_ids,
						'post_type'=>'my_keywords'
					);
					
					$the_wk_query = new WP_Query( $wk_args );

					// The Loop
					if ( $the_wk_query->have_posts() ) {

						while ( $the_wk_query->have_posts() ) {
							$the_wk_query->the_post();
							
							$my_keywords_terms[]=array(
								'kw_id'=>get_the_id(),
								'term'=>get_the_title(),
								'syns'=>get_post_meta(get_the_id(),'bluet_synonyms_keywords',true),
								'dfn'=>get_the_content(),
								'img'=>get_the_post_thumbnail(get_the_id(),'medium')
								);							
							
						}
						
					} else {
						// no posts found
					}
					/* Restore original Post Data */
					wp_reset_postdata();
						
						// first preg replace to eliminate html tags 						
							$regex='<\/?\w+((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)\/?>';							
							$out=array();
							preg_match_all('#('.$regex.')#i',$cont,$out);
							$cont=preg_replace('#('.$regex.')#i','**T_A_G**',$cont); //replace tags by **T_A_G**							
						//end
						
						$limit_match=($settings['bt_kw_match_all']=='on'? -1 : 1);
						
						/*tow loops montioned here to avoid overlapping (chevauchement) */
						foreach($my_keywords_terms as $id=>$arr){
							$term=$arr['term'];
							
							//concat synonyms if they are not empty
							if($arr['syns']!=""){
								$term.='|'.$arr['syns'];
							}

							$img=$arr['img'];
							$dfn=$arr['dfn'];

							$cont=preg_replace('#((\W)('.$term.')(\W))#i','$2__$3__$4',$cont,$limit_match);
						}
						
						foreach($my_keywords_terms as $id=>$arr){
							$term=$arr['term'];
							
							//concat synonyms if they are not empty
							if($arr['syns']!=""){
								$term.='|'.$arr['syns'];
							}

							$img=$arr['img'];
							$dfn=$arr['dfn'];
							
							if($dfn!=""){
								$dfn=" : ".$arr['dfn'];
							}
							
							$html_to_replace='<span class="bluet_tooltip" data-tooltip="'.$arr["kw_id"].'">'
												.'$2'
												.'<span class="bluet_block_to_show" data-tooltip="'.$arr["kw_id"].'">'
													.'<span class="bluet_block_container">'
														.$img
														.'<span class="bluet_title_on_block">$2</span>'
														.$dfn
													.'</span>'
												.'</span>'
											.'</span>';
							
							$cont=preg_replace('#(__('.$term.')__)#i',$html_to_replace,$cont,$limit_match);
							$cont=preg_replace('(<img)','<img height="auto !important"',$cont);
						}
						
						//Reinsert tag HTML elements
						foreach($out[0] as $id=>$tag){						
							$cont=preg_replace('#(\*\*T_A_G\*\*)#',$tag,$cont,1);
						}

					
				}
			}
			return $cont;
		},100);
	}
}

//pour traiter les termes lors de l'activation de l'ajout d'un nouveau terme ou nouveau post (keyword) publish_{my_keywords}
add_action('publish_my_keywords','regenerate_keywords');
add_action('publish_post','regenerate_keywords');
add_action('publish_page','regenerate_keywords');
add_action('trashed_post','regenerate_keywords');

register_activation_hook( __FILE__,'regenerate_keywords');

function regenerate_keywords(){
	
	//fetch terms
	$all_kw_titles=array(); //will contains keywords names with IDs ready for preg_match
	
	$kw_args =array(
		'post_type'=>'my_keywords', //to recieve only keywords
		'posts_per_page'=>-1
	);
	$the_kw_query = new WP_Query( $kw_args );
	
	
	// The Loop
	if ( $the_kw_query->have_posts() ) {
		while ( $the_kw_query->have_posts() ) {
			$the_kw_query->the_post();			

			//
			$syn=get_post_meta(get_the_id(),'bluet_synonyms_keywords',true);
			if(!empty($syn)){
				$syn='|'.$syn;
			}
			$all_kw_titles[get_the_id()]='((\W)('.get_the_title().''.$syn.')(\W))i';			
		}
	}	
	
	/* Restore original Post Data */
	wp_reset_postdata();

	$post_have_kws=array(); //the variable to save earlier
	/*get all posts (but not post type keywords)*/
	
	$args =array(
		'post_type'=>array('post','page'), //to recieve only posts
		'posts_per_page'=>-1
	);
	$the_posts_query = new WP_Query( $args );
		
	// The Loop
	if ( $the_posts_query->have_posts() ) {
		while ( $the_posts_query->have_posts() ) {
			$the_posts_query->the_post();			
			
			//init the post keywords related to zero
			$post_have_kws[get_the_id()]=array();
			
				//set terms for each post , it can be changed to custom fields
				foreach($all_kw_titles as $term_id=>$term){
				//get_post_meta(get_the_id(), "les_ingredients",true);
					//$post_meta='les_ingredients';
					
					// to test post meta :  or preg_match($term,get_post_meta(get_the_id(),$post_meta,true))){			 //to fetch only in content here by get_the_content()
					
					if(preg_match($term,strip_tags(get_the_content()))){ //strip_tags eliminates HTML tags before passing in pregmatch
						$post_have_kws[get_the_id()][]=$term_id;
					}										
				}
		}
	}	
	/* Restore original Post Data */
	wp_reset_postdata();	

		if(!get_option('bluet_post_have_kws')){ //option contains result ...
			add_option('bluet_post_have_kws');
		}
		
		update_option('bluet_post_have_kws',$post_have_kws);
		
		/* begin -- save in post meta bluet_matched_keywords */
		foreach($post_have_kws as $post_id=>$kws_ids){
			update_post_meta($post_id,'bluet_matched_keywords',$kws_ids);
		}
		/*end*/
		
		/* begin -- save in post meta bluet_matched_keywords */
		$Kw_have_posts=bluet_Kw_posts_related();
		
		//init every post_meta 'bluet_posts_in_concern' to zero
		bluet_kw_reset_meta();
		
		//process
		foreach($Kw_have_posts as $kw_id=>$posts_ids){
			$nbr_posts_related=count($posts_ids);
			update_post_meta($kw_id,'bluet_posts_in_concern',$nbr_posts_related);
		}
		/*end*/
}

function bluet_Kw_posts_related(){
	//returns an array of posts related with the current keyword
	$post_have_kws=get_option('bluet_post_have_kws');
	$Kw_have_posts=array();
	$max_related=0;
	
	//calculate the posts related
	if(!empty($post_have_kws)){

		foreach($post_have_kws as $post_id=>$kws){
			foreach($kws as $kw){
				$Kw_have_posts[$kw][]=$post_id;
			}
		}
		
		//look for max related
		foreach($Kw_have_posts as $kposts){
			$tmp_count=count($kposts);
			if($tmp_count>$max_related){
				$max_related=$tmp_count;
			}
		}
	}
	//to prevent zero devision
	if($max_related==0){
		$max_related=1;
	}		
				
	$Kw_have_posts['max_related_posts']=$max_related;
	
	return $Kw_have_posts;
}

function bluet_kw_reset_meta(){
	$kw_args =array(
		'post_type'=>'my_keywords', //to recieve only keywords
		'posts_per_page'=>-1
	);
	$the_kw_query = new WP_Query( $kw_args );
	
	
	// The Loop
	if ( $the_kw_query->have_posts() ) {
		while ( $the_kw_query->have_posts() ) {
			$the_kw_query->the_post();
			
			update_post_meta(get_the_id(),'bluet_posts_in_concern',0); //init by zero every post_meta
		}
	}	
	
	/* Restore original Post Data */
	wp_reset_postdata();
}