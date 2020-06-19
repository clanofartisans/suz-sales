<?php

namespace App\POS\Drivers;

use App\ItemSale;
use App\POS\Contracts\POSContract;

class CounterpointDriver extends AbstractPOSDriver implements POSContract
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
    public function applyItemSale(\App\ItemSale $item): bool
    {
        return false;
    }

    /**
     * Apply a line drive sale to the POS system.
     *
     * @param \App\LineDrive $lineDrive
     * @return bool
     */
    public function applyLineDrive(\App\LineDrive $lineDrive): bool
    {
        return false;
    }

    /**
     * Get a list of all brands in the POS system.
     * Keys are url encoded versions of values.
     *
     * @return iterable
     */
    public function getBrands(): iterable
    {
        $brands = [];

        foreach ($this->getRawBrandDataFromCounterpoint() as $brand) {
            $brands[urlencode($brand)] = $brand;
        }

        return $brands;
    }

    /**
     * Get an item from the POS system.
     *
     * @param string $upc
     * @return \App\ItemSale|null
     */
    public function getItem(string $upc): ?\App\ItemSale
    {
        if ($item = $this->getRawItemDataFromCounterpoint($upc)) {
            $item = ItemSale::make([
                'brand'         => $item['brand'],
                'desc'          => $item['desc'],
                'regular_price' => $item['regular_price'],
                'size'          => $item['size'],
                'upc'           => $item['upc']
            ]);
        }

        return $item;
    }

    /**
     * Initialize an empty INFRA sale in the POS system.
     *
     * @param \App\InfraSheet $infrasheet
     * @return bool
     */
    public function initializeInfraSale(\App\InfraSheet $infrasheet): bool
    {
        return false;
    }

    /**
     * Get raw brand data from Counterpoint.
     *
     * @return iterable|null
     */
    protected function getRawBrandDataFromCounterpoint(): ?iterable
    {
        return null;
    }

    /**
     * Get raw item data from Counterpoint.
     *
     * @param string $upc
     * @return iterable|null
     */
    protected function getRawItemDataFromCounterpoint($upc): ?iterable
    {
        return null;
    }
}
