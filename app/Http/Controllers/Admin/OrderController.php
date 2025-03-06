<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    OrderService,
    BookingService,
};

use Illuminate\Support\Facades\{
    DB,
};

class OrderController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.orders' );
        $this->data['content'] = 'admin.order.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.orders' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '1' => __( 'datatables.order_placed' ),
            '2' => __( 'datatables.order_pending_payment' ),
            '3' => __( 'datatables.order_paid' ),
            '10' => __( 'datatables.order_completed' ),
            '20' => __( 'datatables.order_canceled' ),
        ];
        $this->data['data']['company'] = [];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.orders' );
        $this->data['content'] = 'admin.order.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.orders' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['grade'] = [
            'A',
            'B',
            'C',
            'D',
        ];
        $this->data['data']['order_increment'] = rand(1000, 9999);

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.orders' );
        $this->data['content'] = 'admin.order.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.orders' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function salesReport( Request $request ) {

        $this->data['header']['title'] = __( 'template.sales_report' );
        $this->data['content'] = 'admin.order.sales_report';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.orders' ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['orders'] = OrderService::salesReport( $request );
        $this->data['data']['grades'] = [
            'A',
            'B',
            'C',
            'D',
        ];

        $this->data['data']['status'] = [
            '1' => __( 'datatables.order_placed' ),
            '2' => __( 'datatables.order_pending_payment' ),
            '3' => __( 'datatables.order_paid' ),
            '10' => __( 'datatables.order_completed' ),
            '20' => __( 'datatables.order_canceled' ),
        ];
        return view( 'admin.main' )->with( $this->data );
    }

    public function allOrders( Request $request ) {
        return OrderService::allOrders( $request );
    }

    public function oneOrder( Request $request ) {
        return OrderService::oneOrder( $request );
    }

    public function createOrder( Request $request ) {
        return OrderService::createOrder( $request );
    }

    public function updateOrder( Request $request ) {
        return OrderService::updateOrder( $request );
    }

    public function getSalesReport( Request $request ) {
        return OrderService::getSalesReport( $request );
    }

    public function export( Request $request ) {
        return OrderService::exportOrders( $request );
    }

    public function updateOrderStatus( Request $request ) {
        return OrderService::updateOrderStatus( $request );
    }

    public function scanner( Request $request ) {
        $this->data['header']['title'] = __( 'template.scan_qr' );
        $this->data['content'] = 'admin.order.scan';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.orders' ),
                'class' => 'active',
            ],
        ];
        return view( 'admin.main' )->with( $this->data );
    }

    public function scannedOrder( Request $request ) {
        return OrderService::scannedOrder( $request );
    }

    public function updateOrderStatusView( Request $request ) {
        return OrderService::updateOrderStatusView( $request );
    }

    public function generateTestOrder( Request $request ) {
        return OrderService::generateTestOrder( $request );
    }

}
