<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Models\{
    FileManager,
    SubscriptionVoucher,
    SubscriptionVoucherCode,
    SubscriptionVoucherUrl,
    User,
    UserNotification,
    UserNotificationUser,
    UserSubscription,
};

use Helper;

use Carbon\Carbon;

class SubscriptionVoucherService
{
    const CODE_PLACEHOLDER = '{voucher_code}';

    const URL_PLACEHOLDER = '{voucher_url}';

    // ---------------------------------------------------------------
    // Vouchers
    // ---------------------------------------------------------------

    public static function allSubscriptionVouchers( $request ) {

        $subscriptionVoucher = SubscriptionVoucher::select( 'subscription_vouchers.*' )
            ->withCount( [
                'codes as issued_count' => function( $query ) {
                    $query->whereNotNull( 'user_id' );
                },
                'codes as claimed_count' => function( $query ) {
                    $query->where( 'status', 10 );
                },
                'urls as url_count',
            ] );

        $filterObject = self::filter( $request, $subscriptionVoucher );
        $subscriptionVoucher = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            $subscriptionVoucher->orderBy( 'created_at', $dir );
        }

        $subscriptionVoucherCount = $subscriptionVoucher->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $subscriptionVouchers = $subscriptionVoucher->skip( $offset )->take( $limit )->get();

        if ( $subscriptionVouchers ) {
            $subscriptionVouchers->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = SubscriptionVoucher::count();

        $data = [
            'subscription_vouchers' => $subscriptionVouchers,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $subscriptionVoucherCount : $totalRecord,
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

                $model->whereBetween( 'subscription_vouchers.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'subscription_vouchers.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'subscription_vouchers.name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->type ) ) {
            $model->where( 'subscription_vouchers.type', $request->type );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'subscription_vouchers.status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneSubscriptionVoucher( $request ) {

        $subscriptionVoucher = SubscriptionVoucher::withCount( [
            'codes as issued_count' => function( $query ) {
                $query->whereNotNull( 'user_id' );
            },
            'codes as claimed_count' => function( $query ) {
                $query->where( 'status', 10 );
            },
        ] )->find( Helper::decode( $request->id ) );

        if ( $subscriptionVoucher ) {
            $subscriptionVoucher->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $subscriptionVoucher );
    }

    private static function validateSubscriptionVoucher( $request, $subscriptionVoucher = null ) {

        // Type can't be changed after created
        if ( $subscriptionVoucher ) {
            $request->merge( [
                'type' => $subscriptionVoucher->type,
            ] );
        }

        $request->merge( [
            'universal_code' => strtoupper( trim( $request->universal_code ?? '' ) ),
        ] );

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required', 'max:255' ],
            'type' => [ 'required', 'in:' . SubscriptionVoucher::TYPE_SUBSCRIPTION . ',' . SubscriptionVoucher::TYPE_THIRD_PARTY ],
            'description' => [ 'nullable' ],
            'duration_in_months' => [ 'exclude_unless:type,' . SubscriptionVoucher::TYPE_SUBSCRIPTION, 'required', 'integer', 'min:1', 'max:120' ],
            'quantity' => [ 'exclude_unless:type,' . SubscriptionVoucher::TYPE_SUBSCRIPTION, 'required', 'integer', 'min:1', 'max:10000', function( $attribute, $value, $fail ) use ( $subscriptionVoucher ) {
                if ( $subscriptionVoucher && $value < $subscriptionVoucher->codes()->used()->count() ) {
                    $fail( __( 'subscription_voucher.quantity_less_than_issued' ) );
                }
            } ],
            'has_universal_code' => [ 'exclude_unless:type,' . SubscriptionVoucher::TYPE_SUBSCRIPTION, 'nullable', 'boolean' ],
            // Empty = auto generate. Can't be same as other voucher's universal code or any single code
            'universal_code' => [ 'exclude_unless:type,' . SubscriptionVoucher::TYPE_SUBSCRIPTION, 'exclude_unless:has_universal_code,1', 'nullable', 'alpha_num', 'min:4', 'max:32',
                Rule::unique( 'subscription_vouchers', 'universal_code' )->ignore( $subscriptionVoucher ? $subscriptionVoucher->id : null ),
                Rule::unique( 'subscription_voucher_codes', 'code' ),
            ],
            'start_date' => [ 'nullable', 'date' ],
            'end_date' => [ 'nullable', 'date', 'after_or_equal:start_date' ],
        ] );

        $attributeName = [
            'name' => __( 'subscription_voucher.name' ),
            'type' => __( 'subscription_voucher.type' ),
            'description' => __( 'subscription_voucher.description' ),
            'duration_in_months' => __( 'subscription_voucher.duration_in_months' ),
            'quantity' => __( 'subscription_voucher.quantity' ),
            'universal_code' => __( 'subscription_voucher.universal_code' ),
            'start_date' => __( 'subscription_voucher.start_date' ),
            'end_date' => __( 'subscription_voucher.end_date' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
    }

    public static function createSubscriptionVoucher( $request ) {

        self::validateSubscriptionVoucher( $request );

        DB::beginTransaction();

        try {

            $isThirdParty = $request->type == SubscriptionVoucher::TYPE_THIRD_PARTY;

            $createSubscriptionVoucher = SubscriptionVoucher::create( [
                'name' => $request->name,
                'type' => $request->type,
                'description' => $request->description,
                // Third party: no duration, capacity decided by urls
                'duration_in_months' => $isThirdParty ? 0 : $request->duration_in_months,
                'quantity' => $isThirdParty ? 0 : $request->quantity,
                'universal_code' => self::resolveUniversalCode( $request, $isThirdParty ),
                'start_date' => $request->start_date ?: null,
                'end_date' => $request->end_date ?: null,
                'status' => 10,
            ] );

            self::syncCodes( $createSubscriptionVoucher );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.subscription_vouchers' ) ) ] ),
            'third_party_url' => $isThirdParty ? route( 'admin.subscription_voucher.urls', [ 'voucher' => $createSubscriptionVoucher->encrypted_id ] ) : null,
        ] );
    }

    public static function updateSubscriptionVoucher( $request ) {

        $updateSubscriptionVoucher = SubscriptionVoucher::find( Helper::decode( $request->id ) );

        self::validateSubscriptionVoucher( $request, $updateSubscriptionVoucher );

        DB::beginTransaction();

        try {

            $updateSubscriptionVoucher->name = $request->name;
            $updateSubscriptionVoucher->description = $request->description;
            if ( !$updateSubscriptionVoucher->isThirdParty() ) {
                $updateSubscriptionVoucher->duration_in_months = $request->duration_in_months;
                $updateSubscriptionVoucher->quantity = $request->quantity;
            }
            $updateSubscriptionVoucher->universal_code = self::resolveUniversalCode( $request, $updateSubscriptionVoucher->isThirdParty() );
            $updateSubscriptionVoucher->start_date = $request->start_date ?: null;
            $updateSubscriptionVoucher->end_date = $request->end_date ?: null;
            $updateSubscriptionVoucher->save();

            self::syncCodes( $updateSubscriptionVoucher );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.subscription_vouchers' ) ) ] ),
        ] );
    }

