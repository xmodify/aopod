<?php

namespace App\Providers;

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
        // บังคับให้ Laravel ใช้ APP_URL จาก .env เสมอ เมื่อรันหลัง Reverse Proxy / Port Forwarding
        if (config('app.env') === 'production' && config('app.url')) {
            \URL::forceRootUrl(config('app.url'));
            
            // หาก APP_URL เป็น https ให้บังคับการเชื่อมต่อเป็น https ทั้งหมด
            if (str_starts_with(config('app.url'), 'https://')) {
                \URL::forceScheme('https');
            }
        }
    }
}
