<?php

namespace App\POS\Drivers;

use App\POS\Contracts\POSContract;

class OrderDogDriver extends AbstractPOSDriver implements POSContract
{
    /**
     * Apply an employee discount to the POS system.
     *
     * @param \App\EmployeeDiscount $discount
     * @return bool
     */
    public function applyEmployeeDiscount(\App\EmployeeDiscount $discount): bool
    {
        return false;
    }

    /**
     * Apply a sale to an item in the POS system.
     *
     * @param \App\ItemSale $item
     * @return bool
     */
    public function applyItemSale(\App\ItemSale $item) : bool
    {
        return false;
    }

    /**
     * Apply a line drive sale to the POS system.
     *
     * @param \App\LineDrive $lineDrive
     * @return bool
     */
    public function applyLineDrive(\App\LineDrive $lineDrive) : bool
    {
        return false;
    }

    /**
     * Get a list of all brands in the POS system.
     *
     * @return iterable
     */
    public function getBrands() : iterable
    {
        return [];
    }

    /**
     * Get an item from the POS system.
     *
     * @param string $upc
     * @return \App\ItemSale
     */
    public function getItem(string $upc) : \App\ItemSale
    {
        return false;
    }

    /**
     * Initialize an empty INFRA sale in the POS system.
     *
     * @param \App\InfraSheet $infrasheet
     * @return bool
     */
    public function initializeInfraSale(\App\InfraSheet $infrasheet) : bool
    {
        return false;
    }
}
