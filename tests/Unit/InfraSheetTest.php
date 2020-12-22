<?php

namespace Tests\Unit;

use App\Exceptions\InfraFileTestException;
use App\InfraSheet;
use App\Jobs\ParseInfraSheet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InfraSheetTest extends TestCase
{
    use DatabaseMigrations;

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
        $month = date(1);
        $year  = date(2020);

        $file = Storage::get('infrasheets/TestInfraSheet-Valid.xls');

        $uploadedFile = UploadedFile::fake()->createWithContent('TestInfraSheet-Valid.xls', $file);

        $infrasheet = InfraSheet::makeFromUpload($uploadedFile, $month, $year);

        $this->assertStringEndsWith('.xls', $infrasheet->filename);
        $this->assertRecentTimestamp(Carbon::createFromTimestamp(substr($infrasheet->filename, 12, -4)));
        $this->assertEquals($month, $infrasheet->month);
        $this->assertEquals($year, $infrasheet->year);
        $this->assertDatabaseHas('infra_sheets', ['month' => 1, 'year' => 2020]);
    }

    /** @test */
    public function get_formatted_date()
    {
        $infrasheet = InfraSheet::make(['month' => '1',
                                        'year'  => '2020']);

        $date = $infrasheet->formatted_date;

        $this->assertEquals('January 2020', $date);
    }

    /** @test */
    public function clean_descriptions_from_infra_files()
    {
        $samples = [];

        // Something without special characters
        $samples[0]  = 'Cold Brew Coffee - Mocha';
        $expected[0] = 'Cold Brew Coffee - Mocha';

        // Non-whitespace, (R) symbol, whitespace
        $samples[1]  = 'OG Ezekiel 4:9® English Muffins - Sprouted Whole Grain';
        $expected[1] = 'OG Ezekiel 4:9 English Muffins - Sprouted Whole Grain';

        // Starts with (R) symbol, followed by whitespace
        $samples[2]  = '® RAW Probiotics Men';
        $expected[2] = 'RAW Probiotics Men';

        // (R) symbol surrounded by whitespace
        $samples[3]  = 'OG ® RAW Probiotic Kids';
        $expected[3] = 'OG RAW Probiotic Kids';

        // (R) symbol with no surrounding whitespace
        $samples[4]  = 'Stress Relax®Tranquil Sleep Enteric';
        $expected[4] = 'Stress Relax Tranquil Sleep Enteric';

        // Multiple (R) symbols
        $samples[5]  = "Stress Relax® Suntheanine® L'Theanine";
        $expected[5] = "Stress Relax Suntheanine L'Theanine";

        // (R) symbol followed by multiple spaces
        $samples[6]  = "Stress Relax Suntheanine®  L'Theanine";
        $expected[6] = "Stress Relax Suntheanine L'Theanine";

        // Both an (R) symbol and a (TM) symbol
        $samples[7]  = 'Targeted Choice® Just Breathe™';
        $expected[7] = 'Targeted Choice Just Breathe';

        // Ends with space, followed by an (R) symbol
        $samples[8]  = 'Cinnamon Force ®';
        $expected[8] = 'Cinnamon Force';

        // Ends with (TM) symbol
        $samples[9]  = 'Holy Basil Force™';
        $expected[9] = 'Holy Basil Force';

        $infrasheet = InfraSheet::make(['month' => '1',
                                        'year'  => '2020']);

        $cleaned = [];

        $cleaned[0] = $infrasheet->cleanText($samples[0]);
        $cleaned[1] = $infrasheet->cleanText($samples[1]);
        $cleaned[2] = $infrasheet->cleanText($samples[2]);
        $cleaned[3] = $infrasheet->cleanText($samples[3]);
        $cleaned[4] = $infrasheet->cleanText($samples[4]);
        $cleaned[5] = $infrasheet->cleanText($samples[5]);
        $cleaned[6] = $infrasheet->cleanText($samples[6]);
        $cleaned[7] = $infrasheet->cleanText($samples[7]);
        $cleaned[8] = $infrasheet->cleanText($samples[8]);
        $cleaned[9] = $infrasheet->cleanText($samples[9]);

        $this->assertEquals($expected[0], $cleaned[0]);
        $this->assertEquals($expected[1], $cleaned[1]);
        $this->assertEquals($expected[2], $cleaned[2]);
        $this->assertEquals($expected[3], $cleaned[3]);
        $this->assertEquals($expected[4], $cleaned[4]);
        $this->assertEquals($expected[5], $cleaned[5]);
        $this->assertEquals($expected[6], $cleaned[6]);
        $this->assertEquals($expected[7], $cleaned[7]);
        $this->assertEquals($expected[8], $cleaned[8]);
        $this->assertEquals($expected[9], $cleaned[9]);
    }

    /** @test */
    public function parsing_an_infra_sheet_queues_a_job_to_do_the_actual_parsing()
    {
        Queue::fake();

        $month = date('m');
        $year  = date('Y');

        $file = Storage::get('infrasheets/TestInfraSheet-Valid.xls');

        $uploadedFile = UploadedFile::fake()->createWithContent('TestInfraSheet-Valid.xls', $file);

        $infrasheet = InfraSheet::makeFromUpload($uploadedFile, $month, $year);

        $infrasheet->queueParseSheet();

        Queue::assertPushed(ParseInfraSheet::class, 1);
    }

    /** @test */
    public function parsing_an_infra_sheet_adds_items_locally()
    {
        $month = date('m');
        $year  = date('Y');

        $file = Storage::get('infrasheets/TestInfraSheet-Valid.xls');

        $uploadedFile = UploadedFile::fake()->createWithContent('TestInfraSheet-Valid.xls', $file);

        $infrasheet = InfraSheet::makeFromUpload($uploadedFile, $month, $year);

        $infrasheet->queueParseSheet();

        $this->assertDatabaseCount('item_sales', 12);
        $this->assertDatabaseHas('item_sales', ['upc' => '857554005773']);
        $this->assertDatabaseHas('item_sales', ['upc' => '073472001196']);
    }
}
