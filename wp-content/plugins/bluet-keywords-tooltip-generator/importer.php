<?php

//importer admin page
add_action('admin_menu',function(){
	add_management_page(
		__('KeyWords Tooltip Generator Importer','bluet-kw'), 
		__('KTTG Importer','bluet-kw'), 
		'manage_options', 
		'my_keywords_settings_importer', 
		'bluet_kw_render_importer_page'
	);
});

function bluet_kw_render_importer_page(){
?>
	<form  action="<?php echo(get_admin_url().'tools.php?page=my_keywords_settings_importer'); ?>" method="post">
		<h2><?php _e('Begin importing my keywords','bluet-kw'); ?></h2>
		<input type="hidden" name="go" value="true"/>
		<!-- populate post types -->
		<?php 
		$post_types = get_post_types();
		$unfocus_post_types=array(
			'my_keywords',
			'post',
			'page',
			'attachment',
			'revision',
			'nav_menu_item'
		);
		?>
		<?php _e('Import from','bluet-kw'); ?> : <select name="bluet_posttypes_list">
		<?php 
		foreach($post_types as $post_type){
			if(!in_array($post_type,$unfocus_post_types)){
			?>
			<option value="<?php echo($post_type); ?>"><?php echo($post_type); ?></option>
			<?php
			}
		}
		?>
		</select>
		<input type="submit" value="Begin importing" />
	</form>
	<?php
	$nbr_posts_converted=0;
	$duplicated_titles=array();
	if($_POST['go']=='true'){
		//post_type_name choosen by the user show
		$args=array(
			'post_type' =>$_POST['bluet_posttypes_list'], //post type to convert from
			'posts_per_page'=>-1
		);
		
		// The Query
		$the_query = new WP_Query( $args );
		
				$tmp_titles=get_posttype_titles("my_keywords");

				// The Loop
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				
				if(!in_array(get_the_title(),$tmp_titles)){
					// Create post object					
					$my_post = array(
						'post_type' => 'my_keywords', //post type to convert to
						'post_title'    => get_the_title(),
						'post_content'  => get_the_content(),
						'post_author'   => get_the_author(),
						'post_status' => get_post_status(get_the_id())
					);

					// Insert the post into the database
					wp_insert_post($my_post);
					
					$nbr_posts_converted++;
				}else{
					$duplicated_titles[]=get_the_title();
				}
			}
			?>
				<div id="bluet_convertion_render">
					<h3><span style="color:green;"><?php echo($nbr_posts_converted); ?></span> <?php _e('keywords imported','bluet-kw'); ?> !</h3>
					<?php
						if(count($duplicated_titles)>0){
					?>
					<h3><?php _e('These posts were not imported (Duplicated)','bluet-kw'); ?> :</h3>
					<ul style="list-style: initial; padding-left: 25px;">
					<?php foreach($duplicated_titles as $title){ ?>
						<li><b><?php echo($title); ?></b></li>
						<?php } ?>
					</ul>
					<p style="color:green;"><?php _e('if you want to re-import them, you have to delete them from My Keywords.','bluet-kw'); ?></p>
					<?php
					}
					?>
				</div>
			<?php
		} else {
			//
			?>
			<div id="bluet_convertion_render">
				<h3><?php _e('No posts found','bluet-kw'); ?> !</h3>
			</div>
			<?php
			}
		/* Restore original Post Data */
		wp_reset_postdata();
	}
}

function get_posttype_titles($post_type){
		$ret=array();
		
		$args=array(
			'post_type' =>$post_type,
			'posts_per_page'=>-1
		);
		// The Query
		$the_query = new WP_Query( $args );

		// The Loop
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
								
				$ret[]=get_the_title();			}
		} else {
		}
		
		/* Restore original Post Data */
		wp_reset_postdata();
		return $ret;
}