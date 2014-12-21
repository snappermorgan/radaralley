<?php
/**
 * Saves the lightbox optin "Balanace Theme".
 *
 * @package      OptinMonster
 * @since        1.0.0
 * @author       Thomas Griffin <thomas@retyp.com>
 * @copyright    Copyright (c) 2013, Thomas Griffin
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Save an optin theme.
 *
 * @package      OptinMonster
 * @since        1.0.0
 */
class optin_monster_save_lightbox_balance_theme {

	/**
	 * Prepare any base class properties.
	 *
	 * @since 1.0.0
	 */
	public $type, $theme, $optin, $meta, $data;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $type, $theme, $optin, $data ) {

		global $optin_monster_account;

		// Set class properties.
		$this->type    = $type;
		$this->theme   = $theme;
		$this->optin   = $optin;
		$this->data    = $data;
		$this->meta    = get_post_meta( $this->optin, '_om_meta', true );
		$this->account = $optin_monster_account;

	}

	/**
	 * Builds out the proper optin theme and styling.
	 *
	 * @since 1.0.0
	 */
	public function save_optin() {

		// Save the meta for the optin.
		$this->meta['type']  = $this->type;
		$this->meta['theme'] = $this->theme;

		// Save optin background colors.
		if ( empty( $this->meta['background']['header'] ) )
		    $this->meta['background']['header'] = isset( $this->data['optin_header_bg'] ) ? esc_attr( $this->data['optin_header_bg'] ) : '';
		else
		    $this->meta['background']['header'] = isset( $this->data['optin_header_bg'] ) ? esc_attr( $this->data['optin_header_bg'] ) : $this->meta['background']['header'];

		if ( empty( $this->meta['background']['content'] ) )
		    $this->meta['background']['content'] = isset( $this->data['optin_content_bg'] ) ? esc_attr( $this->data['optin_content_bg'] ) : '';
		else
		    $this->meta['background']['content'] = isset( $this->data['optin_content_bg'] ) ? esc_attr( $this->data['optin_content_bg'] ) : $this->meta['background']['content'];

		if ( empty( $this->meta['background']['footer'] ) )
		    $this->meta['background']['footer'] = isset( $this->data['optin_footer_bg'] ) ? esc_attr( $this->data['optin_footer_bg'] ) : '';
		else
		    $this->meta['background']['footer'] = isset( $this->data['optin_footer_bg'] ) ? esc_attr( $this->data['optin_footer_bg'] ) : $this->meta['background']['footer'];

		// Save the optin title.
		if ( empty( $this->meta['title']['text'] ) )
		    $this->meta['title']['text'] = isset( $this->data['optin_title'] ) ? wp_kses_post( $this->data['optin_title'] ) : '';
		else
		    $this->meta['title']['text'] = isset( $this->data['optin_title'] ) ? wp_kses_post( $this->data['optin_title'] ) : $this->meta['title']['text'];

		if ( empty( $this->meta['title']['color'] ) )
		    $this->meta['title']['color'] = isset( $this->data['optin_title_color'] ) ? esc_attr( $this->data['optin_title_color'] ) : '';
		else
		    $this->meta['title']['color'] = isset( $this->data['optin_title_color'] ) ? esc_attr( $this->data['optin_title_color'] ) : $this->meta['title']['color'];

		if ( empty( $this->meta['title']['font'] ) ) {
		    $this->meta['title']['font'] = isset( $this->data['optin_title_font'] ) && 'Select your font...' !== $this->data['optin_title_font'] ? esc_attr( $this->data['optin_title_font'] ) : '';
		} else {
		    $this->meta['title']['font'] = isset( $this->data['optin_title_font'] ) && 'Select your font...' !== $this->data['optin_title_font'] ? esc_attr( $this->data['optin_title_font'] ) : $this->meta['title']['font'];
		}

		if ( empty( $this->meta['title']['size'] ) )
		    $this->meta['title']['size'] = isset( $this->data['optin_title_size'] ) ? esc_attr( $this->data['optin_title_size'] ) : '';
		else
		    $this->meta['title']['size'] = isset( $this->data['optin_title_size'] ) ? esc_attr( $this->data['optin_title_size'] ) : $this->meta['title']['size'];

		// Save all meta related items.
		$this->save_meta( 'title' );

		// Save the optin tagline.
		if ( empty( $this->meta['tagline']['text'] ) )
		    $this->meta['tagline']['text'] = isset( $this->data['optin_tagline'] ) ? wp_kses_post( $this->data['optin_tagline'] ) : '';
		else
		    $this->meta['tagline']['text'] = isset( $this->data['optin_tagline'] ) ? wp_kses_post( $this->data['optin_tagline'] ) : $this->meta['tagline']['text'];

		if ( empty( $this->meta['tagline']['color'] ) )
		    $this->meta['tagline']['color'] = isset( $this->data['optin_tagline_color'] ) ? esc_attr( $this->data['optin_tagline_color'] ) : '';
		else
		    $this->meta['tagline']['color'] = isset( $this->data['optin_tagline_color'] ) ? esc_attr( $this->data['optin_tagline_color'] ) : $this->meta['tagline']['color'];

		if ( empty( $this->meta['tagline']['font'] ) ) {
		    $this->meta['tagline']['font'] = isset( $this->data['optin_tagline_font'] ) && 'Select your font...' !== $this->data['optin_tagline_font'] ? esc_attr( $this->data['optin_tagline_font'] ) : '';
		} else {
		    $this->meta['tagline']['font'] = isset( $this->data['optin_tagline_font'] ) && 'Select your font...' !== $this->data['optin_tagline_font'] ? esc_attr( $this->data['optin_tagline_font'] ) : $this->meta['tagline']['font'];
		}

		if ( empty( $this->meta['tagline']['size'] ) )
		    $this->meta['tagline']['size'] = isset( $this->data['optin_tagline_size'] ) ? esc_attr( $this->data['optin_tagline_size'] ) : '';
		else
		    $this->meta['tagline']['size'] = isset( $this->data['optin_tagline_size'] ) ? esc_attr( $this->data['optin_tagline_size'] ) : $this->meta['tagline']['size'];

        // Save all meta related items.
		$this->save_meta( 'tagline' );

		// Save any optin bullets.
		if ( empty( $this->meta['bullet']['text'] ) ) {
		    if ( isset( $this->data['optin_bullet'] ) ) {
		        $this->meta['bullet']['text'] = array();
			    foreach ( (array) $this->data['optin_bullet'] as $i => $bullet )
			        $this->meta['bullet']['text'][$i] = wp_kses_post( $bullet );
		    } else {
			    $this->meta['bullet']['text'] = array();
		    }
		} else {
			if ( isset( $this->data['optin_bullet'] ) ) {
			    $this->meta['bullet']['text'] = array();
			    foreach ( (array) $this->data['optin_bullet'] as $i => $bullet )
			        $this->meta['bullet']['text'][$i] = wp_kses_post( $bullet );
		    } else {
			    $this->meta['bullet']['text'] = array();
		    }
		}

		if ( empty( $this->meta['bullet']['color'] ) )
		    $this->meta['bullet']['color'] = isset( $this->data['optin_bullet_color'] ) ? esc_attr( $this->data['optin_bullet_color'] ) : '';
		else
		    $this->meta['bullet']['color'] = isset( $this->data['optin_bullet_color'] ) ? esc_attr( $this->data['optin_bullet_color'] ) : $this->meta['bullet']['color'];

		if ( empty( $this->meta['bullet']['font'] ) ) {
		    $this->meta['bullet']['font'] = isset( $this->data['optin_bullet_font'] ) && 'Select your font...' !== $this->data['optin_bullet_font'] ? esc_attr( $this->data['optin_bullet_font'] ) : '';
		} else {
		    $this->meta['bullet']['font'] = isset( $this->data['optin_bullet_font'] ) && 'Select your font...' !== $this->data['optin_bullet_font'] ? esc_attr( $this->data['optin_bullet_font'] ) : $this->meta['bullet']['font'];
		}

		if ( empty( $this->meta['bullet']['size'] ) )
		    $this->meta['bullet']['size'] = isset( $this->data['optin_bullet_size'] ) ? esc_attr( $this->data['optin_bullet_size'] ) : '';
		else
		    $this->meta['bullet']['size'] = isset( $this->data['optin_bullet_size'] ) ? esc_attr( $this->data['optin_bullet_size'] ) : $this->meta['bullet']['size'];

		// Save all meta related items.
		$this->save_meta( 'bullet' );

		// Save the image field.
		if ( ! empty( $this->meta['image'] ) )
		    $this->meta['image'] = isset( $this->data['optin_image'] ) ? $this->data['optin_image'] : '';
		else
		    $this->meta['image'] = isset( $this->data['optin_image'] ) ? $this->data['optin_image'] : $this->meta['image'];

		// Save the optin name input field.
		if ( isset( $this->data['optin_name_show'] ) )
			$this->meta['name']['show'] = true;
		else
			$this->meta['name']['show'] = false;

		if ( empty( $this->meta['name']['placeholder'] ) )
		    $this->meta['name']['placeholder'] = isset( $this->data['optin_name_placeholder'] ) ? strip_tags( esc_attr( $this->data['optin_name_placeholder'] ) ) : '';
		else
		    $this->meta['name']['placeholder'] = isset( $this->data['optin_name_placeholder'] ) ? strip_tags( esc_attr( $this->data['optin_name_placeholder'] ) ) : $this->meta['name']['placeholder'];

		if ( empty( $this->meta['name']['color'] ) )
		    $this->meta['name']['color'] = isset( $this->data['optin_name_color'] ) ? esc_attr( $this->data['optin_name_color'] ) : '';
		else
		    $this->meta['name']['color'] = isset( $this->data['optin_name_color'] ) ? esc_attr( $this->data['optin_name_color'] ) : $this->meta['name']['color'];

		if ( empty( $this->meta['name']['font'] ) ) {
		    $this->meta['name']['font'] = isset( $this->data['optin_name_font'] ) && 'Select your font...' !== $this->data['optin_name_font'] ? esc_attr( $this->data['optin_name_font'] ) : '';
		} else {
		    $this->meta['name']['font'] = isset( $this->data['optin_name_font'] ) && 'Select your font...' !== $this->data['optin_name_font'] ? esc_attr( $this->data['optin_name_font'] ) : $this->meta['name']['font'];
		}

		if ( empty( $this->meta['name']['size'] ) )
		    $this->meta['name']['size'] = isset( $this->data['optin_name_size'] ) ? esc_attr( $this->data['optin_name_size'] ) : '';
		else
		    $this->meta['name']['size'] = isset( $this->data['optin_name_size'] ) ? esc_attr( $this->data['optin_name_size'] ) : $this->meta['name']['size'];

		// Save all meta related items.
		$this->save_meta( 'name' );


		// Save the optin email input field.
		if ( empty( $this->meta['email']['placeholder'] ) )
		    $this->meta['email']['placeholder'] = isset( $this->data['optin_email_placeholder'] ) ? strip_tags( esc_attr( $this->data['optin_email_placeholder'] ) ) : '';
		else
		    $this->meta['email']['placeholder'] = isset( $this->data['optin_email_placeholder'] ) ? strip_tags( esc_attr( $this->data['optin_email_placeholder'] ) ) : $this->meta['email']['placeholder'];

		if ( empty( $this->meta['email']['color'] ) )
		    $this->meta['email']['color'] = isset( $this->data['optin_email_color'] ) ? esc_attr( $this->data['optin_email_color'] ) : '';
		else
		    $this->meta['email']['color'] = isset( $this->data['optin_email_color'] ) ? esc_attr( $this->data['optin_email_color'] ) : $this->meta['email']['color'];

		if ( empty( $this->meta['email']['font'] ) ) {
		    $this->meta['email']['font'] = isset( $this->data['optin_email_font'] ) && 'Select your font...' !== $this->data['optin_email_font'] ? esc_attr( $this->data['optin_email_font'] ) : '';
		} else {
		    $this->meta['email']['font'] = isset( $this->data['optin_email_font'] ) && 'Select your font...' !== $this->data['optin_email_font'] ? esc_attr( $this->data['optin_email_font'] ) : $this->meta['email']['font'];
		}

		if ( empty( $this->meta['email']['size'] ) )
		    $this->meta['email']['size'] = isset( $this->data['optin_email_size'] ) ? esc_attr( $this->data['optin_email_size'] ) : '';
		else
		    $this->meta['email']['size'] = isset( $this->data['optin_email_size'] ) ? esc_attr( $this->data['optin_email_size'] ) : $this->meta['email']['size'];

		// Save all meta related items.
		$this->save_meta( 'email' );

		// Save the optin submit field.
		if ( empty( $this->meta['submit']['placeholder'] ) )
		    $this->meta['submit']['placeholder'] = isset( $this->data['optin_submit_placeholder'] ) ? strip_tags( esc_attr( $this->data['optin_submit_placeholder'] ) ) : '';
		else
		    $this->meta['submit']['placeholder'] = isset( $this->data['optin_submit_placeholder'] ) ? strip_tags( esc_attr( $this->data['optin_submit_placeholder'] ) ) : $this->meta['submit']['placeholder'];

		if ( empty( $this->meta['submit']['field_color'] ) )
		    $this->meta['submit']['field_color'] = isset( $this->data['optin_submit_field_color'] ) ? esc_attr( $this->data['optin_submit_field_color'] ) : '';
		else
		    $this->meta['submit']['field_color'] = isset( $this->data['optin_submit_field_color'] ) ? esc_attr( $this->data['optin_submit_field_color'] ) : $this->meta['submit']['field_color'];

		if ( empty( $this->meta['submit']['bg_color'] ) )
		    $this->meta['submit']['bg_color'] = isset( $this->data['optin_submit_bg_color'] ) ? esc_attr( $this->data['optin_submit_bg_color'] ) : '';
		else
		    $this->meta['submit']['bg_color'] = isset( $this->data['optin_submit_bg_color'] ) ? esc_attr( $this->data['optin_submit_bg_color'] ) : $this->meta['submit']['bg_color'];

		if ( empty( $this->meta['submit']['font'] ) ) {
		    $this->meta['submit']['font'] = isset( $this->data['optin_submit_font'] ) && 'Select your font...' !== $this->data['optin_submit_font'] ? esc_attr( $this->data['optin_submit_font'] ) : '';
		} else {
		    $this->meta['submit']['font'] = isset( $this->data['optin_submit_font'] ) && 'Select your font...' !== $this->data['optin_submit_font'] ? esc_attr( $this->data['optin_submit_font'] ) : $this->meta['submit']['font'];
		}

		if ( empty( $this->meta['submit']['size'] ) )
		    $this->meta['submit']['size'] = isset( $this->data['optin_submit_size'] ) ? esc_attr( $this->data['optin_submit_size'] ) : '';
		else
		    $this->meta['submit']['size'] = isset( $this->data['optin_submit_size'] ) ? esc_attr( $this->data['optin_submit_size'] ) : $this->meta['submit']['size'];

		// Save all meta related items.
		$this->save_meta( 'submit' );

		// Build all of the fonts together.
		$this->meta['fonts'] = array( $this->meta['title']['font'], $this->meta['tagline']['font'], $this->meta['bullet']['font'], $this->meta['name']['font'], $this->meta['email']['font'], $this->meta['submit']['font'] );
		$this->meta['fonts'] = array_filter( $this->meta['fonts'] );
		$this->meta['fonts'] = array_unique( $this->meta['fonts'] );

		// Remove any non-Google related fonts.
		foreach ( $this->meta['fonts'] as $i => $font )
			if ( ! in_array( $font, $this->account->get_available_fonts( false ) ) )
				unset( $this->meta['fonts'][$i] );

        // If there is any custom CSS, save that now.
        if ( isset( $this->data['optin_custom_css'] ) )
            $this->meta['custom_css'] = trim( esc_html( $this->data['optin_custom_css'] ) );

		// Finally update the post meta.
		update_post_meta( $this->optin, '_om_meta', $this->meta );

	}

	public function get_field( $field, $subfield = '' ) {

		if ( ! empty( $subfield ) )
			return isset( $this->meta[$field][$subfield] ) ? $this->meta[$field][$subfield] : '';
		else
			return isset( $this->meta[$field] ) ? $this->meta[$field] : '';

	}

	public function save_meta( $field ) {

		if ( 'email' == $field || 'name' == $field || 'submit' == $field )
			$meta_field = $field . '_placeholder';
		else
			$meta_field = $field;

		if ( ! isset( $this->meta[$field]['meta']['font_weight'] ) )
		    $this->meta[$field]['meta']['font_weight'] = isset( $this->data['optin_' . $meta_field . '_font-weight'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_font-weight'] ) : 'normal';
		else
		    $this->meta[$field]['meta']['font_weight'] = isset( $this->data['optin_' . $meta_field . '_font-weight'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_font-weight'] ) : $this->meta[$field]['meta']['font_weight'];

		if ( ! isset( $this->meta[$field]['meta']['font_style'] ) )
		    $this->meta[$field]['meta']['font_style'] = isset( $this->data['optin_' . $meta_field . '_font-style'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_font-style'] ) : 'normal';
		else
		    $this->meta[$field]['meta']['font_style'] = isset( $this->data['optin_' . $meta_field . '_font-style'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_font-style'] ) : $this->meta[$field]['meta']['font_style'];

		if ( ! isset( $this->meta[$field]['meta']['text_decoration'] ) )
		    $this->meta[$field]['meta']['text_decoration'] = isset( $this->data['optin_' . $meta_field . '_text-decoration'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_text-decoration'] ) : 'none';
		else
		    $this->meta[$field]['meta']['text_decoration'] = isset( $this->data['optin_' . $meta_field . '_text-decoration'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_text-decoration'] ) : $this->meta[$field]['meta']['text_decoration'];

		if ( ! isset( $this->meta[$field]['meta']['text_align'] ) )
		    $this->meta[$field]['meta']['text_align'] = isset( $this->data['optin_' . $meta_field . '_text-align'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_text-align'] ) : (( 'submit' == $field ) ? 'center' : 'left' );
		else
		    $this->meta[$field]['meta']['text_align'] = isset( $this->data['optin_' . $meta_field . '_text-align'] ) ? esc_attr( $this->data['optin_' . $meta_field . '_text-align'] ) : $this->meta[$field]['meta']['text_align'];

	}

}