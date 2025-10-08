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
    Announcement
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

}
