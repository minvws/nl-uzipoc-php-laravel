<?php

namespace App\Providers;

use App\Http\Responses\OidcLoginResponseHandler;
use Illuminate\Support\ServiceProvider;
use MinVWS\OpenIDConnectLaravel\Http\Responses\LoginResponseHandlerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LoginResponseHandlerInterface::class, OidcLoginResponseHandler::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
