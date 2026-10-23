<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    FestiveAnimationService,
};

class FestiveAnimationController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.festive_animations' );
        $this->data['content'] = 'admin.festive_animation.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.module_parent.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.festive_animations' ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['file_type'] = [
            '1' => __( 'festive_animation.image' ),
            '2' => __( 'festive_animation.gif' ),
            '3' => __( 'festive_animation.video' ),
            '4' => __( 'festive_animation.other' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.festive_animations' ) ) ] );
        $this->data['content'] = 'admin.festive_animation.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.module_parent.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.festive_animation.index' ),
                'text' => __( 'template.festive_animations' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.festive_animations' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.festive_animations' ) ) ] );
        $this->data['content'] = 'admin.festive_animation.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.module_parent.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.festive_animation.index' ),
                'text' => __( 'template.festive_animations' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.festive_animations' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allFestiveAnimations( Request $request ) {

        return FestiveAnimationService::allFestiveAnimations( $request );
    }

    public function oneFestiveAnimation( Request $request ) {

        return FestiveAnimationService::oneFestiveAnimation( $request );
    }

    public function createFestiveAnimation( Request $request ) {

        return FestiveAnimationService::createFestiveAnimation( $request );
    }

    public function updateFestiveAnimation( Request $request ) {

        return FestiveAnimationService::updateFestiveAnimation( $request );
    }

    public function updateFestiveAnimationStatus( Request $request ) {

        return FestiveAnimationService::updateFestiveAnimationStatus( $request );
    }

    public function deleteFestiveAnimation( Request $request ) {

        return FestiveAnimationService::deleteFestiveAnimation( $request );
    }

    public function fileUpload( Request $request ) {

        return FestiveAnimationService::fileUpload( $request );
    }
}
