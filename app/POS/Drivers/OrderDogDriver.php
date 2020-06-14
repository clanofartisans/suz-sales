<?php

namespace App\POS\Drivers;

use App\InfraSheet;
use App\POS\Contracts\POSDriverInterface as POSDriverContract;
use App\POS\POS;
use Carbon\Carbon;

/**
 * Class OrderDogDriver.
 */
class OrderDogDriver extends POS implements POSDriverContract
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
    protected $fields = ['Content-Type: text/xml; charset=utf-8',
                              'SOAPAction: http://services.orderdog.com/Request'];
    /*
     * The curl instance we'll be using for our API calls.
     *
     * @var resource|false
     */
    protected $curl;
    /*
     * The response we received back from our API call.
     *
     * @var \SimpleXMLElement|false
     */
    protected $response;

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
    protected function startCurl()
    {
        if ($this->curl = curl_init()) {
            curl_setopt($this->curl, CURLOPT_URL, $this->baseURL);
            curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($this->curl, CURLOPT_POST, true);
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->fields);
        }
    }

    /*
     * Send our curl query to OrderDog and return their response.
     */
    protected function executeCurl()
    {
        $response = curl_exec($this->curl);

        return $response;
    }

    /*
     * Look up an item in OrderDog and return it as a SimpleXML object.
     *
     * @param string $upc
     *
     * @return \SimpleXMLElement|bool
     */
    public function getItem(string $upc)
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

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $xml);

        $response = $this->executeCurl();

        $this->response = simplexml_load_string($response);

        if ($this->response && !isset($this->response->children('soap', true)->Body->Fault)) {
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

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $xml);

        //return true;

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
     * @param mixed  $item
     * @param string $realPrice
     * @param string $month
     * @param string $year
     *
     * @return string|bool
     */
    public function applyDiscountToItem($item, string $realPrice, string $month, string $year, $percent = null, $localID = null)
    {
        $args  = $this->calcItemDiscountsFromInfra($item, $realPrice);
        $dates = $this->calcItemDiscountDates($month, $year);

        if ($args === false) {
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
     * ?
     */
    public function applyDiscountToManualSale($item, string $amount, string $price, $start, $end, $id = null, $no_begin = null, $no_end = null, $percent = null)
    {
        $xml = "<ItemDiscounts><ItemDiscount><Level>0</Level><Type>Standard</Type><OverrideFlag>false</OverrideFlag><BuyQty>1.0000</BuyQty><MoreFlag>false</MoreFlag><PercentFlag>false</PercentFlag><Amount>$amount</Amount><FreeQty>0.0000</FreeQty><Price>$price</Price><StartDt>$start</StartDt><EndDt>$end</EndDt><DiscountRound>-1</DiscountRound></ItemDiscount></ItemDiscounts>";

        $final = $this->insertDiscountAtCorrectPosition($item, $xml);

        return $final;
    }

    /*
     * Insert the discount info into the item's XML.
     *
     * @param \SimpleXMLElement $item
     * @param string $discountXML
     *
     * @return string|false
     */
    protected function insertDiscountAtCorrectPosition($item, $discountXML)
    {
        if (!isset($item->ItemDiscounts)) {
            $discounted = $this->insertDiscountWithoutExistingDiscounts($item, $discountXML);
        } else {
            $discounted = $this->insertDiscountWithExistingDiscounts($item, $discountXML);
        }

        return $discounted;
    }

    protected function insertDiscountWithoutExistingDiscounts($item, $discountXML)
    {
        $itemXML = $item->asXML();

        if (isset($item->PackPrices)) {
            $insertAfter = '</PackPrices>';
        } else {
            $insertAfter = '</RevenueAcct>';
        }

        $discountXML = $insertAfter.$discountXML;

        $discounted = str_replace($insertAfter, $discountXML, $itemXML);

        return $discounted;
    }

    protected function insertDiscountWithExistingDiscounts($item, $discountXML)
    {
        $itemXML = $item->asXML();

        $discStatus = $this->checkExistingDiscounts($item, $discountXML);

        switch ($discStatus) {
            case 'ignore':
                $discountXML = substr($discountXML, 15);
                $discounted  = str_replace('</ItemDiscounts>', $discountXML, $itemXML);
                break;
            default:
                return false;
                break;
        }

        return $discounted;
    }

    protected function checkExistingDiscounts($item, $discountXML)
    {
        $discStatus = 'none';
        foreach ($item->ItemDiscounts->ItemDiscount as $discount) {
            if ($discount->Type == 'Employee') {
                $discStatus = 'ignore';
                continue;
            }
            if ($discount->Type == 'Standard') {
                if ($this->checkExpiredDiscount($discount) || $this->checkDuplicateDiscount($discount, $discountXML)) {
                    $discStatus = 'ignore';
                    continue;
                }
                $discStatus = 'fail';
                break;
            }
        }

        return $discStatus;
    }

    protected function checkExpiredDiscount($discount)
    {
        $curEndDate       = $discount->EndDt;
        $curEndDateCarbon = new Carbon($curEndDate);

        $carbonNow = Carbon::now();

        if ($curEndDateCarbon < $carbonNow) {
            return true;
        }

        return false;
    }

    protected function checkDuplicateDiscount($discount, $discountXML)
    {
        $current = [];

        $current['Amount']  = (float) $discount->Amount;
        $current['Price']   = (float) $discount->Price;
        $current['StartDt'] = new Carbon($discount->StartDt);
        $current['EndDt']   = new Carbon($discount->EndDt);

        $newDiscount = simplexml_load_string($discountXML);

        $new = [];

        $new['Amount']  = (float) $newDiscount->ItemDiscount->Amount;
        $new['Price']   = (float) $newDiscount->ItemDiscount->Price;
        $new['StartDt'] = new Carbon($newDiscount->ItemDiscount->StartDt);
        $new['EndDt']   = new Carbon($newDiscount->ItemDiscount->EndDt);

        if ($current['Amount'] == $new['Amount'] &&
           $current['Price'] == $new['Price'] &&
           $current['StartDt'] == $new['StartDt'] &&
           $current['EndDt'] == $new['EndDt']) {
            return true;
        }

        return false;
    }

    /*

    OrderDog Get

    Amount  = "1.8000"
    Price   = "2.9900"
    StartDt = "9/1/2017"
    EndDt   = "9/30/2017"

    -----

    Sales Manager Post

    Amount  = "0.1"
    Price   = "4.69"
    StartDt = "2017-12-01 00:00:00"
    EndDt   = "2017-12-31 00:00:00"

     */

    /*
     * Check to make sure we got a good response back from OrderDog.
     *
     * @param SimpleXML $check
     *
     * @return bool
     */
    protected function checkUpdateResponse($check)
    {
        if (isset($check->ResultCode) && isset($check->ResultMsg)) {
            if ($check->ResultCode == '0' && $check->ResultMsg = 'Item Updated') {
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
     * @return array|false
     */
    protected static function calcItemDiscountsFromInfra($item, $realPrice)
    {
        $args = [];

        if ($realPrice == '20%') {
            $price = (float) $item->Price;

            $realPrice = round(($price * 0.8), 2);

            $amount = $price - $realPrice;

            $args['disp_msrp']       = (string) (number_format($price, 2));
            $args['disp_sale_price'] = (string) (number_format($realPrice, 2));
            $args['disp_savings']    = (string) (number_format($amount, 2));

            $args['percent'] = 'true';
            $args['amount']  = '20.0000';
            $args['price']   = (string) (number_format($realPrice, 4));
        } else {
            $realPrice = (float) $realPrice;
            $amount    = ((float) $item->Price) - $realPrice;

            if ($amount <= 0.00) {
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
    protected function calcItemDiscountDates($month, $year)
    {
        $start = new Carbon("first day of $month $year");
        $end   = new Carbon("last day of $month $year");

        $dates = [];

        $dates['start'] = $start->format('n/j/Y');
        $dates['end']   = $end->format('n/j/Y');

        return $dates;
    }

    /*
     * Sets the "display" prices based on the
     * calculated prices and INFRA's info.
     *
     * @param mixed  $item
     * @param string $infraPrice
     *
     * @return array|bool
     */
    public function getDisplayPricesFromItem($item, string $infraPrice)
    {
        $prices = self::calcItemDiscountsFromInfra($item, $infraPrice);

        if ($prices === false) {
            return false;
        }

        $display = [];

        $display['sale_price'] = $prices['disp_sale_price'];
        $display['msrp']       = $prices['disp_msrp'];
        $display['savings']    = $prices['disp_savings'];

        return $display;
    }

    /*
     * ?
     */
    public function quickQuery(string $upc)
    {
        $return = [];

        if ($item = $this->getItem($upc)) {
            $price           = (float) $item->Price;
            $return['brand'] = (string) $item->Brand;
            $return['desc']  = ((string) $item->Description).' '.((string) $item->Size).' '.((string) $item->Form);
            $return['price'] = (string) (number_format($price, 2));

            if ($return['brand'] == 'PRIVATE LABEL' ||
               $return['brand'] == 'VITALITY WORKS' ||
               $return['brand'] == 'RELIANCE PRIVATE LABEL') {
                $return['brand'] = "Suzanne's";
            }

            if ($return['brand'] == 'CRUNCHMASTER') {
                $return['brand'] = 'Crunch Master';
            }

            return $return;
        }

        return false;
    }

    /*
     * ?
     */
    public function getBrands()
    {
        return false;
    }

    public static function escapeBrand($brand)
    {
        return false;
    }

    /*
     * ?
     */
    public function applyLineDrive($brand, $discount, $begin, $end, $id, $no_begin, $no_end)
    {
        return false;
    }

    /*
     * ?
     */
    public function startInfraSheet(InfraSheet $infrasheet)
    {
        return true;
    }

    public function checkForBetterSales($sku, $percent)
    {
        return false;
    }
}
