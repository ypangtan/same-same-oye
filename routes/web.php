<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get( '/', function () {
//     return view( 'welcome' );
// } );

Route::get('/register', function (Request $request) {    
    $userAgent = $request->header('User-Agent');

    if (preg_match('/Android/i', $userAgent)) {
        return redirect('https://play.google.com/store/apps/details?id=com.yobe.android');
    } elseif (preg_match('/iPhone/i', $userAgent)) {
        return redirect('https://apps.apple.com/my/app/yobe/id6740760943');
    }
    
    $code = $request->query('code');

    return response()->json([
        'code' => $code,
        'all_parameters' => $request->all(),
    ]);

});

// This is admin route
require __DIR__ . '/admin.php';
