<?php

/*
/* Contact form module
============================================*/

class WPZOOM_Widget_Contact_Form extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'wpzoom-contact-form',
			__( 'Contact Form', 'zoom-builder' ),
			array(
				'description' => __( 'A simple contact form widget.', 'zoom-builder' ),
				'wpzlb_widget' => true
			),
			array( 'width' => 600 )
		);

	}

	public function widget( $args, $instance ) {

		extract( $args );

		global $wpzlbcm_submit_result;

		$widget_num = absint( substr( $widget_id, strrpos( $widget_id, '-' ) + 1 ) );
		$submit_msg = isset( $wpzlbcm_submit_result[ $widget_num ] ) ? ( $wpzlbcm_submit_result[ $widget_num ] == 'success' ? __( 'Successfully sent!', 'zoom-builder' ) : ( $wpzlbcm_submit_result[ $widget_num ] == 'failed' ? __( 'Sending failed!', 'zoom-builder' ) : '' ) ) : '';
		$title = isset( $instance['title'] ) && !empty( $instance['title'] ) ? apply_filters( 'widget_title', trim( $instance['title'] ), $instance, $this->id_base ) : '';
		$submit_label = isset( $instance['submit_label'] ) && !empty( $instance['submit_label'] ) ? esc_attr( trim( $instance['submit_label'] ) ) : '';
		$fields = isset( $instance['fields'] ) && is_array( $instance['fields'] ) && !empty( $instance['fields'] ) ? $instance['fields'] : array();
		$errspan = '<span class="wpzlbcm-field-error"></span>';

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		?><form action="<?php the_permalink(); ?>" method="post" class="wpzlbcm-form" novalidate>
			<input type="hidden" name="wpzlbcm_submit" value="true" />
			<input type="hidden" name="wpzlbcm_widgetnum" value="<?php echo $widget_num; ?>" />

			<?php if ( !empty( $submit_msg ) ) echo '<p class="wpzlbcm-submit-msg wpzlbcm-submit-' . $wpzlbcm_submit_result[ $widget_num ] . '">' . $submit_msg . '</p>'; ?>

			<fieldset>
				<?php
				foreach ( $fields as $num => $field ) {

					$id = absint( $num );
					$name = apply_filters( 'the_title', trim( $field['name'] ) );
					$type = isset( $field['type'] ) && in_array( $field['type'], array( 'email', 'checkbox', 'radio', 'textarea', 'select' ) ) ? $field['type'] : 'text';
					$required = isset( $field['required'] ) && $field['required'] === true ? ' data-required="yes"' : '';
					$options = isset( $field['options'] ) && is_array( $field['options'] ) && !empty( $field['options'] ) && isset( $field['options']['names'] ) && is_array( $field['options']['names'] ) && !empty( $field['options']['names'] ) ? $field['options'] : array( 'names' => array() );
					$options_names = isset( $options['names'] ) && is_array( $options['names'] ) && !empty( $options['names'] ) ? $options['names'] : array();
					$options_selected = isset( $options['selected'] ) && is_array( $options['selected'] ) && !empty( $options['selected'] ) ? $options['selected'] : array();
					$options_required = isset( $options['required'] ) && is_array( $options['required'] ) && !empty( $options['required'] ) ? $options['required'] : array();
					$options_multiple = isset( $field['multiple'] ) && $field['multiple'] === true;
					$startval = isset( $field['startval'] ) && !empty( $field['startval'] ) ? esc_attr( trim( $field['startval'] ) ) : '';
					$showlabel = ( isset( $field['showlabel'] ) && $field['showlabel'] === true ) || !isset( $field['showlabel'] );
					$placeholder = isset( $field['placeholder'] ) && !empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( trim( $field['placeholder'] ) ) . '"' : '';
					$minlen = isset( $field['minlen'] ) && absint( $field['minlen'] ) > 0 ? ' data-minlen="' . absint( $field['minlen'] ) . '"' : '';
					$maxlen = isset( $field['maxlen'] ) && absint( $field['maxlen'] ) > 0 ? ' data-maxlen="' . absint( $field['maxlen'] ) . '"' : '';
					$classes = isset( $field['classes'] ) && !empty( $field['classes'] ) ? ' ' . preg_replace( '/[^_a-z0-9\- ]/i', '', trim( $field['classes'] ) ) : '';

					?><p class="wpzlbcm-fieldwrap wpzlbcm-fieldwrap-<?php echo $id . $classes; ?>">
						<?php
						if ( $type != 'checkbox' && $type != 'radio' ) echo '<label>';

						if ( $showlabel ) echo '<span class="wpzlbcm-labeltext">' . $name . '<span class="wpzlbcm-labelcolon">:</span></span> ';

						switch ( $type ) {

							case 'textarea':
								echo '<textarea name="wpzlbcm_field_' . $id . '" class="wpzlbcm-field wpzlbcm-field-' . $id . ' wpzlbcm-field-type-textarea"' . $placeholder . $minlen . $maxlen . $required . '>' . $startval . '</textarea>' . $errspan;
								break;

							case 'select':
								echo '<select name="wpzlbcm_field_' . $id . ( $options_multiple ? '[]' : '' ) . '" class="wpzlbcm-field wpzlbcm-field-' . $id . ' wpzlbcm-field-type-select"' . ( $options_multiple ? ' multiple' : '' ) . '>';
								if ( !empty( $options_names ) ) {
									foreach ( $options_names as $optnum => $option_name ) {
										echo '<option value="' . $optnum . '"' . ( in_array( $optnum, $options_selected ) ? ' selected' : '' ) . ( in_array( $optnum, $options_required ) ? ' data-required="yes"' : '' ) . '>' . apply_filters( 'the_title', trim( $option_name ) ) . '</option>';
									}
								} else {
									echo '<option value="-1" selected>' . __( 'No options&hellip;', 'zoom-builder' ) . '</option>';
								}
								echo '</select>' . $errspan;
								break;

							case 'checkbox':
							case 'radio':
								foreach ( $options_names as $optnum => $option_name ) {
									echo '<label><input type="' . $type . '" name="wpzlbcm_field_' . $id . ( $type == 'checkbox' ? '[]' : '' ) . '" class="wpzlbcm-field wpzlbcm-field-' . $id . ' wpzlbcm-field-type-' . $type . '" value="' . $optnum . '"' . ( in_array( $optnum, $options_required ) ? ' data-required="yes"' : '' ) . ( in_array( $optnum, $options_selected ) ? ' checked' : '' ) . ' />' . $errspan . ' ' . apply_filters( 'the_title', trim( $option_name ) ) . '</label>';
								}
								break;

							default:
								echo '<input type="' . $type . '" name="wpzlbcm_field_' . $id . '" class="wpzlbcm-field wpzlbcm-field-' . $id . ' wpzlbcm-field-type-' . $type . '" value="' . $startval . '"' . $placeholder . $minlen . $maxlen . $required . ' />' . $errspan;
								break;

						}

						if ( $type != 'checkbox' && $type != 'radio' ) echo '</label>';
						?>
					</p><?php

				}
				?>

				<input type="submit" value="<?php echo $submit_label; ?>" class="wpzlbcm-submitbtn" />
			</fieldset>

			<div class="wpzlbcm-field-error"></div>
		</form><?php

		/* After widget (defined by themes). */
		echo $after_widget;

	}

	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = sanitize_text_field( trim( $new_instance['title'] ) );
		$instance['to'] = preg_replace( '/[^a-z0-9!#$%&\'*+\-\/=?\^_`{\|}~@\.\[\] ]/i', '', trim( $new_instance['to'] ) );
		$instance['submit_label'] = sanitize_text_field( trim( $new_instance['submit_label'] ) );
		$instance['akismet'] = isset( $new_instance['akismet'] ) && $new_instance['akismet'] === '1';

		$fields = isset( $new_instance['fields'] ) && is_array( $new_instance['fields'] ) && !empty( $new_instance['fields'] ) ? $new_instance['fields'] : array();
		foreach ( $fields as &$field ) {
			$field['name'] = isset( $field['name'] ) && !empty( $field['name'] ) ? sanitize_text_field( trim( $field['name'] ) ) : '';
			$field['type'] = isset( $field['type'] ) && in_array( $field['type'], array( 'email', 'checkbox', 'radio', 'textarea', 'select' ) ) ? $field['type'] : 'text';
			$field['required'] = isset( $field['required'] ) && $field['required'] === '1';

			$options_names = isset( $field['options']['names'] ) && is_array( $field['options']['names'] ) && !empty( $field['options']['names'] ) ? $field['options']['names'] : array();
			foreach ( $options_names as &$name ) { $name = sanitize_text_field( trim( $name ) ); }
			$options_selected = isset( $field['options']['selected'] ) && is_array( $field['options']['selected'] ) && !empty( $field['options']['selected'] ) ? $field['options']['selected'] : array();
			if ( !empty( $options_selected ) ) $options_selected = array_map( 'absint', $options_selected );
			$options_required = isset( $field['options']['required'] ) && is_array( $field['options']['required'] ) && !empty( $field['options']['required'] ) ? $field['options']['required'] : array();
			if ( !empty( $options_required ) ) $options_required = array_map( 'absint', $options_required );
			$field['options'] = array( 'names' => $options_names, 'selected' => $options_selected, 'required' => $options_required );

			$field['multiple'] = isset( $field['multiple'] ) && $field['multiple'] === '1';
			$field['startval'] = isset( $field['startval'] ) && !empty( $field['startval'] ) ? sanitize_text_field( trim( $field['startval'] ) ) : '';
			$field['showlabel'] = isset( $field['showlabel'] ) && $field['showlabel'] === '1';
			$field['placeholder'] = isset( $field['placeholder'] ) && !empty( $field['placeholder'] ) ? sanitize_text_field( trim( $field['placeholder'] ) ) : '';
			$field['minlen'] = isset( $field['minlen'] ) && absint( $field['minlen'] ) >= 0 ? absint( $field['minlen'] ) : 0;
			$field['maxlen'] = isset( $field['maxlen'] ) && absint( $field['maxlen'] ) > 0 ? absint( $field['maxlen'] ) : '';
			$field['classes'] = isset( $field['classes'] ) && !empty( $field['classes'] ) ? preg_replace( '/[^_a-z0-9\- ]/i', '', trim( $field['classes'] ) ) : '';
		}
		$instance['fields'] = $fields;

		return $instance;

	}

	public function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title' => __( 'Contact Form', 'zoom-builder' ),
			'to' => ( '' != ( $admin_email = trim( get_bloginfo( 'admin_email' ) ) ) && is_email( $admin_email ) ? $admin_email : '' ),
			'submit_label' => __( 'Send', 'zoom-builder' ),
			'akismet' => true,
			'fields' => array(
				array( 'name' => __( 'Name', 'zoom-builder' ), 'required' => true ),
				array( 'name' => __( 'Email', 'zoom-builder' ), 'type' => 'email', 'required' => true ),
				array( 'name' => __( 'Subject', 'zoom-builder' ) ),
				array( 'name' => __( 'Message', 'zoom-builder' ), 'type' => 'textarea', 'required' => true )
			)
		);
		$instance = wp_parse_args( (array)$instance, $defaults );

		?><fieldset>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'zoom-builder' ); ?></label><br />
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" type="text" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'to' ); ?>"><?php _e( 'Send Form To:', 'zoom-builder' ); ?></label><br />
				<textarea id="<?php echo $this->get_field_id( 'to' ); ?>" name="<?php echo $this->get_field_name( 'to' ); ?>" rows="2" placeholder="email@domain.com" class="widefat"><?php echo esc_textarea( $instance['to'] ); ?></textarea><br />
				<span class="howto"><?php _e( 'Separate multiple recipients with newlines', 'zoom-builder' ); ?></span>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'submit_label' ); ?>"><?php _e( 'Submit Button Label:', 'zoom-builder' ); ?></label><br />
				<input id="<?php echo $this->get_field_id( 'submit_label' ); ?>" name="<?php echo $this->get_field_name( 'submit_label' ); ?>" value="<?php echo esc_attr( $instance['submit_label'] ); ?>" type="text" class="widefat" />
			</p>

			<p>
				<label><input id="<?php echo $this->get_field_id( 'akismet' ); ?>" name="<?php echo $this->get_field_name( 'akismet' ); ?>" value="1" type="checkbox" <?php checked( $instance['akismet'] ); ?> /><?php _e( 'Use Akismet for Spam Prevention', 'zoom-builder' ); ?></label><br />
				<span class="howto"><?php printf( __( 'You must have the <a href="%s" title="Install Akismet Plugin" class="thickbox">Akismet plugin</a> installed and activated', 'zoom-builder' ), admin_url( 'plugin-install.php?tab=plugin-information&plugin=akismet&TB_iframe=true&width=600&height=620' ) ); ?></span>
			</p>
		</fieldset>

		<label class="fields-label"><?php _e( 'Fields:', 'zoom-builder' ); ?></label>
		<ul class="fields">
			<?php
			$fields = !empty( $instance['fields'] ) && is_array( $instance['fields'] ) ? $instance['fields'] : array( array( 'name' => '' ) );

			$num = 1;
			foreach ( $fields as $field ) {

				if ( empty( $field ) || !is_array( $field ) || !isset( $field['name'] ) ) continue;

				$name = trim( $field['name'] );
				$type = isset( $field['type'] ) && in_array( $field['type'], array( 'email', 'checkbox', 'radio', 'textarea', 'select' ) ) ? $field['type'] : 'text';
				$required = isset( $field['required'] ) && $field['required'] === true;
				$options = isset( $field['options'] ) && is_array( $field['options'] ) && !empty( $field['options'] ) ? $field['options'] : array( 'names' => array( '' ) );
				$options_names = isset( $options['names'] ) && is_array( $options['names'] ) && !empty( $options['names'] ) ? $options['names'] : array( '' );
				$options_selected = isset( $options['selected'] ) && is_array( $options['selected'] ) && !empty( $options['selected'] ) ? $options['selected'] : array();
				$options_required = isset( $options['required'] ) && is_array( $options['required'] ) && !empty( $options['required'] ) ? $options['required'] : array();
				$options_multiple = isset( $field['multiple'] ) && $field['multiple'] === true;
				$startval = isset( $field['startval'] ) && !empty( $field['startval'] ) ? trim( $field['startval'] ) : '';
				$showlabel = ( isset( $field['showlabel'] ) && $field['showlabel'] === true ) || !isset( $field['showlabel'] );
				$placeholder = isset( $field['placeholder'] ) && !empty( $field['placeholder'] ) ? trim( $field['placeholder'] ) : '';
				$minlen = isset( $field['minlen'] ) && intval( $field['minlen'] ) >= 0 ? intval( $field['minlen'] ) : 0;
				$maxlen = isset( $field['maxlen'] ) && intval( $field['maxlen'] ) > 0 ? intval( $field['maxlen'] ) : '';
				$classes = isset( $field['classes'] ) && !empty( $field['classes'] ) ? trim( $field['classes'] ) : '';

				if ( $type == 'radio' || ( $type == 'select' && !$options_multiple ) ) {
					$options_selected = array_slice( $options_selected, 0, 1 );
					$options_required = array_slice( $options_required, 0, 1 );
				}

				?><li class="field type-<?php echo $type; ?>">
					<fieldset>
						<div class="field-controls">
							<span class="field-move field-move-up fa fa-caret-up" title="<?php _e( 'Move This Field Up One', 'zoom-builder' ); ?>"></span>
							<span class="field-move field-move-down fa fa-caret-down" title="<?php _e( 'Move This Field Down One', 'zoom-builder' ); ?>"></span>
							<span class="field-delete fa fa-times" title="<?php _e( 'Remove This Field', 'zoom-builder' ); ?>"></span>
						</div>

						<p class="field-name">
							<label for="<?php echo $this->get_field_id( "field-$num-name" ); ?>"><?php _e( 'Name:', 'zoom-builder' ); ?></label>
							<small class="form-input-tip"><?php _e( 'The name of the field', 'zoom-builder' ); ?></small><br />
							<input id="<?php echo $this->get_field_id( "field-$num-name" ); ?>" name="<?php echo $this->get_field_name( "fields][$num][name" ); ?>" value="<?php echo esc_attr( $name ); ?>" type="text" class="widefat" />
						</p>

						<p class="field-type">
							<label for="<?php echo $this->get_field_id( "field-$num-type" ); ?>"><?php _e( 'Type:', 'zoom-builder' ); ?></label>
							<small class="form-input-tip"><?php _e( 'The type of field', 'zoom-builder' ); ?></small><br />
							<select id="<?php echo $this->get_field_id( "field-$num-type" ); ?>" name="<?php echo $this->get_field_name( "fields][$num][type" ); ?>" class="widefat">
								<option value="text" <?php selected( $type, 'text' ); ?>><?php _e( 'Text', 'zoom-builder' ); ?></option>
								<option value="email" <?php selected( $type, 'email' ); ?>><?php _e( 'Email', 'zoom-builder' ); ?></option>
								<option value="checkbox" <?php selected( $type, 'checkbox' ); ?>><?php _e( 'Checkbox', 'zoom-builder' ); ?></option>
								<option value="radio" <?php selected( $type, 'radio' ); ?>><?php _e( 'Radio', 'zoom-builder' ); ?></option>
								<option value="textarea" <?php selected( $type, 'textarea' ); ?>><?php _e( 'Textarea', 'zoom-builder' ); ?></option>
								<option value="select" <?php selected( $type, 'select' ); ?>><?php _e( 'Select', 'zoom-builder' ); ?></option>
							</select>
						</p>

						<div class="field-options">
							<p><label><?php _e( 'Options:', 'zoom-builder' ); ?></label> <small class="form-input-tip"><?php _e( 'The available options', 'zoom-builder' ); ?></small></p>

							<table>
								<thead>
									<tr>
										<th class="option-name"><?php _e( 'Name', 'zoom-builder' ); ?></th>
										<th class="option-selected"><abbr title="<?php _e( 'Selected', 'zoom-builder' ); ?>"><?php _e( 'Sel.', 'zoom-builder' ); ?></abbr></th>
										<th class="option-required"><abbr title="<?php _e( 'Required', 'zoom-builder' ); ?>"><?php _e( 'Req.', 'zoom-builder' ); ?></abbr></th>
										<th class="option-delete"><abbr title="<?php _e( 'Delete', 'zoom-builder' ); ?>"><?php _e( 'Del.', 'zoom-builder' ); ?></abbr></th>
									</tr>
								</thead>

								<tbody>
									<?php
									foreach ( $options_names as $optnum => $option_name ) {

										$option_name = trim( $option_name );
										$option_selected = in_array( $optnum, $options_selected );
										$option_required = in_array( $optnum, $options_required );

										?><tr>
											<td class="option-name"><input type="text" name="<?php echo $this->get_field_name( "fields][$num][options][names][$optnum" ); ?>" value="<?php echo esc_attr( $option_name ); ?>" class="widefat" /></td>
											<td class="option-selected"><input type="checkbox" name="<?php echo $this->get_field_name( "fields][$num][options][selected][" ); ?>" value="<?php echo $optnum; ?>" <?php checked( $option_selected ); ?> /></td>
											<td class="option-required"><input type="checkbox" name="<?php echo $this->get_field_name( "fields][$num][options][required][" ); ?>" value="<?php echo $optnum; ?>" <?php checked( $option_required ); ?> /></td>
											<td class="option-delete"><i class="fa fa-times"></i></td>
										</tr><?php

									}
									?>

									<tr class="option-add">
										<td colspan="4"><i class="fa fa-plus"></i> <?php _e( 'Add New Option', 'zoom-builder' ); ?></td>
									</tr>
								</tbody>
							</table>
						</div>

						<p class="field-multiple">
							<label><input id="<?php echo $this->get_field_id( "field-$num-multiple" ); ?>" name="<?php echo $this->get_field_name( "fields][$num][multiple" ); ?>" value="1" type="checkbox" <?php checked( $options_multiple ); ?> /> <?php _e( 'Multiple', 'zoom-builder' ); ?></label>
							<small class="form-input-tip"><?php _e( 'Are multiple selections allowed?', 'zoom-builder' ); ?></small>
						</p>

						<p class="field-start-value">
							<label for="<?php echo $this->get_field_id( "field-$num-startval" ); ?>"><?php _e( 'Starting Value:', 'zoom-builder' ); ?></label>
							<small class="form-input-tip"><?php _e( 'The initial value <em>(if any)</em> of the field', 'zoom-builder' ); ?></small><br />
							<input id="<?php echo $this->get_field_id( "field-$num-startval" ); ?>" name="<?php echo $this->get_field_name( "fields][$num][startval" ); ?>" value="<?php echo esc_attr( $startval ); ?>" type="text" class="widefat" />
						</p>

						<p class="field-required">
							<label><input id="<?php echo $this->get_field_id( "field-$num-required" ); ?>" name="<?php echo $this->get_field_name( "fields][$num][required" ); ?>" value="1" type="checkbox" <?php checked( $required ); ?> /> <?php _e( 'Required', 'zoom-builder' ); ?></label>
							<small class="form-input-tip"><?php _e( 'Is this a required field?', 'zoom-builder' ); ?></small>
						</p>

						<div class="field-advanced">
							<span class="field-advanced-label"><?php _e( 'Advanced', 'zoom-builder' ); ?></span>

							<div class="field-advanced-inner">
								<p class="field-show-label">
									<label><input id="<?php echo $this->get_field_id( "field-$num-showlabel" ); ?>" name="<?php echo $this->get_field_name( "fields][$num][showlabel" ); ?>" value="1" type="checkbox" <?php checked( $showlabel ); ?> /> <?php _e( 'Show Input Label', 'zoom-builder' ); ?></label>
									<small class="form-input-tip"><?php _e( 'Should the label for this field be displayed?', 'zoom-builder' ); ?></small>
								</p>

								<p class="field-placeholder">
									<label for="<?php echo $this->get_field_id( "field-$num-placeholder" ); ?>"><?php _e( 'Placeholder:', 'zoom-builder' ); ?></label>
									<small class="form-input-tip"><?php _e( 'The placeholder text of the field', 'zoom-builder' ); ?></small><br />
									<input id="<?php echo $this->get_field_id( "field-$num-placeholder" ); ?>" name="<?php echo $this->get_field_name( "fields][$num][placeholder" ); ?>" value="<?php echo esc_attr( $placeholder ); ?>" type="text" class="widefat" />
								</p>

								<p class="field-minmaxlen field-minlen">
									<label for="<?php echo $this->get_field_id( "field-$num-minlen" ); ?>"><?php _e( 'Minimum Length:', 'zoom-builder' ); ?></label>
									<input id="<?php echo $this->get_field_id( "field-$num-minlen" ); ?>" name="<?php echo $this->get_field_name( "fields][$num][minlen" ); ?>" value="<?php echo esc_attr( $minlen ); ?>" type="text" size="5" />
								</p>

								<p class="field-minmaxlen field-maxlen">
									<label for="<?php echo $this->get_field_id( "field-$num-maxlen" ); ?>"><?php _e( 'Maximum Length:', 'zoom-builder' ); ?></label>
									<input id="<?php echo $this->get_field_id( "field-$num-maxlen" ); ?>" name="<?php echo $this->get_field_name( "fields][$num][maxlen" ); ?>" value="<?php echo esc_attr( $maxlen ); ?>" type="text" size="5" />
								</p>

								<div class="clear"></div>

								<p class="field-classes">
									<label for="<?php echo $this->get_field_id( "field-$num-classes" ); ?>"><?php _e( 'CSS Classes:', 'zoom-builder' ); ?></label>
									<small class="form-input-tip"><?php _e( 'Additional custom CSS classes that will be added to this field <em>[Separate multiple classes with spaces]</em>', 'zoom-builder' ); ?></small><br />
									<input id="<?php echo $this->get_field_id( "field-$num-classes" ); ?>" name="<?php echo $this->get_field_name( "fields][$num][classes" ); ?>" value="<?php echo esc_attr( $classes ); ?>" type="text" placeholder="<?php _e( 'e.g. &quot;special-class another&quot;', 'zoom-builder' ); ?>" class="widefat code" />
								</p>
							</div>
						</div>
					</fieldset>

					<div class="new"></div>
					<div class="delete"></div>
				</li><?php

				$num++;

			}
			?>
		</ul>

		<span class="field-add button"><i class="fa fa-plus"></i> <?php _e( 'Add New Field', 'zoom-builder' ); ?></span><?php

	}

}

