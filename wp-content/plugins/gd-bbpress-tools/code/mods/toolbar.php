<?php

if (!defined('ABSPATH')) exit;

class gdbbMod_Toolbar {
    function __construct() {
        add_action('bbtoolbox_init', array($this, 'init'));
    }

    public function init() {
        add_action('admin_bar_menu', array(&$this, 'admin_bar_menu'), 100);
        add_action('admin_head', array(&$this, 'admin_bar_icon'));
        add_action('wp_head', array(&$this, 'admin_bar_icon'));
    }

    public function admin_bar_icon() { ?>
        <style type="text/css">
            #wpadminbar .ab-top-menu > li.menupop.icon-gdbb-toolbar > .ab-item {
                background-image: url('<?php echo plugins_url('gd-bbpress-tools/gfx/menu.png'); ?>');
                background-repeat: no-repeat;
                background-position: 0.85em 50%;
                padding-left: 32px;
            }
        </style>
    <?php }

    public function admin_bar_menu() {
        global $wp_admin_bar;

        $wp_admin_bar->add_menu(array(
            'id'     => 'gdbb-toolbar',
            'title'  => __("Forums", "gd-bbpress-tools"),
            'href'   => get_post_type_archive_link('forum'),
            'meta'   => array('class' => 'icon-gdbb-toolbar')
        ));

        $wp_admin_bar->add_group(array(
            'parent' => 'gdbb-toolbar',
            'id'     => 'gdbb-toolbar-public'
        ));

        $query = $forums_query = array(
			'post_parent'    => 0,
                        'post_status'    => 'publish',
			'posts_per_page' => 20,
			'orderby'        => 'menu_order',
			'order'          => 'ASC');
        $forums = bbp_get_forums_for_current_user($query);
        if (is_array($forums) && count($forums) > 0) {
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-public',
                'id'     => 'gdbb-toolbar-forums',
                'title'  => __("Forums", "gd-bbpress-tools"),
                'href'   => bbp_get_forums_url()
            ));

            foreach ($forums as $forum) {
                $wp_admin_bar->add_menu(array(
                    'parent' => 'gdbb-toolbar-forums',
                    'id'     => 'gdbb-toolbar-forums-'.$forum->ID,
                    'title'  => apply_filters('the_title', $forum->post_title, $forum->ID),
                    'href'   => get_permalink($forum->ID)
                ));
            }
        }

        $views = bbp_get_views();
        if (is_array($views) && count($views) > 0) {
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-public',
                'id'     => 'gdbb-toolbar-views',
                'title'  => __("Views", "gd-bbpress-tools"),
                'href'   => bbp_get_forums_url()
            ));

            foreach ($views as $view => $args) {
                $wp_admin_bar->add_menu(array(
                    'parent' => 'gdbb-toolbar-views',
                    'id'     => 'gdbb-toolbar-views-'.$view,
                    'title'  => bbp_get_view_title($view),
                    'href'   => bbp_get_view_url($view)
                ));
            }
        }

        if (current_user_can(GDBBPRESSTOOLS_CAP)) {
            $wp_admin_bar->add_group(array(
                'parent' => 'gdbb-toolbar',
                'id'     => 'gdbb-toolbar-admin'
            ));
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-admin',
                'id'     => 'gdbb-toolbar-new',
                'title'  => __("New", "gd-bbpress-tools"),
                'href'   => ''
            ));
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-new',
                'id'     => 'gdbb-toolbar-new-forum',
                'title'  => __("Forum", "gd-bbpress-tools"),
                'href'   => admin_url('post-new.php?post_type=forum')
            ));
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-new',
                'id'     => 'gdbb-toolbar-new-topic',
                'title'  => __("Topic", "gd-bbpress-tools"),
                'href'   => admin_url('post-new.php?post_type=topic')
            ));
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-new',
                'id'     => 'gdbb-toolbar-new-reply',
                'title'  => __("Reply", "gd-bbpress-tools"),
                'href'   => admin_url('post-new.php?post_type=reply')
            ));
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-admin',
                'id'     => 'gdbb-toolbar-edit',
                'title'  => __("Edit", "gd-bbpress-tools"),
                'href'   => ''
            ));
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-edit',
                'id'     => 'gdbb-toolbar-edit-forums',
                'title'  => __("Forums", "gd-bbpress-tools"),
                'href'   => admin_url('edit.php?post_type=forum')
            ));
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-edit',
                'id'     => 'gdbb-toolbar-edit-topics',
                'title'  => __("Topics", "gd-bbpress-tools"),
                'href'   => admin_url('edit.php?post_type=topic')
            ));
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-edit',
                'id'     => 'gdbb-toolbar-edit-replies',
                'title'  => __("Replies", "gd-bbpress-tools"),
                'href'   => admin_url('edit.php?post_type=reply')
            ));

            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-admin',
                'id'     => 'gdbb-toolbar-settings',
                'title'  => __("Settings", "gd-bbpress-tools"),
                'href'   => ''
            ));
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-settings',
                'id'     => 'gdbb-toolbar-settings-main',
                'title'  => __("bbPress Settings", "gd-bbpress-tools"),
                'href'   => admin_url('options-general.php?page=bbpress')
            ));
            $wp_admin_bar->add_group(array(
                'parent' => 'gdbb-toolbar-settings',
                'id'     => 'gdbb-toolbar-settings-third'
            ));
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-settings-third',
                'id'     => 'gdbb-toolbar-settings-third-tools',
                'title'  => __("GD bbPress Tools", "gd-bbpress-tools"),
                'href'   => admin_url('edit.php?post_type=forum&page=gdbbpress_tools')
            ));
            if (defined('GDBBPRESSATTACHMENTS_INSTALLED')) {
                $wp_admin_bar->add_menu(array(
                    'parent' => 'gdbb-toolbar-settings-third',
                    'id'     => 'gdbb-toolbar-settings-third-attachments',
                    'title'  => __("GD bbPress Attchments", "gd-bbpress-tools"),
                    'href'   => admin_url('edit.php?post_type=forum&page=gdbbpress_attachments')
                ));
            }

            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-admin',
                'id'     => 'gdbb-toolbar-tools',
                'title'  => __("Tools", "gd-bbpress-tools"),
                'href'   => ''
            ));
            $wp_admin_bar->add_menu(array(
                'parent' => 'gdbb-toolbar-tools',
                'id'     => 'gdbb-toolbar-tools-recount',
                'title'  => __("Recount", "gd-bbpress-tools"),
                'href'   => admin_url('tools.php?page=bbp-recount')
            ));
        }

        $wp_admin_bar->add_group(array(
            'parent' => 'gdbb-toolbar',
            'id'     => 'gdbb-toolbar-info',
            'meta'   => array('class' => 'ab-sub-secondary')
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'gdbb-toolbar-info',
            'id'     => 'gdbb-toolbar-info-links',
            'title'  => __("Information", "gd-bbpress-tools")
        ));
        $wp_admin_bar->add_group(array(
            'parent' => 'gdbb-toolbar-info-links',
            'id'     => 'gdbb-toolbar-info-links-bbp',
            'meta'   => array('class' => 'ab-sub-secondary')
        ));
        $wp_admin_bar->add_group(array(
            'parent' => 'gdbb-toolbar-info-links',
            'id'     => 'gdbb-toolbar-info-links-d4p',
            'meta'   => array('class' => 'ab-sub-secondary')
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'gdbb-toolbar-info-links-bbp',
            'id'     => 'gdbb-toolbar-bbp-home',
            'title'  => __("bbPress Homepage", "gd-bbpress-tools"),
            'href'   => 'http://bbpress.org/',
            'meta'   => array('target' => '_blank')
        ));
        
        $wp_admin_bar->add_menu(array(
            'parent' => 'gdbb-toolbar-info-links-d4p',
            'id'     => 'gdbb-toolbar-d4p-home',
            'title'  => __("Dev4Press Homepage", "gd-bbpress-tools"),
            'href'   => 'http://www.dev4press.com/',
            'meta'   => array('target' => '_blank')
        ));
    }
}

?>