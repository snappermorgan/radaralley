<?php defined('ABSPATH') or die; # Don't ever load this file directly

/**
 * ZOOM_Builder_Main Class
 *
 * The main admin-side builder interface and associated functions.
 *
 * @package ZOOM_Builder
 * @subpackage Main
 */

class ZOOM_Builder_Main {

	/**
	 * Hook some stuff up to WordPress
	 */
	public static function init() {

		if ( !is_admin() || !current_user_can( 'edit_posts' ) ) return;

		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );

		if ( current_user_can( 'edit_theme_options' ) ) {

			add_action( 'wp_ajax_wpzlb-widgets-order', array( __CLASS__, 'ajax_widgets_order' ) );
			add_action( 'wp_ajax_wpzlb-update-saved-layouts', array( __CLASS__, 'ajax_update_saved_layouts' ) );
			add_action( 'wp_ajax_wpzlb-get-saved-layout-controls', array( __CLASS__, 'ajax_get_saved_layout_controls' ) );
			add_action( 'wp_ajax_wpzlb-load-layout-widgets', array( __CLASS__, 'ajax_load_layout_widgets' ) );

		}

		global $pagenow, $typenow;

		ZOOM_Builder_Utils::fix_typenow(); # Sometimes the global $typenow variable isn't set early enough for our needs, so we try to set it ourselves

