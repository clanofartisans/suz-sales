<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDefaultEmployeeDiscount extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $now = Carbon::now('America/Chicago')->format('Y-m-d H:i:s.v');

        $data['IM_PRC_GRP']['GRP_COD'] = 'EMPDEFAULT';

        $YYMMDD = Carbon::now('America/Chicago')->format('ymd');
        $descr  = 'Employee Discount Default 25%';
        $data['IM_PRC_GRP']['DESCR']   = substr($descr, 0, 30);

        $data['IM_PRC_GRP']['DESCR_UPR'] = strtoupper($data['IM_PRC_GRP']['DESCR']);

        $data['IM_PRC_GRP']['BEG_DAT'] = null;
        $data['IM_PRC_GRP']['BEG_DT']  = null;
        $data['IM_PRC_GRP']['NO_BEG_DAT'] = 'Y';

        $data['IM_PRC_GRP']['END_DAT'] = null;
        $data['IM_PRC_GRP']['END_DT']  = null;
        $data['IM_PRC_GRP']['NO_END_DAT'] = 'Y';

        $data['IM_PRC_GRP']['CUST_FILT']       = "(AR_CUST.CATEG_COD = 'EMPLOYEE')";
        $data['IM_PRC_GRP']['CUST_FILT_TMPLT'] = "Checked=0
IndentLev=0
DataField=CATEG_COD
Template=is (exactly)
Value=EMPLOYEE
Value1=
Value2=
Operation=and";
        $data['IM_PRC_GRP']['CUST_FILT_TEXT'] = "Category is (exactly) EMPLOYEE";

        $data['IM_PRC_RUL']['GRP_COD']    = $data['IM_PRC_GRP']['GRP_COD'];
        $data['IM_PRC_RUL']['RUL_SEQ_NO'] = 1;
        $data['IM_PRC_RUL']['DESCR']      = $data['IM_PRC_GRP']['DESCR'];
        $data['IM_PRC_RUL']['DESCR_UPPR'] = $data['IM_PRC_GRP']['DESCR_UPR'];
        $data['IM_PRC_RUL']['ITEM_FILT']  = "(IM_ITEM.IS_DISCNTBL = 'Y')";

        $data['IM_PRC_RUL']['ITEM_FILT_TMPLT'] = "Checked=0
IndentLev=0
DataField=IS_DISCNTBL
Template=equals
Value=Y
Value1=
Value2=
Operation=and";

        $data['IM_PRC_RUL']['ITEM_FILT_TEXT'] = "Discountable equals Yes";
        $data['IM_PRC_RUL']['PRC_BRK_DESCR']  = "Min qty Price-1 - 25%";

        $data['IM_PRC_RUL_BRK']['GRP_COD']       = $data['IM_PRC_RUL']['GRP_COD'];
        $data['IM_PRC_RUL_BRK']['RUL_SEQ_NO']    = 1;
        $data['IM_PRC_RUL_BRK']['AMT_OR_PCT']    = number_format(25, 4);
        $data['IM_PRC_RUL_BRK']['PRC_BRK_DESCR'] = $data['IM_PRC_RUL']['PRC_BRK_DESCR'];

        DB::connection('sqlsrv')->table('IM_PRC_GRP')->insert([
            ['GRP_TYP'          => 'C',
             'GRP_COD'          => $data['IM_PRC_GRP']['GRP_COD'],
             'GRP_SEQ_NO'       => null,
             'DESCR'            => $data['IM_PRC_GRP']['DESCR'],
             'DESCR_UPR'        => $data['IM_PRC_GRP']['DESCR_UPR'],
             'CUST_FILT'        => $data['IM_PRC_GRP']['CUST_FILT'],
             'BEG_DAT'          => $data['IM_PRC_GRP']['BEG_DAT'],
             'NO_BEG_DAT'       => $data['IM_PRC_GRP']['NO_BEG_DAT'],
             'BEG_DT'           => $data['IM_PRC_GRP']['BEG_DT'],
             'END_DAT'          => $data['IM_PRC_GRP']['END_DAT'],
             'NO_END_DAT'       => $data['IM_PRC_GRP']['NO_END_DAT'],
             'END_DT'           => $data['IM_PRC_GRP']['END_DT'],
             'CUST_FILT_TMPLT'  => $data['IM_PRC_GRP']['CUST_FILT_TMPLT'],
             'LST_MAINT_DT'     => $now,
             'LST_MAINT_USR_ID' => config('pos.counterpoint.user'),
             'LST_LCK_DT'       => null,
             'CUST_FILT_TEXT'   => $data['IM_PRC_GRP']['CUST_FILT_TEXT'],
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
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::connection('sqlsrv')->delete("delete from IM_PRC_RUL_BRK WHERE GRP_COD =  'EMPDEFAULT'");
        DB::connection('sqlsrv')->delete("delete from IM_PRC_RUL WHERE GRP_COD =  'EMPDEFAULT'");
        DB::connection('sqlsrv')->delete("delete from IM_PRC_GRP WHERE GRP_COD =  'EMPDEFAULT'");
    }
}
