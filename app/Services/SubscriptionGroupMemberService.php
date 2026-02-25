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

        if( !empty( $request->leader_id ) ) {
            $request->merge( [
                'leader_id' => \Helper::decode( $request->leader_id )
            ] );
        }

        if( !empty( $request->user_id ) ) {
            $request->merge( [
                'user_id' => \Helper::decode( $request->user_id )
            ] );
        }

        $validator = Validator::make( $request->all(), [
            'leader_id' => [ 'required','exists:users,id', function ( $attribute, $value, $fail ) use ( $request ) {
                $user_subscription = UserSubscription::where( 'user_id', $value )
                    ->isActive()
                    ->isGroup()
                    ->notHitMaxMember()
                    ->first();

                if ( !$user_subscription ) {
                    $fail( 'Leader does not have an active group subscription or group is full.' );
                    return;
                }

                $alreadyInGroup = SubscriptionGroupMember::where( 'user_id', $request->user_id )
                    ->exists();

                if ( $alreadyInGroup ) {
                    $fail( 'User is already in another group.' );
                }
            } ],
            'user_id' => [ 'required','exists:users,id' ],
        ] );


        $attributeName = [
            'leader_id' => __( 'subscription_group_member.leader' ),
            'user_id' => __( 'subscription_group_member.user' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $subscriptionGroupMemberCreate = SubscriptionGroupMember::create([
                'user_id' => $request->user_id,
                'leader_id' => $request->leader_id,
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

        if( !empty( $request->leader_id ) ) {
            $request->merge( [
                'leader_id' => \Helper::decode( $request->leader_id )
            ] );
        }

        if( !empty( $request->user_id ) ) {
            $request->merge( [
                'user_id' => \Helper::decode( $request->user_id )
            ] );
        }

        $validator = Validator::make( $request->all(), [
            'leader_id' => [ 'required','exists:users,id', function ( $attribute, $value, $fail ) use ( $request ) {
                $user_subscription = UserSubscription::where( 'user_id', $value )
                    ->where( 'status', 10 )
                    ->isGroup()
                    ->notHitMaxMember()
                    ->first();

                if ( !$user_subscription ) {
                    $fail( 'Leader does not have an active group subscription or group is full.' );
                    return;
                }

                $alreadyInGroup = SubscriptionGroupMember::where( 'user_id', $request->user_id )
                    ->exists();
                if ( $alreadyInGroup ) {
                    $fail( 'User is already in another group.' );
                }
            } ],
            'user_id' => [ 'required','exists:users,id' ],
        ] );

        $attributeName = [
            'leader_id' => __( 'subscription_group_member.leader' ),
            'user_id' => __( 'subscription_group_member.user' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();

        try {

            $subscriptionGroupMemberUpdate = SubscriptionGroupMember::lockForUpdate()->find( $request->id );
            $subscriptionGroupMemberUpdate->leader_id = $request->leader_id;
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

        $subscriptionGroupMembers = SubscriptionGroupMember::with( [
            'user',
            'leader',
        ] )->select( 'subscription_group_members.*');

        $filterObject = self::filter( $request, $subscriptionGroupMembers );
        $subscriptionGroupMember = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $subscriptionGroupMember->orderBy( 'subscriptionGroupMembers.created_at', $dir );
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
            ] );
        }

        $totalRecord = SubscriptionGroupMember::count();

        $data = [
            'subscription_group_members' => $subscriptionGroupMembers,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $subscriptionGroupMemberCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );

    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->id ) ) {
            $model->where( 'subscriptionGroupMembers.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if ( !empty( $request->leader_id ) ) {
            $model->where( 'subscriptionGroupMembers.leader_id', Helper::decode($request->leader_id) );
            $filter = true;
        }

        if ( !empty( $request->user_id ) ) {
            $model->where( 'subscriptionGroupMembers.user_id', Helper::decode($request->user_id) );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneSubscriptionGroupMember( $request ) {

        $subscriptionGroupMember = SubscriptionGroupMember::with( [
            'user',
            'leader',
        ] )->find( $request->id );

        $subscriptionGroupMember->append( ['encrypted_id'] );
        if( $subscriptionGroupMember ) {
            if( $subscriptionGroupMember->user ) {
                $subscriptionGroupMember->user->append( [ 'encrypted_id' ] );
            }
            if( $subscriptionGroupMember->leader ) {
                $subscriptionGroupMember->leader->append( [ 'encrypted_id' ] );
            }
        }
        
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
            $subscriptionGroupMember->delete();
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.subscription_group_members' ) ) ] ),
        ] );
    }

    // Api
    public static function getSubscriptionGroupMembers() {

        $members = SubscriptionGroupMember::with( [
            'user',
        ] )->where( 'leader_id', auth()->user()->id )
            ->orWhere( 'user_id', auth()->user()->id )
            ->get();
            
        $leader = null;
        $user_subscription = null;

        if( $members ) {
            foreach( $members as $key => $member ) {
                $member->append( [
                    'encrypted_id',
                ] );

                if( $key == 0 ) {
                    $leader = User::find( $member->leader_id );
                    $user_subscription = UserSubscription::where( 'user_id', $member->leader_id )
                        ->isActive()
                        ->isGroup()
                        ->first();
                }
            }
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'get_subscription_group_member_success',
            'members' => $members,
            'leader' => $leader,
            'user_subscription' => $user_subscription,
        ] );

    }

    public static function createSubscriptionGroupMemberApi( $request ) {

        if( !empty( $request->user_id ) ) {
            $request->merge( [
                'user_id' => \Helper::decode( $request->user_id )
            ] );
        }

        $validator = Validator::make( $request->all(), [
            'user_id' => [ 'required', 'exists:users,id', function ( $attribute, $value, $fail ) {
                $user_subscription = UserSubscription::where( 'user_id', auth()->user()->id )
                    ->isActive()
                    ->isGroup()
                    ->notHitMaxMember()
                    ->first();

                if ( !$user_subscription ) {
                    $fail( 'You does not have an active group subscription or group is full.' );
                    return;
                }

                $alreadyInGroup = SubscriptionGroupMember::where( 'user_id', $value )
                    ->exists();
                if ( $alreadyInGroup ) {
                    $fail( 'User is already in another group.' );
                    return;
                }
            } ],
        ] );


        $attributeName = [
            'user_id' => __( 'subscription_group_member.user' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $subscriptionGroupMemberCreate = SubscriptionGroupMember::create([
                'user_id' => $request->user_id,
                'leader_id' => auth()->user()->id,
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
    
    public static function updateSubscriptionGroupMemberApi( $request ) {

        if( !empty( $request->user_id ) ) {
            $request->merge( [
                'user_id' => \Helper::decode( $request->user_id )
            ] );
        }
        if( !empty( $request->id ) ) {
            $request->merge( [
                'id' => \Helper::decode( $request->id )
            ] );
        }

        $validator = Validator::make( $request->all(), [
            'user_id' => [ 'required', 'exists:users,id', function ( $attribute, $value, $fail ) {
                $user_subscription = UserSubscription::where( 'user_id', auth()->user()->id )
                    ->isActive()
                    ->isGroup()
                    ->notHitMaxMember()
                    ->first();

                if ( !$user_subscription ) {
                    $fail( 'You does not have an active group subscription or group is full.' );
                    return;
                }

                $alreadyInGroup = SubscriptionGroupMember::where( 'user_id', $value )
                    ->exists();
                if ( $alreadyInGroup ) {
                    $fail( 'User is already in another group.' );
                    return;
                }
            } ],
        ] );

        $attributeName = [
            'user_id' => __( 'subscription_group_member.user' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();

        try {

            $subscriptionGroupMemberUpdate = SubscriptionGroupMember::lockForUpdate()->find( $request->id );
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

}