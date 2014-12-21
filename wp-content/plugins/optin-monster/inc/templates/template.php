<?php
/**
 * Builds out the HTML templates and styles for each optin design.
 *
 * @package      OptinMonster
 * @since        1.0.0
 * @author       Thomas Griffin <thomas@retyp.com>
 * @copyright    Copyright (c) 2013, Thomas Griffin
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Base non-logged in class that kick starts the theme.
 *
 * @package      OptinMonster
 * @since        1.0.0
 */
class optin_monster_template {

	/**
	 * Prepare any base class properties.
	 *
	 * @since 1.0.0
	 */
	public $type, $theme, $hash, $optin, $meta, $env, $ssl;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $type, $theme, $hash, $optin, $env = '', $ssl = false ) {

		// Set class properties.
		global $optin_monster;
		$this->base    = $optin_monster;
		$this->type    = $type;
		$this->theme   = $theme;
		$this->hash    = $hash;
		$this->optin   = $optin;
		$this->env     = $env;
		$this->ssl     = $ssl;

	}

	/**
	 * Builds out the proper optin theme and styling.
	 *
	 * @since 1.0.0
	 */
	public function build_optin() {

		// Prepare the HTML holder.
		$template = $this->type . '-' . $this->theme;
		if ( 'lightbox' == $this->type ) {
    		require_once plugin_dir_path( $this->base->file ) . 'inc/templates/' . $template . '.php';
    		$class = 'optin_monster_build_' . str_replace( '-', '_', $template );
    		$build = new $class( $this->type, $this->theme, $this->hash, $this->optin, $this->env, $this->ssl, $this->base );
    		$html  = $build->build();
        } else {
            // Provide a filter for building out the design template.
            $html = apply_filters( 'optin_monster_template_' . $this->type, '', $this->theme, $this->base, $this->hash, $this->optin, $this->env, $this->ssl );
        }

		// Return the HTML optin output.
		return $html;

	}

}