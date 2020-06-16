<?php

namespace App\POS\Contracts;

interface POSContract
{
    /**
     * Apply an employee discount to the POS system.
     *
     * @param \App\EmployeeDiscount $discount
     * @return bool
     */
    public function applyEmployeeDiscount(\App\EmployeeDiscount $discount) : bool;

    /**
     * Apply a sale to an item in the POS system.
     *
     * @param \App\ItemSale $item
     * @return bool
     */
    public function applyItemSale(\App\ItemSale $item) : bool;

    /**
     * Apply a line drive sale to the POS system.
     *
     * @param \App\LineDrive $lineDrive
     * @return bool
     */
    public function applyLineDrive(\App\LineDrive $lineDrive) : bool;

    /**
     * Get a list of all brands in the POS system.
     *
     * @return iterable
     */
    public function getBrands() : iterable;

    /**
     * Get an item from the POS system.
     *
     * @param string $upc
     * @return \App\ItemSale
     */
    public function getItem(string $upc) : \App\ItemSale;

    /**
     * Initialize an empty INFRA sale in the POS system.
     *
     * @param \App\InfraSheet $infrasheet
     * @return bool
     */
    public function initializeInfraSale(\App\InfraSheet $infrasheet) : bool;
}
