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

    'mfa' => [
        'enabled' => env( 'MFA_ENABLED' ),
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

    'os' => [
        'api_key' => env( 'ONE_SIGNAL_API_KEY' ),
        'app_id' => env( 'ONE_SIGNAL_APP_ID' ),
    ],

    'url' => [
        'admin' => env( 'ADMIN_URL' ),
        'admin_path' => env( 'ADMIN_PATH' ),
        'api' => env( 'API_URL' ),
        'crm' => env( 'CRM_URL' ),
    ],

    'app' => [
        'name' => env( 'APP_NAME' ),
    ],

    'mail' => [
        'receiver' => env( 'MAIL_RECEIVER' )
    ],

    'eghl' => [
        'test_url' => env( 'EGHL_TEST_URL' ),
        'merchant_id' => env( 'EGHL_MERCHANT_ID' ),
        'merchant_password' => env( 'EGHL_MERCHANT_PASSWORD' ),
        'staging_callabck_url' => env( 'EGHL_STAGING_CALLBACK' ),
        'live_callabck_url' => env( 'EGHL_STAGING_LIVE' ),
        'staging_success_url' => env( 'EGHL_STAGING_SUCCESS_URL' ),
        'live_success_url' => env( 'EGHL_LIVE_SUCCESS_URL' ),
        'staging_failed_url' => env( 'EGHL_STAGING_FAILED_URL' ),
        'live_failed_url' => env( 'EGHL_LIVE_FAILED_URL' ),
        'staging_fallback_url' => env( 'EGHL_STAGING_FALLBACK' ),
        'live_fallback_url' => env( 'EGHL_LIVE_FALLBACK' ),
    ],

    'sms' => [
        'sms_url' => env( 'SMS_URL' ),
        'username' => env( 'SMS_USERNAME' ),
        'password' => env( 'SMS_PASSWORD' ),
    ],

    'brevo' => [
        'key' => env( 'BREVO_API_KEY' ),
        'mail' => env( 'BREVO_FROM_MAIL' ),
        'CONTACT_US_MAIL' => env( 'CONTACT_US_MAIL' ),
    ],
];
