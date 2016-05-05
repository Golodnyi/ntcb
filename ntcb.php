<?php

    /**
     * User: golodnyi
     * Date: 04.05.16
     * Time: 9:12
     * NTCB - binary protocol for Signal S-2551
     */
    class ntcb
    {
        const HEADER_LEN = 16;   // размер заголовка в байтах
        const PREAMBLE_LEN = 4;    // размер преамбулы в байтах
        const IMEI_LEN = 15;   // размер блока с IMEI в байтах
        const PREF_IMEI_LEN = 4;    // размер блока с префиксом IMEI в байтах
        const IMEI_BLOCK_LEN = self::PREF_IMEI_LEN + self::IMEI_LEN; // общий размер блока с IMEI в байтах
        const PREF_IMEI_VAL = '*>S'; // значение префикса для IMEI от датчика

        private $_socket;       // ссылка на сокет
        private $_address;      // адрес сокета
        private $_port;         // порт сокета
        private $_debug;        // debug true|false

        private $_header;       // заголовок запроса (16 byte)
        private $_preamble;     // преамбула char(4) (4 byte)
        private $_idr;          // идентификатор получателя U32 (4 byte)
        private $_ids;          // идентификатор отправителя U32 (4 byte)
        private $_body_size;    // размер тела запроса в байтах U16 (2 byte)
        private $_csd;          // контрольная сумма тела запроса U8 (1 byte)
        private $_csp;          // контрольная сумма заголовка U8 (1 byte)

        private $imei;          // IMEI номер датчика

        private $_body;         // тело запроса

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
        private function setBody($body)
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
        private function setDebug($debug)
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
         * разбор header по переменным
         *
         * @param array $buf
         */
        private function unpackHeader($bufLen)
        {
            if (!strlen($bufLen) || strlen($bufLen) < self::HEADER_LEN)
            {
                throw new Exception('Empty data or incorrect length', -3);
            }
            $this->log('unpack header data');

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
            for ($i = 0; $i < self::PREAMBLE_LEN; $i++)
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

        private function unpackImei($bufLen)
        {
            $this->log('unpack IMEI');

            if (!strlen($bufLen) || strlen($bufLen) < (self::HEADER_LEN + self::IMEI_BLOCK_LEN))
            {
                throw new Exception('Empty data or length < IMEI LEN', -3);
            }

            if (empty($this->getBodySize()))
            {
                $this->log('body is empty');

                return false;
            }

            $bufLen = substr($bufLen, self::HEADER_LEN, self::HEADER_LEN + self::IMEI_BLOCK_LEN);

            if ($bufLen === false)
            {
                throw new Exception('substr return error', -21);
            }

            $unpack = unpack("c4prefix/c15IMEI", $bufLen);

            $pref_imei = '';
            for ($i = 0; $i < self::PREF_IMEI_LEN; $i++)
            {
                if (!isset($unpack['prefix' . $i]))
                {
                    throw new Exception('prefix IMEI ' . $i . ' not isset', -19);
                }

                $pref_imei .= chr($unpack['prefix' . $i]);
            }

            if ($pref_imei != self::PREF_IMEI_VAL)
            {
                throw new Exception('pref imei is not valid', -21);
            }

            $imei = '';
            for ($i = 0; $i < self::IMEI_LEN; $i++)
            {
                if (!isset($unpack['imei' . $i]))
                {
                    throw new Exception('IMEI ' . $i . ' not isset', -20);
                }

                $imei .= chr($unpack['imei' . $i]);
            }

            try
            {
                $this->setImei($imei);
            } catch (Exception $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            if ($unpack === false)
            {
                throw new Exception('Unpack error', -18);
            }

            $this->log('unpack IMEI success');
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
                    throw new Exception(socket_strerror(socket_last_error($this->getSocket())),
                        socket_last_error($this->getSocket()));
                }
                $this->log('connected!');

                $this->log('receiving data...');
                $bufLen = '';
                while (($buf = socket_read($accept, 1)))
                {
                    if ($buf === false)
                    {
                        throw new Exception(socket_strerror(socket_last_error($this->getSocket())),
                            socket_last_error($this->getSocket()));
                    }
                    $bufLen .= $buf;
                }
                $this->log('received ' . strlen($bufLen) . ' bytes');
                $this->log('finished the receive data');

                socket_close($accept);
                $this->log('close the connection!');

                try
                {
                    $this->unpackHeader($bufLen);
                    $this->unpackImei($bufLen);
                } catch (Exception $e)
                {
                    throw new Exception($e->getMessage(), $e->getCode());
                }

                if ($this->_debug)
                {
                    $mt = microtime();
                    file_put_contents(__DIR__ . SLASH . $mt . '_array.log', print_r($buf, true), FILE_APPEND);
                    file_put_contents(__DIR__ . SLASH . $mt . '_string.log', print_r(implode('', $buf), true),
                        FILE_APPEND);
                }
            }
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
        private function setSocket($socket)
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
        private function setAddress($address)
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
        private function setPort($port)
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
         */
        private function setHeader($header)
        {
            if (strlen($header) < self::HEADER_LEN)
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
         */
        private function setPreamble($preamble)
        {
            if (strlen($preamble) < self::PREAMBLE_LEN)
            {
                throw new Exception('Preamble uncorrected length', -5);
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
         */
        private function setIdr($idr)
        {
            if (!is_int($idr))
            {
                throw new Exception('IDr not int', -8);
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
         */
        private function setIds($ids)
        {
            if (!is_int($ids))
            {
                throw new Exception('IDs not int', -9);
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
            return $this->_body_size;
        }

        /**
         * set size of body request (in bytes)
         *
         * @param mixed $body_size
         */
        private function setBodySize($body_size)
        {
            if (!is_int($body_size))
            {
                throw new Exception('body size not int', -8);
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
         */
        private function setCsd($csd)
        {
            if (!is_int($csd))
            {
                throw new Exception('csd not int', -14);
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
         */
        private function setCsp($csp)
        {
            if (!is_int($csp))
            {
                throw new Exception('csp not int', -15);
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
         */
        private function setImei($imei)
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
        private function log($message)
        {
            if ($this->_debug)
            {
                echo '[' . date(DATE_W3C) . '] ' . $message . "\n";
            }
        }

    }