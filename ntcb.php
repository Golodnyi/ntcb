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
            date_default_timezone_set('Europe/Moscow');
            set_time_limit(0);
            ob_implicit_flush();

            if (file_exists(__DIR__ . '/run.lock'))
            {
                $file_pid = file_get_contents(__DIR__ . '/run.lock');
                $ps = shell_exec('ps -A | grep ' . $file_pid);
                if (!is_null($ps))
                {
                    throw new Exception('already run', -2);
                }
            }

            file_put_contents(__DIR__ . '/run.lock', getmypid());

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

            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 10, 'usec' => 0]);

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

            return true;
        }

        private function reconnect($accept)
        {
            socket_close($accept);
            $this->log('Отключаемся от датчика!');
            $this->log('++++++++++');
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
                    $this->reconnect($accept);
                    continue;
                }

                $this->reconnect($accept);
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

                if ($this->xor_sum(substr($this->getHeader(), 0, self::HEADER_LEN - 1),
                        self::HEADER_LEN - 1) !== $this->getCsp()
                )
                {
                    throw new Exception('Контрольная сумма CSp некорректна', -31);
                }
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
                throw new Exception('Сокет не установлен ' . var_dump($accept));
            }
        }

        protected function readHeader($accept)
        {
            if (!$this->getSocket())
            {
                throw new Exception('Сокет не установлен');
            }

            $lengths = [
                'preamble' => self::PREAMBLE_LEN,
                'IDr' => self::IDr_LEN,
                'IDs' => self::IDs_LEN,
                'BODY_LEN' => self::BODY_LEN_LEN,
                'CSd' => self::CSd_LEN,
                'CSp' => self::CSp_LEN
            ];

            $header = '';
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

            }
            $this->setHeader($header);
        }
        protected function readBody($accept)
        {
            if (!$this->getSocket())
            {
                throw new Exception('Сокет не установлен');
            }

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
        }

        private function generateHandshake()
        {
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
                throw new Exception('Неверное значение преамбулы, ожидалось ' . self::PREAMBLE_VAL . ' получено ' . $preamble, -26);
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
         * @throws \Exception
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
            $this->log('IMEI: ' . $imei);
            $this->imei = $imei;
        }


        /**
         * Вывести сообщение в консоли
         *
         * @param $message
         */
        public function log($message)
        {
            if ($message == 'Success') return;
            if ($this->_debug)
            {
                $output = $output_console = '[' . date('H:i:s') . '] ' . $message . "\n";

                if (OS == 'win')
                {
                    $output_console = iconv(mb_detect_encoding($output), 'cp866', $output);
                }

                echo $output_console;
                file_put_contents(__DIR__ . SLASH . 'logs/' . date('Y-m-d') . '.log', $output, FILE_APPEND);
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

        protected function export(array $telemetry, $pref)
        {
            $this->log('Экспорт данных в бд');

            if (empty($telemetry) || !count($telemetry))
            {
                $this->log('Нечего сохранять');
                return false;
            }

            $dbhost = "localhost";
            $dbname = "getpart";
            $dbuser = "getpart2";
            $dbpswd = "BiBxzE";

            try {

                $db = new PDO("mysql:host=".$dbhost.";dbname=".$dbname,$dbuser,$dbpswd);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                $db->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,'SET NAMES UTF8');

            } catch (PDOException $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            /** @var telemetry_flex_v10 $t */
            foreach($telemetry as $t)
            {
                if ($this->getImei()  === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передан IMEI ' . var_dump($this->getImei()), -55);
                }

                if ($pref  === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передан префикс запроса ' . var_dump($pref), -55);
                }

                if ($t->getNumPage() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передан номер ' . var_dump($t->getNumPage()), -55);
                }

                if ($t->getCode() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передан код события ' . var_dump($t->getCode()), -55);
                }

                if ($t->getTime() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передано время события ' . var_dump($t->getTime()), -55);
                }

                if ($t->getModule1() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передано состояние функциональных модулей 1 ' . var_dump($t->getGSM()), -55);
                }

                if ($t->getGSM() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передан уровень сигнала ' . var_dump($t->getGSM()), -55);
                }

                if ($t->getLastTime()  === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передано время последних координат ' . var_dump($t->getLastTime()), -55);
                }

                if ($t->getLat() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передана широта ' . var_dump($t->getLat()), -55);
                }

                if ($t->getLon() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передана долгота ' . var_dump($t->getLon()), -55);
                }

                if ($t->getAlt() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передана высота ' . var_dump($t->getAlt()), -55);
                }

                if ($t->getCourse() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передан курс ' . var_dump($t->getCourse()), -55);
                }

                if ($t->getMileage() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передан пробег ' . var_dump($t->getMileage()), -55);
                }

                if ($t->getCANEngineTurns() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не переданы обороты двигателя ' . var_dump($t->getCANEngineTurns()), -55);
                }

                if ($t->getCANTemp() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передана температура ОЖ ' . var_dump($t->getCANTemp()), -55);
                }

                if ($t->getCANEngineLoad() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передана нагрузка на двигатель ' . var_dump($t->getCANEngineLoad()), -55);
                }

                if ($t->getCANSpeed() === false)
                {
                    throw new Exception('Неверная конфигурация датчика, не передана скорость ' . var_dump($t->getCANSpeed()), -55);
                }

                try
                {
                    $stmt = $db->prepare('SELECT 1 FROM ntcb WHERE `IMEI` = ? AND `numPage` = ? LIMIT 1');
                    $stmt->bindValue(1, $this->getImei(), PDO::PARAM_INT);
                    $stmt->bindValue(2, $t->getNumPage(),  PDO::PARAM_INT);
                    $stmt->execute();
                    $exist = $stmt->rowCount();
                } catch (PDOException $e)
                {
                    throw new Exception($e->getMessage(), $e->getCode());
                }

                if ($exist)
                {
                    $this->log('Данная запись (' . $t->getNumPage() . ') уже есть в бд, пропускаем...');
                    continue;
                }

                /**
                 * Записываем:
                 * IMEI - уникальный идентификатор устройства (int 15)
                 * reqType - тип запроса (телеметрические данные или тревожное сообщение)  (char 2)
                 * numPage - уникальный ID записи (unsigned int 4)
                 * Code - код события (unsigned int 2)
                 * Time - время события (unsigned int 4 или timestamp)
                 * GSM - уровень сигнала (unsigned int 1)
                 * LastTime - время последних валидных координат (unsigned int 4 или timestamp)
                 * Lat - широта (signed int 4)
                 * Lon - долгота (signed int 4)
                 * Alt - высота (signed int 4)
                 * Course - куср (в градусах) (unsigned int 2)
                 * Mileage - текущий пробег в км (float 4 bytes)
                 * CAN_EngineTurns - обороты двигателя (unsigned int 2)
                 * CAN_Temp - температура охлаждающей жидкости в цельсиях (signed int 1)
                 * CAN_EngineLoad - нагрузка на двигатель в процентах (unsigned int 1)
                 * CAN_Speed - скорость (unsigned int 1)
                 */
                try
                {
                    $stmt = $db->prepare('
                        INSERT INTO ntcb
                            (`IMEI`, `reqType`, `numPage`, `Code`, `Module1GSM`, `Module1USB`, `Module1Watch`, `Module1SIM`, `Module1Network`, `Module1Roaming`, `Module1Engine`, `Time`, `GSM`, `LastTime`, `Lat`, `Lon`, `Alt`, `Course`, `Mileage`, `CAN_EngineTurns`, `CAN_Temp`, `CAN_EngineLoad`, `CAN_Speed`)
                        VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ');

                    $stmt->bindValue(1, $this->getImei(), PDO::PARAM_INT);
                    $stmt->bindValue(2, $pref, PDO::PARAM_STR);
                    $stmt->bindValue(3, $t->getNumPage(), PDO::PARAM_INT);
                    $stmt->bindValue(4, $t->getCode(), PDO::PARAM_INT);
                    $stmt->bindValue(5, intval($t->getModule1()[0]), PDO::PARAM_INT);
                    $stmt->bindValue(6, intval($t->getModule1()[1]), PDO::PARAM_INT);
                    $stmt->bindValue(7, intval($t->getModule1()[3]), PDO::PARAM_INT);
                    $stmt->bindValue(8, intval($t->getModule1()[4]), PDO::PARAM_INT);
                    $stmt->bindValue(9, intval($t->getModule1()[5]), PDO::PARAM_INT);
                    $stmt->bindValue(10, intval($t->getModule1()[6]), PDO::PARAM_INT);
                    $stmt->bindValue(11, intval($t->getModule1()[7]), PDO::PARAM_INT);
                    $stmt->bindValue(12, $t->getTime(), PDO::PARAM_INT);
                    $stmt->bindValue(13, $t->getGSM(), PDO::PARAM_INT);
                    $stmt->bindValue(14, $t->getLastTime(), PDO::PARAM_INT);
                    $stmt->bindValue(15, $t->getLat(), PDO::PARAM_INT);
                    $stmt->bindValue(16, $t->getLon(), PDO::PARAM_INT);
                    $stmt->bindValue(17, $t->getAlt(), PDO::PARAM_INT);
                    $stmt->bindValue(18, $t->getCourse(), PDO::PARAM_INT);
                    $stmt->bindValue(19, $t->getMileage(), PDO::PARAM_INT);
                    $stmt->bindValue(20, $t->getCANEngineTurns(), PDO::PARAM_INT);
                    $stmt->bindValue(21, $t->getCANTemp(), PDO::PARAM_INT);
                    $stmt->bindValue(22, $t->getCANEngineLoad(), PDO::PARAM_INT);
                    $stmt->bindValue(23, $t->getCANSpeed(), PDO::PARAM_INT);
                    $insert = $stmt->execute();
                } catch (PDOException $e)
                {
                    throw new Exception($e->getMessage(), $e->getCode());
                }

                if ($insert === false)
                {
                    throw new Exception('Ошибка при insert данных', -55);
                }

                $this->log('Сохранили запись ' . $t->getNumPage());
            }

            return true;
        }
    }