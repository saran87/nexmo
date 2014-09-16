<?php

    namespace Kumar\Nexmo;

    use Illuminate\Log\Writer;
    use Illuminate\Queue\QueueManager;
    use Kumar\Nexmo\Common\SMSCommand;
    use Kumar\Nexmo\Common\SMSMessage;
    use Kumar\Nexmo\Common\SMSReceipt;
    use Kumar\Nexmo\Common\SMSResponse;
    use Kumar\Nexmo\Contract\SMSContract;
    use Kumar\Nexmo\Exception\NexmoMessageException;

    class NexmoProvider extends NexmoClient implements SMSContract
    {

        /**
         * @var string from
         */
        private $from = '';
        /**
         * @var string short code
         */
        private $shortCode = '';

        /**
         * The log writer instance.
         *
         * @var \Illuminate\Log\Writer
         */
        protected $logger;

        /**
         * The QueueManager instance.
         *
         * @var \Illuminate\Queue\QueueManager
         */
        protected $queue;

        /**
         * Indicates if the actual sending is disabled.
         *
         * @var bool
         */
        protected $pretending = false;

        /**
         * @param $from
         */
        public function setFrom($from)
        {
            $this->from = $from;
        }

        /**
         * @return string From address
         */
        public function getFrom()
        {
            return $this->from;
        }

        /**
         * @param $shortCode
         */
        public function setShortCode($shortCode)
        {
            $this->shortCode = $shortCode;
        }

        /**
         * @return string shortCode
         */
        public function getShortCode()
        {
            return $this->shortCode;
        }

        /**
         * Set the log writer instance.
         *
         * @param  \Illuminate\Log\Writer  $logger
         * @return $this
         */
        public function setLogger(Writer $logger)
        {
            $this->logger = $logger;

            return $this;
        }

        /**
         * Set the queue manager instance.
         *
         * @param  \Illuminate\Queue\QueueManager  $queue
         * @return $this
         */
        public function setQueue(QueueManager $queue)
        {
            $this->queue = $queue;

            return $this;
        }

        /**
         * Tell the mailer to not really send messages.
         *
         * @param  bool  $value
         * @return void
         */
        public function pretend($value = true)
        {
            $this->pretending = $value;
        }

        /**
         * @param      $to
         * @param      $text
         * @param null $from
         *
         * @throws \Kumar\Nexmo\Exception\NexmoMessageException
         * @return bool|void
         */
        public function sendText($to, $text, $from = null)
        {

            $from = $from ? : $this->from;

            $this->validateUTFEncoding($from);

            $this->validateUTFEncoding($text);

            /**
             * Sanitize and encode the url
             */
            $from = $this->sanitizePhone($from);
            $to =  $this->sanitizePhone($to);

            /**
             * prepare the data to be sent to nexmo
             */
            $data = $this->getNexmoData($from, $to, $text, 'text');

            $response = $this->sendData(SMSCommand::SEND_SMS, $data);

            return $this->parse($response);
        }

        /**
         * @param      $to
         * @param      $text
         * @param null $from
         *
         * @return mixed
         */
        public function sendUniCodeText($to, $text, $from = null)
        {
            $from = $from ? : $this->from;

            $this->validateUTFEncoding($from);

            $this->validateUTFEncoding($text);

            /**
             * Sanitize and encode the url
             */
            $from = $this->sanitizePhone($from);
            $to =  $this->sanitizePhone($to);

            /**
             * prepare the data to be sent to nexmo
             */
            $data = $this->getNexmoData($from, $to, $text, 'unicode');

            return $this->parse($this->sendData(SMSCommand::SEND_SMS, $data));
        }

        /**
         * @param      $to
         * @param      $body
         * @param null $udh
         * @param null $from
         *
         * @return bool|mixed
         */
        public function sendBinary($to,$body, $udh, $from = null)
        {
            $from = $from ? : $this->from;
            //Binary messages must be hex encoded
            $body = bin2hex($body);
            $udh = bin2hex($udh);

            // Make sure $from is valid
            $from = $this->sanitizePhone($from);
            $to =  $this->sanitizePhone($to);

            $data = array(
                'from' => $from,
                'to'   => $to,
                'type' => 'binary',
                'body' => $body,
                'udh'  => $udh
            );

            return $this->parse($this->sendData(SMSCommand::SEND_SMS, $data));
        }

        /**
         * Prepare new binary message.
         */
        public function sendWap($to, $title, $url, $validity = 172800000, $from = null)
        {

            $from = $from ? : $this->from;

            $this->validateUTFEncoding($from);

            $this->validateUTFEncoding($title);

            $this->validateUTFEncoding($url);

            // Make sure $from is valid
            $from = $this->sanitizePhone($from);

            $data =  $this->getNexmoData($from, $to, 'wappush', $title, $url, $validity);

            return $this->parse($this->sendData(SMSCommand::SEND_SMS, $data));

        }

        /**
         * @param      $to
         * @param      $parts
         * @param null $shortCode
         *
         * @return mixed
         * @throws \Kumar\Nexmo\Exception\NexmoMessageException
         */
        public function sendAlert($to, $parts, $shortCode = null)
        {
            $shortCode = $shortCode ? : $this->shortCode;

            $this->validateUTFEncoding($shortCode);

            $data = $this->getNexmoData($shortCode, $this->sanitizePhone($to));

            foreach ($parts as $key => $value) {
                $this->validateUTFEncoding($value);

                $data[$key] = $value;
            }

            return $this->parse($this->sendData(SMSCommand::SEND_ALERT, $data));
        }

        /**
         * Get the delivery report
         *
         * @return mixed
         */
        public function getDeliveryReport()
        {
           return new SMSReceipt();
        }


        /**
         * @return SMSMessage
         */
        public function getInboundMessage()
        {
            return new SMSMessage();
        }

        /**
         * @param \GuzzleHttp\Message\ResponseInterface $response
         *
         * @return mixed
         * @throws \Kumar\Nexmo\Exception\NexmoMessageException
         */
        private function parse($response)
        {
            $responseArray = [];

            if ($response->getStatusCode() == 200) {

                $data = $response->json();
                if (array_key_exists('message-count', $data)) {

                    foreach ($data['messages'] as $message) {
                       $smsResponse = new SMSResponse($message);
                        if ($smsResponse->status == 0) {
                            $responseArray[] = $smsResponse;
                        } else {
                            throw new NexmoMessageException($smsResponse->getStatusMessage());
                        }
                    }

                    return $responseArray;
                }
            }
        }

        /**
         * @param $value
         *
         * Validate the value for UTF encoding
         *
         * @throws \Kumar\Nexmo\Exception\NexmoMessageException
         */
        private function validateUTFEncoding($value)
        {
            if (!mb_check_encoding($value, 'UTF-8')) {
                throw new NexmoMessageException('invalid UTF-8 encoded string');
            }
        }

        /**
         * @param      $from
         * @param      $to
         * @param null $text
         * @param null $type
         * @param null $title
         * @param null $url
         * @param null $validity
         *
         * @return array
         */
        private function getNexmoData($from, $to, $text = null, $type = null, $title = null, $url = null, $validity = null)
        {

            $data = ['from' => $from,
                     'to'   => $to
                    ];

            if($text){
                $data['text'] = $text;
            }

            if($type){
                $data['type'] = $type;
            }

            if($title){
                $data['title'] = $title;
            }

            if($url){
                $data['url'] = $url;
            }

            if($validity){
                $data['validity'] =  $validity;
            }

            return $data;
        }

    }