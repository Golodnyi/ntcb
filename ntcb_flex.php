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

    class ntcb_flex extends ntcb
    {
        const MATCHING_PROTOCOLS_VAL        = '*>FLEX'; // префикс команды согласования протоколов
        const ANSWER_MATCHING_PROTOCOLS_VAL = '*<FLEX'; // ответный префикс команды согласования протоколов

        protected function processing($accept)
        {
            try {
                parent::processing($accept);
            } catch(Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            $this->readHeader($accept);
            $this->readBody($accept);

            if (!$this->checkSum())
            {
                throw new Exception('Контрольная сумма некорректна', -31);
            }

            try
            {
                $this->sendMatchingProtocol($accept);
            } catch (Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }

        private function sendMatchingProtocol($accept)
        {
            if (!$this->getBodySize())
            {
                throw new Exception('Пустое тело запроса', -33);
            }

            $temp_pref = unpack('c6', substr($this->getBody(), 0, 6));

            if ($temp_pref === false)
            {
                throw new Exception('Функция unpack вернула ошибку', -34);
            }

            $pref = '';
            foreach($temp_pref as $char)
            {
                $pref .= chr($char);
            }

            if ($pref != self::MATCHING_PROTOCOLS_VAL)
            {
                throw new Exception('Некорректный префикс согласования протоколов', -32);
            }


        }
    }