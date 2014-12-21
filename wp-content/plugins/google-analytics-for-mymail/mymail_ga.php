<?php
/**
 * Plugin Name: Google Analytics for MyMail
 * Version: 0.3.2
 * Author: revaxarts.com
 * Author URI: http://revaxarts.com
 * Plugin URI: http://rxa.li/mymail?utm_source=Google+Analytics+for+MyMail
 * License: GPLv2
 * Description: Integrates Google Analytics with MyMail Newsletter Plugin to track your clicks with the popular Analytics service
 */


class MyMailGoogleAnalytics {

	private $plugin_path;
	private $plugin_url;

	public function __construct(){

		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url = plugin_dir_url( __FILE__ );

		register_activation_hook( __FILE__, array( &$this, 'activate') );
		
		load_plugin_textdomain( 'mymail_ga', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		
		add_action( 'init', array( &$this, 'init'), 1 );
	}


	/**
	 * init function.
	 * 
	 * @access public
	 * @return void
	 */
	public function init() {

		
		if(!defined('MYMAIL_VERSION') || !MYMAIL_VERSION){
			
			add_action('admin_notices', array( &$this, 'notice'));
			return;
		
		}
		
		if(is_admin()){
			
			add_action( 'add_meta_boxes',array( &$this, 'add_meta_boxes') );
				
			add_filter( 'mymail_setting_sections', array( &$this, 'settings_tab' ), 1 );
				
			add_action( 'mymail_section_tab_ga',array( &$this, 'settings') );
			
			add_action('save_post', array( &$this, 'save_post'), 10, 2);
		}
		
		add_action( 'mymail_wpfooter',array( &$this, 'wpfooter'));
		add_filter( 'mymail_redirect_to', array( &$this, 'redirect_to' ), 1, 2);
			
	}

	/**
	 * click_target function.
	 * 
	 * @access public
	 * @param mixed $target
	 * @return void
	 */
	public function redirect_to( $target, $campaign_id ){

		if(strpos($target, site_url()) === false) return $target;
		
		global $wp;

		$hash = isset($wp->query_vars['_mymail_hash'])
			? $wp->query_vars['_mymail_hash']
			: (isset($_REQUEST['k']) ? preg_replace('/\s+/', '', $_REQUEST['k']) : NULL);
		$count = isset($wp->query_vars['_mymail_extra'])
			? $wp->query_vars['_mymail_extra']
			: (isset($_REQUEST['c']) ? intval($_REQUEST['c']) : NULL);


		$subscriber = mymail('subscribers')->get_by_hash($hash);
		$campaign = mymail('campaigns')->get($campaign_id);

		if(!$campaign || $campaign->post_type != 'newsletter') return $target;
		
		$search = array('%%CAMP_ID%%', '%%CAMP_TITLE%%', '%%CAMP_TYPE%%', '%%CAMP_LINK%%', '%%SUBSCRIBER_ID%%', '%%SUBSCRIBER_EMAIL%%', '%%SUBSCRIBER_HASH%%', '%%LINK%%');
		$replace = array(
			$campaign->ID,
			$campaign->post_title,
			$campaign->post_status == 'autoresponder' ? 'autoresponder' : 'regular',
			get_permalink($campaign->ID),
			$subscriber->ID,
			$subscriber->email,
			$subscriber->hash,
			$target,
		);
		
		$values = wp_parse_args(get_post_meta($campaign->ID, 'mymail-ga', true), mymail_option('ga', array(
			'utm_source' => 'newslettera',
			'utm_medium' => 'email',
			'utm_term' => '%%LINK%%',
			'utm_content' => '',
			'utm_campaign' => '%%CAMP_TITLE%%',
		)));

		return add_query_arg(array(
			'utm_source' => urlencode(str_replace($search, $replace, $values['utm_source'])),
			'utm_medium' => urlencode(str_replace($search, $replace, $values['utm_medium'])),
			'utm_term' => urlencode(str_replace($search, $replace, $values['utm_term'])),
			'utm_content' => urlencode(str_replace($search, $replace, $values['utm_content'])),
			'utm_campaign' => urlencode(str_replace($search, $replace, $values['utm_campaign'])),
		), $target);
	}



	/**
	 * save_post function.
	 * 
	 * @access public
	 * @param mixed $post_id
	 * @param mixed $post
	 * @return void
	 */
	public function save_post($post_id, $post) {

		if (isset($_POST['mymail_ga']) && $post->post_type == 'newsletter') {
		
			$save = get_post_meta($post_id, 'mymail-ga', true);
			
			$ga_values = mymail_option('ga', array(
				'utm_source' => 'newsletter',
				'utm_medium' => 'email',
				'utm_term' => '%%LINK%%',
				'utm_content' => '',
				'utm_campaign' => '%%CAMP_TITLE%%',
			));
			
			$save = wp_parse_args($_POST['mymail_ga'], $save);
			update_post_meta($post_id, 'mymail-ga', $save);
			
		}

	}


	/**
	 * settings_tab function.
	 * 
	 * @access public
	 * @param mixed $settings
	 * @return void
	 */
	public function settings_tab($settings) {
		
		$position = 11;
		$settings = array_slice($settings, 0, $position, true) +
					array('ga' => 'Google Analytics') +
					array_slice($settings, $position, NULL, true);
		
		return $settings; 
	}


	/**
	 * add_meta_boxes function.
	 * 
	 * @access public
	 * @return void
	 */
	public function add_meta_boxes() {

		global $post;
		
		if(mymail_option('ga_campaign_based')) add_meta_box('mymail_ga', 'Google Analytics', 'mymail_ga_metabox', 'newsletter', 'side', 'low');
	}


	/**
	 * metabox function.
	 * 
	 * @access public
	 * @return void
	 */
	public function metabox(){

		global $post;
		
		$readonly = (in_array($post->post_status, array('finished', 'active')) || $post->post_status == 'autoresponder' && !empty($_GET['showstats'])) ? 'readonly disabled' : '';

		$values = wp_parse_args(get_post_meta($post->ID, 'mymail-ga', true), mymail_option('ga', array(
			'utm_source' => 'newslettera',
			'utm_medium' => 'email',
			'utm_term' => '%%LINK%%',
			'utm_content' => '',
			'utm_campaign' => '%%CAMP_TITLE%%',
		)));
		
		?>
	<style>#mymail_ga {display: inherit;}</style>
		<p><label><?php _e('Campaign Source', 'mymail_ga'); ?>*:<input type="text" name="mymail_ga[utm_source]" value="<?php echo esc_attr($values['utm_source']); ?>" class="widefat" <?php echo $readonly ?>></label></p>
		<p><label><?php _e('Campaign Medium', 'mymail_ga'); ?>*:<input type="text" name="mymail_ga[utm_medium]" value="<?php echo esc_attr($values['utm_medium']); ?>" class="widefat" <?php echo $readonly ?>></label></p>
		<p><label><?php _e('Campaign Term', 'mymail_ga'); ?>:<input type="text" name="mymail_ga[utm_term]" value="<?php echo esc_attr($values['utm_term']); ?>" class="widefat" <?php echo $readonly ?>></label></p>
		<p><label><?php _e('Campaign Content', 'mymail_ga'); ?>:<input type="text" name="mymail_ga[utm_content]" value="<?php echo esc_attr($values['utm_content']); ?>" class="widefat" <?php echo $readonly ?>></label></p>
		<p><label><?php _e('Campaign Name', 'mymail_ga'); ?>*: <input type="text" name="mymail_ga[utm_campaign]" value="<?php echo esc_attr($values['utm_campaign']); ?>" class="widefat" <?php echo $readonly ?>></label></p>
		<?php
	}

	public function settings(){

	?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
			
				var inputs = $('.mymail-ga-value');
				
				inputs.on('keyup change', function(){
					var pairs = [];
					$.each(inputs, function(){
						var el = $(this),
							key = el.attr('name').replace('mymail_options[ga][','').replace(']', '');
						if(el.val())pairs.push(key+'='+encodeURIComponent(el.val().replace(/%%([A-Z_]+)%%/g, '$1')));
					});
					$('#mymail-ga-preview').html('?'+pairs.join('&'));
					
				}).trigger('keyup');
				
				
			});
		</script>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Web Property ID:' ,'mymail_ga') ?></th>
			<td>
			<p class="description"><input type="text" name="mymail_options[ga_id]" value="<?php echo esc_attr(mymail_option('ga_id')) ?>" class="regular-text" placeholder="UA-XXXXXXX-X">
			<?php _e('for the front end page of each newsletter' ,'mymail_ga'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"></th>
			<td><p><a href="https://support.google.com/analytics/answer/1037445" class="external"><?php _e('read "Best Practices for creating Custom Campaigns"' ,'mymail_ga'); ?></a></p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('SetDomainName:' ,'mymail_ga') ?></th>
			<td>
			<p><input type="text" name="mymail_options[ga_setdomainname]" value="<?php echo esc_attr(mymail_option('ga_setdomainname')) ?>" class="regular-text" placeholder="example.com"> <span class="description"><?php echo sprintf(__('(Optional) Sets the %s variable.' ,'mymail_ga'),'<code>_setDomainName</code>'); ?></span></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Defaults' ,'mymail_ga') ?><p class="description"><?php _e('Define the defaults for click tracking. Keep the default values until you know better.' ,'mymail_ga'); ?></p></th>
			<td>
			<?php
			$ga_values = mymail_option('ga', array(
				'utm_source' => 'newsletter',
				'utm_medium' => 'email',
				'utm_term' => '%%LINK%%',
				'utm_content' => '',
				'utm_campaign' => '%%CAMP_TITLE%%',
			));
			?>
			<div class="mymail_text"><label><?php _e('Campaign Source', 'mymail_ga'); ?> *:</label> <input type="text" name="mymail_options[ga][utm_source]" value="<?php echo esc_attr($ga_values['utm_source']); ?>" class="mymail-ga-value regular-text"></div>
			<div class="mymail_text"><label><?php _e('Campaign Medium', 'mymail_ga'); ?> *:</label> <input type="text" name="mymail_options[ga][utm_medium]" value="<?php echo esc_attr($ga_values['utm_medium']); ?>" class="mymail-ga-value regular-text"></div>
			<div class="mymail_text"><label><?php _e('Campaign Term', 'mymail_ga'); ?>:</label> <input type="text" name="mymail_options[ga][utm_term]" value="<?php echo esc_attr($ga_values['utm_term']); ?>" class="mymail-ga-value regular-text"></div>
			<div class="mymail_text"><label><?php _e('Campaign Content', 'mymail_ga'); ?>:</label> <input type="text" name="mymail_options[ga][utm_content]" value="<?php echo esc_attr($ga_values['utm_content']); ?>" class="mymail-ga-value regular-text"></div>
			<div class="mymail_text"><label><?php _e('Campaign Name', 'mymail_ga'); ?> *:</label> <input type="text" name="mymail_options[ga][utm_campaign]" value="<?php echo esc_attr($ga_values['utm_campaign']); ?>" class="mymail-ga-value regular-text"></div>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Example URL', 'mymail_ga'); ?></th>
			<td><code style="max-width:800px;white-space:normal;word-wrap:break-word;display:block;"><?php echo site_url('/') ?><span id="mymail-ga-preview"></span></code></td>
		</tr>
		<tr valign="top">
			<th scope="row"></th>
			<td><p class="description"><?php _e('Available variables:' ,'mymail_ga'); ?><br>%%CAMP_ID%%, %%CAMP_TITLE%%, %%CAMP_TYPE%%, %%CAMP_LINK%%,<br>%%SUBSCRIBER_ID%%, %%SUBSCRIBER_EMAIL%%, %%SUBSCRIBER_HASH%%,<br>%%LINK%%</p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Campaign based value', 'mymail_ga'); ?></th>
			<td><label><input type="checkbox" name="mymail_options[ga_campaign_based]" <?php checked(mymail_option('ga_campaign_based'))?> value="1"> <?php _e('allow campaign based variations of these values', 'mymail_ga'); ?></label><p class="description"><?php _e('adds a metabox on the campaign edit screen to alter the values for each campaign', 'mymail_ga'); ?></p></td>
		</tr>

	</table>
		<?php
	}