function wpzlb_contact_module_admin_enqueue_scripts() {

	if ( !ZOOM_Builder_Utils::screen_is_builder() || !current_user_can( 'edit_posts' ) ) return;

	wp_register_style( 'wpzlb-contact-module-admin-style', plugins_url( '/backend.css', __FILE__ ), array(), ZOOM_Builder::$version );
	wp_register_script( 'wpzlb-contact-module-admin-script', plugins_url( '/backend.js', __FILE__ ), array( 'jquery', 'jquery-ui-spinner' ), ZOOM_Builder::$version, true );

	wp_enqueue_style( 'wpzlb-contact-module-admin-style' );
	wp_enqueue_script( 'wpzlb-contact-module-admin-script' );

}

function wpzlb_contact_module_frontend_enqueue_scripts() {

	if ( !is_single() && !is_page() ) return;

	$current_id = get_the_ID();
	$sidebars_widgets = wp_get_sidebars_widgets();

	if ( empty( $sidebars_widgets ) || !isset( $sidebars_widgets["_wpzlb-page-$current_id-widgets"] ) ) return;

	$current_sidebar = (array)$sidebars_widgets["_wpzlb-page-$current_id-widgets"];
	$matches = preg_grep( '/^wpzoom-contact-form-[0-9]+$/i', $current_sidebar );

	if ( empty( $current_sidebar ) || empty( $matches ) ) return;

	wp_register_style( 'wpzlb-contact-module-frontend-style', plugins_url( '/frontend.css', __FILE__ ), array(), ZOOM_Builder::$version );
	wp_register_script( 'wpzlb-contact-module-frontend-script', plugins_url( '/frontend.js', __FILE__ ), array( 'jquery' ), ZOOM_Builder::$version, true );
	wp_enqueue_style( 'wpzlb-contact-module-frontend-style' );
	wp_enqueue_script( 'wpzlb-contact-module-frontend-script' );
	wp_localize_script( 'wpzlb-contact-module-frontend-script', 'wpzlbcmL10n', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'fieldInputRequired' => __( 'This field is required!', 'zoom-builder' ),
		'fieldSelectionRequired' => __( 'This option must be selected!', 'zoom-builder' ),
		'fieldOptionRequired' => __( 'This field requires the &ldquo;%s&rdquo; option to be selected!', 'zoom-builder' ),
		'fieldTooFewChar' => __( 'This field requires at least %d characters!', 'zoom-builder' ),
		'fieldTooManyChar' => __( 'This field can have no more than %d characters!', 'zoom-builder' ),
		'fieldValidEmail' => __( 'A valid email address is required!', 'zoom-builder' ),
		'submitLabelSending' => __( 'Sending&hellip;', 'zoom-builder' ),
		'submitLabelSent' => __( 'Sent!', 'zoom-builder' ),
		'submitLabelFailed' => __( 'Failed!', 'zoom-builder' )
	) );

}

