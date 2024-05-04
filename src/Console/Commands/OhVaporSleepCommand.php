<?php

namespace DouglasThwaites\OhVapor\Console\Commands;

use Aws\Credentials\Credentials;
use Aws\Result;
use Aws\WAFV2\WAFV2Client;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use OhDear\PhpSdk\OhDear;
use Symfony\Component\Yaml\Yaml;
use function Laravel\Prompts\spin;

class OhVaporSleepCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oh-vapor:start-maintenance 
                    {window? : Maintenance window in seconds }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Puts your Oh Dear site into a short maintenance window.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = config('oh-vapor.oh-dear.api-key');
        $sites = config('oh-vapor.oh-dear.sites');
        $defaultWindow = config('oh-vapor.oh-dear.maintenance');

        $window = (int) $this->argument('window') ?? $defaultWindow;

        dd($defaultWindow, $this->argument('window'), $window);

        $ohDear = new OhDear($apiKey);

        foreach($sites as $siteId)
        {
            $site = $ohDear->site($siteId);
            $period = $ohDear->startSiteMaintenance($siteId, $window);

            $this->info('Site ' . $site->url . ' is in a maintenance period until ' . $period->endsAt);
        }
    }
}
