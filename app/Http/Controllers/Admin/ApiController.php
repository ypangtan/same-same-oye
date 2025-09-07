<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AdministratorService,
};

use Illuminate\Support\Facades\{
    DB,
};

class ApiController extends Controller
{
    public function index( Request $request ) {
        $code = $request->query('code', ''); // keep code available if needed

        return view( 'client.invite', [ 'code' => $code ] );
    }
}
