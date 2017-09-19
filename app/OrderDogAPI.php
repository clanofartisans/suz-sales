<?php

namespace App;

use Carbon\Carbon;

class OrderDogAPI
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

    /*
     * The response we received back from our API call.
     *
     * @var SimpleXML
     */
    public $response;

    /*
     * Start building the curl session we need for our API calls.
     */
    public function __construct()
    {
        $this->startCurl();
    }

    /*
     * Set up the basic options we need for our API calls.
     */
    protected function startCurl() {

        $this->curl = curl_init();

        curl_setopt($this->curl,CURLOPT_URL, $this->baseURL);
        curl_setopt($this->curl,CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl,CURLOPT_POST, true);
        curl_setopt($this->curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl,CURLOPT_HTTPHEADER, $this->fields);
    }

    /*
     * Send our curl query to OrderDog and return their response.
     */
    protected function executeCurl() {
        $response = curl_exec($this->curl);

        curl_close($this->curl);

        return $response;
    }

    /*
     * Look up an item in OrderDog and return it as a SimpleXML object.
     *
     * @param string $upc
     *
     * @return SimpleXML|bool
     */
    public function getItem($upc) {

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <Request xmlns="http://services.orderdog.com/">
      <Identification>
        <PartnerID>10983</PartnerID>
        <Person>
          <User>suzannesnf</User>
          <Password>suzannesnf</Password>
        </Person>
      </Identification>
      <ProtocolID>ItemLookup01</ProtocolID>
      <XMLData>
        <ItemLookup01>
          <UPC>$upc</UPC>
        </ItemLookup01>
      </XMLData>
    </Request>
  </soap:Body>
</soap:Envelope>
XML;

        curl_setopt($this->curl,CURLOPT_POSTFIELDS, $xml);

        $response = $this->executeCurl();

        $this->response = simplexml_load_string($response);

        if(!isset($this->response->children('soap', true)->Body->Fault)) {

            $item = $this->response->children('soap', true)->Body->children()->RequestResponse->RequestResult->ItemLookup01Results->Item;

            return $item;
        }

        return false;
    }

    /*
     * Update an item in OrderDog with the provided discount info.
     * Returns true if we response was okay else returns false.
     *
     * @param string $discounted
     *
     * @return bool
     */
    public function updateItem($discounted)
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <Request xmlns="http://services.orderdog.com/">
      <Identification>
        <PartnerID>10983</PartnerID>
        <Person>
          <User>suzannesnf</User>
          <Password>suzannesnf</Password>
        </Person>
      </Identification>
      <ProtocolID>ItemUpdate01</ProtocolID>
      <XMLData>
        <ItemUpdate01>
          $discounted
        </ItemUpdate01>
      </XMLData>
    </Request>
  </soap:Body>
