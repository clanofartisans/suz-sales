<?php namespace App\POS\Contracts;

use Carbon\Carbon;

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
    public function updateItem(string $discounted);


    /*
     * Calculate the sale prices and dates for this
     * item and add the necessary XML to the XML
     * we originally received from OrderDog.
     *
     * @param SimpleXMLElement $item
     * @param string           $realPrice
     * @param string           $month
     * @param string           $year
     *
     * @return string|bool
     */
    public function applyDiscountToItem(\SimpleXMLElement $item, string $realPrice, string $month, string $year);

    /*
     * ?
     */
    public function applyDiscountToManualSale(\SimpleXMLElement $item, string $amount, string $price, Carbon $start, Carbon $end);

    /*
     * Sets the "display" prices based on the
     * calculated prices and INFRA's info.
     *
     * @param SimpleXMLElement $item
     * @param string           $infraPrice
     *
     * @return array|bool
     */
    public function getDisplayPricesFromItem(\SimpleXMLElement $item, string $infraPrice);

    /*
     * ?
     */
    public function quickQuery(string $upc);
}
