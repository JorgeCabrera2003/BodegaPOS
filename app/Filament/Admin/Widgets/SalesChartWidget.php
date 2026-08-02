<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Sale;
use App\Services\CurrencyService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    // Ocupa 7 de 12 columnas — comparte fila con LowStockWidget (5 cols)
    protected int|string|array $columnSpan = 7;

    public function getHeading(): string
    {
        return '📊 Ventas de los Últimos 7 Días';
    }

    public function getDescription(): ?string
    {
        return 'Eje izquierdo → USD ($) · Eje derecho → Bolívares (Bs.) · Cada moneda usa su propia escala para comparación justa.';
    }

    protected function getData(): array
    {
        $labels   = [];
        $dataUsd  = [];
        $dataVes  = [];
        $currency = app(CurrencyService::class);

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->isoFormat('ddd D/M');

            $usd = (float) \App\Models\Payment::whereHas('sale', fn ($q) => $q->completed())
                ->whereIn('payment_method', \App\Models\Payment::$foreignCurrencyMethods)
                ->whereDate('created_at', $date)
                ->sum('amount_usd');

            $ves = (float) \App\Models\Payment::whereHas('sale', fn ($q) => $q->completed())
                ->whereNotIn('payment_method', \App\Models\Payment::$foreignCurrencyMethods)
                ->whereDate('created_at', $date)
                ->sum('amount_ves');

            $dataUsd[] = round($usd, 2);
            $dataVes[] = round($ves, 2);
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Ventas (USD $)',
                    'data'            => $dataUsd,
                    'borderColor'     => 'rgb(99, 102, 241)',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.15)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y',     // Eje izquierdo
                ],
                [
                    'label'           => 'Ventas (Bs.)',
                    'data'            => $dataVes,
                    'borderColor'     => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y1',    // Eje derecho independiente
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'interaction' => [
                'mode'      => 'index',
                'intersect' => false,
            ],
            'scales' => [
                'y' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'left',
                    'title'    => [
                        'display' => true,
                        'text'    => 'USD ($)',
                        'color'   => 'rgb(99, 102, 241)',
                    ],
                    'grid' => [
                        'color' => 'rgba(99, 102, 241, 0.1)',
                    ],
                    'ticks' => [
                        'color'    => 'rgb(99, 102, 241)',
                        'callback' => "function(v){ return '$' + v.toFixed(2); }",
                    ],
                ],
                'y1' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'right',
                    'title'    => [
                        'display' => true,
                        'text'    => 'Bolívares (Bs.)',
                        'color'   => 'rgb(16, 185, 129)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false, // Evita cuadrícula doble
                    ],
                    'ticks' => [
                        'color'    => 'rgb(16, 185, 129)',
                        'callback' => "function(v){ return 'Bs. ' + v.toLocaleString('es-VE'); }",
                    ],
                ],
            ],
        ];
    }
}
