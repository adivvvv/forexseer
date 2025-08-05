<?php

return [

    'default' => env('BROADCAST_DRIVER', 'null'),

    'connections' => [

        'pusher' => [
            'driver'   => 'pusher',
            'key'      => env('PUSHER_APP_KEY'),
            'secret'   => env('PUSHER_APP_SECRET'),
            'app_id'   => env('PUSHER_APP_ID'),
            'options'  => [
                'cluster'        => env('PUSHER_APP_CLUSTER'),
                'useTLS'         => false,
                'encrypted'      => false,
                'host'           => env('PUSHER_HOST'),
                'port'           => env('PUSHER_PORT'),
                'scheme'         => env('PUSHER_SCHEME'),
                'curl_options'   => [
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                ],
            ],
        ],

        'redis' => [
            'driver'     => 'redis',
            // this must match one of the Redis connections in config/database.php
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