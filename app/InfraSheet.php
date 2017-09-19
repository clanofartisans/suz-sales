<?php

namespace App;

use \DateTime;
use Illuminate\Database\Eloquent\Model;

class InfraSheet extends Model
{
    /**
     * The model's database table.
     *
     * @var array
     */
    protected $table = 'infrasheets';

    /*
     * Any time the month is accessed, return the full name of the month.
     *
     * @return string
     */
    public function getMonthAttribute($value)
    {
        $dateObj   = DateTime::createFromFormat('!m', $value);
        return $dateObj->format('F');
    }

    /*
     * Each INFRA Sheet should have many items associated with it.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany('App\InfraItem');
    }
}
