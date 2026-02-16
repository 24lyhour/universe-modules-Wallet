<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Wallet ID Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the format for auto-generated wallet IDs.
    | Format: {prefix}{padded_number}
    | Example: W00000001
    |
    */
    'wallet_id' => [
        'prefix' => env('WALLET_ID_PREFIX', 'W'),
        'padding' => env('WALLET_ID_PADDING', 8),
        'pad_string' => '0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Wallet Number Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the format for auto-generated wallet numbers.
    | Format: {prefix}-{date_format}-{random_string}
    | Example: WLT-20260216-A3B5C
    |
    */
    'wallet_number' => [
        'prefix' => env('WALLET_NUMBER_PREFIX', 'WLT'),
        'separator' => '-',
        'date_format' => env('WALLET_NUMBER_DATE_FORMAT', 'Ymd'),
        'random_length' => env('WALLET_NUMBER_RANDOM_LENGTH', 5),
        'random_uppercase' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for new wallets.
    |
    */
    'default_currency' => env('WALLET_DEFAULT_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | List of supported currencies for wallets.
    |
    */
    'supported_currencies' => [
        'USD', 'EUR', 'GBP', 'JPY', 'CNY', 'KHR',
    ],

    /*
    |--------------------------------------------------------------------------
    | Balance Precision
    |--------------------------------------------------------------------------
    |
    | Number of decimal places for wallet balances.
    |
    */
    'balance_precision' => 2,
];
