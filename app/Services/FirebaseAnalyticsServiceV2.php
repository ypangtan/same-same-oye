<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;

/**
 * FirebaseAnalyticsServiceV2
 *
 * Fetches GA4 analytics data via the Google Analytics Data API (v1beta).
 * Uses OAuth2 refresh_token — no service-account JSON file required.
 *
 * Setup:
 *
 *   STEP 1 — Create OAuth2 credentials
 *     a. Go to https://console.cloud.google.com/ → APIs & Services → Credentials.
 *     b. Create OAuth 2.0 Client ID → Application type: Web application.
 *     c. Add https://developers.google.com/oauthplayground as an Authorized redirect URI.
 *     d. Note the Client ID and Client Secret.
 *
 *   STEP 2 — Get a refresh_token via OAuth Playground
 *     a. Go to https://developers.google.com/oauthplayground
 *     b. Click the gear icon (top right) → check "Use your own OAuth credentials" →
 *        enter your Client ID and Client Secret.
 *     c. In Step 1, find "Google Analytics Data API v1beta" and select
 *        https://www.googleapis.com/auth/analytics.readonly → Authorise APIs.
 *     d. In Step 2, click "Exchange authorization code for tokens".
 *     e. Copy the refresh_token value.
 *
 *   STEP 3 — Enable the Analytics Data API
 *     a. Go to https://console.cloud.google.com/apis/library.
 *     b. Search "Google Analytics Data API" → Enable.
 *
 *   STEP 4 — Grant your Google account access to GA4
 *     a. Go to https://analytics.google.com/ → Admin → Property Access Management.
 *     b. Add the Google account you used in Step 2 with Viewer role.
 *
 *   STEP 5 — Set .env variables
 *     GOOGLE_OAUTH_CLIENT_ID=your-client-id
 *     GOOGLE_OAUTH_CLIENT_SECRET=your-client-secret
 *     GOOGLE_OAUTH_REFRESH_TOKEN=your-refresh-token
 *     FIREBASE_GA4_PROPERTY_ID=123456789   ← numeric ID, found in GA4 Admin → Property Settings
 *
 * Usage:
 *   // Last 30 days (default)
 *   $stats = FirebaseAnalyticsServiceV2::appStats();
 *
 *   // Custom period — any GA4 date string e.g. '7daysAgo', '2024-01-01'
 *   $stats = FirebaseAnalyticsServiceV2::appStats('7daysAgo');
 *
 *   // Real-time active users (last 30 minutes, no period argument)
 *   $realtime = FirebaseAnalyticsServiceV2::realtimeActiveUsers();
 *
 * Return shape of appStats():
 *   [
 *     'installs'     => ['android' => int, 'ios' => int, 'total' => int],
 *     'removals'     => ['android' => int, 'total' => int],          // iOS removals not reported by GA4
 *     'active_users' => ['android' => int, 'ios' => int, 'total' => int],
 *   ]
 *
 * Return shape of realtimeActiveUsers():
 *   ['android' => int, 'ios' => int, 'total' => int]
 *
 * Caching:
 *   access_token          → cached 58 minutes (expires in 60, buffer of 2 min).
 *   appStats()            → cached 1 hour per env+period combination.
 *   realtimeActiveUsers() → cached 5 minutes (real-time data, short TTL).
 *
 * GA4 metric notes:
 *   - activeUsers  = users who triggered at least one engagement event in the period.
 *   - newUsers     = users who opened the app for the first time.
 *   - app_remove   = Android uninstall event (iOS does not fire this event).
 */
class FirebaseAnalyticsServiceV2
{
    private static function base64UrlEncode( string $data ): string {
        return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
    }

