<?php

/*
/* Media Slider module
============================================*/

// Registering slideshow widget
class WPZOOM_Widget_Media_Slider extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'wpzoom-media-slider',
			__( 'Media Slider', 'zoom-builder' ),
			array(
				'description' => __( 'A slider widget that displays media attached to a specified post from the Slideshow custom post type.', 'zoom-builder' ),
				'wpzlb_widget' => true
			),
			array(
				'width' => 470

			)
		);

	}

	function widget( $args, $instance ) {

		extract( $args );

		/* User-selected settings. */
		$title = isset( $instance['title'] ) && !empty( $instance['title'] ) ? apply_filters( 'widget_title', trim( $instance['title'] ), $instance, $this->id_base ) : '';
		$slideshow_post = isset( $instance['slideshow_post'] ) && 0 < ( $postid = intval( $instance['slideshow_post'] ) ) ? $postid : 0;
		$show_title = isset( $instance['show_title'] ) ? (bool)$instance['show_title'] : false;
		$autoplay = isset( $instance['autoplay'] ) ? (bool)$instance['autoplay'] : false;
		$autoplay_interval = isset( $instance['autoplay_interval'] ) && 0 < ( $interval = intval( $instance['autoplay_interval'] ) ) ? $interval : 4000;
		$thumbs_width = isset( $instance['thumbs_width'] ) && 0 < ( $interval = intval( $instance['thumbs_width'] ) ) ? $interval : 150;
		$thumbs_height = isset( $instance['thumbs_height'] ) && 0 < ( $interval = intval( $instance['thumbs_height'] ) ) ? $interval : 120;
		$slide_effect = isset( $instance['slide_effect'] ) && intval( $instance['slide_effect'] ) == 1 ? 'slide' : 'crossfade';
		$nav_position = esc_attr($instance['nav_position']);
  		$slide_skin = esc_attr($instance['slide_skin']);
   		$caption_position = esc_attr($instance['caption_position']);
   		$caption_position_full = esc_attr($instance['caption_position_full']);
   		$caption_align = esc_attr($instance['caption_align']);
  		$nav_type = esc_attr($instance['nav_type']);

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */

		if ( $show_title ) {
			if ( $title ) echo $before_title . $title . $after_title;
		}

		if ( $slideshow_post > 0 && null !== ( $post = get_post( $slideshow_post ) ) && is_a( $post, 'WP_Post' ) ) {

			if ( '' != ( $slides = get_post_meta( $post->ID, 'wpzoom_slider', true ) ) && is_array( $slides ) && count( $slides ) > 0 ) {

				?>


				<div class="zoom-carousel <?php echo $slide_skin; ?>-skin">


					<?php
						$i = 1;
						$thumbs = '';
						$carouselthumbs = '';
						$tabs = '';

						foreach ( $slides as $slide ) {

							if ( !isset( $slide['imageId'] ) && !isset( $slide['embedCode'] ) ) continue;

							$type = isset( $slide['slideType'] ) && $slide['slideType'] == 'video' ? 'video' : 'image';

							$thumb_output = '';

							$caption = isset( $slide['caption'] ) ? trim( $slide['caption'] ) : '';


							if ( $type == 'image' && isset( $slide['imageId'] ) && 0 < ( $id = intval( $slide['imageId'] ) ) ) {
							 	$image = ZOOM_Builder_Utils::thumbIt( absint( $id ), 1200 );
								$thumb_image = ZOOM_Builder_Utils::thumbIt( absint( $id ), $thumbs_width, $thumbs_height );

								$thumb_output .= '<img src="' . esc_url( $thumb_image ) . '"  width="'.$thumbs_width.'" height="'.$thumbs_height.'"   />';

								$carouselthumbs .= '<a rel="fancybox" class="fancybox-media" href="' . esc_url( $image ) . '">' . $thumb_output . '</a>';

							} elseif ( $type == 'video' && isset( $slide['embedCode'] ) && '' != ( $embed = trim( $slide['embedCode'] ) ) && false !== ( $url = ZOOM_Builder_Video_API::extract_url_from_embed( $embed ) ) ) {

								if ( false !== ( $attachment_id = ZOOM_Builder_Video_API::fetch_save_return_video_thumbnail( $url, $post->ID ) ) )
									$thumb_output .= '<span class="play-icon"></span><img src="' . esc_url( ZOOM_Builder_Utils::thumbIt( absint( $attachment_id ), $thumbs_width, $thumbs_height ) ) . '" width="'.$thumbs_width.'" height="'.$thumbs_height.'"  />';

								$videoContainer .= '<div id="inline-'.$i.'" style="display:none; height:450px;">'. ZOOM_Builder_Utils::embed_fix($slide['embedCode'], 800, 450).'</div>';

								$carouselthumbs .= '<a rel="fancybox" class="fancybox-media" href="#inline-'.$i.'">' . $videoContainer . $thumb_output . '</a>';

							}

							$thumbs .= '<a href="#slide-'. $i . '">' . $thumb_output . '</a>';
							$tabs .= '<li class="slide-' . $i . '"><a href="#">' . $caption . '</a></li>';

							$i++;

						}
					?>


					<?php if ($nav_position == 'top') { ?>

						<?php if ($nav_type == 'thumbs') { ?>
							<div class="carousel-thumbs" id="<?php echo $widget_id; ?>-thumbs">

 								<?php echo $thumbs; ?>

							</div>
						<?php } ?>

						<?php if ($nav_type == 'tabs') { ?>

							<div class="carousel-tabs" id="<?php echo $widget_id; ?>-tabs"></div>

						<?php } ?>


						<?php if ($nav_type == 'dots') { ?>

							<div class="carousel-dots" id="<?php echo $widget_id; ?>-dots"></div>

						<?php } ?>
					<?php } ?>


					<?php if ($slide_skin == 'carousel') { ?>

						<div class="carousel-thumbs" id="<?php echo $widget_id; ?>-thumbs">

							<?php echo $carouselthumbs; ?>

						</div>

						<div class="carousel-nav">

							<a id="<?php echo $widget_id; ?>-prev" class="carousel-nav-prev" href="#"></a>
							<a id="<?php echo $widget_id; ?>-next" class="carousel-nav-next" href="#"></a>

						</div>

					<?php } ?>


					<?php if ($slide_skin != 'carousel') { ?>

 	 					<div class="carousel" id="<?php echo $widget_id; ?>-carousel">

							<?php
							$i = 1;

							foreach ( $slides as $slide ) {

								if ( !isset( $slide['imageId'] ) && !isset( $slide['embedCode'] ) ) continue;

								$type = isset( $slide['slideType'] ) && $slide['slideType'] == 'video' ? 'video' : 'image';
								$output = '';

								$caption = isset( $slide['caption'] ) ? trim( $slide['caption'] ) : '';
								$url = isset( $slide['url'] ) ? trim( $slide['url'] ) : '';
								if ( !empty( $caption ) && !empty( $url ) ) $caption = '<a href="' . $url . '">' . $caption . '</a>';
								$description = isset( $slide['description'] ) ? trim( $slide['description'] ) : '';

								if ( $type == 'image' && isset( $slide['imageId'] ) && 0 < ( $id = intval( $slide['imageId'] ) ) ) {

									$image = ZOOM_Builder_Utils::thumbIt( absint( $id ), 1200, 'auto', true, true );
									if ( is_array( $image ) && count( $image ) > 2 ) {
										$output .= sprintf( '<img src="%s" width="%d" height="%d" />', esc_url( $image[0] ), absint( $image[1] ), absint( $image[2] ) );
									} else {
										$output .= '<img src="' . esc_url( ''.$image ) . '" />';
									}

								} elseif ( $type == 'video' && isset( $slide['embedCode'] ) && '' != ( $embed = trim( $slide['embedCode'] ) ) ) {

									$output .= '<div class="video-cover">' . ZOOM_Builder_Utils::embed_fix( $embed, 1200, 675 ) . '</div>';


								}

								if ( empty( $output ) ) continue;


								echo '<div id="slide-' . $i . '" class="wpzlb-slider-item">';

									if ( !empty($caption) ) echo '<p class="tabs-caption" style="display:none;">' . $caption . '</p>';

										echo '<div class="media-cover h-position-'.$caption_position.'">' . $output . '</div>';


									if ( $type == 'image' && isset( $slide['imageId'] ) && 0 < ( $id = intval( $slide['imageId'] ) ) ) {

											if ($slide_skin == 'full-width' && $caption_position_full != '-1') {

											if ( !empty($caption) || !empty ($description) ) echo '<div class="slide-text align-' .$caption_align . ' position-'.$caption_position_full.'">';

												if ( !empty($caption) ) echo '<h3 class="flex-caption">' . $caption . '</h3>';

												if ( !empty($description) ) echo '<p class="flex-subhead">' . $description . '</p>';

											if ( !empty($caption) || !empty ($description) ) echo '</div>';

										}

									}

									if ($slide_skin == 'caption') {

										if ( !empty($caption) || !empty ($description) ) echo '<div class="slide-text align-' .$caption_align . '">';

											if ( !empty($caption) ) echo '<h3 class="flex-caption">' . $caption . '</h3>';

											if ( !empty($description) ) echo '<p class="flex-subhead">' . $description . '</p>';

										if ( !empty($caption) || !empty ($description) ) echo '</div>';

									}

									if ($slide_skin == 'half') {

										if ( !empty($caption) || !empty ($description) ) echo '<div class="slide-text align-' .$caption_align . ' h-position-'.$caption_position.'">';

											if ( !empty($caption) ) echo '<h3 class="flex-caption">' . $caption . '</h3>';

											if ( !empty($description) ) echo '<p class="flex-subhead">' . $description . '</p>';

										if ( !empty($caption) || !empty ($description) ) echo '</div>';

									}


								echo '<div class="wpzlb-clearfix"></div></div>';


								$i++;

							}
							?>

	 					</div>

						<div class="carousel-nav<?php if ($nav_type == '-1' || $nav_type == 'dots') { echo "-no-thumbs"; }?>">

							<a id="<?php echo $widget_id; ?>-prev" class="carousel-nav-prev" href="#"></a>
							<a id="<?php echo $widget_id; ?>-next" class="carousel-nav-next" href="#"></a>

						</div>



						<?php if ($nav_position == 'bottom') { ?>

							<?php if ($nav_type == 'thumbs') { ?>
								<div class="carousel-thumbs" id="<?php echo $widget_id; ?>-thumbs">

									<?php echo $thumbs; ?>

								</div>
							<?php } ?>

							<?php if ($nav_type == 'tabs') { ?>

								<div class="carousel-tabs" id="<?php echo $widget_id; ?>-tabs">

								</div>

							<?php } ?>


							<?php if ($nav_type == 'dots') { ?>

								<div class="carousel-dots" id="<?php echo $widget_id; ?>-dots"></div>

							<?php } ?>
						<?php } ?>

					<?php } ?>

				</div>

				<script type="text/javascript">

				jQuery(function($){
    				$(window).load(function(){

 						<?php if ($slide_skin == 'full-width' || $slide_skin == 'caption' || $slide_skin == 'half') { ?>

							<?php if ($nav_type == 'tabs' || $nav_type == 'dots') { ?>

	 							$('#<?php echo $widget_id; ?>-carousel').carouFredSel({
									responsive: true,
									circular: true,
									items: 1,
									padding: [50, 50],
	 								auto: {
										<?php if ( $autoplay ) echo 'timeoutDuration: ' . $autoplay_interval . ','; ?>
										play: <?php echo $autoplay ? 'true' : 'false'; ?>
									},
									scroll: {
										fx: '<?php echo $slide_effect; ?>'
									},
									prev: '#<?php echo $widget_id; ?>-prev',
									next: '#<?php echo $widget_id; ?>-next',
									pagination:
										<?php if ($nav_type == 'tabs') { ?>
											{
												container: '#<?php echo $widget_id; ?>-tabs',
												anchorBuilder: function( nr ) {
													return '<a href="#">' + $(this).find('.tabs-caption').text() + '</a>';
												}
											}
										<?php } else { ?>
											'#<?php echo $widget_id; ?>-dots'
										<?php } ?>

								});
							<?php } else { ?>

								$('#<?php echo $widget_id; ?>-carousel').carouFredSel({
									responsive: true,
									circular: false,
									auto: {
										<?php if ( $autoplay ) echo 'timeoutDuration: ' . $autoplay_interval . ','; ?>
										play: <?php echo $autoplay ? 'true' : 'false'; ?>
									},
									items: {
										visible: 1
	 								},
	 								prev: '#<?php echo $widget_id; ?>-prev',
									next: '#<?php echo $widget_id; ?>-next',
	 								width: 1200,
	 								scroll: {
										fx: '<?php echo $slide_effect; ?>',
										onBefore: function() {
											var pos = $(this).triggerHandler('currentPosition'),
											    page = Math.floor( pos / 4 );

											$('#<?php echo $widget_id; ?>-thumbs a')
												.removeClass('selected')
												.filter('[href="#slide-' + ( pos + 1 ) + '"]')
													.addClass('selected');

											$('#<?php echo $widget_id; ?>-thumbs').trigger('slideToPage', page);
										}
									}
								});

								<?php if ($nav_type == 'thumbs') { ?>

									$('#<?php echo $widget_id; ?>-thumbs').carouFredSel({
		 								responsive: true,
										circular: false,
										infinite: false,
										align: "center",
										auto: {
											<?php if ( $autoplay ) echo 'timeoutDuration: ' . $autoplay_interval . ','; ?>
											play: <?php echo $autoplay ? 'true' : 'false'; ?>
										},
		  								items: {
											visible: {
												min: 2,
												max: 10
											},
											width: <?php echo $thumbs_width; ?>,
											height: <?php echo $thumbs_height; ?>
										}

									});

									$('#<?php echo $widget_id; ?>-thumbs a').click(function() {

										$('#<?php echo $widget_id; ?>-carousel').trigger('slideTo', '#' + this.href.split('#').pop() );
								 		$('#<?php echo $widget_id; ?>-thumbs a').removeClass('selected');
										$(this).addClass('selected');
										return false;



									});
									$('#<?php echo $widget_id; ?>-thumbs a:eq(0)').addClass('selected');

								<?php } ?>

							<?php } ?>
						<?php } ?>


						<?php if ($slide_skin == 'carousel') { ?>

							$('#<?php echo $widget_id; ?>-thumbs').carouFredSel({
 								responsive: true,
								circular: false,
								scroll: 1,
								infinite: true,
								align: "center",
								prev: '#<?php echo $widget_id; ?>-prev',
								next: '#<?php echo $widget_id; ?>-next',
								auto: {
									<?php if ( $autoplay ) echo 'timeoutDuration: ' . $autoplay_interval . ','; ?>
									play: <?php echo $autoplay ? 'true' : 'false'; ?>
								},
   								items: {
									visible: {
										min: 2,
										max: 10
									},
									width: <?php echo $thumbs_width; ?>,
									height: <?php echo $thumbs_height; ?>
								}

							});


							$('.fancybox-media').fancybox({
 								height:'auto',
 								fitToView	: false,
 								autoSize	: false,
								padding: 0,
								openEffect	: 'none',
								closeEffect	: 'none'
							});


						<?php } ?>

					});

				});

				</script><?php

			} else {

				echo '<p class="invalid">' . __( 'No slideshow images found for selected post.', 'zoom-builder' ) . '</p>';

			}

		} else {

			echo '<p class="invalid">' . __( 'Invalid slideshow post selected.', 'zoom-builder' ) . '</p>';

		}

		/* After widget (defined by themes). */
		echo $after_widget;

	}

	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['slideshow_post'] = 0 < ( $postid = intval( $new_instance['slideshow_post'] ) ) ? $postid : 0;
		$instance['show_title'] = (bool)$new_instance['show_title'];
		$instance['autoplay'] = (bool)$new_instance['autoplay'];
		$instance['autoplay_interval'] = 0 < ( $interval = intval( $new_instance['autoplay_interval'] ) ) ? $interval : 4000;
		$instance['thumbs_width'] = 0 < ( $interval = intval( $new_instance['thumbs_width'] ) ) ? $interval : 150;
		$instance['thumbs_height'] = 0 < ( $interval = intval( $new_instance['thumbs_height'] ) ) ? $interval : 120;
		$instance['slide_effect'] = intval( $new_instance['slide_effect'] ) == 1 ? 1 : 0;
  		$instance['nav_position'] = strip_tags($new_instance['nav_position']);
		$instance['slide_skin'] = strip_tags($new_instance['slide_skin']);
		$instance['caption_position'] = strip_tags($new_instance['caption_position']);
		$instance['caption_position_full'] = strip_tags($new_instance['caption_position_full']);
		$instance['caption_align'] = strip_tags($new_instance['caption_align']);
		$instance['nav_type'] = strip_tags($new_instance['nav_type']);

		return $instance;

	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => '', 'slide_skin' => 'full-width', 'slideshow_post' => 0, 'show_title' => false, 'caption_position' => 'right', 'caption_position_full' => 'bottom', 'caption_align' => 'left', 'thumbs_width' => 150, 'thumbs_height' => 120, 'autoplay' => false, 'autoplay_interval' => 4000, 'nav_position' => 'bottom', 'nav_type' => 0, 'slide_effect' => 0 );
		$instance = wp_parse_args( (array)$instance, $defaults );
		$title = esc_attr( trim( $instance['title'] ) );
		$slideshow_post = 0 < ( $postid = intval( $instance['slideshow_post'] ) ) ? $postid : 0;
		$autoplay_interval = 0 < ( $interval = intval( $instance['autoplay_interval'] ) ) ? $interval : 4000;
		$thumbs_width = 0 < ( $interval = intval( $instance['thumbs_width'] ) ) ? $interval : 150;
		$thumbs_height = 0 < ( $interval = intval( $instance['thumbs_height'] ) ) ? $interval : 120;
 		$slide_effect = intval( $instance['slide_effect'] ) == 1 ? 1 : 0;
   		$caption_position = esc_attr($instance['caption_position']);
   		$caption_position_full = esc_attr($instance['caption_position_full']);
		$caption_align = esc_attr($instance['caption_align']);
		$slide_skin = esc_attr($instance['slide_skin']);
		$nav_position = esc_attr($instance['nav_position']);
		$nav_type = esc_attr($instance['nav_type']);
 		?>


		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><strong><?php _e( 'Slideshow Title', 'zoom-builder' ); ?></strong></label><br />
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" type="text" class="widefat" />
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_title'] ); ?> id="<?php echo $this->get_field_id( 'show_title' ); ?>" name="<?php echo $this->get_field_name( 'show_title' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php _e( 'Show Slideshow Title?', 'zoom-builder' ); ?></label>
		</p>


		<fieldset>
			<legend><?php _e( 'Select a Skin', 'zoom-builder' ); ?></legend>

			<p>

 				<input class="RadioClass skinFull" id="<?php echo $this->get_field_id('full-width'); ?>" name="<?php echo $this->get_field_name('slide_skin'); ?>" type="radio" value="full-width" <?php checked( $slide_skin, 'full-width' ); ?> />

				<label for="<?php echo $this->get_field_id('full-width'); ?>" class="RadioLabelClass <?php if ($slide_skin === 'full-width') { echo' RadioSelected"'; } ?>">
					<img src="<?php echo plugins_url('full.png', __FILE__); ?>"  title="<?php _e( 'Full width with thumbnails', 'zoom-builder' ); ?>" class="layout-select" />
					<span><?php _e( 'Full-width', 'zoom-builder' ); ?></span>
				</label>


				<input class="RadioClass skinCaption" id="<?php echo $this->get_field_id('caption'); ?>" name="<?php echo $this->get_field_name('slide_skin'); ?>" type="radio" value="caption" <?php checked( $slide_skin, 'caption' ); ?> />

				<label for="<?php echo $this->get_field_id('caption'); ?>" class="RadioLabelClass <?php if ($slide_skin === 'caption') { echo' RadioSelected"'; } ?>">
					<img src="<?php echo plugins_url('caption.png', __FILE__); ?>"  title="<?php _e( 'Full-width with caption', 'zoom-builder' ); ?>" class="layout-select" />
					<span><?php _e( 'Full-width (2)', 'zoom-builder' ); ?></span>
				</label>


				<input class="RadioClass skinHalf" id="<?php echo $this->get_field_id('half'); ?>" name="<?php echo $this->get_field_name('slide_skin'); ?>" type="radio" value="half" <?php checked( $slide_skin, 'half' ); ?> />

				<label for="<?php echo $this->get_field_id('half'); ?>" class="RadioLabelClass <?php if ($slide_skin === 'half') { echo' RadioSelected"'; } ?>">
					<img src="<?php echo plugins_url('half.png', __FILE__); ?>"  title="<?php _e( 'Caption on the right', 'zoom-builder' ); ?>" class="layout-select" />
					<span><?php _e( 'Side by side', 'zoom-builder' ); ?></span>
				</label>


				<input class="RadioClass skinCarousel" id="<?php echo $this->get_field_id('carousel'); ?>" name="<?php echo $this->get_field_name('slide_skin'); ?>" type="radio" value="carousel" <?php checked( $slide_skin, 'carousel' ); ?> />

				<label for="<?php echo $this->get_field_id('carousel'); ?>" class="RadioLabelClass <?php if ($slide_skin === 'carousel') { echo' RadioSelected"'; } ?>">
					<img src="<?php echo plugins_url('carousel.png', __FILE__); ?>"  title="<?php _e( 'Carousel', 'zoom-builder' ); ?>" class="layout-select" />
					<span><?php _e( 'Carousel', 'zoom-builder' ); ?></span>
				</label>



			</p>


 		</fieldset>

 		<fieldset>
			<legend><?php _e( 'Content Settings', 'zoom-builder' ); ?></legend>

			<p class="wpzlb-fixed">
				<label for="<?php echo $this->get_field_id( 'slideshow_post' ); ?>"><?php _e( 'Select a Post', 'zoom-builder' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'slideshow_post' ); ?>" name="<?php echo $this->get_field_name( 'slideshow_post' ); ?>"  >
					<option value="0" <?php selected( $slideshow_post, 0 ); ?>><?php _e( '&mdash; Select a Post &mdash;', 'zoom-builder' ); ?></option>
					<?php
					foreach ( get_posts( array( 'posts_per_page' => -1, 'post_type' => 'slideshow' ) ) as $post ) {
						?><option value="<?php echo intval( $post->ID ); ?>" <?php selected( $slideshow_post, intval( $post->ID ) ); ?>><?php echo esc_attr( apply_filters( 'the_title', $post->post_title ) ); ?></option><?php
					}
					?>
				</select>
				<span class="howto"><?php printf( __( 'Select which post from <a href="%s">Slideshow posts</a> will be displayed in this slider', 'zoom-builder' ), admin_url( '/edit.php?post_type=slideshow' ) ); ?></span>
			</p>


			<p class="caption-position wpzlb-fixed"<?php if ( $slide_skin != 'half' ) echo ' style="display:none"'; ?>>
				<label for="<?php echo $this->get_field_id( 'caption_position' ); ?>"><?php _e( 'Caption Position', 'zoom-builder' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'caption_position' ); ?>" name="<?php echo $this->get_field_name( 'caption_position' ); ?>">
					<option value="-1" selected=""><?php _e( '&mdash; Select &mdash;', 'zoom-builder' ); ?></option>
 					<option value="right" <?php selected( $caption_position, 'right' ); ?>><?php _e( 'Right', 'zoom-builder' ); ?></option>
					<option value="left" <?php selected( $caption_position, 'left' ); ?>><?php _e( 'Left', 'zoom-builder' ); ?></option>
 				</select><br/>
 			</p>

 			<p class="caption-position-full wpzlb-fixed"<?php if ( $slide_skin != 'full-width' ) echo ' style="display:none"'; ?>>
				<label for="<?php echo $this->get_field_id( 'caption_position_full' ); ?>"><?php _e( 'Caption Position', 'zoom-builder' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'caption_position_full' ); ?>" name="<?php echo $this->get_field_name( 'caption_position_full' ); ?>">
					<option value="-1" selected=""><?php _e( '&mdash; Select &mdash;', 'zoom-builder' ); ?></option>
 					<option value="bottom" <?php selected( $caption_position_full, 'bottom' ); ?>><?php _e( 'Bottom', 'zoom-builder' ); ?></option>
					<option value="top" <?php selected( $caption_position_full, 'top' ); ?>><?php _e( 'Top', 'zoom-builder' ); ?></option>
					<option value="left" <?php selected( $caption_position_full, 'left' ); ?>><?php _e( 'Left', 'zoom-builder' ); ?></option>
					<option value="right" <?php selected( $caption_position_full, 'right' ); ?>><?php _e( 'Right', 'zoom-builder' ); ?></option>
 				</select><br/>
 			</p>

			<p class="text-align wpzlb-fixed"<?php if ( $slide_skin === 'carousel' ) echo ' style="display:none"'; ?>>
				<label for="<?php echo $this->get_field_id( 'caption_align' ); ?>"><?php _e( 'Text Align', 'zoom-builder' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'caption_align' ); ?>" name="<?php echo $this->get_field_name( 'caption_align' ); ?>">
 					<option value="left" <?php selected( $caption_align, 'left' ); ?>><?php _e( 'Left', 'zoom-builder' ); ?></option>
					<option value="center" <?php selected( $caption_align, 'center' ); ?>><?php _e( 'Center', 'zoom-builder' ); ?></option>
 					<option value="right" <?php selected( $caption_align, 'right' ); ?>><?php _e( 'Right', 'zoom-builder' ); ?></option>
  				</select><br/>
 			</p>



		</fieldset>


		<fieldset>
			<legend><?php _e( 'Slideshow Settings', 'zoom-builder' ); ?></legend>

			<p class="navigation-type wpzlb-fixed"<?php if ( $slide_skin === 'carousel' ) echo ' style="display:none"'; ?>>
				<label for="<?php echo $this->get_field_id( 'nav_type' ); ?>"><?php _e( 'Navigation Type', 'zoom-builder' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'nav_type' ); ?>" name="<?php echo $this->get_field_name( 'nav_type' ); ?>">
					<option value="-1" selected=""><?php _e( '&mdash; Select &mdash;', 'zoom-builder' ); ?></option>
					<option value="thumbs" <?php selected( $nav_type, 'thumbs' ); ?>><?php _e( 'Thumbnails', 'zoom-builder' ); ?></option>
					<option value="tabs" <?php selected( $nav_type, 'tabs' ); ?>><?php _e( 'Tabs', 'zoom-builder' ); ?></option>
					<option value="dots" <?php selected( $nav_type, 'dots' ); ?>><?php _e( 'Bullets', 'zoom-builder' ); ?></option>
				</select><br/>
			</p>

			<p class="navigation-position wpzlb-fixed"<?php if ( $slide_skin === 'carousel' ) echo ' style="display:none"'; ?>>
				<label for="<?php echo $this->get_field_id( 'nav_position' ); ?>"><?php _e( 'Navigation Position', 'zoom-builder' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'nav_position' ); ?>" name="<?php echo $this->get_field_name( 'nav_position' ); ?>">
					<option value="-1" selected=""><?php _e( '&mdash; Select &mdash;', 'zoom-builder' ); ?></option>
					<option value="top" <?php selected( $nav_position, 'top' ); ?>><?php _e( 'Top', 'zoom-builder' ); ?></option>
					<option value="bottom" <?php selected( $nav_position, 'bottom' ); ?>><?php _e( 'Bottom', 'zoom-builder' ); ?></option>
				</select><br/>
 			</p>

			<p class="wpzlb-fixed">
				<label for="<?php echo $this->get_field_id( 'thumbs_width' ); ?>"><?php _e( 'Thumbnail Size', 'zoom-builder' ); ?></label>
				<input id="<?php echo $this->get_field_id( 'thumbs_width' ); ?>" name="<?php echo $this->get_field_name( 'thumbs_width' ); ?>" value="<?php echo $thumbs_width; ?>" type="text" size="5" /> x
				<input id="<?php echo $this->get_field_id( 'thumbs_height' ); ?>" name="<?php echo $this->get_field_name( 'thumbs_height' ); ?>" value="<?php echo $thumbs_height; ?>" type="text" size="5" /> px
 			</p>


			<p class="wpzlb-fixed">
				<input class="checkbox" type="checkbox" <?php checked( $instance['autoplay'] ); ?> id="<?php echo $this->get_field_id( 'autoplay' ); ?>" name="<?php echo $this->get_field_name( 'autoplay' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'autoplay' ); ?>"><?php _e( 'Autoplay Slider', 'zoom-builder' ); ?></label>
			</p>

			<p class="wpzlb-fixed">
				<label for="<?php echo $this->get_field_id( 'autoplay_interval' ); ?>"><?php _e( 'Autoplay Interval', 'zoom-builder' ); ?></label>
				<input id="<?php echo $this->get_field_id( 'autoplay_interval' ); ?>" name="<?php echo $this->get_field_name( 'autoplay_interval' ); ?>" value="<?php echo $autoplay_interval; ?>" type="text" size="5" /> ms
			</p>

			<p class="effect wpzlb-fixed"<?php if ( $slide_skin === 'carousel' ) echo ' style="display:none"'; ?>>
				<label for="<?php echo $this->get_field_id( 'slide_effect' ); ?>"><?php _e( 'Slideshow Effect', 'zoom-builder' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'slide_effect' ); ?>" name="<?php echo $this->get_field_name( 'slide_effect' ); ?>">
					<option value="0" <?php selected( $slide_effect, 0 ); ?>><?php _e( 'Fade', 'zoom-builder' ); ?></option>
					<option value="1" <?php selected( $slide_effect, 1 ); ?>><?php _e( 'Slide', 'zoom-builder' ); ?></option>
				</select>
			</p>


 		</fieldset>


		 <?php

	}

}

