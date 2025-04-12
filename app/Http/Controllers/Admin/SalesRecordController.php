<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    SalesRecordService,
};

class SalesRecordController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.sales_records' );
        $this->data['content'] = 'admin.sales_record.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.sales_records' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
            '21' => __( 'datatables.redeemed' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.sales_records' ) ) ] );
        $this->data['content'] = 'admin.sales_record.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.sales_record.index' ),
                'text' => __( 'template.sales_records' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.sales_records' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.sales_records' ) ) ] );
        $this->data['content'] = 'admin.sales_record.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.sales_record.index' ),
                'text' => __( 'template.sales_records' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.sales_records' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function import( Request $request ) {

        $this->data['header']['title'] = __( 'template.import_x', [ 'title' => \Str::singular( __( 'template.sales_records' ) ) ] );
        $this->data['content'] = 'admin.sales_record.import';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.sales_record.index' ),
                'text' => __( 'template.sales_records' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.import_x', [ 'title' => \Str::singular( __( 'template.sales_records' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function importSalesRecords( Request $request ) {

        return SalesRecordService::importSalesRecords( $request );
    }
     
    public function allSalesRecords( Request $request ) {

        return SalesRecordService::allSalesRecords( $request );
    }

    public function oneSalesRecord( Request $request ) {

        return SalesRecordService::oneSalesRecord( $request );
    }

    public function createSalesRecord( Request $request ) {

        return SalesRecordService::createSalesRecord( $request );
    }

    public function updateSalesRecord( Request $request ) {

        return SalesRecordService::updateSalesRecord( $request );
    }

    public function updateSalesRecordStatus( Request $request ) {

        return SalesRecordService::updateSalesRecordStatus( $request );
    }
}
