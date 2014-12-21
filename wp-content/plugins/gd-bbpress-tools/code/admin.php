<?php

if (!defined('ABSPATH')) exit;

require_once(GDBBPRESSTOOLS_PATH.'code/tools/admin.php');

class gdbbT_Admin {
    private $page_ids = array();
    private $admin_plugin = false;

    function __construct() {
        add_action('admin_init', array(&$this, 'admin_init'));
        add_action('admin_menu', array(&$this, 'admin_menu'));

        add_filter('plugin_action_links', array(&$this, 'plugin_actions'), 10, 2);
        add_filter('plugin_row_meta', array(&$this, 'plugin_links'), 10, 2);

        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_files'));
    }

    function upgrade_notice() {
        global $gdbbpress_tools;

        if ($gdbbpress_tools->o['upgrade_to_pro_160'] == 1) {
            $no_thanks = add_query_arg('proupgradebbt', 'hide');

            echo '<div class="updated d4p-updated">';
                echo __("Thank you for using this plugin. Please, take a few minutes and check out the GD bbPress Toolbox Pro plugin with many new and improved features.", "gd-bbpress-tools");
                echo '<br/>'.__("Buy GD bbPress Toolbox Pro version or Dev4Press Plugins Pack and get 15% discount using this coupon", "gd-bbpress-tools");
                echo ': <strong style="color: #c00;">GDBBPTOPRO</strong><br/>';
                echo '<strong><a href="http://www.gdbbpbox.com/" target="_blank">'.__("Official Website", "gd-bbpress-tools")."</a></strong> | ";
                echo '<strong><a href="http://d4p.me/247" target="_blank">'.__("Dev4Press Plugins Pack", "gd-bbpress-tools")."</a></strong> | ";
                echo '<a href="'.$no_thanks.'">'.__("Don't display this message anymore", "gd-bbpress-tools")."</a>.";
            echo '</div>';
        }
    }

    public function admin_init() {
        if (isset($_GET['page'])) {
            $this->admin_plugin = $_GET['page'] == 'gdbbpress_tools';
        }

        if (isset($_GET['proupgradebbt']) && $_GET['proupgradebbt'] == 'hide') {
            global $gdbbpress_tools;

            $gdbbpress_tools->o['upgrade_to_pro_160'] = 0;

            update_option('gd-bbpress-tools', $gdbbpress_tools->o);

            wp_redirect(remove_query_arg('proupgradebbt'));
            exit;
        }
    }

    public function enqueue_files() {
        if ($this->admin_plugin) {
            wp_enqueue_style('gd-bbpress-tools', GDBBPRESSTOOLS_URL."css/gd-bbpress-tools_admin.css", array(), GDBBPRESSTOOLS_VERSION);
        }
    }
    
    public function admin_menu() {
        $this->page_ids[] = add_submenu_page('edit.php?post_type=forum', 'GD bbPress Tools', __("Tools", "gd-bbpress-tools"), GDBBPRESSTOOLS_CAP, 'gdbbpress_tools', array(&$this, 'menu_tools'));

        $this->admin_load_hooks();
    }

    public function admin_load_hooks() {
        if (GDBBPRESSTOOLS_WPV < 33) return;

        foreach ($this->page_ids as $id) {
            add_action('load-'.$id, array(&$this, 'load_admin_page'));
        }
    }

