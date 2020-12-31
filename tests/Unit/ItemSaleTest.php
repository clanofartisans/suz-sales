<?php

namespace Tests\Unit;

use App\ItemSale;
use Faker\Generator as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class ItemSaleTest extends TestCase
{
    use MockeryPHPUnitIntegration, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Provides UPC generator
        $this->faker = new Faker();
        $this->faker->addProvider(new \Tests\Faker\CustomGenerators($this->faker));
    }

    /** @test */
    public function calculate_missing_discount_percent_from_sale_pricing()
    {
        $item = factory(ItemSale::class)->create([
            'regular_price'   => 10.00,
            'real_sale_price' => 6.00,
            'savings_amount'  => 4.00
        ]);

        $item->discount_percent = '';

        $this->assertEquals(40, $item->discount_percent);
    }

    /** @test */
    public function sale_is_flagged_if_item_not_found_in_pos_system()
    {
        $sale = Mockery::mock(ItemSale::class)
                       ->shouldAllowMockingProtectedMethods()
                       ->makePartial();

        $sale->shouldReceive('getPOSItem')
            ->andReturn(null)
            ->once();

        $sale->upc = $this->faker->upc;
        $sale->calculatePricingData(1.23);

        $this->assertEquals('$1.23', $sale->display_sale_price);
        $this->assertEquals('Item not found in point of sale system', $sale->flags);
    }

/*
 * regular_price
 * display_sale_price
 * real_sale_price
 * savings_amount
 * discount_percent
 * flags
 */

    /** @test */
    public function default_to_twenty_percent_discount_if_no_sale_price_specified()
    {
        $posData = factory(ItemSale::class)->create([
            'regular_price' => 10.00
        ]);

        $sale = Mockery::mock(ItemSale::class)
                       ->shouldAllowMockingProtectedMethods()
                       ->makePartial();

        $sale->shouldReceive('getPOSItem')
             ->andReturn($posData)
             ->once();

        $sale->upc = $this->faker->upc;
        $sale->calculatePricingData(null);

        $this->assertEquals(10.00, $sale->regular_price);
        $this->assertEquals('$8.00', $sale->display_sale_price);
        $this->assertEquals(8.00, $sale->real_sale_price);
        $this->assertEquals(2.00, $sale->savings_amount);
        $this->assertEquals(20.0, $sale->discount_percent);
        $this->assertEquals(null, $sale->flags);
    }

    /** @test */
    public function creating_a_sale_rounds_prices_to_two_decimal_places()
    {
        $posData = factory(ItemSale::class)->create([
            'regular_price' => 2.19
        ]);

        $sale = Mockery::mock(ItemSale::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $sale->shouldReceive('getPOSItem')
            ->andReturn($posData)
            ->once();

        $sale->upc = $this->faker->upc;
        $sale->calculatePricingData();

        $this->assertEquals(2.19, $sale->regular_price);
        $this->assertEquals('$1.75', $sale->display_sale_price);
        $this->assertEquals(1.75, $sale->real_sale_price);
        $this->assertEquals(0.44, $sale->savings_amount);
        $this->assertEquals(20.0913, $sale->discount_percent);
        $this->assertEquals(null, $sale->flags);
    }

    /** @test */
    public function creating_a_sale_with_quantity_for_amount_notation_calculates_prices_correctly()
    {
        $posData = factory(ItemSale::class)->create([
            'regular_price' => 3.50
        ]);

        $sale = Mockery::mock(ItemSale::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $sale->shouldReceive('getPOSItem')
            ->andReturn($posData)
            ->once();

        $sale->upc = $this->faker->upc;
        $sale->calculatePricingData('2/$5');

        $this->assertEquals(3.50, $sale->regular_price);
        $this->assertEquals('2/$5', $sale->display_sale_price);
        $this->assertEquals(2.50, $sale->real_sale_price);
        $this->assertEquals(1.00, $sale->savings_amount);
        $this->assertEquals(28.5714, $sale->discount_percent);
        $this->assertEquals(null, $sale->flags);
    }

    /** @test */
    public function flag_a_sale_if_pos_price_is_lower_than_sale_price()
    {
        $posData = factory(ItemSale::class)->create([
            'regular_price' => 1.23
        ]);

        $sale = Mockery::mock(ItemSale::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $sale->shouldReceive('getPOSItem')
            ->andReturn($posData)
            ->once();

        $sale->upc = $this->faker->upc;
        $sale->calculatePricingData(4.56);

        $this->assertEquals(1.23, $sale->regular_price);
        $this->assertEquals('$4.56', $sale->display_sale_price);
        $this->assertEquals(4.56, $sale->real_sale_price);
        $this->assertEquals('Item price is lower than sale price', $sale->flags);
    }
}
