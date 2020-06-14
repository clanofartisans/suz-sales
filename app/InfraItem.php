<?php

namespace App;

use App\Jobs\ApplySalePrice;
use App\Jobs\GenerateImage;
use Carbon\Carbon;
use File;
use Illuminate\Database\Eloquent\Model;
use POS;
use SnappyImage;

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
                           'brand_uc',
                           'desc',
                           'size',
                           'list_price',
                           'list_price_calc',
                           'approved',
                           'processed',
                           'imaged',
                           'printed',
                           'flags',
                           'expires',
                           'percent_off'];

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
     * and queue it up for processing for OrderDog. // ODREF
     */
    public function approve()
    {
        $this->approved = true;
        $this->save();
        dispatch((new ApplySalePrice($this))->onQueue('processing'));
    }

    /*
     * Process the item and update it in OrderDog. If everything // ODREF
     * goes okay, queue another job to generate the printable
     * sale tag image we'll use to generate PDF documents.
     *
     * @return bool
     */
    public function process()
    {
        $item = POS::getItem($this->upc);

        if ($item === false) {
            $this->flags = 'Item not found in point of sale system';
            $this->save();
        } else {
            $month = $this->infrasheet->month;
            $year  = $this->infrasheet->year;

            $updateDisplayPrices = $this->updateWithPOSInfo($item);

            if ($updateDisplayPrices === false) {
                $this->flags = 'Item price is lower than sale price';
                $this->save();

                return true;
            }

            if (!isset($this->percent_off)) {
                $this->percent_off = $this->calcPercentageDiscount($item->PRC_1, $this->list_price_calc);
            }

            $discounted = POS::applyDiscountToItem($item, $this->list_price_calc, $month, $year, $this->percent_off, $this->id);

            if ($discounted === false) {
                $this->flags = 'Item already has discounts';
                $this->save();
            } elseif ($discounted === 'Item price is lower than sale price') {
                $this->flags = 'Item price is lower than sale price';
                $this->save();
            } else {
                dispatch((new GenerateImage($this))->onQueue('imaging'));

                if (POS::updateItem($discounted)) {
                    $this->processed = true;
                    $this->flags     = null;
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
        $image = SnappyImage::loadView('saletags.salebw', ['data' => $this]);

        $filename = storage_path("app/images/infra/$this->id.png");

        $image->save($filename, true);

        if (File::exists($filename)) {
            $this->imaged = true;

            $expiration    = $this->calcExpirationDate();
            $this->expires = $expiration;

            $this->save();

            return true;
        }

        return false;
    }

    public function queue()
    {
        $this->printed = false;
        $this->queued  = true;
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

    /*
     * Get the base pricing info from OrderDog, then // ODREF
     * calculate all the sale and display prices,
     * and then save the info to the database.
     *
     * @param \SimpleXMLElement $info
     *
     * @return bool
     */
    public function updateWithPOSInfo($info)
    {
        $prices = POS::getDisplayPricesFromItem($info, $this->list_price_calc);

        if ($prices === false) {
            return false;
        }

        if (strpos($this->list_price, '/') !== false) {
            $this->disp_sale_price = $this->list_price;
        } else {
            $this->disp_sale_price = '$'.$prices['sale_price'];
        }

        $this->disp_msrp    = $prices['msrp'];
        $this->disp_savings = $prices['savings'];

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

    public function cleanup()
    {
        $filename = storage_path("app/images/infra/$this->id.png");

        File::delete($filename);

        $this->imaged = true;
        $this->save();

        return true;
    }

    protected function calcPercentageDiscount($reg_price, $sale_price)
    {
        if ($sale_price == '20%') {
            return 20.0000;
        }

        $percentage = round(((1.0000 - ($sale_price / $reg_price)) * 100.0000), 4);

        return $percentage;
    }
}
