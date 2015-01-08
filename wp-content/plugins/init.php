<?php
use Symfony\Component\ClassLoader\ClassLoader;
/*
 * This file is part of the Designmodo WordPress Plugin.
 *
 * (c) Designmodo Inc. <info@designmodo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Class loader
if (!class_exists('Symfony\\Component\\ClassLoader\\ClassLoader')) {
    require_once DM_BASE_PATH . '/vendor/Symfony/Component/ClassLoader/ClassLoader.php';
}

$_qardsClassLoader = new ClassLoader();
$_qardsClassLoader->addPrefix('Symfony', DM_BASE_PATH . '/vendor/');
$_qardsClassLoader->addPrefix('Designmodo', DM_BASE_PATH . '/vendor/');
$_qardsClassLoader->register();
$_qardsClassLoader->setUseIncludePath(true);

use Designmodo\Qards\Utility\Context;
use Designmodo\Qards\Utility\Menu;
use Designmodo\Qards\Utility\Post;
use Designmodo\Qards\Utility\Timber;
use Designmodo\Qards\Utility\Migrations;
use Designmodo\Qards\Utility\Rewrite;
use Designmodo\Qards\Utility\Api;
use Designmodo\Qards\Utility\ContactForm;
use Designmodo\Qards\Utility\User;
use Designmodo\Qards\Utility\SettingsPage;
use Designmodo\Qards\License\License;
use Designmodo\Qards\Utility\Subscription;
use Designmodo\Qards\Utility\StyleScriptFilter;

// Init exception handler
set_exception_handler(function ($exception)
{
    echo '
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Oops!</title>
    <style>
    </style>
  </head>
  <body>
  	<div>
	    <h1>Beep beep boop!</h1>
        <h2>Error occurred :(</h2>
        <p>Something went wrong while displaying this page.</p>
        <p>Please, let us know about this incident via <a href="mailto:' . get_option('admin_email') . '?subject=' . rawurlencode('I found an error #' . $exception->getCode() . ' on your web site ' . $_SERVER['SERVER_NAME'] . '.') . '&body=' . rawurlencode('
Hi! I just found an error on your web site ' . $_SERVER['SERVER_NAME'] . '.
Here is details:
' . $exception->getCode() . ': ' . $exception->getMessage() . '
On the ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '">email</a>.</p>
        <pre>' . $exception->getCode() . ': ' . $exception->getMessage() . '</pre>
	</div>
  </body>
</html>';
});

// Initialize the plugin
add_action(
    'init',
    function () {
        // Init the migration tool
        Migrations::init();

        // License init
        License::init();

        // Init template engine
        Timber::init();

        // Register custom post type
        Post::registerCustomPostType();

        // Init menus
        Menu::init();

        // Setting page init
        SettingsPage::init();

//         // Declarate theme supported features
//         add_theme_support('post-formats');
//         add_theme_support('post-thumbnails');
//         add_theme_support('menus');

        // Add Mediaelement on the page
        wp_enqueue_style( 'wp-mediaelement' );
        wp_enqueue_script( 'wp-mediaelement' );

        // Rewriter init
        Rewrite::init();

        // Ajax handler
        add_action('wp_ajax_dm_api', array('Designmodo\Qards\Utility\Api', 'handler'));
        add_action('wp_ajax_nopriv_dm_api', array('Designmodo\Qards\Utility\Api', 'handler'));

        // Contact form handler
        ContactForm::handler();

        // Registrantion of new user handler
        User::registrationHandler();

        // Registrantion of new subscriber handler
        Subscription::subscriptionHandler();

        // Add links on the plugins page
        add_filter(
            'plugin_action_links_qards/qards.php', //'plugin_row_meta',
            function ( $links ) {
                $links[] = '<a href="http://designmodo.com/qards/faq/">FAQ</a>';
                $links[] = '<a href="http://designmodo.com/qards/first-steps/" target="_blank">First Steps</a>';
                $links[] = '<a href=" http://designmodo.com/qards/contact-us/" target="_blank">Support</a>';
                return $links;
            }
        );
    }
);

// Init scripts/styles filtering
add_action('plugins_loaded', function() { //plugins_loaded
    add_action('wp_print_styles',  array('Designmodo\Qards\Utility\StyleScriptFilter', 'filterStyles'));
    add_action('wp_print_scripts', array('Designmodo\Qards\Utility\StyleScriptFilter', 'filterScripts'));
});