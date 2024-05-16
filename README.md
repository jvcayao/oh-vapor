# OhVapor!

This package provides support for the use case of [Laravel Vapor](https://vapor.laravel.com/) with the managed firewall (AWS WAF) enabled and the [Oh Dear](https://ohdear.app/) monitoring service.


## Why?

When using OhDear to monitor your Laravel Vapor application if a firewall is enabled Vapor the OhDear is likely going to be blocked by the AWS WAF as automated traffic (which it is ;)).

Usually this would just mean whitelisting Oh Dear's IP addresses in the AWS WAF however in this case that will only work until your next `vapor deploy` at which point the firewall is completely reset removing the whitelisted IP's and OhDear will be blocked again.

## Installation

    composer require douglasthwaites/oh-vapor

## Configuration
AWS access credentials with full WAF access only and your Oh Dear API key

    WAF_AWS_ACCESS_KEY_ID=very
    WAF_AWS_SECRET_ACCESS_KEY=secret
    WAF_AWS_DEFAULT_REGION=stuff
    OH_DEAR_API_KEY=here


There is also a publishable config file if you wish.

## How does it work

To get around this limitation of two fantastic tools OhVapor has two commands, once which sets a maintenance period in Oh Dear and then another which re configures the AWS firewall to allow Oh Dear's IP's through.

### Start maintenance command
Create a maintenance window in OhDear for x many seconds on y many site ID's:

    php artisan oh-vapor:start-maintenance seconds siteId

### Update WAF command
When reconfiguring the firewall OhVapor will reach out to get the [lastest list of Oh Dear IP's](https://ohdear.app/docs/faq/what-ips-does-oh-dear-monitor-from) and then create an IP set which is then applied to the WAF as a scope down statement i.e let em through ;)

It'll also use/reapply the current list of firewall rules in your vapor.yml

    php artisan oh-vapor:update-waf environmentName

### Where to call these command?
These commands can be called anytime but putting them in your CI/CD makes the most sense.
    
    # Put site Oh Dear site ID 12345 into maintenance mode for 5 minutes
    php artisan oh-vapor:start-maintenance 300 12345
    
    # Deploy to Vapor
    vapor deploy production
    
    # Allow Oh Dear through the firewall
    php artisan oh-vapor:update-waf production
    
As long as the vapor deploy is quicker than your maintenance window you can dial it in till your hearts content.
    
Keep on keeping on!
