<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorManualSaleOdUpdateColumToPosUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manual_sales', function (Blueprint $table) {
            $table->renameColumn('od_update', 'pos_update');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manual_sales', function (Blueprint $table) {
            $table->renameColumn('pos_update', 'od_update');
        });
    }
}
