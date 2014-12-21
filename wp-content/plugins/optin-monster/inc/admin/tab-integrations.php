<?php
/**
 * "Integrations" tab class.
 *
 * @package      OptinMonster
 * @since        1.0.0
 * @author       Thomas Griffin <thomas@retyp.com>
 * @copyright    Copyright (c) 2013, Thomas Griffin
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Loads reports.
 *
 * @package      OptinMonster
 * @since        1.0.0
 */
class optin_monster_tab_integrations {

	/**
	 * Prepare any base class properties.
	 *
	 * @since 1.0.0
	 */
	public $base, $user, $host, $protocol, $url, $db, $optin, $tab;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

        // Bring base class into scope.
        $this->base    = optin_monster::get_instance();
        $this->user    = wp_get_current_user();
        $this->tab     = 'integrations';

        add_action( 'optin_monster_tab_' . $this->tab, array( $this, 'do_tab' ) );

    }

	/**
	 * Outputs the tab content.
	 *
	 * @since 1.0.0
	 */
	public function do_tab() {

	    $providers = get_option( 'optin_monster_providers' );

	    if ( empty( $providers ) ) {
    	    echo '<div class="alert alert-success"><p><strong>You have not created any email service integrations yet.</strong></p></div>';
	    } else {
	        echo '<div class="alert alert-success"><p><strong>Your email service integrations are listed below and can be managed from this screen.</strong></p></div>';
    	    echo '<div class="optin-integrations om-clearfix">';
    	        $i = 1;
    	        foreach ( (array) $providers as $provider => $array ) {
    	            if ( 1 == $i || 1 == $i%3 )
    	                echo '<div class="optin-integration-wrap om-clearfix">';

    	            echo '<div class="optin-integration ' . $provider . '">';
            	        echo '<div class="logo"></div>';
                        echo '<ul class="integration om-clearfix">';
                        if ( empty( $array ) ) {
                            echo '<li><span class="name">No accounts registered.</span></li>';
                        } else {
            	            foreach ( $array as $hash => $data ) {
                                echo '<li><span class="name">' . $data['label'] . '</span> <a class="button button-secondary button-small delete-integration" href="#" title="Delete Integration" data-provider="' . $provider . '" data-hash="' . $hash . '">Delete</a></li>';
                    	    }
                        }
            	        echo '</ul>';
            	    echo '</div>';

            	    if ( 0 == $i%3 || $i == count( $providers ) )
            	        echo '</div>';
            	    $i++;
    	        }
    	    echo '</div>';
	    }

	    add_action( 'admin_print_footer_scripts', array( $this, 'footer_scripts' ) );

	}

	public function footer_scripts() {

    	?>
    	<script type="text/javascript">
    	    jQuery(document).ready(function($){
        	    $('.delete-integration').on('click', function(e){
        	        var $this = $(this),
        	            delete_integration = confirm('Are you sure you want to delete this integration?');
        	        if ( ! delete_integration ) return;
        	        e.preventDefault();
        	        $.post(ajaxurl, { action: 'delete_integration', provider: $this.data('provider'), hash: $this.data('hash') }, function(resp){
            	        $this.parent().fadeOut(300);
        	        }, 'json');
        	    });
    	    });
    	</script>
    	<?php

	}

}

// Initialize the class.
$optin_monster_tab_integrations = new optin_monster_tab_integrations();