<?php

namespace Tests\Unit;

use App\Http\Controllers\InfraController;
use Carbon\Carbon;
use Tests\TestCase;

class InfraControllerTest extends TestCase
{
    /** @test */
    public function get_previous_current_and_next_year()
    {
        Carbon::setTestNow(Carbon::parse('June 20, 2020'));

        $controller = new InfraController;

        $years = $controller->getUploadFormYears();

        $this->assertEquals([2019, 2020, 2021], $years);
    }

    /** @test */
    public function get_form_selected_month()
    {
        Carbon::setTestNow(Carbon::parse('December 1, 2020'));

        $controller = new InfraController;

        $selectedMonth = $controller->getSelectedMonth();

        $this->assertEquals(1, $selectedMonth);
    }

    /** @test */
    public function get_form_selected_year()
    {
        Carbon::setTestNow(Carbon::parse('December 1, 2020'));

        $controller = new InfraController;

        $selectedYear = $controller->getSelectedYear();

        $this->assertEquals(2021, $selectedYear);
    }
}
