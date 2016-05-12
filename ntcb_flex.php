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

        private $_size_array = [
            'L' => 4,
            'S' => 2,
            'C' => 1
        ];

        private $_telemetry_values10 = [
            0 => [0 => 'L', 1 => 'numPage'], // id записи в черном ящике
            1 => [0 => 'S', 1 => 'Code'], // код события
            2 => [0 => 'L', 1 => 'Time'], // время события
            3 => [0 => 'C', 1 => 'State'], // статус устройства (битфилд)
            4 => [0 => 'C', 1 => 'Module1'], // статус функциональных модулей 1 (битфилд)
            5 => [0 => 'C', 1 => 'Module2'], // статус функциональных модулей 2 (битфилд)
            6 => [0 => 'C', 1 => 'GSM'], // уровень gsm
            7 => [0 => 'C', 1 => 'StateGauge'], // состояние навигационного датчика GPS/Глонасс (битфилд)
            8 => [0 => 'L', 1 => 'LastTime'], // время последних валидных координат
            9 => [0 => 'l', 1 => 'Lat'], // последняя валидная широта
            10 => [0 => 'l', 1 => 'Lon'], // долгота
            11 => [0 => 'l', 1 => 'Alt'], // высота
            12 => [0 => 'L', 1 => 'Speed'], // скорость (флоут)
            13 => [0 => 'S', 1 => 'Course'], // курс
            14 => [0 => 'L', 1 => 'Mileage'], // текущий пробег (флоут)
            15 => [0 => 'L', 1 => 'Way'], // последний отрезок пути (флоут)
            16 => [0 => 'S', 1 => 'AllSeconds'], // общее кол-во сек на последнем отрезке
            17 => [0 => 'S', 1 => 'SecondLast'], // тоже самое, но по которому вычислялся пробег
            18 => [0 => 'S', 1 => 'Power'], // напряжение на основном источнике питания
            19 => [0 => 'S', 1 => 'Reserv'], // напряжение на резеврном источнике питания
            20 => [0 => 'S', 1 => 'StateU_Ain1'], // напряжение на анологовом входе 1
            21 => [0 => 'S', 1 => 'StateU_Ain2'], // 2
            22 => [0 => 'S', 1 => 'StateU_Ain3'], // 3
            23 => [0 => 'S', 1 => 'StateU_Ain4'], // 4
            24 => [0 => 'S', 1 => 'StateU_Ain5'], // 5
            25 => [0 => 'S', 1 => 'StateU_Ain6'], // 6
            26 => [0 => 'S', 1 => 'StateU_Ain7'], // 7
            27 => [0 => 'S', 1 => 'StateU_Ain8'], // 8
            28 => [0 => 'C', 1 => 'StateIn1'], // текущие показания дискретных датчиков 1
            29 => [0 => 'C', 1 => 'StateIn2'], // 2
            30 => [0 => 'C', 1 => 'stateOut1'], // текущее состояние выходов 1
            31 => [0 => 'C', 1 => 'StateOut2'], // 2
            32 => [0 => 'L', 1 => 'StateInImp1'], // показания счетчика импульсов 1
            33 => [0 => 'L', 1 => 'StateInImp2'], // 2
            34 => [0 => 'S', 1 => 'Frequency1'], // частота на аналогово-часточном датчике уровня топлива 1
            35 => [0 => 'S', 1 => 'Frequency2'], // 2
            36 => [0 => 'L', 1 => 'Motochas'], // моточасы, посчитанные во время срабатывания датчика работы генератора
            37 => [0 => 'S', 1 => 'LevelRS485_1'], // уровень топлива, измеренный датчиком уровня топлива 1 RS-485
            38 => [0 => 'S', 1 => 'LevelRS485_2'], // 2
            39 => [0 => 'S', 1 => 'LevelRS485_3'], // 3
            40 => [0 => 'S', 1 => 'LevelRS485_4'], // 4
            41 => [0 => 'S', 1 => 'LevelRS485_5'], // 5
            42 => [0 => 'S', 1 => 'LevelRS485_6'], // 6
            43 => [0 => 'S', 1 => 'LevelRS232'], // уровень топлива, измененный датчиком уровня топлива RS-232
            44 => [0 => 'c', 1 => 'Temp1'], // температура с цифрового датчика 1 (в цельсиях)
            45 => [0 => 'c', 1 => 'Temp2'], // 2
            46 => [0 => 'c', 1 => 'Temp3'], // 3
            47 => [0 => 'c', 1 => 'Temp4'], // 4
            48 => [0 => 'c', 1 => 'Temp5'], // 5
            49 => [0 => 'c', 1 => 'Temp6'], // 6
            50 => [0 => 'c', 1 => 'Temp7'], // 7
            51 => [0 => 'c', 1 => 'Temp8'], // 8
            52 => [0 => 'S', 1 => 'CAN_FuelLevel'], // уровень топлива в баке
            53 => [0 => 'L', 1 => 'CAN_FuelConsumption'], // полный расход топлива
            54 => [0 => 'S', 1 => 'CAN_EngineTurns'], // обороты двигателя
            55 => [0 => 'c', 1 => 'CAN_Temp'], // температура охлаждающей жидкости двигатедя
            56 => [0 => 'L', 1 => 'CAN_FullRun'], // полный пробег ТС
            57 => [0 => 'S', 1 => 'CAN_AxleLoad_1'], // нагрузка на ось 1
            58 => [0 => 'S', 1 => 'CAN_AxleLoad_2'], // 2
            59 => [0 => 'S', 1 => 'CAN_AxleLoad_3'], // 3
            60 => [0 => 'S', 1 => 'CAN_AxleLoad_4'], // 4
            61 => [0 => 'S', 1 => 'CAN_AxleLoad_5'], // 5
            62 => [0 => 'C', 1 => 'CAN_PedalAccel'], // положение педали газа
            63 => [0 => 'C', 1 => 'CAN_PedalStop'], // тормоза
            64 => [0 => 'C', 1 => 'CAN_EngineLoad'], // нагрузка на двигатель
            65 => [0 => 'S', 1 => 'CAN_LevelFiltr'], // уровень жидкости в дизельном фильтре выхлопных газов
            66 => [0 => 'L', 1 => 'CAN_EngineTime'], // время работы двигателя
            67 => [0 => 's', 1 => 'CAN_TimeTO'], // расстояние до то
            68 => [0 => 'C', 1 => 'CAN_Speed'], // скорость ТС
        ];

        private $_prefix;
        private $_protocol;
        private $_protocol_version;
        private $_struct_version;
        private $_data_size;
        private $_bitfield;
        public $_telemetry;

        /**
         * @return mixed
         */
        public function getTelemetry()
        {
            return $this->_telemetry;
        }

        /**
         * @param mixed $telemetry
         */
        public function setTelemetry($telemetry)
        {
            $this->_telemetry = $telemetry;
        }

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
                file_put_contents('telemetry.serialize', serialize($this->getTelemetry()));
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

            $prefix = socket_read($accept, 2);

            if ($prefix === false)
            {
                throw new Exception('Датчик не вернул префикс телеметрических данных', -37);
            }

            $prefix = unpack('c2', $prefix);

            if ($prefix === false)
            {
                throw new Exception('Функция unpack вернула ошибку', -38);
            }

            $pref = '';
            foreach($prefix as $byte)
            {
                $pref .= chr($byte);
            }

            try
            {
                switch ($pref)
                {
                    case self::TELEMETRY_PREFIX_VAL:
                        if ($this->getStructVersion() == self::STRUCT_VERSION10)
                        {
                            $this->log('Телеметрические данные 10-ой версии');
                            require_once __DIR__ . SLASH . 'classes' . SLASH . 'telemetry_flex_v10.php';
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
                        throw new Exception('Неподдерживаемый тип запроса ' . $pref, -39);
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

            $telemetry = new telemetry_flex_v10();

            $size = current(unpack('C', $size));
            for ($i = 0; $i < $size; $i++)
            {
                for ($j = 0; $j < count($this->getBitfield()); $j++)
                {
                    if (!$this->getBitfield()[$j])
                    {
                        continue;
                    }

                    $size_read = $this->_size_array[strtoupper($this->_telemetry_values10[$j][0])];
                    $this->log('прочитали ' . $size_read . ' байт');
                    $buf = socket_read($accept, $size_read);

                    if ($buf == false)
                    {
                        throw new Exception('Ошибка получения данных из сокета, параметр ' . $this->_telemetry_values10[$j][1]);
                    }
                    $buf = unpack($this->_telemetry_values10[$j][0], $buf);

                    if ($buf == false)
                    {
                        throw new Exception('Функция unpack вернула ошибку на параметре ' . $this->_telemetry_values10[$j][1] . ' (' . $this->_telemetry_values10[$i][0] . ')');
                    }

                    $method = 'set' . str_replace('_', '', $this->_telemetry_values10[$j][1]);
                    if (!method_exists($telemetry, $method))
                    {
                        throw new Exception('Метод ' . $method . ' не существует', -50);
                    }

                    $telemetry->$method(current($buf));
                }
            }

            $this->setTelemetry($telemetry);
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