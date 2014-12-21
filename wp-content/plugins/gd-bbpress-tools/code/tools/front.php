<?php

if (!defined('ABSPATH')) exit;

class gdbbMod_Front {
    function __construct() {
        add_action('bbtoolbox_core', array($this, 'load'));
    }

    public function load() {
        add_action('bbp_head', array(&$this, 'bbp_head'));
        add_action('wp_enqueue_scripts', array(&$this, 'wp_enqueue_scripts'));
    }

    public function wp_enqueue_scripts() {
        if (d4p_bbt_o('include_always') == 1 || d4p_is_bbpress()) {
            if (d4p_bbt_o('include_css') == 1) {
                wp_enqueue_style('d4p-bbtools-css', GDBBPRESSTOOLS_URL.'css/gd-bbpress-tools.css', array(), GDBBPRESSTOOLS_VERSION);
            }

            if (d4p_bbt_o('include_js') == 1) {
                wp_enqueue_script('jquery');
                wp_enqueue_script('d4p-bbtools-js', GDBBPRESSTOOLS_URL.'js/gd-bbpress-tools.js', array('jquery'), GDBBPRESSTOOLS_VERSION, true);
            }
        }
    }

    public function bbp_head() { 
        if (d4p_bbt_o('include_always') == 1 || d4p_is_bbpress()) {

        ?><script type="text/javascript">
            /* <![CDATA[ */
            var gdbbPressToolsInit = {
                quote_method: "<?php echo d4p_bbt_o('quote_method'); ?>",
                quote_wrote: "<?php echo __("wrote", "gd-bbpress-tools"); ?>",
                bbpress_version: <?php echo d4p_bbpress_version(); ?>,
                wp_version: <?php echo GDBBPRESSTOOLS_WPV; ?>,
                wp_editor: <?php echo d4p_bbpress_version() > 20 ? (bbp_use_wp_editor() ? 1 : 0) : 0; ?>
            };
            /* ]]> */
        </script><?php }
    }
}

global $gdbbpress_tools_front;
$gdbbpress_tools_front = new gdbbMod_Front();

?>