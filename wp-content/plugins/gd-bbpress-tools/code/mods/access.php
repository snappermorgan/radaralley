<?php

if (!defined('ABSPATH')) exit;

class gdbbMod_Access {
    function __construct() {
        add_action('bbp_init', array($this, 'admin_disable_access'), 8);
    }

    /** Based on the code by John James Jacoby from 'bbPress - No Admin' plugin:
     *  http://wordpress.org/extend/plugins/bbpress-no-admin/
     */
    public function admin_disable_access() {
        remove_action('admin_menu', 'bbp_admin_separator');
        remove_action('custom_menu_order', 'bbp_admin_custom_menu_order');
        remove_action('menu_order', 'bbp_admin_menu_order');

        add_filter('bbp_register_forum_post_type', array($this, 'admin_disable_post_type'));
        add_filter('bbp_register_topic_post_type', array($this, 'admin_disable_post_type'));
        add_filter('bbp_register_reply_post_type', array($this, 'admin_disable_post_type'));
    }

    public function admin_disable_post_type($args) {
        $args['show_in_nav_menus'] = false;
        $args['show_ui'] = false;
        $args['can_export'] = false;
        $args['capability_type'] = null;

        return $args;
    }
}

?>
