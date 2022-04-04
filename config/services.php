<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
    'app_url' => [
        'url' => env('APP_URL')
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'mpesa' => [
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'env' => env('MPESA_ENV'),
        'business_shortcode' => env('MPESA_BUSINESS_SHORTCODE'),
        'initiator_name' => env('MPESA_INITIATOR_NAME'),
        'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
        'lipa_passkey' => env('MPESA_LIPA_NA_MPESA_PASSKEY'),
        'stk_push_url_sandbox' => env('MPESA_STK_PUSH_URL_SANDBOX'),
        'stk_push_url_live' => env('MPESA_STK_PUSH_URL_LIVE'),
        'access_token_url_sandbox' => env('MPESA_ACCESS_TOKEN_URL_SANDBOX'),
        'access_token_url_live' => env('MPESA_ACCESS_TOKEN_URL_LIVE'),
        'b2c_url_sandbox' => env('MPESA_B2C_URL_SANDBOX'),
        'b2c_url_live' => env('MPESA_B2C_URL_LIVE'),
        'query_url_sandbox' => env('MPESA_QUERY_URL_SANDBOX'),
        'query_url_live' => env('MPESA_QUERY_URL_LIVE'),
     ],

];
