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
 * ContactForm provides features of contact form.
 */
class ContactForm
{

    /**
     * Contact form handler
     *
     * @return void
     */
    static public function handler()
    {
        // Email handler
        if (!empty($_REQUEST['dm_contact_form'])) {
            $sender = empty($_REQUEST['dm_contact_form']['from']) ? '' : $_REQUEST['dm_contact_form']['from'];
            $senderName = empty($_REQUEST['dm_contact_form']['name']) ? '' : $_REQUEST['dm_contact_form']['name'];
            $message = empty($_REQUEST['dm_contact_form']['msg']) ? '' : $_REQUEST['dm_contact_form']['msg'];
            if ($sender && $message) {
                $subject = get_option('blogname') . ' | Contact form';
                Email::send($sender, get_option('admin_email'), $message, $subject, $senderName);
                Context::getInstance()->set('contact_form_success', true);
            }
        }
    }
}