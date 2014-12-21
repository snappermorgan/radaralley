<?php
/**
 * Builds out the ligthbox case-study theme.
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
class optin_monster_build_lightbox_case_study_theme {

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
			border:0;
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
			width: 700px;
			z-index: ' . ( 'customizer' == $this->env ? 1 : 734626274 ) . ';
            -moz-box-shadow: inset 3px 4px 250px #d4d4d4;
            -webkit-box-shadow: inset 3px 4px 250px #d4d4d4;
            box-shadow: inset 3px 4px 250px #d4d4d4;

		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-wrap {
			position: relative;
			height: 100%;
		}
		html div#om-' . $this->hash . ' #om-close {
			position: absolute;
			top: -12px;
			right: -12px;
			text-decoration: none !important;
			display: block;
			width: 35px;
			height: 35px;
			background: url(' . plugins_url( 'inc/css/images/case-study-close.png', $this->base->file ) . ') no-repeat scroll 0 0;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-bar {
		    margin-bottom: 20px;
		    clear: both;
		}
		html div#om-' . $this->hash . ' .om-bar {
		    float: left;
			width: 25%;
			height: 11px;
			display: block;
		}
		html div#om-' . $this->hash . ' .om-red-bar {
			background-color: #ef3e36;
		}
		html div#om-' . $this->hash . ' .om-green-bar {
			background-color: #abb92e;
		}
		html div#om-' . $this->hash . ' .om-orange-bar {
			background-color: #f57826;
		}
		html div#om-' . $this->hash . ' .om-blue-bar {
			background-color: #17b4e9;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-header {
			min-height: 30px;
			padding: 15px;
			width: 100%;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-title {
			font-size: 24px;
			color: #484848;
			width: 100%;
			text-align: center;
			margin-bottom: 15px;
			text-shadow: 0 1px #fff;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-title span,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-title strong,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-title em,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-title u {
			font-family: inherit;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-tagline {
			font-size: 16px;
			line-height: 1.25;
			color: #484848;
			width: 100%;
			margin-bottom: 30px;
			text-align: center;
			text-shadow: 0 1px #fff;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-tagline span,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-tagline strong,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-tagline em,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-tagline u {
			font-family: inherit;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-content {
			padding: 15px;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-content-clear {
			min-height: 175px;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-left {
			float: left;
			width: 280px;
			position: relative;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-image-container {
			position: relative;
			width: 280px;
			height: 245px;
			margin: 0 auto;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-image-container img {
			display: block;
			margin: 0 auto;
			text-align: center;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-right {
			float: right;
			width: 370px;
			background-color: #dadada;
			margin-right: -15px;
			padding: 20px;
			position: relative;
		}
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-arrow {
			position: absolute;
			width: 49px;
			height: 56px;
			left: -40px;
			top: -40px;
		}
		html div#om-' . $this->hash . ' label {
		    color: #333;
		}
		html div#om-' . $this->hash . ' input,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-name,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-email {
			background-color: #fff;
			width: 100%;
			border: 1px solid #ccc;
			font-size: 16px;
			line-height: 24px;
			padding: 10px 6px;
			overflow: hidden;
			outline: none;
			margin: 0 0 15px;
			vertical-align: middle;
		}
		html div#om-' . $this->hash . ' input[type=submit],
		html div#om-' . $this->hash . ' button,
		html div#om-' . $this->hash . ' #om-lightbox-' . $this->theme . '-optin-submit {
			background: #484848;
			border: 1px solid #484848;
			width: 100%;
			color: #fff;
			font-size: 16px;
			padding: 10px 6px;
			line-height: 24px;
			height: ' . ( isset( $this->meta['email']['provider'] ) && 'custom' == $this->meta['email']['provider'] ? '46px' : '24px' ) . ';
			text-align: center;
			vertical-align: middle;
			cursor: pointer;
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
			$theme .= '<div id="om-lightbox-' . $this->theme . '-optin-wrap" class="om-clearfix">';
			    $theme .= '<a href="#" id="om-close" title="Close"></a>';

			    $theme .= '<div id="om-lightbox-' . $this->theme . '-optin-bar" class="om-clearfix">';
    			    $theme .= '<span class="om-bar om-red-bar"></span>';
    			    $theme .= '<span class="om-bar om-green-bar"></span>';
    			    $theme .= '<span class="om-bar om-orange-bar"></span>';
    			    $theme .= '<span class="om-bar om-blue-bar"></span>';
                $theme .= '</div>';

				// Header area.
				$theme .= '<div id="om-lightbox-' . $this->theme . '-header" class="om-clearfix">';
				    $title = $this->get_field( 'title' );
				    $style = '';
				    if ( ! empty( $title['color'] ) )
				        $style .= 'color:' . $title['color'] . ';';
				    if ( ! empty( $title['font'] ) )
				        $style .= 'font-family:\'' . $title['font'] . '\', sans-serif;';
				    if ( ! empty( $title['size'] ) )
				        $style .= 'font-size:' . $title['size'] . 'px;';
				    if ( ! empty( $title['meta'] ) )
				        foreach ( (array) $title['meta'] as $prop => $val )
				            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
					$theme .= '<h1 id="om-lightbox-' . $this->theme . '-optin-title" class="om-clearfix" style="' . ( ! empty( $style ) ? $style : '' ) . '">' . ( ! empty( $title['text'] ) ? $title['text'] : '' ) . '</h1>';

					$tagline = $this->get_field( 'tagline' );
				    $style = '';
				    if ( ! empty( $tagline['color'] ) )
				        $style .= 'color:' . $tagline['color'] . ';';
				    if ( ! empty( $tagline['font'] ) )
				        $style .= 'font-family:\'' . $tagline['font'] . '\', sans-serif;';
				    if ( ! empty( $tagline['size'] ) )
				        $style .= 'font-size:' . $tagline['size'] . 'px;';
				    if ( ! empty( $tagline['meta'] ) )
				        foreach ( (array) $tagline['meta'] as $prop => $val )
				            $style .= str_replace( '_', '-', $prop ) . ':' . $val . ';';
					$theme .= '<h2 id="om-lightbox-' . $this->theme . '-optin-tagline" class="om-clearfix" style="' . ( ! empty( $style ) ? $style : '' ) . '">' . ( ! empty( $tagline['text'] ) ? $tagline['text'] : '' ) . '</h2>';
				$theme .= '</div>';

				// Content area.
				$theme .= '<div id="om-lightbox-' . $this->theme . '-content" class="om-clearfix">';
					$theme .= '<div id="om-lightbox-' . $this->theme . '-content-clear">';
						$theme .= '<div id="om-lightbox-' . $this->theme . '-left">';
						    $theme .= '<div id="om-lightbox-' . $this->theme . '-optin-image-container">';
							     $url = get_the_post_thumbnail( $this->optin, 'full' );
							     $theme .= $url;
                            $theme .= '</div>';
						$theme .= '</div>';
						$class = $this->get_field( 'name', 'show' ) ? 'om-has-name-email' : 'om-has-email';
						$theme .= '<div id="om-lightbox-' . $this->theme . '-right" class="' . $class . '">';
						    $theme .= '<img id="om-lightbox-' . $this->theme . '-arrow" src="' . plugins_url( 'inc/css/images/case-study-arrow.png', $this->base->file ) . '" alt="Arrow" />';
						    if ( isset( $this->meta['email']['provider'] ) && 'custom' == $this->meta['email']['provider'] ) :
                                $theme .= html_entity_decode( $this->meta['custom_html'] );
        				    else :
                                $disabled = 'customizer' == $this->env ? ' disabled="disabled"' : '';
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
        						    $theme .= '<input' . $disabled . ' id="om-lightbox-' . $this->theme . '-optin-name" type="text" value="" placeholder="' . ( ! empty( $name['placeholder'] ) ? $name['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';
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
        						$theme .= '<input' . $disabled . ' id="om-lightbox-' . $this->theme . '-optin-email" type="email" value="" placeholder="' . ( ! empty( $email['placeholder'] ) ? $email['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';

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
        						$theme .= '<input' . $disabled . ' id="om-lightbox-' . $this->theme . '-optin-submit" type="submit" value="' . ( ! empty( $submit['placeholder'] ) ? $submit['placeholder'] : '' ) . '" style="' . ( ! empty( $style ) ? $style : '' ) . '" />';
                            endif;
						$theme .= '</div>';
					$theme .= '</div>';
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
						$theme .= '$("#om-lightbox-' . $this->theme . '-optin-name, #om-lightbox-' . $this->theme . '-optin-email").resize(function(){$("#om-lightbox-' . $this->theme . '-optin-submit").css({ "height": ($(this).outerHeight()+12) + "px", "line-height": ($(this).outerHeight()-10) + "px" });});';
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

}