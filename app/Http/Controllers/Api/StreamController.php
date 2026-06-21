<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\StreamLog;

class StreamController extends Controller
{
    /**
     * 1. Record a stream event (radio, item, playlist, collection). 
     *
     * @group Stream API
     * 
     * <strong>content_type</strong><br>
     * 1: Radio<br>
     * 2: Item<br>
     * 3: Playlist<br>
     * 4: Collection
     *
     * @authenticated
     * 
     * @bodyParam content_type string required One of: 1, 2, 3, 4. Example: 2
     * @bodyParam radio_id integer The name the radio being streamed (null for other content types). Example: 5
     * @bodyParam item_id integer The encrypted id of the item being streamed (null for other content types). Example: 5
     * @bodyParam playlist_id integer The encrypted id of the playlist being streamed (null for other content types). Example: 5
     * @bodyParam collection_id integer The encrypted id of the collection being streamed (null for other content types). Example: 5
     * 
     */
    public function recordStream( Request $request ) {

        
    }
}
