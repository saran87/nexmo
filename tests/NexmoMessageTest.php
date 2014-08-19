<?php

use \Kumar\Nexmo\NexmoClient;

class NexmoMessageTest extends PHPUnit_Framework_TestCase{

    public function test_can_be_sent(){
        $nexmoMessage = new NexmoClient('4b25fb68', '21c99c33');
        $nexmoMessage->setFrom('15853715085');
        $response = $nexmoMessage->sendText('15857646580', 'Test Message');

        print_r($response);
        $this->assertEquals($response->status,0);
    }

    public function test_sending_to_wrong_number(){
        $nexmoMessage = new NexmoClient('4b25fb68', '21c99c33');
        $nexmoMessage->setFrom('15853715085');
        $response = $nexmoMessage->sendText('1585764658', 'Test Message');

        print_r($response);
        $this->assertEquals($response->status,1);
    }

} 