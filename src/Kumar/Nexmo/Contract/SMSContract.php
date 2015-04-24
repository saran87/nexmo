<?php
/**
 * Created by PhpStorm.
 * User: Kumar
 * Date: 9/15/14
 * Time: 8:26 PM
 */

namespace Kumar\Nexmo\Contract;


interface SMSContract {
    /**
     * @param      $to
     * @param      $text
     * @param null $from
     *
     * Send text message
     *
     * @return mixed
     */
    public function sendText($to, $text, $from = null);

    /**
     * @param      $to
     * @param      $text
     * @param null $from
     *
     * @return mixed
     */
    public function sendUniCodeText($to, $text, $from = null);

    /**
     * @param      $to
     * @param      $body
     * @param      $udh
     * @param null $from
     *
     * @return mixed
     */
    public function sendBinary($to, $body, $udh, $from = null);

    /**
     * @param      $to
     * @param      $title
     * @param      $url
     * @param int  $validity
     * @param null $from
     *
     * @return mixed
     */
    public function sendWap($to, $title, $url, $validity = 172800000, $from = null);

    /**
     * @param      $to
     * @param array $data
     * @param null $shortCode
     *
     * @return mixed
     */
    public function sendAlert($to, $data, $shortCode = null);

    /**
     * Get the inbound message
     *
     * @return \Kumar\Nexmo\NexmoMessage
     */
    public function getInboundMessage();

    /**
     * Get the delivery report
     *
     * @return mixed
     */
    public function getDeliveryReport();

} 