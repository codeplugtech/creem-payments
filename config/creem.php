<?php

return [
    'api_key' => env('CREEM_API_KEY'),
    'sandbox' => env('CREEM_SANDBOX', false),
    'currency' => env('CREEM_CURRENCY', 'USD'),
    'currency_locale' => env('CREEM_CURRENCY_LOCALE', 'en'),
    'webhook_secret' => env('CREEM_WEBHOOK_SECRET', ''),
    'path' => env('CREEM_PATH', 'creem'),
    'user_model' => App\Models\User::class,
];
