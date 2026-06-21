<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    StreamService
};

class StreamController extends Controller
{
    /**
     * Record an activity (stream, banner click, or popup click).
     *
     * @group Activity API
     *
     * <strong>type</strong><br>
     * stream: Record a stream event<br>
     * banner_click: Record a banner click<br>
     * popup_click: Record a popup click<br>
     * <br>
     * <strong>content_type</strong> (required when type=stream)<br>
     * 1: Radio<br>
     * 2: Item<br>
     * 3: Playlist<br>
     * 4: Collection<br>
     * 5: Banner<br>
     * 6: Pop Announcement<br>
     *
     * @authenticated
     *
     * @bodyParam type string required One of: stream, banner_click, popup_click. Example: stream
     * @bodyParam content_type integer Required when type=stream. One of: 1, 2, 3, 4, 5, 6. Example: 2
     * @bodyParam radio_name string nullable. Example: "Hits FM"
     * @bodyParam item_id integer Required when content_type=2. Example: 5
     * @bodyParam playlist_id integer Required when content_type=3. Example: 5
     * @bodyParam collection_id integer Required when content_type=4. Example: 5
     * @bodyParam banner_id integer Required when content_type=5. Example: 5
     * @bodyParam pop_announcement_id integer Required when content_type=6. Example: 5
     *
     */
    public function record( Request $request ) {

        return StreamService::recordStream( $request );
    }
}
