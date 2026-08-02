<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    // Ocupa 5 de 12 columnas — al lado del gráfico de ventas (7 cols)
    protected int|string|array $columnSpan = 5;

    public function getHeading(): string
    {
        return '⚠️ Stock Bajo';
    }

    public function getDescription(): ?string
    {
        return 'Productos que requieren reabastecimiento urgente.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
                    ->where('is_active', true)
                    ->orderBy('stock_quantity')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($record) => $record->stock_quantity <= 0 ? 'danger' : 'warning')
                    ->formatStateUsing(fn ($state) => $state <= 0 ? '⛔ Agotado' : $state . ' uds.'),

                Tables\Columns\TextColumn::make('min_stock_alert')
                    ->label('Mín.')
                    ->suffix(' uds.')
                    ->color('gray'),
            ])
            ->emptyStateHeading('✅ Inventario Saludable')
            ->emptyStateDescription('Todos los productos tienen stock suficiente.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->defaultPaginationPageOption(6)
            ->paginated([6, 10, 25]);
    }
}
