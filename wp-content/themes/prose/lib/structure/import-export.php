<?php
/**
 * This file controls the import and export functions for Prose.
 *
 * @author StudioPress
 * @since 1.5.0
 */

add_filter( 'genesis_export_options', 'prose_export_options' );
/**
 * Filter the Genesis export options to include Design Settings.
 *
 * Because Genesis is so damn awesome, all you need is a checkbox label and the settings field to export/import.
 *
 * @since 1.5.0
 */
function prose_export_options( $options ) {
	
	$options['design'] = array(
		'label'          => __( 'Design Settings', 'prose' ),
		'settings-field' => PROSE_SETTINGS_FIELD,
	);
	
	return $options;
	
}