<?php

namespace App\Providers;

use App\User\Auth\Controller;
use App\User\Auth\SRP;
use ArtisanSdk\SRP\Config;
use ArtisanSdk\SRP\Server;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;

class Auth extends AuthServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->when(Controller::class)
            ->needs(SRP::class)
            ->give(function ($app) {
                $config = $app['config']->get('srp');

                return new SRP(
                    new Server(Config::fromArray($config)),
                    $app['cache.store'],
                    $config['ttl']
                );
            });
    }
}
