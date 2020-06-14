<?php

namespace App\POS;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|bool applyDiscountToItem($item, string $realPrice, string $month, string $year, $percent = null, $localID = null)
 * @method static array applyDiscountToManualSale($item, string $amount, string $price, $start, $end, $id, $no_begin, $no_end, $percent = null)
 * @method static bool applyEmployeeDiscount(string $brand, $discount, \Carbon\Carbon $begin, \Carbon\Carbon $end, int $id, bool $no_begin, bool $no_end)
 * @method static bool applyLineDrive($brand, $discount, $begin, $end, $id, $no_begin, $no_end)
 * @method static bool checkForBetterSales($sku, $percent)
 * @method static string escapeBrand(string $brand)
 * @method static array|bool getDisplayPricesFromItem($item, string $infraPrice)
 * @method static array|bool getBrands()
 * @method static \SimpleXMLElement|bool getItem(string $upc)
 * @method static bool performRenumbering()
 * @method static array|bool quickQuery(string $upc)
 * @method static bool renumberSales()
 * @method static bool startInfraSheet(\App\InfraSheet $infrasheet)
 * @method static bool updateItem(string $discounted)
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
