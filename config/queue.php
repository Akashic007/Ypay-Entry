<?php

return [
    'default'     => 'redis',
    'connections' => [
        'sync'     => [
            'type' => 'sync',
        ],
        'database' => [
            'type'       => 'database',
            'queue'      => 'default',
            'table'      => 'jobs',
            'connection' => null,
        ],
        'redis'    => [
            'type'       => 'redis',
            'queue'      => 'default',
            'host'       => env('redis.host', '127.0.0.1'),
            'port'       => env('redis.port', '6379'),
            'password'   => env('redis.password', ''),
            'select'     => env('redis.queue_select', 3),
            'timeout'    => 0,
            'persistent' => true,
            'prefix' => env('redis.name', 'entry:').'queue:',
        ],
    ],

    'failed'      => [
        'type'  => 'none',
        'table' => 'failed_jobs',
    ],
];
