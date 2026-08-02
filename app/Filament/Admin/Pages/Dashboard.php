<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\LowStockWidget;
use App\Filament\Admin\Widgets\SalesChartWidget;
use App\Filament\Admin\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Escritorio';
    protected static ?string $title = 'Escritorio';
    protected static ?int $navigationSort = 0;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-home';
    }

    /**
     * Cuadrícula de 12 columnas para ubicar widgets en fracciones exactas.
     * SalesChartWidget (7 cols) + LowStockWidget (5 cols) = 12 (misma fila).
     */
    public function getColumns(): int|array
    {
        return 12;
    }

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            SalesChartWidget::class,
            LowStockWidget::class,
        ];
    }
}
