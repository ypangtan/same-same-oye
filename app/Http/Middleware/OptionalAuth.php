<?php

namespace App\Http\Middleware;

use App\Models\Guest;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Support\Facades\Crypt;

class OptionalAuth extends Middleware
{
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {

        $user = null;

        foreach ($guards as $guard) {
            if ($guardUser = Auth::guard($guard)->user()) {
                $user = $guardUser;
                break;
            }
        }

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo( $request )
    {
        if ( !$request->expectsJson() ) {

            if ( request()->is( 'backoffice/*' ) ) {
                return route( 'admin.login' );
            }
            
            return route( 'web.login' );
        }
    }
}
