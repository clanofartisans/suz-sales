<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManualSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manual_sales', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->string('upc');
            $table->string('brand');
            $table->string('desc');
            $table->float('sale_price', 6, 2);
            $table->string('disp_sale_price')->nullable();
            $table->float('reg_price', 6, 2);
            $table->float('savings', 6, 2);
            $table->string('sale_cat');
            $table->boolean('color');
            $table->boolean('processed');
            $table->boolean('imaged');
            $table->boolean('printed');
            $table->text('flags')->nullable();
            $table->timestamp('sale_begin')->nullable();
            $table->timestamp('sale_end')->nullable();
            $table->timestamp('expires')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manual_sales');
    }
}
