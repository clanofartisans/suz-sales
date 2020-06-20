<?php

namespace App\POS\Drivers;

use App\ItemSale;
use App\POS\Contracts\POSContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

        foreach ($this->getCleanBrandDataFromCounterpoint() as $brand) {
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
        if ($item = $this->getCleanItemDataFromCounterpoint($upc)) {
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
     * Convert raw brand data from Counterpoint to a usable array.
     *
     * @return iterable
     */
    protected function getCleanBrandDataFromCounterpoint(): iterable
    {
        $brands = [];

        if ($result = $this->getRawBrandDataFromCounterpoint()) {
            foreach ($result as $raw) {
                $brands[] = $raw->PROF_ALPHA_2;
            }
        }

        return $brands;
    }

    /**
     * Convert raw item data from Counterpoint to a usable array.
     *
     * @param string $upc
     * @return iterable|null
     */
    protected function getCleanItemDataFromCounterpoint($upc): ?iterable
    {
        if ($match = $this->getRawItemDataFromCounterpoint($this->getItemNumberFromUPC($upc))) {
            $item = [];

            $item['brand']         = $match->PROF_ALPHA_2;
            $item['desc']          = $match->DESCR;
            $item['regular_price'] = $match->PRC_1;
            $item['size']          = $match->PROF_ALPHA_2;
            $item['upc']           = $upc;

            return $item;
        }

        return null;
    }

    /**
     * Look up the actual Counterpoint item number associated with a UPC.
     *
     * @param string $upc
     * @return string|null
     */
    protected function getItemNumberFromUPC($upc): ?string
    {
        $match = DB::connection('counterpoint')
                   ->table('VI_IM_SKU_BARCOD')
                   ->select('ITEM_NO')
                   ->where('BARCOD', $upc)
                   ->first();

        return $match ? $match->ITEM_NO : null;
    }

    /**
     * Get raw brand data from Counterpoint.
     *
     * @return Collection
     */
    protected function getRawBrandDataFromCounterpoint(): Collection
    {
        return DB::connection('counterpoint')
                 ->table('IM_ITEM')
                 ->select('PROF_ALPHA_2')
                 ->distinct()
                 ->orderBy('PROF_ALPHA_2', 'asc')
                 ->get();
    }

    /**
     * Get an item's raw data from Counterpoint.
     *
     * @param string $item_no
     * @return \stdClass|null
     */
    protected function getRawItemDataFromCounterpoint(string $item_no): ?\stdClass
    {
        return DB::connection('counterpoint')
            ->table('IM_ITEM')
            ->where('ITEM_NO', $item_no)
            ->first();
    }
}
