<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLineDrivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_drives', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->string('brand');
            $table->unsignedTinyInteger('discount');
            $table->boolean('processed');
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
        Schema::dropIfExists('line_drives');
    }
}
