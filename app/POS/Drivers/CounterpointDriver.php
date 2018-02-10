<?php namespace App\POS\Drivers;

use App\InfraItem;
use App\InfraSheet;
use DB;
use App\POS\POS;
use Carbon\Carbon;
use App\POS\Contracts\POSDriverInterface as POSDriverContract;

/**
 * Class CounterpointDriver
 */
class CounterpointDriver extends POS implements POSDriverContract
{
    /*
     * Look up an item in OrderDog and return it as a SimpleXML object.
     *
     * @param string $upc
     *
     * @return SimpleXMLElement|bool
     */
    public function getItem(string $upc)
    {
        $item = DB::connection('sqlsrv')->table('IM_ITEM')->where('ITEM_NO', $upc)->first();

        if($item) {

            return $item;
        }

        return false;
    }

    /*
     * Update an item in OrderDog with the provided discount info.
     * Returns true if we response was okay else returns false.
     *
     * @param string $discounted
     *
     * @return bool
     */
    public function updateItem($discounted)
    {
        if($discounted['sale_type'] == 'INFRA') {
            $this->updateInfraItem($discounted);
        }
        if($discounted['sale_type'] == 'Manual') {
            $this->updateManualItem($discounted);
        }

        return true;
    }

    /*
     * ?
     */
    protected function updateInfraItem($discounted)
    {
        $now = Carbon::now('America/Chicago')->format('Y-m-d H:i:s.v');

        $percent_off = $this->calcPercentageDiscount($discounted['reg_price'], $discounted['IM_PRC_RUL_BRK']['AMT_OR_PCT']);

        $test = DB::connection('sqlsrv')->table('IM_PRC_RUL')->insert([
            ['GRP_TYP'          => 'P',
             'GRP_COD'          => $discounted['IM_PRC_RUL']['GRP_COD'],
             'RUL_SEQ_NO'       => $discounted['IM_PRC_RUL']['RUL_SEQ_NO'],
             'DESCR'            => $discounted['IM_PRC_RUL']['DESCR'],
             'DESCR_UPR'        => $discounted['IM_PRC_RUL']['DESCR_UPPR'],
             'CUST_FILT'        => null,
             'CUST_FILT_TMPLT'  => null,
             'ITEM_FILT'        => $discounted['IM_PRC_RUL']['ITEM_FILT'],
             'ITEM_FILT_TMPLT'  => $discounted['IM_PRC_RUL']['ITEM_FILT_TMPLT'],
             'SAL_FILT'         => null,
             'SAL_FILT_TMPLT'   => null,
             'MIN_QTY'          => 0.0000,
             'LST_MAINT_DT'     => $now,
             'LST_MAINT_USR_ID' => config('pos.counterpoint.user'),
             'LST_LCK_DT'       => null,
             'CUSTOM_SP'        => null,
             'CUST_FILT_TEXT'   => '*** All ***',
             'ITEM_FILT_TEXT'   => $discounted['IM_PRC_RUL']['ITEM_FILT_TEXT'],
             'SAL_FILT_TEXT'    => '*** All ***',
             'PRC_BRK_DESCR'    => $discounted['IM_PRC_RUL']['PRC_BRK_DESCR'],
             'CUST_NO'          => null,
             'ITEM_NO'          => $discounted['IM_PRC_RUL']['ITEM_NO']]
            ]);

        DB::connection('sqlsrv')->table('IM_PRC_RUL_BRK')->insert([
            ['GRP_TYP'          => 'P',
             'GRP_COD'          => $discounted['IM_PRC_RUL_BRK']['GRP_COD'],
             'RUL_SEQ_NO'       => $discounted['IM_PRC_RUL_BRK']['RUL_SEQ_NO'],
             'PRC_METH'         => 'D',
            'PRC_BASIS'        => '1',
            'AMT_OR_PCT'       => $percent_off,
             'PRC_BRK_DESCR'    => $discounted['IM_PRC_RUL_BRK']['PRC_BRK_DESCR'],
             'LST_MAINT_DT'     => null,
             'LST_MAINT_USR_ID' => null,
             'LST_LCK_DT'       => null]
            ]);

        return true;
    }

