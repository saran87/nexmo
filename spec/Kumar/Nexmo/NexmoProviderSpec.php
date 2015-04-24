<?php

namespace spec\Kumar\Nexmo;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NexmoProviderSpec extends ObjectBehavior
{
    private $config = ['key' => '',
                       'secret' => '',
                       'from' => '',
                       'to' => '',
                       'shortcode'=> '',
                       'pretend' =>  false];

    function  let(){
        $key = $this->config['key'];
        $secret = $this->config['secret'];
        $this->beConstructedWith($key,$secret);
        $this->pretend($this->config['pretend']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Kumar\Nexmo\NexmoProvider');
    }

    function it_should_set_from(){
        $this->setFrom($this->config['from']);

        $this->getFrom()->shouldBe($this->config['from']);
    }

    function it_should_set_short_code(){
        $this->setShortCode($this->config['to']);

        $this->getShortCode()->shouldBe($this->config['to']);
    }


    function it_will_throw_exception_getting_delivery_report(){
        $this->shouldThrow('Kumar\Nexmo\Exception\NexmoMessageException')->duringGetDeliveryReport();
    }

    function it_can_recieve_delivery_report(){
        $this->getDeliveryReport(['status' => 1])->shouldReturnAnInstanceOf('Kumar\Nexmo\Common\SMSReceipt');
    }

    /**
    function it_can_throw_exception_getting_inbound_message(){
        $this->shouldThrow('Kumar\Nexmo\Exception\NexmoMessageException')->duringGetInboundMessage();
    }**/

    function it_can_recieve_inbound_message(){
        $message = $this->getInboundMessage(['type' => 'text']);
        $message->shouldReturnAnInstanceOf('Kumar\Nexmo\Common\SMSMessage');
    }

    function it_will_through_MessageException(){

        $to = $this->config['to'];
        $text = "Hello text message text message";
        $this->pretend(false);
        $this->shouldThrow('Kumar\Nexmo\Exception\NexmoMessageException')->duringSendText($to, $text);
    }

    function it_can_send_sms(){
        $this->setFrom($this->config['from']);
        $to = $this->config['to'];
        $text = "Hello text message text message";
        $this->sendText($to, $text)->shouldBeArray();

    }

    function it_will_through_MessageException_while_alert(){

        $this->setShortCode($this->config['shortcode']);
        $to = $this->config['to'];
        $parts = ['message' => 'Hello text message text message'];
        $this->pretend(false);
        $this->shouldThrow('Kumar\Nexmo\Exception\NexmoMessageException')->duringSendAlert($to, $parts);
    }

    function it_can_send_alert(){

        $this->setShortCode($this->config['shortcode']);
        $to = $this->config['to'];
        $parts = ['sender'=>'kumar', 'message' => 'Hello text message text message'];
        $this->sendAlert($to, $parts)->shouldBeArray();
    }


}
