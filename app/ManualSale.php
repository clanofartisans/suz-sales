<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ManualSale extends Model
{
    protected $table = 'manual_sales';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['upc',
                           'brand',
                           'desc',
                           'sale_price',
                           'disp_sale_price',
                           'reg_price',
                           'savings',
                           'sale_cat',
                           'color',
                           'processed',
                           'imaged',
                           'printed',
                           'flags',
                           'sale_begin',
                           'sale_end',
                           'expires'];
}
