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

];