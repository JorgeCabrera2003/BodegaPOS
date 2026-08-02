<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Sale;
use App\Models\Expense;
use Carbon\Carbon;

class FinanceStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // Income (Sales)
        $incomeThisMonth = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_usd');
        $incomeLastMonth = Sale::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->sum('total_usd');
        
        $incomeTrend = $incomeThisMonth - $incomeLastMonth;
        $incomeIcon = $incomeTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $incomeColor = $incomeTrend >= 0 ? 'success' : 'danger';

        // Expenses
        $expensesThisMonth = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->sum('amount_usd');
        $expensesLastMonth = Expense::whereBetween('expense_date', [$startOfLastMonth, $endOfLastMonth])->sum('amount_usd');
        
        $expensesTrend = $expensesThisMonth - $expensesLastMonth;
        // For expenses, going down is good (success), going up is bad (danger)
        $expensesIcon = $expensesTrend <= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up';
        $expensesColor = $expensesTrend <= 0 ? 'success' : 'danger';

        // Net Profit
        $netThisMonth = $incomeThisMonth - $expensesThisMonth;
        $netLastMonth = $incomeLastMonth - $expensesLastMonth;
        
        $netTrend = $netThisMonth - $netLastMonth;
        $netIcon = $netTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $netColor = $netTrend >= 0 ? 'success' : 'danger';

        return [
            Stat::make('Ingresos (Mes Actual)', '$' . number_format($incomeThisMonth, 2))
                ->description(($incomeTrend >= 0 ? '+' : '') . '$' . number_format($incomeTrend, 2) . ' vs mes anterior')
                ->descriptionIcon($incomeIcon)
                ->color($incomeColor),
                
            Stat::make('Egresos (Mes Actual)', '$' . number_format($expensesThisMonth, 2))
                ->description(($expensesTrend >= 0 ? '+' : '') . '$' . number_format($expensesTrend, 2) . ' vs mes anterior')
                ->descriptionIcon($expensesIcon)
                ->color($expensesColor),
                
            Stat::make('Balance Neto', '$' . number_format($netThisMonth, 2))
                ->description(($netTrend >= 0 ? '+' : '') . '$' . number_format($netTrend, 2) . ' vs mes anterior')
                ->descriptionIcon($netIcon)
                ->color($netColor),
        ];
    }
}