    // Universal code: admin key in, empty = auto generate
    private static function resolveUniversalCode( $request, $isThirdParty ) {

        if ( $isThirdParty || !$request->boolean( 'has_universal_code' ) ) {
            return null;
        }

        return $request->universal_code ?: SubscriptionVoucherCode::generateUniqueCode();
    }

    /**
     * Keep total codes = quantity. More quantity generate new codes,
     * less quantity delete unassigned codes ( used codes are protected by validation ).
     */
    private static function syncCodes( $subscriptionVoucher ) {

        if ( $subscriptionVoucher->isThirdParty() ) {
            return;
        }

        $diff = $subscriptionVoucher->quantity - $subscriptionVoucher->codes()->count();

        if ( $diff > 0 ) {

            $now = now();
            $codes = [];
            while ( count( $codes ) < $diff ) {
                $code = SubscriptionVoucherCode::generateUniqueCode();
                $codes[ $code ] = [
                    'subscription_voucher_id' => $subscriptionVoucher->id,
                    'code' => $code,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach ( array_chunk( array_values( $codes ), 500 ) as $chunk ) {
                SubscriptionVoucherCode::insert( $chunk );
            }

        } else if ( $diff < 0 ) {

            $ids = $subscriptionVoucher->codes()
                ->unassigned()
                ->orderBy( 'id', 'DESC' )
                ->limit( abs( $diff ) )
                ->pluck( 'id' );

            SubscriptionVoucherCode::whereIn( 'id', $ids )->delete();
        }
    }

    public static function updateSubscriptionVoucherStatus( $request ) {

        $updateSubscriptionVoucher = SubscriptionVoucher::find( Helper::decode( $request->id ) );
        $updateSubscriptionVoucher->status = $request->status;
        $updateSubscriptionVoucher->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.subscription_vouchers' ) ) ] ),
        ] );
    }

    // ---------------------------------------------------------------
    // Voucher Codes
    // ---------------------------------------------------------------

