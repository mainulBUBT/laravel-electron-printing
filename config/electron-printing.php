<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Electron Print Service Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the Electron printing service. When disabled, all
    | print functions will return false without attempting to connect.
    |
    */

    'enabled' => env('PRINT_SERVICE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Print Service URL
    |--------------------------------------------------------------------------
    |
    | The URL of your Electron print service. This should include the protocol
    | (http/https), IP address or hostname, and port number.
    |
    | Example: http://192.168.1.100:3000
    |
    */

    'service_url' => env('PRINT_SERVICE_URL', 'http://localhost:3000'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time (in seconds) to wait for the print service to respond.
    | Increase this for large documents or slow networks.
    |
    */

    'timeout' => env('PRINT_SERVICE_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Maximum Payload Size
    |--------------------------------------------------------------------------
    |
    | Maximum size (in MB) for print payloads. Increase this for large PDFs
    | or high-resolution images. The Electron service will read this value.
    |
    */

    'max_payload_size' => env('PRINT_MAX_PAYLOAD_SIZE', 50),

    /*
    |--------------------------------------------------------------------------
    | Use WebSocket Broadcasting
    |--------------------------------------------------------------------------
    |
    | Enable WebSocket broadcasting for real-time printing. When enabled,
    | print jobs will be broadcast via Laravel's broadcasting system.
    | Requires Laravel WebSockets or Reverb to be configured.
    |
    */

    'use_websocket' => env('PRINT_USE_WEBSOCKET', false),

    /*
    |--------------------------------------------------------------------------
    | Broadcasting Channel
    |--------------------------------------------------------------------------
    |
    | The channel name used for broadcasting print jobs. The Electron service
    | must be configured to listen to the same channel.
    |
    */

    'broadcast_channel' => env('PRINT_BROADCAST_CHANNEL', 'printing'),

    /*
    |--------------------------------------------------------------------------
    | Default Printer
    |--------------------------------------------------------------------------
    |
    | The default printer name to use when no printer is specified.
    | Leave empty to use the system default printer.
    |
    */

    'default_printer' => env('PRINT_DEFAULT_PRINTER', null),

    /*
    |--------------------------------------------------------------------------
    | Default Print Options
    |--------------------------------------------------------------------------
    |
    | Default options for all print jobs. These can be overridden per job.
    |
    */

    'default_options' => [
        'silent' => true,
        'printBackground' => true,
        'pageSize' => 'A4',
        'marginsType' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Print Profiles
    |--------------------------------------------------------------------------
    |
    | Pre-configured print profiles for different printer types.
    | Use these profiles by name instead of passing options every time.
    |
    */

    'profiles' => [
        'thermal_80mm' => [
            'pageSize' => ['width' => 80000, 'height' => 297000],
            'marginsType' => 1,
            'printBackground' => true,
            'preferCSSPageSize' => true,
            'scaleFactor' => 100,
        ],
        'thermal_58mm' => [
            'pageSize' => ['width' => 58000, 'height' => 297000],
            'marginsType' => 1,
            'printBackground' => true,
            'preferCSSPageSize' => true,
            'scaleFactor' => 100,
        ],
        'a4' => [
            'pageSize' => 'A4',
            'marginsType' => 0,
            'printBackground' => true,
            'landscape' => false,
        ],
        'a4_landscape' => [
            'pageSize' => 'A4',
            'marginsType' => 0,
            'printBackground' => true,
            'landscape' => true,
        ],
        'letter' => [
            'pageSize' => 'Letter',
            'marginsType' => 0,
            'printBackground' => true,
        ],
        'label' => [
            'pageSize' => ['width' => 100000, 'height' => 50000],
            'marginsType' => 1,
            'printBackground' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automatic retry behavior for failed print jobs.
    |
    */

    'retry' => [
        'enabled' => env('PRINT_RETRY_ENABLED', true),
        'max_attempts' => env('PRINT_RETRY_MAX_ATTEMPTS', 3),
        'delay' => env('PRINT_RETRY_DELAY', 5), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable logging of print jobs and errors.
    |
    */

    'logging' => [
        'enabled' => env('PRINT_LOGGING_ENABLED', true),
        'channel' => env('PRINT_LOG_CHANNEL', 'daily'),
    ],

];
