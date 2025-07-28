<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    MarketingNotificationService,
    FileManagerService,
};

class MarketingNotificationController extends Controller
{
    public function index() {

        $this->data['header']['title'] = __( 'template.marketing_notifications' );
        $this->data['content'] = 'admin.marketing_notifications.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.marketing_notifications' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.marketing_notifications' ),
        ];

        $this->data['data']['type'] = [
            '1' => __( 'announcement.news' ),
            '2' => __( 'announcement.event' ),
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
            '21' => __( 'datatables.expired' ),
        ];

        return view( 'admin.main' )->with( $this->data );   
    }

    public function add() {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.marketing_notifications' ) ) ] );
        $this->data['content'] = 'admin.marketing_notifications.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.marketing_notifications' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.marketing_notifications' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.marketing_notifications' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );  
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.marketing_notifications' ) ) ] );
        $this->data['content'] = 'admin.marketing_notifications.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.marketing_notifications' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.marketing_notifications' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.marketing_notifications' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );  
    }

    public function allMarketingNotifications( Request $request ) {
        return MarketingNotificationService::allAnnouncements( $request );
    }

    public function oneMarketingNotification( Request $request ) {
        return MarketingNotificationService::oneAnnouncement( $request );
    }

    public function createMarketingNotification( Request $request ) {
        return MarketingNotificationService::createAnnouncement( $request );
    }

    public function updateMarketingNotification( Request $request ) {
        return MarketingNotificationService::updateAnnouncement( $request );
    }

    public function updateMarketingNotificationStatus( Request $request ) {
        return MarketingNotificationService::updateAnnouncementStatus( $request );
    }

    public function ckeUpload( Request $request ) {
        return FileManagerService::ckeUpload( $request );
    }
}
