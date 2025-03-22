<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    AnnouncementService,
    VoucherService
};

use App\Models\{
    Announcement
};

class AnnouncementController extends Controller
{
    /**
     * 1. Get announcements 
     * 
     * <aside class="notice">Get all announcement filtered, claim the promotion with claim voucher api</aside>
     * 
     * @authenticated
     * 
     * @group Announcement API
     * 
     */
    public function getAnnouncements( Request $request ) {

        return AnnouncementService::getAnnouncements( $request );
    }

    /**
     * 2. Claim Announcement 
     * 
     * @authenticated
     * 
     * @group Voucher API
     * 
     * @bodyParam announcement required integer The id of announcement to be claim. Example: 1
     * 
     */
    public function claim( Request $request ) {

        $validator = Validator::make( $request->all(), [
            'announcement' => [ 'required' ],
        ] );

        $attributeName = [
            'announcement' => __( 'template.announcement' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->after(function ($validator) use ($request) {
            $announcement = Announcement::find( $request->announcement );
            
            if ( !$announcement || is_null( $announcement->voucher_id ) ) {
                $validator->errors()->add('announcement', __( 'template.voucher_required' ));
            }
        });
        
        // Set attribute names and validate
        $validator->setAttributeNames( $attributeName )->validate();

        $request->merge( [
            'voucher_id' => Announcement::find( $request->announcement )->voucher_id
        ] );

        return VoucherService::claimVoucher( $request );
    }

}
