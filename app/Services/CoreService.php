<?php

namespace App\Services;

use Illuminate\Support\Facades\{
    DB,
};

use App\Models\{
    AdministratorNotification,
    AdministratorNotificationSeen,
};

use Helper;

class CoreService {

    public static function getNotificationList( $request ) {

        $notifications = AdministratorNotification::select(
            'administrator_notifications.*',
            DB::raw( 'CASE WHEN administrator_notification_seens.id > 0 THEN 1 ELSE 0 END as is_read' )
        )->where( function( $query ) {
            $query->whereNull( 'administrator_notification_administrators.a_id' );
            $query->orWhere( 'administrator_notification_administrators.a_id', auth()->user()->id );
        } );

        $notifications->leftJoin( 'administrator_notification_administrators', function( $query ) {
            $query->on( 'administrator_notification_administrators.an_id', '=', 'administrator_notifications.id' );
        } );

        $notifications->leftJoin( 'administrator_notification_seens', function( $query ) {
            $query->on( 'administrator_notification_seens.an_id', '=', 'administrator_notifications.id' );
            $query->on( 'administrator_notification_seens.a_id', '=', DB::raw( auth()->user()->id ) );
        } );

        $newNotifications = clone $notifications;
        $newNotifications->where( 'administrator_notification_seens.id' );
        $unread = $newNotifications->count();

        // $notifications->where( function( $query ) {
        //     // $query->whereNull( 'administrator_notification_seens.id' );
        //     $query->orWhere( 'administrator_notification_seens.id', '>', 0 );
        // } );

        $notifications->orderBy( 'administrator_notifications.created_at', 'DESC' );

        // $notifications->limit( 10 );

        $notifications = $notifications->get();

        foreach ( $notifications as $notification ) { 
            
            if ( $notification->meta_data ) {

                $metaKeys = json_decode( $notification->meta_data, true );
 
                foreach ( $metaKeys as $key => $metaKey ) {
                    $metaKeys[$key] = __( $metaKey );
                }

                $notification->system_title = __( $notification->system_title, $metaKeys );
                $notification->system_content = __( $notification->system_content, $metaKeys );
            } else {
                $notification->system_title = __( $notification->system_title );
                $notification->system_content = __( $notification->system_content );
            }
            
            $notification->icon = 'ni-clock';
            $notification->time_ago = Helper::getDisplayTimeUnit( $notification->created_at );
            $notification->url = route( 'admin.module_parent.vending_machine_stock.index' );
        }

        return response()->json( [
            'notifications' => $notifications,
            'unread' => $unread,
        ] );
    }

    public static function seenNotification( $request ) {

        if ( $request->id == 0 ) {

            $notifications = AdministratorNotification::select(
                'administrator_notifications.*',
                DB::raw( 'CASE WHEN administrator_notification_seens.id > 0 THEN 1 ELSE 0 END as is_read' )
            )->where( function( $query ) {
                $query->whereNull( 'administrator_notification_administrators.a_id' );
                $query->orWhere( 'administrator_notification_administrators.a_id', auth()->user()->id );
            } );
    
            $notifications->leftJoin( 'administrator_notification_administrators', function( $query ) {
                $query->on( 'administrator_notification_administrators.an_id', '=', 'administrator_notifications.id' );
            } );
    
            $notifications->leftJoin( 'administrator_notification_seens', function( $query ) {
                $query->on( 'administrator_notification_seens.an_id', '=', 'administrator_notifications.id' );
                $query->on( 'administrator_notification_seens.a_id', '=', DB::raw( auth()->user()->id ) );
            } );

            $notifications->whereNull( 'administrator_notification_seens.id' );

            $toBeRead = $notifications->get()->pluck( 'id' );

            foreach ( $toBeRead as $tbr ) {
                AdministratorNotificationSeen::firstOrCreate( [
                    'an_id' => $tbr,
                    'a_id' => auth()->user()->id,
                ] );
            }

            return response()->json( $toBeRead );

        } else {

            AdministratorNotificationSeen::firstOrCreate( [
                'an_id' => $request->id,
                'a_id' => auth()->user()->id,
            ] );
        }
    }
}