    public static function allSubscriptionVoucherCodes( $request ) {

        // Third party voucher has no code, see urls instead
        $subscriptionVoucherCode = SubscriptionVoucherCode::with( [
            'voucher',
            'user',
        ] )->select( 'subscription_voucher_codes.*' )
            ->whereNotNull( 'code' );

        $filter = false;

        if ( !empty( $request->subscription_voucher ) ) {
            $subscriptionVoucherCode->where( 'subscription_voucher_id', Helper::decode( $request->subscription_voucher ) );
            $filter = true;
        }

        if ( !empty( $request->code ) ) {
            $subscriptionVoucherCode->where( 'code', 'LIKE', '%' . strtoupper( $request->code ) . '%' );
            $filter = true;
        }

        if ( !empty( $request->user ) ) {
            $userInput = $request->user;
            $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $userInput );

            $subscriptionVoucherCode->whereHas( 'user', function( $query ) use ( $normalizedPhone, $userInput ) {
                $query->where( 'users.phone_number', 'LIKE', "%$normalizedPhone%" )
                    ->orWhere( 'users.email', 'LIKE', "%$userInput%" );
            } );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $subscriptionVoucherCode->where( 'status', $request->status );
            $filter = true;
        }

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            $subscriptionVoucherCode->orderBy( 'created_at', $dir );
        }

        $subscriptionVoucherCodeCount = $subscriptionVoucherCode->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $subscriptionVoucherCodes = $subscriptionVoucherCode->skip( $offset )->take( $limit )->get();

        if ( $subscriptionVoucherCodes ) {
            $subscriptionVoucherCodes->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = SubscriptionVoucherCode::whereNotNull( 'code' )->count();

        $data = [
            'subscription_voucher_codes' => $subscriptionVoucherCodes,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $subscriptionVoucherCodeCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    // Void an available code, or restore a voided code. Claimed code can't be changed.
    public static function updateSubscriptionVoucherCodeStatus( $request ) {

        $updateSubscriptionVoucherCode = SubscriptionVoucherCode::find( Helper::decode( $request->id ) );

        if ( !$updateSubscriptionVoucherCode || $updateSubscriptionVoucherCode->status == 10 || !in_array( $request->status, [ 1, 20 ] ) ) {
            return response()->json( [
                'message' => __( 'subscription_voucher.code_cannot_update' ),
            ], 500 );
        }

        $updateSubscriptionVoucherCode->status = $request->status;
        $updateSubscriptionVoucherCode->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => __( 'subscription_voucher.code' ) ] ),
        ] );
    }

    // ---------------------------------------------------------------
    // Third Party Voucher Urls
    // ---------------------------------------------------------------

    public static function allSubscriptionVoucherUrls( $request ) {

        // select() must come before withCount(), otherwise it overrides the count column
        $subscriptionVoucherUrl = SubscriptionVoucherUrl::select( 'subscription_voucher_urls.*' )
            ->withCount( 'codes as assigned_count' )
            ->where( 'subscription_voucher_id', Helper::decode( $request->subscription_voucher ) );

        $filter = false;

        if ( !empty( $request->url ) ) {
            $subscriptionVoucherUrl->where( 'url', 'LIKE', '%' . $request->url . '%' );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $subscriptionVoucherUrl->where( 'status', $request->status );
            $filter = true;
        }

        $subscriptionVoucherUrl->orderBy( 'id', 'ASC' );

        $subscriptionVoucherUrlCount = $subscriptionVoucherUrl->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $subscriptionVoucherUrls = $subscriptionVoucherUrl->skip( $offset )->take( $limit )->get();

        if ( $subscriptionVoucherUrls ) {
            $subscriptionVoucherUrls->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = SubscriptionVoucherUrl::where( 'subscription_voucher_id', Helper::decode( $request->subscription_voucher ) )->count();

        $data = [
            'subscription_voucher_urls' => $subscriptionVoucherUrls,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $subscriptionVoucherUrlCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    // Multi add, one url per line
    public static function createSubscriptionVoucherUrls( $request ) {

        $urls = collect( preg_split( '/\r\n|\r|\n/', $request->urls ?? '' ) )
            ->map( function( $url ) {
                return trim( $url );
            } )
            ->filter()
            ->values();

        $validator = Validator::make( [
            'subscription_voucher' => $request->subscription_voucher,
            'urls' => $urls->all(),
            'max_users' => $request->max_users,
        ], [
            'subscription_voucher' => [ 'required' ],
            'urls' => [ 'required', 'array', 'max:1000' ],
            'urls.*' => [ 'url', 'max:2000' ],
            'max_users' => [ 'nullable', 'integer', 'min:1' ],
        ], [
            'urls.*.url' => __( 'subscription_voucher.invalid_url_line' ),
        ] );

        $validator->setAttributeNames( [
            'subscription_voucher' => strtolower( __( 'subscription_voucher.voucher' ) ),
            'urls' => strtolower( __( 'subscription_voucher.urls' ) ),
            'max_users' => strtolower( __( 'subscription_voucher.max_users' ) ),
        ] );

        // Show which line is invalid, all errors under "urls"
        if ( $validator->fails() ) {
            $errors = [];
            foreach ( $validator->errors()->messages() as $key => $messages ) {
                if ( str_starts_with( $key, 'urls.' ) ) {
                    $line = (int) explode( '.', $key )[1] + 1;
                    $errors['urls'][] = __( 'subscription_voucher.line_x', [ 'line' => $line ] ) . ': ' . $messages[0];
                } else {
                    $errors[ $key ][] = $messages[0];
                }
            }
            if ( isset( $errors['urls'] ) ) {
                $errors['urls'] = [ implode( ' ', $errors['urls'] ) ];
            }
            throw ValidationException::withMessages( $errors );
        }

        $subscriptionVoucher = SubscriptionVoucher::find( Helper::decode( $request->subscription_voucher ) );

        if ( !$subscriptionVoucher || !$subscriptionVoucher->isThirdParty() ) {
            throw ValidationException::withMessages( [
                'urls' => __( 'subscription_voucher.not_third_party' ),
            ] );
        }

        DB::beginTransaction();

        try {

            foreach ( $urls as $url ) {
                SubscriptionVoucherUrl::create( [
                    'subscription_voucher_id' => $subscriptionVoucher->id,
                    'url' => $url,
                    'max_users' => $request->max_users ?: null,
                    'status' => 10,
                ] );
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'subscription_voucher.urls_added', [ 'total' => $urls->count() ] ),
        ] );
    }

    // Multi edit, items: [ { id, url, max_users } ]
    public static function updateSubscriptionVoucherUrls( $request ) {

        $items = collect( $request->items ?? [] )->map( function( $item ) {
            return [
                'id' => $item['id'] ?? null,
                'url' => trim( $item['url'] ?? '' ),
                'max_users' => ( $item['max_users'] ?? '' ) === '' ? null : $item['max_users'],
            ];
        } );

        $subscriptionVoucherUrls = SubscriptionVoucherUrl::withCount( 'codes as assigned_count' )
            ->whereIn( 'id', $items->pluck( 'id' )->filter()->map( function( $id ) {
                return Helper::decode( $id );
            } ) )
            ->get()
            ->keyBy( 'encrypted_id' );

        $validator = Validator::make( [ 'items' => $items->all() ], [
            'items' => [ 'required', 'array' ],
            'items.*.id' => [ 'required', function( $attribute, $value, $fail ) use ( $subscriptionVoucherUrls ) {
                if ( !isset( $subscriptionVoucherUrls[ $value ] ) ) {
                    $fail( __( 'subscription_voucher.url_not_found' ) );
                }
            } ],
            'items.*.url' => [ 'required', 'url', 'max:2000' ],
            'items.*.max_users' => [ 'nullable', 'integer', 'min:1' ],
        ] );

        // Url already assigned to user can't be edited
        $validator->after( function( $validator ) use ( $items, $subscriptionVoucherUrls ) {
            foreach ( $items as $index => $item ) {
                $url = $subscriptionVoucherUrls[ $item['id'] ] ?? null;
                if ( $url && $url->assigned_count > 0 ) {
                    $validator->errors()->add( "items.$index.url", __( 'subscription_voucher.url_assigned_cannot_edit' ) );
                }
            }
        } );

        $validator->setAttributeNames( [
            'items.*.url' => strtolower( __( 'subscription_voucher.url' ) ),
            'items.*.max_users' => strtolower( __( 'subscription_voucher.max_users' ) ),
        ] )->validate();

        DB::beginTransaction();

        try {

            foreach ( $items as $item ) {
                $updateSubscriptionVoucherUrl = $subscriptionVoucherUrls[ $item['id'] ];
                $updateSubscriptionVoucherUrl->url = $item['url'];
                $updateSubscriptionVoucherUrl->max_users = $item['max_users'];
                $updateSubscriptionVoucherUrl->save();
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => __( 'subscription_voucher.urls' ) ] ),
        ] );
    }

    // Multi status update, ids: [ encrypted_id ]
    public static function updateSubscriptionVoucherUrlStatus( $request ) {

        $ids = collect( $request->ids ?? [] )->map( function( $id ) {
            return Helper::decode( $id );
        } );

        SubscriptionVoucherUrl::whereIn( 'id', $ids )->get()->each( function( $url ) use ( $request ) {
            $url->status = $request->status == 10 ? 10 : 20;
            $url->save();
        } );

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => __( 'subscription_voucher.urls' ) ] ),
        ] );
    }

    // Multi delete, only url not assigned to any user can be deleted
    public static function deleteSubscriptionVoucherUrls( $request ) {

        $ids = collect( $request->ids ?? [] )->map( function( $id ) {
            return Helper::decode( $id );
        } );

        $subscriptionVoucherUrls = SubscriptionVoucherUrl::withCount( 'codes as assigned_count' )
            ->whereIn( 'id', $ids )
            ->get();

        if ( $subscriptionVoucherUrls->where( 'assigned_count', '>', 0 )->isNotEmpty() ) {
            return response()->json( [
                'message' => __( 'subscription_voucher.url_assigned_cannot_delete' ),
            ], 500 );
        }

        $subscriptionVoucherUrls->each( function( $url ) {
            $url->delete();
        } );

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => __( 'subscription_voucher.urls' ) ] ),
        ] );
    }

    // ---------------------------------------------------------------
    // Voucher Notifications
    // ---------------------------------------------------------------

    public static function allVoucherNotifications( $request ) {

        // select() must come before withCount(), otherwise it overrides the count columns
        $notification = UserNotification::with( [
            'subscriptionVoucher',
        ] )->select( 'user_notifications.*' )
            ->withCount( [
                'subscriptionVoucherCodes as recipient_count',
                'subscriptionVoucherCodes as claimed_count' => function( $query ) {
                    $query->where( 'status', 10 );
                },
            ] )
            ->whereNotNull( 'user_notifications.subscription_voucher_id' );

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );

                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );
            }

            $notification->whereBetween( 'user_notifications.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $notification->where( 'user_notifications.en_title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->subscription_voucher ) ) {
            $notification->whereHas( 'subscriptionVoucher', function( $query ) use ( $request ) {
                $query->where( 'name', 'LIKE', '%' . $request->subscription_voucher . '%' );
            } );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $notification->where( 'user_notifications.status', $request->status );
            $filter = true;
        }

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' ) ?? 'DESC';
            $notification->orderBy( 'created_at', $dir );
        }

        $notificationCount = $notification->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $notifications = $notification->skip( $offset )->take( $limit )->get();

        $totalRecord = UserNotification::whereNotNull( 'subscription_voucher_id' )->count();

        $data = [
            'notifications' => $notifications,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $notificationCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    public static function oneVoucherNotification( $request ) {

        $notification = UserNotification::with( [
            'subscriptionVoucher',
            'subscriptionVoucherCodes.user',
            'subscriptionVoucherCodes.url',
        ] )->whereNotNull( 'subscription_voucher_id' )
            ->find( Helper::decode( $request->id ) );

        if ( $notification ) {
            $notification->append( [
                'path',
            ] );

            if ( $notification->subscriptionVoucher ) {
                $notification->subscriptionVoucher->append( [
                    'encrypted_id',
                ] );
            }
        }

        return response()->json( $notification );
    }

    private static function validateVoucherNotification( $request, $isCreate ) {

        $rules = [
            'en_title' => [ 'required' ],
            'en_content' => [ 'required' ],
            'image' => [ 'nullable' ],
            'publishing_date' => [ 'nullable', 'date' ],
        ];

        if ( $isCreate ) {
            $rules['subscription_voucher'] = [ 'required' ];
            $rules['users'] = [ 'required' ];
        }

        $validator = Validator::make( $request->all(), $rules );

        $attributeName = [
            'subscription_voucher' => __( 'subscription_voucher.voucher' ),
            'en_title' => __( 'datatables.title' ),
            'en_content' => __( 'subscription_voucher.content' ),
            'image' => __( 'subscription_voucher.image' ),
            'users' => __( 'template.users' ),
            'publishing_date' => __( 'subscription_voucher.publishing_date' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
    }

    /**
     * Create one notification for all selected users, but each user get his own voucher code.
     * Content can use {voucher_code}, it will be replaced by the user's code.
     */
    public static function createVoucherNotification( $request ) {

        self::validateVoucherNotification( $request, true );

        $userIds = collect( explode( ',', $request->users ) )
            ->filter()
            ->map( function( $encryptedId ) {
                return Helper::decode( $encryptedId );
            } )
            ->unique()
            ->values();

        $users = User::whereIn( 'id', $userIds )->get();

        $isScheduled = self::isFutureDate( $request->publishing_date );

        DB::beginTransaction();

        try {

            // Lock voucher so concurrent sending won't exceed quantity
            $subscriptionVoucher = SubscriptionVoucher::lockForUpdate()->findOrFail( Helper::decode( $request->subscription_voucher ) );

            if ( $subscriptionVoucher->status != 10 ) {
                throw ValidationException::withMessages( [
                    'subscription_voucher' => __( 'subscription_voucher.voucher_not_active' ),
                ] );
            }

            if ( $subscriptionVoucher->isThirdParty() ) {
                $urlSlots = self::allocateUrls( $subscriptionVoucher, $users->count() );
            } else if ( $users->count() > $subscriptionVoucher->remainingQuantity() ) {
                throw ValidationException::withMessages( [
                    'users' => __( 'subscription_voucher.not_enough_quantity', [ 'remaining' => $subscriptionVoucher->remainingQuantity() ] ),
                ] );
            }

            $alreadyReceived = SubscriptionVoucherCode::with( 'user' )
                ->where( 'subscription_voucher_id', $subscriptionVoucher->id )
                ->whereIn( 'user_id', $users->pluck( 'id' ) )
                ->where( 'status', '!=', 20 )
                ->get();

            if ( $alreadyReceived->isNotEmpty() ) {
                throw ValidationException::withMessages( [
                    'users' => __( 'subscription_voucher.users_already_received', [
                        'users' => $alreadyReceived->map( function( $code ) {
                            return $code->user->email ?? $code->user->phone_number;
                        } )->implode( ', ' ),
                    ] ),
                ] );
            }

            $createNotification = UserNotification::create( [
                'subscription_voucher_id' => $subscriptionVoucher->id,
                'type' => 2,
                'en_title' => $request->en_title,
                'en_content' => $request->en_content,
                'is_broadcast' => 0,
                'url_slug' => \Str::slug( $request->en_title ),
                'key' => 'subscription_voucher',
                'system_title' => NULL,
                'system_content' => NULL,
                'meta_data' => NULL,
                'publishing_date' => $request->publishing_date ?: null,
                // 1: scheduled, push by notifications:send-scheduled when publishing date arrived
                'status' => $isScheduled ? 1 : 10,
            ] );

            $file = FileManager::find( $request->image );
            if ( $file ) {
                $createNotification->image = $file->file;
                $createNotification->save();

                $file->status = 10;
                $file->save();
            }

            // Give out pre-generated codes first, create new one only if not enough ( old voucher without pre-generated codes )
            $unassignedCodes = $subscriptionVoucher->isThirdParty() ? collect() : $subscriptionVoucher->codes()
                ->unassigned()
                ->orderBy( 'id', 'ASC' )
                ->lockForUpdate()
                ->take( $users->count() )
                ->get();

            $codes = [];
            foreach ( $users->values() as $index => $user ) {

                UserNotificationUser::create( [
                    'user_notification_id' => $createNotification->id,
                    'user_id' => $user->id,
                ] );

                $unassignedCode = $unassignedCodes->shift();
                if ( $unassignedCode ) {
                    $unassignedCode->user_id = $user->id;
                    $unassignedCode->user_notification_id = $createNotification->id;
                    $unassignedCode->save();

                    $codes[ $user->id ] = $unassignedCode->setRelation( 'url', null );
                    continue;
                }

                $codes[ $user->id ] = SubscriptionVoucherCode::create( [
                    'subscription_voucher_id' => $subscriptionVoucher->id,
                    'user_id' => $user->id,
                    'user_notification_id' => $createNotification->id,
                    'code' => $subscriptionVoucher->isThirdParty() ? null : SubscriptionVoucherCode::generateUniqueCode(),
                    'subscription_voucher_url_id' => $subscriptionVoucher->isThirdParty() ? $urlSlots[ $index ]->id : null,
                    'status' => 1,
                ] )->setRelation( 'url', $subscriptionVoucher->isThirdParty() ? $urlSlots[ $index ] : null );
            }

            DB::commit();

        } catch ( ValidationException $e ) {

            DB::rollback();

            throw $e;

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        // Push after commit, so a push failure won't rollback the codes. Scheduled one push by cron later
        if ( !$isScheduled ) {
            foreach ( $codes as $userId => $code ) {
                self::sendNotification( $userId, $createNotification, $code );
            }
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.subscription_voucher_notifications' ) ) ] ),
        ] );
    }

    private static function isFutureDate( $date ) {

        return $date && Carbon::parse( $date, 'Asia/Kuala_Lumpur' )->gt( Carbon::now( 'Asia/Kuala_Lumpur' ) );
    }

    /**
     * Push scheduled voucher notification ( status 1 ) when publishing date arrived, then mark published.
     * Used by notifications:send-scheduled command.
     */
    public static function dispatchScheduledVoucherNotification( $notification ) {

        // Mark first, so overlapping cron won't push twice
        $updated = UserNotification::where( 'id', $notification->id )
            ->where( 'status', 1 )
            ->update( [ 'status' => 10 ] );

        if ( !$updated ) {
            return;
        }

        $codes = SubscriptionVoucherCode::with( 'url' )
            ->where( 'user_notification_id', $notification->id )
            ->get();

        foreach ( $codes as $code ) {
            self::sendNotification( $code->user_id, $notification, $code );
        }
    }

    /**
     * Pick a url for each user: fill the earliest url until max_users, then next url.
     * 1 url & unlimited max users = everyone same url, max users 1 = everyone different url.
     */
    private static function allocateUrls( $subscriptionVoucher, $total ) {

        $urls = SubscriptionVoucherUrl::withCount( 'codes as assigned_count' )
            ->where( 'subscription_voucher_id', $subscriptionVoucher->id )
            ->where( 'status', 10 )
            ->orderBy( 'id', 'ASC' )
            ->lockForUpdate()
            ->get();

        $slots = [];
        foreach ( $urls as $url ) {
            while ( count( $slots ) < $total && ( $url->max_users === null || $url->assigned_count < $url->max_users ) ) {
                $slots[] = $url;
                $url->assigned_count++;
            }
        }

        if ( count( $slots ) < $total ) {
            throw ValidationException::withMessages( [
                'users' => __( 'subscription_voucher.not_enough_urls', [ 'remaining' => count( $slots ) ] ),
            ] );
        }

        return $slots;
    }

    // Only content can be updated, recipients & codes are fixed once sent
    public static function updateVoucherNotification( $request ) {

        self::validateVoucherNotification( $request, false );

        DB::beginTransaction();

        try {

            $updateNotification = UserNotification::whereNotNull( 'subscription_voucher_id' )
                ->findOrFail( Helper::decode( $request->id ) );

            $updateNotification->en_title = $request->en_title;
            $updateNotification->en_content = $request->en_content;
            $updateNotification->url_slug = \Str::slug( $request->en_title );

            // Publishing date only can change before it is sent. Empty = send now ( by cron within 1 minute )
            if ( $updateNotification->status == 1 ) {
                $updateNotification->publishing_date = $request->publishing_date ?: Carbon::now( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i' );
            }

            $updateNotification->save();

            if ( $request->image ) {
                $file = FileManager::find( $request->image );
                if ( $file ) {

                    $oldImage = $updateNotification->image;
                    $updateNotification->image = $file->file;
                    $updateNotification->save();

                    $file->status = 10;
                    $file->save();

                    if ( $oldImage && Storage::disk( 'public' )->exists( $oldImage ) ) {
                        Storage::disk( 'public' )->delete( $oldImage );
                    }
                }
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.subscription_voucher_notifications' ) ) ] ),
        ] );
    }

    public static function updateVoucherNotificationStatus( $request ) {

        $updateNotification = UserNotification::whereNotNull( 'subscription_voucher_id' )
            ->find( Helper::decode( $request->id ) );

        // Scheduled one not sent yet, publish / unpublish would skip the push
        if ( $updateNotification->status == 1 ) {
            return response()->json( [
                'message' => __( 'subscription_voucher.scheduled_cannot_change_status' ),
            ], 500 );
        }

        $updateNotification->status = $request->status;
        $updateNotification->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.subscription_voucher_notifications' ) ) ] ),
        ] );
    }

    // Replace {voucher_code} / {voucher_url} with the user's own code / url
    private static function replacePlaceholders( $text, $code ) {

        return str_replace(
            [ self::CODE_PLACEHOLDER, self::URL_PLACEHOLDER ],
            [ $code->code ?? '', $code && $code->url ? $code->url->url : '' ],
            $text
        );
    }

    private static function sendNotification( $userId, $notification, $code ) {

        $raw = $notification->getAttributes();

        $enTitle = self::replacePlaceholders( $raw['en_title'], $code );
        $enContent = self::replacePlaceholders( $raw['en_content'], $code );

        $messageContent = [
            'key' => 'subscription_voucher',
            'id' => $notification->id,
            'title' => [
                'en' => $enTitle,
                'zh' => $enTitle,
            ],
            'message' => [
                'en' => $enContent,
                'zh' => $enContent,
            ],
        ];

        try {
            Helper::sendNotification( $userId, $messageContent );
        } catch ( \Throwable $th ) {
            \Log::warning( 'Subscription voucher push failed', [
                'user_id' => $userId,
                'user_notification_id' => $notification->id,
                'error' => $th->getMessage(),
            ] );
        }
    }

    /**
     * Put the user's own voucher code into voucher notifications ( replace {voucher_code} and append voucher_code ).
     * Used by client notification APIs.
     */
    public static function appendVoucherCodes( $notifications, $userId ) {

        $notifications = collect( $notifications )->filter( function( $notification ) {
            return $notification && $notification->subscription_voucher_id;
        } );

        if ( $notifications->isEmpty() ) {
            return;
        }

        $codes = SubscriptionVoucherCode::with( [ 'voucher', 'url' ] )
            ->whereIn( 'user_notification_id', $notifications->pluck( 'id' ) )
            ->where( 'user_id', $userId )
            ->get()
            ->keyBy( 'user_notification_id' );

        foreach ( $notifications as $notification ) {

            $code = $codes[ $notification->id ] ?? null;

            // English only, zh follow en so zh locale won't get empty title / content
            foreach ( [ 'title', 'content' ] as $field ) {
                $value = $notification->getAttributes()[ 'en_' . $field ] ?? null;
                if ( $value ) {
                    $value = self::replacePlaceholders( $value, $code );
                    $notification->setAttribute( 'en_' . $field, $value );
                    $notification->setAttribute( 'zh_' . $field, $value );
                }
            }

            $isThirdParty = $code && $code->voucher && $code->voucher->isThirdParty();

            // voucher_type: 1 = subscription ( claim with voucher_code ), 2 = third party ( open voucher_url )
            $notification->setAttribute( 'voucher_type', $code && $code->voucher ? $code->voucher->type : null );
            $notification->setAttribute( 'voucher_code', $code ? $code->code : null );
            $notification->setAttribute( 'voucher_url', $code && $code->url ? $code->url->url : null );
            $notification->setAttribute( 'voucher_status', $code && !$isThirdParty ? $code->status : null );
        }
    }

    // ---------------------------------------------------------------
    // Client API
    // ---------------------------------------------------------------

    public static function claimSubscriptionVoucher( $request ) {

        $request->merge( [
            'code' => strtoupper( trim( $request->code ?? '' ) ),
        ] );

        $validator = Validator::make( $request->all(), [
            'code' => [ 'required' ],
        ] );

        $validator->setAttributeNames( [
            'code' => strtolower( __( 'subscription_voucher.code' ) ),
        ] )->validate();

        DB::beginTransaction();

        try {

            // Lock user then code, so the same user can't claim twice at the same time
            $user = User::lockForUpdate()->find( auth()->user()->id );

            // Universal code: lock voucher, then give user one unassigned code from quantity
            $universalVoucher = SubscriptionVoucher::where( 'universal_code', $request->code )
                ->lockForUpdate()
                ->first();

            if ( $universalVoucher ) {

                if ( !$universalVoucher->isClaimable() ) {
                    throw ValidationException::withMessages( [
                        'code' => __( 'subscription_voucher.voucher_not_available' ),
                    ] );
                }

                // User already received own code ( e.g. by notification ): claim that code, don't take another from quantity
                $voucherCode = $universalVoucher->codes()
                    ->where( 'user_id', $user->id )
                    ->where( 'status', '!=', 20 )
                    ->orderBy( 'status', 'DESC' )
                    ->lockForUpdate()
                    ->first();

                if ( $voucherCode && $voucherCode->status == 10 ) {
                    throw ValidationException::withMessages( [
                        'code' => __( 'subscription_voucher.voucher_already_claimed' ),
                    ] );
                }

                if ( !$voucherCode ) {
                    $voucherCode = $universalVoucher->codes()
                        ->unassigned()
                        ->orderBy( 'id', 'ASC' )
                        ->lockForUpdate()
                        ->first();
                }

                if ( !$voucherCode ) {
                    throw ValidationException::withMessages( [
                        'code' => __( 'subscription_voucher.universal_code_fully_claimed' ),
                    ] );
                }

                $voucherCode->setRelation( 'voucher', $universalVoucher );

            } else {

                $voucherCode = SubscriptionVoucherCode::with( 'voucher' )
                    ->where( 'code', $request->code )
                    ->lockForUpdate()
                    ->first();
            }

            if ( !$voucherCode || $voucherCode->status == 20 || ( $voucherCode->user_id && $voucherCode->user_id != $user->id ) ) {
                throw ValidationException::withMessages( [
                    'code' => __( 'subscription_voucher.invalid_code' ),
                ] );
            }

            if ( $voucherCode->status == 10 ) {
                throw ValidationException::withMessages( [
                    'code' => __( 'subscription_voucher.code_already_claimed' ),
                ] );
            }

            if ( !$voucherCode->voucher || $voucherCode->voucher->isThirdParty() || !$voucherCode->voucher->isClaimable() ) {
                throw ValidationException::withMessages( [
                    'code' => __( 'subscription_voucher.voucher_not_available' ),
                ] );
            }

            // Only free user can use. 3 = no own plan & no group plan
            if ( $user->checkPlanValidity() != 3 ) {
                throw ValidationException::withMessages( [
                    'code' => __( 'subscription_voucher.only_free_user' ),
                ] );
            }

            $claimedSameVoucher = SubscriptionVoucherCode::where( 'subscription_voucher_id', $voucherCode->subscription_voucher_id )
                ->where( 'user_id', $user->id )
                ->where( 'status', 10 )
                ->exists();

            if ( $claimedSameVoucher ) {
                throw ValidationException::withMessages( [
                    'code' => __( 'subscription_voucher.voucher_already_claimed' ),
                ] );
            }

            $now = Carbon::now()->timezone( 'Asia/Kuala_Lumpur' );

            $userSubscription = UserSubscription::create( [
                'user_id' => $user->id,
                'subscription_plan_id' => null,
                'status' => 10,
                'start_date' => $now,
                'end_date' => $now->copy()->addMonthsNoOverflow( $voucherCode->voucher->duration_in_months ),
                'type' => 2,
            ] );

            $voucherCode->user_id = $user->id;
            $voucherCode->user_subscription_id = $userSubscription->id;
            $voucherCode->claimed_at = $now;
            $voucherCode->status = 10;
            $voucherCode->save();

            $user->checkPlanValidity();

            DB::commit();

        } catch ( ValidationException $e ) {

            DB::rollback();

            throw $e;

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'claim_subscription_voucher_failed',
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'subscription_voucher.claim_success', [ 'months' => $voucherCode->voucher->duration_in_months ] ),
            'message_key' => 'claim_subscription_voucher_success',
            'data' => [
                'code' => $voucherCode->code,
                'duration_in_months' => $voucherCode->voucher->duration_in_months,
                'start_date' => $userSubscription->start_date,
                'end_date' => $userSubscription->end_date,
            ],
        ] );
    }
}
