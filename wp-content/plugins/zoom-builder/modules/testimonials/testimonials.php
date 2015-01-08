<?php

/*
/* Testimonials module
============================================*/

// Registering Testimonials widget
class WPZlb_Testimonials extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'wpzoom-testimonials',
			__( 'Testimonials', 'zoom-builder' ),
			array(
				'description' => __( 'Displays a specified amount of posts from the testimonial custom post type.', 'zoom-builder' ),
				'wpzlb_widget' => true
			)
		);

	}

	function widget( $args, $instance ) {

		extract( $args );

		/* User-selected settings. */
		$title 	= apply_filters('widget_title', $instance['title'] );
		$show_photo = $instance['show_photo'];
		$show_author = $instance['show_author'];
		$show_author_position = $instance['show_author_position'];
		$show_author_company = $instance['show_author_company'];
		$show_author_company_link = $instance['show_author_company_link'];
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

		$loop = new WP_Query( array( 'post_type' => 'testimonial', 'posts_per_page' => $item_num, 'orderby' => $orderby) );

		if ( $loop->have_posts() ) : while ( $loop->have_posts() ) : $loop->the_post();

		$customFields = get_post_custom();

		if ($show_author == 'on') {
			$testimonial_author = get_the_title($loop->post_parent);
		}
		if ($show_author_position == 'on') {
			$testimonial_position = $customFields['wpzoom_testimonial_author_position'][0];
		}
		if ($show_author_company == 'on') {
			$testimonial_company = $customFields['wpzoom_testimonial_author_company'][0];
		}
		if ($show_author_company_link == 'on') {
			$testimonial_company_url = $customFields['wpzoom_testimonial_author_company_url'][0];
		}

		?>
				<div class="wpzlb-testimonial">

					<?php
					if ($show_photo == 'on') {
						printf( '<img src="%s" height="62" width="72" />', ZOOM_Builder_Utils::thumbIt( absint( get_post_thumbnail_id() ), 72, 62 ) );
					}
 					?>

 					<div class="wpzlb-testimonial_content">

						<blockquote><?php the_content(); ?></blockquote>

						<?php

						if ($testimonial_author) echo "<h4>&#8212; $testimonial_author</h4>";
						if ($testimonial_position) echo "<span class=\"wpzlb-position\">$testimonial_position</span>";
						if ($testimonial_company && $testimonial_position) echo ", ";
						if ($testimonial_company) {
							echo '<span class="wpzlb-company">';
							if ($testimonial_company_url) echo "<a href=\"$testimonial_company_url\">";
							echo $testimonial_company;
							if ($testimonial_company_url) echo '</a>';
							echo '</span>';
						}
						?>
					</div>
					<div class="cleaner">&nbsp;</div>

				</div><!-- end .testimonial -->
			<?php
			endwhile; else:

				echo '<p class="noposts"><em>' . __( 'No posts to display&hellip;', 'zoom-builder' ) . '</em></p>';

			endif;

			//Reset query_posts
			wp_reset_query();

		/* After widget (defined by themes). */
		echo $after_widget;

	}

	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['show_photo'] = $new_instance['show_photo'];
		$instance['show_author'] = $new_instance['show_author'];
		$instance['show_author_position'] = $new_instance['show_author_position'];
		$instance['show_author_company'] = $new_instance['show_author_company'];
		$instance['show_author_company_link'] = $new_instance['show_author_company_link'];
		$instance['item_num'] = intval( $new_instance['item_num'] );
		$instance['random_post'] = $new_instance['random_post'];
		return $instance;

	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __( 'Testimonials', 'zoom-builder' ), 'show_title' => 'on', 'show_count' => 1, 'show_photo' => 'on', 'show_author' => 'on', 'show_author_position' => 'on', 'show_author_company' => 'on', 'show_author_company_link' => 'on', 'item_num' => 2, 'random_post' => 'on');
		$instance = wp_parse_args( (array) $instance, $defaults );

		?><p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:', 'zoom-builder' ); ?></label><br />
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" size="35" />
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_photo'); ?>" name="<?php echo $this->get_field_name('show_photo'); ?>" <?php checked( $instance['show_photo'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('show_photo'); ?>"><?php _e('Display author photo', 'zoom-builder'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_author'); ?>" name="<?php echo $this->get_field_name('show_author'); ?>" <?php checked( $instance['show_author'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('show_author'); ?>"><?php _e('Display author name', 'zoom-builder'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_author_position'); ?>" name="<?php echo $this->get_field_name('show_author_position'); ?>" <?php checked( $instance['show_author_position'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('show_author_position'); ?>"><?php _e('Display author position', 'zoom-builder'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_author_company'); ?>" name="<?php echo $this->get_field_name('show_author_company'); ?>" <?php checked( $instance['show_author_company'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('show_author_company'); ?>"><?php _e('Display author company', 'zoom-builder'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_author_company_link'); ?>" name="<?php echo $this->get_field_name('show_author_company_link'); ?>" <?php checked( $instance['show_author_company_link'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('show_author_company_link'); ?>"><?php _e('Link author company', 'zoom-builder'); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'item_num' ); ?>"><?php _e( 'Number of Testimonials to Show:', 'zoom-builder' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'item_num' ); ?>" name="<?php echo $this->get_field_name( 'item_num' ); ?>" value="<?php echo intval( $instance['item_num'] ); ?>" type="text" size="3" />
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('random_post'); ?>" name="<?php echo $this->get_field_name('random_post'); ?>" <?php checked( $instance['random_post'], 'on' ); ?> />
			<label for="<?php echo $this->get_field_id('random_post'); ?>"><?php _e('Show Random Testimonials', 'zoom-builder'); ?></label>
		</p><?php

	}

}

