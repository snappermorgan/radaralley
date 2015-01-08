<?php

/*
/* Team Members module
============================================*/

// Registering the Team Widget
class WPZOOM_Team extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'wpzoom-team',
			__( 'Team Members', 'zoom-builder' ),
			array(
				'description' => __( 'Displays a specified amount of team members from the team custom post type.', 'zoom-builder' ),
				'wpzlb_widget' => true
			)
		);

	}

	function widget( $args, $instance ) {

		extract( $args );

		/* User-selected settings. */
		$title 	= apply_filters('widget_title', $instance['title'] );
		$show_avatar = $instance['show_avatar'];
		$show_member = $instance['show_member'];
		$show_role = $instance['show_role'];
		$show_company = $instance['show_company'];
		$show_link = $instance['show_link'];
		$show_twitter = $instance['show_twitter'];
		$item_num = 0 < ( $num = intval( $instance['item_num'] ) ) ? $num : 3;
		$random_post = $instance['random_post'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */

		if ( $title )
			echo $before_title . $title . $after_title;

		if ($random_post == 'on')
		{
			$orderby = 'rand';
		}
		else
		{
			$orderby = 'date';
		}

		$loop = new WP_Query( array( 'post_type' => 'team', 'posts_per_page' => $item_num, 'orderby' => $orderby) );

		while ( $loop->have_posts() ) : $loop->the_post();

		$customFields = get_post_custom();

		$wpzoom_member_gravatar = $customFields['wpzoom_member_gravatar'][0];

		if ($show_role == 'on') {
			$wpzoom_member_position = $customFields['wpzoom_member_position'][0];
		}
		if ($show_company == 'on') {
			$wpzoom_member_company = $customFields['wpzoom_member_company'][0];
		}
		if ($show_link == 'on') {
			$wpzoom_member_url = $customFields['wpzoom_member_url'][0];
		}
 		if ($show_twitter == 'on') {
			$wpzoom_member_twitter = $customFields['wpzoom_member_twitter'][0];
		}

		?>
				<div class="wpzlb-member-item">

					<?php
					if ($show_avatar == 'on') {

						if ($wpzoom_member_gravatar) {
							echo get_avatar( $wpzoom_member_gravatar, $size = '65' );
						}  else {
							get_the_image( array( 'size' => 'widget-team-photo', 'width' => 65, 'link_to_post' => false ) );
						}

				 	} ?>

 					<div class="wpzlb-member-details">

						<?php

						if ($show_member == 'on') {

							the_title( '<h4>', '</h4>' );

						}

						if ($wpzoom_member_twitter) {
 							echo '<a href="http://twitter.com/'.$wpzoom_member_twitter.'" target="_blank" class="wpzlb-member-twitter">'. __('Follow me', 'zoom-builder'). '</a>';
						}

 						if ($wpzoom_member_url) echo "<a href=\"$wpzoom_member_url\">";

							if ($wpzoom_member_company) echo "<span class=\"wpzlb-member-link\">$wpzoom_member_company</span>";

						if ($wpzoom_member_url) echo '</a>';

						if ($wpzoom_member_position) echo "<span class=\"wpzlb-member-position\">$wpzoom_member_position</span>";

						?>
					</div>
					<div class="cleaner">&nbsp;</div>

				</div><!-- / .member-item -->
			<?php
			endwhile;

			//Reset query_posts
			wp_reset_query();

		/* After widget (defined by themes). */
		echo $after_widget;

	}

	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['show_avatar'] = $new_instance['show_avatar'];
		$instance['show_member'] = $new_instance['show_member'];
		$instance['show_role'] = $new_instance['show_role'];
		$instance['show_company'] = $new_instance['show_company'];
		$instance['show_link'] = $new_instance['show_link'];
		$instance['show_twitter'] = $new_instance['show_twitter'];
		$instance['item_num'] = intval( $new_instance['item_num'] );
		$instance['random_post'] = $new_instance['random_post'];
		return $instance;

	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __( 'The Team', 'zoom-builder' ), 'show_title' => 'on', 'show_count' => 1, 'show_avatar' => 'on', 'show_member' => 'on', 'show_role' => 'on', 'show_company' => 'on', 'show_link' => 'on', 'show_twitter' => 'on', 'item_num' => 3, 'random_post' => 'on');
		$instance = wp_parse_args( (array) $instance, $defaults );

		?><p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:', 'zoom-builder' ); ?></label><br />
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" size="35" />
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_avatar'); ?>" name="<?php echo $this->get_field_name('show_avatar'); ?>" <?php checked( $instance['show_avatar'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('show_avatar'); ?>"><?php _e('Display member photo', 'zoom-builder'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_member'); ?>" name="<?php echo $this->get_field_name('show_member'); ?>" <?php checked( $instance['show_member'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('show_member'); ?>"><?php _e('Display member name', 'zoom-builder'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_role'); ?>" name="<?php echo $this->get_field_name('show_role'); ?>" <?php checked( $instance['show_role'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('show_role'); ?>"><?php _e('Display member role', 'zoom-builder'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_company'); ?>" name="<?php echo $this->get_field_name('show_company'); ?>" <?php checked( $instance['show_company'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('show_company'); ?>"><?php _e('Display member company', 'zoom-builder'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_link'); ?>" name="<?php echo $this->get_field_name('show_link'); ?>" <?php checked( $instance['show_link'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('show_link'); ?>"><?php _e('Link company to member URL', 'zoom-builder'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_twitter'); ?>" name="<?php echo $this->get_field_name('show_twitter'); ?>" <?php checked( $instance['show_twitter'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('show_twitter'); ?>"><?php _e('Display Twitter Icon', 'zoom-builder'); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'item_num' ); ?>"><?php _e('Number of Members to Show', 'zoom-builder'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'item_num' ); ?>" name="<?php echo $this->get_field_name( 'item_num' ); ?>" value="<?php echo intval( $instance['item_num'] ); ?>" type="text" size="3" />
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('random_post'); ?>" name="<?php echo $this->get_field_name('random_post'); ?>" <?php checked( $instance['random_post'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('random_post'); ?>"><?php _e('Show Random Members', 'zoom-builder'); ?></label>
		</p><?php

	}

}

