<?php

namespace App;

use POS;
use File;
use SnappyImage;
use App\Jobs\GenerateImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualSale extends Model
{
    use SoftDeletes;

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
                           'pos_update',
                           'processed',
                           'imaged',
                           'printed',
                           'flags',
                           'sale_begin',
                           'sale_end',
                           'expires',
                           'no_begin',
                           'no_end'];

    protected $dates = ['sale_begin', 'sale_end', 'expires'];

    /*
     * Process the item and update it in OrderDog. If everything // ODREF
     * goes okay, queue another job to generate the printable
     * sale tag image we'll use to generate PDF documents.
     *
     * @return bool
     */
    public function process()
    {
        if($this->pos_update) {

            $item = POS::getItem($this->upc);

            if($item === false) {
                $this->flags = 'Item not found in point of sale system';
                $this->save();
            } else {
                $discounted = POS::applyDiscountToManualSale($item, $this->savings, $this->sale_price, $this->sale_begin, $this->sale_end, $this->id, $this->no_begin, $this->no_end);

                if($discounted === false) {
                    $this->flags = 'Item already has discounts';
                    $this->save();
                } else {
                    dispatch((new GenerateImage($this))->onQueue('imaging'));

                    if(POS::updateItem($discounted)) {
                        $this->processed = true;
                        $this->flags     = null;
                        $this->save();
                    }
                }
            }
        } else {
            dispatch((new GenerateImage($this))->onQueue('imaging'));

            $this->processed = true;
            $this->save();
        }

        return true;
    }

    /*
     * Take all the item info and generate a sale tag image.
     *
     * @return bool
     */
    public function processImage()
    {
        if($this->color) {
            $view = 'saletags.salecolor';
        } else {
            $view = 'saletags.salebw';
        }

        $image = SnappyImage::loadView($view, array('data' => $this));

        $filename = storage_path("app/images/manual/$this->id.png");

        $image->save($filename, true);

        if (File::exists($filename)) {
            $this->imaged = true;
            $this->save();

            $this->queue();

            return true;
        }

        return false;
    }

    public function queue()
    {
        $this->printed = false;
        $this->queued = true;
        $this->save();
    }

    /*
     * Flag the item as having been printed.
     */
    public function print()
    {
        $this->queued  = false;
        $this->printed = true;
        $this->save();
    }

    public function cleanup()
    {
        $filename = storage_path("app/images/manual/$this->id.png");

        File::delete($filename);

        return true;
    }
}
