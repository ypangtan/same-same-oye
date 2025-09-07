<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    PopAnnouncementService,
};

class PopAnnouncementController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.pop_announcements' );
        $this->data['content'] = 'admin.pop_announcement.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.pop_announcements' ),
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
            '2' => __( 'pop_announcement.user_specific_voucher' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.pop_announcements' ) ) ] );
        $this->data['content'] = 'admin.pop_announcement.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.pop_announcement.index' ),
                'text' => __( 'template.pop_announcements' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.pop_announcements' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'voucher.percentage' ),
            '2' => __( 'voucher.fixed_amount' ),
            '3' => __( 'voucher.free_cup' ),
        ];

        $this->data['data']['voucher_type'] = [
            '2' => __( 'pop_announcement.user_specific_voucher' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.pop_announcements' ) ) ] );
        $this->data['content'] = 'admin.pop_announcement.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.pop_announcement.index' ),
                'text' => __( 'template.pop_announcements' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.pop_announcements' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'voucher.percentage' ),
            '2' => __( 'voucher.fixed_amount' ),
            '3' => __( 'voucher.free_cup' ),
        ];

        $this->data['data']['voucher_type'] = [
            '2' => __( 'pop_announcement.user_specific_voucher' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allPopAnnouncements( Request $request ) {

        return PopAnnouncementService::allPopAnnouncements( $request );
    }

    public function onePopAnnouncement( Request $request ) {

        return PopAnnouncementService::onePopAnnouncement( $request );
    }

    public function createPopAnnouncement( Request $request ) {

        return PopAnnouncementService::createPopAnnouncement( $request );
    }

    public function updatePopAnnouncement( Request $request ) {

        return PopAnnouncementService::updatePopAnnouncement( $request );
    }

    public function updatePopAnnouncementStatus( Request $request ) {

        return PopAnnouncementService::updatePopAnnouncementStatus( $request );
    }

    public function ckeUpload( Request $request ) {

        return PopAnnouncementService::ckeUpload( $request );
    }

    public function imageUpload( Request $request ) {

        return PopAnnouncementService::imageUpload( $request );
    }
    
}
