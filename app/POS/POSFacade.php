<?php

namespace App\POS;

use Illuminate\Support\Facades\Facade;

/**
 * @method string|bool applyDiscountToItem($item, string $realPrice, string $month, string $year, $percent = null, $localID = null)
 * @method applyDiscountToManualSale($item, string $amount, string $price, $start, $end, $id, $no_begin, $no_end, $percent = null)
 * @method bool applyEmployeeDiscount(string $brand, $discount, \Carbon\Carbon $begin, \Carbon\Carbon $end, int $id, bool $no_begin, bool $no_end)
 * @method applyLineDrive($brand, $discount, $begin, $end, $id, $no_begin, $no_end)
 * @method checkForBetterSales($sku, $percent)
 * @method static string escapeBrand(string $brand)
 * @method array|bool getDisplayPricesFromItem($item, string $infraPrice)
 * @method getBrands()
 * @method \SimpleXMLElement|bool getItem(string $upc)
 * @method bool performRenumbering()
 * @method quickQuery(string $upc)
 * @method bool renumberSales()
 * @method startInfraSheet(\App\InfraSheet $infrasheet)
 * @method bool updateItem(string $discounted)
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