add_action( 'widgets_init', create_function( '', 'register_widget("WPZOOM_Widget_Media_Slider");' ) );


// Registering Slideshow Post Type
function wpzoom_register_slideshow_posttype() {
	global $pagenow;

	register_post_type( 'slideshow', array(
		'labels' => array(
			'name' => _x( 'Slideshow', 'post type general name', 'zoom-builder' ),
			'singular_name' => _x( 'Slideshow Item', 'post type singular name', 'zoom-builder' ),
			'add_new' => _x( 'Add a New Slideshow Item', 'slideshow item', 'zoom-builder' ),
			'add_new_item' => __( 'Add New Slideshow Item', 'zoom-builder' ),
			'edit_item' => __( 'Edit Slideshow Item', 'zoom-builder' ),
			'new_item' => __( 'New Slideshow Item', 'zoom-builder' ),
			'view_item' => __( 'View Slideshow Item', 'zoom-builder' ),
			'search_items' => __( 'Search Slideshow', 'zoom-builder' ),
			'not_found' =>  __( 'Nothing found', 'zoom-builder' ),
			'not_found_in_trash' => __( 'Nothing found in Trash', 'zoom-builder' ),
			'parent_item_colon' => ''
		),
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
 		'query_var' => true,
		'rewrite' => array( 'slug' => 'slideshow', 'with_front' => false ),
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_icon' => 'dashicons-slides',
		'supports' => array( 'title', 'thumbnail' )
	) );
}
add_action('init', 'wpzoom_register_slideshow_posttype');

