<?php

namespace App;

use POS;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LineDrive extends Model
{
    use SoftDeletes;

    protected $table = 'line_drives';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['brand',
                           'discount',
                           'processed',
                           'flags',
                           'sale_begin',
                           'sale_end',
                           'expires'];

    protected $dates = ['sale_begin', 'sale_end', 'expires'];

    /*
     * ?
     */
    public function process()
    {
        $result = POS::applyLineDrive($this->brand, $this->discount, $this->sale_begin, $this->sale_end, $this->id);

        if($result === false) {
            $this->flags = 'An error occurred';
            $this->save();
        } else {
            $this->processed = true;
            $this->flags     = null;
            $this->save();
        }

        return true;
    }
}
