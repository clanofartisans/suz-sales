<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApplyLineDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sale;
    public $tries = 5;

    /*
     * Serialize the given InfraItem into the job.
     */
    public function __construct($sale)
    {
        $this->sale = $sale;
    }

    /*
     * Call the process method on the job's InfraItem instance.
     */
    public function handle()
    {
        $this->sale->process();
    }
}
