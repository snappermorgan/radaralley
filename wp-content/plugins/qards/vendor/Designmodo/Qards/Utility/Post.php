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

use Designmodo\Qards\Page\Layout\Layout;
/**
 * Post provides post features of WordPress.
 */
class Post
{
    /**
     * Register custom post type
     *
     * @return void
     */
    static public function registerCustomPostType()
    {
        $labels = array(
            'name'               => __( 'Qards'),
            'singular_name'      => __( 'Qards Page' ),
            'menu_name'          => __( 'Qards' ),
            'name_admin_bar'     => __( 'Qards Page' ),
            'add_new'            => __( 'Add New' ),
            'add_new_item'       => __( 'Add New Page' ),
            'new_item'           => __( 'New Page' ),
            'edit_item'          => __( 'Edit Page' ),
            'view_item'          => __( 'View Page' ),
            'all_items'          => __( 'All Pages' ),
            'search_items'       => __( 'Search Pages' ),
            'parent_item_colon'  => __( 'Parent Pages:' ),
            'not_found'          => __( 'No pages found.' ),
            'not_found_in_trash' => __( 'No pages found in Trash.' )
        );

        // Define icon styles for the custom post type
        add_action(
            'admin_head',
            function () {
                ?>
                <style type="text/css" media="screen">
                    @font-face {
                        font-family: 'qards-icon';
                        src:url('<?php echo DM_PLUGIN_URL; ?>/fonts/qards-icon.eot?-azkf4y');
                        src:url('<?php echo DM_PLUGIN_URL; ?>/fonts/qards-icon.eot?#iefix-azkf4y') format('embedded-opentype'),
                            url('<?php echo DM_PLUGIN_URL; ?>/fonts/qards-icon.woff?-azkf4y') format('woff'),
                            url('<?php echo DM_PLUGIN_URL; ?>/fonts/qards-icon.ttf?-azkf4y') format('truetype'),
                            url('<?php echo DM_PLUGIN_URL; ?>/fonts/qards-icon.svg?-azkf4y#icomoon') format('svg');
                        font-weight: normal;
                        font-style: normal;
                    }
                    #menu-posts-qards_page .wp-menu-image:before {
                        font-family: 'qards-icon';
                        speak: none;
                        font-style: normal;
                        font-weight: normal;
                        font-variant: normal;
                        text-transform: none;
                        line-height: 1;
                        /* Better Font Rendering =========== */
                        -webkit-font-smoothing: antialiased;
                        -moz-osx-font-smoothing: grayscale;
                        content: "\e600";
                        font-size: 18px !important;
                    }
                </style>
                <?php
            }
        );

        $args = array(
            'labels'             => $labels,
            'menu_icon'          => '',
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('with_front' => false, 'slug' => 'qards' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'supports'           => array( 'title', 'author' )
        );

        register_post_type( DM_POST_TYPE, $args );

        // Add custom post type to the get_pages
        add_filter(
            'get_pages',
            function ($pages) {
                $qardsPosts = get_posts(array('post_type' => DM_POST_TYPE, 'posts_per_page' => 5000));
                return array_merge($pages, $qardsPosts);
            }
        );

        // Add custom post type to front page selector
        add_action(
            'pre_get_posts',
            function ($query) {
                if ('' == $query->query_vars['post_type'] && 0 != $query->query_vars['page_id']) {
                    $query->query_vars['post_type'] = array(
                        'page',
                        DM_POST_TYPE
                    );
                }
            }
        );

        // Set layout to new page
        add_action(
            'wp_insert_post',
            function ($postId) {
                // Set layout id
                $post = new \TimberPost($postId);
                if ($post->get_post_type()->name == DM_POST_TYPE && !get_post_meta($postId, '_qards_page_layout', true)) {
                    // Set status draft
                    Db::update(
                        Db::getWpDb()->posts,
                        array('post_status' => 'draft'),
                        array('ID' => $postId, 'post_status' => 'auto-draft')
                    );
                    $layout = new Layout();
                    $layout->save();
                    add_post_meta($postId, '_qards_page_layout', $layout->getId(), true)
                    ||
                    update_post_meta($postId, '_qards_page_layout', $layout->getId());
                }
            }
        );

        // Switch off autosave
        add_action(
            'admin_enqueue_scripts',
            function () {
                if (DM_POST_TYPE == get_post_type()) {
                    wp_register_script('custom-admin', DM_PLUGIN_URL . '/js/custom-admin.js', array('jquery'));
                    wp_enqueue_script('custom-admin');
                    wp_dequeue_script('autosave');
                }
            }
        );

        // Img unautop
        add_filter(
            'the_content',
            function ($pee) {
                 $pee = preg_replace('/<p>\\s*?(<a .*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s', '<div class="figure">$1</div>', $pee);
                 return $pee;
            },
            30
        );


        // Filters wp_title to print a neat <title> tag based on what is being viewed.
        add_filter(
            'wp_title',
            function ( $title, $sep, $seplocation ) {
                if ( is_feed() ) {
                    return $title;
                }
                global $page, $paged;
                $post = new \TimberPost();
                if ($post->get_post_type()->name != DM_POST_TYPE) {
                    return $title;
                }
                $title = $post->title();
                // Add the blog name
                if ( 'right' == $seplocation ) { // sep on right, so reverse the order
                    $title = $title . ' ' . $sep . ' ' . get_bloginfo( 'name', 'display' );
                } else {
                    $title = get_bloginfo( 'name', 'display' ) . ' ' . $sep . ' ' . $title;
                }

                // Add the blog description for the home/front page.
                $site_description = get_bloginfo( 'description', 'display' );
                if ( $site_description && ( is_home() || is_front_page() ) ) {
                    $title .= " $sep $site_description";
                }

                // Add a page number if necessary:
                if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
                    $title .= " $sep " . sprintf( __( 'Page %s', '_s' ), max( $paged, $page ) );
                }

                return $title;
            },
            10,
            3
        );

        self::assignTpl();
    }

    /**
     * Assign template to CPT
     *
     * @return void
     */
    static public function assignTpl()
    {
        add_filter(
            'template_include',
            function ($template)
            {
                global $post;
                if (get_post_meta($post->ID, '_qards_page_layout', true)) {
                    return DM_BASE_PATH . '/page.php';
                }
                return $template;
            }
        );
    }
}