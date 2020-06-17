<?php

namespace Tests\Feature;

use App\ItemSale;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ManualSaleTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /** @test */
    public function user_can_view_a_manual_sale()
    {
        factory(ItemSale::class)->create([
            'upc'                => '021245750123',
            'brand'              => 'KAL BRAND',
            'desc'               => 'Melatonin 3mg',
            'size'               => '120 TABS',
            'regular_price'      => 10.00,
            'display_sale_price' => '2/$12',
            'real_sale_price'    => 6.00,
            'savings_amount'     => 4.00,
            'discount_percent'   => 40.0,
            'sale_begin'         => Carbon::create(2019, 6, 1),
            'sale_end'           => Carbon::create(2019, 6, 30),
            'sale_category'      => 'Every Day Low Price'
        ]);

        $response = $this->get('/manual');

        $response->assertSee('021245750123');
        $response->assertSee('KAL BRAND');
        $response->assertSee('Melatonin 3mg 120 TABS');
        $response->assertSee('2/$12');
        $response->assertSee('$10.00');
        $response->assertSee('40%');
        $response->assertSee('Jun 1, 2019');
        $response->assertSee('Jun 30, 2019');
        $response->assertSee('Every Day Low Price');
    }

    /** @test */
    public function user_can_view_a_list_of_manual_sales()
    {
        $items = [];

        $items[] = factory(ItemSale::class)->create([
            'upc' => '021245750123'
        ]);

        $items[] = factory(ItemSale::class)->create([
            'upc' => '033984019348'
        ]);

        $items[] = factory(ItemSale::class)->create([
            'upc' => '076280039009'
        ]);

        $response = $this->get('/manual');

        $response->assertSee('021245750123');
        $response->assertSee('033984019348');
        $response->assertSee('076280039009');
    }

    /** @test */
    public function user_can_create_a_manual_sale()
    {
        $this->post('/manual/store', [
            'pos_update'         => 'pos_update_yes',
            'bw_or_color'        => 'bw',
            'upc'                => '021245750123',
            'brand'              => 'KAL BRAND',
            'description'        => 'Melatonin 3mg',
            'size'               => '120 TABS',
            'real_sale_price'    => '6',
            'display_sale_price' => '2/$12',
            'regular_price'      => '10',
            'savings_amount'     => '4.00',
            'discount_percent'   => '',
            'sale_category'      => 'Every Day Low Price',
            'sale_begin'         => '06/01/2019',
            'sale_end'           => '06/30/2019'
        ]);

        $response = $this->get('/manual');

        $response->assertSee('021245750123');
        $response->assertSee('KAL BRAND');
        $response->assertSee('Melatonin 3mg 120 TABS');
        $response->assertSee('2/$12');
        $response->assertSee('$10.00');
        $response->assertSee('40%');
        $response->assertSee('Jun 1, 2019');
        $response->assertSee('Jun 30, 2019');
        $response->assertSee('Every Day Low Price');
    }
}
