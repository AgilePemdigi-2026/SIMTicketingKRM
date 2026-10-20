<?php

return [
    'local_enabled' => env('TICKET_LOCAL_ENABLED', false),
    'passwords' => [
        'admin' => env('LOCAL_ADMIN_PASSWORD'),
        'wisatawan' => env('LOCAL_WISATAWAN_PASSWORD'),
        'petugas' => env('LOCAL_PETUGAS_PASSWORD'),
    ],
];
