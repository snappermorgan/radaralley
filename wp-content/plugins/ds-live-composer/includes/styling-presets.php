<?php

/**
 * Table of Contents
 *
 * dslc_presets_load ( Replace current settings with preset settings )
 */


/**
 * Replace current settings with preset settings
 *
 * @since 1.0.5
 */

function dslc_presets_load( $settings ) {

	// If preset applied
	if ( isset( $settings['css_load_preset'] ) && $settings['css_load_preset'] !== '' ) {

		// Presets data
		$preset_id = $settings['css_load_preset'];
		$presets = maybe_unserialize( get_option( 'dslc_presets' ) );
		$preset_data = false;

		// If there are presets
		if ( is_array( $presets ) ) {

			// Go through all presets
			foreach ( $presets as $preset ) {
					
				// Find the correct preset
				if ( $preset['id'] == $preset_id )
					$preset_data = $preset;

			}

		}

		// If preset exists
		if ( $preset_data ) {

			// Get preset settings
			$preset_settings = maybe_unserialize( base64_decode( $preset_data['code'] ) );
			$preset_settings_stripped = $preset_settings;

			// Go through all the settings
			foreach ( $settings as $key => $value ) {

				// If the setting is in the presets, use it
				if ( isset( $preset_settings[$key] ) ) {
					$settings[$key] = $preset_settings[$key];
					unset( $preset_settings_stripped[$key] );
				}

			}

			// Fill in the blanks
			foreach ( $preset_settings_stripped as $key => $value ) {
				$settings[$key] = $value;
			}

		}

	}

	// Pass the settings back
	return $settings;

} add_filter( 'dslc_filter_settings', 'dslc_presets_load' );