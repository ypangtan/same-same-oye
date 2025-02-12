<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    VendingMachineService,
};

class VendingMachineController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.vending_machines' );
        $this->data['content'] = 'admin.vending_machine.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.vending_machines' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
            '21' => __( 'vending_machine.maintenance_required' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vending_machines' ) ) ] );
        $this->data['content'] = 'admin.vending_machine.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.vending_machine.index' ),
                'text' => __( 'template.vending_machines' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.vending_machines' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.vending_machines' ) ) ] );
        $this->data['content'] = 'admin.vending_machine.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.vending_machine.index' ),
                'text' => __( 'template.vending_machines' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.vending_machines' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allVendingMachines( Request $request ) {

        return VendingMachineService::allVendingMachines( $request );
    }

    public function oneVendingMachine( Request $request ) {

        return VendingMachineService::oneVendingMachine( $request );
    }

    public function createVendingMachine( Request $request ) {

        return VendingMachineService::createVendingMachine( $request );
    }

    public function updateVendingMachine( Request $request ) {

        return VendingMachineService::updateVendingMachine( $request );
    }

    public function updateVendingMachinestatus( Request $request ) {

        return VendingMachineService::updateVendingMachinestatus( $request );
    }

    public function removeVendingMachineGalleryImage( Request $request ) {

        return VendingMachineService::removeVendingMachineGalleryImage( $request );
    }
}
