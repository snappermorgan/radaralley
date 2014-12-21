<?php

if (!defined('ABSPATH')) exit;

class gdbbMod_Admin {
    public $admin_plugin = false;

    function __construct() {
        add_action('admin_init', array(&$this, 'admin_init'));
    }

    public function admin_init() {
        if (isset($_POST['gdbb-views-submit'])) {
            global $gdbbpress_tools;
            check_admin_referer('gd-bbpress-tools');

            $gdbbpress_tools->o['view_mostreplies_active'] = isset($_POST['view_mostreplies_active']) ? 1 : 0;
            $gdbbpress_tools->o['view_latesttopics_active'] = isset($_POST['view_latesttopics_active']) ? 1 : 0;
            $gdbbpress_tools->o['view_searchresults_active'] = isset($_POST['view_searchresults_active']) ? 1 : 0;

            update_option('gd-bbpress-tools', $gdbbpress_tools->o);
            wp_redirect(add_query_arg('settings-updated', 'true'));
            exit();
        }

        if (isset($_POST['gdbb-bbcode-submit'])) {
            global $gdbbpress_tools;
            check_admin_referer('gd-bbpress-tools');

            $all_bbcodes = array(
                'b', 'u', 'i', 's', 'center', 'right', 'left', 'justify', 'sub', 'sup', 'pre',
                'reverse', 'list', 'ol', 'ul', 'li', 'blockquote', 'div', 'area', 'border',
                'hr', 'size', 'color', 'quote', 'url', 'google', 'youtube', 'vimeo', 'img',
                'note');

            $deactivate = array();
            $active = isset($_POST['bbcode_activated']) ? (array)$_POST['bbcode_activated'] : array();

            foreach ($all_bbcodes as $bbc) {
                if (!in_array($bbc, $active)) {
                    $deactivate[] = $bbc;
                }
            }

            $gdbbpress_tools->o['bbcodes_active'] = isset($_POST['bbcodes_active']) ? 1 : 0;
            $gdbbpress_tools->o['bbcodes_notice'] = isset($_POST['bbcodes_notice']) ? 1 : 0;
            $gdbbpress_tools->o['bbcodes_bbpress_only'] = isset($_POST['bbcodes_bbpress_only']) ? 1 : 0;
            $gdbbpress_tools->o['bbcodes_special_super_admin'] = isset($_POST['bbcodes_special_super_admin']) ? 1 : 0;
            $gdbbpress_tools->o['bbcodes_special_roles'] = (array)$_POST['bbcodes_special_roles'];
            $gdbbpress_tools->o['bbcodes_special_action'] = isset($_POST['bbcodes_special_action']) ? 1 : 0;
            $gdbbpress_tools->o['bbcodes_deactivated'] = $deactivate;

            update_option('gd-bbpress-tools', $gdbbpress_tools->o);
            wp_redirect(add_query_arg('settings-updated', 'true'));
            exit();
        }

        if (isset($_POST['gdbb-tools-submit'])) {
            global $gdbbpress_tools;
            check_admin_referer('gd-bbpress-tools');

            $gdbbpress_tools->o['include_always'] = isset($_POST['include_always']) ? 1 : 0;
            $gdbbpress_tools->o['include_js'] = isset($_POST['include_js']) ? 1 : 0;
            $gdbbpress_tools->o['include_css'] = isset($_POST['include_css']) ? 1 : 0;
            $gdbbpress_tools->o['quote_active'] = isset($_POST['quote_active']) ? 1 : 0;
            $gdbbpress_tools->o['quote_location'] = $_POST['quote_location'];
            $gdbbpress_tools->o['quote_method'] = $_POST['quote_method'];
            $gdbbpress_tools->o['quote_super_admin'] = isset($_POST['quote_super_admin']) ? 1 : 0;
            $gdbbpress_tools->o['quote_roles'] = (array)$_POST['quote_roles'];
            $gdbbpress_tools->o['toolbar_active'] = isset($_POST['toolbar_active']) ? 1 : 0;
            $gdbbpress_tools->o['toolbar_super_admin'] = isset($_POST['toolbar_super_admin']) ? 1 : 0;
            $gdbbpress_tools->o['toolbar_roles'] = (array)$_POST['toolbar_roles'];
            $gdbbpress_tools->o['admin_disable_active'] = isset($_POST['admin_disable_active']) ? 1 : 0;
            $gdbbpress_tools->o['admin_disable_super_admin'] = isset($_POST['admin_disable_super_admin']) ? 1 : 0;
            $gdbbpress_tools->o['admin_disable_roles'] = (array)$_POST['admin_disable_roles'];
            $gdbbpress_tools->o['signature_active'] = isset($_POST['signature_active']) ? 1 : 0;
            $gdbbpress_tools->o['signature_length'] = intval($_POST['signature_length']);
            $gdbbpress_tools->o['signature_super_admin'] = isset($_POST['signature_super_admin']) ? 1 : 0;
            $gdbbpress_tools->o['signature_roles'] = (array)$_POST['signature_roles'];
            $gdbbpress_tools->o['signature_method'] = $_POST['signature_method'];
            $gdbbpress_tools->o['signature_enhanced_super_admin'] = isset($_POST['signature_enhanced_super_admin']) ? 1 : 0;
            $gdbbpress_tools->o['signature_enhanced_roles'] = (array)$_POST['signature_enhanced_roles'];
            $gdbbpress_tools->o['signature_buddypress_profile_group'] = $_POST['signature_buddypress_profile_group'];

            update_option('gd-bbpress-tools', $gdbbpress_tools->o);
            wp_redirect(add_query_arg('settings-updated', 'true'));
            exit();
        }
    }
}

global $gdbbpress_tools_admin;
$gdbbpress_tools_admin = new gdbbMod_Admin();

?>