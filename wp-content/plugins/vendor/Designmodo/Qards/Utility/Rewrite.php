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
 * Rewrite implements removing slugs of CPT from URLs.
 */
class Rewrite
{

    /**
     * Init rewriter
     *
     * @return void
     */
    static public function init()
    {
        self::regenerateRewriteRules();

        // Save post
        add_action(
            'save_post',
            function ($postId) {
                Rewrite::regenerateRewriteRules();
            }
        );

        // Press Visual Editor button
        add_filter(
            'redirect_post_location',
            function ($location) {
                if (!empty($_POST['visualEditorHref'])) {
                    $location = $_POST['visualEditorHref'];
                }
                wp_redirect($location);
            }
        );

        // Insert/update post handler
        add_action(
            'wp_insert_post',
            function ($postId) {
                $currentPostType = get_post_type($postId);
                $customPostTypes = get_post_types(array('_builtin' => false), 'names');
                foreach ($customPostTypes as $type) {
                    if ($type == $currentPostType) {
                        Rewrite::regenerateRewriteRules();
                    }
                }
            }
        );

        // Remove slug from link
        add_filter(
            'post_type_link',
            function ($permalink) {
                $customPostTypes = get_post_types(array('_builtin' => false), 'objects');
                foreach ($customPostTypes as $type => $postType) {
                    // TODO add $postType check FIXME !!!!!
                    $slug = trim($postType->rewrite['slug'], '/');
                    $permalink = str_replace(get_bloginfo('url') . '/' . $slug . '/', get_bloginfo('url') . '/', $permalink);
                }
                return $permalink;
            }
        );

        // Filter the slug
        add_filter(
            'wp_unique_post_slug',
            function ($slug, $postId, $postStatus, $postType, $postParent, $originalSlug) {
                if ($originalSlug == $slug) {
                    $hierarchicalPostTypes = array_merge(get_post_types(array('hierarchical' => true)), array('page' => 'page', 'post' => 'post', DM_POST_TYPE => DM_POST_TYPE, 'sf_page' => 'sf_page'));
                    $sql = '
                    SELECT
                        post_name
                    FROM ' . Db::getWpDb()->posts . '
                    WHERE
                        post_name = %s
                        AND
                        ID != %d
                        AND
                        post_type IN ( "' . implode( '", "', esc_sql( $hierarchicalPostTypes ) ) . '" )
                    LIMIT 1';
                    $postNameCheck = Db::getColumn($sql, array(
                        $slug,
                        $postId
                    ));
                    if ($postNameCheck) {
                        $suffix = 2;
                        do {
                            $altPostName = _truncate_post_slug($slug, 200 - (strlen($suffix) + 1)) . "-$suffix";
                            $postNameCheck = Db::getColumn($sql, array(
                                $altPostName,
                                $postId
                            ));
                            $suffix ++;
                        } while ($postNameCheck);
                        $slug = $altPostName;
                    }
                }
                return $slug;
            },
            10,
            6
        );

    }

    /**
     * Regenerate rewrite rules for each custom page
     *
     * @return void
     */
    static public function regenerateRewriteRules()
    {
        self::generateRewriteRules();
        flush_rewrite_rules(false);
    }

    /**
     * Generate rewrite rules for each custom page
     *
     * @return void
     */
    static public function generateRewriteRules()
    {
        $customPostTypes = get_post_types(array('_builtin' => false), 'objects');
        foreach ($customPostTypes as $type => $postType) {
            $posts = Db::getAll(
                'SELECT post_name
                FROM `' . Db::getWpDb()->posts . '`
                WHERE post_name !=  ""
                AND post_type = %s',
                array($type)
            );
            foreach ($posts as $post) {
                add_rewrite_rule($post['post_name'] . '$', 'index.php?' . $postType->query_var . '=' . $post['post_name'], 'top');
            }
        }
    }
}