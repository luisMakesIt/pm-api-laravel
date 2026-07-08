<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Define basic policy gates
        Gate::define('manage-project', function ($user, $project) {
            return true;
        });

        Gate::define('view-project', function ($user, $project) {
            return true;
        });

        // API version in responses
    }
}
