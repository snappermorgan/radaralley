<?php
defined('ABSPATH') or die("No script kiddies please!");

/*place metabox after the Title*/ 
add_action('edit_form_after_title', function() {
    global $post, $wp_meta_boxes,$post_type;
    
	do_meta_boxes($post_type,'after_title',$post);
	//echo("<pre>");print_r($post_type);echo("</pre>");
	
});
/**/

add_action('do_meta_boxes',function(){
//for keywords
		add_meta_box(
		'bluet_kw_synonyms_meta',
		__('Synonyms','bluet-kw'),
		'bluet_synonyms_render',
		'my_keywords',
		'after_title',
		'high'
		);

		add_meta_box(
		'bluet_kw_related_posts_meta',
		__('Posts in concern','bluet-kw').' * (BlueT)',
		'bluet_posts_related_render',
		'my_keywords',
		'side',
		'high'
		);
		
//for posts
		add_meta_box(
		'bluet_kw_post_related_keywords_meta',
		__('Keywords related','bluet-kw').' * (BlueT)',
		'bluet_keywords_related_render',
		'post',
		'side',
		'high'
		);
//for pages		
		add_meta_box(
		'bluet_kw_page_related_keywords_meta',
		__('Keywords related','bluet-kw').' * (BlueT)',
		'bluet_keywords_related_render',
		'page',
		'side',
        'high'
		);
});
function bluet_posts_related_render(){
	$nbr=get_post_meta(get_the_id(),'bluet_posts_in_concern',true);	
	if(empty($nbr)){
		$nbr=0;
	}
	echo($nbr);
}
function bluet_synonyms_render(){
	?>
	<p>
	<input id="bluet_synonyms_name" name="bluet_synonyms_name" type="text" value="<?php echo(get_post_meta(get_the_id(),'bluet_synonyms_keywords',true));?>" placeholder="<?php _e("Type here the keyword's Synonyms separated with '|'","bluet-kw");?>" style=" width:100%;" />
	</p>
	<?php
}
function bluet_keywords_related_render(){
//exclude checkbox to exclode the current post from being matched
	$exclude_me = get_post_meta(get_the_id(),'bluet_exclude_post_from_matching',true);
?>
	<div>
		<p><b>- <?php _e('Exclude this post from being matched','bluet-kw'); ?></b></p>
		<input type="checkbox" id="bluet_kw_admin_exclude_post_from_matching_id" onClick="hideIfChecked('bluet_kw_admin_exclude_post_from_matching_id','bluet_kw_admin_div_terms')" name="bluet_exclude_post_from_matching_name" <?php if($exclude_me) echo "checked"; ?>/><span style="color:red;"><?php _e('Exclude this post','bluet-kw'); ?></span>
	
<?php
//show keywords list related
	$my_kws=get_post_meta(get_the_id(),'bluet_matched_keywords',true);
	$bluet_matching_keywords_field=get_post_meta(get_the_id(),'bluet_matching_keywords_field',true);
	
	//print_r($bluet_matching_keywords_field);
	?>
		<div id="bluet_kw_admin_div_terms">
		<?php

	if(!empty($my_kws)){
	?>		
		<p><b>- <?php _e('Specify keywords to match','bluet-kw');?></b></p>
	<?php
	echo('<ul>');
		foreach($my_kws as $kw_id){
			$check_unchecked="";
			if(!empty($bluet_matching_keywords_field)){
				if(!empty($bluet_matching_keywords_field[$kw_id])){
					$check_unchecked="checked";
				}
			}
			echo('<li><input type="checkbox" name="matchable_keywords['.$kw_id.']" value="'.$kw_id.'" '.$check_unchecked.'>'.get_the_title($kw_id).'</li>'); 
		}
	echo('</ul>');
	?>
	<p style="color:green;"><?php _e('Notice : if you leave them all unchecked they will be ALL matched','bluet-kw');?></p>
	<?php
	}else{
		echo('<p>'.__('No KeyWords found for this post','bluet-kw').'</p>');
	}
	
	echo('<a href="'.get_admin_url().'edit.php?post_type=my_keywords">');
	echo(__('Manage KeyWords','bluet-kw').' >>');
	echo('</a>');
		echo('</div>');
	echo "</div>";
}

add_action('save_post',function(){
	//saving synonyms
	
	if($_POST['post_type']=='my_keywords'){
		//do sanitisation and validation
		
		//saving
		$syns_save=$_POST['bluet_synonyms_name'];
		//replace ||||||| by only one
		$syns_save=preg_replace('(\|{2,100})','|',$syns_save);
		
		//eliminate spaces special caracters
		$syns_save=preg_replace('(^\||\|$|[\s]{2,100})','',$syns_save);
		update_post_meta($_POST['post_ID'],'bluet_synonyms_keywords',$syns_save);
		
	}else if($_POST['post_type']=='post' or $_POST['post_type']=='page'){
		$exclude_me=$_POST['bluet_exclude_post_from_matching_name'];
		
		//save exclude post from matching
		update_post_meta($_POST['post_ID'],'bluet_exclude_post_from_matching',$exclude_me);
		
		
		$matchable_keywords=$_POST['matchable_keywords'];
		$arr_match=array();

		if(!empty($matchable_keywords)){
			foreach($matchable_keywords as $k=>$matchable_kw_id){
				$arr_match[$matchable_kw_id]=$matchable_kw_id;
			}
		}else{
			//
		}
		update_post_meta($_POST['post_ID'],'bluet_matching_keywords_field',$arr_match);

	
	}
}); 
