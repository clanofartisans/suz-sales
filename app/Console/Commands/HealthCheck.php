<?php namespace App\Console\Commands;

use Illuminate\Console\Command;

class HealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'healthcheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks application status and pings healthchecks.io';

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
        $tests      = 0;
        $totalTests = 3;
        $failed     = [];

        $url = env('HEALTHCHECK_URL');

        // Check to make sure Laravel appears to be loaded correctly
        $laravelVersion = \App::version();
        if(substr($laravelVersion, 0, 1) === "7") {
            $tests++;
        } else {
            $failed[] = 'Laravel Version Check';
        }

        // Check if MariaDB connection appears to be up
        try {
            $mariaTest = \DB::table('users')
                            ->where('email', 'brad@clanofartisans.com')
                            ->first();
            if($mariaTest->name === 'Brad Turner') {
                $tests++;
            } else {
                $failed[] = 'MariaDB Connection';
            }
        } catch (\Exception $e) {
            $failed[] = 'MariaDB Connection';
            report($e);
        }

        // Check if point of sale connection appears to be up
        try {
            $posTest = \POS::quickQuery('0');
            if(isset($posTest['desc']) && $posTest['desc'] === 'NOT AN ITEM ') {
                $tests++;
            } else {
                $failed[] = 'Point of Sale System Connection';
            }
        } catch (\Exception $e) {
            $failed[] = 'Point of Sale System Connection';
            report($e);
        }

        // Output results to console in case we're running tests manually
        $this->info($tests . '/' . $totalTests . ' tests passed.');

        // Ping HealthChecks.io on all green, or log a warning if something failed
        if($tests === $totalTests) {
            file_get_contents($url);
        } else {
            $message  = ($totalTests - $tests) . ' health check tests have failed.';

            foreach($failed as $failure) {
                $message .= "\n" . '> ' . $failure;
            }

            \Log::warning($message);
        }
    }
}
