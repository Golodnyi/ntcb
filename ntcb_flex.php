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

        const TELEMETRY_PREFIX_VAL          = '~A';     // префикс телеметрических данных из черного ящика
        const WARNING_PREFIX_VAL            = '~T';     // префикс тревожного сообщения
        const TELEMETRY_CURRENT_PREFIX_VAL  = '~C';     // префикс телеметрических данных текущего состояния (видимо по запросу или вместо пинга)

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
                $this->readHeader($accept);
                $this->readBody($accept);
                $this->checkSum();
                $this->matchingProtocol();
                $this->sendGenerateMatchingProtocol($accept);
                $this->readTelemetry($accept);
            } catch(Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }

        private function readTelemetry($accept)
        {
            if (!$this->getSocket())
            {
                throw new Exception('Сокет не установлен', -1);
            }

            $prefix = socket_read($this->getSocket(), 2);

            if ($prefix === false)
            {
                throw new Exception('Датчик не вернул телеметрические данные', -37);
            }

            $prefix = unpack('c2', $prefix);

            if ($prefix === false)
            {
                throw new Exception('Функция unpack вернула ошибку', -38);
            }

            $pref = '';
            foreach($prefix as $byte)
            {
                $prefix .= chr($byte);
            }

            try
            {
                switch ($pref)
                {
                    case self::TELEMETRY_PREFIX_VAL:
                        if ($this->getStructVersion() == self::STRUCT_VERSION10)
                        {
                            $this->log('Телеметрические данные 10-ой версии');
                            $this->unpackTelemetryData10($accept);
                        } else if ($this->getStructVersion() == self::STRUCT_VERSION20)
                        {
                            $this->log('Телеметрические данные 20-ой версии');
                            //TODO: реализовать unpack фцнкцию для 2-ой версии
                        }
                        else
                        {
                            throw new Exception('Неизвестная версия структурных данных протокола', -38);
                        }
                        break;
                    case self::TELEMETRY_CURRENT_PREFIX_VAL:
                        //TODO: написать unpack функцию
                        if ($this->getStructVersion() == self::STRUCT_VERSION10)
                        {
                            $this->log('Телеметрические данные текущего состояния 10-ой версии');
                        } else if ($this->getStructVersion() == self::STRUCT_VERSION20)
                        {
                            $this->log('Телеметрические данные текущего состояния 20-ой версии');
                        }
                        else
                        {
                            throw new Exception('Неизвестная версия структурных данных протокола', -38);
                        }
                        break;
                    case self::WARNING_PREFIX_VAL:
                        //TODO: написать unpack функцию
                        if ($this->getStructVersion() == self::STRUCT_VERSION10)
                        {
                            $this->log('Тревожный запрос 10-ой версии');
                        } else if ($this->getStructVersion() == self::STRUCT_VERSION20)
                        {
                            $this->log('Тревожный запрос 20-ой версии');
                        }
                        else
                        {
                            throw new Exception('Неизвестная версия структурных данных протокола', -38);
                        }
                        break;
                    default:
                        throw new Exception('Неподдерживаемый тип запроса', -39);
                }
            } catch (Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }

        private function unpackTelemetryData10($accept)
        {
            if ($this->getStructVersion() != self::STRUCT_VERSION10)
            {
                throw new Exception('Некорректная версия структурных данных', -40);
            }

            $size = socket_read($accept, 1);

            if ($size === false)
            {
                throw new Exception('Датчик не вернул размер телеметрических данных', -37);
            }

            $size = current(unpack('C', $size));

            for ($i = 0; $i < $size; $i++)
            {
                if (($id = socket_read($accept, 4)) === false)
                {
                    throw new Exception('Не удалось прочитать ID из сокета', -41);
                }

                if (($id = unpack('L', $id)) === FALSE)
                {
                    throw new Exception('Функция unpack вернула ошибку при парсинге ID', -1);
                }
                $id = current($id);
                $this->log('ID = ' . $id);
            }
        }

        private function matchingProtocol()
        {
            $this->log('Начинаем согласование протоколов');

            try
            {
                $this->getBodySize();
            } catch(Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            $temp_pref = unpack('c6', substr($this->getBody(), 0, 6));

            if ($temp_pref === false)
            {
                throw new Exception('Функция unpack вернула ошибку', -34);
            }

            try
            {
                $prefix = '';
                foreach ($temp_pref as $char)
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

                $this->log('Протокол FLEX, версия ' . $protocol_version . ', структура ' . $stuct_version . ', размер конфигурационного поля ' . $data_size . ' байт');

                $length = ceil($data_size / 8);
                $bitfield_temp = unpack('C' . $length, substr($this->getBody(), 10, $length));
                $this->setBitfield($this->getBitfieldFromData($bitfield_temp, $data_size));
            } catch (Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }
            $this->log('Закончили согласование протоколов');
        }

        private function generateMatchingProtocol()
        {
            $this->log('Генерация заголовка ответа...');

            $preamble = self::PREAMBLE_VAL;
            $hs = self::ANSWER_MATCHING_PROTOCOLS_VAL;
            $body = '';
            for ($i = 0; $i < strlen($hs); $i++)
            {
                $body .= pack('c', ord($hs[$i]));
            }
            $body .= pack('C', $this->getProtocol());
            $body .= pack('C', $this->getProtocolVersion());
            $body .= pack('C', $this->getStructVersion());

            $binary = pack('cccc', ord($preamble[0]), ord($preamble[1]), ord($preamble[2]), ord($preamble[3]));
            $binary .= pack('L', $this->getIds());
            $binary .= pack('L', $this->getIdr());
            $binary .= pack('S', strlen($body));
            $binary .= pack('C', $this->xor_sum($body, strlen($body)));
            $binary .= pack('C', $this->xor_sum($binary, strlen($binary)));
            $binary .= $body;
            $this->log('Заголовок ответа сгенерирован...');

            return $binary;
        }

        private function sendGenerateMatchingProtocol($accept)
        {
            $this->log('Отправка согласования протоколов');
            $binary = $this->generateMatchingProtocol();

            $send = socket_write($accept, $binary, strlen($binary));

            if ($send != strlen($binary))
            {
                throw new Exception('Отправили ' . $send . ' байт, должны были отправить ' . strlen($binary) . ' байт', - 36);
            }

            $this->log('Завершена отправка согласования протоколов');
        }

        private function getBitfieldFromData($bitfield_temp, $data_size)
        {
            $bitfield = [];
            $z = 0;

            foreach($bitfield_temp as $byte)
            {
                for ($j = 0; $j < 8; $j++)
                {
                    $bit = decbin($byte);
                    $bitfield[] = !$bit[$j] ? 0 : 1;
                    if (++$z >= $data_size)
                    {
                        break;
                    }
                }
            }
            return $bitfield;
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