	/**
	 * notice function.
	 * 
	 * @access public
	 * @return void
	 */
	public function notice( ) {
		$msg = sprintf(__('You have to enable the %s to use the Google Analytics Extension!', 'mymail_ga'), '<a href="http://rxa.li/mymail?utm_source=Google+Analytics+for+MyMail">MyMail Newsletter Plugin</a>');
	?>
		<div class="error"><p><strong><?php	echo $msg; ?></strong></p></div>
	<?php

	}


	/**
	 * wpfooter function.
	 * 
	 * @access public
	 * @return void
	 */
	public function wpfooter( ) {
		
		$ua = mymail_option('ga_id');
		$setDomainName = mymail_option('ga_setdomainname');
		
		if(!$ua) return;
	?>

	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', '<?php echo $ua ?>']);
		<?php if($setDomainName) echo "_gaq.push(['_setDomainName', '$setDomainName']);"; ?>
		
		_gaq.push(['_trackPageview']);
		(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
	</script>
	<?php
			
	}

	/**
	 * activate function.
	 * 
	 * activate function
	 * @access public
	 * @return void
	 */
	public function activate() {

		if(!mymail_option('ga_id')) mymail_notice(sprintf(__('Please enter your Web Property ID on the %s!', 'mymail_ga'), '<a href="options-general.php?page=newsletter-settings&mymail_remove_notice=mymail_google_analytics#ga">Settings Page</a>'), '', false, 'google_analytics');
		
	}


}

new MyMailGoogleAnalytics();