function wpzlb_contact_module_regular_submit() {

	$is_ajax = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest';

	if ( isset( $_POST['wpzlbcm_submit'] ) && !$is_ajax ) {

		global $wpzlbcm_submit_result;

		$wpzlbcm_submit_result[ absint( $_POST['wpzlbcm_widgetnum'] ) ] = wpzlb_contact_module_process_submit() ? 'success' : 'failed';

	}

}

function wpzlb_contact_module_ajax_submit() {

	echo wpzlb_contact_module_process_submit() ? '1' : '0';
	exit;

}

function wpzlb_contact_module_process_submit() {

	if ( !isset( $_POST['wpzlbcm_widgetnum'] ) || absint( $_POST['wpzlbcm_widgetnum'] ) < 1 ) return false;

	$widget_class = new WPZOOM_Widget_Contact_Form;
	$widget_all_settings = $widget_class->get_settings();
	$widget_number = absint( $_POST['wpzlbcm_widgetnum'] );
	$widget_settings = isset( $widget_all_settings[ $widget_number ] ) ? $widget_all_settings[ $widget_number ] : array();
	unset( $widget_class, $widget_all_settings );
	if ( empty( $widget_settings ) ) return false;

	$to = isset( $widget_settings['to'] ) && trim( $widget_settings['to'] ) != '' ? array_filter( preg_split( '/\r\n|\n|\r/', $widget_settings['to'], null, PREG_SPLIT_NO_EMPTY ), 'is_email' ) : array();
	$to = !empty( $to ) ? $to : trim( get_bloginfo( 'admin_email' ) );
	if ( empty( $to ) ) return false;

	$akismet = ( isset( $widget_settings['akismet'] ) && $widget_settings['akismet'] === true ) || !isset( $widget_settings['akismet'] );

	$error = false;

	$fields = $widget_settings['fields'];

	foreach ( $fields as $id => $field ) {

		$type = $field['type'];

		if ( $type == 'checkbox' || $type == 'radio' || $type == 'select' ) {

			if ( $type == 'checkbox' || $type == 'radio' || ( $type == 'select' && isset( $field['multiple'] ) && $field['multiple'] === true ) ) {

				$post_field = isset( $_POST['wpzlbcm_field_' . $id] ) ? (array)$_POST['wpzlbcm_field_' . $id] : array();

				foreach ( $field['options']['required'] as $required ) {
					if ( !in_array( $required, $post_field ) ) $error = true;
				}

			} else {

				$post_field = isset( $_POST['wpzlbcm_field_' . $id] ) ? trim( $_POST['wpzlbcm_field_' . $id] ) : '';
				if ( !empty( $field['options']['required'] ) && $field['options']['required'][0] != $post_field ) $error = true;

			}

		} else {

			$post_field = isset( $_POST['wpzlbcm_field_' . $id] ) ? trim( $_POST['wpzlbcm_field_' . $id] ) : '';
			$minlen = isset( $field['minlen'] ) && absint( $field['minlen'] ) > 0 ? absint( $field['minlen'] ) : 0;
			$maxlen = isset( $field['maxlen'] ) && $field['maxlen'] !== '' && absint( $field['maxlen'] ) > 0 ? absint( $field['maxlen'] ) : false;

			if ( ( $field['required'] === true && empty( $post_field ) ) || ( $minlen > 0 && strlen( $post_field ) < $minlen ) || ( $maxlen !== false && strlen( $post_field ) > $maxlen ) )
				$error = true;

		}

	}

	if ( $error === true ) return false;

	$spam = $akismet ? bm_checkSpam( array( 'comment_type' => 'email_form', 'comment_content' => implode( "\n\n", $fields ) ) ) : false;

	if ( $spam === true ) return false;

	$message = '';
	foreach ( $fields as $field_id => $field ) {

		if ( $field['type'] == 'checkbox' || $field['type'] == 'radio' || $field['type'] == 'select' ) {

			$value = '';
			$selected = isset( $_POST['wpzlbcm_field_' . $field_id] ) ? (array)$_POST['wpzlbcm_field_' . $field_id] : array();

			foreach ( $field['options']['names'] as $optid => $optname ) {
				$value .= sprintf( "%s - %s\n", $optname, ( in_array( $optid, $selected ) ? '[SELECTED]' : '[NOT SELECTED]' ) );
			}

		} else {

			$value = isset( $_POST['wpzlbcm_field_' . $field_id] ) && trim( $_POST['wpzlbcm_field_' . $field_id] ) != '' ? htmlspecialchars( trim( $_POST['wpzlbcm_field_' . $field_id] ) ) : __( '[EMPTY]', 'zoom-builder' );

		}

		$message .= sprintf( "Field Name: \n%s\n\nField Value(s): \n%s\n\n-----\n\n", $field['name'], $value );

	}

	return wp_mail( $to, sprintf( '%s contact form submission', get_bloginfo( 'name' ) ), sprintf( __( "Form Fields:\n-----\n\n%s", 'zoom-builder' ), $message ) );

}

