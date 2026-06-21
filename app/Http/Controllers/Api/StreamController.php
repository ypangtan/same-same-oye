<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\{
    StreamLog,
    Banner,
    BannerClick,
    PopAnnouncement,
    PopAnnouncementClick,
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
     * 4: Collection
     *
     * @authenticated
     *
     * @bodyParam type string required One of: stream, banner_click, popup_click. Example: stream
     * @bodyParam content_type integer Required when type=stream. One of: 1, 2, 3, 4. Example: 2
     * @bodyParam radio_name string Required when content_type=1. Example: "Hits FM"
     * @bodyParam item_id integer Required when content_type=2. Example: 5
     * @bodyParam playlist_id integer Required when content_type=3. Example: 5
     * @bodyParam collection_id integer Required when content_type=4. Example: 5
     * @bodyParam id integer Required when type=banner_click or popup_click. The ID of the banner or popup. Example: 1
     *
     */
    public function record( Request $request ) {

        $request->validate( [ 'type' => 'required|in:stream,banner_click,popup_click' ] );

        switch ( $request->type ) {

            case 'stream':
                $request->validate( [
                    'content_type'  => 'required|integer|in:1,2,3,4',
                    'radio_name'    => 'required_if:content_type,1|nullable|string|max:255',
                    'item_id'       => 'required_if:content_type,2|nullable|exists:items,id',
                    'playlist_id'   => 'required_if:content_type,3|nullable|exists:playlists,id',
                    'collection_id' => 'required_if:content_type,4|nullable|exists:collections,id',
                ] );
                StreamLog::create( [
                    'user_id'       => auth( 'user' )->id(),
                    'content_type'  => $request->content_type,
                    'radio_name'    => $request->radio_name,
                    'item_id'       => $request->item_id,
                    'playlist_id'   => $request->playlist_id,
                    'collection_id' => $request->collection_id,
                ] );
                break;

            case 'banner_click':
                $request->validate( [ 'id' => 'required|exists:banners,id' ] );
                BannerClick::create( [
                    'banner_id' => $request->id,
                    'user_id'   => auth()->id() ?? null,
                ] );
                break;

            case 'popup_click':
                $request->validate( [ 'id' => 'required|exists:pop_announcements,id' ] );
                PopAnnouncementClick::create( [
                    'pop_announcement_id' => $request->id,
                    'user_id'             => auth()->id() ?? null,
                ] );
                break;
        }

        return response()->json( [ 'success' => true ] );
    }
}
