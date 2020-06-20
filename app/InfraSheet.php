<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int     id
 * @property int     month
 * @property int     year
 * @property string  filename
 * @property string  created_at
 * @property string  updated_at
 */
class InfraSheet extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The items that belong to this INFRA sheet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function items()
    {
        return $this->belongsToMany('App\ItemSale');
    }
}