    /*
     * ?
     */
    protected function updateManualItem($discounted)
    {
        $now = Carbon::now('America/Chicago')->format('Y-m-d H:i:s.v');

        $percent_off = $this->calcPercentageDiscount($discounted['reg_price'], $discounted['IM_PRC_RUL_BRK']['AMT_OR_PCT']);

        DB::connection('sqlsrv')->table('IM_PRC_GRP')->insert([
            ['GRP_TYP'          => 'C',
            'GRP_COD'          => $discounted['IM_PRC_GRP']['GRP_COD'],
            'GRP_SEQ_NO'       => null,
            'DESCR'            => $discounted['IM_PRC_GRP']['DESCR'],
            'DESCR_UPR'        => $discounted['IM_PRC_GRP']['DESCR_UPR'],
            'CUST_FILT'        => null,
            'BEG_DAT'          => $discounted['IM_PRC_GRP']['BEG_DAT'],
            'NO_BEG_DAT'       => $discounted['IM_PRC_GRP']['NO_BEG_DAT'],
            'BEG_DT'           => $discounted['IM_PRC_GRP']['BEG_DT'],
            'END_DAT'          => $discounted['IM_PRC_GRP']['END_DAT'],
            'NO_END_DAT'       => $discounted['IM_PRC_GRP']['NO_END_DAT'],
            'END_DT'           => $discounted['IM_PRC_GRP']['END_DT'],
            'CUST_FILT_TMPLT'  => null,
            'LST_MAINT_DT'     => $now,
            'LST_MAINT_USR_ID' => config('pos.counterpoint.user'),
            'LST_LCK_DT'       => null,
            'CUST_FILT_TEXT'   => '*** All ***',
            'CUST_NO'          => null,
            'MIX_MATCH_COD'    => null]
        ]);

        DB::connection('sqlsrv')->table('IM_PRC_RUL')->insert([
            ['GRP_TYP'          => 'C',
            'GRP_COD'          => $discounted['IM_PRC_RUL']['GRP_COD'],
            'RUL_SEQ_NO'       => $discounted['IM_PRC_RUL']['RUL_SEQ_NO'],
            'DESCR'            => $discounted['IM_PRC_RUL']['DESCR'],
            'DESCR_UPR'        => $discounted['IM_PRC_RUL']['DESCR_UPPR'],
            'CUST_FILT'        => null,
            'CUST_FILT_TMPLT'  => null,
            'ITEM_FILT'        => $discounted['IM_PRC_RUL']['ITEM_FILT'],
            'ITEM_FILT_TMPLT'  => $discounted['IM_PRC_RUL']['ITEM_FILT_TMPLT'],
            'SAL_FILT'         => null,
            'SAL_FILT_TMPLT'   => null,
            'MIN_QTY'          => 0.0000,
            'LST_MAINT_DT'     => $now,
            'LST_MAINT_USR_ID' => config('pos.counterpoint.user'),
            'LST_LCK_DT'       => null,
            'CUSTOM_SP'        => null,
            'CUST_FILT_TEXT'   => '*** All ***',
            'ITEM_FILT_TEXT'   => $discounted['IM_PRC_RUL']['ITEM_FILT_TEXT'],
            'SAL_FILT_TEXT'    => '*** All ***',
            'PRC_BRK_DESCR'    => $discounted['IM_PRC_RUL']['PRC_BRK_DESCR'],
            'CUST_NO'          => null,
            'ITEM_NO'          => $discounted['IM_PRC_RUL']['ITEM_NO']]
        ]);

        DB::connection('sqlsrv')->table('IM_PRC_RUL_BRK')->insert([
            ['GRP_TYP'          => 'C',
            'GRP_COD'          => $discounted['IM_PRC_RUL_BRK']['GRP_COD'],
            'RUL_SEQ_NO'       => $discounted['IM_PRC_RUL_BRK']['RUL_SEQ_NO'],
            'PRC_METH'         => 'D',
            'PRC_BASIS'        => '1',
            'AMT_OR_PCT'       => $percent_off,
            'PRC_BRK_DESCR'    => $discounted['IM_PRC_RUL_BRK']['PRC_BRK_DESCR'],
            'LST_MAINT_DT'     => null,
            'LST_MAINT_USR_ID' => null,
            'LST_LCK_DT'       => null]
        ]);

        return true;
    }

