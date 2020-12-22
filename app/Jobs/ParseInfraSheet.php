<?php

namespace App\Jobs;

use App\InfraSheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParseInfraSheet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $infrasheet;

    /**
     * Create a new job instance.
     *
     * @param InfraSheet $infrasheet
     */
    public function __construct(InfraSheet $infrasheet)
    {
        $this->infrasheet = $infrasheet;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->infrasheet->parseSheet();
    }
}
