<?php
/*
 * This file is part of the Designmodo WordPress Plugin.
*
* (c) Designmodo Inc. <info@designmodo.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Designmodo\Qards\License;

/**
 * License implements license and update features.
 */
class License
{
    /**
     * Init license
     *
     * @return void
     */
    static public function init()
    {
        require_once DM_BASE_PATH . 'vendor/AME/api-manager-example.php';
    }
}