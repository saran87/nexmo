<?php

    namespace Kumar\Nexmo;

    use Illuminate\Events\Dispatcher;
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
         * The event dispatcher instance.
         *
         * @var \Illuminate\Events\Dispatcher
         */
        protected $events;

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
         * @param Dispatcher $events
         *
         * @return $this
         */
        public function setEventDispatcher( Dispatcher $events = null){
            $this->events = $events;

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

            $response = $this->sendMessage(SMSCommand::SEND_SMS, $data);

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

            return $this->parse($this->sendMessage(SMSCommand::SEND_SMS, $data));
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

            return $this->parse($this->sendMessage(SMSCommand::SEND_SMS, $data));
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

            return $this->parse($this->sendMessage(SMSCommand::SEND_SMS, $data));

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

            return $this->parse($this->sendMessage(SMSCommand::SEND_ALERT, $data));
        }

        /**
         * @param bool $data
         *
         * @return SMSReceipt|mixed
         */
        public function getDeliveryReport($data = false)
        {
           return new SMSReceipt($data);
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
         * Queue a new sms message for sending.
         *
         * @param  string  $command
         * @param  array   $data
         * @param  string  $queue
         * @return void
         */
        public function queue($command, array $data, $queue = null)
        {
            $this->queue->push('Kumar\Nexmo\NexmoProvider@handleQueuedMessage', compact('command', 'data'), $queue);
        }

        /**
         * Queue a new sms message for sending on the given queue.
         *
         * @param  string  $queue
         * @param  string  $command
         * @param  array   $data
         * @return void
         */
        public function queueOn($queue, $command, array $data)
        {
            $this->queue($command, $data, $queue);
        }

        /**
         * Queue a new sms message for sending after (n) seconds.
         *
         * @param  int  $delay
         * @param  string  $command
         * @param  array  $data
         * @param  string  $queue
         * @return void
         */
        public function later($delay, $command, array $data, $queue = null)
        {
            $this->queue->later($delay, 'Kumar\Nexmo\NexmoProvider@handleQueuedMessage', compact('command', 'data'), $queue);
        }

        /**
         * Queue a new sms message for sending after (n) seconds on the given queue.
         *
         * @param  string  $queue
         * @param  int  $delay
         * @param  string $command
         * @param  array  $data
         * @return void
         */
        public function laterOn($queue, $delay, $command, array $data)
        {
            $this->later($delay, $command, $data, $queue);
        }

        /**
         * Build the callable for a queued e-mail job.
         *
         * @param  mixed  $callback
         * @return mixed
         */
        protected function buildQueueCallable($callback)
        {
            if ( ! $callback instanceof \Closure) return $callback;

            return \serialize(new SerializableClosure($callback));
        }

        /**
         * Handle a queued e-mail message job.
         *
         * @param  \Illuminate\Queue\Jobs\Job  $job
         * @param  array  $data
         * @return void
         */
        public function handleQueuedMessage($job, $data)
        {
            $this->sendData($data['command'], $data['data']);

            $job->delete();
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

        private function sendMessage($command, $data){

            if ($this->events)
            {
                $this->events->fire('sms.sending', ['command' =>$command, 'data' =>$data]);
            }

            if($this->pretending){
                $response = $this->logMessage($command, $data);
            }else{
                $response = $this->sendData($command, $data);
            }

            if ($this->events)
            {
                $this->events->fire('sms.sent',  ['command' =>$command, 'data' =>$data, 'response' =>$response]);
            }
            return $response;
        }

        /**
         * @param $command
         * @param $data
         */
        private function logMessage($command, $data){

            if($this->logger){

                $message = implode(', ', $data);

                $this->logger->info("Pretending to {$command} to: {$message}");
            }

            $response = "{'message-count':'1', 'messages' : [{'messageId': 'pretend', 'status': 'pretend'}]}";

            $responseStream = Stream::factory($response);
            // print_r($responseStream);
            try{
                $response = new Response(200);
            }
            catch(\Exception $ex){
                print_r($ex);
            }

            print_r($response);


        }

    }