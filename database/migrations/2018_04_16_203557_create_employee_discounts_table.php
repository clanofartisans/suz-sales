<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_discounts', function (Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->string('brand');
            $table->unsignedTinyInteger('discount');
            $table->boolean('processed');
            $table->text('flags')->nullable();
            $table->timestamp('sale_begin')->nullable();
            $table->timestamp('sale_end')->nullable();
            $table->timestamp('expires')->nullable();
            $table->boolean('no_begin')->default(true);
            $table->boolean('no_end')->default(true);
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
        Schema::dropIfExists('employee_discounts');
    }
}
