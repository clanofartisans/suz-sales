<?php

namespace App\Console\Commands;

use App\InfraItem;
use App\Jobs\CleanupImages;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupInfra extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:infra';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches cleanup jobs for expired infra sales';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $items = InfraItem::where('expires', '<', Carbon::now())
                          ->where('imaged', true)
                          ->get();

        foreach ($items as $item) {
            dispatch((new CleanupImages($item))->onQueue('cleanup'));
        }
    }
}
