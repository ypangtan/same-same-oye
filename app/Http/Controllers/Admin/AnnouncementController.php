<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AnnouncementService,
};

class AnnouncementController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.announcements' );
        $this->data['content'] = 'admin.announcement.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.announcements' ),
                'class' => 'active',
            ],
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
            '21' => __( 'datatables.expired' ),
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'voucher.percentage' ),
            '2' => __( 'voucher.fixed_amount' ),
            '3' => __( 'voucher.free_cup' ),
        ];

        $this->data['data']['voucher_type'] = [
            '2' => __( 'announcement.user_specific_voucher' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.announcements' ) ) ] );
        $this->data['content'] = 'admin.announcement.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.announcement.index' ),
                'text' => __( 'template.announcements' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.announcements' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'voucher.percentage' ),
            '2' => __( 'voucher.fixed_amount' ),
            '3' => __( 'voucher.free_cup' ),
        ];

        $this->data['data']['voucher_type'] = [
            '2' => __( 'announcement.user_specific_voucher' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.announcements' ) ) ] );
        $this->data['content'] = 'admin.announcement.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.announcement.index' ),
                'text' => __( 'template.announcements' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.announcements' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'voucher.percentage' ),
            '2' => __( 'voucher.fixed_amount' ),
            '3' => __( 'voucher.free_cup' ),
        ];

        $this->data['data']['voucher_type'] = [
            '2' => __( 'announcement.user_specific_voucher' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allAnnouncements( Request $request ) {

        return AnnouncementService::allAnnouncements( $request );
    }

    public function oneAnnouncement( Request $request ) {

        return AnnouncementService::oneAnnouncement( $request );
    }

    public function createAnnouncement( Request $request ) {

        return AnnouncementService::createAnnouncement( $request );
    }

    public function updateAnnouncement( Request $request ) {

        return AnnouncementService::updateAnnouncement( $request );
    }

    public function updateAnnouncementStatus( Request $request ) {

        return AnnouncementService::updateAnnouncementStatus( $request );
    }

    public function removeAnnouncementGalleryImage( Request $request ) {

        return AnnouncementService::removeAnnouncementGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return AnnouncementService::ckeUpload( $request );
    }
    
}
