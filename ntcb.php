<?php
    /**
     * User: golodnyi
     * Date: 04.05.16
     * Time: 9:12
     * NTCB - бинарный протокол для Сигнал S-2551
     */
    abstract class ntcb
    {
        const HEADER_LEN        = 16;       // размер заголовка в байтах
        const PREAMBLE_LEN      = 4;        // размер преамбулы в байтах
        const IDr_LEN           = 4;        // размер IDr
        const IDs_LEN           = 4;        // размер IDs
        const BODY_LEN_LEN      = 2;        // размер поля с размером тела запроса
        const CSd_LEN           = 1;        // размер контрольной суммы тела
        const CSp_LEN           = 1;        // размер контрольной суммы заголовка
        const IMEI_LEN          = 15;       // размер блока с IMEI в байтах
        const PREF_IMEI_LEN     = 3;        // размер блока с префиксом IMEI в байтах
        const PREF_IMEI_VAL     = '*>S';    // значение префикса для IMEI от датчика
        const HANDSHAKE_VAL     = '*<S';    // значение префикса хендшейка ответа
        const PREAMBLE_VAL      = '@NTC';   // значение преамбулы по умолчанию
        const IMEI_BLOCK_LEN    = self::PREF_IMEI_LEN + self::IMEI_LEN; // общий размер блока с IMEI в байтах

        protected $_socket;       // ссылка на сокет
        protected $_address;      // адрес сокета
        protected $_port;         // порт сокета
        protected $_debug;        // debug true|false

        protected $_header;       // заголовок запроса (16 byte)
        protected $_preamble;     // преамбула char(4) (4 byte)
        protected $_idr;          // идентификатор получателя U32 (4 byte)
        protected $_ids;          // идентификатор отправителя U32 (4 byte)
        protected $_body_size;    // размер тела запроса в байтах U16 (2 byte)
        protected $_csd;          // контрольная сумма тела запроса U8 (1 byte)
        protected $_csp;          // контрольная сумма заголовка U8 (1 byte)

        protected $imei;          // IMEI номер датчика

        protected $_body;         // тело запроса

        /**
         * Получить тело запроса
         *
         * @return mixed
         */
        public function getBody()
        {
            return $this->_body;
        }

        /**
         * Установить тело запроса
         *
         * @param mixed $body
         */
        protected function setBody($body)
        {
            $this->_body = $body;
        }

        /**
         * @param bool $debug
         */
        public function __construct($debug = false)
        {
            set_time_limit(0);
            ob_implicit_flush();

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

            $this->setDebug($debug);
            $this->log('Запущен из: ' . __DIR__ . SLASH);
            $this->log('Режим отладки: ' . print_r($debug, true));
        }

        /**
         * Получить уровень отладки
         *
         * @return mixed
         */
        public function getDebug()
        {
            return $this->_debug;
        }

        /**
         * Установить уровень отладки
         *
         * @param mixed $debug
         */
        private function setDebug($debug)
        {
            $this->_debug = $debug;
        }

        /**
         * Установить прослушку порта
         *
         * @param string $address
         * @param string $port
         *
         * @throws \Exception
         */
        public function listen($address = '0.0.0.0', $port = '9000')
        {
            if (!($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)))
            {
                throw new Exception(socket_strerror(socket_last_error($socket)), socket_last_error($socket));
            }

            $this->log('Подключились к сокету: ' . print_r($socket, true));

            if (!(socket_bind($socket, $address, $port)))
            {
                throw new Exception(socket_strerror(socket_last_error($socket)), socket_last_error($socket));
            }

            if (!(socket_listen($socket, SOMAXCONN)))
            {
                throw new Exception(socket_strerror(socket_last_error($socket)), socket_last_error($socket));
            }

            $this->setAddress($address);
            $this->setPort($port);

            $this->log('Слушаем ' . $this->getAddress() . ':' . $this->getPort());

            $this->setSocket($socket);
        }

        private function unpackImei()
        {
            $this->log('Распаковка IMEI');

            try
            {
                $this->getBodySize();
            } catch(Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            $buffer = substr($this->getBody(), 0, self::PREF_IMEI_LEN);

            if ($buffer === false)
            {
                throw new Exception('Функция substr вернула ошибку', -21);
            }

            if ($buffer != self::PREF_IMEI_VAL)
            {
                throw new Exception('IMEI отсутсвует в теле запроса', -20);
            }

            $buffer = substr($this->getBody(), self::PREF_IMEI_LEN + 1, self::IMEI_LEN);

            try
            {
                $this->setImei($buffer);
            } catch (Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            $this->log('Сохранен IMEI');

            return true;
        }

        /**
         * run socket listener
         *
         * @throws \Exception
         */
        public function run()
        {
            if (!$this->getSocket())
            {
                throw new Exception('Сокет не установлен', -1);
            }

            $this->log('Ожидание подключения...');

            while (true)
            {
                if (($accept = socket_accept($this->getSocket())) === false)
                {
                    $this->log(socket_strerror(socket_last_error($this->getSocket())));
                    continue;
                }
                $this->log('Подключился датчик!');

                try
                {
                    $this->readHeader($accept);
                    $this->getBodySize();
                    $this->readBody($accept);
                    $this->checkSum();
                    $this->unpackImei();
                    $this->sendHandshake($accept);
                    $this->processing($accept);
                } catch (Exception $e)
                {
                    $this->log($e->getMessage());
                    socket_close($accept);
                    $this->log('Отключаемся от датчика!');
                    continue;
                }

                socket_close($accept);
                $this->log('Отключаемся от датчика!');
            }
        }

        protected function checkSum()
        {
            try
            {
                if ($this->xor_sum($this->getBody(), $this->getBodySize()) !== $this->getCsd())
                {
                    throw new Exception('Контрольная сумма CSd некорректна', -31);
                }
                $this->log('Контрольная сумма CSd корректна!');

                if ($this->xor_sum(substr($this->getHeader(), 0, self::HEADER_LEN - 1),
                        self::HEADER_LEN - 1) !== $this->getCsp()
                )
                {
                    throw new Exception('Контрольная сумма CSp некорректна', -31);
                }
                $this->log('Контрольная сумма CSp корректна!');
            } catch (Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }
            return true;
        }

        /**
         * Данный метод должен быть перезагружен в дочернем классе
         * в нем основная логика работы с конкретной версией
         * реализации протокола
         *
         * @param $accept
         *
         * @throws \Exception
         */
        protected function processing($accept)
        {
            if (!$this->getSocket())
            {
                throw new Exception('Сокет не установлен');
            }
        }

        protected function readHeader($accept)
        {
            if (!$this->getSocket())
            {
                throw new Exception('Сокет не установлен');
            }

            $this->log('Рукопожатие...');

            $lengths = [
                'preamble' => self::PREAMBLE_LEN,
                'IDr' => self::IDr_LEN,
                'IDs' => self::IDs_LEN,
                'BODY_LEN' => self::BODY_LEN_LEN,
                'CSd' => self::CSd_LEN,
                'CSp' => self::CSp_LEN
            ];

            $header = '';
            $handle = fopen(__DIR__ . SLASH . microtime() . '_handshake.bin', 'wb');
            foreach($lengths as $key => $length)
            {
                $buf = socket_read($accept, $length);
                if ($buf === false) {
                    throw new Exception(socket_strerror(socket_last_error()), socket_last_error());
                }

                $header .= $buf;

                try
                {
                    switch ($key)
                    {
                        case 'preamble':
                            $p = '';
                            foreach (unpack('c4', $buf) as $item)
                            {
                                $p .= chr($item);
                            }
                            $this->setPreamble($p);
                            break;
                        case 'IDr':
                            $this->setIdr(current(unpack('L', $buf)));
                            break;
                        case 'IDs':
                            $this->setIds(current(unpack('L', $buf)));
                            break;
                        case 'BODY_LEN':
                            $this->setBodySize(current(unpack('S', $buf)));
                            break;
                        case 'CSd':
                            $this->setCsd(current(unpack('C', $buf)));
                            break;
                        case 'CSp':
                            $this->setCsp(current(unpack('C', $buf)));
                            break;
                    }
                } catch (Exception $e)
                {
                    throw new Exception($e->getMessage(), $e->getCode());
                }

                fwrite($handle, $buf, strlen($buf));
            }
            fclose($handle);
            $this->setHeader($header);
            $this->log('Получено ' . strlen($header) . ' байт');
            $this->log('Закончили рукопожатие');
        }
        protected function readBody($accept)
        {
            if (!$this->getSocket())
            {
                throw new Exception('Сокет не установлен');
            }

            $this->log('Получаем тело запроса...');

            $handle = fopen(__DIR__ . SLASH . microtime() . '_body.bin', 'wb');

            try
            {
                $buf = socket_read($accept, $this->getBodySize());
            } catch(Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }
            if ($buf === false) {
                throw new Exception(socket_strerror(socket_last_error()), socket_last_error());
            }

            $this->setBody($buf);

            fwrite($handle, $buf, strlen($buf));
            fclose($handle);

            $this->log('Получено ' . strlen($buf) . ' байт');
            $this->log('Закончили получение тела запроса');
        }

        private function generateHandshake()
        {
            $this->log('Генерация данных для рукопожатия...');

            $preamble = self::PREAMBLE_VAL;
            $hs = self::HANDSHAKE_VAL;
            $body = '';
            for ($i = 0; $i < strlen($hs); $i++)
            {
                $body .= pack('c', ord($hs[$i]));
            }
            $binary = pack('cccc', ord($preamble[0]), ord($preamble[1]), ord($preamble[2]), ord($preamble[3]));
            $binary .= pack('L', $this->getIds());
            $binary .= pack('L', $this->getIdr());
            $binary .= pack('S', strlen($body));
            $binary .= pack('C', $this->xor_sum($body, strlen($body)));
            $binary .= pack('C', $this->xor_sum($binary, strlen($binary)));
            $binary .= $body;
            $this->log('Данные для рукопожатия сгенерированы...');

            return $binary;
        }

        private function sendHandshake($accept)
        {
            if (!$this->getSocket())
            {
                throw new Exception('Сокет не установлен');
            }

            $binary = $this->generateHandshake();

            if (($r = socket_write($accept, $binary, strlen($binary))) === false)
            {
                throw new Exception(socket_strerror(socket_last_error($this->getSocket())), socket_last_error($this->getSocket()));
            }

            if ($r != strlen($binary))
            {
                throw new Exception('Отправили ' . $r , ' байт, а должны были отправить ' . strlen($binary) . ' байт, проблемы с каналом связи?');
            }

            $this->log('Отправили ответное рукопожатие (' . strlen($binary) . ' байт)');
        }

        /**
         * Получить ресурс сокета
         *
         * @return mixed
         */
        public function getSocket()
        {
            return $this->_socket;
        }

        /**
         * Установить ресурс сокета
         *
         * @param mixed $socket
         */
        private function setSocket($socket)
        {
            $this->_socket = $socket;
        }

        /**
         * Получить адрес сервера
         *
         * @return mixed
         */
        public function getAddress()
        {
            return $this->_address;
        }

        /**
         * Установить адрес сервера
         *
         * @param mixed $address
         */
        private function setAddress($address)
        {
            $this->_address = $address;
        }

        /**
         * Получить порт сервера
         *
         * @return mixed
         */
        public function getPort()
        {
            return $this->_port;
        }

        /**
         * Установить порт сервера
         *
         * @param mixed $port
         */
        private function setPort($port)
        {
            $this->_port = $port;
        }

        /**
         * Получить заголовок запроса
         *
         * @return mixed
         */
        public function getHeader()
        {
            return $this->_header;
        }

        /**
         * Установить заголовок запроса
         *
         * @param mixed $header
         *
         * @throws \Exception
         */
        private function setHeader($header)
        {
            if ($header !== false && strlen($header) < self::HEADER_LEN)
            {
                throw new Exception('Неверная длина заголовка, ожидалось ' . self::HEADER_LEN . ' байт, получено ' . strlen($header) . ' байт', -23);
            }

            $this->_header = $header;
        }

        /**
         * Получить преамбулу заголовка
         *
         * @return mixed
         */
        public function getPreamble()
        {
            return $this->_preamble;
        }

        /**
         * Установить преамбулу заголовка
         *
         * @param mixed $preamble
         *
         * @throws \Exception
         */
        private function setPreamble($preamble)
        {
            if (strlen($preamble) < self::PREAMBLE_LEN)
            {
                throw new Exception('Преамбула заголовка неверной длины, ожидалось ' . self::PREAMBLE_LEN .' байт, получено ' . strlen($preamble) . ' байт.', -5);
            }

            if ($preamble != self::PREAMBLE_VAL)
            {
                throw new Exception('Неверное значение преамбулы, ожидалось ' . self::PREAMBLE_VAL, ', получено ' . $preamble, -26);
            }

            $this->_preamble = $preamble;
        }

        /**
         * Получить ID получателя пакета
         *
         * @return mixed
         */
        public function getIdr()
        {
            return $this->_idr;
        }

        /**
         * Установить id получателя пакета
         *
         * @param mixed $idr
         *
         * @throws \Exception
         */
        private function setIdr($idr)
        {
            if (!is_int($idr))
            {
                throw new Exception('IDr не INT', -8);
            }

            $this->_idr = $idr;
        }

        /**
         * Получить ID отправителя пакета
         *
         * @return mixed
         */
        public function getIds()
        {
            return $this->_ids;
        }

        /**
         * Установить ID отправителя пакета
         *
         * @param mixed $ids
         *
         * @throws \Exception
         */
        private function setIds($ids)
        {
            if (!is_int($ids))
            {
                throw new Exception('IDs не INT', -8);
            }

            $this->_ids = $ids;
        }

        /**
         * Получить размер тела запроса в байтах
         *
         * @return mixed
         */
        public function getBodySize()
        {
            $size = intval($this->_body_size);

            if (!$size)
            {
                throw new Exception('Нулевой размер тела запроса');
            }

            return $size;
        }

        /**
         * Установить размер тела запроса в байтах
         *
         * @param mixed $body_size
         *
         * @throws \Exception
         */
        private function setBodySize($body_size)
        {
            if (!is_int($body_size))
            {
                throw new Exception('Размер тела запроса не INT', -8);
            }

            $this->_body_size = $body_size;
        }

        /**
         * Получить контрольную сумму CSd
         *
         * @return mixed
         */
        public function getCsd()
        {
            return $this->_csd;
        }

        /**
         * Установить контрольную сумму CSd
         *
         * @param mixed $csd
         *
         * @throws \Exception
         */
        private function setCsd($csd)
        {
            if (!is_int($csd))
            {
                throw new Exception('CSd не INT', -14);
            }

            $this->_csd = $csd;
        }

        /**
         * Получить контрольную сумму CSp
         *
         * @return mixed
         */
        public function getCsp()
        {
            return $this->_csp;
        }

        /**
         * Установить контрольную сумму CSp
         *
         * @param mixed $csp
         *
         * @throws \Exception
         */
        private function setCsp($csp)
        {
            if (!is_int($csp))
            {
                throw new Exception('CSP не INT', -15);
            }

            $this->_csp = $csp;
        }

        /**
         * Получить IMEI датчика
         *
         * @return mixed
         */
        public function getImei()
        {
            return $this->imei;
        }

        /**
         * Установить IMEI датчика
         *
         * @param mixed $imei
         *
         * @throws \Exception
         */
        private function setImei($imei)
        {
            if (strlen($imei) < self::IMEI_LEN)
            {
                throw new Exception('Неверная длина IMEI, ожидалось ' . self::IMEI_LEN . ' байт, получено ' . strlen($imei) . ' байт', -18);
            }

            $this->imei = $imei;
        }


        /**
         * Вывести сообщение в консоли
         *
         * @param $message
         */
        public function log($message)
        {
            if ($this->_debug)
            {
                $output = '[' . date(DATE_W3C) . '] ' . $message . "\n";

                if (OS == 'win')
                {
                    $output = iconv(mb_detect_encoding($output), 'cp866', $output);
                }

                echo $output;
            }
        }

        /**
         * Рассчитать контрольную сумму заголовка или тела запроса
         *
         * @param $bufLen
         * @param $length
         *
         * @return int
         */
        protected function xor_sum($bufLen, $length)
        {
            $temp_sum = 0;

            for ($i = 0; $i < $length; $i++)
            {
                $temp_sum ^= ord(substr($bufLen, $i, 1));
            }

            return $temp_sum;
        }
    }