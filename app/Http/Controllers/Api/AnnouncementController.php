<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{
    Validator,
};

use App\Services\{
    AnnouncementService,
    PopAnnouncementService,
    VoucherService
};

use App\Models\{
    Announcement,
    PopAnnouncement,
    PopAnnouncementClick,
};

class AnnouncementController extends Controller
{
    /**
     * 1. Get all pop announcements 
     * 
     * @group Announcement API
     * 
     */
    public function getAllPopAnnouncements( Request $request ) {

        return PopAnnouncementService::getAllPopAnnouncements( $request );
    }

    public function recordClick( Request $request ) {

        $popup = PopAnnouncement::find( $request->id );

        if ( !$popup ) {
            return response()->json( [ 'message' => 'Announcement not found.' ], 404 );
        }

        PopAnnouncementClick::create( [
            'pop_announcement_id' => $popup->id,
            'user_id'             => auth()->id() ?? null,
        ] );

        return response()->json( [ 'message' => 'Click recorded.' ] );
    }

}
