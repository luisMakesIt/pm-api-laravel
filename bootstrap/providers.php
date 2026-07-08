<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Load DomPDF service provider
        $this->loadViewsFrom(resource_path('views'), 'pmapi');
    }
}
