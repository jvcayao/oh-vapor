<?php

return [

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