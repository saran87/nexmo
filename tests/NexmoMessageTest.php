<?php

use \Kumar\Nexmo\NexmoClient;

class NexmoMessageTest extends PHPUnit_Framework_TestCase{

    public function test_can_be_sent(){
        $nexmoMessage = new NexmoClient('', '');
        $nexmoMessage->setFrom('');
        $response = $nexmoMessage->sendText('', 'Test Message');

        print_r($response);
        $this->assertEquals($response->status,0);
    }

    public function test_sending_to_wrong_number(){
        $nexmoMessage = new NexmoClient('', '');
        $nexmoMessage->setFrom('');
        $response = $nexmoMessage->sendText('', 'Test Message');

        print_r($response);
        $this->assertEquals($response->status,1);
    }

} 