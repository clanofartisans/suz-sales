<?php

namespace App\Jobs;

use POS;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RenumberSales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    /*
     * Serialize the given InfraItem into the job.
     */
    public function __construct()
    {
        //
    }

    /*
     * Call the process method on the job's InfraItem instance.
     */
    public function handle()
    {
        POS::performRenumbering();
    }
}