    /*
     * Calculate the sale prices and dates for this
     * item and add the necessary XML to the XML
     * we originally received from OrderDog.
     *
     * @param mixed  $item
     * @param string $realPrice
     * @param string $month
     * @param string $year
     *
     * @return array|string|bool
     */
    public function applyDiscountToItem($item, string $realPrice, string $month, string $year)
    {
        $args = $this->calcItemDiscountsFromInfra($item, $realPrice);

        if($args === false) {
            return 'Item price is lower than sale price';
        }
        $price   = $args['price'];

        $localID = $this->getLocalItemID($item, $month, $year);

        $c_begDate = Carbon::createFromFormat('F Y j', "$month $year 1");

        $data['sale_type'] = 'INFRA';

        $data['reg_price'] = $item->PRC_1;

        $data['IM_PRC_RUL']['GRP_COD']    = 'INFRA' . $c_begDate->format('my');
        $data['IM_PRC_RUL']['RUL_SEQ_NO'] = $localID;
        $data['IM_PRC_RUL']['DESCR']      = "$item->ITEM_NO $price";
        $data['IM_PRC_RUL']['DESCR_UPPR'] = strtoupper($data['IM_PRC_RUL']['DESCR']);
        $data['IM_PRC_RUL']['ITEM_FILT']  = "(IM_ITEM.ITEM_NO = '$item->ITEM_NO')";

        $data['IM_PRC_RUL']['ITEM_FILT_TMPLT'] = "Checked=0
IndentLev=0
DataField=ITEM_NO
Template=is (exactly)
Value=$item->ITEM_NO
Value1=
Value2=
Operation=and";

        $data['IM_PRC_RUL']['ITEM_FILT_TEXT'] = "Item number is (exactly) $item->ITEM_NO";
        $data['IM_PRC_RUL']['PRC_BRK_DESCR']  = "Min qty $price";
        $data['IM_PRC_RUL']['ITEM_NO']        = $item->ITEM_NO;

        $data['IM_PRC_RUL_BRK']['GRP_COD']       = $data['IM_PRC_RUL']['GRP_COD'];
        $data['IM_PRC_RUL_BRK']['RUL_SEQ_NO']    = $data['IM_PRC_RUL']['RUL_SEQ_NO'];
        $data['IM_PRC_RUL_BRK']['AMT_OR_PCT']    = $price;
        $data['IM_PRC_RUL_BRK']['PRC_BRK_DESCR'] = $data['IM_PRC_RUL']['DESCR'];

        return $data;
    }

