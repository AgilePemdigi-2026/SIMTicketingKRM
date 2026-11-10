<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Payment Gateway Configuration
    |--------------------------------------------------------------------------
    */

    // Server Key dari file .env
    'server_key' => env('MIDTRANS_SERVER_KEY'),

    // Client Key untuk frontend
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    // Signature Secret Key (opsional, jika berbeda dari Server Key)
    'signature_key' => env('MIDTRANS_SIGNATURE_KEY') ?: env('MIDTRANS_SIGNATURE_SECRET_KEY'),

    // False = Sandbox, True = Production
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // Endpoint Core API Midtrans
    'sandbox_url' => 'https://api.sandbox.midtrans.com',

    'production_url' => 'https://api.midtrans.com',

    // Batas waktu koneksi API
    'timeout' => 15,

];
