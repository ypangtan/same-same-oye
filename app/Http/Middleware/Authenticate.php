<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if ($request->is('playlist/share*')) {
            return null; // 或 redirect 去 app 的页面
        }

        if ( ! $request->expectsJson() ) {

            if( request()->is( config( 'services.url.admin_path' ) . '/*' ) ) {
                return route( 'admin.signin' );
            }

            return route( 'login' );
        }
    }
}