    public function plugin_actions($links, $file) {
        if ($file == 'gd-bbpress-tools/gd-bbpress-tools.php' ){
            $settings_link = '<a href="edit.php?post_type=forum&page=gdbbpress_tools">'.__("Settings", "gd-bbpress-tools").'</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    function plugin_links($links, $file) {
        if ($file == 'gd-bbpress-tools/gd-bbpress-tools.php'){
            $links[] = '<a href="edit.php?post_type=forum&page=gdbbpress_tools&tab=faq">'.__("FAQ", "gd-bbpress-tools").'</a>';
            $links[] = '<a target="_blank" style="color: #cc0000; font-weight: bold;" href="http://www.gdbbpbox.com/">'.__("Upgrade to GD bbPress Toolbox Pro", "gd-bbpress-tools").'</a>';
        }

        return $links;
    }

    public function load_admin_page() {
        $screen = get_current_screen();

        $screen->set_help_sidebar('
            <p><strong>Dev4Press:</strong></p>
            <p><a target="_blank" href="http://www.dev4press.com/">'.__("Website", "gd-bbpress-tools").'</a></p>
            <p><a target="_blank" href="http://twitter.com/milangd">'.__("On Twitter", "gd-bbpress-tools").'</a></p>
            <p><a target="_blank" href="http://facebook.com/dev4press">'.__("On Facebook", "gd-bbpress-tools").'</a></p>');

        $screen->add_help_tab(array(
            "id" => "gdpt-screenhelp-help",
            "title" => __("Get Help", "gd-bbpress-tools"),
            "content" => '<h5>'.__("General Plugin Information", "gd-bbpress-tools").'</h5>
                <p><a href="http://www.dev4press.com/plugins/gd-bbpress-tools/" target="_blank">'.__("Home Page on Dev4Press.com", "gd-bbpress-tools").'</a> | 
                <a href="http://wordpress.org/extend/plugins/gd-bbpress-tools/" target="_blank">'.__("Home Page on WordPress.org", "gd-bbpress-tools").'</a></p> 
                <h5>'.__("Getting Plugin Support", "gd-bbpress-tools").'</h5>
                <p><a href="http://www.dev4press.com/forums/forum/free-plugins/gd-bbpress-tools/" target="_blank">'.__("Support Forum on Dev4Press.com", "gd-bbpress-tools").'</a> | 
                <a href="http://wordpress.org/tags/gd-bbpress-tools?forum_id=10" target="_blank">'.__("Support Forum on WordPress.org", "gd-bbpress-tools").'</a> | 
                <a href="http://www.dev4press.com/plugins/gd-bbpress-tools/support/" target="_blank">'.__("Plugin Support Sources", "gd-bbpress-tools").'</a></p>'));

        $screen->add_help_tab(array(
            "id" => "gdpt-screenhelp-website",
            "title" => "Dev4Press", "sfc",
            "content" => '<p>'.__("On Dev4Press website you can find many useful plugins, themes and tutorials, all for WordPress. Please, take a few minutes to browse some of these resources, you might find some of them very useful.", "gd-bbpress-tools").'</p>
                <p><a href="http://www.dev4press.com/plugins/" target="_blank"><strong>'.__("Plugins", "gd-bbpress-tools").'</strong></a> - '.__("We have more than 10 plugins available, some of them are commercial and some are available for free.", "gd-bbpress-tools").'</p>
                <p><a href="http://www.dev4press.com/themes/" target="_blank"><strong>'.__("Themes", "gd-bbpress-tools").'</strong></a> - '.__("All our themes are based on our own xScape Theme Framework, and only available as premium.", "gd-bbpress-tools").'</p>
                <p><a href="http://www.dev4press.com/category/tutorials/" target="_blank"><strong>'.__("Tutorials", "gd-bbpress-tools").'</strong></a> - '.__("Premium and free tutorials for our plugins themes, and many general and practical WordPress tutorials.", "gd-bbpress-tools").'</p>
                <p><a href="http://www.dev4press.com/documentation/" target="_blank"><strong>'.__("Central Documentation", "gd-bbpress-tools").'</strong></a> - '.__("Growing collection of functions, classes, hooks, constants with examples for our plugins and themes.", "gd-bbpress-tools").'</p>
                <p><a href="http://www.dev4press.com/forums/" target="_blank"><strong>'.__("Support Forums", "gd-bbpress-tools").'</strong></a> - '.__("Premium support forum for all with valid licenses to get help. Also, report bugs and leave suggestions.", "gd-bbpress-tools").'</p>'));
    }

    public function menu_tools() {
        global $gdbbpress_tools;

        $options = $gdbbpress_tools->o;
        $_user_roles = d4p_bbpress_get_user_roles();

        include(GDBBPRESSTOOLS_PATH.'forms/panels.php');
    }
}

global $gdbbpress_t_admin;
$gdbbpress_t_admin = new gdbbT_Admin();

?>