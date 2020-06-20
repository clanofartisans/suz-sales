<?php

namespace App;

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function infrasheet()
    {
        return $this->belongsToMany('App\InfraSheet', 'infrasheet_itemsale', 'itemsale_id', 'infrasheet_id');
    }

    /**
     * Set or calculate the discount percent.
     *
     * @param  string  $value
     * @return void
     */
    public function setDiscountPercentAttribute($value)
    {
        if (!is_numeric($value)) {
            $value = $this->calculateDiscountPercent();
        }
        $this->attributes['discount_percent'] = $value;
    }

    /**
     * Set or calculate the discount percent.
     *
     * @return float
     */
    protected function calculateDiscountPercent()
    {
        return round(((1 - ($this->real_sale_price / $this->regular_price)) * 100), 4);
    }
}
