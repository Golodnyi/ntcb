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

    class ntcb_f6 extends ntcb
    {
        const TELE_VAL          = '*>A';    // значение префикса телеметрических данных от датчика
        const TELE_LEN          = 3;        // длина префикса теле данных
        const TELE_SIZE_LEN     = 1;        // длина блока теле данных
        const FORMAT_TYPE_LEN   = 1;        // длина типа формата в байтах
        const RECORD_ID_LEN     = 4;        // длина сквозного номера записи в энергонезависимой памяти в байтах
        const EVENT_CODE_LEN    = 2;        // длина кода события в байтах
        const DATE_LEN          = 6;        // длина даты в байтах
        const STATUS_LEN        = 1;        // длина статуса устройства в байтах (битфилд, первые 2 бита)
        const STATUS_MODULE_LEN = 1;        // длина статуса функциональных модулей в байтах (битфилд, 8 бит)
        const GSM_LEVEL_LEN     = 1;        // длина уровня сигнала в байтах
        const STATUS_OUTPUT_LEN = 1;        // длина текущего состояния выходов в байтах
        const STATUS_DISCR_SENSORS_LEN = 1; // длина состояния дискретных датчиков в байтах
        const VOLTAGE_POWER_LEN = 2;        // длина напряжения на основном источнике питания в байтах
        const VOLTAGE_POWER_BACKUP_LEN = 2; // длина напряжения на резервном источнике питания в байтах
        const A_INPUT1_LEN      = 2;        // длина на аналоговом входе 1 в байтах
        const A_INPUT2_LEN      = 2;        // длина на аналоговом входе 2 в байтах
        const A_INPUT3_LEN      = 2;        // длина на аналоговом входе 3 в байтах
        const PULSE_COUNTER1_LEN = 4;       // длина счетчика импульсов 1 в байтах
        const PULSE_COUNTER2_LEN = 4;       // длина счетчика импульсов 2 в байтах
        const A_FREQ_FUEL1_LEN  = 2;        // длина частоты на аналоговом частотном датчике уровня топлива 1 в байтах
        const A_FREQ_FUEL2_LEN  = 2;        // длина частоты на аналоговом частотном датчике уровня топлива 2 в байтах
        const FUEL1_PROC_LEN    = 1;        // длина значения толпива в основном баке, в процентах от объема CAN, -1 параметр не считывается
        const FUEL2_PROC_LEN    = 2;        // длина значения толпива в основном баке, в процентах от объема CAN, -1 параметр не считывается

        protected $_format;       // формат запроса
        protected $_record_id;    // id записи

        protected function processing($accept)
        {
            try {
                parent::processing($accept);
            } catch(Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }

        private function getBlockSize($accept)
        {
            $size = 0;
            $lengths = [
                'prefix' => self::TELE_LEN,
                'size' => self::TELE_SIZE_LEN
            ];

            $handle = fopen(__DIR__ . SLASH . microtime() . '_tele.bin', 'wb');
            foreach($lengths as $key => $length)
            {
                $buf = socket_read($accept, $length);
                if ($buf === false) {
                    throw new Exception(socket_strerror(socket_last_error()), socket_last_error());
                }

                switch ($key)
                {
                    case 'prefix':
                        $p = '';
                        foreach (unpack('c3', $buf) as $item)
                        {
                            $p .= chr($item);
                        }

                        if ($p != self::TELE_VAL)
                        {
                            throw new Exception('prefix tele data incorrect', -30);
                        }

                        break;
                    case 'size':
                        $size = current(unpack('С', $buf));
                        break;
                }

            }

            fwrite($handle, $buf, strlen($buf));
            return $size;
        }

        private function logSocket($accept)
        {
            $handle = fopen(__DIR__ . SLASH . microtime() . '_telemetries_loging.bin', 'wb');
            $this->log('Запись телеметрических данных...');
            while(($buf = socket_read($accept, 1)) != '')
            {
                fwrite($handle, $buf, strlen($buf));
            }
            $this->log('Окончание записи телеметрических данных...');
            fclose($handle);
        }
    }