		if ( ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) ) {

			add_action( 'pre_post_update', array( __CLASS__, 'pre_post_update' ), 10, 2 );

			if ( in_array( $typenow, ZOOM_Builder::$post_types ) ) {

				add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts_builder' ) );
				add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );

			}

		}

	}

	/**
	 * Hook some stuff up to WordPress on admin init
	 */
	public static function admin_init() {

		add_action( 'admin_action_wpzlb-export-layout', array( __CLASS__, 'download_export_layout' ) );

	}

	/**
	 * Enqueue all the scripts/styles needed for the builder interface
	 */
	public static function admin_enqueue_scripts_builder( $hook ) {

		wp_deregister_style( 'font-awesome' );
		wp_register_style( 'font-awesome', ZOOM_Builder::$assets_url . '/css/font-awesome.css', array(), ZOOM_Builder::$version );
		wp_register_style( 'wpzlb-style-builder', ZOOM_Builder::$assets_url . '/css/admin-builder.css', array( 'font-awesome' ), ZOOM_Builder::$version );
		wp_register_script( 'jquery-cookie', ZOOM_Builder::$assets_url . '/js/jquery.cookie.js', array( 'jquery' ), ZOOM_Builder::$version, true );
		wp_register_script( 'jquery-mousewheel', ZOOM_Builder::$assets_url . '/js/jquery.mousewheel.js', array( 'jquery' ), ZOOM_Builder::$version, true );
		wp_register_script( 'colresizable', ZOOM_Builder::$assets_url . '/js/colresizable.js', array( 'jquery' ), ZOOM_Builder::$version, true );
		wp_register_script( 'tipsy', ZOOM_Builder::$assets_url . '/js/tipsy.js', array( 'jquery' ), ZOOM_Builder::$version, true );
		wp_register_script( 'wpzlb-script-builder', ZOOM_Builder::$assets_url . '/js/builder.js', array( 'jquery', 'jquery-ui-position', 'jquery-ui-spinner', 'jquery-ui-dialog', 'jquery-ui-autocomplete', 'wp-color-picker', 'jquery-cookie', 'jquery-mousewheel', 'colresizable', 'tipsy' ), ZOOM_Builder::$version, true );

		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'font-awesome' );
		wp_enqueue_style( 'wpzlb-style-builder' );
		wp_enqueue_script( 'jquery-ui-position' );
		wp_enqueue_script( 'jquery-ui-spinner' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'admin-widgets' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-cookie' );
		wp_enqueue_script( 'jquery-mousewheel' );
		wp_enqueue_script( 'colresizable' );
		wp_enqueue_script( 'tipsy' );
		wp_enqueue_script( 'wpzlb-script-builder' );
		wp_enqueue_media();

		wp_localize_script( 'wpzlb-script-builder', 'wpzlbL10n', array(

			'adminPostEditUrl' => admin_url( '/post.php?post=' . get_the_ID() . '&action=edit' ),

			'maxColumns' => ZOOM_Builder::$max_columns,

			'tooltipColHighlightWidgetAdded' => __( 'Widget Added to Selected Column!', 'zoom-builder' ),

			'inputPlaceholderGroupName' => __( 'Group Name&hellip;', 'zoom-builder' ),

			'tooltipGroupName' => __( 'Group Name', 'zoom-builder' ),
			'tooltipGroupRearrange' => __( 'Click &amp; Drag to Rearrange This Group', 'zoom-builder' ),
			'tooltipGroupChangeSettings' => __( 'Change This Group&rsquo;s Settings', 'zoom-builder' ),
			'tooltipGroupRemove' => __( 'Remove This Group', 'zoom-builder' ),

			'tooltipRowRearrange' => __( 'Click &amp; Drag to Rearrange This Row', 'zoom-builder' ),
			'tooltipRowType' => __( 'Amount of Columns', 'zoom-builder' ),
			'tooltipRowRemove' => __( 'Remove This Row', 'zoom-builder' ),
			'tooltipDividerRemove' => __( 'Remove This Divider', 'zoom-builder' ),

			'inputLabelFontColor' => __( 'Font Color:', 'zoom-builder' ),
			'inputLabelBackground' => __( 'Background', 'zoom-builder' ),
			'inputLabelBackgroundColor' => __( 'Color:', 'zoom-builder' ),
			'inputLabelBackgroundImage' => __( 'Image:', 'zoom-builder' ),
			'inputLabelBackgroundImageButtonSelect' => __( 'Select Image', 'zoom-builder' ),
			'inputLabelBackgroundImageButtonClear' => __( 'Clear', 'zoom-builder' ),
			'inputLabelBackgroundPosition' => __( 'Position:', 'zoom-builder' ),
			'inputLabelBackgroundPositionLeft' => __( 'Left', 'zoom-builder' ),
			'inputLabelBackgroundPositionCenter' => __( 'Center', 'zoom-builder' ),
			'inputLabelBackgroundPositionRight' => __( 'Right', 'zoom-builder' ),
			'inputLabelBackgroundRepeat' => __( 'Repeat:', 'zoom-builder' ),
			'inputLabelBackgroundRepeatNo' => __( 'No Repeat', 'zoom-builder' ),
			'inputLabelBackgroundRepeatTile' => __( 'Tile', 'zoom-builder' ),
			'inputLabelBackgroundRepeatTileHorizontally' => __( 'Tile Horizontally', 'zoom-builder' ),
			'inputLabelBackgroundRepeatTileVertically' => __( 'Tile Vertically', 'zoom-builder' ),
			'inputLabelBackgroundAttachment' => __( 'Attachment:', 'zoom-builder' ),
			'inputLabelBackgroundAttachmentScroll' => __( 'Scroll', 'zoom-builder' ),
			'inputLabelBackgroundAttachmentFixed' => __( 'Fixed', 'zoom-builder' ),
			'inputLabelPadding' => __( 'Padding:', 'zoom-builder' ),
			'inputLabelBorder' => __( 'Border', 'zoom-builder' ),
			'inputLabelBorderTop' => __( 'Top:', 'zoom-builder' ),
			'inputLabelBorderLeft' => __( 'Left:', 'zoom-builder' ),
			'inputLabelBorderRight' => __( 'Right:', 'zoom-builder' ),
			'inputLabelBorderBottom' => __( 'Bottom:', 'zoom-builder' ),
			'inputLabelBorderColor' => __( 'Color', 'zoom-builder' ),
			'inputLabelBorderWidth' => __( 'Width', 'zoom-builder' ),
			'inputLabelBorderStyle' => __( 'Style', 'zoom-builder' ),
			'inputLabelBorderStyleNone' => __( 'None', 'zoom-builder' ),
			'inputLabelBorderStyleSolid' => __( 'Solid', 'zoom-builder' ),
			'inputLabelBorderStyleDotted' => __( 'Dotted', 'zoom-builder' ),
			'inputLabelBorderStyleDashed' => __( 'Dashed', 'zoom-builder' ),
			'inputLabelBorderStyleDouble' => __( 'Double', 'zoom-builder' ),
			'inputLabelBorderStyleGroove' => __( 'Groove', 'zoom-builder' ),
			'inputLabelBorderStyleRidge' => __( 'Ridge', 'zoom-builder' ),
			'inputLabelBorderStyleInset' => __( 'Inset', 'zoom-builder' ),
			'inputLabelBorderStyleOutset' => __( 'Outset', 'zoom-builder' ),
			'inputLabelBorderRadius' => __( 'Radius', 'zoom-builder' ),
			'inputLabelMargin' => __( 'Margin:', 'zoom-builder' ),

			'rowDividerLabel' => __( 'Divider', 'zoom-builder' ),

			'tooltipGroupSettingsLockInputs' => __( 'Lock all sides to the same value', 'zoom-builder' ),
			'tooltipGroupSettingsPositionTop' => __( 'Top', 'zoom-builder' ),
			'tooltipGroupSettingsPositionLeft' => __( 'Left', 'zoom-builder' ),
			'tooltipGroupSettingsPositionRight' => __( 'Right', 'zoom-builder' ),
			'tooltipGroupSettingsPositionBottom' => __( 'Bottom', 'zoom-builder' ),
			'tooltipGroupSettingsPositionTopLeft' => __( 'Top Left', 'zoom-builder' ),
			'tooltipGroupSettingsPositionTopRight' => __( 'Top Right', 'zoom-builder' ),
			'tooltipGroupSettingsPositionBottomLeft' => __( 'Bottom Left', 'zoom-builder' ),
			'tooltipGroupSettingsPositionBottomRight' => __( 'Bottom Right', 'zoom-builder' ),
			'tooltipGroupSettingsReset' => __( 'Reset all group settings to default', 'zoom-builder' ),
			'tooltipGroupSettingsDidReset' => __( 'Group Settings Reset!', 'zoom-builder' ),
			'tooltipGroupSettingsClose' => __( 'Close', 'zoom-builder' ),

			'backgroundImageModalTitle' => __( 'Select a Background Image', 'zoom-builder' ),
			'backgroundImageModalButtonLabel' => __( 'Use This Image', 'zoom-builder' ),

			'confirmGroupSettingsReset' => __( 'Are you sure you want to reset this group&rsquo;s settings? All settings for this group will be reset to their default values if you proceed.', 'zoom-builder' ),
			'confirmGroupRemove' => __( 'Are you sure you want to remove this group? All widgets and their settings in this group will be removed if you proceed.', 'zoom-builder' ),
			'confirmRowRemove' => __( 'Are you sure you want to remove this row? All widgets and their settings in this row will be removed if you proceed.', 'zoom-builder' ),
			'confirmColumnRemove' => __( 'Are you sure you want to remove these columns? All widgets and their settings within these columns will be removed if you proceed.', 'zoom-builder' ),

			'tooltipSavedLayoutsLoad' => __( 'Load Saved Layout', 'zoom-builder' ),
			'tooltipSavedLayoutsLoadSuccess' => __( 'Layout Loaded Successfully!', 'zoom-builder' ),
			'tooltipSavedLayoutsLoadFailed' => __( 'Loading of the Selected Layout Failed!', 'zoom-builder' ),
			'savedLayoutsLoadConfirm' => __( "This will overwrite the current layout with the selected saved layout. This action cannot be undone!\n\nAre you sure you want to continue?", 'zoom-builder' ),
			'savedLayoutsSaveDialogTitle' => __( 'Save Current Layout', 'zoom-builder' ),
			'savedLayoutsSaveDialogButtonSave' => __( 'Save', 'zoom-builder' ),
			'savedLayoutsSaveDialogButtonCancel' => __( 'Cancel', 'zoom-builder' ),
			'savedLayoutsSaveDialogNameEmptyAlert' => __( 'A name is required to save this layout!', 'zoom-builder' ),
			'savedLayoutsSaveDialogOverwriteConfirm' => __( "A saved layout with that name already exists!\n\nClick &ldquo;OK&rdquo; to overwrite the existing layout, or &ldquo;Cancel&rdquo; to go back and choose another name.", 'zoom-builder' ),
			'savedLayoutsSaveDialogSuccess' => __( 'Layout saved successfully!', 'zoom-builder' ),
			'savedLayoutsSaveDialogFailed' => __( 'Layout save failed!', 'zoom-builder' ),
			'savedLayoutsSaveDialogLayouts' => ZOOM_Builder_Utils::get_saved_layouts_names(),

			'buttonLabelAddRow' => __( 'Add New Row', 'zoom-builder' ),
			'buttonLabelAddDivider' => __( 'Add New Divider', 'zoom-builder' ),
			'buttonLabelCloneWidget' => __( 'Clone', 'zoom-builder' )

		) );

	}

	/**
	 * Called when a post is saved in order to save custom meta fields
	 */
	public static function pre_post_update( $post_id, $data ) {

		if ( !isset( $_POST['wpzlb_theme_locations'] ) || !is_array( $_POST['wpzlb_theme_locations'] ) || !in_array( $data['post_type'], ZOOM_Builder::$post_types ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) )
			return $post_id;

		$locations = array();
		foreach ( $_POST['wpzlb_theme_locations'] as $location_id => $location_group ) {
			if ( '' != ( $lid = sanitize_title( trim( $location_id ) ) ) && '' != ( $lgr = sanitize_title( trim( $location_group ) ) ) ) {
				$locations[$lid] = $lgr;
			}
		}

		update_post_meta( $post_id, '_wpzlb_theme_locations', $locations );

	}

	/**
	 * Receives AJAX requests from the layout editor to save the widgets and the associated layout data for a given page
	 */
	public static function ajax_widgets_order() {

		check_ajax_referer( 'save-sidebar-widgets', 'savewidgets' );

		if ( !current_user_can( 'edit_theme_options' ) )
			wp_die( -1 );

		unset( $_POST['savewidgets'], $_POST['action'] );

		if ( isset( $_POST['sidebar'] ) && !empty( $_POST['sidebar'] ) && isset( $_POST['widgets'] ) && isset( $_POST['layout'] ) ) {

			$sidebar = trim( $_POST['sidebar'] );
			$postid = (int)preg_replace( '/^_wpzlb-page-([0-9]+)-widgets$/i', '$1', $sidebar );
			if ( $postid < 1 ) wp_die( -1 );
			$widgets = trim( $_POST['widgets'] ) != '' ? explode( ',', trim( $_POST['widgets'] ) ) : array();
			$layout = null != ( $l = json_decode( stripslashes( trim( $_POST['layout'] ) ), true ) ) && !empty( $l ) ? $l : array();

			$sidebars_widgets = (array)wp_get_sidebars_widgets();
			$sidebars_widgets[ $sidebar ] = array_map( create_function( '$a', 'return substr($a, strpos($a, "_") + 1);' ), $widgets );
			wp_set_sidebars_widgets( $sidebars_widgets );

			foreach ( $layout as $groupi => $group ) {
				foreach ( $group['rows'] as $rowi => $row ) {
					if ( $row['type'] == 'divider' ) continue;
					foreach ( $row['columns'] as $coli => $col ) {
						foreach ( $col['widgets'] as $widgeti => $widget ) {
							$layout[$groupi]['rows'][$rowi]['columns'][$coli]['widgets'][$widgeti] = substr( $widget, strpos( $widget, '_' ) + 1 );
						}
					}
				}
			}

			update_post_meta( $postid, '_wpzoom_layout', ZOOM_Builder_Utils::sanitize_layout_array( $layout ) );

			wp_die( 1 );

		}

		wp_die( -1 );

	}

	/**
	 * Receives AJAX requests from the layout editor to update the saved layout data
	 */
	public static function ajax_update_saved_layouts() {

		check_ajax_referer( 'update-saved-layouts', 'updatelayouts' );

		if ( !current_user_can( 'manage_options' ) )
			wp_die( -1 );

		unset( $_POST['updatelayouts'], $_POST['action'] );

		if ( isset( $_POST['name'] ) && !empty( $_POST['name'] ) && isset( $_POST['layout'] ) && !empty( $_POST['layout'] ) ) {

			$saved_layouts = ZOOM_Builder::$saved_layouts;
			$sent_data_name = stripslashes( trim( (string)$_POST['name'] ) );
			$sent_data_layout = json_decode( stripslashes( trim( (string)$_POST['layout'] ) ), true );

			if ( empty( $sent_data_name ) || is_null( $sent_data_layout ) || !is_array( $sent_data_layout ) ) wp_die( -1 );

			global $wp_registered_widgets;

			$sanitized_layout = ZOOM_Builder_Utils::sanitize_layout_array( $sent_data_layout );

			foreach ( $sanitized_layout as $gid => $group ) {

				foreach ( $group['rows'] as $rid => $row ) {

					if ( $row['type'] == 'divider' ) continue;

					foreach ( $row['columns'] as $cid => $col ) {

						if ( !isset( $col['widgets'] ) ) continue;

						foreach ( $col['widgets'] as $wid => $widget ) {

							$widget_id = substr( $widget, strpos( $widget, '_' ) + 1 );

							if ( isset( $wp_registered_widgets[ $widget_id ] ) ) {

								$instance = $wp_registered_widgets[ $widget_id ];
								$number = $instance['params'][0]['number'];
								$all_settings = $instance['callback'][0]->get_settings();
								$settings = $all_settings[ $number ];

								$sanitized_layout[$gid]['rows'][$rid]['columns'][$cid]['widgets'][$wid] = array( 'id' => $widget_id, 'class' => get_class( $instance['callback'][0] ), 'settings' => $settings );

							}

						}

					}

				}

			}

			$saved_layouts[ sanitize_title( $sent_data_name ) ] = array( 'name' => sanitize_text_field( $sent_data_name ), 'layout' => ZOOM_Builder_Utils::sanitize_layout_array( $sanitized_layout, true ) );

			update_option( 'wpzlb-saved-layouts', $saved_layouts );

			ZOOM_Builder::$saved_layouts = $saved_layouts;

			wp_die( json_encode( ZOOM_Builder_Utils::get_saved_layouts_names() ) );

		}

		wp_die( -1 );

	}

	/**
	 * Outputs the admin-side layout with widgets and controls when requested via AJAX
	 */
	public static function ajax_get_saved_layout_controls() {

		if ( !current_user_can( 'edit_theme_options' ) )
			wp_die( -1 );

		unset( $_POST['action'] );

		if ( isset( $_POST['layoutid'] ) && !empty( $_POST['layoutid'] ) && isset( $_POST['postid'] ) && 0 < ( $id = intval( $_POST['postid'] ) ) ) {

			$layoutid = trim( (string)$_POST['layoutid'] );
			$newids = isset( $_POST['newids'] ) ? json_decode( stripslashes( trim( (string)$_POST['newids'] ) ), true ) : array();

			if ( ( isset( ZOOM_Builder::$saved_layouts[ $layoutid ] ) || isset( ZOOM_Builder::$predefined_layouts[ preg_replace( '/^wpzlb-predefined\./i', '', $layoutid ) ] ) ) && !is_null( $newids ) && is_array( $newids ) ) {

				$layout = ZOOM_Builder_Utils::update_widget_ids( ( isset( ZOOM_Builder::$saved_layouts[ $layoutid ] ) ? ZOOM_Builder::$saved_layouts[ $layoutid ]['layout'] : ZOOM_Builder::$predefined_layouts[ preg_replace( '/^wpzlb-predefined\./i', '', $layoutid ) ]['layout'] ), $newids );

			} else {

				wp_die( -1 );

			}

			require_once( ABSPATH . 'wp-admin/includes/widgets.php' );

			self::list_widget_controls( $id, $layout );

			wp_die();

		}

		wp_die( -1 );

	}

	/**
	 * Receives AJAX requests from the layout editor to load a saved/predefined layout
	 * This function registers new widget instances with settings taken from the layout JSON before another AJAX request is sent to return the newly loaded layout markup
	 */
	public static function ajax_load_layout_widgets() {

		check_ajax_referer( 'load-layout-widgets', 'loadlayoutwidgets' );

		if ( !current_user_can( 'edit_theme_options' ) )
			wp_die( -1 );

		unset( $_POST['loadlayoutwidgets'], $_POST['action'] );

		if ( isset( $_POST['layoutid'] ) && !empty( $_POST['layoutid'] ) && isset( $_POST['postid'] ) && 0 < ( $postid = intval( $_POST['postid'] ) ) ) {

			$layoutid = trim( (string)$_POST['layoutid'] );

			if ( isset( ZOOM_Builder::$saved_layouts[ $layoutid ] ) || isset( ZOOM_Builder::$predefined_layouts[ preg_replace( '/^wpzlb-predefined\./i', '', $layoutid ) ] ) ) {

				global $wp_widget_factory;

				$layout = isset( ZOOM_Builder::$saved_layouts[ $layoutid ] ) ? ZOOM_Builder::$saved_layouts[ $layoutid ]['layout'] : ZOOM_Builder::$predefined_layouts[ preg_replace( '/^wpzlb-predefined\./i', '', $layoutid ) ]['layout'];
				$sidebars_widgets = (array)wp_get_sidebars_widgets();
				$ids = $sidebar_widgets = array();

				foreach ( $layout as $group ) {

					foreach ( $group['rows'] as $row ) {

						if ( $row['type'] == 'divider' ) continue;

						foreach ( $row['columns'] as $column ) {

							if ( !isset( $column['widgets'] ) ) continue;

							foreach ( $column['widgets'] as $widget ) {

								$widget_class = isset( $wp_widget_factory->widgets[ (string)$widget['class'] ] ) ? $wp_widget_factory->widgets[ (string)$widget['class'] ] : false;

								if ( $widget_class === false ) continue;

								$number = $widget_class->number + 1;
								$old_id = (string)$widget['id'];
								$new_id = $widget_class->id_base . '-' . $number;
								$all_settings = $widget_class->get_settings();
								$widget_class->_set( $number );
								$this_settings = $widget_class->update( (array)$widget['settings'], array() );
								$all_settings[ $number ] = $this_settings;

								$widget_class->save_settings( $all_settings );
								$widget_class->updated = true;

								$ids[ $old_id ] = $sidebar_widgets[] = $new_id;

							}

						}

					}

				}

				$sidebars_widgets["_wpzlb-page-$postid-widgets"] = $sidebar_widgets;
				wp_set_sidebars_widgets( $sidebars_widgets );

				if ( !empty( $ids ) )
					wp_send_json( $ids );
				else
					wp_die( 1 );

			}

			wp_die( -1 );

		}

		wp_die( -1 );

	}

	/**
	 * Add needed meta boxes on the page add/edit screen in the admin
	 */
	public static function add_meta_boxes( $post_type ) {

		if ( in_array( $post_type, ZOOM_Builder::$post_types ) ) {

			add_meta_box( 'wpzoom_layout_builder', __( 'ZOOM Builder', 'zoom-builder' ), array( __CLASS__, 'meta_box_layout_builder' ), $post_type, 'advanced', 'default' );

			if ( !empty( ZOOM_Builder::$theme_group_locations ) )
				add_action( 'wpzlb_additional_builder_locations_meta_box', array( __CLASS__, 'meta_box_special_groups' ) );

		}

	}

	/**
	 * Outputs the main layout builder interface
	 */
	public static function meta_box_layout_builder() {

		require_once( ABSPATH . 'wp-admin/includes/widgets.php' );

		$saved_layouts = ZOOM_Builder_Utils::filter_excluded_layouts( ZOOM_Builder::$saved_layouts );
		$predefined_layouts = ZOOM_Builder::$predefined_layouts;

		?><div class="hide-if-js"><p><?php _e( 'JavaScript must be enabled to use this feature.', 'zoom-builder' ); ?></p></div>

		<div id="wpzlb" class="hide-if-no-js">

			<div id="widgets-left">

				<div id="available-widgets" class="widgets-holder-wrap<?php echo isset( $_COOKIE['wpzlb_available_widgets_collapsed'] ) && $_COOKIE['wpzlb_available_widgets_collapsed'] == 'true' ? ' closed' : ''; ?>">

					<div class="sidebar-name">
						<h3><?php _e( 'Available Widgets', 'zoom-builder' ); ?> <span id="removing-widget"><?php _ex( 'Deactivate', 'removing-widget', 'zoom-builder' ); ?> <span></span></span> <span class="sidebar-name-arrow">&nbsp;</span></h3>
					</div>

					<div class="widget-holder">

						<div class="sidebar-description">
							<p class="description"><?php _e( 'Drag widgets from here to a column below to activate them.', 'zoom-builder' ); ?></p>
						</div>

						<div id="widget-list">

							<?php self::list_widgets(); ?>

							<div class="clear"></div>

						</div>

					</div>

				</div>

			</div>

			<div id="widgets-right">

				<div class="loadsave-layout">
					<div id="wpzlb-loadlayout" class="item-edit<?php echo empty( $saved_layouts ) && empty( $predefined_layouts ) ? ' disabled' : ''; ?>" title="<?php _e( 'Load Saved Layout', 'zoom-builder' ); ?>">
						<span><i class="fa fa-folder-open"></i><i class="fa fa-caret-down"></i></span>

						<ul>
							<?php
							if ( !empty( $saved_layouts ) ) {
								foreach ( $saved_layouts as $id => $data ) {
									printf( '<li data-saved-layout-id="%1$s" title="%2$s">%2$s</li>', esc_attr( $id ), sanitize_text_field( trim( $data['name'] ) ) );
								}
							} else {
								echo sprintf( '<li class="none"><em>%s</em></li>', __( 'No saved layouts&hellip;', 'zoom-builder' ) );
							}
							?>

							<li class="wpzlb-loadlayout-predefined">
								<strong><?php _e( 'Predefined Layouts', 'zoom-builder' ); ?></strong>

								<ul>
									<?php
									if ( !empty( $predefined_layouts ) ) {
										foreach ( $predefined_layouts as $predefined_layout ) {
											printf( '<li data-saved-layout-id="%1$s" title="%2$s">%2$s</li>', esc_attr( 'wpzlb-predefined.' . sanitize_title( trim( $predefined_layout['name'] ) ) ), sanitize_text_field( trim( $predefined_layout['name'] ) ) );
										}
									} else {
										echo sprintf( '<li class="none"><em>%s</em></li>', __( 'No predefined layouts&hellip;', 'zoom-builder' ) );
									}
									?>
								</ul>
							</li>
						</ul>

						<?php wp_nonce_field( 'load-layout-widgets', '_wpzlbnonce_load_layout_widgets', false ); ?>
					</div>

					<span id="wpzlb-savelayout" class="item-edit" title="<?php _e( 'Save Current Layout', 'zoom-builder' ); ?>"><i class="fa fa-download"></i></span>
					<div id="wpzlb-savelayout-dialog">
						<?php wp_nonce_field( 'update-saved-layouts', '_wpzlbnonce_saved_layouts', false ); ?>
						<p><label><?php _e( 'Name:', 'zoom-builder' ); ?> <small class="required"><em><?php _e( '* Required', 'zoom-builder' ); ?></em></small> <input type="text" name="wpzlb-savelayout-name" id="wpzlb-savelayout-name" class="widefat" /></label></p>
					</div>
				</div>

				<div class="clear"></div>

				<div class="widgets-holder-wrap">

					<?php self::list_widget_controls(); ?>

				</div>

				<span id="wpzlb-addgroup" class="button"><i class="fa fa-plus"></i> <?php _e( 'Add New Group', 'zoom-builder' ); ?></span>

			</div>

			<div id="wpzlb-widget-picker">
				<div class="wpzlb-widget-picker-outer-wrap">
					<div class="wpzlb-widget-picker-inner-wrap">
						<?php self::list_widgets( true ); ?>
					</div>
				</div>
			</div>

			<?php wp_nonce_field( 'save-sidebar-widgets', '_wpnonce_widgets', false ); ?>

			<div class="clear"></div>

		</div><?php

	}

	/**
	 * Outputs the special layout builder groups picker meta box
	 */
	public static function meta_box_special_groups() {

		$locations = (array)ZOOM_Builder::$theme_group_locations;
		$amount = count( $locations );
		$i = 1;
		$layout = get_post_meta( get_the_ID(), '_wpzoom_layout', true );
		if ( empty( $layout ) || !is_array( $layout ) ) return false;
		$layout = ZOOM_Builder_Utils::sanitize_layout_array( $layout );
		$group_locations = (array)get_post_meta( get_the_ID(), '_wpzlb_theme_locations', true );

		foreach ( $locations as $location_id => $location_name ) {

			$selected = isset( $group_locations[ $location_id ] ) ? $group_locations[ $location_id ] : '-1';

			echo '<label>' . sprintf( __( 'Select a group for <strong>%s</strong>: ', 'zoom-builder' ), $location_name ) . '<br /><select name="wpzlb_theme_locations[' . $location_id . ']" id="wpzlb_theme_locations-' . $location_id . '"><option value="-1"' . ( $selected == '-1' ? ' selected' : '' ) . '>' . __( '&mdash; Select Group &mdash;', 'zoom-builder' ) . '</option>';

			foreach ( $layout as $group ) {

				if ( !isset( $group['groupname'] ) || empty( $group['groupname'] ) ) continue;

				echo '<option value="' . sanitize_title( trim( $group['groupname'] ) ) . '"' . ( $selected == sanitize_title( trim( $group['groupname'] ) ) ? ' selected' : '' ) . '>' . sanitize_text_field( trim( $group['groupname'] ) ) . '</option>';

			}

			echo '</select></label>';

			if ( $i < $amount ) echo '<hr/>';

			$i++;

		}

	}

	/**
	 * Takes a string of HTML of an admin-side widget control, inserts a custom clone widget link, and returns the resulting HTML string
	 */
	public static function insert_widget_clone_link( $widget_html ) {

		return str_ireplace( '<a class="widget-control-close', '<a class="widget-control-clone" href="#clone">' . __( 'Clone', 'zoom-builder' ) . '</a> | <a class="widget-control-close', $widget_html );

	}

	/**
	 * Nearly the same as the core wp_list_widgets() function, with just a few changes
	 */
	public static function list_widgets( $names_only = false ) {

		global $wp_registered_widgets, $sidebars_widgets, $wp_registered_widget_controls;

		$sort = $wp_registered_widgets;
		$wpzoom_widgets = $builder_widgets = array();

		foreach ( $sort as $i => $w ) {

			if ( ZOOM_Builder_Utils::is_wpzoom_widget( $w['id'] ) || ZOOM_Builder_Utils::is_wpzoom_builder_widget( $w['id'] ) ) {

				if ( ZOOM_Builder_Utils::is_wpzoom_widget( $w['id'] ) ) {
					$wpzoom_widgets[$i] = $w;
				} elseif ( ZOOM_Builder_Utils::is_wpzoom_builder_widget( $w['id'] ) ) {
					$w['name'] = preg_replace( '/^WPZOOM: /i', '', $w['name'] );
					$builder_widgets[$i] = $w;
				}

				unset( $sort[$i] );

			}

		}

		usort( $sort, '_sort_name_callback' );
		usort( $wpzoom_widgets, '_sort_name_callback' );
		usort( $builder_widgets, '_sort_name_callback' );

		$sort = array_merge( $builder_widgets, array( '[SEPARATOR]' ), $wpzoom_widgets, $sort );

		unset( $wpzoom_widgets, $builder_widgets );

		$done = array();

		foreach ( $sort as $widget ) {

			if ( $widget == '[SEPARATOR]' ) {
				echo '<div class="sep clear"></div>';
				continue;
			}

			if ( in_array( $widget['callback'], $done, true ) || ZOOM_Builder_Utils::is_excluded( $widget['id'] ) )
				continue;

			$sidebar = is_active_widget( $widget['callback'], $widget['id'], false, false );
			$done[] = $widget['callback'];

			if ( ! isset( $widget['params'][0] ) )
				$widget['params'][0] = array();

			$args = array( 'widget_id' => $widget['id'], 'widget_name' => $widget['name'], '_display' => 'template' );

			if ( isset($wp_registered_widget_controls[$widget['id']]['id_base']) && isset($widget['params'][0]['number']) ) {
				$id_base = $wp_registered_widget_controls[$widget['id']]['id_base'];
				$args['_temp_id'] = "$id_base-__i__";
				$args['_multi_num'] = next_widget_id_number($id_base);
				$args['_add'] = 'multi';
			} else {
				$args['_add'] = 'single';
				if ( $sidebar )
					$args['_hide'] = '1';
			}

			$args = wp_list_widget_controls_dynamic_sidebar( array( 0 => $args, 1 => $widget['params'][0] ) );
			$args[0]['before_widget'] = ZOOM_Builder_Utils::insert_wpzoom_classes( $widget['id'], $args[0]['before_widget'] );

			if ( $names_only === true ) {

				echo $args[0]['before_widget'];

				?><div class="widget-top">
					<div class="widget-title"><h4><?php echo esc_html( strip_tags( $args[0]['widget_name'] ) ); ?></h4></div>
				</div><?php

				echo $args[0]['after_widget'];

			} else {

				call_user_func_array( 'wp_widget_control', $args );

			}

		}

	}

	/**
	 * Displays the widget controls on the admin side formatted in the proper layout (copies a lot of code from the core dynamic_sidebar() function)
	 */
	public static function list_widget_controls( $page_id = null, $alt_layout = null ) {

		global $wp_registered_sidebars, $wp_registered_widgets, $sidebars_widgets;

		add_filter( 'dynamic_sidebar_params', 'wp_list_widget_controls_dynamic_sidebar' );

		$id = is_null( $page_id ) ? get_the_ID() : intval( $page_id );
		if ( $id < 1 ) return false;

		$page = get_post( $id );
		if ( $page === null || !( $page instanceof WP_Post ) || !in_array( $page->post_type, ZOOM_Builder::$post_types ) ) return false;

		$layout = ZOOM_Builder_Utils::sanitize_layout_array( ( is_null( $alt_layout ) ? get_post_meta( $id, '_wpzoom_layout', true ) : $alt_layout ), !is_null( $alt_layout ) );
		$group_count = count( $layout );
		$sidebar_id = "_wpzlb-page-$id-widgets";

		if ( !isset( $sidebars_widgets[ $sidebar_id ] ) ) $sidebars_widgets[ $sidebar_id ] = array();

		$sidebar = isset( $wp_registered_sidebars[$sidebar_id] ) ? $wp_registered_sidebars[$sidebar_id] : array();

		$did_one = false;

		foreach ( $layout as $groupi => $group ) {

			$background_position = isset( $group['settings']['background']['image']['position'] ) && in_array( trim( $group['settings']['background']['image']['position'] ), array( 'left', 'center', 'right' ) ) ? trim( $group['settings']['background']['image']['position'] ) : 'center';
			$background_repeat = isset( $group['settings']['background']['image']['repeat'] ) && in_array( trim( $group['settings']['background']['image']['repeat'] ), array( 'norepeat', 'tile', 'tileh', 'tilev' ) ) ? trim( $group['settings']['background']['image']['repeat'] ) : 'tile';
			$background_attachment = isset( $group['settings']['background']['image']['attachment'] ) && in_array( trim( $group['settings']['background']['image']['attachment'] ), array( 'scroll', 'fixed' ) ) ? trim( $group['settings']['background']['image']['attachment'] ) : 'scroll';
			$padding_top_val = isset( $group['settings']['padding']['top'] ) ? intval( $group['settings']['padding']['top'] ) : 0;
			$padding_left_val = isset( $group['settings']['padding']['left'] ) ? intval( $group['settings']['padding']['left'] ) : 0;
			$padding_right_val = isset( $group['settings']['padding']['right'] ) ? intval( $group['settings']['padding']['right'] ) : 0;
			$padding_bottom_val = isset( $group['settings']['padding']['bottom'] ) ? intval( $group['settings']['padding']['bottom'] ) : 0;
			$margin_top_val = isset( $group['settings']['margin']['top'] ) ? intval( $group['settings']['margin']['top'] ) : 0;
			$margin_left_val = isset( $group['settings']['margin']['left'] ) ? intval( $group['settings']['margin']['left'] ) : 0;
			$margin_right_val = isset( $group['settings']['margin']['right'] ) ? intval( $group['settings']['margin']['right'] ) : 0;
			$margin_bottom_val = isset( $group['settings']['margin']['bottom'] ) ? intval( $group['settings']['margin']['bottom'] ) : 0;
			$borderwidth_top_val = isset( $group['settings']['border']['top']['width'] ) ? intval( $group['settings']['border']['top']['width'] ) : 0;
			$borderwidth_left_val = isset( $group['settings']['border']['left']['width'] ) ? intval( $group['settings']['border']['left']['width'] ) : 0;
			$borderwidth_right_val = isset( $group['settings']['border']['right']['width'] ) ? intval( $group['settings']['border']['right']['width'] ) : 0;
			$borderwidth_bottom_val = isset( $group['settings']['border']['bottom']['width'] ) ? intval( $group['settings']['border']['bottom']['width'] ) : 0;
			$bordercolor_top_val = isset( $group['settings']['border']['top']['color'] ) ? trim( $group['settings']['border']['top']['color'] ) : '';
			$bordercolor_left_val = isset( $group['settings']['border']['left']['color'] ) ? trim( $group['settings']['border']['left']['color'] ) : '';
			$bordercolor_right_val = isset( $group['settings']['border']['right']['color'] ) ? trim( $group['settings']['border']['right']['color'] ) : '';
			$bordercolor_bottom_val = isset( $group['settings']['border']['bottom']['color'] ) ? trim( $group['settings']['border']['bottom']['color'] ) : '';
			$borderstyle_top_val = isset( $group['settings']['border']['top']['style'] ) ? trim( $group['settings']['border']['top']['style'] ) : 'none';
			$borderstyle_left_val = isset( $group['settings']['border']['left']['style'] ) ? trim( $group['settings']['border']['left']['style'] ) : 'none';
			$borderstyle_right_val = isset( $group['settings']['border']['right']['style'] ) ? trim( $group['settings']['border']['right']['style'] ) : 'none';
			$borderstyle_bottom_val = isset( $group['settings']['border']['bottom']['style'] ) ? trim( $group['settings']['border']['bottom']['style'] ) : 'none';
			$borderradius_top_val = isset( $group['settings']['border']['top']['radius'] ) ? intval( $group['settings']['border']['top']['radius'] ) : 0;
			$borderradius_left_val = isset( $group['settings']['border']['left']['radius'] ) ? intval( $group['settings']['border']['left']['radius'] ) : 0;
			$borderradius_right_val = isset( $group['settings']['border']['right']['radius'] ) ? intval( $group['settings']['border']['right']['radius'] ) : 0;
			$borderradius_bottom_val = isset( $group['settings']['border']['bottom']['radius'] ) ? intval( $group['settings']['border']['bottom']['radius'] ) : 0;
			$settings_changed = $background_position != 'center' || $background_repeat != 'tile' || $background_attachment != 'scroll' || $padding_top_val != 0 || $padding_left_val != 0 || $padding_right_val != 0 || $padding_bottom_val != 0 || $margin_top_val != 0 || $margin_left_val != 0 || $margin_right_val != 0 || $margin_bottom_val != 0 || $borderwidth_top_val != 0 || $borderwidth_left_val != 0 || $borderwidth_right_val != 0 || $borderwidth_bottom_val != 0 || $bordercolor_top_val != '' || $bordercolor_left_val != '' || $bordercolor_right_val != '' || $bordercolor_bottom_val != '' || $borderstyle_top_val != 'none' || $borderstyle_left_val != 'none' || $borderstyle_right_val != 'none' || $borderstyle_bottom_val != 'none' || $borderradius_top_val != 0 || $borderradius_left_val != 0 || $borderradius_right_val != 0 || $borderradius_bottom_val != 0;

			echo '<div class="wpzlb-group">
			       <div class="wpzlb-group-controls">
			         <a class="wpzlb-group-controls-move' . ( $group_count < 2 ? ' disabled' : '' ) . '" title="' . __( 'Click &amp; Drag to Rearrange This Group', 'zoom-builder' ) . '"></a>
			         <a class="wpzlb-group-controls-settings' . ( $settings_changed ? ' wpzlb-group-controls-settings-changed' : '' ) . '" title="' . __( 'Change This Group&rsquo;s Settings', 'zoom-builder' ) . '"></a>
							 <a class="wpzlb-group-controls-remove' . ( $group_count < 2 ? ' disabled' : '' ) . '" title="' . __( 'Remove This Group', 'zoom-builder' ) . '"></a>
			         <div class="clear"></div>
			       </div>
			       <div class="wpzlb-group-name"><input type="text" class="wpzlb-group-name-input" size="35" value="' . ( isset( $group['groupname'] ) ? esc_attr( trim( $group['groupname'] ) ) : '' ) . '" placeholder="' . __( 'Group Name&hellip;', 'zoom-builder' ) . '" autocomplete="off" title="' . __( 'Group Name', 'zoom-builder' ) . '" /></div>
			       <div class="clear"></div>
			       <div class="wpzlb-rows">';

			$row_count = count( $group['rows'] );
			foreach ( $group['rows'] as $rowi => $row ) {

				echo '<div class="wpzlb-row wpzlb-row-type-' . $row['type'] . '">
				        <div class="wpzlb-row-controls">
				          <a class="wpzlb-row-controls-move' . ( $group_count < 2 && $row_count < 2 ? ' disabled' : '' ) . '" title="' . __( 'Click &amp; Drag to Rearrange This Row', 'zoom-builder' ) . '"></a>' .
				          ( $row['type'] != 'divider' ? '<span class="wpzlb-row-controls-type" title="' . __( 'Amount of Columns', 'zoom-builder' ) . '"><input type="text" value="' . absint( $row['type'] ) . '" size="1" maxlength="2" autocomplete="off" class="wpzlb-row-controls-type-input code" /></span>' : '' ) .
				          '<a class="wpzlb-row-controls-remove' . ( $row_count < 2 ? ' disabled' : '' ) . '" title="' . ( $row['type'] == 'divider' ? __( 'Remove This Divider', 'zoom-builder' ) : __( 'Remove This Row', 'zoom-builder' ) ) . '"></a>
				          <div class="clear"></div>
				        </div>';

				if ( $row['type'] != 'divider' ) {

					echo '<div class="wpzlb-columns-wrap"><table class="wpzlb-columns"><tbody><tr>';

					for ( $coli = 0; $coli < ZOOM_Builder::$max_columns; $coli++ ) {

						$column = $row['type'] != 'divider' && isset( $row['columns'][$coli] ) ? $row['columns'][$coli] : array( 'widgets' => array() );

						$width = isset( $column['width'] ) && floatval( $column['width'] ) > 0 ? round( floatval( $column['width'] ), 1 ) : 0;

						echo '<td class="wpzlb-column wpzlb-column-' . ( $coli + 1 ) . '"' . ( $width > 0 ? ' style="width:' . $width . '%"' : '' ) . '><div class="wpzlb-column-wrap"><div class="widgets-sortables">';

						foreach ( $column['widgets'] as $widget ) {

							if ( !is_null( $alt_layout ) ) {
								$the_id = is_array( $widget ) && isset( $widget['id'] ) ? $widget['id'] : $widget;
								$widget = strpos( $the_id, '_' ) !== false ? substr( $the_id, strpos( $the_id, '_' ) + 1 ) : $the_id;
							}

							if ( !isset( $wp_registered_widgets[$widget] ) ) continue;

							$params = array_merge(
								array( array_merge( $sidebar, array( 'widget_id' => $widget, 'widget_name' => $wp_registered_widgets[$widget]['name'] ) ) ),
								(array)$wp_registered_widgets[$widget]['params']
							);

							$classname_ = '';
							foreach ( (array)$wp_registered_widgets[$widget]['classname'] as $cn ) {
								if ( is_string($cn) )
									$classname_ .= '_' . $cn;
								elseif ( is_object($cn) )
									$classname_ .= '_' . get_class($cn);
							}
							$classname_ = ltrim( $classname_, '_' );
							$params[0]['before_widget'] = sprintf( $params[0]['before_widget'], $widget, $classname_ );

							$params = apply_filters( 'dynamic_sidebar_params', $params );

							if ( ZOOM_Builder_Utils::is_wpzoom_builder_widget( $params[0]['widget_id'] ) )
								$params[0]['widget_name'] = preg_replace( '/^WPZOOM: /i', '', $params[0]['widget_name'] );

							$params[0]['before_widget'] = ZOOM_Builder_Utils::insert_wpzoom_classes( $widget, $params[0]['before_widget'] );

							$callback = $wp_registered_widgets[$widget]['callback'];

							do_action( 'dynamic_sidebar', $wp_registered_widgets[$widget] );

							if ( is_callable( $callback ) ) {

								ob_start();
								call_user_func_array( $callback, $params );
								$widget_html = ob_get_clean();
								echo self::insert_widget_clone_link( $widget_html );
								$did_one = true;

							}

						}

						echo '</div><div class="wpzlb-column-width"><span><span>' . $width . '%</span></span></div><div class="wpzlb-column-focusable" tabindex="-1"></div></div></td>';

					}

					echo '</tr></tbody></table></div>';

				} else {

					echo '<p class="wpzlb-divider-label"><span>' . __( 'Divider', 'zoom-builder' ) . '</span></p><div class="clear"></div>';

				}

				echo '</div>';

			}

			$padding_linked = ( $padding_top_val == $padding_left_val ) && ( $padding_top_val == $padding_right_val ) && ( $padding_top_val == $padding_bottom_val ) ? ' checked' : '';
			$margin_linked = ( $margin_top_val == $margin_left_val ) && ( $margin_top_val == $margin_right_val ) && ( $margin_top_val == $margin_bottom_val ) ? ' checked' : '';
			$borderwidth_linked = ( $borderwidth_top_val == $borderwidth_left_val ) && ( $borderwidth_top_val == $borderwidth_right_val ) && ( $borderwidth_top_val == $borderwidth_bottom_val ) ? ' checked' : '';
			$bordercolor_linked = ( $bordercolor_top_val == $bordercolor_left_val ) && ( $bordercolor_top_val == $bordercolor_right_val ) && ( $bordercolor_top_val == $bordercolor_bottom_val ) ? ' checked' : '';
			$borderstyle_linked = ( $borderstyle_top_val == $borderstyle_left_val ) && ( $borderstyle_top_val == $borderstyle_right_val ) && ( $borderstyle_top_val == $borderstyle_bottom_val ) ? ' checked' : '';
			$borderradius_linked = ( $borderradius_top_val == $borderradius_left_val ) && ( $borderradius_top_val == $borderradius_right_val ) && ( $borderradius_top_val == $borderradius_bottom_val ) ? ' checked' : '';

			echo '</div>
			  <div class="clear"></div>
			  <span class="wpzlb-group-addrow button"><i class="fa fa-plus"></i> ' . __( 'Add New Row', 'zoom-builder' ) . '</span>
			  <span class="wpzlb-group-adddivider button"><i class="fa fa-sort"></i> ' . __( 'Add New Divider', 'zoom-builder' ) . '</span>
			  <div class="wpzlb-group-settings">
			    <div class="wpzlb-group-settings-inner">
			      <div class="reset" title="' . __( 'Reset all group settings to default', 'zoom-builder' ) . '"><span title="' . __( 'Group Settings Reset!', 'zoom-builder' ) . '"></span></div>
						<div class="close" title="' . __( 'Close', 'zoom-builder' ) . '"></div>
			      <table class="altspacing">
			        <tbody>
			          <tr valign="top">
			            <th scope="row">' . __( 'Font Color:', 'zoom-builder' ) . '</th>
			            <td><input type="text" class="fontcolor code" size="7" value="' . ( isset( $group['settings']['font'] ) ? esc_attr( trim( $group['settings']['font'] ) ) : '' ) . '" autocomplete="off" /></td>
			          </tr>
			        </tbody>
			      </table>
			      <fieldset>
			        <legend>' . __( 'Background', 'zoom-builder' ) . '</legend>
			        <table>
			          <tbody>
			            <tr valign="top">
			              <th scope="row">' . __( 'Color:', 'zoom-builder' ) . '</th>
			              <td><input type="text" class="bgcolor code" size="7" value="' . ( isset( $group['settings']['background']['color'] ) ? esc_attr( trim( $group['settings']['background']['color'] ) ) : '' ) . '" autocomplete="off" /></td>
			            </tr>
			            <tr valign="top">
			              <th scope="row">' . __( 'Image:', 'zoom-builder' ) . '</th>
			              <td><input type="hidden" class="bgimgid" value="' . ( isset( $group['settings']['background']['image']['id'] ) ? esc_attr( trim( $group['settings']['background']['image']['id'] ) ) : '' ) . '" /> <input type="text" class="bgimgurl code" value="' . ( isset( $group['settings']['background']['image']['url'] ) ? esc_attr( trim( $group['settings']['background']['image']['url'] ) ) : '' ) . '" size="22" autocomplete="off" /> <span class="select-image button">' . __( 'Select Image', 'zoom-builder' ) . '</span> <span class="clear-image button">' . __( 'Clear', 'zoom-builder' ) . '</span></td>
			            </tr>
			            <tr valign="top">
			              <th scope="row">' . __( 'Position:', 'zoom-builder' ) . '</th>
			              <td><label><input type="radio" name="bgimgpos' . ( $groupi + 1 ) . '" class="bgimgpos" value="left"' . checked( 'left', $background_position, false ) . ' /> <span class="code">' . __( 'Left', 'zoom-builder' ) . '</span></label> <label><input type="radio" name="bgimgpos' . ( $groupi + 1 ) . '" class="bgimgpos" value="center"' . checked( 'center', $background_position, false ) . ' /> <span class="code">' . __( 'Center', 'zoom-builder' ) . '</span></label> <label><input type="radio" name="bgimgpos' . ( $groupi + 1 ) . '" class="bgimgpos" value="right"' . checked( 'right', $background_position, false ) . ' /> <span class="code">' . __( 'Right', 'zoom-builder' ) . '</span></label></td>
			            </tr>
			            <tr valign="top">
			              <th scope="row">' . __( 'Repeat:', 'zoom-builder' ) . '</th>
			              <td><select class="bgimgrepeat code"><option value="norepeat"' . selected( 'norepeat', $background_repeat, false ) . '>' . __( 'No Repeat', 'zoom-builder' ) . '</option><option value="tile"' . selected( 'tile', $background_repeat, false ) . '>' . __( 'Tile', 'zoom-builder' ) . '</option><option value="tileh"' . selected( 'tileh', $background_repeat, false ) . '>' . __( 'Tile Horizontally', 'zoom-builder' ) . '</option><option value="tilev"' . selected( 'tilev', $background_repeat, false ) . '>' . __( 'Tile Vertically', 'zoom-builder' ) . '</option></select></td>
			            </tr>
			            <tr valign="top">
			              <th scope="row">' . __( 'Attachment:', 'zoom-builder' ) . '</th>
			              <td><label><input type="radio" name="bgimgattach' . ( $groupi + 1 ) . '" class="bgimgattach" value="scroll"' . checked( 'scroll', $background_attachment, false ) . ' /> <span class="code">' . __( 'Scroll', 'zoom-builder' ) . '</span></label> <label><input type="radio" name="bgimgattach' . ( $groupi + 1 ) . '" class="bgimgattach" value="fixed"' . checked( 'fixed', $background_attachment, false ) . ' /> <span class="code">' . __( 'Fixed', 'zoom-builder' ) . '</span></label></td>
			            </tr>
			          </tbody>
			        </table>
			      </fieldset>
			      <table class="paddmarg">
			        <tbody>
			          <tr valign="top">
			            <th scope="row">' . __( 'Padding:', 'zoom-builder' ) . ' <span class="inputlink paddinglink' . $padding_linked . '" title="' . __( 'Lock all sides to the same value', 'zoom-builder' ) . '"></span></th>
			            <td><input type="text" class="padding paddingtop postop code" size="3" value="' . $padding_top_val . '" autocomplete="off" /></td>
			            <td><input type="text" class="padding paddingleft posleft code" size="3" value="' . $padding_left_val . '" autocomplete="off" /></td>
			            <td><input type="text" class="padding paddingright posright code" size="3" value="' . $padding_right_val . '" autocomplete="off" /></td>
			            <td><input type="text" class="padding paddingbottom posbottom code" size="3" value="' . $padding_bottom_val . '" autocomplete="off" /></td>
			          </tr>
			          <tr valign="top">
			            <th scope="row">' . __( 'Margin:', 'zoom-builder' ) . ' <span class="inputlink marginlink' . $margin_linked . '" title="' . __( 'Lock all sides to the same value', 'zoom-builder' ) . '"></span></th>
			            <td><input type="text" class="margin margintop postop code" size="3" value="' . $margin_top_val . '" autocomplete="off" /></td>
			            <td><input type="text" class="margin marginleft posleft code" size="3" value="' . $margin_left_val . '" autocomplete="off" /></td>
			            <td><input type="text" class="margin marginright posright code" size="3" value="' . $margin_right_val . '" autocomplete="off" /></td>
			            <td><input type="text" class="margin marginbottom posbottom code" size="3" value="' . $margin_bottom_val . '" autocomplete="off" /></td>
			          </tr>
			        </tbody>
			      </table>
			      <fieldset>
			        <legend>' . __( 'Border', 'zoom-builder' ) . '</legend>
			        <table>
			          <thead>
			            <tr>
			              <th>&nbsp;</th>
			              <th>' . __( 'Width', 'zoom-builder' ) . ' <span class="inputlink borderwidthlink' . $borderwidth_linked . '" title="' . __( 'Lock all sides to the same value', 'zoom-builder' ) . '"></span></th>
			              <th>' . __( 'Color', 'zoom-builder' ) . ' <span class="inputlink bordercolorlink' . $bordercolor_linked . '" title="' . __( 'Lock all sides to the same value', 'zoom-builder' ) . '"></span></th>
			              <th>' . __( 'Style', 'zoom-builder' ) . ' <span class="inputlink borderstylelink' . $borderstyle_linked . '" title="' . __( 'Lock all sides to the same value', 'zoom-builder' ) . '"></span></th>
			              <th>' . __( 'Radius', 'zoom-builder' ) . ' <span class="inputlink borderradiuslink' . $borderradius_linked . '" title="' . __( 'Lock all sides to the same value', 'zoom-builder' ) . '"></span></th>
			            </tr>
			          </thead>
			          <tbody>
			            <tr valign="top">
			              <th scope="row">' . __( 'Top:', 'zoom-builder' ) . '</th>
			              <td><input type="text" class="borderwidth borderwidthtop code" size="3" value="' . $borderwidth_top_val . '" autocomplete="off" /></td>
			              <td><input type="text" class="bordercolor bordercolortop code" size="7" value="' . esc_attr( $bordercolor_top_val ) . '" autocomplete="off" /></td>
			              <td><select class="borderstyle borderstyletop code"><option value="none"' . selected( 'none', $borderstyle_top_val, false ) . '>' . __( 'None', 'zoom-builder' ) . '</option><option value="solid"' . selected( 'solid', $borderstyle_top_val, false ) . '>' . __( 'Solid', 'zoom-builder' ) . '</option><option value="dotted"' . selected( 'dotted', $borderstyle_top_val, false ) . '>' . __( 'Dotted', 'zoom-builder' ) . '</option><option value="dashed"' . selected( 'dashed', $borderstyle_top_val, false ) . '>' . __( 'Dashed', 'zoom-builder' ) . '</option><option value="double"' . selected( 'double', $borderstyle_top_val, false ) . '>' . __( 'Double', 'zoom-builder' ) . '</option><option value="groove"' . selected( 'groove', $borderstyle_top_val, false ) . '>' . __( 'Groove', 'zoom-builder' ) . '</option><option value="ridge"' . selected( 'ridge', $borderstyle_top_val, false ) . '>' . __( 'Ridge', 'zoom-builder' ) . '</option><option value="inset"' . selected( 'inset', $borderstyle_top_val, false ) . '>' . __( 'Inset', 'zoom-builder' ) . '</option><option value="outset"' . selected( 'outset', $borderstyle_top_val, false ) . '>' . __( 'Outset', 'zoom-builder' ) . '</option></select></td>
			              <td class="borderradius"><input type="text" class="borderradius borderradiustopright postopright code" size="3" value="' . $borderradius_top_val . '" autocomplete="off" /></td>
			            </tr>
			            <tr valign="top">
			              <th scope="row">' . __( 'Left:', 'zoom-builder' ) . '</th>
			              <td><input type="text" class="borderwidth borderwidthleft code" size="3" value="' . $borderwidth_left_val . '" autocomplete="off" /></td>
			              <td><input type="text" class="bordercolor bordercolorleft code" size="7" value="' . esc_attr( $bordercolor_left_val ) . '" autocomplete="off" /></td>
			              <td><select class="borderstyle borderstyleleft code"><option value="none"' . selected( 'none', $borderstyle_left_val, false ) . '>' . __( 'None', 'zoom-builder' ) . '</option><option value="solid"' . selected( 'solid', $borderstyle_left_val, false ) . '>' . __( 'Solid', 'zoom-builder' ) . '</option><option value="dotted"' . selected( 'dotted', $borderstyle_left_val, false ) . '>' . __( 'Dotted', 'zoom-builder' ) . '</option><option value="dashed"' . selected( 'dashed', $borderstyle_left_val, false ) . '>' . __( 'Dashed', 'zoom-builder' ) . '</option><option value="double"' . selected( 'double', $borderstyle_left_val, false ) . '>' . __( 'Double', 'zoom-builder' ) . '</option><option value="groove"' . selected( 'groove', $borderstyle_left_val, false ) . '>' . __( 'Groove', 'zoom-builder' ) . '</option><option value="ridge"' . selected( 'ridge', $borderstyle_left_val, false ) . '>' . __( 'Ridge', 'zoom-builder' ) . '</option><option value="inset"' . selected( 'inset', $borderstyle_left_val, false ) . '>' . __( 'Inset', 'zoom-builder' ) . '</option><option value="outset"' . selected( 'outset', $borderstyle_left_val, false ) . '>' . __( 'Outset', 'zoom-builder' ) . '</option></select></td>
			              <td class="borderradius"><input type="text" class="borderradius borderradiustopleft postopleft code" size="3" value="' . $borderradius_left_val . '" autocomplete="off" /></td>
			            </tr>
			            <tr valign="top">
			              <th scope="row">' . __( 'Right:', 'zoom-builder' ) . '</th>
			              <td><input type="text" class="borderwidth borderwidthright code" size="3" value="' . $borderwidth_right_val . '" autocomplete="off" /></td>
			              <td><input type="text" class="bordercolor bordercolorright code" size="7" value="' . esc_attr( $bordercolor_right_val ) . '" autocomplete="off" /></td>
			              <td><select class="borderstyle borderstyleright code"><option value="none"' . selected( 'none', $borderstyle_right_val, false ) . '>' . __( 'None', 'zoom-builder' ) . '</option><option value="solid"' . selected( 'solid', $borderstyle_right_val, false ) . '>' . __( 'Solid', 'zoom-builder' ) . '</option><option value="dotted"' . selected( 'dotted', $borderstyle_right_val, false ) . '>' . __( 'Dotted', 'zoom-builder' ) . '</option><option value="dashed"' . selected( 'dashed', $borderstyle_right_val, false ) . '>' . __( 'Dashed', 'zoom-builder' ) . '</option><option value="double"' . selected( 'double', $borderstyle_right_val, false ) . '>' . __( 'Double', 'zoom-builder' ) . '</option><option value="groove"' . selected( 'groove', $borderstyle_right_val, false ) . '>' . __( 'Groove', 'zoom-builder' ) . '</option><option value="ridge"' . selected( 'ridge', $borderstyle_right_val, false ) . '>' . __( 'Ridge', 'zoom-builder' ) . '</option><option value="inset"' . selected( 'inset', $borderstyle_right_val, false ) . '>' . __( 'Inset', 'zoom-builder' ) . '</option><option value="outset"' . selected( 'outset', $borderstyle_right_val, false ) . '>' . __( 'Outset', 'zoom-builder' ) . '</option></select></td>
			              <td class="borderradius"><input type="text" class="borderradius borderradiusbottomright posbottomright code" size="3" value="' . $borderradius_right_val . '" autocomplete="off" /></td>
			            </tr>
			            <tr valign="top">
			              <th scope="row">' . __( 'Bottom:', 'zoom-builder' ) . '</th>
			              <td><input type="text" class="borderwidth borderwidthbottom code" size="3" value="' . $borderwidth_bottom_val . '" autocomplete="off" /></td>
			              <td><input type="text" class="bordercolor bordercolorbottom code" size="7" value="' . esc_attr( $bordercolor_bottom_val ) . '" autocomplete="off" /></td>
			              <td><select class="borderstyle borderstylebottom code"><option value="none"' . selected( 'none', $borderstyle_bottom_val, false ) . '>' . __( 'None', 'zoom-builder' ) . '</option><option value="solid"' . selected( 'solid', $borderstyle_bottom_val, false ) . '>' . __( 'Solid', 'zoom-builder' ) . '</option><option value="dotted"' . selected( 'dotted', $borderstyle_bottom_val, false ) . '>' . __( 'Dotted', 'zoom-builder' ) . '</option><option value="dashed"' . selected( 'dashed', $borderstyle_bottom_val, false ) . '>' . __( 'Dashed', 'zoom-builder' ) . '</option><option value="double"' . selected( 'double', $borderstyle_bottom_val, false ) . '>' . __( 'Double', 'zoom-builder' ) . '</option><option value="groove"' . selected( 'groove', $borderstyle_bottom_val, false ) . '>' . __( 'Groove', 'zoom-builder' ) . '</option><option value="ridge"' . selected( 'ridge', $borderstyle_bottom_val, false ) . '>' . __( 'Ridge', 'zoom-builder' ) . '</option><option value="inset"' . selected( 'inset', $borderstyle_bottom_val, false ) . '>' . __( 'Inset', 'zoom-builder' ) . '</option><option value="outset"' . selected( 'outset', $borderstyle_bottom_val, false ) . '>' . __( 'Outset', 'zoom-builder' ) . '</option></select></td>
			              <td class="borderradius"><input type="text" class="borderradius borderradiusbottomleft posbottomleft code" size="3" value="' . $borderradius_bottom_val . '" autocomplete="off" /></td>
			            </tr>
			          </tbody>
			        </table>
			      </fieldset>
			    </div>
			  </div>
			</div>';

		}

		return $did_one;

	}

	/**
	 * Outputs a JSON file representation of a saved layout as a downloadable file
	 */
	public static function download_export_layout() {

		if ( isset( $_GET['layout_id'] ) && !empty( $_GET['layout_id'] ) && array_key_exists( $_GET['layout_id'], ZOOM_Builder::$saved_layouts ) && current_user_can( 'export' ) ) {

			header( 'Content-Type: application/json' );
			header( sprintf( 'Content-Disposition: attachment; filename="zoom-builder-export_%s.json"', date( 'y-m-d' ) ) );

			echo json_encode( ZOOM_Builder::$saved_layouts[ $_GET['layout_id'] ] );

			exit;

		}

		wp_die( __( 'You do not have permission to access this page.', 'zoom-builder' ) );

	}

}