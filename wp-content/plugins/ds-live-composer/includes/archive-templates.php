<?php

/**
 * Table of Contents
 *
 * dslc_archive_template_redirect ( Load custom template )
 * dslc_archive_template_init ( Register options )
 */


/**
 * Load custom template 
 *
 * @since 1.0.5
 */

function dslc_archive_template_redirect( $archive_template ) {

	global $post;

	$template = dslc_get_option( $post->post_type, 'dslc_plugin_options_archives' );
	if ( ! $template || $template == 'none' ) return $archive_template;

	$archive_template = DS_LIVE_COMPOSER_ABS . '/templates/dslc-archive.php';
	return $archive_template;

}
add_filter( 'archive_template', 'dslc_archive_template_redirect' );
add_filter( 'category_template', 'dslc_archive_template_redirect' );

/**
 * Load custom template for author archive
 *
 * @since 1.0.6
 */

function dslc_author_archive_template_redirect( $archive_template ) {	

	$template = dslc_get_option( 'author', 'dslc_plugin_options_archives' );
	if ( ! $template || $template == 'none' ) return $archive_template;

	$archive_template = DS_LIVE_COMPOSER_ABS . '/templates/dslc-archive.php';
	return $archive_template;

} add_filter( 'author_template', 'dslc_author_archive_template_redirect' );

/**
 * Register options
 *
 * @since 1.0.5
 */

function dslc_archive_template_init() {

	global $dslc_plugin_options;
	global $dslc_var_modules;
	global $dslc_post_types;

	$opts = array();

	// Page Options
	$pages_opts = array();
	$pages_opts[] = array(
		'label' => __( 'Default', 'dslc_string' ),
		'value' => 'none'
	);
	$pages = get_pages();	
	foreach ( $pages as $page ) {
		$pages_opts[] = array(
			'label' => $page->post_title,
			'value' => $page->ID
		);
	}

	foreach ( $dslc_post_types as $post_type ) {

		$opts[$post_type] = array(
			'label' => $post_type . ' archives',
			'descr' => __( 'Choose which page should serve as template.', 'dslc_string' ),
			'std' => 'none',
			'type' => 'select',
			'choices' => $pages_opts
		);

	}

	$opts['author'] = array(
		'label' => 'Author archives',
		'descr' => __( 'Choose which page should serve as template.', 'dslc_string' ),
		'std' => 'none',
		'type' => 'select',
		'choices' => $pages_opts
	);

	$dslc_plugin_options['dslc_plugin_options_archives'] = array(
		'title' => __( 'Archives Settings', 'dslc_string' ),
		'options' => $opts
	);

} add_action( 'dslc_hook_register_options', 'dslc_archive_template_init' );