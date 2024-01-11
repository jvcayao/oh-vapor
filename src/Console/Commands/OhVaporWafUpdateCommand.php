<?php

namespace DouglasThwaites\OhVapor\Console\Commands;

use Aws\AwsClient;
use Aws\Sdk;
use Aws\WAFV2\WAFV2Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class OhVaporWafUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oh-vapor:waf:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the AWS WAF provisioned by Vapor to allow OhDear to monitor your site.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Look for the Vapor firewall rule

        // Error out if no Vapor firewall rule exists

        // Pull in updated list of OhDear IP addresses adding a single subnet IP range of /32 to each

        // Check if IP set exists

        // Create IP set if missing

        // Update existing IP set

        // Add a scope down rule for the OhDear IP set


        $ips = Http::get('https://ohdear.app/used-ips.json')->json();

        $waf = new WAFV2Client([
            'region' => 'us-east-1'
        ]);

        $acls = $waf->listWebACLs([
            'Scope' => 'REGIONAL'
        ]);

        $ipSets = $waf->listIPSets([
            'Scope' => 'REGIONAL'
        ]);

        $ipSet = $waf->getIPSet([
            
        ]);

        $acl = $waf->getWebACL([
            
        ]);

        $ruleGroup = $waf->updateWebACL();

        dd($ruleGroup);
    }
}
