<?php

namespace App\Providers;

use App\User\Auth\SRP;
use App\User\Controllers\Login;
use ArtisanSdk\SRP\Config;
use ArtisanSdk\SRP\Server;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class Auth extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $config = $this->app['config']->get('srp');

        $this->app->when(Login::class)
            ->needs(SRP::class)
            ->give(function ($app) use ($config) {
                return new SRP(
                    new Server(Config::fromArray($config)),
                    $app['cache.store'],
                    $config['ttl']
                );
            });
    }
}
