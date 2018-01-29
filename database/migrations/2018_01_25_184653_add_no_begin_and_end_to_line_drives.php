<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNoBeginAndEndToLineDrives extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('line_drives', function($table) {
            $table->boolean('no_begin')->default(false);
            $table->boolean('no_end')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('line_drives', function($table) {
            $table->dropColumn('no_begin');
            $table->dropColumn('no_end');
        });
    }
}
