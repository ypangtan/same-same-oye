<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    UserNotification,
    UserNotificationUser,
    FileManager,
    User,
    UserDevice,
};

use Helper;

use Carbon\Carbon;

class MarketingNotificationService {

    public static function allAnnouncements( $request ) {

        $notification = UserNotification::with( ['UserNotificationUsers.user.socialLogins'] )
        // ->leftJoin('user_notification_users', 'user_notification_users.user_notification_id', '=', 'user_notifications.id')
        // ->leftJoin('users', 'users.id', '=', 'user_notification_users.user_id')
        ->select('user_notifications.*');
        
        $notification->where('system_title', '<>', null);
        // $notification->has('UserNotificationUsers', '!=', 0);
        // dd($notification->count());
        $filterObject = self::filter( $request, $notification );
        $notification = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $notification->orderBy( 'created_at', $dir );
                    break;
                case 3:
                    $notification->orderBy( 'type', $dir );
                    break;
                case 4:
                    $notification->orderBy( 'status', $dir );
                    break;
            }
        }

        $notificationCount = $notification->count();

        $limit = $request->length;
        $offset = $request->start;

        $notifications = $notification->skip( $offset )->take( $limit )->get();

        if ( $notifications ) {
            $notifications->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = UserNotification::where('system_title', '<>', null)->whereNull( 'user_id' )->count();

        $data = [
            'notifications' => $notifications,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $notificationCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'user_notifications.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'user_notifications.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $title = $request->title ) ) {
            $locale = app()->getLocale();
            $model->where( "user_notifications.title->$locale", 'LIKE', "%$title%" );
            $filter = true;
        }

        if ( !empty( $request->type ) ) {
            $model->where( 'user_notifications.type', $request->type );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'user_notifications.status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->user ) ) {
            $userInput = $request->user;
            $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $userInput );
        
            $model->whereHas( 'UserNotificationUsers.user', function ( $query ) use ( $normalizedPhone, $userInput ) {
                $query->where( 'users.phone_number', 'LIKE', "%$normalizedPhone%" )
                      ->orWhere( 'users.email', 'LIKE', "%$userInput%" );
            } );
        
            $filter = true;
        }
        

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneAnnouncement( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $userNotification = UserNotification::find( $request->id );

        $userNotification->append( [
            'path',
            'display_status'
        ] );

        return response()->json( $userNotification );
    }

    public static function createAnnouncement( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'type' => [ 'required', 'in:2,3' ],
            'title' => [ 'required' ],
            'content' => [ 'required' ],
            'image' => [ 'nullable'],
            'target_url' => [ 'nullable'],
            'all_users' => [ 'nullable', 'in:1,0' ],
            'users' => [ 'required_if:all_users,0' ], 
        ] );

        $attributeName = [
            'type' => __( 'datatables.type' ),
            'title' => __( 'datatables.title' ),
            'content' => __( 'marketing_notification.content' ),
            'image' => __( 'marketing_notification.image' ),
            'target_url' => __( 'marketing_notification.target_url' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        // $request->users != NULL ? $selectedUsersId = explode(',', $request->users) : $selectedUsersId = User::where( 'status', 10 )->select( 'id' )->get()->pluck( 'encrypted_id' );
        $request->users != NULL ? $selectedUsersId = explode(',', $request->users) : $selectedUsersId = array();

        $is_template = self::isPrefixes($request->content);

        $is_broadcast = intval( $request->all_users ) == 1 ? 10 : 0;

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createAnnouncement = UserNotification::create( [
                'type' => $request->type,
                'target_url' => $request->target_url,
                'title' => $request->title,
                'content' => $request->content,
                'is_template' => $is_template,
                'is_broadcast' => $is_broadcast,
                'url_slug' => $request->url_slug ? $request->url_slug : \Str::slug( $request->title ),
                'key' => 'home',
                'system_title' => $request->title,
                'system_content' => NULL,
                'system_data' => NULL,
                'meta_data' => NULL,
            ] );

            $file = FileManager::find( $request->image );
            if ( $file ) {
                $fileName = explode( '/', $file->file );
                $target = 'marketing_notification/' . $createAnnouncement->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $file->file, $target );

                $createAnnouncement->image = $target;
                $createAnnouncement->save();

                $file->status = 10;
                $file->save();
            }

            if( $createAnnouncement && count( $selectedUsersId ) > 0 ){
                foreach( $selectedUsersId as $key => $val ){
                    $user = User::findOrFail( Helper::decode( $val ) );
                    self::sendNotification( $user, $createAnnouncement ); 
                }
            }

            if( $request->users == NULL ){
                $selectedUsersId = User::where( 'status', 10 )->get();
                foreach( $selectedUsersId as $user ){
                    self::sendNotification( $user, $createAnnouncement ); 
                }
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ] );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.marketing_notifications' ) ) ] ),
        ] );

    }

    public static function updateAnnouncement( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'type' => [ 'required', 'in:2,3' ],
            'title' => [ 'required' ],
            'content' => [ 'required' ],
            'image' => [ 'nullable' ],
            'target_url' => [ 'nullable' ],
            'all_users' => ['nullable', 'in:1,0'],
        ] );

        $attributeName = [
            'type' => __( 'datatables.type' ),
            'title' => __( 'datatables.title' ),
            'content' => __( 'marketing_notification.content' ),
            'image' => __( 'marketing_notification.image' ),
            'target_url' => __( 'marketing_notification.target_url' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateAnnouncement = UserNotification::find( $request->id );
            $updateAnnouncement->title = $request->title;
            // $updateAnnouncement->target_url = $request->target_url;
            $updateAnnouncement->url_slug = $request->url_slug ? $request->url_slug : \Str::slug( $request->title );
            $updateAnnouncement->key = 'home';
            $updateAnnouncement->content = $request->content;

            if ( $request->image ) {
                $file = FileManager::find( $request->image );
                if ( $file ) {

                    Storage::disk( 'public' )->delete( $updateAnnouncement->photo );

                    $fileName = explode( '/', $file->file );
                    $target = 'marketing_notification/' . $updateAnnouncement->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $file->file, $target );
    
                    $updateAnnouncement->image = $target;
                    $updateAnnouncement->save();
    
                    $file->status = 10;
                    $file->save();
                }
            }
            
            $is_broadcast = intval( $request->all_users ) == 1 ? 10 : 20;

            $is_template = self::isPrefixes($request->content);

            $updateAnnouncement->is_broadcast = $is_broadcast;
            $updateAnnouncement->type = $request->type;
            $updateAnnouncement->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ] );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.marketing_notifications' ) ) ] ),
        ] );
    }

    public static function updateAnnouncementStatus( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateAnnouncement = UserNotification::where( 'id', $request->id )->first();
        $updateAnnouncement->status = $request->status;
        $updateAnnouncement->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.marketing_notifications' ) ) ] ),
        ] );
    }

    public static function getUserAnnouncements( $request ) {

        $marketingNotificationsIds = UserNotificationUser::where( 'user_id', auth()->user()->id )->pluck('user_notification_id');

        $marketingNotificationss = UserNotification::select('user_notifications.*' )->whereIn( 'id', $marketingNotificationsIds )->orWhere( 'is_broadcast', 10 )->where('status' , 10);

        $marketingNotificationss->orderBy( 'user_notifications.created_at', 'DESC' );

        $marketingNotificationss = $marketingNotificationss->simplePaginate( empty( $request->per_page ) ? 10 : $request->per_page );

        foreach ( $marketingNotificationss->items() as $marketingNotifications ) {

            $marketingNotifications->append( [
                'photo_path',
            ] );

        }

        return response()->json( $marketingNotificationss );

    }

    public static function ckeUpload( $request ) {

        $file = $request->file( 'file' )->store( 'articles/ckeditor', [ 'disk' => 'public' ] );

        $data = [
            'url' => asset( 'storage/' . $file ),
        ];

        return response()->json( $data );
    }

    public static function isPrefixes( $string ) {
        $pattern = '/\{(\w+)\}/';
        preg_match_all( $pattern, $string, $matches );

        return count( $matches[1] ) > 0 ? 10 : 20 ;
    }
    
    public static function deleteAnnouncement( $request ) {

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $UserNotification = UserNotification::find( $request->id );
            $UserNotification->delete();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollBack();

            abort( 500, $th->getMessage() . ' in line: ' . $th->getLine() );
        }

        return response()->json( [
            'message' => __( 'template.delete_x', [ 'title' => Str::singular( __( 'template.marketing_notifications' ) ) ] ),
        ] );
    }

    private static function sendNotification( $user, $createAnnouncement ) {

        $messageContent = array();

        $messageContent['key'] = 'announcement';
        $messageContent['id'] = $createAnnouncement->id;
        $messageContent['message'] = $createAnnouncement->title;

        Helper::sendNotification( $user->id, $messageContent );
        
    } 
}
