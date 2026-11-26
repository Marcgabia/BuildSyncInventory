<?php

/*
|--------------------------------------------------------------------------
| Integration settings (team standard B6 / B7)
|--------------------------------------------------------------------------
|
| Reads every integration variable from .env. Use it like:
|   config('integration.system_code')        // "INV"
|   config('integration.enabled')            // false until Yugto 2
|   config('integration.api_key')            // our own key (checks X-API-KEY)
|   config('integration.systems.PROC.url')   // base URL, ends in /api/integration/v1
|   config('integration.systems.PROC.key')   // PROC's INTEGRATION_API_KEY
|
*/

return [

    'system_code' => env('SYSTEM_CODE', 'INV'),

    'enabled' => (bool) env('INTEGRATION_ENABLED', false),

    'api_key' => env('INTEGRATION_API_KEY'),

    'version' => 'v1',

    'systems' => [
        'CRM' => [
            'url' => env('CRM_API_URL'),
            'key' => env('CRM_API_KEY'),
        ],
        'PROC' => [
            'url' => env('PROC_API_URL'),
            'key' => env('PROC_API_KEY'),
        ],
        'INV' => [
            'url' => env('INV_API_URL'),
            'key' => env('INV_API_KEY'),
        ],
        'HR' => [
            'url' => env('HR_API_URL'),
            'key' => env('HR_API_KEY'),
        ],
    ],

];
