<?php
class GFCouponsData{

    public static function update_table(){
        global $wpdb;
        $table_name = self::get_coupons_table_name();

        if ( ! empty($wpdb->charset) )
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if ( ! empty($wpdb->collate) )
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $table_name (
              id mediumint(8) unsigned not null auto_increment,
              form_id mediumint(8) unsigned default null,
              is_active tinyint(1) not null default 1,
              meta longtext,
              PRIMARY KEY  (id),
              KEY form_id (form_id)
            )$charset_collate;";

        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function get_coupons_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_coupons";
    }

    public static function get_feeds(){
        global $wpdb;
        $table_name = self::get_coupons_table_name();
        $form_table_name = RGFormsModel::get_form_table_name();
        $sql = "SELECT s.id, s.is_active, s.form_id, s.meta, f.title as form_title
                FROM $table_name s
                INNER JOIN $form_table_name f ON s.form_id = f.id";

        $results1 = $wpdb->get_results($sql, ARRAY_A);

        $sql2 = "SELECT s.id, s.is_active, s.form_id, s.meta, 'Any Form' as form_title
                FROM $table_name s
                WHERE s.form_id is null";

        $results2 = $wpdb->get_results($sql2, ARRAY_A);

        $results = array_merge($results1, $results2);

        $count = sizeof($results);
        for($i=0; $i<$count; $i++){
            $results[$i]["meta"] = maybe_unserialize($results[$i]["meta"]);
        }

        return $results;
    }

    public static function delete_feed($id){
        global $wpdb;
        $table_name = self::get_coupons_table_name();
        $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id=%s", $id));
    }

    public static function get_feed_by_form($form_id, $only_active = false){
        global $wpdb;
        $table_name = self::get_coupons_table_name();
        $active_clause = $only_active ? " AND is_active=1" : "";
        $sql = $wpdb->prepare("SELECT id, form_id, is_active, meta FROM $table_name WHERE form_id=%d $active_clause", $form_id);
        $results = $wpdb->get_results($sql, ARRAY_A);
        if(empty($results))
            return array();

        //Deserializing meta
        $count = sizeof($results);
        for($i=0; $i<$count; $i++){
            $results[$i]["meta"] = maybe_unserialize($results[$i]["meta"]);
        }
        return $results;
    }

    public static function get_feed_any_form($only_active = false){
        global $wpdb;
        $table_name = self::get_coupons_table_name();
        $active_clause = $only_active ? " AND is_active=1" : "";
        $sql = "SELECT id, 0, is_active, meta FROM {$table_name} WHERE form_id is null OR form_id = 0";
        $results = $wpdb->get_results($sql, ARRAY_A);
        if(empty($results))
            return array();

        //Deserializing meta
        $count = sizeof($results);
        for($i=0; $i<$count; $i++){
            $results[$i]["meta"] = maybe_unserialize($results[$i]["meta"]);
        }
        return $results;
    }
    
    public static function get_active_feeds(){
        global $wpdb;
        $table_name = self::get_coupons_table_name();
        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE is_active=%d", 1);
        $results = $wpdb->get_results($sql, ARRAY_A);
        if(empty($results))
            return array();

        $count = sizeof($results);
        for($i=0; $i<$count; $i++){
            $results[$i]["meta"] = maybe_unserialize($results[$i]["meta"]);
        }

        return $results;
    }
    
    public static function get_feed($id){
        global $wpdb;
        $table_name = self::get_coupons_table_name();
        $sql = $wpdb->prepare("SELECT id, form_id, is_active, meta FROM $table_name WHERE id=%d", $id);
        $results = $wpdb->get_results($sql, ARRAY_A);
        if(empty($results))
            return array();

        $result = $results[0];
        $result["meta"] = maybe_unserialize($result["meta"]);

        if(empty($result["form_id"])){
            $result["form_id"] = "0";
        }

        return $result;
    }

    public static function update_feed($id, $form_id, $is_active, $setting){
        global $wpdb;
        $table_name = self::get_coupons_table_name();
        $setting = maybe_serialize($setting);
        if($id == 0){
            //insert
            if(empty($form_id))
                $wpdb->insert($table_name, array("is_active"=> $is_active, "meta" => $setting), array("%d", "%s"));
            else
                $wpdb->insert($table_name, array("form_id" => $form_id, "is_active"=> $is_active, "meta" => $setting), array("%d", "%d", "%s"));

            $id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
        }
        else{
            //update
            if(empty($form_id))
                $form_id = 'NULL';
            else
                $form_id = absint($form_id);

            $wpdb->query($wpdb->prepare("UPDATE {$table_name} SET form_id={$form_id}, is_active=%d, meta=%s WHERE id=%d", $is_active, $setting, $id));
        }

        return $id;
    }

    public static function drop_tables(){
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS " . self::get_coupons_table_name());
    }
}
?>
