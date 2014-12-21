<?php

if (!defined('ABSPATH')) exit;

require_once(GDBBPRESSTOOLS_PATH.'code/tools/public.php');

function d4p_bbt_o($name) {
    global $gdbbpress_tools;
    return $gdbbpress_tools->o[$name];
}

?>