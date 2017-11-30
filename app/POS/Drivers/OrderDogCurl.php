<?php namespace App\POS\Drivers;

class OrderDogCurl
{
    /**
     * The base URL for our API calls.
     *
     * @var string
     */
    protected $baseURL = 'http://services.orderdog.com/webservice.asmx';

    /**
     * The required request headers for our API calls.
     *
     * @var array
     */
    protected $fields = array('Content-Type: text/xml; charset=utf-8',
                              'SOAPAction: http://services.orderdog.com/Request');

    /*
     * The curl instance we'll be using for our API calls.
     *
     * @var resource
     */
    protected $curl;

    public function __construct()
    {
        $this->curl = curl_init();

        curl_setopt($this->curl,CURLOPT_URL, $this->baseURL);
        curl_setopt($this->curl,CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl,CURLOPT_POST, true);
        curl_setopt($this->curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl,CURLOPT_HTTPHEADER, $this->fields);
    }
}
