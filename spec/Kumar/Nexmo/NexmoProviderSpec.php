<?php

namespace spec\Kumar\Nexmo;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NexmoProviderSpec extends ObjectBehavior
{
    function  let(){
        $key = '4b25fb68';
        $secret = '21c99c33';
        $this->beConstructedWith($key,$secret);

    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Kumar\Nexmo\NexmoProvider');
    }

    function it_should_set_from(){
        $this->setFrom('15853715085');

        $this->getFrom()->shouldBe('15853715085');
    }

    function it_should_set_short_code(){
        $this->setShortCode('96167');

        $this->getShortCode()->shouldBe('96167');
    }

    function it_will_through_MessageException(){

        $to = "15857646580";
        $text = "Hello text message text message";
        $this->shouldThrow('Kumar\Nexmo\Exception\NexmoMessageException')->duringSendText($to, $text);
    }

    function it_can_send_sms(){
        $this->setFrom('15853715085');
        $to = "15857646580";
        $text = "Hello text message text message";
        $this->sendText($to, $text)->shouldBeArray();

    }

    function it_will_through_MessageException_while_alert(){

        $this->setShortCode('96167');
        $to = "15857646580";
        $parts = ['message' => 'Hello text message text message'];
        $this->shouldThrow('Kumar\Nexmo\Exception\NexmoMessageException')->duringSendAlert($to, $parts);
    }

    function it_can_send_alert(){

        $this->setShortCode('96167');
        $to = "15857646580";
        $parts = ['sender'=>'kumar', 'message' => 'Hello text message text message'];
        $this->sendAlert($to, $parts)->shouldBeArray();
    }
}
