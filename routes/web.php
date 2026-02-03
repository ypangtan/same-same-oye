<?php

use App\Http\Controllers\Admin\ApiController;
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

Route::get('.well-known/apple-app-site-association', function () {
    $data = [
        'applinks' => [
            'details' => [
                [
                    'appIDs' => [
                        '8XQG3SLQJX.com.mecar.user'
                    ],
                    'components' => [
                        [
                            '#' => 'no_universal_links',
                            'exclude' => true,
                            'comment' => 'Matches any URL with a fragment that equals no_universal_links and instructs the system not to open it as a universal link.'
                        ],
                        [
                            '/' => '/register/*',
                            'comment' => 'Matches any URL with a path that starts with /register/.'
                        ]
                    ]
                ]
            ]
        ],
        'webcredentials' => [
            'apps' => [
                '8XQG3SLQJX.com.mecar.user'
            ]
        ]
    ];
    
    return response()->json($data);
});

Route::get('/register', function (Request $request) {    
    $userAgent = $request->header('User-Agent');

    if (preg_match('/Android/i', $userAgent)) {
        return redirect('https://play.google.com/store/apps/details?id=com.ifei.android');
    } elseif (preg_match('/iPhone/i', $userAgent)) {
        return redirect('https://apps.apple.com/my/app/ifei/id6740760943');
    }
    
    $code = $request->query('code');

    return response()->json([
        'code' => $code,
        'all_parameters' => $request->all(),
        'agent' => $userAgent,
    ]);

});


// This is admin route
require __DIR__ . '/admin.php';
