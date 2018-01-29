<?php namespace App\POS\Contracts;

use Carbon\Carbon;
use App\InfraSheet;

/**
 * Interface POSDriverInterface
 */
interface POSDriverInterface
{
    /*
     * Look up an item in OrderDog and return it as a SimpleXML object.
     *
     * @param string $upc
     *
     * @return SimpleXMLElement|bool
     */
    public function getItem(string $upc);

    /*
     * Update an item in OrderDog with the provided discount info.
     * Returns true if we response was okay else returns false.
     *
     * @param string $discounted
     *
     * @return bool
     */
    public function updateItem($discounted);


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
    public function applyDiscountToItem($item, string $realPrice, string $month, string $year);

    /*
     * ?
     */
    public function applyDiscountToManualSale($item, string $amount, string $price, $start, $end, $id, $no_begin, $no_end);

    /*
     * Sets the "display" prices based on the
     * calculated prices and INFRA's info.
     *
     * @param mixed  $item
     * @param string $infraPrice
     *
     * @return array|bool
     */
    public function getDisplayPricesFromItem($item, string $infraPrice);

    /*
     * ?
     */
    public function quickQuery(string $upc);

    /*
     * ?
     */
    public function getBrands();

    public static function escapeBrand($brand);

    public function applyLineDrive($brand, $discount, $begin, $end, $id, $no_begin, $no_end);

    /*
     * ?
     */
    public function startInfraSheet(InfraSheet $infrasheet);
}