function wpzoom_register_team_widget() {
	register_widget('WPZOOM_Team');
}
add_action('widgets_init', 'wpzoom_register_team_widget');


// Team Post Type
function wpzoom_register_team_posttype() {
	global $pagenow;

	register_post_type( 'team', array(
		'labels' => array(
			'name' => _x( 'Team', 'post type general name', 'zoom-builder' ),
			'singular_name' => _x( 'Member', 'post type singular name', 'zoom-builder' ),
			'add_new' => _x( 'Add a New Member', 'member item', 'zoom-builder' ),
			'add_new_item' => __( 'Add New Member', 'zoom-builder' ),
			'edit_item' => __( 'Edit Member', 'zoom-builder' ),
			'new_item' => __( 'New Member', 'zoom-builder' ),
			'view_item' => __( 'View Member', 'zoom-builder' ),
			'search_items' => __( 'Search Members', 'zoom-builder' ),
			'not_found' =>  __( 'Nothing found', 'zoom-builder' ),
			'not_found_in_trash' => __( 'Nothing found in Trash', 'zoom-builder' ),
			'parent_item_colon' => ''
		),
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'team', 'with_front' => false ),
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_icon' => 'dashicons-groups',
		'supports' => array( 'title', 'editor', 'thumbnail' )
	) );
}
add_action('init', 'wpzoom_register_team_posttype');


// Change default caption of the post title
function change_team_title( $title ){
     $screen = get_current_screen();

     if  ( 'team' == $screen->post_type ) {
          $title = __('Enter the team member\'s name here', 'zoom-builder');
     }

     return $title;
}

add_filter( 'enter_title_here', 'change_team_title' );



