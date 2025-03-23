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
     * @queryParam show_claimed integer To show claimed announcement . Example: 1
     * 
     */
    public function getAnnouncements( Request $request ) {

        return AnnouncementService::getAnnouncements( $request );
    }

    /**
     * 2. Close/Claim Announcement 
     * 
     * @authenticated
     * 
     * <aside class="notice">Marked the announcement as read, claim any promotion inside</aside>
     * 
     * 
     * @group Announcement API
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
            
            if ( !$announcement ) {
                $validator->errors()->add('announcement', __( 'template.announcement_not_found' ));
            }
        });
        
        // Set attribute names and validate
        $validator->setAttributeNames( $attributeName )->validate();
        $announcement = Announcement::find( $request->announcement );
        if( $announcement->voucher_id ) {

            $request->merge( [
                'voucher_id' => $announcement->voucher_id
            ] );

           return VoucherService::claimVoucher( $request );

        } else {
            AnnouncementView::create( [
                'user_id' => auth()->user()->id,
                'announcement_id' => $announcement->id,
            ] );

            return response()->json( [
                'message' => __('announcement.close'),
                'message_key' => 'close',
            ] );
        }


    }

}
