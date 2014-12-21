<?php


function pb_lb_sort_by_title( $a, $b ) {
	return strcasecmp( $a['title'], $b['title'] );
}

if ( !function_exists( 'wp_print_r' ) ) {
	function wp_print_r( $args, $die = true ) {
		$echo = '<pre>' . print_r( $args, true ) . '</pre>';
		if ( $die ) die( $echo );
		else echo $echo;
	}
}
