<?php

namespace App\POS\Drivers;

use App\Exceptions\POSSystemException;
use App\InfraSheet;
use App\ItemSale;
use App\POS\Contracts\POSContract;
use Carbon\Carbon;
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
     * @param ItemSale $item
     * @return bool
     */
    public function applyItemSale(ItemSale $item): bool
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
     * @return ItemSale|null
     */
    public function getItem(string $upc): ?ItemSale
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
     * @param InfraSheet $infrasheet
     * @return bool
     * @throws POSSystemException
     */
    public function initializeInfraSale(InfraSheet $infrasheet): bool
    {
        $begin = Carbon::create($infrasheet->year, $infrasheet->month, 1);
        $end = $begin->copy()->endOfMonth();

        $data = [
            'GRP_TYP' => 'C',
            'NO_BEG_DAT' => 'N',
            'NO_END_DAT' => 'N',
            'CUST_FILT_TEXT' => '*** All ***',
            'GRP_COD' => 'INFRA' . $begin->format('my'),
            'DESCR' => 'INFRA ' . $begin->format('F Y'),
            'DESCR_UPR' => strtoupper('INFRA ' . $begin->format('F Y')),
            'BEG_DAT' => $begin->format('Y-m-d') . ' 00:00:00.000',
            'BEG_DT' => $begin->format('Y-m-d') . ' 00:00:00.000',
            'END_DAT' => $end->format('Y-m-d') . ' 00:00:00.000',
            'END_DT' => $end->format('Y-m-d') . ' 23:59:59.000',
            'LST_MAINT_DT' => Carbon::now()->format('Y-m-d H:i:s.v'),
            'LST_MAINT_USR_ID' => config('pos.counterpoint.user')
        ];

        if (!$this->insertIntoDatabase('IM_PRC_GRP', $data)) {
            throw new POSSystemException('The INFRA sale data has already been initialized in Counterpoint for the month you specified.');
        }

        return true;
    }

    /**
     * Inserts raw data directly into the Counterpoint database.
     *
     * @param string $table
     * @param array $data
     * @return bool
     */
    protected function insertIntoDatabase(string $table, array $data): bool
    {
        try {
            $this->connection()->table($table)->insert($data);
        } catch (\Exception $e) {
            return false;
        }

        return true;
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
        if ($this->getItemNumberFromUPC($upc) && $match = $this->getRawItemDataFromCounterpoint($this->getItemNumberFromUPC($upc))) {
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
     * @codeCoverageIgnore (Covered by integration test)
     * @param string $upc
     * @return string|null
     */
    protected function getItemNumberFromUPC($upc): ?string
    {
        $match = $this->connection()
            ->table('VI_IM_SKU_BARCOD')
            ->select('ITEM_NO')
            ->where('BARCOD', $upc)
            ->first();

        return $match ? $match->ITEM_NO : null;
    }

    /**
     * Get raw brand data from Counterpoint.
     *
     * @codeCoverageIgnore (Covered by integration test)
     * @return Collection
     */
    protected function getRawBrandDataFromCounterpoint(): Collection
    {
        return $this->connection()
            ->table('IM_ITEM')
            ->select('PROF_ALPHA_2')
            ->distinct()
            ->orderBy('PROF_ALPHA_2', 'asc')
            ->get();
    }

    /**
     * Get an item's raw data from Counterpoint.
     *
     * @codeCoverageIgnore (Covered by integration test)
     * @param string $item_no
     * @return \stdClass|null
     */
    protected function getRawItemDataFromCounterpoint(string $item_no): ?\stdClass
    {
        return $this->connection()
            ->table('IM_ITEM')
            ->where('ITEM_NO', $item_no)
            ->first();
    }

    /**
     * Get a connection to the Counterpoint database.
     *
     * @codeCoverageIgnore (Covered by integration test)
     * @return \Illuminate\Database\ConnectionInterface
     */
    protected function connection(): \Illuminate\Database\ConnectionInterface
    {
        return DB::connection(env('CP_DB_CONNECTION'));
    }
}
