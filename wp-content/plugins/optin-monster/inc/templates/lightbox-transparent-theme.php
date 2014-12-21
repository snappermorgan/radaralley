<?php
/**
 * Builds out the ligthbox transparent theme.
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
class optin_monster_build_lightbox_transparent_theme {

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
	public function __construct( $type, $theme, $hash, $optin, $env = '', $ssl = false, $base ) {

		// Set class properties.
		$this->type    = $type;
		$this->theme   = $theme;
		$this->hash    = $hash;
		$this->optin   = $optin;
		$this->meta    = get_post_meta( $this->optin, '_om_meta', true );
		$this->env     = $env;
		$this->ssl     = $ssl;
		$this->base    = $base;

	}

	/**
	 * Builds out a lightbox optin using the "Balance Theme".
	 *
	 * @since 1.0.0
	 */
	public function build() {

		// Build out the CSS styling for the optin.
		$theme = '<style type="text/css">';
		$css = '
		html div#om-' . $this->hash . ',
		html div#om-' . $this->hash . ' * {
			background:none;
			border: 0;
			border-radius:0;
			-webkit-border-radius:0;
			-moz-border-radius:0;
			float:none;
			font:normal 100%/normal helvetica,arial,sans-serif;
			-webkit-font-smoothing:antialiased;
			height:auto;
			letter-spacing:normal;
			outline:none;
			position:static;
			text-decoration:none;
			text-indent:0;
			text-shadow:none;
			text-transform:none;
			width:auto;
			visibility:visible;
			overflow:visible;
			margin:0;
			padding:0;
			line-height:1;
			box-sizing:border-box;
			-webkit-box-sizing:border-box;
			-moz-box-sizing:border-box;
			-webkit-box-shadow:none;
			-moz-box-shadow:none;
			-ms-box-shadow:none;
			-o-box-shadow:none;
			box-shadow:none;
			-webkit-appearance:none;
		}
		html div#om-' . $this->hash . ' {
			background: rgb(0, 0, 0);
			background: rgba(0, 0, 0, .7);
			-webkit-font-smoothing: antialiased;
			line-height: 1;
			width: 100%;
			height: 100%;
		}
		html div#om-' . $this->hash . ' strong {
			font-weight: bold;
		}
		html div#om-' . $this->hash . ' em {
			font-style: italic;
		}
		html div#om-' . $this->hash . ' u {
			text-decoration: underline;
		}
		html div#om-' . $this->hash . ' .om-clearfix {
			clear: both;
		}
		html div#om-' . $this->hash . ' .om-clearfix:after {
			clear: both;
			content: ".";
			display: block;
			height: 0;
			line-height: 0;
			overflow: auto;
			visibility: hidden;
			zoom: 1;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin {
			background: #fff;
			display: none;
			position: absolute;
			top: 50%;
			left: 50%;
			min-height: 175px;
			width: 714px;
			z-index: ' . ( 'customizer' == $this->env ? 1 : 734626274 ) . ';
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-wrap {
			position: relative;
			height: 100%;
			border: 7px solid #474747;
		}
		html div#om-' . $this->hash . ' #om-close {
			position: absolute;
			top: -19px;
			right: -19px;
			text-decoration: none !important;
			display: block;
			width: 35px;
			height: 35px;
			background: transparent url(' . plugins_url( 'inc/css/images/case-study-close.png', $this->base->file ) . ') no-repeat scroll 0 0 !important;
			z-index: 321;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-content {
			width: 700px;
			height: 450px;
			position: relative;
			margin: 0;
			padding: 0;
			background: ' . ( 'customizer' == $this->env ? '#5c5c5c url(' . plugins_url( 'inc/css/images/image-holder.png', $this->base->file ) . ') no-repeat scroll 50% 50%;' : '#5c5c5c;' ) . '
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-image-container {
			width: 700px;
			height: 450px;
			display: block;
			margin: 0 auto;
			text-align: center;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-footer {
		    position: absolute;
		    bottom: 0;
		    right: 0;
		    left: auto;
		    top: auto;
			padding: 20px;
			background: rgba(255, 255, 255, .15);
			width: 255px;
		}
		html div#om-' . $this->hash . ' label {
		    color: #333;
		}
        html div#om-' . $this->hash . ' input,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-name,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-email {
			background-color: #fff;
			width: 215px;
			border: 1px solid #ccc;
			font-size: 16px;
			line-height: 24px;
			padding: 4px 6px;
			overflow: hidden;
			outline: none;
			margin: 0 auto 10px;
			vertical-align: middle;
			display: block;
		}
		html div#om-' . $this->hash . ' input[type="submit"],
		html div#om-' . $this->hash . ' button,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-submit {
			background: #ff6200;
			border: 1px solid #ff6200;
			width: 215px;
			color: #fff;
			font-size: 16px;
			padding: 4px 6px;
			line-height: 24px;
			height: ' . ( isset( $this->meta['email']['provider'] ) && 'custom' == $this->meta['email']['provider'] ? '34px' : '24px' ) . ';
			text-align: center;
			margin: 0 auto;
			vertical-align: middle;
			cursor: pointer;
			display: block;
			margin: 0;
		}
		html div#om-' . $this->hash . ' input[type=checkbox],
		html div#om-' . $this->hash . ' input[type=radio] {
		    -webkit-appearance: checkbox;
		    width: auto;
		    outline: invert none medium;
		    padding: 0;
		    margin: 0;
		}
		@media (max-width: 700px) {
		    html div#om-' . $this->hash . '[style] {
		        position: absolute !important;
		    }
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin[style] {
		        width: 100%;
		        position: relative;
		        top: 30px !important;
		    }
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-wrap {
		        padding: 10px;
		    }
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-left,
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-right,
		    html div#om-' . $this->hash . ' input,
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-name,
            html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-email,
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-submit,
		    html div#om-' . $this->hash . ' .om-has-email #om-lightbox-' . $this->theme . '-optin-email {
		        float: none;
		        width: 100%;
		    }
		    html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-left {
		        margin-bottom: 15px;
		    }
		}
		';

		// Minify CSS a bit.
		$theme .= str_replace( array( "\n", "\t", "\r" ), '', $css );

		// If there is any custom CSS, append it now.
		if ( ! empty( $this->meta['custom_css'] ) )
		    $theme .= str_replace( array( "\n", "\t", "\r" ), '', $this->meta['custom_css'] );

        // Close out the styles.
        $theme .= '</style>';

		// Build out the HTML structure for the optin.
		$theme .= '<div id="om-lightbox-' . $this->theme . '-optin" class="om-lightbox-' . $this->theme . ' om-clearfix">';
		    $border_color = $this->get_field( 'background', 'border' );
			$theme .= '<div id="om-lightbox-' . $this->theme . '-optin-wrap" class="om-clearfix" style="' . ( ! empty( $border_color ) ? 'border-color: ' . $border_color : '' ) . '">';
				$theme .= '<a href="#" id="om-close" title="Close" style="' . ( ! empty( $border_color ) ? 'background-color: ' . $border_color : '' ) . '"></a>';

				// Content area.
				$theme .= '<div id="om-lightbox-' . $this->theme . '-content" class="om-clearfix">';
                    $theme .= '<div id="om-lightbox-' . $this->theme . '-optin-image-container">';
					     $url = get_the_post_thumbnail( $this->optin, 'full' );
					     $theme .= $url;
                    $theme .= '</div>';
				$theme .= '</div>';

				// Footer area.
                $bg = $this->get_field( 'background', 'footer' );
                $border = $this->get_field( 'background', 'footer_border' );
                $class = $this->get_field( 'name', 'show' ) ? ' om-has-name-email' : ' om-has-email';
				$theme .= '<div id="om-lightbox-' . $this->theme . '-footer" class="om-clearfix' . $class . '" style="' . ( ! empty( $bg ) ? 'background-color: rgba(' . $this->hex2rgb( $bg ) . ', .15);' : '' ) . ( ! empty( $border ) ? 'border-top-color: ' . $border . ';' : '' ) . '">';
				    if ( isset( $this->meta['email']['provider'] ) && 'custom' == $this->meta['email']['provider'] ) :
                        $theme .= html_entity_decode( $this->meta['custom_html'] );
				    else :
                        $show_name = $this->get_field( 'name', 'show' );
                        if ( $show_name ) {
						    $name = $this->get_field( 'name' );
	    				    $style = '';
	    				    if ( ! empty( $name['color'] ) )
	    				        $style .= 'color:' . $name['color'] . ';';
	    				    if ( ! empty( $name['font'] ) )
	    				        $style .= 'font-family:\'' . $name['font'] . '\', sans-serif;';
	    				    if ( ! empty( $name['size'] ) )
	    				        $style .= 'font-size:' . $name['size'] . 'px;';
	    				    if ( ! empty( $name['meta'] ) )
	    				        foreach ( (array) $name['meta'] as $prop => $val )
	    				            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
						    $theme .= '<input id="om-lightbox-' . $this->theme . '-optin-name" type="text" value="" placeholder="' . ( ! empty( $name['placeholder'] ) ? $name['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';
						}

					    $email = $this->get_field( 'email' );
    				    $style = '';
    				    if ( ! empty( $email['color'] ) )
    				        $style .= 'color:' . $email['color'] . ';';
    				    if ( ! empty( $email['font'] ) )
    				        $style .= 'font-family:\'' . $email['font'] . '\', sans-serif;';
    				    if ( ! empty( $email['size'] ) )
    				        $style .= 'font-size:' . $email['size'] . 'px;';
    				    if ( ! empty( $email['meta'] ) )
    				        foreach ( (array) $email['meta'] as $prop => $val )
    				            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
						$theme .= '<input id="om-lightbox-' . $this->theme . '-optin-email" type="email" value="" placeholder="' . ( ! empty( $email['placeholder'] ) ? $email['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';

                        $submit = $this->get_field( 'submit' );
    				    $style = '';
    				    if ( ! empty( $submit['field_color'] ) )
    				        $style .= 'color:' . $submit['field_color'] . ';';
    				    if ( ! empty( $submit['bg_color'] ) )
    				        $style .= 'background-color:' . $submit['bg_color'] . ';border-color:' . $submit['bg_color'] . ';';
    				    if ( ! empty( $submit['font'] ) )
    				        $style .= 'font-family:\'' . $submit['font'] . '\', sans-serif;';
    				    if ( ! empty( $submit['size'] ) )
    				        $style .= 'font-size:' . $submit['size'] . 'px;';
    				    if ( ! empty( $submit['meta'] ) )
    				        foreach ( (array) $submit['meta'] as $prop => $val )
    				            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
						$theme .= '<input id="om-lightbox-' . $this->theme . '-optin-submit" type="submit" value="' . ( ! empty( $submit['placeholder'] ) ? $submit['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';
                    endif;
				$theme .= '</div>';
			$theme .= '</div>';
		$theme .= '</div>';

		// Build out any necessary JS elements.
		$theme .= '<script type="text/javascript">';
			$theme .= 'function om_js_' . str_replace( '-', '_', $this->hash ) . '(){';
				$theme .= 'this.init = function($){this.resize_element($, "div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin");},';
				$theme .= 'this.resize_element = function($, el){';
					if ( 'customizer' == $this->env ) :
						$theme .= '$(el).css({ top: ($("#om-' . $this->hash . '").parent().height() - $(el).outerHeight()) / 2, left: ($("#om-' . $this->hash . '").parent().width() - $(el).outerWidth()) / 2 });';
						$theme .= '$(window).resize(function(){$(el).css({ top: ($("#om-' . $this->hash . '").parent().height() - $(el).outerHeight()) / 2, left: ($("#om-' . $this->hash . '").parent().width() - $(el).outerWidth()) / 2 });});';
						$theme .= '$(el).resize(function(){$(el).css({ top: ($("#om-' . $this->hash . '").parent().height() - $(el).outerHeight()) / 2, left: ($("#om-' . $this->hash . '").parent().width() - $(el).outerWidth()) / 2 });});';
						$theme .= '$("#om-lightbox-' . $this->theme . '-optin-name, #om-lightbox-' . $this->theme . '-optin-email").resize(function(){$("#om-lightbox-' . $this->theme . '-optin-submit").css({ "height": $(this).outerHeight() + "px", "line-height": ($(this).outerHeight()-10) + "px" });});';
						$theme .= '$("div#om-' . $this->hash . ' input[data-om-render=label]").each(function(){var new_el = $(this).changeElementType(\'label\');});';
						$theme .= '$("div#om-' . $this->hash . ' label[data-om-render=label]").each(function(){var new_el = $(this).text($(this).attr(\'value\')).removeAttr(\'type\');});';
						$theme .= '$("div#om-' . $this->hash . '").find("input").each(function(){$(this).attr("disabled", "disabled");});';
						$theme .= '$("div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin").fadeIn(300);';
					else :
			    		$theme .= '$(el).css({ top: ($(window).height() - $(el).outerHeight()) / 2, left: ($(window).width() - $(el).outerWidth()) / 2 });';
			    		$theme .= '$(window).resize(function(){$(el).css({ top: ($(window).height() - $(el).outerHeight()) / 2, left: ($(window).width() - $(el).outerWidth()) / 2 });});';
			    	endif;
				$theme .= '}';
			$theme .= '}';
		$theme .= '</script>';

		// Return the theme output and design.
		return $theme;

	}

	public function get_field( $field, $subfield = '' ) {

		if ( ! empty( $subfield ) )
			return isset( $this->meta[$field][$subfield] ) ? $this->meta[$field][$subfield] : '';
		else
			return isset( $this->meta[$field] ) ? $this->meta[$field] : '';

	}

	public function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);

        if(strlen($hex) == 3) {
           $r = hexdec(substr($hex,0,1).substr($hex,0,1));
           $g = hexdec(substr($hex,1,1).substr($hex,1,1));
           $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
           $r = hexdec(substr($hex,0,2));
           $g = hexdec(substr($hex,2,2));
           $b = hexdec(substr($hex,4,2));
        }
        $rgb = array($r, $g, $b);
        return implode(",", $rgb); // returns the rgb values separated by commas
    }

}