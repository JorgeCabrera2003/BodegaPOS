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
        // Poor man's cron for Laragon / local environments
        // Ejecuta la sincronización en segundo plano usando defer() (Laravel 11.23+)
        // para que no bloquee la carga de la página del usuario.
        if (! app()->runningInConsole()) {
            if (! \Illuminate\Support\Facades\Cache::has('bcv_sync_checked')) {
                // Bloquear por 60 minutos para no saturar procesos
                \Illuminate\Support\Facades\Cache::put('bcv_sync_checked', true, now()->addMinutes(60));
                
                defer(function () {
                    \Illuminate\Support\Facades\Artisan::call('currency:fetch-bcv');
                });
            }
        }
    }
}
