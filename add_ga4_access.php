<?php

$accessToken = $argv[1] ?? null;

if ( ! $accessToken ) {
    echo "Usage: php add_ga4_access.php YOUR_ACCESS_TOKEN\n";
    exit(1);
}

$propertyId  = '522079653';
$email       = 'firebase-adminsdk-fbsvc@sama-sama-oye-e1f46.iam.gserviceaccount.com';
$url         = "https://analyticsadmin.googleapis.com/v1alpha/properties/{$propertyId}/accessBindings";

$payload = json_encode([
    'user'  => $email,
    'roles' => ['predefinedRoles/viewer'],
]);

$ch = curl_init( $url );
curl_setopt_array( $ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ],
] );

$response = curl_exec( $ch );
$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
curl_close( $ch );

echo "HTTP {$httpCode}\n";
echo $response . "\n";
