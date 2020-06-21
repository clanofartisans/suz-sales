<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Determines if a timestamp is "recent" or not (within [$differenceInSeconds] seconds).
     *
     * @param Carbon $timeToTest
     * @param int $differenceInSeconds
     */
    public function assertRecentTimestamp(Carbon $timeToTest, $differenceInSeconds = 600)
    {
        $this->assertEqualsWithDelta(Carbon::now()->timestamp, $timeToTest->timestamp, $differenceInSeconds);
    }
}
