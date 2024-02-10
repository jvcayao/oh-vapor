<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AWS Credentials
    |--------------------------------------------------------------------------
    |
    | AWS credentials with WAF permissions
    |
    |
    */
    'aws' => [
        'key' => env('WAF_AWS_ACCESS_KEY_ID'),
        'secret' => env('WAF_AWS_SECRET_ACCESS_KEY'),
        'region' => env('WAF_AWS_DEFAULT_REGION', 'us-east-1')
    ],

    /*
    |--------------------------------------------------------------------------
    | WebACLs
    |--------------------------------------------------------------------------
    |
    | General WebACL config values
    |
    |
    */
    'webACLs' => [
        'scope' => 'REGIONAL',
        'description' => 'OhVapor modified WebACL'
    ],

    /*
    |--------------------------------------------------------------------------
    | Region
    |--------------------------------------------------------------------------
    |
    | Default AWS region
    |
    |
    */
    'region' => 'us-east-1',

    /*
    |--------------------------------------------------------------------------
    | IP Set
    |--------------------------------------------------------------------------
    |
    | Information related to AWS IP set
    | for OhDear whitelisted IP's

    |
    */
    'ip-set' => [
        'name' => 'OhVaporIpSet',
        'description' => 'Oh Dear IPV4 monitoring whitelist.'
    ]

];