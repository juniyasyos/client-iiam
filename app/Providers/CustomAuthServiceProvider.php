<?php

namespace App\Providers;

use App\Http\Controllers\Auth\CustomSessionGuard;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class CustomAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Extend Auth manager to add custom session guard
        Auth::extend('custom_session', function ($app, $name, $config) {
            $provider = Auth::createUserProvider($config['provider'] ?? null);

            $guard = new CustomSessionGuard(
                $name,
                $provider,
                $app['session.store'],
                $app['request']
            );

            // Set cookie jar
            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($app['cookie']);
            }

            // Set dispatcher
            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($app['events']);
            }

            // Set request
            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($app->refresh('request', $guard, 'setRequest'));
            }

            return $guard;
        });
    }
}
