<?php
/**
 * "Misc" tab class.
 *
 * @package      OptinMonster
 * @since        1.0.0
 * @author       Thomas Griffin <thomas@retyp.com>
 * @copyright    Copyright (c) 2013, Thomas Griffin
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Loads miscellaneous tab.
 *
 * @package      OptinMonster
 * @since        1.0.0
 */
class optin_monster_tab_misc {

	/**
	 * Prepare any base class properties.
	 *
	 * @since 1.0.0
	 */
	public $base, $tab;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

        // Bring base class into scope.
        global $optin_monster_account, $wpdb, $optin_monster_license;
        $this->base    = optin_monster::get_instance();
        $this->tab     = 'misc';
        $this->account = $optin_monster_account;

        add_action( 'optin_monster_tab_' . $this->tab, array( $this, 'do_tab' ) );

    }

	/**
	 * Outputs the tab content.
	 *
	 * @since 1.0.0
	 */
	public function do_tab() {

	    if ( isset( $_POST['om-misc-settings'] ) ) {
	        $this->save_settings();
	        echo '<div class="alert alert-success"><p><strong>Settings saved.</strong></p></div>';
        }

        $this->license = get_option( 'optin_monster_license' );

		// Load the misc view.
		echo '<form id="om-misc-form" method="post">';
		    echo '<input type="hidden" name="om-misc-settings" value="1" />';
    		echo '<table class="form-table om-misc-settings">';
    		    echo '<tbody>';
    		        echo '<tr scope="row" valign="middle">';
    		            echo '<th scope="row"><label for="om-license-key">OptinMonster License Key</label></th>';
    		            echo '<td valign="middle">';
    		                echo '<input id="om-license-key" name="om-misc-key" type="text" value="' . ( ! empty( $this->license['key'] ) ? $this->license['key'] : '' ) . '" /> <a href="#" class="button button-primary validate-license" title="Validate License">Validate License</a> <a href="#" class="button button-secondary deactivate-license" title="Deactivate License">Deactivate License</a> <img style="display:none;margin-left:5px;vertical-align:middle;" class="loading" src="' . includes_url() . '/images/wpspin.gif" alt="Loading" />';
    		            echo '</td>';
    		        echo '</tr>';
    		        if ( optin_monster::get_instance()->is_reporting_active() ) :
    		        echo '<tr scope="row" valign="middle">';
    		            echo '<th scope="row"><label for="om-report-data">Clear Report Data Interval</label></th>';
    		            echo '<td valign="middle">';
    		                echo '<input id="om-report-data" name="om-misc-report" type="text" value="' . ( ! empty( $this->license['report'] ) ? $this->license['report'] : '' ) . '" />';
    		                echo '<p class="description"><em>Because OptinMonster stores reporting data for each optin impression, the table holding the data can grow rather quickly on high traffic sites. Although the reporting table is very optimized, it is recommended that you clear your reporting data at regular intervals to prevent your database from growing abnormally large. The default is every 30 days. Set this to 0 if you do not want to purge reporting data.</em></p>';
    		            echo '</td>';
    		        echo '</tr>';
    		        endif;
    		        echo '<tr scope="row" valign="middle">';
    		            echo '<th scope="row"><label for="om-global-cookie">OptinMonster Global Cookie</label></th>';
    		            echo '<td valign="middle">';
    		                echo '<input id="om-global-cookie" name="om-global-cookie" type="text" value="' . ( ! empty( $this->license['global_cookie'] ) ? $this->license['global_cookie'] : 0 ) . '" />';
    		                echo '<p class="description"><em>Entering a number (e.g. 30) will set a global cookie once any optin has resulted in a successful conversion. This global cookie will prevent any other optins from loading on your site for that visitor until the cookie expires. Defaults to 0 (no global cookie).</em></p>';
    		            echo '</td>';
    		        echo '</tr>';
    		    echo '</tbody>';
    		echo '</table>';
    		echo '<p class="submit"><input class="button button-primary button-large" type="submit" name="om-misc-submit" value="Save Misc Settings" /></p>';
		echo '</form>';


		add_action( 'admin_print_footer_scripts', array( $this, 'footer_scripts' ) );

	}

	public function save_settings() {

        $option = get_option( 'optin_monster_license' );
    	if ( isset( $_POST['om-misc-report'] ) ) {
    	    $report_interval = (int) $_POST['om-misc-report'];
        	if ( 0 == $report_interval )
        	    wp_clear_scheduled_hook( 'optin_monster_clear_reporting' );

            if ( ! is_int( $report_interval ) )
                $report_interval = 30;

            $option['report'] = (int) $report_interval;
            update_option( 'optin_monster_license', $option );
    	}

    	if ( isset( $_POST['om-misc-key'] ) )
    	    $option['key'] = $_POST['om-misc-key'];

        if ( isset( $_POST['om-global-cookie'] ) )
    	    $option['global_cookie'] = (int) $_POST['om-global-cookie'];

        update_option( 'optin_monster_license', $option );

    	do_action( 'optin_monster_save_misc_settings', $_POST );

	}

    /**
	 * Outputs footer scripts for the tab.
	 *
	 * @since 1.0.0
	 */
	public function footer_scripts() {

    	?>
    	<script type="text/javascript">
    	    jQuery(document).ready(function($){
        	    $('.validate-license').on('click', function(e){
        	        e.preventDefault();
        	        var $this = $(this),
        	            text  = $this.text(),
        	            license = $('#om-license-key').val();
                    $('.alert').remove();
                    $this.text('Validating...');
                    $('.loading').show();
                    $.post(ajaxurl, { action: 'om_verify_license', license: license }, function(resp){
                        if ( resp && resp.error ) {
                            $('.om-misc-settings').before('<div class="alert alert-error" style="margin-bottom:0;"><p><strong>' + resp.error + '</strong></p></div>');
                            $('.loading').hide();
                            $this.text(text);
                        } else {
                            $('.om-misc-settings').before('<div class="alert alert-success" style="margin-bottom:0;"><p><strong>' + resp.success + '</strong></p></div>');
                            $('.loading').hide();
                            $this.text(text);
                        }
                    }, 'json');
        	    });
        	    $('.deactivate-license').on('click', function(e){
        	        e.preventDefault();
        	        var $this = $(this),
        	            text  = $this.text(),
        	            license = $('#om-license-key').val();
                    $('.alert').remove();
                    $this.text('Deactivating...');
                    $('.loading').show();
                    $.post(ajaxurl, { action: 'om_deactivate_license', license: license }, function(resp){
                        if ( resp && resp.error ) {
                            $('.om-misc-settings').before('<div class="alert alert-error" style="margin-bottom:0;"><p><strong>' + resp.error + '</strong></p></div>');
                            $('.loading').hide();
                            $this.text(text);
                        } else {
                            $('.om-misc-settings').before('<div class="alert alert-success" style="margin-bottom:0;"><p><strong>' + resp.success + '</strong></p></div>');
                            $('.loading').hide();
                            $this.text(text);
                            $('#om-license-key').val('');
                        }
                    }, 'json');
        	    });
    	    });
    	</script>
    	<?php

	}

}

// Initialize the class.
$optin_monster_tab_misc = new optin_monster_tab_misc();