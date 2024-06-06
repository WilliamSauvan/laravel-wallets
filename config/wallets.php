<?php

return [
    'google_wallet' => [
        'auth_file_path' => env('GOOGLE_WALLET_APPLICATION_CREDENTIALS', ''),
        'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID', ''),
    ],
    'apple_wallet' => [
        'certificates_file_path' => env('APPLE_WALLET_CERTIFICATES_FILE_PATH'),
        'certificates_password' => env('APPLE_WALLET_CERTIFICATES_PASSWORD'),
        'pass_identifier' => env('APPLE_WALLET_PASS_IDENTIFIER'),
        'team_identifier' => env('APPLE_WALLET_TEAM_IDENTIFIER'),
    ],
];