    private static function getAccessToken(): string {
        return Cache::remember( 'ga4_service_account_access_token', 3480, function () {
            $credentials = json_decode( file_get_contents( config( 'services.firebase.credentials_path' ) ), true );

            $now     = time();
            $header  = self::base64UrlEncode( json_encode( [ 'alg' => 'RS256', 'typ' => 'JWT' ] ) );
            $payload = self::base64UrlEncode( json_encode( [
                'iss'   => $credentials['client_email'],
                'sub'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ] ) );

            $toSign = $header . '.' . $payload;
            openssl_sign( $toSign, $signature, $credentials['private_key'], 'SHA256' );
            $jwt = $toSign . '.' . self::base64UrlEncode( $signature );

            $http     = new Client();
            $response = $http->post( 'https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $jwt,
                ],
            ] );

            return json_decode( $response->getBody()->getContents(), true )['access_token'];
        } );
    }

    /** Standard (historical) report — use for installs, removals, active users over a date range. */
    private static function runReport( string $accessToken, string $propertyId, array $payload ): array {
        $http     = new Client();
        $response = $http->post(
            "https://analyticsdata.googleapis.com/v1beta/properties/{$propertyId}:runReport",
            [
                'headers' => [ 'Authorization' => "Bearer {$accessToken}" ],
                'json'    => $payload,
            ]
        );
        return json_decode( $response->getBody()->getContents(), true );
    }

    /**
     * Real-time report — use for active users in the last 30 minutes.
     * Supports only a limited set of dimensions/metrics vs runReport.
     * See: https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/properties/runRealtimeReport
     */
    private static function runRealtimeReport( string $accessToken, string $propertyId, array $payload ): array {
        $http     = new Client();
        $response = $http->post(
            "https://analyticsdata.googleapis.com/v1beta/properties/{$propertyId}:runRealtimeReport",
            [
                'headers' => [ 'Authorization' => "Bearer {$accessToken}" ],
                'json'    => $payload,
            ]
        );
        return json_decode( $response->getBody()->getContents(), true );
    }

    /**
     * Returns a GA4 dimensionFilter scoped to the live stream IDs.
     * Prevents mixing web traffic with app traffic when both share the same property.
     * Returns null if no streams configured — GA4 will return data for all streams.
     */
    private static function streamFilter(): ?array {
        $streams = config( 'services.firebase.streams', [] );

        if ( empty( $streams ) ) {
            return null;
        }

        return [
            'filter' => [
                'fieldName'    => 'streamId',
                'inListFilter' => [ 'values' => array_values( $streams ) ],
            ],
        ];
    }

    /**
     * Returns install, removal, and active-user counts broken down by platform.
     *
     * @param  string $period  GA4 start-date string. Examples: '30daysAgo', '7daysAgo', '2024-01-01'.
     *                         End date is always today.
     */
    public static function appStats( string $period = '30daysAgo' ): array {
        $env      = app()->environment();
        $cacheKey = "firebase_app_stats_v2_{$env}_{$period}";

        return Cache::remember( $cacheKey, 3600, function () use ( $period ) {

            $propertyId   = config( 'services.firebase.ga4_property_id' );
            $accessToken  = self::getAccessToken();
            $dateRange    = [ 'startDate' => $period, 'endDate' => 'today' ];
            $streamFilter = self::streamFilter();

            // --- New installs (first_open event) ---
            $installsPayload = [
                'dateRanges' => [ $dateRange ],
                'dimensions' => [ [ 'name' => 'platform' ] ],
                'metrics'    => [ [ 'name' => 'newUsers' ] ],
            ];
            if ( $streamFilter ) {
                $installsPayload['dimensionFilter'] = $streamFilter;
            }

            // --- Uninstalls (app_remove event, Android only — iOS doesn't fire this) ---
            $removalsPayload = [
                'dateRanges'      => [ $dateRange ],
                'dimensions'      => [ [ 'name' => 'platform' ] ],
                'metrics'         => [ [ 'name' => 'eventCount' ] ],
                'dimensionFilter' => [
                    'andGroup' => [
                        'expressions' => array_filter( [
                            [
                                'filter' => [
                                    'fieldName'    => 'eventName',
                                    'stringFilter' => [ 'value' => 'app_remove', 'matchType' => 'EXACT' ],
                                ],
                            ],
                            $streamFilter,
                        ] ),
                    ],
                ],
            ];

            // --- Active users (engaged at least once in the period) ---
            $activeUsersPayload = [
                'dateRanges' => [ $dateRange ],
                'dimensions' => [ [ 'name' => 'platform' ] ],
                'metrics'    => [ [ 'name' => 'activeUsers' ] ],
            ];
            if ( $streamFilter ) {
                $activeUsersPayload['dimensionFilter'] = $streamFilter;
            }

            $installsResult    = self::runReport( $accessToken, $propertyId, $installsPayload );
            $removalsResult    = self::runReport( $accessToken, $propertyId, $removalsPayload );
            $activeUsersResult = self::runReport( $accessToken, $propertyId, $activeUsersPayload );

            $installs = [ 'android' => 0, 'ios' => 0, 'total' => 0 ];
            foreach ( $installsResult['rows'] ?? [] as $row ) {
                $platform = strtolower( $row['dimensionValues'][0]['value'] );
                $count    = (int) $row['metricValues'][0]['value'];
                if ( $platform === 'android' )      $installs['android'] = $count;
                elseif ( $platform === 'ios' )       $installs['ios']     = $count;
                $installs['total'] += $count;
            }

            $removals = [ 'android' => 0, 'total' => 0 ];
            foreach ( $removalsResult['rows'] ?? [] as $row ) {
                $platform = strtolower( $row['dimensionValues'][0]['value'] );
                $count    = (int) $row['metricValues'][0]['value'];
                if ( $platform === 'android' ) $removals['android'] = $count;
                $removals['total'] += $count;
            }

            $activeUsers = [ 'android' => 0, 'ios' => 0, 'total' => 0 ];
            foreach ( $activeUsersResult['rows'] ?? [] as $row ) {
                $platform = strtolower( $row['dimensionValues'][0]['value'] );
                $count    = (int) $row['metricValues'][0]['value'];
                if ( $platform === 'android' )      $activeUsers['android'] = $count;
                elseif ( $platform === 'ios' )       $activeUsers['ios']     = $count;
                $activeUsers['total'] += $count;
            }

            return [
                'installs'     => $installs,
                'removals'     => $removals,
                'active_users' => $activeUsers,
            ];
        } );
    }

    /**
     * Returns the number of users active in the last 30 minutes, broken down by platform.
     * Uses GA4's runRealtimeReport endpoint — data is not sampled but has a ~60s delay.
     *
     * Cached for 5 minutes. To get fresher data, call Cache::forget() with the key
     * "firebase_realtime_active_users_{env}" before calling this method.
     */
    public static function realtimeActiveUsers(): array {
        $env      = app()->environment();
        $cacheKey = "firebase_realtime_active_users_{$env}";

        return Cache::remember( $cacheKey, 300, function () {

            $propertyId  = config( 'services.firebase.ga4_property_id' );
            $accessToken = self::getAccessToken();

            // minutesAgo is the only date range supported by runRealtimeReport.
            // '0' = now, '29' = 30 minutes ago.
            $payload = [
                'minuteRanges' => [ [ 'startMinutesAgo' => 29, 'endMinutesAgo' => 0 ] ],
                'dimensions'   => [ [ 'name' => 'platform' ] ],
                'metrics'      => [ [ 'name' => 'activeUsers' ] ],
            ];

            // Note: streamId filter is NOT supported by runRealtimeReport.
            // If your property mixes web + app streams, results will include all platforms.

            $result = self::runRealtimeReport( $accessToken, $propertyId, $payload );

            $activeUsers = [ 'android' => 0, 'ios' => 0, 'total' => 0 ];
            foreach ( $result['rows'] ?? [] as $row ) {
                $platform = strtolower( $row['dimensionValues'][0]['value'] );
                $count    = (int) $row['metricValues'][0]['value'];
                if ( $platform === 'android' )      $activeUsers['android'] = $count;
                elseif ( $platform === 'ios' )       $activeUsers['ios']     = $count;
                $activeUsers['total'] += $count;
            }

            return $activeUsers;
        } );
    }
}
