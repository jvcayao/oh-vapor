<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AWS Credentials
    |--------------------------------------------------------------------------
    |
    | AWS credentials with WAF permissions.
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
    | Oh Dear
    |--------------------------------------------------------------------------
    |
    | Oh Dear settings including API key, list of site id's to
    | put into a maintenance window and for how long the
    | window should last if not closed.
    |
    */
    'oh-dear' => [
        'api-key' => env('OH_DEAR_API_KEY')
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