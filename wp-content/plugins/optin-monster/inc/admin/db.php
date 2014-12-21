<?php
/**
 * DB class.
 *
 * @since 1.0.0
 *
 * @package	OptinMonster
 * @author	Thomas Griffin
 */
class optin_monster_hits {

    /**
	 * Holds the DB version.
	 *
	 * @since 1.0.0
	 */
	public $db_version = '1.0';

	/**
	 * Constructor. Hooks all interactions into correct areas to start
	 * the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Shh...

	}

	public function activate() {

		global $wpdb, $charset_collate;
		$table_name = $wpdb->prefix . 'om_hits_log';
		if ( ! empty( $wpdb->charset ) )
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if ( ! empty( $wpdb->collate ) )
            $charset_collate .= " COLLATE $wpdb->collate";

		$sql_create_table = "CREATE TABLE {$table_name} (
		          hit_id bigint(20) unsigned NOT NULL auto_increment,
		          optin_id int(8) unsigned NOT NULL default '0',
		          hit_date datetime NOT NULL default '0000-00-00 00:00:00',
		          hit_type varchar(10) NOT NULL default '',
		          user_agent varchar(128) NOT NULL default '',
		          referer varchar(256) NOT NULL default '',
		          PRIMARY KEY  (hit_id),
		          KEY optin_id (optin_id),
		          KEY hit_date (hit_date),
		          KEY hit_type (hit_type)
		     ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_create_table );

		update_option( 'om_hits_db_version', $this->db_version );

	}

	public function uninstall() {

		global $wpdb;
		$table_name = $wpdb->prefix . 'om_hits_log';

		$sql_drop_table = "DROP TABLE IF EXISTS {$table_name}";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_drop_table );

		delete_option( 'om_hits_db_version' );

	}

}