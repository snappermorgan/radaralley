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

use Designmodo\Qards\Utility\Db;
/**
 * Migrations implements migration feature for WP plugin.
 */
class Migrations
{

    /**
     * Init Migrations
     *
     * @return void
     */
    static public function init()
    {
        if (get_option('qards_version') != DM_PLUGIN_VERSION) {
            self::migrate();
            add_option('qards_version', DM_PLUGIN_VERSION) || update_option('qards_version', DM_PLUGIN_VERSION);
        }
    }

    /**
     * Run SQL scripts
     *
     * @return void
     */
    static public function migrate()
    {
        $sql = array();

        // New install
        if (!get_option('qards_version')) {

            // Setup settings
            if (!get_option('qards_settings')) {

                $optName = 'qards_settings';
                $optVal = array(
                    'DM_GLOBAL_CSS' => 'body.qards {
    /* Qards CSS */
}'
                );
                add_option($optName, $optVal) || update_option($optName, $optVal);
            }

            // Setup DB
            if (Db::getColumn('SHOW TABLES LIKE "' . Db::getPluginTableName(Db::TABLE_COMPONENT) . '"') != Db::getPluginTableName(Db::TABLE_COMPONENT)) {
                $sql[] = '
                    CREATE TABLE `' . Db::getPluginTableName(Db::TABLE_COMPONENT) . '` (
                      `id` int(10) NOT NULL AUTO_INCREMENT,
                      `template_id` varchar(200) NOT NULL,
                      `thumb` LONGTEXT NOT NULL,
                      `model` text NOT NULL,
                      `custom_css` longtext NOT NULL,
                      `is_system` tinyint(1) NOT NULL DEFAULT "0",
                      `is_hidden` tinyint(1) NOT NULL DEFAULT "0",
                      `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`)
                    ) DEFAULT CHARSET=utf8;
                ';
                $sql[] = '
                    INSERT INTO `' . Db::getPluginTableName(Db::TABLE_COMPONENT) . '` VALUES
                    (1,"common.header","","[]", "", 1, 1, 1405943921),
                    (2,"common.footer","","[]", "", 1, 1, 1405943921);
                ';
            }

            if (Db::getColumn('SHOW TABLES LIKE "' . Db::getPluginTableName(Db::TABLE_LAYOUT) . '"') != Db::getPluginTableName(Db::TABLE_LAYOUT)) {
                $sql[] = '
                    CREATE TABLE `' . Db::getPluginTableName(Db::TABLE_LAYOUT) . '` (
                      `id` int(10) NOT NULL AUTO_INCREMENT,
                      `is_system` tinyint(1) NOT NULL DEFAULT 0,
                      PRIMARY KEY (`id`)
                    ) DEFAULT CHARSET=utf8;
                ';
            }

            if (Db::getColumn('SHOW TABLES LIKE "' . Db::getPluginTableName(Db::TABLE_LAYOUT_COMPONENT) . '"') != Db::getPluginTableName(Db::TABLE_LAYOUT_COMPONENT)) {
                $sql[] = '
                    CREATE TABLE `' . Db::getPluginTableName(Db::TABLE_LAYOUT_COMPONENT) . '` (
                      `layout_id` int(10) NOT NULL,
                      `component_id` int(10) NOT NULL,
                      `order` int(10) NOT NULL
                    ) DEFAULT CHARSET=utf8;
                ';
            }

            if (Db::getColumn('SHOW TABLES LIKE "' . Db::getPluginTableName(Db::TABLE_RESOURCE) . '"') != Db::getPluginTableName(Db::TABLE_RESOURCE)) {
                $sql[] = '
                    CREATE TABLE `' . Db::getPluginTableName(Db::TABLE_RESOURCE) . '` (
                      `id` int(10) NOT NULL AUTO_INCREMENT,
                      `template_id` varchar(255) NOT NULL,
                      `type` varchar(255) NOT NULL,
                      `data` longtext NOT NULL,
                      `is_custom` tinyint(1) NOT NULL DEFAULT "1",
                      `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`)
                    ) DEFAULT CHARSET=utf8;
                ';
            }

            if (Db::getColumn('SHOW TABLES LIKE "' . Db::getPluginTableName(Db::TABLE_SUBSCRIBER) . '"') != Db::getPluginTableName(Db::TABLE_SUBSCRIBER)) {
                $sql[] = '
                    CREATE TABLE IF NOT EXISTS `' . Db::getPluginTableName(Db::TABLE_SUBSCRIBER) . '` (
                      `email` varchar(255) NOT NULL,
                      `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ) DEFAULT CHARSET=utf8;
                ';
            }

            if (Db::getColumn('SHOW TABLES LIKE "' . Db::getPluginTableName(Db::TABLE_SS) . '"') != Db::getPluginTableName(Db::TABLE_SS)) {
                $sql[] = '
                    CREATE TABLE IF NOT EXISTS `' . Db::getPluginTableName(Db::TABLE_SS) . '` (
                      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                      `handle` varchar(255) NOT NULL,
                      `src` text NOT NULL,
                      `is_active` tinyint(1) unsigned NOT NULL,
                      PRIMARY KEY (`id`)
                    ) DEFAULT CHARSET=utf8;
                ';
            }


        // Migration
        } else if (version_compare(get_option('qards_version'), DM_PLUGIN_VERSION, '<')) {

            // If old version is older than 2.1
            if (version_compare(get_option('qards_version'), '1.1', '<')) {
            }

        }

        if ($sql) {
            require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta(join(PHP_EOL, $sql));
        }
    }
}