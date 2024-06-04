<?php

return [
    'google_wallet' => [
        'auth_file_path' => env('GOOGLE_WALLET_APPLICATION_CREDENTIALS', ''),
        'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID', ''),
    ],
    'apple_wallet' => [
        // Configuration spécifique pour Apple Wallet
    ],
];
