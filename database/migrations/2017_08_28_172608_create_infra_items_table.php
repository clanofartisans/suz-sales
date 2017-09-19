<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInfraItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('infra_items', function(Blueprint $table)
        {
            Schema::enableForeignKeyConstraints();

            $table->bigIncrements('id');
            $table->unsignedInteger('infrasheet_id');
            $table->string('upc');
            $table->string('brand');
            $table->string('desc');
            $table->string('size');
            $table->string('list_price')->nullable();
            $table->string('list_price_calc');
            $table->string('disp_sale_price')->nullable();
            $table->string('disp_msrp')->nullable();
            $table->string('disp_savings')->nullable();
            $table->boolean('approved');
            $table->boolean('processed');
            $table->boolean('imaged');
            $table->boolean('printed');
            $table->text('flags')->nullable();
            $table->timestamp('expires')->nullable();
            $table->timestamps();

            $table->foreign('infrasheet_id')
                  ->references('id')->on('infrasheets')
                  ->onDelete('cascade');

            Schema::disableForeignKeyConstraints();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::enableForeignKeyConstraints();

        Schema::drop('infra_items');

        Schema::disableForeignKeyConstraints();
    }
}
