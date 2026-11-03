<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Payment Gateway Configuration
    |--------------------------------------------------------------------------
    */

    // Server Key dari file .env
    'server_key' => env('MIDTRANS_SERVER_KEY'),

    // False = Sandbox, True = Production
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // Endpoint Core API Midtrans
    'sandbox_url' => 'https://api.sandbox.midtrans.com',

    'production_url' => 'https://api.midtrans.com',

    // Batas waktu koneksi API
    'timeout' => 15,

];
