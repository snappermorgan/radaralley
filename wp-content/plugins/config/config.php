<?php
/*
 * This file is part of the Designmodo WordPress Plugin.
 *
 * (c) Designmodo Inc. <info@designmodo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$_qards_options = get_option('qards_settings');
$_qards_config = array();
$_qards_config['DM_DEBUG_MODE'] = false;
$_qards_config['DM_DS'] = '/';
$_qards_config['DM_TEMPLATE_ID_REGEX'] = '/^([a-z0-9\-\_\.]+)$/i';
$_qards_config['DM_TEMPLATE_DELIMETER'] = '.';
$_qards_config['DM_UPGRADE_URL'] = 'http://designmodo.com/';
$_qards_config['DM_RENEW_LICENSE_URL'] = 'http://designmodo.com/my-account';
$_qards_config['DM_BASE_PATH'] = str_replace('\\', '/', dirname(dirname(__FILE__))) . $_qards_config['DM_DS'];
$_qards_config['DM_TPL_PATH'] = $_qards_config['DM_BASE_PATH'] . 'templates';
$_qards_config['DM_RESOURCES_URI'] = str_replace('\\', '/', 'templates/');
//$_qards_config['DM_TPL_BUILDIN_PATH'] = $_qards_config['DM_BASE_PATH'] . 'templates/common/ui-kit';
$_qards_config['DM_TPL_EXT'] = '.html.twig';
$_qards_config['DM_TPL_JS_CONFIG_PATH'] = $_qards_config['DM_BASE_PATH'] . 'js/config';
$_qards_config['DM_PLUGIN_URL'] = plugin_dir_url(realpath( __DIR__ . '/../qards.php' ));
$_qards_config['DM_PLUGIN_VERSION'] = '1.0.0';
$_qards_config['DM_POST_TYPE'] = 'qards_page';
$_qards_config['DM_UNRELATED_COMPONENT_MAX_AGE'] = (60 * 60 * 24 * 1); // 1 day
$_qards_config['DM_MIN_PHP_VERSION'] = '5.3.0';
$_qards_config['DM_POST_TEASER_LIMIT'] = 120;