<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('manage-project', function ($user, $project) {
            return true;
        });

        Gate::define('view-project', function ($user, $project) {
            return true;
        });

        $this->loadViewsFrom(resource_path('views'), 'pmapi');
    }
}
