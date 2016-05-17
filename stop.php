<?php
    /**
     * User: golodnyi
     * Date: 16.05.16
     * Time: 17:50
     */
    if (file_exists(__DIR__ . '/run.lock'))
    {
        $file_pid = file_get_contents(__DIR__ . '/run.lock');
        $ps = shell_exec('ps -A | grep ' . $file_pid);
        if (!is_null($ps))
        {
            $ps = shell_exec('kill ' . $file_pid);
        }
    }