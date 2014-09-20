<?php

    namespace Kumar\Nexmo\Common;
    use Kumar\Nexmo\Exception\NexmoMessageException;

    /**
     * Class SMSReport handles and incoming message receipts sent by Nexmo
     *
     * Usage: $var = new SMSReport ();
     * Methods:
     *     exists ( )
     *
     *
     */

    class SMSReceipt extends SMSObject
    {

        const STATUS_DELIVERED = 'DELIVERED';
        const STATUS_EXPIRED = 'EXPIRED';
        const STATUS_FAILED = 'FAILED';
        const STATUS_BUFFERED = 'BUFFERED';

        private $errorCodeMap = [
                        0 =>	'Delivered',
                        1 =>	'Unknown',
                        2 =>	'Absent Subscriber - Temporary',
                        3 =>	'Absent Subscriber - Permanent',
                        4 =>	'Call barred by user',
                        5 =>	'Portability Error',
                        6 =>	'Anti-Spam Rejection',
                        7 =>	'Handset Busy',
                        8 =>	'Network Error',
                        9 =>	'Illegal Number',
                        10 =>	'Invalid Message',
                        11 =>	'Unroutable',
                        12 =>	'Destination Un-Reachable',
                        13 =>	'Subscriber Age Restriction',
                        14 =>	'Number Blocked by Carrier',
                        15 =>	'Pre-Paid - Insufficent funds',
                        99 =>	'General Error',
                    ];
        public function __construct($data = false)
        {
            if (!$data) $data = $_REQUEST;

            if (!isset($data['status'])) {
                throw new NexmoMessageException("Invalid SMSReport format");
            }

            parent::__construct($data);
            // Flag that a receipt was found
            $this->setAttribute('isExit',true);
        }


        /**
         * Returns true if a valid receipt is found
         */
        public function exists()
        {
            return $this->isExit;
        }


        public function getStatusMessage(){

            return $this->errorCodeMap[$this->errCode];
        }
    }