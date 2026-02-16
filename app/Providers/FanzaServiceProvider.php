<?php

namespace App\Providers;

use App\Services\FanzaApiService;
use Illuminate\Support\ServiceProvider;

class FanzaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FanzaApiService::class, function () {
            return new FanzaApiService();
        });
    }

    public function boot(): void
    {
        //
    }
}
