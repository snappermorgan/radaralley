<?php
/*
Plugin Name: Gravity Forms Coupons Add-On
Plugin URI: http://www.gravityforms.com
Description: Enables Gravity Forms administrators to create coupon codes that can be applied to products, services or subscriptions when used in conjuction with a payment add-on such as PayPal and Authorize.net
Version: 1.1
Author: rocketgenius
Author URI: http://www.rocketgenius.com

------------------------------------------------------------------------
Copyright 2009 rocketgenius

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

add_action('init',  array('GFCoupons', 'init'));
register_activation_hook( __FILE__, array("GFCoupons", "add_permissions"));

class GFCoupons {

    private static $path = "gravityformscoupons/coupons.php";
    private static $url = "http://www.gravityforms.com";
    private static $slug = "gravityformscoupons";
    private static $version = "1.1";
    private static $min_gravityforms_version = "1.7.5";
    private static $_configs = array();

    //Plugin starting point. Will load appropriate files
    public static function init(){

        if( ! class_exists( 'GFForms' ) )
            return;

        //loading translations
        load_plugin_textdomain('gravityformscoupons', FALSE, '/gravityformscoupons/languages' );

        if(RG_CURRENT_PAGE == "plugins.php"){

            add_action('after_plugin_row_' . self::$path, array('GFCoupons', 'plugin_row') );

           //force new remote request for version info on the plugin page
            self::flush_version_info();
        }

        if(!self::is_gravityforms_supported()){
           return;
        }

        if(is_admin()){

            //runs the setup when version changes
            self::setup();

            //automatic upgrade hooks
            add_filter("transient_update_plugins", array('GFCoupons', 'check_update'));
            add_filter("site_transient_update_plugins", array('GFCoupons', 'check_update'));
            add_action('install_plugins_pre_plugin-information', array('GFCoupons', 'display_changelog'));

            //integrating with Members plugin
            if(function_exists('members_get_capabilities'))
                add_filter('members_get_capabilities', array("GFCoupons", "members_get_capabilities"));

            //creates the subnav left menu
            add_filter("gform_addon_navigation", array('GFCoupons', 'create_menu'));

            // add coupon field to form editor
            add_filter("gform_add_field_buttons", array('GFCoupons', 'coupon_add_field'));
            add_filter("gform_field_type_title", array('GFCoupons', 'coupon_assign_title'));
            add_action("gform_editor_js", array('GFCoupons', 'coupon_gform_editor_js'));
            add_action("gform_editor_js_set_default_values", array('GFCoupons', 'set_defaults'));

            if(self::is_coupons_page()){

                //loading data lib
                require_once(self::get_base_path() . "/data.php");

                //enqueueing sack for AJAX requests
                wp_enqueue_script(array("sack"));

                //loading upgrade lib
                require_once("plugin-upgrade.php");

                //loading Gravity Forms tooltips
                require_once(GFCommon::get_base_path() . "/tooltips.php");
                add_filter('gform_tooltips', array('GFCoupons', 'tooltips'));

            }
            else if(in_array(RG_CURRENT_PAGE, array("admin-ajax.php"))){

                //loading data class
                require_once(self::get_base_path() . "/data.php");

                add_action('wp_ajax_rg_update_feed_active', array('GFCoupons', 'update_feed_active'));
                add_action('wp_ajax_gf_select_coupons_form', array('GFCoupons', 'select_coupons_form'));

                add_action('wp_ajax_gf_apply_coupon_code', array('GFCoupons', 'apply_coupon_code'));
                add_action('wp_ajax_nopriv_gf_apply_coupon_code', array('GFCoupons', 'apply_coupon_code'));

            }
            else if(RGForms::get("page") == "gf_settings"){
                RGForms::add_settings_page("Coupons", array("GFCoupons", "settings_page"), self::get_base_url() . "/images/coupon_wordpress_icon_32.png");
            }

            else{
                // ManageWP premium update filters
                add_filter( 'mwp_premium_update_notification', array('GFCoupons', 'premium_update_push') );
                add_filter( 'mwp_premium_perform_update', array('GFCoupons', 'premium_update') );
            }
        }
        else{
            //enqueueing sack for AJAX requests
            wp_enqueue_script(array("sack"));

            //handling post submission.
            add_action("gform_after_submission", array('GFCoupons', 'update_usage_count'), 10, 2);

            add_filter('gform_validation',array("GFCoupons", "validate_coupons"), 10, 4);

            add_action("gform_enqueue_scripts", array("GFCoupons", "enqueue_coupon_script"), 10, 2);

            add_filter("gform_preview_styles", array('GFCoupons', 'enqueue_preview_style'), 10, 2);
        }

        add_action("gform_field_input", array('GFCoupons', 'coupon_input'), 10, 5);

		add_filter("gform_product_info", array('GFCoupons', 'add_discounts'), 5, 3);
    }


    //--------------   Automatic upgrade ---------------------------------------------------

    //Integration with ManageWP
    public static function premium_update_push( $premium_update ){

        if( !function_exists( 'get_plugin_data' ) )
            include_once( ABSPATH.'wp-admin/includes/plugin.php');

        $update = GFCommon::get_version_info();
        $update = $update["offerings"][self::$slug];
        if( $update["is_available"] == true && version_compare(self::$version, $update["version"], '<') ){
            $plugin_data = get_plugin_data( __FILE__ );
            $plugin_data['type'] = 'plugin';
            $plugin_data['slug'] = self::$path;
            $plugin_data['new_version'] = isset($update['version']) ? $update['version'] : false ;
            $premium_update[] = $plugin_data;
        }

        return $premium_update;
    }

    //Integration with ManageWP
    public static function premium_update( $premium_update ){

        if( !function_exists( 'get_plugin_data' ) )
            include_once( ABSPATH.'wp-admin/includes/plugin.php');

        $update = GFCommon::get_version_info();
        $update = $update["offerings"][self::$slug];
        if( $update["is_available"] == true && version_compare(self::$version, $update["version"], '<') ){
            $plugin_data = get_plugin_data( __FILE__ );
            $plugin_data['slug'] = self::$path;
            $plugin_data['type'] = 'plugin';
            $plugin_data['url'] = isset($update["url"]) ? $update["url"] : false; // OR provide your own callback function for managing the update

            array_push($premium_update, $plugin_data);
        }
        return $premium_update;
    }

    public static function flush_version_info(){
        require_once("plugin-upgrade.php");
        RGCouponUpgrade::set_version_info(false);
    }

    public static function plugin_row(){
        if(!self::is_gravityforms_supported()){
            $message = sprintf(__("Gravity Forms " . self::$min_gravityforms_version . " is required. Activate it now or %spurchase it today!%s"), "<a href='http://www.gravityforms.com'>", "</a>");
            RGCouponUpgrade::display_plugin_message($message, true);
        }
        else{
            $version_info = RGCouponUpgrade::get_version_info(self::$slug, self::get_key(), self::$version);

            if(!$version_info["is_valid_key"]){
                $new_version = version_compare(self::$version, $version_info["version"], '<') ? __('There is a new version of Gravity Forms Coupons Add-On available.', 'gravityformscoupons') .' <a class="thickbox" title="Gravity Forms Coupons Add-On" href="plugin-install.php?tab=plugin-information&plugin=' . self::$slug . '&TB_iframe=true&width=640&height=808">'. sprintf(__('View version %s Details', 'gravityformscoupons'), $version_info["version"]) . '</a>. ' : '';
                $message = $new_version . sprintf(__('%sRegister%s your copy of Gravity Forms to receive access to automatic upgrades and support. Need a license key? %sPurchase one now%s.', 'gravityformscoupons'), '<a href="admin.php?page=gf_settings">', '</a>', '<a href="http://www.gravityforms.com">', '</a>') . '</div></td>';
                RGCouponUpgrade::display_plugin_message($message);
            }
        }
    }

    //Displays current version details on Plugin's page
    public static function display_changelog(){
        if($_REQUEST["plugin"] != self::$slug)
            return;

        //loading upgrade lib
        require_once("plugin-upgrade.php");

        RGCouponUpgrade::display_changelog(self::$slug, self::get_key(), self::$version);
    }

    public static function check_update($update_plugins_option){
        require_once("plugin-upgrade.php");

        return RGCouponUpgrade::check_update(self::$path, self::$slug, self::$url, self::$slug, self::get_key(), self::$version, $update_plugins_option);
    }

    private static function get_key(){
        if(self::is_gravityforms_supported())
            return GFCommon::get_key();
        else
            return "";
    }
    ///--------------------------------------------------------------------------------------

    public static function enqueue_preview_style($styles, $form) {
        $coupon_fields = GFCommon::get_fields_by_type($form, array('coupon'));
        if (false === empty ($coupon_fields)){
            wp_register_style("coupon_style", self::get_base_url() . "/css/gcoupons.css", null, self::$version);
            $styles[] = "coupon_style";
        }

        return $styles;
    }

    public static function enqueue_coupon_script($form, $is_ajax){
        $coupon_fields = GFCommon::get_fields_by_type($form, array("coupon"));

        //ignore forms that don't have coupon fields
        if(empty($coupon_fields))
            return;

        wp_enqueue_script("coupon_script", self::get_base_url() . "/js/coupons.js", array("jquery", "gform_json"), self::$version);
        wp_enqueue_script("gforms_json");
        wp_enqueue_style("coupon_style", self::get_base_url() . "/css/gcoupons.css", null, self::$version);

        //printing out ajaxurl global variable to be used in ajax calls
        ?>
        <script type="text/javascript">
            if(!window["ajaxurl"])
                var ajaxurl ="<?php echo admin_url("admin-ajax.php")?>";
        </script>
        <?php

    }

    //Returns true if the current page is an Feed pages. Returns false if not
    private static function is_coupons_page(){
        $current_page = trim(strtolower(rgget("page")));
        $coupons_pages = array("gf_coupons");

        return in_array($current_page, $coupons_pages);
    }

    public static function update_feed_active(){
        check_ajax_referer('rg_update_feed_active','rg_update_feed_active');
        $id = rgpost("feed_id");
        $feed = GFCouponsData::get_feed($id);
        GFCouponsData::update_feed($id, $feed["form_id"], rgpost("is_active"), $feed["meta"]);
    }

    //Creates or updates database tables. Will only run when version changes
    private static function setup(){

        if(get_option("gf_coupons_version") != self::$version){
            //loading data lib
            require_once(self::get_base_path() . "/data.php");

            GFCouponsData::update_table();
        }

        update_option("gf_coupons_version", self::$version);
    }

    //Adds feed tooltips to the list of tooltips
    public static function tooltips($tooltips){
        $coupons_tooltips = array(
            "coupons_gravity_form" => "<h6>" . __("Gravity Form", "gravityformscoupons") . "</h6>" . __("Select the Gravity Form you would like to integrate with Coupons.", "gravityformscoupons"),
            "coupon_name" => "<h6>" . __("Coupon Name", "gravityformscoupons") . "</h6>" . __("Enter coupon name.", "gravityformscoupons"),
            "coupon_code" => "<h6>" . __("Coupon Code", "gravityformscoupons") . "</h6>" . __("Enter the value users should enter to apply this coupon to the form total.", "gravityformscoupons"),
            "coupon_type" => "<h6>" . __("Coupon Type", "gravityformscoupons") . "</h6>" . __("Select which coupon type should be used. Flat (\$) or Percentage.", "gravityformscoupons"),
            "coupon_amount" => "<h6>" . __("Coupon Amount", "gravityformscoupons") . "</h6>" . __("Enter the amount to be deducted from the form total.", "gravityformscoupons"),
            "coupon_start" => "<h6>" . __("Start Date", "gravityformscoupons") . "</h6>" . __("Enter the date when the coupon should start.", "gravityformscoupons"),
            "coupon_expiration" => "<h6>" . __("Expiration Date", "gravityformscoupons") . "</h6>" . __("Enter the date when the coupon should expire.", "gravityformscoupons"),
            "coupon_limit" => "<h6>" . __("Usage Limit", "gravityformscoupons") . "</h6>" . __("Enter the number of times coupon code can be used.", "gravityformscoupons"),
            "coupon_options" => "<h6>" . __("Is Stackable", "gravityformscoupons") . "</h6>" . __("When the \"Is Stackable\" option is selected, this coupon code will be allowed to be used in conjunction with another coupon code.", "gravityformscoupons")
        );
        return array_merge($tooltips, $coupons_tooltips);
    }

    //Creates Coupons left nav menu under Forms
    public static function create_menu($menus){

        // Adding submenu if user has access
        $permission = self::has_access("gravityforms_coupons");
        if(!empty($permission))
            $menus[] = array("name" => "gf_coupons", "label" => __("Coupons", "gravityformscoupons"), "callback" =>  array("GFCoupons", "coupons_page"), "permission" => $permission);

        return $menus;
    }

    public static function coupon_input($input, $field, $value, $lead_id, $form_id){

        if($field["type"] != "coupon"){
            return $input;
		}

        if(RG_CURRENT_VIEW == "entry") {
        	$input = "<input type='hidden' id='input_" . $field["id"] . "' name='input_" . $field["id"] . "' value='" . $value . "' />";
            return $input . "<br/>" . __("Coupon fields are not editable", "gravityformscoupons");
		}

        $coupons_detail = rgpost("gf_coupons_{$form_id}");
        $coupon_codes = empty($coupons_detail) ? "" : rgpost("input_{$field["id"]}");
        $input = "<div class='ginput_container' id='gf_coupons_container_{$form_id}'>" .
                    "<input id='gf_coupon_code_{$form_id}' class='gf_coupon_code' onkeyup='DisableApplyButton(" . $form_id . ");' onchange='DisableApplyButton(" . $form_id . ");' type='text' " . disabled(is_admin(), true, false) . " " . GFCommon::get_tabindex() . "/>" .
                    "<input type='button' disabled='true' onclick='ApplyCouponCode(" . $form_id . ");' value='" . __("Apply", "gravityformscoupons") . "' id='gf_coupon_button' class='button' " . disabled(is_admin(), true, false) . " " . GFCommon::get_tabindex() . "/> ".
                    "<img style='display:none;' id='gf_coupon_spinner' src='" . self::get_base_url()  . "/images/spinner.gif' alt='" . __("please wait", "gravityformscoupons") . "'/>" .

                    "<div id='gf_coupon_info'></div>" .

                    "<input type='hidden' id='gf_coupon_codes_{$form_id}' name='input_{$field["id"]}' value='" . esc_attr($coupon_codes) . "' />" .
                    "<input type='hidden' id='gf_total_no_discount_{$form_id}'/>".
                    "<input type='hidden' id='gf_coupons_{$form_id}' name='gf_coupons_{$form_id}' value='" . esc_attr($coupons_detail) . "' />" .
                 "</div>";

        return $input;
    }

    public static function coupon_add_field($field_groups){

        foreach($field_groups as &$group){
            if($group["name"] == "pricing_fields"){
                $group["fields"][] = array("class"=>"button", "value" => __("Coupon", "gravityformscoupons"), "onclick" => "StartAddCouponField('coupon');");
                break;
            }
        }
        return $field_groups;
    }

    public static function coupon_assign_title($title){
        if($title == "coupon")
            return __("Coupon", "gravityformscoupons");
        else
            return $title;
    }

    public static function set_defaults(){

        ?>
        case "coupon" :
            field.label = "<?php _e("Coupon", "gravityformscoupons"); ?>";//setting the default field label
        break;
        <?php
    }

    public static function coupon_gform_editor_js(){
        ?>

        <script type='text/javascript'>

            function StartAddCouponField(type){

                if(GetFieldsByType(["product"]).length <= 0){
                    alert("<?php _e("You must add a product field to the form first", "gravityformscoupons") ?>");
                    return;
                }
                else
                {
                    StartAddField(type);
                }
            }

            jQuery(document).ready(function($) {

                // from forms.js; can add custom "tos_setting" as well
                fieldSettings["coupon"] = ".label_setting, .admin_label_setting, .description_setting, .error_message_setting, .css_class_setting, .conditional_logic_field_setting"; //this will show all the fields of the Paragraph Text field minus a couple that I didn't want to appear.

                //binding to the load field settings event to initialize the checkbox
                $(document).bind("gform_load_field_settings", function(event, field, form){
                    jQuery("#field_coupon").attr("checked", field["field_coupon"] == true);
                    $("#field_coupon_value").val(field["coupon"]);
                });
            });

        </script>
        <?php
    }

    public static function get_coupon_field($form){
        $coupons = GFCommon::get_fields_by_type($form, array("coupon"));
        return count($coupons) > 0 ? $coupons[0] : false;
    }

	public static function get_submitted_coupon_codes($form){
		$coupon_field = self::get_coupon_field($form);

		if(!$coupon_field || rgempty("input_{$coupon_field["id"]}"))
			return false;

		$coupons = explode(",", rgpost("input_{$coupon_field["id"]}"));
		$coupons = array_map("trim", $coupons);
		return $coupons;
	}

	public static function get_entry_coupon_codes($form, $entry){

		$coupon_field = self::get_coupon_field($form);

		if(!$coupon_field || rgempty($coupon_field["id"], $entry) )
			return false;

		$coupons = explode(",", rgar( $entry, $coupon_field['id'] ) );
		$coupons = array_map("trim", $coupons);

		return $coupons;
	}

    public static function validate_coupons($validation_result){

        //if form has already failed validation, abort
        if($validation_result["is_valid"] == false)
            return $validation_result;

        //if coupon field is hidden, abort
        $form = $validation_result["form"];
        if(self::is_coupon_visible($form) == false)
            return $validation_result;

        //if there are no coupon codes to validate, abort
        $coupon_codes = self::get_submitted_coupon_codes($form);
        if(!$coupon_codes)
            return $validation_result;

        $existing_coupon_codes = "";
        $message = "";

        foreach($coupon_codes as $coupon_code){

            $config = self::get_config($form, $coupon_code);
            if(!$config){
               $message = __("Coupon code: " . $coupon_code . " is invalid.", "gravityformscoupons");
               break;
            }

            $can_apply = self::can_apply_coupon($coupon_code, $existing_coupon_codes, $config, $message, $form);
            if($can_apply){
                $existing_coupon_codes .= empty($existing_coupon_codes) ? $coupon_code : $coupon_code . "," . $existing_coupon_codes;
            }
            else{
                break;
            }
        }

        $validation_result = empty($message) ? $validation_result : self::set_validation_result($validation_result, $form, $message);
        return $validation_result;

    }

    public static function set_validation_result($validation_result, $form, $message){

        foreach($validation_result["form"]["fields"] as &$field)
        {
            if($field["type"] == "coupon")
            {
                $field["failed_validation"] = true;
                $field["validation_message"] = $message;
                break;
            }

        }
        $validation_result["is_valid"] = false;
        return $validation_result;

    }

    public static function is_coupon_visible($form) {

        $is_visible = true;
        foreach($form["fields"] as &$field)
        {
            if($field["type"] == "coupon")
            {
                // if conditional is enabled, but the field is hidden, ignore conditional
                $is_visible = !RGFormsModel::is_field_hidden($form, $field, array());
                break;
            }

        }
        return $is_visible;

    }

    public static function coupons_page(){
        $view = rgget("view");
        if($view == "edit")
            self::edit_page(rgget("id"));
        else
            self::list_page();
    }

    //Displays the coupon feeds list page
    private static function list_page(){
        if(!self::is_gravityforms_supported()){
            die(__(sprintf("Coupons Add-On requires Gravity Forms %s. Upgrade automatically on the %sPlugin page%s.", self::$min_gravityforms_version, "<a href='plugins.php'>", "</a>"), "gravityformscoupons"));
        }

        if(rgpost("action") == "delete"){
            check_admin_referer("list_action", "gf_coupons_list");

            $id = absint(rgpost("action_argument"));
            GFCouponsData::delete_feed($id);
            ?>
            <div class="updated fade" style="padding:6px"><?php _e("Coupon deleted.", "gravityformscoupons") ?></div>
            <?php
        }
        else if (!rgempty("bulk_action")){
            check_admin_referer("list_action", "gf_coupons_list");
            $selected_feeds = rgpost("feed");
            if(is_array($selected_feeds)){
                foreach($selected_feeds as $feed_id)
                    GFCouponsData::delete_feed($feed_id);
            }
            ?>
            <div class="updated fade" style="padding:6px"><?php _e("Coupons deleted.", "gravityformscoupons") ?></div>
            <?php
        }

        ?>
        <style type="text/css">
        table.widefat.gf_coupons_list td.column-title,
        table.widefat.gf_coupons_list td.column-active,
        table.widefat.gf_coupons_list td.column-date {
        	padding-top: 8px !important;
        }
        </style>
        <div class="wrap">
            <img alt="<?php _e("Coupons", "gravityformscoupons") ?>" src="<?php echo self::get_base_url()?>/images/coupon_wordpress_icon_32.png" style="float:left; margin:9px 7px 0 0;"/>
            <h2><?php _e("Coupons", "gravityformscoupons"); ?>
            <a class="button add-new-h2" href="admin.php?page=gf_coupons&view=edit&id=0"><?php _e("Add New", "gravityformscoupons") ?></a>
            </h2>

            <form id="feed_form" method="post">
                <?php wp_nonce_field('list_action', 'gf_coupons_list') ?>
                <input type="hidden" id="action" name="action"/>
                <input type="hidden" id="action_argument" name="action_argument"/>

                <div class="tablenav">
                    <div class="alignleft actions" style="padding:8px 0 7px 0;">
                        <label class="hidden" for="bulk_action"><?php _e("Bulk action", "gravityformscoupons") ?></label>
                        <select name="bulk_action" id="bulk_action">
                            <option value=''> <?php _e("Bulk action", "gravityformscoupons") ?> </option>
                            <option value='delete'><?php _e("Delete", "gravityformscoupons") ?></option>
                        </select>
                        <input type="submit" class="button" value="<?php _e("Apply", "gravityformscoupons") ?>" onclick="if( jQuery('#bulk_action').val() == 'delete' && !confirm('<?php  echo __("Delete selected coupons? \'Cancel\' to stop, \'OK\' to delete.", "gravityformscoupons") ?>')) { return false; } return true;" />
                    </div>
                </div>
                <table class="widefat fixed gf_coupons_list" cellspacing="0">
                    <thead>
                        <tr>
                            <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
                            <th scope="col" id="active" class="manage-column check-column"></th>
                            <th scope="col" class="manage-column"><?php _e("Title", "gravityformscoupons") ?></th>
                            <th scope="col" class="manage-column"><?php _e("Form", "gravityformscoupons") ?></th>
                            <th scope="col" class="manage-column"><?php _e("Amount", "gravityformscoupons") ?></th>
                            <th scope="col" class="manage-column"><?php _e("Usage Limit", "gravityformscoupons") ?></th>
                            <th scope="col" class="manage-column"><?php _e("Usage Count", "gravityformscoupons") ?></th>
                            <th scope="col" class="manage-column"><?php _e("Expires", "gravityformscoupons") ?></th>
                        </tr>
                    </thead>

                    <tfoot>
                        <tr>
                            <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
                            <th scope="col" id="active" class="manage-column check-column"></th>
                            <th scope="col" class="manage-column"><?php _e("Title", "gravityformscoupons") ?></th>
                            <th scope="col" class="manage-column"><?php _e("Form", "gravityformscoupons") ?></th>
                            <th scope="col" class="manage-column"><?php _e("Amount", "gravityformscoupons") ?></th>
                            <th scope="col" class="manage-column"><?php _e("Usage Limit", "gravityformscoupons") ?></th>
                            <th scope="col" class="manage-column"><?php _e("Usage Count", "gravityformscoupons") ?></th>
                            <th scope="col" class="manage-column"><?php _e("Expires", "gravityformscoupons") ?></th>
                        </tr>
                    </tfoot>

                    <tbody class="list:user user-list">
                        <?php
                        $feeds = GFCouponsData::get_feeds();
                        if(is_array($feeds) && sizeof($feeds) > 0){
                            foreach($feeds as $feed){
                                ?>
                                <tr class='author-self status-inherit' valign="top">
                                    <th scope="row" class="check-column"><input type="checkbox" name="feed[]" value="<?php echo $feed["id"] ?>"/></th>
                                    <td class="column-active"><img src="<?php echo self::get_base_url() ?>/images/active<?php echo intval($feed["is_active"]) ?>.png" alt="<?php echo $feed["is_active"] ? __("Active", "gravityformscoupons") : __("Inactive", "gravityformscoupons");?>" title="<?php echo $feed["is_active"] ? __("Active", "gravityformscoupons") : __("Inactive", "gravityformscoupons");?>" onclick="ToggleActive(this, <?php echo $feed['id'] ?>); " /></td>

                                    <td class="column-title">
                                        <a href="admin.php?page=gf_coupons&view=edit&id=<?php echo $feed["id"] ?>" title="<?php _e("Edit", "gravityformscoupons") ?>"><?php echo $feed["meta"]["coupon_name"] . " (" . $feed["meta"]["coupon_code"] . ")"; ?></a>
                                        <div class="row-actions">
                                            <span class="edit">
                                            <a title="Edit this setting" href="admin.php?page=gf_coupons&view=edit&id=<?php echo $feed["id"] ?>" title="<?php _e("Edit", "gravityformscoupons") ?>"><?php _e("Edit", "gravityformscoupons") ?></a>
                                            |
                                            </span>

                                            <span class="edit">
                                            <a title="<?php _e("Delete", "gravityformscoupons") ?>" href="javascript: if(confirm('<?php _e("Delete this coupon? ", "gravityformscoupons") ?> <?php _e("\'Cancel\' to stop, \'OK\' to delete.", "gravityformscoupons") ?>')){ DeleteSetting(<?php echo $feed["id"] ?>);}"><?php _e("Delete", "gravityformscoupons")?></a>

                                            </span>
                                        </div>
                                    </td>
                                    <td class="column-title"><?php echo empty($feed["form_title"]) ? __("Any Form", "gravityformscoupons") : $feed["form_title"]; ?></td>
                                    <td class="column-title"><?php echo $feed["meta"]["coupon_amount"]; ?></td>
                                    <td class="column-title"><?php echo empty($feed["meta"]["coupon_limit"]) ? "Unlimited" : $feed["meta"]["coupon_limit"]; ?></td>
                                    <td class="column-title"><?php echo empty($feed["meta"]["coupon_usage"]) ? 0 : $feed["meta"]["coupon_usage"]; ?></td>
                                    <td class="column-date"><?php echo empty($feed["meta"]["coupon_expiration"]) ? "Never Expires" : $feed["meta"]["coupon_expiration"];?></td>
                                </tr>
                                <?php
                            }
                        }
                        else{
                            ?>
                            <tr>
                                <td colspan="9" style="padding:20px;">
                                    <?php _e(sprintf("You don't have any Coupons configured. Let's go %screate one%s!", '<a href="admin.php?page=gf_coupons&view=edit&id=0">', "</a>"), "gravityformscoupons"); ?>
                                </td>
                            </tr>
                            <?php
                        }

                        ?>
                    </tbody>
                </table>
            </form>
        </div>
        <script type="text/javascript">
            function DeleteSetting(id){
                jQuery("#action_argument").val(id);
                jQuery("#action").val("delete");
                jQuery("#feed_form")[0].submit();
            }
            function ToggleActive(img, feed_id){
                var is_active = img.src.indexOf("active1.png") >=0
                if(is_active){
                    img.src = img.src.replace("active1.png", "active0.png");
                    jQuery(img).attr('title','<?php _e("Inactive", "gravityformscoupons") ?>').attr('alt', '<?php _e("Inactive", "gravityformscoupons") ?>');
                }
                else{
                    img.src = img.src.replace("active0.png", "active1.png");
                    jQuery(img).attr('title','<?php _e("Active", "gravityformscoupons") ?>').attr('alt', '<?php _e("Active", "gravityformscoupons") ?>');
                }

                jQuery.post(ajaxurl,{action:"rg_update_feed_active", rg_update_feed_active:"<?php echo wp_create_nonce("rg_update_feed_active") ?>",
                                    feed_id: feed_id,
                                    is_active: is_active ? 0 : 1,
                                    cookie: encodeURIComponent(document.cookie)});

                return true;
            }

        </script>
        <?php
    }


    public static function settings_page(){

        if(rgpost("uninstall")){
            check_admin_referer("uninstall", "gf_coupons_uninstall");
            self::uninstall();

            ?>
            <div class="updated fade" style="padding:20px;"><?php _e(sprintf("Gravity Forms Coupons Add-On has been successfully uninstalled. It can be re-activated from the %splugins page%s.", "<a href='plugins.php'>","</a>"), "gravityformscoupons")?></div>
            <?php
            return;
        }
        ?>

        <form action="" method="post">
            <?php wp_nonce_field("uninstall", "gf_coupons_uninstall") ?>
            <?php if(GFCommon::current_user_can_any("gravityforms_coupons_uninstall")){ ?>
                <div class="hr-divider"></div>

                <h3><?php _e("Uninstall Coupons Add-On", "gravityformscoupons") ?></h3>
                <div class="delete-alert"><?php _e("Warning! This operation deletes ALL Coupons.", "gravityformscoupons") ?>
                    <?php
                    $uninstall_button = '<input type="submit" name="uninstall" value="' . __("Uninstall Coupons Add-On", "gravityformspaypalpro") . '" class="button" onclick="return confirm(\'' . __("Warning! ALL Coupons will be deleted. This cannot be undone. \'OK\' to delete, \'Cancel\' to stop", "gravityformscoupons") . '\');"/>';
                    echo apply_filters("gform_coupons_uninstall_button", $uninstall_button);
                    ?>
                </div>
            <?php } ?>
        </form>
        <?php
    }

    private static function edit_page(){
        require_once(GFCommon::get_base_path() . "/currency.php");
        wp_enqueue_script(array("jquery-ui-datepicker"));
        ?>
        <link rel="stylesheet" href="<?php echo GFCommon::get_base_url() ?>/css/admin.css" />

        <style type="text/css">

            .gf_coupons_invalid_form {
            	margin-top: 30px;
				background-color: #FFEBE8;
				border: 1px solid #CC0000;
				padding: 10px;
				width: 600px;}

            #coupons_form_container .gforms_form_settings {
                margin: 0px;
            }
            .coupon_validation_error {
                background-color: #FFDFDF;
                border: 1px dotted #C89797;
                margin-bottom: 6px;
                margin-top: 4px;
                padding-bottom: 6px;
                padding-top: 6px;
            }
            table.gforms_form_settings tr td img.ui-datepicker-trigger {
	            position: relative;
	            top: 4px;
            }

        </style>
        <script type="text/javascript">
            var currency_config = <?php echo json_encode(RGCurrency::get_currency(GFCommon::get_currency()));?>;
            var form = Array();
            jQuery(document).ready(function(){
                jQuery(document).on('change', '.gf_format_money', function(){
                    var cur = new Currency(currency_config)
                    jQuery(this).val(cur.toMoney(jQuery(this).val()));
                });
                jQuery(document).on('change', '.gf_format_percentage', function(event){
                    var cur = new Currency(currency_config)
                    var value = cur.toNumber(jQuery(this).val()) ? cur.toNumber(jQuery(this).val()) + '%' : '';
                    jQuery(this).val( value );
                });
            });
        </script>

        <script type="text/javascript" src="<?php echo GFCommon::get_base_url() ?>/js/gravityforms.js"></script>
        <div class="wrap">
            <img alt="<?php _e("Coupons", "gravityformscoupons") ?>" style="margin: 9px 7px 0pt 0pt; float: left;" src="<?php echo self::get_base_url() ?>/images/coupon_wordpress_icon_32.png"/>


        <?php

        //getting setting id (0 when creating a new one)
        $id = !rgempty("coupons_setting_id") ? rgpost("coupons_setting_id") : absint(rgget("id"));

        ?>
        <h2><?php empty($id) ? _e("Add Coupon", "gravityformscoupons") : _e("Edit Coupon", "gravityformscoupons") ?></h2>


        <?php
        $config = empty($id) ? array("is_active" => true, "meta" => array()) : GFCouponsData::get_feed($id);

        $is_validation_error = false;

        //updating meta information
        if(!rgempty("gf_coupons_submit") && !rgblank("gf_coupons_form")){

            $_POST;
            $config["form_id"] = rgpost("gf_coupons_form");

            $config["meta"]["coupon_name"] = rgpost("gf_coupon_name");
            $config["meta"]["coupon_code"] = strtoupper(rgpost("gf_coupon_code"));
            $config["meta"]["coupon_type"] = rgpost("gf_coupon_type");
            $config["meta"]["coupon_amount"] = rgpost("gf_coupon_amount");

            $config["meta"]["coupon_start"] = rgpost("gf_coupon_start");
            $config["meta"]["coupon_expiration"] = rgpost("gf_coupon_expiration");
            $config["meta"]["coupon_limit"] = rgpost("gf_coupon_limit");
            $config["meta"]["coupon_stackable"] = rgpost("gf_coupon_stackable");


            if(empty($config["meta"]["coupon_name"]) || empty($config["meta"]["coupon_code"]) || empty($config["meta"]["coupon_amount"])){
                $is_validation_error = true;
            }
            
            $duplicate_coupon_code = self::can_create_coupon($config,GFCouponsData::get_active_feeds());
            
            if($duplicate_coupon_code)
                $is_validation_error = true;

            if(!$is_validation_error){

                $id = GFCouponsData::update_feed($id, $config["form_id"], $config["is_active"], $config["meta"]);
                ?>
                <div class="updated fade" style="padding:6px"><?php echo sprintf(__("Coupon Updated. %sback to list%s", "gravityformscoupons"), "<a href='?page=gf_coupons'>", "</a>") ?></div>
                <input type="hidden" name="coupons_setting_id" value="<?php echo $id ?>"/>
                <?php

            }
        }

        require_once(GFCommon::get_base_path() . "/currency.php");
        $currency = RGCurrency::get_currency(GFCommon::get_currency());
        $currency_symbol = $currency['symbol_left'];

        ?>
            <form method="post" action="">
                <input type="hidden" name="coupons_setting_id" value="<?php echo $id ?>"/>

                <?php
                    if($is_validation_error){
                        ?>
                        <div class="error below-h2">
                            <p><?php _e('There was an issue saving your coupon. Please address the errors below and try again.'); ?></p>
                        </div>
                        <?php
                    }
                ?>
                <br/>
                <div id="coupons_form_container" valign="top" >
                    <table class="gforms_form_settings" style="width:600px;">
                    	<tr>
							<td colspan="2"><h4 class="gf_settings_subgroup_title"><?php _e("Applies to Which Form?", "gravityformscoupons")?></h4></td>
						</tr>
                        <tr>
                            <th>
                                <label for="gf_coupons_form">
                                    <?php _e("Gravity Form", "gravityformscoupons"); ?>
                                    <?php gform_tooltip("coupons_gravity_form") ?>
                                </label>
                            </th>
                            <td>
                                <select id="gf_coupons_form" name="gf_coupons_form" onchange="SelectForm(jQuery(this).val());" tabindex="1">
                                    <option value=""><?php _e("Select a Form", "gravityformscoupons"); ?> </option>
                                    <option value="0" <?php selected(rgar($config, "form_id"), "0") ?>><?php _e("Any Form", "gravityformscoupons"); ?> </option>
                                    <?php
                                    $forms = RGFormsModel::get_forms();
                                    foreach($forms as $form){
                                        $selected = absint($form->id) == rgar($config,"form_id") ? "selected='selected'" : "";
                                        ?>
                                        <option value="<?php echo absint($form->id) ?>"  <?php echo $selected ?>><?php echo esc_html($form->title) ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                &nbsp;&nbsp;
                                <img src="<?php echo GFCoupons::get_base_url() ?>/images/loading.gif" id="coupons_wait" style="display: none;"/>
                            </td>
                        </tr>

                    </table>

                </div>

                <div id="gf_coupons_invalid_coupon_form" class="gf_coupons_invalid_form margin_vertical_10"  style="display:none;" >
                    <?php _e("The form selected does not have any Coupon fields. Please add a Coupon field to the form and try again.", "gravityformscoupons") ?>
                </div>

                <div id="coupons_field_group" valign="top" <?php echo rgblank(rgar($config, "form_id")) ? "style='display:none;'" : "" ?>>

                    <table class="gforms_form_settings">
                    	<tr>
							<td colspan="2"><h4 class="gf_settings_subgroup_title"><?php _e("Coupon Basics", "gravityformscoupons")?></h4></td>
						</tr>

                        <tr <?php echo ($is_validation_error && empty($config["meta"]["coupon_name"]) ? "class=\"coupon_validation_error\"" : "" )?> >
                            <th >
                                <label for="gf_coupon_name">
                                    <?php _e("Coupon Name", "gravityformscoupons"); ?>
                                    <span class="description">(<?php _e("required", "gravityformscoupons")?>)</span>
                                    <?php gform_tooltip("coupon_name") ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="gf_coupon_name" id="gf_coupon_name" class="fieldwidth-3" value="<?php echo rgar($config["meta"],"coupon_name") ?>" tabindex="2" />
                            </td>

                        </tr>



                        <tr <?php echo ($is_validation_error && empty($config["meta"]["coupon_code"]) ? "class=\"coupon_validation_error\"" : "" )?>>
                            <th>
                                <label for="gf_coupon_code">
                                    <?php _e("Coupon Code", "gravityformscoupons"); ?>
                                    <span class="description">(<?php _e("required", "gravityformscoupons")?>)</span>
                                    <?php gform_tooltip("coupon_code") ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="gf_coupon_code" id="gf_coupon_code" class="fieldwidth-3" value="<?php echo rgar($config["meta"],"coupon_code") ?>" tabindex="3"/>
                            </td>
                        </tr>
                        
                        <tr <?php echo ($is_validation_error && $duplicate_coupon_code ? "class=\"coupon_validation_error\"" : "style='display:none;'" )?>>
                            <th colspan="2"><?php _e("The Coupon Code entered is already in use. Please enter a unique Coupon Code and try again.", "gravityformscoupons") ?></th>
                        </tr>

                        <tr <?php echo ($is_validation_error && empty($config["meta"]["coupon_amount"]) ? "class=\"coupon_validation_error\"" : "" )?>>
                            <th>
                                <label for="gf_coupon_type" style="display:block;">
                                    <?php _e("Coupon Amount", "gravityformscoupons"); ?>
                                    <span class="description">(<?php _e("required", "gravityformscoupons")?>)</span>
                                    <?php gform_tooltip("coupon_type") ?>
                                </label>
                            </th>
                            <td>
                                <select id="gf_coupon_type" name="gf_coupon_type" onchange="SetCouponType(jQuery(this));" tabindex="4">
                                    <option value="flat" <?php echo rgars($config,"meta/coupon_type") == "flat" ? "selected='selected'" : "" ?> ><?php _e("Flat(" . $currency_symbol . ")", "gravityformscoupons") ?></option>
                                    <option value="percentage" <?php echo rgars($config,"meta/coupon_type") == "percentage" ? "selected='selected'" : "" ?> ><?php _e("Percentage(%)", "gravityformscoupons") ?></option>
                                </select>
                                &nbsp;
                                <input type="text" name="gf_coupon_amount" id="gf_coupon_amount" placeholder="<?php echo rgars($config,"meta/coupon_type") != "percentage" ? $currency_symbol . '1.00' : '1%'; ?>" class="<?php echo rgars($config,"meta/coupon_type") != "percentage" ? 'gf_format_money' : 'gf_format_percentage'; ?>" value="<?php echo rgar($config["meta"],"coupon_amount") ?>"  tabindex="5"/>
                            </td>
                        </tr>

                        <tr>
							<td colspan="2"><h4 class="gf_settings_subgroup_title"><?php _e("Coupon Options", "gravityformscoupons")?></h4></td>
						</tr>

                        <tr>
                            <th>
                                <label for="gf_coupon_start">
                                    <?php _e("Start Date", "gravityformscoupons"); ?>
                                    <?php gform_tooltip("coupon_start") ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="gf_coupon_start" class="datepicker" id="gf_coupon_start" data-value="<?php echo rgar($config["meta"],"coupon_start") ?>" value="<?php echo rgar($config["meta"],"coupon_start") ?>" tabindex="6"/>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="gf_coupon_expiration">
                                    <?php _e("Expiration Date", "gravityformscoupons"); ?>
                                    <?php gform_tooltip("coupon_expiration") ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="gf_coupon_expiration" class="datepicker" id="gf_coupon_expiration" data-value="<?php echo rgar($config["meta"],"coupon_expiration") ?>" value="<?php echo rgar($config["meta"],"coupon_expiration") ?>" tabindex="7"/>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="gf_coupon_limit">
                                    <?php _e("Usage Limit", "gravityformscoupons"); ?>
                                <?php gform_tooltip("coupon_limit") ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="gf_coupon_limit" id="gf_coupon_limit" class="fieldwidth-3" value="<?php echo rgar($config["meta"],"coupon_limit") ?>" tabindex="8"/>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="gf_coupon_stackable" class="inline">
                                    <?php _e("Is Stackable", "gravityformscoupons"); ?>
                                    <?php gform_tooltip("coupon_options") ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" name="gf_coupon_stackable" id="gf_coupon_stackable" value="1"  <?php echo rgars($config,"meta/coupon_stackable") ? "checked='checked'" : ""?> tabindex="9" />
                                <label for="gf_coupon_stackable" class="inline">
                                    <?php _e("Is Stackable", "gravityformscoupons"); ?>
                                </label>
                            </td>
                        </tr>

                    </table>

                    <div id="coupon_submit_container" class="margin_vertical_10" style="clear:both;">
                        <input type="submit" name="gf_coupons_submit" value="<?php echo empty($id) ? __("Save Coupon", "gravityformscoupons") : __("Update Coupon", "gravityformscoupons"); ?>" class="button-primary" tabindex="10"/>
                    </div>
                </div>
            </form>
        </div>
        <script type="text/javascript">

            jQuery(document).ready(function($){

                jQuery('.datepicker').each(
                    function (){
                        var image = "<?php echo self::get_base_url()?>/images/calendar.png";
                        jQuery(this).datepicker({showOn: "both", buttonImage: image, buttonImageOnly: true, dateFormat: "mm/dd/yy" });
                    }
                );
            });

            function SelectForm(formId){
                if(!formId){
                    jQuery("#coupons_field_group").slideUp();
                    return;
                }

                jQuery("#coupons_wait").show();
                jQuery("#coupons_field_group").slideUp();

                var mysack = new sack(ajaxurl);
                mysack.execute = 1;
                mysack.method = 'POST';
                mysack.setVar( "action", "gf_select_coupons_form" );
                mysack.setVar( "gf_select_coupons_form", "<?php echo wp_create_nonce("gf_select_coupons_form") ?>" );
                mysack.setVar( "form_id", formId);
                mysack.encVar( "cookie", document.cookie, false );
                mysack.onError = function() {jQuery("#coupons_wait").hide(); alert('<?php _e("Ajax error while selecting a form", "gravityformscoupons") ?>' )};
                mysack.runAJAX();

                return true;

            }

            function EndSelectForm(form_meta){
                //setting global form object
                form = form_meta;

                jQuery(".gf_coupons_invalid_form").hide();
                if(form != null && GetFieldsByType(["coupon"]).length == 0){
                    jQuery("#gf_coupons_invalid_coupon_form").show();
                    jQuery("#coupons_wait").hide();
                    return;
                }

                //Calling callback functions
                jQuery(document).trigger('couponsFormSelected', [form]);

                jQuery("#coupons_field_group").slideDown();
                jQuery("#coupons_wait").hide();
            }

            function GetFieldsByType(types){
                var fields = new Array();
                for(var i=0; i<form["fields"].length; i++){
                    if(IndexOf(types, form["fields"][i]["type"]) >= 0)
                        fields.push(form["fields"][i]);
                }
                return fields;
            }

            function IndexOf(ary, item){
                for(var i=0; i<ary.length; i++)
                    if(ary[i] == item)
                        return i;

                return -1;
            }

            function InsertVariable(element_id, callback, variable){
                if(!variable)
                    variable = jQuery('#' + element_id + '_variable_select').val();

                var messageElement = jQuery("#" + element_id);

                if(document.selection) {
                    // Go the IE way
                    messageElement[0].focus();
                    document.selection.createRange().text=variable;
                }
                else if(messageElement[0].selectionStart) {
                    // Go the Gecko way
                    obj = messageElement[0]
                    obj.value = obj.value.substr(0, obj.selectionStart) + variable + obj.value.substr(obj.selectionEnd, obj.value.length);
                }
                else {
                    messageElement.val(variable + messageElement.val());
                }

                jQuery('#' + element_id + '_variable_select')[0].selectedIndex = 0;

                if(callback && window[callback])
                    window[callback].call();
            }

            function SetCouponType(elem) {
                var type = elem.val();
                var formatClass = type == 'flat' ? 'gf_format_money' : 'gf_format_percentage';
                jQuery('#gf_coupon_type_options').removeClass('flat percentage').addClass(type);
                jQuery('#gf_coupon_amount').removeClass('gf_format_money gf_format_percentage').addClass(formatClass).trigger('change');
                var placeholderText = type == 'flat' ? '$1.00' : '1%';
                jQuery('#gf_coupon_amount').attr("placeholder",placeholderText);

            }

        </script>

        <?php

    }

    public static function can_create_coupon ($config,$active_coupons){
        
        if(!$active_coupons)
            return false;
        
        foreach($active_coupons as $coupon){
            if($config["meta"]["coupon_code"] == $coupon["meta"]["coupon_code"] && $config["id"] != $coupon["id"])
                return true;
        }
        return false;
        
    }
    
    public static function add_discounts($product_info, $form, $lead){

        //Only add discount once when form is submitted
        $coupon_codes =  self::get_entry_coupon_codes( $form, $lead );
        if(!$coupon_codes)
            return $product_info;

        $total = GFCommon::get_total($product_info);

        $coupons = self::get_coupons_by_codes($coupon_codes,$form);
        $discounts = self::get_discounts($coupons,$total,$discount_total);

        foreach($coupons as $coupon){

            $price = GFCommon::to_number($discounts[$coupon["code"]]["discount"]);
            $product_info["products"][$coupon["code"]] = array(
                                                            "name" => $coupon["name"],
                                                            "price" => -$price,
                                                            "quantity" => 1,
                                                            "options" => array(
                                                                            array(  "option_label" => __("Coupon Code:", "gravityformscoupons") . " " . $coupon["code"],
                                                                                    "price" => 0)
                                                                            )
                                                            );
        }

        return $product_info;
    }

    public static function update_usage_count($entry, $form){

        require_once(self::get_base_path() . "/data.php");

        $coupon_codes = self::get_submitted_coupon_codes($form);
        if(!$coupon_codes)
            return;

        $coupons = self::get_coupons_by_codes( $coupon_codes, $form );
        if( !is_array( $coupons ) )
            return;

        foreach($coupons as $coupon){

            $config = self::get_config($form,$coupon["code"]);
            $config["meta"]["coupon_usage"] = empty($config["meta"]["coupon_usage"]) ? 1 : $config["meta"]["coupon_usage"] + 1;
            GFCouponsData::update_feed($config["id"], empty($config["form_id"]) ? 0 : $config["form_id"], $config["is_active"], $config["meta"]);
        }

    }

    public static function apply_coupon_code(){

        $coupon_code = strtoupper($_POST["couponCode"]);
        $result;
        $invalid_reason = "";
        if(empty($coupon_code))
        {
            $invalid_reason = __("You must enter a value for coupon code.", "gravityformscoupons");
            $result = array("is_valid" => false,"invalid_reason" => $invalid_reason);
            die(GFCommon::json_encode($result));
        }

        $form_id = intval($_POST["formId"]);
        $existing_coupon_codes = $_POST["existing_coupons"];
        $total = $_POST["total"];


        //fields meta
        $form = RGFormsModel::get_form_meta($form_id);
        $config = self::get_config($form,$coupon_code);


        if(!$config || !$config["is_active"])
        {
            $invalid_reason = __("Invalid coupon.", "gravityformscoupons");
            $result = array("is_valid" => false,"invalid_reason" => $invalid_reason);
            die(GFCommon::json_encode($result));
        }

        $can_apply = self::can_apply_coupon($coupon_code, $existing_coupon_codes, $config, $invalid_reason, $form);

        if($can_apply)
        {
            $coupon_codes = empty($existing_coupon_codes) ? $coupon_code : $coupon_code . "," . $existing_coupon_codes;
            $coupons = self::get_coupons_by_codes(explode(",", $coupon_codes),$form);

            $coupons = self::sort_coupons($coupons);
            foreach($coupons as $c)
                $couponss[$c["code"]] = array("amount" => $c["amount"], "name" => $c["name"], "type" => $c["type"], "code" => $c["code"], "can_stack" => $c["can_stack"], "usage_count" => $c["usage_count"]);

            $result = array("is_valid" => $can_apply, "coupons" => $couponss, "invalid_reason" => $invalid_reason, "coupon_code" => $coupon_code);

            die(GFCommon::json_encode($result));
        }
        else
        {
            $result = array("is_valid" => false,"invalid_reason" => $invalid_reason);
            die(GFCommon::json_encode($result));
        }

    }

    public static function get_coupons_by_codes($codes,$form){

        if (!is_array($codes)){
            $codes = explode(",", $codes);
        }

        $coupons = array();
        foreach($codes as $coupon_code){
            $coupon_code = trim($coupon_code);

            $config = self::get_config($form, $coupon_code);
            if($config){
                $coupons[$coupon_code] = array("amount" => self::adjust_coupon_amount($config["meta"]["coupon_amount"]),"name" => $config["meta"]["coupon_name"], "type" => $config["meta"]["coupon_type"], "name" => $config["meta"]["coupon_name"], "code"=> $coupon_code, "can_stack" => $config["meta"]["coupon_stackable"] == 1 ? true : false ,  "usage_count" => empty($config["meta"]["coupon_usage"]) ? 0 : $config["meta"]["coupon_usage"]);
            }
         }

        if(empty($coupons))
            return false;

        return $coupons;
    }

    public static function get_coupon_by_code($config){

        if(empty($config))
            return false;

        $coupon = array("amount" => self::adjust_coupon_amount($config["meta"]["coupon_amount"]) ,"name" => $config["meta"]["coupon_name"], "type" => $config["meta"]["coupon_type"], "name" => $config["meta"]["coupon_name"], "code"=> $config["meta"]["coupon_code"], "can_stack" => $config["meta"]["coupon_stackable"] == 1 ? true : false ,  "usage_count" => empty($config["meta"]["coupon_usage"]) ? 0 : $config["meta"]["coupon_usage"], "limit" => 10);

        if(empty($coupon))
            return false;

        return $coupon;
    }

    public static function adjust_coupon_amount($amount){

        require_once(GFCommon::get_base_path() . "/currency.php");

        $currency = RGCurrency::get_currency(GFCommon::get_currency());
        $currency_symbol = $currency['symbol_left'];

        $amount = str_replace("%","",$amount);
        $amount = str_replace($currency_symbol,"",$amount);
        $amount = str_replace(" ","",$amount);
        $amount = GFCommon::to_number($amount);

        return $amount;

    }

    public static function get_discounts($coupons,&$total=0,&$discount_total) {

        require_once(GFCommon::get_base_path() . "/currency.php");
        $currency = RGCurrency::get_currency(GFCommon::get_currency());

        $coupons = self::sort_coupons($coupons);

        $discount_total = 0;

        foreach($coupons as $coupon) {

            $discount = 0;

            $discount = self::get_discount($coupon,$total);

            $discount_total += $discount;

            $total -= $discount;

            $discounts[$coupon["code"]]["code"] = $coupon["code"];
            $discounts[$coupon["code"]]["name"] = $coupon["name"];
            $discounts[$coupon["code"]]["discount"] = GFCommon::to_money($discount);
            $discounts[$coupon["code"]]["amount"] = $coupon["amount"];
            $discounts[$coupon["code"]]["type"] = $coupon["type"];

        }

        return $discounts;
    }

    private static function get_discount($coupon, $price) {
         if($coupon["type"] == 'flat') {
            $discount = GFCommon::to_number($coupon["amount"]);
        } else {
            $discount = $price * ($coupon["amount"] / 100);
        }

        return $price - $discount >= 0 ? $discount : $price;
    }

    private static function sort_coupons($coupons) {

        $sorted = array('cart_flat' => array(), 'cart_percentage' => array());

        foreach($coupons as $coupon) {

            $thing = $sorted['cart' . '_' . $coupon["type"]];

            if($coupon["type"] == 'percentage') {
                $sorted['cart_' . $coupon["type"]][$coupon["code"]] = $coupon;
            } else
            if($coupon["type"] != 'percentage') {
                $sorted['cart_' . $coupon["type"]][$coupon["code"]] = $coupon;
            }

        }

        if(!empty($sorted['cart_percentage']) && count($sorted['cart_' . $coupon["type"]]) > 0)
        {
            usort($sorted['cart_percentage'], array("GFCoupons", "array_cmp"));
        }


        return array_merge($sorted['cart_flat'], $sorted['cart_percentage']);
    }

    public static function array_cmp($a, $b){
        return strcmp($a["amount"], $b["amount"]);
    }

    public static function can_apply_coupon($coupon_code, $existing_coupon_codes, $config, &$invalid_reason="", $form){

        $coupon = self::get_coupon_by_code($config);
        if(!$coupon){
            $invalid_reason = __("Invalid coupon.", "gravityformscoupons");
            return false;
        }

        if(!self::is_valid($config,$invalid_reason))
            return false;


        //see if coupon code has already been applied, a code can only be applied once
        if (in_array($coupon_code, explode(",", $existing_coupon_codes)))
        {
            $invalid_reason = __("This coupon can't be applied more than once.", "gravityformscoupons");
            return false;
        }

        //checking if coupon can be stacked
        if(!is_array($existing_coupon_codes))
        {
            $existing_coupons = empty($existing_coupon_codes) ? array() : self::get_coupons_by_codes(explode(",", $existing_coupon_codes),$form);
        }
        foreach($existing_coupons as $existing_coupon){
            if(!$existing_coupon["can_stack"] || !$coupon["can_stack"]){
                $invalid_reason = __("This coupon can't be used in conjuction with other coupons you have already entered.", "gravityformscoupons");
                return false;
            }
        }

        return true;
    }

    public static function is_valid_code($code, $config, &$invalid_reason=""){
        $code = strtoupper($code);
        if(!self::is_valid($config,$invalid_reason))
            return false;

        $code_exists = false;

        if($config["meta"]["coupon_code"] == $code){
            $code_exists = true;
        }

        if(!$code_exists){
            $invalid_reason = __("Invalid coupon.", "gravityformscoupons");
            return false;
        }

        return true;
    }

    public static function is_valid($config, &$invalid_reason=""){

        $start_date = strtotime($config["meta"]["coupon_start"]); //start of the day
        $end_date = strtotime($config["meta"]["coupon_expiration"] . " 23:59:59"); //end of the day

        $now = GFCommon::get_local_timestamp();

        //validating start date
        if($config["meta"]["coupon_start"] && $now < $start_date){
            $invalid_reason = __("Invalid coupon.", "gravityformscoupons");
            return false;
        }

        //validating end date
        if($config["meta"]["coupon_expiration"] && $now > $end_date){
            $invalid_reason = __("This coupon has expired.", "gravityformscoupons");
            return false;
        }

        //validating usage limit
        $is_under_limit = false;
        $coupon_usage = empty($config["meta"]["coupon_usage"]) ? 0 : intval($config["meta"]["coupon_usage"]);
        if(empty($config["meta"]["coupon_limit"]) || $coupon_usage < intval($config["meta"]["coupon_limit"]))
            $is_under_limit = true;
        if(!$is_under_limit){
            $invalid_reason = __("This coupon has reached its usage limit.", "gravityformscoupons");
            return false;
        }

        //coupon is valid
        return true;
    }

    public static function get_config($form, $coupon_code){
        $coupon_code = trim($coupon_code);

        if(!class_exists("GFCouponsData"))
            require_once(self::get_base_path() . "/data.php");

        $array_key = $form["id"] . ":" . $coupon_code;
        if(array_key_exists($array_key , self::$_configs))
            return self::$_configs[$array_key];

        // getting form specific configs
        $configs1 = GFCouponsData::get_feed_by_form($form["id"]);

        // getting configs for any form
        $configs2 = GFCouponsData::get_feed_any_form();

        $configs = array_merge($configs1, $configs2);
        if(!$configs)
            return false;

        foreach($configs as $config){
            if($config["meta"]["coupon_code"] == $coupon_code)
            {
                self::$_configs[$form["id"] . ":" . $coupon_code] = $config;
                return $config;
            }
        }

        return false;
    }

    public static function add_permissions(){
        global $wp_roles;
        $wp_roles->add_cap("administrator", "gravityforms_coupons");
        $wp_roles->add_cap("administrator", "gravityforms_coupons_uninstall");
    }

    //Target of Member plugin filter. Provides the plugin with Gravity Forms lists of capabilities
    public static function members_get_capabilities( $caps ) {
        return array_merge($caps, array('gravityforms_coupons', 'gravityforms_coupons_uninstall'));
    }

    public static function select_coupons_form(){

        check_ajax_referer("gf_select_coupons_form", "gf_select_coupons_form");
        $form_id =  intval($_POST["form_id"]);

        //fields meta
        $form = RGFormsModel::get_form_meta($form_id);
        $coupon_fields = GFCommon::get_fields_by_type($form, array("coupon"));

        die("EndSelectForm(" . GFCommon::json_encode($form) . ");");

    }

    public static function get_form_fields($form){

        //Adding default fields
        array_push($form["fields"],array("id" => "date_created" , "label" => __("Entry Date", "gravityformscoupons")));
        array_push($form["fields"],array("id" => "ip" , "label" => __("User IP", "gravityformscoupons")));
        array_push($form["fields"],array("id" => "source_url" , "label" => __("Source Url", "gravityformscoupons")));

        $fields = array();
        if(is_array($form["fields"])){
            foreach($form["fields"] as $field){
                if(is_array(rgar($field,"inputs"))){

                    foreach($field["inputs"] as $input)
                        $fields[] =  array($input["id"], GFCommon::get_label($field, $input["id"]));
                }
                else if(!rgar($field,"displayOnly")){
                    $fields[] =  array($field["id"], GFCommon::get_label($field));
                }
            }
        }

        $str = "<option value=''>" . __("Insert variable", "gravityformscoupons") . "</option>";
        foreach($fields as $field){
            $field_id = $field[0];
            $field_label = $field[1];
            $str .= "<option value='{" . esc_attr($field_label) . ":" . $field_id . "}'>" . esc_html($field_label) . "</option>";
        }

        return $str;
    }

    public static function uninstall(){

        //loading data lib
        require_once(self::get_base_path() . "/data.php");

        if(!GFCoupons::has_access("gravityforms_counpons_uninstall"))
            die(__("You don't have adequate permission to uninstall Coupons Add-On.", "gravityformscoupons"));

        //droping all tables
        GFCouponsData::drop_tables();

        //removing options
        delete_option("gf_coupons_version");

        //Deactivating plugin
        $plugin = "gravityformscoupons/coupons.php";
        deactivate_plugins($plugin);
        update_option('recently_activated', array($plugin => time()) + (array)get_option('recently_activated'));
    }

    private static function is_gravityforms_installed(){
        return class_exists("RGForms");
    }

    private static function is_gravityforms_supported(){
        if(class_exists("GFCommon")){
            $is_correct_version = version_compare(GFCommon::$version, self::$min_gravityforms_version, ">=");
            return $is_correct_version;
        }
        else{
            return false;
        }
    }

    protected static function has_access($required_permission){
        $has_members_plugin = function_exists('members_get_capabilities');
        $has_access = $has_members_plugin ? current_user_can($required_permission) : current_user_can("level_7");
        if($has_access)
            return $has_members_plugin ? $required_permission : "level_7";
        else
            return false;
    }

    //Returns the url of the plugin's root folder
    protected static function get_base_url(){
        return plugins_url(null, __FILE__);
    }

    //Returns the physical path of the plugin's root folder
    protected static function get_base_path(){
        $folder = basename(dirname(__FILE__));
        return WP_PLUGIN_DIR . "/" . $folder;
    }



}
?>