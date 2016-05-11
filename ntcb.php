<?php
    error_reporting(E_ALL);
    /**
     * User: golodnyi
     * Date: 04.05.16
     * Time: 9:12
     * NTCB - binary protocol for Signal S-2551
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
         * get request body
         *
         * @return mixed
         */
        public function getBody()
        {
            return $this->_body;
        }

        /**
         * set request body
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
            $this->log('run from: ' . __DIR__ . SLASH);
            $this->log('set debug: ' . print_r($debug, true));
        }

        /**
         * get debug
         *
         * @return mixed
         */
        public function getDebug()
        {
            return $this->_debug;
        }

        /**
         * set debug
         *
         * @param mixed $debug
         */
        protected function setDebug($debug)
        {
            $this->_debug = $debug;
        }

        /**
         * set listen address and port
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

            $this->log('connect to socket: ' . print_r($socket, true));

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

            $this->log('listen ' . $this->getAddress() . ':' . $this->getPort());

            $this->setSocket($socket);
        }

        /**
         * @param $bufLen
         *
         * @throws \Exception
         */
        protected function unpackHeader($bufLen)
        {
            $this->log('unpack header data');

            if (!strlen($bufLen) || strlen($bufLen) < self::HEADER_LEN)
            {
                throw new Exception('Empty data or incorrect length', -3);
            }

            $bufLen = substr($bufLen, 0, self::HEADER_LEN);

            if ($bufLen === false)
            {
                throw new Exception('substr return error', -21);
            }

            $unpack = unpack("c4preamble/LIDr/LIDs/Ssize/cCSd/cCSp", $bufLen);

            if ($unpack === false)
            {
                throw new Exception('Unpack error', -4);
            }

            $preamble = '';
            for ($i = 1; $i <= self::PREAMBLE_LEN; $i++)
            {
                if (!isset($unpack['preamble' . $i]))
                {
                    throw new Exception('preamble ' . $i . ' not isset', -6);
                }

                $preamble .= chr($unpack['preamble' . $i]);
            }

            try
            {
                $this->setPreamble($preamble);

                if (!isset($unpack['IDr']))
                {
                    throw new Exception('IDr not isset', -7);
                }
                $this->setIdr($unpack['IDr']);

                if (!isset($unpack['IDs']))
                {
                    throw new Exception('IDs not isset', -8);
                }
                $this->setIdr($unpack['IDs']);

                if (!isset($unpack['size']))
                {
                    throw new Exception('body size not isset', -10);
                }
                $this->setBodySize($unpack['size']);

                if (!isset($unpack['CSd']))
                {
                    throw new Exception('CSd not isset', -12);
                }
                $this->setCsd($unpack['CSd']);

                if (!isset($unpack['CSp']))
                {
                    throw new Exception('CSp not isset', -13);
                }
                $this->setCsp($unpack['CSp']);

                $this->setHeader($bufLen);
            } catch (Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            $this->log('unpack header success');
        }

        protected function unpackImei()
        {
            $this->log('unpack IMEI');

            if (!$this->getBodySize())
            {
                throw new Exception('Empty body data', -3);
            }

            $buffer = substr($this->getBody(), 0, self::PREF_IMEI_LEN);

            if ($buffer === false)
            {
                throw new Exception('substr return error', -21);
            }

            if ($buffer != self::PREF_IMEI_VAL)
            {
                throw new Exception('IMEI not received', -20);
            }

            $buffer = substr($this->getBody(), self::PREF_IMEI_LEN + 1, self::IMEI_LEN);

            try
            {
                $this->setImei($buffer);
            } catch (Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            $this->log('unpack IMEI success');

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
                throw new Exception('Is not connected to the socket, you do not assign the listener?', -1);
            }

            $this->log('waiting for connection...');

            while (true)
            {
                if (($accept = socket_accept($this->getSocket())) === false)
                {
                    $this->log(socket_strerror(socket_last_error($this->getSocket())));
                    continue;
                }
                $this->log('connected!');

                try
                {
                    $this->readHeader($accept);

                    if (!$this->getBodySize())
                    {
                        $this->log('received only the header, there is no work');
                        socket_close($accept);
                        $this->log('close the connection!');
                        continue;
                    }

                    $this->readBody($accept);
                } catch (Exception $e)
                {
                    $this->log($e->getMessage());
                    socket_close($accept);
                    $this->log('close the connection!');
                    continue;
                }

                if ($this->xor_sum($this->getBody(), $this->getBodySize()) !== $this->getCsd())
                {
                    $this->log('CSd sum incorrect!');
                    socket_close($accept);
                    $this->log('close the connection!');
                    continue;
                }
                $this->log('CSd sum correct!');

                if ($this->xor_sum(substr($this->getHeader(), 0, self::HEADER_LEN - 1), self::HEADER_LEN - 1) !== $this->getCsp())
                {
                    $this->log('CSp sum incorrect!');
                    socket_close($accept);
                    $this->log('close the connection!');
                    continue;
                }
                $this->log('CSp sum correct!');

                try
                {
                    $this->unpackImei();
                    $this->sendHandshake($accept);
                    $this->readTelemetries($accept);
                } catch (Exception $e)
                {
                    $this->log($e->getMessage());
                    socket_close($accept);
                    $this->log('close the connection!');
                    continue;
                }

                socket_close($accept);
                $this->log('close the connection!');
            }
        }

        protected function readTelemetries($accept)
        {
            if (!$this->getSocket())
            {
                throw new Exception('socket is not set');
            }
        }
        protected function readHeader($accept)
        {
            if (!$this->getSocket())
            {
                throw new Exception('socket is not set');
            }

            $this->log('receiving handshake...');

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
                            $this->setIds(current(unpack('L', $buf)));
                            break;
                        case 'IDs':
                            $this->setIdr(current(unpack('L', $buf)));
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
            $this->log('received ' . strlen($header) . ' bytes');
            $this->log('finished the receive handshake');
        }
        protected function readBody($accept)
        {
            if (!$this->getSocket())
            {
                throw new Exception('socket is not set');
            }

            $this->log('receiving body...');

            $handle = fopen(__DIR__ . SLASH . microtime() . '_body.bin', 'wb');

            $buf = socket_read($accept, $this->getBodySize());
            if ($buf === false) {
                throw new Exception(socket_strerror(socket_last_error()), socket_last_error());
            }

            $this->setBody($buf);

            fwrite($handle, $buf, strlen($buf));
            fclose($handle);

            $this->log('received ' . strlen($buf) . ' bytes');
            $this->log('finished the receive body');
        }

        protected function generateHandshake()
        {
            $this->log('prepare handshake...');

            $preamble = self::PREAMBLE_VAL;
            $hs = self::HANDSHAKE_VAL;
            $body = '';
            for ($i = 0; $i < strlen($hs); $i++)
            {
                $body .= pack('c', $hs[$i]);
            }
            $binary = pack('cccc', $preamble[0], $preamble[1], $preamble[2], $preamble[3]);
            $binary .= pack('L', $this->getIds());
            $binary .= pack('L', $this->getIdr());
            $binary .= pack('S', strlen($body));
            $binary .= pack('C', $this->xor_sum($body, strlen($body)));
            $binary .= pack('C', $this->xor_sum($binary, strlen($binary)));
            $binary .= $body;
            $this->log('complete handshake...');

            return $binary;
        }

        protected function sendHandshake($accept)
        {
            if (!$this->getSocket())
            {
                throw new Exception('socket is not set');
            }

            $binary = $this->generateHandshake();

            if (($r = socket_write($accept, $binary, strlen($binary))) === false)
            {
                throw new Exception(socket_strerror(socket_last_error($this->getSocket())), socket_last_error($this->getSocket()));
            }

            if ($r != strlen($binary))
            {
                throw new Exception('send ' . $r , ' bytes, total (' . strlen($binary) . ')');
            }

            $this->log('send answer handshake ' . strlen($binary) . ' bytes');
        }

        /**
         * get resource socket
         *
         * @return mixed
         */
        public function getSocket()
        {
            return $this->_socket;
        }

        /**
         * set resource socket
         *
         * @param mixed $socket
         */
        protected function setSocket($socket)
        {
            $this->_socket = $socket;
        }

        /**
         * get listen address
         *
         * @return mixed
         */
        public function getAddress()
        {
            return $this->_address;
        }

        /**
         * set listen address
         *
         * @param mixed $address
         */
        protected function setAddress($address)
        {
            $this->_address = $address;
        }

        /**
         * get listen port
         *
         * @return mixed
         */
        public function getPort()
        {
            return $this->_port;
        }

        /**
         * set listen port
         *
         * @param mixed $port
         */
        protected function setPort($port)
        {
            $this->_port = $port;
        }

        /**
         * get request header
         *
         * @return mixed
         */
        public function getHeader()
        {
            return $this->_header;
        }

        /**
         * set request header
         *
         * @param mixed $header
         *
         * @throws \Exception
         */
        protected function setHeader($header)
        {
            if ($header !== false && strlen($header) < self::HEADER_LEN)
            {
                throw new Exception('header incorrect length', -23);
            }

            $this->_header = $header;
        }

        /**
         * get preamble
         *
         * @return mixed
         */
        public function getPreamble()
        {
            return $this->_preamble;
        }

        /**
         * set preamble
         *
         * @param mixed $preamble
         *
         * @throws \Exception
         */
        protected function setPreamble($preamble)
        {
            if (strlen($preamble) < self::PREAMBLE_LEN)
            {
                throw new Exception('Preamble uncorrected length', -5);
            }

            if ($preamble != self::PREAMBLE_VAL)
            {
                throw new Exception('Preamble value not incorrect', -26);
            }

            $this->_preamble = $preamble;
        }

        /**
         * get id recipient
         *
         * @return mixed
         */
        public function getIdr()
        {
            return $this->_idr;
        }

        /**
         * set id recipient
         *
         * @param mixed $idr
         *
         * @throws \Exception
         */
        protected function setIdr($idr)
        {
            if (!is_int($idr))
            {
                throw new Exception('IDr is not int', -8);
            }

            $this->_idr = $idr;
        }

        /**
         * get id sender
         *
         * @return mixed
         */
        public function getIds()
        {
            return $this->_ids;
        }

        /**
         * set id sender
         *
         * @param mixed $ids
         *
         * @throws \Exception
         */
        protected function setIds($ids)
        {
            if (!is_int($ids))
            {
                throw new Exception('IDs is not int', -8);
            }

            $this->_ids = $ids;
        }

        /**
         * get size of body request (in bytes)
         *
         * @return mixed
         */
        public function getBodySize()
        {
            return intval($this->_body_size);
        }

        /**
         * set size of body request (in bytes)
         *
         * @param mixed $body_size
         *
         * @throws \Exception
         */
        protected function setBodySize($body_size)
        {
            if (!is_int($body_size))
            {
                throw new Exception('body size is not int', -8);
            }

            $this->_body_size = $body_size;
        }

        /**
         * get control sum of body request
         *
         * @return mixed
         */
        public function getCsd()
        {
            return $this->_csd;
        }

        /**
         * set control sum of body request
         *
         * @param mixed $csd
         *
         * @throws \Exception
         */
        protected function setCsd($csd)
        {
            if (!is_int($csd))
            {
                throw new Exception('csd is not int', -14);
            }

            $this->_csd = $csd;
        }

        /**
         * get control sum of header request
         *
         * @return mixed
         */
        public function getCsp()
        {
            return $this->_csp;
        }

        /**
         * set control sum of header request
         *
         * @param mixed $csp
         *
         * @throws \Exception
         */
        protected function setCsp($csp)
        {
            if (!is_int($csp))
            {
                throw new Exception('csp is not int', -15);
            }

            $this->_csp = $csp;
        }

        /**
         * @return mixed
         */
        public function getImei()
        {
            return $this->imei;
        }

        /**
         * @param mixed $imei
         *
         * @throws \Exception
         */
        protected function setImei($imei)
        {
            if (strlen($imei) < self::IMEI_LEN)
            {
                throw new Exception('imei length is not ' . self::IMEI_LEN . ' chars');
            }

            $this->imei = $imei;
        }


        /**
         * write log to console
         *
         * @param $message
         */
        protected function log($message)
        {
            if ($this->_debug)
            {
                echo '[' . date(DATE_W3C) . '] ' . $message . "\n";
            }
        }

        /**
         * calculate hash
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

        /**
         * @return mixed
         */
        public function getFormat()
        {
            return $this->_format;
        }

        /**
         * @param mixed $format
         *
         * @throws \Exception
         */
        protected function setFormat($format)
        {
            if (strlen($format) != self::FORMAT_TYPE_LEN)
            {
                throw new Exception('format type length is not ' . self::FORMAT_TYPE_LEN . ' bytes');
            }

            $this->_format = $format;
        }


    }