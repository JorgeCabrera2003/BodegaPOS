<?php

namespace App\Filament\Admin\Resources\Sales\Tables;

use App\Services\CurrencyService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('# Venta')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('cashier.name')
                    ->label('Cajero')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->default('—')
                    ->toggleable(),

                TextColumn::make('subtotal_usd')
                    ->label('Subtotal')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('igtf_usd')
                    ->label('IGTF (3%)')
                    ->money('USD')
                    ->sortable()
                    ->color('warning')
                    ->toggleable(),

                TextColumn::make('total_usd')
                    ->label('Total USD')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('total_ves')
                    ->label('Total Bs.')
                    ->formatStateUsing(fn ($state) => 'Bs. ' . number_format((float)$state, 2, ',', '.'))
                    ->sortable()
                    ->color('info'),

                TextColumn::make('amount_paid_usd')
                    ->label('Monto Cobrado')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('change_usd')
                    ->label('Vuelto')
                    ->money('USD')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'pending'   => 'warning',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => '✅ Completada',
                        'cancelled' => '❌ Cancelada',
                        'pending'   => '⏳ Pendiente',
                        default     => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Fecha / Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->timezone('America/Caracas'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'completed' => '✅ Completadas',
                        'cancelled' => '❌ Canceladas',
                        'pending'   => '⏳ Pendientes',
                    ]),
                TrashedFilter::make()->label('Eliminadas'),
            ])
            ->recordActions([
                EditAction::make()->label('Ver / Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                    ForceDeleteBulkAction::make()->label('Eliminar permanente'),
                    RestoreBulkAction::make()->label('Restaurar'),
                ]),
            ]);
    }
}
