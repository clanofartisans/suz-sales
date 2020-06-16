<?php

namespace App\POS\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool applyEmployeeDiscount(\App\EmployeeDiscount $discount)
 * @method static bool applyItemSale(\App\ItemSale $item)
 * @method static bool applyLineDrive(\App\LineDrive $lineDrive)
 * @method static array getBrands()
 * @method static \App\ItemSale getItem(string $upc)
 * @method static bool initializeInfraSale(\App\InfraSheet $infrasheet)
 *
 * @see \App\POS\POSManager
 */
class POS extends Facade
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
