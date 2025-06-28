<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Axepta Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for BNP Paribas Axepta payment
    | gateway integration.
    |
    */

    'api_url' => env('AXEPTA_API_URL', 'https://paymentpage.axepta.bnpparibas/paymentpage.aspx'),

    'merchant_id' => env('AXEPTA_MERCHANT_ID'),

    'blowfish_key' => env('AXEPTA_BLOWFISH_KEY'),

    'hmac_key' => env('AXEPTA_HMAC_KEY'),

    'test_mode' => env('AXEPTA_TEST_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency to use for payments if none is specified.
    |
    */
    'default_currency' => env('AXEPTA_DEFAULT_CURRENCY', 'EUR'),

    /*
    |--------------------------------------------------------------------------
    | Message Version
    |--------------------------------------------------------------------------
    |
    | The version of the Axepta API to use.
    |
    */
    'message_version' => '2.0',
];