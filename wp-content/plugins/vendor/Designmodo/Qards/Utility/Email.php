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
 * Email provides features of emails.
 */
class Email
{
    /**
     * Send email
     *
     * @param string $sender
     * @param string $recipient
     * @param string $message
     * @param string $subject
     * @return void
     */
    static public function send($sender, $recipient, $message, $subject, $name = '')
    {
        $name = !$name ? $sender : $name;
        $message = preg_replace('|&[^a][^m][^p].{0,3};|', '', $message);
        $message = preg_replace('|&amp;|', '&', $message);
        $message = wordwrap(strip_tags($message), 80, "\n");
        $message = str_replace("\r\n", "<br />", $message);
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
        $headers .= "From: \"$name\" <$sender>\n";
        @wp_mail($recipient, $subject, $message, $headers);
    }
}