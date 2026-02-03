<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Spatie\Activitylog\Models\Activity;

use App\Models\{
    Item,
    Playlist,
    Collection
};

use App\Observers\{
    ItemObserver,
    PlaylistObserver,
    CollectionObserver
};

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

require_once( 'BrowserDetection.php' );

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $browser = new \Wolfcast\BrowserDetection();

        Activity::saving( function( Activity $activity ) use ( $browser ) {

            $activity->properties = $activity->properties->put( 'agent', [
                'ip' => request()->ip(),
                'user_agent' => $browser->getUserAgent(),
                'browserName' => $browser->getName() . ' ' . $browser->getVersion(),
                'os' => $browser->getPlatformVersion() . ' ' . $browser->getPlatformVersion( true ),
            ] );
        } );

        if( $this->app->environment( 'production' ) ) {
            \URL::forceScheme( 'https' );
        }

        Item::observe(ItemObserver::class);
        Playlist::observe(PlaylistObserver::class);

        
        if( !$this->app->environment( 'production' ) ) {
           Http::macro('logRequests', function () {
                return Http::withOptions([
                    'debug' => true,
                ]);
            });
            
            // 或者全局监听 HTTP 请求
            Http::globalRequestMiddleware(function ($request) {
                Log::channel('payment')->info('HTTP Request', [
                    'method' => $request->method(),
                    'url' => (string) $request->url(),
                    'headers' => $request->headers(),
                    'body' => $request->body(),
                ]);
                
                return $request;
            });
            
            Http::globalResponseMiddleware(function ($response) {
                Log::channel('payment')->info('HTTP Response', [
                    'status' => $response->status(),
                    'url' => (string) $response->effectiveUri(),
                    'body' => $response->body(),
                ]);
                
                return $response;
            });
        }
    }
}