// Member Details
add_action('admin_menu', 'wpzoom_testimonials_box');

function wpzoom_testimonials_box() {
	add_meta_box('wpzoom_member_details', 'Member Details', 'wpzoom_member_details', 'team', 'side', 'high');
}

function wpzoom_member_details() {
	global $post;
	?>
	<fieldset>
		<input type="hidden" name="saveMember" id="saveMember" value="1" />
		<div>
			<p>
				<label for="wpzoom_member_gravatar"><strong><?php _e('Gravatar Email Address', 'zoom-builder');?>:</strong></label><br />
				<input class="widefat" type="text" name="wpzoom_member_gravatar" id="wpzoom_member_gravatar" value="<?php echo get_post_meta($post->ID, 'wpzoom_member_gravatar', true); ?>"/><br/>
				<span class="description"><?php _e( 'If member doesn&rsquo;t have a <a href="http://en.gravatar.com/">Gravatar</a>, upload a &ldquo;Featured Image&rdquo;.', 'zoom-builder' ); ?></span>
 			</p>

 			<p>
				<label for="wpzoom_member_company"><strong><?php _e('Company Name', 'zoom-builder');?>:</strong></label><br />
				<input type="text" class="widefat" name="wpzoom_member_company" id="wpzoom_member_company" value="<?php echo get_post_meta($post->ID, 'wpzoom_member_company', true); ?>"><br />
				<span class="description"><?php _e('Example: <strong>WPZOOM</strong>', 'zoom-builder');?></span>
			</p>

			<p>
				<label for="wpzoom_member_position"><strong><?php _e('Role', 'zoom-builder');?>:</strong></label><br />
				<input type="text" class="widefat" name="wpzoom_member_position" id="wpzoom_member_position" value="<?php echo get_post_meta($post->ID, 'wpzoom_member_position', true); ?>"><br />
				<span class="description"><?php _e('Example: <strong>CEO &amp; Founder</strong>', 'zoom-builder');?></span>
			</p>

			<p>
				<label for="wpzoom_member_url"><strong><?php _e('URL', 'zoom-builder');?>:</strong></label><br />
				<input type="text" class="widefat" name="wpzoom_member_url" id="wpzoom_member_url" value="<?php echo get_post_meta($post->ID, 'wpzoom_member_url', true); ?>"><br />
				<span class="description"><?php _e('Example: <strong>http://www.wpzoom.com</strong>', 'zoom-builder');?></span>
			</p>

			<p>
				<label for="wpzoom_member_twitter"><strong><?php _e( 'Twitter Username:', 'zoom-builder' ); ?></strong></label><br />
				<input type="text" class="widefat" name="wpzoom_member_twitter" id="wpzoom_member_twitter" value="<?php echo get_post_meta($post->ID, 'wpzoom_member_twitter', true); ?>"><br />
				<span class="description"><?php _e('Example: <strong>wpzoom</strong>', 'zoom-builder');?></span>
			</p>

  		</div>
	</fieldset>
	<?php
}


add_action( 'save_post', 'wpzoom_testimonials_save' );

function wpzoom_testimonials_save( $post_id ) {

	// called after a post or page is saved
	if ( $parent_id = wp_is_post_revision( $post_id ) ) {
	  $post_id = $parent_id;
	}

	if ( isset( $_POST['saveMember'] ) ) {

		update_custom_meta( $post_id, $_POST['wpzoom_member_gravatar'], 'wpzoom_member_gravatar' );
		update_custom_meta( $post_id, $_POST['wpzoom_member_position'], 'wpzoom_member_position' );
		update_custom_meta( $post_id, $_POST['wpzoom_member_company'], 'wpzoom_member_company' );
		update_custom_meta( $post_id, $_POST['wpzoom_member_url'], 'wpzoom_member_url' );
		update_custom_meta( $post_id, $_POST['wpzoom_member_twitter'], 'wpzoom_member_twitter' );
 	}

}