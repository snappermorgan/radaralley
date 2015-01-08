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
 * User provides features of WordPress users.
 */
class User
{
    /**
     * Registrantion of new user handler
     *
     * @return void
     */
    static public function registrationHandler()
    {
        if (!empty($_POST['dm_register_user'])) {
            Context::getInstance()->set('register_form_success', false);
            $registerFormErrors = array();
            if (! empty($_POST['dm_register_user']['subscribe'])) {
                $_POST['dm_register_user']['pwd'] = $_POST['dm_register_user']['rpwd'] = md5(microtime(true) . @$_SERVER['REMOTE_ADDR']);
            }
            if (empty($_POST['dm_register_user']['email']) || !is_email($_POST['dm_register_user']['email'])) {
                $registerFormErrors[] = __('Email must be specified.');
                $_POST['dm_register_user']['email'] = '';
            }
            if (isset($_POST['dm_register_user']['login']) && empty($_POST['dm_register_user']['login'])) {
                $registerFormErrors[] = __('Unique name must be specified.');
            } elseif (!isset($_POST['dm_register_user']['login'])) {
                $_POST['dm_register_user']['login'] = $_POST['dm_register_user']['email'];
            }
            if (empty($_POST['dm_register_user']['pwd'])) {
                $registerFormErrors[] = __('Password must be specified.');
            }
            if ($_POST['dm_register_user']['pwd'] !== $_POST['dm_register_user']['rpwd']) {
                $registerFormErrors[] = __('Passwords must match.');
            }
            if (username_exists($_POST['dm_register_user']['login'])) {
                $registerFormErrors[] = __('This user is exist already.');
            }
            if (empty($registerFormErrors)) {
                if (wp_create_user($_POST['dm_register_user']['login'], $_POST['dm_register_user']['pwd'], $_POST['dm_register_user']['email'])) {
//                     Context::getInstance()->set('register_form_success', true);
                    wp_redirect(add_query_arg(array('success' => true), $_SERVER['REQUEST_URI']));
                    exit;
                }
            } else {
                Context::getInstance()->set('register_form_errors', $registerFormErrors);
            }
        }
    }
}