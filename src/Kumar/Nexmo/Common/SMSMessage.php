<?php


namespace Kumar\Nexmo\Common;

use Kumar\Nexmo\Exception\NexmoMessageException;

class SMSMessage extends SMSObject{

    public function __construct($data = false)
    {
        if (!$data) $data = $_REQUEST;

        if (!isset($data['type'])) {
            throw new NexmoMessageException("Invalid SMSMessage format");
        }

        parent::__construct($data);
        // Flag that a receipt was found
        $this->setAttribute('isExit',true);
    }


    /**
     * Returns true if a valid receipt is found
     */
    public function isText()
    {
        return (array_key_exists('type', $this->attributes) &&  $this->attributes['type'] == 'text');
    }
}
