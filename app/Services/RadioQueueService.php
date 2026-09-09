<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    RadioQueueItem,
    RadioListenerSnapshot,
};

use Helper;

use Carbon\Carbon;

class RadioQueueService {

    /**
     * Queue listing (tracks not played yet) for the backoffice datatable.
     */
    public static function allItems( $request ) {

        $items = RadioQueueItem::select( 'radio_queue_items.*' )
            ->where( 'status', '!=', RadioQueueItem::STATUS_PLAYED );

        $filterObject = self::filter( $request, $items );
        $item = $filterObject['model'];
        $filter = $filterObject['filter'];

        // The queue's order IS the broadcast order, so it always sorts by position — the
        // reorder drag handle is the only way to change it (same approach as TrendingContent).
        $item->orderBy( 'radio_queue_items.position', 'asc' );

        $itemCount = $item->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $items = $item->skip( $offset )->take( $limit )->get();

        if ( $items ) {
            $items->append( [
                'encrypted_id',
                'display_duration',
                'image_url',
            ] );
        }

        $totalRecord = RadioQueueItem::where( 'status', '!=', RadioQueueItem::STATUS_PLAYED )->count();

        return response()->json( [
            'radio_queue_items' => $items,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $itemCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ] );
    }

    /**
     * Played history — the record that stays even after the R2 file itself has been deleted.
     */
    public static function allHistory( $request ) {

        $items = RadioQueueItem::select( 'radio_queue_items.*' )
            ->where( 'status', RadioQueueItem::STATUS_PLAYED );

        $filterObject = self::filter( $request, $items );
        $item = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $item->orderBy( 'radio_queue_items.played_at', $dir );
                    break;
                case 2:
                    $item->orderBy( 'radio_queue_items.title', $dir );
                    break;
            }
        } else {
            $item->orderBy( 'radio_queue_items.played_at', 'desc' );
        }

        $itemCount = $item->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $items = $item->skip( $offset )->take( $limit )->get();

        $totalRecord = RadioQueueItem::where( 'status', RadioQueueItem::STATUS_PLAYED )->count();

        return response()->json( [
            'radio_queue_items' => $items,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $itemCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ] );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'radio_queue_items.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->created_date ) ) {
            self::applyDateRangeFilter( $model, 'radio_queue_items.created_at', $request->created_date );
            $filter = true;
        }

        if ( !empty( $request->played_date ) ) {
            self::applyDateRangeFilter( $model, 'radio_queue_items.played_at', $request->played_date );
            $filter = true;
        }

        return [ 'model' => $model, 'filter' => $filter ];
    }

    private static function applyDateRangeFilter( $model, $column, $value ) {

        if ( str_contains( $value, 'to' ) ) {
            $dates = explode( ' to ', $value );

            $startDate = explode( '-', $dates[0] );
            $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );

            $endDate = explode( '-', $dates[1] );
            $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );
        } else {
            $dates = explode( '-', $value );

            $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
            $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );
        }

        $model->whereBetween( $column, [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
    }

    public static function createItem( $request ) {

        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'file' => [ 'required' ],
        ] );

        $attributeName = [
            'title' => __( 'radio.title' ),
            'file' => __( 'radio.song' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            RadioQueueItem::create( [
                'title' => $request->title,
                'file' => $request->file,
                'file_name' => $request->file_name,
                'image' => $request->image ?: null,
                'duration' => $request->duration ?: null,
                'position' => (int) RadioQueueItem::max( 'position' ) + 1,
                'status' => RadioQueueItem::STATUS_QUEUED,
                'add_by' => auth()->user()->id,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.radio_queue' ) ) ] ),
        ] );
    }

    /**
     * Remove a track from the queue before it has aired. Since it was never broadcast there is
     * no history worth keeping, so this hard-deletes both the row and the R2 file.
     */
    public static function deleteItem( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $item = RadioQueueItem::find( $request->id );

        if ( !$item ) {
            return response()->json( [
                'message' => __( 'template.record_not_found' ),
            ], 404 );
        }

        if ( $item->file ) {
            StorageService::delete( $item->file );
        }

        $item->delete();

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.radio_queue' ) ) ] ),
        ] );
    }

    public static function updateOrder( $request ) {

        $updates = $request->input( 'updates' );

        foreach( $updates as $update ) {
            $item = RadioQueueItem::where( 'status', RadioQueueItem::STATUS_QUEUED )
                ->find( Helper::decode( $update['id'] ) );
            if( $item ) {
                $item->position = $update['position'];
                $item->save();
            }
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.radio_queue' ) ) ] ),
        ] );
    }

    /**
     * Hand the next track to the streaming engine (Liquidsoap). The item is immediately marked
     * "reserved" so a second poll before this one is confirmed played doesn't hand out the same
     * track twice. A reservation older than 10 minutes (engine died mid-play) is treated as
     * abandoned and becomes eligible again.
     *
     * Deliberately plain text, not JSON: "{id}\n{url}\n{title}", or an empty body when the
     * queue is empty. Liquidsoap's JSON API has changed shape across versions (needs a typed
     * `default` argument on newer ones); a line-based body needs nothing but string.split,
     * which has been stable forever. The title is what Liquidsoap tags the request's ICY
     * metadata with, which Icecast then reports back live — see IcecastService::getStatus()
     * and nowPlaying(), which reads it from there instead of guessing off duration.
     */
    public static function next() {

        $item = RadioQueueItem::where( function( $q ) {
                $q->where( 'status', RadioQueueItem::STATUS_QUEUED )
                    ->orWhere( function( $q2 ) {
                        $q2->where( 'status', RadioQueueItem::STATUS_RESERVED )
                            ->where( 'reserved_at', '<', Carbon::now()->subMinutes( 10 ) );
                    } );
            } )
            ->orderBy( 'position', 'asc' )
            ->first();

        if ( !$item ) {
            return response( '', 200 )->header( 'Content-Type', 'text/plain' );
        }

        $item->status = RadioQueueItem::STATUS_RESERVED;
        $item->reserved_at = Carbon::now();
        $item->save();

        $body = $item->encrypted_id . "\n" . $item->file_url . "\n" . $item->title;

        return response( $body, 200 )->header( 'Content-Type', 'text/plain' );
    }

    /**
     * Callback from the streaming engine the instant a track starts airing (safe to delete the
     * R2 file at this point — Liquidsoap has already downloaded it locally). Only the title and
     * timestamps are kept afterwards, as the played-history record.
     */
    public static function markPlayed( $request ) {

        $id = Helper::decode( $request->id );
        $item = RadioQueueItem::find( $id );

        if ( !$item ) {
            return response()->json( [
                'message' => __( 'template.record_not_found' ),
            ], 404 );
        }

        if ( $item->file ) {
            StorageService::delete( $item->file );
        }

        $item->file = null;
        $item->status = RadioQueueItem::STATUS_PLAYED;
        $item->played_at = Carbon::now();
        $item->save();

        // Note: this is the engine reporting a track aired, not a user pressing play — it does
        // NOT go into stream_logs (content_type=1 there is the user-triggered "listened to
        // radio" event from the app, via StreamService::recordStream, and stays keyed to a real
        // user_id). This row in radio_queue_items IS the played-history record.

        return response()->json( [ 'message' => 'ok' ] );
    }

    /**
     * Live "now playing" panel for the backoffice. The title comes straight from Icecast's own
     * live metadata (Liquidsoap tags each request with title="..." via annotate:, and
     * output.icecast forwards that as an ICY update) — not guessed from our own duration data,
     * so it's accurate even if ffprobe got a track's duration wrong, and correctly goes blank
     * once the queue runs dry and the engine falls back to silence (which carries no title).
     *
     * Icecast only ever carries a text title, no image, so the cover image is found by matching
     * that title back to our own played-history row. Titles aren't unique, but pairing "matches
     * the live title" with "most recently played" is reliable enough in practice.
     */
    public static function nowPlaying() {

        $status = IcecastService::getStatus();

        $image = null;
        if ( $status['title'] ) {
            $current = RadioQueueItem::where( 'status', RadioQueueItem::STATUS_PLAYED )
                ->where( 'title', $status['title'] )
                ->orderBy( 'played_at', 'desc' )
                ->first();
            $image = $current->image_url ?? null;
        }

        return response()->json( [
            'title' => $status['title'],
            'image' => $image,
            'listeners' => $status['listeners'],
            'online' => $status['online'],
        ] );
    }

    /**
     * Listener count over time, for the backoffice graph. Reads the periodic snapshots written
     * by the radio:snapshot-listeners scheduled command — Icecast itself has no history.
     */
    public static function listenerGraph( $request ) {

        $hours = (int) ( $request->hours ?: 24 );
        $since = Carbon::now()->subHours( $hours );

        $rows = RadioListenerSnapshot::where( 'created_at', '>=', $since )
            ->orderBy( 'created_at', 'asc' )
            ->get( [ 'listeners', 'created_at' ] );

        return response()->json( [
            'labels' => $rows->map( fn( $r ) => $r->created_at->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i' ) ),
            'data' => $rows->pluck( 'listeners' ),
        ] );
    }
}
