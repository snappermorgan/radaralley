<?php

/*
/* Pricing Table module
============================================*/

class WPZOOM_Widget_Pricing_Table extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'wpzoom-pricing-table',
			__( 'Pricing Table', 'zoom-builder' ),
			array(
				'description' => __( 'Create Pricing Tables easily.', 'zoom-builder' ),
				'wpzlb_widget' => true
			)
		);

	}

	function widget($args, $instance) {
		extract($args);

		$title = isset($instance['title']) && !empty($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
		$plans = isset($instance['plans']) && is_array($instance['plans']) ? $instance['plans'] : array();
		$amount = count( $plans );

		echo $before_widget;
		echo $before_title . $title . $after_title;

		echo '<div class="pricing-tables pure-g-r">';

		foreach ( $plans as $plan ) {

			?><div class="pure-u-1-<?php echo $amount; ?>">
				<div class="pricing-table pricing-table-<?php echo sanitize_title( $plan['planname'] ); ?>">
					<div class="pricing-table-header"<?php echo preg_match( '/^#[a-f0-9]{6}$/i', trim( $plan['plancolor'] ) ) ? ' style="background-color:' . $plan['plancolor'] . '"' : ''; ?>>
						<h2><?php echo apply_filters( 'the_title', $plan['planname'] ); ?></h2>

						<span class="pricing-table-price">
							<?php echo esc_html( trim( $plan['price'] ) ); ?> <span><?php echo esc_html( trim( $plan['pricetitle'] ) ); ?></span>
						</span>
					</div>

					<ul class="pricing-table-list">
						<?php
						foreach ( preg_split( "/\r\n|\n|\r/", trim( $plan['features'] ) ) as $feature )
							echo '<li>' . apply_filters( 'the_title', $feature ) . '</li>';
						?>
					</ul>

					<?php if ($plan['buttonurl'] && $plan['button']) { ?>

						<a href="<?php echo esc_url( trim( $plan['buttonurl'] ) ); ?>" class="pure-button button-choose"><?php echo apply_filters( 'the_title', $plan['button'] ); ?></a>
					<?php } ?>

				</div>
			</div><?php

		}

		echo '</div> <!-- end pricing-tables -->';

		echo $after_widget;
	}

	function form($instance) {
		$plans = isset($instance['plans']) && is_array($instance['plans']) && !empty($instance['plans']) ? $instance['plans'] : array();
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title') ?>"><?php _e('Title:', 'zoom-builder') ?></label>
			<input type="text" size="35" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title') ?>" value="<?php echo isset($instance['title']) ? esc_attr($instance['title']) : __('Pricing Plans', 'zoom-builder') ?>" class="widefat" />
		</p>

		<div class="wpzoom-sub-widgets">
			<?php if(empty($plans)) echo '<p class="wpzoom-sub-widgets-empty"><em>' . __('No pricing plan&hellip;', 'zoom-builder') . '</em></p>';

			$planname_id = $this->get_field_id('plan%d-planname');
			$planname_name = $this->get_field_name('plans][%d][planname');
			$plancolor_id = $this->get_field_id('plan%d-plancolor');
			$plancolor_name = $this->get_field_name('plans][%d][plancolor');
			$price_id = $this->get_field_id('plan%d-price');
			$price_name = $this->get_field_name('plans][%d][price');
			$pricetitle_id = $this->get_field_id('plan%d-pricetitle');
			$pricetitle_name = $this->get_field_name('plans][%d][pricetitle');
			$features_id = $this->get_field_id('plan%d-features');
			$features_name = $this->get_field_name('plans][%d][features');
			$button_id = $this->get_field_id('plan%d-button');
			$button_name = $this->get_field_name('plans][%d][button');
			$buttonurl_id = $this->get_field_id('plan%d-buttonurl');
			$buttonurl_name = $this->get_field_name('plans][%d][buttonurl');

			foreach($plans as $i=>$plan) { $i++; ?>
				<div class="widget">
					<div class="widget-top">
						<div class="widget-title-action"><a class="widget-action hide-if-no-js" href="#available-widgets"></a></div>
						<div class="widget-title"><h4><?php printf(__('Pricing Plan #%d', 'zoom-builder'), $i) ?></h4></div>
					</div>

					<div class="widget-inside">
						<p>
							<label for="<?php printf( $planname_id, $i ); ?>"><?php _e('Plan Name:', 'zoom-builder') ?></label>
							<input type="text" size="35" id="<?php printf( $planname_id, $i ); ?>" name="<?php printf( $planname_name, $i ); ?>"<?php echo isset( $plan['planname'] ) ? ' value="' . esc_attr( $plan['planname'] ) . '"' : '' ?> class="widefat" />
						</p>

						<p class="plan-color">
							<label for="<?php printf( $plancolor_id, $i ); ?>"><?php _e('Plan Color:', 'zoom-builder') ?></label><br/>
							<input type="text" size="7" id="<?php printf( $plancolor_id, $i ); ?>" name="<?php printf( $plancolor_name, $i ); ?>"<?php echo isset( $plan['plancolor'] ) ? ' value="' . esc_attr( $plan['plancolor'] ) . '"' : '' ?> class="code" />
						</p>

						<p>
							<label for="<?php printf( $price_id, $i ); ?>"><?php _e('Price:', 'zoom-builder') ?></label>
							<input type="text" size="35" id="<?php printf( $price_id, $i ); ?>" name="<?php printf( $price_name, $i ); ?>"<?php echo isset( $plan['price'] ) ? ' value="' . esc_attr( $plan['price'] ) . '"' : '' ?> class="widefat" />
						</p>

						<p>
							<label for="<?php printf( $pricetitle_id, $i ); ?>"><?php _e('Price Title:', 'zoom-builder') ?></label>
							<input type="text" size="35" id="<?php printf( $pricetitle_id, $i ); ?>" name="<?php printf( $pricetitle_name, $i ); ?>"<?php echo isset( $plan['pricetitle'] ) ? ' value="' . esc_attr( $plan['pricetitle'] ) . '"' : '' ?> class="widefat" />
						</p>

						<p>
							<label for="<?php printf( $features_id, $i ); ?>"><?php _e('Features:', 'zoom-builder') ?></label>
							<textarea id="<?php printf( $features_id, $i ); ?>" name="<?php printf( $features_name, $i ); ?>" rows="4" class="widefat"><?php echo isset( $plan['features'] ) ? '' . esc_textarea( $plan['features'] ) . '' : '' ?></textarea>
							<span class="description">Enter each feature in a new line</span>

						</p>

						<p>
							<label for="<?php printf( $button_id, $i ); ?>"><?php _e('Button Text:', 'zoom-builder') ?></label>
							<input type="text" size="35" id="<?php printf( $button_id, $i ); ?>" name="<?php printf( $button_name, $i ); ?>"<?php echo isset( $plan['button'] ) ? ' value="' . esc_attr( $plan['button'] ) . '"' : '' ?> class="widefat" />
						</p>

						<p>
							<label for="<?php printf( $buttonurl_id, $i ); ?>"><?php _e('Button URL:', 'zoom-builder') ?></label>
							<input type="text" size="35" id="<?php printf( $buttonurl_id, $i ); ?>" name="<?php printf( $buttonurl_name, $i ); ?>"<?php echo isset( $plan['buttonurl'] ) ? ' value="' . esc_attr( $plan['buttonurl'] ) . '"' : '' ?> class="widefat" />
						</p>

						<div class="widget-control-actions">
							<div class="alignleft">
								<a class="wpzoom-widget-control-remove" href="#remove"><?php _e('Delete', 'zoom-builder') ?></a> |
								<a class="widget-control-close" href="#close"><?php _e('Close', 'zoom-builder') ?></a>
							</div>
							<br class="clear" />
						</div>
					</div>
				</div>
			<?php } ?>
		</div>

		<p><a class="button wpzoom-widget-control-addnew" href="#addnew"><?php _e('+ Add a new plan', 'zoom-builder') ?></a></p>

		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = isset( $new_instance['title'] ) ? strip_tags( trim( $new_instance['title'] ) ) : '';
		$instance['plans'] = array();

		if ( isset( $new_instance['plans'] ) && is_array( $new_instance['plans'] ) ) {
			foreach ( $new_instance['plans'] as $plan ) {
				$temp = array();

				$temp['planname'] = trim( $plan['planname'] );
				$temp['plancolor'] = preg_match( '/^#[a-f0-9]{6}$/i', $plan['plancolor'] ) ? $plan['plancolor'] : '';
				$temp['price'] = trim( $plan['price'] );
				$temp['pricetitle'] = trim( $plan['pricetitle'] );
				$temp['features'] = trim( $plan['features'] );
				$temp['button'] = trim( $plan['button'] );
				$temp['buttonurl'] = esc_url_raw( $plan['buttonurl'] );

				if ( !empty( $temp ) )
					$instance['plans'][] = $temp;
			}
		}

		return $instance;
	}

}

