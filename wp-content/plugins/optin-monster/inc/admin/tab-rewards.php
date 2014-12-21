<?php
/**
 * "Rewards" tab class.
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
class optin_monster_tab_rewards {

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
        global $optin_monster_account, $wpdb;
        $this->base    = optin_monster::get_instance();
        $this->tab     = 'rewards';
        $this->account = $optin_monster_account;

        add_action( 'optin_monster_tab_' . $this->tab, array( $this, 'do_tab' ) );

    }

	/**
	 * Outputs the tab content.
	 *
	 * @since 1.0.0
	 */
	public function do_tab() {

	    // Load scripts.
	    wp_enqueue_script( 'jquery-ui-accordion' );

		// Load the rewards view.
		echo '<div class="optin-monster-rewards">';
		    echo '<div class="accordion-panel soliloquy om-clearfix">';
		        echo '<div class="panel-logo"></div>';
		        echo '<h3>Soliloquy</h3>';
		        echo '<p class="panel-desc">The best responsive WordPress slider plugin.</p>';
		    echo '</div>';
		    echo '<div class="panel-content">';
		        echo '<p>Get 20% off Soliloquy, the best responsive WordPress slider plugin on the market, by using the exclusive OptinMonster discount code <strong>OPTINMONSTER20</strong> when you check out.</p>';
		        echo '<a class="button button-primary button-large" href="http://soliloquywp.com/pricing/" title="Buy Soliloquy" target="_blank">Buy Soliloquy</a>';
		    echo '</div>';
		echo '</div>';

		add_action( 'admin_print_footer_scripts', array( $this, 'footer_scripts' ) );

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
        	    $('.optin-monster-rewards').accordion({
        	        active: false,
        	        collapsible: true,
            	    header: '.accordion-panel'
        	    });
    	    });
    	</script>
    	<?php

	}

}

// Initialize the class.
$optin_monster_tab_rewards = new optin_monster_tab_rewards();