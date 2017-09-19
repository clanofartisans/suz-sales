<?php

namespace App\Jobs;

use App\InfraItem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $item;
    public $tries = 5;

    /*
     * Serialize the given InfraItem into the job.
     */
    public function __construct(InfraItem $item)
    {
        $this->item = $item;
    }

    /*
     * Call the processImage method on the job's InfraItem instance.
     */
    public function handle()
    {
        $this->item->processImage();
    }
}