    /*
     * ?
     */
    public function applyDiscountToManualSale($item, string $amount, string $price, $start, $end, $id, $no_begin, $no_end)
    {
        $data['sale_type'] = 'Manual';

        $data['reg_price'] = $item->PRC_1;

        $data['IM_PRC_GRP']['GRP_COD'] = 'SMMS' . $id;

        $YYMMDD = Carbon::now('America/Chicago')->format('ymd');
        $descr   = $item->ITEM_NO . ' '. $YYMMDD. ' ' . $item->PROF_ALPHA_2 . ' ' . $item->DESCR;
        $data['IM_PRC_GRP']['DESCR']   = substr($descr, 0, 30);

        $data['IM_PRC_GRP']['DESCR_UPR'] = strtoupper($data['IM_PRC_GRP']['DESCR']);

        if($no_begin) {
            $data['IM_PRC_GRP']['BEG_DAT'] = null;
            $data['IM_PRC_GRP']['BEG_DT']  = null;
            $data['IM_PRC_GRP']['NO_BEG_DAT'] = 'Y';
        } else {
            $data['IM_PRC_GRP']['BEG_DAT'] = $start->format('Y-m-d') . ' 00:00:00.000';
            $data['IM_PRC_GRP']['BEG_DT']  = $data['IM_PRC_GRP']['BEG_DAT'];
            $data['IM_PRC_GRP']['NO_BEG_DAT'] = 'N';
        }

        if($no_end) {
            $data['IM_PRC_GRP']['END_DAT'] = null;
            $data['IM_PRC_GRP']['END_DT']  = null;
            $data['IM_PRC_GRP']['NO_END_DAT'] = 'Y';
        } else {
            $data['IM_PRC_GRP']['END_DAT'] = $end->format('Y-m-d') . ' 00:00:00.000';
            $data['IM_PRC_GRP']['END_DT']  = $end->format('Y-m-d') . ' 23:59:59.000';
            $data['IM_PRC_GRP']['NO_END_DAT'] = 'N';
        }

        $data['IM_PRC_RUL']['GRP_COD']    = $data['IM_PRC_GRP']['GRP_COD'];
        $data['IM_PRC_RUL']['RUL_SEQ_NO'] = 1;
        $data['IM_PRC_RUL']['DESCR']      = $data['IM_PRC_GRP']['DESCR'];
        $data['IM_PRC_RUL']['DESCR_UPPR'] = $data['IM_PRC_GRP']['DESCR_UPR'];
        $data['IM_PRC_RUL']['ITEM_FILT']  = "(IM_ITEM.ITEM_NO = '$item->ITEM_NO')";

        $data['IM_PRC_RUL']['ITEM_FILT_TMPLT'] = "Checked=0
IndentLev=0
DataField=ITEM_NO
Template=is (exactly)
Value=$item->ITEM_NO
Value1=
Value2=
Operation=and";

        $data['IM_PRC_RUL']['ITEM_FILT_TEXT'] = "Item number is (exactly) $item->ITEM_NO";
        $data['IM_PRC_RUL']['PRC_BRK_DESCR']  = "Min qty $price";
        $data['IM_PRC_RUL']['ITEM_NO']        = $item->ITEM_NO;

        $data['IM_PRC_RUL_BRK']['GRP_COD']       = $data['IM_PRC_RUL']['GRP_COD'];
        $data['IM_PRC_RUL_BRK']['RUL_SEQ_NO']    = 1;
        $data['IM_PRC_RUL_BRK']['AMT_OR_PCT']    = $price;
        $data['IM_PRC_RUL_BRK']['PRC_BRK_DESCR'] = "$item->ITEM_NO $price";

        return $data;
    }

    /*
     * Insert the discount info into the item's XML.
     *
     * @param SimpleXML $item
     * @param string    $discountXML
     *
     * @return string|bool
     */
    protected function insertDiscountAtCorrectPosition($item, $discountXML)
    {
        if(!isset($item->ItemDiscounts)) {
            $discounted = $this->insertDiscountWithoutExistingDiscounts($item, $discountXML);
        } else {
            $discounted = $this->insertDiscountWithExistingDiscounts($item, $discountXML);
        }

        return $discounted;
    }

    /*
     * Calculates and returns all the pricing info for an item.
     *
     * @param SimpleXML $item
     * @param string    $realPrice
     *
     * @return array|bool
     */
    protected static function calcItemDiscountsFromInfra($item, $realPrice)
    {
        if($realPrice == '20%') {
            $price = (float) $item->PRC_1;

            $realPrice = round(($price * 0.8), 2);

            $amount    = $price - $realPrice;

            $args['disp_msrp']       = (string) (number_format($price, 2));
            $args['disp_sale_price'] = (string) (number_format($realPrice, 2));
            $args['disp_savings']    = (string) (number_format($amount, 2));

            $args['percent'] = 'true';
            $args['amount']  = '20.0000';
            $args['price']   = (string) (number_format($realPrice, 4));
        } else {
            $realPrice = (float) $realPrice;
            $amount    = ((float) $item->PRC_1) - $realPrice;

            if($amount <= 0.00) {
                return false;
            }

            $args['disp_msrp']       = (string) (number_format(((float) $item->PRC_1), 2));
            $args['disp_sale_price'] = (string) (number_format($realPrice, 2));
            $args['disp_savings']    = (string) (number_format($amount, 2));

            $args['percent'] = 'false';
            $args['amount']  = (string) (number_format($amount, 4));
            $args['price']   = (string) (number_format($realPrice, 4));
        }

        return $args;
    }

