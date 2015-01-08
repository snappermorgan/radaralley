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
 * Db implements access to DB.
 */
class Db
{

    const PLUGIN_PREFIX = 'qards_';

    const TABLE_LAYOUT = 'layout';

    const TABLE_COMPONENT = 'component';

    const TABLE_LAYOUT_COMPONENT = 'layout_component';

    const TABLE_RESOURCE = 'resource';

    const TABLE_SUBSCRIBER = 'subscriber';

    const TABLE_SS = 'ss';

    /**
     * Get plugin table name
     *
     * @param string $table
     * @return string
     */
    static public function getPluginTableName($table)
    {
        global $wpdb;
        return $wpdb->get_blog_prefix(get_current_blog_id()) . self::PLUGIN_PREFIX . $table;
    }

    /**
     * Prepare query
     *
     * @param string $sql Query statement with sprintf()-like placeholders
     * @param array $params
     * @return string
     */
    static public function prepare($sql, $params = array())
    {
        global $wpdb;
        if (!empty($params)) {
            $prepared = call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $params));
        } else {
            $prepared = $sql;
        }
        return $prepared;
    }

    /**
     * Get one row from table
     *
     * @param string $sql Query statement with sprintf()-like placeholders
     * @param array $params
     * @return array|NULL
     */
    static public function getRow($sql, $params = array())
    {
        global $wpdb;
        $prepared = self::prepare($sql, $params);
        return $wpdb->get_row($prepared, ARRAY_A);
    }

    /**
     * Get one column from the row
     *
     * @param string $sql Query statement with sprintf()-like placeholders
     * @param array $params
     * @return string|null Database query result (as string), or null on failure
     */
    static public function getColumn($sql, $params = array())
    {
        global $wpdb;
        $prepared = self::prepare($sql, $params);
        return $wpdb->get_var($prepared);
    }

    /**
     * Get a few rows from table
     *
     * @param string $sql Query statement with sprintf()-like placeholders
     * @param array $params
     * @return array
     */
    static public function getAll($sql, $params = array())
    {
        global $wpdb;
        $prepared = self::prepare($sql, $params);
        $results = $wpdb->get_results($prepared, ARRAY_A);
        return $results ? $results : array();
    }

    /**
     * Replase rows in table
     *
     * @param string $table
     * @param array $data
     * @return int|false The number of rows affected, or false on error.
     */
    static public function replace($table, $data)
    {
        global $wpdb;
        return $wpdb->replace(
            $table,
            $data
        );
    }

    /**
     * Insert row into table
     *
     * @param string $table
     * @param array $data
     * @return int|false ID, or false on error.
     */
    static public function insert($table, $data)
    {
        global $wpdb;
        $wpdb->insert(
            $table,
            $data
        );
        return $wpdb->insert_id;
    }

    /**
     * Update rows in table
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @return int|false The number of rows updated, or false on error.
     */
    static public function update($table, $data, $where)
    {
        global $wpdb;
        return $wpdb->update(
            $table,
            $data,
            $where
        );
    }

    /**
     * Delete rows from table
     *
     * @param string $table
     * @param array $data
     * @return int|false The number of rows updated, or false on error.
     */
    static public function delete($table, $data)
    {
        global $wpdb;
        return $wpdb->delete(
            $table,
            $data
        );
    }

    /**
     * Perform a MySQL database query, using current database connection.
     *
     * @param string $sql Query statement with sprintf()-like placeholders
     * @param array $params
     * @return int|false Number of rows affected/selected or false on error
     */
    static public function query($sql, $params = array())
    {
        global $wpdb;
        $prepared = self::prepare($sql, $params);
        return $wpdb->query($prepared, ARRAY_A);
    }

    /**
     * Get $wpdb
     *
     * @return wpdb
     */
    static public function getWpDb()
    {
        global $wpdb;
        return $wpdb;
    }
}