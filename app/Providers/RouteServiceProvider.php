<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{

    protected $apiNamespace ='App\Http\Controllers\Api';


    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Route::prefix('api')
            //     ->middleware('api')
            //     ->group(base_path('routes/api_v1.php'));

            Route::group( [
                'middleware' => [ 'api', 'api_version:v1' ],
                'namespace' => "{ $this->apiNamespace }\V1",
                'prefix' => 'api/v1',
            ], function( $router ) {
                require base_path( 'routes/api_v1.php' );
            } );
            
        });
        
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(200);
        });

        // The radio streaming engine (Liquidsoap) polls /api/v1/radio/next repeatedly whenever
        // the queue is empty (no built-in backoff), so it needs its own, much more generous
        // bucket instead of sharing the general 'api' one — see routes/api_v1.php.
        RateLimiter::for('radio-engine', function (Request $request) {
            return Limit::perMinute(600);
        });
    }
}