function carousel_scripts() {

	if ( !is_single() && !is_page() ) return;

	$current_id = get_the_ID();
	$sidebars_widgets = wp_get_sidebars_widgets();

	if ( empty( $sidebars_widgets ) || !isset( $sidebars_widgets["_wpzlb-page-$current_id-widgets"] ) ) return;

	$current_sidebar = (array)$sidebars_widgets["_wpzlb-page-$current_id-widgets"];
	$matches = preg_grep( '/^wpzoom-media-slider-[0-9]+$/i', $current_sidebar );

	if ( empty( $current_sidebar ) || empty( $matches ) ) return;

	wp_register_script( 'caroufredsel', plugins_url( '/caroufredsel.js', __FILE__ ), array( 'jquery' ), ZOOM_Builder::$version );
	wp_register_script( 'fancybox', plugins_url( '/fancybox/fancybox.js', __FILE__ ), array(), ZOOM_Builder::$version );
  wp_register_style( 'fancybox-css', plugins_url( '/fancybox/jquery.fancybox.css', __FILE__ ), array(), ZOOM_Builder::$version );
	wp_enqueue_script( 'caroufredsel' );
  wp_enqueue_script( 'fancybox' );
	wp_enqueue_style( 'fancybox-css' );

}


add_action( 'wp_enqueue_scripts', 'carousel_scripts' );

