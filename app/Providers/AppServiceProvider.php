<?php

namespace App\Providers;

use App\Support\EnsureDatabaseReady;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mode oneshot: cukup `php artisan serve`, database disiapkan otomatis.
        EnsureDatabaseReady::ensure();
    }
}
