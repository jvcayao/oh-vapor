<?php

namespace DouglasThwaites\OhVapor\Console\Commands;

use Aws\Result;
use Aws\WAFV2\WAFV2Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use function Laravel\Prompts\note;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\error;
use function Laravel\Prompts\alert;

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
        // Preform checks
        note('note');
        info('info');
        warning('warning');
        error('error');
        alert('alert');

        // Look for the Vapor firewall rule

        // Error out if no Vapor firewall rule exists

        // Pull in updated list of OhDear IP addresses adding a single subnet IP range of /32 to each

        // Check if IP set exists

        // Create IP set if missing

        // Update existing IP set

        // Get all the WebACLs

        // Find the Vapor WebACL

        // Generate a modified WebACL with the IP set applied

            // Extract an existing rate limit rule if any

            // Extract the current bot controls if any

            // Modify the bot control rule

        // Update the WebACL with the modified version




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
            'Id' => 'xxxxxxxxx',
            'Name' => 'OhVaporIpSet',
            'Scope' => 'REGIONAL'
        ]);

        $acl = $waf->getWebACL([
            'Id' => 'xxxxxxxxxxxx',
            'Name' => 'vapor-firewall-xxxxxxxxx',
            'Scope' => 'REGIONAL'
        ]);

        $webAcl = $this->generateModifiedWebAcl($acl);

        $result = $waf->updateWebACL($webAcl);

        dd($result);
    }

    private function generateModifiedWebAcl(Result $vaporRule)
    {
        $acl = [
            "Scope" => 'REGIONAL',
            "LockToken" => $vaporRule['LockToken'],
            'Description' => 'Test description',
            "Id" => "xxxxxxxxxxxx",
            "ARN" => $vaporRule['WebACL']['ARN'],
            "DefaultAction" => [
                "Allow" => [
                ]
            ],
            "LabelNamespace" => $vaporRule['WebACL']['LabelNamespace'],
            "ManagedByFirewallManager" => false,
            "Name" => $vaporRule['WebACL']['Name'],
            "Rules" => [
                [
                    "Action" => [
                        "Block" => [
                        ]
                    ],
                    "Name" => "vapor-firewall-xxxxxxx-rate-limit-rule",
                    "Priority" => 0,
                    "Statement" => [
                        "RateBasedStatement" => [
                            "AggregateKeyType" => "IP",
                            "EvaluationWindowSec" => 300,
                            "Limit" => 1000
                        ]
                    ],
                    "VisibilityConfig" => [
                        "CloudWatchMetricsEnabled" => true,
                        "MetricName" => "vapor-firewall-xxxxxxxxx-rate-limit",
                        "SampledRequestsEnabled" => false
                    ]
                ],
                [
                    "Name" => "vapor-firewall-xxxxxxxxx-bot-control-rule",
                    "OverrideAction" => [
                        "None" => [
                        ]
                    ],
                    "Priority" => 1,
                    "Statement" => [
                        "ManagedRuleGroupStatement" => [
                            "ManagedRuleGroupConfigs" => [
                                [
                                    "AWSManagedRulesBotControlRuleSet" => [
                                        "InspectionLevel" => "COMMON"
                                    ]
                                ]
                            ],
                            "Name" => "AWSManagedRulesBotControlRuleSet",
                            "RuleActionOverrides" => [
                                [
                                    "ActionToUse" => [
                                        "Count" => [
                                        ]
                                    ],
                                    "Name" => "CategoryAdvertising"
                                ],
                                [
                                    "ActionToUse" => [
                                        "Count" => [
                                        ]
                                    ],
                                    "Name" => "CategoryArchiver"
                                ],
                                [
                                    "ActionToUse" => [
                                        "Count" => [
                                        ]
                                    ],
                                    "Name" => "CategoryContentFetcher"
                                ],
                                [
                                    "ActionToUse" => [
                                        "Count" => [
                                        ]
                                    ],
                                    "Name" => "CategoryHttpLibrary"
                                ],
                                [
                                    "ActionToUse" => [
                                        "Count" => [
                                        ]
                                    ],
                                    "Name" => "CategoryLinkChecker"
                                ],
                                [
                                    "ActionToUse" => [
                                        "Count" => [
                                        ]
                                    ],
                                    "Name" => "CategoryMiscellaneous"
                                ],
                                [
                                    "ActionToUse" => [
                                        "Count" => [
                                        ]
                                    ],
                                    "Name" => "CategoryMonitoring"
                                ],
                                [
                                    "ActionToUse" => [
                                        "Count" => [
                                        ]
                                    ],
                                    "Name" => "CategorySeo"
                                ],
                                [
                                    "ActionToUse" => [
                                        "Count" => [
                                        ]
                                    ],
                                    "Name" => "CategorySocialMedia"
                                ],
                                [
                                    "ActionToUse" => [
                                        "Count" => [
                                        ]
                                    ],
                                    "Name" => "CategorySearchEngine"
                                ]
                            ],
                            "ScopeDownStatement" => [
                                "NotStatement" => [
                                    "Statement" => [
                                        "IPSetReferenceStatement" => [
                                            "ARN" => "xxxxxxxx"
                                        ]
                                    ]
                                ]
                            ],
                            "VendorName" => "AWS"
                        ]
                    ],
                    "VisibilityConfig" => [
                        "CloudWatchMetricsEnabled" => true,
                        "MetricName" => "vapor-firewall-xxxxxxx-bot-control",
                        "SampledRequestsEnabled" => false
                    ]
                ]
            ],
            "VisibilityConfig" => [
                "CloudWatchMetricsEnabled" => true,
                "MetricName" => "vapor-firewall-xxxxxxxxxx",
                "SampledRequestsEnabled" => false
            ]
        ];

        return $acl;
    }
}
