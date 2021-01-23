<?php

namespace App;

use App\POS\Facades\POS;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int     id
 * @property bool    applied
 * @property bool    approved
 * @property string  brand
 * @property bool    color
 * @property string  created_at
 * @property string  deleted_at
 * @property string  desc
 * @property float   discount_percent
 * @property string  display_sale_price
 * @property string  expires_at
 * @property string  flags
 * @property bool    pos_update
 * @property bool    printed
 * @property bool    queued
 * @property float   real_sale_price
 * @property float   regular_price
 * @property string  sale_begin
 * @property string  sale_category
 * @property string  sale_end
 * @property float   savings_amount
 * @property string  size
 * @property string  upc
 * @property string  updated_at
 */
class ItemSale extends Model
{
    use SoftDeletes;

    /**
     * The model's attribute defaults.
     *
     * @var array
     */
    protected $attributes = [
        'applied'       => false,
        'approved'      => false,
        'color'         => false,
        'sale_category' => 'Great Savings',
        'pos_update'    => true,
        'printed'       => false,
        'queued'        => false,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'applied'          => 'boolean',
        'approved'         => 'boolean',
        'color'            => 'boolean',
        'discount_percent' => 'decimal:4',
        'pos_update'       => 'boolean',
        'printed'          => 'boolean',
        'queued'           => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_at',
        'sale_begin',
        'sale_end',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The INFRA sheet this item belongs to. Items only belong to
     * one INFRA sheet, not "belong to many". This is just the
     * simplest way to set up this relationship in Laravel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function infrasheet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InfraSheet::class);
    }

    /**
     * Set or calculate the discount percent.
     *
     * @param  string  $value
     */
    public function setDiscountPercentAttribute(string $value)
    {
        if (!is_numeric($value)) {
            $value = $this->calculateDiscountPercent();
        }
        $this->attributes['discount_percent'] = $value;
    }

    /**
     * Calculates all pricing info for the sale.
     *
     * @param null $salePrice
     * @return $this
     */
    public function calculatePricingData($salePrice = null): ItemSale
    {
        if (!($posItem = $this->getPOSItem($this->upc))) {
            $this->display_sale_price = $this->calculateDisplaySalePrice($salePrice, $salePrice);
            $this->flags = 'Item not found in point of sale system';

            return $this;
        }

        $this->regular_price      = $posItem->regular_price;
        $this->real_sale_price    = $this->calculateRealSalePrice($salePrice, $posItem->regular_price);
        $this->display_sale_price = $this->calculateDisplaySalePrice($salePrice, $this->real_sale_price);
        $this->discount_percent   = $this->calculatePercentageDiscount($this->regular_price, $this->real_sale_price);
        $this->savings_amount     = $this->regular_price - $this->real_sale_price;

        $this->checkForSalePriceProblems();

        return $this;
    }

    /**
     * Gets item info from the point of sale system.
     *
     * @param string $upc
     * @return ItemSale|null
     */
    protected function getPOSItem(string $upc): ?ItemSale
    {
        if (!empty($posItem = POS::getItem($upc))) {
            return $posItem;
        }

        return null;
    }

    /**
     * Calculate the actual sale price of an item, based on
     * things like "4/$5" and also strip any formatting.
     *
     * @param $price
     * @param $regularPrice
     * @return string
     */
    protected function calculateRealSalePrice($price, $regularPrice): string
    {

        if (!empty($price)) {
            if (strpos($price, '/') !== false) {
                try {
                    $price     = rtrim($price);
                    $pieces    = explode('/', $price);
                    $pieces[1] = ltrim($pieces[1], '$');

                    $priceCalc = $pieces[1] / (float) $pieces[0];

                    return $this->roundSalePrice($priceCalc);
                }
                catch (\Exception $e) {
                    \Log::warning($e->getMessage(), $e->getTrace());
                }
            } else {
                $price = ltrim($price, '$');
                if (is_numeric($price)) {
                    $price = (float) $price;

                    return $this->roundSalePrice($price);
                }
            }
        }

        return $this->roundSalePrice($regularPrice * 0.8);
    }

    /**
     * Rounds a price to two decimal places.
     *
     * @param $price
     * @return string
     */
    protected function roundSalePrice($price): string
    {
        if (!empty($price) && !is_string($price)) {
            return number_format($price, 2);
        }

        return $price;
    }

    /**
     * Determines if the given display price should be used,
     * or if we should use the actual sale price instead.
     *
     * @param $displaySalePrice
     * @param $realSalePrice
     * @return string|null
     */
    protected function calculateDisplaySalePrice($displaySalePrice, $realSalePrice): ?string
    {
        if(is_numeric($displaySalePrice)) {
            return '$'.$displaySalePrice;
        }

        if(!empty($displaySalePrice)) {
            return $displaySalePrice;
        }

        return '$'.$realSalePrice;
    }

    /**
     * Calculate the discount in percent.
     *
     * @param $regularPrice
     * @param $realSalePrice
     * @return float
     */
    protected function calculatePercentageDiscount($regularPrice, $realSalePrice): float
    {
        $percentage = round(((1.0000 - ($realSalePrice / $regularPrice)) * 100.0000), 4);

        return $percentage;
    }

    /**
     * Set or calculate the discount percent.
     *
     * @return float
     */
    protected function calculateDiscountPercent(): float
    {
        return round(((1 - ($this->real_sale_price / $this->regular_price)) * 100), 4);
    }

    /**
     * Sets the flags property if there are issues with the sale price.
     */
    protected function checkForSalePriceProblems()
    {
        if($this->isSalePriceLowerThanRegularPrice()) {
            $this->flags = 'Item price is lower than sale price';
        }
    }

    /**
     * Checks if the calculated sale price is lower
     * than the item's regular price in the POS.
     *
     * @return bool
     */
    protected function isSalePriceLowerThanRegularPrice(): bool
    {
        if($this->discount_percent > 0) {
            return false;
        }

        return true;
    }
}
