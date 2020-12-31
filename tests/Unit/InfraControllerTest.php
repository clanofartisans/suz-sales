<?php

namespace Tests\Unit;

use App\Events\InfraSheetUploaded;
use App\Http\Controllers\InfraController;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InfraControllerTest extends TestCase
{
    use DatabaseMigrations;

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

    /** @test */
    public function uploading_valid_infrasheet_saves_to_database_and_triggers_uploaded_event()
    {
        Event::fake();

        $testFile = base_path('tests/InfraFiles/TestInfraSheet-Valid.xls');

        $file = new UploadedFile($testFile, 'TestInfraSheet-Valid.xls');

        $this->post('/infra', [
            'upworkbook' => $file,
            'upmonth'    => '1',
            'upyear'     => '2020'
        ]);

        Event::assertDispatched(InfraSheetUploaded::class);
    }

    /** @test */
    public function uploading_invalid_infrasheet_does_not_save_or_trigger_uploaded_event()
    {
        Event::fake();

        $testFile = base_path('tests/InfraFiles/TestInfraSheet-NoKeHEWorksheet.xls');

        $file = new UploadedFile($testFile, 'TestInfraSheet-NoKeHEWorksheet.xls');

        $this->post('/infra', [
            'upworkbook' => $file,
            'upmonth'    => '1',
            'upyear'     => '2020'
        ]);

        Event::assertNotDispatched(InfraSheetUploaded::class);
    }
}
