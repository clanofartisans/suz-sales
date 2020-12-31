<?php

//use App\InfraItem;
//use App\ManualSale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorNotFoundInOrderdogFlagToPosFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        $infraItems = InfraItem::where('flags', 'Item not found in OrderDog')
                               ->get();

        foreach ($infraItems as $item) {
            $item->update(['flags' => 'Item not found in point of sale system']);
        }

        $manualItems = ManualSale::where('flags', 'Item not found in OrderDog')
                                 ->get();

        foreach ($manualItems as $item) {
            $item->update(['flags' => 'Item not found in point of sale system']);
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
        /*
        $infraItems = InfraItem::where('flags', 'Item not found in point of sale system')
                               ->get();

        foreach ($infraItems as $item) {
            $item->update(['flags' => 'Item not found in OrderDog']);
        }

        $manualItems = ManualSale::where('flags', 'Item not found in point of sale system')
                                 ->get();

        foreach ($manualItems as $item) {
            $item->update(['flags' => 'Item not found in OrderDog']);
        }
        */
    }
}
