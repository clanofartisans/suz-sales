<?php

namespace App;

use File;
use SnappyImage;
use App\OrderDogAPI;
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
                           'od_update',
                           'processed',
                           'imaged',
                           'printed',
                           'flags',
                           'sale_begin',
                           'sale_end',
                           'expires'];

    /*
     * Process the item and update it in OrderDog. If everything
     * goes okay, queue another job to generate the printable
     * sale tag image we'll use to generate PDF documents.
     *
     * @return bool
     */
    public function process()
    {
        if($this->od_update) {
            $getAPI = new OrderDogAPI;

            $item = $getAPI->getItem($this->upc);

            if($item === false) {
                $this->flags = 'Item not found in OrderDog';
                $this->save();
            } else {
                $discounted = $getAPI->applyDiscountToManualSale($item, $this->savings, $this->sale_price, $this->sale_begin, $this->sale_end);

                if($discounted === false) {
                    $this->flags = 'Item already has discounts';
                    $this->save();
                } else {
                    dispatch((new GenerateImage($this))->onQueue('imaging'));

                    $updateAPI = new OrderDogAPI;

                    if($updateAPI->updateItem($discounted)) {
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

            return true;
        }

        return false;
    }

    /*
     * Flag the item as having been printed.
     */
    public function print()
    {
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
