<?php

    /**
     * User: golodnyi
     * Date: 06.05.16
     * Time: 15:05
     */

    if (!defined('SLASH'))
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
        {
            define('SLASH', '\\');
        }
        else
        {
            define('SLASH', '/');
        }
    }

    require_once __DIR__ . SLASH . 'ntcb.php';

    class ntcb_v6 extends ntcb
    {

    }