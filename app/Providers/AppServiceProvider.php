<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use Illuminate\Support\ServiceProvider;
use MinVWS\OpenIDConnectLaravel\Http\Responses\LoginResponseInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LoginResponseInterface::class, LoginResponse::class);
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
