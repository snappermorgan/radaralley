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
 * Subscription provides features of subscription.
 */
class Subscription
{
    /**
     * Registrantion of new subscriber handler
     *
     * @return void
     */
    static public function subscriptionHandler()
    {
        if (!empty($_POST['qards_subscribe'])) {
            $formMsgs = array();
            $email = empty($_POST['qards_subscribe']['email']) ? '' : trim($_POST['qards_subscribe']['email']);
            if (!is_email($email)) {
                $formMsgs['error'][] = __('Email must be specified.');
            }
            if (Db::getColumn('SELECT email FROM `' . Db::getPluginTableName(Db::TABLE_SUBSCRIBER) . '` WHERE `email` = %s', array($email))) {
                $formMsgs['error'][] = __('This Email is subscribed already.');
            }
            if (empty($formMsgs['error'])) {
                Db::insert(Db::getPluginTableName(Db::TABLE_SUBSCRIBER), array('email' => $email));
                $formMsgs['success'][] = __('Your Email was successfully subscribed.');
            }
            Context::getInstance()->set('subscription_form_messages', $formMsgs);
        }
    }

    /**
     * Get all subscribers
     *
     * @return array
     */
    static public function getAll()
    {
        return Db::getAll('SELECT * FROM `' . Db::getPluginTableName(Db::TABLE_SUBSCRIBER) . '`');
    }

    /**
     * Export all subscribers
     *
     * @return void
     */
    static public function export()
    {
        $subscribers = self::getAll();
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=subscribers.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        foreach ($subscribers as $subscriber) {
            echo $subscriber['email'] . PHP_EOL;
        }
        exit;
    }


}