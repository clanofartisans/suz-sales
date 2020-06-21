<?php

namespace Tests\Feature;

use App\InfraSheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class InfraSheetTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /** @test */
    public function user_can_view_a_list_of_infra_sheets()
    {
        $this->withoutExceptionHandling();

        $infrasheets = [];

        $infrasheets[] = InfraSheet::create(['filename' => '123.xls',
                                             'month'    => '1',
                                             'year'     => '2020']);

        $infrasheets[] = InfraSheet::create(['filename' => '456.xls',
                                             'month'    => '2',
                                             'year'     => '2020']);

        $infrasheets[] = InfraSheet::create(['filename' => '789.xls',
                                             'month'    => '3',
                                             'year'     => '2020']);

        $response = $this->get('/infra');

        $response->assertSee('January 2020');
        $response->assertSee('February 2020');
        $response->assertSee('March 2020');
    }
}
