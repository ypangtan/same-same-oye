<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads live stats straight from Icecast's own status-json.xsl — Icecast keeps no history, so
 * this is only ever "right now". Anything historical (the listener graph) is built from the
 * periodic snapshots radio:snapshot-listeners writes into radio_listener_snapshots.
 */
class IcecastService
{
    public static function getStatus() {

        $url = config( 'services.radio.icecast_status_url' );

        if ( !$url ) {
            return [ 'listeners' => 0, 'online' => false, 'title' => null ];
        }

        try {
            $response = Http::timeout( 3 )->get( $url );

            if ( !$response->successful() ) {
                return [ 'listeners' => 0, 'online' => false, 'title' => null ];
            }

            $source = data_get( $response->json(), 'icestats.source' );

            // Icecast returns an object for a single mount, or an array when there's more than
            // one — this app only runs one mount, so just take the first either way.
            if ( is_array( $source ) && array_is_list( $source ) ) {
                $source = $source[0] ?? null;
            }

            if ( !$source ) {
                return [ 'listeners' => 0, 'online' => false, 'title' => null ];
            }

            // Populated once Liquidsoap has pushed at least one track's ICY metadata (it tags
            // each request with title="..." via the annotate: protocol — see radio.liq). Empty
            // until then, or while silence (no metadata) is what's actually on air.
            $title = $source['title'] ?? null;

            return [
                'listeners' => (int) ( $source['listeners'] ?? 0 ),
                'online' => true,
                'title' => $title !== '' ? $title : null,
            ];

        } catch ( \Throwable $e ) {
            Log::warning( 'Icecast status check failed: ' . $e->getMessage() );
            return [ 'listeners' => 0, 'online' => false, 'title' => null ];
        }
    }
}
