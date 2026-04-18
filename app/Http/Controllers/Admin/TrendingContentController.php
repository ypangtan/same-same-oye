<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    TrendingContentService,
    FileManagerService,
    FileService,
};

class TrendingContentController extends Controller
{
    public function index() {

        $this->data['header']['title'] = __( 'template.trending_contents' );
        $this->data['content'] = 'admin.trending_content.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.trending_contents' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.trending_contents' ),
        ];
        
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];
        
        return view( 'admin.main' )->with( $this->data );   
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.trending_contents' ) ) ] );
        $this->data['content'] = 'admin.trending_content.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.trending_contents' ),
            'title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.trending_contents' ) ) ] ),
            'mobile_title' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.trending_contents' ) ) ] ),
        ];

        return view( 'admin.main' )->with( $this->data );  
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.trending_contents' ) ) ] );
        $this->data['content'] = 'admin.trending_content.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.trending_contents' ),
            'title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.trending_contents' ) ) ] ),
            'mobile_title' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.trending_contents' ) ) ] ),
        ];


        return view( 'admin.main' )->with( $this->data );  
    }

    public function allTrendingContents( Request $request ) {
        return TrendingContentService::allTrendingContents( $request );
    }

    public function oneTrendingContent( Request $request ) {
        return TrendingContentService::oneTrendingContent( $request );
    }

    public function createTrendingContent( Request $request ) {
        return TrendingContentService::createTrendingContent( $request );
    }

    public function updateTrendingContent( Request $request ) {
        return TrendingContentService::updateTrendingContent( $request );
    }

    public function updateTrendingContentStatus( Request $request ) {
        return TrendingContentService::updateTrendingContentStatus( $request );
    }

    public function songUpload( Request $request ) {
        return FileService::songUpload( $request );
    }

    public function imageUpload( Request $request ) {
        $request->merge( [
            'source' => 'image/trending_content'
        ] );
        return FileService::imageUpload( $request );
    }

    public function updateOrder( Request $request ) {
        return TrendingContentService::updateOrder( $request );
    }
}
