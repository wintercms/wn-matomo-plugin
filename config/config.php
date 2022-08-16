<?php return [

    /*
    |--------------------------------------------------------------------------
    | Matomo Instance URL
    |--------------------------------------------------------------------------
    |
    | This is the URL to the Matomo instance that hosts your data.
    |
    | Example: https://matomo.org/
    |
    */

    'server' => env('MATOMO_SERVER', 'https://example.matomo.cloud/'),

    /*
    |--------------------------------------------------------------------------
    | Matomo Site ID
    |--------------------------------------------------------------------------
    |
    | This is the Site ID for the Matomo site that corresponds to this Winter
    | instance.
    |
    */

    'site_id' => env('MATOMO_SITE_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | Matomo Auth Token
    |--------------------------------------------------------------------------
    |
    | This is the non-sudo auth token that has access to the necessary
    | Matomo functionality that you wish to interact with using this plugin
    |
    */

    'auth_token' => env('MATOMO_TOKEN'),
    
    /*
    |--------------------------------------------------------------------------
    | Reporting API Cache TTL
    |--------------------------------------------------------------------------
    |
    | This is the length of time (in seconds) that requests made through
    | the ReportingAPI should be cached for. Since the cache key includes the
    | full request URL and those values determine the data present, all data
    | should be remembered forever since for a given generated cacheKey the
    | data will always be the same. However, in the interest of not having
    | our cache directory grow indefinitely we'll keep it around for only
    | two days because most of the time the end date is the present day
    |
    */

    'reportingapi_cache_ttl' => 60 * 60 * 24 * 2,

    /*
    |--------------------------------------------------------------------------
    | Track Backend Users
    |--------------------------------------------------------------------------
    |
    | If enabled this will include the tracking logic even when Backend Users
    | are the ones making the request. It is recommended to leave it disabled
    | to avoid polluting your analytics data with internal traffic
    |
    */

    'track_backend_users' => false,

    /*
    |--------------------------------------------------------------------------
    | Track in Maintenance Mode
    |--------------------------------------------------------------------------
    |
    | If enabled this will include the tracking logic even when maintenance
    | mode (either hard with `artisan down` or soft through the backend) is
    | enabled.
    |
    */

    'track_maintenance_mode' => false,

];