    /*
     * Calculates and returns the first and last day of the provided month.
     *
     * @param string $month
     * @param string $year
     *
     * @return array
     */
    protected function calcItemDiscountDates($month, $year)
    {
        $start = new Carbon("first day of $month $year");
        $end   = new Carbon("last day of $month $year");

        $dates['start'] = $start->format('n/j/Y');
        $dates['end']   = $end->format('n/j/Y');

        return $dates;
    }

    /*
     * Sets the "display" prices based on the
     * calculated prices and INFRA's info.
     *
     * @param mixed  $item
     * @param string $infraPrice
     *
     * @return array|bool
     */
    public function getDisplayPricesFromItem($item, string $infraPrice)
    {
        $prices = self::calcItemDiscountsFromInfra($item, $infraPrice);

        if($prices === false) {
            return false;
        }

        $display['sale_price'] = $prices['disp_sale_price'];
        $display['msrp']       = $prices['disp_msrp'];
        $display['savings']    = $prices['disp_savings'];

        return $display;
    }

    /*
     * ?
     */
    public function quickQuery(string $upc)
    {
        $return = [];

        if($item = $this->getItem($upc)) {

            $price = (float) $item->PRC_1;
            $return['brand']  = (string) $item->PROF_ALPHA_2;
            $return['desc']   = ((string) $item->DESCR) . ' ' . ((string) $item->PROF_ALPHA_1);
            $return['price']  = (string) (number_format($price, 2));

            if($return['brand'] == "PRIVATE LABEL" ||
               $return['brand'] == "VITALITY WORKS" ||
               $return['brand'] == "RELIANCE PRIVATE LABEL")
            {
                $return['brand'] = "Suzanne's";
            }

            return $return;
        }

        return false;
    }

    /*
     * ?
     */
    public function getBrands()
    {
        $brands = [];

        $result = DB::connection('sqlsrv')->table('IM_ITEM')
                                          ->select('PROF_ALPHA_2')
                                          ->distinct()
                                          ->orderBy('PROF_ALPHA_2', 'asc')
                                          ->get();

        foreach($result as $raw) {
            $brand   = $raw->PROF_ALPHA_2;
            $encoded = urlencode($brand);

            $brands[$encoded] = $brand;
        }

        return $brands;
    }

    public static function escapeBrand($brand)
    {
        $escaped = str_replace ("'","''", $brand);

        return $escaped;
    }

