<?php
/**
 * Plugin Name: Gravity Forms MyMail Add-On
 * Version: 0.3.1
 * Author: revaxarts.com
 * Author URI: http://revaxarts.com
 * Plugin URI: http://rxa.li/mymail
 * License: GPLv2
 * Description: Integrates MyMail Newsletter Plugin with Gravity Forms to subscribe users with a Gravity Form.
 * Requires the MyMail Newsletter plugin >= 2 and the Gravity Forms plugin
 */



class MyMailGravitiyForm {

	private $plugin_path;
	private $plugin_url;

	public function __construct(){	
	
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url = plugin_dir_url( __FILE__ );

		register_activation_hook( __FILE__, array(&$this, 'activate') );
		register_deactivation_hook( __FILE__, array(&$this, 'deactivate') );

		load_plugin_textdomain( 'plugin-name-locale', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		
		add_action( 'init', array( &$this, 'init' ) );


	}
	
	public function activate( $network_wide ){

	}
	
	public function deactivate( $network_wide ){
		
	}
	
	public function init(){
		
		add_filter("gform_after_submission", array( &$this, 'after_submission'), 10, 2);
		
		if(is_admin()){

			add_filter("gform_form_settings_menu", array( &$this, 'settings_menu'), 10, 2);
			add_action('gform_form_settings_page_mymail', array( &$this, 'settings_page'));

			if(isset($_POST['gform_save_settings'])){
				$this->save();
			}
		}
	}
	
	public function after_submission($entry, $form){

		//mymail options are defiend?
		if(!isset($form['mymail'])) return;

		//active?
		if(!isset($form['mymail']['active'])) return;

		//condition check matches?
		if(isset($form['mymail']['conditional'])){

			//radio button
			if(isset($form['mymail']['conditional_id'])){

				if(isset($entry[$form['mymail']['conditional_id']]) && ($entry[$form['mymail']['conditional_id']] != $form['mymail']['conditional_field'])) return;
				if(!isset($entry[$form['mymail']['conditional_id']])) return;

			//checkbox
			}else{

				if(isset($entry[$form['mymail']['conditional_field']]) && empty($entry[$form['mymail']['conditional_field']])) return;
				if(!isset($entry[$form['mymail']['conditional_field']])) return;

			}


		}

		$mymailforms = mymail_option('forms');
		$mymailform = isset($mymailforms[$form['mymail']['form_id']]) ? $mymailforms[$form['mymail']['form_id']] : $mymailforms[0];

		$userdata = array();
		foreach($form['mymail']['map'] as $field_id => $key){
			if($key == 'email'){
				$email = $entry[$field_id];
			}else if($key != -1){
				$userdata[$key] = $entry[$field_id];
			}
		}

		$lists = $form['mymail']['lists'];

		$double_opt_in = isset($form['mymail']['double-opt-in']);
		$overwrite = true;

		mymail_subscribe( $email, $userdata, $lists, $double_opt_in, $overwrite, $mergelists, $template );

	}
	

	public function page(){
	
		include $this->plugin_path.'/views/page.php';
		
	}
	
	public function settings_page(){
	
		GFFormSettings::page_header();

		include $this->plugin_path.'/views/page.php';
		
		GFFormSettings::page_footer();
		
	}
	
	public function settings_menu($settings_tabs, $form_id){

		$settings_tabs[] = array(
			'name' => 'mymail',
			'label' => 'MyMail',
		);
		return $settings_tabs;
	}

	public function save(){


		if ( !current_user_can( 'manage_options' ) ) wp_die( __('Cheatin&#8217; uh?') );
		
		if ( !isset( $_POST['gform_save_form_settings'] ) || !wp_verify_nonce( $_POST['gform_save_form_settings'], 'mymail_gf_save_form' )) wp_die( __('Cheatin&#8217; uh?') );

		$form_id = isset( $_GET['id'] ) ? intval($_GET['id']) : null;
		if ( !$form_id ) return;
		
		$form = RGFormsModel::get_form_meta( $form_id );
		if ( !$form ) return;

		$form['mymail'] = $_POST['mymail'];
		$conditional = explode('|', $form['mymail']['conditional_field']);
		
		if(count($conditional) > 1){
			$form['mymail']['conditional_id'] = array_shift($conditional);
		}

		$form['mymail']['conditional_field'] = implode('|',$conditional);

		RGFormsModel::update_form_meta( $form_id, $form );


	}

}
new MyMailGravitiyForm();

?>
