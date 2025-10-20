<?php namespace App\Libraries;


/**
 * Class SabeeApp
 *
 * @package App\libraries
 *
 * @version 1.0
 */
class SabeeClient
{
    
    private $apiKey = '';
    
    private $opts = [
        'api-url'     => 'https://api.sabeeapp.com/connect/',
        'api-version' => 1,
    ];
    
    /**
     * @var $success bool
     */
    public $success;
    /**
     * @var $message string
     */
    public $message;
    /**
     * @var $errors array
     */
    public $errors;
    /**
     * @var $errors string First error encountered during the operation
     */
    public $firstError;
    /**
     * @var $request array
     */
    public $request;
    /**
     * Raw response from SabeeApp API
     *
     * @var $response object
     */
    public $rawResponse;
    /**
     * Relevant part of the response. E.g. product list on '/products' request.
     *
     * @var $data array|string
     */
    public $data;
    
    /**
     * SabeeApp constructor.
     *
     * @param       $apiKey
     * @param array $opts
     *
     * @return SabeeClient
     */
    function __construct($apiKey, $opts = [])
    {
        return $this->init($apiKey, $opts);
    }
    
    /* ----------------------------------------------------------------------------------------------------------------------------------- init -+- */
    /**
     * Init the object with SabeeApp api key and options
     *
     * @param $apiKey       string
     * @param $opts         array Options array
     *
     * @return $this
     */
    public function init($apiKey, $opts)
    {
        $this->apiKey = $apiKey;
        $this->opts = array_merge($this->opts, $opts);
        
        return $this;
    }
    
    /* -------------------------------------------------------------------------------------------------------------------------------- request -+- */
    /**
     * @param string $interface
     * @param array  $parameters
     * @param string $method   GET|POST
     * @param bool   $insecure allow requests without setting proper SSL certificates on requesting machine. Should only be used for testing purposes.
     *
     * @return $this
     * @throws \Exception
     */
    public function request($interface = "", $parameters = [], $method = 'GET', $insecure = false)
    {
        // build query from parameters
        
        if ($method == 'GET' && count($parameters)) {
            $query = http_build_query($parameters);
            $interface .= "?$query";
        }
        
        $ch = curl_init($this->opts['api-url'] . "/$interface");
        
        // prepare auth headers
        $headers = [
            "Content-Type: application/json",
            "api_key: $this->apiKey",
            "api_version: {$this->opts['api-version']}",
        ];
        // set the headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        }
        
        if ($insecure) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            // a cURL error was occurred
            $this->success = false;
            $this->message = 'cURL error code ' . curl_errno($ch) . ': ' . curl_error($ch);
        } else {
            // parse the response
            $this->rawResponse = json_decode($result);
            
            // populate $this with the data from the response
            $this->success = object_get($this->rawResponse, 'success');
            $this->errors = object_get($this->rawResponse, 'errors');
            $this->message = object_get($this->rawResponse, 'message', '');
            $this->data = object_get($this->rawResponse, 'data');
            $this->request = $ch;
            
            $this->firstError = false;
            
            // populate firstError variable
            if ($this->success == false) {
                $this->firstError = array_get($this->errors, '0', false);
                if ($this->firstError) {
                    $this->firstError = object_get($this->firstError, 'ret_msg', false);
                }
            }
        }
        
        return $this;
    }
    
    /* ------------------------------------------------------------------------------------------------------------------------ hotel Inventory -+- */
    public function hotelInventory($parameters = [])
    {
        $this->request('hotel/inventory', $parameters);
        
        return $this;
    }
    
    /* --------------------------------------------------------------------------------------------------------------------------- booking List -+- */
    /**
     * "Returns booking list for requested day or period"
     *
     * @param array $parameters
     *
     * @return $this
     *
     * @see https://connect.sabeeapp.com/#cb7983ab-63b1-4b21-9782-b9aef7eb856f
     */
    public function bookingList($parameters = [])
    {
        $parameters = array_merge([
            'hotel_id' => array_get($this->opts, 'hotel_id')
        ], $parameters);
        
        $this->request('booking/list', $parameters);
        
        return $this;
    }
    
    /* ------------------------------------------------------------------------------------------------------------------------- service Submit -+- */
    /**
     * "Post request to send services to SabeeApp"
     *
     * @param array $parameters
     *
     * @return $this
     *
     * @see https://connect.sabeeapp.com/#b39db313-ba67-43be-addd-f63138341d23
     */
    public function serviceSubmit($parameters = [])
    {
        $parameters = array_merge([
            'hotel_id' => array_get($this->opts, 'hotel_id')
        ], $parameters);
        
        $this->request('service/submit', $parameters, 'POST');
        
        return $this;
    }
}