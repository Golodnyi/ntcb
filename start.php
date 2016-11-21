<?php
/**
 * User: golodnyi
 * Date: 05.05.16
 * Time: 12:03
 */
    date_default_timezone_set('Europe/Moscow');

    require_once __DIR__ . DIRECTORY_SEPARATOR . 'ntcb_flex.php';

    $port = 9000;

    if (isset($argv[1]) && is_numeric($argv[1]))
    {
        $port = $argv[1];
    }

    try
    {
        $ntcb = new ntcb_flex(true);
        $ntcb->listen('0.0.0.0', $port);
        $ntcb->run();
    } catch (Exception $e)
    {
        echo '[' . date(DATE_W3C) . '] ' .
            $e->getMessage() .
            " [code " . $e->getCode() . "]\n";

        file_put_contents(
            __DIR__ . DIRECTORY_SEPARATOR . 'ntcb.log',
            '[' . $e->getCode() . ']' . ' ' . $e->getMessage() . "\n",
            FILE_APPEND
        );
    }