if ( !function_exists( 'wpzlb_media_slider_admin_head' ) ) {
	function wpzlb_media_slider_admin_head() {

		?><style type="text/css">
			.wpzoom-builder-widget fieldset {
				border: solid 1px #DFE0E0;
				padding:15px 20px;
				margin: 15px 0 10px;
				background: rgba(248, 248, 248, 0.34);
				-webkit-border-radius: 5px;
				-moz-border-radius: 5px;
				border-radius: 5px;
			}

			.wpzoom-builder-widget legend { font:bold 14px/14px Arial, Helvetica, sans-serif; }
			.wpzlb-fixed { border-bottom: 1px solid #eee; margin:0 0 15px; padding: 0 0 15px;}
			.wpzlb-fixed label { width: 170px; float: left; }
			.wpzlb-fixed span.howto { margin-top: 5px; margin-left:170px; }
			.wpzoom-builder-widget .RadioClass { display: none;  }
			.wpzoom-builder-widget .RadioLabelClass { margin-right: 15px; margin-bottom: 10px;}
			.wpzoom-builder-widget .RadioLabelClass  span { margin:6px 0 0; font-size:10px; padding:1px 7px; border-radius: 20px; background: #8FB2C9; color:#fff; font-weight: bold; text-transform: uppercase; display: block; }
			.wpzoom-builder-widget .RadioSelected  span { background: #3173b2;  }
			.wpzoom-builder-widget img.layout-select { border: solid 2px #c0cdd6; border-radius: 3px; background: #fefefe;  }
			.wpzoom-builder-widget .RadioSelected img.layout-select { border: solid 2px #3173b2; }

			.wpzoom-builder-widget hr { border:none; background: #DFE0E0; height: 1px; }
		</style>

		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('#wpzlb').on('change', '.RadioClass', function(){
					if ( $(this).is(':checked') ) {
						$(this).parent().find('.RadioSelected:not(:checked)').removeClass('RadioSelected');
						$(this).next('label').addClass('RadioSelected');

						if ( $(this).hasClass('skinCarousel') ) {
 							$(this).closest('.widget-content').find('.navigation-type').hide();
							$(this).closest('.widget-content').find('.navigation-position').hide();
							$(this).closest('.widget-content').find('.text-align').hide();
 							$(this).closest('.widget-content').find('.effect').hide();
						} else {
 							$(this).closest('.widget-content').find('.navigation-type').show();
							$(this).closest('.widget-content').find('.navigation-position').show();
							$(this).closest('.widget-content').find('.text-align').show();
 							$(this).closest('.widget-content').find('.effect').show();
						}
						if ( $(this).hasClass('skinHalf') ) {
							$(this).closest('.widget-content').find('.caption-position').show();

						} else {
							$(this).closest('.widget-content').find('.caption-position').hide();
						}
						if ( $(this).hasClass('skinFull') ) {
							$(this).closest('.widget-content').find('.caption-position-full').show();

						} else {
							$(this).closest('.widget-content').find('.caption-position-full').hide();
						}
					}
				});
			});
		</script><?php

	}
}

if ( !function_exists( 'wpzlb_media_slider_admin_init' ) ) {
	function wpzlb_media_slider_admin_init() {
		if ( !ZOOM_Builder_Utils::screen_is_builder() || !current_user_can( 'edit_posts' ) ) return;

		add_action( 'admin_head', 'wpzlb_media_slider_admin_head' );
	}
	add_action( 'admin_init', 'wpzlb_media_slider_admin_init' );
}

if ( !function_exists( 'wpzlb_media_slider_init' ) ) {
	function wpzlb_media_slider_init() {
		new ZOOM_Builder_Post_Slider( array( 'slideshow' ) );
	}
	add_action( 'init', 'wpzlb_media_slider_init' );
}

class ZOOM_Builder_Post_Slider {
    private $sliders = array();

    public function __construct( $args ) {
        foreach ( $args as $key => $slider_args ) {
            if ( is_string( $slider_args ) ) {
                $this->sliders[ $slider_args ] = array();
            }
        }

        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'admin_head-post-new.php', array( $this, 'admin_head' ), 100);
        add_action( 'admin_head-post.php', array( $this, 'admin_head' ), 100);
        add_action( 'wp_ajax_wpzoom_sliderthumb_get', array( $this, 'sliderthumb_get' ) );
        add_action( 'save_post', array( $this, 'save_post' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
    }

    /**
     * Register slider meta boxes.
     *
     * @return void
     */
    public function add_meta_boxes() {
        foreach ( $this->sliders as $page => $args ) {
            add_meta_box(
                'page-slider_' . $page,         // $id
                __( 'Upload Images or Embed Videos', 'zoom-builder' ),          // $title
                array( $this, 'show_metabox' ), // $callback
                $page,                          // $page
                'normal',                       // $context
                'high'                          // $priority
            );
        }
    }

    /**
     * Displays custom metabox with slider options.
     *
     * @return void
     */
    public function show_metabox() {
        global $post;

        $meta = get_post_meta($post->ID, 'wpzoom_slider', true);

        $i = 1;

        $default_image = plugins_url( '/image.png', __FILE__ );

        $html = '<li id="wpzoom_slider_%1$d" class="%2$s">
                    <span class="sort hndle button" title="' . __( 'Click and drag to reorder this slide', 'zoom-builder' ) . '"><img src="' . plugins_url( '/move.png', __FILE__ ) . '" /></span>
                    <span class="wpzoom_slide_remove button" title="' . __( 'Click to remove this slide', 'zoom-builder' ) . '">&times;</span>
                    <div class="wpzoom_slide_type">
                        <input name="wpzoom_slider[%1$d][slideType]" type="hidden" class="wpzoom_slide_type_input" value="%2$s" />
                        <span class="button">' . __( 'Type:', 'zoom-builder' ) . ' <a href="" class="wpzoom_slide_type_image">' . __( 'Image', 'zoom-builder' ) . '</a> | <a href="" class="wpzoom_slide_type_video">' . __( 'Video', 'zoom-builder' ) . '</a></span>
                    </div>

                    <div class="wpzoom_slide_preview"%6$s>
                        <img src="%4$s" height="180" width="250" class="wpzoom_slide_preview_image" data-defaultimg="' . $default_image . '" />
                        <textarea name="wpzoom_slider[%1$d][embedCode]" class="wpzoom_slide_embed_code code" placeholder="' . __( 'Paste embed code here...', 'zoom-builder' ) . '">%5$s</textarea>

                        <div class="wpzoom_slide_actions">
                            <input name="wpzoom_slider[%1$d][imageId]" type="hidden" class="wpzoom_slide_upload_image" value="%3$s" />
                            <span class="wpzoom_slide_upload_image_button button">' . __( 'Choose Image', 'zoom-builder' ) . '</span>
                            <span class="wpzoom_slide_clear_image_button button%8$s">' . __( 'Remove Image', 'zoom-builder' ) . '</span>
                        </div>
                    </div>

                    <textarea name="wpzoom_slider[%1$d][caption]" rows="1" placeholder="' . __( 'Enter title', 'zoom-builder' ) . '" class="wpzoom_slide_caption">%7$s</textarea><br />
                    <input type="text" name="wpzoom_slider[%1$d][url]" value="%10$s" placeholder="' . __( 'Enter slide URL', 'zoom-builder' ) . '" class="wpzoom_slide_url" /><br />
                    <textarea name="wpzoom_slider[%1$d][description]" rows="2" placeholder="' . __( 'Enter description (HTML allowed)&hellip;', 'zoom-builder' ) . '" class="wpzoom_slide_description">%9$s</textarea>
                </li>';

        echo '<input type="hidden" name="wpzoom_slider_meta_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';

        echo '<div class="slider_btn_add"><a class="wpzoom_slide_add button" href="#">' . __( '+ Add Slide', 'zoom-builder' ) . '</a><br class="clear"></div><ul class="wpzoom_slider' . (count($meta) <= 1 ? ' onlyone' : '') . '">';

        if ( !empty( $meta ) ) {

            foreach ( $meta as $item ) {

                $type = isset( $item['slideType'] ) && $item['slideType'] == 'video' ? 'video' : 'image';
                $id = isset( $item['imageId'] ) && is_numeric( $item['imageId'] ) ? intval( $item['imageId'] ) : 0;
                $src = wp_get_attachment_image_src( $id, 'medium' );
                $image = $id > 0 ? $src[0] : '';
                $embed = isset( $item['embedCode'] ) && !empty( $item['embedCode'] ) ? trim( $item['embedCode'] ) : '';
                $embedimg = !empty( $embed ) && false !== ( $url = ZOOM_Builder_Video_API::extract_url_from_embed( $embed ) ) && false !== ( $img = ZOOM_Builder_Video_API::fetch_video_thumbnail( $url, $post->ID ) ) && isset( $img['thumb_url'] ) && !empty( $img['thumb_url'] ) ? ' style="background-image:url(\'' . esc_url( $img['thumb_url'] ) . '\')"' : '';
                $disabled = empty( $image ) || $image == $default_image ? ' button-disabled' : '';
                $caption = isset( $item['caption'] ) && !empty( $item['caption'] ) ? trim( $item['caption'] ) : '';
								$url = isset( $item['url'] ) && !empty( $item['url'] ) ? trim( $item['url'] ) : '';
                $description = isset( $item['description'] ) && !empty( $item['description'] ) ? trim( $item['description'] ) : '';

                printf( $html, $i, $type, ( $type == 'image' ? $id : '' ), ( $type == 'image' && !empty( $image ) ? $image : $default_image ), ( $type == 'video' ? $embed : '' ), ( $type == 'video' ? $embedimg : '' ), $caption, $disabled, $description, $url );

                $i++;

            }

        } else {

            printf( $html, $i, 'image', 0, $default_image, '', '', '', ' button-disabled', '', '' );

        }

        echo '</ul><br class="clear" />';

        return;
    }

    public function save_post( $post_id ) {
        // verify nonce
        if (! isset($_POST['wpzoom_slider_meta_box_nonce']) || ! wp_verify_nonce($_POST['wpzoom_slider_meta_box_nonce'], basename(__FILE__)))
            return $post_id;
        // check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;
        // check permissions
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id))
                return $post_id;
            } elseif (!current_user_can('edit_post', $post_id)) {
                return $post_id;
        }


        $slides = isset($_POST['wpzoom_slider']) ? (array)$_POST['wpzoom_slider'] : array();
        $new = array();

        foreach ( $slides as $slide ) {
            $type = isset( $slide['slideType'] ) && $slide['slideType'] == 'video' ? 'video' : 'image';
            $id = isset( $slide['imageId'] ) && is_numeric( $slide['imageId'] ) ? intval( $slide['imageId'] ) : 0;
            $embed = isset( $slide['embedCode'] ) && !empty( $slide['embedCode'] ) ? trim( $slide['embedCode'] ) : '';
            $caption = isset( $slide['caption'] ) && !empty( $slide['caption'] ) ? trim( $slide['caption'] ) : '';
						$url = isset( $slide['url'] ) && !empty( $slide['url'] ) ? trim( $slide['url'] ) : '';
            $description = isset( $slide['description'] ) && !empty( $slide['description'] ) ? trim( $slide['description'] ) : '';

            $new_arr = array( 'slideType' => $type );

            if ( $type == 'image' && $id > 0 ) {
                $new_arr['imageId'] = $id;
								wp_insert_attachment( array( 'ID' => $id, 'post_parent' => $post_id ) );
            } elseif ( $type == 'video' && !empty( $embed ) )
                $new_arr['embedCode'] = $embed;

            if ( !empty( $caption ) ) $new_arr['caption'] = $caption;
						if ( !empty( $url ) ) $new_arr['url'] = $url;
            if ( !empty( $description ) ) $new_arr['description'] = $description;

            if ( !isset( $new_arr['imageId'] ) && !isset( $new_arr['embedCode'] ) && !isset( $new_arr['caption'] ) && !isset( $new_arr['description'] ) ) continue;

            $new[] = $new_arr;
        }

        update_post_meta($post_id, 'wpzoom_slider', $new);
    }

    public function admin_head() {
        ?>
        <style type="text/css">
        .slider_btn_add { margin:22px 0 0 10px; }
        .wpzoom_slider li, .wpzoom_slider li * { margin: 0; }
        .wpzoom_slider li {  display: inline-block; vertical-align: top; position: relative; text-align: center; background-color: #eee; padding: 5px; border: 1px solid #e0e0e0; border-radius: 4px; margin: 10px !important; }
        .wpzoom_slider li .hndle, .wpzoom_slider li .wpzoom_slide_remove { display: none; position: absolute; top: -6px; z-index: 10; height: auto; padding: 4px; border-radius: 50%; }
        .wpzoom_slider li:hover .hndle, .wpzoom_slider li:hover .wpzoom_slide_remove { display: block; }
        .wpzoom_slider li .hndle { left: -6px; }
        .wpzoom_slider li .hndle img { display: block; height: 16px; width: 16px; }
        .wpzoom_slider li .wpzoom_slide_remove { right: -6px; font-size: 18px !important; line-height: 11px; }
        .wpzoom_slider.onlyone li .wpzoom_slide_remove { display: none; }
        .wpzoom_slider li .wpzoom_slide_remove:hover, .wpzoom_slider li .wpzoom_slide_remove:active { color: red; border-color: red; }
        .wpzoom_slide_preview { display: block; position: relative; background: #f7f7f7 url('<?php echo plugins_url( '/image.png', __FILE__ ); ?>') center no-repeat; background-size: cover; min-height: 167px; width: 250px; border: 1px solid #ccc; margin-bottom: 8px !important; }
        .video .wpzoom_slide_preview { background-image: url('<?php echo plugins_url( '/video.png', __FILE__ ); ?>'); }
        .wpzoom_slide_preview_image { display: block; top: 0; left: 0; right: 0; bottom: 0; background: #fff; height: 100%; width: 100%; border: 0; outline: none; }
        .video .wpzoom_slide_preview_image { display: none; }
        .wpzoom_slide_embed_code { display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; text-shadow: 0 0 3px #f7f7f7, 0 0 3px #f7f7f7, 0 0 3px #f7f7f7, 0 0 3px #f7f7f7, 0 0 3px #f7f7f7, 0 0 3px #f7f7f7, 0 0 3px #f7f7f7; background: rgba(247, 247, 247, 0.7); height: 100%; width: 100%; resize: none; padding: 8px; border: 0; -moz-border-radius: 0; -webkit-border-radius: 0; border-radius: 0; }
        .wpzoom_slider li.video:hover .wpzoom_slide_embed_code, .wpzoom_slide_embed_code:focus { display: block; }
        .wpzoom_slide_type, .wpzoom_slide_actions { display: none; position: absolute; left: 0; right: 0; text-align: center; }
        .wpzoom_slider li:hover .wpzoom_slide_actions, .wpzoom_slider li:hover .wpzoom_slide_type { display: block; }
        .wpzoom_slider li.video:hover .wpzoom_slide_actions { display: none; }
        .wpzoom_slide_actions { top: 78px; }
        .video .wpzoom_slide_actions { display: none; }
        .wpzoom_slide_type { top: -6px; z-index: 9; }
        .wpzoom_slide_type a { text-decoration: none; color: #21759b; }
        .wpzoom_slide_actions span, .wpzoom_slide_type span { /*line-height: 16px; color: #555; background: #f3f3f3; background: -moz-linear-gradient(top, #fefefe, #f4f4f4); background: -webkit-linear-gradient(top, #fefefe, #f4f4f4); background: linear-gradient(to bottom, #fefefe, #f4f4f4); padding: 2px 6px; border: 1px solid #ccc;*/ }
        .wpzoom_slide_actions span { /*cursor: pointer; border-top: 0;*/ }
        .wpzoom_slide_actions span.wpzoom_slide_upload_image_button { /*-moz-border-radius-bottomleft: 4px; -webkit-border-bottom-left-radius: 4px; border-bottom-left-radius: 4px;*/ }
        .wpzoom_slide_actions span.wpzoom_slide_clear_image_button { /*border-left: 0; -moz-border-radius-bottomright: 4px; -webkit-border-bottom-right-radius: 4px; border-bottom-right-radius: 4px;*/ }
        .wpzoom_slide_type span { cursor: default !important; font-size: 10px !important; line-height: 16px !important; height: auto !important; }
        a.wpzoom_slide_type_image { cursor: default; font-weight: bold; color: #555; }
        a.wpzoom_slide_type_video:hover, a.wpzoom_slide_type_video:active { color: #d54e21; }
        .video a.wpzoom_slide_type_image { cursor: pointer; font-weight: normal; color: #21759b; }
        .video a.wpzoom_slide_type_image:hover, .video a.wpzoom_slide_type_image:active { color: #d54e21; }
        .video a.wpzoom_slide_type_video { cursor: default; font-weight: bold; color: #555; }
        .wpzoom_slider li .wpzoom_slide_caption { width: 100%; resize:vertical;  }
				.wpzoom_slider li .wpzoom_slide_url { width: 100%; margin:6px 0 0; }
        .wpzoom_slider li .wpzoom_slide_description { width:250px; resize:vertical; margin:6px 0 0; }
		.wpzoom_slider li br { display: block; }

        </style>
    <?php
    }

    public function assets( $hook ) {
        global $typenow;

        if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) return;
        if ( isset( $typenow ) && ! array_key_exists( $typenow, $this->sliders ) ) return;

        wp_enqueue_script( 'zoom-post-slider', plugins_url( '/post-slider.js', __FILE__ ), array( 'jquery', 'media-upload', 'thickbox' ), ZOOM_Builder::$version );
    }

    public function sliderthumb_get() {
        if ( isset( $_POST['wpzoom_sliderthumb_embedcode'] ) && isset( $_POST['wpzoom_sliderthumb_postid'] ) ) {
            $url = ZOOM_Builder_Video_API::extract_url_from_embed( trim( stripslashes( $_POST['wpzoom_sliderthumb_embedcode'] ) ) );
            $postid = intval( $_POST['wpzoom_sliderthumb_postid'] );

            if ( empty( $url ) || filter_var( $url, FILTER_VALIDATE_URL ) === false || $postid < 1 ) {
                wp_send_json_error();
            }

            $thumb_url = ZOOM_Builder_Video_API::fetch_video_thumbnail( $url, $postid );
            header( 'Content-type: application/json' );
            if ( $thumb_url === false ) {
                wp_send_json_error();
            } else {
                wp_send_json_success( $thumb_url );
            }
        }
    }
}