function wpzlb_register_testimonials_widget() {
	register_widget('WPZlb_Testimonials');
}
add_action('widgets_init', 'wpzlb_register_testimonials_widget');


// Testimonial Post Type
function wpzoom_register_testimonials_posttype() {
	global $pagenow;

	register_post_type( 'testimonial', array(
		'labels' => array(
			'name' => _x( 'Testimonials', 'post type general name', 'zoom-builder' ),
			'singular_name' => _x( 'Testimonial', 'post type singular name', 'zoom-builder' ),
			'add_new' => _x( 'Add a New Testimonial', 'testimonial item', 'zoom-builder' ),
			'add_new_item' => __( 'Add New Testimonial', 'zoom-builder' ),
			'edit_item' => __( 'Edit Testimonial', 'zoom-builder' ),
			'new_item' => __( 'New Testimonial', 'zoom-builder' ),
			'view_item' => __( 'View Testimonial', 'zoom-builder' ),
			'search_items' => __( 'Search Testimonials', 'zoom-builder' ),
			'not_found' =>  __( 'Nothing found', 'zoom-builder' ),
			'not_found_in_trash' => __( 'Nothing found in Trash', 'zoom-builder' ),
			'parent_item_colon' => ''
		),
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'testimonial', 'with_front' => false ),
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_icon' => 'dashicons-format-quote',
		'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' )
	) );
}
add_action('init', 'wpzoom_register_testimonials_posttype');



// Change default caption of the post title
function change_testimonial_title( $title ){
    $screen = get_current_screen();

    if  ( 'testimonial' == $screen->post_type ) {
        $title = __('Enter the author\'s name here', 'zoom-builder');
    }

    return $title;
}

add_filter( 'enter_title_here', 'change_testimonial_title' );



// Testimonials Options
add_action('admin_menu', 'wpzoom_team_options_box');

function wpzoom_team_options_box() {
	add_meta_box('wpzbuilder_testimonial_options', 'Author Details', 'wpzbuilder_testimonial_options', 'testimonial', 'side', 'high');
}


function wpzbuilder_testimonial_options() {
	global $post;
	?>
	<fieldset>
		<input type="hidden" name="saveTestimonial" id="saveTestimonial" value="1" />
		<div>

			<p>
				<label for="wpzoom_testimonial_author_company"><strong><?php _e('Author Company', 'zoom-builder');?>:</strong></label><br />
				<input type="text" class="widefat" name="wpzoom_testimonial_author_company" id="wpzoom_testimonial_author_company" value="<?php echo get_post_meta($post->ID, 'wpzoom_testimonial_author_company', true); ?>"><br />
				<span class="description"><?php _e('Example: WPZOOM', 'zoom-builder');?></span>
			</p>

			<p>
				<label for="wpzoom_testimonial_author_position"><strong><?php _e('Author Position', 'zoom-builder');?>:</strong></label><br />
				<input type="text" class="widefat" name="wpzoom_testimonial_author_position" id="wpzoom_testimonial_author_position" value="<?php echo get_post_meta($post->ID, 'wpzoom_testimonial_author_position', true); ?>"><br />
				<span class="description"><?php _e('Example: CEO &amp; Founder', 'zoom-builder');?></span>
			</p>

			<p>
				<label for="wpzoom_testimonial_author_company_url"><strong><?php _e('Author Company URL', 'zoom-builder');?>:</strong></label><br />
				<input type="text" class="widefat" name="wpzoom_testimonial_author_company_url" id="wpzoom_testimonial_author_company_url" value="<?php echo get_post_meta($post->ID, 'wpzoom_testimonial_author_company_url', true); ?>"><br />
				<span class="description"><?php _e('Example: http://www.wpzoom.com', 'zoom-builder');?></span>
			</p>

  		</div>
	</fieldset>
	<?php
}


add_action( 'save_post', 'wpzoom_member_details_save' );

function wpzoom_member_details_save( $post_id ) {

	// called after a post or page is saved
	if ( $parent_id = wp_is_post_revision( $post_id ) ) {
	  $post_id = $parent_id;
	}

	if ( isset( $_POST['saveTestimonial'] ) ) {
 			update_custom_meta( $post_id, $_POST['wpzoom_testimonial_author_position'], 'wpzoom_testimonial_author_position' );
			update_custom_meta( $post_id, $_POST['wpzoom_testimonial_author_company'], 'wpzoom_testimonial_author_company' );
			update_custom_meta( $post_id, $_POST['wpzoom_testimonial_author_company_url'], 'wpzoom_testimonial_author_company_url' );
		}

	}

if ( !function_exists( 'update_custom_meta' ) ) :
function update_custom_meta( $post_id, $meta_value, $meta_key ) {
    // To create new meta
    if ( ! get_post_meta( $post_id, $meta_key ) ) {
        add_post_meta( $post_id, $meta_key, $meta_value );
    } else {
        // or to update existing meta
        update_post_meta( $post_id, $meta_key, $meta_value );
    }
}
endif;