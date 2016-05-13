<?php
/**
 * User: golodnyi
 * Date: 05.05.16
 * Time: 12:03
 */
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
    {
        define('SLASH', '\\');
        define('PORT', 9000);
        define('OS', 'win');
    }
    else
    {
        define('SLASH', '/');
        define('PORT', 9001);
        define('OS', 'lin');
    }

    require_once __DIR__ . SLASH . 'ntcb_flex.php';

    try
    {
        $ntcb = new ntcb_flex(true);
        $ntcb->listen('0.0.0.0', PORT);
        $ntcb->run();
    } catch (Exception $e)
    {
        echo '[' . date(DATE_W3C) . '] ' .
            $e->getMessage() .
            " [code " . $e->getCode() . "]\n";

        file_put_contents(
            __DIR__ . SLASH . 'ntcb.log',
            '[' . $e->getCode() . ']' . ' ' . $e->getMessage() . "\n",
            FILE_APPEND
        );
    }