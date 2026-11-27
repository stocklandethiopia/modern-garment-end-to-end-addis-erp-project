<?php

$pgsql = [
    'driver' => 'pgsql',
    'url' => env('DB_URL'),
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'garment_erp'),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => env('DB_SEARCH_PATH', 'public'),
    'sslmode' => env('DB_SSLMODE', 'prefer'),
];

return [
    'default' => env('DB_CONNECTION', 'pgsql'),
    'connections' => [
        'pgsql' => array_merge($pgsql, [
            'username' => env('DB_USERNAME', 'app_runtime'),
            'password' => env('DB_PASSWORD', ''),
        ]),
        'pgsql_migrator' => array_merge($pgsql, [
            'username' => env('MIGRATION_DB_USERNAME', 'erp_migrator'),
            'password' => env('MIGRATION_DB_PASSWORD', ''),
        ]),
        // A separate, narrowly privileged connection for SECURITY DEFINER APIs.
        // app_runtime deliberately cannot execute identity-sensitive mutation functions.
        'pgsql_control' => array_merge($pgsql, [
            // Do not inherit DB_URL credentials: the normal app URL may contain app_runtime.
            'url' => env('CONTROL_DB_URL'),
            'username' => env('CONTROL_DB_USERNAME', 'app_control'),
            'password' => env('CONTROL_DB_PASSWORD', ''),
        ]),
    ],
    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],
    'redis' => [
        'client' => env('REDIS_CLIENT', 'predis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', 'garment_erp_database_'),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', 'localhost'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', 'localhost'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
];
