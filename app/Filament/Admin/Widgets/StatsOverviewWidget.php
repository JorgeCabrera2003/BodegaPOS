<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use App\Models\Sale;
use App\Services\CurrencyService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public function getHeading(): string
    {
        return 'Resumen del Negocio';
    }

    public function getDescription(): ?string
    {
        return 'Indicadores clave de hoy y del inventario';
    }

    protected function getStats(): array
    {
        $currency = app(CurrencyService::class);
        $rateVes  = $currency->getActiveRate();

        // Ventas de hoy
        $todaySales    = Sale::completed()->today()->get();
        $todayTotalUsd = $todaySales->sum('total_usd');
        $todayTotalVes = $currency->usdToVes($todayTotalUsd);
        $todayCount    = $todaySales->count();

        // Ventas de ayer para tendencia
        $yesterdayUsd = Sale::completed()
            ->whereDate('created_at', today()->subDay())
            ->sum('total_usd');
        $trendPercent = $yesterdayUsd > 0
            ? round((($todayTotalUsd - $yesterdayUsd) / $yesterdayUsd) * 100, 1)
            : 0;

        // Inventario
        $lowStockCount    = Product::whereColumn('stock_quantity', '<=', 'min_stock_alert')->count();
        $outOfStockCount  = Product::where('stock_quantity', '<=', 0)->count();
        $totalProducts    = Product::where('is_active', true)->count();

        // Ventas esta semana
        $weekSales    = Sale::completed()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_usd');
        $weekSalesVes = $currency->usdToVes($weekSales);

        return [
            Stat::make('💰 Ventas de Hoy (USD)', '$' . number_format($todayTotalUsd, 2))
                ->description(number_format($todayTotalVes, 2, ',', '.') . ' Bs · ' . $todayCount . ' ' . ($todayCount === 1 ? 'transacción' : 'transacciones'))
                ->descriptionIcon($trendPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trendPercent >= 0 ? 'success' : 'danger')
                ->chart($this->getDailySalesChart()),

            Stat::make('📦 Stock Bajo / Agotado', $lowStockCount . ' / ' . $outOfStockCount)
                ->description('De ' . $totalProducts . ' productos activos en catálogo')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'warning' : 'success'),

            Stat::make('📅 Ventas Esta Semana (USD)', '$' . number_format($weekSales, 2))
                ->description(number_format($weekSalesVes, 2, ',', '.') . ' Bs  · Tasa BCV: Bs. ' . number_format($rateVes, 4, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }

    private function getDailySalesChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = (float) Sale::completed()
                ->whereDate('created_at', today()->subDays($i))
                ->sum('total_usd');
        }
        return $data;
    }
}