</soap:Envelope>
XML;

        curl_setopt($this->curl,CURLOPT_POSTFIELDS, $xml);

        $response = $this->executeCurl();

        $simple = simplexml_load_string($response);

        $check = $simple->children('soap', true)->Body->children()->RequestResponse->RequestResult->ItemUpdate01Results;

        return $this->checkUpdateResponse($check);
    }

    /*
     * Calculate the sale prices and dates for this
     * item and add the necessary XML to the XML
     * we originally received from OrderDog.
     *
     * @param SimpleXML $item
     * @param string    $realPrice
     * @param string    $month
     * @param string    $year
     *
     * @return string|bool
     */
    public function applyDiscountToItem($item, $realPrice, $month, $year)
    {
        $args = $this->calcItemDiscountsFromInfra($item, $realPrice);
        $dates = $this->calcItemDiscountDates($month, $year);

        if($args === false) {
            return 'Item price is lower than sale price';
        }

        $percent = $args['percent'];
        $amount  = $args['amount'];
        $price   = $args['price'];
        $start   = $dates['start'];
        $end     = $dates['end'];

        $xml = "<ItemDiscounts><ItemDiscount><Level>0</Level><Type>Standard</Type><OverrideFlag>false</OverrideFlag><BuyQty>1.0000</BuyQty><MoreFlag>false</MoreFlag><PercentFlag>$percent</PercentFlag><Amount>$amount</Amount><FreeQty>0.0000</FreeQty><Price>$price</Price><StartDt>$start</StartDt><EndDt>$end</EndDt><DiscountRound>-1</DiscountRound></ItemDiscount></ItemDiscounts>";

        $final = $this->insertDiscountAtCorrectPosition($item, $xml);

        return $final;
    }

    /*
     * Insert the discount info into the item's XML.
     *
     * @param SimpleXML $item
     * @param string    $discountXML
     *
     * @return string|bool
     */
    public function insertDiscountAtCorrectPosition($item, $discountXML)
    {
        $itemXML = $item->asXML();

        if(!isset($item->ItemDiscounts)) {
            if(isset($item->PackPrices)) {
                $insertAfter = "</PackPrices>";
            } else {
                $insertAfter = "</RevenueAcct>";
            }
        } else {
            return false;
        }

        $discountXML = $insertAfter . $discountXML;

        $discounted = str_replace($insertAfter, $discountXML, $itemXML);

        return($discounted);
    }

    /*
     * Check to make sure we got a good response back from OrderDog.
     *
     * @param SimpleXML $check
     *
     * @return bool
     */
    public function checkUpdateResponse($check)
    {
        if(isset($check->ResultCode) && isset($check->ResultMsg)) {
            if($check->ResultCode == '0' && $check->ResultMsg = 'Item Updated') {
                return true;
            }
        }
        return false;
    }

    /*
     * Calculates and returns all the pricing info for an item.
     *
     * @param SimpleXML $item
     * @param string    $realPrice
     *
     * @return array|bool
     */
    public static function calcItemDiscountsFromInfra($item, $realPrice)
    {
        if($realPrice == '20%') {
            $price = (float) $item->Price;

            $realPrice = round(($price * 0.8), 2);

            $amount    = $price - $realPrice;

            $args['disp_msrp']       = (string) (number_format($price, 2));
            $args['disp_sale_price'] = (string) (number_format($realPrice, 2));
            $args['disp_savings']    = (string) (number_format($amount, 2));

            $args['percent'] = 'true';
            $args['amount']  = '20.0000';
            $args['price']   = (string) (number_format($realPrice, 4));
        } else {
            $realPrice = (float) $realPrice;
            $amount    = ((float) $item->Price) - $realPrice;

            if($amount <= 0.00) {
                return false;
            }

            $args['disp_msrp']       = (string) (number_format(((float) $item->Price), 2));
            $args['disp_sale_price'] = (string) (number_format($realPrice, 2));
            $args['disp_savings']    = (string) (number_format($amount, 2));

            $args['percent'] = 'false';
            $args['amount']  = (string) (number_format($amount, 4));
            $args['price']   = (string) (number_format($realPrice, 4));
        }

        return $args;
    }

    /*
     * Calculates and returns the first and last day of the provided month.
     *
     * @param string $month
     * @param string $year
     *
     * @return array
     */
    public function calcItemDiscountDates($month, $year)
    {
        $start = new Carbon("first day of $month $year");
        $end   = new Carbon("last day of $month $year");

        $dates['start'] = $start->format('n/j/Y');
        $dates['end']   = $end->format('n/j/Y');

        return $dates;
    }

    /*
     * Sets the "display" prices based on the
     * calculated prices and INFRA's info.
     *
     * @param SimpleXML $item
     * @param string    $infraPrice
     *
     * @return array|bool
     */
    public static function getDisplayPricesFromItem($item, $infraPrice)
    {
        $prices = self::calcItemDiscountsFromInfra($item, $infraPrice);

        if($prices === false) {
            return false;
        }

        $display['sale_price'] = $prices['disp_sale_price'];
        $display['msrp']       = $prices['disp_msrp'];
        $display['savings']    = $prices['disp_savings'];

        return $display;
    }

    public function quickQuery($upc)
    {
        $return = [];

        if($item = $this->getItem($upc)) {

            $price = (float) $item->Price;
            $return['brand']  = (string) $item->Brand;
            $return['desc']   = ((string) $item->Description) . ' ' . ((string) $item->Size) . ' ' . ((string) $item->Form);
            $return['price']  = (string) (number_format($price, 2));

            return $return;
        }

        return false;
    }
}
