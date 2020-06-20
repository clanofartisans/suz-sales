<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfraSheetItemSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('infra_sheet_item_sale', function (Blueprint $table) {
            $table->unsignedBigInteger('infra_sheet_id');
            $table->unsignedBigInteger('item_sale_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('infra_sheet_item_sale');
    }
}
