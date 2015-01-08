<?php

/*
/* Posts module
============================================*/

class WPZOOM_Widget_Posts extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'wpzoom-posts',
			__( 'Posts', 'zoom-builder' ),
			array(
				'description' => __( 'Display posts, optionally filter by category.', 'zoom-builder' ),
				'wpzlb_widget' => true
			)
		);

	}

	function widget( $args, $instance ) {

		extract( $args );

		/* User-selected settings. */
		$title 			= apply_filters('widget_title', $instance['title'] );
		$category 		= $instance['category'];
		$show_count 	= $instance['show_count'];
		$offset_posts 	= $instance['offset_posts'];
		$show_date 		= $instance['show_date'] ? true : false;
		$show_thumb 	= $instance['show_thumb'] ? true : false;
		$show_comments 	= $instance['show_comments'] ? true : false;
		$show_excerpt 	= $instance['show_excerpt'] ? true : false;
		$excerpt_length = $instance['excerpt_length'];
		$show_title 	= $instance['hide_title'] ? false : true;

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		echo '<ul class="wpzlb-recent-posts">';

		$query_opts = apply_filters('wpzoom_query', array(
			'posts_per_page' => $show_count,
			'offset' => $offset_posts,
			'post_type' => 'post'
		));
		if ( $category ) $query_opts['cat'] = $category;

		query_posts($query_opts);
		if ( have_posts() ) : while ( have_posts() ) : the_post();

			if ($instance['thumb_width'] > 150) {

				echo '<li class="fixed-width" style="width: '.$instance['thumb_width'].'px">';

			} else {

				echo '<li>';
			}

				if ( $show_thumb && has_post_thumbnail() ) {
					echo '<div class="wpzlb-thumb"><a href="' . get_permalink() . '"><img src="' . ZOOM_Builder_Utils::thumbIt( absint( get_post_thumbnail_id() ), absint( $instance['thumb_width'] ), absint( $instance['thumb_height'] ) ) . '" height="' . $instance['thumb_height'] . '" width="' . $instance['thumb_width'] . '"></a></div>';
 				}

				if ( $show_date ) echo '<span class="wpzlb-date">' . get_the_date() . '</span> <br />';

 				if ( $show_title ) echo '<h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3><br />';

				if ( $show_comments ) { ?><span class="wpzlb-comments"><?php comments_popup_link( __('0 comments', 'zoom-builder'), __('1 comment', 'zoom-builder'), __('% comments', 'zoom-builder'), '', __('Comments are Disabled', 'zoom-builder') ); ?></span> <br /> <?php }

 				if ( $show_excerpt ) {
					$the_excerpt = get_the_excerpt();

					// cut to character limit
					$the_excerpt = substr( $the_excerpt, 0, $excerpt_length );

					// cut to last space
					$the_excerpt = substr( $the_excerpt, 0, strrpos( $the_excerpt, ' '));

					echo '<span class="wpzlb-excerpt">' . $the_excerpt . '</span>';
				}
			echo '<div class="wpzlb-clearfix"></div></li>';
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
		$instance['category'] = $new_instance['category'];
		$instance['show_count'] = $new_instance['show_count'];
		$instance['offset_posts'] = $new_instance['offset_posts'];
		$instance['show_date'] = $new_instance['show_date'];
		$instance['show_thumb'] = $new_instance['show_thumb'];
		$instance['show_comments'] = $new_instance['show_comments'];
		$instance['show_excerpt'] = $new_instance['show_excerpt'];
		$instance['hide_title'] = $new_instance['hide_title'];
		$instance['thumb_width'] = $new_instance['thumb_width'];
		$instance['thumb_height'] = $new_instance['thumb_height'];
		$instance['excerpt_length'] = $new_instance['excerpt_length'];

		return $instance;
	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => '', 'category' => 0, 'show_count' => 5, 'show_date' => false, 'show_thumb' => false, 'show_comments' => false, 'show_excerpt' => false, 'hide_title' => false, 'thumb_width' => 225, 'thumb_height' => 160, 'excerpt_length' => 55 );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'zoom-builder' ); ?></label><br />
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Category:', 'zoom-builder' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
				<option value="0" <?php if ( !$instance['category'] ) echo 'selected="selected"'; ?>><?php _e( 'All', 'zoom-builder' ); ?></option>
				<?php
				$categories = get_categories(array('type' => 'post'));

				foreach( $categories as $cat ) {
					echo '<option value="' . $cat->cat_ID . '"';

					if ( $cat->cat_ID == $instance['category'] ) echo  ' selected="selected"';

					echo '>' . $cat->cat_name . ' (' . $cat->category_count . ')';

					echo '</option>';
				}
				?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'show_count' ); ?>"><?php _e( 'Show:', 'zoom-builder' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" value="<?php echo $instance['show_count']; ?>" type="text" size="2" /> <?php _e( 'posts', 'zoom-builder' ); ?>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'offset_posts' ); ?>"><?php _e( 'Offset:', 'zoom-builder' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'offset_posts' ); ?>" name="<?php echo $this->get_field_name( 'offset_posts' ); ?>" value="<?php echo $instance['offset_posts']; ?>" type="text" size="2" /> <?php _e( 'posts', 'zoom-builder' ); ?>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['hide_title'], 'on' ); ?> id="<?php echo $this->get_field_id( 'hide_title' ); ?>" name="<?php echo $this->get_field_name( 'hide_title' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'hide_title' ); ?>"><?php _e( 'Hide post title', 'zoom-builder' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_date'], 'on' ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date', 'zoom-builder' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_thumb'], 'on' ); ?> id="<?php echo $this->get_field_id( 'show_thumb' ); ?>" name="<?php echo $this->get_field_name( 'show_thumb' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_thumb' ); ?>"><?php _e( 'Display post thumbnail', 'zoom-builder' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_comments'], 'on' ); ?> id="<?php echo $this->get_field_id( 'show_comments' ); ?>" name="<?php echo $this->get_field_name( 'show_comments' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_comments' ); ?>"><?php _e( 'Display number of comments', 'zoom-builder' ); ?></label>
		</p>

		<?php
		// only allow thumbnail dimensions if GD library supported
		if ( function_exists('imagecreatetruecolor') ) {
		?>
		<p>
		   <label for="<?php echo $this->get_field_id( 'thumb_width' ); ?>"><?php _e( 'Thumbnail size', 'zoom-builder' ); ?></label> <input type="text" id="<?php echo $this->get_field_id( 'thumb_width' ); ?>" name="<?php echo $this->get_field_name( 'thumb_width' ); ?>" value="<?php echo $instance['thumb_width']; ?>" size="3" /> x <input type="text" id="<?php echo $this->get_field_id( 'thumb_height' ); ?>" name="<?php echo $this->get_field_name( 'thumb_height' ); ?>" value="<?php echo $instance['thumb_height']; ?>" size="3" />
		</p>
		<?php
		}
		?>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_excerpt'], 'on' ); ?> id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_excerpt' ); ?>"><?php _e( 'Display post excerpt', 'zoom-builder' ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'excerpt_length' ); ?>"><?php _e( 'Excerpt character limit:', 'zoom-builder' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'excerpt_length' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" value="<?php echo $instance['excerpt_length']; ?>" type="text" size="4" />
		</p>

		<?php
	}
}


add_action( 'widgets_init', create_function( '', 'register_widget("WPZOOM_Widget_Posts");' ) );