<?php

namespace App;

use File;
use SnappyImage;
use Carbon\Carbon;
use App\Jobs\GenerateImage;
use App\Jobs\ApplySalePrice;
use Illuminate\Database\Eloquent\Model;

class InfraItem extends Model
{
    /**
     * The model's database table.
     *
     * @var array
     */
    protected $table = 'infra_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['infrasheet_id',
                           'upc',
                           'brand',
                           'desc',
                           'size',
                           'list_price',
                           'list_price_calc',
                           'approved',
                           'processed',
                           'imaged',
                           'printed',
                           'flags',
                           'expires'];

    /*
     * Each item belongs to an INFRA workbook.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function infrasheet()
    {
        return $this->belongsTo('App\InfraSheet');
    }

    /*
     * The sale has been approved. Update the database
     * and queue it up for processing for OrderDog.
     */
    public function approve()
    {
        $this->approved = true;
        $this->save();
        dispatch((new ApplySalePrice($this))->onQueue('processing'));
    }

    /*
     * Process the item and update it in OrderDog. If everything
     * goes okay, queue another job to generate the printable
     * sale tag image we'll use to generate PDF documents.
     *
     * @return bool
     */
    public function process()
    {
        $getAPI = new OrderDogAPI;

        $item = $getAPI->getItem($this->upc);

        if($item === false) {
            $this->flags = 'Item not found in OrderDog';
            $this->save();
        } else {
            $month = $this->infrasheet->month;
            $year  = $this->infrasheet->year;

            $updateDisplayPrices = $this->updateWithOrderDogInfo($item);

            if($updateDisplayPrices === false) {
                return false;
            }

            $discounted = $getAPI->applyDiscountToItem($item, $this->list_price_calc, $month, $year);

            if($discounted === false) {
                $this->flags = 'Item already has discounts';
                $this->save();
            } elseif($discounted === 'Item price is lower than sale price') {
                $this->flags = 'Item price is lower than sale price';
                $this->save();
            } else {
                dispatch((new GenerateImage($this))->onQueue('imaging'));

                $updateAPI = new OrderDogAPI;

                if($updateAPI->updateItem($discounted)) {
                    $this->processed = true;
                    $this->flags = null;
                    $this->save();
                }
            }
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
        $image = SnappyImage::loadView('saletags.salebw', array('data' => $this));

        $filename = realpath(storage_path('app/images/infra')) . '\\' . $this->id . '.png';

        $image->save($filename, true);

        if (File::exists($filename)) {
            $this->imaged = true;

            $expiration = $this->calcExpirationDate();
            $this->expires = $expiration;

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

    /*
     * Get the base pricing info from OrderDog, then
     * calculate all the sale and display prices,
     * and then save the info to the database.
     *
     * @param SimpleXML|bool $info
     *
     * @return bool
     */
    public function updateWithOrderDogInfo($info)
    {
        $prices = OrderDogAPI::getDisplayPricesFromItem($info, $this->list_price_calc);

        if($prices === false) {
            return false;
        }

        if(strpos($this->list_price, '/') !== false) {
            $this->disp_sale_price = $this->list_price;
        } else {
            $this->disp_sale_price = '$'.$prices['sale_price'];
        }

        $this->disp_msrp       = $prices['msrp'];
        $this->disp_savings    = $prices['savings'];

        $this->save();

        return true;
    }

    /*
     * Calculate the expiration date for the sale.
     *
     * @return Carbon
     */
    public function calcExpirationDate()
    {
        $month = $this->infrasheet->month;
        $year  = $this->infrasheet->year;

        $lastDay = new Carbon("last day of $month $year");

        $expires = $lastDay->copy()->addDay();

        return $expires;
    }
}