    /*
     * ?
     */
    public function applyLineDrive($brand, $discount, $begin, $end, $id, $no_begin, $no_end)
    {
        $now = Carbon::now('America/Chicago')->format('Y-m-d H:i:s.v');

        $escaped = self::escapeBrand($brand);

        $data['IM_PRC_GRP']['GRP_COD'] = 'SMLD' . $id;

        $YYMMDD = Carbon::now('America/Chicago')->format('ymd');
        $descr  = $brand . ' ' . $YYMMDD;
        $data['IM_PRC_GRP']['DESCR']   = substr($descr, 0, 30);

        $data['IM_PRC_GRP']['DESCR_UPR'] = strtoupper($data['IM_PRC_GRP']['DESCR']);

        if($no_begin) {
            $data['IM_PRC_GRP']['BEG_DAT'] = null;
            $data['IM_PRC_GRP']['BEG_DT']  = null;
            $data['IM_PRC_GRP']['NO_BEG_DAT'] = 'Y';
        } else {
            $data['IM_PRC_GRP']['BEG_DAT'] = $begin->format('Y-m-d') . ' 00:00:00.000';
            $data['IM_PRC_GRP']['BEG_DT']  = $data['IM_PRC_GRP']['BEG_DAT'];
            $data['IM_PRC_GRP']['NO_BEG_DAT'] = 'N';
        }

        if($no_end) {
            $data['IM_PRC_GRP']['END_DAT'] = null;
            $data['IM_PRC_GRP']['END_DT']  = null;
            $data['IM_PRC_GRP']['NO_END_DAT'] = 'Y';
        } else {
            $data['IM_PRC_GRP']['END_DAT'] = $end->format('Y-m-d') . ' 00:00:00.000';
            $data['IM_PRC_GRP']['END_DT']  = $end->format('Y-m-d') . ' 23:59:59.000';
            $data['IM_PRC_GRP']['NO_END_DAT'] = 'N';
        }

        $data['IM_PRC_RUL']['GRP_COD']    = $data['IM_PRC_GRP']['GRP_COD'];
        $data['IM_PRC_RUL']['RUL_SEQ_NO'] = 1;
        $data['IM_PRC_RUL']['DESCR']      = $data['IM_PRC_GRP']['DESCR'];
        $data['IM_PRC_RUL']['DESCR_UPPR'] = $data['IM_PRC_GRP']['DESCR_UPR'];
        $data['IM_PRC_RUL']['ITEM_FILT']  = "(IM_ITEM.PROF_ALPHA_2 = '$escaped')";

        $data['IM_PRC_RUL']['ITEM_FILT_TMPLT'] = "Checked=0
IndentLev=0
DataField=PROF_ALPHA_2
Template=is (exactly)
Value=$escaped
Value1=
Value2=
Operation=and";

        $data['IM_PRC_RUL']['ITEM_FILT_TEXT'] = "Brand is (exactly) $escaped";
        $data['IM_PRC_RUL']['PRC_BRK_DESCR']  = "Min qty Price-1 - $discount%";

        $data['IM_PRC_RUL_BRK']['GRP_COD']       = $data['IM_PRC_RUL']['GRP_COD'];
        $data['IM_PRC_RUL_BRK']['RUL_SEQ_NO']    = 1;
        $data['IM_PRC_RUL_BRK']['AMT_OR_PCT']    = number_format($discount, 4);
        $data['IM_PRC_RUL_BRK']['PRC_BRK_DESCR'] = $data['IM_PRC_RUL']['PRC_BRK_DESCR'];

        DB::connection('sqlsrv')->table('IM_PRC_GRP')->insert([
            ['GRP_TYP'          => 'C',
             'GRP_COD'          => $data['IM_PRC_GRP']['GRP_COD'],
             'GRP_SEQ_NO'       => null,
             'DESCR'            => $data['IM_PRC_GRP']['DESCR'],
             'DESCR_UPR'        => $data['IM_PRC_GRP']['DESCR_UPR'],
             'CUST_FILT'        => null,
             'BEG_DAT'          => $data['IM_PRC_GRP']['BEG_DAT'],
             'NO_BEG_DAT'       => $data['IM_PRC_GRP']['NO_BEG_DAT'],
             'BEG_DT'           => $data['IM_PRC_GRP']['BEG_DT'],
             'END_DAT'          => $data['IM_PRC_GRP']['END_DAT'],
             'NO_END_DAT'       => $data['IM_PRC_GRP']['NO_END_DAT'],
             'END_DT'           => $data['IM_PRC_GRP']['END_DT'],
             'CUST_FILT_TMPLT'  => null,
             'LST_MAINT_DT'     => $now,
             'LST_MAINT_USR_ID' => config('pos.counterpoint.user'),
             'LST_LCK_DT'       => null,
             'CUST_FILT_TEXT'   => '*** All ***',
             'CUST_NO'          => null,
             'MIX_MATCH_COD'    => null]
            ]);

        DB::connection('sqlsrv')->table('IM_PRC_RUL')->insert([
            ['GRP_TYP'          => 'C',
             'GRP_COD'          => $data['IM_PRC_RUL']['GRP_COD'],
             'RUL_SEQ_NO'       => $data['IM_PRC_RUL']['RUL_SEQ_NO'],
             'DESCR'            => $data['IM_PRC_RUL']['DESCR'],
             'DESCR_UPR'        => $data['IM_PRC_RUL']['DESCR_UPPR'],
             'CUST_FILT'        => null,
             'CUST_FILT_TMPLT'  => null,
             'ITEM_FILT'        => $data['IM_PRC_RUL']['ITEM_FILT'],
             'ITEM_FILT_TMPLT'  => $data['IM_PRC_RUL']['ITEM_FILT_TMPLT'],
             'SAL_FILT'         => null,
             'SAL_FILT_TMPLT'   => null,
             'MIN_QTY'          => 0.0000,
             'LST_MAINT_DT'     => $now,
             'LST_MAINT_USR_ID' => config('pos.counterpoint.user'),
             'LST_LCK_DT'       => null,
             'CUSTOM_SP'        => null,
             'CUST_FILT_TEXT'   => '*** All ***',
             'ITEM_FILT_TEXT'   => $data['IM_PRC_RUL']['ITEM_FILT_TEXT'],
             'SAL_FILT_TEXT'    => '*** All ***',
             'PRC_BRK_DESCR'    => $data['IM_PRC_RUL']['PRC_BRK_DESCR'],
             'CUST_NO'          => null,
             'ITEM_NO'          => null]
            ]);

        DB::connection('sqlsrv')->table('IM_PRC_RUL_BRK')->insert([
            ['GRP_TYP'          => 'C',
             'GRP_COD'          => $data['IM_PRC_RUL_BRK']['GRP_COD'],
             'RUL_SEQ_NO'       => $data['IM_PRC_RUL_BRK']['RUL_SEQ_NO'],
             'PRC_METH'         => 'D',
             'PRC_BASIS'        => '1',
             'AMT_OR_PCT'       => $data['IM_PRC_RUL_BRK']['AMT_OR_PCT'],
             'PRC_BRK_DESCR'    => $data['IM_PRC_RUL_BRK']['PRC_BRK_DESCR'],
             'LST_MAINT_DT'     => null,
             'LST_MAINT_USR_ID' => null,
             'LST_LCK_DT'       => null]
            ]);

        return true;
    }

