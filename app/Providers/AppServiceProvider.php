<?php

namespace App\Providers;

use App\Extensions\Md5Hasher;
use Illuminate\Support\Facades\Hash;
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
        // See docs/VULN-MAP.md (A04) — this makes md5() the app-wide password hasher.
        Hash::extend('md5', fn () => new Md5Hasher);
    }
}
