<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CurrencyService — Núcleo de conversión monetaria bimonetaria.
 *
 * Principio SRP: Esta clase solo se ocupa de conversiones de divisas.
 * Principio DRY: Toda operación matemática de moneda pasa por aquí.
 *
 * La tasa se lee EXCLUSIVAMENTE desde Redis para máximo rendimiento.
 * La base de datos solo se consulta en caso de cache miss.
 */
class CurrencyService
{
    public const CACHE_KEY     = 'active_bcv_rate';
    public const CACHE_TTL_HRS = 12;
    public const IGTF_RATE     = 0.03; // 3% — Ley de IGTF Venezuela

    /**
     * Obtiene la tasa BCV activa.
     * Lee desde Redis; si no existe, consulta DB y almacena en cache.
     */
    public function getActiveRate(): float
    {
        return (float) Cache::remember(
            self::CACHE_KEY,
            now()->addHours(self::CACHE_TTL_HRS),
            function () {
                $rate = ExchangeRate::latestRate()->value('rate');

                if (! $rate) {
                    Log::warning('CurrencyService: No se encontró tasa BCV activa en DB.');
                    return 1.0;
                }

                return $rate;
            }
        );
    }

    /**
     * Convierte dólares a bolívares usando la tasa activa.
     */
    public function usdToVes(float $usd, ?float $rate = null): float
    {
        $rate ??= $this->getActiveRate();
        return $this->roundVes($usd * $rate);
    }

    /**
     * Convierte bolívares a dólares usando la tasa activa.
     */
    public function vesToUsd(float $ves, ?float $rate = null): float
    {
        $rate ??= $this->getActiveRate();

        if ($rate === 0.0) {
            return 0.0;
        }

        return $this->roundUsd($ves / $rate);
    }

    /**
     * Calcula el IGTF (3%) sobre un monto en USD.
     * Aplica únicamente cuando el método de pago es en divisas.
     */
    public function calculateIgtf(float $amountUsd): float
    {
        return $this->roundUsd($amountUsd * self::IGTF_RATE);
    }

    /**
     * Redondeo contable estándar: 2 decimales, modo HALF_UP.
     */
    public function roundUsd(float $amount): float
    {
        return round($amount, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Redondeo para bolívares: 2 decimales.
     */
    public function roundVes(float $amount): float
    {
        return round($amount, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Formatea un monto en USD para display.
     */
    public function formatUsd(float $amount): string
    {
        return '$ ' . number_format($amount, 2, '.', ',');
    }

    /**
     * Formatea un monto en VES para display.
     */
    public function formatVes(float $amount): string
    {
        return 'Bs. ' . number_format($amount, 2, ',', '.');
    }

    /**
     * Actualiza la tasa en cache Redis con el nuevo valor.
     * Llamado por FetchBcvRateCommand tras insertar en DB.
     */
    public function updateCachedRate(float $rate): void
    {
        Cache::put(self::CACHE_KEY, $rate, now()->addHours(self::CACHE_TTL_HRS));
        Log::info("CurrencyService: Tasa BCV actualizada en cache: {$rate}");
    }

    /**
     * Invalida el cache forzando recarga desde DB en la próxima consulta.
     */
    public function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
