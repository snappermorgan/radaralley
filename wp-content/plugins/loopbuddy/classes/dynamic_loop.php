<?php

/*
Handler for loop rendering
Written by Dustin Bolton, Ronald Huereca, and Chris Jean for iThemes.com
Version 1.4.0

Version History
	1.0.0 - 2012-08-24 - Chris Jean
		Setting version to 1.0.0
	1.1.0 - 2012-08-24 - Chris Jean
		Cleaned up some of the logic used in the render_loop function.
		Set the $wp_query->in_the_loop variable before the call to the main
			render_loop() function and unset it afterwards. This fixes issues
			with plugins that check in_the_loop().
	1.1.1 - 2012-12-01 - Chris Jean
		Minor code cleanup.
	1.3.0 - 2012-12-01 - Chris Jean
		Starting at version 1.3.0 to match the plugin release version.
	1.3.1 - 2013-02-20 - Chris Jean
		Cleaned up logic in the get_loop_type function.
	1.4.0 - 2013-03-18 - Chris Jean
		Added custom post type archive support.
*/


class pb_lb_dynamic_loop {
	function pb_lb_dynamic_loop( &$parent ) {
		$this->_parent = &$parent;
		
		if ( ! function_exists( 'dynamic_loop' ) || ! function_exists( 'register_dynamic_loop_handler' ) )
			return false;
		
		register_dynamic_loop_handler( array( &$this, 'render_loop' ) );
	}
	
	function render_loop() {
		$options = $this->_parent->_options;
		//Are we on a single post or page?  If so, let's check to see if we're overriding the loop
		if ( is_single() || is_page() ) {
			global $post;
			if ( is_object( $post ) ) {
				$meta = $this->_parent->get_post_meta( $post->ID );
				if ( is_array( $meta ) ) {
					if ( $meta[ 'enabled' ] ) {
						//Do custom loop
						//Check query variables to see if they are still valid
						if ( isset( $this->_parent->_options[ 'layouts' ][ $meta[ 'layout' ] ] ) ) {
							$GLOBALS['wp_query']->in_the_loop = true;
							$query_id = isset( $this->_parent->_options['queries'][$meta['query']] ) ? $meta['query'] : false;
							
							$result = $this->_parent->render_loop( $query_id, $meta['layout'] );
							
							$GLOBALS['wp_query']->in_the_loop = false;
							
							if ( ! is_wp_error( $result ) ) {
								echo $result;
								return true;
							}
							
							return false;
						}
					} //end meta enabled
				} //end is_array( $meta )
			} //end is_object( $post );
		} //end if single || page
		
		//Check to see if the loop object is present
		if ( !isset( $options[ 'loops' ] ) ) return false;
		//Get the current post type
		$loop_type = $this->get_loop_type();
		
		$loop_settings = isset( $options['loops'][$loop_type] ) ? $options['loops'][$loop_type] : false;
		
		if ( $loop_settings ) {
			$query_id = ( 'default' == $loop_settings['query'] ) ? -1 : $loop_settings['query'];
			$layout_id = ( 'default' == $loop_settings['layout'] ) ? -1 : $loop_settings['layout'];
			
			if ( ( -1 === $query_id ) && ( -1 === $layout_id ) )
				return false;
			
			
			$GLOBALS['wp_query']->in_the_loop = true;
			
			$result = $this->_parent->render_loop( $query_id, $layout_id );
			
			$GLOBALS['wp_query']->in_the_loop = false;
			
			if ( ! is_wp_error( $result ) ) {
				echo $result;
				return true;
			}
			
			return false;
		}
		
		return false;
	} //end render_loop
	//Gets the loop type - false on failure
	function get_loop_type() {
		if ( is_single() || is_page() || is_attachment() )
			return get_post_type();
		
		if ( is_category() )
			return 'category';
		
		if ( is_search() )
			return 'search';
		
		if ( is_tag() )
			return 'post_tag';
		
		if ( is_404() )
			return '404';
		
		if ( is_archive() ) {
			if ( is_post_type_archive() )
				return 'post_type_archive_' . get_post_type();
			
			if ( is_day() )
				return 'day_archive';
			
			if ( is_month() )
				return 'month_archive';
			
			if ( is_year() )
				return 'year_archive';
			
			$taxonomy_name = get_query_var( 'taxonomy' );
			if ( ! empty( $taxonomy_name ) )
				return $taxonomy_name;
			
			return false;
		}
		
		if ( is_home() )
			return 'home';
		
		if ( is_front_page() )
			return 'front';
		
		return false;
	} //end get_loop_type
} //end class pb_lb_dynamic_loop

$pb_lb_dynamic_loop = new pb_lb_dynamic_loop( $this );
