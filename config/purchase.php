<?php

return [
    'routing' => [
        'signed' => false,
        'middleware' => ['api'],
        'prefix' => 'liap',
    ],

    'google_play_package_name' => env('GOOGLE_PLAY_PACKAGE_NAME', 'com.some.thing'),
    'google_application_credentials' => base_path((string)env('GOOGLE_APPLICATION_CREDENTIALS')),
    
    'appstore_password' => env('APPSTORE_PASSWORD', ''),
    'appstore_sandbox' => env('APPSTORE_SANDBOX', true),
    
    'appstore_private_key_id' => env('APPSTORE_PRIVATE_KEY_ID'),
    'appstore_private_key' => env('APPSTORE_PRIVATE_KEY'),
    'appstore_issuer_id' => env('APPSTORE_ISSUER_ID'),
    'appstore_bundle_id' => env('APPSTORE_BUNDLE_ID'),
    
    'eventListeners' => [
        \Imdhemy\Purchases\Events\GooglePlay\SubscriptionRenewed::class => [],
        \Imdhemy\Purchases\Events\GooglePlay\SubscriptionCanceled::class => [],
        \Imdhemy\Purchases\Events\AppStore\DidRenew::class => [],
        \Imdhemy\Purchases\Events\AppStore\Cancel::class => [],
        \Imdhemy\Purchases\Events\AppStore\Refund::class => [],
    ],
];