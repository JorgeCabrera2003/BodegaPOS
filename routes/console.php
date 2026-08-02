<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ─── BodegaPOS Task Scheduling ────────────────────────────────────────────────

/**
 * Sincronización automática de la tasa BCV.
 * Se ejecuta dos veces al día según los bloques de actualización del BCV:
 *   - 09:00 AM: Tasa de apertura de jornada
 *   - 13:00 PM: Tasa de mediodía (bloque vespertino)
 *
 * Zona horaria: America/Caracas (UTC-4, sin cambio de horario)
 * withoutOverlapping(): Previene race conditions si el proceso tarda
 * onOneServer(): Garantiza ejecución única en infraestructura multi-servidor
 */
Schedule::command('currency:fetch-bcv')
    ->twiceDaily(9, 13)
    ->timezone('America/Caracas')
    ->withoutOverlapping(10) // Lock máximo de 10 minutos
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/bcv-scheduler.log'));
