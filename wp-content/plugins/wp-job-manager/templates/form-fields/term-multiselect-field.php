<?php 
// Get selected value
if ( isset( $field['value'] ) ) {
	$selected = $field['value'];
} elseif (  ! empty( $field['default'] ) && is_int( $field['default'] ) ) {
	$selected = $field['default'];
} elseif ( ! empty( $field['default'] ) && ( $term = get_term_by( 'slug', $field['default'], $field['taxonomy'] ) ) ) {
	$selected = $term->term_id;
} else {
	$selected = '';
}

wp_enqueue_script( 'wp-job-manager-term-multiselect' );

job_manager_dropdown_categories( array( 
	'taxonomy'     => $field['taxonomy'],
	'hierarchical' => 1, 
	'name'         => isset( $field['name'] ) ? $field['name'] : $key, 
	'orderby'      => 'name', 
	'selected'     => $selected,
	'hide_empty'   => false,
) );

if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>