<?php
/*
 * This file is part of the Designmodo WordPress Plugin.
 *
 * (c) Designmodo Inc. <info@designmodo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Designmodo\Qards\Utility;

/**
 * Menu implements menu handler for WordPress.
 */
class Menu
{


    const MENU_LOCATION_MAIN = 'qards-main-menu';

    /**
     * Init menus
     *
     * @return void
     */
    static public function init()
    {
        // Register locations for menus
        register_nav_menu(self::MENU_LOCATION_MAIN, __('Qards main'));

        // Create menus if needed
        self::initMenus();

        // Edit mode switcher
        add_action(
            'wp_before_admin_bar_render',
            function () {
                global $wp_admin_bar;
                $post = new \TimberPost();
                if (is_object($post->get_post_type()) && $post->get_post_type()->name == DM_POST_TYPE && !Context::getInstance()->get('edit_mode') && !is_admin()) {
                    $wp_admin_bar->add_menu(
                        array(
                            'parent' => false,
                            'id' => 'dm_edit_mode',
                            'title' => __('Visual editor'),
                            'href' => add_query_arg(array('dm_edit_mode' => 'true'), get_permalink($post->ID)),
                        )
                    );
                }
            }
        );

        // Register custom fields for sp_page editor
        add_action(
            'add_meta_boxes',
            function () {
                add_meta_box(
                    'dm_meta_visual_editor',
                    'Visual editor',
                    function ($post) {
                        echo '<a href="' . add_query_arg( array('dm_edit_mode' => 'true'), get_permalink($post) ) . '" class="button button-primary button-large visual_editor">Visual editor</a>';
                    },
                    DM_POST_TYPE,
                    'normal',
                    'high'
                );
            }
        );

        // Add custom actions to the post grid
        add_filter(
            'post_row_actions',
            function ($actions) {
                if (get_post_type(get_the_ID()) == DM_POST_TYPE && isset($actions['edit'])){
                    $actions['visual_editor'] = '<a href="' . add_query_arg( array('dm_edit_mode' => 'true'), get_permalink() ) . '" class="button button-primary button-large" style="vertical-align: middle;">Visual editor</a>';
                }
                return $actions;
            }
        );
    }

    /**
     * Create dummy menus
     *
     * @return void
     */
    static public function initMenus()
    {
        $menuItemsMain = array(
            array('menu-item-title' =>  __('Home')),
            array('menu-item-title' =>  __('Company')),
            array('menu-item-title' =>  __('Portfolio')),
            array('menu-item-title' =>  __('Blog')),
            array('menu-item-title' =>  __('Contact'))
        );
        $menus = array(
            array(
                'name' => __('Qards main'),
                'location' => self::MENU_LOCATION_MAIN,
                'items' => $menuItemsMain
            ),
        );
        $setupMenuItems = function($menuId, $items, $parentItemId = null) use ( &$setupMenuItems ) {
            foreach ($items as $item) {
                $item['menu-item-url'] = isset($item['menu-item-url']) ? $item['menu-item-url'] : '#';
                $item['menu-item-status'] = isset($item['menu-item-status']) ? $item['menu-item-status'] : 'publish';
                if ($parentItemId) {
                    $item['menu-item-parent-id'] = $parentItemId;
                }
                $menuItemId = wp_update_nav_menu_item($menuId, 0, $item);
                if ($item['children']) {
                    $setupMenuItems($menuId, $item['children'], $menuItemId);
                }
            }
        };

        foreach ($menus as $menu) {
            $locations = get_theme_mod('nav_menu_locations');
            if (!get_term_by('name', $menu['name'], 'nav_menu') && empty($locations[$menu['location']])) {
                $menuId = wp_create_nav_menu($menu['name']);
                $setupMenuItems($menuId, $menu['items']);
                $locations = get_theme_mod('nav_menu_locations');
                $locations[$menu['location']] = $menuId;
                set_theme_mod( 'nav_menu_locations', $locations );
            } else if (empty($locations[$menu['location']])) {
                $menuObj = get_term_by('name', $menu['name'], 'nav_menu');
                $locations = get_theme_mod('nav_menu_locations');
                $locations[$menu['location']] = $menuObj->term_id;
                set_theme_mod( 'nav_menu_locations', $locations );
            }
        }
    }
}