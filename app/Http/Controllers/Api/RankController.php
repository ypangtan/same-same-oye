<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    RankService,
    VoucherService
};

use App\Models\{
    Rank
};

class RankController extends Controller
{
    /**
     * 1. Get ranks 
     * 
     * <aside class="notice">Get all rank filtered, claim the promotion with claim voucher api</aside>
     * 
     * @authenticated
     * 
     * @group Rank API
     * 
     * @queryParam show_claimed integer To show claimed rank . Example: 1
     * 
     */
    public function getRanks( Request $request ) {

        return RankService::getRanks( $request );
    }

    /**
     * 2. Close/Claim Rank 
     * 
     * @authenticated
     * 
     * <aside class="notice">Marked the rank as read, claim any promotion inside</aside>
     * 
     * 
     * @group Rank API
     * 
     * @bodyParam rank required integer The id of rank to be claim. Example: 1
     * 
     */
    public function claim( Request $request ) {

        $validator = Validator::make( $request->all(), [
            'rank' => [ 'required' ],
        ] );

        $attributeName = [
            'rank' => __( 'template.rank' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->after(function ($validator) use ($request) {
            $rank = Rank::find( $request->rank );
            
            if ( !$rank ) {
                $validator->errors()->add('rank', __( 'template.rank_not_found' ));
            }
        });
        
        // Set attribute names and validate
        $validator->setAttributeNames( $attributeName )->validate();
        $rank = Rank::find( $request->rank );
        if( $rank->voucher_id ) {

            $request->merge( [
                'voucher_id' => $rank->voucher_id
            ] );

           return VoucherService::claimVoucher( $request );

        } else {
            RankView::create( [
                'user_id' => auth()->user()->id,
                'rank_id' => $rank->id,
            ] );

            return response()->json( [
                'message' => __('rank.close'),
                'message_key' => 'close',
            ] );
        }


    }

}
