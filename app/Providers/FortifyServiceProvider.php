<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Hash,
    RateLimiter,
    Validator,
};
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

use App\Models\{
    Administrator,
};

use Helper;

Use Carbon\Carbon;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ( request()->is( config( 'services.url.admin_path' ) . '/*' ) ) {
            config()->set( 'fortify.guard', 'admin' );
            config()->set( 'fortify.home', config( 'services.url.admin_path' ) . '/dashboard' );
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing( CreateNewUser::class );
        Fortify::updateUserProfileInformationUsing( UpdateUserProfileInformation::class );
        Fortify::updateUserPasswordsUsing( UpdateUserPassword::class );
        Fortify::resetUserPasswordsUsing( ResetUserPassword::class );

        Fortify::loginView( function () {
            return redirect()->route( 'admin.login' );
        } );

        RateLimiter::for( 'login', function ( Request $request ) {
            $email = (string) $request->email;

            return Limit::perMinute( 5 )->by( $email.$request->ip() );
        } );

        RateLimiter::for( 'two-factor', function ( Request $request ) {
            return Limit::perMinute( 5 )->by( $request->session()->get( 'login.id' ) );
        } );

        Fortify::authenticateUsing( function ( Request $request ) {
            $validator = Validator::make( $request->all(), [
                'email' => [ 'required', function( $attribute, $value, $fail ) use ( $request, &$administrator ) {
                    $administrator = Administrator::where( function( $query ) use ( $request ) {
                            $query->where( 'email', $request->email )
                                ->orWhere( 'phone_number', $request->email );
                        })
                        ->where( 'status', 10 )
                        ->first();

                    if ( !$administrator || !Hash::check( $request->password, $administrator->password ) ) {
                        $fail( __( 'auth.failed' ) );
                        return ;
                    }
                } ],
                'password' => 'required',
            ] );

            $attributeName = [
                'username' => __( 'administrator.email' ),
            ];
    
            foreach( $attributeName as $key => $aName ) {
                $attributeName[$key] = strtolower( $aName );
            }
            
            $validator->setAttributeNames( $attributeName )->validate();
            
            return $administrator;
        } );


        $this->app->singleton(
            \Laravel\Fortify\Contracts\LogoutResponse::class,
            \App\Http\Responses\LogoutResponse::class
        );
    }
}
