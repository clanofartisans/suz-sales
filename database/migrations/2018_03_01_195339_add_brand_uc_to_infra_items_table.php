<?php

//use App\InfraItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandUcToInfraItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('infra_items', function ($table) {
            $table->string('brand_uc')->nullable();
        });
/*
        foreach (InfraItem::all() as $item) {
            $item->update([
              'brand_uc' => strtoupper($item->brand)
            ]);
        }
*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('infra_items', function ($table) {
            $table->dropColumn('brand_uc');
        });
    }
}
