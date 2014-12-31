<?php



	global $content_width;

	$dslc_plugin_options['dslc_plugin_options'] = array(
		'title' => __( 'General Options', 'dslc_string' ),
		'options' => array(
			'lc_max_width' => array(
				'label' => __( 'Max Width', 'dslc_string' ),
				'std' => '',
				'type' => 'text',
				'descr' => __( 'The width of the modules section when row is set to wrapped. If not set the $content_width variable from theme will be used.', 'dslc_string' ),
			),
			'lc_force_important_css' => array(
				'label' => __( 'Force !important CSS', 'dslc_string' ),
				'std' => 'disabled',
				'type' => 'select',
				'descr' => __( 'In case the CSS from the theme is influencing CSS for the modules, enabling this will in most cases fix that.', 'dslc_string' ),
				'choices' => array(
					array(
						'label' => __( 'Enabled', 'dslc_string' ),
						'value' => 'enabled'
					),
					array(
						'label' => __( 'Disabled', 'dslc_string' ),
						'value' => 'disabled'
					)
				)
			)
		)
	);

	$dslc_plugin_options['dslc_plugin_options_widgets_m'] = array(
		'title' => __( 'Widgets Module', 'dslc_string' ),
		'options' => array(

			'sidebars' => array (
				'label' => __( 'Sidebars', 'dslc_string' ),
				'std' => '',
				'type' => 'list'
			),
		)
	);

	$dslc_plugin_options['dslc_plugin_options_cpt_slugs'] = array(

		'title' => __( 'Slugs', 'dslc_string' ),
		'options' => array( 

			'projects_slug' => array(
				'label' => __( '<strong>Project</strong> Slug', 'dslc_string' ),
				'std' => 'project-view',
				'type' => 'text'
			),
			'projects_cats_slug' => array(
				'label' => __( '<strong>Projects</strong> Category Slug', 'dslc_string' ),
				'std' => 'dslc_projects_cats',
				'type' => 'text'
			),

			'galleries_slug' => array(
				'label' => __( '<strong>Gallery</strong> Slug', 'dslc_string' ),
				'std' => 'gallery-view',
				'type' => 'text'
			),
			'galleries_cats_slug' => array(
				'label' => __( '<strong>Galleries</strong> Category Slug', 'dslc_string' ),
				'std' => 'dslc_galleries_cats',
				'type' => 'text'
			),

			'downloads_slug' => array(
				'label' => __( '<strong>Download</strong> Slug', 'dslc_string' ),
				'std' => 'download-view',
				'type' => 'text'
			),
			'downloads_cats_slug' => array(
				'label' => __( '<strong>Downloads</strong> Categories Slug', 'dslc_string' ),
				'std' => 'dslc_downloads_cat',
				'type' => 'text'
			),
			'downloads_tags_slug' => array(
				'label' => __( '<strong>Downloads</strong> Tags Slug', 'dslc_string' ),
				'std' => 'dslc_downloads_tag',
				'type' => 'text'
			),

			'staff_slug' => array(
				'label' => __( '<strong>Staff</strong> Slug', 'dslc_string' ),
				'std' => 'staff-view',
				'type' => 'text'
			),
			'staff_cats_slug' => array(
				'label' => __( '<strong>Staff</strong> Categories Slug', 'dslc_string' ),
				'std' => 'dslc_staff_cats',
				'type' => 'text'
			),

			'partners_slug' => array(
				'label' => __( '<strong>Partner</strong> Slug', 'dslc_string' ),
				'std' => 'partner-view',
				'type' => 'text'
			),
			'partners_cats_slug' => array(
				'label' => __( '<strong>Partners</strong> Categories Slug', 'dslc_string' ),
				'std' => 'dslc_partners_cats',
				'type' => 'text'
			),
			

		)

	);

/**
 * Feature Control
 */

function dslc_feature_control_settings() {

	global $dslc_var_modules;
	global $dslc_plugin_options;

	$module_opts_array = array();

	foreach ( $dslc_var_modules as $module ) {

		$module_opts_array[ $module['id'] ] = array(
			'label' => '"' . $module['title'] . '" <small>module</small>',
			'std' => 'enabled',
			'type' => 'select',
			'choices' => array(
				array(
					'label' => __( '&#x2714; Enabled', 'dslc_string' ),
					'value' => 'enabled'
				),
				array(
					'label' => __( '&#x2716; Disabled', 'dslc_string' ),
					'value' => 'disabled'
				)
			)
		);

	}

	$dslc_plugin_options['dslc_plugin_options_features'] = array(
		'title' => __( 'Features Control', 'dslc_string' ),
		'options' => $module_opts_array
	);

} add_action( 'dslc_hook_register_modules', 'dslc_feature_control_settings', 999 );

function dslc_feature_control_unregister() {

	global $dslc_var_modules;
	$features = dslc_get_options( 'dslc_plugin_options_features' );

	foreach ( $dslc_var_modules as $module ) {
		if ( isset( $features[ $module['id'] ] ) && $features[ $module['id'] ] == 'disabled' ) {
			dslc_unregister_module( $module['id'] );
		}
	}


} add_action( 'dslc_hook_unregister_modules', 'dslc_feature_control_unregister', 999 );