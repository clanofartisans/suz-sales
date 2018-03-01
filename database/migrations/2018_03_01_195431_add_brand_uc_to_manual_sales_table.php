<?php

use App\ManualSale;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBrandUcToManualSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manual_sales', function($table) {
            $table->string('brand_uc')->nullable();
        });

        foreach (ManualSale::all() as $item) {
            $item->update([
              'brand_uc' => strtoupper($item->brand)
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manual_sales', function($table) {
            $table->dropColumn('brand_uc');
        });
    }
}
