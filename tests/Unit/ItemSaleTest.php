<?php

namespace Tests\Unit;

use App\ItemSale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemSaleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function set_discount_percent_to_provided_value()
    {
        $item = factory(ItemSale::class)->create();

        $item->discount_percent = 23.2;

        $this->assertEquals(23.2, $item->discount_percent);
    }

    /** @test */
    public function calculate_missing_discount_percent_from_sale_pricing()
    {
        $item = factory(ItemSale::class)->create([
            'regular_price'      => 10.00,
            'real_sale_price'    => 6.00,
            'savings_amount'     => 4.00
        ]);

        $item->discount_percent = '';

        $this->assertEquals(40, $item->discount_percent);
    }
}
