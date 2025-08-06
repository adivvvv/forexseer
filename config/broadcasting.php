<?php
// config/broadcasting.php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcast Driver
    |--------------------------------------------------------------------------
    |
    | This controls the default broadcaster that will be used by the framework
    | when an event implements the ShouldBroadcast interface. You may set
    | this to "redis", "pusher", "log", or "null".
    |
    */

    'default' => env('BROADCAST_DRIVER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the broadcast connections that are set up for your
    | application. You are free to add your own custom connections.
    |
    */

    'connections' => [

        'pusher' => [
            'driver'   => 'pusher',
            'key'      => env('PUSHER_APP_KEY', 'local'),
            'secret'   => env('PUSHER_APP_SECRET', 'local'),
            'app_id'   => env('PUSHER_APP_ID', 'local'),
            'options'  => [
                'cluster'        => env('PUSHER_APP_CLUSTER', 'mt1'),
                'useTLS'         => false,
                'encrypted'      => false,
                'host'           => env('PUSHER_HOST', 'forexseer.test'),
                'port'           => env('PUSHER_PORT', 6001),
                'scheme'         => env('PUSHER_SCHEME', 'http'),
                'path'           => env('PUSHER_PATH', '/socket.io'),
                'curl_options'   => [
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                ],
            ],
        ],

        'redis' => [
            'driver'     => 'redis',
            // this matches the 'default' Redis connection in config/database.php
            'connection' => env('BROADCAST_REDIS_CONNECTION', 'default'),
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];