/* Use Akismet (if available) on contact form submissions
 * Props Ben Gillbanks - http://www.binarymoon.co.uk/2010/03/akismet-plugin-theme-stop-spam-dead/
==================================================================================================== */
if ( !function_exists( 'bm_checkSpam' ) ) {
	function bm_checkSpam( $content ) {

		$isSpam = FALSE;
		$content = (array) $content;

		if ( function_exists( 'akismet_init' ) ) {

			$wpcom_api_key = get_option( 'wordpress_api_key' );

			if ( !empty( $wpcom_api_key ) ) {

				global $akismet_api_host, $akismet_api_port;

				$content['user_ip'] = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
				$content['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
				$content['referrer'] = $_SERVER['HTTP_REFERER'];
				$content['blog'] = home_url();

				if ( empty( $content['referrer'] ) ) {
					$content['referrer'] = get_permalink();
				}

				$queryString = '';

				foreach ( $content as $key => $data ) {
					if ( !empty( $data ) ) {
						$queryString .= $key . '=' . urlencode( stripslashes( $data ) ) . '&';
					}
				}

				$response = akismet_http_post( $queryString, $akismet_api_host, '/1.1/comment-check', $akismet_api_port );

				if ( $response[1] == 'true' ) {
					update_option( 'akismet_spam_count', get_option('akismet_spam_count') + 1 );
					$isSpam = TRUE;
				}

			}

		}

		return $isSpam;

	}
}

add_action( 'widgets_init', create_function( '', 'register_widget("WPZOOM_Widget_Contact_Form");' ) );
add_action( 'admin_enqueue_scripts', 'wpzlb_contact_module_admin_enqueue_scripts' );
add_action( 'wp_enqueue_scripts', 'wpzlb_contact_module_frontend_enqueue_scripts' );
add_action( 'wp_ajax_wpzlbcm', 'wpzlb_contact_module_ajax_submit' );
add_action( 'wp_ajax_nopriv_wpzlbcm', 'wpzlb_contact_module_ajax_submit' );
add_action( 'init', 'wpzlb_contact_module_regular_submit' );