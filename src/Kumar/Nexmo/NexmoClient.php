<?php namespace Kumar\Nexmo;

use GuzzleHttp\Client;
use Kumar\Nexmo\Exception\NexmoServiceException;
use stdClass;
use Whoops\Example\Exception;

/**
 * Class NexmoClient
 * @package Kumar\Nexmo
 */
abstract class NexmoClient
{

    /**
     * Nexmo account credentials
     *
     */
    private $nx_key = '';
    /**
     * @var string nexmo secret key
     */
    private $nx_secret = '';
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;
    /**
     * Nexmo Base URL
     */
    const BASE_URL = 'https://rest.nexmo.com/';

    private $COMMANDS = array (
        'get_balance' => array('method' => 'GET', 'url' => 'account/get-balance/{k}/{s}'),
        'get_pricing' => array('method' => 'GET', 'url' => 'account/get-pricing/outbound/{k}/{s}/{country_code}'),
        'get_own_numbers' => array('method' => 'GET', 'url' => 'account/numbers/{k}/{s}'),
        'search_numbers' => array('method' => 'GET', 'url' => 'number/search/{k}/{s}/{country_code}?pattern={pattern}'),
        'buy_number' => array('method' => 'POST', 'url' => 'number/buy/{k}/{s}/{country_code}/{msisdn}'),
        'cancel_number' => array('method' => 'POST', 'url' => 'number/cancel/{k}/{s}/{country_code}/{msisdn}'),
        'send_sms' => array('method' => 'POST','headers' => ['Content-Type' => 'application/x-www-form-urlencoded'], 'url' => 'sms/json'),
        'send_alert' => array('method' => 'POST', 'url' => 'sc/us/alert/json')
    );

    /**
     * @var bool
     */
    public $ssl_verify = false; // Verify Nexmo SSL before sending any message


    /**
     * @param $api_key
     * @param $api_secret
     */
    function __construct($api_key, $api_secret)
    {
        $this->nx_key = $api_key;
        $this->nx_secret = $api_secret;
        $this->client =  new Client();
    }


    /**
     * Validate an originator string
     *
     * If the originator ('from' field) is invalid, some networks may reject the network
     * whilst stinging you with the financial cost! While this cannot correct them, it
     * will try its best to correctly format them.
     */
    protected function sanitizePhone($inp)
    {
        // Remove any invalid characters
        $ret = preg_replace('/[^a-zA-Z0-9]/', '', (string)$inp);

        if (preg_match('/[a-zA-Z]/', $inp)) {

            // Alphanumeric format so make sure it's < 11 chars
            $ret = substr($ret, 0, 11);

        } else {

            // Numerical, remove any prepending '00'
            if (substr($ret, 0, 2) == '00') {
                $ret = substr($ret, 2);
                $ret = substr($ret, 0, 15);
            }
        }

        return (string)$ret;
    }

    /**
     * @param $command
     * @param $data
     *
     * @throws \Kumar\Nexmo\Exception\NexmoServiceException
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function sendData($command, $data){

        try{
            $url = self::BASE_URL . $this->COMMANDS[$command]['url'];
            $headers = array_key_exists('headers', $this->COMMANDS[$command])? $this->COMMANDS[$command]['headers'] : [];
            $request = $this->getRequest($this->COMMANDS[$command]['method'], $headers, $url, $data);

            $response = $this->client->send($request);
        }
        catch(\Exception $ex){
            throw new NexmoServiceException($ex->getMessage());
        }

        return $response;

    }

    /**
     * @param      $method
     * @param      $url
     * @param      $data
     *
     * @param null $headers
     *
     * @return \GuzzleHttp\Message\RequestInterface
     */
    private function getRequest($method, $headers, $url, $data){

        /**
         * set the api key and secret for the request
         */
        $data['api_key'] = $this->nx_key;
        $data['api_secret'] = $this->nx_secret;

        return $this->client->createRequest($method,$url,['query'=>$data,'headers'=> $headers]);
    }

}
