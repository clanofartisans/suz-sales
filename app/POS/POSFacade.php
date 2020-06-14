<?php

namespace App\POS;

use Illuminate\Support\Facades\Facade;

/**
 * @method \SimpleXMLElement|bool getItem(string $upc)
 * @method bool updateItem(string $discounted)
 * @method string|bool applyDiscountToItem($item, string $realPrice, string $month, string $year, $percent = null, $localID = null)
 * @method applyDiscountToManualSale($item, string $amount, string $price, $start, $end, $id, $no_begin, $no_end, $percent = null)
 * @method array|bool getDisplayPricesFromItem($item, string $infraPrice)
 * @method quickQuery(string $upc)
 * @method getBrands()
 * @method static string escapeBrand(string $brand)
 * @method applyLineDrive($brand, $discount, $begin, $end, $id, $no_begin, $no_end)
 * @method startInfraSheet(\App\InfraSheet $infrasheet)
 * @method checkForBetterSales($sku, $percent)
 */
class POSFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'pos';
    }
}
