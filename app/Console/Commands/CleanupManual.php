<?php namespace App\Console\Commands;

use Carbon\Carbon;
use App\ManualSale;
use App\Jobs\CleanupImages;
use Illuminate\Console\Command;

class CleanupManual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:manual';

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
        $items = ManualSale::withTrashed()
                           ->where('expires', '<', Carbon::now())
                           ->get();

        foreach($items as $item) {
            dispatch((new CleanupImages($item))->onQueue('cleanup'));
        }
    }
}
