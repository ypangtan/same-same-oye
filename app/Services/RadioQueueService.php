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
    RadioSetting,
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

    /** Output transition: id identifies the new track; empty id means silence. */
    public static function markPlayed( $request ) {
        $request->validate( [ 'id' => [ 'present', 'nullable', 'string' ] ] );
        $item = $request->filled( 'id' )
            ? RadioQueueItem::find( Helper::decode( $request->id ) ) : null;
        if ( $request->filled( 'id' ) && !$item ) {
            return response()->json( [ 'message' => 'Record not found' ], 404 );
        }
        if ( $item && $item->status == RadioQueueItem::STATUS_PLAYED ) {
            return response()->json( [ 'message' => 'ok' ] );
        }
        $finished = DB::transaction( function() use ( $item ) {
            $previous = RadioQueueItem::where( 'status', RadioQueueItem::STATUS_PLAYING )
                ->when( $item, fn( $q ) => $q->where( 'id', '!=', $item->id ) )
                ->lockForUpdate()->get();
            foreach ( $previous as $track ) {
                $track->status = RadioQueueItem::STATUS_PLAYED;
                $track->save();
            }
            if ( $item && $item->status != RadioQueueItem::STATUS_PLAYING ) {
                $item->status = RadioQueueItem::STATUS_PLAYING;
                $item->played_at = Carbon::now();
                $item->save();
            }
            return $previous;
        } );
        foreach ( $finished as $track ) {
            // Delete only after the actual output transition, keeping the live cover intact.
            foreach ( [ 'file', 'image' ] as $field ) {
                if ( !$track->$field ) continue;
                try {
                    if ( StorageService::delete( $track->$field ) ) {
                        $track->$field = null;
                    } else {
                        \Log::warning( 'Radio: cleanup failed', [ 'id' => $track->id, 'field' => $field ] );
                    }
                } catch ( \Throwable $e ) {
                    \Log::warning( 'Radio: cleanup failed', [ 'id' => $track->id, 'field' => $field ] );
                }
            }
            $track->save();
        }
        return response()->json( [ 'message' => 'ok' ] );
    }

    /** The engine-maintained playing state determines title and cover, never MP3 duration. */
    public static function nowPlaying() {
        $status = IcecastService::getStatus();
        $current = $status['online']
            ? RadioQueueItem::where( 'status', RadioQueueItem::STATUS_PLAYING )
                ->orderBy( 'played_at', 'desc' )->first() : null;
        $image = $current->image_url ?? null;
        if ( $current && !$image ) {
            $image = RadioSetting::current()->default_image_url;
        }
        return response()->json( [
            'title' => $current->title ?? null,
            'image' => $image,
            'listeners' => $status['listeners'],
            'online' => $status['online'],
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

        if ( $item && $item->status == RadioQueueItem::STATUS_PLAYING ) {
            return response()->json( [ 'message' => 'Cannot delete a playing track.' ], 422 );
        }

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

        // A "Reserved" track has already been claimed by the streaming engine and is about to
        // air — a pending track landing at or before its position wouldn't change real playback
        // order (the reserved one plays regardless of what the list shows above it), just
        // create a confusing mismatch between the list and what's actually next. Re-number
        // pending tracks (keeping whatever relative order the drag above just produced) so they
        // always sort after it.
        $reservedPosition = RadioQueueItem::where( 'status', RadioQueueItem::STATUS_RESERVED )
            ->min( 'position' );

        if ( $reservedPosition !== null ) {
            $next = $reservedPosition + 1;
            RadioQueueItem::where( 'status', RadioQueueItem::STATUS_QUEUED )
                ->orderBy( 'position', 'asc' )
                ->get()
                ->each( function( $item ) use ( &$next ) {
                    $item->update( [ 'position' => $next++ ] );
                } );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.radio_queue' ) ) ] ),
        ] );
    }

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

    public static function getDefaultImage() {

        return response()->json( [
            'image' => RadioSetting::current()->default_image_url,
        ] );
    }

    public static function updateDefaultImage( $request ) {

        $setting = RadioSetting::current();
        $setting->default_image = $request->image ?: null;
        $setting->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => __( 'radio.default_image' ) ] ),
            'image' => $setting->default_image_url,
        ] );
    }

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
