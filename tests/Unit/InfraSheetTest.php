<?php

namespace Tests\Unit;

use App\Exceptions\InfraFileTestException;
use App\InfraSheet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InfraSheetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @throws InfraFileTestException
     */
    public function test_a_valid_infra_file()
    {
        $file = base_path('tests/InfraFiles/TestInfraSheet-Valid.xls');

        $result = InfraSheet::testInfraFile($file);

        $this->assertTrue($result);
    }

    /**
     * @test
     * @throws InfraFileTestException
     */
    public function test_cannot_find_the_infra_file()
    {
        $file = base_path('tests/InfraFiles/asdf.txt');

        $this->expectException(InfraFileTestException::class);

        InfraSheet::testInfraFile($file);
    }

    /**
     * @test
     * @throws InfraFileTestException
     */
    public function test_an_infra_file_that_is_not_a_valid_spreadsheet_file()
    {
        $file = base_path('tests/InfraFiles/TestInfraSheet-NotSpreadsheet.xls');

        $this->expectException(InfraFileTestException::class);

        InfraSheet::testInfraFile($file);
    }

    /**
     * @test
     * @throws InfraFileTestException
     */
    public function test_an_infra_file_with_an_invalid_kehe_worksheet()
    {
        $file = base_path('tests/InfraFiles/TestInfraSheet-NoKeHEWorksheet.xls');

        $this->expectException(InfraFileTestException::class);

        InfraSheet::testInfraFile($file);
    }

    /**
     * @test
     * @throws InfraFileTestException
     */
    public function test_an_infra_file_with_invalid_data_headers()
    {
        $file = base_path('tests/InfraFiles/TestInfraSheet-NoDataHeaders.xls');

        $this->expectException(InfraFileTestException::class);

        InfraSheet::testInfraFile($file);
    }

    /**
     * @test
     * @throws InfraFileTestException
     */
    public function test_an_infra_file_with_an_invalid_brand_column()
    {
        $file = base_path('tests/InfraFiles/TestInfraSheet-NoBrandColumn.xls');

        $this->expectException(InfraFileTestException::class);

        InfraSheet::testInfraFile($file);
    }

    /**
     * @test
     * @throws InfraFileTestException
     */
    public function test_an_infra_file_with_an_invalid_description_column()
    {
        $file = base_path('tests/InfraFiles/TestInfraSheet-NoDescColumn.xls');

        $this->expectException(InfraFileTestException::class);

        InfraSheet::testInfraFile($file);
    }

    /**
     * @test
     * @throws InfraFileTestException
     */
    public function test_an_infra_file_with_an_invalid_price_column()
    {
        $file = base_path('tests/InfraFiles/TestInfraSheet-NoPriceColumn.xls');

        $this->expectException(InfraFileTestException::class);

        InfraSheet::testInfraFile($file);
    }

    /**
     * @test
     * @throws InfraFileTestException
     */
    public function test_an_infra_file_with_an_invalid_size_column()
    {
        $file = base_path('tests/InfraFiles/TestInfraSheet-NoSizeColumn.xls');

        $this->expectException(InfraFileTestException::class);

        InfraSheet::testInfraFile($file);
    }

    /**
     * @test
     * @throws InfraFileTestException
     */
    public function test_an_infra_file_with_an_invalid_upc_column()
    {
        $file = base_path('tests/InfraFiles/TestInfraSheet-NoUPCColumn.xls');

        $this->expectException(InfraFileTestException::class);

        InfraSheet::testInfraFile($file);
    }

    /**
     * @test
     * @throws InfraFileTestException
     */
    public function test_an_infra_file_with_an_invalid_product_data()
    {
        $file = base_path('tests/InfraFiles/TestInfraSheet-NoProducts.xls');

        $this->expectException(InfraFileTestException::class);

        InfraSheet::testInfraFile($file);
    }

    /**
     * @test
     * @throws InfraFileTestException
     */
    public function create_an_infra_sheet_from_an_uploaded_file_and_date()
    {
        $month = date('m');
        $year  = date('Y');

        $file = Storage::get('infrasheets/TestInfraSheet-Valid.xls');

        $uploadedFile = UploadedFile::fake()->createWithContent('TestInfraSheet-Valid.xls', $file);

        $infrasheet = InfraSheet::makeFromUpload($uploadedFile, $month, $year);

        $this->assertStringEndsWith('.xls', $infrasheet->filename);
        $this->assertRecentTimestamp(Carbon::createFromTimestamp(substr($infrasheet->filename, 12, -4)));
        $this->assertEquals($month, $infrasheet->month);
        $this->assertEquals($year, $infrasheet->year);
    }

    /** @test */
    public function get_formatted_date()
    {
        $infrasheet = InfraSheet::make(['month' => '1',
                                        'year'  => '2020']);

        $date = $infrasheet->formatted_date;

        $this->assertEquals('January 2020', $date);
    }
}