add_action( 'widgets_init', create_function( '', 'register_widget("WPZOOM_Widget_Pricing_Table");' ) );




function wpzlb_pricing_admin_head() {
	?>

	<style type="text/css">
		.wpzoom-sub-widgets {
			margin: 2em 0;
		}

		.wpzoom-sub-widgets .widget {
			width: auto;
		}

		.wpzoom-sub-widgets .widget:last-child {
			margin-bottom: 0;
		}

		.wpzoom-sub-widgets .widget .widget-top {
			cursor: auto;
		}

		.wpzoom-sub-widgets-empty {
			text-align: center;
			color: #666;
			padding: 5px !important;
			border: 1px dashed #666;
		}

		.wpzoom-widget-control-addnew img {
			vertical-align: middle;
		}
	</style>



	<script type="text/javascript">
		jQuery(function($){
			var color_picker_options = ( typeof wpzlb_color_picker_palettes != 'undefined' ) ? { palettes: wpzlb_color_picker_palettes } : {};

			function updateColorPickers() {
				$(this).find('.plan-color > input:not(.wp-color-picker)').wpColorPicker( color_picker_options );
			}

			$(document.body).on('click.widgets-toggle', '.wpzoom-builder-widget-pricing-table', updateColorPickers);
			wpzlbWidgetSaveCallbacks.push(updateColorPickers);

			$('#wpzlb').on('click', '.wpzoom-sub-widgets .wpzoom-widget-control-remove', function(e){
				e.preventDefault();

				$(this).closest('.widget').slideUp('slow', function(){
					var $widgets = $(this).closest('.wpzoom-sub-widgets');

					$(this).remove();

					if($('.widget', $widgets).size() <= 0) {
						$('<p class="wpzoom-sub-widgets-empty"><em><?php _e('No pricing plan&hellip;', 'zoom-builder') ?></em></p>').prependTo($widgets);
					} else {
						$('.widget', $widgets).each(function(i){
							$('.widget-title h4', $(this)).html('<?php _e('Pricing Plan #', 'zoom-builder') ?>' + (i + 1));
						});
					}
				});

				return false;
			});

			$('#wpzlb').on('click', '.wpzoom-widget-control-addnew', function(e){
				e.preventDefault();

				var instance = $(this).closest('.widget').attr('id').split('-').pop(),
						$widgets = $(this).parent().parent().find('.wpzoom-sub-widgets'),
						amount = $('.widget', $widgets).size(),
						newPlanId = amount + 1,
						inputIdPrefix = 'wpzoom-pricing-table-' + instance + '-plan' + newPlanId + '-',
						inputNamePrefix = 'widget-wpzoom-pricing-table[' + instance + '][plans][' + newPlanId + '][';

				if(amount < 1) $widgets.empty();

				$('<div class="widget">' +
						'<div class="widget-top">' +
							'<div class="widget-title-action"><a class="widget-action hide-if-no-js" href="#available-widgets"></a></div>' +
							'<div class="widget-title"><h4><?php _e('Pricing Plan #', 'zoom-builder') ?>' + newPlanId + '</h4></div>' +
						'</div>' +

						'<div class="widget-inside" style="display:block">' +
							'<p>' +
								'<label for="' + inputIdPrefix + 'planname"><?php _e('Plan Name:', 'zoom-builder') ?></label> ' +
								'<input type="text" size="35" id="' + inputIdPrefix + 'planname" name="' + inputNamePrefix + 'planname]" class="widefat" />' +
							'</p>' +

							'<p class="plan-color">' +
								'<label for="' + inputIdPrefix + 'plancolor"><?php _e('Plan Color:', 'zoom-builder') ?></label><br/> ' +
								'<input type="text" size="7" id="' + inputIdPrefix + 'plancolor" name="' + inputNamePrefix + 'plancolor]" class="code" />' +
							'</p>' +

							'<p>' +
								'<label for="' + inputIdPrefix + 'price"><?php _e('Price:', 'zoom-builder') ?></label> ' +
								'<input type="text" size="35" id="' + inputIdPrefix + 'price" name="' + inputNamePrefix + 'price]" class="widefat" />' +
							'</p>' +

							'<p>' +
								'<label for="' + inputIdPrefix + 'pricetitle"><?php _e('Price Title:', 'zoom-builder') ?></label> ' +
								'<input type="text" size="35" id="' + inputIdPrefix + 'pricetitle" name="' + inputNamePrefix + 'pricetitle]" class="widefat" />' +
							'</p>' +

							'<p>' +
								'<label for="' + inputIdPrefix + 'features"><?php _e('Features:', 'zoom-builder') ?></label> ' +
								'<textarea id="' + inputIdPrefix + 'features" name="' + inputNamePrefix + 'features]" rows="4" class="widefat"></textarea>' +
								'<span class="description">Enter each feature in a new line</span>' +
							'</p>' +

							'<p>' +
								'<label for="' + inputIdPrefix + 'button"><?php _e('Button Text:', 'zoom-builder') ?></label> ' +
								'<input type="text" size="35" id="' + inputIdPrefix + 'button" name="' + inputNamePrefix + 'button]" class="widefat" />' +
							'</p>' +

							'<p>' +
								'<label for="' + inputIdPrefix + 'buttonurl"><?php _e('Button URL:', 'zoom-builder') ?></label> ' +
								'<input type="text" size="35" id="' + inputIdPrefix + 'buttonurl" name="' + inputNamePrefix + 'buttonurl]" class="widefat" />' +
							'</p>' +

							'<div class="widget-control-actions">' +
								'<div class="alignleft">' +
									'<a class="wpzoom-widget-control-remove" href="#remove"><?php _e('Delete', 'zoom-builder') ?></a> | ' +
									'<a class="widget-control-close" href="#close"><?php _e('Close', 'zoom-builder') ?></a>' +
								'</div>' +
								'<br class="clear" />' +
							'</div>' +
						'</div>' +
					'</div>').appendTo($widgets).hide().slideDown('slow');

				$('.plan-color > input').wpColorPicker( color_picker_options );

				return false;
			});
		});
	</script>

	<?php
}



function wpzlb_pricing_admin_init() {
	if ( !ZOOM_Builder_Utils::screen_is_builder() || !current_user_can( 'edit_posts' ) ) return;

	add_action( 'admin_head', 'wpzlb_pricing_admin_head' );
}
add_action( 'admin_init', 'wpzlb_pricing_admin_init' );