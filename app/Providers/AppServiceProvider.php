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
use Illuminate\Support\Facades\Log;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};

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

        app()->singleton(HandlerStack::class, function () {
            $stack = HandlerStack::create();
            
            $stack->push(Middleware::tap(
                function (RequestInterface $request) {
                    Log::channel('payment')->info('=== HTTP Request ===', [
                        'method' => $request->getMethod(),
                        'url' => (string) $request->getUri(),
                        'headers' => $request->getHeaders(),
                        'body' => (string) $request->getBody(),
                    ]);
                },
                function (RequestInterface $request, $options, ResponseInterface $response) {
                    Log::channel('payment')->info('=== HTTP Response ===', [
                        'url' => (string) $request->getUri(),
                        'status' => $response->getStatusCode(),
                        'body' => (string) $response->getBody(),
                    ]);
                }
            ));
            
            return $stack;
        });
    }
}
