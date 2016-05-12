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
        const FLEX_VAL                      = '0xB0';   // код протокола FLEX
        const VERSION                       = [10, 20]; // список поддерживаемых версий
        const STRUCT_VERSION                = [10, 20]; // список поддерживаемых версий структур
        const STRUCT_VERSION10              = 10;       // значение для 10 версии струкутры
        const STRUCT_VERSION20              = 20;       // значение для 20 версии структуры
        const SIZE_CONFIG10                 = 69;       // размер конфигурационного поля при 10 версии структуры
        const SIZE_CONFIG20                 = 122;      // размер конфигурационного поля, при 20 версии структуры

        private $_prefix;
        private $_protocol;
        private $_protocol_version;
        private $_struct_version;
        private $_data_size;
        private $_bitfield;

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
            $this->log('Начинаем согласование протоколов');

            if (!$this->getBodySize())
            {
                throw new Exception('Пустое тело запроса', -33);
            }

            $temp_pref = unpack('c6', substr($this->getBody(), 0, 6));

            if ($temp_pref === false)
            {
                throw new Exception('Функция unpack вернула ошибку', -34);
            }

            $prefix = '';
            foreach($temp_pref as $char)
            {
                $prefix .= chr($char);
            }

            $this->setPrefix($prefix);

            $protocol = current(unpack('C', substr($this->getBody(), 6, 1)));

            if ($protocol === false)
            {
                throw new Exception('Функция unpack вернула ошибку', -34);
            }

            $this->setProtocol($protocol);

            $protocol_version = current(unpack('C', substr($this->getBody(), 7, 1)));

            if ($protocol_version === false)
            {
                throw new Exception('Функция unpack вернула ошибку', -34);
            }

            $this->setProtocolVersion($protocol_version);

            $stuct_version = current(unpack('C', substr($this->getBody(), 8, 1)));

            if ($stuct_version === false)
            {
                throw new Exception('Функция unpack вернула ошибку', -34);
            }

            $this->setStructVersion($stuct_version);

            $data_size = current(unpack('C', substr($this->getBody(), 9, 1)));

            if ($data_size === false)
            {
                throw new Exception('Функция unpack вернула ошибку', -34);
            }

            $this->setDataSize($data_size);

            $this->log('Протокол FLEX, версия ' . $protocol_version .', структура ' . $stuct_version. ', размер конфигурационного поля ' . $data_size . ' байт');

            $length = ceil($data_size / 8);
            $bitfield_temp = unpack('C'.$length, substr($this->getBody(), 10, $length));
            $bitfield = [];

            $z = 0;
            for( $i = 1; $i < count($bitfield_temp) + 1; $i++)
            {
                for ($j = 0; $j < 8; $j++)
                {


                    $bit = decbin($bitfield_temp[$i])[$j];
                    $bitfield[] = !$bit ? 0 : 1;

                    if (++$z >= $data_size)
                    {
                        break;
                    }
                }
            }

            $this->setBitfield($bitfield);

            $this->log('Закончили согласование протоколов');
        }

        /**
         * @return mixed
         */
        public function getPrefix()
        {
            return $this->_prefix;
        }

        /**
         * @param mixed $prefix
         */
        private function setPrefix($prefix)
        {
            if ($prefix != self::MATCHING_PROTOCOLS_VAL)
            {
                throw new Exception('Некорректный префикс согласования протоколов', -32);
            }

            $this->_prefix = $prefix;
        }

        /**
         * @return mixed
         */
        public function getProtocol()
        {
            return $this->_protocol;
        }

        /**
         * @param mixed $protocol
         */
        private function setProtocol($protocol)
        {
            if ($protocol != self::FLEX_VAL)
            {
                throw new Exception('Версия протокола не совпадает с FLEX: ' . $protocol, -35);
            }

            $this->_protocol = $protocol;
        }

        /**
         * @return mixed
         */
        public function getProtocolVersion()
        {
            return $this->_protocol_version;
        }

        /**
         * @param mixed $protocol_version
         */
        private function setProtocolVersion($protocol_version)
        {
            if (!in_array($protocol_version, self::VERSION))
            {
                throw new Exception('Неверная версия протокола ' . $protocol_version, -34);
            }

            $this->_protocol_version = $protocol_version;
        }

        /**
         * @return mixed
         */
        public function getStructVersion()
        {
            return $this->_struct_version;
        }

        /**
         * @param mixed $struct_version
         */
        private function setStructVersion($struct_version)
        {
            if (!in_array($struct_version, self::STRUCT_VERSION))
            {
                throw new Exception('Неверная версия структуры данных протокола ' . $struct_version, -34);
            }

            $this->_struct_version = $struct_version;
        }

        /**
         * @return mixed
         */
        public function getDataSize()
        {
            return $this->_data_size;
        }

        /**
         * @param mixed $data_size
         */
        private function setDataSize($data_size)
        {
            if ($this->getStructVersion() == self::STRUCT_VERSION10 && $data_size != self::SIZE_CONFIG10)
            {
                throw new Exception('Неверный размер конфигурационного поля при текущей версии структуры', -36);
            }

            if ($this->getStructVersion() == self::STRUCT_VERSION20 && $data_size != self::SIZE_CONFIG20)
            {
                throw new Exception('Неверный размер конфигурационного поля при текущей версии структуры', -37);
            }

            $this->_data_size = $data_size;
        }

        /**
         * @return mixed
         */
        public function getBitfield()
        {
            return $this->_bitfield;
        }

        /**
         * @param mixed $bitfield
         */
        private function setBitfield($bitfield)
        {
            if (count($bitfield) != $this->getDataSize())
            {
                throw new Exception('Неверный размер битфилда, ожидалось ' . $this->getDataSize() . ', получили ' . count($bitfield));
            }

            $this->_bitfield = $bitfield;
        }



    }