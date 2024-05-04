<?php

namespace DouglasThwaites\OhVapor\Console\Commands;

use Aws\Credentials\Credentials;
use Aws\Result;
use Aws\WAFV2\WAFV2Client;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;
use function Laravel\Prompts\spin;

class OhVaporWafUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oh-vapor:update-waf
                {environment : THe environment you would like to update the WAF for.}
    ';

    /**
     * Most recent lock token from AWS
     *
     * @var string
     */
    protected string $webAclLockToken;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the AWS WAF provisioned by Vapor to allow OhDear to monitor your site.';

    /**
     * The AWS WafV2 client
     *
     * @var WAFV2Client
     */
    private WAFV2Client $client;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $environment = $this->argument('environment');
        $webAclDescription = config('oh-vapor.webACLs.description');

        // Get the Vapor configuration
        $firewallConfig = $this->getVaporFirewallConfig($environment);

        // Get the WebACL with an API gateway for the current environment
        $webACL = $this->getWebACL($environment);

        // Skip if the WAF is already modified
        if($webACL['Description'] == $webAclDescription)
        {
            $this->exitAlert('WebACL already modified for ' . $environment);
        }


        // Check if IP set exists
        $this->upsertWhitelistedIpSet();

        // Create modified webACL
        $modifiedWebACL = $webACL;

        // Remove rules from modified webACL
        $modifiedWebACL['Rules'] = [];

        // Add OhVapor IP set to rate limit rule
        if(isset($firewallConfig['rate-limit']))
        {
            $modifiedWebACL['Rules'][] = $this->getModifiedRateLimitRule($webACL);
        }

        // Add OhVapor IP set to bot control rule
        if(isset($firewallConfig['bot-control']))
        {
            $modifiedWebACL['Rules'][] = $this->getModifiedBotControlRule($webACL);
        }

        // Update the webACL
        $this->getWafV2Client()->updateWebACL([
            ...$modifiedWebACL,
            'LockToken' => $this->webAclLockToken,
            'Scope' => config('oh-vapor.webACLs.scope'),
            'Description' => config('oh-vapor.webACLs.description')
        ]);

        $this->info('Updated Vapor/AWS firewall with OhDear monitoring IP addresses.');
    }

    /*
     * Get the Vapor configuration for
     * the current environment
     */
    private function getVaporFirewallConfig(string $environment) : array
    {
        $path = base_path('vapor.yml');
        $contents = File::get($path);
        $config = Yaml::parse($contents);

        // Fail if there is no Vapor configuration for this environment
        if(!isset($config['environments'][$environment]))
        {
            $this->exitAlert('Matching environment not found.');
        }

        // Fail if there is no Vapor firewall configured
        if(!isset($config['environments'][$environment]['firewall']))
        {
            $this->exitAlert('Firewall configuration not found.');
        }

        return $config['environments'][$environment]['firewall'];
    }

    /**
     * Find the environments related WebACL
     * by comparing API Gateway resources
     */
    private function getWebACL(string $environment) : bool|array
    {
        // Get all webACLs
        $response = $this->getWafV2Client()->listWebACLs([
            'Scope' => config('oh-vapor.webACLs.scope')
        ]);

        // Match environment to API gateway ARN
        foreach($response['WebACLs'] as $webACL)
        {
            $apiGateway = $this->getApiGateway($webACL['ARN']);

            $stage = Str::of($apiGateway['ResourceArns'][0])->explode('/')->last();

            if($environment != $stage) continue;

            // Retrieve the full webACL
            $webACL = $this->getWafV2Client()->getWebACL([
                'Scope' => 'REGIONAL',
                ...$webACL
            ]);

            // Update the WebACL LockToken
            $this->webAclLockToken = $webACL->get('LockToken');

            return $webACL->get('WebACL');
        }

        $this->exitAlert('Could not match webACL to environment');

        return false;
    }

    private function getWafV2Client()
    {
        $credentials = new Credentials(
            config('oh-vapor.aws.key'),
            config('oh-vapor.aws.secret')
        );

        return new WAFV2Client([
            'region' => config('oh-vapor.region'),
            'credentials' => $credentials
        ]);
    }

    private function getApiGateway(string $arn)
    {
        return $this->getWafV2Client()->listResourcesForWebACL([
            'ResourceType' => 'API_GATEWAY',
            'WebACLArn' => $arn
        ]);
    }

    private function exitAlert(string $string)
    {
        $this->alert($string);
        exit();
    }

    private function upsertWhitelistedIpSet()
    {
        // Pull in updated list of OhDear IP addresses adding a single IP range to each
        $ohDearIps = $this->getMonitoringIpsFromOhDear();

        // Get the OhVaporIpSet
        $ohVaporIpSet = $this->getOhVaporIpSet();

        // Create an empty IP set if missing
        if(empty($ohVaporIpSet)) $ohVaporIpSet = $this->createOhVaporIpSet()['Summary'];

        // Update the OhVapor IP set
        return $this->getWafV2Client()->updateIPSet([
            'Addresses' => $ohDearIps,
            'Scope' => 'REGIONAL',
            ...$ohVaporIpSet
        ]);
    }

    private function createOhVaporIpSet()
    {
        return $this->getWafV2Client()->createIPSet([
            'Name' => config('oh-vapor.ip-set.name'),
            'Description' => config('oh-vapor.ip-set.name'),
            'Scope' => 'REGIONAL',
            'IPAddressVersion' => 'IPV4',
            'Addresses' => []
        ]);
    }

    private function getMonitoringIpsFromOhDear() : array
    {
        $data = Http::get('https://ohdear.app/used-ips.json')->json();

        return collect($data)->map(function (array $item) {

            return $item['ipv4'] . '/32';

        })->toArray();
    }

    private function getModifiedRateLimitRule(array $webACL, string $environment) : array
    {
        // Get firewall config
        $config = $this->getVaporFirewallConfig($environment);

        // Get the firewall rule
        $rule = $this->getFirewallRule(
            $webACL,
            $webACL['Name'] . '-rate-limit-rule'
        );

        // Generate the modification
        $modification = [
            'AggregateKeyType' => 'IP',
            'Limit' => $config['rate-limit'],
            'ScopeDownStatement' => [
                'NotStatement' => [
                    'Statement' => [
                        'IPSetReferenceStatement' => [
                            'ARN' => $this->getOhVaporIpSet()['ARN']
                        ]
                    ]
                ]
            ]
        ];

        // Add the scope down statement to the RateBasedStatement
        return [
            ...$rule,
            'Statement' => [
                'RateBasedStatement' => $modification
            ]
        ];
    }

    private function getModifiedBotControlRule(array $webACL, string $environment) : array
    {
        // Get firewall config
        $config = $this->getVaporFirewallConfig($environment);

        // Get bot control rules
        $controls = $config['bot-control'];

        // Get the firewall rule
        $rule = $this->getFirewallRule(
            $webACL,
            $webACL['Name'] . '-bot-control-rule'
        );

        $ruleActionOverrides = [];

        // Generate action overrides
        foreach ($rule['Statement']['ManagedRuleGroupStatement']['ExcludedRules'] as $excludedRule)
        {
            $ruleActionOverrides[] = [
                'Name' => $excludedRule['Name'],
                'ActionToUse' => [
                    'Count' => []
                ]
            ];
        }

        // Replace existing statement
        $rule['Statement'] = [
            'ManagedRuleGroupStatement' => [
                'ManagedRuleGroupConfigs' => [[
                    'AWSManagedRulesBotControlRuleSet' => [
                        'InspectionLevel' => 'COMMON'
                    ]
                ]],
                'Name' => 'AWSManagedRulesBotControlRuleSet',
                'RuleActionOverrides' => $ruleActionOverrides,
                'ScopeDownStatement' => [
                    'NotStatement' => [
                        'Statement' => [
                            'IPSetReferenceStatement' => [
                                'ARN' => $this->getOhVaporIpSet()['ARN']
                            ]
                        ]
                    ]
                ],
                'VendorName' => 'AWS'
            ],

        ];

        return $rule;
    }

    private function getFirewallRule(array $webACL, string $name)
    {
        // Pull out and return the matching rule
        return collect($webACL['Rules'])->firstWhere('Name', $name);
    }

    private function getOhVaporIpSet()
    {
        // Get all IP sets
        $ipSets = $this->getWafV2Client()->listIPSets([
            'Scope' => 'REGIONAL'
        ]);

        // Get the OhVapor IP set
        return collect($ipSets->get('IPSets'))->firstWhere('Name', config('oh-vapor.ip-set.name'));
    }
}
