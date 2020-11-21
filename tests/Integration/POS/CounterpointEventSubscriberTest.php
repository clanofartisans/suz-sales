<?php

namespace Tests\Integration\POS;

use App\Events\InfraSheetUploaded;
use App\Exceptions\POSSystemException;
use App\InfraSheet;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CounterpointEventSubscriberTest extends TestCase
{
    /**
     * @test
     * @group integration
     */
    public function integration_with_counterpoint__initialize_a_new_infra_sale_month_if_none_exists()
    {
        $infrasheet = new InfraSheet;
        $infrasheet->month = 1;
        $infrasheet->year  = 2030;

        InfraSheetUploaded::dispatch($infrasheet);

        DB::connection(env('CP_DB_CONNECTION'));
        $result = DB::connection(env('CP_DB_CONNECTION'))->table('IM_PRC_GRP')
                                                              ->select('GRP_COD')
                                                              ->where('GRP_COD', 'INFRA0130')
                                                              ->get();

        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @test
     * @throws POSSystemException
     * @group integration
     */
    public function integration_with_counterpoint__do_not_initialize_a_new_infra_sale_if_one_already_exists()
    {
        $infrasheet = new InfraSheet;
        $infrasheet->month = 1;
        $infrasheet->year  = 2030;

        InfraSheetUploaded::dispatch($infrasheet);

        $this->expectException(POSSystemException::class);

        InfraSheetUploaded::dispatch($infrasheet);

    }

    protected function tearDown(): void
    {
        DB::connection(env('CP_DB_CONNECTION'))->table('IM_PRC_GRP')
            ->where('GRP_COD', 'INFRA0130')
            ->delete();

        parent::tearDown();
    }
}