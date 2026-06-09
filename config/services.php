<?php

// config/services.php
// Tambahkan key 'gemini' ke array yang sudah ada.
// Jangan replace seluruh file — merge dengan isi existing.

return [

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // ─────────────────────────────────────────────────────────
    // TAMBAHKAN BLOK INI ke config/services.php
    // ─────────────────────────────────────────────────────────
    'gemini' => [
        'key'       => env('GEMINI_API_KEY'),
        'model'     => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'url'       => 'https://generativelanguage.googleapis.com/v1beta/models/',
        'tolerance' => (int) env('VERIFICATION_TOLERANCE_PCT', 5),
    ],

];
