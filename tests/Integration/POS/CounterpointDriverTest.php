<?php

namespace Tests\Integration\POS;

use App\POS\Facades\POS;
use App\ItemSale;
use App\POS\Drivers\CounterpointDriver;
use Illuminate\Database\Connection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class CounterpointDriverTest extends TestCase
{
    /**
     * @test
     * @group integration
     */
    public function integration_with_counterpoint__get_an_item_from_counterpoint()
    {
        $item = POS::getItem('021245750123');

        $this->assertInstanceOf(ItemSale::class, $item);
        $this->assertEquals('MELATONIN 3 3 MG', $item->desc);
        $this->assertEquals('KAL BRAND', $item->brand);
    }

    /**
     * @test
     * @group integration
     */
    public function integration_with_counterpoint__return_null_when_an_item_is_not_in_counterpoint()
    {
        $item = POS::getItem('123456789012');

        $this->assertNull($item);
    }

    /**
     * @test
     * @group integration
     */
    public function integration_with_counterpoint__get_a_list_of_brands_from_counterpoint()
    {
        $brands = POS::getBrands();

        $this->assertEquals($brands['KAL+BRAND'], 'KAL BRAND');
        $this->assertEquals($brands['SOLARAY'], 'SOLARAY');
    }

    /**
     * @test
     * @group integration
     */
    public function integration_with_counterpoint__get_the_same_item_using_multiple_upcs()
    {
        $one = POS::getItem('076630591218');
        $two = POS::getItem('076630588805');

        $this->assertEquals('ESTER C 1000 MG EFFERVESCENT P', $one->desc);
        $this->assertEquals('ESTER C 1000 MG EFFERVESCENT P', $two->desc);
    }
}
