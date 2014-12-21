<?php

if (!defined('ABSPATH')) exit;

function d4p_bbt_is_role($setting_name) {
    global $gdbbpress_tools;
    $allowed = false;

    if (current_user_can('d4p_bbpt_'.$setting_name)) {
        $allowed = true;
    } else if (is_super_admin()) {
        $allowed = $gdbbpress_tools->o[$setting_name.'_super_admin'] == 1;
    } else if (is_user_logged_in()) {
        $roles = $gdbbpress_tools->o[$setting_name.'_roles'];

        if (is_null($roles)) {
            $allowed = true;
        } else if (is_array($roles)) {
            global $current_user;

            if (is_array($current_user->roles)) {
                $matched = array_intersect($current_user->roles, $roles);
                $allowed = !empty($matched);
            }
        }
    }

    return $allowed;
}

?>