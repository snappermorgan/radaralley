<?php

/*
/* Clients module
============================================*/

// Registering Clients widget
class WPZlb_Clients extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'wpzoom-clients',
			__( 'Clients', 'zoom-builder' ),
			array(
				'description' => __( 'A sliding list of clients and/or partners from the clients custom post type.', 'zoom-builder' ),
				'wpzlb_widget' => true
			)
		);

	}

	function widget( $args, $instance ) {

		extract( $args );

		/* User-selected settings. */
		$title 			= apply_filters('widget_title', $instance['title'] );
		$show_count 	= $instance['show_count'];
		$orderby = $instance['random'] == 'on' ? 'rand' : 'date';

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		?>
		<ul class="wpzlb-clients_widget"><?php

		$query_opts = apply_filters('wpzoom_query', array(
			'posts_per_page' => $show_count,
			'post_type' => 'client',
			'orderby' => $orderby
		));

		query_posts($query_opts);
		if ( have_posts() ) : while ( have_posts() ) : the_post();
			$url = esc_url( trim( get_post_meta( get_the_ID(), 'wpzoom_client_url', true ) ) );

			echo '<li>';

			if ( !empty( $url ) ) echo '<a href="' . $url . '" title="' . the_title_attribute('echo=0') . '">';

			printf( '<img src="%s" width="200" />', ZOOM_Builder_Utils::thumbIt( absint( get_post_thumbnail_id() ), 200, auto, false ) );

			if ( !empty( $url ) ) echo '</a>';

			echo '</li>';

		endwhile; else:

			echo '<li class="noposts"><em>' . __( 'No posts to display&hellip;', 'zoom-builder' ) . '</em></li>';

		endif;

			//Reset query_posts
			wp_reset_query();
		echo '</ul><div class="wpzlb-clearfix"></div>';

		/* After widget (defined by themes). */
		echo $after_widget;

	}


	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['show_count'] = $new_instance['show_count'];
		$instance['random'] = $new_instance['random'];

		return $instance;

	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __( 'Valuble Clients', 'zoom-builder' ), 'show_count' => 8, 'random' => 'on' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'zoom-builder' ); ?></label><br />
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'show_count' ); ?>"><?php _e( 'Show:', 'zoom-builder' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" value="<?php echo $instance['show_count']; ?>" type="text" size="2" /> <?php _e( 'posts', 'zoom-builder' ); ?>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['random'], 'on' ); ?> id="<?php echo $this->get_field_id( 'random' ); ?>" name="<?php echo $this->get_field_name( 'random' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'random' ); ?>"><?php _e( 'Show random clients', 'zoom-builder' ); ?></label>
		</p>

		<?php

	}

}

function wpzlb_register_cl_widget() {
	register_widget('WPZlb_Clients');
}
add_action('widgets_init', 'wpzlb_register_cl_widget');


// Clients Post Type
function wpzlb_register_clients_posttype() {
	global $pagenow;

	register_post_type( 'client', array(
		'labels' => array(
			'name' => _x( 'Clients', 'post type general name', 'zoom-builder' ),
			'singular_name' => _x( 'Client', 'post type singular name', 'zoom-builder' ),
			'add_new' => _x( 'Add a New Client', 'client item', 'zoom-builder' ),
			'add_new_item' => __( 'Add New Client', 'zoom-builder' ),
			'edit_item' => __( 'Edit Client', 'zoom-builder' ),
			'new_item' => __( 'New Client', 'zoom-builder' ),
			'view_item' => __( 'View Client', 'zoom-builder' ),
			'search_items' => __( 'Search Clients', 'zoom-builder' ),
			'not_found' => __( 'Nothing found', 'zoom-builder' ),
			'not_found_in_trash' => __( 'Nothing found in Trash', 'zoom-builder' ),
			'parent_item_colon' => ''
		),
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'client', 'with_front' => false ),
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_icon' => 'dashicons-businessman',
		'supports' => array( 'title', 'editor', 'thumbnail' )
	) );
}
add_action('init', 'wpzlb_register_clients_posttype');


// Change default caption of the post title
function change_client_title( $title ){
    $screen = get_current_screen();

    if  ( 'client' == $screen->post_type ) {
        $title = __('Enter the Company\'s name here', 'zoom-builder');
    }

    return $title;
}

add_filter( 'enter_title_here', 'change_client_title' );



// Testimonials Options
add_action('admin_menu', 'wpzlb_clients_options_box');

function wpzlb_clients_options_box() {
	add_meta_box('wpzlb_client_options', 'Client Options', 'wpzlb_client_options', 'client', 'side', 'high');
}


// Clients Options
function wpzlb_client_options() {
	global $post;
	?>
	<fieldset>
		<input type="hidden" name="saveClient" id="saveClient" value="1" />
		<div>

			<p>
				<label for="wpzoom_client_url" ><strong><?php _e('Client URL</strong> (optional)', 'zoom-builder');?></label><br />
				<input class="widefat" type="text" name="wpzoom_client_url" id="wpzoom_client_url" value="<?php echo get_post_meta($post->ID, 'wpzoom_client_url', true); ?>"/>
				<br />
			</p>

 		</div>
	</fieldset>
	<?php
	}


add_action( 'save_post', 'wpzoom_clients_save' );

function wpzoom_clients_save( $post_id ) {

	// called after a post or page is saved
	if ( $parent_id = wp_is_post_revision( $post_id ) ) {
	  $post_id = $parent_id;
	}

	if ( isset( $_POST['saveClient'] ) ) {
		update_custom_meta( $post_id, $_POST['wpzoom_client_url'], 'wpzoom_client_url' );
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