<?php

//* News Theme Setting Defaults
add_filter( 'genesis_theme_settings_defaults', 'news_theme_defaults' );
function news_theme_defaults( $defaults ) {

	$defaults['blog_cat_num']              = 10;
	$defaults['content_archive']           = 'full';
	$defaults['content_archive_limit']     = 0;
	$defaults['content_archive_thumbnail'] = 0;
	$defaults['posts_nav']                 = 'numeric';
	$defaults['site_layout']               = 'content-sidebar';

	return $defaults;

}

//* News Theme Setup
add_action( 'after_switch_theme', 'news_theme_setting_defaults' );
function news_theme_setting_defaults() {

	_genesis_update_settings( array(
		'blog_cat_num'              => 10,	
		'content_archive'           => 'full',
		'content_archive_limit'     => 0,
		'content_archive_thumbnail' => 0,
		'posts_nav'                 => 'numeric',
		'site_layout'               => 'content-sidebar',
	) );
	
	_genesis_update_settings( array(
		'location_horizontal'             => 'Left',
		'location_vertical'               => 'Top',
		'posts_num'                       => '5',
		'slideshow_arrows'                => 0,
		'slideshow_excerpt_content_limit' => '100',
		'slideshow_excerpt_content'       => 'full',
		'slideshow_excerpt_width'         => '100',
		'slideshow_excerpt_show'          => 0,
		'slideshow_height'                => '400',
		'slideshow_more_text'             => __( 'Continue Reading&hellip;', 'news' ),
		'slideshow_pager'                 => 1,
		'slideshow_title_show'            => 1,
		'slideshow_width'                 => '737',
	), GENESIS_RESPONSIVE_SLIDER_SETTINGS_FIELD );

	update_option( 'posts_per_page', 10 );

}

//* Set Genesis Responsive Slider defaults
add_filter( 'genesis_responsive_slider_settings_defaults', 'news_responsive_slider_defaults' );
function news_responsive_slider_defaults( $defaults ) {

	$args = array(
		'location_horizontal'             => 'Left',
		'location_vertical'               => 'Top',
		'posts_num'                       => '5',
		'slideshow_arrows'                => 0,
		'slideshow_excerpt_content_limit' => '100',
		'slideshow_excerpt_content'       => 'full',
		'slideshow_excerpt_width'         => '100',
		'slideshow_excerpt_show'          => 0,
		'slideshow_height'                => '400',
		'slideshow_more_text'             => __( 'Continue Reading&hellip;', 'news' ),
		'slideshow_pager'                 => 1,
		'slideshow_title_show'            => 1,
		'slideshow_width'                 => '737',
	);

	$args = wp_parse_args( $args, $defaults );
	
	return $args;
}

//* Simple Social Icon Defaults
add_filter( 'simple_social_default_styles', 'news_social_default_styles' );
function news_social_default_styles( $defaults ) {

	$args = array(
		'alignment'              => 'aligncenter',
		'background_color'       => '#f6f5f2',
		'background_color_hover' => '#000000',
		'border_radius'          => 3,
		'icon_color'             => '#aaaaaa',
		'icon_color_hover'       => '#ffffff',
		'size'                   => 36,
		);
		
	$args = wp_parse_args( $args, $defaults );
	
	return $args;
	
}