    /*
     * ?
     */
    public function startInfraSheet(InfraSheet $infrasheet)
    {
        $c_begDate = Carbon::createFromFormat('F Y j', "$infrasheet->month $infrasheet->year 1");
        $c_endDate = $c_begDate->copy()->endOfMonth();

        $data = [];

        $data['groupCode'] = 'INFRA' . $c_begDate->format('my');
        $data['desc']      = 'INFRA ' . $c_begDate->format('F Y');
        $data['descUpper'] = strtoupper($data['desc']);
        $data['begDate']   = $c_begDate->format('Y-m-d') . ' 00:00:00.000';
        $data['begTime']   = $data['begDate'];
        $data['endDate']   = $c_endDate->format('Y-m-d') . ' 00:00:00.000';
        $data['endTime']   = $c_endDate->format('Y-m-d') . ' 23:59:59.000';

        $now = Carbon::now('America/Chicago')->format('Y-m-d H:i:s.v');

        DB::connection('sqlsrv')->table('IM_PRC_GRP')->insert([
            ['GRP_TYP'          => 'P',
             'GRP_COD'          => $data['groupCode'],
             'GRP_SEQ_NO'       => null,
             'DESCR'            => $data['desc'],
             'DESCR_UPR'        => $data['descUpper'],
             'CUST_FILT'        => null,
             'BEG_DAT'          => $data['begDate'],
             'NO_BEG_DAT'       => 'N',
             'BEG_DT'           => $data['begTime'],
             'END_DAT'          => $data['endDate'],
             'NO_END_DAT'       => 'N',
             'END_DT'           => $data['endTime'],
             'CUST_FILT_TMPLT'  => null,
             'LST_MAINT_DT'     => $now,
             'LST_MAINT_USR_ID' => config('pos.counterpoint.user'),
             'LST_LCK_DT'       => null,
             'CUST_FILT_TEXT'   => '*** All ***',
             'CUST_NO'          => null,
             'MIX_MATCH_COD'    => null]
            ]);

        return true;
    }

    protected function getLocalItemID($item, $month, $year)
    {
        $infraDate = Carbon::createFromFormat('F Y j', "$month $year 1");

        $infrasheet = InfraSheet::where('month', $infraDate->month)
                                ->where('year', $infraDate->year)
                                ->firstOrFail();

        $item = InfraItem::where('infrasheet_id', $infrasheet->id)
                         ->where('upc', $item->ITEM_NO)
                         ->firstOrFail();

        return $item->id;
    }

    protected function calcPercentageDiscount($reg_price, $sale_price)
    {
        $percentage = round(((1.0000 - ($sale_price / $reg_price)) * 100.0000), 4);

        return $percentage;
    }
}
