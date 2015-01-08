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
 * StyleScriptFilter filtering scripts and stiles.
 */
class StyleScriptFilter
{
    const OWNER_THEME = 1;
    const OWNER_PLUGIN = 2;
    const OWNER_UNKNOWN = 3;

    /**
     * Filter syles
     *
     * @return void
     */
    static public function filterStyles()
    {
        // Check for page type
        if (is_admin()) {
            return;
        }
        $post = new \TimberPost();
        if ($post->get_post_type()->name != DM_POST_TYPE) {
            return;
        }
        global $wp_styles;

        self::filter(
            $wp_styles->registered,
            function($handle) {
                wp_deregister_style($handle);
            }
        );
    }

    /**
     * Filter scripts
     *
     * @return void
     */
    static public function filterScripts()
    {
        // Check for page type
        if (is_admin()) {
            return;
        }
        $post = new \TimberPost();
        if ($post->get_post_type()->name != DM_POST_TYPE) {
            return;
        }
        global $wp_scripts;

        self::filter(
            $wp_scripts->registered,
            function($handle) {
                wp_deregister_script($handle);
            }
        );
    }

    /**
     * Filter resources and delete if needed
     *
     * @param array $registered
     * @param callable $remover
     * @return void
     */
    static private function filter($registered, $remover)
    {
        foreach ($registered as $dependency) {
            // Check owner type
            $src = (is_string($dependency->src) ? $dependency->src : '');
            $owner = self::getOwnerType($src);
            $handle = $dependency->handle;

            // Get SS rule
            $ssRule = Db::getRow(
                'SELECT * FROM `' . Db::getPluginTableName(Db::TABLE_SS) . '` WHERE `handle` = %s AND `src` = %s',
                array($handle, $src)
            );
            // If it's new
            if (!$ssRule) {
                $ssRule = array(
                    'handle' => $handle,
                    'src' => $src,
                    'is_active' => ($owner == self::OWNER_THEME ? 0 : 1 ) // By default plugins and unknown are active, but theme disabled
                );
                Db::insert(
                    Db::getPluginTableName(Db::TABLE_SS),
                    $ssRule
                );
            }
            // Derigister style
            if (!$ssRule['is_active']) {
                call_user_func_array($remover, array($handle));
            }
        }
    }

    /**
     * Get owner of resource
     *
     * @param string $src
     * @return int
     */
    static public function getOwnerType($src) {
        if (strpos($src, '/themes/')) {
            return self::OWNER_THEME;
        } elseif (strpos($src, '/plugins/')) {
            return self::OWNER_PLUGIN;
        } else {
            return self::OWNER_UNKNOWN;
        }
    }
}