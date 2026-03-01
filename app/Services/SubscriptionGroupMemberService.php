<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    Crypt,
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
            $model->where( 'subscriptionGroupMembers.id', '!=', \Helper::decode($request->id) );
            $filter = true;
        }

        if ( !empty( $request->leader_id ) ) {
            $model->where( 'subscriptionGroupMembers.leader_id', \Helper::decode($request->leader_id) );
            $filter = true;
        }

        if ( !empty( $request->user_id ) ) {
            $model->where( 'subscriptionGroupMembers.user_id', \Helper::decode($request->user_id) );
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

    // Api
    public static function getSubscriptionGroupMembers() {

        $members = SubscriptionGroupMember::with( [
            'user',
        ] )->where( 'leader_id', auth()->user()->id )
            ->orWhere( 'user_id', auth()->user()->id )
            ->first();
            
        $leader = null;
        $user_subscription = null;

        if( $members ) {
            if( $members->leader_id == auth()->user()->id ) {
                // is leader
                $leader = auth()->user();
                $user_subscription = UserSubscription::where( 'user_id', auth()->user()->id )
                    ->isActive()
                    ->isGroup()
                    ->first();

                $members = SubscriptionGroupMember::with( [
                    'user',
                ] )->where( 'leader_id', auth()->user()->id )
                    ->get();

                foreach( $members as $key => $member ) {
                    $member->append( [
                        'encrypted_id',
                    ] );
                }

            } else {
                // is member
                $members->append( [
                    'encrypted_id',
                ] );
                $leader = User::find( $members->leader_id );
                $user_subscription = UserSubscription::where( 'user_id', $members->leader_id )
                    ->isActive()
                    ->isGroup()
                    ->first();
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

        $validator = Validator::make( $request->all(), [
            'user' => [ 'required', function ( $attribute, $value, $fail ) {
                $user_subscription = UserSubscription::where( 'user_id', auth()->user()->id )
                    ->isActive()
                    ->isGroup()
                    ->notHitMaxMember()
                    ->first();

                if ( !$user_subscription ) {
                    $fail( __( 'subscription_group_member.not_active_group_subscription' ) );
                    return;
                }

                $user = User::where( 'email', $value )->where( 'status', 10 )->first();
                if ( !$user ) {
                    $fail( __( 'subscription_group_member.user_not_found' ) );
                    return;
                }

                $alreadyInGroup = SubscriptionGroupMember::where( 'user_id', $value )
                    ->exists();
                if ( $alreadyInGroup ) {
                    $fail( __( 'subscription_group_member.user_already_in_group' ) );
                    return;
                }
            } ],
        ] );


        $attributeName = [
            'user' => __( 'subscription_group_member.user' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $user = User::where( 'email', $request->user )->where( 'status', 10 )->first();
            $subscriptionGroupMemberCreate = SubscriptionGroupMember::create([
                'user_id' => $user->id,
                'leader_id' => auth()->user()->id,
                'status' => 1,
            ]);
            
            $plan = auth()->user()->subscriptions()->isActive()->first();
            $subscription_plan = $plan ? $plan->plan()->first() : null;

            // send invite notification to user
            $data = [
                'email' => $user->email,
                'name' => $user->name,
                'plan_name' => $subscription_plan ? $subscription_plan->name : null,
                'sender_name' => auth()->user()->name,
                'invitation_link' => config( 'services.deeplink.deeplink_url' ) . '?token=' . Crypt::encryptString( $subscriptionGroupMemberCreate->id ),
                'type' => 4,
            ];
            $service = new MailService( $data );
            $result = $service->send();
            if( !$result || !isset( $result['status'] ) || $result['status'] != 200 ) {
                DB::rollback();
                return response()->json([
                    'message' => __('user.send_mail_fail'),
                    'message_key' => 'send_mail_failed',
                    'data' => null,
                ], 500 );
            }

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

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'user' => [ 'required', function ( $attribute, $value, $fail ) {
                $user_subscription = UserSubscription::where( 'user_id', auth()->user()->id )
                    ->isActive()
                    ->isGroup()
                    ->first();

                if ( !$user_subscription ) {
                    $fail( __( 'subscription_group_member.not_active_group_subscription' ) );
                    return;
                }

                $groupMember = SubscriptionGroupMember::find( request()->id );
                if ( !$groupMember ) {
                    $fail( __( 'subscription_group_member.not_found' ) );
                    return;
                }

                if ( $groupMember->leader_id != auth()->user()->id ) {
                    $fail( __( 'subscription_group_member.not_leader' ) );
                    return;
                }

                $user = User::where( 'email', $value )->where( 'status', 10 )->first();
                if ( !$user ) {
                    $fail( __( 'subscription_group_member.user_not_found' ) );
                    return;
                }

                $alreadyInGroup = SubscriptionGroupMember::where( 'user_id', $value )
                    ->exists();
                if ( $alreadyInGroup ) {
                    $fail( __( 'subscription_group_member.user_already_in_group' ) );
                    return;
                }
            } ],
        ] );


        $attributeName = [
            'user' => __( 'subscription_group_member.user' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $user = User::where( 'email', $request->user )->where( 'status', 10 )->first();

            $subscriptionGroupMemberCreate = SubscriptionGroupMember::find( $request->id );
            $subscriptionGroupMemberCreate->update( [
                'user_id' => $user->id,
                'status' => 1,
            ] );

            $plan = auth()->user()->subscriptions()->isActive()->first();
            $subscription_plan = $plan ? $plan->plan()->first() : null;

            // send invite notification to user
            $data = [
                'email' => $user->email,
                'name' => $user->name,
                'plan_name' => $subscription_plan ? $subscription_plan->name : null,
                'sender_name' => auth()->user()->name,
                'invitation_link' => config( 'services.deeplink.deeplink_url' ) . '?token=' . Crypt::encryptString( $subscriptionGroupMemberCreate->id ),
                'type' => 4,
            ];
            $service = new MailService( $data );
            $result = $service->send();
            if( !$result || !isset( $result['status'] ) || $result['status'] != 200 ) {
                DB::rollback();
                return response()->json([
                    'message' => __('user.send_mail_fail'),
                    'message_key' => 'send_mail_failed',
                    'data' => null,
                ], 500 );
            }

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

    public static function acceptSubscriptionGroupMember( $request ) {
        
        try {
            $request->merge( [
                'token' => Crypt::decryptString( $request->token ),
            ] );
        } catch ( \Throwable $th ) {
            return response()->json( [
                'message' => __( 'validation.header_message' ),
                'errors' => [
                    'token' => [
                        __( 'subscription_group_member.invalid_invite' ),
                    ],
                ]
            ], 422 );
        }


        DB::beginTransaction();

        try {
            $subscriptionGroupMember = SubscriptionGroupMember::find( $request->token );
            if ( !$subscriptionGroupMember ) {
                return response()->json( [
                    'message' => __( 'validation.header_message' ),
                    'errors' => [
                        'token' => [
                            __( 'subscription_group_member.invalid_invite' ),
                        ],
                    ]
                ], 422 );
            }
            $subscriptionGroupMember->update( [
                'status' => 10,
            ] );
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'subscription_group_members.accepted' ),
        ] );
    }

}