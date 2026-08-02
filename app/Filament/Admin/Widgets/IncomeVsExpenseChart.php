<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Sale;
use App\Models\Expense;
use Carbon\Carbon;

class IncomeVsExpenseChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Ingresos vs Egresos (Este Mes)';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $sales = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(created_at) as date, SUM(total_usd) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $expenses = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(expense_date) as date, SUM(amount_usd) as total')
            ->groupBy('date')
            ->pluck('total', 'date');
            
        $daysInMonth = Carbon::now()->daysInMonth;
        $labels = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $labels[] = $i;
            $date = Carbon::now()->setDay($i)->format('Y-m-d');
            $incomeData[] = $sales->get($date, 0);
            $expenseData[] = $expenses->get($date, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos (Ventas)',
                    'data' => $incomeData,
                    'borderColor' => '#10b981', // green
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'fill' => 'start',
                ],
                [
                    'label' => 'Egresos (Gastos)',
                    'data' => $expenseData,
                    'borderColor' => '#ef4444', // red
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
