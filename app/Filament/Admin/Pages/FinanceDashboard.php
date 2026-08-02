<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use App\Filament\Admin\Widgets\FinanceStatsWidget;
use App\Filament\Admin\Widgets\IncomeVsExpenseChart;

class FinanceDashboard extends Page
{
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-banknotes';
    }

    public static function getNavigationLabel(): string
    {
        return 'Finanzas';
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Panel de Finanzas';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Finanzas';
    }

    public static function getNavigationSort(): ?int
    {
        return 11;
    }

    protected string $view = 'filament.admin.pages.finance-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            FinanceStatsWidget::class,
            IncomeVsExpenseChart::class,
        ];
    }
}
