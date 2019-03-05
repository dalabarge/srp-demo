<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

class Routes extends RouteServiceProvider
{
    /**
     * Define the routes for the application.
     */
    public function map()
    {
        Route::middleware('web')
            ->namespace('App\Http')
            ->group(base_path('routes/web.php'));
    }
}
