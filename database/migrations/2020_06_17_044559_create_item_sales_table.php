<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infra_sheet_id')->nullable();
            $table->string('upc');
            $table->string('brand');
            $table->string('desc');
            $table->string('size');
            $table->decimal('regular_price', 6, 2)->nullable();
            $table->string('display_sale_price');
            $table->decimal('real_sale_price', 6, 2)->nullable();
            $table->decimal('savings_amount', 6, 2)->nullable();
            $table->decimal('discount_percent', 7, 4)->nullable();
            $table->string('sale_category')->default('Great Savings');
            $table->boolean('color')->default(false);
            $table->boolean('pos_update')->default(true);
            $table->boolean('approved')->default(false);
            $table->boolean('applied')->default(false);
            $table->boolean('queued')->default(false);
            $table->boolean('printed')->default(false);
            $table->string('flags')->nullable();
            $table->timestamp('sale_begin')->nullable();
            $table->timestamp('sale_end')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_sales');
    }
}
