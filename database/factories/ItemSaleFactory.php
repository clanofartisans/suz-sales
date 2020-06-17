<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ItemSale;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(ItemSale::class, function (Faker $faker) {
    $faker->addProvider(new \Tests\Faker\CustomGenerators($faker)); // Provides UPC generator

    $prices = [];
    $prices['regular_price']      = $faker->randomFloat(2, 5,60);
    $prices['discount_percent']   = $faker->numberBetween(10, 50);
    $prices['real_sale_price']    = round($prices['regular_price'] * (100 - $prices['discount_percent']) / 100.0, 2);
    $prices['display_sale_price'] = '$' . number_format($prices['real_sale_price'], 2);
    $prices['savings_amount']     = $prices['regular_price'] - $prices['real_sale_price'];

    return [
        'applied'            => false,
        'approved'           => false,
        'brand'              => Str::upper($faker->company),
        'color'              => false,
        'desc'               => $faker->text(20),
        'discount_percent'   => $prices['discount_percent'],
        'display_sale_price' => $prices['display_sale_price'],
        'pos_update'         => true,
        'printed'            => false,
        'queued'             => false,
        'real_sale_price'    => $prices['real_sale_price'],
        'regular_price'      => $prices['regular_price'],
        'sale_begin'         => Carbon::now()->firstOfMonth(),
        'sale_category'      => 'Great Savings',
        'sale_end'           => Carbon::now()->endOfMonth(),
        'savings_amount'     => $prices['savings_amount'],
        'size'               => ($faker->numberBetween(1, 6) * 30) . ' CAPS',
        'upc'                => $faker->upc
    ];
});
