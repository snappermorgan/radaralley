<?php

/*
/* Call to action widget module
============================================*/

class WPZOOM_Widget_Call_To_Action extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'wpzoom-call-to-action',
			__( 'Call to Action', 'zoom-builder' ),
			array(
				'description' => __( 'A call to action widget with title, description, and linkable button.', 'zoom-builder' ),
				'wpzlb_widget' => true
			),
			array(
				'width' => 530

			)
		);

	}

	public function widget( $args, $instance ) {

		extract( $args );

		/* User-selected settings. */
		$title = isset( $instance['title'] ) && !empty( $instance['title'] ) ? apply_filters( 'widget_title', trim( $instance['title'] ), $instance, $this->id_base ) : '';
		$separator = isset( $instance['separator'] ) && !empty( $instance['separator'] ) ? apply_filters( 'widget_title', trim( $instance['separator'] ), $instance, $this->id_base ) : '';
		$desc = isset( $instance['description'] ) && !empty( $instance['description'] ) ? apply_filters( 'widget_text', trim( $instance['description'] ), $instance ) : '';
		$caption_align = esc_attr($instance['caption_align']);
		$btntxt = isset( $instance['button_text'] ) && !empty( $instance['button_text'] ) ? apply_filters( 'widget_title', trim( $instance['button_text'] ) ) : '';
		$btnurl = isset( $instance['button_url'] ) && !empty( $instance['button_url'] ) ? esc_url( trim( $instance['button_url'] ) ) : '';
		$btntxtclr = isset( $instance['button_txtcolor'] ) && !empty( $instance['button_txtcolor'] ) && preg_match( '/#[a-f0-9]{6}/i', trim( $instance['button_txtcolor'] ) ) ? trim( $instance['button_txtcolor'] ) : '';
		$btnbgclr = isset( $instance['button_bgcolor'] ) && !empty( $instance['button_bgcolor'] ) && preg_match( '/#[a-f0-9]{6}/i', trim( $instance['button_bgcolor'] ) ) ? trim( $instance['button_bgcolor'] ) : '';
		$btnstyle = !empty( $btntxtclr ) || !empty( $btnbgclr ) ? ' style="' . ( !empty( $btntxtclr ) ? 'color:' . $btntxtclr . ';' : '' ) . ( !empty( $btnbgclr ) ? 'background-color:' . $btnbgclr : '' ) . '"' : '';

		$btntwotxt = isset( $instance['button_2_text'] ) && !empty( $instance['button_2_text'] ) ? apply_filters( 'widget_title', trim( $instance['button_2_text'] ) ) : '';
		$btntwourl = isset( $instance['button_2_url'] ) && !empty( $instance['button_2_url'] ) ? esc_url( trim( $instance['button_2_url'] ) ) : '';
		$btntwotxtclr = isset( $instance['button_2_txtcolor'] ) && !empty( $instance['button_2_txtcolor'] ) && preg_match( '/#[a-f0-9]{6}/i', trim( $instance['button_2_txtcolor'] ) ) ? trim( $instance['button_2_txtcolor'] ) : '';
		$btntwobgclr = isset( $instance['button_2_bgcolor'] ) && !empty( $instance['button_2_bgcolor'] ) && preg_match( '/#[a-f0-9]{6}/i', trim( $instance['button_2_bgcolor'] ) ) ? trim( $instance['button_2_bgcolor'] ) : '';
		$btntwostyle = !empty( $btntwotxtclr ) || !empty( $btntwobgclr ) ? ' style="' . ( !empty( $btntwotxtclr ) ? 'color:' . $btntwotxtclr . ';' : '' ) . ( !empty( $btntwobgclr ) ? 'background-color:' . $btntwobgclr : '' ) . '"' : '';
 		$button_skin = esc_attr($instance['button_skin']);


		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */

		echo '<div class="wpzlb-call-to-action skin-'. $button_skin .'">';

			if ($button_skin == 'btn-right' || $button_skin == 'centered-top' || $button_skin == 'btn-left') {

				if ( $btntxt && $btnurl ) echo '<div class="wpzlb-button"><a href="' . $btnurl . '"' . $btnstyle . '>' . $btntxt . '</a></div>';

			}


			if ( $title || $desc ) {

				echo '<div class="wpzlb-inner-content align-' .$caption_align . '">';

					if ( $title ) echo $before_title . $title . $after_title;

					if ( $desc ) echo '<span class="wpzlb-description">' . nl2br($desc) . '</span>';

				echo '</div>';

			}

			if ( $button_skin == 'centered') {

				if ( $btntxt && $btnurl ) echo '<div class="wpzlb-clearfix"></div><div class="wpzlb-button"><a href="' . $btnurl . '"' . $btnstyle . '>' . $btntxt . '</a></div>';

			}


			if ( $button_skin == 'two-btn') {

				echo '<div class="wpzlb-clearfix"></div><div class="wpzlb-button align-' .$caption_align . '">';

					if ( $btntxt && $btnurl ) echo '<a href="' . $btnurl . '"' . $btnstyle . '>' . $btntxt . '</a>';

					if ( $btntwotxt && $separator ) echo '<span class="wpzlb-call-separator">'. $separator .'</span>';

					if ( $btntwotxt && $btntwourl && $button_skin == 'two-btn') echo '<a href="' . $btntwourl . '"' . $btntwostyle . '>' . $btntwotxt . '</a>';

				echo '</div';

			}

		echo '</div>';

		/* After widget (defined by themes). */
		echo $after_widget;

	}

	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['separator'] = strip_tags( $new_instance['separator'] );
		$instance['caption_align'] = strip_tags($new_instance['caption_align']);
		$instance['description'] = current_user_can('unfiltered_html') ? $new_instance['description'] : stripslashes( wp_filter_post_kses( addslashes( $new_instance['description'] ) ) );
		$instance['button_text'] = strip_tags( $new_instance['button_text'] );
		$instance['button_url'] = strip_tags( $new_instance['button_url'] );
		$instance['button_txtcolor'] = preg_match( '/#[a-f0-9]{6}/i', trim( $new_instance['button_txtcolor'] ) ) ? trim( $new_instance['button_txtcolor'] ) : '';
		$instance['button_bgcolor'] = preg_match( '/#[a-f0-9]{6}/i', trim( $new_instance['button_bgcolor'] ) ) ? trim( $new_instance['button_bgcolor'] ) : '';

		$instance['button_2_text'] = strip_tags( $new_instance['button_2_text'] );
		$instance['button_2_url'] = strip_tags( $new_instance['button_2_url'] );
		$instance['button_2_txtcolor'] = preg_match( '/#[a-f0-9]{6}/i', trim( $new_instance['button_2_txtcolor'] ) ) ? trim( $new_instance['button_2_txtcolor'] ) : '';
		$instance['button_2_bgcolor'] = preg_match( '/#[a-f0-9]{6}/i', trim( $new_instance['button_2_bgcolor'] ) ) ? trim( $new_instance['button_2_bgcolor'] ) : '';
		$instance['button_skin'] = strip_tags($new_instance['button_skin']);

		return $instance;

	}

	public function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => '', 'description' => '', 'caption_align' => 'left', 'separator' => 'or', 'button_skin' => 'btn-right', 'button_text' => '', 'button_url' => '', 'button_txtcolor' => '', 'button_bgcolor' => '', 'button_2_text' => '', 'button_2_url' => '', 'button_2_txtcolor' => '', 'button_2_bgcolor' => '' );
		$instance = wp_parse_args( (array)$instance, $defaults );
		$button_skin = esc_attr($instance['button_skin']);
		$caption_align = esc_attr($instance['caption_align']);

		?>


		<fieldset>
			<legend>Select a Skin:</legend>

			<p>

 				<input class="RadioClass" id="<?php echo $this->get_field_id('btn-right'); ?>" name="<?php echo $this->get_field_name('button_skin'); ?>" type="radio" value="btn-right" <?php checked( $button_skin, 'btn-right' ); ?> />

				<label for="<?php echo $this->get_field_id('btn-right'); ?>" class="RadioLabelClass <?php if ($button_skin === 'btn-right') { echo' RadioSelected"'; } ?>">
					<img src="<?php echo plugins_url('skin-1.png', __FILE__); ?>"  title="<?php _e( 'Button on the right', 'zoom-builder' ); ?>" class="layout-select" />
					<span><?php _e( 'Right button', 'zoom-builder' ); ?></span>
				</label>


				<input class="RadioClass" id="<?php echo $this->get_field_id('btn-left'); ?>" name="<?php echo $this->get_field_name('button_skin'); ?>" type="radio" value="btn-left" <?php checked( $button_skin, 'btn-left' ); ?> />

				<label for="<?php echo $this->get_field_id('btn-left'); ?>" class="RadioLabelClass <?php if ($button_skin === 'btn-left') { echo' RadioSelected"'; } ?>">
					<img src="<?php echo plugins_url('skin-2.png', __FILE__); ?>"  title="<?php _e( 'Button on the left', 'zoom-builder' ); ?>" class="layout-select" />
					<span><?php _e( 'Left button', 'zoom-builder' ); ?></span>
				</label>


				<input class="RadioClass" id="<?php echo $this->get_field_id('centered'); ?>" name="<?php echo $this->get_field_name('button_skin'); ?>" type="radio" value="centered" <?php checked( $button_skin, 'centered' ); ?> />

				<label for="<?php echo $this->get_field_id('centered'); ?>" class="RadioLabelClass <?php if ($button_skin === 'centered') { echo' RadioSelected"'; } ?>">
					<img src="<?php echo plugins_url('skin-3.png', __FILE__); ?>"  title="<?php _e( 'Centered', 'zoom-builder' ); ?>" class="layout-select" />
					<span><?php _e( 'Center button', 'zoom-builder' ); ?></span>
				</label>


				<input class="RadioClass" id="<?php echo $this->get_field_id('centered-top'); ?>" name="<?php echo $this->get_field_name('button_skin'); ?>" type="radio" value="centered-top" <?php checked( $button_skin, 'centered-top' ); ?> />

				<label for="<?php echo $this->get_field_id('centered-top'); ?>" class="RadioLabelClass <?php if ($button_skin === 'centered-top') { echo' RadioSelected"'; } ?>">
					<img src="<?php echo plugins_url('skin-4.png', __FILE__); ?>"  title="<?php _e( 'Centered with button at the top', 'zoom-builder' ); ?>" class="layout-select" />
					<span><?php _e( 'Top button', 'zoom-builder' ); ?></span>
				</label>


				<input class="RadioClass skinTwobtn" id="<?php echo $this->get_field_id('two-btn'); ?>" name="<?php echo $this->get_field_name('button_skin'); ?>" type="radio" value="two-btn" <?php checked( $button_skin, 'two-btn' ); ?> />

				<label for="<?php echo $this->get_field_id('two-btn'); ?>" class="RadioLabelClass <?php if ($button_skin === 'two-btn') { echo' RadioSelected"'; } ?>">
					<img src="<?php echo plugins_url('skin-5.png', __FILE__); ?>"  title="<?php _e( 'Two buttons centered', 'zoom-builder' ); ?>" class="layout-select" />
					<span><?php _e( 'Two buttons', 'zoom-builder' ); ?></span>
				</label>



			</p>


 		</fieldset>

 		<fieldset>
 			<legend>Content</legend>

	 		<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Headline:', 'zoom-builder' ); ?></label><br />
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php _e( 'Content:', 'zoom-builder' ); ?></label><br />
				<textarea id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>" rows="4" class="widefat"><?php echo $instance['description']; ?></textarea>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'caption_align' ); ?>"><?php _e( 'Text Align:', 'zoom-builder' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'caption_align' ); ?>" name="<?php echo $this->get_field_name( 'caption_align' ); ?>">
				<option value="left" <?php selected( $caption_align, 'left' ); ?>><?php _e( 'Left', 'zoom-builder' ); ?></option>
				<option value="right" <?php selected( $caption_align, 'right' ); ?>><?php _e( 'Right', 'zoom-builder' ); ?></option>
				<option value="center" <?php selected( $caption_align, 'center' ); ?>><?php _e( 'Center', 'zoom-builder' ); ?></option>
				</select><br/>
			</p>

		</fieldset>

		<fieldset class="call-to-action-controls">
			<legend><?php _e( 'Button', 'zoom-builder' ); ?></legend>
			<p>
				<label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php _e( 'Label:', 'zoom-builder' ); ?></label><br />
				<input id="<?php echo $this->get_field_id( 'button_text' ); ?>" name="<?php echo $this->get_field_name( 'button_text' ); ?>" value="<?php echo $instance['button_text']; ?>" type="text" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'button_url' ); ?>"><?php _e( 'URL:', 'zoom-builder' ); ?></label><br />
				<input id="<?php echo $this->get_field_id( 'button_url' ); ?>" name="<?php echo $this->get_field_name( 'button_url' ); ?>" value="<?php echo $instance['button_url']; ?>" type="text" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'button_txtcolor' ); ?>"><?php _e( 'Text Color:', 'zoom-builder' ); ?></label><br />
				<input class="call-to-action-text-picker" id="<?php echo $this->get_field_id( 'button_txtcolor' ); ?>" name="<?php echo $this->get_field_name( 'button_txtcolor' ); ?>" value="<?php echo $instance['button_txtcolor']; ?>" type="text" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'button_bgcolor' ); ?>"><?php _e( 'Background Color:', 'zoom-builder' ); ?></label><br />
				<input class="call-to-action-background-picker" id="<?php echo $this->get_field_id( 'button_bgcolor' ); ?>" name="<?php echo $this->get_field_name( 'button_bgcolor' ); ?>" value="<?php echo $instance['button_bgcolor']; ?>" type="text" class="widefat" />
			</p>
		</fieldset>

		<fieldset class="button-separator"<?php if ( $button_skin != 'two-btn' ) echo ' style="display:none"'; ?>>
 			<legend><?php _e( 'Separator', 'zoom-builder' ); ?></legend>

			<p>
 				<input id="<?php echo $this->get_field_id( 'separator' ); ?>" name="<?php echo $this->get_field_name( 'separator' ); ?>" value="<?php echo $instance['separator']; ?>" type="text" class="widefat" />
 				<span class="description"><?php _e( 'Separator between buttons', 'zoom-builder' ); ?></span>
			</p>

		</fieldset>

		<fieldset class="call-to-action-controls second-button"<?php if ( $button_skin != 'two-btn' ) echo ' style="display:none"'; ?>>
			<legend><?php _e( 'Button #2', 'zoom-builder' ); ?></legend>
			<p>
				<label for="<?php echo $this->get_field_id( 'button_2_text' ); ?>"><?php _e( 'Label:', 'zoom-builder' ); ?></label><br />
				<input id="<?php echo $this->get_field_id( 'button_2_text' ); ?>" name="<?php echo $this->get_field_name( 'button_2_text' ); ?>" value="<?php echo $instance['button_2_text']; ?>" type="text" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'button_2_url' ); ?>"><?php _e( 'URL:', 'zoom-builder' ); ?></label><br />
				<input id="<?php echo $this->get_field_id( 'button_2_url' ); ?>" name="<?php echo $this->get_field_name( 'button_2_url' ); ?>" value="<?php echo $instance['button_2_url']; ?>" type="text" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'button_2_txtcolor' ); ?>"><?php _e( 'Text Color:', 'zoom-builder' ); ?></label><br />
				<input class="call-to-action-text-picker" id="<?php echo $this->get_field_id( 'button_2_txtcolor' ); ?>" name="<?php echo $this->get_field_name( 'button_2_txtcolor' ); ?>" value="<?php echo $instance['button_2_txtcolor']; ?>" type="text" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'button_2_bgcolor' ); ?>"><?php _e( 'Background Color:', 'zoom-builder' ); ?></label><br />
				<input class="call-to-action-background-picker" id="<?php echo $this->get_field_id( 'button_2_bgcolor' ); ?>" name="<?php echo $this->get_field_name( 'button_2_bgcolor' ); ?>" value="<?php echo $instance['button_2_bgcolor']; ?>" type="text" class="widefat" />
			</p>
		</fieldset><?php

	}

	public static function admin_init() {

		if ( !ZOOM_Builder_Utils::screen_is_builder() || !current_user_can( 'edit_posts' ) ) return;

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_head', array( __CLASS__, 'scripts' ) );

	}

	public static function assets() {

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

	}

	public static function scripts() {

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

			.wpzoom-builder-widget .RadioClass { display: none;  }
			.wpzoom-builder-widget .RadioLabelClass { display: inline; float: left; margin-right: 10px; }
			.wpzoom-builder-widget .RadioLabelClass  span { margin:6px 0 0; font-size:10px; padding:1px 7px; border-radius: 20px; background: #8FB2C9; color:#fff; font-weight: bold; text-transform: uppercase; display: block; }
			.wpzoom-builder-widget .RadioSelected  span { background: #3173b2;  }
			.wpzoom-builder-widget img.layout-select { border: solid 2px #c0cdd6; border-radius: 3px; background: #fefefe;  }
			.wpzoom-builder-widget .RadioSelected img.layout-select { border: solid 2px #3173b2; }

			.wpzoom-builder-widget hr { border:none; background: #DFE0E0; height: 1px; }
		</style>

		<script type="text/javascript">
			jQuery(function($){

				var color_picker_options = ( typeof wpzlb_color_picker_palettes != 'undefined' ) ? { palettes: wpzlb_color_picker_palettes } : {};

				function updateColorPickers() {
					$(this).find('.call-to-action-text-picker:not(.wp-color-picker), .call-to-action-background-picker:not(.wp-color-picker)').wpColorPicker( color_picker_options );
				}

				$(document.body).on('click.widgets-toggle', '.wpzoom-builder-widget-call-to-action', updateColorPickers);
				wpzlbWidgetSaveCallbacks.push(updateColorPickers);

				$('#wpzlb').on('change', '.RadioClass', function(){
					if ( $(this).is(':checked') ) {
						$(this).parent().find('.RadioSelected:not(:checked)').removeClass('RadioSelected');
						$(this).next('label').addClass('RadioSelected');

						if ( $(this).hasClass('skinTwobtn') ) {
							$(this).closest('.widget-content').find('.button-separator').show();
							$(this).closest('.widget-content').find('.second-button').show();
						} else {
							$(this).closest('.widget-content').find('.button-separator').hide();
							$(this).closest('.widget-content').find('.second-button').hide();
						}
					}
				});
			});
		</script><?php

	}

}

add_action( 'widgets_init', create_function( '', 'register_widget("WPZOOM_Widget_Call_To_Action");' ) );
add_action( 'admin_init', array( 'WPZOOM_Widget_Call_To_Action', 'admin_init' ) );