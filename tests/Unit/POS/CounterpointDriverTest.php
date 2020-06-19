<?php

namespace Tests\Unit\POS;

use App\ItemSale;
use App\POS\Drivers\CounterpointDriver;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class CounterpointDriverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @test */
    public function get_an_item_from_counterpoint()
    {
        $sampleData = [
            'upc'           => '021245750123',
            'brand'         => 'KAL Brand',
            'desc'          => 'Melatonin 3mg',
            'size'          => '120 TABS',
            'regular_price' => 12.34
        ];

        $pos = Mockery::mock(CounterpointDriver::class)
                      ->shouldAllowMockingProtectedMethods()
                      ->makePartial();

        $pos->shouldReceive('getRawItemDataFromCounterpoint')
            ->with('021245750123')
            ->andReturn($sampleData)
            ->once();

        $item = $pos->getItem('021245750123');

        $this->assertInstanceOf(ItemSale::class, $item);
        $this->assertEquals('021245750123', $item->upc);
        $this->assertEquals('Melatonin 3mg', $item->desc);
    }

    /** @test */
    public function return_null_when_an_item_is_not_in_counterpoint()
    {
        $pos = Mockery::mock(CounterpointDriver::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $pos->shouldReceive('getRawItemDataFromCounterpoint')
            ->with('012345678905')
            ->andReturn(null)
            ->once();

        $item = $pos->getItem('012345678905');

        $this->assertNull($item);
    }

    /** @test */
    public function get_a_list_of_brands_from_counterpoint()
    {
        $sampleData = ["Burt's Bees",
                       'KAL Brand',
                       'Solaray'];

        $expectedReturn = ['Burt%27s+Bees' => "Burt's Bees",
                           'KAL+Brand'     => 'KAL Brand',
                           'Solaray'       => 'Solaray'];

        $pos = Mockery::mock(CounterpointDriver::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $pos->shouldReceive('getRawBrandDataFromCounterpoint')
            ->andReturn($sampleData)
            ->once();

        $brands = $pos->getBrands();

        $this->assertIsIterable($brands);
        $this->assertEquals($expectedReturn, $brands);
    }
}
