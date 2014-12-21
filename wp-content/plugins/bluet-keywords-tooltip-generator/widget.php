<?php
defined('ABSPATH') or die("No script kiddies please!");

class bluet_keyword_widget extends wp_widget{
	function __construct(){
		$params=array(
			'description'=>'contains keywords used in the current single post.',
			'name'=>'My keywords (BlueT)'
		);
		parent::__construct('my_keywords_widget','',$params);
	}
	
	public function widget( $args, $instance ) {
		if(!is_single()) return false; //only for single pages
		
		$exclude_me = get_post_meta(get_the_id(),'bluet_exclude_post_from_matching',true);
		//if the current post tells us to exclude from fetch
		if($exclude_me) return false;

			
		// outputs the content of the widget
		extract($args);
		echo($before_widget);
			echo($before_title);
				echo $instance['title'];
			echo($after_title);
			echo('<ul>');
			//widget content process here
				$my_keywords_ids=get_post_meta(get_the_id(),'bluet_matched_keywords',true);
				
				//if user specifies keywords to match
				$bluet_matching_keywords_field=get_post_meta(get_the_id(),'bluet_matching_keywords_field',true);
				if(!empty($bluet_matching_keywords_field)){
					$my_keywords_ids=$bluet_matching_keywords_field;
				}
				
				if(!empty($my_keywords_ids)){ //if have terms in concern
					foreach($my_keywords_ids as $term_id){
					
					// The Query
					$wk_args=array(
						'p'=>$term_id,
						'post_type'=>'my_keywords'
					);
					
						$the_wk_query = new WP_Query( $wk_args );

					// The Loop
					if ( $the_wk_query->have_posts() ) {

						while ( $the_wk_query->have_posts() ) {
							$the_wk_query->the_post();
							
								$trm=get_the_title();
								
								$kw_id=get_the_id();

							// adding &zwnj; (invisible character) to avoid tooltips overlapping 
								$dfn=preg_replace('#('.$trm.')#i',$trm.'&zwnj;',get_the_content());
								
								$img=get_the_post_thumbnail($term_id,'medium');
								
								$trm=$trm.'&zwnj;';
						}
						
					}

					/* Restore original Post Data */
					wp_reset_postdata();
					
						$string_to_show='<li>
											<span class="bluet_tooltip" data-tooltip="'.$kw_id.'">
												'.$trm.'
											</span> 
										</li>';
						
						echo($string_to_show);
					}
				}else{
					_e('no terms found for this post','bluet-kw');
				}
			//
				echo('</ul>');

		echo($after_widget);
	}

	public function form( $instance ) {
	?>
		<label for="<?php echo $this->get_field_id('title'); ?>" >Title : </label>
		<input
			class="widefat"
			id="<?php echo $this->get_field_id('title'); ?>"
			name="<?php echo $this->get_field_name('title'); ?>"
			value="<?php if(isset($instance['title'])) echo esc_attr($instance['title']); ?>"
		/>
	<?php
	
		// outputs the options form on admin
	}
	
	public function register_widget(){
	}	
}

add_action('widgets_init',function(){
	register_widget('bluet_keyword_widget');
});

?>