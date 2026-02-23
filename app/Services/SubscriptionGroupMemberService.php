<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};

use Helper;

use App\Models\{
    SubscriptionGroupMember,
    User,
    UserSubscription,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SubscriptionGroupMemberService {

    public static function createSubscriptionGroupMember( $request ) {

        $validator = Validator::make( $request->all(), [
            'owner_id' => [ 'required','exists:users,id', function ( $attribute, $value, $fail ) use ( $request ) {
                $user_subscription = UserSubscription::where( 'user_id', $value )
                    ->where( 'status', 10 )
                    ->isGroup()
                    ->notHitMaxMember()
                    ->first();

                if ( !$user_subscription ) {
                    $fail( 'Owner does not have an active group subscription or group is full.' );
                    return;
                }

                $alreadyInGroup = SubscriptionGroupMember::where( 'user_id', $request->user_id )
                    ->whereHas( 'userSubscription', function ( $q ) use ( $value ) {
                        $q->where( 'status', 10 )
                        ->isGroup()
                        ->where( 'user_id', '!=', $value );
                    } )
                    ->exists();

                if ( $alreadyInGroup ) {
                    $fail( 'User is already in another group.' );
                }
            } ],
            'user_id' => [ 'required','exists:users,id' ],
        ] );


        $attributeName = [
            'owner_id' => __( 'subscription_group_member.owner' ),
            'user_id' => __( 'subscription_group_member.user' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $user_subscription = UserSubscription::where( 'user_id', $request->owner_id )
                ->where( 'status', 10 )
                ->first();
            $subscriptionGroupMemberCreate = SubscriptionGroupMember::create([
                'user_id' => $request->user_id,
                'user_subscription_id' => $user_subscription->id,
            ]);

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.subscription_group_members' ) ) ] ),
            'status' => 200
        ] );
    }
    
    public static function updateSubscriptionGroupMember( $request ) {

        $validator = Validator::make( $request->all(), [
            'owner_id' => [ 'required','exists:users,id', function ( $attribute, $value, $fail ) use ( $request ) {
                $user_subscription = UserSubscription::where( 'user_id', $value )
                    ->where( 'status', 10 )
                    ->isGroup()
                    ->notHitMaxMember()
                    ->first();

                if ( !$user_subscription ) {
                    $fail( 'Owner does not have an active group subscription or group is full.' );
                    return;
                }

                $alreadyInGroup = SubscriptionGroupMember::where( 'user_id', $request->user_id )
                    ->whereHas( 'userSubscription', function ( $q ) use ( $value ) {
                        $q->where( 'status', 10 )
                        ->isGroup()
                        ->where( 'user_id', '!=', $value );
                    } )
                    ->exists();

                if ( $alreadyInGroup ) {
                    $fail( 'User is already in another group.' );
                }
            } ],
            'user_id' => [ 'required','exists:users,id' ],
        ] );

        $attributeName = [
            'owner_id' => __( 'subscription_group_member.owner' ),
            'user_id' => __( 'subscription_group_member.user' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();

        try {

            $user_subscription = UserSubscription::where( 'user_id', $request->owner_id )
                ->where( 'status', 10 )
                ->first();

            $subscriptionGroupMemberUpdate = SubscriptionGroupMember::lockForUpdate()->find( $request->id );
            $subscriptionGroupMemberUpdate->user_subscription_id = $user_subscription->id;
            $subscriptionGroupMemberUpdate->user_id = $request->user_id;
            $subscriptionGroupMemberUpdate->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.subscription_group_members' ) ) ] ),
        ] );
    }

    public static function allSubscriptionGroupMembers( $request ) {

        $subscriptionGroupMembers = SubscriptionGroupMember::select( 'subscriptionGroupMembers.*');

        $filterObject = self::filter( $request, $subscriptionGroupMembers );
        $subscriptionGroupMember = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $subscriptionGroupMember->orderBy( 'subscriptionGroupMembers.created_at', $dir );
                    break;
                case 2:
                    $subscriptionGroupMember->orderBy( 'subscriptionGroupMembers.title', $dir );
                    break;
                case 3:
                    $subscriptionGroupMember->orderBy( 'subscriptionGroupMembers.description', $dir );
                    break;
            }
        }

            $subscriptionGroupMemberCount = $subscriptionGroupMember->count();

            $limit = $request->length == -1 ? 1000000 : $request->length;
            $offset = $request->start;

            $subscriptionGroupMembers = $subscriptionGroupMember->skip( $offset )->take( $limit )->get();

            if ( $subscriptionGroupMembers ) {
                $subscriptionGroupMembers->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }

            $totalRecord = SubscriptionGroupMember::count();

            $data = [
                'subscriptionGroupMembers' => $subscriptionGroupMembers,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $subscriptionGroupMemberCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'subscriptionGroupMembers.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'subscriptionGroupMembers.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_subscriptionGroupMember)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_subscriptionGroupMember . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->subscriptionGroupMember_type ) ) {
            $model->where( 'type', $request->subscriptionGroupMember_type );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->code ) ) {
            $model->where( 'code', 'LIKE', '%' . $request->code . '%' );
            $filter = true;
        }

        if ( !empty( $request->vending_machine_id ) ) {
            $vendingMachineSubscriptionGroupMembers = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'subscriptionGroupMember_id' );
            $model->whereNotIn( 'id', $vendingMachineSubscriptionGroupMembers );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneSubscriptionGroupMember( $request ) {

        $subscriptionGroupMember = SubscriptionGroupMember::find( $request->id );

        $subscriptionGroupMember->append( ['encrypted_id','image_path'] );
        
        return response()->json( $subscriptionGroupMember );
    }

    public static function deleteSubscriptionGroupMember( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'subscriptionGroupMember.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $subscriptionGroupMember = SubscriptionGroupMember::find($request->id);
            Storage::delete( $subscriptionGroupMember->image );
            $subscriptionGroupMember->delete();
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.subscriptionGroupMembers' ) ) ] ),
        ] );
    }

    public static function getSubscriptionGroupMembers() {

        $user_subscription = UserSubscription::where( 'user_id', auth()->user()->id )->isGroup()->where( 'status', 10 )->first();
        
        if ( !$user_subscription ) {
            $userSubscriptionGroup = SubscriptionGroupMember::where('user_id', auth()->user()->id )->first();
            if( !$userSubscriptionGroup ) {
                return response()->json([
                    'message' => '',
                    'message_key' => 'get_subscription_group_member_success',
                    'members' => [],
                    'owner' => [],
                    'user_subscription' => [],
                ] );
            }
            $user_subscription = UserSubscription::find( $userSubscriptionGroup->user_subscription_id );
        }

        $owner = User::find( $user_subscription->user_id );

        $members = SubscriptionGroupMember::with( [
            'user'
        ] )->where('user_subscription_id', $user_subscription->id );

        $members = $members->get();

        foreach( $members as $subscriptionGroupMember ) {
            $subscriptionGroupMember->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'get_subscription_group_member_success',
            'members' => $members,
            'owner' => $owner,
            'user_subscription' => $user_subscription,
        ] );

    }

}