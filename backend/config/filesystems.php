<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    // Tenant-private ERP attachments use the private local disk for clean
    // development installs. Production must set PRIVATE_DOCUMENTS_DISK to an
    // approved private object-storage provider during installer preflight.
    'private_documents_disk' => env('PRIVATE_DOCUMENTS_DISK', 'local'),
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false,
            'throw' => false,
            'report' => false,
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'https://api.example.invalid'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
    ],
    'links' => [public_path('storage') => storage_path('app/public')],
];
