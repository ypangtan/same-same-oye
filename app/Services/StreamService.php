<?php

namespace App\Services;

use App\Models\{
    StreamLog,
};

class SettingService {

    public static function recordStream( $request ) {
        $request->validate( [
            'content_type' => [ 'required', 'in:1,2,3,4' ],
            'radio_name'    => [ 'nullable' ],
            'item_id'     => [ 'required_if:content_type,2', 'exists:items,id' ],
            'playlist_id' => [ 'required_if:content_type,3', 'exists:playlists,id' ],
            'collection_id' => [ 'required_if:content_type,4', 'exists:collections,id' ],
        ] );

        StreamLog::create( [
            'user_id'      => auth()->id() ?? null,
            'content_type' => $request->content_type,
            'radio_name'   => $request->radio_name ?? null,
            'item_id'    => $request->item_id ?? null,
            'playlist_id' => $request->playlist_id ?? null,
            'collection_id' => $request->collection_id ?? null,
        ] );

        return response()->json( [ 'message' => 'Stream recorded.' ] );
    }
}
