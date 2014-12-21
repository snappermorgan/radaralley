<?php
/*
Plugin Name: Ultimate Addons for Visual Composer (shared on wplocker.com)
Plugin URI: https://brainstormforce.com/demos/ultimate/
Author: Brainstorm Force
Author URI: https://www.brainstormforce.com
Version: 3.4.2
Description: Includes Visual Composer premium addon elements like Icon, Info Box, Interactive Banner, Flip Box, Info List & Counter. Best of all - provides A Font Icon Manager allowing users to upload / delete custom icon fonts. 
Text Domain: smile
*/
if(!defined('__ULTIMATE_ROOT__')){
	define('__ULTIMATE_ROOT__', dirname(__FILE__));
}
if(!defined('ULTIMATE_VERSION')){
	define('ULTIMATE_VERSION', '3.4.2');
}
if(!class_exists('Ultimate_VC_Addons'))
{
	add_action('admin_init','init_addons');
	function init_addons()
	{
		$required_vc = '3.7';
		if(defined('WPB_VC_VERSION')){
			if( version_compare( $required_vc, WPB_VC_VERSION, '>' )){
				add_action( 'admin_notices', 'admin_notice_for_version');
			}
		} else {
			//add_action( 'admin_notices', 'admin_notice_for_vc_activation');
		}
	}// end init_addons
	function admin_notice_for_version()
	{
		echo '<div class="updated"><p>The <strong>Ultimate addons for Visual Composer</strong> plugin requires <strong>Visual Composer</strong> version 3.7.2 or greater.</p></div>';	
	}
	function admin_notice_for_vc_activation()
	{
		echo '<div class="updated"><p>The <strong>Ultimate addons for Visual Composer</strong> plugin requires <strong>Visual Composer</strong> Plugin installed and activated.</p></div>';
	}
	// plugin class
	class Ultimate_VC_Addons
	{
		var $paths = array();
		var $module_dir;
		var $assets_js;
		var $assets_css;
		var $admin_js;
		var $admin_css;
		var $vc_template_dir;
		var $vc_dest_dir;
		function __construct()
		{
			//add_action( 'init', array($this,'init_addons'));
			$this->vc_template_dir = plugin_dir_path( __FILE__ ).'vc_templates/';
			$this->vc_dest_dir = get_template_directory().'/vc_templates/';
			$this->module_dir = plugin_dir_path( __FILE__ ).'modules/';
			$this->assets_js = plugins_url('assets/js/',__FILE__);
			$this->assets_css = plugins_url('assets/css/',__FILE__);
			$this->admin_js = plugins_url('admin/js/',__FILE__);
			$this->admin_css = plugins_url('admin/css/',__FILE__);
			$this->paths = wp_upload_dir();
			$this->paths['fonts'] 	= 'smile_fonts';
			$this->paths['fonturl'] = set_url_scheme(trailingslashit($this->paths['baseurl']).$this->paths['fonts']);
			add_action('init',array($this,'aio_init'));
			add_action('admin_enqueue_scripts',array($this,'aio_admin_scripts'));
			add_action('wp_enqueue_scripts',array($this,'aio_front_scripts'));
			add_action('admin_init',array($this,'toggle_updater'));
			if(!get_option('ultimate_row')){
				update_option('ultimate_row','enable');
			}
			//add_action('admin_init', array($this, 'aio_move_templates'));
		}// end constructor
		function aio_init()
		{
			// activate addons one by one from modules directory
			$ultimate_modules = get_option('ultimate_modules');
			$ultimate_modules[] = 'ultimate_just_icon';
			$ultimate_modules[] = 'ultimate_functions';
			$ultimate_modules[] = 'ultimate_icon_manager';
			$ultimate_modules[] = 'ultimate_font_manager';
			$ultimate_modules[] = 'ultimate_list_icon';

			if(get_option('ultimate_row') == "enable")
				$ultimate_modules[] = 'ultimate_parallax';

			foreach(glob($this->module_dir."/*.php") as $module)
			{
				$ultimate_file = basename($module);
				$ultimate_fileName = preg_replace('/\\.[^.\\s]{3,4}$/', '', $ultimate_file);
				
				if(is_array($ultimate_modules) && !empty($ultimate_modules)){ 
					if(in_array(strtolower($ultimate_fileName),$ultimate_modules) ){
						require_once($module);
					}
				}
			}
			if(in_array("woocomposer",$ultimate_modules) ){
				if(defined('WOOCOMMERCE_VERSION'))
				{
					if(version_compare( '2.1.0', WOOCOMMERCE_VERSION, '<' )) {
						foreach(glob(plugin_dir_path( __FILE__ ).'woocomposer/modules/*.php') as $module)
						{
							require_once($module);
						}
					} else {
						//add_action( 'admin_notices', array($this, 'woocomposer_admin_notice_for_woocommerce'));
					}
				} else {
					//add_action( 'admin_notices', array($this, 'woocomposer_admin_notice_for_woocommerce'));
				}
			}
		}// end aio_init
		function woocomposer_admin_notice_for_woocommerce(){
			echo '<div class="error"><p>The <strong>WooComposer </strong> plugin requires <strong>WooCommerce</strong> plugin installed and activated with version greater than 2.1.0.</p></div>';	
		}
		function aio_admin_scripts($hook)
		{
			// enqueue css files on backend'
			if($hook == "post.php" || $hook == "post-new.php"){
				wp_enqueue_style('aio-icon-manager',$this->admin_css.'icon-manager.css');
				wp_enqueue_style('ult-animate',$this->assets_css.'animate.css');
				wp_enqueue_script('vc-inline-editor',$this->assets_js.'vc-inline-editor.js',array('vc_inline_custom_view_js'),'1.5',true);
				$fonts = get_option('smile_fonts');
				if(is_array($fonts))
				{
					foreach($fonts as $font => $info)
					{
						if(strpos($info['style'], 'http://' ) !== false) {
							wp_enqueue_style('bsf-'.$font,$info['style']);
						} else {
							wp_enqueue_style('bsf-'.$font,trailingslashit($this->paths['fonturl']).$info['style']);
						}
					}
				}
			}
		}// end aio_admin_scripts
		function aio_front_scripts()
		{
			// register js
			wp_register_script('ultimate-appear',$this->assets_js.'jquery.appear.js',array('jquery'),'1.5',true);			
			wp_register_script('ultimate-custom',$this->assets_js.'custom.js',array('jquery'),'1.5',true);
			// register css
			wp_register_style('ultimate-animate',$this->assets_css.'animate.css');
			wp_register_style('ultimate-style',$this->assets_css.'style.css');
			if(function_exists('vc_is_editor')){
				if(vc_is_editor()){
					wp_enqueue_style('vc-fronteditor',$this->assets_css.'vc-fronteditor.css');
				}
			}
			$fonts = get_option('smile_fonts');
			if(is_array($fonts))
			{
				foreach($fonts as $font => $info)
				{
					$style_url = $info['style'];
					if(strpos($style_url, 'http://' ) !== false) {
						wp_enqueue_style('bsf-'.$font,$info['style']);
					} else {
						wp_enqueue_style('bsf-'.$font,trailingslashit($this->paths['fonturl']).$info['style']);
					}
				}
			}
		}// end aio_front_scripts
		function aio_move_templates()
		{
			// Make destination directory 
			if (!is_dir($this->vc_dest_dir)) { 
				wp_mkdir_p($this->vc_dest_dir);
			}
			@chmod($this->vc_dest_dir,0777);
			foreach(glob($this->vc_template_dir.'*') as $file)
			{
				$new_file = basename($file);
				@copy($file,$this->vc_dest_dir.$new_file);
			}
		}// end aio_move_templates
		function toggle_updater(){
			if(defined('ULTIMATE_USE_BUILTIN')){
				update_option('ultimate_updater','disabled');
			} else {
				update_option('ultimate_updater','enabled');
			}
			
			$modules = array(
				'ultimate_animation',
				'ultimate_buttons',
				'ultimate_countdown',
				'ultimate_flip_box',
				'ultimate_google_maps',
				'ultimate_google_trends',
				'ultimate_headings',
				'ultimate_icon_timeline',
				'ultimate_info_box',
				'ultimate_info_circle',
				'ultimate_info_list',
				'ultimate_info_tables',
				'ultimate_interactive_banners',
				'ultimate_interactive_banner_2',
				'ultimate_modals',
				'ultimate_parallax',
				'ultimate_pricing_tables',
				'ultimate_spacer',
				'ultimate_stats_counter',
				'ultimate_swatch_book',
				'ultimate_icons',
				'ultimate_list_icon',
				'woocomposer',
			);
			$ultimate_modules = get_option('ultimate_modules');
			if(!$ultimate_modules){
				update_option('ultimate_modules',$modules);
			}
		}
	}//end class
	new Ultimate_VC_Addons;
	// load admin area
	require_once('admin/admin.php');
	$ultimate_modules = get_option('ultimate_modules');
	if($ultimate_modules &&  in_array("woocomposer",$ultimate_modules) ){
		require_once('woocomposer/woocomposer.php');
	}
}// end class check
/*
* Generate RGB colors from given HEX color
*
* @function: ultimate_hex2rgb()
* @Package: Ultimate Addons for Visual Compoer
* @Since: 2.1.0
* @param: $hex - HEX color value
* 		  $opecaty - Opacity in float value
* @returns: value with rgba(r,g,b,opacity);
*/
if(!function_exists('ultimate_hex2rgb')){
	function ultimate_hex2rgb($hex,$opacity=1) {
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
	   $rgba = 'rgba('.$r.','.$g.','.$b.','.$opacity.')';
	   //return implode(",", $rgb); // returns the rgb values separated by commas
	   return $rgba; // returns an array with the rgb values
	}
}
