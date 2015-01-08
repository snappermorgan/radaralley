<?php
/**
 * Plugin Name: Qards
 * Plugin URI: http://designmodo.com/qards/
 * Description: You focus on content, the purpose of your website and the story you want to tell. Leave the rest to us.
 * Version: 1.0.0
 * Author: Designmodo Inc.
 * Author URI: http://designmodo.com/
 */

// Config loader.
$_qards_config = array();
require_once 'config/config.php';
if (getenv('APPLICATION_ENV')) {
    require_once 'config/' . getenv('APPLICATION_ENV') . '.php';
}
if (!empty($_qards_config)) {
    foreach ($_qards_config as $k => $v) {
        define($k, $v);
    }

    // Check PHP version
    if (version_compare(phpversion(), DM_MIN_PHP_VERSION, '<') && ! is_admin()) {
        throw new Exception('Qards requires PHP 5.3.0 or greater. You have ' . phpversion(), 986545);
    }

    require_once DM_BASE_PATH . 'vendor/AME/api-manager-example.php';
    function _qards_activation_ame() {
        AME()->instance()->activation();
    }
    register_activation_hook(__FILE__, '_qards_activation_ame');
    function _qards_deactivation_ame() {
        AME()->instance()->uninstall();
    }
    register_deactivation_hook(__FILE__, '_qards_deactivation_ame');

    // Load initializer
    require_once 'init.php';
}