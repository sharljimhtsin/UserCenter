<?php

namespace App\Providers;

use App\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
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
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * @var $authService AuthManager
         * @var $authorService Gate
         */
        $authorService = $this->app[Gate::class];
        $authService = $this->app['auth'];
        $authService->viaRequest('api', function (Request $request) {
            if ($request->input('api_token')) {
                return User::getQuery()->where('api_token', $request->input('api_token'))->first();
            } else {
                return null;
            }
        });
        $authorService->define('index', function () {
            return false;
        });
    }
}
